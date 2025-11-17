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

use GlpiPlugin\ResponsibilityTerms\TermTemplate;
use GlpiPlugin\ResponsibilityTerms\Term;
use GlpiPlugin\ResponsibilityTerms\Config as TermConfig;

function plugin_change_profile_responsibilityterms()
{
    // Logic that runs when the profile is changed
}

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_responsibilityterms_install()
{
    global $DB;

    $migration = new Migration(PLUGIN_RESPONSIBILITYTERMS_VERSION);
    
    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

    // Table: Term Templates
    if (!$DB->tableExists('glpi_plugin_responsibilityterms_templates')) {
        $query = "CREATE TABLE `glpi_plugin_responsibilityterms_templates` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `name` varchar(255) NOT NULL,
                  `content` LONGTEXT NOT NULL COMMENT 'Template content with placeholders',
                  `include_computers` tinyint NOT NULL default '0',
                  `include_phones` tinyint NOT NULL default '0',
                  `include_lines` tinyint NOT NULL default '0',
                  `is_active` tinyint NOT NULL default '1',
                  `date_creation` timestamp NULL default NULL,
                  `date_mod` timestamp NULL default NULL,
                  PRIMARY KEY (`id`),
                  KEY `name` (`name`),
                  KEY `is_active` (`is_active`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->doQuery($query);
    }

    // Table: Generated Terms (PDFs)
    if (!$DB->tableExists('glpi_plugin_responsibilityterms_terms')) {
        $query = "CREATE TABLE `glpi_plugin_responsibilityterms_terms` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `users_id` int {$default_key_sign} NOT NULL COMMENT 'FK to glpi_users',
                  `templates_id` int {$default_key_sign} NOT NULL COMMENT 'FK to templates table',
                  `pdf_content` LONGBLOB NULL COMMENT 'PDF binary data',
                  `pdf_filename` varchar(255) NULL,
                  `signature_status` ENUM('pending', 'sent', 'signed', 'rejected') default 'pending',
                  `signature_url` TEXT NULL COMMENT 'URL returned from signature API',
                  `date_creation` timestamp NULL default NULL,
                  `date_signature` timestamp NULL default NULL,
                  PRIMARY KEY (`id`),
                  KEY `users_id` (`users_id`),
                  KEY `templates_id` (`templates_id`),
                  KEY `signature_status` (`signature_status`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->doQuery($query);
    }

    // Table: Equipment Links (relation N-N between terms and equipment)
    if (!$DB->tableExists('glpi_plugin_responsibilityterms_items')) {
        $query = "CREATE TABLE `glpi_plugin_responsibilityterms_items` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `terms_id` int {$default_key_sign} NOT NULL COMMENT 'FK to terms table',
                  `items_id` int {$default_key_sign} NOT NULL COMMENT 'FK to equipment (Computer, Phone, Line)',
                  `itemtype` varchar(100) NOT NULL COMMENT 'Type: Computer, Phone, Line',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `unicity` (`terms_id`, `items_id`, `itemtype`),
                  KEY `terms_id` (`terms_id`),
                  KEY `item` (`itemtype`, `items_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->doQuery($query);
    }

    // Table: Configuration (signature API settings)
    if (!$DB->tableExists('glpi_plugin_responsibilityterms_configs')) {
        $query = "CREATE TABLE `glpi_plugin_responsibilityterms_configs` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `signature_url` TEXT NULL,
                  `signature_method` varchar(10) default 'POST',
                  `auth_type` ENUM('basic', 'bearer') default 'bearer',
                  `auth_user` varchar(255) NULL,
                  `auth_password` varchar(255) NULL,
                  `auth_token` TEXT NULL,
                  `date_mod` timestamp NULL default NULL,
                  PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->doQuery($query);
        
        // Insert default configuration
        $query = "INSERT INTO `glpi_plugin_responsibilityterms_configs` 
                  (`id`, `signature_method`, `auth_type`) 
                  VALUES (1, 'POST', 'bearer')";
        $DB->doQuery($query);
    }

    // Add rights to all profiles with no access by default
    ProfileRight::addProfileRights([TermTemplate::$rightname, Term::$rightname]);
    
    // Grants full access to profiles that can update the Config (super-admins)
    $migration->addRight(TermTemplate::$rightname, ALLSTANDARDRIGHT, ['config' => UPDATE]);
    $migration->addRight(Term::$rightname, ALLSTANDARDRIGHT, ['config' => UPDATE]);
    
    // Explicitly grant full permissions to Super-Admin profile
    foreach ([TermTemplate::$rightname, Term::$rightname] as $rightname) {
        // Get Super-Admin profile ID
        $profiles = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_profiles',
            'WHERE'  => ['name' => 'Super-Admin']
        ]);
        
        foreach ($profiles as $profile) {
            $DB->update(
                'glpi_profilerights',
                [
                    'rights' => CREATE | READ | UPDATE | DELETE | PURGE
                ],
                [
                    'profiles_id' => $profile['id'],
                    'name'        => $rightname
                ]
            );
        }
    }

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_responsibilityterms_uninstall()
{
    global $DB;

    // Delete rights
    ProfileRight::deleteProfileRights([TermTemplate::$rightname, Term::$rightname]);

    // Drop tables
    $tables = [
        'glpi_plugin_responsibilityterms_items',
        'glpi_plugin_responsibilityterms_terms',
        'glpi_plugin_responsibilityterms_templates',
        'glpi_plugin_responsibilityterms_configs'
    ];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $query = "DROP TABLE `{$table}`";
            $DB->doQuery($query);
        }
    }

    return true;
}
