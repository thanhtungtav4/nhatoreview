# News Site — Tin tức trên `post` core

Mở rộng `wp-core-first.md` + `data-rendering.md` cho site tin tức.
**Tin tức = `post` core + `category`/`tag` core. KHÔNG tạo CPT/taxonomy riêng** (theo wp-core-first).

## Template hierarchy (dùng đúng file)

| Trang | File | Query |
|---|---|---|
| Blog index / danh sách tin | `home.php` | main loop `have_posts()/the_post()` |
| Bài viết chi tiết | `single.php` | main loop |
| Lưu trữ theo category | `category.php` | main loop |
| Lưu trữ theo tag | `tag.php` | main loop |
| Lưu trữ chung | `archive.php` | main loop |
| Kết quả tìm kiếm | `search.php` | main loop |

- Query phụ (tin liên quan, tin nổi bật) → `WP_Query` + `'no_found_rows' => true` + `wp_reset_postdata()`. KHÔNG `query_posts`. Chi tiết `performance.md`.

## DRY — card tin một partial

- Card tin lặp → **một** `partials/components/card-news.php`, gọi trong loop:
  `get_template_part('partials/components/card-news', null, $args)`.
- Cấm copy-paste markup card mỗi item (lỗi hiện tại ở `home.php`).

## Template tag bắt buộc (KHÔNG hardcode)

| Dữ liệu | DÙNG | ĐỪNG |
|---|---|---|
| Link bài | `the_permalink()` / `get_permalink()` | `href=""` |
| Tiêu đề | `the_title()` | text tĩnh |
| Ngày | `get_the_date()` / `the_time()` | "March 2024" |
| Thumbnail | `the_post_thumbnail('medium_large')` / `get_the_post_thumbnail()` — size theo card, không `full` | `<img src="/template/...">` |
| Trích | `the_excerpt()` / `get_the_excerpt()` | lorem |
| Category | `get_the_category_list()` / `the_category()` | tag tĩnh |
| Tác giả | `get_the_author()` | "Hat una" |

- Ảnh theme (decor) → `get_theme_file_uri('assets/images/...')`, KHÔNG `/template/...`.
- Fallback thumbnail đã có `Theme\Hooks\ImageHook` — dùng, không tự fallback.
- Phân trang → `underscores_pagination_links()`.

## Cấm (trỏ data-rendering.md)

- KHÔNG dummy/demo data. Rỗng → ẩn khối (`?? ''`, `?: 0`), không bịa default.
- Escape output đúng ngữ cảnh: `esc_html`/`esc_url`/`esc_attr`/`wp_kses_post`.
