# Quy tắc đặt tên - Underscores Theme

Dựa trên cấu trúc thực tế của Underscores Theme, các quy tắc đặt tên sau phải được tuân thủ để đảm bảo nhất quán và tương thích với PSR-4 autoload.

## Mô hình tải code

- **Class** nằm trong namespace `Theme\` (parent) / `Theme\Child\` (child), thư mục `app/`, autoload PSR-4 qua `composer.json`.
- **Hàm thủ tục (helper)** vẫn là hàm global, đặt trong `includes/functions/`, require trong `includes/bootstrap.php`.
- **Hook** là class trong `app/Hooks/`, đăng ký bằng static `register()` gọi từ `includes/bootstrap.php`. KHÔNG còn `configs/loadFile.php`.

## 1. HÀM (Function) thủ tục

### 1.1. Prefix
- Bắt buộc prefix `underscores_` (parent) / `underscores_child_` (child).
- Ví dụ: `underscores_get_primary_term`, `underscores_child_asset_uri`.

### 1.2. Cấu trúc
```php
if ( ! function_exists( 'underscores_get_primary_term' ) ) {
    function underscores_get_primary_term( int $post_id, string $taxonomy = 'category' ) {
        // code
    }
}
```

### 1.3. File lưu trữ
- Vị trí: `includes/functions/`
- Đặt tên kebab-case theo domain: `image-functions.php`, `pagination-functions.php`, `taxonomy-functions.php`.

## 2. CLASS (PSR-4)

### 2.1. Namespace + tên
- Namespace `Theme\` (parent), `Theme\Child\` (child).
- Tên class PascalCase, KHÔNG prefix `Underscores_`.
- Ví dụ: `Theme\Setup\ThemeSetup`, `Theme\Hooks\CommonHook`, `Theme\Child\Hooks\PerformanceHook`.

### 2.2. Cấu trúc hook class
```php
namespace Theme\Hooks;

defined('ABSPATH') || exit;

final class ExampleHook
{
    public static function register(): void
    {
        $self = new self();
        add_action('wp_enqueue_scripts', [$self, 'do_something']);
    }

    public function do_something(): void
    {
        // code
    }
}
```

### 2.3. File lưu trữ
- Vị trí: `app/` ánh xạ namespace PSR-4 (vd `app/Hooks/CommonHook.php` = `Theme\Hooks\CommonHook`).
- Tên file = tên class + `.php` (PSR-4), KHÔNG dùng `class.` prefix.

### 2.4. Đăng ký
Gọi `::register()` trong `includes/bootstrap.php`:
```php
\Theme\Hooks\CommonHook::register();
```

## 3. TEMPLATE FILES & THƯ MỤC

### 3.1. Partials
- Vị trí: `partials/templates/` (phẳng, 1 cấp).
- Ví dụ: `partials/templates/category.php`, `partials/templates/single-post.php`.
- Load bằng `get_template_part('partials/templates/category')`.

### 3.2. Page templates
- Vị trí: `page-template/`, đặt tên `template-{name}.php`.

### 3.3. WordPress template files
- Root directory, theo chuẩn WP: `single.php`, `archive.php`, `page.php`.

## 4. CSS CLASS

- Class do theme tự sinh (partial, component, section): BEM với prefix `underscores-`: `.underscores-block__element--modifier`.
- Markup lấy từ build `/template` của dự án: GIỮ nguyên class của build (vd `hd`, `ft`, `menu-list`) để CSS/JS build chạy đúng — không đổi sang BEM.

## 5. HOOKS

### 5.1. Action/Filter custom
- Prefix `underscores_`.
- Extension points hiện có: `underscores_before_common_css`, `underscores_after_common_css`, `underscores_before_common_js`, `underscores_after_common_js`.
- Filter hiệu suất: `underscores_child_common_libraries`, `underscores_preconnect_google_fonts`, `underscores_custom_logo_fetchpriority`, `underscores_child_style_loading_strategies`, `underscores_child_critical_css_path`, `underscores_child_jquery_in_footer`, `underscores_child_jquery_migrate`, `underscores_script_to_module`.

### 5.2. AJAX
- Đăng ký qua `includes/config/ajax.php` (map `handle => callable`) + `Theme\Hooks\AjaxHook::register()`.
- Handler là hàm global trong `includes/ajax/`, vd `underscores_ajax_get_posts`.

## 6. DATABASE

- Post meta: prefix `_underscores_`.
- Option: prefix `underscores_`.

## 7. CONSTANTS

- UPPERCASE, prefix `UNDERSCORES_` / `UNDERSCORES_CHILD_`.
- Ví dụ: `UNDERSCORES_THEME_VERSION`, `UNDERSCORES_THEME_PATH`.

## 8. HƯỚNG DẪN CHO AI

1. **Hàm helper mới** → `includes/functions/{domain}-functions.php`, prefix `underscores_`, wrap `function_exists`, require trong `includes/bootstrap.php`.
2. **Hook/class mới** → `app/{Sub}/Name.php`, namespace `Theme\...`, `final class` + static `register()`, gọi `::register()` trong `includes/bootstrap.php`.
3. **Template part** → `partials/templates/`, kebab-case, dùng `get_template_part()`.
4. **AJAX** → handler global trong `includes/ajax/`, đăng ký qua `includes/config/ajax.php`.

## 9. TÓM TẮT

| Thành phần | Quy ước | Ví dụ | Vị trí |
|---|---|---|---|
| Functions | `underscores_` | `underscores_get_primary_term` | `includes/functions/taxonomy-functions.php` |
| Classes | namespace `Theme\` | `Theme\Hooks\CommonHook` | `app/Hooks/CommonHook.php` |
| CSS Classes | `underscores-` | `.underscores-header__logo` | `assets/css/` |
| Hooks | `underscores_` | `underscores_after_common_css` | `app/Hooks/` |
| Post Meta | `_underscores_` | `_underscores_post_view` | Database |
| Constants | `UNDERSCORES_` | `UNDERSCORES_THEME_VERSION` | `functions.php` |

**Lưu ý**: File PHP nên có `declare(strict_types=1);` ở đầu và kiểm tra `ABSPATH`.
