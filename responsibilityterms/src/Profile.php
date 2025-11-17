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

namespace GlpiPlugin\ResponsibilityTerms;

use CommonGLPI;
use Html;
use Session;

/**
 * Profile class
 * Handles profile rights for the plugin
 */
class Profile extends CommonGLPI
{
    static $rightname = 'profile';

    public static function getTypeName($nb = 0)
    {
        return __('Responsibility Terms', 'responsibilityterms');
    }

    public static function getIcon()
    {
        return 'ti ti-file-certificate';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof \Profile
            && $item->getField('id')
        ) {
            return self::createTabEntry(self::getTypeName());
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof \Profile) {
            $profile = new self();
            $profile->showForm($item->getID());
        }
        return true;
    }

    public function showForm(int $profiles_id): void
    {
        // Get the actual Profile object
        $profile = new \Profile();
        if (!$profile->getFromDB($profiles_id)) {
            return;
        }

        // Check if user can view profiles
        if (!$profile->can($profiles_id, READ)) {
            return;
        }

        echo "<div class='spaced'>";

        $can_edit = Session::haveRight(self::$rightname, UPDATE);
        if ($can_edit) {
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $matrix_options = [
            'canedit' => $can_edit,
            'title'   => self::getTypeName()
        ];

        $rights = [
            [
                'itemtype' => TermTemplate::class,
                'label'    => TermTemplate::getTypeName(Session::getPluralNumber()),
                'field'    => TermTemplate::$rightname,
            ],
            [
                'itemtype' => Term::class,
                'label'    => Term::getTypeName(Session::getPluralNumber()),
                'field'    => Term::$rightname,
            ],
        ];
        
        // Use the Profile object to call displayRightsChoiceMatrix
        $profile->displayRightsChoiceMatrix($rights, $matrix_options);

        if ($can_edit) {
            echo "<div class='text-center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }
        echo '</div>';
    }

    public static function getAllRights()
    {
        return [
            [
                'rights'    => TermTemplate::$rightname,
                'label'     => TermTemplate::getTypeName(2),
                'field'     => TermTemplate::$rightname,
                'default'   => 0
            ],
            [
                'rights'    => Term::$rightname,
                'label'     => Term::getTypeName(2),
                'field'     => Term::$rightname,
                'default'   => 0
            ]
        ];
    }
}
