<?php
// DB configuration
$host = 'localhost';
$db   = 'iziform_db'; // Nom de ta base de données
$user = 'root';        // Nom utilisateur MySQL
$pass = '';            // Mot de passe MySQL
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options PDO pour sécurité et performance
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche exceptions en cas d'erreur
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Prépare réellement les requêtes pour sécurité
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Message d'erreur professionnel pour dev
    echo "<h2>Erreur de connexion à la base de données :</h2>";
    echo "<p>".$e->getMessage()."</p>";
    exit;
}
?>
