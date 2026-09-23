<?php
declare(strict_types=1);
defined('ABSPATH') || exit;

$page_fields = function_exists('get_fields') ? (get_fields() ?: []) : [];
$_page_sections = [
    'banner' => is_array($page_fields['banner_settings'] ?? null) ? $page_fields['banner_settings'] : [],
    'topics' => is_array($page_fields['topics_settings'] ?? null) ? $page_fields['topics_settings'] : [],
    'related' => is_array($page_fields['related_settings'] ?? null) ? $page_fields['related_settings'] : [],
];
$section_enabled = 'underscores_child_section_is_visible';
$banner_settings = $_page_sections['banner'];
$topics_settings = $_page_sections['topics'];

$related_settings = $_page_sections['related'];

// Expected ACF sections: banner_settings, topics_settings, related_settings.
$image_url = 'underscores_child_acf_image_url';

$related_query = underscores_child_related_posts_query($related_settings, 'nghe-thuat');

$hero_image = $image_url($banner_settings['image'] ?? get_post_thumbnail_id());
$hero_title = trim((string) ($banner_settings['title'] ?? ''));
$hero_title = $hero_title !== '' ? $hero_title : trim((string) get_the_title());
$hero_lead  = trim((string) ($banner_settings['lead'] ?? ''));
if ($section_enabled($banner_settings) && ($hero_image !== '' || $hero_lead !== '' || $hero_title !== '')) :
    ?>
    <section class="nh-hero nh-hero--section">
        <?php if ($hero_image !== '') : ?><div class="nh-hero__media"><img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high"></div><?php endif; ?>
        <div class="nh-hero__overlay"></div>
        <div class="nh-hero__inner nh-container">
            <?php if ($hero_title !== '') : ?><h1 class="nh-hero__title"><?php echo wp_kses_post($hero_title); ?></h1><?php endif; ?>
            <?php if ($hero_lead !== '') : ?><p class="nh-hero__lead"><?php echo esc_html($hero_lead); ?></p><?php endif; ?>
        </div>
    </section>
    <?php
endif;

$features = is_array($topics_settings['items'] ?? null) ? array_values(array_filter(
    $topics_settings['items'],
    static fn ($feature): bool => is_array($feature) && array_filter(
        $feature,
        static fn ($value): bool => $value !== '' && $value !== [] && $value !== null && $value !== 0 && $value !== false
    ) !== []
)) : [];
$features_title = trim((string) ($topics_settings['title'] ?? ''));
if ($section_enabled($topics_settings) && $features !== []) :
    ?>
    <section class="section-head">
        <div class="nh-container nh-rule-head">
            <?php if ($features_title !== '') : ?><h2 class="nh-rule-head__title"><?php echo esc_html($features_title); ?></h2><?php endif; ?>
            <span class="nh-rule-head__line"></span>
            <a class="nh-rule-head__action" href="#chu-de-01">
                <span><?php echo esc_html(sprintf(_n('%d chủ đề', '%d chủ đề', count($features), 'underscores-child'), count($features))); ?></span>
                <?php echo underscores_child_icon_mask('icon_arrow_right'); ?>
            </a>
        </div>
    </section>
    <?php foreach ($features as $index => $feature) :
        $media = $image_url($feature['image'] ?? '');
        $feature_id = sprintf('chu-de-%02d', $index + 1);
        ?>
        <section class="nh-feature<?php echo $index % 2 === 1 ? ' nh-feature--reverse' : ''; ?>" id="<?php echo esc_attr($feature_id); ?>">
            <div class="nh-feature__row">
                <div class="nh-feature__copy">
                    <p class="nh-feature__number"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></p>
                    <?php if (!empty($feature['title'])) : ?><h3 class="nh-feature__title"><?php echo wp_kses_post((string) $feature['title']); ?></h3><?php endif; ?>
                    <span class="nh-feature__rule"></span>
                    <?php if (!empty($feature['text'])) : ?><p class="nh-feature__text"><?php echo esc_html((string) $feature['text']); ?></p><?php endif; ?>
                    <?php if (!empty($feature['link'])) : ?>
                        <?php echo underscores_child_acf_link($feature['link'], '<span>' . esc_html__('Xem tất cả', 'underscores-child') . '</span><span class="nh-cta__arrow" aria-hidden="true">' . underscores_child_icon_mask('icon_arrow_right') . '</span>', 'nh-cta nh-cta--ink'); ?>
                    <?php endif; ?>
                </div>
                <?php if ($media !== '') : ?><div class="nh-feature__media"><img src="<?php echo esc_url($media); ?>" alt="<?php echo esc_attr(wp_strip_all_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', (string) ($feature['title'] ?? '')))); ?>" loading="lazy"></div><?php endif; ?>
            </div>
            <?php if (!empty($feature['stats']) && is_array($feature['stats'])) : ?>
                <div class="nh-feature__foot">
                    <hr class="nh-hairline">
                    <div class="nh-statbar">
                        <div class="nh-statbar__group">
                            <?php foreach ($feature['stats'] as $stat) :
                                if (!is_array($stat) || (empty($stat['value']) && empty($stat['label']))) {
                                    continue;
                                }
                                $icon_id = absint($stat['icon'] ?? 0);
                                $value = trim((string) ($stat['value'] ?? ''));
                                $label = trim((string) ($stat['label'] ?? ''));
                                ?>
                                <span class="nh-stat">
                                    <?php if ($icon_id > 0) : ?><?php echo wp_get_attachment_image($icon_id, 'thumbnail', false, ['alt' => '', 'loading' => 'lazy']); ?><?php endif; ?>
                                    <?php echo esc_html(trim($value . ' ' . $label)); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php
                        $share_link_url = is_array($feature['share_link'] ?? null) ? trim((string) ($feature['share_link']['url'] ?? '')) : '';
                        if ($share_link_url !== '' && $share_link_url[0] !== '#') :
                            ?>
                            <?php echo underscores_child_acf_link($feature['share_link'], underscores_child_icon_mask('icon_share') . '<span>' . esc_html__('Chia sẻ', 'underscores-child') . '</span>', 'nh-stat'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php
endif;


if ($section_enabled($related_settings)) { underscores_child_render_related_posts($related_query); }
