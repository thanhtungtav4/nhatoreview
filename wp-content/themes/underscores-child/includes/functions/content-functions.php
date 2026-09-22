<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('underscores_child_article_content_with_toc')) {
    /**
     * Add stable anchors to article headings and return a generated table of contents.
     *
     * @return array{content:string,toc:array<int,array{id:string,title:string,level:int}>}
     */
    function underscores_child_article_content_with_toc(string $content): array
    {
        $toc  = [];
        $used = [];

        $updated_content = preg_replace_callback(
            '/<h([23])\b([^>]*)>(.*?)<\/h\1>/is',
            static function (array $matches) use (&$toc, &$used): string {
                $level = (int) $matches[1];
                $attrs = $matches[2];
                $inner = $matches[3];
                $title = trim(wp_strip_all_tags($inner));

                if ($title === '') {
                    return $matches[0];
                }

                $id = '';
                if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $id_match)) {
                    $id = sanitize_title($id_match[1]);
                }

                if ($id === '') {
                    $id = sanitize_title($title);
                }

                if ($id === '') {
                    $id = 'section';
                }

                $base_id = $id;
                $suffix  = 2;

                while (isset($used[$id])) {
                    $id = $base_id . '-' . $suffix;
                    $suffix++;
                }

                $used[$id] = true;

                if (! preg_match('/\bid\s*=/i', $attrs)) {
                    $attrs .= ' id="' . esc_attr($id) . '"';
                } else {
                    $id = sanitize_title($id_match[1] ?? $id);
                }

                $toc[] = [
                    'id'    => $id,
                    'title' => $title,
                    'level' => $level,
                ];

                return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
            },
            $content
        );

        if (is_string($updated_content)) {
            $updated_content = preg_replace_callback(
                '/<figure\b([^>]*)>/i',
                static function (array $matches): string {
                    $attrs = $matches[1];

                    if (preg_match('/\bclass\s*=/i', $attrs)) {
                        $attrs = preg_replace(
                            '/\bclass\s*=\s*(["\'])(.*?)\1/i',
                            static fn (array $class_match): string => 'class=' . $class_match[1] . trim($class_match[2] . ' article__figure') . $class_match[1],
                            $attrs,
                            1
                        ) ?? $attrs;
                    } else {
                        $attrs .= ' class="article__figure"';
                    }

                    return '<figure' . $attrs . '>';
                },
                $updated_content
            ) ?? $updated_content;

            if (stripos($updated_content, '<section') === false) {
                $updated_content = preg_replace_callback(
                    '/(<h[23]\b[^>]*>.*?<\/h[23]>)(.*?)(?=<h[23]\b|$)/is',
                    static function (array $matches): string {
                        return '<section class="article__section">' . $matches[1] . $matches[2] . '</section>';
                    },
                    $updated_content
                ) ?? $updated_content;
            } else {
                $updated_content = preg_replace_callback(
                    '/<section\b([^>]*)>/i',
                    static function (array $matches): string {
                        $attrs = $matches[1];

                        if (preg_match('/\bclass\s*=/i', $attrs)) {
                            $attrs = preg_replace(
                                '/\bclass\s*=\s*(["\'])(.*?)\1/i',
                                static fn (array $class_match): string => 'class=' . $class_match[1] . trim($class_match[2] . ' article__section') . $class_match[1],
                                $attrs,
                                1
                            ) ?? $attrs;
                        } else {
                            $attrs .= ' class="article__section"';
                        }

                        return '<section' . $attrs . '>';
                    },
                    $updated_content
                ) ?? $updated_content;
            }
        }

        return [
            'content' => is_string($updated_content) ? $updated_content : $content,
            'toc'     => $toc,
        ];
    }
}

if (!function_exists('underscores_child_render_cf7')) {
    /**
     * Render a Contact Form 7 form selected in Theme Settings.
     */
    function underscores_child_render_cf7(string $option_name): string
    {
        if (! shortcode_exists('contact-form-7')) {
            return '';
        }

        $form_id = absint(underscores_get_option($option_name, 0));

        if ($form_id < 1) {
            return '';
        }

        return do_shortcode('[contact-form-7 id="' . $form_id . '"]');
    }
}

if (!function_exists('underscores_child_icon')) {
    /**
     * Render a Theme Settings system icon (group_theme_settings.icon_*) as a plain <img>.
     * Only for icons with a single fixed color everywhere they appear — the uploaded file
     * already bakes that color in. For icons whose color changes with hover/context, use
     * underscores_child_icon_mask() instead.
     *
     * @param array<string,mixed> $attrs Extra wp_get_attachment_image() attributes.
     */
    function underscores_child_icon(string $option_name, array $attrs = []): string
    {
        $id = absint(underscores_get_option($option_name, 0));
        if ($id < 1) {
            return '';
        }

        $attrs = wp_parse_args($attrs, ['alt' => '', 'loading' => 'lazy']);

        return (string) wp_get_attachment_image($id, 'full', false, $attrs);
    }
}

if (!function_exists('underscores_child_icon_mask')) {
    /**
     * Render a Theme Settings system icon (group_theme_settings.icon_*) as a CSS mask-image
     * element — for icons whose color changes with hover/context (arrow, chevron, menu...).
     * background-color:currentColor through the mask reproduces the old inline-SVG
     * fill="currentColor" behaviour, hover states included, with an uploaded file instead of
     * the sprite. Pair with the shared .nh-icon-mask rule in tokens/base.css.
     */
    function underscores_child_icon_mask(string $option_name, string $class = ''): string
    {
        $id = absint(underscores_get_option($option_name, 0));
        if ($id < 1) {
            return '';
        }

        $url = wp_get_attachment_image_url($id, 'full');
        if (! $url) {
            return '';
        }

        $classes = trim('nh-icon-mask ' . $class);

        return '<span class="' . esc_attr($classes) . '" style="--nh-icon-mask:url(' . esc_url($url) . ')" aria-hidden="true"></span>';
    }
}
