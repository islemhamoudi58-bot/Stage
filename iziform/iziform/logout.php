<?php
session_start(); // Démarre la session

// Supprimer toutes les variables de session
$_SESSION = [];

// Détruire la session
session_destroy();

// Supprimer le cookie de session (optionnel mais conseillé)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Rediriger vers la page de login
header("Location: index.php");
exit;
