<?php
// public/logout.php
session_start();
// On vide toutes les variables de session
$_SESSION = [];
// On détruit la session
session_unset();
session_destroy();
// On redirige vers la page de login
header('Location: login.php');
exit;
