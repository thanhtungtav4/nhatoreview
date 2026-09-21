<?php
/**
 * This is default template for tags
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
   die;
}

$current_term = get_queried_object();
$taxonomy = $current_term->taxonomy;

get_header();
?>
<?php while ( have_posts() ) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; ?>
<?php
get_footer();
