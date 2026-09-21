# Skill: Create Flexible Content Section

## Mục đích

Thêm một **layout** mới vào field `flexible_content` (ACF Local JSON) và tạo file partial render
tương ứng, để admin xếp khối nội dung động và theme render đúng layout.

## Tại sao làm như này

| Quyết định | Tại sao |
|---|---|
| Layout `name` ↔ file `partials/sections/{name}.php` (kebab) | Theme render bằng `underscores_child_render_flexible_sections()` loop layout → `get_template_part('partials/sections/{layout}')`. Tên lệch = section không hiện. |
| Sửa JSON Local thay vì PHP | Field group sống trong git (`acf-json/`), sync nhất quán; không dùng Extended ACF (đã gỡ). |
| `key` layout + sub_field cố định, không đổi | ACF map data theo `key`; đổi/trùng = mất data ngầm. |
| Partial đọc `$args` (mảng section truyền vào), không `get_sub_field()` ngoài loop | Render qua `get_template_part(..., $section)` → dữ liệu đã có sẵn trong `$args`, gọi `get_sub_field()` ngoài The Loop sẽ trả rỗng. |
| Chạy `validate.php` trước khi xong | JSON lồng nhiều tầng dễ sai cú pháp / trùng key. |

## Khi nào dùng

- Trang cần các khối nội dung **xếp tự do, lặp lại, đổi thứ tự** (landing page, page giới thiệu…).
- KHÔNG dùng cho 1 field đơn lẻ (→ thêm field thường) hay option toàn site (→ `add-theme-option`).

## Tham số

1. `section_name` (bắt buộc): vd `Hero Banner`, `Testimonials`.
2. `group_json` (bắt buộc): file group chứa field flexible, vd `underscores-child/acf-json/group_page_about.json` (tạo bằng `make:page about --acf` nếu chưa có).
3. `flexible_field_name` (bắt buộc): `name` của field `flexible_content` (vd `sections`).
4. `sub_fields` (bắt buộc): `tên (type), …` — vd `"heading (text), photo (image)"`.

## Quy trình

1. **Chuẩn hoá**: `section_name` → `layout_name` (snake_case), `slug` (kebab-case).

2. **Sửa file `group_json`**:
   - Tìm field `"type": "flexible_content"` đúng `name`. Nếu chưa có field flexible → tạo field đó trước.
   - Thêm vào `layouts[]`:
     ```json
     {
         "key": "layout_{{layout_name}}",
         "name": "{{layout_name}}",
         "label": "{{section_name}}",
         "display": "block",
         "sub_fields": [ /* mỗi sub_field 1 object */ ]
     }
     ```
   - Mỗi sub_field: `{ "key": "field_{{layout_name}}_{name}", "label": "...", "name": "...", "type": "..." }`
     (`image` → `return_format: id`; `link` → `return_format: array`). Key cố định, không trùng.

3. **Tạo partial** `underscores-child/partials/sections/{{slug}}.php`:
   ```php
   <?php
   defined('ABSPATH') || exit;

   /** @var array $args Sub fields + layout, post_id, index, is_first (underscores_child_render_flexible_sections()). */
   $heading = $args['heading'] ?? '';
   $photo   = (int) ($args['photo'] ?? 0);
   $img_attrs = ! empty($args['is_first']) ? ['loading' => 'eager', 'fetchpriority' => 'high'] : [];
   ?>
   <section class="section-{{slug}}">
       <?php if ($heading): ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
       <?php if ($photo) echo wp_get_attachment_image($photo, 'large', false, $img_attrs); ?>
   </section>
   ```

4. **Validate + review** (bắt buộc):
   ```bash
   php underscores-child/acf-json/validate.php --summary
   ```
   - Phải in `OK`. `--summary` in cây layout/sub_field để bạn review nhanh.

5. **Báo + bàn giao để review**: đường dẫn JSON + partial; review cây field. _Ghi chú deploy_: commit; **Sync** trong wp-admin.

## Ví dụ end-to-end

**User**: `> create-flexible-section --name="Team Members" --group="underscores-child/acf-json/group_page_about.json" --flexible-field=sections --sub-fields="heading (text), photo (image)"`

**AI**:
1. Thêm layout `layout_team_members` (sub-fields `heading`, `photo`) vào field `sections`.
2. Tạo `underscores-child/partials/sections/team-members.php` đọc `$args`.
3. `php underscores-child/acf-json/validate.php --summary` → `OK` + cây layout để review.
4. Báo: "Đã thêm layout 'Team Members' + partial, validate OK — review rồi commit." Section render khi page gọi `underscores_child_render_flexible_sections('sections')`. (deploy: nhớ Sync.)

## Checklist

- [ ] `validate.php --summary` = OK
- [ ] Review cây layout/sub_field đúng ý
- [ ] Layout `name` == tên file partial (kebab)
- [ ] `image`/`link` có `return_format` đúng
- [ ] Partial đọc `$args`, escape output
- [ ] Ảnh dùng size phù hợp; section đầu (`is_first`) eager + `fetchpriority=high` (xem `performance.md`)
- [ ] Commit (deploy: nhớ Sync)

## Troubleshooting

- **Section không hiện** → layout `name` lệch tên file partial, hoặc page chưa gọi `underscores_child_render_flexible_sections()`.
- **Field rỗng trong partial** → gọi `get_sub_field()` thay vì đọc `$args`; phải đọc từ `$args`.
- **Mất data sau khi sửa** → đã đổi `key` layout/sub_field đang có data.
