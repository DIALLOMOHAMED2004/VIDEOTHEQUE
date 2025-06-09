<?php
// admin/video_delete_confirm.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID est présent en GET et qu’il s’agit d’un entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de vidéo non valide.');
}
$video_id = (int) $_GET['id'];

try {
    // 2) Récupérer juste le titre de la vidéo pour l’afficher dans la confirmation
    $stmt = $pdo->prepare('SELECT titre FROM videos WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        die('Vidéo introuvable.');
    }
} catch (Exception $e) {
    die('Erreur lors de la récupération de la vidéo : ' . $e->getMessage());
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmer la suppression</title>
</head>
<body>
    <h2>Confirmation de suppression</h2>

    <p>Voulez-vous vraiment supprimer la vidéo suivante ?</p>
    <p><strong><?= sanitize($video['titre']) ?></strong></p>

    <!-- Formulaire de confirmation en POST vers video_delete.php -->
    <form action="video_delete.php" method="post">
        <input type="hidden" name="id" value="<?= $video_id ?>">
        <button type="submit">Oui, supprimer</button>
        <a href="video_list.php" style="margin-left: 10px;">Non, retourner à la liste</a>
    </form>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
