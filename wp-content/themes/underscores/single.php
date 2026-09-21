<?php
/**
 * The template for single page
 * 
 * @author Underscores
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

get_template_part( 'partials/templates/single', get_post_type() );