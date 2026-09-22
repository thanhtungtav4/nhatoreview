<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args    = is_array($args ?? null) ? $args : [];
$post_id = absint($args['post_id'] ?? get_the_ID());

if ($post_id < 1) {
    return;
}

$title        = trim((string) ($args['title'] ?? get_the_title($post_id)));
$excerpt      = trim((string) ($args['text'] ?? get_the_excerpt($post_id)));
$event_date   = trim((string) ($args['event_date'] ?? get_field('event_date', $post_id)));
$registration = $args['event_registration'] ?? get_field('event_registration', $post_id);
$registration = is_array($registration) ? $registration : [];
$register_url = trim((string) ($registration['url'] ?? ''));
$register_text = trim((string) ($registration['title'] ?? ''));
$date_stamp   = $event_date !== '' ? strtotime($event_date) : false;

if ($title === '' || $excerpt === '' || $register_url === '' || $register_text === '' || $date_stamp === false) {
    return;
}

$target = trim((string) ($registration['target'] ?? ''));
?>
<article class="nh-event-card">
    <span class="nh-event-card__date">
        <strong><?php echo esc_html(wp_date('d', $date_stamp)); ?></strong>
        <span><?php echo esc_html(wp_date('M', $date_stamp)); ?></span>
    </span>
    <span class="nh-event-card__body">
        <span class="nh-event-card__title"><?php echo esc_html($title); ?></span>
        <span class="nh-event-card__text"><?php echo esc_html($excerpt); ?></span>
        <a class="nh-event-card__cta" href="<?php echo esc_url($register_url); ?>"<?php echo $target !== '' ? ' target="' . esc_attr($target) . '" rel="noopener"' : ''; ?>>
            <?php echo esc_html($register_text); ?>
            <svg aria-hidden="true"><use href="#nh-arrow-right"></use></svg>
        </a>
    </span>
</article>
