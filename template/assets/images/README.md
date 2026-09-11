# NHATO image assets

Đặt ảnh xuất từ Figma hoặc ảnh nội dung đã tối ưu vào thư mục này. Asset homepage NHATO 2 được gom trong `home/`.

- `home/home-hero.jpg`: ảnh nền hero.
- `home/space-mansion.jpg`, `home/space-office.jpg`, `home/space-light-home.jpg`, `home/space-villa.jpg`: ảnh các card không gian tiêu biểu.
- `home/editorial-art.jpg`: ảnh nội dung editorial có thể tái sử dụng cho các bài viết.
- Section `Giá trị NHATO` dùng lại `home/home-hero.jpg` theo thiết kế Figma; đường cong và đường nối quy trình là SVG inline trong `index.html`.

- Ưu tiên AVIF/WebP cho ảnh lớn.
- Ảnh nội dung cần có kích thước `width` và `height` khi đưa vào HTML để tránh layout shift.
- Dùng `loading="lazy"` cho ảnh ngoài vùng nhìn đầu tiên; ảnh hero/LCP không lazy-load.
- Tên file dùng chữ thường, dấu gạch ngang và không dùng khoảng trắng.
