<?php

include(__DIR__ . '/../../../inc/includes.php');

Session::checkLoginUser();

use GlpiPlugin\IframeManager\Iframe;

$iframe = new Iframe();

// Check permissions
if (!Iframe::canView()) {
    Html::displayRightError();
}

// Process POST actions
if (isset($_POST['add'])) {
    $iframe->check(-1, CREATE, $_POST);
    
    if ($id = $iframe->add($_POST)) {
        Session::addMessageAfterRedirect(__('Iframe created successfully', 'iframemanager'));
        Html::redirect('iframe.php');
    } else {
        Session::addMessageAfterRedirect(__('Error creating iframe', 'iframemanager'), false, ERROR);
    }
} else if (isset($_POST['update'])) {
    $iframe->check($_POST['id'], UPDATE);
    
    if ($iframe->update($_POST)) {
        Session::addMessageAfterRedirect(__('Item successfully updated'));
        Html::redirect('iframe.form.php?id=' . $_POST['id']);
    } else {
        Session::addMessageAfterRedirect(__('Error updating item'), false, ERROR);
        Html::redirect('iframe.form.php?id=' . $_POST['id']);
    }
} else if (isset($_POST['delete'])) {
    $iframe->check($_POST['id'], DELETE);
    
    if ($iframe->delete($_POST)) {
        Session::addMessageAfterRedirect(__('Iframe deleted successfully', 'iframemanager'));
        Html::redirect('iframe.php');
    } else {
        Session::addMessageAfterRedirect(__('Error deleting iframe', 'iframemanager'), false, ERROR);
        Html::back();
    }
}

// Display header
if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::header(
        Iframe::getTypeName(1),
        $_SERVER['PHP_SELF'],
        'tools',
        'GlpiPlugin\IframeManager\IframeMenu',
        'iframe_list'
    );
} else {
    Html::helpHeader(
        Iframe::getTypeName(1),
        $_SERVER['PHP_SELF']
    );
}

// Get ID from GET or use -1 for new item
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Load item if editing
if ($id > 0) {
    $iframe->getFromDB($id);
}

// Display form
$iframe->display(['id' => $id]);

Html::footer();



// <!-- <div style="width: 100%; background-color: white; border-radius: 8px; padding: 16px;">
//     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
//         <h2>Formulário de Iframe</h2>
//         <a href="iframe.list.php">Voltar para a Lista de Iframes</a>
//     </div>
//     <form style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 20px;" action="iframe.save.php" method="POST">
//         <input type="hidden" name="id" value="<?= isset($_GET['id']) ? intval($_GET['id']) : '' ?">

//         <div style="width: 100%;">
//             <label for="name">Nome:</label><br>
//             <input type="text" id="name" name="name" required style="width: 100%;" value="<?= isset($iframeFormData['name']) ? htmlspecialchars($iframeFormData['name']) : '' ?"><br><br>
//         </div>

//         <div style="width: 100%;">
//             <label for="description">Descrição:</label><br>
//             <textarea id="description" name="description" rows="4" style="width: 100%;"><?= isset($iframeFormData['description']) ? htmlspecialchars($iframeFormData['description']) : '' ?</textarea><br><br>
//         </div>

//         <div style="width: 100%;">
//             <label for="link">URL do Iframe:</label><br>
//             <input type="url" id="link" name="link" required style="width: 100%;" value="<?= isset($iframeFormData['link']) ? htmlspecialchars($iframeFormData['link']) : '' ?"><br><br>
//         </div>

//         <div style="width: 100%;">
//             <label for="is_active">Ativo:</label>
//             <input type="checkbox" id="is_active" name="is_active" value="1" <?= isset($iframeFormData['is_active']) && $iframeFormData['is_active'] ? 'checked' : '' ?<br><br>
//         </div>

//         <input type="submit" value="Salvar Iframe">
//     </form>
// </div> -->