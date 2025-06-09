<?php
// admin/video_create.php

// 1) Protection de la page : on s'assure que l'admin est connecté
require_once __DIR__ . '/../includes/functions.php';
checkAdminSession();

// 2) Connexion à la base pour récupérer la liste des thèmes
require_once __DIR__ . '/../config/database.php';

try {
    // ← Gestion des thèmes : on récupère tous les thèmes, triés par nom
    $stmt = $pdo->prepare('SELECT id, nom FROM themes ORDER BY nom');
    $stmt->execute();
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la requête échoue, on arrête et affiche l'erreur
    die('Erreur lors de la récupération des thèmes : ' . $e->getMessage());
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une vidéo</title>
</head>
<body>
    <h2>Ajouter une nouvelle vidéo</h2>

    <!-- Formulaire qui enverra en POST vers video_store.php -->
    <form action="video_store.php" method="post">
        <!-- 1) Champ Titre -->
        <label for="title">Titre :</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <!-- 2) Champ Lien YouTube -->
        <label for="link">Lien YouTube :</label><br>
        <input type="text" id="link" name="link" placeholder="https://www.youtube.com/watch?v=XXXXX" required><br><br>

        <!-- 3) Liste déroulante Thème (récupérée dynamiquement) -->
        <label for="theme_id">Thème :</label><br>
        <select id="theme_id" name="theme_id" required>
            <option value="">-- Sélectionnez un thème --</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?= $theme['id'] ?>">
                    <?= sanitize($theme['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <!-- 4) Champ Description (facultatif) -->
        <label for="description">Description (facultatif) :</label><br>
        <textarea id="description" name="description" rows="4" cols="50"></textarea><br><br>

        <button type="submit">Ajouter la vidéo</button>
    </form>

    <p><a class="button" href="video_list.php">← Retour à la liste des vidéos</a></p>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
