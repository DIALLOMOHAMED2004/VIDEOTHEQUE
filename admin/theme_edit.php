<?php
// admin/theme_edit.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID du thème est présent en GET et qu’il s’agit d’un entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de thème non valide.');
}
$theme_id = (int) $_GET['id'];

try {
    // 2) Récupérer les données du thème existant
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
    <title>Modifier le thème : <?= sanitize($theme['nom']) ?></title>
</head>
<body>
    <h2>Modifier le thème</h2>

    <form action="theme_update.php" method="post">
        <!-- On transmet l'ID du thème via un champ caché -->
        <input type="hidden" name="id" value="<?= $theme_id ?>">

        <!-- Champ Nom du thème prérempli -->
        <label for="nom">Nom du thème :</label><br>
        <input 
            type="text" 
            id="nom" 
            name="nom" 
            value="<?= sanitize($theme['nom']) ?>" 
            required
        ><br><br>

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <p><a class="button" href="theme_list.php">← Retour à la liste des thèmes</a></p>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
