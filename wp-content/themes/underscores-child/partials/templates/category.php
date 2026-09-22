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

        <?php
        $pagination = paginate_links([
            'type'      => 'array',
            'mid_size'  => 2,
            'prev_text' => '__NHATO_PREV__',
            'next_text' => '__NHATO_NEXT__',
        ]);
        ?>
        <?php if (is_array($pagination) && $pagination !== []) : ?>
            <nav class="nh-pagination" aria-label="<?php esc_attr_e('Phân trang', 'underscores-child'); ?>">
                <?php foreach ($pagination as $pagination_item) : ?>
                    <?php
                    if (str_contains($pagination_item, 'aria-current="page"') && str_contains($pagination_item, 'page-numbers current')) {
                        $pagination_item = '<a class="is-current" href="' . esc_url(get_pagenum_link(max(1, (int) get_query_var('paged')))) . '" aria-current="page">' . esc_html(wp_strip_all_tags($pagination_item)) . '</a>';
                    }
                    $pagination_item = str_replace('__NHATO_PREV__', esc_html__('Trước', 'underscores-child'), $pagination_item);
                    $pagination_item = str_replace('__NHATO_NEXT__', esc_html__('Sau', 'underscores-child'), $pagination_item);
                    echo wp_kses_post($pagination_item);
                    ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    <?php else : ?>
        <p><?php esc_html_e('Chưa có bài viết nào trong danh mục này.', 'underscores-child'); ?></p>
    <?php endif; ?>
</div>
<?php
wp_reset_postdata();
get_footer();
