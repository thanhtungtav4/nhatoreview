<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'intro' => is_array($page_fields['intro_settings'] ?? null) ? $page_fields['intro_settings'] : [],
    'related' => is_array($page_fields['related_settings'] ?? null) ? $page_fields['related_settings'] : [],
];
$section_enabled = static function (array $section): bool {
    return !array_key_exists('is_show', $section) || (bool) $section['is_show'];
};
$banner_settings = $_page_sections['banner'];
$intro_settings = $_page_sections['intro'];

$related_settings = $_page_sections['related'];

// Expected ACF sections: banner_settings, intro_settings, related_settings.
$image_url = static function ($image): string {
    $id = is_array($image) ? absint($image['ID'] ?? $image['id'] ?? 0) : absint($image);
    if ($id > 0) {
        return (string) wp_get_attachment_image_url($id, 'full');
    }

    return is_string($image) ? $image : '';
};
$related_posts = is_array($related_settings['posts'] ?? null) ? $related_settings['posts'] : [];
$selected_posts = array_values(array_filter(array_map('absint', $related_posts)));
$related_mode = (($related_settings['mode'] ?? 'auto') === 'manual') ? 'manual' : 'auto';
$related_query_args = [
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'ignore_sticky_posts' => true,
    'no_found_rows' => true,
    'update_post_term_cache' => false,
];
if ($related_mode === 'manual') {
    $related_query_args['post__in'] = $selected_posts !== [] ? $selected_posts : [0];
    $related_query_args['orderby'] = 'post__in';
}
$related_query = new WP_Query($related_query_args);

$render_related = static function (WP_Query $query): void {
    if (!$query->have_posts()) {
        return;
    }
    underscores_child_prime_thumbnail_cache($query);
    ?>
    <section class="section-posts">
        <div class="nh-container nh-stack">
            <div class="nh-rule-head">
                <h2 class="nh-rule-head__title"><?php esc_html_e('Bài viết khác', 'underscores-child'); ?></h2>
                <span class="nh-rule-head__line"></span>
                <a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>">
                    <span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span>
                    <?php echo underscores_child_icon_mask('icon_arrow_right'); ?>
                </a>
            </div>
            <div class="nh-grid-3">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php get_template_part('partials/components/card-post', null, ['post_id' => get_the_ID()]); ?>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
};

$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
$hero_lead  = (string) ($banner_settings['lead'] ?? '');
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '')) :
    ?>
    <section class="nh-hero nh-hero--section">
        <?php if ($hero_image !== '') : ?>
            <div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div>
        <?php endif; ?>
        <div class="nh-hero__overlay"></div>
        <div class="nh-hero__inner nh-container">
            <?php if (get_the_title() !== '') : ?><h1 class="nh-hero__title"><?php the_title(); ?></h1><?php endif; ?>
            <?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?>
        </div>
    </section>
    <?php
endif;

$intro_eyebrow = (string) ($intro_settings['eyebrow'] ?? '');
$intro_title   = (string) ($intro_settings['title'] ?? '');
$panels       = is_array($intro_settings['panels'] ?? null) ? $intro_settings['panels'] : [];
$quote        = (string) ($intro_settings['quote'] ?? '');
if ($section_enabled($intro_settings) && ($intro_eyebrow !== '' || $intro_title !== '' || $panels !== [] || $quote !== '')) :
    ?>
    <section class="section-intro">
        <div class="nh-container nh-stack">
            <?php if ($intro_eyebrow !== '' || $intro_title !== '') : ?>
                <div class="nh-intro u-gap-13">
                    <?php if ($intro_eyebrow !== '') : ?><p class="nh-intro__eyebrow u-gap-8"><?php echo esc_html($intro_eyebrow); ?></p><?php endif; ?>
                    <?php if ($intro_title !== '') : ?><h2 class="nh-rule-head__title u-text-center"><?php echo esc_html($intro_title); ?></h2><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($panels !== []) : ?>
                <div class="nh-grid-2">
                    <?php foreach ($panels as $index => $panel) :
                        if (!is_array($panel)) {
                            continue;
                        }
                        $src  = $image_url($panel['image'] ?? '');
                        $href = is_array($panel['link'] ?? null) ? (string) ($panel['link']['url'] ?? '') : (string) ($panel['url'] ?? '');
                        $class = 'nh-panel' . ($index % 2 === 1 ? ' nh-panel--dim' : '');
                        if ($href !== '') :
                            ?>
                            <a class="<?php echo esc_attr($class); ?>" href="<?php echo esc_url($href); ?>"<?php if ($src !== '') : ?> style="--nh-panel-image:url('<?php echo esc_url($src); ?>')"<?php endif; ?>>
                        <?php else : ?>
                            <div class="<?php echo esc_attr($class); ?>"<?php if ($src !== '') : ?> style="--nh-panel-image:url('<?php echo esc_url($src); ?>')"<?php endif; ?>>
                        <?php endif; ?>
                            <?php if (!empty($panel['number'])) : ?><span class="nh-panel__number"><?php echo esc_html((string) $panel['number']); ?></span><?php endif; ?>
                            <?php if (!empty($panel['title'])) : ?><span class="nh-panel__title"><?php echo esc_html((string) $panel['title']); ?></span><?php endif; ?>
                            <span class="nh-panel__rule"></span>
                            <?php if (!empty($panel['text'])) : ?><span class="nh-panel__text"><?php echo esc_html((string) $panel['text']); ?></span><?php endif; ?>
                            <?php if ($href !== '') : ?>
                                <span class="nh-cta"><span><?php esc_html_e('Khám phá', 'underscores-child'); ?></span><span class="nh-cta__arrow"><?php echo underscores_child_icon_mask('icon_arrow_right'); ?></span></span>
                            <?php endif; ?>
                        <?php if ($href !== '') : ?></a><?php else : ?></div><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($quote !== '') : ?>
                <blockquote class="nh-quote">
                    <span class="nh-quote__mark"><?php echo underscores_child_icon('icon_quote_mark'); ?></span>
                    <p><?php echo wp_kses_post($quote); ?></p>
                </blockquote>
            <?php endif; ?>
        </div>
    </section>
    <?php
endif;


if ($section_enabled($related_settings)) { $render_related($related_query); }
