# Underscores Theme (WordPress)

Theme WordPress custom cho hệ sinh thái Underscores.

- Theme name: `Underscores Theme`
- Current version: `4.3.0`
- Main branch: `main`

## 1) Mục tiêu dự án

Underscores Theme được tổ chức theo hướng:

- tối ưu hiệu năng frontend (enqueue có kiểm soát, defer có chọn lọc)
- ưu tiên WP core (xem `.ai/rules/wp-core-first.md`), không chứa giao diện riêng của dự án
- hook = class PSR-4 trong `app/`, helper global trong `includes/functions/`, AJAX map qua `includes/config/ajax.php`
- hỗ trợ upload AVIF/WebP, SVG (lọc script) và ảnh fallback cho thumbnail

## 2) Yêu cầu môi trường

- WordPress: `>= 6.5` (script `strategy` defer 6.3+, upload AVIF 6.5+)
- PHP: `>= 8.1` (khuyến nghị)
- Composer: để cài dependency PHP
- Node.js: không bắt buộc, dùng để check syntax JS

## 3) Cài đặt

1. Đưa source vào:
   - `wp-content/themes/underscores`
2. Cài dependency:
   - `composer install`
3. Kích hoạt theme trong WP Admin.
4. Thiết lập:
   - Front page
   - Posts page
   - Menus (`top-header-menu`, `header-menu`; child thêm `footer-menu`)

## 4) Lưu ý quan trọng về assets

Theme đang dùng 2 nguồn assets:

- Assets trong theme: `assets/...`
- Assets template ngoài theme qua constant:
  - `UNDERSCORES_SITE_TEMPLATE_URL = {site_url}/template`

Điều này nghĩa là server cần có thư mục `/template` hợp lệ (build frontend của từng dự án: `css/style.css`, `js/main.js`, `assets/library/*`...) để frontend hoạt động đầy đủ.

## 5) Cấu trúc thư mục

```text
underscores/
├── .ai/                          # Rules + Skills cho AI agent
│   ├── rules/
│   └── skills/
├── app/                          # Class PSR-4 (namespace Theme\)
│   ├── Setup/ThemeSetup.php
│   ├── Nav/MenuWalker.php
│   └── Hooks/{Common,Image,BlogPage,BlogSingle,DefaultPage,Ajax}Hook.php
├── includes/
│   ├── bootstrap.php             # require helper + ::register() các class
│   ├── config/ajax.php           # map action AJAX -> callback
│   ├── functions/                # helper global (prefix underscores_)
│   └── ajax/                     # AJAX handler (hàm global)
├── partials/templates/           # template parts (category, tag, taxonomy, single-post)
├── assets/                       # 404.css, default-thumbnail.jpg, underscores-frontend.js (+ modules/)
├── *.php                         # template hierarchy: index, page, single, archive, category, tag, taxonomy, search, 404
├── functions.php                 # define constants + bootstrap
├── composer.json                 # PSR-4: Theme\ -> app/, Theme\Child\ -> ../underscores-child/app/
└── vendor/                       # composer autoloader
```

## 6) Bootstrap flow

1. `functions.php`
   - define constants (`UNDERSCORES_THEME_PATH`, `UNDERSCORES_THEME_INCLUDES_PATH`, ...)
   - require `vendor/autoload.php`
   - require `includes/bootstrap.php`
2. `includes/bootstrap.php`
   - `require_once` các helper trong `includes/functions/` + `includes/ajax/`
   - gọi `::register()` cho từng hook class (autoload PSR-4):
     - `\Theme\Setup\ThemeSetup::register()`
     - `\Theme\Hooks\CommonHook::register()`, `ImageHook`, `AjaxHook`
     - asset theo trang (blog, single...) do child/dự án thêm bằng page hook, không đặt sẵn trong parent
3. `\Theme\Hooks\AjaxHook::register()`
   - đọc map trong `includes/config/ajax.php`
   - đăng ký `wp_ajax_*` và `wp_ajax_nopriv_*`

---

## 7) Agent Integration (Rules & Skills)

Bộ quy tắc + skill cho mọi AI agent (xem `AGENTS.md`). Tất cả ở `.ai/`.

