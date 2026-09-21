# Skill: Create New Underscores Module

## Description

Automates creating a new module in the Underscores Theme following the PSR-4 modular rules.
Hooks/classes go to `app/` (namespace `Theme\`) and are wired in `includes/bootstrap.php` via `::register()`.
Helper functions go to `includes/functions/` and are required in `includes/bootstrap.php`. There is no `configs/loadFile.php`.

## Parameters

1. `module_type`: (Required) `function`, `hook`, `class`, `ajax`, `post-type`, `taxonomy`.
2. `module_name`: (Required) e.g. `Product`, `UserAuth`, `Common`.

## Execution Steps

1. **Determine path + name**:
   - `hook` → `app/Hooks/{{ModuleName}}Hook.php`, class `Theme\Hooks\{{ModuleName}}Hook`.
   - `class` → `app/{{Sub}}/{{ModuleName}}.php`, class `Theme\{{Sub}}\{{ModuleName}}`.
   - `function` → `includes/functions/{{module-name}}-functions.php` (global fns, prefix `underscores_`).
   - `ajax` → `includes/ajax/{{module-name}}-ajax.php` (global handler) + entry in `includes/config/ajax.php`.
   - `post-type` → `app/PostTypes/{{ModuleName}}.php` (class with `register()`).
   - `taxonomy` → `app/Taxonomies/{{ModuleName}}.php` (class with `register()`).

2. **Create file**:
   - Hook/class boilerplate:
     ```php
     <?php

     declare(strict_types=1);

     namespace Theme\Hooks;

     defined('ABSPATH') || exit;

     final class {{ModuleName}}Hook
     {
         public static function register(): void
         {
             $self = new self();
             // add_action(...) / add_filter(...)
         }
     }
     ```
   - Function-file boilerplate:
     ```php
     <?php

     defined('ABSPATH') || exit;

     if (! function_exists('underscores_{{name}}')) {
         function underscores_{{name}}() { /* ... */ }
     }
     ```

3. **Wire it up**:
   - Hook/class → add `\Theme\...\{{Name}}::register();` to `includes/bootstrap.php`; run `composer dump-autoload`.
   - Function/ajax → add `require_once` to `includes/bootstrap.php` (ajax: also add handle to `includes/config/ajax.php`).

4. **Report**: path created + how it was registered.

## Usage Example

**User**: `> create-new-module --type=hook --name=Product`

**AI**:
1. Creates `app/Hooks/ProductHook.php` (class `Theme\Hooks\ProductHook` with `register()`).
2. Adds `\Theme\Hooks\ProductHook::register();` to `includes/bootstrap.php`.
3. Runs `composer dump-autoload`.
4. Responds: "Created `Theme\Hooks\ProductHook` at `app/Hooks/ProductHook.php` and registered it in `includes/bootstrap.php`."
