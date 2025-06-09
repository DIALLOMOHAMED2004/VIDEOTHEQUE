<?php
// admin/video_update.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que tous les champs obligatoires sont présents
if (
    !isset($_POST['id'], $_POST['title'], $_POST['link'], $_POST['theme_id'])
    || !ctype_digit($_POST['id'])
) {
    die('Données du formulaire incomplètes ou invalides.');
}

$video_id    = (int) $_POST['id'];
$title       = trim($_POST['title']);
$link        = trim($_POST['link']);
$theme_id    = (int) $_POST['theme_id'];
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// 2) Validation / Sanitisation
$errors = [];

// 2.1 ID vidéo valide ?
if ($video_id <= 0) {
    $errors[] = 'ID de vidéo non valide.';
}

// 2.2 Titre non vide
if ($title === '') {
    $errors[] = 'Le titre est obligatoire.';
}

// 2.3 Lien YouTube : vérifier qu'il s'agit d'une URL valide
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    $errors[] = 'Le lien YouTube est invalide ou mal formé.';
}

// 2.4 Thème : vérifier que l'ID existe bien dans la table `themes`
if ($theme_id <= 0) {
    $errors[] = 'Veuillez sélectionner un thème.';
} else {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM themes WHERE id = :id');
        $stmt->bindValue(':id', $theme_id, PDO::PARAM_INT);
        $stmt->execute();
        if ((int)$stmt->fetchColumn() === 0) {
            $errors[] = 'Le thème sélectionné n\'existe pas.';
        }
    } catch (Exception $e) {
        die('Erreur lors de la vérification du thème : ' . $e->getMessage());
    }
}

// 2.5 Vérifier que la vidéo existe avant d’effectuer l’update
try {
    $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM videos WHERE id = :id');
    $stmtCheck->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    if ((int)$stmtCheck->fetchColumn() === 0) {
        $errors[] = 'La vidéo que vous essayez de modifier n\'existe pas.';
    }
} catch (Exception $e) {
    die('Erreur lors de la vérification de la vidéo : ' . $e->getMessage());
}

// Si des erreurs sont présentes, on les affiche et on arrête le script
if (count($errors) > 0) {
    echo '<h3>Impossible de modifier la vidéo pour les raisons suivantes :</h3>';
    echo '<ul style="color: red;">';
    foreach ($errors as $err) {
        echo '<li>' . sanitize($err) . '</li>';
    }
    echo '</ul>';
    echo '<p><a href="video_edit.php?id=' . $video_id . '">← Retour au formulaire d’édition</a></p>';
    exit;
}

// 3) Mise à jour en base
try {
    $sql = '
        UPDATE videos
        SET
            titre       = :titre,
            lien_youtube= :lien,
            theme_id    = :theme_id,
            description = :desc
        WHERE id = :id
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':titre', $title, PDO::PARAM_STR);
    $stmt->bindValue(':lien', $link, PDO::PARAM_STR);
    $stmt->bindValue(':theme_id', $theme_id, PDO::PARAM_INT);
    $stmt->bindValue(':desc', $description, PDO::PARAM_STR);
    $stmt->bindValue(':id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de la mise à jour de la vidéo : ' . $e->getMessage());
}

// 4) Redirection vers la liste avec message de succès
header('Location: video_list.php?msg=update_ok');
exit;
