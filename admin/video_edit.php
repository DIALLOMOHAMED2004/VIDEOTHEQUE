<?php
// admin/video_edit.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l'ID de la vidéo est passé en GET et est un entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('ID de vidéo non valide.');
}
$video_id = (int) $_GET['id'];

try {
    // 2) Récupérer la vidéo existante
    $sqlVideo = '
        SELECT titre, lien_youtube, theme_id, description 
        FROM videos 
        WHERE id = :id
        LIMIT 1
    ';
    $stmtVideo = $pdo->prepare($sqlVideo);
    $stmtVideo->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmtVideo->execute();
    $video = $stmtVideo->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        die('Vidéo introuvable.');
    }

    // ← Gestion des thèmes : on récupère tous les thèmes, triés par nom
    $stmtThemes = $pdo->prepare('SELECT id, nom FROM themes ORDER BY nom');
    $stmtThemes->execute();
    $themes = $stmtThemes->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Erreur lors de la récupération des données : ' . $e->getMessage());
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la vidéo : <?= sanitize($video['titre']) ?></title>
</head>
<body>
    <h2>Modifier la vidéo</h2>

    <form action="video_update.php" method="post">
        <!-- On transmet l'ID de la vidéo via un champ caché -->
        <input type="hidden" name="id" value="<?= $video_id ?>">

        <!-- 1) Champ Titre prérempli -->
        <label for="title">Titre :</label><br>
        <input 
            type="text" 
            id="title" 
            name="title" 
            value="<?= sanitize($video['titre']) ?>" 
            required
        ><br><br>

        <!-- 2) Champ Lien YouTube prérempli -->
        <label for="link">Lien YouTube :</label><br>
        <input 
            type="text" 
            id="link" 
            name="link" 
            value="<?= sanitize($video['lien_youtube']) ?>" 
            required
        ><br><br>

        <!-- 3) Liste déroulante Thème (sélection du thème courant) -->
        <label for="theme_id">Thème :</label><br>
        <select id="theme_id" name="theme_id" required>
            <option value="">-- Sélectionnez un thème --</option>
            <?php foreach ($themes as $theme): ?>
                <option 
                    value="<?= $theme['id'] ?>" 
                    <?= $theme['id'] == $video['theme_id'] ? 'selected' : '' ?>
                >
                    <?= sanitize($theme['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <!-- 4) Champ Description prérempli (facultatif) -->
        <label for="description">Description (facultatif) :</label><br>
        <textarea 
            id="description" 
            name="description" 
            rows="4" 
            cols="50"
        ><?= sanitize($video['description']) ?></textarea><br><br>

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <p><a class="button" href="video_list.php">← Retour à la liste des vidéos</a></p>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
