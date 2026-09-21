# Quy tắc Phát triển Module - Underscores Theme

Tài liệu định nghĩa quy trình chuẩn để tạo, tích hợp và quản lý module/tính năng mới, đảm bảo nhất quán và dễ bảo trì.

## 1. NGUYÊN TẮC CỐT LÕI

Mọi tính năng mới phát triển dưới dạng file độc lập (module). **Không viết code logic trực tiếp vào `functions.php`**.
Mô hình: **class qua PSR-4 autoload**, **hàm helper require thủ công**. KHÔNG còn `configs/loadFile.php`.

## 2. CẤU TRÚC THƯ MỤC

- **`app/`**: Class PSR-4, namespace `Theme\` (vd `app/Hooks/CommonHook.php` = `Theme\Hooks\CommonHook`). Đây là nơi đặt hook + setup + service.
- **`includes/functions/`**: Hàm helper global, prefix `underscores_` (vd `image-functions.php`).
- **`includes/ajax/`**: Handler AJAX (hàm global), đăng ký qua `includes/config/ajax.php`.
- **`app/PostTypes/`** / **`app/Taxonomies/`**: CPT/taxonomy là class có `register()` (xem skill `create-cpt`).

## 3. QUY TRÌNH THÊM MODULE (BẮT BUỘC)

### Cách A — Hook/Class (khuyến nghị cho logic)
1. Tạo `app/{Sub}/Name.php`:
   ```php
   namespace Theme\{Sub};

   defined('ABSPATH') || exit;

   final class Name
   {
       public static function register(): void
       {
           $self = new self();
           add_action('hook_name', [$self, 'callback']);
       }

       public function callback(): void { /* ... */ }
   }
   ```
2. Gọi `\Theme\{Sub}\Name::register();` trong `includes/bootstrap.php`.
3. Chạy `composer dump-autoload` nếu class mới chưa autoload.

### Cách B — Hàm helper
1. Tạo/mở `includes/functions/{domain}-functions.php`, hàm prefix `underscores_`, wrap `function_exists`.
2. Thêm `require_once` vào `includes/bootstrap.php`.

**Bắt buộc** mọi file: kiểm tra `defined('ABSPATH') || exit;`.

## 4. VAI TRÒ FILE CHÍNH

- **`functions.php`**: entry point — define constants, require `vendor/autoload.php` + `includes/bootstrap.php`.
- **`includes/bootstrap.php`**: require helper thủ tục + gọi `::register()` cho từng hook class.
- **`composer.json`**: map PSR-4 `Theme\` → `app/`, `Theme\Child\` → child `app/`.

## 5. Ví dụ yêu cầu

> "Tạo module xử lý hook sản phẩm.
> 1. Tạo `app/Hooks/ProductHook.php`, namespace `Theme\Hooks`, `final class ProductHook` + static `register()`.
> 2. Trong `register()`, hook `woocommerce_after_single_product` vào một method.
> 3. Gọi `\Theme\Hooks\ProductHook::register();` trong `includes/bootstrap.php`."

Hook class chỉ đăng ký asset / query khi đúng ngữ cảnh (`is_page_template()`, `is_singular()`...) — xem `performance.md`.
