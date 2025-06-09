<?php
// admin/theme_store.php

require_once __DIR__ . '/../includes/functions.php';
checkAdminSession(); // Protection : redirige vers login si non connecté

require_once __DIR__ . '/../config/database.php';

// Vérifier que le champ 'nom' est présent et non vide
if (!isset($_POST['nom']) || trim($_POST['nom']) === '') {
    die('Le nom du thème est obligatoire.');
}

$nom = trim($_POST['nom']);

// Optionnel : vérifier qu’un thème portant ce nom n’existe pas déjà
try {
    $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM themes WHERE nom = :nom');
    $stmtCheck->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmtCheck->execute();
    if ((int)$stmtCheck->fetchColumn() > 0) {
        die('Un thème avec ce nom existe déjà.');
    }
} catch (Exception $e) {
    die('Erreur lors de la vérification du thème existant : ' . $e->getMessage());
}

// Insertion du nouveau thème
try {
    $stmt = $pdo->prepare('INSERT INTO themes (nom) VALUES (:nom)');
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmt->execute();
} catch (Exception $e) {
    die('Erreur lors de l’ajout du thème : ' . $e->getMessage());
}

// Redirection vers la liste des thèmes avec message de succès
header('Location: theme_list.php?msg=ajout_ok');
exit;
