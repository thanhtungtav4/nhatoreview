<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$page_fields = [];
if (function_exists('get_fields')) {
    try {
        $page_fields = get_fields() ?: [];
    } catch (\Throwable $e) {
        $page_fields = [];
    }
}

// Expected ACF group keys: banner_settings, category_strip_settings, style_picker_settings.
$banner_settings         = is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [];
$category_strip_settings = is_array($page_fields['category_strip_settings'] ?? null) ? $page_fields['category_strip_settings'] : [];
$style_picker_settings   = is_array($page_fields['style_picker_settings'] ?? null) ? $page_fields['style_picker_settings'] : [];

// Expected ACF group keys: booking_settings, portfolio_settings, services_settings.
$booking_settings   = is_array($page_fields['booking_settings'] ?? null) ? $page_fields['booking_settings'] : [];
$portfolio_settings = is_array($page_fields['portfolio_settings'] ?? null) ? $page_fields['portfolio_settings'] : [];
$services_settings  = is_array($page_fields['services_settings'] ?? null) ? $page_fields['services_settings'] : [];

// Expected ACF group keys: art_settings, taste_settings, contact_band_settings.
$art_settings           = is_array($page_fields['art_settings'] ?? null) ? $page_fields['art_settings'] : [];
$taste_settings        = is_array($page_fields['taste_settings'] ?? null) ? $page_fields['taste_settings'] : [];
$contact_band_settings = is_array($page_fields['contact_band_settings'] ?? null) ? $page_fields['contact_band_settings'] : [];

// Expected ACF group keys: partners_settings and events_settings. Theme Settings may also provide partners.
$partners_settings = is_array($page_fields['partners_settings'] ?? null) ? $page_fields['partners_settings'] : [];
$events_settings   = is_array($page_fields['events_settings'] ?? null) ? $page_fields['events_settings'] : [];

$image_id = static function ($image): int {
    if (is_array($image)) {
        return absint($image['ID'] ?? $image['id'] ?? 0);
    }

    return absint($image);
};

$link_data = static function ($link): array {
    if (is_string($link)) {
        return ['url' => $link, 'title' => '', 'target' => ''];
    }

    if (!is_array($link)) {
        return ['url' => '', 'title' => '', 'target' => ''];
    }

    return [
        'url'    => (string) ($link['url'] ?? ''),
        'title'  => (string) ($link['title'] ?? ''),
        'target' => (string) ($link['target'] ?? ''),
    ];
};

$image_url = static function (int $attachment_id, string $size = 'full'): string {
    if ($attachment_id < 1) {
        return '';
    }

    return (string) wp_get_attachment_image_url($attachment_id, $size);
};

$section_visible = static function (array $section): bool {
    return !array_key_exists('is_show', $section) || (bool) $section['is_show'];
};

underscores_child_set_main_class('page-home');
get_header();

