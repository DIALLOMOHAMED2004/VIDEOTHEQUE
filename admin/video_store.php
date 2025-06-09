<?php
// admin/video_store.php

// 1) Protection de la page
require_once __DIR__ . '/../includes/functions.php';
checkAdminSession();

// 2) Connexion à la base de données
require_once __DIR__ . '/../config/database.php';

// 3) Récupérer et nettoyer les données postées
$title       = isset($_POST['title']) ? trim($_POST['title']) : '';
$link        = isset($_POST['link']) ? trim($_POST['link']) : '';
$theme_id    = isset($_POST['theme_id']) ? (int) $_POST['theme_id'] : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// 4) Validation / Sanitisation
$errors = [];

// 4.1 Titre non vide
if ($title === '') {
    $errors[] = 'Le titre est obligatoire.';
}

// 4.2 Lien YouTube : vérifier qu'il s'agit d'une URL valide
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    $errors[] = 'Le lien YouTube est invalide ou mal formé.';
}

// 4.3 Thème : vérifier que l'ID existe bien dans la table `themes`
if ($theme_id <= 0) {
    $errors[] = 'Veuillez sélectionner un thème.';
} else {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM themes WHERE id = :id');
        $stmt->bindValue(':id', $theme_id, PDO::PARAM_INT);
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();
        if ($count === 0) {
            $errors[] = 'Le thème sélectionné n\'existe pas.';
        }
    } catch (Exception $e) {
        die('Erreur lors de la vérification du thème : ' . $e->getMessage());
    }
}

// Si des erreurs de validation sont présentes, on les affiche et on arrête le script
if (count($errors) > 0) {
    echo '<h3>Impossible d’ajouter la vidéo pour les raisons suivantes :</h3>';
    echo '<ul style="color: red;">';
    foreach ($errors as $err) {
        echo '<li>' . sanitize($err) . '</li>';
    }
    echo '</ul>';
    echo '<p><a href="video_create.php">← Retour au formulaire d’ajout</a></p>';
    exit;
}

// 5) Insertion en base
try {
    $sql = 'INSERT INTO videos (titre, lien_youtube, theme_id, description, date_ajout)
            VALUES (:titre, :lien, :theme_id, :desc, NOW())';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':titre', $title, PDO::PARAM_STR);
    $stmt->bindValue(':lien', $link, PDO::PARAM_STR);
    $stmt->bindValue(':theme_id', $theme_id, PDO::PARAM_INT);
    $stmt->bindValue(':desc', $description, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de l’insertion de la vidéo : ' . $e->getMessage());
}

// 6) Redirection vers la liste des vidéos avec message de confirmation
header('Location: video_list.php?msg=ajout_ok');
exit;
