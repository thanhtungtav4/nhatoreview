# Hệ thống tải code - Underscores Theme

Tài liệu mô tả cách theme tải code và cách mở rộng khi thêm file mới.
Mô hình hiện tại: **PSR-4 autoload cho class** + **require thủ công cho hàm helper**.
KHÔNG còn `configs/loadFile.php`.

## 1. TỔNG QUAN

```
functions.php
    ├── Định nghĩa constants (UNDERSCORES_*)
    ├── require vendor/autoload.php (Composer PSR-4)
    └── require includes/bootstrap.php
        ├── require helper thủ tục (includes/functions/*, includes/ajax/*)
        └── gọi ::register() cho từng hook class (autoload qua PSR-4)
```

- **Class** (namespace `Theme\`) tự autoload từ `app/` qua composer PSR-4.
- **Hàm helper** là hàm global, require tường minh trong `includes/bootstrap.php`.
- **Hook** đăng ký bằng static `register()` gọi từ `includes/bootstrap.php`.

### 1.1. Constants quan trọng
| Constant | Giá trị | Mục đích |
|---|---|---|
| `UNDERSCORES_THEME_PATH` | `get_template_directory()` | Đường dẫn tuyệt đối theme |
| `UNDERSCORES_THEME_APP_PATH` | `…/app` | Class PSR-4 |
| `UNDERSCORES_THEME_INCLUDES_PATH` | `…/includes` | Helper, AJAX, bootstrap |
| `UNDERSCORES_THEME_CONFIG_PATH` | `…/includes/config` | Map AJAX (`ajax.php`) |
| `UNDERSCORES_SITE_TEMPLATE_URL` | `{siteurl}/template` | Build frontend của dự án |

## 2. CẤU TRÚC HIỆN TẠI

### 2.1. `functions.php`
```php
define( 'UNDERSCORES_THEME_VERSION', '4.3.0' );
define( 'UNDERSCORES_THEME_PATH', get_template_directory() );
define( 'UNDERSCORES_THEME_INCLUDES_PATH', UNDERSCORES_THEME_PATH . '/includes' );
define( 'UNDERSCORES_THEME_CONFIG_PATH', UNDERSCORES_THEME_PATH . '/includes/config' );

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/bootstrap.php';
```

### 2.2. `composer.json` (PSR-4)
```json
{
    "autoload": {
        "psr-4": {
            "Theme\\": "app/",
            "Theme\\Child\\": "../underscores-child/app/"
        }
    }
}
```
Một autoloader của parent phục vụ cả parent (`Theme\`) và child (`Theme\Child\`).

### 2.3. `includes/bootstrap.php`
```php
// Helper thủ tục.
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/image-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/pagination-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/taxonomy-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/ajax/post-ajax.php';

// Hook + setup class (autoload PSR-4).
\Theme\Setup\ThemeSetup::register();
\Theme\Hooks\CommonHook::register();
\Theme\Hooks\ImageHook::register();
\Theme\Hooks\AjaxHook::register();
```

## 3. VỊ TRÍ FILE

| Loại | Vị trí | Định dạng | Tải bằng |
|---|---|---|---|
| Class/Hook | `app/{Sub}/` | `Name.php` (namespace `Theme\{Sub}`) | PSR-4 autoload + `::register()` |
| Functions | `includes/functions/` | `{domain}-functions.php` | `require_once` trong `bootstrap.php` |
| Ajax handler | `includes/ajax/` | `{context}-ajax.php` (hàm global) | `require` + `includes/config/ajax.php` |
| ACF field group | child `acf-json/` | `group_*.json` (Local JSON) | ACF tự load (không qua PHP) |

> Cách thêm module mới chi tiết: xem `modular-development.md`.

## 4. COMPOSER

- Không có dependency runtime bắt buộc; `composer.json` chỉ dùng cho PSR-4 autoload.
- ACF dùng plugin ACF Pro + Local JSON, không qua composer.
- Thêm dependency: `composer require vendor/pkg` rồi `composer dump-autoload`.

## 5. LƯU Ý
- Mọi file PHP nên có `defined('ABSPATH') || exit;`.
- Class mới phải đúng đường dẫn PSR-4 (`app/` + namespace `Theme\…`), nếu không sẽ không autoload → chạy `composer dump-autoload`.
- Sau khi thêm/đổi tên class, chạy `composer dump-autoload`.
