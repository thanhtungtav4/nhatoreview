# Child Theme Rules

Đọc cùng `AGENTS.md`. Quy tắc viết page template, ACF, asset trong child theme.
(Render data: xem `../../underscores/.ai/rules/data-rendering.md` — không bịa dummy, chỉ guard rỗng.)

## Page template style

- Template lớn: gọi `get_fields()` MỘT lần, tách thành biến section rõ ràng ở đầu file:
  ```php
  $acf = get_fields() ?: [];
  $banner = $acf['banner_settings'] ?? [];
  $intro  = $acf['intro_settings'] ?? [];

  get_header();

  if (! empty($banner['is_show'])) {
      get_template_part('partials/front-page/section-banner', null, $banner);
  }
  if (! empty($intro['is_show'])) {
      get_template_part('partials/front-page/section-intro', null, $intro);
  }

  get_footer();
  ```
- Render section theo thứ tự cố định bằng `if` tường minh. Không build `section => partial` map để loop khi thứ tự cố định.
- Template là lớp orchestration: load fields → gán biến → gọi partial. Markup lớn → tách partial.
- Tên section phải khớp giữa ACF group, biến template, và tên file partial (dễ search).

## Partial style

- Truyền nguyên mảng section vào partial; partial đọc `$args` trực tiếp.
- Fallback đặt sát markup trong partial.
- KHÔNG mở thêm `<main>` trong partial (đã mở ở `header.php`).

## ACF (Local JSON)

- Một field group / page; dùng `Tab + Group + is_show` cho page theo section.
- KHÔNG tạo field trùng WP core: tiêu đề → `the_title()`, nội dung → `the_content()`, ảnh banner → featured image, mô tả → excerpt. Page không có section riêng thì KHÔNG cần field group (partial fallback `the_title` + `the_content`).
- Dữ liệu toàn site (liên hệ, social, tracking) đọc từ Theme Settings; khối dùng ≥2 nơi đặt ở `partials/components/` (vd `social-list.php`).
- Label tiếng Việt, mô tả rõ cho admin (`Tiêu đề`, `Ảnh`, `Danh sách slide`...), tránh label kỹ thuật (`Items`, `Active`) trừ khi đã quy ước.
- Key field ổn định kể cả khi đổi label (đổi key = mất data).

## Asset

- CSS/JS chung → child common hooks. CSS/JS theo page → 1 hook file riêng khi cần.
- KHÔNG enqueue CSS/JS trong markup template.
- Đặt tên asset theo slug: `assets/css/pages/about.css`, `assets/scripts/pages/about.js`.
- Script: `['in_footer' => true, 'strategy' => 'defer']`; CSS/JS page chỉ enqueue khi `is_page_template()` khớp. Chi tiết `../../underscores/.ai/rules/performance.md`.
- Section đầu trang (ảnh hero/LCP): partial nhận `$args['is_first']` → `['loading' => 'eager', 'fetchpriority' => 'high']`.

## Helper boundary

Đã có: `underscores_get_option()`, `underscores_child_acf_link()`, `underscores_child_render_flexible_sections()`.
Cho phép thêm (logic chung, tái dùng ≥2 nơi): vd `underscores_child_section_is_visible()`.
Ảnh: dùng thẳng `wp_get_attachment_image($id, $size, false, $attrs)` (có srcset/sizes/lazy) — KHÔNG helper trả URL ảnh để tự in `<img>`.

Tránh: `underscores_child_prepare_{page}_data()` hay bất kỳ helper "biết toàn bộ cấu trúc 1 page".
Nguyên tắc: **10 biến rõ ràng hơn 1 hàm transform lớn**; ưu tiên đọc ACF thẳng.
