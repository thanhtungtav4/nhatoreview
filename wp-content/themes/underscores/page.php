<?php
/**
 * The template for single post type is page
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

get_header();
?>
<?php the_content(); ?>
<?php
get_footer();