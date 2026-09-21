# Quy tắc Sections Nội dung Linh hoạt (Flexible Content) - Underscores Theme

Tài liệu này quy định quy trình làm việc với ACF Flexible Content để xây dựng các trang có cấu trúc động, dựa trên việc mapping giữa Layouts và các file template partials.

## 1. NGUYÊN TẮC CỐT LÕI: LAYOUT-TO-FILE MAPPING

Mỗi "Layout" được định nghĩa trong một field Flexible Content phải tương ứng với một file template partial trong thư mục `partials/sections/`. Hệ thống sẽ tự động tìm và load file partial này dựa trên tên của Layout.

- **Field Type**: `Flexible Content`
- **Thư mục Partials**: `partials/sections/`

## 2. QUY TRÌNH LÀM VIỆC

### Bước 1: Định nghĩa Field Group với Flexible Content (ACF Local JSON)

- Tạo/sửa field group trong wp-admin → **Custom Fields**, thêm field type **Flexible Content** tên `sections`.
- Thêm các `Layout` cần thiết. **Tên (name) của Layout là yếu tố quyết định** (map sang file partial).
- Lưu → ACF ghi `underscores-child/acf-json/group_*.json`. Commit JSON, **Sync** trên môi trường khác.

**Ví dụ JSON** (field `sections` trong một group):
```json
{
    "key": "field_page_sections",
    "label": "Sections",
    "name": "sections",
    "type": "flexible_content",
    "button_label": "Thêm Section",
    "layouts": [
        {
            "key": "layout_hero_banner",
            "name": "hero_banner",
            "label": "Hero Banner",
            "display": "block",
            "sub_fields": [ /* sub-fields cho Hero Banner */ ]
        },
        {
            "key": "layout_image_gallery",
            "name": "image_gallery",
            "label": "Image Gallery",
            "display": "block",
            "sub_fields": [ /* ... */ ]
        }
    ]
}
```

### Bước 2: Tạo file Template Partials

- Với mỗi `Layout` đã tạo ở trên, hãy tạo một file PHP tương ứng trong thư mục `partials/sections/`.
- **Quy tắc đặt tên file (quan trọng)**: Tên file phải là phiên bản `kebab-case` của tên Layout. `get_row_layout()` sẽ trả về tên layout (ví dụ: `hero_banner`), và theme sẽ tìm file `hero-banner.php`.

- `Layout::make('Hero Banner', 'hero_banner')` -> `partials/sections/hero-banner.php`
- `Layout::make('Image Gallery', 'image_gallery')` -> `partials/sections/image-gallery.php`
- `Layout::make('Call to Action', 'cta')` -> `partials/sections/cta.php`

### Bước 3: Hiển thị các Sections trong template

Dùng helper có sẵn của child — KHÔNG tự viết vòng `have_rows()`:

```php
// partials/templates/{slug}-page.php (scaffolder đã sinh sẵn)
if (underscores_child_render_flexible_sections('sections', get_the_ID())) {
    return;
}
// Fallback khi page chưa có section: dữ liệu core.
the_title('<h1>', '</h1>');
the_content();
```

Helper đọc field **1 lần** bằng `get_field()`, bỏ qua layout chưa có file partial, và gọi
`get_template_part('partials/sections/{layout-kebab}', null, $args)` cho từng row.

### Bước 4: Lấy dữ liệu trong file Section Partial

Partial đọc **`$args`** (sub fields của layout + meta), KHÔNG `get_sub_field()` (helper không chạy `the_row()`):

| Key trong `$args` | Ý nghĩa |
|---|---|
| `{sub_field_name}` | Giá trị đã format của sub field (image = ID, link = array...) |
| `layout` | Tên layout, vd `hero_banner` |
| `post_id` | ID page chứa section |
| `index` | Thứ tự row (0-based) |
| `is_first` | `true` cho section render đầu tiên → dùng cho ảnh LCP |

**Ví dụ**: `partials/sections/hero-banner.php`
```php
<?php
defined('ABSPATH') || exit;

$title    = $args['title'] ?? '';
$subtitle = $args['subtitle'] ?? '';
$image_id = (int) ($args['background_image'] ?? 0);
// Section đầu trang = ứng viên LCP → không lazy, ưu tiên tải (xem performance.md).
$img_attrs = ! empty($args['is_first']) ? ['loading' => 'eager', 'fetchpriority' => 'high'] : [];

if (! $title && ! $image_id) {
    return;
}
?>
<section class="underscores-hero-banner">
    <?php if ($image_id) echo wp_get_attachment_image($image_id, 'full', false, $img_attrs + ['class' => 'underscores-hero-banner__bg']); ?>
    <div class="container">
        <?php if ($title) : ?><h1><?php echo esc_html($title); ?></h1><?php endif; ?>
        <?php if ($subtitle) : ?><p><?php echo esc_html($subtitle); ?></p><?php endif; ?>
    </div>
</section>
```
> Ảnh nền dùng `<img>` + CSS `object-fit`, không `style="background-image"` (không lazy/srcset được, không ưu tiên LCP).

## 3. Ví dụ yêu cầu

> "Thêm một section 'Video Player' vào field group trang.
> 1. Trong file `acf-json/group_*.json` chứa field `sections`, thêm một layout mới `video_player` với sub-fields `video_url` (oembed) và `caption` (text). Bấm **Sync** trong wp-admin.
> 2. Tạo file template partial mới tại `partials/sections/video-player.php`.
> 3. Trong file partial, đọc `$args['video_url']` và `$args['caption']` để hiển thị trình phát video và chú thích."
