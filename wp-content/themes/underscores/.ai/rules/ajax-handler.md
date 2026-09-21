# Quy tắc Xử lý AJAX - Underscores Theme

Tài liệu này cung cấp quy trình chuẩn để tạo và quản lý các endpoint AJAX trong Underscores Theme, đảm bảo tính bảo mật, nhất quán và hiệu quả.

## 1. NGUYÊN TẮC CỐT LÕI

- **Bảo mật là trên hết**: Mọi endpoint AJAX phải được bảo vệ chống lại các lỗ hổng phổ biến như CSRF và XSS.
- **Tổ chức tập trung**: Tất cả logic xử lý AJAX phải được đặt trong thư mục `includes/ajax/`.
- **Phản hồi nhất quán**: Mọi phản hồi từ server phải tuân theo một cấu trúc JSON chuẩn.

## 2. QUY TRÌNH TẠO ENDPOINT AJAX

### Bước 1: Tạo file xử lý AJAX

- Tạo file PHP mới trong `includes/ajax/`, vd `load-posts-ajax.php`. Handler là **hàm global** (không tự `add_action`).
- Thêm `require_once` file này trong `includes/bootstrap.php`.

**Ví dụ**: `includes/ajax/load-posts-ajax.php`
```php
<?php

defined('ABSPATH') || exit;

if (! function_exists('underscores_ajax_load_more_posts')) {
    function underscores_ajax_load_more_posts() {
        // Logic xử lý sẽ ở đây
    }
}
```

### Bước 2: Đăng ký action qua `includes/config/ajax.php`

- Thêm cặp `handle => callable` vào map trả về trong `includes/config/ajax.php`:
  ```php
  return [
      'underscores_ajax_load_more_posts' => 'underscores_ajax_load_more_posts',
  ];
  ```
- `Theme\Hooks\AjaxHook::register()` (gọi từ `includes/bootstrap.php`) tự đăng ký cả `wp_ajax_{handle}` (user đăng nhập) và `wp_ajax_nopriv_{handle}` (khách).
- Nếu chỉ cho user đăng nhập, kiểm tra quyền bên trong hàm.

### Bước 3: Bảo mật Endpoint

Đây là bước **BẮT BUỘC**.

- **Kiểm tra Nonce**: Luôn bắt đầu hàm xử lý AJAX bằng việc kiểm tra nonce để chống tấn công CSRF. Sử dụng `check_ajax_referer()`.
- **Sanitize Input**: Làm sạch và xác thực tất cả dữ liệu nhận được từ client (`$_POST`, `$_GET`).

**Ví dụ**:
```php
function underscores_ajax_load_more_posts() {
    // 1. Kiểm tra Nonce
    check_ajax_referer('underscores-ajax-security', 'security');

    // 2. Sanitize dữ liệu đầu vào
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    // ... logic truy vấn bài viết ...
}
```

### Bước 4: Xử lý Logic và Trả về Phản hồi

- Trả về bằng `wp_send_json_success($data)` / `wp_send_json_error($data, $status)` — hai hàm này đã tự `wp_die()`.
- "Không có kết quả" **không phải lỗi** → `wp_send_json_success` với mảng rỗng + message; `wp_send_json_error` chỉ cho lỗi thật (nonce sai 403, exception 500).
- Query theo `performance.md`: không N+1, prime cache, `wp_reset_postdata()`.
- Không `echo` gì trước khi gửi JSON (vd hàm có tham số `$echo`) — sẽ làm hỏng JSON.

**Ví dụ hoàn chỉnh** (tham chiếu thật: `includes/ajax/post-ajax.php`):
```php
function underscores_ajax_load_more_posts() {
    if (! check_ajax_referer('underscores-ajax-security', 'security', false)) {
        wp_send_json_error(['message' => __('Hành động không được xác thực', 'underscores')], 403);
    }

    $paged = max(1, absint($_POST['paged'] ?? 1));

    $query = new WP_Query([
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => 6,
        'paged'                  => $paged,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
    ]);

    if (! $query->have_posts()) {
        wp_send_json_success(['html' => '', 'has_more' => false]);
    }

    update_post_thumbnail_cache($query);

    ob_start();
    while ($query->have_posts()) {
        $query->the_post();
        get_template_part('partials/components/card-news');
    }
    wp_reset_postdata();

    wp_send_json_success([
        'html'     => ob_get_clean(),
        'has_more' => $paged < $query->max_num_pages,
    ]);
}
```

### Lưu ý page cache + nonce

Nonce `underscores_params.ajaxNonce` được in vào HTML và hết hạn sau 12–24h. Trang được page cache lâu hơn
→ khách nhận nonce hết hạn → AJAX lỗi 403. Chọn 1:
- TTL page cache < 12h, hoặc
- Endpoint **chỉ đọc, public** (load thêm bài, lọc danh sách) → REST route `GET` với `permission_callback => '__return_true'`, không nonce, sanitize + giới hạn input.
- Endpoint **ghi dữ liệu** (form, giỏ hàng) → luôn nonce + kiểm tra quyền, không cache trang chứa form hoặc lấy nonce mới qua request riêng.

## 3. TÍCH HỢP PHÍA CLIENT (JAVASCRIPT)

Params đã được localize sẵn vào object `underscores_params` (handle `underscores-frontend`,
xem `Theme\Hooks\CommonHook::localize_frontend_params`):

```js
underscores_params = {
    siteURL:   '...',
    ajaxURL:   '/wp-admin/admin-ajax.php',
    ajaxNonce: '...'
}
```

Gửi request: luôn kèm `action` (= handle trong `includes/config/ajax.php`) và `security` (= `ajaxNonce`).

```javascript
fetch(underscores_params.ajaxURL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action:   'underscores_ajax_load_more_posts',
        security: underscores_params.ajaxNonce,
        page:     2,
    }),
})
.then(r => r.json())
.then(res => {
    if (res.success) { /* res.data.html */ }
});
```

## 4. Ví dụ yêu cầu

> "Tạo AJAX 'Thêm vào giỏ hàng'.
> 1. Tạo `includes/ajax/cart-ajax.php` (hàm `underscores_ajax_add_to_cart`), require trong `includes/bootstrap.php`, thêm handle vào `includes/config/ajax.php`.
> 2. Trong hàm: check nonce `underscores-ajax-security`, lấy `product_id` từ `$_POST`, gọi `WC()->cart->add_to_cart()`.
> 3. Trả về `wp_send_json_success()` / `wp_send_json_error()`."
