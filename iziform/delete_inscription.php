<?php
require_once 'connexion.php';
$id = $_GET['id'] ?? 0;
$conn->prepare("DELETE FROM inscriptions WHERE id=?")->execute([$id]);
header("Location: liste_inscriptions.php");
exit;
