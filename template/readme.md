# NHATO Collection — Design System

NHATO Collection is a Vietnamese architecture, interior, art and lifestyle publisher and
studio based in Hanoi (R4 Royal City, Nguyễn Trãi, Thanh Xuân). The brand runs a single
public surface — an editorial website under the NHATO REVIEW / NHATO Collection banner —
that mixes studio work (spaces it designs and builds) with a magazine (articles and YouTube
reviews of houses, villas, offices and interiors). Its own footer line states the position:
*"NHATO Collection là không gian kết nối giữa kiến trúc, nội thất, nghệ thuật và phong cách
sống, hướng đến việc tạo nên những giá trị bền vững và truyền cảm hứng cho những trải nghiệm
sống."*

The site is organised as five sections — **Network** (editorial and news), **Home / Không
gian** (spaces), **Art / Nghệ thuật**, **Taste / Phong vị** and **Original / Bản sắc** —
plus About and Contact.

Everything in this kit is plain **HTML, CSS and vanilla JS**. There is no React, no build
step and no npm. Link `styles.css` and write markup.

## Sources

| Source | What was taken from it |
| --- | --- |
| Figma file **NHATO 2.fig** (mounted; 1 page, 10 frames, 2,832 nodes) | Colour and type values, frame geometry, the five component families, the logo bitmap, article/category imagery |
| GitHub **https://github.com/thanhtungtav4/nhatoreview** (branch `main`, subtree `template/`) | The shipped static template: `assets/css/common/variables.css` token set, BEM component CSS, header/footer partials, homepage markup, icon SVG paths, homepage photography |

Both are worth exploring further. The repo's `template/README.md` documents the intended
WordPress mapping (partials → template parts, `href` → `home_url()`/`get_permalink()`), and
the Figma file holds five page frames this kit has not yet built out.

---

## CONTENT FUNDAMENTALS

**Language.** Vietnamese for everything a reader reads; English for navigation and
structural labels. That split is deliberate and consistent: nav is `Network / Home / Art /
Taste / Original`, utility strings are `Call us:`, `Follow us on`, `Contact us`, `Your email
address`, `Designed by NHATO Design` — while headlines, body copy, section headings
(`Khám phá`, `Hỗ trợ`, `Không gian tiêu biểu`, `Bài viết khác`) and every article are
Vietnamese. Never translate the English labels; never leave a headline in English.

**Casing.** Display headlines are set in full caps: `KHÁM PHÁ / PHONG CÁCH SỐNG`,
`KIẾN TẠO GIÁ TRỊ SỐNG`, `TIN MỚI NHẤT`, `TIN NỔI BẬT`, `VIDEO NỔI BẬT`. Section titles in
the paper band are sentence case (`Không gian tiêu biểu`, `Tư duy thiết kế sáng tạo`).
Eyebrows and buttons are uppercase with wide tracking (`DỰ ÁN`, `KHÁM PHÁ NHATO`, `GỬI NGAY`).
Article titles use Title Case With Most Words Capitalised — a Vietnamese magazine habit:
*Thiết Kế Biệt Thự Art Deco: Đỉnh Cao Kiến Trúc Xa Hoa*.

**Person.** The brand speaks as *chúng tôi* (we) and addresses the reader as *bạn* (you),
warmly but formally: *"Để lại thông tin chúng tôi sẽ gọi lại cho bạn ngay khi có thể"*,
*"Chúng tôi rất mong được đồng hành cùng bạn."* Never *tôi*, never slangy *mình*.

**Register.** Aspirational and unhurried, but concrete. Copy leans on nouns of value —
*giá trị, bản sắc, cảm hứng, trải nghiệm, tinh tế, bền vững* — and pairs them with a
practical promise: *"Mỗi không gian được tạo nên từ sự thấu hiểu nhu cầu, sự sáng tạo trong
giải pháp và sự tận tâm trong từng chi tiết."* The editorial voice is one notch louder than
the studio voice — YouTube titles shout (*CHỈ 15 TỶ Từ Đất Trống Hóa Biệt Thự ĐẸP TỪNG
CENTIMET Ai Thấy Cũng Mê!!*) while site copy never does.

**Length.** Headlines 2–6 words per line, two lines maximum. Leads one sentence, ~20–30
words, no full stop in the hero. Card descriptions 6–12 words (*"Mang đến dấu ấn riêng"*,
*"Đẳng cấp & sang trọng"*). Article excerpts run ~30 words and end in an ellipsis.

