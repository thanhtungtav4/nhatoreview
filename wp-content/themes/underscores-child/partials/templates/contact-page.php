<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'close' => is_array($page_fields['close_settings'] ?? null) ? $page_fields['close_settings'] : [],
    'info' => is_array($page_fields['info_settings'] ?? null) ? $page_fields['info_settings'] : [],
    'form' => is_array($page_fields['form_settings'] ?? null) ? $page_fields['form_settings'] : [],
    'map' => is_array($page_fields['map_settings'] ?? null) ? $page_fields['map_settings'] : [],
];
$section_enabled = 'underscores_child_section_is_visible';
$banner_settings = $_page_sections['banner'];
$close_settings = $_page_sections['close'];
$info_settings = $_page_sections['info'];
$form_settings = $_page_sections['form'];
$map_settings = $_page_sections['map'];

// Expected ACF sections: banner_settings, close_settings, info_settings,
// form_settings, map_settings.
$image_url = 'underscores_child_acf_image_url';
$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
$hero_lead = (string) ($banner_settings['lead'] ?? '');
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '')) : ?><section class="nh-hero nh-hero--section"><?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?><div class="nh-hero__overlay"></div><div class="nh-hero__inner nh-container"><?php if (get_the_title() !== '') : ?><h1 class="nh-hero__title"><?php the_title(); ?></h1><?php endif; ?><?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?></div></section><?php endif;

$close_title = (string) ($close_settings['title'] ?? '');
$close_lead = (string) ($close_settings['lead'] ?? '');
$close_link = $close_settings['link'] ?? null;
if ($section_enabled($close_settings) && ($close_title !== '' || $close_lead !== '' || $close_link !== null)) : ?><section class="nh-close-band"><div class="nh-close-band__inner nh-container"><?php if ($close_title !== '') : ?><h2 class="nh-close-band__title"><?php echo wp_kses_post($close_title); ?></h2><?php endif; ?><?php if ($close_lead !== '') : ?><p class="nh-close-band__lead"><?php echo esc_html($close_lead); ?></p><?php endif; ?><?php if (is_array($close_link) && !empty($close_link['url'])) : ?><?php echo underscores_child_acf_link($close_link, '<span>' . esc_html((string) ($close_link['title'] ?? '')) . '</span><span aria-hidden="true">→</span>', 'nh-close-band__action'); ?><?php endif; ?></div></section><?php endif;

$contact_info = is_array($info_settings['items'] ?? null) ? $info_settings['items'] : [];
$contact_image = $image_url($info_settings['image'] ?? '');
$contact_info_title = (string) ($info_settings['title'] ?? '');
$contact_form_title = (string) ($form_settings['title'] ?? '');
$form_html = function_exists('underscores_child_render_cf7') ? underscores_child_render_cf7('contact_form_id') : '';
$map_title = (string) ($map_settings['title'] ?? '');
$map_url = (string) ($map_settings['url'] ?? '');
$show_info = $section_enabled($info_settings) && ($contact_info !== [] || $contact_image !== '' || $contact_info_title !== '');
$show_form = $section_enabled($form_settings) && ($form_html !== '' || $contact_form_title !== '');
$show_map = $section_enabled($map_settings) && $map_url !== '';
if ($show_info || $show_form || $show_map) : ?>
    <section class="section"><div class="nh-container"><?php if ($show_info || $show_form) : ?><div class="nh-enquire">
        <?php if ($show_info) : ?><div class="nh-enquire__info"><?php if ($contact_image !== '') : ?><img src="<?php echo esc_url($contact_image); ?>" alt=""><?php endif; ?><div class="nh-enquire__copy"><?php if ($contact_info_title !== '') : ?><h2 class="nh-enquire__heading"><?php echo esc_html($contact_info_title); ?></h2><?php endif; ?><div class="nh-enquire__details"><?php foreach ($contact_info as $item) : if (!is_array($item) || (empty($item['label']) && empty($item['value']))) { continue; } ?><p><?php if (!empty($item['label'])) : ?><strong><?php echo esc_html((string) $item['label']); ?></strong><?php endif; ?><?php if (!empty($item['value'])) : ?> <?php echo esc_html((string) $item['value']); ?><?php endif; ?></p><?php endforeach; ?></div></div></div><?php endif; ?>
        <?php if ($show_form) : ?><div id="contact-form" class="nh-enquire__form"><?php if ($contact_form_title !== '') : ?><h2 class="nh-enquire__heading"><?php echo esc_html($contact_form_title); ?></h2><?php endif; ?><?php if ($form_html !== '') : ?><div class="nh-contact-form"><?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></div><?php endif; ?>
    </div><?php endif; ?><?php if ($show_map) : ?><iframe class="contact__map" title="<?php echo esc_attr($map_title); ?>" src="<?php echo esc_url($map_url); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe><?php endif; ?></div></section>
<?php endif;
