<?php
session_start();
require_once "../includes/db.php";

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

// Récupération publications
$nouvelles = $conn->query("
    SELECT p.titre, p.contenu, p.date_publication, u.nom, u.prenom, u.role
    FROM publications p
    JOIN users u ON p.auteur_id = u.id
    ORDER BY p.date_publication DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Publications | Étudiant</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #121212;
    color: #e0e0e0;
    font-family: 'Inter', sans-serif;
}

/* TITRE */
.page-title {
    color: #ffffff;
    font-weight: 600;
}

/* CARD */
.card {
    background-color: #1e1e2f;
    color: #e0e0e0;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,.3);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

/* TEXTE */
.card-title {
    font-weight: 600;
    color: #ffffff;
}

.card-text {
    color: #d1d5db;
}

/* SMALL TEXT */
.text-muted {
    color: #9ca3af !important;
}

/* EMPTY STATE */
.empty-box {
    text-align: center;
    padding: 50px;
    color: #9ca3af;
}
</style>
</head>

<body>

<?php include "../includes/sidebar_student.php"; ?>

<div class="container p-4">

    <!-- HEADER -->
    <h3 class="mb-4 page-title">
        <i class="bi bi-newspaper me-2"></i> Publications
    </h3>

    <?php if (empty($nouvelles)): ?>
        <div class="empty-box">
            <i class="bi bi-info-circle fs-1 mb-3"></i>
            <p>Aucune publication pour le moment.</p>
        </div>
    <?php endif; ?>

    <!-- LISTE -->
    <?php foreach ($nouvelles as $n): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">

                <h5 class="card-title">
                    <?= htmlspecialchars($n['titre']) ?>
                </h5>

                <p class="card-text">
                    <?= nl2br(htmlspecialchars($n['contenu'])) ?>
                </p>

                <small class="text-muted">
                    <i class="bi bi-person"></i>
                    <?= htmlspecialchars($n['nom'] . ' ' . $n['prenom']) ?>
                    (<?= htmlspecialchars($n['role']) ?>)
                    • <?= date("d/m/Y H:i", strtotime($n['date_publication'])) ?>
                </small>

            </div>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>