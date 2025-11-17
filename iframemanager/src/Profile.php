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

namespace GlpiPlugin\IframeManager;

use CommonGLPI;
use Html;
use Session;

class Profile extends CommonGLPI
{
    static $rightname = 'profile';

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $item instanceof \Profile
            && $item->getField('id')
        ) {
            return self::createTabEntry(__('Iframe Manager', 'iframemanager'));
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof \Profile) {
            $profile = new self();
            $profile->showFormExample($item->getID());
        }
        return true;
    }

    public function showFormExample(int $profiles_id): void
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
        ];
        $rights = [
            [
                'itemtype' => Iframe::class,
                'label'    => Iframe::getTypeName(Session::getPluralNumber()),
                'field'    => Iframe::$rightname,
            ],
        ];
        $matrix_options['title'] = __('Iframe Manager', 'iframemanager');
        
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
}
