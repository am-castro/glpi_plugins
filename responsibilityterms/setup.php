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

// Autoload via Composer if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback: Manual autoload
    spl_autoload_register(function ($class) {
        // Project-specific namespace prefix
        $prefix = 'GlpiPlugin\\ResponsibilityTerms\\';
        
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
        
        // Replace namespace separators with directory separators and append with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    });
}

use GlpiPlugin\ResponsibilityTerms\TermTemplate;
use GlpiPlugin\ResponsibilityTerms\Term;
use GlpiPlugin\ResponsibilityTerms\Config;
use GlpiPlugin\ResponsibilityTerms\TermsMenu;
use GlpiPlugin\ResponsibilityTerms\Profile;

define('PLUGIN_RESPONSIBILITYTERMS_VERSION', '1.0.0');
define('PLUGIN_RESPONSIBILITYTERMS_MIN_GLPI', '10.0.0');
define('PLUGIN_RESPONSIBILITYTERMS_MAX_GLPI', '11.3.99');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_responsibilityterms()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    // CSRF compliance
    $PLUGIN_HOOKS['csrf_compliant']['responsibilityterms'] = true;

    // Register classes
    Plugin::registerClass(Profile::class, ['addtabon' => ['Profile']]);
    Plugin::registerClass(Term::class, ['addtabon' => ['User']]);
    Plugin::registerClass(TermsMenu::class);
    Plugin::registerClass(TermTemplate::class);
    Plugin::registerClass(Config::class);
    
    // Add menu entries
    if (TermTemplate::canView()) {
        // Ferramentas → Termos
        $PLUGIN_HOOKS['menu_toadd']['responsibilityterms'] = [
            'tools' => TermsMenu::class
        ];
    }

    // Add Terms tab to User page
    if (Term::canView()) {
        $PLUGIN_HOOKS['add_javascript']['responsibilityterms'][] = 'js/terms.js';
    }

    // Config page
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['responsibilityterms'] = 'front/config.form.php';
    }

    // Change profile
    $PLUGIN_HOOKS['change_profile']['responsibilityterms'] = 'plugin_change_profile_responsibilityterms';
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_responsibilityterms()
{
    return [
        'name'         => 'Responsibility Terms',
        'version'      => PLUGIN_RESPONSIBILITYTERMS_VERSION,
        'author'       => 'F13 Tecnologia',
        'license'      => 'GPLv2+',
        'homepage'     => 'https://github.com/f13-tecnologia/responsibilityterms',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_RESPONSIBILITYTERMS_MIN_GLPI,
                'max' => PLUGIN_RESPONSIBILITYTERMS_MAX_GLPI,
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
function plugin_responsibilityterms_check_prerequisites()
{
    // Check if PHP has PDF support (for future PDF generation)
    if (!extension_loaded('gd')) {
        echo "GD extension is required for PDF generation";
        return false;
    }
    
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_responsibilityterms_check_config($verbose = false)
{
    if (true) {
        return true;
    }

    if ($verbose) {
        echo __s('Installed / not configured', 'responsibilityterms');
    }
    return false;
}
