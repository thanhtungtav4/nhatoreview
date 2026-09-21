<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Render default pagination
 * 
 * @param WP_Query $wp_query
 * @param bool $echo
 * 
 * @return string
 */
if ( ! function_exists( 'underscores_pagination_links' ) ) {
    function underscores_pagination_links( $wp_query = null, $echo = false ) {
        if ( empty( $wp_query ) ) {
            global $wp_query;
        }

        if ( $wp_query->max_num_pages <= 1 ) {
            return;
        }

        $bignum = 999999999;

        // Output
        $output = '';
        $output .= '<div class="paginations">';
        $output .= paginate_links( [
            'base'      => str_replace( $bignum, '%#%', esc_url( get_pagenum_link( $bignum ) ) ),
            'format'    => '',
            // Trang hiện tại lấy từ chính query truyền vào (đúng cả trong AJAX, nơi query var chính = 0).
            'current'   => max( 1, (int) $wp_query->get( 'paged' ) ),
            'total'     => $wp_query->max_num_pages,
            'prev_text' => '<img src="' . esc_url( UNDERSCORES_SITE_TEMPLATE_URL . '/assets/images/icons/chevron-right.svg' ) . '" alt="' . esc_attr__( 'Trang trước', 'underscores' ) . '" loading="lazy" />',
            'next_text' => '<img src="' . esc_url( UNDERSCORES_SITE_TEMPLATE_URL . '/assets/images/icons/chevron-right.svg' ) . '" alt="' . esc_attr__( 'Trang sau', 'underscores' ) . '" loading="lazy" />',
            'type'      => 'list',
            'end_size'  => 2,
            'mid_size'  => 3
        ] );
        $output .= '</div>';

        if ( $echo ) {
            echo $output;
        }
        else {
            return $output;
        }
    }
}

