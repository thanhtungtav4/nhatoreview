<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$current_term = get_queried_object();
$term_name    = $current_term instanceof WP_Term ? $current_term->name : trim((string) single_cat_title('', false));

underscores_child_set_main_class('page-news');
get_header();
?>
<div class="nh-container nh-stack">
    <h1 class="nh-headline"><?php echo esc_html($term_name); ?></h1>

    <?php if (have_posts()) : ?>
        <?php global $wp_query; underscores_child_prime_thumbnail_cache($wp_query); ?>
        <div class="nh-grid-4">
            <?php
            while (have_posts()) :
                the_post();
                get_template_part('partials/components/card-news', null, ['post_id' => get_the_ID()]);
            endwhile;
            ?>
        </div>

        <?php underscores_child_render_pagination(); ?>
    <?php else : ?>
        <p><?php esc_html_e('Chưa có bài viết nào trong danh mục này.', 'underscores-child'); ?></p>
    <?php endif; ?>
</div>
<?php
wp_reset_postdata();
get_footer();
