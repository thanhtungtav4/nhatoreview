<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$logo_id = (int) get_theme_mod('custom_logo');
$contact = underscores_get_option('footer_contact_section', []);
$phone   = '';

foreach ((array) ($contact['items'] ?? []) as $item) {
    $link = is_array($item['link'] ?? null) ? $item['link'] : [];
    $url  = (string) ($link['url'] ?? '');

    if (str_starts_with($url, 'tel:')) {
        $phone = $url;
        break;
    }
}

$page_key = 'home';
if (is_home() || is_singular('post') || is_category() || is_tag() || is_search()) {
    $page_key = 'network';
} elseif (is_singular('project') || is_post_type_archive('project')) {
    $page_key = 'home';
} elseif (is_singular('event') || is_post_type_archive('event')) {
    $page_key = 'home';
} elseif (is_singular('portfolio') || is_post_type_archive('portfolio')) {
    $page_key = 'home';
} elseif (is_page()) {
    $template = get_page_template_slug();
    $page_key = match ($template) {
        'page-template/template-network.php'  => 'network',
        'page-template/template-art.php'      => 'art',
        'page-template/template-taste.php'    => 'taste',
        'page-template/template-original.php' => 'original',
        'page-template/template-space.php'    => 'home',
        default                              => is_front_page() ? 'home' : sanitize_title((string) get_post_field('post_name', get_queried_object_id())),
    };
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?php echo esc_url(UNDERSCORES_SITE_TEMPLATE_URL . '/assets/logo/favicon.svg'); ?>" type="image/svg+xml">
    <?php
    $meta_description = '';
    $meta_image        = '';
    if (is_singular()) {
        $meta_description = trim((string) get_the_excerpt());
        $meta_thumb_id     = (int) get_post_thumbnail_id();
        if ($meta_thumb_id > 0) {
            $meta_image = (string) wp_get_attachment_image_url($meta_thumb_id, 'large');
        }
    }
    if ($meta_description === '') {
        $meta_description = trim((string) get_bloginfo('description'));
    }
    if ($meta_image === '') {
        $meta_logo_id = (int) get_theme_mod('custom_logo');
        if ($meta_logo_id > 0) {
            $meta_image = (string) wp_get_attachment_image_url($meta_logo_id, 'large');
        }
    }
    $meta_title = wp_get_document_title();
    ?>
    <?php if ($meta_description !== '') : ?><meta name="description" content="<?php echo esc_attr($meta_description); ?>"><?php endif; ?>
    <meta property="og:type" content="<?php echo esc_attr(is_singular() && !is_front_page() ? 'article' : 'website'); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
    <meta property="og:title" content="<?php echo esc_attr($meta_title); ?>">
    <?php if ($meta_description !== '') : ?><meta property="og:description" content="<?php echo esc_attr($meta_description); ?>"><?php endif; ?>
    <meta property="og:url" content="<?php echo esc_url(is_singular() ? (string) get_permalink() : home_url(add_query_arg([], $_SERVER['REQUEST_URI'] ?? '/'))); ?>">
    <?php if ($meta_image !== '') : ?><meta property="og:image" content="<?php echo esc_url($meta_image); ?>"><?php endif; ?>
    <meta name="twitter:card" content="<?php echo esc_attr($meta_image !== '' ? 'summary_large_image' : 'summary'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr($page_key); ?>">
<?php wp_body_open(); ?>
<header class="nh-header" data-nh-header>
    <div class="nh-header__inner nh-container">
        <a class="nh-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> — Trang chủ">
            <span class="nh-brand__mark">
                <?php if ($logo_id > 0) : ?>
                    <?php echo wp_get_attachment_image($logo_id, 'full', false, ['alt' => get_bloginfo('name'), 'sizes' => '(max-width: 760px) 136px, 180px']); ?>
                <?php else : ?>
                    <span><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </span>
        </a>

        <nav id="nh-nav" class="nh-header__nav" data-nh-nav aria-label="<?php esc_attr_e('Điều hướng chính', 'underscores'); ?>">
            <ul class="nh-header__list">
                <li class="nh-header__item">
                    <button class="nh-header__menu" type="button" aria-label="<?php esc_attr_e('Mở danh mục', 'underscores'); ?>">
                        <?php echo underscores_child_icon_mask('icon_menu'); ?>
                    </button>
                </li>
                <?php
                wp_nav_menu([
                    'theme_location' => 'header-menu',
                    'container'      => false,
                    'menu_class'     => '',
                    'items_wrap'     => '%3$s',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                    'nhato_context'  => 'header',
                ]);
                ?>
            </ul>
        </nav>

        <div class="nh-header__utilities">
            <?php get_template_part('partials/components/social-list', null, ['class' => 'nh-social--header', 'limit' => 3]); ?>
            <?php if ($phone !== '') : ?>
                <a class="nh-header__call" href="<?php echo esc_url($phone); ?>">
                    <?php esc_html_e('Call us:', 'underscores'); ?> <?php echo esc_html(preg_replace('/^tel:/', '', $phone)); ?>
                </a>
            <?php endif; ?>
            <button class="nh-header__search" type="button" aria-label="<?php esc_attr_e('Tìm kiếm', 'underscores'); ?>">
                <?php echo underscores_child_icon_mask('icon_search'); ?>
            </button>
            <button class="nh-header__toggle" type="button" data-nh-menu-toggle aria-controls="nh-nav" aria-expanded="false" aria-label="<?php esc_attr_e('Mở menu', 'underscores'); ?>">
                <span></span><span></span>
            </button>
        </div>
    </div>
</header>
<main id="main-content" <?php echo get_main_class(); ?>>
