<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];

// Expected ACF keys: hero_image, hero_lead, intro_eyebrow, intro_title, network_roles,
// network_audiences, network_gallery, partners, related_posts.
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
                <a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>"><span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span><span aria-hidden="true">→</span></a>
            </div>
            <div class="nh-grid-3 u-gap-22">
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
        <?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?>
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
$roles         = is_array($page_fields['network_roles'] ?? null) ? $page_fields['network_roles'] : [];
if ($intro_eyebrow !== '' || $intro_title !== '' || $roles !== []) :
    ?>
    <section class="section-intro">
        <div class="nh-container nh-stack">
            <?php if ($intro_eyebrow !== '' || $intro_title !== '') : ?>
                <div class="nh-intro u-gap-13">
                    <?php if ($intro_eyebrow !== '') : ?><p class="nh-intro__eyebrow"><?php echo esc_html($intro_eyebrow); ?></p><?php endif; ?>
                    <?php if ($intro_title !== '') : ?><h2 class="nh-intro__title"><?php echo esc_html($intro_title); ?></h2><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($roles !== []) : ?>
                <div class="nh-grid-3 u-gap-22">
                    <?php foreach ($roles as $role) :
                        if (!is_array($role)) { continue; }
                        $name = (string) ($role['title'] ?? $role['name'] ?? '');
                        $copy = (string) ($role['text'] ?? $role['description'] ?? '');
                        $href = is_array($role['link'] ?? null) ? (string) ($role['link']['url'] ?? '') : (string) ($role['url'] ?? '');
                        $background = $image_url($role['image'] ?? '');
                        if ($name === '' && $copy === '' && $background === '') { continue; }
                        ?>
                        <a class="nh-role-card" href="<?php echo esc_url($href !== '' ? $href : '#'); ?>"<?php if ($background !== '') : ?> style="--nh-role-card-image:url('<?php echo esc_url($background); ?>')"<?php endif; ?>>
                            <span class="nh-role-card__body"><?php if (!empty($role['icon'])) : ?><svg class="nh-role-card__icon" aria-hidden="true" width="48" height="48"><use href="#<?php echo esc_attr(ltrim((string) $role['icon'], '#')); ?>"></use></svg><?php endif; ?><span class="nh-role-card__copy">
                                <?php if ($name !== '') : ?><strong><?php echo esc_html($name); ?></strong><?php endif; ?>
                                <?php if ($copy !== '') : ?><span><?php echo esc_html($copy); ?></span><?php endif; ?>
                            </span></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
endif;

