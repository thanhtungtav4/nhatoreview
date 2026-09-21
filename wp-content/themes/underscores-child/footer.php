<?php

/**
 * The template for displaying footer.
 *
 * Skeleton chung: cột liên hệ (Theme Settings) + menu footer (WP menu) + bản quyền (WP core).
 *
 * @package Underscores
 */

if (!defined('ABSPATH')) {
    die();
}

$contact       = underscores_get_option('footer_contact_section', []);
$contact_items = $contact['items'] ?? [];
?>
    </main>
    <footer class="ft">
      <div class="container">
        <div class="ft-list row">
          <div class="ft-col col-6">
            <div class="content-info">
              <?php if (! empty($contact['title'])) : ?>
                <p class="tt"><?php echo esc_html($contact['title']); ?></p>
              <?php endif; ?>
              <?php if ($contact_items) : ?>
                <ul class="menu-list">
                  <?php foreach ($contact_items as $item) : ?>
                    <?php
                    $link = $item['link'] ?? null;
                    $text = ($item['text'] ?? '') ?: (is_array($link) ? ($link['title'] ?? '') : '');

                    if ($text === '') {
                        continue;
                    }
                    ?>
                    <li class="menu-item"><?php echo underscores_child_acf_link($link, esc_html($text), 'menu-link'); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <div class="ft-social">
                <?php get_template_part('partials/components/social-list'); ?>
              </div>
            </div>
          </div>
          <?php if (has_nav_menu('footer-menu')) : ?>
            <div class="ft-col col-6">
              <div class="content-info">
                <p class="tt"><?php echo esc_html(wp_get_nav_menu_name('footer-menu')); ?></p>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer-menu',
                    'container'      => false,
                    'menu_class'     => 'menu-list',
                    'depth'          => 1,
                    'fallback_cb'    => false,
                    'walker'         => new \Theme\Nav\MenuWalker(),
                ]);
                ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="ft-copyright">
          <p class="txt">&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?></p>
        </div>
      </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
