# Quy tắc Render Data - Underscores Theme

Quy tắc bắt buộc khi viết code hiển thị dữ liệu (ACF, post meta, options). Áp dụng cả parent và child.

## Nguyên tắc cốt lõi

**Data do người dùng nhập ở admin. Code KHÔNG bịa nội dung.**
Template chỉ làm 2 việc: (1) đọc giá trị an toàn, (2) ẩn khối nếu rỗng. Không placeholder, không fallback giả, không helper thừa.

## Tại sao

| Quyết định | Tại sao |
|---|---|
| KHÔNG tạo dummy/placeholder data | User nhập data thật ở admin. Dummy ("Lorem ipsum", "Tên công ty", ảnh demo) làm bẩn UI khi quên xoá, gây hiểu nhầm "đã có nội dung", và lẫn vào output thật. |
| Chỉ `?? ''` / `?: 0` để chống lỗi rỗng | Mục tiêu duy nhất của guard là **không vỡ PHP** khi field chưa nhập. Không phải để "điền hộ" nội dung. |
| Rỗng thì **ẩn khối**, không in chuỗi mặc định | Khối trống đẹp hơn khối chứa text giả. Để chỗ trống cho admin biết cần nhập. |
| KHÔNG bọc helper cho mỗi field | Helper chỉ tồn tại khi có **logic tái dùng thật** (normalize link, build srcset). Bọc `get_x()` quanh 1 `get_field()` là spam — thêm file, thêm tên, không thêm giá trị. |

## Quy tắc

### 1. Guard rỗng, không bịa giá trị
```php
// ĐÚNG — guard chống lỗi, không bịa
$heading = $args['heading'] ?? '';
$image   = $args['image'] ?? 0;

// SAI — bịa nội dung mặc định
$heading = $args['heading'] ?? 'Tiêu đề mặc định';
$image   = $args['image'] ?? get_template_directory_uri() . '/assets/images/demo.jpg';
```

### 2. Rỗng thì ẩn, không in placeholder
```php
// ĐÚNG
<?php if ($heading): ?>
    <h2><?php echo esc_html($heading); ?></h2>
<?php endif; ?>

<?php if ($image) echo wp_get_attachment_image($image, 'large'); // size gần kích thước hiển thị — performance.md ?>

// SAI
<h2><?php echo esc_html($heading ?: 'Chưa có tiêu đề'); ?></h2>
<img src="<?php echo $image ?: '/demo.jpg'; ?>">
```

### 3. Không spam helper
```php
// SAI — helper bọc 1 dòng, không logic
function get_hero_heading() { return get_field('heading') ?? ''; }

// ĐÚNG — đọc thẳng (đã có underscores_get_option cho options)
$heading = $args['heading'] ?? '';
$contact_title = underscores_get_option('footer_contact_section')['title'] ?? '';
```
Chỉ tạo helper khi có **logic thật, tái dùng ≥2 nơi** (vd chuẩn hoá link ACF, fallback ảnh theo size).

### 4. Không tạo file data demo / seed
- KHÔNG viết script/SQL chèn data mẫu.
- KHÔNG hardcode mảng nội dung mẫu trong template.
- Trang trống khi chưa nhập là **đúng** — không "đổ đầy cho đẹp demo".

## Ngoại lệ (vẫn cần guard, không phải bịa)

- **Default cấu hình của field** (bật/tắt `is_show`, select kiểu hiển thị, số cột): đặt `default_value` trong ACF JSON, không hardcode trong PHP. Field **nội dung** (text, ảnh, link) KHÔNG `default_value` — dùng `placeholder`/`instructions` (xem skill `convert-html-to-wp`).
- **Ảnh fallback hệ thống** (vd thumbnail mặc định khi post không có ảnh): đã có cơ chế ở theme — dùng lại, không tạo mới per-template.
- **Chuỗi i18n cố định của giao diện** (label nút, tiêu đề section tĩnh): là text giao diện, không phải data — viết thẳng + `__()` được.

## Checklist khi review render code

- [ ] Mọi field đọc qua `?? ''` / `?: 0`, KHÔNG có giá trị bịa
- [ ] Field rỗng → khối bị ẩn (`if`), không in text/ảnh giả
- [ ] Không có helper bọc 1 dòng quanh `get_field()`/`get_sub_field()`
- [ ] Không có dummy data, không file seed
- [ ] Output escape đúng (`esc_html`/`esc_url`/`esc_attr`)
