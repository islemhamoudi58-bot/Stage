<?php
// CONFIGURATION BASE DE DONNÉES
define('DB_HOST', 'localhost');
define('DB_NAME', 'iziform_db2');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// CONFIGURATION DU SITE
define('SITE_NAME', 'Institut Al Zaytouna');
define('SITE_URL', 'http://localhost/iziform_website');
define('ADMIN_EMAIL', 'zitounaformation@yahoo.com');

// CHEMINS
define('ROOT_PATH', dirname(__DIR__));

// FONCTIONS UTILES
function secure_input($data) {
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

function send_email($to, $subject, $message, $from = 'zitounaformation@yahoo.com') {
    $headers = "From: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['valeur'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

// TIMEZONE
date_default_timezone_set('Africa/Tunis');

// ERREURS (pour debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
