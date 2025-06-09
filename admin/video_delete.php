<?php
// admin/video_delete.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID est présent en POST et qu’il s’agit d’un entier
if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    die('ID de vidéo non valide.');
}
$video_id = (int) $_POST['id'];

try {
    // 2) Effectuer la suppression
    $stmt = $pdo->prepare('DELETE FROM videos WHERE id = :id');
    $stmt->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de la suppression de la vidéo : ' . $e->getMessage());
}

// 3) Redirection vers la liste avec message de succès
header('Location: video_list.php?msg=delete_ok');
exit;
