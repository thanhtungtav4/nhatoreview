# NHATO 2 — Figma → WordPress template

Bộ khung tĩnh này ưu tiên làm chuẩn header/footer trước khi triển khai từng page. Homepage NHATO 2 đã được dựng theo frame Home trong Figma; markup dùng HTML5 semantic, class theo BEM và được tách thành partial để map sang WordPress template parts.

## Chạy preview

Từ thư mục gốc của workspace:

```bash
python3 -m http.server 4173 --directory template
```

Mở `http://127.0.0.1:4173/`. Cần chạy qua local server vì `partials.js` tải các partial HTML bằng `fetch`.

## Cấu trúc

- `partials/header.html`, `partials/footer.html`: source dùng chung, có thể chuyển thành `header.php`, `footer.php` hoặc template parts của theme.
- `assets/css/common/`: biến thiết kế, reset, base, header, footer và file `common.css` làm manifest.
- `assets/css/pages/`: CSS riêng theo page; `home.css` chứa toàn bộ các section homepage theo thứ tự Figma: hero, danh mục, dự án, quy trình giá trị và bài viết.
- `assets/js/partials.js`: nạp partial HTML.
- `assets/js/common.js`: menu responsive, active nav, sticky header, Escape close, newsletter validation và năm bản quyền.
- `assets/images/home/`: asset hình ảnh đã export/tối ưu cho homepage; gồm hero, card dự án và card bài viết. Section giá trị dùng lại ảnh hero theo bố cục Figma, với đường cong và sơ đồ quy trình dựng bằng SVG. Khi có asset gốc khác từ Figma, chỉ cần thay `src` mà không phải refactor layout.
- `assets/icons/`: chỗ nhận icon SVG dùng chung.

## Quy ước tích hợp WordPress

1. Giữ các hook nội dung trong `main`; không gắn layout vào nội dung cố định.
2. Khi chuyển sang WP, thay các `href` tương đối bằng `home_url()`, `get_permalink()` hoặc menu location.
3. Newsletter hiện chỉ là UI/validation phía client; endpoint/CRM sẽ được nối riêng sau khi thống nhất phạm vi.
4. URL social và font chính thức cần thay bằng dữ liệu đã chốt với bên duyệt Figma. Các ảnh homepage đang nằm trong `assets/images/home/`; khi tích hợp WordPress có thể thay `src` bằng URL từ Media Library/ACF.

## Breakpoint đã chuẩn bị

CSS có các mốc 375–420px, 560–680px, 768–900px, 1024px, 1180px và desktop 1440px; layout dùng `clamp`, grid co giãn, không dùng chiều rộng cố định gây tràn ngang.
