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
use Session;

/**
 * Menu class for Iframes
 * This is a placeholder class to enable menu structure in GLPI
 */
class IframeMenu extends CommonGLPI
{
    public static $rightname = 'plugin_iframemanager_iframe';

    public static function getTypeName($nb = 0)
    {
        return _n('Iframe', 'Iframes', $nb, 'iframemanager');
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
            $menu['page']  = Iframe::getSearchURL(false);
            $menu['icon']  = self::getIcon();
            
            $menu['options']['iframe_list'] = [
                'title' => __('Manage Iframes', 'iframemanager'),
                'page'  => Iframe::getSearchURL(false),
                'icon'  => 'ti ti-list',
                'links' => [
                    'search' => Iframe::getSearchURL(false),
                    'add'    => Iframe::getFormURL(false),
                ]
            ];
            
            $menu['options']['iframe_viewer'] = [
                'title' => __('Iframe Viewer', 'iframemanager'),
                'page'  => '/plugins/iframemanager/front/iframe.viewer.php',
                'icon'  => 'ti ti-browser',
            ];
            
            $menu['options']['iframe_charts'] = [
                'title' => __('Custom Charts', 'iframemanager'),
                'page'  => '/plugins/iframemanager/front/iframe.charts.php',
                'icon'  => 'ti ti-chart-line',
            ];
            
            $menu['options']['iframe_dashboard'] = [
                'title' => __('Dashboard', 'iframemanager'),
                'page'  => '/plugins/iframemanager/front/iframe.dashboard.php',
                'icon'  => 'ti ti-layout-dashboard',
            ];
        }
        
        return $menu;
    }

    public static function getIcon()
    {
        return Iframe::getIcon();
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
