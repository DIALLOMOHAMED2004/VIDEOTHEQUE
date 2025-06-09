<?php
// admin/theme_create.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un thème</title>
</head>
<body>
    <h2>Ajouter un nouveau thème</h2>

    <form action="theme_store.php" method="post">
        <!-- Champ Nom du thème -->
        <label for="nom">Nom du thème :</label><br>
        <input type="text" id="nom" name="nom" required><br><br>

        <button type="submit">Ajouter le thème</button>
    </form>

    <p><a class="button" href="theme_list.php">← Retour à la liste des thèmes</a></p>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
