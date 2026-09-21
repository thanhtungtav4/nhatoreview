<?php
/**
 * The template for search page
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

$search = get_search_query();

get_header();
?>
<?php while ( have_posts() ) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; ?>
<?php
get_footer();