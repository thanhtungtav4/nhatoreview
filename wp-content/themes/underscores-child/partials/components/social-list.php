<?php
/**
 * Danh sách mạng xã hội — dùng chung footer + menu mobile.
 * Data: Theme Settings → tab Mạng xã hội (social_links).
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$socials = underscores_get_option('social_links', []);

if (! $socials) {
    return;
}
?>
<div class="social-block">
    <div class="social-list">
        <?php foreach ($socials as $social) : ?>
            <?php
            $url   = $social['url'] ?? '';
            $icon  = (int) ($social['icon'] ?? 0);
            $label = $social['platform'] ?? '';

            if (! $url || ! $icon) {
                continue;
            }

            // Có tên → link mang aria-label, icon là trang trí (alt rỗng); không có → giữ alt của Media.
            $img_attrs = $label ? ['alt' => '', 'loading' => 'lazy'] : ['loading' => 'lazy'];
            ?>
            <a class="social-link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"<?php echo $label ? ' aria-label="' . esc_attr($label) . '"' : ''; ?>>
                <?php echo wp_get_attachment_image($icon, 'full', false, $img_attrs); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
