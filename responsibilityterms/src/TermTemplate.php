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

use CommonDBTM;
use Html;
use Session;

/**
 * Term Template class
 * Manages templates for responsibility terms
 */
class TermTemplate extends CommonDBTM
{
    public static $rightname = 'plugin_responsibilityterms_template';
    
    public static function getTable($classname = null)
    {
        return 'glpi_plugin_responsibilityterms_templates';
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Term Template', 'Term Templates', $nb, 'responsibilityterms');
    }

    public static function getMenuName()
    {
        return __('Term Templates', 'responsibilityterms');
    }

    public static function getIcon()
    {
        return 'ti ti-file-text';
    }

    public static function canCreate(): bool
    {
        return Session::haveRight(self::$rightname, CREATE);
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }

    public static function canUpdate(): bool
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canDelete(): bool
    {
        return Session::haveRight(self::$rightname, DELETE);
    }

    public static function canPurge(): bool
    {
        return Session::haveRight(self::$rightname, PURGE);
    }

    public function getTabNameForItem(\CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return self::getTypeName(2);
        }
        return '';
    }

    public static function displayTabContentForItem(\CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            self::showForProfile($item->getID());
        }
        return true;
    }

    /**
     * Show profile form for permissions
     *
     * @param int $profiles_id
     */
    public static function showForProfile($profiles_id)
    {
        $profile = new \Profile();
        $profile->getFromDB($profiles_id);

        if ($profile->fields['interface'] == 'central') {
            \ProfileRight::displayRightsChoiceMatrix([
                self::$rightname => [
                    'label' => self::getTypeName(2),
                    'field' => self::$rightname
                ]
            ], [
                'canedit'       => true,
                'default_class' => 'tab_bg_2',
                'title'         => __('Responsibility Terms', 'responsibilityterms')
            ]);
        }
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        echo Html::input('name', ['value' => $this->fields['name'] ?? '', 'size' => 50]);
        echo "</td>";
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo('is_active', $this->fields['is_active'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4'>" . __('Template Content', 'responsibilityterms') . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4'>";
        echo "<textarea name='content' rows='15' style='width:98%;'>";
        echo $this->fields['content'] ?? '';
        echo "</textarea>";
        echo "<br><small>" . __('Available placeholders: {USER_NAME}, {USER_EMAIL}, {USER_REGISTRATION}, {EQUIPMENT_LIST}, {DATE}', 'responsibilityterms') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4'><strong>" . __('Include Equipment Types', 'responsibilityterms') . "</strong></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Computers', 'responsibilityterms') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo('include_computers', $this->fields['include_computers'] ?? 0);
        echo "</td>";
        echo "<td>" . __('Phones', 'responsibilityterms') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo('include_phones', $this->fields['include_phones'] ?? 0);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Lines (CHIPs)', 'responsibilityterms') . "</td>";
        echo "<td>";
        \Dropdown::showYesNo('include_lines', $this->fields['include_lines'] ?? 0);
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }

    public function prepareInputForAdd($input)
    {
        if (!isset($input['date_creation'])) {
            $input['date_creation'] = $_SESSION['glpi_currenttime'];
        }
        $input['date_mod'] = $_SESSION['glpi_currenttime'];
        
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $input['date_mod'] = $_SESSION['glpi_currenttime'];
        return $input;
    }

    /**
     * Get raw search function for the class
     *
     * @return array of search option
     */
    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'            => '2',
            'table'         => self::getTable(),
            'field'         => 'name',
            'name'          => __('Name'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id'       => '3',
            'table'    => self::getTable(),
            'field'    => 'is_active',
            'name'     => __('Active'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id'       => '4',
            'table'    => self::getTable(),
            'field'    => 'include_computers',
            'name'     => __('Include Computers', 'responsibilityterms'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id'       => '5',
            'table'    => self::getTable(),
            'field'    => 'include_phones',
            'name'     => __('Include Phones', 'responsibilityterms'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id'       => '6',
            'table'    => self::getTable(),
            'field'    => 'include_lines',
            'name'     => __('Include Lines', 'responsibilityterms'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id'       => '19',
            'table'    => self::getTable(),
            'field'    => 'date_creation',
            'name'     => __('Creation date'),
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id'       => '121',
            'table'    => self::getTable(),
            'field'    => 'date_mod',
            'name'     => __('Last update'),
            'datatype' => 'datetime'
        ];

        return $tab;
    }

    /**
     * Get templates for dropdown
     *
     * @return array
     */
    public static function getTemplatesForDropdown()
    {
        global $DB;

        $templates = [];
        
        $iterator = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => ['is_active' => 1],
            'ORDER' => 'name'
        ]);

        foreach ($iterator as $data) {
            $templates[$data['id']] = $data['name'];
        }

        return $templates;
    }
}
