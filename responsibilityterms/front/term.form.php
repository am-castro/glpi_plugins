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

use GlpiPlugin\ResponsibilityTerms\Term;

include('../../../inc/includes.php');

Session::checkRight(Term::$rightname, CREATE);

if (isset($_POST["generate"])) {
    $users_id = $_POST['users_id'];
    $templates_id = $_POST['templates_id'];
    
    if (!empty($users_id) && !empty($templates_id)) {
        $term_id = Term::generatePDF($users_id, $templates_id);
        
        if ($term_id) {
            Session::addMessageAfterRedirect(__('Term generated successfully', 'responsibilityterms'), true, INFO);
        } else {
            Session::addMessageAfterRedirect(__('Failed to generate term', 'responsibilityterms'), false, ERROR);
        }
    } else {
        Session::addMessageAfterRedirect(__('Missing required fields', 'responsibilityterms'), false, WARNING);
    }
    
    Html::back();
}

Html::back();
