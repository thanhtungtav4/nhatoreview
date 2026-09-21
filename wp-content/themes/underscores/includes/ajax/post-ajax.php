<?php
defined('ABSPATH') || exit;

if (! function_exists('underscores_ajax_get_posts')) {
    /**
     * AJAX endpoint for post listing with optional taxonomy filters.
     *
     * Request params:
     * - posts_per_page (int, optional, clamped to 1..50)
     * - paged (int, optional, >= 1)
     * - taxonomies (array, optional): [taxonomy => [term_id, ...]]
     *
     * Response (chuẩn wp_send_json_success / wp_send_json_error):
     * - có bài: { success: true,  data: { posts: [...], pagination_html } }
     * - rỗng:   { success: true,  data: { posts: [], empty_message } }
     * - lỗi:    { success: false, data: { message } } (HTTP 403 / 500)
     */
    function underscores_ajax_get_posts() {
        try {
            if (! check_ajax_referer('underscores-ajax-security', 'security', false)) {
                wp_send_json_error(['message' => __('Hành động không được xác thực', 'underscores')], 403);
            }

            // Normalize pagination input to safe bounds.
            $posts_per_page = filter_input(INPUT_POST, 'posts_per_page', FILTER_VALIDATE_INT);
            if (! $posts_per_page) {
                $posts_per_page = UNDERSCORES_POSTS_PER_PAGE;
            }
            $posts_per_page = max(1, min(50, (int) $posts_per_page));

            $paged = filter_input(INPUT_POST, 'paged', FILTER_VALIDATE_INT);
            if (! $paged) {
                $paged = 1;
            }
            $paged = max(1, (int) $paged);

            $args = [
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => $posts_per_page,
                'paged'                  => $paged,
                'ignore_sticky_posts'    => true,
                'update_post_term_cache' => false, // response không dùng term.
                // Keep relation fixed so filters can be appended conditionally below.
                'tax_query'              => ['relation' => 'AND'],
                'orderby'                => ['date' => 'DESC'],
            ];

            /**
             * Input format
             *
             * [
             *     'category' => [1, 2],
             *     'post_tag' => [1, 2],
             * ]
             */
            $raw_taxonomies = isset($_POST['taxonomies']) ? wp_unslash($_POST['taxonomies']) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized per key/term below.
            if (! empty($raw_taxonomies) && is_array($raw_taxonomies)) {
                foreach ($raw_taxonomies as $taxonomy => $terms) {
                    if (! is_array($terms)) {
                        continue;
                    }

                    // Only allow valid, registered taxonomy keys.
                    $taxonomy = sanitize_key((string) $taxonomy);
                    if (empty($taxonomy) || ! taxonomy_exists($taxonomy)) {
                        continue;
                    }

                    // Terms are expected as integer term IDs.
                    $sanitized_terms = array_filter(array_map('absint', $terms));
                    if (empty($sanitized_terms)) {
                        continue;
                    }

                    $args['tax_query'][] = [
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $sanitized_terms,
                    ];
                }
            }

            // KHÔNG dùng 'fields' => 'ids': the_post() sẽ get_post() + meta từng bài (N+1 query).
            // Query đầy đủ → WP prime post + meta cache 1 lần.
            $query = new WP_Query($args);

            if (! $query->have_posts()) {
                wp_send_json_success([
                    'posts'         => [],
                    'empty_message' => __('Không có bài viết nào được tìm thấy', 'underscores'),
                ]);
            }

            update_post_thumbnail_cache($query);                                            // 1 query cho mọi thumbnail.
            cache_users(array_map('intval', wp_list_pluck($query->posts, 'post_author'))); // 1 query cho mọi tác giả.

            $posts = [];
            while ($query->have_posts()) {
                $query->the_post();

                $posts[] = [
                    'id'        => get_the_ID(),
                    'title'     => get_the_title(),
                    'thumbnail' => get_the_post_thumbnail(null, 'medium_large'),
                    'permalink' => get_permalink(),
                    'date'      => get_the_date('d/m/Y'),
                    'author'    => get_the_author(),
                ];
            }
            wp_reset_postdata();

            wp_send_json_success([
                'posts'           => $posts,
                // Frontend render pagination bằng server markup.
                'pagination_html' => (string) underscores_pagination_links($query),
            ]);
        } catch (\Throwable $th) {
            wp_send_json_error([
                'message' => __('Đã xảy ra sự cố', 'underscores'),
            ], 500);
        }
        wp_die();
    }
}
