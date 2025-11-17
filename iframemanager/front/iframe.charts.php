<?php

/**
 * -------------------------------------------------------------------------
 * Iframe Manager plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Iframe Manager.
 *
 * Iframe Manager is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Iframe Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Iframe Manager. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2024 by F13 Tecnologia.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/iframemanager
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\IframeManager\Iframe;

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

// Get all active iframes
$iframe_obj = new Iframe();
$iframes = $iframe_obj->find(['is_active' => 1], ['ORDER' => 'name']);

// Get selected iframe ID from URL
$selected_id = isset($_GET['iframe_id']) ? (int)$_GET['iframe_id'] : 0;

// If no iframe selected but there are iframes, select the first one
if ($selected_id == 0 && count($iframes) > 0) {
    $first_iframe = reset($iframes);
    $selected_id = $first_iframe['id'];
}

$iframe_data = null;
$url = null;

if ($selected_id > 0) {
    try {
        $iframe_data = Iframe::getIframeById($selected_id);
        
        // Use the new method that handles both Metabase and regular iframes
        $url = Iframe::getProcessedUrl($selected_id);
        
        // Validate URL (only allow http/https)
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], ['http', 'https'])) {
            $url = null;
        }
    } catch (Exception $e) {
        $iframe_data = null;
        $url = null;
    }
}

// Display header
if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::header(
        __('Custom Charts', 'iframemanager'),
        $_SERVER['PHP_SELF'],
        'assets',
        'GlpiPlugin\IframeManager\IframeChartsMenu'
    );
} else {
    Html::helpHeader(
        __('Custom Charts', 'iframemanager'),
        $_SERVER['PHP_SELF']
    );
}

echo '<div class="container-fluid p-3">';

// Iframe selector - compact version
if (count($iframes) == 0) {
    echo '<div class="alert alert-warning">';
    echo __('No active iframes found. Please create and activate iframes first.', 'iframemanager');
    echo '</div>';
    echo '<a href="iframe.form.php" class="btn btn-primary">' . __('Create iframe', 'iframemanager') . '</a>';
} else {
    echo '<form method="GET" action="' . $_SERVER['PHP_SELF'] . '" class="mb-3">';
    echo '<div class="row g-2 align-items-center">';
    echo '<div class="col-auto">';
    echo '<label for="iframe_id" class="col-form-label">' . __('Chart:', 'iframemanager') . '</label>';
    echo '</div>';
    echo '<div class="col-auto" style="min-width: 300px;">';
    echo '<select name="iframe_id" id="iframe_id" class="form-select" onchange="this.form.submit()">';
    
    foreach ($iframes as $iframe) {
        $selected = ($iframe['id'] == $selected_id) ? 'selected' : '';
        echo '<option value="' . $iframe['id'] . '" ' . $selected . '>';
        echo htmlspecialchars($iframe['name']);
        echo '</option>';
    }
    
    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    
    // Display iframe if selected and URL is valid
    if ($url && $iframe_data) {
        echo '<div style="width: 100%; height: calc(100vh - 180px);">';
        echo '<iframe src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" ';
        echo 'style="width: 100%; height: 100%; border: none;" ';
        echo 'frameborder="0" ';
        echo 'allowfullscreen>';
        echo '</iframe>';
        echo '</div>';
    }
}

echo '</div>';

Html::footer();
