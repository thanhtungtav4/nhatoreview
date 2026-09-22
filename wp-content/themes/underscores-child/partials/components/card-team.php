<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args     = is_array($args ?? null) ? $args : [];
$name     = trim((string) ($args['name'] ?? ''));
$role     = trim((string) ($args['role'] ?? ''));
$photo_id = absint($args['photo_id'] ?? 0);
$image    = $photo_id > 0 ? wp_get_attachment_image($photo_id, 'medium_large', false, ['alt' => $name, 'loading' => 'lazy']) : '';

if ($name === '' || $role === '' || $photo_id < 1 || $image === '') {
    return;
}
?>
<article class="nh-team-card">
    <?php echo $image; ?>
    <span class="nh-team-card__body">
        <span class="nh-team-card__name"><?php echo esc_html($name); ?></span>
        <span class="nh-team-card__role"><?php echo esc_html($role); ?></span>
    </span>
</article>
