<?php

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

use GlpiPlugin\IframeManager\Iframe;

// Check permissions
if (!Iframe::canView()) {
    Html::displayRightError();
}

// Get all active iframes
$iframe = new Iframe();
$iframes = $iframe->find(['is_active' => 1], ['name']);

// Get selected iframe data if ID is provided
$selected_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$iframe_data = null;
$url = null;

if ($selected_id) {
    try {
        $iframe_data = Iframe::getIframeById($selected_id);
        
        // Get current user information
        $user = new User();
        $user->getFromDB(Session::getLoginUserID());
        
        // Replace placeholders in the URL
        $url = $iframe_data['link'];
        $url = str_replace('{user_id}', Session::getLoginUserID(), $url);
        $url = str_replace('{user_name}', $user->fields['name'] ?? '', $url);
        $url = str_replace('{user_realname}', $user->fields['realname'] ?? '', $url);
        $url = str_replace('{user_firstname}', $user->fields['firstname'] ?? '', $url);
        $url = str_replace('{user_email}', $user->getDefaultEmail() ?? '', $url);
        $url = str_replace('{user_login}', $user->fields['name'] ?? '', $url);
        
        // Validate URL (only allow http/https)
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], ['http', 'https'])) {
            $url = null;
        }
    } catch (Exception $e) {
        $iframe_data = null;
        $url = null;
    }
}

// Display header without menu for full screen experience
Html::nullHeader('Dashboard', $_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= $iframe_data['name'] ?? 'Selecione um iframe' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            overflow: hidden;
        }
        
        .dashboard-header {
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 60px;
        }
        
        .dashboard-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .dashboard-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        
        .iframe-selector {
            min-width: 250px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        
        .iframe-selector:hover {
            border-color: #4CAF50;
        }
        
        .iframe-selector:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .iframe-container {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
        }
        
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        
        .empty-state {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
            background: white;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state-text {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .empty-state-hint {
            font-size: 14px;
            color: #999;
        }
        
        .fullscreen-btn {
            background: #2196F3;
            color: white;
        }
        
        .fullscreen-btn:hover {
            background: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1 class="dashboard-title">📊 Dashboard</h1>
        
        <div class="dashboard-controls">
            <select class="iframe-selector" id="iframeSelector" onchange="loadIframe(this.value)">
                <option value="">Selecione um iframe...</option>
                <?php foreach ($iframes as $iframe_item): ?>
                    <option value="<?= $iframe_item['id'] ?>" <?= ($selected_id == $iframe_item['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($iframe_item['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($selected_id && $url): ?>
                <button class="btn btn-primary" onclick="reloadIframe()" title="Recarregar">
                    🔄 Recarregar
                </button>
                <button class="btn fullscreen-btn" onclick="toggleFullscreen()" title="Tela Cheia">
                    ⛶ Tela Cheia
                </button>
                <a href="iframe.form.php?id=<?= $selected_id ?>" class="btn btn-secondary" title="Editar">
                    ✏️ Editar
                </a>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-danger" title="Abrir em nova aba">
                    🗗 Nova Aba
                </a>
            <?php endif; ?>
            
            <a href="iframe.php" class="btn btn-secondary" title="Lista de Iframes">
                📋 Lista
            </a>
        </div>
    </div>
    
    <?php if ($selected_id && $url): ?>
        <div class="iframe-container" id="iframeContainer">
            <iframe id="dashboardIframe" src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" allowfullscreen></iframe>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <div class="empty-state-text">Nenhum iframe selecionado</div>
            <div class="empty-state-hint">Selecione um iframe no menu acima para visualizar</div>
        </div>
    <?php endif; ?>
    
    <script>
        function loadIframe(id) {
            if (id) {
                window.location.href = 'iframe.dashboard.php?id=' + id;
            }
        }
        
        function reloadIframe() {
            const iframe = document.getElementById('dashboardIframe');
            if (iframe) {
                iframe.src = iframe.src;
            }
        }
        
        function toggleFullscreen() {
            const container = document.getElementById('iframeContainer');
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    alert('Erro ao entrar em tela cheia: ' + err.message);
                });
            } else {
                document.exitFullscreen();
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // F11 - Toggle fullscreen
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
            // F5 or Ctrl+R - Reload iframe
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                const iframe = document.getElementById('dashboardIframe');
                if (iframe) {
                    e.preventDefault();
                    reloadIframe();
                }
            }
        });
    </script>
</body>
</html>
