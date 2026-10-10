<?php
session_start();
require "../includes/db.php";
require "../includes/auth.php";

// Sécurité : formateur connecté
check_auth('formateur');

$formateur_id = $_SESSION['user_id'];

// Récupérer les classes assignées au formateur
$stmt = $conn->prepare("
    SELECT c.id, c.nom, c.niveau, c.annee
    FROM classes c
    JOIN classes_formateurs cf ON c.id = cf.classe_id
    WHERE cf.formateur_id = ?
    ORDER BY c.nom
");
$stmt->execute([$formateur_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque classe, récupérer les étudiants
$etudiants_par_classe = [];
foreach ($classes as $c) {
    $stmt = $conn->prepare("
        SELECT u.id, u.nom, u.prenom
        FROM users u
        JOIN inscriptions i ON u.id = i.student_id
        WHERE i.classe_id = ?
        ORDER BY u.nom
    ");
    $stmt->execute([$c['id']]);
    $etudiants_par_classe[$c['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


include "../includes/sidebar_formateur.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes classes | Formateur</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* === BODY NEON === */
body {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    color: #e0e0e0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* === TITRES GRADIENT NEON === */
h2.gradient-title, h3.gradient-title {
    background: linear-gradient(90deg, #00ffea, #ff00ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
    text-shadow: 0 0 10px #00ffea, 0 0 20px #ff00ff;
}

/* === CARDS NEON === */
.card {
    background-color: #1a1a2e;
    color: #e0e0e0;
    border-radius: 15px;
    box-shadow: 0 0 15px #00ffea, 0 0 25px #ff00ff;
    border: none;
}
.card-header {
    background: linear-gradient(135deg, #00ffea, #ff00ff);
    color: #fff;
    font-weight: 600;
    border-radius: 15px 15px 0 0;
}

/* === BOUTONS NEON === */
.btn-outline-primary, .btn-outline-success, .btn-outline-danger {
    color: #fff;
    border-color: #555;
}
.btn-outline-primary:hover { background-color: #00ffea; border-color: #00e6d0; color: #000; }
.btn-outline-success:hover { background-color: #ff00ff; border-color: #e600e6; color: #000; }
.btn-outline-danger:hover { background-color: #ff073a; border-color: #e6002e; color: #fff; }

/* === TABLE NEON === */
.table {
    background-color: #1a1a2e;
    color: #e0e0e0;
    border-color: #444;
}
.table th, .table td { border-color: #444; }
.table thead {
    background: linear-gradient(135deg, #00ffea, #ff00ff);
    color: #fff;
}

/* === LISTES === */
ul { padding-left: 20px; }

/* === ALERTS === */
.alert { border-radius: 10px; }

/* === FORM LABELS === */
.form-label { color: #e0e0e0; font-weight: 600; }

/* === SELECT & INPUT === */
.form-select, .form-control {
    background-color: #1a1a2e;
    color: #e0e0e0;
    border-color: #555;
}
.form-select:focus, .form-control:focus {
    border-color: #00ffea;
    box-shadow: 0 0 5px #00ffea;
}
</style>
</head>
<body>

<div class="container-fluid p-4">
    <h2 class="mb-4 gradient-title">📚 Mes classes</h2>

    <?php if(empty($classes)): ?>
        <div class="alert alert-warning">Vous n'avez aucune classe assignée pour le moment.</div>
    <?php else: ?>
        <div class="row g-4">
        <?php foreach($classes as $c): ?>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <?= htmlspecialchars($c['nom']) ?> (<?= $c['niveau'].' '.$c['annee'] ?>)
                    </div>
                    <div class="card-body p-2">
                        <strong>Étudiants :</strong>
                        <?php if(empty($etudiants_par_classe[$c['id']])): ?>
                            <p class="text-muted">Aucun étudiant inscrit</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach($etudiants_par_classe[$c['id']] as $e): ?>
                                    <li><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="mt-2 d-grid gap-2">
                            <a href="absences.php?classe_id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-sm">🚫 Absences</a>
                            <a href="messages.php?classe_id=<?= $c['id'] ?>" class="btn btn-outline-success btn-sm">💬 Messages</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>