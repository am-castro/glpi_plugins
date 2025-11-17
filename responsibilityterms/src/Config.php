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

/**
 * Config class
 * Manages plugin configuration for digital signature integration
 */
class Config extends CommonDBTM
{
    public static function getTable($classname = null)
    {
        return 'glpi_plugin_responsibilityterms_configs';
    }

    public static function getTypeName($nb = 0)
    {
        return __('Signature Configuration', 'responsibilityterms');
    }

    public static function getIcon()
    {
        return 'ti ti-settings';
    }

    /**
     * Show configuration form
     */
    public static function showConfigForm()
    {
        global $CFG_GLPI;

        $config = self::getConfig();

        echo "<div class='center'>";
        echo "<form name='config_form' method='post' action='" . 
             $CFG_GLPI['root_doc'] . "/plugins/responsibilityterms/front/config.form.php'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='4'>" . __('Digital Signature API Configuration', 'responsibilityterms') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('API URL', 'responsibilityterms') . "</td>";
        echo "<td colspan='3'>";
        echo Html::input('signature_url', [
            'value' => $config['signature_url'] ?? '',
            'size' => 80
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('HTTP Method', 'responsibilityterms') . "</td>";
        echo "<td>";
        \Dropdown::showFromArray('signature_method', [
            'POST' => 'POST',
            'PUT'  => 'PUT'
        ], [
            'value' => $config['signature_method'] ?? 'POST'
        ]);
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Authentication Type', 'responsibilityterms') . "</td>";
        echo "<td colspan='3'>";
        echo "<input type='radio' name='auth_type' value='basic' id='auth_basic' " . 
             (($config['auth_type'] ?? 'bearer') == 'basic' ? 'checked' : '') . 
             " onclick='toggleAuthFields()'>";
        echo "<label for='auth_basic'> " . __('Basic Auth', 'responsibilityterms') . "</label> &nbsp;&nbsp;";
        echo "<input type='radio' name='auth_type' value='bearer' id='auth_bearer' " . 
             (($config['auth_type'] ?? 'bearer') == 'bearer' ? 'checked' : '') . 
             " onclick='toggleAuthFields()'>";
        echo "<label for='auth_bearer'> " . __('Bearer Token', 'responsibilityterms') . "</label>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' id='basic_auth_fields'>";
        echo "<td>" . __('Username', 'responsibilityterms') . "</td>";
        echo "<td>";
        echo Html::input('auth_user', [
            'value' => $config['auth_user'] ?? '',
            'size' => 40
        ]);
        echo "</td>";
        echo "<td>" . __('Password', 'responsibilityterms') . "</td>";
        echo "<td>";
        echo Html::input('auth_password', [
            'value' => $config['auth_password'] ?? '',
            'type' => 'password',
            'size' => 40
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1' id='bearer_auth_fields'>";
        echo "<td>" . __('Bearer Token', 'responsibilityterms') . "</td>";
        echo "<td colspan='3'>";
        echo "<textarea name='auth_token' rows='3' style='width:98%;'>";
        echo $config['auth_token'] ?? '';
        echo "</textarea>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4' class='center'>";
        echo Html::hidden('id', ['value' => $config['id'] ?? 1]);
        echo Html::submit(__('Save'), ['name' => 'update']);
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";

        // JavaScript to toggle auth fields
        echo "<script>
        function toggleAuthFields() {
            var authType = document.querySelector('input[name=\"auth_type\"]:checked').value;
            var basicFields = document.getElementById('basic_auth_fields');
            var bearerFields = document.getElementById('bearer_auth_fields');
            
            if (authType === 'basic') {
                basicFields.style.display = 'table-row';
                bearerFields.style.display = 'none';
            } else {
                basicFields.style.display = 'none';
                bearerFields.style.display = 'table-row';
            }
        }
        
        // Initialize on page load
        toggleAuthFields();
        </script>";
    }

    /**
     * Get current configuration
     *
     * @return array
     */
    public static function getConfig()
    {
        global $DB;

        $config = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => ['id' => 1],
            'LIMIT' => 1
        ])->current();

        return $config ?: [];
    }

    /**
     * Check if signature configuration exists and is valid
     *
     * @return bool
     */
    public static function hasSignatureConfig()
    {
        $config = self::getConfig();
        return !empty($config['signature_url']);
    }

    /**
     * Update configuration
     *
     * @param array $input
     * @return bool
     */
    public static function updateConfig($input)
    {
        global $DB;

        $input['date_mod'] = $_SESSION['glpi_currenttime'];
        
        if (isset($input['id'])) {
            return $DB->update(self::getTable(), $input, ['id' => $input['id']]);
        }
        
        return false;
    }

    /**
     * Send PDF to signature API
     *
     * @param int $term_id
     * @return array Response with status and message
     */
    public static function sendToSignature($term_id)
    {
        $term = new Term();
        if (!$term->getFromDB($term_id)) {
            return ['success' => false, 'message' => __('Term not found', 'responsibilityterms')];
        }

        $config = self::getConfig();
        if (empty($config['signature_url'])) {
            return ['success' => false, 'message' => __('Signature URL not configured', 'responsibilityterms')];
        }

        // Prepare request
        $pdf_base64 = base64_encode($term->fields['pdf_content']);
        
        $payload = [
            'document' => $pdf_base64,
            'user_id' => $term->fields['users_id'],
            'term_id' => $term_id,
            'filename' => $term->fields['pdf_filename']
        ];

        // Setup authentication headers
        $headers = ['Content-Type: application/json'];
        
        if ($config['auth_type'] == 'basic') {
            $auth = base64_encode($config['auth_user'] . ':' . $config['auth_password']);
            $headers[] = 'Authorization: Basic ' . $auth;
        } else {
            $headers[] = 'Authorization: Bearer ' . $config['auth_token'];
        }

        // Send request
        $ch = curl_init($config['signature_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            // Update term status
            global $DB;
            $DB->update(Term::getTable(), [
                'signature_status' => 'sent',
                'signature_url' => $config['signature_url']
            ], ['id' => $term_id]);

            return ['success' => true, 'message' => __('Term sent to signature successfully', 'responsibilityterms')];
        } else {
            return [
                'success' => false, 
                'message' => __('Failed to send term to signature', 'responsibilityterms') . ': ' . ($error ?: "HTTP $http_code")
            ];
        }
    }
}
