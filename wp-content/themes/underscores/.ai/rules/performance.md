# Performance — Frontend + Query cho theme

Mở rộng `lazy-first.md` + `wp-core-first.md`. Rule này ép cách viết theme không làm chậm trang.
Đo đạc / profiling backend (TTFB, Query Monitor, object cache, cron) → skill `wp-performance`.

## 1. Asset (CSS/JS)

| Làm | Đừng |
|---|---|
| Script theme: `wp_enqueue_script($h, $src, $deps, $ver, ['in_footer' => true, 'strategy' => 'defer'])` (core 6.3+) | `str_replace('<script', '<script defer')` trong `script_loader_tag` — core không biết, dependency chạy sai thứ tự |
| Handle plugin muốn defer: `wp_script_add_data($handle, 'strategy', 'defer')` | defer `jquery` / `wp-hooks` / `wp-i18n` / CF7 (plugin in inline script phụ thuộc) |
| Asset riêng 1 trang → page hook, điều kiện `is_page_template()` / `is_singular()` | enqueue mọi trang "cho chắc" |
| Thư viện chung không dùng → bỏ qua filter `underscores_child_common_libraries` | để 8 thư viện chạy trên site chỉ cần 2 |
| Version = `filemtime` (`underscores_child_asset_version`, `underscores_child_template_asset_version`) | gỡ `ver` khỏi URL (hỏng cache-bust sau deploy) · hardcode `?v=1` |
| CSS không cần ngay (thư viện popup, form): `underscores_child_mark_style_loading_strategy($h, 'media')` | `@import` trong CSS |
| Critical CSS: đặt `assets/css/critical/{slug}.css` → `PerformanceHook` tự inline | inline cả file CSS lớn vào `<head>` |
| Preconnect/preload qua filter core `wp_resource_hints` / `wp_preload_resources`, chỉ khi thật dùng | echo `<link rel="preconnect">` cố định mọi trang |
| Deps khai báo đúng thực tế (swiper/gsap/aos KHÔNG cần jQuery) | `['jquery']` cho mọi thư viện |
| — | enqueue file rỗng / chỉ comment (vd `style.css` của child chỉ có header theme) |

## 2. Ảnh

- `wp_get_attachment_image($id, $size)` với **size gần kích thước hiển thị** (`medium_large`, size riêng) — `full` chỉ cho ảnh thật sự full-width.
- KHÔNG gỡ `sizes` / `srcset` (thiếu `sizes` → trình duyệt coi 100vw, tải ảnh lớn nhất).
- Ảnh LCP (hero, section đầu — `$args['is_first']` từ `underscores_child_render_flexible_sections()`):
  ```php
  $attrs = ! empty($args['is_first']) ? ['loading' => 'eager', 'fetchpriority' => 'high'] : [];
  echo wp_get_attachment_image($image_id, 'full', false, $attrs);
  ```
  Mỗi trang **1** ảnh `fetchpriority="high"`. Ảnh còn lại để core tự `loading="lazy"` + `decoding="async"`.
- `<img>` viết tay (icon trong build `/template`) phải có `width`/`height` (chống CLS). Icon đơn sắc → inline SVG.
- `add_image_size()` chỉ khi dùng thật (mỗi size = thêm 1 file cho mọi ảnh upload).

## 3. Query

- KHÔNG query trong vòng lặp (N+1). Cần dữ liệu liên quan → lấy trước 1 lần rồi map.
- `WP_Query`:
  - `'no_found_rows' => true` khi KHÔNG phân trang (bỏ `SQL_CALC_FOUND_ROWS`).
  - `'fields' => 'ids'` CHỈ khi thật sự chỉ cần ID. Nếu sau đó gọi `the_post()` / `get_the_*()` → mỗi bài 1 query → dùng query đầy đủ.
  - `'update_post_term_cache' => false` / `'update_post_meta_cache' => false` khi không đọc term / meta.
  - Trước loop có thumbnail / tác giả: `update_post_thumbnail_cache($query)`, `cache_users(wp_list_pluck($query->posts, 'post_author'))`.
  - `'posts_per_page'` luôn có giới hạn (không `-1` với dữ liệu tăng dần). Sau query phụ: `wp_reset_postdata()`.
