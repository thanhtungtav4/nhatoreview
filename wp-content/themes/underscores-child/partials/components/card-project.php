<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args    = is_array($args ?? null) ? $args : [];
$post_id = absint($args['post_id'] ?? get_the_ID());

if ($post_id < 1) {
    return;
}

$title        = trim((string) ($args['title'] ?? get_the_title($post_id)));
$permalink    = (string) ($args['permalink'] ?? get_the_permalink($post_id));
$thumbnail_id = absint($args['thumbnail_id'] ?? get_post_thumbnail_id($post_id));
$location     = trim((string) ($args['location'] ?? get_field('project_location', $post_id)));
$image        = $thumbnail_id > 0 ? wp_get_attachment_image($thumbnail_id, 'medium_large', false, ['alt' => $title, 'loading' => 'lazy']) : '';

if ($title === '' || $permalink === '' || $thumbnail_id < 1 || $location === '' || $image === '') {
    return;
}
?>
<a class="nh-project-card" href="<?php echo esc_url($permalink); ?>">
    <?php echo $image; ?>
    <span class="nh-project-card__shade"></span>
    <span class="nh-project-card__copy">
        <strong><?php echo esc_html($title); ?></strong>
        <small><?php echo esc_html($location); ?></small>
    </span>
</a>
