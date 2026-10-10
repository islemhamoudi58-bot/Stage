<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['user_id'];

/* ================= INFOS ÉTUDIANT ================= */
$stmt = $conn->prepare("
    SELECT 
        u.nom AS nom_etudiant,
        c.nom AS classe,
        c.niveau,
        c.annee
    FROM users u
    JOIN inscriptions i ON u.id = i.student_id
    JOIN classes c ON i.classe_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$student_id]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Étudiant | Iziform</title>

<!-- FONTS + BOOTSTRAP -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root {
    --bg-dark: #0b1220;
    --card-bg: #111827;
    --border-color: #1f2937;
    --text-muted: #94a3b8;
    --accent-blue: #3b82f6;
    --accent-green: #10b981;
}

/* GLOBAL */
body {
    background-color: var(--bg-dark);
    color: white;
    font-family: 'Inter', sans-serif;
}

/* FORCER TEXTE BLANC */
h3, h5, p, a {
    color: #ffffff !important;
}

.dashboard-card h5 {
    color: #ffffff !important;
    font-weight: 600;
}

/* HEADER */
.welcome-banner {
    background: linear-gradient(135deg, #111827, #1e293b);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 25px;
}

/* CARD */
.card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

/* PROFIL */
.profile-icon {
    font-size: 60px;
    color: var(--accent-blue);
}

/* ACTION CARD */
.dashboard-card {
    cursor: pointer;
    height: 100%;
    padding: 20px;
}

.dashboard-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* ICON COLORS */
.icon-assiduite {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.icon-news {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

a {
    text-decoration: none;
    color: inherit;
}

a:hover {
    color: #ffffff !important;
}

.text-muted {
    color: var(--text-muted) !important;
}
</style>
</head>

<body>

<?php include "../includes/sidebar_student.php"; ?>

<div class="container-fluid p-4">

    <!-- HEADER -->
    <div class="welcome-banner mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1">🎓 Tableau de Bord Étudiant</h3>
            <p class="text-muted mb-0">
                Bienvenue <?= htmlspecialchars($etudiant['nom_etudiant']) ?> 👋
            </p>
        </div>
        <div class="badge bg-primary px-3 py-2">
            <?= date('d M Y • H:i') ?>
        </div>
    </div>

<?php if ($etudiant): ?>
<div class="row g-4">

    <!-- PROFIL -->
    <div class="col-md-4">
        <div class="card text-center p-4">
            <i class="bi bi-person-circle profile-icon"></i>
            <h5 class="mt-3"><?= htmlspecialchars($etudiant['nom_etudiant']) ?></h5>
            <p class="text-muted">Étudiant Iziform</p>

            <div class="mt-2">
                <span class="badge bg-secondary">
                    <?= htmlspecialchars($etudiant['classe']) ?>
                </span>
                <span class="badge bg-dark">
                    <?= htmlspecialchars($etudiant['niveau']) ?>
                </span>
                <span class="badge bg-success">
                    <?= htmlspecialchars($etudiant['annee']) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="col-md-8">
        <div class="row g-4">

            <!-- ASSIDUITÉ -->
            <div class="col-md-6">
                <a href="absences.php">
                    <div class="card dashboard-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="dashboard-icon icon-assiduite me-3">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                            <h5 class="mb-0">Assiduité</h5>
                        </div>
                        <p class="text-muted">
                            Consultez vos absences et présences.
                        </p>
                    </div>
                </a>
            </div>

            <!-- ACTUALITÉS -->
            <div class="col-md-6">
                <a href="nouvelles.php">
                    <div class="card dashboard-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="dashboard-icon icon-news me-3">
                                <i class="bi bi-megaphone fs-4"></i>
                            </div>
                            <h5 class="mb-0">Actualités</h5>
                        </div>
                        <p class="text-muted">
                            Consultez les publications importantes.
                        </p>
                    </div>
                </a>
            </div>

        </div>
    </div>

</div>
<?php endif; ?>

</div>

</body>
</html>