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

        // Google Fonts trực tiếp qua <link> (không @import trong nhato.css): browser preload
        // scanner thấy ngay từ HTML đầu, và CommonHook::preconnect_fonts() (kiểm tra
        // wp_styles()->queue theo src chứa fonts.googleapis.com) mới bắt được để in
        // rel=preconnect cho fonts.googleapis.com + fonts.gstatic.com — @import giấu URL này
        // bên trong nội dung CSS nên trước đây filter đó luôn no-op.
        // Chỉ 4 family đang có component thật dùng qua --font-display/--font-body/
        // --font-editorial/--font-ui. Token --font-alt (Montserrat) / --font-alt-2 (Plus Jakarta
        // Sans) trong tokens/typography.css chưa có component nào áp dụng — thêm lại 2 family
        // này vào URL bên dưới ngay khi có component thật dùng var(--font-alt[-2]).
        wp_enqueue_style(
            'nhato-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=Manrope:wght@200..800&family=Ibarra+Real+Nova:ital,wght@0,400..700;1,400..700&family=Inter:wght@100..900&display=swap',
            [],
            null
        );

        // Partial trước đây @import trong nhato.css → enqueue riêng, deps nối chuỗi để giữ đúng
        // thứ tự cascade (token trước, component sau) mà vẫn để browser tải song song qua
        // HTTP/2 ngay từ HTML thay vì phải tải+parse nhato.css xong mới biết có các file này.
        $css_base = UNDERSCORES_SITE_TEMPLATE_URL . '/assets/css/';
        $partials = [
            'nhato-tokens-colors'     => 'tokens/colors.css',
            'nhato-tokens-typography' => 'tokens/typography.css',
            'nhato-tokens-layout'     => 'tokens/layout.css',
            'nhato-tokens-figma'      => 'tokens/figma-variables.css',
            'nhato-tokens-base'       => 'tokens/base.css',
            'nhato-chrome'            => 'chrome.css',
            'nhato-search'            => 'search.css',
            'nhato-actions'           => 'actions.css',
            'nhato-content'           => 'content.css',
            'nhato-home-sections'     => 'home-sections.css',
            'nhato-forms'             => 'forms.css',
            'nhato-media'             => 'media.css',
            'nhato-pages'             => 'pages.css',
            'nhato-utilities'         => 'utilities.css',
            'nhato-mobile'            => 'mobile.css',
        ];

        $previous_handle = 'nhato-fonts';
        foreach ($partials as $handle => $relative_path) {
            wp_enqueue_style(
                $handle,
                $css_base . $relative_path,
                [$previous_handle],
                underscores_child_template_asset_version('/assets/css/' . $relative_path)
            );
            $previous_handle = $handle;
        }

        // Không có trang nào cần 2 file này cho first paint:
        // - search.css: chỉ style .nh-search (display:none mặc định, JS bật .is-open khi bấm nút
        //   tìm kiếm trong header) — xem comment đầu file search.css.
        // - forms.css: chỉ style .nh-newsletter, dùng đúng 1 chỗ là footer.php (site-wide, luôn
        //   dưới fold). .nh-field/.nh-label khai báo nhưng chưa component nào dùng.
        // Dùng cơ chế media-swap có sẵn (PerformanceHook::apply_style_loading_strategy):
        // tải với media=print (không chặn render) rồi JS-less onload đổi thành media=all,
        // kèm <noscript> fallback cho trình duyệt tắt JS.
        underscores_child_mark_style_loading_strategy('nhato-search', 'media');
        underscores_child_mark_style_loading_strategy('nhato-forms', 'media');

        // Per-template defer: Playwright rendered mỗi trang ở 2 viewport (390×844, 1440×900),
        // với mỗi file kiểm tra "có selector nào khớp element nằm trong fold không" (không tách
        // rule bên trong file — an toàn hơn, tránh vỡ thứ tự cascade/reconstruct @media sai).
        // File không khớp ở CẢ 2 viewport trên template đó → defer qua media-swap.
        // tokens/chrome/mobile KHÔNG BAO GIỜ nằm trong danh sách này: mobile.css có rule
        // `.nh-header__call{display:none}` ẩn số điện thoại trong header ở mobile — element này
        // mặc định HIỂN THỊ (chrome.css), heuristic rect-based không bắt được "file cần để ẨN
        // thứ đang hiện" (rect đã =0 ở trạng thái cuối) — defer sai sẽ làm header vỡ layout
        // thoáng qua lúc gap. Xem chi tiết & script phân tích trong commit message.
        $template_defer_map = [
            'front-page' => ['nhato-home-sections', 'nhato-pages', 'nhato-utilities'],
            'about'      => ['nhato-actions', 'nhato-home-sections', 'nhato-utilities'],
            'art'        => ['nhato-actions', 'nhato-home-sections', 'nhato-utilities'],
            'network'    => ['nhato-actions', 'nhato-home-sections'],
            'original'   => ['nhato-actions', 'nhato-home-sections'],
            'space'      => ['nhato-actions', 'nhato-home-sections', 'nhato-utilities'],
            'taste'      => ['nhato-actions', 'nhato-home-sections', 'nhato-utilities'],
            'contact'    => ['nhato-actions', 'nhato-content', 'nhato-home-sections', 'nhato-utilities'],
        ];
        $current_template_slug = underscores_child_get_current_template_slug();
        foreach ($template_defer_map[$current_template_slug] ?? [] as $deferred_handle) {
            underscores_child_mark_style_loading_strategy($deferred_handle, 'media');
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
        $defer = ['in_footer' => true, 'strategy' => 'defer'];

        do_action('underscores_before_common_js');

        wp_enqueue_script(
            'nhato-icons',
            UNDERSCORES_SITE_TEMPLATE_URL . '/assets/icons/nhato-icons.js',
            [],
            underscores_child_template_asset_version('/assets/icons/nhato-icons.js'),
            $defer
        );

        wp_enqueue_script(
            'nhato-script',
            UNDERSCORES_SITE_TEMPLATE_URL . '/assets/js/nhato.js',
            ['nhato-icons'],
            underscores_child_template_asset_version('/assets/js/nhato.js'),
            $defer
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
