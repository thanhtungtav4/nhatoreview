<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$args         = is_array($args ?? null) ? $args : [];
$content_type = sanitize_key((string) ($args['content_type'] ?? get_post_type()));
$post_id      = absint(get_the_ID());

$definitions = [
    'project' => [
        'label'         => __('Dự án', 'underscores'),
        'archive'       => get_post_type_archive_link('project'),
        'location_field'=> 'project_location',
        'gallery_field' => 'project_gallery',
    ],
    'portfolio' => [
        'label'         => __('Portfolio', 'underscores'),
        'archive'       => get_post_type_archive_link('portfolio'),
        'gallery_field' => 'portfolio_gallery',
    ],
    'event' => [
        'label'         => __('Sự kiện', 'underscores'),
        'archive'       => get_post_type_archive_link('event'),
    ],
];

if ($post_id < 1 || !isset($definitions[$content_type])) {
    return;
}

$definition = $definitions[$content_type];
$get_field  = static function (string $name) use ($post_id) {
    return function_exists('get_field') ? get_field($name, $post_id) : null;
};
$image_id = static function ($image): int {
    if (is_array($image)) {
        return absint($image['ID'] ?? $image['id'] ?? 0);
    }

    return absint($image);
};

$title               = trim((string) get_the_title($post_id));
$lead                = trim((string) get_the_excerpt($post_id));
$featured_image_id   = get_post_thumbnail_id($post_id);
$metadata            = [];
$gallery_ids         = [];
$external_link       = '';
$event_registration  = [];

if (!empty($definition['location_field'])) {
    $location = trim((string) $get_field($definition['location_field']));
    if ($location !== '') {
        $metadata[] = [
            'label' => __('Địa điểm', 'underscores'),
            'value' => $location,
        ];
    }
}

if ($content_type === 'project') {
    $terms = get_the_terms($post_id, 'project_category');
    if (is_array($terms)) {
        $term_names = array_values(array_filter(array_map(
            static fn ($term): string => $term instanceof WP_Term ? trim($term->name) : '',
            $terms
        )));
        if ($term_names !== []) {
            $metadata[] = [
                'label' => __('Loại dự án', 'underscores'),
                'value' => implode(', ', $term_names),
            ];
        }
    }
}

if ($content_type === 'portfolio') {
    $terms = get_the_terms($post_id, 'portfolio_category');
    if (is_array($terms)) {
        $term_names = array_values(array_filter(array_map(
            static fn ($term): string => $term instanceof WP_Term ? trim($term->name) : '',
            $terms
        )));
        if ($term_names !== []) {
            $metadata[] = [
                'label' => __('Danh mục', 'underscores'),
                'value' => implode(', ', $term_names),
            ];
        }
    }

    $external_link = trim((string) $get_field('portfolio_external_link'));
}

if ($content_type === 'event') {
    $event_date = trim((string) $get_field('event_date'));
    $date_stamp = $event_date !== '' ? strtotime($event_date) : false;
    if ($date_stamp !== false) {
        $metadata[] = [
            'label' => __('Ngày diễn ra', 'underscores'),
            'value' => wp_date('d/m/Y', $date_stamp),
        ];
    }

    $event_time = trim((string) $get_field('event_time'));
    if ($event_time !== '') {
        $metadata[] = [
            'label' => __('Thời gian', 'underscores'),
            'value' => $event_time,
        ];
    }

    $event_location = trim((string) $get_field('event_location'));
    if ($event_location !== '') {
        $metadata[] = [
            'label' => __('Địa điểm', 'underscores'),
            'value' => $event_location,
        ];
    }

    $event_registration = $get_field('event_registration');
    $event_registration = is_array($event_registration) ? $event_registration : [];
}

