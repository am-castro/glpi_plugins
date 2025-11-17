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

namespace GlpiPlugin\IframeManager;

use CommonGLPI;
use Session;

/**
 * Menu class for Iframe Charts in Assets section
 * This is a placeholder class to enable menu structure in GLPI
 */
class IframeChartsMenu extends CommonGLPI
{
    public static $rightname = 'plugin_iframemanager_iframe';

    public static function getTypeName($nb = 0)
    {
        return __('Iframe Charts', 'iframemanager');
    }

    public static function getMenuName()
    {
        return static::getTypeName(Session::getPluralNumber());
    }

    public static function getMenuContent()
    {
        $menu = [];
        
        if (Iframe::canView()) {
            $menu['title'] = self::getMenuName();
            $menu['page']  = '/plugins/iframemanager/front/iframe.charts.php';
            $menu['icon']  = 'ti ti-chart-line';
        }
        
        return $menu;
    }

    public static function getIcon()
    {
        return 'ti ti-chart-line';
    }

    public static function canView(): bool
    {
        return Iframe::canView();
    }

    public static function canCreate(): bool
    {
        return Iframe::canCreate();
    }
    
    public static function removeRightsFromSession()
    {
        // Nothing to do, rights are managed by Iframe class
    }
}
