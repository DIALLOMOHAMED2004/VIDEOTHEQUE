<?php
// admin/theme_update.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// 1) Vérifier que l’ID et le champ 'nom' sont présents
if (
    !isset($_POST['id'], $_POST['nom']) ||
    !ctype_digit($_POST['id']) ||
    trim($_POST['nom']) === ''
) {
    die('Données du formulaire incomplètes ou invalides.');
}

$theme_id = (int) $_POST['id'];
$nom = trim($_POST['nom']);

// 2) Vérifier que le thème existe
try {
    $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM themes WHERE id = :id');
    $stmtCheck->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    if ((int)$stmtCheck->fetchColumn() === 0) {
        die('Le thème que vous essayez de modifier n\'existe pas.');
    }
} catch (Exception $e) {
    die('Erreur lors de la vérification du thème : ' . $e->getMessage());
}

// 3) Vérifier qu’aucun autre thème n’a déjà le même nom (optionnel)
try {
    $stmtUnique = $pdo->prepare('SELECT COUNT(*) FROM themes WHERE nom = :nom AND id <> :id');
    $stmtUnique->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmtUnique->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmtUnique->execute();
    if ((int)$stmtUnique->fetchColumn() > 0) {
        die('Un autre thème portant ce nom existe déjà.');
    }
} catch (Exception $e) {
    die('Erreur lors de la vérification du nom de thème : ' . $e->getMessage());
}

// 4) Mise à jour en base
try {
    $stmt = $pdo->prepare('UPDATE themes SET nom = :nom WHERE id = :id');
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindValue(':id', $theme_id, PDO::PARAM_INT);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de la mise à jour du thème : ' . $e->getMessage());
}

// 5) Redirection vers la liste des thèmes avec message de succès
header('Location: theme_list.php?msg=update_ok');
exit;
 