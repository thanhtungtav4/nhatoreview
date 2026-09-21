<?php

declare(strict_types=1);

namespace Theme\Child\Hooks;

defined('ABSPATH') || exit;

/**
 * Child-owned common CSS/JS pipeline + child menu locations + tracking scripts (Theme Settings).
 *
 * Fires the parent extension points so context hooks still work:
 *   underscores_before_common_css / underscores_after_common_css
 *   underscores_before_common_js  / underscores_after_common_js
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
    }

    public function print_header_scripts(): void
    {
        $this->print_tracking_script('header_scripts');
    }

    public function print_footer_scripts(): void
    {
        $this->print_tracking_script('footer_scripts');
    }

    /**
     * In nguyên snippet admin dán vào. Đã lọc lúc lưu (LocalJson::sanitize_script_field)
     * + options page chỉ cho manage_options.
     */
    private function print_tracking_script(string $name): void
    {
        $scripts = underscores_get_option('scripts_section', []);
        $code    = trim((string) ($scripts[$name] ?? ''));

        if ($code !== '') {
            echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin snippet, sanitized on save.
        }
    }

    /**
     * Danh sách link ở footer (vd cột "Liên kết nhanh") = WP menu, không phải ACF repeater.
     * Tiêu đề cột lấy từ tên menu: wp_get_nav_menu_name('footer-menu').
     */
    public function register_menus(): void
    {
        register_nav_menus([
            'footer-menu' => __('Footer Menu', 'underscores'),
        ]);
    }

    public function enqueue_common_css_assets(): void
    {
        wp_enqueue_style(
            'underscores-parent-style',
            UNDERSCORES_SITE_TEMPLATE_URL . '/css/style.css',
            [],
            underscores_child_template_asset_version('/css/style.css')
        );

        do_action('underscores_before_common_css');

        wp_enqueue_style(
            'underscores-common',
            UNDERSCORES_SITE_TEMPLATE_URL . '/css/backdoor.css',
            ['underscores-parent-style'],
            underscores_child_template_asset_version('/css/backdoor.css')
        );

        // style.css của child chỉ chứa header theme → KHÔNG enqueue (tốn 1 request vô ích).
        // CSS dự án viết vào assets/css/child-theme.css.
        wp_enqueue_style(
            'underscores-child-style',
            underscores_child_asset_uri('assets/css/child-theme.css'),
            ['underscores-common'],
            underscores_child_asset_version('assets/css/child-theme.css')
        );

        do_action('underscores_after_common_css');
    }

    public function enqueue_common_js_assets(): void
    {
        // Mọi script theme: in ở footer + `defer` qua API core (WP 6.3+). Core tự hạ về blocking
        // nếu một dependent không defer được — không tự chèn attribute bằng script_loader_tag.
        $defer = ['in_footer' => true, 'strategy' => 'defer'];

        /**
         * Thư viện từ build /template: handle => [path, deps].
         * Dự án KHÔNG dùng thư viện nào thì bỏ qua filter (mỗi file thừa = 1 request + parse JS):
         *   add_filter('underscores_child_common_libraries', fn($libs) => array_diff_key($libs, array_flip(['select2', 'splitting'])));
         */
        $libraries = (array) apply_filters('underscores_child_common_libraries', [
            'swiper-bundle' => ['/assets/library/swiper/swiper-bundle.min.js', []],
            'aos'           => ['/assets/library/aos/aos.js', []],
            'select2'       => ['/assets/library/select2/select2.min.js', ['jquery']],
            'fancybox'      => ['/assets/library/fancybox/fancybox.umd.js', []],
            'smoothscroll'  => ['/assets/library/smoothscroll/SmoothScroll.min.js', []],
            'gsap'          => ['/assets/library/gsap/gsap.min.js', []],
            'scrolltrigger' => ['/assets/library/ScrollTrigger/ScrollTrigger.min.js', ['gsap']],
            'splitting'     => ['/assets/library/splitting/splitting.min.js', []],
        ]);

        foreach ($libraries as $handle => [$path, $deps]) {
            wp_enqueue_script(
                $handle,
                UNDERSCORES_SITE_TEMPLATE_URL . $path,
                $deps,
                underscores_child_template_asset_version($path),
                $defer
            );
        }

        do_action('underscores_before_common_js');

        wp_enqueue_script(
            'underscores-main',
            UNDERSCORES_SITE_TEMPLATE_URL . '/js/main.js',
            array_merge(['jquery'], array_keys($libraries)),
            underscores_child_template_asset_version('/js/main.js'),
            $defer
        );

        wp_enqueue_script(
            'underscores-frontend',
            UNDERSCORES_THEME_PATH_URI . '/assets/scripts/underscores-frontend.js',
            ['underscores-main'],
            UNDERSCORES_THEME_VERSION,
            $defer
        );

        wp_enqueue_script(
            'underscores-child-script',
            underscores_child_asset_uri('assets/scripts/child-theme.js'),
            ['underscores-frontend'],
            underscores_child_asset_version('assets/scripts/child-theme.js'),
            $defer
        );

        do_action('underscores_after_common_js');
    }
}
