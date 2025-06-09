<?php
// admin/theme_delete.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID est présent en POST et qu’il s’agit d’un entier
if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    die('ID de thème non valide.');
}
$theme_id = (int) $_POST['id'];

try {
    // 2) Vérifier si des vidéos utilisent ce thème
    $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM videos WHERE theme_id = :id');
    $stmtCheck->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    $countVideos = (int) $stmtCheck->fetchColumn();

    if ($countVideos > 0) {
        die('Impossible de supprimer ce thème : des vidéos y sont rattachées.');
    }

    // 3) Supprimer le thème
    $stmt = $pdo->prepare('DELETE FROM themes WHERE id = :id');
    $stmt->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de la suppression du thème : ' . $e->getMessage());
}

// 4) Redirection vers la liste des thèmes avec message de succès
header('Location: theme_list.php?msg=delete_ok');
exit;
