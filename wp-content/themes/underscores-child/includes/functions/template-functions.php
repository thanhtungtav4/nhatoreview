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
