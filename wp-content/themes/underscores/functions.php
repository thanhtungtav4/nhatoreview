<?php
declare(strict_types=1);

/**
 * The template for displaying index.
 *
 * @package Underscores
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define theme information
define( 'UNDERSCORES_THEME_VERSION', '4.3.0' );
define( 'UNDERSCORES_THEME_PATH', get_template_directory() );
define( 'UNDERSCORES_THEME_PATH_URI', get_template_directory_uri() );
define( 'UNDERSCORES_THEME_APP_PATH', UNDERSCORES_THEME_PATH . '/app' );
define( 'UNDERSCORES_THEME_INCLUDES_PATH', UNDERSCORES_THEME_PATH . '/includes' );
define( 'UNDERSCORES_THEME_CONFIG_PATH', UNDERSCORES_THEME_PATH . '/includes/config' );
define( 'UNDERSCORES_SITE_URL', get_option( 'siteurl' ) );
define( 'UNDERSCORES_SITE_TEMPLATE_URL', UNDERSCORES_SITE_URL . '/template' );

// Define theme page
define( 'UNDERSCORES_PAGE_HOME', get_option( 'page_on_front', true ) );
define( 'UNDERSCORES_PAGE_BLOG', get_option( 'page_for_posts', true ) );
define( 'UNDERSCORES_CUSTOM_LOGO', get_theme_mod('custom_logo') );
define( 'UNDERSCORES_POSTS_PER_PAGE', get_option('posts_per_page', 6) );

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/bootstrap.php';
