# NHATO Collection — website UI kit

A static, plain HTML/CSS/JS recreation of the NHATO Collection site. No framework, no
build step: open `index.html` over a local server and click through.

```bash
python3 -m http.server 4173 --directory .
```

## Screens

| File | Source |
| --- | --- |
| `index.html` | Figma frame *Trang chủ - Homepage* (3:5, body 107:720) from **NHATO 3.fig** + `template/index.html` and `assets/css/pages/home.css` in the repo |
| `news.html` | Figma frame *Danh mục tin tức - News categories* |
| `article.html` | Figma frame *Chi tiết tin tức - News details* (261:434) |
| `contact.html` | Figma frame *liên hệ - contact* |
| `network.html` | Figma frame *Kết nối - Network* (108:6) |
| `space.html` | Figma frame *Không gian - Home* (132:473) |
| `art.html` | Figma frame *Nghệ thuật - art* (152:513) |
| `taste.html` | Figma frame *Phong vị - taste* (152:911) |
| `original.html` | Figma frame *bản sắc - original* (171:438) |
| `about.html` | Figma frame *về chúng tôi - about us* (223:10) |

All ten frames in the Figma file are built; nothing is held at a placeholder.

Audited against **NHATO 3.fig** (13 Sep 2026): every section in every frame is present.
Two sections were added in that pass — the article outline / related-news block on
`article.html`, and the video strip plus founder carousel on `about.html`.

## How it is wired

- `../../styles.css` carries every token and component class. Nothing in this folder
  redefines a colour, a font or a radius — page files only hold page layout.
- `pages.css` holds the grids that are specific to a screen (news lead, article column,
  contact split, the section-page band rhythm).
- `chrome.js` injects the shared header and footer into `<div data-nh-mount="header">`
  and `…="footer">`. In WordPress these become `header.php` / `footer.php`.
- `../../assets/icons/nhato-icons.js` inlines the icon sprite as the first element in
  `<body>`; every icon is then `<svg class="nh-icon"><use href="#nh-name"></use></svg>`.
- `../../assets/js/nhato.js` handles the mobile menu, the sticky header state, the
  active nav item and newsletter validation.

## Homepage section order (NHATO 3.fig)

Hero → category strip → style picker → booking band → portfolio tabs + mosaic →
"Không gian sống" services band (ink) → art đương đại → art đông dương → phong vị →
contact band → đối tác / sự kiện sắp tới → footer.

The project grid, the "Giá trị NHATO" value band and the article grid are NOT on this
homepage in NHATO 3.fig. They live on `space.html` (grid + value band) and `news.html`
(article grid), which is where the file puts them.

## Known gaps

- The newsletter and contact forms validate on the client only; the sources define no endpoint.
- Social URLs are placeholders — the repo README flags them as pending sign-off.
- The map on `contact.html` uses the Figma map bitmap, not a live map embed.
- The partner logos are one sheet bitmap (`assets/images/partners-sheet.png`) cropped by
  `background-position`, exactly as the Figma frame does it — the file ships no individual
  partner logo assets.
- Homepage bitmaps were downscaled from the Figma exports (some were 4096px / 7 MB) to the
  sizes the layout actually renders. Re-export at full size if you need print resolution.
- Mobile ergonomics live in `components/chrome/mobile.css`, the one place the kit departs
  from the Figma desktop frames: ≤1180px raises every link, icon and pagination cell to a
  44px hit target and lifts meta type to 13px; ≤760px drops the header "Call us" pill (the
  number stays in the footer and on the contact page); ≤620px stacks `.nh-rule-head`.
  Audited at 360 / 390 / 768px — no horizontal overflow, no sub-40px targets, no type
  under 12px.
- Every glyph in the sprite is now built from vectors out of the .fig. Five were complete
  single SVGs and are copied verbatim (`assets/icons/fig/role-designer.svg`,
  `role-brand.svg`, `role-expert-person.svg` among them). Six more — `nh-taste-ritual`,
  `nh-taste-sense`, `nh-role-expert`, `nh-role-client`, `nh-listen`, `nh-spark` — are
  frames the file assembles from ONE sub-vector repeated 4–14 times with per-instance
  transforms the extraction drops. For those the sub-vector is the file's real path at its
  real size, and only the placement is reconstructed to fill the frame's own box:
  `nh-listen` ten 8.641×8.639 rings on a 14.51 orbit, `nh-spark` fourteen 10.003×10 arrows
  radiating from centre, `nh-role-expert` four 18×23.257 person glyphs 2×2 in the 48 box,
  `nh-role-client` seven 8.92×9 rings (six on an orbit, one centred), `nh-taste-ritual`
  four 5.902×20.029 wisps across the 72 box, `nh-taste-sense` six 6.218×17.918 wisps 3×2
  inside the 34.815×59.999 box at 18.601/6.001. Export those six 40–72px icon frames from
  Figma as SVG and drop them in over the symbols to make the placement exact too.
