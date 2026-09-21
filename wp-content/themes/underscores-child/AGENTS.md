# Underscores Theme Child — Agent Guide

Nguồn hướng dẫn chung cho mọi AI agent (Claude Code, Cursor, Copilot, Antigravity/Gemini, opencode, Trae...).
Read this file before editing `wp-content/themes/underscores-child`.
Rules + skills nằm trong `.ai/` (trung lập). Các file `GEMINI.md`, `.cursor/rules/`, `.github/copilot-instructions.md` chỉ trỏ về đây.

## Goal

This child theme keeps the newer Underscores child structure, but uses a page-building style with these priorities:

- keep the file structure modular
- keep page templates easy to read
- keep ACF grouped by page
- keep page assets loaded only where needed

This guide must be self-contained. Do not assume any other local theme exists for reference.

Starter state: the child ships with NO page templates, page field groups or project markup. `header.php` / `footer.php` are a generic skeleton (logo, menu, search, contact, social, footer menu, copyright). Each project builds its own pages with the scaffolder below and replaces the skeleton markup with its design.

## Read These Files First

1. `.ai/rules/child-theme.md`
2. News/Woo: `../underscores/.ai/rules/news-site.md` + `../underscores/.ai/rules/woocommerce.md` (child override header/footer/home phải tuân 2 rule này)
3. `includes/bootstrap.php` (bootstrap: requires helpers, registers hook classes)
4. `app/Hooks/ThemeHook.php`
5. `app/Hooks/PerformanceHook.php`
6. `includes/functions/performance-functions.php`
7. `includes/functions/template-functions.php`
8. `../underscores/.ai/rules/performance.md` (asset strategy, images/LCP, queries, page cache)

