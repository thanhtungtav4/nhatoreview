<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];

// Expected ACF keys: hero_image, hero_lead, contact_info, contact_form_id,
// contact_form_image, map_url, close_title, close_lead, close_link.
$image_url = static function ($image): string {
    $id = is_array($image) ? absint($image['ID'] ?? $image['id'] ?? 0) : absint($image);
    if ($id > 0) { return (string) wp_get_attachment_image_url($id, 'full'); }
    return is_string($image) ? $image : '';
};
$hero_image = $image_url($page_fields['hero_image'] ?? get_post_thumbnail_id());
$hero_lead = (string) ($page_fields['hero_lead'] ?? '');
if ($hero_image !== '' || $hero_lead !== '' || get_the_title() !== '') : ?><section class="nh-hero nh-hero--section"><?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?><div class="nh-hero__overlay"></div><div class="nh-hero__inner nh-container"><?php if (get_the_title() !== '') : ?><h1 class="nh-hero__title"><?php the_title(); ?></h1><?php endif; ?><?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?></div></section><?php endif;

$close_title = (string) ($page_fields['close_title'] ?? '');
$close_lead = (string) ($page_fields['close_lead'] ?? '');
$close_link = $page_fields['close_link'] ?? null;
if ($close_title !== '' || $close_lead !== '' || $close_link !== null) : ?><section class="nh-close-band"><div class="nh-close-band__inner nh-container"><?php if ($close_title !== '') : ?><h2 class="nh-close-band__title"><?php echo wp_kses_post($close_title); ?></h2><?php endif; ?><?php if ($close_lead !== '') : ?><p class="nh-close-band__lead"><?php echo esc_html($close_lead); ?></p><?php endif; ?><?php if (is_array($close_link) && !empty($close_link['url'])) : ?><?php echo underscores_child_acf_link($close_link, '<span>' . esc_html((string) ($close_link['title'] ?? '')) . '</span><span aria-hidden="true">→</span>', 'nh-close-band__action'); ?><?php endif; ?></div></section><?php endif;
$contact_info = is_array($page_fields['contact_info'] ?? null) ? $page_fields['contact_info'] : [];
$contact_image = $image_url($page_fields['contact_form_image'] ?? '');
$form_html = function_exists('underscores_child_render_cf7') ? underscores_child_render_cf7('contact_form_id') : '';
if ($contact_info !== [] || $form_html !== '' || $contact_image !== '' || !empty($page_fields['map_url']) || !empty($page_fields['contact_info_title']) || !empty($page_fields['contact_form_title'])) : ?>
    <section class="section"><div class="nh-container"><div class="nh-enquire">
        <div class="nh-enquire__info"><?php if ($contact_image !== '') : ?><img src="<?php echo esc_url($contact_image); ?>" alt=""><?php endif; ?><div class="nh-enquire__copy"><?php if (!empty($page_fields['contact_info_title'])) : ?><h2 class="nh-enquire__heading"><?php echo esc_html((string) $page_fields['contact_info_title']); ?></h2><?php endif; ?><div class="nh-enquire__details"><?php foreach ($contact_info as $item) : if (!is_array($item) || (empty($item['label']) && empty($item['value']))) { continue; } ?><p><?php if (!empty($item['label'])) : ?><strong><?php echo esc_html((string) $item['label']); ?></strong><?php endif; ?><?php if (!empty($item['value'])) : ?> <?php echo esc_html((string) $item['value']); ?><?php endif; ?></p><?php endforeach; ?></div></div></div>
        <div class="nh-enquire__form"><?php if (!empty($page_fields['contact_form_title'])) : ?><h2 class="nh-enquire__heading"><?php echo esc_html((string) $page_fields['contact_form_title']); ?></h2><?php endif; ?><?php if ($form_html !== '') : ?><div class="nh-contact-form"><?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></div>
    </div><?php if (!empty($page_fields['map_url'])) : ?><iframe class="contact__map" title="<?php echo esc_attr((string) ($page_fields['map_title'] ?? '')); ?>" src="<?php echo esc_url((string) $page_fields['map_url']); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe><?php endif; ?></div></section>
<?php endif;