**Micro-copy patterns.** Datelines are always `Ngày đăng: DD/MM/YYYY` with the label in
medium weight. Counters read `29320 Lượt xem`. Phone is written `0968 677 337`. Process steps
are numbered `01`–`05` with a one-word verb (`Lắng nghe`, `Định hướng`, `Triển khai`,
`Thi công`, `Hoàn thiện`) and a short gloss.

**Emoji.** None. Not in the Figma file, not in the repo. Do not introduce any. The only
non-alphabetic glyphs the brand uses as punctuation are `↗` (outbound link), `«` `»` `...`
(pagination), `›` (see more) and `–` in time ranges.

---

## VISUAL FOUNDATIONS

**The idea.** Warm dark interiors photographed in low light, a single antique gold, and
almost nothing else. Every surface is either a photograph under a scrim, near-black ink, or
a warm off-white paper. Gold is a line weight, not a fill — it appears as 1px strokes,
hairline underlines, small caps and the logo gradient, and only becomes a solid ground on a
chip, a submit button or a button hover.

**Colour.** `#DDA642` gold (127 uses in the Figma file) is the only accent. Ink is
`#1E1E1E` (289 uses) — the header scrim, the value band and the footer all sit on it. Paper
is `#F0EFED`. Greys run `#CECECE` for rules, `#9D9D9D` for the 1px frame around the hero and
the category tiles, and `#797979`/`#757575` for meta text. `#FF0000` is reserved for
required-field marks and the YouTube play badge. There are only two background colours on
any page: paper and ink. The shipped web build warmed these slightly (`#C8AA6C` gold,
`#1D1C1A` charcoal, `#F4F2EE` paper); those values are kept in `tokens/colors.css` for
reference only — every component resolves to the Figma value.

