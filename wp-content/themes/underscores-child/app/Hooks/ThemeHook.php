<?php

declare(strict_types=1);

namespace Theme\Child\Hooks;

defined('ABSPATH') || exit;

/**
 * Child-owned NHATO CSS/JS pipeline, menus and Theme Settings snippets.
 */
final class ThemeHook
{
    public static function register(): void
    {
        $self = new self();

        add_action('after_setup_theme', [$self, 'register_menus']);
        add_action('wp_enqueue_scripts', [$self, 'enqueue_common_css_assets'], 10);
        add_action('wp_enqueue_scripts', [$self, 'enqueue_common_js_assets'], 10);
        add_action('wp_head', [$self, 'print_header_scripts'], 100);
        add_action('wp_footer', [$self, 'print_footer_scripts'], 100);
        add_filter('nav_menu_css_class', [$self, 'add_header_item_class'], 10, 4);
        add_filter('nav_menu_link_attributes', [$self, 'add_header_link_attributes'], 10, 4);
    }

    public function print_header_scripts(): void
    {
        $this->print_tracking_script('header_scripts');
    }

    public function print_footer_scripts(): void
    {
        $this->print_tracking_script('footer_scripts');
    }

    private function print_tracking_script(string $name): void
    {
        $scripts = underscores_get_option('scripts_section', []);
        $code    = trim((string) ($scripts[$name] ?? ''));

        if ($code !== '') {
            echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin snippet, sanitized on save.
        }
    }

    public function register_menus(): void
    {
        register_nav_menus([
            'footer-explore-menu' => __('Footer — Khám phá', 'underscores'),
            'footer-support-menu' => __('Footer — Hỗ trợ', 'underscores'),
            'footer-menu'         => __('Footer Menu (legacy)', 'underscores'),
        ]);
    }

    public function enqueue_common_css_assets(): void
    {
        do_action('underscores_before_common_css');

        // Google Fonts trực tiếp qua <link> (không @import): CommonHook::preconnect_fonts()
        // (kiểm tra wp_styles()->queue theo src chứa fonts.googleapis.com) in được
        // rel=preconnect cho fonts.googleapis.com + fonts.gstatic.com. Chỉ 4 family có
        // component thật dùng qua --font-display/--font-body/--font-editorial/--font-ui.
        // Token --font-alt (Montserrat) / --font-alt-2 (Plus Jakarta Sans) trong
        // tokens/typography.css chưa có component nào áp dụng — thêm lại 2 family này vào
        // URL bên dưới ngay khi có component thật dùng var(--font-alt[-2]).
        wp_enqueue_style(
            'nhato-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=Manrope:wght@200..800&family=Ibarra+Real+Nova:ital,wght@0,400..700;1,400..700&family=Inter:wght@100..900&display=swap',
            [],
            null
        );

        // 14 partial trước đây enqueue riêng (parallel discovery, đúng, nhưng 14 request) →
        // gộp thành bundle sinh bởi scripts/build-asset-bundles.sh (chạy lại script đó sau khi
        // sửa bất kỳ file .css nào nó đọc). Vẫn giữ nguyên chiến lược 2 tầng: bundle luôn-chặn
        // (core) + bundle theo-template (blocking-{a,b,c,d,default}, xem phân tích Playwright
        // ở commit trước) + bundle luôn-defer (deferred-base) + bundle theo-template defer.
        // mobile.css nằm trong core (không bao giờ defer — xem lý do trong build script).
        $css_base = UNDERSCORES_SITE_TEMPLATE_URL . '/assets/css/bundles/';

        wp_enqueue_style(
            'nhato-core',
            $css_base . 'core.css',
            ['nhato-fonts'],
            underscores_child_template_asset_version('/assets/css/bundles/core.css')
        );

        $template_groups = [
            'front-page' => 'c',
            'about'      => 'a',
            'art'        => 'a',
            'network'    => 'b',
            'original'   => 'b',
            'space'      => 'a',
            'taste'      => 'a',
            'contact'    => 'd',
        ];
        $group = $template_groups[underscores_child_get_current_template_slug() ?? ''] ?? 'default';

        wp_enqueue_style(
            'nhato-blocking',
            $css_base . 'blocking-' . $group . '.css',
            ['nhato-core'],
            underscores_child_template_asset_version('/assets/css/bundles/blocking-' . $group . '.css')
        );

        // search.css/forms.css (không trang nào cần cho first paint — xem comment gốc 2 file)
        // + phần deferred riêng theo template, gộp chung 1 request bằng preload-swap.
        // 'preload' (rel=preload as=style, không phải 'media' = media=print) vì Chrome đo được
        // media=print bị xếp initialPriority=VeryLow (thấp nhất) — preload as=style báo đúng
        // "sắp dùng làm stylesheet" nên scheduler ưu tiên cao hơn, tải xong sớm hơn thật sự.
        wp_enqueue_style(
            'nhato-deferred-base',
            $css_base . 'deferred-base.css',
            ['nhato-blocking'],
            underscores_child_template_asset_version('/assets/css/bundles/deferred-base.css')
        );
        underscores_child_mark_style_loading_strategy('nhato-deferred-base', 'preload');

        $previous_handle = 'nhato-deferred-base';
        if ($group !== 'default') {
            wp_enqueue_style(
                'nhato-deferred',
                $css_base . 'deferred-' . $group . '.css',
                ['nhato-deferred-base'],
                underscores_child_template_asset_version('/assets/css/bundles/deferred-' . $group . '.css')
            );
            underscores_child_mark_style_loading_strategy('nhato-deferred', 'preload');
            $previous_handle = 'nhato-deferred';
        }

        wp_enqueue_style(
            'underscores-child-style',
            underscores_child_asset_uri('assets/css/child-theme.css'),
            [$previous_handle],
            underscores_child_asset_version('assets/css/child-theme.css')
        );

        do_action('underscores_after_common_css');
    }

