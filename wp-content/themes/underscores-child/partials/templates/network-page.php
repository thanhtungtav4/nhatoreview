<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'intro' => is_array($page_fields['intro_settings'] ?? null) ? $page_fields['intro_settings'] : [],
    'audience' => is_array($page_fields['audience_settings'] ?? null) ? $page_fields['audience_settings'] : [],
    'gallery' => is_array($page_fields['gallery_settings'] ?? null) ? $page_fields['gallery_settings'] : [],
    'partners' => is_array($page_fields['partners_settings'] ?? null) ? $page_fields['partners_settings'] : [],
    'related' => is_array($page_fields['related_settings'] ?? null) ? $page_fields['related_settings'] : [],
];
$section_enabled = 'underscores_child_section_is_visible';
$banner_settings = $_page_sections['banner'];
$intro_settings = $_page_sections['intro'];
$audience_settings = $_page_sections['audience'];
$gallery_settings = $_page_sections['gallery'];
$partners_settings = $_page_sections['partners'];

$related_settings = $_page_sections['related'];

// Expected ACF sections: banner_settings, intro_settings, audience_settings,
// gallery_settings, partners_settings, related_settings.
$image_url = 'underscores_child_acf_image_url';

$related_query = underscores_child_related_posts_query($related_settings);

$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
$hero_lead  = (string) ($banner_settings['lead'] ?? '');
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '')) :
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

$intro_eyebrow = (string) ($intro_settings['eyebrow'] ?? '');
$intro_title   = (string) ($intro_settings['title'] ?? '');
$roles         = is_array($intro_settings['roles'] ?? null) ? $intro_settings['roles'] : [];
if ($section_enabled($intro_settings) && ($intro_eyebrow !== '' || $intro_title !== '' || $roles !== [])) :
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
                        $role_icon_id = absint($role['icon'] ?? 0);
                        if ($name === '' && $copy === '' && $background === '') { continue; }
                        $role_tag = $href !== '' ? 'a' : 'div';
                        ?>
                        <<?php echo $role_tag; ?> class="nh-role-card"<?php if ($href !== '') : ?> href="<?php echo esc_url($href); ?>"<?php endif; ?><?php if ($background !== '') : ?> style="--nh-role-card-image:url('<?php echo esc_url($background); ?>')"<?php endif; ?>>
                            <span class="nh-role-card__body"><?php if ($role_icon_id > 0) : ?><?php echo wp_get_attachment_image($role_icon_id, 'thumbnail', false, ['class' => 'nh-role-card__icon', 'alt' => '', 'loading' => 'lazy']); ?><?php endif; ?><span class="nh-role-card__copy">
                                <?php if ($name !== '') : ?><strong><?php echo esc_html($name); ?></strong><?php endif; ?>
                                <?php if ($copy !== '') : ?><span><?php echo esc_html($copy); ?></span><?php endif; ?>
                            </span></span>
                        </<?php echo $role_tag; ?>>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
endif;

$audiences = is_array($audience_settings['items'] ?? null) ? $audience_settings['items'] : [];
if ($section_enabled($audience_settings) && $audiences !== []) : ?>
    <section class="section-audience">
        <div class="nh-container section-audience__inner">
            <div>
                <?php if (!empty($audience_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $audience_settings['eyebrow']); ?></p><?php endif; ?>
                <?php if (!empty($audience_settings['title'])) : ?><h2 class="nh-section-title u-text-white"><?php echo wp_kses_post((string) $audience_settings['title']); ?></h2><?php endif; ?>
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

$gallery = is_array($gallery_settings['images'] ?? null) ? $gallery_settings['images'] : [];
if ($section_enabled($gallery_settings) && $gallery !== []) : ?>
    <section id="gallery" class="section-gallery">
        <div class="nh-container nh-intro u-gap-13">
            <?php if (!empty($gallery_settings['eyebrow'])) : ?><p class="nh-intro__eyebrow u-gap-8"><?php echo esc_html((string) $gallery_settings['eyebrow']); ?></p><?php endif; ?>
            <?php if (!empty($gallery_settings['title'])) : ?><h2 class="nh-intro__title"><?php echo esc_html((string) $gallery_settings['title']); ?></h2><?php endif; ?>
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
        <?php if (!empty($gallery_settings['link'])) : ?>
            <div class="nh-container u-flex u-justify-center u-mt-40"><?php echo underscores_child_acf_link($gallery_settings['link'], '<span>' . esc_html__('XEM TẤT CẢ', 'underscores-child') . '</span><span class="nh-cta__arrow" aria-hidden="true">' . underscores_child_icon_mask('icon_arrow_right') . '</span>', 'nh-cta nh-cta--ink'); ?></div>
        <?php endif; ?>
    </section>
<?php endif;

$partners = is_array($partners_settings['items'] ?? null) ? $partners_settings['items'] : [];
if ($partners === []) {
    $fallback_partners = underscores_get_option('partners', []);
    $partners = is_array($fallback_partners) ? $fallback_partners : [];
}
if ($section_enabled($partners_settings) && $partners !== []) : ?>
    <section class="section-partners">
        <div class="nh-container nh-intro u-gap-13">
            <?php if (!empty($partners_settings['eyebrow'])) : ?><p class="nh-intro__eyebrow u-gap-8"><?php echo esc_html((string) $partners_settings['eyebrow']); ?></p><?php endif; ?>
            <?php if (!empty($partners_settings['title'])) : ?><h2 class="nh-intro__title"><?php echo esc_html((string) $partners_settings['title']); ?></h2><?php endif; ?>
        </div>
        <div class="nh-container nh-partner-table">
            <?php foreach ($partners as $partner) : if (!is_array($partner)) { continue; } $logos = is_array($partner['logos'] ?? null) ? $partner['logos'] : []; ?>
                <div class="nh-partner-row">
                    <div class="nh-partner-row__label">
                        <?php if (!empty($partner['title'])) : ?><strong><?php echo esc_html((string) $partner['title']); ?></strong><?php endif; ?>
                        <?php if (!empty($partner['subtitle'])) : ?><span><?php echo esc_html((string) $partner['subtitle']); ?></span><?php endif; ?>
                    </div>
                    <div class="nh-partner-row__logos">
                        <?php foreach ($logos as $logo) :
                            $logo_image = is_array($logo) && isset($logo['image']) ? $logo['image'] : $logo;
                            $src = $image_url($logo_image);
                            $logo_href = is_array($logo) ? trim((string) ($logo['url'] ?? '')) : '';
                            if ($src === '') { continue; }
                            $logo_tag = $logo_href !== '' ? 'a' : 'span';
                            ?>
                            <<?php echo $logo_tag; ?> class="nh-partner"<?php if ($logo_href !== '') : ?> href="<?php echo esc_url($logo_href); ?>" aria-label="<?php echo esc_attr((string) ($partner['title'] ?? '')); ?>"<?php endif; ?>><img src="<?php echo esc_url($src); ?>" alt="" loading="lazy"></<?php echo $logo_tag; ?>>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif;

if ($section_enabled($related_settings)) { underscores_child_render_related_posts($related_query); }
