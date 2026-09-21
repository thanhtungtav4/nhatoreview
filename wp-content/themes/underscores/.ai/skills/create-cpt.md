# Skill: Create Custom Post Type (CPT)

## Mục đích

Tạo và đăng ký một Custom Post Type dưới dạng class PSR-4 trong `app/PostTypes/`, wired qua `includes/bootstrap.php`.

## Tại sao làm như này

| Quyết định | Tại sao |
|---|---|
| CPT là class trong `app/PostTypes/` (namespace `Theme\PostTypes\`) | Theme dùng PSR-4: hook/đăng ký gói trong class có `register()`, autoload qua composer. Không viết hàm rời rạc, không có `loadFile.php`. |
| `register_post_type()` gọi trong `add_action('init', ...)` | WP yêu cầu đăng ký post type ở hook `init`; đăng ký sớm/muộn hơn sẽ không nhận. |
| `supports`, `rewrite slug` truyền tham số | Mặc định an toàn (title/editor/thumbnail/excerpt/revisions), slug URL rõ ràng, dễ chỉnh. |
| ACF field cho CPT = Local JSON (không kèm trong skill này) | Tách trách nhiệm: CPT là cấu trúc dữ liệu, field là nội dung. Field tạo riêng qua Local JSON (location rule `Post Type == {key}`). |

## Khi nào dùng

- Cần loại nội dung riêng (sản phẩm, dự án, sự kiện…) tách khỏi post/page.
- KHÔNG dùng nếu chỉ cần phân loại post hiện có (→ taxonomy).

## Tham số

1. `name_singular` (bắt buộc): vd `Product`, `Dự án`.
2. `name_plural` (bắt buộc): vd `Products`, `Dự án`.
3. `slug` (bắt buộc): slug URL, vd `san-pham`, `du-an`.
4. `supports` (tuỳ chọn): danh sách phân cách phẩy. Mặc định `title,editor,thumbnail,excerpt,revisions`.

## Quy trình

1. **Chuẩn hoá**: từ `name_singular` → post type key (lowercase, `_`, ≤20 ký tự), class `Theme\PostTypes\{ClassName}`, file `app/PostTypes/{ClassName}.php`.

2. **Tạo class**:
   ```php
   <?php

   declare(strict_types=1);

   namespace Theme\PostTypes;

   defined('ABSPATH') || exit;

   final class Product
   {
       public static function register(): void
       {
           add_action('init', [new self(), 'register_post_type']);
       }

       public function register_post_type(): void
       {
           register_post_type('product', [
               'labels'      => [ /* name, singular_name, add_new_item, ... */ ],
               'public'      => true,
               'has_archive' => true,
               'rewrite'     => ['slug' => 'san-pham'],
               'supports'    => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
               'show_in_rest'=> true,
           ]);
       }
   }
   ```

3. **Wire** trong `includes/bootstrap.php`: `\Theme\PostTypes\Product::register();` → chạy `composer dump-autoload`.

4. **Verify**: `php -l app/PostTypes/{ClassName}.php` + autoload (`php -r 'require "vendor/autoload.php"; var_dump(class_exists("Theme\\PostTypes\\Product"));'`).

5. **Báo + gợi ý**: đường dẫn file; hỏi: "Tạo ACF field group (Local JSON) cho '{{name_singular}}' luôn không?" (location `Post Type == {key}`).

## Ví dụ end-to-end

**User**: `> create-cpt --singular="Dự án" --plural="Dự án" --slug="du-an" --supports="title,thumbnail,editor"`

**AI**:
1. Tạo `app/PostTypes/DuAn.php` (class `Theme\PostTypes\DuAn`), `register_post_type('du_an', ...)` nhãn tiếng Việt, supports đã chọn, hook `init` trong `register()`.
2. Thêm `\Theme\PostTypes\DuAn::register();` vào `includes/bootstrap.php`; `composer dump-autoload`.
3. `php -l` pass, class autoload OK.
4. Báo: "Đã tạo CPT 'Dự án' tại `app/PostTypes/DuAn.php`, đăng ký trong `includes/bootstrap.php`. Tạo ACF field group cho 'Dự án' luôn không?"

## Checklist

- [ ] Post type key ≤ 20 ký tự, snake_case
- [ ] `register_post_type()` trong hook `init`
- [ ] Đã `::register()` trong `includes/bootstrap.php` + `composer dump-autoload`
- [ ] `php -l` pass, class autoload OK
- [ ] Nhãn (labels) đầy đủ

## Troubleshooting

- **CPT không hiện trong admin** → quên `::register()` trong `includes/bootstrap.php`, hoặc chưa `composer dump-autoload`.
- **404 khi xem single CPT** → vào Settings → Permalinks bấm Save để flush rewrite rules.
- **Class not found** → file/namespace sai chuẩn PSR-4 (`Theme\PostTypes\X` ↔ `app/PostTypes/X.php`).
