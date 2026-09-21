<?php

/**
 * The template for displaying header.
 *
 * @package Underscores
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Menu dùng 2 nơi (desktop + mobile) → query/render 1 lần, in 2 lần.
$header_menu = wp_nav_menu([
    'theme_location' => 'header-menu',
    'container'      => false,
    'menu_class'     => 'menu-list',
    'fallback_cb'    => false,
    'echo'           => false,
    'items_wrap'     => '<ul class="%2$s">%3$s</ul>', // bỏ id: HTML in 2 lần sẽ trùng id.
    'walker'         => new \Theme\Nav\MenuWalker(),
]);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<?php // KHÔNG dùng wp_is_mobile() trong markup: page cache sẽ trả nhầm bản desktop/mobile — dùng CSS media query. ?>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php $main_class_attribute = get_main_class(); ?>
    <header class="hd">
      <div class="container">
        <div class="hd-wrap">
          <div class="hd-logo">
                <?php
                    the_custom_logo();

                    if (is_front_page()) {
                        echo '<h1 class="hide-sitename">';
                            echo esc_html(get_bloginfo('name'));
                        echo '</h1>';
                    }
                ?>
          </div>
          <div class="hd-nav">
            <div class="menu-nav">
              <?php echo $header_menu; // phpcs:ignore WordPress.Security.EscapeOutput -- core menu HTML. ?>
            </div>
          </div>
          <div class="hd-action">
            <div class="hd-search tgBtn" data-toggle="ip-search-header" data-no-scroll="true"><span class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span></div>
            <div class="hd-burger" id="hamburger"> 
              <div class="burger-wrap">
                <svg class="menu-svg" viewBox="0 0 100 100">
                    <path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path>
                    <path class="path-2" d="m 30,50 h 40"></path>
                    <path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mobile-overlay"></div>
      <div class="mobile">
        <div class="mobile-con">
          <div class="mobile-wr">
            <div class="mobile-nav">
              <div class="menu-nav">
                <?php echo $header_menu; // phpcs:ignore WordPress.Security.EscapeOutput -- core menu HTML. ?>
              </div>
            </div>
            <div class="mobile-contact">
              <?php get_template_part('partials/components/social-list'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="hd-search-box tgTarget" data-toggle-id="ip-search-header">
        <div class="close tgRmv"><?php esc_html_e('Đóng', 'underscores'); ?></div>
        <div class="container">
          <?php get_search_form(); ?>
        </div>
      </div>
    </header>
    <main <?php echo $main_class_attribute; ?>>
