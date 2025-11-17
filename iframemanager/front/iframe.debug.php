<?php

/**
 * Debug page to check Metabase integration
 */

use GlpiPlugin\IframeManager\Iframe;

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

// Get iframe ID from URL
$iframe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

Html::header('Metabase Debug', $_SERVER['PHP_SELF'], 'tools', 'pluginiframemanagerdebug');

echo '<div class="container-fluid">';
echo '<h2>🔍 Metabase Integration Debug</h2>';

if ($iframe_id == 0) {
    echo '<div class="alert alert-warning">Por favor, forneça um ID de iframe na URL: ?id=X</div>';
    
    // List all iframes
    $iframe_obj = new Iframe();
    $iframes = $iframe_obj->find([], ['ORDER' => 'name']);
    
    if (count($iframes) > 0) {
        echo '<h3>Iframes Disponíveis:</h3>';
        echo '<ul>';
        foreach ($iframes as $iframe) {
            echo '<li>';
            echo '<a href="?id=' . $iframe['id'] . '">';
            echo htmlspecialchars($iframe['name']) . ' (ID: ' . $iframe['id'] . ')';
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
} else {
    // Debug specific iframe
    echo '<div class="card mb-3">';
    echo '<div class="card-header"><h3>Iframe ID: ' . $iframe_id . '</h3></div>';
    echo '<div class="card-body">';
    
    try {
        // Get iframe data
        $iframe_data = Iframe::getIframeById($iframe_id);
        
        echo '<h4>📋 Dados do Iframe:</h4>';
        echo '<pre>';
        print_r($iframe_data);
        echo '</pre>';
        
        echo '<hr>';
        
        // Check if is_metabase field exists
        echo '<h4>🎯 Verificações:</h4>';
        echo '<ul>';
        echo '<li><strong>Campo is_metabase existe?</strong> ';
        echo isset($iframe_data['is_metabase']) ? '✅ Sim' : '❌ Não';
        echo '</li>';
        
        echo '<li><strong>Valor de is_metabase:</strong> ';
        echo isset($iframe_data['is_metabase']) ? $iframe_data['is_metabase'] : 'N/A';
        echo '</li>';
        
        echo '<li><strong>É iframe Metabase?</strong> ';
        echo !empty($iframe_data['is_metabase']) ? '✅ Sim' : '❌ Não';
        echo '</li>';
        
        echo '<li><strong>URL Original:</strong> ';
        echo htmlspecialchars($iframe_data['link']);
        echo '</li>';
        echo '</ul>';
        
        echo '<hr>';
        
        // Check Metabase token
        echo '<h4>🔑 Token Metabase:</h4>';
        
        global $DB;
        
        // Check if table exists
        echo '<ul>';
        echo '<li><strong>Tabela glpi_plugin_metabase_configs existe?</strong> ';
        if ($DB->tableExists('glpi_plugin_metabase_configs')) {
            echo '✅ Sim';
            
            $query = "SELECT * FROM `glpi_plugin_metabase_configs` LIMIT 1";
            $result = $DB->query($query);
            
            if ($result && $row = $DB->fetchAssoc($result)) {
                echo '</li>';
                echo '<li><strong>Configuração encontrada:</strong>';
                echo '<pre>';
                print_r($row);
                echo '</pre>';
                echo '</li>';
                
                echo '<li><strong>Secret Key:</strong> ';
                if (isset($row['secret_key']) && !empty($row['secret_key'])) {
                    echo '✅ Configurada (';
                    echo strlen($row['secret_key']) . ' caracteres)';
                } else {
                    echo '❌ Não configurada';
                }
                echo '</li>';
            } else {
                echo '</li>';
                echo '<li>⚠️ Tabela existe mas não há configuração</li>';
            }
        } else {
            echo '❌ Não (Plugin Metabase não instalado)';
        }
        echo '</ul>';
        
        echo '<hr>';
        
        // Process URL
        echo '<h4>🔄 URL Processada:</h4>';
        
        $processed_url = Iframe::getProcessedUrl($iframe_id);
        
        echo '<ul>';
        echo '<li><strong>URL Gerada:</strong><br>';
        echo '<code>' . htmlspecialchars($processed_url) . '</code>';
        echo '</li>';
        
        echo '<li><strong>Tipo de URL:</strong> ';
        if (strpos($processed_url, '/embed/') !== false) {
            echo '✅ URL assinada (Metabase)';
        } else {
            echo '⚠️ URL original (não assinada)';
        }
        echo '</li>';
        
        // Test regex
        echo '<li><strong>Teste de Regex:</strong> ';
        if (preg_match('#^(https?://[^/]+)/(dashboard|question)/(\d+)(?:-[a-zA-Z0-9-]+)?#i', $iframe_data['link'], $matches)) {
            echo '✅ URL corresponde ao padrão Metabase';
            echo '<ul>';
            echo '<li>Site URL: ' . htmlspecialchars($matches[1]) . '</li>';
            echo '<li>Tipo: ' . htmlspecialchars($matches[2]) . '</li>';
            echo '<li>Resource ID: ' . htmlspecialchars($matches[3]) . '</li>';
            echo '</ul>';
        } else {
            echo '❌ URL não corresponde ao padrão esperado';
        }
        echo '</li>';
        echo '</ul>';
        
        echo '<hr>';
        
        // Preview iframe
        echo '<h4>🖼️ Preview do Iframe:</h4>';
        echo '<iframe src="' . htmlspecialchars($processed_url, ENT_QUOTES, 'UTF-8') . '" ';
        echo 'style="width: 100%; height: 600px; border: 1px solid #ccc;" ';
        echo 'frameborder="0">';
        echo '</iframe>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">';
        echo '<strong>Erro:</strong> ' . htmlspecialchars($e->getMessage());
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

echo '</div>';

Html::footer();
