<?php
session_start();
require "../includes/db.php";
require "../includes/auth.php";

// Sécurité : formateur connecté
check_auth('formateur');

$formateur_id  = $_SESSION['user_id'];
$formateur_nom = $_SESSION['nom'] ?? 'Formateur';

$classe_id = $_GET['classe_id'] ?? null;
$date_jour = $_GET['date_absence'] ?? date('Y-m-d');

$etudiants = [];
$message   = "";

/* ================== LISTE DES CLASSES ================== */
$stmt = $conn->prepare("
    SELECT c.id, c.nom 
    FROM classes c
    JOIN classes_formateurs cf ON c.id = cf.classe_id
    WHERE cf.formateur_id = ?
    ORDER BY c.nom
");
$stmt->execute([$formateur_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== CHARGER ÉTUDIANTS ================== */
if ($classe_id) {
    $stmt = $conn->prepare("
        SELECT u.id, u.nom, u.prenom,
        (SELECT a.absent 
         FROM absences a 
         WHERE a.student_id = u.id 
           AND a.classe_id = ?
           AND a.date_absence = ?) AS etat_actuel
        FROM users u
        JOIN inscriptions i ON u.id = i.student_id
        WHERE i.classe_id = ?
        ORDER BY u.nom
    ");
    $stmt->execute([$classe_id, $date_jour, $classe_id]);
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ================== ENREGISTREMENT ================== */
if (isset($_POST['save_absences'])) {
    $classe_id = $_POST['classe_id'];
    $date_jour = $_POST['date_absence'];
    $absents   = $_POST['absent'] ?? [];

    foreach ($etudiants as $e) {
        $is_absent = in_array($e['id'], $absents) ? 1 : 0;

        $stmt = $conn->prepare("
            INSERT INTO absences (student_id, classe_id, date_absence, absent)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE absent = VALUES(absent)
        ");
        $stmt->execute([$e['id'], $classe_id, $date_jour, $is_absent]);
    }

    header("Location: absences.php?classe_id=$classe_id&date_absence=$date_jour&success=1");
    exit;
}


include "../includes/sidebar_formateur.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des absences</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* === BODY DARK NEON === */
body {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    color: #f0f0f0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* === TITRES GRADIENT NEON === */
h2.gradient-title, h3.gradient-title {
    background: linear-gradient(90deg, #00fff7, #ff00ff, #ffea00);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 800;
    text-shadow: 0 0 8px #00fff7, 0 0 12px #ff00ff;
}

/* === CARDS NEON === */
.card {
    background-color: #1a1a2e;
    color: #f0f0f0;
    border-radius: 15px;
    border: none;
    box-shadow: 0 0 15px #00fff7, 0 0 25px #ff00ff, 0 0 35px #ffea00;
}

/* === TABLE NEON === */
.table-responsive {
    background-color: #1a1a2e;
    border-radius: 15px;
    overflow: hidden;
}
.table {
    color: #f0f0f0;
    border-color: #333;
    margin-bottom: 0;
}
.table th, .table td {
    border-color: #333;
}
.table thead {
    background: linear-gradient(135deg, #00fff7, #ff00ff, #ffea00);
    color: #000;
    text-shadow: 0 0 3px #fff;
}

/* === BOUTONS NEON === */
.btn-primary, .btn-success {
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 0 8px #00fff7, 0 0 12px #ff00ff;
    color: #000;
}
.btn-primary { background-color: #00fff7; border-color: #00fff7; }
.btn-primary:hover { background-color: #00cfcf; border-color: #00cfcf; }
.btn-success { background-color: #ffea00; border-color: #ffea00; }
.btn-success:hover { background-color: #e5d800; border-color: #e5d800; }

/* === INPUT CHECKBOX === */
.absent-checkbox, .present-checkbox {
    width: 20px;
    height: 20px;
    accent-color: #ff00ff;
}

/* === ALERTS NEON === */
.alert {
    border-radius: 10px;
    box-shadow: 0 0 10px #ff00ff;
    background-color: #1a1a2e;
    color: #fff;
}

/* === FORM LABEL === */
.form-label {
    color: #f0f0f0;
    font-weight: 600;
}

/* === SELECT & INPUT === */
.form-select, .form-control {
    background-color: #1a1a2e;
    color: #f0f0f0;
    border-color: #555;
}
.form-select:focus, .form-control:focus {
    border-color: #00fff7;
    box-shadow: 0 0 5px #00fff7, 0 0 10px #ff00ff;
}
</style>
</head>
<body>

<div class="container-fluid p-4">

    <h3 class="mb-4 gradient-title">📋 Feuille d’appel</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Absences enregistrées avec succès</div>
    <?php endif; ?>

    <!-- FILTRE -->
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Classe</label>
                <select name="classe_id" class="form-select" required>
                    <option value="">Sélectionner...</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($classe_id == $c['id']) ? 'selected' : '' ?>>
                            <?= $c['nom'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Date</label>
                <input type="date" name="date_absence" class="form-control" value="<?= $date_jour ?>" required>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Charger la liste</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <?php if ($classe_id && !empty($etudiants)): ?>
    <form method="POST">
        <input type="hidden" name="classe_id" value="<?= $classe_id ?>">
        <input type="hidden" name="date_absence" value="<?= $date_jour ?>">

        <div class="table-responsive shadow-sm rounded mb-4">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Étudiant</th>
                        <th class="text-center" width="100">Absent</th>
                        <th class="text-center" width="100">Présent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $e): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($e['nom']) ?></strong> <?= htmlspecialchars($e['prenom']) ?></td>
                        <td class="text-center">
                            <input type="checkbox"
                                   class="absent-checkbox"
                                   name="absent[]"
                                   value="<?= $e['id'] ?>"
                                   <?= ($e['etat_actuel'] == 1) ? 'checked' : '' ?>>
                        </td>
                        <td class="text-center">
                            <input type="checkbox"
                                   class="present-checkbox"
                                   <?= ($e['etat_actuel'] == 0) ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="p-3 text-end border-top">
                <button type="submit" name="save_absences" class="btn btn-success px-5">
                    💾 Valider l’appel
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>

</div>

<!-- JS pour cases mutuellement exclusives -->
<script>
document.querySelectorAll('tr').forEach(row => { // on parcourt toutes les lignes du tableau (chaque étudiant)
    const absent = row.querySelector('.absent-checkbox');
    // on récupère la case "Absent" dans la ligne
    const present = row.querySelector('.present-checkbox');

    // si les deux cases existent dans la ligne
    if (absent && present) {
        absent.addEventListener('change', () => {  // quand on coche ou décoche "Absent"

            present.checked = !absent.checked;
        });
        present.addEventListener('change', () => {
            absent.checked = !present.checked;
        });
    }
});
</script>

</body>
</html>