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
$text         = trim((string) ($args['text'] ?? get_the_excerpt($post_id)));
$date         = trim((string) ($args['date'] ?? get_the_date('', $post_id)));
$views        = trim((string) ($args['views'] ?? ''));
$image        = $thumbnail_id > 0 ? wp_get_attachment_image($thumbnail_id, 'medium_large', false, ['alt' => $title, 'loading' => 'lazy']) : '';

if ($title === '' || $permalink === '' || $thumbnail_id < 1 || $text === '' || $date === '' || $image === '') {
    return;
}
?>
<a class="nh-post-card" href="<?php echo esc_url($permalink); ?>">
    <span class="nh-post-card__image">
        <?php echo $image; ?>
    </span>
    <span class="nh-post-card__body">
        <span class="nh-statbar__group">
            <?php if ($views !== '') : ?>
                <span class="nh-stat">
                    <svg aria-hidden="true"><use href="#nh-eye"></use></svg>
                    <?php echo esc_html($views); ?>
                </span>
            <?php endif; ?>
            <span class="nh-stat">
                <svg aria-hidden="true"><use href="#nh-calendar"></use></svg>
                <?php echo esc_html($date); ?>
            </span>
        </span>
        <span class="nh-post-card__title"><?php echo esc_html($title); ?></span>
        <span class="nh-post-card__text"><?php echo esc_html($text); ?></span>
    </span>
</a>
