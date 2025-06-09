<?php
// includes/functions.php

/**
 * Démarre la session et vérifie si l'administrateur est connecté.
 * Si non, redirige vers la page de login.
 */
function checkAdminSession() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        // Si l'administrateur n'est pas connecté, on envoie vers login.php
        header('Location: ../public/login.php');
        exit;
    }
}

/**
 * Nettoie une chaîne pour éviter les failles XSS.
 * Exemple d'utilisation : echo sanitize($_POST['username']);
 */
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Récupère tous les thèmes depuis la base de données.
 * Retourne un tableau associatif [ ['id' => ..., 'nom' => ...], … ]
 * En cas d'erreur, lance une exception.
 *
 * @param PDO $pdo L'objet PDO de connexion à la base.
 * @return array
 * @throws Exception
 */
function getAllThemes(PDO $pdo) {
    $stmt = $pdo->prepare('SELECT id, nom FROM themes ORDER BY nom');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
