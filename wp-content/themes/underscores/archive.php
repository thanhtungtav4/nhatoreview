<?php
/**
 * The template for archives page
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}
?>

<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; ?>

<?php get_footer(); ?>