# nhatoreview

Template tĩnh HTML, CSS và vanilla JS cho website NHATO Collection. Không cần npm, không có bước build.

## Cách chạy

Từ thư mục gốc repo:

```bash
python3 -m http.server 4173 --directory .
```

Mở trình duyệt:

- Website: http://localhost:4173/template/ui_kits/nhato-web/
- Trang chủ: http://localhost:4173/template/ui_kits/nhato-web/index.html

Hoặc chạy trực tiếp trong `template/`:

```bash
cd template
python3 -m http.server 4173 --directory .
```

Khi đó URL là http://localhost:4173/ui_kits/nhato-web/

Cần HTTP server vì CSS/JS dùng đường dẫn tương đối. Các trang: `index`, `news`, `article`, `contact`, `network`, `space`, `art`, `taste`, `original`, `about`.
