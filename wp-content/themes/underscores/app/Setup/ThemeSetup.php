<?php

declare(strict_types=1);

namespace Theme\Setup;

defined('ABSPATH') || exit;

/**
 * Theme setup: supports, head cleanup, title/term rewrites, htaccess hardening.
 */
final class ThemeSetup
{
    private static ?self $instance = null;

    private function __construct()
    {
        add_action('after_setup_theme', [$this, 'after_setup_theme']);
        add_action('login_enqueue_scripts', [$this, 'login_custom_logo']);
        add_filter('login_headerurl', fn() => home_url('/'));
        add_filter('login_headertext', fn() => get_bloginfo('name'));

        $this->add_filters();
        $this->remove_wp_head_actions();
        $this->remove_block_assets();
    }

    public static function register(): self
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function after_setup_theme(): void
    {
        // 'administrator' is a role, not a capability; gate on a real cap.
        if (! current_user_can('edit_posts') && ! is_admin()) {
            show_admin_bar(false);
        }

        load_theme_textdomain('underscores', get_template_directory() . '/languages');
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('html5', ['comment-list', 'search-form', 'comment-form', 'style', 'script']);
        // 'woocommerce' support is declared in CommonHook::register_menus(); 'menus' is implied.
        add_theme_support('custom-logo', [
            'height'      => 100,
            'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => ['site-title', 'site-description'],
        ]);
    }

    private function add_filters(): void
    {
        // Title được xử lý bởi add_theme_support('title-tag') + core document_title_parts.
        add_filter('the_generator', [$this, 'remove_rss_version']);
        add_filter('get_the_archive_title', [$this, 'rewrite_term_title']);
        add_filter('login_errors', [$this, 'custom_wordpress_error_message']);
        add_filter('style_loader_src', [$this, 'remove_version_from_scripts']);
        add_filter('script_loader_src', [$this, 'remove_version_from_scripts']);
        add_filter('mod_rewrite_rules', [$this, 'rewrite_htaccess'], 999999);
        add_filter('image_editor_output_format', [$this, 'image_editor_output_format']);
        add_filter('wp_generate_attachment_metadata', [$this, 'compress_original_image_backup'], 20, 2);
        add_filter('upload_mimes', [$this, 'custom_upload_mimes']);
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg_upload']);
        add_filter('wpcf7_autop_or_not', '__return_false');
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('embed_oembed_discover', '__return_false');
    }

    private function remove_wp_head_actions(): void
    {
        // Only what current WP core still emits. (wlwmanifest, rsd_link, *_post_rel_link
        // were removed/no longer hooked by default in modern core.)
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        remove_action('rest_api_init', 'wp_oembed_register_route');
    }

    public function custom_wordpress_error_message(): string
    {
        return __('That was not quite correct...', 'underscores');
    }

    /**
     * Chỉ gỡ `ver` khi nó lộ version WordPress core. Giữ nguyên `ver` của asset theme/plugin
     * (filemtime / version) — gỡ hết sẽ làm hỏng cache-busting sau mỗi lần deploy.
     */
    public function remove_version_from_scripts($src)
    {
        if (! is_string($src) || strpos($src, 'ver=') === false) {
            return $src;
        }

        $query = (string) wp_parse_url($src, PHP_URL_QUERY);
        parse_str($query, $args);

        return (($args['ver'] ?? null) === get_bloginfo('version')) ? remove_query_arg('ver', $src) : $src;
    }

    public function remove_rss_version(): string
    {
        return '';
    }

    public function rewrite_term_title($title)
    {
        $strip_texts = ['Category:', 'Tag:', 'Tags:'];
        return (is_category() || is_tag()) ? str_replace($strip_texts, '', $title) : $title;
    }

