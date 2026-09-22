<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$contact           = underscores_get_option('footer_contact_section', []);
$contact_items     = (array) ($contact['items'] ?? []);
$footer_content    = underscores_get_option('footer_content', []);
$newsletter_form   = underscores_child_render_cf7('newsletter_form_id');
$logo_id           = (int) get_theme_mod('custom_logo');
$explore_location  = has_nav_menu('footer-explore-menu') ? 'footer-explore-menu' : 'footer-menu';
$explore_title     = $explore_location === 'footer-menu' ? wp_get_nav_menu_name('footer-menu') : wp_get_nav_menu_name('footer-explore-menu');
$support_location  = has_nav_menu('footer-support-menu') ? 'footer-support-menu' : '';
?>
</main>
<footer class="nh-footer">
    <div class="nh-footer__main nh-container">
        <div class="nh-footer__intro">
            <a class="nh-footer__brand-box" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> — Trang chủ">
                <?php if ($logo_id > 0) : ?>
                    <?php echo wp_get_attachment_image($logo_id, 'full', false, ['alt' => get_bloginfo('name')]); ?>
                <?php else : ?>
                    <span><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>
            <?php if (!empty($footer_content['description'])) : ?>
                <p class="nh-footer__description"><?php echo esc_html($footer_content['description']); ?></p>
            <?php endif; ?>
            <div class="nh-footer__follow">
                <p class="nh-footer__label"><?php esc_html_e('Follow us on', 'underscores'); ?></p>
                <?php get_template_part('partials/components/social-list', null, ['class' => 'nh-social--tight']); ?>
            </div>
        </div>

        <?php if ($explore_location && has_nav_menu($explore_location)) : ?>
            <div class="nh-footer__column">
                <h2 class="nh-footer__heading"><?php echo esc_html($explore_title); ?></h2>
                <?php
                wp_nav_menu([
                    'theme_location' => $explore_location,
                    'container'      => false,
                    'menu_class'     => 'nh-footer__links',
                    'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ]);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($support_location !== '') : ?>
            <div class="nh-footer__column">
                <h2 class="nh-footer__heading"><?php echo esc_html(wp_get_nav_menu_name($support_location)); ?></h2>
                <?php
                wp_nav_menu([
                    'theme_location' => $support_location,
                    'container'      => false,
                    'menu_class'     => 'nh-footer__links',
                    'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                ]);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($contact_items !== []) : ?>
            <address class="nh-footer__column nh-footer__contact">
                <?php if (!empty($contact['title'])) : ?>
                    <h2 class="nh-footer__heading"><?php echo esc_html($contact['title']); ?></h2>
                <?php endif; ?>
                <?php foreach ($contact_items as $item) : ?>
                    <?php
                    $link = is_array($item['link'] ?? null) ? $item['link'] : [];
                    $text = trim((string) ($item['text'] ?? ''));

                    if ($text === '') {
                        $text = trim((string) ($link['title'] ?? ''));
                    }

                    if ($text === '') {
                        continue;
                    }
                    ?>
                    <p>
                        <?php if (!empty($link['url'])) : ?>
                            <?php echo underscores_child_acf_link($link, esc_html($text)); // phpcs:ignore WordPress.Security.EscapeOutput -- helper escapes URL and caller escapes text. ?>
                        <?php else : ?>
                            <?php echo esc_html($text); ?>
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </address>
        <?php endif; ?>

        <?php if ($newsletter_form !== '') : ?>
            <div class="nh-footer__newsletter">
                <?php if (!empty($footer_content['newsletter_title'])) : ?>
                    <h2 class="nh-footer__newsletter-title"><?php echo esc_html($footer_content['newsletter_title']); ?></h2>
                <?php endif; ?>
                <?php if (!empty($footer_content['newsletter_copy'])) : ?>
                    <p class="nh-footer__newsletter-copy"><?php echo esc_html($footer_content['newsletter_copy']); ?></p>
                <?php endif; ?>
                <div class="nh-newsletter">
                    <?php echo $newsletter_form; // phpcs:ignore WordPress.Security.EscapeOutput -- Contact Form 7 returns trusted form markup. ?>
                </div>
                <?php if (!empty($footer_content['credit'])) : ?>
                    <p class="nh-footer__credit"><?php echo esc_html($footer_content['credit']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="nh-container nh-footer__copyright">
        <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
