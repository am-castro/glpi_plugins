<?php

/**
 * -------------------------------------------------------------------------
 * Responsibility Terms plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Responsibility Terms.
 *
 * Responsibility Terms is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Responsibility Terms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Responsibility Terms. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by F13 Tecnologia.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/f13-tecnologia/responsibilityterms
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\ResponsibilityTerms\TermTemplate;

include('../../../inc/includes.php');

Session::checkRight(TermTemplate::$rightname, READ);

if (empty($_GET["id"])) {
    $_GET["id"] = "";
}

$template = new TermTemplate();

if (isset($_POST["add"])) {
    $template->check(-1, CREATE, $_POST);
    $newID = $template->add($_POST);
    Html::redirect($template->getFormURLWithID($newID));
} elseif (isset($_POST["update"])) {
    $template->check($_POST['id'], UPDATE);
    $template->update($_POST);
    Html::back();
} elseif (isset($_POST["purge"])) {
    $template->check($_POST['id'], PURGE);
    $template->delete($_POST, 1);
    $template->redirectToList();
} elseif (isset($_POST["delete"])) {
    $template->check($_POST['id'], DELETE);
    $template->delete($_POST);
    $template->redirectToList();
}

Html::header(
    TermTemplate::getTypeName(1),
    $_SERVER['PHP_SELF'],
    'tools',
    'GlpiPlugin\ResponsibilityTerms\TermsMenu',
    'termtemplate'
);

$template->display([
    'id' => $_GET["id"]
]);

Html::footer();
