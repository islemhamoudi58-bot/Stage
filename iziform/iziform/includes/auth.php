<?php
require_once "db.php";

// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fonction pour sécuriser le login
 * @param string $email
 * @param string $password
 * @return bool
 */
function login($email, $password) {
    global $conn;

    // Préparer la requête (sécurisé)
    $stmt = $conn->prepare("SELECT id, nom,email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérifier si utilisateur existe et mot de passe correct
    if ($user && password_verify($password, $user['password'])) {
        // Stocker les infos dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role']; // admin, student, formateur

        // Redirection selon rôle
        switch($user['role']){
            case 'admin':
                header("Location: ../admin/dashboard.php");
                break;
            case 'student':
                header("Location: ../student/dashboard.php");
                break;
            case 'formateur':
                header("Location: ../formateur/dashboard.php");
                break;
        }
        exit;
    } else {
        return false; // email ou mdp incorrect
    }
}

/**
 * Vérifie si un utilisateur est connecté et son rôle
 */
function check_auth($role){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role){
        header("Location: ../index.php");
        exit;
    }
}

/**
 * Déconnexion
 */
function logout(){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>