- Lọc theo thuộc tính → **taxonomy**, không `meta_query` (LIKE / nhiều điều kiện meta rất chậm).
- Kết quả tốn kém, ít thay đổi (đếm, tổng hợp, gọi API ngoài) → `get_transient()` / `wp_cache_get()` với key rõ ràng, xoá ở `save_post` / `edited_term`. KHÔNG cache HTML chứa nonce hoặc dữ liệu theo user.
- ACF: `get_fields()` 1 lần / template; Flexible Content đọc bằng `underscores_child_render_flexible_sections()` (1 lần `get_field`); options page đọc mỗi request → `'autoload' => true`.

## 4. Thân thiện page cache

- KHÔNG `wp_is_mobile()`, cookie, `is_user_logged_in()` để đổi markup trang public → page cache trả nhầm phiên bản. Responsive = CSS media query.
- Nonce in trong HTML sống 12–24h: TTL page cache phải ngắn hơn, hoặc endpoint **chỉ đọc, public** (load thêm bài) dùng REST `GET` không nonce (vẫn sanitize + giới hạn input). Endpoint ghi dữ liệu luôn check nonce + capability.
- Khối dùng 2 nơi (menu desktop + mobile) → render 1 lần (`'echo' => false`) rồi in 2 lần; bỏ `id` trùng (`items_wrap`).

## 5. jQuery + module script

- jQuery được đưa xuống footer mặc định (`Theme\Child\Hooks\PerformanceHook::optimize_jquery`). Script plugin in ở `<head>` có khai báo phụ thuộc `jquery` → core tự kéo jQuery lên `<head>` cho trang đó, không cần làm gì.
- Chỉ vỡ khi có `<script>jQuery(...)</script>` echo trần (không qua enqueue) trong head/body. Sửa code đó thành `wp_add_inline_script('jquery', ..., 'after')`; plugin bên thứ 3 không sửa được → `add_filter('underscores_child_jquery_in_footer', '__return_false')`.
- `jquery-migrate` giữ mặc định. Console sạch cảnh báo `JQMIGRATE` → `add_filter('underscores_child_jquery_migrate', '__return_false')`.
- Code mới KHÔNG phụ thuộc jQuery nếu vanilla JS làm được (swiper, gsap, aos... không cần jQuery).
- Script `type="module"` (filter `underscores_script_to_module`) luôn chạy sau khi parse HTML. KHÔNG `wp_add_inline_script($module_handle, ..., 'after')`: inline chạy TRƯỚC module + core hạ cả chuỗi dependency defer về blocking. Cần code chạy sau → đặt trong chính file module.

## 6. Đã tắt sẵn ở parent — đừng bật lại

Emoji script/CSS, `global-styles`, `classic-theme-styles`, `wp-block-library` (block editor tắt), oEmbed discovery/host JS, REST link trong head, XML-RPC.
Theme cần block editor → bỏ tương ứng trong `Theme\Setup\ThemeSetup::remove_block_assets()`, không enqueue lại từng file.

## Checklist review

- [ ] Script mới có `strategy` (`defer`) + `in_footer`, deps đúng
- [ ] Asset riêng trang chỉ enqueue trên trang đó
- [ ] Ảnh dùng size phù hợp; ảnh LCP eager + `fetchpriority="high"`; `<img>` tay có width/height
- [ ] Không query trong loop; `WP_Query` có `no_found_rows` / prime cache khi cần
- [ ] Không `wp_is_mobile()` / dữ liệu theo user trong markup public
- [ ] Không file rỗng / thư viện thừa được enqueue
- [ ] Không `<script>jQuery(...)` echo trần; không inline `after` trên script defer/module
