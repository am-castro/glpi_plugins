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

/**
 * Show how to dowload a file (or any stream) from the REST API
 * as well as metatadata stored in DB
 *
 * This itemtype is designed to be the same as Document in GLPI Core
 * to focus on the file dowload and upload features
 *
 * Example to download a file with cURL
 *
 * $ curl -X GET \
 * -H 'Content-Type: application/json' \
 * -H 'Session-Token: s6f3jik227ttrsat7d8ap9laal' \
 * -H 'Accept: application/octet-stream' \
 * 'http://path/to/glpi/apirest.php/PluginExampleDocument/1' \
 * --output /tmp/test_download
 *
 * Example to upload a file with cURL
 *
 * $ curl -X POST \
 * -H 'Content-Type: multipart/form-data' \
 * -H "Session-Token: s6f3jik227ttrsat7d8ap9laal" \
 * -F 'uploadManifest={"input": {"name": "Uploaded document", "_filename" : ["file.txt"]}}' \
 * -F 'file[]=@/tmp/test.txt' \
 * 'http://path/to/glpi/apirest.php/PluginExampleDocument/'
 *
 */

namespace GlpiPlugin\IframeManager;

use Glpi\Exception\Http\NotFoundHttpException;
use Glpi\Exception\Http\HttpException;

use CommonDBTM;
use CommonGLPI;
use Dropdown;
use Html;
use Session;

class Iframe extends CommonDBTM
{
    public static $rightname = 'plugin_iframemanager_iframe';
    
    // Explicitly define the table name for GLPI
    public static function getTable($classname = null)
    {
        return 'glpi_plugin_iframemanager_iframes';
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Iframe', 'Iframes', $nb, 'iframemanager');
    }

    public static function getMenuName()
    {
        return self::getTypeName(1);
    }

    public static function getIcon()
    {
        return 'ti ti-layout-dashboard';
    }

    public static function canCreate(): bool
    {
        // TODO: Debug - remover o true depois que as permissões funcionarem
        return true; // Session::haveRight(self::$rightname, CREATE) || Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canView(): bool
    {
        return true; // Session::haveRight(self::$rightname, READ);
    }

    public static function canUpdate(): bool
    {
        return true; // Session::haveRight(self::$rightname, UPDATE);
    }

    public static function canDelete(): bool
    {
        return true; // Session::haveRight(self::$rightname, DELETE);
    }

    public static function getSearchURL($full = true)
    {
        return \Plugin::getWebDir('iframemanager', $full) . '/front/iframe.php';
    }

    public static function getFormURL($full = true)
    {
        return \Plugin::getWebDir('iframemanager', $full) . '/front/iframe.form.php';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return self::getTypeName(1);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Config') {
            $iframe = new self();
            $iframe->showForm();
        }
        return true;
    }

