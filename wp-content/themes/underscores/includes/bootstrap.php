<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Bootstrap the theme.
 *
 * Namespaced classes (Theme\) autoload via composer PSR-4 (app/).
 * Procedural helpers + AJAX handlers are plain functions, required here.
 */

// Procedural helpers (used as template tags / hook callbacks).
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/image-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/pagination-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/functions/taxonomy-functions.php';
require_once UNDERSCORES_THEME_INCLUDES_PATH . '/ajax/post-ajax.php';

// Register theme setup + hook classes.
\Theme\Setup\ThemeSetup::register();
\Theme\Hooks\CommonHook::register();
\Theme\Hooks\ImageHook::register();
\Theme\Hooks\AjaxHook::register();
