<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

$args       = is_array($args ?? null) ? $args : [];
$extra_class = trim((string) ($args['class'] ?? ''));
$limit      = absint($args['limit'] ?? 0);
$socials    = underscores_get_option('social_links', []);

if (!is_array($socials) || $socials === []) {
    return;
}

if ($limit > 0) {
    $socials = array_slice($socials, 0, $limit);
}
?>
<div class="nh-social<?php echo $extra_class !== '' ? ' ' . esc_attr($extra_class) : ''; ?>" aria-label="<?php esc_attr_e('Mạng xã hội', 'underscores'); ?>">
    <?php foreach ($socials as $social) : ?>
        <?php
        $url   = trim((string) ($social['url'] ?? ''));
        $icon  = absint($social['icon'] ?? 0);
        $label = trim((string) ($social['platform'] ?? ''));

        if ($label === '' && $url !== '') {
            $host  = parse_url($url, PHP_URL_HOST);
            $label = is_string($host) ? preg_replace('/^www\./', '', $host) : '';
        }

        if ($url === '' || $icon < 1) {
            continue;
        }
        ?>
        <a class="nh-social__link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"<?php echo $label !== '' ? ' aria-label="' . esc_attr($label) . '"' : ''; ?>>
            <?php echo wp_get_attachment_image($icon, 'full', false, ['alt' => '', 'loading' => 'lazy', 'class' => 'nh-icon']); ?>
        </a>
    <?php endforeach; ?>
</div>
