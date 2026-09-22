<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
underscores_child_set_main_class('page-news');
get_header();

$post_id = (int) get_the_ID();
$post_fields = function_exists('get_fields') ? (get_fields($post_id) ?: []) : [];
$related_settings = is_array($post_fields['related_settings'] ?? null) ? $post_fields['related_settings'] : [];
$related_is_show = !array_key_exists('is_show', $related_settings) || (bool) $related_settings['is_show'];
$related_mode = (($related_settings['mode'] ?? 'auto') === 'manual') ? 'manual' : 'auto';
$related_selected_posts = is_array($related_settings['posts'] ?? null)
    ? array_values(array_filter(array_map('absint', $related_settings['posts'])))
    : [];

$primary_term = underscores_get_primary_term($post_id, 'category');
$primary_term_link = $primary_term instanceof WP_Term ? get_term_link($primary_term) : '';

if (is_wp_error($primary_term_link)) {
    $primary_term_link = '';
}

$featured_image_id = get_post_thumbnail_id($post_id);
$lead = trim((string) get_the_excerpt());
$toc_data = underscores_child_article_content_with_toc(get_the_content());
$toc_entries = is_array($toc_data['toc'] ?? null) ? $toc_data['toc'] : [];
// The helper returns article HTML with stable heading IDs.
?>
<div class="page-news__body">
    <div class="nh-container">
        <div class="article">
            <article class="article__main">
            <div class="article__top">
                <nav class="article__crumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'underscores'); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Trang chủ', 'underscores'); ?></a>
                    <?php if ($primary_term instanceof WP_Term && $primary_term_link !== '') : ?>
                        <a href="<?php echo esc_url($primary_term_link); ?>" aria-current="page"><?php echo esc_html($primary_term->name); ?></a>
                    <?php endif; ?>
                </nav>
                <span class="nh-dateline">
                    <svg aria-hidden="true" focusable="false"><use href="#nh-clock-sm"></use></svg>
                    <span><strong><?php esc_html_e('Ngày đăng:', 'underscores'); ?></strong> <?php echo esc_html(get_the_date('d/m/Y')); ?></span>
                </span>
            </div>

            <?php the_title('<h1 class="article__title">', '</h1>'); ?>

            <?php if ($lead !== '') : ?>
                <p class="article__lead"><?php echo wp_kses_post($lead); ?></p>
            <?php endif; ?>

            <?php if ($featured_image_id > 0) : ?>
                <?php echo wp_get_attachment_image($featured_image_id, 'full', false, ['class' => 'article__hero']); ?>
            <?php endif; ?>

            <div class="article__body">
                <?php if ($toc_entries !== []) : ?>
                    <div class="article__toc">
                        <p class="article__toc-head">
                            <strong><?php esc_html_e('Nội dung của bài viết', 'underscores'); ?></strong>
                            <button type="button" data-nh-toc-toggle><?php esc_html_e('[Ẩn]', 'underscores'); ?></button>
                        </p>
                        <ol data-nh-toc>
                            <?php foreach ($toc_entries as $toc_entry) : ?>
                                <?php
                                $toc_id = trim((string) ($toc_entry['id'] ?? ''));
                                $toc_title = trim((string) ($toc_entry['title'] ?? ''));

                                if ($toc_id === '' || $toc_title === '') {
                                    continue;
                                }
                                ?>
                                <li><a href="#<?php echo esc_attr($toc_id); ?>"><?php echo esc_html($toc_title); ?></a></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>

                <?php echo $toc_data['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- content is filtered by the article helper. ?>

                <?php
                $featured_post_ids = [];
                $featured_query_args = [
                    'post_type'              => 'post',
                    'post_status'            => 'publish',
                    'posts_per_page'         => 1,
                    'post__not_in'           => [$post_id],
                    'ignore_sticky_posts'    => true,
                    'no_found_rows'          => true,
                    'update_post_term_cache' => false,
                ];

                if ($primary_term instanceof WP_Term) {
                    $featured_query_args['category__in'] = [(int) $primary_term->term_id];
                }

                $featured_query = new WP_Query($featured_query_args);
                if ($featured_query->have_posts()) {
                    underscores_child_prime_thumbnail_cache($featured_query);
                }
                ?>
                <?php if ($featured_query->have_posts()) : ?>
                    <a class="nh-more-btn" href="#tin-lien-quan">
                        <?php esc_html_e('Đọc tiếp', 'underscores'); ?><svg aria-hidden="true" focusable="false"><use href="#nh-more-chevrons"></use></svg>
                    </a>
                <?php endif; ?>
                <?php if ($featured_query->have_posts()) : ?>
                    <?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
                        <?php
                        $featured_post_id = (int) get_the_ID();
                        $featured_post_ids[] = $featured_post_id;
                        $featured_thumbnail_id = get_post_thumbnail_id($featured_post_id);
                        $featured_excerpt = trim((string) get_the_excerpt());
                        ?>
                        <a class="article__featured" href="<?php the_permalink(); ?>">
                            <span class="article__featured-inner">
                                <?php if ($featured_thumbnail_id > 0) : ?>
                                    <?php echo wp_get_attachment_image($featured_thumbnail_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); ?>
                                <?php endif; ?>
                                <span class="article__featured-copy">
                                    <strong><?php the_title(); ?></strong>
                                    <?php if ($featured_excerpt !== '') : ?>
                                        <span><?php echo wp_kses_post($featured_excerpt); ?></span>
                                    <?php endif; ?>
                                </span>
                            </span>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
                <?php
                $related_query = null;
                if ($related_is_show) {
                    $related_query_args = [
                        'post_type'              => 'post',
                        'post_status'            => 'publish',
                        'posts_per_page'         => 6,
                        'post__not_in'           => array_values(array_unique(array_merge([$post_id], $featured_post_ids))),
                        'ignore_sticky_posts'    => true,
                        'no_found_rows'          => true,
                        'update_post_term_cache' => false,
                    ];

                    if ($related_mode === 'manual') {
                        $related_query_args['post__in'] = $related_selected_posts !== [] ? $related_selected_posts : [0];
                        $related_query_args['orderby'] = 'post__in';
                    } elseif ($primary_term instanceof WP_Term) {
                        $related_query_args['category__in'] = [(int) $primary_term->term_id];
                    }

                    $related_query = new WP_Query($related_query_args);
                    if ($related_mode === 'auto' && !$related_query->have_posts() && $primary_term instanceof WP_Term) {
                        unset($related_query_args['category__in']);
                        $related_query = new WP_Query($related_query_args);
                    }
                }
                ?>

                <?php if ($related_query instanceof WP_Query && $related_query->have_posts()) : ?>
                    <?php underscores_child_prime_thumbnail_cache($related_query); ?>
                    <hr class="nh-hairline">
                    <div id="tin-lien-quan" class="u-flex-col u-gap-24 u-mt-16">
                        <div class="article__related-head">
                            <h2><?php esc_html_e('Tin liên quan', 'underscores'); ?></h2>
                            <?php if ($primary_term_link !== '') : ?>
                                <a class="article__readmore" href="<?php echo esc_url($primary_term_link); ?>">
                                    <?php esc_html_e('Xem tất cả', 'underscores'); ?><svg aria-hidden="true" focusable="false"><use href="#nh-chevron-right"></use></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="article__related-grid">
                            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                <?php
                                $related_post_id = (int) get_the_ID();
                                $related_permalink = get_permalink($related_post_id);
                                $related_title = trim((string) get_the_title($related_post_id));
                                $related_thumbnail_id = get_post_thumbnail_id($related_post_id);
                                ?>
                                <?php if ($related_permalink !== '' && $related_title !== '' && $related_thumbnail_id > 0) : ?>
                                    <a class="nh-thumb-card u-w-full" href="<?php echo esc_url($related_permalink); ?>">
                                        <?php echo wp_get_attachment_image($related_thumbnail_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']); ?>
                                        <span class="nh-thumb-card__title"><?php echo esc_html($related_title); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </article>

        <?php
        $latest_query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 5,
            'post__not_in'           => [$post_id],
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        ]);
        ?>
        <?php if ($latest_query->have_posts()) : ?>
                <?php underscores_child_prime_thumbnail_cache($latest_query); ?>
            <aside class="aside">
                <?php while ($latest_query->have_posts()) : $latest_query->the_post(); ?>
                    <?php
                    $latest_post_id = (int) get_the_ID();
                    $latest_thumbnail_id = get_post_thumbnail_id($latest_post_id);
                    ?>
                    <a class="nh-article-compact" href="<?php the_permalink(); ?>">
                        <?php if ($latest_thumbnail_id > 0) : ?>
                            <?php echo wp_get_attachment_image($latest_thumbnail_id, 'thumbnail', false, ['class' => 'nh-article-compact__thumb', 'alt' => '', 'loading' => 'lazy']); ?>
                        <?php endif; ?>
                        <span class="nh-article-compact__body">
                            <span class="nh-article-compact__top">
                                <span class="nh-dateline">
                                    <svg aria-hidden="true" focusable="false"><use href="#nh-clock"></use></svg>
                                    <span><?php echo esc_html(get_the_date('d/m/Y')); ?></span>
                                </span>
                            </span>
                            <span class="nh-article-compact__title"><?php the_title(); ?></span>
                        </span>
                    </a>
                <?php endwhile; ?>
            </aside>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