    public function enqueue_common_js_assets(): void
    {
        do_action('underscores_before_common_js');

        // nhato-icons.js + nhato.js gộp thành 1 file (scripts/build-asset-bundles.sh) — cả 2
        // là IIFE độc lập `(function(){...})()`, nối theo đúng thứ tự cũ (icons trước, hành vi
        // UI sau) nên giữ nguyên semantics, chỉ còn 1 request thay vì 2.
        wp_enqueue_script(
            'nhato-script',
            UNDERSCORES_SITE_TEMPLATE_URL . '/assets/js/bundle.js',
            [],
            underscores_child_template_asset_version('/assets/js/bundle.js'),
            ['in_footer' => true, 'strategy' => 'defer']
        );

        do_action('underscores_after_common_js');
    }

    /**
     * The NHATO stylesheet expects a dedicated link class and body page key.
     * The menu remains managed by WordPress; these attributes only adapt output
     * to the existing visual system.
     *
     * @param array<int,string> $classes
     * @param \WP_Post          $item
     * @param object             $args
     * @param int                $depth
     * @return array<int,string>
     */
    public function add_header_item_class(array $classes, $item, $args, int $depth): array
    {
        if (($args->nhato_context ?? '') !== 'header') {
            return $classes;
        }

        $classes[] = 'nh-header__item';

        return array_values(array_unique($classes));
    }

    /**
     * @param array<string,string> $atts
     * @param \WP_Post              $item
     * @param object                $args
     * @param int                   $depth
     * @return array<string,string>
     */
    public function add_header_link_attributes(array $atts, $item, $args, int $depth): array
    {
        if (($args->nhato_context ?? '') !== 'header') {
            return $atts;
        }

        $classes = preg_split('/\s+/', trim((string) ($atts['class'] ?? '')));
        $classes = array_values(array_filter(array_unique(array_merge($classes ?: [], ['nh-header__link']))));

        $atts['class']        = implode(' ', $classes);
        $atts['data-nav-link'] = '';
        $atts['data-page']    = $this->menu_page_key($item);

        return $atts;
    }

    private function menu_page_key($item): string
    {
        $url  = (string) ($item->url ?? '');
        $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return 'home';
        }

        return sanitize_title((string) basename($path));
    }
}