    public function showForm($ID = -1, array $options = [])
    {
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        echo Html::input('name', ['value' => $this->fields['name'] ?? '', 'size' => 40]);
        echo "</td>";
        echo "<td>" . __('Active') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('is_active', $this->fields['is_active'] ?? 1);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Description') . "</td>";
        echo "<td colspan='3'>";
        echo Html::textarea(['name' => 'description', 'value' => $this->fields['description'] ?? '', 'cols' => 80, 'rows' => 3]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Is Metabase Dashboard?', 'iframemanager') . "</td>";
        echo "<td colspan='3'>";
        $is_metabase = $this->fields['is_metabase'] ?? 0;
        Dropdown::showYesNo('is_metabase', $is_metabase, -1, ['on_change' => 'toggleMetabaseFields()']);
        echo "<br><small>" . __('If yes, will use Metabase configuration to sign the URL with JWT token', 'iframemanager') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' id='url_field' style='display: " . ($is_metabase ? 'none' : 'table-row') . ";'>";
        echo "<td>" . __('URL') . "</td>";
        echo "<td colspan='3'>";
        echo Html::input('link', ['value' => $this->fields['link'] ?? '', 'size' => 80, 'type' => 'url']);
        echo "<br><small>" . __('Full URL for the iframe (e.g., https://example.com/page)', 'iframemanager') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1 metabase_field' style='display: " . ($is_metabase ? 'table-row' : 'none') . ";'>";
        echo "<td>" . __('Metabase Dashboard ID', 'iframemanager') . "</td>";
        echo "<td colspan='3'>";
        echo Html::input('metabase_dashboard_id', ['value' => $this->fields['metabase_dashboard_id'] ?? '', 'size' => 10, 'type' => 'number']);
        echo "<br><small>" . __('Dashboard ID from Metabase (only the number, e.g., 3)', 'iframemanager') . "</small>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1 metabase_field' style='display: " . ($is_metabase ? 'table-row' : 'none') . ";'>";
        echo "<td>" . __('Metabase Site URL', 'iframemanager') . " <span style='color: #666;'>(" . __('Optional', 'iframemanager') . ")</span></td>";
        echo "<td colspan='3'>";
        echo Html::input('metabase_site_url', ['value' => $this->fields['metabase_site_url'] ?? '', 'size' => 80, 'type' => 'url']);
        echo "<br><small>" . __('Base URL of your Metabase instance (e.g., https://metabase.solarbr.com.br). Leave empty to use the URL configured in the Metabase plugin.', 'iframemanager') . "</small>";
        echo "</td>";
        echo "</tr>";

        // JavaScript to toggle fields
        echo "<script>
        function toggleMetabaseFields() {
            const isMetabase = document.querySelector('[name=\"is_metabase\"]').value == '1';
            document.getElementById('url_field').style.display = isMetabase ? 'none' : 'table-row';
            document.querySelectorAll('.metabase_field').forEach(el => {
                el.style.display = isMetabase ? 'table-row' : 'none';
            });
        }
        </script>";

        $this->showFormButtons($options);
        return true;
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Characteristics')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'description',
            'name'               => __('Description'),
            'datatype'           => 'text',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'link',
            'name'               => __('URL'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => $this->getTable(),
            'field'              => 'is_active',
            'name'               => __('Active'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('View'),
            'datatype'           => 'specific',
            'nosearch'           => true,
            'nosort'             => true,
            'massiveaction'      => false,
            'additionalfields'   => ['id']  // Changed from 'name' to 'id'
        ];

        return $tab;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'id':
                // Debug: Get the actual ID from the row data
                // In search results, the row ID is in a different place
                $id = null;
                
                // Try multiple sources for the ID
                if (isset($options['raw_data']['id']) && is_numeric($options['raw_data']['id'])) {
                    $id = (int)$options['raw_data']['id'];
                } elseif (isset($values['id']) && is_numeric($values['id'])) {
                    $id = (int)$values['id'];
                } elseif (isset($options['searchopt']['table']) && isset($options['item_id'])) {
                    $id = (int)$options['item_id'];
                }
                
                if ($id && $id > 0) {
                    $plugin_dir = \Plugin::getWebDir('iframemanager');
                    $url = $plugin_dir . '/front/iframe.display.php?id=' . $id;
                    return '<a href="' . htmlspecialchars($url) . '" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> ' . __('View') . '
                            </a>';
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
    
    /**
     * Retrieves the URL of an iframe by its ID.
     *
     * @param int $id The ID of the iframe.
     * @return string The URL of the iframe.
     * @throws NotFoundHttpException If the iframe is not found.
     */
    public static function getUrl(int $id): string
    {
        $iframe = new self();
        if (!$iframe->getFromDB($id)) {
            throw new NotFoundHttpException("Iframe with ID $id not found.");
        }
        return $iframe->fields['link'];
    }
    /**
     * Retrieves the content of an iframe by its ID.
     *
     * @param int $id The ID of the iframe.
     * @return string The content of the iframe.
     * @throws NotFoundHttpException If the iframe is not found.
     */
    public static function getContent(int $id): string
    {
        $iframe = new self();
        if (!$iframe->getFromDB($id)) {
            throw new NotFoundHttpException("Iframe with ID $id not found.");
        }
        return $iframe->fields['content'];
    }

    public static function getIframeById(int $id): array
    {
        $iframe = new self();
        if (!$iframe->getFromDB($id)) {
            throw new NotFoundHttpException("Iframe with ID $id not found.");
        }
        return $iframe->fields;
    }
    /**
     * Retrieves all iframes.
     *
     * @return array An array of all iframes.
     */
    public static function getAll(): array
    {
        $iframe = new self();
        return $iframe->find([]);
    }

    public static function createIframe(array $data): int
    {
        $iframe = new self();
        return $iframe->add($data);
    }

    public static function updateIframe(int $id, array $data): bool
    {
        $iframe = new self();
        if (!$iframe->getFromDB($id)) {
            throw new NotFoundHttpException("Iframe with ID $id not found.");
        }
        $data['id'] = $id;
        return $iframe->update($data);
    }

    public static function deleteIframe(int $id): bool
    {
        $iframe = new self();
        if (!$iframe->getFromDB($id)) {
            throw new NotFoundHttpException("Iframe with ID $id not found.");
        }
        return $iframe->delete(['id' => $id]);
    }

    public static function displayForm(int $id = null): void
    {
        $iframe = new self();
        if ($id && is_numeric($id) && $id > 0) {
            $iframe->getFromDB($id);
        }
        $iframe->showForm($id);
    }

    /**
     * Get the processed URL for display (with Metabase support)
     * 
     * @param int $id Iframe ID
     * @return string The processed URL ready for iframe src
     */
    public static function getProcessedUrl(int $id): string
    {
        $iframe_data = self::getIframeById($id);
        
        // Check if it's a Metabase iframe
        if (!empty($iframe_data['is_metabase'])) {
            return self::generateMetabaseUrl($iframe_data);
        }
        
        // Regular iframe - replace placeholders
        return self::replacePlaceholders($iframe_data['link']);
    }

    /**
     * Get Metabase token from GLPI Metabase plugin configuration
     * 
     * @return string|null The Metabase secret token or null if not found
     */
    private static function getMetabaseToken(): ?string
    {
        global $DB;
        
        // Priority 1: Try to get from glpi_configs table (plugin:metabase context)
        // The official Metabase plugin stores the token as 'embedded_token'
        $config = \Config::getConfigurationValues('plugin:metabase');
        if (isset($config['embedded_token']) && !empty($config['embedded_token'])) {
            return $config['embedded_token'];
        }
        
        // Priority 2: Try direct query to glpi_configs
        if ($DB->tableExists('glpi_configs')) {
            $query = "SELECT `value` FROM `glpi_configs` 
                      WHERE `context` = 'plugin:metabase' 
                      AND `name` = 'embedded_token' 
                      AND `value` IS NOT NULL 
                      AND `value` != '' 
                      LIMIT 1";
            $result = $DB->query($query);
            
            if ($result && $row = $DB->fetchAssoc($result)) {
                $token = $row['value'] ?? null;
                if (!empty($token)) {
                    return $token;
                }
            }
        }
        
        // Priority 3: Try old format with secret_key
        if ($DB->tableExists('glpi_plugin_metabase_configs')) {
            $query = "SELECT `secret_key` FROM `glpi_plugin_metabase_configs` WHERE `secret_key` IS NOT NULL AND `secret_key` != '' LIMIT 1";
            $result = $DB->query($query);
            
            if ($result && $row = $DB->fetchAssoc($result)) {
                $token = $row['secret_key'] ?? null;
                if (!empty($token)) {
                    return $token;
                }
            }
        }
        
        // Priority 4: Try environment variable (for development/testing)
        if (getenv('METABASE_SECRET_KEY')) {
            return getenv('METABASE_SECRET_KEY');
        }
        
        // Priority 5: Try PHP constant (can be defined in config_db.php)
        if (defined('METABASE_SECRET_KEY')) {
            return METABASE_SECRET_KEY;
        }
        
        return null;
    }

    /**
     * Get Metabase Site URL from GLPI Metabase plugin configuration
     * 
     * @return string|null The Metabase site URL or null if not found
     */
    private static function getMetabaseSiteUrl(): ?string
    {
        global $DB;
        
        // Priority 1: Try to get from Metabase plugin table
        if ($DB->tableExists('glpi_plugin_metabase_configs')) {
            $query = "SELECT `metabase_url` FROM `glpi_plugin_metabase_configs` WHERE `metabase_url` IS NOT NULL AND `metabase_url` != '' LIMIT 1";
            $result = $DB->query($query);
            
            if ($result && $row = $DB->fetchAssoc($result)) {
                $url = $row['metabase_url'] ?? null;
                if (!empty($url)) {
                    // Remove trailing slash
                    return rtrim($url, '/');
                }
            }
        }
        
        // Priority 2: Try to get from glpi_configs table
        $config = \Config::getConfigurationValues('plugin:Metabase');
        if (isset($config['metabase_url']) && !empty($config['metabase_url'])) {
            return rtrim($config['metabase_url'], '/');
        }
        
        // Priority 3: Try alternative config storage
        if ($DB->tableExists('glpi_configs')) {
            $query = "SELECT `value` FROM `glpi_configs` 
                      WHERE `context` = 'plugin:Metabase' 
                      AND `name` = 'metabase_url' 
                      AND `value` IS NOT NULL 
                      AND `value` != '' 
                      LIMIT 1";
            $result = $DB->query($query);
            
            if ($result && $row = $DB->fetchAssoc($result)) {
                $url = $row['value'] ?? null;
                if (!empty($url)) {
                    return rtrim($url, '/');
                }
            }
        }
        
        // Priority 4: Try environment variable (for development/testing)
        if (getenv('METABASE_SITE_URL')) {
            return rtrim(getenv('METABASE_SITE_URL'), '/');
        }
        
        // Priority 5: Try PHP constant (can be defined in config_db.php)
        if (defined('METABASE_SITE_URL')) {
            return rtrim(METABASE_SITE_URL, '/');
        }
        
        return null;
    }

    /**
     * Generate Metabase signed URL
     * 
     * @param array $iframe_data Iframe data from database
     * @return string Signed Metabase URL
     */
    private static function generateMetabaseUrl(array $iframe_data): string
    {
        // Get token from Metabase plugin configuration
        $token = self::getMetabaseToken();
        
        if (!$token) {
            // No token found, return original URL
            // NOTE: For this to work, you need to enable "Public Sharing" in Metabase
            // or install the GLPI Metabase plugin and configure the secret key
            \Session::addMessageAfterRedirect(
                __('Warning: Metabase plugin not configured. Using direct URL. Enable public sharing in Metabase or install the GLPI Metabase plugin.', 'iframemanager'),
                false,
                WARNING
            );
            return $iframe_data['link'] ?? '';
        }
        
        // Use the new dedicated fields if available
        $metabaseSiteUrl = $iframe_data['metabase_site_url'] ?? null;
        $resourceId = $iframe_data['metabase_dashboard_id'] ?? null;
        
        // If metabase_site_url is not set in iframe data, try to get from plugin
        if (!$metabaseSiteUrl) {
            $metabaseSiteUrl = self::getMetabaseSiteUrl();
        }
        
        // Fallback to parsing the link field if needed
        if (!$metabaseSiteUrl || !$resourceId) {
            $url = $iframe_data['link'] ?? '';
            
            // Parse the URL to extract site URL and resource info
            // Example URL: http://10.62.150.135:3000/dashboard/3
            preg_match('#^(https?://[^/]+)/(dashboard|question)/(\d+)(?:-[a-zA-Z0-9-]+)?#i', $url, $matches);
            
            if (empty($matches)) {
                // Invalid format, return original URL
                return $url;
            }
            
            if (!$metabaseSiteUrl) {
                $metabaseSiteUrl = $matches[1];
            }
            $type = $matches[2]; // dashboard or question
            if (!$resourceId) {
                $resourceId = (int)$matches[3];
            }
        } else {
            // Using new fields, default to dashboard
            $type = 'dashboard';
        }
        
        // Generate URL using MetabaseEmbed class
        if ($type === 'dashboard') {
            return MetabaseEmbed::generateDashboardUrl(
                $metabaseSiteUrl,
                $token,
                $resourceId,
                [], // Empty params - Metabase only accepts params configured in the dashboard
                10, // 10 minutes expiration
                true, // bordered
                true  // titled
            );
        } else {
            return MetabaseEmbed::generateQuestionUrl(
                $metabaseSiteUrl,
                $token,
                $resourceId,
                [], // Empty params
                10,
                true,
                true
            );
        }
    }

    /**
     * Get user parameters for Metabase
     * 
     * @return array User parameters
     */
    private static function getUserParams(): array
    {
        if (!isset($_SESSION) || !class_exists('Session')) {
            return [];
        }
        
        $params = [];
        
        // Get current user
        if (class_exists('Session') && method_exists('Session', 'getLoginUserID')) {
            $userId = \Session::getLoginUserID();
            if ($userId) {
                $params['user_id'] = $userId;
                
                // Try to get more user info
                if (class_exists('User')) {
                    $user = new \User();
                    if ($user->getFromDB($userId)) {
                        $params['user_name'] = $user->fields['name'] ?? '';
                        $params['user_realname'] = $user->fields['realname'] ?? '';
                        $params['user_firstname'] = $user->fields['firstname'] ?? '';
                        if (method_exists($user, 'getDefaultEmail')) {
                            $params['user_email'] = $user->getDefaultEmail() ?? '';
                        }
                    }
                }
            }
        }
        
        return $params;
    }

    /**
     * Replace placeholders in URL with user data
     * 
     * @param string $url Original URL
     * @return string URL with replaced placeholders
     */
    private static function replacePlaceholders(string $url): string
    {
        $params = self::getUserParams();
        
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }
}

