<?php

declare(strict_types=1);

namespace Theme\Hooks;

defined('ABSPATH') || exit;

/**
 * Image-related output filters.
 */
final class ImageHook
{
    public static function register(): void
    {
        $self = new self();
        // KHÔNG gỡ `sizes`: thiếu sizes trình duyệt coi ảnh rộng 100vw → tải candidate srcset lớn nhất.
        add_filter('post_thumbnail_html', [$self, 'fallback_thumbnail'], 20, 1);
    }

    /**
     * Ảnh mặc định khi post không có thumbnail. Có width/height (chống CLS), lazy, alt rỗng (trang trí).
     */
    public function fallback_thumbnail($html)
    {
        if (! empty($html)) {
            return $html;
        }

        return sprintf(
            '<img src="%s" width="640" height="480" alt="" loading="lazy" decoding="async" class="underscores-default-thumbnail"/>',
            esc_url(UNDERSCORES_THEME_PATH_URI . '/assets/images/default-thumbnail.jpg')
        );
    }
}
