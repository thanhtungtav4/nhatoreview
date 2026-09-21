---
name: convert-html-to-wp
description: "Use when converting a static HTML template/page (e.g. template/home.html, about.html) into this WordPress theme. Scans the HTML, classifies each section into ACF / Custom Post Type / Taxonomy / static, produces a plan, and STOPS for user confirmation before writing any code."
---

# Convert HTML → WordPress

Chuyển 1 file HTML tĩnh (vd `template/home.html`) sang WP theme này: scan → phân loại dữ liệu →
ra plan → **chờ user duyệt** → mới build. Tuân thủ `lazy-first.md` + `data-rendering.md`.

## Nguyên tắc bắt buộc

1. **Phase 1 (Scan + Plan) KHÔNG tạo/sửa file nào.** Chỉ đọc HTML + xuất plan.
2. **Dừng hẳn sau plan, chờ user xác nhận/chỉnh.** Không build cho đến khi user nói OK.
3. Phần mơ hồ → hỏi user, KHÔNG tự quyết.

## Khi nào dùng

- Có file HTML (1 page hoặc nhiều page trong `template/*.html`) cần dựng thành theme.
- Trước khi viết ACF JSON / CPT / template.

## Heuristic phân loại (mặc định)

Quét từng vùng nội dung (section/block lặp), gán loại theo nấc đầu tiên đúng:

| Tín hiệu trong HTML | Loại WP | Vì sao |
|---|---|---|
| Nhiều item **giống cấu trúc** + có link sang **trang chi tiết** (vd `news/bai-1.html`) | **Custom Post Type** | Mỗi item là 1 bản ghi có URL/single riêng. |
| Item lặp dùng để **lọc/nhóm** CPT (danh mục, thẻ, thương hiệu) | **Taxonomy** (gắn vào CPT) | Phân loại, không phải nội dung gốc. |
| Item lặp nhưng **không có trang riêng** (logo đối tác, slide banner, social) | **ACF Repeater** | Danh sách thuộc về 1 page/option, không cần single. |
| Nội dung **xuất hiện 1 lần / page** (CTA, số liệu, gallery) | **ACF field/group** theo page template | Nội dung tĩnh admin nhập, gắn page. |
| Tiêu đề / nội dung / ảnh banner / mô tả ngắn của page | **WP core** (`the_title`, `the_content`, featured image, excerpt) | Core đã có — KHÔNG tạo field `heading`/`intro`/`banner`. |
| List link tới page/post (menu header/footer, cột "Dịch vụ") | **WP Menu** (`register_nav_menus` + `wp_nav_menu`) | Core đã có, admin kéo thả, tự có `current-menu-item`. |
| Logo / favicon / tên site / slogan | **WP core** (`the_custom_logo`, Site Icon, `bloginfo`) | Core đã có — KHÔNG tạo field. |
| Giá trị **toàn site** khác (hotline, email, địa chỉ, social, footer, tracking) | **ACF Options** (`group_theme_settings`) | Dùng chung mọi page. |
| Markup **cố định, không đổi** (icon trang trí, layout wrapper) | **Static** (hardcode trong partial) | Không cần admin sửa → đừng tạo field. |

> Lazy-first: KHÔNG tạo CPT/taxonomy/field "để dành". Chỉ tạo cho vùng admin thật sự cần sửa.
> Vùng tĩnh → để thẳng trong template, không bịa field.

## DRY — Tách dùng chung (BẮT BUỘC)

Cái gì lặp lại thì tách ra dùng chung, KHÔNG copy-paste lặp đi lặp lại. Hai chiều:

### A. Markup lặp → tách PHP partial
| Lặp trong HTML | → Tách thành | Dùng |
|---|---|---|
| Header/Footer (mọi page) | `header.php` / `footer.php` | `get_header()` / `get_footer()` |
| Khối UI lặp (card tin, card sản phẩm, item box, nút, breadcrumb) | `partials/components/{tên}.php` | `get_template_part('partials/components/{tên}', null, $data)` |
| Section khung lặp (banner, CTA, section title) | `partials/sections/{tên}.php` | `get_template_part(...)` |
| Đoạn HTML giống nhau xuất hiện **≥2 lần** | 1 partial nhận `$args` | truyền data khác nhau vào |

- Truyền dữ liệu qua tham số thứ 3 của `get_template_part($slug, null, $args)`, partial đọc `$args`.
- Card trong vòng lặp CPT/repeater → MỘT partial component, gọi trong loop. KHÔNG dán lại markup mỗi item.
- Nguyên tắc: thấy markup lặp lần 2 → tách partial ngay (xem `lazy-first.md`: tái dùng > viết lại).

