<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('underscores_child_render_flexible_sections')) {
    /**
     * Render Flexible Content: mỗi layout → partials/sections/{layout-kebab}.php.
     *
     * Đọc field 1 lần bằng get_field() (ACF load + format toàn bộ rows), partial nhận data qua $args:
     *   $args = [...sub fields của layout..., 'layout' => 'hero_banner', 'post_id' => 12, 'index' => 0, 'is_first' => true]
     * `is_first` dùng cho ảnh LCP (section đầu): loading eager + fetchpriority high.
     *
     * @return bool true nếu render ít nhất 1 section.
     */
    function underscores_child_render_flexible_sections(string $field_name = 'sections', ?int $post_id = null): bool
    {
        if (!function_exists('get_field')) {
            return false;
        }

        $post_id  = $post_id ?: (int) get_the_ID();
        $sections = $post_id > 0 ? get_field($field_name, $post_id) : null;

        if (!is_array($sections) || $sections === []) {
            return false;
        }

        static $located = []; // layout => có template? (tránh locate_template lặp cho layout trùng)
        $rendered = false;

        foreach (array_values($sections) as $index => $section) {
            $layout = is_array($section) ? (string) ($section['acf_fc_layout'] ?? '') : '';

            if ($layout === '') {
                continue;
            }

            $template_slug = 'partials/sections/' . str_replace('_', '-', $layout);
            $located[$layout] ??= (bool) locate_template($template_slug . '.php', false, false);

            if (!$located[$layout]) {
                continue;
            }

            unset($section['acf_fc_layout']);

            get_template_part($template_slug, null, $section + [
                'layout'   => $layout,
                'post_id'  => $post_id,
                'index'    => $index,
                'is_first' => !$rendered,
            ]);

            $rendered = true;
        }

        return $rendered;
    }
}

if (!function_exists('underscores_child_acf_link')) {
    /**
     * Render ACF link field (return_format array) thành <a>, hoặc <span> nếu không có URL.
     *
     * @param mixed  $link       Giá trị ACF link: ['url','title','target'] | '' | null.
     * @param string $inner_html HTML bên trong — caller PHẢI escape trước.
     * @param string $class      Class cho thẻ bọc.
     */
    function underscores_child_acf_link($link, string $inner_html, string $class = ''): string
    {
        $url        = is_array($link) ? (string) ($link['url'] ?? '') : '';
        $class_attr = $class !== '' ? ' class="' . esc_attr($class) . '"' : '';

        if ($url === '') {
            return '<span' . $class_attr . '>' . $inner_html . '</span>';
        }

        $target      = (string) ($link['target'] ?? '');
        $target_attr = $target !== '' ? ' target="' . esc_attr($target) . '"' : '';
        $rel_attr    = $target === '_blank' ? ' rel="noopener"' : '';

        return '<a' . $class_attr . ' href="' . esc_url($url) . '"' . $target_attr . $rel_attr . '>' . $inner_html . '</a>';
    }
}

if (!function_exists('underscores_child_prime_thumbnail_cache')) {
    /**
     * Prime attachment cache cho toàn bộ thumbnail của 1 WP_Query trong 1 query,
     * tránh N+1 khi loop gọi wp_get_attachment_image()/get_the_post_thumbnail() theo từng bài.
     * Gọi 1 lần ngay sau khi xác nhận have_posts() true, trước vòng lặp while.
     */
    function underscores_child_prime_thumbnail_cache(WP_Query $query): void
    {
        if ($query->post_count > 0) {
            update_post_thumbnail_cache($query);
        }
    }
}

if (!function_exists('underscores_child_section_is_visible')) {
    /**
     * ACF section group (mỗi section có field is_show): hiển thị mặc định trừ khi field
     * is_show được set false rõ ràng. Dùng cho mọi section trong page template.
     */
    function underscores_child_section_is_visible(array $section): bool
    {
        return !array_key_exists('is_show', $section) || (bool) $section['is_show'];
    }
}

