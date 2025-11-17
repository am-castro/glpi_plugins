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

Session::checkRight(Term::$rightname, READ);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    Html::displayErrorAndDie(__('Item not found', 'responsibilityterms'));
}

$term = new Term();
if (!$term->getFromDB($_GET['id'])) {
    Html::displayErrorAndDie(__('Item not found', 'responsibilityterms'));
}

// Check if user has permission to view this term
// Either the user owns the term or has rights to view all terms
if ($term->fields['users_id'] != Session::getLoginUserID() && !Session::haveRight(Term::$rightname, READ)) {
    Html::displayErrorAndDie(__('Access denied', 'responsibilityterms'));
}

// Get PDF content from database
$pdf_content = $term->fields['pdf_content'];

if (empty($pdf_content)) {
    Html::displayErrorAndDie(__('PDF content not found', 'responsibilityterms'));
}

// Set headers for PDF download/display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="term_' . $term->fields['id'] . '.pdf"');
header('Content-Length: ' . strlen($pdf_content));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output PDF content
echo $pdf_content;
exit;
