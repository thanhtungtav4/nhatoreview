repo: thanhtungtav4/nhatoreview
branch: main
path: template

## Last sync

date: 2026-09-11T14:56:19Z

### Updated in this project

- Imported the token set from `template/assets/css/common/variables.css` into `tokens/`.
- Rebuilt header, footer, hero, cards and forms as `nh-*` classes under `components/`.
- Copied the homepage photography, wordmark and favicon into `assets/`.
- Recreated the homepage and four more screens in `ui_kits/nhato-web/`.

## Screen map

| Screen | Built from |
| --- | --- |
| `ui_kits/nhato-web/index.html` | `template/index.html`, `template/assets/css/pages/home.css`, `template/partials/header.html`, `template/partials/footer.html` |
| `ui_kits/nhato-web/news.html` | Figma frame *Danh mục tin tức - News categories* |
| `ui_kits/nhato-web/article.html` | Figma frame *Chi tiết tin tức - News details* |
| `ui_kits/nhato-web/contact.html` | Figma frame *liên hệ - contact* |
| `ui_kits/nhato-web/{art,taste,original,space,about}.html` | `template/pages/*.html`, `template/assets/css/pages/placeholder.css` |
| `components/chrome/chrome.css` | `template/assets/css/common/{header,footer,base,reset}.css` |
| `components/{actions,content,media,forms}/*.css` | `template/assets/css/pages/home.css` + Figma frame literals |
| `tokens/*.css` | `template/assets/css/common/variables.css` + Figma colour/type usage |
| `assets/icons/nhato-icons.svg` | inline SVG in `template/partials/*.html` and `template/index.html` |
