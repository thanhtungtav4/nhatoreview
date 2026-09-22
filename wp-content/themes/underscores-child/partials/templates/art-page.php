<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'topics' => is_array($page_fields['topics_settings'] ?? null) ? $page_fields['topics_settings'] : [],
    'related' => is_array($page_fields['related_settings'] ?? null) ? $page_fields['related_settings'] : [],
];
$section_enabled = static function (array $section): bool {
    return !array_key_exists('is_show', $section) || (bool) $section['is_show'];
};
$banner_settings = $_page_sections['banner'];
$topics_settings = $_page_sections['topics'];

$related_settings = $_page_sections['related'];

// Expected ACF sections: banner_settings, topics_settings, related_settings.
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
];
if ($related_mode === 'manual') {
    $related_query_args['post__in'] = $selected_posts !== [] ? $selected_posts : [0];
    $related_query_args['orderby'] = 'post__in';
} else {
    $related_query_args['category_name'] = 'nghe-thuat';
}
$related_query = new WP_Query($related_query_args);

$render_related = static function (WP_Query $query): void {
    if (!$query->have_posts()) {
        return;
    }
    ?>
    <section class="section-posts">
        <div class="nh-container nh-stack">
            <div class="nh-rule-head">
                <h2 class="nh-rule-head__title"><?php esc_html_e('Bài viết khác', 'underscores-child'); ?></h2>
                <span class="nh-rule-head__line"></span>
                <a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>">
                    <span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span>
                    <svg aria-hidden="true" viewBox="0 0 18 13"><use href="#nh-arrow-right"></use></svg>
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
$hero_title = trim((string) ($banner_settings['title'] ?? ''));
$hero_title = $hero_title !== '' ? $hero_title : trim((string) get_the_title());
$hero_lead  = trim((string) ($banner_settings['lead'] ?? ''));
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || $hero_title !== '')) :
    ?>
    <section class="nh-hero nh-hero--section">
        <?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?>
        <div class="nh-hero__overlay"></div>
        <div class="nh-hero__inner nh-container">
            <?php if ($hero_title !== '') : ?><h1 class="nh-hero__title"><?php echo wp_kses_post($hero_title); ?></h1><?php endif; ?>
            <?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?>
        </div>
    </section>
    <?php
endif;

$features = is_array($topics_settings['items'] ?? null) ? array_values(array_filter(
    $topics_settings['items'],
    static fn ($feature): bool => is_array($feature) && array_filter(
        $feature,
        static fn ($value): bool => $value !== '' && $value !== [] && $value !== null && $value !== 0 && $value !== false
    ) !== []
)) : [];
$features_title = trim((string) ($topics_settings['title'] ?? ''));
if ($section_enabled($topics_settings) && $features !== []) :
    ?>
    <section class="section-head">
        <div class="nh-container nh-rule-head">
            <?php if ($features_title !== '') : ?><h2 class="nh-rule-head__title"><?php echo esc_html($features_title); ?></h2><?php endif; ?>
            <span class="nh-rule-head__line"></span>
            <a class="nh-rule-head__action" href="#chu-de-01">
                <span><?php echo esc_html(sprintf(_n('%d chủ đề', '%d chủ đề', count($features), 'underscores-child'), count($features))); ?></span>
                <svg aria-hidden="true" viewBox="0 0 18 13"><use href="#nh-arrow-right"></use></svg>
            </a>
        </div>
    </section>
    <?php foreach ($features as $index => $feature) :
        $media = $image_url($feature['image'] ?? '');
        $feature_id = sprintf('chu-de-%02d', $index + 1);
        ?>
        <section class="nh-feature<?php echo $index % 2 === 1 ? ' nh-feature--reverse' : ''; ?>" id="<?php echo esc_attr($feature_id); ?>">
            <div class="nh-feature__row">
                <div class="nh-feature__copy">
                    <p class="nh-feature__number"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></p>
                    <?php if (!empty($feature['title'])) : ?><h3 class="nh-feature__title"><?php echo wp_kses_post((string) $feature['title']); ?></h3><?php endif; ?>
                    <span class="nh-feature__rule"></span>
                    <?php if (!empty($feature['text'])) : ?><p class="nh-feature__text"><?php echo esc_html((string) $feature['text']); ?></p><?php endif; ?>
                    <?php if (!empty($feature['link'])) : ?>
                        <?php echo underscores_child_acf_link($feature['link'], '<span>' . esc_html__('Xem tất cả', 'underscores-child') . '</span><span class="nh-cta__arrow" aria-hidden="true"><svg viewBox="0 0 18 13"><use href="#nh-arrow-right"></use></svg></span>', 'nh-cta nh-cta--ink'); ?>
                    <?php endif; ?>
                </div>
                <?php if ($media !== '') : ?><div class="nh-feature__media"><img src="<?php echo esc_url($media); ?>" alt="<?php echo esc_attr(wp_strip_all_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', (string) ($feature['title'] ?? '')))); ?>" loading="lazy"></div><?php endif; ?>
            </div>
            <?php if (!empty($feature['stats']) && is_array($feature['stats'])) : ?>
                <div class="nh-feature__foot">
                    <hr class="nh-hairline">
                    <div class="nh-statbar">
                        <div class="nh-statbar__group">
                            <?php foreach ($feature['stats'] as $stat) :
                                if (!is_array($stat) || (empty($stat['value']) && empty($stat['label']))) {
                                    continue;
                                }
                                $icon = sanitize_key((string) ($stat['icon'] ?? ''));
                                $value = trim((string) ($stat['value'] ?? ''));
                                $label = trim((string) ($stat['label'] ?? ''));
                                ?>
                                <span class="nh-stat">
                                    <?php if ($icon !== '') : ?><svg aria-hidden="true"><use href="#<?php echo esc_attr($icon); ?>"></use></svg><?php endif; ?>
                                    <?php echo esc_html(trim($value . ' ' . $label)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($feature['share_link'])) : ?>
                            <?php echo underscores_child_acf_link($feature['share_link'], '<svg aria-hidden="true"><use href="#nh-share"></use></svg><span>' . esc_html__('Chia sẻ', 'underscores-child') . '</span>', 'nh-stat'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php
endif;


if ($section_enabled($related_settings)) { $render_related($related_query); }
