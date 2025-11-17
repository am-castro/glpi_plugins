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

/**
 * Metabase Embed Helper Class
 * Generates signed URLs for embedding Metabase dashboards and questions
 */
class MetabaseEmbed
{
    /**
     * Generate a signed URL for embedding a Metabase dashboard
     *
     * @param string $metabaseSiteUrl   The base URL of your Metabase instance (e.g., "http://10.62.150.135:3000")
     * @param string $metabaseSecretKey The secret key from Metabase settings (Settings > Admin > Embedding)
     * @param int    $dashboardId       The ID of the dashboard to embed
     * @param array  $params            Optional parameters to pass to the dashboard (e.g., ['user_id' => 123])
     * @param int    $expirationMinutes How many minutes until the token expires (default: 10)
     * @param bool   $bordered          Show border around the iframe (default: true)
     * @param bool   $titled            Show title in the iframe (default: true)
     *
     * @return string The signed iframe URL
     */
    public static function generateDashboardUrl(
        string $metabaseSiteUrl,
        string $metabaseSecretKey,
        int $dashboardId,
        array $params = [],
        int $expirationMinutes = 10,
        bool $bordered = true,
        bool $titled = true
    ): string {
        // Create payload
        $payload = [
            'resource' => ['dashboard' => $dashboardId],
            'params' => empty($params) ? (object)[] : $params, // Force empty object instead of empty array
            'exp' => time() + ($expirationMinutes * 60) // Expiration time
        ];

        // Generate JWT token
        $token = self::generateJWT($payload, $metabaseSecretKey);

        // Build iframe URL
        $iframeUrl = rtrim($metabaseSiteUrl, '/') . '/embed/dashboard/' . $token;
        
        // Add query parameters
        $queryParams = [];
        if ($bordered) {
            $queryParams[] = 'bordered=true';
        }
        if ($titled) {
            $queryParams[] = 'titled=true';
        }
        
        if (!empty($queryParams)) {
            $iframeUrl .= '#' . implode('&', $queryParams);
        }

        return $iframeUrl;
    }

    /**
     * Generate a signed URL for embedding a Metabase question
     *
     * @param string $metabaseSiteUrl   The base URL of your Metabase instance
     * @param string $metabaseSecretKey The secret key from Metabase settings
     * @param int    $questionId        The ID of the question to embed
     * @param array  $params            Optional parameters to pass to the question
     * @param int    $expirationMinutes How many minutes until the token expires (default: 10)
     * @param bool   $bordered          Show border around the iframe (default: true)
     * @param bool   $titled            Show title in the iframe (default: true)
     *
     * @return string The signed iframe URL
     */
    public static function generateQuestionUrl(
        string $metabaseSiteUrl,
        string $metabaseSecretKey,
        int $questionId,
        array $params = [],
        int $expirationMinutes = 10,
        bool $bordered = true,
        bool $titled = true
    ): string {
        // Create payload
        $payload = [
            'resource' => ['question' => $questionId],
            'params' => empty($params) ? (object)[] : $params, // Force empty object instead of empty array
            'exp' => time() + ($expirationMinutes * 60)
        ];

        // Generate JWT token
        $token = self::generateJWT($payload, $metabaseSecretKey);

        // Build iframe URL
        $iframeUrl = rtrim($metabaseSiteUrl, '/') . '/embed/question/' . $token;
        
        // Add query parameters
        $queryParams = [];
        if ($bordered) {
            $queryParams[] = 'bordered=true';
        }
        if ($titled) {
            $queryParams[] = 'titled=true';
        }
        
        if (!empty($queryParams)) {
            $iframeUrl .= '#' . implode('&', $queryParams);
        }

        return $iframeUrl;
    }

    /**
     * Generate JWT token using HS256 algorithm (HMAC with SHA-256)
     * 
     * This is a simple JWT implementation that doesn't require external libraries.
     * For production use with more complex requirements, consider using a library like firebase/php-jwt
     *
     * @param array  $payload The payload to encode
     * @param string $secret  The secret key for signing
     *
     * @return string The JWT token
     */
    private static function generateJWT(array $payload, string $secret): string
    {
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Encode Header
        $headerEncoded = self::base64UrlEncode(json_encode($header));

        // Encode Payload
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Create Signature
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        // Create JWT
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL encode
     * Standard base64 encoding with URL-safe characters
     *
     * @param string $data The data to encode
     *
     * @return string The encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Example usage method - demonstrates how to use the class
     *
     * @return string Example iframe URL
     */
    public static function example(): string
    {
        $metabaseSiteUrl = "http://10.62.150.135:3000";
        $metabaseSecretKey = "5a5b4bc416dd55466a97d01fadc8c3d63e5cc873195c19f3680174f5a5657f15";
        $dashboardId = 3;
        
        return self::generateDashboardUrl(
            $metabaseSiteUrl,
            $metabaseSecretKey,
            $dashboardId,
            [],      // No parameters
            10,      // 10 minute expiration
            true,    // Show border
            true     // Show title
        );
    }
}
