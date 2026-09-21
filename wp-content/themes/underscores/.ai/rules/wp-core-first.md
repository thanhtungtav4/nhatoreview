# WordPress-Core-First — Tôn trọng cấu trúc WP

Mở rộng của `lazy-first.md` cho riêng WordPress: **ưu tiên cái WP core đã có, theo chuẩn WP,
KHÔNG tự chế lại bánh xe.** WP đã giải hầu hết bài toán theme — dùng API/hook/template tag của nó.

## Nguyên tắc

Trước khi viết, hỏi: *WordPress đã có hàm/hook/feature cho việc này chưa?* Nếu có → dùng.
Chỉ tự viết khi WP thật sự không có (vd primary-term, cache-bust by mtime).

## Bảng thay thế — dùng cái này, đừng tự làm

| Việc | DÙNG (WP core) | ĐỪNG tự làm |
|---|---|---|
| Title `<title>` | `add_theme_support('title-tag')` + filter `document_title_parts` / `document_title_separator` | tự ghép qua filter `wp_title` (deprecated khi có title-tag) |
| Menu | `wp_nav_menu(['theme_location' => ...])` + custom `Walker_Nav_Menu` nếu cần markup riêng | hardcode `<ul><li>` |
| Phân trang | `paginate_links()` (đã wrap trong `underscores_pagination_links`) | tự tính trang, tự `<a>` |
| Ảnh attachment | `wp_get_attachment_image()`, `the_post_thumbnail()`, `get_the_post_thumbnail()` | tự `<img>` + tự query URL |
| URL ảnh | `wp_get_attachment_image_url()`, `get_theme_file_uri()` | path tay `/template/assets/...` |
| Query bài viết | `WP_Query`, `get_posts()`, `get_the_terms()`, `get_term()` | `$wpdb` raw SQL |
| Escape / sanitize | `esc_html/esc_attr/esc_url/wp_kses_post`, `sanitize_*`, `absint`, `sanitize_key` | tự regex/strip |
| Class body/main | `body_class()`, `post_class()` (pattern) | tự build chuỗi class song song |
| Asset version | `filemtime()` cache-bust (helper sẵn) | hardcode `?v=1.0`, gỡ `ver` |
| Defer/async script | `wp_enqueue_script(..., ['strategy' => 'defer'])`, `wp_script_add_data()` | chèn `defer` bằng `script_loader_tag` |
| Preconnect / preload | filter `wp_resource_hints` / `wp_preload_resources` | echo `<link>` cố định trong `wp_head` |
| Upload AVIF/WebP | core (WebP 5.8+, AVIF 6.5+) | tự thêm mime + sửa `wp_check_filetype_and_ext` |
| Logo | `the_custom_logo()` + `custom-logo` support | tự `<img src>` logo, field ACF `logo` |
| Favicon | Site Icon (Tùy biến → Nhận dạng site), core tự in trong `wp_head()` | field ACF `favicon`, gọi thêm `wp_site_icon()` trong header (in trùng) |
| Tên site / slogan | `bloginfo('name')` / `bloginfo('description')` | field ACF `site_name` / `slogan` |
| Tiêu đề / nội dung / banner / mô tả của page | `the_title()` / `the_content()` / `the_post_thumbnail()` / `the_excerpt()` | field ACF `heading`, `intro`, `banner_image` |
| Search form | `get_search_form()` + override `searchform.php` ở child | tự `<form action="">` trong header |
| Đa ngôn ngữ | API plugin (`pll_the_languages()`) có `function_exists` | hardcode link VN/EN |
| `<head>` chuẩn | `<!doctype html>`, `wp_head()`, `wp_body_open()` | XHTML doctype, IE conditional, gọi lại `wp_site_icon()` |
| Danh sách link trỏ tới page/post (cột "Liên kết nhanh", "Dịch vụ" ở footer) | `register_nav_menus` + `wp_nav_menu`, tiêu đề = `wp_get_nav_menu_name()` | ACF repeater `link` |
| Search form | `get_search_form()` / `searchform.php` | tự `<form>` |
| Plugin check | `function_exists()` / `class_exists()` | `is_plugin_active()` (chỉ load ở admin → frontend fatal) |
| Nav data trong template | template tag (`the_title`, `the_permalink`, `the_excerpt`) | tự đọc field thủ công |

## Theo template hierarchy + cấu trúc WP

- Template files theo [hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/): `single.php`, `archive.php`, `taxonomy.php`, `404.php`, `single-{cpt}.php`...
- Override trong child theme = file cùng tên ở root child (đúng cách, không hack).
- Đăng ký: `register_post_type`/`register_taxonomy` ở hook `init`; `register_nav_menus` ở `after_setup_theme`. Menu register thì PHẢI render bằng `wp_nav_menu` (đừng register rồi hardcode).
- Enqueue: `wp_enqueue_style/script` + `wp_localize_script` (đừng nhúng `<script>`/`<link>` thẳng trong template).
- The Loop: `have_posts()/the_post()` cho query chính; `WP_Query` + `wp_reset_postdata()` cho query phụ.

## Khi nào tự viết được (WP không có)

- `underscores_get_primary_term` — WP không có primary-term (dùng Yoast nếu có, fallback `get_the_terms`).
- `underscores_child_asset_version` — cache-bust theo `filemtime`, WP không có one-liner.
- Custom `Walker_Nav_Menu` — khi markup menu khác mặc định (vẫn extends core Walker, không viết lại từ đầu).

→ Tự viết thì vẫn **đứng trên API WP** (extend core class, gọi template tag bên trong), không bypass.

## Khi review / convert HTML

- Markup menu tĩnh → `wp_nav_menu` + Walker khớp class (xem `app/Nav/MenuWalker.php`).
- `<img src="/template/...">` → `wp_get_attachment_image()` (ảnh ACF) hoặc `get_theme_file_uri()` (ảnh theme).
- Khối lặp danh sách → `WP_Query` + The Loop, không hardcode item.
- Footer "menu" thực ra là data liên hệ (email, SĐT, địa chỉ) → Theme Settings, KHÔNG phải `wp_nav_menu`.
- Footer menu thật (list link tới page/post/term, có `current-menu-item`) → `wp_nav_menu`, KHÔNG phải ACF repeater.
