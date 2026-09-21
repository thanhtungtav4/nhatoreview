<?php

declare(strict_types=1);

namespace Theme\Nav;

defined('ABSPATH') || exit;

use Walker_Nav_Menu;

/**
 * Nav walker khớp markup template: <ul class="menu-list"> > <li class="menu-item [dropdown]"> > <a class="menu-link">.
 * Dùng với wp_nav_menu(['walker' => new \Theme\Nav\MenuWalker, 'menu_class' => 'menu-list', 'container' => false]).
 */
final class MenuWalker extends Walker_Nav_Menu
{
    /** @param string $output @param int $depth @param array $args */
    public function start_lvl(&$output, $depth = 0, $args = null): void
    {
        $output .= '<ul class="menu-list">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null): void
    {
        $output .= '</ul>';
    }

    /**
     * @param string $output
     * @param \WP_Post $item
     * @param int $depth
     * @param array $args
     * @param int $id
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0): void
    {
        $item_classes = array_filter((array) $item->classes);
        $classes      = ['menu-item'];

        if (in_array('menu-item-has-children', $item_classes, true)) {
            $classes[] = 'dropdown';
        }

        foreach ($item_classes as $class) {
            // Giữ trạng thái current/ancestor + class admin tự nhập (CSS Classes); bỏ class nội bộ menu-item-*.
            if (in_array($class, ['current-menu-item', 'current-menu-parent', 'current-menu-ancestor'], true)
                || ! preg_match('/^(menu-item|page[_-]item|current[_-])/', $class)) {
                $classes[] = $class;
            }
        }

        $atts = [
            'class'        => 'menu-link',
            'href'         => $item->url ?: '#',
            'title'        => $item->attr_title ?: '',
            'target'       => $item->target ?: '',
            'rel'          => $item->xfn ?: ($item->target === '_blank' ? 'noopener' : ''),
            'aria-current' => $item->current ? 'page' : '',
        ];

        // Giữ tương thích plugin (vd SEO, tracking) đang filter thuộc tính link của core.
        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        $attributes = '';
        foreach ($atts as $name => $value) {
            if ($value === '' || $value === null || $value === false) {
                continue;
            }
            $value       = $name === 'href' ? esc_url($value) : esc_attr((string) $value);
            $attributes .= ' ' . $name . '="' . $value . '"';
        }

        $title = apply_filters('nav_menu_item_title', apply_filters('the_title', $item->title, $item->ID), $item, $args, $depth);

        $output .= sprintf(
            '<li class="%s"><a%s>%s</a>',
            esc_attr(implode(' ', array_unique(array_map('sanitize_html_class', $classes)))),
            $attributes,
            esc_html($title)
        );
    }

    public function end_el(&$output, $item, $depth = 0, $args = null): void
    {
        $output .= '</li>';
    }
}
