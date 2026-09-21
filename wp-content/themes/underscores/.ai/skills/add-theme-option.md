# Skill: Add Theme Option

## Mục đích

Thêm một field mới vào trang **Theme Settings** (ACF Local JSON), để admin nhập một giá trị
toàn cục (hotline, email, link mạng xã hội…) và template đọc lại qua `underscores_get_option()`.

## Tại sao làm như này

| Quyết định | Tại sao |
|---|---|
| Sửa file `acf-json/group_theme_settings.json` | Theme dùng ACF **Local JSON** — field group lưu trong git, deploy nhất quán giữa các môi trường. Không định nghĩa field bằng PHP/Extended ACF (đã gỡ). |
| `key` cố định, đặt theo quy tắc, KHÔNG đổi sau khi dùng | ACF map data theo `key`. Đổi/trùng `key` = **mất data ngầm**, không báo lỗi. Đây là lỗi nguy hiểm nhất của ACF. |
| Chạy `validate.php` trước khi báo xong | Sửa JSON tay dễ hỏng cú pháp / trùng key. Validate là rào chắn rẻ hơn debug mất data về sau. |
| Đọc value qua helper `underscores_get_option()` | Trừu tượng hoá `get_field(..., 'option')` — đổi logic 1 chỗ, template không phải sửa. |

## Khi nào dùng

- Cần 1 giá trị **toàn site** mà admin chỉnh được (không phải theo từng page).
- KHÔNG dùng cho field theo page cụ thể → xem `create-flexible-section` hoặc field group theo page template.
- KHÔNG dùng khi WP core đã có: logo (`the_custom_logo`), favicon (Site Icon), tên site/slogan (`bloginfo`), list link tới page (`wp_nav_menu`). Xem `wp-core-first.md`.

## Tham số

1. `field_label` (bắt buộc): nhãn hiển thị, vd `Facebook URL`.
2. `field_name` (bắt buộc): snake_case, vd `facebook_url`. Là phần `underscores_get_option('facebook_url')`.
3. `field_type` (bắt buộc): `text`, `textarea`, `wysiwyg`, `image`, `url`, `email`, `number`, `true_false`, `link`.
4. `target_group` (tuỳ chọn): `name` của `group` con để chèn vào (vd `footer_contact_section`). Bỏ trống = chèn ở cấp gốc.

## Quy trình

1. **Mở** `underscores-child/acf-json/group_theme_settings.json`.

2. **Sinh field object** (AI tự tạo, key an toàn):
   ```json
   {
       "key": "field_ts_{{field_name}}",
       "label": "{{field_label}}",
       "name": "{{field_name}}",
       "type": "{{field_type}}"
   }
   ```
   - Quy tắc `key`: `field_ts_{{field_name}}`. Nếu đã tồn tại → thêm hậu tố số (`_2`) để tránh trùng.
   - `image` → thêm `"return_format": "id"`. `link` → `"return_format": "array"`.

3. **Chèn** vào `fields[]` (hoặc `sub_fields[]` của `target_group`). Giữ JSON hợp lệ (dấu phẩy, ngoặc).

4. **Validate + review** (bắt buộc):
   ```bash
   php underscores-child/acf-json/validate.php --summary
   ```
   - Phải in `OK` (JSON hợp lệ, không trùng key/name).
   - `--summary` in cây field để bạn **review nhanh** field vừa thêm (name, type, return_format) mà không phải đọc raw JSON.

5. **Báo + bàn giao để review**:
   - "Đã thêm `{{field_label}}` vào Theme Settings — validate OK. Xem cây field ở output `--summary` để review."
   - Lấy giá trị trong template: `underscores_get_option('{{field_name}}')`.
   - _Ghi chú deploy_: commit JSON; trên môi trường khác vào wp-admin → Custom Fields bấm **Sync** để nạp vào DB.

## Ví dụ end-to-end

**User**: `> add-theme-option --label="Instagram URL" --name="instagram_url" --type=url`

**AI**:
1. Mở `acf-json/group_theme_settings.json`.
2. Chèn `{ "key": "field_ts_instagram_url", "label": "Instagram URL", "name": "instagram_url", "type": "url" }` vào `fields[]`.
3. Chạy `php underscores-child/acf-json/validate.php --summary` → `OK` + cây field để review.
4. Báo: "Đã thêm 'Instagram URL', validate OK — review cây field rồi commit." (deploy: nhớ Sync.)
5. Dùng trong footer:
   ```php
   <?php if ($ig = underscores_get_option('instagram_url')): ?>
       <a href="<?php echo esc_url($ig); ?>">Instagram</a>
   <?php endif; ?>
   ```

## Checklist

- [ ] `validate.php --summary` = OK
- [ ] Review cây field: name/type/return_format đúng ý
- [ ] `field_name` snake_case, không trùng field cùng cấp
- [ ] `image` có `return_format: id` / `link` có `return_format: array`
- [ ] Commit JSON (deploy: nhớ Sync)

## Troubleshooting

- **Sync không hiện trong admin** → ACF chưa thấy thay đổi: kiểm tra JSON có hợp lệ không (`validate.php`), file đúng `acf-json/`.
- **Field hiện nhưng `underscores_get_option()` trả null** → sai `field_name`, hoặc chưa Sync nên DB chưa có group.
- **Mất giá trị cũ sau khi sửa** → đã đổi `key` của field đang có data. Khôi phục lại `key` cũ.