### 7.1 Rules (.ai/rules/)
- **Lazy-First**: Dòng code tốt nhất là dòng không viết — ladder YAGNI → tái dùng → WP core → native → 1 dòng; không cắt validation/security/a11y (triết lý [ponytail](https://github.com/DietrichGebert/ponytail)).
- **WP-Core-First**: Ưu tiên WP API/hook/template tag có sẵn, theo template hierarchy (`wp_nav_menu`, `title-tag`, `wp_get_attachment_image`, `paginate_links`...) — không tự chế lại.
- **Naming Convention**: Đặt tên đồng nhất (`underscores_` prefix, kebab-case template...).
- **Modular Development**: Hook/class trong `app/` (PSR-4), đăng ký qua `::register()` trong `includes/bootstrap.php`.
- **Auto-load System**: Cơ chế tải file (PSR-4 + require helper, không loadFile.php).
- **AJAX Handler**: Bảo mật + phản hồi AJAX (nonce, JSON success/error).
- **ACF Integration**: ACF Local JSON (`acf-json/`, sync qua admin) + Theme Options.
- **Data Rendering**: Không bịa dummy data; guard `?? ''` chống lỗi rỗng, ẩn khối khi trống; không spam helper.
- **Performance**: script `strategy` defer core, asset theo trang, ảnh size + LCP, query không N+1, thân thiện page cache (`performance.md`).
- **Flexible Content**: Mapping Layout name ↔ template file.

### 7.2 Skills nội bộ (.ai/skills/)
- `convert-html-to-wp`: scan file HTML template → plan (vùng nào ACF/CPT/Taxonomy) → **chờ duyệt** → build.
- `create-new-module` · `create-ajax-endpoint` · `create-flexible-section` · `create-cpt` · `add-theme-option`

### 7.3 Skills WordPress chính thức (.ai/skills/, từ [WordPress/agent-skills](https://github.com/WordPress/agent-skills))
- `wp-performance`: caching, DB optimization, profiling, Server-Timing.
- `wp-phpstan`: static analysis (phpstan.neon, baseline, WP typing).
- `wp-playground`: local env tức thì qua WordPress Playground.
- `wp-wpcli-and-ops`: WP-CLI, search-replace, automation, multisite.

---

## 8) Thành phần chính

### 8.1 Theme setup

File: `app/Setup/ThemeSetup.php`

- add/remove hỗ trợ WP core
- cleanup `wp_head`: generator, REST link, oEmbed, emoji script/CSS
- tắt block editor + dequeue `wp-block-library`, `global-styles`, `classic-theme-styles`
- chỉ gỡ `ver` trùng version WP core (giữ cache-bust của asset)
- custom login style
- upload SVG (chỉ `manage_options`, lọc script); AVIF/WebP dùng hỗ trợ sẵn của core

### 8.2 Common hook / Asset pipeline

File: `app/Hooks/CommonHook.php`

- enqueue CSS/JS chung
- localize biến AJAX (`underscores_params`)
- cấu hình script type module
- preconnect Google Fonts chỉ khi có stylesheet fonts.googleapis.com (filter `wp_resource_hints`)
- khi có child theme: child `ThemeHook` tự dựng pipeline (script `strategy => defer`), parent chỉ localize + module

### 8.3 AJAX posts

File: `includes/ajax/post-ajax.php`

Action: `underscores_ajax_get_posts`

Input:

- `posts_per_page` (int, clamp 1..50)
- `paged` (int >= 1)
- `taxonomies` dạng:
  - `{ taxonomy_key: [term_id, ...] }`

Output (`wp_send_json_success` / `wp_send_json_error`):

- có bài: `{ success: true, data: { posts[], pagination_html } }`
- rỗng: `{ success: true, data: { posts: [], empty_message } }`
- lỗi: `{ success: false, data: { message } }` (HTTP 403 nonce sai / 500)

Query prime sẵn cache thumbnail + tác giả (không N+1). Trang được page cache > 12h → xem "Lưu ý page cache + nonce" trong `.ai/rules/ajax-handler.md`.

## 9) Quy trình phát triển

### 9.1 Cài dependency

```bash
composer install
```

### 9.2 Sử dụng AI Automation
Khi phát triển tính năng mới, ưu tiên dùng các **Skills** đã định nghĩa để code sinh ra đúng chuẩn.

Ví dụ: *"Tạo một AJAX endpoint mới để xử lý form liên hệ"* -> AI sẽ sử dụng skill `create-ajax-endpoint`.

### 9.3 Kiểm tra syntax PHP

```bash
find . -type f -name '*.php' -print0 | while IFS= read -r -d '' f; do
  php -l "$f" || exit 1
done
```

## 10) Quy ước code (bắt buộc)

- **Tuân thủ tuyệt đối các Rules trong `.ai/rules/`**.
- Escape output đúng ngữ cảnh: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- Validate/sanitize input trước query: `sanitize_key`, `sanitize_text_field`, `absint`, ...
- Luồng AJAX luôn có nonce check.
- Không viết query SQL thô nếu WP API đã hỗ trợ.
- Không query nặng trong loop.

## 11) Troubleshooting nhanh

### Lỗi thiếu CSS/JS template

- Kiểm tra thư mục `/template/assets/...` tồn tại trên site.

### AJAX trả lỗi xác thực

- Kiểm tra nonce `underscores-ajax-security` đã được localize vào `underscores_params`.

---

### Visual Guide
Xem chi tiết trình bày về cấu trúc, quy tắc và kỹ năng tại: [preview.html](https://coreai.underscoresweb.dev/aicode/preview.html)

