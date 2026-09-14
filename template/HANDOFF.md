# NHATO Collection — Claude Code handoff

Bạn đang có toàn bộ design system NHATO Collection dưới dạng **HTML + CSS + vanilla JS thuần**. Không React, không build step, không npm. Mở bất kỳ file `.html` nào bằng browser là chạy.

## Bắt đầu ở đâu

1. Đọc `readme.md` — đây là tài liệu chính: content rules (song ngữ VI/EN), visual foundations, iconography, danh sách component classes, và các quyết định thiết kế đã chốt (Figma thắng khi lệch với web build).
2. Link một file CSS duy nhất:
   ```html
   <link rel="stylesheet" href="styles.css">
   <script src="assets/icons/nhato-icons.js"></script>
   ```
   `styles.css` đã import toàn bộ tokens + components.
3. Xem `ui_kits/nhato-web/` — 10 screen dựng lại đầy đủ website, mỗi screen tương ứng 1 Figma frame. Đây là reference chuẩn nhất cho markup.

## Cấu trúc

| Path | Nội dung |
| --- | --- |
| `styles.css` | Entry point duy nhất. Google Fonts + toàn bộ import. |
| `tokens/` | `colors.css`, `typography.css`, `layout.css`, `base.css`, `figma-variables.css` — 119 CSS custom properties. |
| `components/` | CSS theo nhóm: `chrome/` (header, footer, brand, social), `actions/` (button, chip, pagination, icon button), `content/` (card, section head, process step, meta), `forms/`, `media/` (hero, band, page intro). Tất cả BEM-ish prefix `nh-`. |
| `assets/` | `logo/` (2 PNG — logo là raster, không redraw), `images/` (photography), `icons/` (sprite 20 glyph + loader JS), `js/` (site behaviour). |
| `ui_kits/nhato-web/` | 10 screen hoàn chỉnh + `chrome.js`, `pages.css`. |
| `templates/` | 4 starting point: `homepage/`, `article/`, `news/`, `contact/`. Mỗi folder là một `.dc.html` + `ds-base.js`. |
| `guidelines/` | 18 specimen card (Colors, Type, Spacing, Brand). |
| `github.md` | Liên kết repo gốc + lịch sử sync. |

## Component classes có sẵn

`nh-brand` `nh-social` `nh-header` `nh-footer` `nh-btn` (`--solid` `--outline` `--outline-ink`) `nh-cta` `nh-outline-link` `nh-text-link` `nh-icon-btn` `nh-pagination` `nh-chip` `nh-section-head`/`nh-section-title` `nh-headline` `nh-hairline` `nh-tile-strip`/`nh-tile` `nh-round-arrow` `nh-category-panel`/`nh-category` `nh-project-card` `nh-article-card` `nh-news-card` `nh-article-row` `nh-article-compact` `nh-thumb-card` `nh-meta` `nh-dateline` `nh-process-step` `nh-field`/`nh-label` `nh-newsletter` `nh-hero` `nh-slide-index` `nh-band` `nh-page-intro`

## Nguyên tắc không được phá

- **Chỉ 2 màu nền trên mọi trang**: paper `#F0EFED` và ink `#1E1E1E`. Gold `#DDA642` là **nét**, không phải mảng — 1px stroke, hairline underline, small caps, logo gradient; chỉ thành nền đặc ở chip, submit button và button hover.
- **Bo góc vuông tuyệt đối.** Ngoại lệ duy nhất: category panel `0.45rem` và pill `1000px` cho chip "Tin mới" + nút mũi tên tròn.
- **Hai font**: Cormorant Garamond (display, tracking `-0.045em`, line-height 100%, không dưới 24px) và Manrope (UI + body). Ibarra Real Nova chỉ cho dateline. Inter cho body bài viết dài.
- **Motion**: một curve `cubic-bezier(.2,.7,.2,1)`, ba duration — 180ms màu, 220ms underline sweep, 500ms image zoom. Không bounce, không autoplay. `prefers-reduced-motion` tắt hết.
- **Không blur, không backdrop-filter, không gradient trang trí.** Gradient chỉ dùng làm scrim cho legibility.
- **Không emoji.** Chỉ `↗ « » ... ›` làm dấu typographic.
- **Ảnh**: nội thất ấm, ánh sáng vàng, không người trong khung, không đen trắng, không grain. Aspect ratio cố định theo loại card (0.9 project, 1.66 article, 1.5 news).
- Focus ring `2px #F0CA86` offset `4px` — không bao giờ remove.

## Nếu port sang framework

Kit này là CSS-first nên port rất nhẹ: giữ nguyên `tokens/` + `components/` làm global stylesheet, wrap markup thành component của framework đích. Các React wrapper trong `components/` (`Frame5`, `Frame2144771281`, `Frame2144771605`, `Frame1731`, `Icon`) chỉ là typed props contract, không chứa style — xoá đi CSS vẫn chạy.

## Còn thiếu

- Social URLs, newsletter endpoint, contact endpoint — đang là placeholder.
- Font binaries — đang load từ Google Fonts. Nếu NHATO license cut riêng thì drop file vào và đổi `@import` trong `styles.css`.
- 5 page frame trong Figma chưa dựng.
- Arial / Times New Roman xuất hiện trong Figma trên layer chưa restyle — không phải phần của palette, đừng dùng.

## File do compiler sinh ra — đừng sửa tay

`_ds_bundle.js`, `_ds_manifest.json`, `_adherence.oxlintrc.json`. Nếu chỉ code HTML/CSS thuần thì không cần tới chúng.