    public function rewrite_htaccess($rules)
    {
        $custom_rules = "
            RewriteRule wp-content/plugins/(.*\.php)$ - [R=404,L]
            RewriteRule wp-content/themes/(.*\.php)$ - [R=404,L]
            RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]
            RewriteCond %{QUERY_STRING} GLOBALS(=|[|%[0-9A-Z]{0,2}) [OR]
            RewriteCond %{QUERY_STRING} _REQUEST(=|[|%[0-9A-Z]{0,2})
            RewriteRule ^(.*)$ index.php [F,L]
            RewriteRule ^wp-admin/includes/ - [F,L]
            RewriteRule !^wp-includes/ - [S=3]
            RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
            RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
            RewriteRule ^wp-content/uploads/.*\.(php|rb|py)$ - [F,L,NC]
            RewriteRule ^wp-config.php$ - [F,L,NC]
        ";
        return str_replace("</IfModule>", $custom_rules . "</IfModule>", $rules);
    }

    public function custom_upload_mimes($mimes)
    {
        if (! current_user_can('manage_options')) {
            return $mimes;
        }

        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * Sub size (thumbnail/medium/medium_large/large) sinh ra từ JPEG/PNG → xuất WebP.
     * Core tự kiểm tra server hỗ trợ WebP trước khi đổi format (không hỗ trợ → giữ nguyên,
     * an toàn để bật mặc định). Không map sang AVIF: AVIF chỉ ~82-95% trình duyệt hỗ trợ
     * (2026), thiếu fallback <picture> sẽ vỡ ảnh trên trình duyệt cũ / webview Zalo, Facebook.
     * File gốc (ảnh dưới ngưỡng big_image_size_threshold, phục vụ size 'full') KHÔNG đổi định
     * dạng — chỉ sub size dùng cho card/grid/srcset.
     *
     * @param array<string, string> $formats Map mime nguồn → mime đích.
     * @return array<string, string>
     */
    public function image_editor_output_format(array $formats): array
    {
        $formats['image/jpeg'] = 'image/webp';
        $formats['image/png']  = 'image/webp';

        return $formats;
    }

    /**
     * Core giữ nguyên byte-gốc (chưa nén) dưới `original_image` khi upload bị đổi format/scale
     * (phục vụ "Restore Original Image" trong Media Editor). Ảnh chụp thẳng máy ảnh / export
     * PNG không nén có thể vài MB–chục MB mỗi file — không phục vụ cho visitor (chỉ dùng khi
     * admin bấm restore) nhưng vẫn tốn dung lượng host/backup. Nén lại thành WebP q87 ngay sau
     * khi tạo, đổi tên `{stem}-original.webp` để không đụng file `{stem}.webp` đang serve.
     * Không nén nhỏ hơn → bỏ qua, giữ nguyên bản gốc (an toàn, hiếm khi xảy ra).
     *
     * @param array<string, mixed> $metadata
     */
    public function compress_original_image_backup(array $metadata, int $attachment_id): array
    {
        if (empty($metadata['original_image']) || str_ends_with((string) $metadata['original_image'], '-original.webp')) {
            return $metadata;
        }

        $file = get_attached_file($attachment_id);
        if ($file === '' || $file === false || ! file_exists($file)) {
            return $metadata;
        }

        $dir      = dirname($file);
        $old_path = $dir . '/' . $metadata['original_image'];
        if (! file_exists($old_path)) {
            return $metadata;
        }

        $old_size = filesize($old_path);
        $stem     = pathinfo((string) $metadata['original_image'], PATHINFO_FILENAME);
        $new_path = $dir . '/' . $stem . '-original.webp';

        $editor = wp_get_image_editor($old_path);
        if (is_wp_error($editor)) {
            return $metadata;
        }

        $editor->set_quality(87);
        $saved = $editor->save($new_path, 'image/webp');
        if (is_wp_error($saved)) {
            return $metadata;
        }

        $new_size = file_exists($new_path) ? filesize($new_path) : 0;
        if ($new_size <= 0 || $new_size >= $old_size) {
            @unlink($new_path); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- best-effort cleanup of a failed/no-gain attempt.
            return $metadata;
        }

        unlink($old_path);
        $metadata['original_image'] = $stem . '-original.webp';

        return $metadata;
    }

    /**
     * Strip script/event-handler vectors from uploaded SVGs (stored-XSS guard).
     *
     * ponytail: regex scrub of the obvious vectors; swap in enshrined/svg-sanitizer
     * if you need full XML-aware sanitization.
     */
    public function sanitize_svg_upload($file)
    {
        if (($file['type'] ?? '') !== 'image/svg+xml' || empty($file['tmp_name'])) {
            return $file;
        }

        $svg = file_get_contents($file['tmp_name']);
        if ($svg === false) {
            $file['error'] = __('Không đọc được file SVG.', 'underscores');
            return $file;
        }

        $clean = preg_replace(
            ['#<script[\s\S]*?</script>#i', '#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '#(href|xlink:href)\s*=\s*("|\')\s*javascript:[^"\']*\2#i'],
            '',
            $svg
        );

        if ($clean !== $svg && file_put_contents($file['tmp_name'], $clean) === false) {
            $file['error'] = __('Không thể làm sạch file SVG.', 'underscores');
        }

        return $file;
    }

    public function login_custom_logo(): void
    {
        if (! has_custom_logo()) {
            return; // ponytail: không có logo khách → để WP logo mặc định.
        }

        $logo_id  = get_theme_mod('custom_logo');
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');

        if (! $logo_url) {
            return;
        }

        printf(
            '<style>#login h1 a{background-image:url(%s);background-size:contain;width:auto;height:80px}</style>',
            esc_url($logo_url)
        );
    }

    public function remove_block_assets(): void
    {
        add_filter('use_block_editor_for_post', '__return_false', 10);
        add_filter('use_block_editor_for_post_type', '__return_false', 10);

        add_action('init', static function () {
            add_filter('use_widgets_block_editor', '__return_false');
        });

        add_action('wp_enqueue_scripts', static function () {
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('wc-block-style');
            wp_dequeue_style('global-styles');        // theme.json CSS inline (~10KB) — theme classic không dùng.
            wp_dequeue_style('classic-theme-styles'); // style cho block button/file — đã tắt block editor.
        }, 100);

        // Emoji: script detect + CSS inline ở mọi trang; trình duyệt hiện đại tự render emoji.
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
        add_filter('emoji_svg_url', '__return_false');

        add_filter('woocommerce_blocks_enqueue_scripts', '__return_false');
    }
}