if (!empty($definition['gallery_field'])) {
    $gallery = $get_field($definition['gallery_field']);
    $gallery = is_array($gallery) ? $gallery : ($gallery ? [$gallery] : []);
    foreach ($gallery as $image) {
        $attachment_id = $image_id($image);
        if ($attachment_id > 0) {
            $gallery_ids[] = $attachment_id;
        }
    }
    $gallery_ids = array_values(array_unique($gallery_ids));
}

underscores_child_set_main_class('page-single-content page-' . $content_type);
get_header();
?>
<div class="page-single-content">
    <div class="nh-container">
        <article class="single-content">
            <div class="article__top">
                <nav class="article__crumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'underscores'); ?>">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Trang chủ', 'underscores'); ?></a>
                    <?php if (!empty($definition['archive'])) : ?>
                        <a href="<?php echo esc_url((string) $definition['archive']); ?>"><?php echo esc_html((string) $definition['label']); ?></a>
                    <?php endif; ?>
                </nav>
                <span class="nh-dateline">
                    <?php echo underscores_child_icon('icon_clock_sm'); ?>
                    <span><strong><?php esc_html_e('Ngày đăng:', 'underscores'); ?></strong> <?php echo esc_html(get_the_date('d/m/Y', $post_id)); ?></span>
                </span>
            </div>

            <h1 class="article__title"><?php echo esc_html($title); ?></h1>

            <?php if ($lead !== '') : ?>
                <p class="article__lead"><?php echo wp_kses_post($lead); ?></p>
            <?php endif; ?>

            <?php if ($featured_image_id > 0) : ?>
                <figure class="single-content__hero">
                    <?php echo wp_get_attachment_image($featured_image_id, 'full', false, ['alt' => $title, 'loading' => 'eager']); ?>
                </figure>
            <?php endif; ?>

            <div class="article__body">
                <?php if ($metadata !== []) : ?>
                    <div class="single-content__meta" aria-label="<?php esc_attr_e('Thông tin chi tiết', 'underscores'); ?>">
                        <?php foreach ($metadata as $item) : ?>
                            <span class="single-content__meta-item">
                                <strong><?php echo esc_html((string) $item['label']); ?></strong>
                                <span><?php echo esc_html((string) $item['value']); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php the_content(); ?>

                <?php if ($external_link !== '') : ?>
                    <a class="nh-cta nh-cta--ink single-content__cta" href="<?php echo esc_url($external_link); ?>" target="_blank" rel="noopener">
                        <?php esc_html_e('Xem dự án bên ngoài', 'underscores'); ?>
                        <span class="nh-cta__arrow"><?php echo underscores_child_icon_mask('icon_arrow_right'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($event_registration !== [] && !empty($event_registration['url'])) : ?>
                    <?php $registration_target = trim((string) ($event_registration['target'] ?? '')); ?>
                    <a class="nh-cta nh-cta--ink single-content__cta" href="<?php echo esc_url((string) $event_registration['url']); ?>"<?php echo $registration_target !== '' ? ' target="' . esc_attr($registration_target) . '" rel="noopener"' : ''; ?>>
                        <?php echo esc_html((string) ($event_registration['title'] ?? __('Đăng ký', 'underscores'))); ?>
                        <span class="nh-cta__arrow"><?php echo underscores_child_icon_mask('icon_arrow_right'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($gallery_ids !== []) : ?>
                    <section class="single-content__gallery" aria-label="<?php esc_attr_e('Thư viện hình ảnh', 'underscores'); ?>">
                        <?php foreach ($gallery_ids as $gallery_id) : ?>
                            <?php
                            $caption = trim((string) wp_get_attachment_caption($gallery_id));
                            $image   = wp_get_attachment_image($gallery_id, 'large', false, ['alt' => $title, 'loading' => 'lazy']);
                            if ($image === '') {
                                continue;
                            }
                            ?>
                            <figure>
                                <?php echo $image; ?>
                                <?php if ($caption !== '') : ?><figcaption><?php echo esc_html($caption); ?></figcaption><?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </div>
</div>
<?php get_footer(); ?>
