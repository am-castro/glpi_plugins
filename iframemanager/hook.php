<?php

use GlpiPlugin\IframeManager\Iframe;

function plugin_change_profile_iframemanager()
{
    // Logic that runs when the profile is changed
}


/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_iframemanager_install()
{
    global $DB;

    $migration = new Migration(PLUGIN_EXAMPLE_VERSION);
    Config::setConfigurationValues('plugin:IframeManager', ['configuration' => false]);
    
    // Configure Metabase settings in glpi_configs
    Config::setConfigurationValues('plugin:IframeManager', [
        'metabase_secret_key' => '',
        'metabase_site_url' => ''
    ]);

    // Adds the right(s) to all pre-existing profiles with no access by default
    ProfileRight::addProfileRights([Iframe::$rightname]);

    // Grants full access to profiles that can update the Config (super-admins)
    $migration->addRight(Iframe::$rightname, ALLSTANDARDRIGHT, [Config::$rightname => UPDATE]);
    
    // Explicitly grant full permissions to Super-Admin profile
    $query = "UPDATE `glpi_profilerights` 
              SET `rights` = " . (CREATE | READ | UPDATE | DELETE | PURGE) . "
              WHERE `name` = '" . Iframe::$rightname . "'
              AND `profiles_id` IN (
                  SELECT `id` FROM `glpi_profiles` WHERE `name` = 'Super-Admin'
              )";
    $DB->query($query);

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

    if (!$DB->tableExists('glpi_plugin_iframemanager_iframes')) {
        $query = "CREATE TABLE `glpi_plugin_iframemanager_iframes` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `name` varchar(255) default NULL,
                  `description` TEXT,
                  `link` TEXT NOT NULL,
                  `is_active` tinyint NOT NULL default '1',
                  `is_metabase` tinyint NOT NULL default '0' COMMENT 'Use Metabase plugin configuration for token',
                  `metabase_site_url` varchar(255) default NULL COMMENT 'Base URL of Metabase instance',
                  `metabase_dashboard_id` int default NULL COMMENT 'Dashboard ID in Metabase',
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

        $DB->doQuery($query);

        // Insert example iframe (without specifying ID to use AUTO_INCREMENT)
        $query = "INSERT INTO `glpi_plugin_iframemanager_iframes`
                       (`name`, `description`, `link`, `is_active`)
                VALUES ('UNUN', 'Example dashboard', 'https://unun.site', 1)";
        $DB->doQuery($query);
    } else {
        // Migration: Add is_metabase field if it doesn't exist
        $migration->addField('glpi_plugin_iframemanager_iframes', 'is_metabase', 'bool', [
            'value' => 0,
            'comment' => 'Use Metabase plugin configuration for token'
        ]);
        
        // Migration: Add metabase_site_url field if it doesn't exist
        $migration->addField('glpi_plugin_iframemanager_iframes', 'metabase_site_url', 'string', [
            'value' => null,
            'comment' => 'Base URL of Metabase instance'
        ]);
        
        // Migration: Add metabase_dashboard_id field if it doesn't exist
        $migration->addField('glpi_plugin_iframemanager_iframes', 'metabase_dashboard_id', 'integer', [
            'value' => null,
            'comment' => 'Dashboard ID in Metabase'
        ]);
        
        $migration->executeMigration();
    }

    return true;
}


/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_iframemanager_uninstall()
{
    global $DB;

    $config = new Config();
    $config->deleteConfigurationValues('plugin:IframeManager', ['configuration' => false]);

    ProfileRight::deleteProfileRights([Iframe::$rightname]);

    // Drop iframes table
    if ($DB->tableExists('glpi_plugin_iframemanager_iframes')) {
        $query = 'DROP TABLE `glpi_plugin_iframemanager_iframes`';
        $DB->doQuery($query);
    }

    return true;
}
