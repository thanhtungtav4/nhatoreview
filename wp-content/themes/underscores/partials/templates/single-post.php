<?php
/**
 * This is template for single post
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

$post_id = get_the_ID();
$post_type = get_post_type();

$primary_term = underscores_get_primary_term($post_id);

get_header();
?>

<?php
get_footer();