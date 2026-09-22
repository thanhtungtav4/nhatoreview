<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'categories' => is_array($page_fields['categories_settings'] ?? null) ? $page_fields['categories_settings'] : [],
    'projects' => is_array($page_fields['projects_settings'] ?? null) ? $page_fields['projects_settings'] : [],
    'values' => is_array($page_fields['values_settings'] ?? null) ? $page_fields['values_settings'] : [],
];
$section_enabled = static function (array $section): bool {
    return !array_key_exists('is_show', $section) || (bool) $section['is_show'];
};
$banner_settings = $_page_sections['banner'];
$categories_settings = $_page_sections['categories'];
$projects_settings = $_page_sections['projects'];
$values_settings = $_page_sections['values'];

// Expected ACF sections: banner_settings, categories_settings,
// projects_settings, values_settings.
$image_url = static function ($image): string {
    $id = is_array($image) ? absint($image['ID'] ?? $image['id'] ?? 0) : absint($image);
    if ($id > 0) { return (string) wp_get_attachment_image_url($id, 'full'); }
    return is_string($image) ? $image : '';
};
$render_related = static function (): void {
    $query = new WP_Query(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'ignore_sticky_posts' => true]);
    if (!$query->have_posts()) { return; }
    ?>
    <section class="section-posts"><div class="nh-container nh-stack"><div class="nh-rule-head">
        <h2 class="nh-rule-head__title"><?php esc_html_e('Bài viết khác', 'underscores-child'); ?></h2><span class="nh-rule-head__line"></span>
        <a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>"><span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></a>
    </div><div class="nh-grid-3">
        <?php while ($query->have_posts()) : $query->the_post(); get_template_part('partials/components/card-post', null, ['post_id' => get_the_ID()]); endwhile; ?>
    </div></div></section>
    <?php wp_reset_postdata();
};

$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
$hero_lead = (string) ($banner_settings['lead'] ?? '');
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '')) : ?>
    <section class="nh-hero nh-hero--section">
        <?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?><div class="nh-hero__overlay"></div>
        <div class="nh-hero__inner nh-container"><?php if (get_the_title() !== '') : ?><h1 class="nh-hero__title"><?php the_title(); ?></h1><?php endif; ?><?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?></div>
    </section>
<?php endif;

$categories = is_array($categories_settings['items'] ?? null) ? $categories_settings['items'] : [];
if ($section_enabled($categories_settings) && $categories !== []) : ?>
    <section class="section-panel"><div class="nh-container"><div class="nh-category-panel">
        <?php foreach ($categories as $category) : if (!is_array($category)) { continue; } $href = is_array($category['link'] ?? null) ? (string) ($category['link']['url'] ?? '') : (string) ($category['url'] ?? ''); $icon = sanitize_key((string) ($category['icon'] ?? '')); ?>
            <a class="nh-category" href="<?php echo esc_url($href !== '' ? $href : '#'); ?>">
                <?php if ($icon !== '') : ?><svg class="nh-category__icon" aria-hidden="true"><use href="#<?php echo esc_attr($icon); ?>"></use></svg><?php endif; ?>
                <span class="nh-category__body"><?php if (!empty($category['title'])) : ?><h3><?php echo esc_html((string) $category['title']); ?></h3><?php endif; ?><?php if (!empty($category['text'])) : ?><p><?php echo esc_html((string) $category['text']); ?></p><?php endif; ?></span>
            </a>
        <?php endforeach; ?>
    </div></div></section>
<?php endif;

$projects = new WP_Query([
    'post_type' => 'project',
    'post_status' => 'publish',
    'posts_per_page' => 16,
    'paged' => max(1, (int) get_query_var('paged')),
]);
if ($section_enabled($projects_settings) && $projects->have_posts()) : ?>
    <section class="section-projects"><div class="nh-container nh-stack">
        <div class="nh-section-head"><div><?php if (!empty($projects_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $projects_settings['eyebrow']); ?></p><?php endif; ?><?php if (!empty($projects_settings['title'])) : ?><h2 class="nh-section-title"><?php echo esc_html((string) $projects_settings['title']); ?></h2><?php endif; ?></div><?php if (!empty($projects_settings['link'])) : ?><?php echo underscores_child_acf_link($projects_settings['link'], '<span>' . esc_html__('Xem tất cả', 'underscores-child') . '</span><span class="nh-cta__arrow" aria-hidden="true"><svg width="16" height="16"><use href="#nh-arrow-right"></use></svg></span>', 'nh-cta nh-cta--ink'); ?><?php endif; ?></div>
        <div class="nh-grid-4">
            <?php while ($projects->have_posts()) : $projects->the_post(); get_template_part('partials/components/card-project', null, ['post_id' => get_the_ID()]); endwhile; ?>
        </div>
        <?php $pagination = paginate_links(['total' => (int) $projects->max_num_pages, 'current' => max(1, (int) get_query_var('paged')), 'type' => 'list', 'prev_text' => '←', 'next_text' => '→']); if ($pagination) : ?><nav class="nh-pagination" aria-label="<?php esc_attr_e('Phân trang dự án', 'underscores-child'); ?>"><?php echo wp_kses_post($pagination); ?></nav><?php endif; ?>
    </div></section>
<?php endif; wp_reset_postdata();

$value_image = $image_url($values_settings['image'] ?? '');
$value_steps = is_array($values_settings['steps'] ?? null) ? $values_settings['steps'] : [];
if ($section_enabled($values_settings) && ($value_image !== '' || !empty($values_settings['title']) || $value_steps !== [])) : ?>
    <section class="nh-band"><div class="nh-band__image"><?php if ($value_image !== '') : ?><img src="<?php echo esc_url($value_image); ?>" alt="" loading="lazy"><?php endif; ?></div><div class="nh-band__content nh-container"><div class="nh-values-grid">
        <div class="nh-band__copy"><?php if (!empty($values_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $values_settings['eyebrow']); ?></p><?php endif; ?><?php if (!empty($values_settings['title'])) : ?><h2 class="nh-band__title"><?php echo wp_kses_post((string) $values_settings['title']); ?></h2><?php endif; ?><?php if (!empty($values_settings['text'])) : ?><p class="nh-band__lead"><?php echo esc_html((string) $values_settings['text']); ?></p><?php endif; ?></div>
        <?php if ($value_steps !== []) : ?><div class="nh-process-canvas"><svg class="nh-process-path" viewBox="0 0 697 381" fill="none" aria-hidden="true"><path d="M75.5 24H656A41 41 0 0 1 697 65V189A41 41 0 0 1 656 230H181.5"/></svg><?php foreach ($value_steps as $index => $step) : if (!is_array($step)) { continue; } $step_number = $index + 1; $step_icon = sanitize_key((string) ($step['icon'] ?? '')); ?><div class="nh-process-step nh-process-step--<?php echo esc_attr((string) $step_number); ?>"><span class="nh-process-step__number"><?php echo esc_html(sprintf('%02d', $step_number)); ?></span><span class="nh-process-step__body"><?php if ($step_icon !== '') : ?><svg class="nh-process-step__icon" aria-hidden="true"><use href="#<?php echo esc_attr($step_icon); ?>"></use></svg><?php endif; ?><?php if (!empty($step['title'])) : ?><strong><?php echo esc_html((string) $step['title']); ?></strong><?php endif; ?><?php if (!empty($step['text'])) : ?><small><?php echo wp_kses_post((string) $step['text']); ?></small><?php endif; ?></span></div><?php endforeach; ?></div><?php endif; ?>
    </div></div></section>
<?php endif;

$render_related();
