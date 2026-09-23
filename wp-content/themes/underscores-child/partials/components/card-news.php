<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args    = is_array($args ?? null) ? $args : [];
$post_id = absint($args['post_id'] ?? get_the_ID());

if ($post_id < 1) {
    return;
}

$title        = trim((string) get_the_title($post_id));
$permalink    = get_the_permalink($post_id);
$thumbnail_id = absint($args['thumbnail_id'] ?? get_post_thumbnail_id($post_id));
$excerpt      = trim((string) ($args['excerpt'] ?? get_the_excerpt($post_id)));
$date         = trim((string) ($args['date'] ?? get_the_date('d/m/Y', $post_id)));
$image        = $thumbnail_id > 0 ? wp_get_attachment_image($thumbnail_id, 'medium_large', false, ['alt' => '', 'loading' => 'lazy']) : '';

if ($title === '' || $permalink === '' || $thumbnail_id < 1 || $excerpt === '' || $date === '' || $image === '') {
    return;
}
?>
<a class="nh-news-card" href="<?php echo esc_url($permalink); ?>">
    <?php echo $image; ?>
    <span class="nh-news-card__meta">
        <span class="nh-dateline">
            <?php echo underscores_child_icon('icon_clock'); ?>
            <span><strong><?php esc_html_e('Ngày đăng:', 'underscores-child'); ?></strong> <?php echo esc_html($date); ?></span>
        </span>
    </span>
    <span class="nh-news-card__title"><?php echo esc_html($title); ?></span>
    <span class="nh-news-card__excerpt"><?php echo esc_html($excerpt); ?></span>
</a>
