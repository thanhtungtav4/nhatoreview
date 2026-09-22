<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

underscores_child_set_main_class('page-news page-news__body');

get_header();

$blog_posts = [];
if (have_posts()) {
    global $wp_query;
    underscores_child_prime_thumbnail_cache($wp_query);
    while (have_posts()) {
        the_post();
        $current_post = get_post();

        if ($current_post instanceof WP_Post) {
            $blog_posts[] = $current_post;
        }
    }
}
?>
<div class="nh-container nh-stack">
    <?php if ($blog_posts !== []) : ?>
        <h1 class="nh-headline"><?php esc_html_e('TIN MỚI NHẤT', 'underscores-child'); ?></h1>

        <?php $lead_post = $blog_posts[0]; ?>
        <div class="nh-lead-grid">
            <a class="nh-lead" href="<?php echo esc_url(get_permalink($lead_post)); ?>">
                <?php
                $lead_title = get_the_title($lead_post);
                $lead_image = get_the_post_thumbnail(
                    $lead_post,
                    'large',
                    [
                        'alt'           => $lead_title,
                        'loading'       => 'eager',
                        'fetchpriority' => 'high',
                    ]
                );
                ?>
                <?php if ($lead_image) : ?><span class="nh-lead__figure"><?php echo $lead_image; ?></span><?php endif; ?>
                <span class="nh-lead__body">
                    <span class="nh-lead__meta">
                        <span class="nh-dateline">
                            <svg aria-hidden="true"><use href="#nh-clock"></use></svg>
                            <span><strong><?php esc_html_e('Ngày đăng:', 'underscores-child'); ?></strong> <?php echo esc_html(get_the_date('d/m/Y', $lead_post)); ?></span>
                        </span>
                        <?php $lead_category = get_the_category($lead_post); ?>
                        <?php if (!empty($lead_category[0]->name)) : ?><span class="nh-chip"><?php echo esc_html($lead_category[0]->name); ?></span><?php endif; ?>
                    </span>
                    <?php if ($lead_title !== '') : ?><span class="nh-lead__title"><?php echo esc_html($lead_title); ?></span><?php endif; ?>
                    <?php $lead_excerpt = get_the_excerpt($lead_post); ?>
                    <?php if ($lead_excerpt !== '') : ?><span class="nh-lead__excerpt"><?php echo esc_html($lead_excerpt); ?></span><?php endif; ?>
                </span>
            </a>

            <?php $rail_posts = array_slice($blog_posts, 1, 5); ?>
            <?php if ($rail_posts !== []) : ?>
                <div class="nh-rail">
                    <?php foreach ($rail_posts as $rail_post) : ?>
                        <?php
                        $rail_title = get_the_title($rail_post);
                        $rail_image = get_the_post_thumbnail(
                            $rail_post,
                            'thumbnail',
                            ['class' => 'nh-article-compact__thumb', 'alt' => $rail_title, 'loading' => 'lazy']
                        );
                        if ($rail_title === '' || get_permalink($rail_post) === '') {
                            continue;
                        }
                        ?>
                        <a class="nh-article-compact" href="<?php echo esc_url(get_permalink($rail_post)); ?>">
                            <?php if ($rail_image) : ?><?php echo $rail_image; ?><?php endif; ?>
                            <span class="nh-article-compact__body">
                                <span class="nh-article-compact__top">
                                    <span class="nh-dateline">
                                        <svg aria-hidden="true"><use href="#nh-clock"></use></svg>
                                        <span><strong><?php esc_html_e('Ngày đăng:', 'underscores-child'); ?></strong> <?php echo esc_html(get_the_date('d/m/Y', $rail_post)); ?></span>
                                </span>
                                </span>
                                <span class="nh-article-compact__title"><?php echo esc_html($rail_title); ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php $featured_posts = array_slice($blog_posts, 5); ?>
        <?php if ($featured_posts !== []) : ?>
            <hr class="nh-hairline">
            <h2 class="nh-headline nh-headline--quiet"><?php esc_html_e('TIN NỔI BẬT', 'underscores-child'); ?></h2>
            <div class="nh-grid-4">
                <?php foreach ($featured_posts as $featured_post) : ?>
                    <?php get_template_part('partials/components/card-news', null, ['post_id' => (int) $featured_post->ID]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $pagination = paginate_links([
            'type'      => 'array',
            'mid_size'  => 2,
            'prev_text' => '__NHATO_PREV__',
            'next_text' => '__NHATO_NEXT__',
        ]);
        ?>
        <?php if (is_array($pagination) && $pagination !== []) : ?>
            <nav class="nh-pagination" aria-label="<?php esc_attr_e('Phân trang tin tức', 'underscores-child'); ?>">
                <?php foreach ($pagination as $pagination_item) : ?>
                    <?php
                    if (str_contains($pagination_item, 'aria-current="page"') && str_contains($pagination_item, 'page-numbers current')) {
                        $pagination_item = '<a class="is-current" href="' . esc_url(get_pagenum_link(max(1, (int) get_query_var('paged')))) . '" aria-current="page">' . esc_html(wp_strip_all_tags($pagination_item)) . '</a>';
                    }
                    $pagination_item = str_replace(
                        '<a class="prev page-numbers"',
                        '<a class="prev page-numbers" aria-label="' . esc_attr__('Trang trước', 'underscores-child') . '"',
                        $pagination_item
                    );
                    $pagination_item = str_replace(
                        '<a class="next page-numbers"',
                        '<a class="next page-numbers" aria-label="' . esc_attr__('Trang tiếp theo', 'underscores-child') . '"',
                        $pagination_item
                    );
                    $pagination_item = str_replace(
                        '__NHATO_PREV__',
                        '<svg aria-hidden="true" focusable="false"><use href="#nh-page-prev"></use></svg><span class="screen-reader-text">' . esc_html__('Trang trước', 'underscores-child') . '</span>',
                        $pagination_item
                    );
                    $pagination_item = str_replace(
                        '__NHATO_NEXT__',
                        '<svg aria-hidden="true" focusable="false"><use href="#nh-page-next"></use></svg><span class="screen-reader-text">' . esc_html__('Trang tiếp theo', 'underscores-child') . '</span>',
                        $pagination_item
                    );
                    echo wp_kses(
                        $pagination_item,
                        [
                            'a'    => ['class' => true, 'href' => true, 'aria-current' => true, 'aria-label' => true],
                            'span' => ['class' => true, 'aria-current' => true],
                            'svg'  => ['aria-hidden' => true, 'focusable' => true],
                            'use'  => ['href' => true],
                        ]
                    );
                    ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php // TODO: Add the featured videos section when the video data model is finalized. ?>
    <?php endif; ?>
</div>
<?php

wp_reset_postdata();
get_footer();
