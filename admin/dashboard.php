<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Vérifie que l'admin est connecté

// À partir d'ici, on sait que $_SESSION['admin_id'] est défini
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Admin</title>
</head>
<body>
    <h1>Bienvenue, <?php echo sanitize($_SESSION['admin_username']); ?> !</h1>

    <ul>
        <li><a class="button" href="video_list.php">Gérer les vidéos</a></li>
        <!-- Ajoutez d'autres liens (gestion des thèmes, etc.) si besoin -->
         <br>
        <li><a class="button" href="../public/logout.php">Se déconnecter</a></li>
    </ul>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
