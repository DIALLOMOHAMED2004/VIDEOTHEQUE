<?php
// admin/theme_delete_confirm.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID du thème est passé en GET et qu’il s’agit d’un entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de thème non valide.');
}
$theme_id = (int) $_GET['id'];

try {
    // 2) Récupérer le nom du thème pour l’afficher dans la confirmation
    $stmt = $pdo->prepare('SELECT nom FROM themes WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmt->execute();
    $theme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$theme) {
        die('Thème introuvable.');
    }
} catch (Exception $e) {
    die('Erreur lors de la récupération du thème : ' . $e->getMessage());
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmer la suppression du thème</title>
</head>
<body>
    <h2>Confirmation de suppression</h2>

    <p>Voulez-vous vraiment supprimer le thème suivant ?</p>
    <p><strong><?= sanitize($theme['nom']) ?></strong></p>

    <!-- Formulaire de confirmation en POST vers theme_delete.php -->
    <form action="theme_delete.php" method="post">
        <input type="hidden" name="id" value="<?= $theme_id ?>">
        <button type="submit">Oui, supprimer</button>
        <a href="theme_list.php" style="margin-left: 10px;">Non, retourner à la liste</a>
    </form>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
