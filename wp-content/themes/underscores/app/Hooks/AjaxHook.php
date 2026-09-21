<?php

declare(strict_types=1);

namespace Theme\Hooks;

defined('ABSPATH') || exit;

/**
 * Registers AJAX endpoints from includes/config/ajax.php (handle => callable map).
 */
final class AjaxHook
{
    public static function register(): void
    {
        $list_ajax = require UNDERSCORES_THEME_CONFIG_PATH . '/ajax.php';

        foreach ($list_ajax as $key => $value) {
            add_action('wp_ajax_' . $key, $value);
            add_action('wp_ajax_nopriv_' . $key, $value);
        }
    }
}
