# Underscores Theme — Agent Guide

Nguồn hướng dẫn chung cho mọi AI agent (Claude Code, Cursor, Copilot, Antigravity/Gemini, opencode, Trae...).
Rules + skills nằm trong `.ai/` (trung lập, mọi agent dùng được).

## Đọc trước khi code

- `.ai/rules/` — quy tắc bắt buộc (naming, auto-load, ACF, data-rendering, news-site, woocommerce...).
- `.ai/skills/` — workflow tự động hoá:
  - `convert-html-to-wp`: chuyển HTML tĩnh → theme (scan → plan ACF/CPT/Taxonomy → **chờ user duyệt** → build).
  - Skill nội bộ: `create-*`, `add-theme-option`, `woo-theme-setup` (bật Woo chuẩn: support → wrapper → mini-cart → perf).
  - Skill WordPress chính thức (folder `SKILL.md`, từ [WordPress/agent-skills](https://github.com/WordPress/agent-skills)): `wp-performance`, `wp-phpstan`, `wp-playground`, `wp-wpcli-and-ops`.
- `README.md` — kiến trúc + bootstrap flow.

## Mô hình kiến trúc (tóm tắt)

- **Class/hook**: PSR-4, namespace `Theme\`, thư mục `app/`, đăng ký bằng static `::register()` trong `includes/bootstrap.php`. Không có `configs/loadFile.php`.
- **Helper**: hàm global prefix `underscores_` trong `includes/functions/`, require trong `includes/bootstrap.php`.
- **AJAX**: handler global trong `includes/ajax/`, map qua `includes/config/ajax.php` + `Theme\Hooks\AjaxHook`.
- **ACF**: Local JSON trong child `acf-json/` (không dùng Extended ACF). Validate: `php underscores-child/acf-json/validate.php --summary`.
- **Constants**: prefix `UNDERSCORES_` trong `functions.php`.

## Quy tắc cốt lõi (xem chi tiết trong `.ai/rules/`)

- **Lazy-first** (`.ai/rules/lazy-first.md`): dòng code tốt nhất là dòng không viết. Theo ladder YAGNI → tái dùng → WP core → native → dep đã cài → 1 dòng → tối thiểu. KHÔNG cắt validation/security/a11y.
- **WP-core-first** (`.ai/rules/wp-core-first.md`): ưu tiên API/hook/template tag WP có sẵn, theo template hierarchy. `wp_nav_menu` thay menu hardcode, `title-tag` thay tự ghép title, `wp_get_attachment_image` thay `<img>` tay, `function_exists` thay `is_plugin_active`. Tự viết chỉ khi WP không có (và vẫn đứng trên API WP).
- Theo `.ai/rules/naming-convention.md`, `auto-load-system.md`, `modular-development.md`.
- **Data rendering** (`.ai/rules/data-rendering.md`): KHÔNG bịa dummy data; chỉ guard `?? ''`/`?: 0` chống lỗi rỗng; rỗng thì ẩn khối; không spam helper bọc 1 dòng `get_field()`.
- **Performance** (`.ai/rules/performance.md`): script `strategy => defer` qua core, asset chỉ trên trang dùng, ảnh đúng size + ảnh LCP `fetchpriority=high`, không query trong loop / N+1, không `wp_is_mobile()` trong markup (vỡ page cache).
- **News + Woo** (`.ai/rules/news-site.md`, `woocommerce.md`): tin tức dùng `post` core + template tag (cấm hardcode/dummy); Woo tích hợp qua `woocommerce.php` wrapper (không `<main>` lồng) + mini-cart block; WC ≥ 7.8 không tự load cart fragments — chỉ giới hạn lại khi dùng mini-cart classic ở header.
- Escape output đúng ngữ cảnh: `esc_html`/`esc_attr`/`esc_url`/`wp_kses_post`.
- AJAX ghi dữ liệu luôn check nonce + quyền; response `wp_send_json_success/error` (xem `ajax-handler.md`, lưu ý nonce + page cache).

## Khi đổi class

Sau khi thêm/đổi tên class trong `app/`, chạy `composer dump-autoload`.
