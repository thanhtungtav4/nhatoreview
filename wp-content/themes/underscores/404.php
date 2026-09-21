<?php 
/**
 * The template for displaying 404.
 *
 * @package Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <main>
        <div class="wrapper">
            <div class="underscores-notfound-template">
                <div class="notfound">
                    <div class="notfound-404">
                        <h3><?php echo esc_html__( 'Oops! Page not found', 'underscores' ); ?></h3>
                        <h1><span>4</span><span>0</span><span>4</span></h1>
                    </div>
                    <h2><?php echo esc_html__( 'we are sorry, but the page you requested was not found', 'underscores' ); ?></h2>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php echo esc_html__( 'Back to the home page', 'underscores' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
