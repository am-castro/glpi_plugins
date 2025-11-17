<?php

use GlpiPlugin\IframeManager\Iframe;

include(__DIR__ . '/../../../inc/includes.php');

Session::checkRight(Iframe::$rightname, READ);

Html::header(
    Iframe::getTypeName(Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    'tools',
    'GlpiPlugin\IframeManager\IframeMenu',
    'iframe_list'
);

Search::show(Iframe::class);

Html::footer();

//  <style>
//     table {
//         border-collapse: collapse;
//         width: 100%;
//     }

//     th, td {
//         border: 1px solid #ddd;
//         padding: 8px;
//         text-align: left;
//     }

//     th {
//         background-color: brown;
//         color: white;
//     }
// </style>
// <div style="width: 100%; background-color: white; border-radius: 8px; padding: 16px;">
//     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
//         <h2>Lista de Iframes Configurados</h2>
//         <a href="iframe.form.php">Adicionar Novo Iframe</a>
//     </div>
    
//     <table style="width: 100%;">
//         <thead style="background-color: white; color: white; padding: 5px;">
//             <tr>
//                 <th>ID</th>
//                 <th>Ativo</th>
//                 <th>Nome</th>
//                 <th>Descrição</th>
//                 <th>URL</th>
//             </tr>
//         </thead>
//         <tbody>
//             <?php foreach ($iframes as $data): ?
//                 <tr>
//                     <td><a href="iframe.form.php?id=<?php echo $data['id'] ?"><?php echo $data['id'] ?</a></td>
//                     <td><?= $data['is_active'] ? 'Sim' : 'Não' ?</td>
//                     <td><?= htmlspecialchars($data['name']) ?</td>
//                     <td><?= htmlspecialchars($data['description']) ?</td>
//                     <td><?= htmlspecialchars($data['link']) ?</td>
//                 </tr>
//             <?php endforeach; ?
//         </tbody>
//     </table>
// </div> -->
