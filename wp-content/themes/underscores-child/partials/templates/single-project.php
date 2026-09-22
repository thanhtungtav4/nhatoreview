<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

get_template_part('partials/templates/single-content', null, ['content_type' => 'project']);
