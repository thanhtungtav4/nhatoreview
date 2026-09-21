<?php

declare(strict_types=1);

namespace Theme\Hooks;

defined('ABSPATH') || exit;

/**
 * Core theme setup, asset pipeline and MIME/header tweaks.
 *
 * Fires the cross-theme extension points:
 *   underscores_before_common_css / underscores_after_common_css
 *   underscores_before_common_js  / underscores_after_common_js
 */
final class CommonHook
{
    public static function register(): void
    {
        $self = new self();

        add_action('after_setup_theme', [$self, 'register_menus']);
        add_filter('admin_url', [$self, 'tag_ajax_admin_url'], 999, 3);
        add_action('wp_enqueue_scripts', [$self, 'jquery_alias'], 1);
        add_action('wp_enqueue_scripts', [$self, 'enqueue_common_css'], 10);
        add_action('wp_enqueue_scripts', [$self, 'enqueue_common_js'], 10);
        add_action('underscores_after_common_js', [$self, 'localize_frontend_params'], 10);
        add_filter('script_loader_tag', [$self, 'script_to_module'], 10, 2);
        add_filter('wp_resource_hints', [$self, 'preconnect_fonts'], 10, 2);

        if (function_exists('underscores_optimize_custom_logo_attrs')) {
            add_filter('get_custom_logo_image_attributes', 'underscores_optimize_custom_logo_attrs');
        }
    }

    public function register_menus(): void
    {
        register_nav_menus([
            'top-header-menu' => __('Top Header Menu', 'underscores'),
            'header-menu'     => __('Header Menu', 'underscores'),
        ]);

        add_theme_support('woocommerce');
    }

    public function tag_ajax_admin_url($url, $path, $blog_id)
    {
        if ($path === 'admin-ajax.php' && ! is_admin()) {
            $url .= '?underscores-ajax';
        }

        return $url;
    }

    public function jquery_alias(): void
    {
        if (! wp_script_is('jquery', 'registered')) {
            return;
        }

        wp_add_inline_script('jquery', 'var $ = jQuery;', 'before');
    }

    public function enqueue_common_css(): void
    {
        if (is_404()) {
            wp_enqueue_style('underscores-404', UNDERSCORES_THEME_PATH_URI . '/assets/css/404.css', [], UNDERSCORES_THEME_VERSION);
        }

        if (is_child_theme()) {
            return;
        }

        if (! apply_filters('underscores_enable_parent_common_css', true)) {
            return;
        }

        // Font / CSS riêng dự án đặt ở child hoặc build /template, không hardcode trong parent core.
        // style.css của theme chỉ chứa header → không enqueue.
        do_action('underscores_before_common_css');

        wp_enqueue_style('underscores-common', UNDERSCORES_SITE_TEMPLATE_URL . '/assets/css/common.css', [], UNDERSCORES_THEME_VERSION);

        do_action('underscores_after_common_css');
    }

    public function enqueue_common_js(): void
    {
        if (is_child_theme()) {
            return;
        }

        if (! apply_filters('underscores_enable_parent_common_js', true)) {
            return;
        }

        do_action('underscores_before_common_js');
        do_action('underscores_after_common_js');
    }

    public function localize_frontend_params(): void
    {
        if (! wp_script_is('underscores-frontend', 'enqueued') && ! wp_script_is('underscores-frontend', 'registered')) {
            return;
        }

        $params = apply_filters('underscores_ajax_params', [
            'siteURL'   => get_site_url(),
            'ajaxURL'   => admin_url('admin-ajax.php'),
            'ajaxNonce' => wp_create_nonce('underscores-ajax-security'),
        ]);

        wp_localize_script('underscores-frontend', 'underscores_params', $params);
    }

    /**
     * Gắn type="module" CHỈ vào thẻ <script src> của handle (nhận diện qua id="{handle}-js").
     * $tag của core còn chứa inline script before/after — đổi cả chúng sang module sẽ đổi scope + thời điểm chạy.
     */
    public function script_to_module($tag, $handle)
    {
        $handlers = apply_filters('underscores_script_to_module', [
            'underscores-main',
            'underscores-frontend',
        ]);

        if (! in_array($handle, $handlers, true)) {
            return $tag;
        }

        $id = preg_quote($handle . '-js', '/');

        // Mở thẻ <script ...> có đúng id → bỏ type cũ (vd text/javascript khi thiếu html5 support) rồi gắn module.
        return (string) preg_replace_callback(
            '/<script\b[^>]*\bid=(["\'])' . $id . '\1[^>]*>/',
            static fn(array $m): string => preg_replace(
                '/^<script/',
                '<script type="module"',
                (string) preg_replace('/\s+type=(["\'])[^"\']*\1/', '', $m[0])
            ),
            $tag,
            1
        );
    }

    /**
     * Preconnect Google Fonts CHỈ khi có stylesheet fonts.googleapis.com được enqueue
     * (preconnect thừa = tốn kết nối). Font @import trong CSS build → bật bằng filter
     * `underscores_preconnect_google_fonts`.
     *
     * AVIF/WebP: WP core hỗ trợ upload sẵn (WebP 5.8+, AVIF 6.5+) — không tự thêm mime.
     *
     * @param array<int, string|array<string, string>> $urls
     * @return array<int, string|array<string, string>>
     */
    public function preconnect_fonts(array $urls, string $relation_type): array
    {
        if ($relation_type !== 'preconnect') {
            return $urls;
        }

        $uses_google_fonts = false;
        $styles = wp_styles();

        foreach ($styles->queue as $handle) {
            $src = $styles->registered[$handle]->src ?? '';
            if (is_string($src) && str_contains($src, 'fonts.googleapis.com')) {
                $uses_google_fonts = true;
                break;
            }
        }

        if (! apply_filters('underscores_preconnect_google_fonts', $uses_google_fonts)) {
            return $urls;
        }

        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];

        return $urls;
    }
}
