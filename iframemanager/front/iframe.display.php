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

use GlpiPlugin\IframeManager\Iframe;

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

// Check if user has permission to view iframes
// if (!Session::haveRight(Iframe::$rightname, READ)) {
//     Html::displayRightError();
//     exit;
// }

// Get iframe ID from URL
$iframe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($iframe_id <= 0) {
    Session::addMessageAfterRedirect(__('Invalid iframe ID'), false, ERROR);
    Html::redirect('iframe.php');
}

try {
    $iframe_data = Iframe::getIframeById($iframe_id);
    
    // Check if iframe is active
    if (!$iframe_data['is_active']) {
        Session::addMessageAfterRedirect(__('This iframe is not active'), false, WARNING);
        Html::redirect('iframe.php');
    }
    
    // Get current user information
    $user = new User();
    $user->getFromDB(Session::getLoginUserID());
    
    // Replace placeholders in the URL
    $url = $iframe_data['link'];
    $url = str_replace('{user_id}', Session::getLoginUserID(), $url);
    $url = str_replace('{user_name}', $user->fields['name'] ?? '', $url);
    $url = str_replace('{user_realname}', $user->fields['realname'] ?? '', $url);
    $url = str_replace('{user_firstname}', $user->fields['firstname'] ?? '', $url);
    $url = str_replace('{user_email}', $user->getDefaultEmail() ?? '', $url);
    $url = str_replace('{user_login}', $user->fields['name'] ?? '', $url);
    
    // Validate URL (only allow http/https)
    $parsed_url = parse_url($url);
    if (!isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], ['http', 'https'])) {
        Session::addMessageAfterRedirect(__('Invalid URL scheme'), false, ERROR);
        Html::redirect('iframe.php');
    }
    
} catch (Exception $e) {
    Session::addMessageAfterRedirect(__('Iframe not found'), false, ERROR);
    Html::redirect('iframe.php');
}

// Display header
if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::header(
        $iframe_data['name'] ?? 'Iframe',
        $_SERVER['PHP_SELF'],
        'plugins',
        Iframe::class,
    );
} else {
    Html::helpHeader(
        $iframe_data['name'] ?? 'Iframe',
        $_SERVER['PHP_SELF']
    );
}

echo '<div class="card mb-3">';
echo '<div class="card-header">';
echo '<div style="display: flex; flex-direction: column;">';
echo '<h3>' . htmlspecialchars($iframe_data['name']) . '</h3>';
if (!empty($iframe_data['description'])) {
    echo '<p class="mb-0">' . htmlspecialchars($iframe_data['description']) . '</p>';
}
echo '</div>';
echo '</div>';
echo '<div class="card-body p-0">';
echo '<iframe src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" 
      style="width: 100%; height: 80vh; border: none;" 
      frameborder="0" 
      allowfullscreen></iframe>';
echo '</div>';
echo '<div class="d-flex gap-2 mb-3">';
echo '<a href="iframe.php" class="btn btn-secondary me-2">' . __('Back to list') . '</a>';
echo '<a href="iframe.form.php?id=' . $id . '" class="btn btn-primary">' . __('Edit') . '</a>';
echo '</div>';
echo '</div>';

Html::footer();