Hooks are PSR-4 classes under `app/` (namespace `Theme\Child\`), autoloaded via the
parent `composer.json` map and wired in `includes/bootstrap.php` by calling each class `register()`.
Procedural helpers stay as plain functions in `includes/functions/`. There is no `configs/loadFile.php`.

## Default Workflow For A New Page

1. Create `page-template/template-{slug}.php`.
2. Create `partials/templates/{slug}-page.php` if the page markup should stay separate from the WP template wrapper.
3. Create the page field group via ACF Local JSON: `acf-json/group_page_{slug}.json` (or define it in wp-admin → Custom Fields and let ACF write the JSON, then commit + Sync).
4. Create `app/Hooks/{Slug}PageHook.php` (namespace `Theme\Child\Hooks`, final class with a static `register()`) if the page needs body classes or page-specific CSS/JS.
5. Call `\Theme\Child\Hooks\{Slug}PageHook::register();` in `includes/bootstrap.php`.
6. Put page CSS in `assets/css/pages/{slug}.css`.
7. Put page JS in `assets/scripts/pages/{slug}.js`.

If the local WP-CLI command is available, prefer:

```bash
wp underscores make page about --title="About" --acf --assets
```

If the local environment does not bootstrap WordPress cleanly, use the standalone child-theme scaffold command instead:

```bash
php wp-content/themes/underscores-child/bin/underscores-child make:page about --title="About" --acf --assets
```

## Coding Direction

- Prefer readable page templates over heavy abstraction.
- Prefer one ACF file per page template.
- Prefer one hook file per page when page-specific assets are needed.
- Remember that Underscores also uses a hook-based asset pattern where CSS/JS are registered outside the template and injected through common asset hooks.
- Use shared partials only when the block is truly reused.
- Use `get_fields()` once in large templates, then split into `*_settings` variables.
- Use `Group + Tab + is_show` for section-based ACF layouts when appropriate.
- Follow this template style when building large pages:
  - assign each section to a simple variable near the top of the template
  - use names that match the ACF group and partial name, for example `$banner_settings`, `$program_settings`
  - render sections in a fixed explicit order with normal `if` blocks
  - keep the main template as an orchestration layer: load fields, assign variables, call partials
  - prefer page-specific partials over a large monolithic template
  - pass the raw section array into the partial instead of a transformed page-wide data object
  - keep fallback content close to the section markup inside the partial when possible
  - allow small generic helpers for image URL, link normalization, and section visibility
- Keep helper functions generic. Small helpers for image/link/visibility are fine.
- Do not create page-specific data preparation functions such as `prepare_*_data()` for templates.
- Do not use a `section => partial` render loop when a plain explicit sequence is easier to read.

Example:

```php
$acf_fields = get_fields() ?: [];
$banner_settings = $acf_fields['banner_settings'] ?? [];
$program_settings = $acf_fields['program_settings'] ?? [];

get_header();

if (!empty($banner_settings) && !empty($banner_settings['is_show'])) {
    get_template_part('partials/front-page/section-banner', null, $banner_settings);
}

if (!empty($program_settings) && !empty($program_settings['is_show'])) {
    get_template_part('partials/front-page/section-program', null, $program_settings);
}

get_footer();
```

## Avoid

> Lazy-first: dòng code tốt nhất là dòng không viết. Theo `../underscores/.ai/rules/lazy-first.md` (ladder YAGNI → tái dùng → WP core → native → 1 dòng), không bao giờ cắt validation/security/a11y.

- Do not put business logic directly into `functions.php`.
- Do not add a huge global asset loader for page-specific CSS/JS if a small page hook is enough.
- Do not over-engineer helpers when plain template code is clearer.
- Do not hide an entire page's defaults and structure inside one shared helper function.
- Do not create dummy/placeholder data, demo content, or seed scripts. Data is entered in admin.
- Do not wrap a one-line `get_field()`/`get_sub_field()` in a helper. Read it directly.
- When rendering data, only guard against empty (`?? ''` / `?: 0`) and hide the block if empty — never invent default content. See `.ai/rules/data-rendering.md`.
- Do not require another repo to understand the expected code style. If a rule matters, describe it in this file or the linked rule files.

## Overrides

- Root-level template overrides such as `header.php`, `footer.php`, `page.php`, or `single.php` should live directly in the child theme root.
- Shared UI blocks belong in `partials/components/`.
- Flexible-content or reusable section blocks belong in `partials/sections/`.

## Underscores Asset Pattern

The parent Underscores theme has a second asset-loading style:

- common assets are loaded in a global hook file
- page or context assets are injected via:
  - `underscores_before_common_css`
  - `underscores_after_common_css`
  - `underscores_before_common_js`
  - `underscores_after_common_js`

Use this pattern when the new child code should align closely with the parent Underscores flow.

Use a normal `wp_enqueue_scripts` page hook when that is simpler and more isolated.

In this repo, the child theme owns the common CSS/JS pipeline.

- keep generic shared hooks and filters in the parent theme
- disable the parent common CSS/JS enqueue when the child provides its own pipeline
- rebuild the shared asset order in the child so Underscores asset hooks still work
- do not move the whole parent `CommonHook.php` into the child just to change project assets
- do not enqueue the parent `style.css` unless it contains real runtime CSS

Important:

- do not enqueue CSS/JS directly inside template markup
- define your own `$prefix` inside the callback when needed
- do not assume the Underscores hook passes callback arguments
- keep Web Vitals tweaks in child `PerformanceHook.php` (critical CSS, style loading strategy), not in the parent `CommonHook.php`
- defer scripts with the core API (`'strategy' => 'defer'` / `wp_script_add_data()`), never by rewriting tags in `script_loader_tag`
- trim unused `/template` libraries with the `underscores_child_common_libraries` filter instead of editing the enqueue loop
- do not defer or async jQuery, `wp-hooks`, `wp-i18n`, or CF7 runtime handles unless there is a project-specific proof that it is safe
- jQuery is printed in the footer by default (`PerformanceHook::optimize_jquery`); never echo raw `<script>jQuery(...)</script>` — use `wp_add_inline_script('jquery', ..., 'after')`. Opt out with `underscores_child_jquery_in_footer`.

## Source Of Truth

When there is a conflict:

1. Follow the child theme structure in this repo.
2. Follow the explicit template style described in this file and the linked rule files.
3. Keep the final result simple enough for another developer to edit quickly.
