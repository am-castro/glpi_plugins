<?php

/**
 * -------------------------------------------------------------------------
 * Example plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Example.
 *
 * Example is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Example is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Example. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2006-2022 by Example plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/example
 * -------------------------------------------------------------------------
 */

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

use GlpiPlugin\IframeManager\Iframe;

// Check if user has permission to create/update iframes
if (!Session::haveRight(Iframe::$rightname, UPDATE)) {
    Session::addMessageAfterRedirect(__('No data provided', 'iframemanager'), false, ERROR);
    Html::redirect('iframe.php');
    exit;
}

$iframe = new Iframe();

// Handle form submission
if (isset($_POST['add']) || isset($_POST['update'])) {
    // Prepare input data
    $input = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'link' => $_POST['link'] ?? '',
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    $iframe_id = null;

    if (isset($_POST['update']) && isset($_POST['id'])) {
        // Update existing iframe
        $input['id'] = (int) $_POST['id'];
        $iframe_id = $input['id'];
        
        if ($iframe->update($input)) {
            Session::addMessageAfterRedirect(__('Iframe updated successfully'), true);
            // Redirect to display page
            Html::redirect('iframe.display.php?id=' . $iframe_id);
        } else {
            Session::addMessageAfterRedirect(__('Error updating iframe'), false, ERROR);
            Html::redirect('iframe.form.php?id=' . $iframe_id);
        }
    } elseif (isset($_POST['add'])) {
        // Create new iframe
        $iframe_id = $iframe->add($input);
        
        if ($iframe_id) {
            Session::addMessageAfterRedirect(__('Iframe created successfully'), true);
            // Redirect to display page
            Html::redirect('iframe.display.php?id=' . $iframe_id);
        } else {
            Session::addMessageAfterRedirect(__('Error creating iframe'), false, ERROR);
            Html::redirect('iframe.form.php');
        }
    }
}

// If we get here, something went wrong
Session::addMessageAfterRedirect(__('Invalid request'), false, ERROR);
Html::redirect('iframe.php');
