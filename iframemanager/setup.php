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

// Load environment variables for local development (if .env exists)
if (file_exists(__DIR__ . '/.env')) {
    include_once(__DIR__ . '/load_env.php');
}

// Autoload classes
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Manual autoload fallback if composer autoload is not available
    spl_autoload_register(function ($class) {
        // Project-specific namespace prefix
        $prefix = 'GlpiPlugin\\IframeManager\\';
        
        // Base directory for the namespace prefix
        $base_dir = __DIR__ . '/src/';
        
        // Check if the class uses the namespace prefix
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // No, move to the next registered autoloader
            return;
        }
        
        // Get the relative class name
        $relative_class = substr($class, $len);
        
        // Replace namespace separators with directory separators
        // and append with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    });
}

use Glpi\Plugin\Hooks;
use GlpiPlugin\IframeManager\Iframe;
use GlpiPlugin\IframeManager\IframeMenu;
use GlpiPlugin\IframeManager\IframeChartsMenu;
use GlpiPlugin\IframeManager\Profile;

define('PLUGIN_EXAMPLE_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define('PLUGIN_EXAMPLE_MIN_GLPI', '10.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_EXAMPLE_MAX_GLPI', '11.3.99');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_iframemanager()
{
    global $PLUGIN_HOOKS,$CFG_GLPI;

    // CSRF compliance
    $PLUGIN_HOOKS['csrf_compliant']['iframemanager'] = true;

    // Display a menu entry ?
    Plugin::registerClass(Profile::class, ['addtabon' => ['Profile']]);
    Plugin::registerClass(IframeMenu::class);
    Plugin::registerClass(IframeChartsMenu::class);
    Plugin::registerClass(Iframe::class);
    
    // Add Iframes menu to Tools section using IframeMenu class
    if (Iframe::canView()) {
        $PLUGIN_HOOKS['menu_toadd']['iframemanager'] = [
            'tools' => IframeMenu::class,
            'assets' => IframeChartsMenu::class
        ];
    }

    // Config page
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['iframemanager'] = 'front/iframe.php';
    }

    // Change profile
    $PLUGIN_HOOKS['change_profile']['iframemanager'] = 'plugin_change_profile_iframemanager';
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_iframemanager()
{
    return [
        'name'         => 'Iframe Manager',
        'version'      => PLUGIN_EXAMPLE_VERSION,
        'author'       => 'F13 Tecnologia, Antonio Marcos',
        'license'      => 'GPLv2+',
        'homepage'     => 'https://github.com/pluginsGLPI/iframemanager',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_EXAMPLE_MIN_GLPI,
                'max' => PLUGIN_EXAMPLE_MAX_GLPI,
            ],
        ],
    ];
}


/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_iframemanager_check_prerequisites()
{
    return !false;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_iframemanager_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __s('Installed / not configured', 'iframemanager');
    }
    return false;
}
