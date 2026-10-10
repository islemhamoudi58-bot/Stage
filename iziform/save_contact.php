<?php
header("Content-Type: text/html; charset=UTF-8");

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=izi_contact;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);//erreur serveur 
    echo "<span style='color:red'>Connexion DB échouée ❌</span>";
    exit;
}
//verifier si formulaire envoye
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        empty($_POST['nom']) ||
        empty($_POST['prenom']) ||
        empty($_POST['formation']) ||
        empty($_POST['message'])
    ) {
        echo "<span style='color:red'>Tous les champs sont obligatoires ❌</span>";
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO commentaires (nom, prenom, formation, message)
        VALUES (:nom, :prenom, :formation, :message)
    ");

    $stmt->execute([
        ":nom" => htmlspecialchars($_POST['nom']),
        ":prenom" => htmlspecialchars($_POST['prenom']),
        ":formation" => htmlspecialchars($_POST['formation']),
        ":message" => htmlspecialchars($_POST['message'])
    ]);

    echo "<span style='color:green'>Message envoyé avec succès ✔️</span>";
}
?>
