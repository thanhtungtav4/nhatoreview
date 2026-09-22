<?php

declare(strict_types=1);

namespace Theme\Child\Hooks;

defined('ABSPATH') || exit;

/**
 * NHATO editorial content models.
 *
 * Projects, events and portfolios are independent content types. Portfolio tabs
 * are taxonomy terms on one portfolio post type, not separate post types per tab.
 */
final class ContentTypesHook
{
    public static function register(): void
    {
        add_action('init', [new self(), 'register_content_types'], 5);
    }

    public function register_content_types(): void
    {
        register_post_type('project', [
            'labels' => [
                'name'               => __('Dự án', 'underscores'),
                'singular_name'      => __('Dự án', 'underscores'),
                'add_new'            => __('Thêm dự án', 'underscores'),
                'add_new_item'       => __('Thêm dự án mới', 'underscores'),
                'edit_item'          => __('Sửa dự án', 'underscores'),
                'new_item'           => __('Dự án mới', 'underscores'),
                'view_item'          => __('Xem dự án', 'underscores'),
                'search_items'       => __('Tìm dự án', 'underscores'),
                'not_found'          => __('Chưa có dự án.', 'underscores'),
                'menu_name'          => __('Dự án', 'underscores'),
            ],
            'public'             => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-building',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'has_archive'        => true,
            'rewrite'            => ['slug' => 'du-an', 'with_front' => false],
            'query_var'          => 'project',
            'menu_position'      => 21,
            'show_in_nav_menus'  => true,
        ]);

        register_post_type('event', [
            'labels' => [
                'name'               => __('Sự kiện', 'underscores'),
                'singular_name'      => __('Sự kiện', 'underscores'),
                'add_new'            => __('Thêm sự kiện', 'underscores'),
                'add_new_item'       => __('Thêm sự kiện mới', 'underscores'),
                'edit_item'          => __('Sửa sự kiện', 'underscores'),
                'new_item'           => __('Sự kiện mới', 'underscores'),
                'view_item'          => __('Xem sự kiện', 'underscores'),
                'search_items'       => __('Tìm sự kiện', 'underscores'),
                'not_found'          => __('Chưa có sự kiện.', 'underscores'),
                'menu_name'          => __('Sự kiện', 'underscores'),
            ],
            'public'             => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'has_archive'        => true,
            'rewrite'            => ['slug' => 'su-kien', 'with_front' => false],
            'query_var'          => 'event',
            'menu_position'      => 22,
            'show_in_nav_menus'  => true,
        ]);

        register_post_type('portfolio', [
            'labels' => [
                'name'               => __('Portfolio', 'underscores'),
                'singular_name'      => __('Portfolio', 'underscores'),
                'add_new'            => __('Thêm portfolio', 'underscores'),
                'add_new_item'       => __('Thêm portfolio mới', 'underscores'),
                'edit_item'          => __('Sửa portfolio', 'underscores'),
                'new_item'           => __('Portfolio mới', 'underscores'),
                'view_item'          => __('Xem portfolio', 'underscores'),
                'search_items'       => __('Tìm portfolio', 'underscores'),
                'not_found'          => __('Chưa có portfolio.', 'underscores'),
                'menu_name'          => __('Portfolio', 'underscores'),
            ],
            'public'             => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-format-gallery',
            'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
            'has_archive'        => true,
            'rewrite'            => ['slug' => 'portfolio', 'with_front' => false],
            'query_var'          => 'portfolio',
            'menu_position'      => 23,
            'show_in_nav_menus'  => true,
        ]);

        register_taxonomy('project_category', ['project'], [
            'labels' => [
                'name'          => __('Loại dự án', 'underscores'),
                'singular_name' => __('Loại dự án', 'underscores'),
                'search_items'  => __('Tìm loại dự án', 'underscores'),
                'all_items'     => __('Tất cả loại dự án', 'underscores'),
                'edit_item'     => __('Sửa loại dự án', 'underscores'),
                'add_new_item'  => __('Thêm loại dự án', 'underscores'),
                'menu_name'     => __('Loại dự án', 'underscores'),
            ],
            'public'            => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'loai-du-an', 'with_front' => false],
        ]);

        register_taxonomy('portfolio_category', ['portfolio'], [
            'labels' => [
                'name'          => __('Danh mục portfolio', 'underscores'),
                'singular_name' => __('Danh mục portfolio', 'underscores'),
                'search_items'  => __('Tìm danh mục portfolio', 'underscores'),
                'all_items'     => __('Tất cả danh mục portfolio', 'underscores'),
                'edit_item'     => __('Sửa danh mục portfolio', 'underscores'),
                'add_new_item'  => __('Thêm danh mục portfolio', 'underscores'),
                'menu_name'     => __('Danh mục portfolio', 'underscores'),
            ],
            'public'            => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'portfolio-category', 'with_front' => false],
        ]);
    }
}
