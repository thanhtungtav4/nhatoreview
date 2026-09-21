<?php

declare(strict_types=1);

namespace Theme\Child\Hooks;

defined('ABSPATH') || exit;

/**
 * Web Vitals: inline critical CSS + style loading strategies (preload / media swap) + jQuery xuống footer.
 *
 * Script defer/async: dùng API core `wp_enqueue_script(..., ['strategy' => 'defer'])` hoặc
 * `wp_script_add_data($handle, 'strategy', 'defer')` cho handle plugin — không filter script_loader_tag.
 */
final class PerformanceHook
{
    public static function register(): void
    {
        $self = new self();
        add_action('wp_enqueue_scripts', [$self, 'optimize_jquery'], 1);
        add_action('wp_head', [$self, 'output_critical_css'], 2);
        add_filter('style_loader_tag', [$self, 'apply_style_loading_strategy'], 20, 4);
    }

    /**
     * jQuery mặc định in trong <head> → chặn render (~30KB + migrate). Đưa về footer (group 1).
     *
     * An toàn với script enqueue đúng chuẩn: script nào in ở <head> mà khai báo phụ thuộc `jquery`,
     * core (WP_Scripts::set_group) tự kéo jQuery lên lại <head> cho trang đó.
     * Chỉ vỡ khi plugin/theme echo `<script>jQuery(...)</script>` trần trong body/head không qua enqueue
     * → tắt: add_filter('underscores_child_jquery_in_footer', '__return_false');
     *
     * jquery-migrate: giữ mặc định (plugin cũ dùng API jQuery đã bỏ). Site đã kiểm tra console sạch →
     *   add_filter('underscores_child_jquery_migrate', '__return_false');
     */
    public function optimize_jquery(): void
    {
        if (is_admin() || is_customize_preview()) {
            return;
        }

        $scripts = wp_scripts();

        if (! apply_filters('underscores_child_jquery_migrate', true) && isset($scripts->registered['jquery'])) {
            $scripts->registered['jquery']->deps = array_values(array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']));
        }

        if (! apply_filters('underscores_child_jquery_in_footer', true)) {
            return;
        }

        foreach (['jquery', 'jquery-core', 'jquery-migrate'] as $handle) {
            if (isset($scripts->registered[$handle])) {
                $scripts->add_data($handle, 'group', 1);
            }
        }
    }

    public function output_critical_css(): void
    {
        if (is_admin()) {
            return;
        }

        $critical_css = underscores_child_get_critical_css_contents();

        if ($critical_css === '') {
            return;
        }

        $critical_css_path = underscores_child_get_critical_css_path();
        $critical_source = $critical_css_path ? basename($critical_css_path) : 'inline';

        echo '<style id="underscores-child-critical-css" data-source="' . esc_attr($critical_source) . '">' . $critical_css . '</style>' . "\n";
    }

    public function apply_style_loading_strategy(string $tag, string $handle, string $href, string $media): string
    {
        if (is_admin()) {
            return $tag;
        }

        $strategies = underscores_child_get_style_loading_strategies();
        $strategy = $strategies[$handle] ?? null;

        if (! $strategy) {
            return $tag;
        }

        $href = esc_url($href);
        $media = $media && $media !== 'all' ? $media : 'all';
        $id = esc_attr($handle . '-css');

        if ($strategy === 'media') {
            return sprintf(
                "<link rel='stylesheet' id='%s' href='%s' media='print' onload=\"this.media='%s'\" />\n<noscript><link rel='stylesheet' id='%s-noscript' href='%s' media='%s' /></noscript>\n",
                $id,
                $href,
                esc_attr($media),
                $id,
                $href,
                esc_attr($media)
            );
        }

        return sprintf(
            "<link rel='preload' id='%s' href='%s' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" />\n<noscript><link rel='stylesheet' id='%s-noscript' href='%s' media='%s' /></noscript>\n",
            $id,
            $href,
            $id,
            $href,
            esc_attr($media)
        );
    }
}
