<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Custom logo nằm trong header (luôn above-the-fold) → không lazy-load.
 *
 * - width/height: the_custom_logo() đã in từ metadata, không cần đọc lại.
 * - KHÔNG đặt fetchpriority="high": logo hiếm khi là LCP; mỗi trang chỉ nên có 1 ảnh
 *   high priority (ảnh hero). Logo thật sự là LCP → filter `underscores_custom_logo_fetchpriority`.
 *
 * @param array $attrs
 * @return array
 */
if ( ! function_exists( 'underscores_optimize_custom_logo_attrs' ) ) {
    function underscores_optimize_custom_logo_attrs($attrs)
    {
        unset($attrs['loading']);

        if (apply_filters('underscores_custom_logo_fetchpriority', false)) {
            $attrs['fetchpriority'] = 'high';
        }

        return $attrs;
    }
}
