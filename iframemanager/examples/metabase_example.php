<?php

/**
 * -------------------------------------------------------------------------
 * EXEMPLO DE USO: Metabase Embed Helper
 * -------------------------------------------------------------------------
 * 
 * Este arquivo mostra como usar a classe MetabaseEmbed para gerar
 * URLs assinadas para embedar dashboards e perguntas do Metabase
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\IframeManager\MetabaseEmbed;

// ============================================================================
// CONFIGURAÇÃO DO METABASE
// ============================================================================

$METABASE_SITE_URL = "http://10.62.150.135:3000";
$METABASE_SECRET_KEY = "5a5b4bc416dd55466a97d01fadc8c3d63e5cc873195c19f3680174f5a5657f15";

// ============================================================================
// EXEMPLO 1: Embedar um Dashboard simples (equivalente ao código Node.js)
// ============================================================================

$dashboardId = 3;
$iframeUrl = MetabaseEmbed::generateDashboardUrl(
    $METABASE_SITE_URL,
    $METABASE_SECRET_KEY,
    $dashboardId,
    [],      // Sem parâmetros
    10,      // Expira em 10 minutos
    true,    // Com borda
    true     // Com título
);

echo "URL do Dashboard:\n";
echo $iframeUrl . "\n\n";

// ============================================================================
// EXEMPLO 2: Dashboard com parâmetros dinâmicos
// ============================================================================

// Você pode passar parâmetros para filtrar o dashboard
$params = [
    'user_id' => 123,
    'date_from' => '2024-01-01',
    'date_to' => '2024-12-31'
];

$iframeUrlWithParams = MetabaseEmbed::generateDashboardUrl(
    $METABASE_SITE_URL,
    $METABASE_SECRET_KEY,
    $dashboardId,
    $params, // Parâmetros dinâmicos
    10,
    true,
    true
);

echo "URL do Dashboard com parâmetros:\n";
echo $iframeUrlWithParams . "\n\n";

// ============================================================================
// EXEMPLO 3: Embedar uma Question (Pergunta/Gráfico)
// ============================================================================

$questionId = 5;
$questionUrl = MetabaseEmbed::generateQuestionUrl(
    $METABASE_SITE_URL,
    $METABASE_SECRET_KEY,
    $questionId,
    [],
    10,
    true,
    true
);

echo "URL da Question:\n";
echo $questionUrl . "\n\n";

// ============================================================================
// EXEMPLO 4: Usar no GLPI - Substituir placeholders do usuário
// ============================================================================

// No contexto do GLPI, você pode usar informações do usuário logado
// Para isso, você precisaria modificar o método Iframe::getIframeById()

/*
// Exemplo de como ficaria no iframe.viewer.php ou iframe.charts.php:

use GlpiPlugin\IframeManager\Iframe;
use GlpiPlugin\IframeManager\MetabaseEmbed;

$iframe_data = Iframe::getIframeById($selected_id);

// Se o iframe é do tipo Metabase
if ($iframe_data['is_metabase']) {
    // Configurações do Metabase (podem vir do banco ou config)
    $metabaseUrl = "http://10.62.150.135:3000";
    $metabaseKey = "sua_chave_secreta";
    
    // ID do dashboard vem do campo 'link' ou de um campo específico
    $dashboardId = (int)$iframe_data['metabase_dashboard_id'];
    
    // Parâmetros com dados do usuário logado
    $params = [
        'user_id' => Session::getLoginUserID(),
        'user_name' => $_SESSION['glpiname'],
    ];
    
    // Gera URL assinada
    $url = MetabaseEmbed::generateDashboardUrl(
        $metabaseUrl,
        $metabaseKey,
        $dashboardId,
        $params,
        10
    );
} else {
    // URL normal do iframe
    $url = $iframe_data['link'];
}
*/

// ============================================================================
// EXEMPLO 5: HTML para exibir o iframe
// ============================================================================

echo "HTML para embedar:\n";
echo '<iframe src="' . htmlspecialchars($iframeUrl) . '" 
    width="100%" 
    height="600" 
    frameborder="0" 
    allowtransparency>
</iframe>' . "\n\n";

// ============================================================================
// NOTAS IMPORTANTES
// ============================================================================

/*

1. ONDE ENCONTRAR A SECRET KEY DO METABASE:
   - Acesse: Settings > Admin > Embedding
   - Habilite "Enable embedding"
   - Copie a "Embedding secret key"

2. COMO HABILITAR UM DASHBOARD PARA EMBEDDING:
   - Abra o dashboard no Metabase
   - Clique no ícone de compartilhamento
   - Clique em "Embedding"
   - Habilite "Enable sharing"
   - Configure quais parâmetros serão aceitos

3. PARÂMETROS DINÂMICOS:
   - No Metabase, você define quais parâmetros o dashboard aceita
   - Depois passa esses parâmetros no array $params
   - Exemplo: se o dashboard tem um filtro "user_id", você passa ['user_id' => 123]

4. EXPIRAÇÃO DO TOKEN:
   - Por padrão, o token expira em 10 minutos
   - Ajuste conforme necessário (max recomendado: 60 minutos)
   - Tokens expirados não funcionam (segurança)

5. INTEGRAÇÃO COM GLPI:
   - Você pode criar um campo adicional na tabela de iframes para indicar
     se é um iframe Metabase
   - Armazenar dashboard_id, question_id, secret_key
   - Gerar URL dinamicamente ao exibir

*/
