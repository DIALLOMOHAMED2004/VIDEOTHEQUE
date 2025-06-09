<?php
// public/login_process.php
// Ce fichier vérifie les identifiants et démarre la session si tout est bon.

session_start();

// Vérifier que username et password sont bien envoyés en POST
if (!isset($_POST['username'], $_POST['password'])) {
    // Si on a un accès direct sans passer par le formulaire
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php'; // Connexion $pdo

// Récupérer et nettoyer les champs envoyés
$username = trim($_POST['username']);
$password = $_POST['password'];

// Préparer la requête pour récupérer l'administrateur
$sql = 'SELECT id, password_hash FROM administrateurs WHERE username = :username';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':username', $username, PDO::PARAM_STR);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    // Pas d'administrateur avec ce nom d'utilisateur
    header('Location: login.php?error=1');
    exit;
}

// Vérifier le mot de passe
if (password_verify($password, $user['password_hash'])) {
    // Mot de passe correct → on enregistre les informations dans la session
    $_SESSION['admin_id']       = $user['id'];
    $_SESSION['admin_username'] = $username;
    // Redirection vers la page d'administration
    header('Location: ../admin/dashboard.php');
    exit;
} else {
    // Mot de passe incorrect
    header('Location: login.php?error=1');
    exit;
}
