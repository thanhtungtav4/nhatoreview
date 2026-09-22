<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];

// Expected ACF keys: hero_image, hero_lead, intro_eyebrow, intro_title,
// original_panels, original_quote, original_features.
$image_url = static function ($image): string {
    $id = is_array($image) ? absint($image['ID'] ?? $image['id'] ?? 0) : absint($image);
    if ($id > 0) {
        return (string) wp_get_attachment_image_url($id, 'full');
    }

    return is_string($image) ? $image : '';
};
$render_related = static function (): void {
    $query = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'ignore_sticky_posts' => true,
    ]);
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
                    <svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg>
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

$hero_image = $image_url($page_fields['hero_image'] ?? get_post_thumbnail_id());
$hero_lead  = (string) ($page_fields['hero_lead'] ?? '');
if ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '') :
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

$intro_eyebrow = (string) ($page_fields['intro_eyebrow'] ?? '');
$intro_title   = (string) ($page_fields['intro_title'] ?? '');
$panels       = is_array($page_fields['original_panels'] ?? null) ? $page_fields['original_panels'] : [];
$quote        = (string) ($page_fields['original_quote'] ?? '');
if ($intro_eyebrow !== '' || $intro_title !== '' || $panels !== [] || $quote !== '') :
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
                                <span class="nh-cta"><span><?php esc_html_e('Khám phá', 'underscores-child'); ?></span><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span></span>
                            <?php endif; ?>
                        <?php if ($href !== '') : ?></a><?php else : ?></div><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($quote !== '') : ?>
                <blockquote class="nh-quote">
                    <span class="nh-quote__mark" aria-hidden="true"><svg><use href="#nh-quote-mark"></use></svg></span>
                    <p><?php echo wp_kses_post($quote); ?></p>
                </blockquote>
            <?php endif; ?>
        </div>
    </section>
    <?php
endif;

$features = is_array($page_fields['original_features'] ?? null) ? $page_fields['original_features'] : [];
foreach ($features as $index => $feature) :
    if (!is_array($feature)) {
        continue;
    }
    $src = $image_url($feature['image'] ?? '');
    ?>
    <section class="nh-feature<?php echo $index % 2 === 1 ? ' nh-feature--reverse' : ''; ?>">
        <div class="nh-feature__row">
            <div class="nh-feature__copy">
                <p class="nh-feature__number"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></p>
                <?php if (!empty($feature['title'])) : ?><h3 class="nh-feature__title"><?php echo wp_kses_post((string) $feature['title']); ?></h3><?php endif; ?>
                <span class="nh-feature__rule"></span>
                <?php if (!empty($feature['text'])) : ?><p class="nh-feature__text"><?php echo esc_html((string) $feature['text']); ?></p><?php endif; ?>
                <?php if (!empty($feature['link'])) : ?>
                    <?php echo underscores_child_acf_link($feature['link'], '<span>' . esc_html__('Xem tất cả', 'underscores-child') . '</span><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span>', 'nh-cta nh-cta--ink'); ?>
                <?php endif; ?>
            </div>
            <?php if ($src !== '') : ?><div class="nh-feature__media"><img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr((string) ($feature['title'] ?? '')); ?>" loading="lazy"></div><?php endif; ?>
        </div>
    </section>
<?php endforeach;

$render_related();