$banner_image_id = $image_id($banner_settings['image'] ?? get_post_thumbnail_id());
$banner_title    = trim((string) ($banner_settings['title'] ?? get_the_title()));
$banner_lead     = trim((string) ($banner_settings['lead'] ?? $banner_settings['text'] ?? ''));
$banner_link     = $link_data($banner_settings['link'] ?? []);
if ($section_visible($banner_settings) && ($banner_image_id > 0 || $banner_title !== '' || $banner_lead !== '')) :
    ?>
    <section class="nh-hero">
        <?php if ($banner_image_id > 0) : ?><div class="nh-hero__media"><?php echo wp_get_attachment_image($banner_image_id, 'full', false, ['alt' => $banner_title, 'loading' => 'eager', 'fetchpriority' => 'high']); ?></div><?php endif; ?>
        <div class="nh-hero__overlay"></div>
        <div class="nh-hero__inner nh-container">
            <?php if ($banner_title !== '') : ?><h1 class="nh-hero__title"><?php echo wp_kses_post($banner_title); ?></h1><?php endif; ?>
            <?php if ($banner_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($banner_lead); ?></p><?php endif; ?>
            <?php if ($banner_link['url'] !== '') : ?>
                <div class="nh-hero__actions">
                    <a class="nh-cta" href="<?php echo esc_url($banner_link['url']); ?>"<?php echo $banner_link['target'] !== '' ? ' target="' . esc_attr($banner_link['target']) . '" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($banner_link['title']); ?><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php
$category_items = is_array($category_strip_settings['items'] ?? null) ? $category_strip_settings['items'] : [];
if ($section_visible($category_strip_settings) && $category_items !== []) :
    ?>
    <section class="home-categories">
        <?php if (!empty($category_strip_settings['title'])) : ?><h2 class="sr-only"><?php echo esc_html((string) $category_strip_settings['title']); ?></h2><?php endif; ?>
        <div class="nh-tile-strip">
            <?php foreach ($category_items as $item) :
                if (!is_array($item)) {
                    continue;
                }
                $title = trim((string) ($item['title'] ?? ''));
                $text  = trim((string) ($item['text'] ?? $item['description'] ?? ''));
                $link  = $link_data($item['link'] ?? []);
                $image = $image_url($image_id($item['image'] ?? 0), 'large');
                if ($title === '' && $text === '' && $image === '') {
                    continue;
                }
                if ($link['url'] === '') {
                    continue;
                }
                ?>
                <a class="nh-tile" href="<?php echo esc_url($link['url']); ?>"<?php if ($image !== '') : ?> style="--nh-tile-image:url('<?php echo esc_url($image); ?>')"<?php endif; ?><?php echo $link['target'] !== '' ? ' target="' . esc_attr($link['target']) . '" rel="noopener"' : ''; ?>>
                    <span class="nh-tile__body">
                        <?php if ($title !== '') : ?><span class="nh-tile__title"><?php echo esc_html($title); ?></span><?php endif; ?>
                        <?php if ($text !== '') : ?><span class="nh-tile__text"><?php echo esc_html($text); ?></span><?php endif; ?>
                        <span class="nh-round-arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<div class="home-stack">
    <?php
    $style_items = is_array($style_picker_settings['items'] ?? null) ? $style_picker_settings['items'] : [];
    if ($section_visible($style_picker_settings) && ($style_items !== [] || !empty($style_picker_settings['title']) || !empty($style_picker_settings['text']))) :
        $style_link = $link_data($style_picker_settings['link'] ?? []);
        ?>
        <section class="nh-container">
            <div class="nh-style-picker">
                <div class="nh-style-picker__copy">
                    <div class="nh-style-picker__head">
                        <?php if (!empty($style_picker_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $style_picker_settings['eyebrow']); ?></p><?php endif; ?>
                        <?php if (!empty($style_picker_settings['title'])) : ?><h2 class="nh-section-title u-mt-0"><?php echo esc_html((string) $style_picker_settings['title']); ?></h2><?php endif; ?>
                        <?php if (!empty($style_picker_settings['text'])) : ?><p><?php echo esc_html((string) $style_picker_settings['text']); ?></p><?php endif; ?>
                    </div>
                    <?php if ($style_link['url'] !== '') : ?><a class="nh-cta nh-cta--ink" href="<?php echo esc_url($style_link['url']); ?>"<?php echo $style_link['target'] !== '' ? ' target="' . esc_attr($style_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($style_link['title']); ?><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span></a><?php endif; ?>
                </div>
                <?php if ($style_items !== []) : ?>
                    <div class="nh-style-picker__rows">
                        <?php foreach (array_chunk($style_items, 4) as $style_row) : ?>
                            <div class="nh-style-row">
                                <?php foreach ($style_row as $style_item) :
                                    if (!is_array($style_item)) {
                                        continue;
                                    }
                                    $style_title = trim((string) ($style_item['title'] ?? $style_item['name'] ?? ''));
                                    $style_image = $image_url($image_id($style_item['image'] ?? 0), 'large');
                                    $style_link  = $link_data($style_item['link'] ?? []);
                                    if ($style_title === '' && $style_image === '') {
                                        continue;
                                    }
                                    $style_tag = $style_link['url'] !== '' ? 'a' : 'div';
                                    ?>
                                    <<?php echo $style_tag; ?> class="nh-style-tile"<?php if ($style_link['url'] !== '') : ?> href="<?php echo esc_url($style_link['url']); ?>"<?php echo $style_link['target'] !== '' ? ' target="' . esc_attr($style_link['target']) . '" rel="noopener"' : ''; ?><?php endif; ?><?php if ($style_image !== '') : ?> style="--nh-style-tile-image:url('<?php echo esc_url($style_image); ?>')"<?php endif; ?>>
                                        <?php if ($style_title !== '') : ?><span><?php echo esc_html($style_title); ?></span><?php endif; ?>
                                    </<?php echo $style_tag; ?>>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $booking_image = $image_url($image_id($booking_settings['image'] ?? 0), 'full');
    if ($section_visible($booking_settings) && $booking_settings !== []) :
        $booking_link = $link_data($booking_settings['link'] ?? []);
        ?>
        <section class="nh-booking"<?php if ($booking_image !== '') : ?> style="--nh-booking-image:url('<?php echo esc_url($booking_image); ?>')"<?php endif; ?>>
            <div class="nh-booking__inner">
                <div class="nh-booking__copy">
                    <?php if (!empty($booking_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $booking_settings['eyebrow']); ?></p><?php endif; ?>
                    <?php if (!empty($booking_settings['title'])) : ?><h2 class="nh-booking__title"><?php echo esc_html((string) $booking_settings['title']); ?></h2><?php endif; ?>
                </div>
                <?php if ($booking_link['url'] !== '') : ?><a class="nh-cta" href="<?php echo esc_url($booking_link['url']); ?>"<?php echo $booking_link['target'] !== '' ? ' target="' . esc_attr($booking_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($booking_link['title']); ?><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span></a><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $portfolio_terms = get_terms([
        'taxonomy'   => 'portfolio_category',
        'hide_empty' => true,
    ]);
    $portfolio_terms = is_wp_error($portfolio_terms) ? [] : $portfolio_terms;
    $portfolio_query = new WP_Query([
        'post_type'           => 'portfolio',
        'post_status'         => 'publish',
        'posts_per_page'      => 12,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ]);
    $mosaic_main_id  = $image_id($portfolio_settings['mosaic_main_image'] ?? 0);
    $mosaic_side_ids = array_values(array_filter([
        $image_id($portfolio_settings['mosaic_side_image_1'] ?? 0),
        $image_id($portfolio_settings['mosaic_side_image_2'] ?? 0),
        $image_id($portfolio_settings['mosaic_side_image_3'] ?? 0),
    ]));
    $has_mosaic = $mosaic_main_id > 0 && count($mosaic_side_ids) === 3;
    if ($section_visible($portfolio_settings) && ($portfolio_terms !== [] || $portfolio_query->have_posts())) :
        ?>
        <section class="nh-container u-flex-col u-gap-40 u-items-center">
            <?php if (!empty($portfolio_settings['text'])) : ?><p class="nh-portfolio-lead"><?php echo esc_html((string) $portfolio_settings['text']); ?></p><?php endif; ?>
            <div class="nh-tabs" role="tablist" aria-label="<?php echo esc_attr((string) ($portfolio_settings['tabs_label'] ?? __('Danh mục portfolio', 'underscores-child'))); ?>">
                <button class="nh-tab" type="button" role="tab" aria-selected="true" aria-controls="portfolio-grid" data-portfolio-term=""><?php echo esc_html((string) ($portfolio_settings['all_label'] ?? __('Tất cả', 'underscores-child'))); ?></button>
                <?php foreach ($portfolio_terms as $portfolio_term) : ?>
                    <button class="nh-tab" type="button" role="tab" aria-selected="false" aria-controls="portfolio-grid" data-portfolio-term="<?php echo esc_attr($portfolio_term->slug); ?>"><?php echo esc_html($portfolio_term->name); ?></button>
                <?php endforeach; ?>
            </div>
            <?php if ($has_mosaic) : ?>
                <div class="nh-mosaic" data-portfolio-mosaic>
                    <?php echo wp_get_attachment_image($mosaic_main_id, 'large', false, ['class' => 'nh-mosaic__main', 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 900px) 100vw, 970px']); ?>
                    <div class="nh-mosaic__side">
                        <?php foreach ($mosaic_side_ids as $mosaic_side_id) : ?>
                            <?php echo wp_get_attachment_image($mosaic_side_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 900px) 200px, 306px']); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($portfolio_query->have_posts()) : ?>
                <?php underscores_child_prime_thumbnail_cache($portfolio_query); ?>
                <div id="portfolio-grid" class="nh-grid-4" data-portfolio-grid<?php echo $has_mosaic ? ' hidden' : ''; ?>>
                    <?php while ($portfolio_query->have_posts()) : $portfolio_query->the_post(); ?>
                        <?php
                        $post_terms = get_the_terms(get_the_ID(), 'portfolio_category');
                        $post_terms = is_array($post_terms) ? $post_terms : [];
                        $term_slugs = array_values(array_filter(array_map(static fn ($term): string => $term instanceof WP_Term ? $term->slug : '', $post_terms)));
                        $location   = $post_terms !== [] ? (string) $post_terms[0]->name : '';
                        ?>
                        <div data-portfolio-terms="<?php echo esc_attr(implode(' ', $term_slugs)); ?>">
                            <?php get_template_part('partials/components/card-project', null, ['post_id' => get_the_ID(), 'location' => $location]); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    endif;
    wp_reset_postdata();
    ?>

    <?php
    $service_items = is_array($services_settings['items'] ?? null) ? $services_settings['items'] : [];
    if ($section_visible($services_settings) && $service_items !== []) :
        $services_link = $link_data($services_settings['link'] ?? []);
        ?>
        <section class="home-services">
            <div class="home-services__head">
                <div>
                    <?php if (!empty($services_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $services_settings['eyebrow']); ?></p><?php endif; ?>
                    <?php if (!empty($services_settings['title'])) : ?><h2 class="nh-section-title u-text-white u-text-upper"><?php echo esc_html((string) $services_settings['title']); ?></h2><?php endif; ?>
                </div>
                <?php if ($services_link['url'] !== '') : ?><a class="nh-cta" href="<?php echo esc_url($services_link['url']); ?>"<?php echo $services_link['target'] !== '' ? ' target="' . esc_attr($services_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($services_link['title']); ?><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span></a><?php endif; ?>
            </div>
            <div class="home-services__grid">
                <?php foreach ($service_items as $service) :
                    if (!is_array($service)) {
                        continue;
                    }
                    $service_title = trim((string) ($service['title'] ?? ''));
                    $service_text  = trim((string) ($service['text'] ?? $service['description'] ?? ''));
                    $service_image = $image_id($service['image'] ?? 0);
                    if ($service_title === '' && $service_text === '' && $service_image < 1) {
                        continue;
                    }
                    ?>
                    <article class="nh-service-card">
                        <?php if ($service_image > 0) : ?><span class="nh-service-card__image"><?php echo wp_get_attachment_image($service_image, 'large', false, ['alt' => $service_title, 'loading' => 'lazy']); ?></span><?php endif; ?>
                        <span class="nh-service-card__body">
                            <?php if ($service_title !== '') : ?><span class="nh-service-card__title"><?php echo esc_html($service_title); ?></span><?php endif; ?>
                            <?php if ($service_text !== '') : ?><span class="nh-service-card__text"><?php echo esc_html($service_text); ?></span><?php endif; ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $art_items = is_array($art_settings['items'] ?? null) ? $art_settings['items'] : [];
    if ($section_visible($art_settings) && is_array($art_items) && $art_items !== []) :
        ?>
        <?php foreach (array_values($art_items) as $art_index => $art_item) :
            if (!is_array($art_item)) {
                continue;
            }
            $art_ghost = trim((string) ($art_item['ghost'] ?? ''));
            $art_title = trim((string) ($art_item['title'] ?? ''));
            $art_text  = trim((string) ($art_item['text'] ?? $art_item['description'] ?? ''));
            $art_image = $image_id($art_item['image'] ?? 0);
            $art_link  = $link_data($art_item['link'] ?? []);
            if ($art_title === '' && $art_text === '' && $art_image < 1) {
                continue;
            }
            ?>
            <section class="nh-art-feature<?php echo $art_index % 2 === 1 ? ' nh-art-feature--reverse' : ''; ?>">
                <div class="nh-art-feature__copy">
                    <div class="u-flex-col u-gap-13">
                        <?php if (!empty($art_item['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $art_item['eyebrow']); ?></p><?php endif; ?>
                        <?php if ($art_ghost !== '' || $art_title !== '') : ?><h2 class="nh-stack-title"><?php if ($art_ghost !== '') : ?><span class="nh-stack-title__ghost"><?php echo esc_html($art_ghost); ?></span><?php endif; ?><?php if ($art_title !== '') : ?><span class="nh-stack-title__lead"><?php echo esc_html($art_title); ?></span><?php endif; ?></h2><?php endif; ?>
                    </div>
                    <?php if ($art_text !== '') : ?><p class="nh-art-feature__text"><?php echo esc_html($art_text); ?></p><?php endif; ?>
                    <?php if ($art_link['url'] !== '') : ?><a class="nh-cta nh-cta--ink" href="<?php echo esc_url($art_link['url']); ?>"<?php echo $art_link['target'] !== '' ? ' target="' . esc_attr($art_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($art_link['title']); ?><span class="nh-cta__arrow"><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></span></a><?php endif; ?>
                </div>
                <?php if ($art_image > 0) : ?><div class="nh-art-feature__media"><?php echo wp_get_attachment_image($art_image, 'large', false, ['alt' => $art_title, 'loading' => 'lazy']); ?></div><?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php
    $taste_items = is_array($taste_settings['items'] ?? null) ? $taste_settings['items'] : [];
    if ($section_visible($taste_settings) && ($taste_items !== [] || !empty($taste_settings['title']) || !empty($taste_settings['text']))) :
        ?>
        <section class="u-flex-col u-gap-40">
            <div class="nh-container u-flex-col u-gap-24 u-items-center">
                <div class="u-flex-col u-gap-13 u-items-center">
                    <?php if (!empty($taste_settings['eyebrow'])) : ?><p class="nh-eyebrow"><?php echo esc_html((string) $taste_settings['eyebrow']); ?></p><?php endif; ?>
                    <?php if (!empty($taste_settings['title'])) : ?><h2 class="nh-section-title u-mt-0 u-text-center"><?php echo esc_html((string) $taste_settings['title']); ?></h2><?php endif; ?>
                </div>
                <?php if (!empty($taste_settings['text'])) : ?><p class="nh-taste-lead"><?php echo esc_html((string) $taste_settings['text']); ?></p><?php endif; ?>
            </div>
            <?php if ($taste_items !== []) : ?>
                <div class="nh-taste-row">
                    <?php foreach ($taste_items as $taste_item) :
                        if (!is_array($taste_item)) {
                            continue;
                        }
                        $taste_label = trim((string) ($taste_item['title'] ?? $taste_item['label'] ?? ''));
                        $taste_text  = trim((string) ($taste_item['text'] ?? $taste_item['description'] ?? ''));
                        $taste_image = $image_id($taste_item['image'] ?? 0);
                        if ($taste_label === '' && $taste_text === '' && $taste_image < 1) {
                            continue;
                        }
                        ?>
                        <div class="nh-taste-tile">
                            <?php if ($taste_image > 0) : ?><?php echo wp_get_attachment_image($taste_image, 'large', false, ['alt' => $taste_label, 'loading' => 'lazy']); ?><?php endif; ?>
                            <span class="nh-taste-tile__caption">
                                <?php if ($taste_label !== '') : ?><span class="nh-taste-tile__label"><?php echo esc_html($taste_label); ?></span><?php endif; ?>
                                <?php if ($taste_text !== '') : ?><span class="nh-taste-tile__text"><?php echo esc_html($taste_text); ?></span><?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php
    $contact_form = function_exists('underscores_child_render_cf7') ? underscores_child_render_cf7('contact_form_id') : '';
    if ($section_visible($contact_band_settings) && ($contact_form !== '' || $contact_band_settings !== [])) :
        $contact_image = $image_url($image_id($contact_band_settings['image'] ?? 0), 'full');
        ?>
        <section class="nh-contact-band"<?php if ($contact_image !== '') : ?> style="--nh-contact-band-image:url('<?php echo esc_url($contact_image); ?>')"<?php endif; ?>>
            <div class="nh-contact-band__inner">
                <div class="nh-contact-band__form">
                    <?php if (!empty($contact_band_settings['title'])) : ?><h2 class="nh-contact-band__lead"><?php echo esc_html((string) $contact_band_settings['title']); ?></h2><?php endif; ?>
                    <?php if ($contact_form !== '') : ?><div class="nh-contact-form"><?php echo $contact_form; ?></div><?php endif; ?>
                </div>
                <div class="nh-contact-band__aside">
                    <?php if (!empty($contact_band_settings['aside_title'])) : ?><h2 class="nh-contact-band__title"><?php echo esc_html((string) $contact_band_settings['aside_title']); ?></h2><?php endif; ?>
                    <?php if (!empty($contact_band_settings['aside_text'])) : ?><p class="nh-contact-band__body"><?php echo esc_html((string) $contact_band_settings['aside_text']); ?></p><?php endif; ?>
                    <?php if (!empty($contact_band_settings['details']) && is_array($contact_band_settings['details'])) : ?>
                        <div class="nh-contact-band__details">
                            <?php foreach ($contact_band_settings['details'] as $detail) : $detail_text = is_array($detail) ? trim((string) ($detail['text'] ?? '')) : trim((string) $detail); if ($detail_text !== '') : ?><p><?php echo esc_html($detail_text); ?></p><?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $partners = is_array($partners_settings['items'] ?? null) ? $partners_settings['items'] : [];
    if ($partners === [] && function_exists('underscores_get_option')) {
        $partners = underscores_get_option('partners', []);
        $partners = is_array($partners) ? $partners : [];
    }
    $events_query = new WP_Query([
        'post_type'              => 'event',
        'post_status'            => 'publish',
        'posts_per_page'         => 3,
        'ignore_sticky_posts'    => true,
        'meta_key'               => 'event_date',
        'orderby'                => 'meta_value',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'update_post_term_cache' => false,
    ]);
    if (($section_visible($partners_settings) && $partners !== []) || ($section_visible($events_settings) && $events_query->have_posts())) :
        $partners_link = $link_data($partners_settings['link'] ?? []);
        $events_link   = $link_data($events_settings['link'] ?? []);
        ?>
        <section class="nh-container home-partners-events">
            <div class="nh-two-col">
                <?php if ($section_visible($partners_settings) && $partners !== []) : ?>
                    <div>
                        <div class="nh-col-head">
                            <?php if (!empty($partners_settings['title'])) : ?><h2><?php echo esc_html((string) $partners_settings['title']); ?></h2><?php endif; ?>
                            <?php if ($partners_link['url'] !== '') : ?><a class="nh-text-link" href="<?php echo esc_url($partners_link['url']); ?>"<?php echo $partners_link['target'] !== '' ? ' target="' . esc_attr($partners_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($partners_link['title']); ?><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></a><?php endif; ?>
                        </div>
                        <div class="nh-partner-grid u-mt-32">
                            <?php foreach ($partners as $partner) :
                                if (!is_array($partner)) {
                                    continue;
                                }
                                $partner_image = $image_id($partner['image'] ?? $partner['logo'] ?? 0);
                                $partner_link  = $link_data($partner['link'] ?? $partner['url'] ?? []);
                                $partner_name  = trim((string) ($partner['title'] ?? $partner['name'] ?? ''));
                                if ($partner_image < 1 && $partner_name === '') {
                                    continue;
                                }
                                ?>
                                <?php if ($partner_link['url'] !== '') : ?><a class="nh-partner" href="<?php echo esc_url($partner_link['url']); ?>"<?php echo $partner_link['target'] !== '' ? ' target="' . esc_attr($partner_link['target']) . '" rel="noopener"' : ''; ?> aria-label="<?php echo esc_attr($partner_name); ?>"><?php if ($partner_image > 0) : ?><?php echo wp_get_attachment_image($partner_image, 'medium', false, ['alt' => $partner_name, 'loading' => 'lazy']); ?><?php endif; ?></a><?php else : ?><span class="nh-partner" aria-label="<?php echo esc_attr($partner_name); ?>"><?php if ($partner_image > 0) : ?><?php echo wp_get_attachment_image($partner_image, 'medium', false, ['alt' => $partner_name, 'loading' => 'lazy']); ?><?php endif; ?></span><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($section_visible($events_settings) && $events_query->have_posts()) : ?>
                    <div>
                        <div class="nh-col-head">
                            <?php if (!empty($events_settings['title'])) : ?><h2><?php echo esc_html((string) $events_settings['title']); ?></h2><?php endif; ?>
                            <?php if ($events_link['url'] !== '') : ?><a class="nh-text-link" href="<?php echo esc_url($events_link['url']); ?>"<?php echo $events_link['target'] !== '' ? ' target="' . esc_attr($events_link['target']) . '" rel="noopener"' : ''; ?>><?php echo esc_html($events_link['title']); ?><svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg></a><?php endif; ?>
                        </div>
                        <div class="nh-event-grid u-mt-32">
                            <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
                                <?php get_template_part('partials/components/card-event', null, ['post_id' => get_the_ID()]); ?>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    endif;
    wp_reset_postdata();
    ?>
</div>

<?php get_footer();
