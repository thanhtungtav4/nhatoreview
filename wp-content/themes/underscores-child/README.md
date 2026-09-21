# Underscores Theme Child

Child theme duoc to chuc theo PSR-4 (hook = class) + ACF Local JSON, theo rule trong `.ai` va `wp-content/themes/underscores/.ai`.

## Cau truc

```text
underscores-child/
|-- acf-json/                       # ACF Local JSON (commit vao git, Sync trong wp-admin)
|   |-- group_theme_settings.json   # Theme Settings: Lien he, Mang xa hoi, Ma tracking
|   `-- validate.php                # guard JSON/key/name trung
|-- app/                            # Class PSR-4, namespace Theme\Child\
|   |-- Acf/LocalJson.php           # save/load JSON + options page + loc script
|   `-- Hooks/{Performance,Theme}Hook.php
|-- assets/
|   |-- css/child-theme.css         # (+ pages/{slug}.css do scaffolder tao)
|   `-- scripts/child-theme.js      # (+ pages/{slug}.js do scaffolder tao)
|-- bin/underscores-child           # CLI scaffold (standalone)
|-- includes/
|   |-- bootstrap.php               # require helper + ::register() cac class
|   |-- classes/                    # CLI scaffolder (dev tooling)
|   `-- functions/                  # helper global (underscores_child_*)
|-- partials/components/            # khoi dung chung: social-list.php
|-- stubs/cli/                      # stub cho make:page
|-- header.php / footer.php / searchform.php   # skeleton chung
|-- functions.php
`-- style.css
```
Thu muc `page-template/`, `partials/templates/`, `partials/sections/`, `assets/*/pages/` duoc scaffolder tu tao khi can.

## Nguon du lieu (uu tien WP core)

| Du lieu | Nhap o dau | Render |
|---|---|---|
| Logo, favicon, ten site, slogan | Giao dien -> Tuy bien -> Nhan dang site | `the_custom_logo()`, `wp_head()`, `bloginfo()` |
| Menu header / menu footer | Giao dien -> Menu (`header-menu`, `footer-menu`) | `wp_nav_menu` + `Theme\Nav\MenuWalker` |
| Tieu de / noi dung page | Trinh soan page | `the_title()` / `the_content()` (hoac Flexible `sections` neu co) |
| Lien he, social, ma tracking | Theme Settings (ACF options) | `footer.php`, `partials/components/social-list.php`, `ThemeHook` |
| Ban quyen footer | Ten site (Settings -> General) | `wp_date('Y')` + `get_bloginfo('name')` |

## Flow load

1. `functions.php` define constants + bootstrap (`after_setup_theme`).
2. `includes/bootstrap.php`: require helper thu tuc + goi `::register()` cho cac class (Acf\LocalJson, cac Hook).
3. Class tu autoload qua composer PSR-4 cua **parent** (`Theme\Child\` -> child `app/`).
4. KHONG con `configs/loadFile.php`. ACF dung Local JSON (`acf-json/`), KHONG con `inc/acf-fields/`.

## Cach code nhanh

- Them helper moi: them ham vao `includes/functions/{domain}-functions.php` (prefix `underscores_child_`), require trong `includes/bootstrap.php`.
- Them hook moi: tao class `app/Hooks/{Name}Hook.php` (namespace `Theme\Child\Hooks`, static `register()`), goi `::register()` trong `includes/bootstrap.php`.
- Them ACF field group: tao/sua trong wp-admin -> Custom Fields, ACF tu ghi `acf-json/group_*.json` (commit + **Sync**). Hoac tao file JSON thu cong roi Sync.
- Them flexible section: them layout trong file `acf-json/group_*.json`, render file cung ten trong `partials/sections/`.
- Override template: tao file cung ten trong child, vi du `page-template/template-about.php`.
- Toi uu Web Vitals: dat trong `app/Hooks/PerformanceHook.php` + `includes/functions/performance-functions.php` (critical CSS `assets/css/critical/{slug}.css`, `underscores_child_mark_style_loading_strategy()`).
- Script: `ThemeHook` enqueue voi `['in_footer' => true, 'strategy' => 'defer']` (core). Bot thu vien `/template` khong dung qua filter `underscores_child_common_libraries`.
- Version asset `/template` = filemtime (`underscores_child_template_asset_version()`); `style.css` child chi chua header theme nen KHONG enqueue — CSS du an viet vao `assets/css/child-theme.css`.
- jQuery mac dinh o footer (`PerformanceHook::optimize_jquery`); script plugin o head phu thuoc jquery thi core tu keo len head. Tat: filter `underscores_child_jquery_in_footer`; bo migrate: `underscores_child_jquery_migrate`.
- Khong defer/async `jquery`, `wp-hooks`, `wp-i18n`, hoac runtime Contact Form 7 neu chua verify ky.
- Chi tiet: `../underscores/.ai/rules/performance.md`.

## Main Class Pattern

- `<main>` mo trong `header.php`, dong trong `footer.php`. Class mac dinh: `main`.
- Set them class truoc `get_header()`: `underscores_child_set_main_class('page-contact');`
  (nhieu class: truyen chuoi cach nhau khoang trang hoac mang).
- Them class theo dieu kien: dung filter `main_class` trong page hook, khong hard-code trong partial.

Vi du page hook (class):
```php
namespace Theme\Child\Hooks;

defined('ABSPATH') || exit;

final class ContactPageHook
{
    public static function register(): void
    {
        $self = new self();
        add_filter('main_class', [$self, 'main_class']);
    }

    public function main_class(array $classes): array
    {
        if (! is_page_template('page-template/template-contact.php')) {
            return $classes;
        }
        $classes[] = 'underscores-contact-page';
        return $classes;
    }
}
```

Luu y: khong mo them `<main>` trong `partials/templates/*`; partial chi render noi dung ben trong `<main>`.

## WP-CLI scaffold

Tao: `page-template/template-{slug}.php`, `partials/templates/{slug}-page.php` (Flexible `sections`, fallback `the_title` + `the_content`),
`--acf` -> `acf-json/group_page_{slug}.json` (field `sections`), `--assets` -> `assets/css|scripts/pages/{slug}.*`.
Page hook (body class, enqueue asset theo page) tao tay trong `app/Hooks/{Slug}PageHook.php` + `::register()` trong `includes/bootstrap.php`.

- Standalone: `php wp-content/themes/underscores-child/bin/underscores-child make:page about --title="Giới thiệu" --acf --assets`
- Qua WP-CLI: `wp underscores make page about --acf --assets`
- `--dry-run` xem truoc, `--force` ghi de.

## AI Guide

- Entry file cho AI / agent: `AGENTS.md`
- Rule chi tiet:
  - `.ai/rules/child-theme.md`

## Ghi chu

- Helper functions: prefix `underscores_child_`.
- Class: namespace `Theme\Child\`, PascalCase, khong prefix.
- Ten file trong `partials` / `includes/functions`: `kebab-case`.
- Ten page template: `template-{name}.php`.
- ACF: Local JSON trong `acf-json/`, khong dung Extended ACF / PHP field group.