$audiences = is_array($page_fields['network_audiences'] ?? null) ? $page_fields['network_audiences'] : [];
if ($audiences !== []) : ?>
    <section class="section-audience">
        <div class="nh-container section-audience__inner">
            <div>
                <?php if (!empty($page_fields['audience_eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $page_fields['audience_eyebrow']); ?></p><?php endif; ?>
                <?php if (!empty($page_fields['audience_title'])) : ?><h2 class="nh-section-title u-text-white"><?php echo wp_kses_post((string) $page_fields['audience_title']); ?></h2><?php endif; ?>
            </div>
            <div class="section-audience__cards">
                <?php foreach ($audiences as $audience) : if (!is_array($audience)) { continue; } ?>
                    <div class="nh-audience-card">
                        <span class="nh-audience-card__head">
                            <?php if (!empty($audience['title'])) : ?><strong><?php echo esc_html((string) $audience['title']); ?></strong><?php endif; ?>
                            <?php if (!empty($audience['subtitle'])) : ?><span><?php echo esc_html((string) $audience['subtitle']); ?></span><?php endif; ?>
                        </span>
                        <?php if (!empty($audience['text'])) : ?><p><?php echo esc_html((string) $audience['text']); ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif;

$gallery = is_array($page_fields['network_gallery'] ?? null) ? $page_fields['network_gallery'] : [];
if ($gallery !== []) : ?>
    <section class="section-gallery">
        <div class="nh-container nh-intro u-gap-13">
            <?php if (!empty($page_fields['gallery_eyebrow'])) : ?><p class="nh-intro__eyebrow u-gap-8"><?php echo esc_html((string) $page_fields['gallery_eyebrow']); ?></p><?php endif; ?>
            <?php if (!empty($page_fields['gallery_title'])) : ?><h2 class="nh-intro__title"><?php echo esc_html((string) $page_fields['gallery_title']); ?></h2><?php endif; ?>
        </div>
        <?php
        $gallery = array_values($gallery);
        $gallery_rows = [
            [0, 1, 2, 3, 4, 5],
            [6, 0, 1, 2, 3, 4],
            [5, 6, 0, 1, 2, 3],
        ];
        ?>
        <div class="nh-gallery">
            <?php foreach ($gallery_rows as $row_indexes) : ?>
                <div class="nh-gallery__row"><div class="nh-gallery__track">
                    <?php foreach ([$row_indexes, $row_indexes] as $set_index => $indexes) : ?>
                        <div class="nh-gallery__set"<?php if ($set_index === 1) : ?> aria-hidden="true"<?php endif; ?>>
                            <?php foreach ($indexes as $index) : if (!isset($gallery[$index])) { continue; } $src = $image_url($gallery[$index]); if ($src !== '') : ?><img src="<?php echo esc_url($src); ?>" alt="" loading="lazy"><?php endif; endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div></div>
            <?php endforeach; ?>
        </div>
        <div class="nh-container u-flex u-justify-center u-mt-40"><a class="nh-cta nh-cta--ink" href="#">XEM TẤT CẢ<span class="nh-cta__arrow" aria-hidden="true"><svg width="16" height="16"><use href="#nh-arrow-right"></use></svg></span></a></div>
    </section>
<?php endif;

$partners = is_array($page_fields['partners'] ?? null) ? $page_fields['partners'] : [];
if ($partners === []) {
    $partners = is_array(underscores_get_option('partners', [])) ? underscores_get_option('partners', []) : [];
}
if ($partners !== []) : ?>
    <section class="section-partners">
        <div class="nh-container nh-intro u-gap-13">
            <?php if (!empty($page_fields['partners_eyebrow'])) : ?><p class="nh-intro__eyebrow u-gap-8"><?php echo esc_html((string) $page_fields['partners_eyebrow']); ?></p><?php endif; ?>
            <?php if (!empty($page_fields['partners_title'])) : ?><h2 class="nh-intro__title"><?php echo esc_html((string) $page_fields['partners_title']); ?></h2><?php endif; ?>
        </div>
        <div class="nh-container nh-partner-table">
            <?php foreach ($partners as $partner) : if (!is_array($partner)) { continue; } $logos = is_array($partner['logos'] ?? null) ? $partner['logos'] : []; ?>
                <div class="nh-partner-row">
                    <div class="nh-partner-row__label">
                        <?php if (!empty($partner['title'])) : ?><strong><?php echo esc_html((string) $partner['title']); ?></strong><?php endif; ?>
                        <?php if (!empty($partner['subtitle'])) : ?><span><?php echo esc_html((string) $partner['subtitle']); ?></span><?php endif; ?>
                    </div>
                    <div class="nh-partner-row__logos">
                        <?php foreach ($logos as $logo) : $src = $image_url(is_array($logo) && isset($logo['image']) ? $logo['image'] : $logo); if ($src !== '') : ?><a class="nh-partner" href="<?php echo esc_url(is_array($logo) ? (string) ($logo['url'] ?? '#') : '#'); ?>" aria-label="<?php echo esc_attr((string) ($partner['title'] ?? '')); ?>"><img src="<?php echo esc_url($src); ?>" alt="" loading="lazy"></a><?php endif; endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif;

$render_related();
