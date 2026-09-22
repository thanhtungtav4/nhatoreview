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

        wp_enqueue_style(
            'nhato-style',
            UNDERSCORES_SITE_TEMPLATE_URL . '/assets/css/nhato.css',
            [],
            underscores_child_template_asset_version('/assets/css/nhato.css')
        );

        wp_enqueue_style(
            'underscores-child-style',
            underscores_child_asset_uri('assets/css/child-theme.css'),
            ['nhato-style'],
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
