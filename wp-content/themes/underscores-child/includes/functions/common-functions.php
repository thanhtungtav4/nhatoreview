<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('underscores_get_option')) {
    function underscores_get_option(string $field_name, mixed $default = null): mixed
    {
        if (!function_exists('get_field')) {
            return $default;
        }

        // ACF 6.x stores the singleton in $GLOBALS['acf']; during edge-case
        // early loading the global may be an array instead of the ACF object,
        // causing a fatal on ->has_setting(). Guard both ways.
        if (function_exists('acf')) {
            $instance = acf();
            if (!is_object($instance)) {
                return $default;
            }
        }

        try {
            $value = get_field($field_name, 'option');
        } catch (\Throwable $e) {
            return $default;
        }

        if ($value === null || $value === false || $value === '' || $value === []) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('underscores_child_asset_path')) {
    function underscores_child_asset_path(string $relative_path): string
    {
        return UNDERSCORES_CHILD_THEME_PATH . '/' . ltrim($relative_path, '/');
    }
}

if (!function_exists('underscores_child_asset_uri')) {
    function underscores_child_asset_uri(string $relative_path): string
    {
        return UNDERSCORES_CHILD_THEME_URI . '/' . ltrim($relative_path, '/');
    }
}

if (!function_exists('underscores_child_asset_version')) {
    function underscores_child_asset_version(string $relative_path): string
    {
        $asset_path = underscores_child_asset_path($relative_path);

        if (file_exists($asset_path)) {
            return (string) filemtime($asset_path);
        }

        return UNDERSCORES_CHILD_THEME_VERSION ?: '1.0.0';
    }
}

if (!function_exists('underscores_child_template_asset_version')) {
    /**
     * Version cho asset trong build `/template` (UNDERSCORES_SITE_TEMPLATE_URL = siteurl/template
     * ↔ ABSPATH/template): filemtime → build mới tự bust cache, không phụ thuộc version theme.
     */
    function underscores_child_template_asset_version(string $relative_path): string
    {
        $file = ABSPATH . 'template/' . ltrim($relative_path, '/');

        return file_exists($file) ? (string) filemtime($file) : (string) UNDERSCORES_THEME_VERSION;
    }
}
