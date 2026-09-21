# Hướng dẫn ACF (Local JSON) - Underscores Theme

Theme dùng **ACF Local JSON** (cơ chế native của ACF Pro). Field group được lưu dưới dạng file `.json`
trong `acf-json/` của **child theme**, sync qua admin UI. KHÔNG dùng `vinkla/extended-acf` (đã gỡ),
KHÔNG định nghĩa field group bằng PHP code.

## 1. CƠ CHẾ

- Field group định nghĩa/sửa trong **wp-admin → Custom Fields**.
- Khi lưu, ACF ghi/cập nhật file `acf-json/group_{key}.json` (nhờ filter `acf/settings/save_json`).
- Khi load, ACF đọc các file trong `acf-json/` (filter `acf/settings/load_json`).
- Cấu hình save/load + đăng ký Options Page nằm ở class `Theme\Child\Acf\LocalJson`
  (`app/Acf/LocalJson.php`), gọi `::register()` trong child `includes/bootstrap.php`.

Yêu cầu: **ACF Pro** cài trên site (đã có).

## 2. THƯ MỤC

```
underscores-child/
├── acf-json/                       # Field group JSON (commit vào git)
│   └── group_theme_settings.json
└── app/Acf/LocalJson.php       # save/load paths + options page
```

## 3. QUY TRÌNH THÊM / SỬA FIELD GROUP

### Cách chuẩn (khuyến nghị) — qua admin UI
1. wp-admin → **Custom Fields → Add New** (hoặc sửa group có sẵn).
2. Thêm field, đặt location rule, lưu.
3. ACF tự ghi `acf-json/group_{key}.json`. **Commit file JSON** vào git.
4. Trên môi trường khác: ACF hiện nút **Sync** để import group từ JSON vào DB.

### Khi cần tạo JSON thủ công (vd qua AI/scaffold)
1. Tạo `acf-json/group_{context}_{name}.json`.
2. Mỗi field cần `key` duy nhất, ổn định (prefix `field_`), `name` snake_case, `type`.
3. `location` là mảng-lồng-mảng (OR ngoài, AND trong).
4. **Validate + review** trước khi commit:
   ```bash
   php underscores-child/acf-json/validate.php --summary
   ```
   - Guard: chặn JSON sai + key trùng + name trùng (3 lỗi làm mất data/hỏng sync).
   - `--summary`: in cây field (name/type/return_format) để review nhanh, không phải đọc raw JSON.
5. Commit JSON. Khi deploy / đổi môi trường: vào admin bấm **Sync** để nạp vào DB.

## 4. QUY TẮC ĐẶT TÊN

- Group key: `group_{context}_{name}` (vd `group_page_about`).
- Field key: `field_{group}_{field}` — duy nhất toàn site, KHÔNG đổi sau khi đã dùng (đổi key = mất data).
- Field name: snake_case (vd `banner_settings`, `is_show`).

## 5. SCHEMA JSON TỐI THIỂU

```json
{
    "key": "group_page_example",
    "title": "Example Page",
    "fields": [
        {
            "key": "field_page_example_heading",
            "label": "Heading",
            "name": "heading",
            "type": "text"
        }
    ],
    "location": [
        [
            { "param": "page_template", "operator": "==", "value": "page-template/template-example.php" }
        ]
    ],
    "style": "seamless",
    "position": "acf_after_title",
    "label_placement": "top",
    "active": true
}
```

Field type thường dùng: `text`, `textarea`, `wysiwyg`, `image` (`"return_format": "id"`),
`link` (`"return_format": "array"`), `true_false`, `group` (`sub_fields`), `repeater` (`sub_fields`),
`tab`, `flexible_content` (`layouts`).

## 6. OPTIONS PAGE (Theme Settings)

- Local JSON KHÔNG lưu options page → đăng ký bằng code trong `Theme\Child\Acf\LocalJson::register_options_page()` (`acf_add_options_page`).
- Options page hiện có: slug `theme-setting` (capability `manage_options`, `autoload` true), field group `group_theme_settings.json`:
  | Tab | Field (name) | Render ở |
  |---|---|---|
  | Liên hệ | `footer_contact_section` (title, items[text, link]) | `footer.php` |
  | Mạng xã hội | `social_links` [platform, icon, url] | `partials/components/social-list.php` (footer + menu mobile) |
  | Mã tracking | `scripts_section` (header_scripts, footer_scripts) | `ThemeHook` → `wp_head` / `wp_footer` (lọc `unfiltered_html` khi lưu) |
- Đây là bộ **tối thiểu** của starter. Dự án cần thêm (chi nhánh, nút CTA nổi, form...) → skill `add-theme-option`, KHÔNG thêm field trùng core.
- Mỗi dữ liệu CHỈ 1 nguồn: không tạo lại hotline/email/copyright/social ở tab khác; khối dùng ≥2 nơi → component đọc chung.
- **KHÔNG đưa vào Theme Settings những gì WP core đã có** (xem `wp-core-first.md`):
  - Logo → `custom-logo` / `the_custom_logo()`
  - Favicon → Site Icon (core in qua `wp_head()`)
  - Tên site / slogan → Settings → General (`bloginfo('name')` / `bloginfo('description')`)
  - List link tới page/post (footer menu) → `wp_nav_menu`
  - Tiêu đề / nội dung / ảnh banner / mô tả của page → `the_title()` / `the_content()` / featured image / excerpt (KHÔNG field `heading`, `intro`, `banner` trùng)
  - Mã GA/Pixel riêng lẻ → dán snippet vào `header_scripts` (không tạo field ID riêng)
- Field group dùng `tab` để nhóm, và mỗi nhóm thường là 1 `group` con (vd `footer_contact_section`, `scripts_section`).

### Truy xuất giá trị
Dùng helper `underscores_get_option()` (không gọi `get_field(..., 'option')` trực tiếp trong template):
```php
// File: includes/functions/common-functions.php (child)
if (! function_exists('underscores_get_option')) {
    function underscores_get_option($field_name, $default = null) {
        if (! function_exists('get_field')) {
            return $default;
        }
        $value = get_field($field_name, 'option');
        return ($value === null || $value === false || $value === '' || $value === []) ? $default : $value;
    }
}
```
Field nằm trong `group` con → đọc group rồi lấy key (đúng cấu trúc data thật, không bịa default):
```php
$contact = underscores_get_option('footer_contact_section', []);   // mảng
$title   = $contact['title'] ?? '';
$items   = $contact['items'] ?? [];
the_custom_logo(); // logo KHÔNG nằm trong options — dùng core
```
> Theo `data-rendering.md`: chỉ `?? ''`/`?: 0` chống lỗi rỗng — KHÔNG `?? '© 2024'` hay text/ảnh mặc định.

### Thêm option mới
Xem skill `add-theme-option`. Cần options page mới → thêm `acf_add_options_page(...)` trong `LocalJson::register_options_page()`.

## 7. THÊM FIELD GROUP — tóm tắt cho agent

- KHÔNG sinh `register_extended_field_group()` / `use Extended\ACF\...`.
- Tạo/sửa = ghi `acf-json/group_*.json` đúng schema mục 5, key ổn định (không đổi sau khi dùng).
- Validate: `php underscores-child/acf-json/validate.php --summary`. Sau đó **Sync** trong wp-admin.

## 8. THAM KHẢO

1. [ACF Local JSON](https://www.advancedcustomfields.com/resources/local-json/)
2. [ACF Field Types](https://www.advancedcustomfields.com/resources/)
3. [Auto-load system](auto-load-system.md)
