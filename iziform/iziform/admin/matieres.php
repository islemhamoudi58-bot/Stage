<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

check_auth('admin');//interdit l'acce si tu nest pas admin

$success = "";//message
$error = "";

// AJOUTER
if (isset($_POST['add'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $classe = $_POST['classe'];
    $formateur = $_POST['formateur'];
    $semestre = $_POST['semestre'];

    try {
        $stmt = $conn->prepare("INSERT INTO matieres (nom, classe_id, formateur_id, semestre) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $classe, $formateur, $semestre]);
        $success = "Matière ajoutée !";
    } catch (Exception $e) { $error = "Erreur lors de l'ajout."; }
}

// MODIFIER
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nom = htmlspecialchars($_POST['nom']);
    $classe = $_POST['classe'];
    $formateur = $_POST['formateur'];
    $semestre = $_POST['semestre'];

    try {
        $stmt = $conn->prepare("UPDATE matieres SET nom=?, classe_id=?, formateur_id=?, semestre=? WHERE id=?");
        $stmt->execute([$nom, $classe, $formateur, $semestre, $id]);
        $success = "Matière mise à jour !";
    } catch (Exception $e) { $error = "Erreur lors de la modification."; }
}

// SUPPRIMER
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM matieres WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: matieres.php");
    exit;
}

// RÉCUPÉRATION
$f_semestre = $_GET['f_semestre'] ?? '';
$sql = "SELECT m.*, c.nom AS classe_nom, f.nom AS formateur_nom
        FROM matieres m
        JOIN classes c ON m.classe_id = c.id
        LEFT JOIN formateurs f ON m.formateur_id = f.id";

if (!empty($f_semestre)) { $sql .= " WHERE m.semestre = :sem"; }
$sql .= " ORDER BY c.nom ASC, m.nom ASC";

$stmt = $conn->prepare($sql);
if (!empty($f_semestre)) { $stmt->bindValue(':sem', $f_semestre); }
$stmt->execute();
$matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classes = $conn->query("SELECT * FROM classes ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$formateurs = $conn->query("SELECT * FROM formateurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);


include "../includes/sidebar_admin.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Matières | Iziform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #121212; color: #e0e0e0; }

/* TABLE */
.table-dark { background-color: #1e1e2f; color: #e0e0e0; }
.table-dark thead { background-color: #2c2c3e; color: #fff; }
.table-dark tbody tr:hover { background-color: #2a2a40; }

/* CARDS */
.card { background-color: #1e1e2f; color: #e0e0e0; border: none; }

/* FORMULAIRES */
.form-control, .form-select { background-color: #2a2a40; color: #e0e0e0; border: 1px solid #444; }
.form-control:focus, .form-select:focus { background-color: #2a2a40; color: #fff; border-color: #2575fc; box-shadow: none; }

/* BADGES */
.badge-sem { padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 0.8rem; }
.semestre-1 { background-color: #1565c0; color: #fff; }
.semestre-2 { background-color: #2e7d32; color: #fff; }
.badge.bg-secondary { background-color: #33334d; color: #fff; }

/* BUTTONS */
.btn-primary { background-color: #2575fc; border-color: #2575fc; }
.btn-primary:hover { background-color: #1a5edb; border-color: #1a5edb; }
.btn-success { background-color: #28a745; border-color: #28a745; }
.btn-success:hover { background-color: #218838; border-color: #1e7e34; }

/* MODALS */
.modal-content { background-color: #1e1e2f; color: #e0e0e0; }
.modal .form-control, .modal .form-select { background-color: #2a2a40; color: #e0e0e0; border: 1px solid #444; }
.modal .form-control:focus, .modal .form-select:focus { background-color: #2a2a40; color: #fff; border-color: #2575fc; }

/* ALERTS */
.alert { border-radius: 8px; }

</style>
</head>
<body>

<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-journal-text"></i> Gestion des Matières</h3>
    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addForm">
        <i class="bi bi-plus-lg"></i> Nouveau
    </button>
</div>

<?php if($success): ?><div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<div class="collapse mb-4" id="addForm">
<div class="card shadow-sm">
<div class="card-body">
<form method="POST" class="row g-3">
    <div class="col-md-3">
        <label class="form-label fw-bold small">Nom de la matière</label>
        <input type="text" name="nom" class="form-control" placeholder="Titre..." required>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-bold small">Classe</label>
        <select name="classe" class="form-select" required>
            <option value="">Choisir...</option>
            <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold small">Formateur</label>
        <select name="formateur" class="form-select" required>
            <option value="">Attribuer à...</option>
            <?php foreach ($formateurs as $f): ?>
            <option value="<?= $f['id'] ?>"><?= $f['nom'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-bold small">Semestre</label>
        <select name="semestre" class="form-select">
            <option value="S1">S1</option>
            <option value="S2">S2</option>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button name="add" class="btn btn-success w-100">Enregistrer</button>
    </div>
</form>
</div>
</div>
</div>

<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-dark table-hover align-middle mb-0" id="matieresTable">
<thead>
<tr>
<th>Matière</th>
<th>Classe</th>
<th>Formateur</th>
<th class="text-center">Semestre</th>
<th class="text-end">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($matieres as $m): ?>
<tr>
<td class="fw-bold"><?= htmlspecialchars($m['nom']) ?></td>
<td><span class="badge bg-secondary"><?= htmlspecialchars($m['classe_nom']) ?></span></td>
<td><?= htmlspecialchars($m['formateur_nom'] ?? 'Non assigné') ?></td>
<td class="text-center">
<span class="badge-sem <?= $m['semestre']=='S1'?'semestre-1':'semestre-2' ?>"><?= $m['semestre'] ?></span>
</td>
<td class="text-end">
<button class="btn btn-sm btn-light border me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $m['id'] ?>"><i class="bi bi-pencil text-primary"></i></button>
<a href="?delete=<?= $m['id'] ?>" class="btn btn-sm btn-light border" onclick="return confirm('Supprimer cette matière ?')"><i class="bi bi-trash text-danger"></i></a>
</td>
</tr>

<!-- MODAL ÉDITION -->
<div class="modal fade" id="editModal<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content shadow">
<form method="POST">
<div class="modal-header border-0">
<h5 class="modal-title fw-bold">Modifier la matière</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input type="hidden" name="id" value="<?= $m['id'] ?>">
<div class="mb-3">
<label class="form-label fw-bold small">Nom</label>
<input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($m['nom']) ?>" required>
</div>
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label fw-bold small">Classe</label>
<select name="classe" class="form-select">
<?php foreach($classes as $c): ?>
<option value="<?= $c['id'] ?>" <?= $c['id']==$m['classe_id']?'selected':'' ?>><?= $c['nom'] ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6 mb-3">
<label class="form-label fw-bold small">Semestre</label>
<select name="semestre" class="form-select">
<option value="S1" <?= $m['semestre']=='S1'?'selected':'' ?>>S1</option>
<option value="S2" <?= $m['semestre']=='S2'?'selected':'' ?>>S2</option>
</select>
</div>
</div>
<div class="mb-3">
<label class="form-label fw-bold small">Formateur</label>
<select name="formateur" class="form-select">
<?php foreach($formateurs as $f): ?>
<option value="<?= $f['id'] ?>" <?= $f['id']==$m['formateur_id']?'selected':'' ?>><?= $f['nom'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="modal-footer border-0">
<button type="submit" name="edit" class="btn btn-primary w-100">Enregistrer les modifications</button>
</div>
</form>
</div>
</div>
</div>

<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Recherche en temps réel
document.getElementById('searchInput')?.addEventListener('keyup', function(){
let term = this.value.toLowerCase();
document.querySelectorAll('#matieresTable tbody tr').forEach(r=>{
r.style.display = r.textContent.toLowerCase().includes(term)?'':'none';
});
});
</script>
</body>
</html>