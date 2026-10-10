<?php
session_start();
if (!isset($_SESSION['admin'])) {
  exit("Accès refusé");
}

$pdo = new PDO(
  "mysql:host=localhost;dbname=izi_contact;charset=utf8",
  "root","",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (isset($_GET['id'])) {
  $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
  $stmt->execute([$_GET['id']]);
}

header("Location: admin.php");
