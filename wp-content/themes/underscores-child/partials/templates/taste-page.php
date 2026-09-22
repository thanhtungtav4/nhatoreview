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
    if ($id > 0) { return (string) wp_get_attachment_image_url($id, 'full'); }
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
    $related_query_args['category_name'] = 'phong-vi';
}
$related_query = new WP_Query($related_query_args);
$has_related_posts = $section_enabled($related_settings) && $related_query->have_posts();

$render_related = static function (WP_Query $query): void {
    if (!$query->have_posts()) { return; }
    ?>
    <section class="section-posts"><div class="nh-container nh-stack"><div class="nh-rule-head"><h2 class="nh-rule-head__title"><?php esc_html_e('Bài viết khác', 'underscores-child'); ?></h2><span class="nh-rule-head__line"></span><a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>"><span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span><span aria-hidden="true">→</span></a></div><div class="nh-grid-3"><?php while ($query->have_posts()) : $query->the_post(); get_template_part('partials/components/card-post', null, ['post_id' => get_the_ID()]); endwhile; ?></div></div></section>
    <?php wp_reset_postdata();
};
$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
if ($section_enabled($banner_settings) && $hero_image !== '') : ?><section class="nh-hero nh-hero--section"><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><div class="nh-hero__overlay"></div><?php if (get_the_title() !== '') : ?><h1 class="sr-only"><?php the_title(); ?></h1><?php endif; ?></section><?php endif;

$intro_eyebrow = (string) ($intro_settings['eyebrow'] ?? '');
$intro_title = (string) ($intro_settings['title'] ?? '');
$intro_lead = (string) ($intro_settings['lead'] ?? '');
$pillars = is_array($intro_settings['pillars'] ?? null) ? $intro_settings['pillars'] : [];
$portraits = is_array($intro_settings['portraits'] ?? null) ? $intro_settings['portraits'] : [];
if ($section_enabled($intro_settings) && ($intro_eyebrow !== '' || $intro_title !== '' || $intro_lead !== '' || $pillars !== [] || $portraits !== [])) : ?>
    <section class="section-intro<?php echo $has_related_posts ? '' : ' section-intro--standalone'; ?>"><div class="nh-container nh-stack"><div class="nh-intro"><?php if ($intro_eyebrow !== '') : ?><p class="nh-intro__eyebrow"><?php echo esc_html($intro_eyebrow); ?></p><?php endif; ?><?php if ($intro_title !== '') : ?><h2 class="nh-intro__title"><?php echo wp_kses_post($intro_title); ?></h2><?php endif; ?><?php if ($intro_lead !== '') : ?><p class="nh-intro__lead"><?php echo esc_html($intro_lead); ?></p><?php endif; ?></div>
        <?php if ($pillars !== []) : ?><div class="nh-pillars"><?php foreach ($pillars as $pillar) : if (!is_array($pillar)) { continue; } $icon = sanitize_key((string) ($pillar['icon'] ?? '')); ?><div class="nh-pillar"><?php if ($icon !== '') : ?><svg class="nh-pillar__icon" aria-hidden="true"><use href="#<?php echo esc_attr($icon); ?>"></use></svg><?php endif; ?><?php if (!empty($pillar['title'])) : ?><h3 class="nh-pillar__title"><?php echo esc_html((string) $pillar['title']); ?></h3><?php endif; ?><?php if (!empty($pillar['text'])) : ?><p class="nh-pillar__text"><?php echo esc_html((string) $pillar['text']); ?></p><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
        <?php if ($portraits !== []) : ?><div class="nh-grid-4"><?php foreach ($portraits as $portrait) : if (!is_array($portrait)) { continue; } $src = $image_url($portrait['image'] ?? ''); $href = is_array($portrait['link'] ?? null) ? (string) ($portrait['link']['url'] ?? '') : (string) ($portrait['url'] ?? ''); if ($src === '' && empty($portrait['title'])) { continue; } $tag = $href !== '' ? 'a' : 'div'; ?><<?php echo $tag; ?> class="nh-portrait-card"<?php if ($href !== '') : ?> href="<?php echo esc_url($href); ?>"<?php endif; ?>><?php if ($src !== '') : ?><span class="nh-portrait-card__image"><img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr((string) ($portrait['title'] ?? '')); ?>" loading="lazy"></span><?php endif; ?><span class="nh-portrait-card__body"><?php if (!empty($portrait['title'])) : ?><span class="nh-portrait-card__title"><?php echo esc_html((string) $portrait['title']); ?></span><?php endif; ?><?php if (!empty($portrait['text'])) : ?><span class="nh-portrait-card__text"><?php echo esc_html((string) $portrait['text']); ?></span><?php endif; ?></span></<?php echo $tag; ?>><?php endforeach; ?></div><?php endif; ?>
    </div></section>
<?php endif;
if ($section_enabled($related_settings)) { $render_related($related_query); }
