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

/**
 * Terms Menu class
 * Creates the menu structure: Ferramentas → Termos
 */
class TermsMenu extends CommonGLPI
{
    public static function getMenuName()
    {
        return __('Terms', 'responsibilityterms');
    }

    public static function getIcon()
    {
        return 'ti ti-file-certificate';
    }

    public static function getMenuContent()
    {
        global $CFG_GLPI;

        $menu = [];
        $menu['title'] = self::getMenuName();
        $menu['page']  = '/plugins/responsibilityterms/front/termtemplate.php';
        $menu['icon']  = self::getIcon();

        // Add submenu items
        $menu['options'] = [];

        // Template de Termos
        if (TermTemplate::canView()) {
            $menu['options']['termtemplate'] = [
                'title' => TermTemplate::getTypeName(2),
                'page'  => TermTemplate::getSearchURL(false),
                'icon'  => TermTemplate::getIcon(),
                'links' => [
                    'search' => TermTemplate::getSearchURL(false),
                ]
            ];

            if (TermTemplate::canCreate()) {
                $menu['options']['termtemplate']['links']['add'] = TermTemplate::getFormURL(false);
            }
        }

        // Configurações
        if (\Session::haveRight('config', UPDATE)) {
            $menu['options']['config'] = [
                'title' => Config::getTypeName(1),
                'page'  => '/plugins/responsibilityterms/front/config.form.php',
                'icon'  => Config::getIcon(),
            ];
        }

        return $menu;
    }
}