**Type.** Two families do the work. **Cormorant Garamond** (Bold/SemiBold, −0.045em
tracking, 100 % line-height) for every display line and centred editorial headline — never
below 24px. **Manrope** for the interface and body: 16 SemiBold card titles at 26px leading,
16 Medium nav, 14 Bold buttons, 13 Regular body (the file's most-used size, 183 times).
**Ibarra Real Nova** 14 appears only on datelines; **Inter** carries long article bodies
(14/24, 16/28) and the gold chip; **Montserrat** and **Plus Jakarta Sans** are incidental.
Times New Roman appears on unstyled Figma layers and should not be used in new work.

**Layout.** A 1440 frame with a 72px page gutter; the build narrows content to a 1296px
column with a fluid gutter. Grids are 4-up for projects and news, 3-up for articles and
videos, 5-up for the category panel. The editorial pages stack sections 40px apart with
0.5px `#CECECE` hairlines between them and a centred uppercase headline starting each block.
Nothing is fixed except the header, which is absolutely positioned over the hero and turns
into an opaque bar once scrolled.

**Backgrounds.** Photography, full-bleed, always. No illustration, no pattern, no texture,
no gradient used as decoration — gradients exist only as legibility scrims:
`linear-gradient(180deg,#1E1E1E,rgba(30,30,30,.6))` under the header,
`linear-gradient(270deg,transparent 32.21%,rgba(0,0,0,.75))` across the hero, and
`transparent 40% → rgba(16,14,12,.82)` at the foot of a project card. Protection is always a
gradient, never a solid capsule behind text.

**Imagery.** Warm, gold-lit interiors: dark timber, brass, deep blue-green walls, evening
lamps. No people in frame. No black and white, no grain, no cool tint. Photographs are
cropped to `aspect-ratio: 0.9` for project cards, `1.66` for article cards, `1.5` for news
cards, a 94px square for rail thumbs and `436px` tall for a lead story.

**Corners and borders.** Square. Cards, images and buttons have no radius at all. The only
exceptions in the whole system are the homepage category panel (`0.45rem`) and the pill
(`1000px`) used for the "Tin mới" chip and circular arrow buttons. Borders are always 1px —
`#CECECE` on paper, gold on anything interactive, `rgba(244,242,238,.18)` on dark. The icon
buttons in Figma use a `1.333px` stroke.

**Shadow.** Nearly absent. `0 4px 4px rgba(0,0,0,.25)` under the header,
`0 1rem 2.8rem rgba(29,28,26,.06)` under the category panel, and nothing else. Cards do not
lift. There are no inner shadows.

**Transparency and blur.** No blur anywhere — no backdrop-filter, no frosted panels. Alpha
is used for scrims, for the `rgba(255,255,255,.34)` category panel, and for muting text
(`rgba(29,28,26,.68)` on paper, `rgba(244,242,238,.84)` on ink).

**Hover.** Nav links fade to `#E4D0A1` and sweep a gold 1px underline from left over 220ms.
Social icons brighten and lift 2px. Outline buttons invert to a solid gold ground with ink
text. Card images scale to `1.045` over 500ms while the card itself stays put. Article
titles turn gold. The newsletter submit slides 3px right.

**Press and focus.** Buttons drop 1px on `:active`. Focus is a 2px `#F0CA86` outline at 4px
offset — never removed.

**Motion.** One curve, `cubic-bezier(.2,.7,.2,1)`, and three durations: 180ms for colour
changes, 220ms for the underline sweep, 500ms for image zoom. Nothing bounces, nothing
springs, nothing autoplays. `prefers-reduced-motion` cuts all of it.

---

## ICONOGRAPHY

The brand has **no icon font and no third-party icon library**. Icons are hand-drawn inline
SVG in the shipped build — the repo's `assets/icons/README.md` says so explicitly: *"Icon
giao diện đang dùng inline SVG… không nhúng icon bằng ảnh raster."* The Figma file names its
glyphs after the open sets they were pulled from (`vuesax/linear/arrow-down`, `basil:arrow-
right-solid`, `ci:menu-alt-05`, `tdesign:time`, `ant-design:double-right-outlined`,
`mingcute:right-line`, `qlementine-icons:youtube-16`, `logos:youtube-icon`) but the exported
paths are what ship.

All 20 of them are collected verbatim in **`assets/icons/nhato-icons.svg`** and inlined by
**`assets/icons/nhato-icons.js`**:

```html
<body>
<script src="assets/icons/nhato-icons.js"></script>
…
<svg class="nh-icon"><use href="#nh-arrow-right"></use></svg>
```

The script injects the sprite and copies each symbol's `viewBox` onto its host `<svg>`, so
you only ever set a size in CSS. Available: `facebook tiktok youtube x arrow-right
arrow-down menu eye calendar clock house apartment villa office commercial listen spark
build schedule handover`.

Style: single-stroke line icons, `fill="none"`, `stroke="currentColor"`, round caps and
joins. Stroke weight varies by drawing size — 1.2 at 24–32px, 1.25 at 64px, 1.35 for the
social glyphs, 2.667 for the 32px menu. Facebook and the YouTube play triangle are the only
filled shapes. Category icons are drawn at 64px and rendered at 48px in gold; process icons
are drawn at 32px and rendered at 36px in paper. **No emoji, ever.** `↗ « » ... ›` are used
as typographic marks rather than icons.

The brand mark is a raster export, not a vector: `assets/logo/nhato-collection-lockup.png`
(full lockup with the keyhole COLLECTION rule) and `assets/logo/nhato-wordmark.png` (gold
NHATO wordmark, paired in markup with a letter-spaced `COLLECTION` subline). It is a gold
gradient, so it sits on ink or paper and never on gold. Do not redraw or recolour it.

---

## Index

| Path | What it is |
| --- | --- |
| `styles.css` | The one file consumers link. Google Fonts import + every token and component import. |
| `tokens/colors.css` | Brand gold, ink, neutrals, semantic colours, scrims, semantic aliases. |
| `tokens/typography.css` | Font stacks, the size scale, line heights, weights, tracking. |
| `tokens/layout.css` | Page grid, auto-layout gaps, radii, borders, shadows, scrims, motion. |
| `tokens/base.css` | Reset, base element styles, `.nh-container`, `.nh-eyebrow`, `.sr-only`. |
| `tokens/figma-variables.css` | The 4 Figma Variables in the file (light + dark modes). |
| `components/chrome/chrome.css` | Brand lockup, social row, site header, site footer. |
| `components/actions/actions.css` | Buttons, hero CTA, outline link, text link, icon button, chip, pagination. |
| `components/content/content.css` | Section heads, hairlines, category tile, project card, article card, news card, the three Figma article rows, meta and dateline, process step. |
| `components/forms/forms.css` | Field, label, newsletter form. |
| `components/media/media.css` | Hero, slide index, dark band, page intro. |
| `guidelines/*.card.html` | 18 foundation specimen cards (Colors, Type, Spacing, Brand). |
| `ui_kits/nhato-web/` | The website recreation — 10 screens, one per Figma frame, plus `chrome.js`, `pages.css` and its own README. |
| `templates/homepage/` | Starting-point template: the full marketing homepage. |
| `templates/article/` | Starting-point template: the long-form article page with sidebar rail. |
| `templates/news/` | Starting-point template: the editorial index — lead story, news grid, video row. |
| `templates/contact/` | Starting-point template: masthead, enquiry form, map and CTA band. |
| `assets/logo/`, `assets/images/`, `assets/icons/`, `assets/js/` | Logo, photography, icon sprite, site behaviour. |
| `SKILL.md` | Agent-skill entry point. |
| `github.md` | Upstream repository association and sync record. |

## Component classes

`nh-brand` · `nh-social` · `nh-header` · `nh-footer` · `nh-btn` (`--solid`, `--outline`,
`--outline-ink`) · `nh-cta` · `nh-outline-link` · `nh-text-link` · `nh-icon-btn` ·
`nh-pagination` · `nh-chip` · `nh-section-head` / `nh-section-title` · `nh-headline` ·
`nh-hairline` · `nh-tile-strip` / `nh-tile` · `nh-round-arrow` · `nh-category-panel` /
`nh-category` · `nh-project-card` · `nh-article-card` ·
`nh-news-card` · `nh-article-row` · `nh-article-compact` · `nh-thumb-card` · `nh-meta` ·
`nh-dateline` · `nh-process-step` · `nh-field` / `nh-label` · `nh-newsletter` · `nh-hero` ·
`nh-slide-index` · `nh-band` · `nh-page-intro`

### Components

The Figma file defines five standalone components and leaves four of them auto-named, so the
exports keep the Figma names. The canonical implementation of each is a CSS class, because
this kit is plain HTML/CSS/JS. A thin React wrapper ships alongside each one for consumers
building in React — the wrappers render exactly the same markup and add no styling.

| Component | Figma component | Class | Reads as |
| --- | --- | --- | --- |
| `Frame5` | `Frame 5` (header, 1440×94) | `.nh-header` | site header |
| `Frame2144771281` | `Frame 2144771281` (880-wide article row) | `.nh-article-row`, grid variant `.nh-news-card` | article row |
| `Frame2144771605` | `Frame 2144771605` (388-wide compact row) | `.nh-article-compact` | compact article row |
| `Frame1731` | `Frame 1731` (280×268 thumb card) | `.nh-thumb-card` | thumb card |
| `Icon` | `vuesax/linear/arrow-down` | `#nh-arrow-down` in the sprite | any sprite glyph |
| `VuesaxLinearArrowDown` | `vuesax/linear/arrow-down` | `#nh-arrow-down` | the arrow-down glyph |

## Intentional additions

- **`Icon` wrapper.** The Figma file's only icon component is `vuesax/linear/arrow-down`;
  one generic `Icon` covers it and the other 19 glyphs rather than shipping 20 components.
- **React wrappers.** `Frame5`, `Frame2144771281`, `Frame2144771605`, `Frame1731` and `Icon`
  exist only so React consumers get a typed props contract. They hold no styles — delete
  them and the CSS still works.
- **Icon sprite + loader** (`assets/icons/nhato-icons.svg` / `.js`). The sources ship icons
  as loose inline SVG repeated in every file; collecting them into one sprite is a packaging
  change, not a design one — the paths are unchanged.

## Substitutions and gaps

- **Where Figma and the shipped build disagree, Figma wins.** Three places differ and the kit
  follows the Figma file: the header logo is the 180×62 lockup with the slogan (not a
  wordmark plus a typed COLLECTION line), the nav row opens with a 32px white menu glyph
  (`ci:menu-alt-05`) and the "Call us" plate is outlined in `#1E1E1E`, not gold; and the row
  under the hero is five 288×232 photo tiles under an `rgba(0,0,0,.8)` layer
  (`.nh-tile`), not the build's white icon panel. `.nh-category-panel` / `.nh-category` are
  kept in `components/content/content.css` for parity with the live site.

- **Vàng chính thức là `#DDA642`** (giá trị Figma). The web build's `#C8AA6C` is recorded in
  `tokens/colors.css` as `--nh-gold-warm` but is not used anywhere.
- **No font binaries.** Manrope, Cormorant Garamond, Ibarra Real Nova, Inter, Montserrat and
  Plus Jakarta Sans are loaded from Google Fonts at their exact family names. If NHATO
  licenses specific cuts, drop the files in and swap the import.
- **Arial and Times New Roman** appear in the Figma file (30 and 44 uses) on layers that were
  never restyled. They resolve to system faces and are not part of the intended palette.
- **The Figma Variables collection is not a brand token set** — it holds four iOS-style
  background colours (`Backgrounds/Tertiary`, etc.). It is preserved in
  `tokens/figma-variables.css` for completeness; the real token values come from the frame
  literals and the repo's `variables.css`.
- **No text styles** are defined in the Figma file, so `tokens/typography.css` was built from
  the measured type usage instead.
- **Social URLs, the newsletter endpoint and the contact endpoint** are undefined in both
  sources and are left as placeholders.
