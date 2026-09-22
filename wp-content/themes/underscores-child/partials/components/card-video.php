<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args         = is_array($args ?? null) ? $args : [];
$youtube_url  = trim((string) ($args['youtube_url'] ?? ''));
$thumbnail_id = absint($args['thumbnail_id'] ?? 0);
$title        = trim((string) ($args['title'] ?? ''));
$variant      = trim((string) ($args['variant'] ?? ''));
$youtube_id   = '';

if ($youtube_url !== '') {
    $parts = wp_parse_url($youtube_url);
    $host  = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
    $path  = is_array($parts) ? (string) ($parts['path'] ?? '') : '';

    if ($host === 'youtu.be' || str_ends_with($host, '.youtu.be')) {
        $youtube_id = trim((string) strtok(ltrim($path, '/'), '/'));
    } elseif (str_contains($host, 'youtube.com')) {
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $youtube_id = trim((string) ($query['v'] ?? ''));

        if ($youtube_id === '' && preg_match('~/(?:embed|shorts|live)/([^/?]+)~', $path, $matches)) {
            $youtube_id = trim((string) ($matches[1] ?? ''));
        }
    }
}

if ($youtube_url === '' || $youtube_id === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $youtube_id) || $thumbnail_id < 1 || ($variant !== 'about' && $title === '')) {
    return;
}

$image = wp_get_attachment_image($thumbnail_id, 'medium_large', false, ['alt' => $title, 'loading' => 'lazy']);

if ($image === '') {
    return;
}
?>
<?php if ($variant === 'about') : ?>
<a class="nh-video-tile" href="<?php echo esc_url($youtube_url); ?>" data-nh-video="<?php echo esc_attr($youtube_id); ?>">
    <?php echo $image; ?>
    <span class="nh-video-tile__badge">
        <?php echo underscores_child_icon('icon_youtube_badge'); ?>
    </span>
</a>
<?php else : ?>
<a class="nh-video-card" href="<?php echo esc_url($youtube_url); ?>" data-nh-video="<?php echo esc_attr($youtube_id); ?>">
    <span class="nh-video-card__frame">
        <?php echo $image; ?>
        <span class="nh-video-card__badge">
            <?php echo underscores_child_icon('icon_youtube_badge'); ?>
        </span>
    </span>
    <span class="nh-video-card__title"><?php echo esc_html($title); ?></span>
</a>
<?php endif; ?>