### B. Field/giá trị dùng chung → Theme Settings (ACF Options)
| Xuất hiện ở | → Đặt tại | Đọc bằng |
|---|---|---|
| Nhiều page / toàn site (hotline, email, địa chỉ, social, tracking) — trừ logo/favicon/tên site/menu/copyright: dùng WP core | **Theme Settings** `group_theme_settings.json` (tab Liên hệ/Mạng xã hội/Mã tracking, thêm tab khi dự án cần) | `underscores_get_option('footer_contact_section')['items']` |
| Chỉ 1 page cụ thể (hero của trang About) | ACF field gắn page template đó | `get_fields()` |

- KHÔNG lặp lại field "hotline", "social" ở từng page → đưa lên Options 1 lần, mọi page đọc chung.
- Header/Footer là partial chung → data của chúng lấy từ Theme Settings, không phải field riêng từng page.

## Text mockup → gợi ý ACF (BẮT BUỘC khi tạo field)

Text đang có trong HTML mockup (vd "Thông tin liên hệ", "Giờ làm việc: 08:00 - 17:00", "info@example.com")
KHÔNG vứt đi — đưa vào ACF làm **gợi ý cho admin dễ nhập**. Cơ chế: **placeholder + instructions**, KHÔNG `default_value`.

- Field để **trống** (admin tự nhập). KHÔNG `default_value` → tránh leak text mockup ra frontend khi admin chưa nhập.
- `placeholder`: đặt = text mockup. **CHỈ field có hỗ trợ**: `text`, `textarea`, `url`, `email`, `number`, `password`.
- `instructions`: với field KHÔNG có placeholder (`image`, `link`, `select`, `repeater`, `true_false`, `wysiwyg`) → ghi text mockup vào instructions, vd `"VD trong thiết kế: 372-374 Đường 3/2..."`.
- Data thật (địa chỉ, sđt, email) cũng dùng placeholder — gợi ý, không điền sẵn.

| Field type | Gợi ý đặt ở |
|---|---|
| text, textarea, url, email, number | `placeholder` = text mockup |
| image, link, select, repeater, true_false, wysiwyg | `instructions` = "VD trong thiết kế: ..." |

> Mục đích: admin mở wp-admin thấy ngay nội dung thiết kế gốc làm mẫu, nhập nhanh, không phải mò lại file HTML.

## Layout ACF cho admin (BẮT BUỘC — trình bày gọn)

Field xếp dọc 100% width = admin cuộn mệt. Set layout để thao tác nhanh:

- **`wrapper.width`** (% ) — gộp field ngắn cùng hàng:
  - Field liên quan/ngắn (label + giá trị 1 dòng: hotline, email, sđt, ngày, link) → `"wrapper": {"width": "50"}` (2 cột) hoặc `"33"` (3 cột).
  - Field dài (textarea, wysiwyg, repeater, image lớn) → để full `"width": ""` (100%).
- **Tab** (`type: tab`) — nhóm field theo khối UI/section, mỗi tab 1 chủ đề (Liên hệ / Mạng xã hội / Hero / Đối tác...). Tab dài → tách nhiều tab thay vì 1 trang field dằng dặc.
- **Group** (`type: group`, `layout: block`) — bọc field cùng 1 section (vd `footer_contact_section`) để gọn + đọc bằng `underscores_get_option('footer_contact_section')['title']`.
- **Repeater** — `layout: table` cho row nhiều cột ngắn (social: platform/icon/url); `layout: block` cho row field phức tạp/lồng nhau.
- **`instructions`** ngắn gọn mỗi field (kèm gợi ý text mockup ở trên).

| Field | wrapper.width gợi ý |
|---|---|
| hotline, email, sđt, ngày, 1 link | 50 hoặc 33 (cùng hàng) |
| title, label ngắn | 50 |
| textarea, wysiwyg, repeater, image | "" (full) |

> Mục tiêu: 1 section nhìn 1 màn hình, field liên quan nằm cạnh nhau, không cuộn vô tận.

## Phase 1 — SCAN + PLAN (read-only)

Đọc (các) file HTML, rồi xuất **đúng 5 phần** sau, KHÔNG viết code:

### 1. ASCII map của page
```
home.html
┌─────────────────────────────┐
│ HEADER        [Options]     │
│ HERO          [ACF page]    │
│ ABOUT         [ACF page]    │
│ NEWS LIST     [CPT: post]   │  ← + Taxonomy: category
│ PARTNERS      [ACF repeater]│
│ FOOTER        [Options]     │
└─────────────────────────────┘
```

### 2. Bảng vùng → loại dữ liệu
Cột "Gợi ý (text mockup)" = text gốc trong HTML, sẽ thành `placeholder`/`instructions` (xem mục "Text mockup → gợi ý ACF").