if (!function_exists('underscores_child_related_posts_query')) {
    /**
     * Xây WP_Query cho block "Bài viết khác": mode=manual → post__in theo thứ tự chọn trong
     * ACF; mode=auto → 3 bài publish mới nhất, lọc theo $category_slug nếu có truyền vào.
     *
     * @param array{mode?:string,posts?:array<int,mixed>} $settings
     */
    function underscores_child_related_posts_query(array $settings, string $category_slug = ''): WP_Query
    {
        $related_posts  = is_array($settings['posts'] ?? null) ? $settings['posts'] : [];
        $selected_posts = array_values(array_filter(array_map('absint', $related_posts)));
        $mode           = (($settings['mode'] ?? 'auto') === 'manual') ? 'manual' : 'auto';

        $args = [
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 3,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        ];

        if ($mode === 'manual') {
            $args['post__in'] = $selected_posts !== [] ? $selected_posts : [0];
            $args['orderby']  = 'post__in';
        } elseif ($category_slug !== '') {
            $args['category_name'] = $category_slug;
        }

        return new WP_Query($args);
    }
}

if (!function_exists('underscores_child_render_related_posts')) {
    /**
     * Render block "Bài viết khác": rule-head + link chuyên mục bài viết + lưới 3 cột card-post.
     * Không render gì nếu query rỗng. Gọi wp_reset_postdata() trước khi return.
     */
    function underscores_child_render_related_posts(WP_Query $query): void
    {
        if (!$query->have_posts()) {
            return;
        }

        underscores_child_prime_thumbnail_cache($query);
        ?>
        <section class="section-posts">
            <div class="nh-container nh-stack">
                <div class="nh-rule-head">
                    <h2 class="nh-rule-head__title"><?php esc_html_e('Bài viết khác', 'underscores-child'); ?></h2>
                    <span class="nh-rule-head__line"></span>
                    <a class="nh-rule-head__action" href="<?php echo esc_url(get_post_type_archive_link('post') ?: home_url('/')); ?>">
                        <span><?php esc_html_e('Tất cả bài viết', 'underscores-child'); ?></span>
                        <?php echo underscores_child_icon_mask('icon_arrow_right'); ?>
                    </a>
                </div>
                <div class="nh-grid-3">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php get_template_part('partials/components/card-post', null, ['post_id' => get_the_ID()]); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php
        wp_reset_postdata();
    }
}

if (!function_exists('underscores_child_render_pagination')) {
    /**
     * Render <nav class="nh-pagination"> cho main query hiện tại (category.php / taxonomy.php):
     * paginate_links() + rewrite token Trước/Sau + đánh dấu is-current. Không render gì nếu
     * chỉ có 1 trang.
     */
    function underscores_child_render_pagination(): void
    {
        $pagination = paginate_links([
            'type'      => 'array',
            'mid_size'  => 2,
            'prev_text' => '__NHATO_PREV__',
            'next_text' => '__NHATO_NEXT__',
        ]);

        if (!is_array($pagination) || $pagination === []) {
            return;
        }
        ?>
        <nav class="nh-pagination" aria-label="<?php esc_attr_e('Phân trang', 'underscores-child'); ?>">
            <?php foreach ($pagination as $pagination_item) : ?>
                <?php
                if (str_contains($pagination_item, 'aria-current="page"') && str_contains($pagination_item, 'page-numbers current')) {
                    $pagination_item = '<a class="is-current" href="' . esc_url(get_pagenum_link(max(1, (int) get_query_var('paged')))) . '" aria-current="page">' . esc_html(wp_strip_all_tags($pagination_item)) . '</a>';
                }
                $pagination_item = str_replace('__NHATO_PREV__', esc_html__('Trước', 'underscores-child'), $pagination_item);
                $pagination_item = str_replace('__NHATO_NEXT__', esc_html__('Sau', 'underscores-child'), $pagination_item);
                echo wp_kses_post($pagination_item);
                ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }
}
