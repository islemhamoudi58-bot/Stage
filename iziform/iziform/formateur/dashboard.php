<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'formateur') {
    header("Location: ../index.php");
    exit;
}

$formateur_id = $_SESSION['user_id'];

// ====== STATS ======
$nb_classes = $conn->prepare("SELECT COUNT(*) 
                                FROM classes c JOIN classes_formateurs cf ON c.id = cf.classe_id
                                WHERE cf.formateur_id = ?");
$nb_classes->execute([$formateur_id]);
$nb_classes = $nb_classes->fetchColumn();

$nb_students = $conn->prepare("SELECT COUNT(DISTINCT i.student_id) 
                                FROM inscriptions i JOIN classes_formateurs cf ON i.classe_id = cf.classe_id
                                WHERE cf.formateur_id = ?");
$nb_students->execute([$formateur_id]);
$nb_students = $nb_students->fetchColumn();

$nb_absences = $conn->prepare("SELECT COUNT(*) 
                                FROM absences a JOIN classes_formateurs cf ON a.classe_id = cf.classe_id
                                WHERE cf.formateur_id = ?");
$nb_absences->execute([$formateur_id]);
$nb_absences = $nb_absences->fetchColumn();

$nb_messages = $conn->prepare("SELECT COUNT(*)
                                FROM messages 
                                WHERE destinataire_id = ?");
$nb_messages->execute([$formateur_id]);
$nb_messages = $nb_messages->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Formateur | Iziform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #121212; color: #e0e0e0; }
.sidebar-container { background-color: #1f1f2f; }
.card {
    background-color: #1e1e2f;
    color: #e0e0e0;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,.4);
}
.card-header {
    background: linear-gradient(135deg, #198754, #0d6efd);
    color: #fff;
    font-weight: 600;
    border-radius: 15px 15px 0 0;
}
.btn-outline-primary, .btn-outline-success, .btn-outline-danger, .btn-outline-dark {
    color: #e0e0e0;
    border-color: #555;
}
.btn-outline-primary:hover { background-color: #0d6efd; border-color: #0a58ca; color: #fff; }
.btn-outline-success:hover { background-color: #198754; border-color: #157347; color: #fff; }
.btn-outline-danger:hover { background-color: #dc3545; border-color: #b02a37; color: #fff; }
.btn-outline-dark:hover { background-color: #343a40; border-color: #1d2124; color: #fff; }
.table { background-color: #1e1e2f; color: #e0e0e0; }
.table th, .table td { border: 1px solid #444; }
.table-hover tbody tr:hover { background-color: #34344f; }
</style>
</head>
<body>

<?php include "../includes/sidebar_formateur.php"; ?>

<div class="container-fluid p-4">
<h2 class="mb-4" style="background: linear-gradient(90deg,#0d6efd,#198754); -webkit-background-clip: text; color: transparent;">👋 Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?></h2>

<!-- STAT CARDS -->
<div class="row g-4 mb-4">
<?php
$cards = [
    ['icon'=>'bi-easel-fill','title'=>'Classes','count'=>$nb_classes,'color'=>'text-primary'],
    ['icon'=>'bi-people-fill','title'=>'Étudiants','count'=>$nb_students,'color'=>'text-success'],
    ['icon'=>'bi-x-circle','title'=>'Absences','count'=>$nb_absences,'color'=>'text-danger'],
    ['icon'=>'bi-envelope-fill','title'=>'Messages','count'=>$nb_messages,'color'=>'text-warning']
];
foreach($cards as $c): ?>
<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body text-center">
<i class="bi <?= $c['icon'] ?> fs-1 <?= $c['color'] ?>"></i>
<h5 class="mt-2"><?= $c['title'] ?></h5>
<h3><?= $c['count'] ?></h3>
</div>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- ACTIONS RAPIDES -->
<div class="row g-4">
<div class="col-md-8">
<div class="card shadow-sm border-0">
<div class="card-header">📌 Actions rapides</div>
<div class="card-body">
<p>✔ Marquer les absences</p>
<p>✔ Publier des nouvelles</p>

</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm border-0">
<div class="card-header">⚡ Accès rapide</div>
<div class="card-body d-grid gap-2">
<a href="classes.php" class="btn btn-outline-primary">📚 Mes classes</a>
<a href="absences.php" class="btn btn-outline-danger">🚫 Absences</a>
<a href="../logout.php" class="btn btn-outline-dark">🚪 Déconnexion</a>
</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>