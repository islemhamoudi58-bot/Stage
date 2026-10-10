<?php
require "../includes/auth.php";
require "../includes/db.php";

// Seul l'admin peut accéder
check_auth('admin');

/* Récupération des étudiants inscrits */
$etudiants = $conn->query("
    SELECT u.id, u.nom, u.email, COUNT(i.id) AS nb_inscriptions
    FROM users u
    LEFT JOIN inscriptions i ON i.student_id = u.id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY u.nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
include "../includes/sidebar_admin.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Liste des Étudiants | Admin</title>
</head>
<body>

<div class="container-fluid p-4">
    <h2>Liste des étudiants inscrits</h2>

    <div class="card shadow p-4 mt-4">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Nombre d'inscriptions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nom']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>
                        <td><?= $e['nb_inscriptions'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($etudiants)): ?>
                    <tr>
                        <td colspan="3" class="text-center">Aucun étudiant inscrit pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