| Section | Loại | Lý do | Field/data dự kiến | Gợi ý (text mockup) |
|---|---|---|---|---|
| Hero | ACF (page about) | tĩnh, 1 lần | heading, subheading, image, cta(link) | heading="Unleashing Your Style...", cta="Xem thêm" |
| News list | CPT `news` | lặp + có single | title, thumbnail, date, content | — (data thật từ DB) |
| ... | ... | ... | ... | ... |

### 3. Danh sách CPT + Taxonomy đề xuất
- CPT `news` (slug `tin-tuc`) — supports: title, editor, thumbnail. Single: `single-news.php`.
- Taxonomy `news_category` gắn `news` (slug `danh-muc-tin`).

### 4. Mapping file: section → file WP
| Section | File WP sẽ tạo |
|---|---|
| Hero | `partials/sections/hero.php` + field trong `acf-json/group_page_*.json` |
| News list | `partials/sections/news-list.php` (WP_Query) + CPT class `app/PostTypes/News.php` |
| ... | ... |

Kèm **partial dùng chung tách ra** (DRY) + **field đưa lên Theme Settings**:
| Lặp / dùng chung | → Tách thành |
|---|---|
| News card (lặp trong list + trang category) | `partials/components/card-news.php` (1 file, gọi trong loop) |
| Header / Footer | `header.php` / `footer.php`, data từ Theme Settings |
| Hotline, social | Theme Settings `group_theme_settings` (không lặp field từng page) |
| Logo / favicon / menu | WP core: `the_custom_logo()`, Site Icon, `wp_nav_menu` (không tạo field) |

### 5. Câu hỏi mờ (cần user trả lời)
- "Partners là CPT (có trang đối tác riêng) hay chỉ ACF repeater logo?" 
- "News có cần phân trang / filter theo category không?"
- (chỉ hỏi chỗ thật sự mơ hồ, đừng hỏi cho có)

**→ Kết thúc Phase 1 tại đây. In: "Plan xong. Duyệt / chỉnh để mình build (CPT → Taxonomy → ACF → template)."**
**KHÔNG build cho đến khi user xác nhận.**

## Phase 2 — BUILD (chỉ sau khi user OK)

Theo thứ tự (mỗi loại có skill riêng):
1. **CPT** → skill `create-cpt` (class trong `app/PostTypes/`, register trong `includes/bootstrap.php`).
2. **Taxonomy** → class trong `app/Taxonomies/`, register init.
3. **ACF field group** → ghi `acf-json/group_*.json`, location đúng (page template / CPT / options). Validate: `php underscores-child/acf-json/validate.php --summary`.
4. **Template/partials** → page template + partials/sections. Render data theo `data-rendering.md` (guard `?? ''`, ẩn khối rỗng, escape output). List CPT dùng `WP_Query` + `get_template_part`.
   - **DRY**: markup lặp ≥2 lần → MỘT partial component nhận `$args`, gọi nhiều nơi. Card trong loop = 1 partial gọi trong vòng lặp.
   - Field dùng chung (hotline/social/footer) → đọc từ Theme Settings; logo/favicon/menu → WP core, KHÔNG lặp field từng page.
5. Asset CSS/JS theo page hook (xem child `child-theme.md`) + `performance.md`: script `strategy defer`, thư viện không dùng bỏ qua `underscores_child_common_libraries`, ảnh size phù hợp, ảnh hero `is_first` eager + `fetchpriority=high`, `<img>` tay có width/height.

Sau build: `php -l`, `composer dump-autoload` (nếu thêm class), nhắc user **Sync** ACF + Settings→Permalinks (flush rewrite cho CPT).

## Checklist

- [ ] Phase 1 KHÔNG tạo file
- [ ] Đủ 5 phần plan (map, bảng, CPT/tax, mapping file, câu hỏi)
- [ ] Đã hỏi phần mơ hồ, không tự quyết
- [ ] Chờ user OK trước Phase 2
- [ ] Không tạo CPT/field cho vùng tĩnh (lazy-first)
- [ ] Markup lặp ≥2 lần đã tách partial dùng chung (không copy-paste)
- [ ] Field dùng chung/toàn site đã đưa lên Theme Settings (không lặp từng page)
- [ ] Text mockup đã đưa vào `placeholder`/`instructions` làm gợi ý admin (KHÔNG `default_value`, KHÔNG vứt đi)
- [ ] Field ngắn đã set `wrapper.width` gộp hàng; tab/group chia khối gọn (không xếp dọc 100% dằng dặc)
- [ ] Checklist `performance.md` đạt (defer, asset theo trang, ảnh LCP, không query trong loop)
- [ ] Build xong: validate ACF, dump-autoload, nhắc Sync + flush permalink
