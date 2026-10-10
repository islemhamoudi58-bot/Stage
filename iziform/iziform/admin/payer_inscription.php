<?php
require_once 'connexion.php';
$id = $_GET['id'] ?? 0;
$conn->prepare("
  UPDATE inscriptions SET statut_paiement='paye' WHERE id=?
")->execute([$id]);
header("Location: inscriptions.php");
exit;
