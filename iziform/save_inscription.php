<?php
require_once 'connexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom           = $_POST['nom'] ?? '';
    $prenom        = $_POST['prenom'] ?? '';
    $email         = $_POST['email'] ?? '';
    $telephone     = $_POST['tel'] ?? '';
    $formation     = $_POST['formation'] ?? '';
    $typeFormation = $_POST['typeFormation'] ?? '';

    if (
        empty($nom) || empty($prenom) || empty($email) ||
        empty($telephone) || empty($formation) || empty($typeFormation)
    ) {
        die("Toutes les données sont obligatoires");
    }

    $sql = "INSERT INTO inscriptions
            (nom, prenom, email, telephone, formation, type_formation)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $nom,
        $prenom,
        $email,
        $telephone,
        $formation,
        $typeFormation
    ]);

    // redirection vers la liste
    header("Location: liste_inscriptions.php");
    exit;
}
