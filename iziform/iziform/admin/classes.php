<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
check_auth('admin');

$message = "";


// 1. Ajouter une classe
if (isset($_POST['add_classe'])) {//lutilisateur a bien appye sue le bouton du formulaire add-classe
    $stmt = $conn->prepare("INSERT INTO classes (nom, niveau, semestre, annee_scolaire) VALUES (?, ?, ?, ?)");
    $stmt->execute([//envooi le donnees
        $_POST['nom'], 
        $_POST['niveau'], 
        $_POST['semestre'], 
        $_POST['annee_scolaire']
    ]);
    $message = "Classe créée avec succès !";
}

// 2. Modifier une classe
if (isset($_POST['update_classe'])) {
    $nom = $_POST['nom'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $semestre = $_POST['semestre'] ?? '';
    $annee = $_POST['annee_scolaire'] ?? '';
    $id = $_POST['classe_id'] ?? 0;

    $stmt = $conn->prepare("UPDATE classes SET nom=?, niveau=?, semestre=?, annee_scolaire=? WHERE id=?");
    $stmt->execute([$nom, $niveau, $semestre, $annee, $id]);
    $message = "La classe a été mise à jour !";
}

// 3. Supprimer une classe
if (isset($_GET['del_classe'])) {
    $conn->prepare("DELETE FROM classes WHERE id=?")->execute([$_GET['del_classe']]);
    header("Location: classes.php"); 
    exit;
}

// 4. Assigner Étudiant / Formateur ajouter soit etudiant ou etudiant
if (isset($_POST['assign_person'])) {
    if ($_POST['type'] == 'student') {
        $stmt = $conn->prepare("INSERT IGNORE INTO inscriptions (student_id, classe_id) VALUES (?, ?)");
    } else {
        $stmt = $conn->prepare("INSERT IGNORE INTO classes_formateurs (formateur_id, classe_id) VALUES (?, ?)");
    }
    $stmt->execute([$_POST['person_id'], $_POST['classe_id']]);
}

// 5. Retirer Étudiant / Formateur
if (isset($_GET['remove'])) {//detection de get remove
    if ($_GET['type'] == 'student') {
        $conn->prepare("DELETE FROM inscriptions WHERE student_id=? AND classe_id=?")->execute([$_GET['id'], $_GET['classe']]);
    } else {
        $conn->prepare("DELETE FROM classes_formateurs WHERE formateur_id=? AND classe_id=?")->execute([$_GET['id'], $_GET['classe']]);
    }
    header("Location: classes.php"); 
    exit;
}
// 6. Ajouter un NOUVEL étudiant + l'inscrire dans la classe
if (isset($_POST['add_new_student'])) {

    // 1. Créer l'étudiant
    $stmt = $conn->prepare("
        INSERT INTO users (nom, email, password, role)
        VALUES (?, ?, ?, 'student')
    ");
    $stmt->execute([
        $_POST['nom'],
        $_POST['email'],
        password_hash($_POST['password'], PASSWORD_DEFAULT)
    ]);

    $student_id = $conn->lastInsertId();

    // 2. Inscrire dans la classe
    $stmt = $conn->prepare("
        INSERT INTO inscriptions (student_id, classe_id)
        VALUES (?, ?)
    ");
    $stmt->execute([
        $student_id,
        $_POST['classe_id']
    ]);

    $message = "Étudiant ajouté et inscrit avec succès";
}


//RÉCUPÉRATION DES DONNÉES 
$classes = $conn->query("SELECT * FROM classes ORDER BY id DESC")->fetchAll();
$all_students = $conn->query("SELECT id, nom FROM users WHERE role='student' ORDER BY nom")->fetchAll();
$all_formateurs = $conn->query("SELECT id, nom FROM formateurs ORDER BY nom")->fetchAll();


include "../includes/sidebar_admin.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
          /* 🌙 BACKGROUND */
body {
    background: linear-gradient(135deg, #0b1220, #0f1b2e);
    color: #e6edf3;
}

/* 🧩 CARD */
.card {
    background: #111c2e !important;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-4px);
}

/* 🟢 HEADER */
.card-header {
    background: transparent !important;
    color: #00ffcc !important;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* 🟡 TITRE */
h3 {
    background: linear-gradient(90deg, #00ffcc, #00aaff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* 🔘 BUTTONS */
.btn-primary {
    background: linear-gradient(90deg, #00ffcc, #00aaff);
    border: none;
    color: #000;
}

.btn-success {
    background: linear-gradient(90deg, #00ffcc, #00ff99);
    border: none;
}

.btn-warning {
    background: #ffaa00;
    border: none;
}

.btn-danger {
    background: #ff4d4d;
    border: none;
}

/* 📋 TABLE */
.table {
    color: #e6edf3;
}

.table thead {
    color: #8aa4c8;
    background: transparent !important;
}

.table-bordered {
    border-color: rgba(255,255,255,0.1);
}

.table tr:hover {
    background: rgba(255,255,255,0.04);
}

/* MODALS */
.modal-content {
    background: #111c2e;
    color: #fff;
    border-radius: 15px;
}

.modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.modal-footer {
    border-top: 1px solid rgba(255,255,255,0.1);
}

/* INPUT */
.form-control, .form-select {
    background: #0f1b2e;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}

.form-control:focus, .form-select:focus {
    border-color: #00ffcc;
    box-shadow: 0 0 5px #00ffcc;
}

/* ALERT */
.alert-success {
    background: rgba(0,255,150,0.1);
    border: none;
    color: #00ffcc;
}
/* 🤍 CARD BLANCHE POUR CERTAINES CLASSES */
.card-white {
    background: #ffffff !important;
    color: #000 !important;
}

/* Corriger texte dans card blanche */
.card-white .card-header {
    color: #000 !important;
    border-bottom: 1px solid #ddd;
}

.card-white .table {
    color: #000;
}

.card-white .table thead {
    color: #555;
}

.card-white .form-control,
.card-white .form-select {
    background: #fff;
    color: #000;
    border: 1px solid #ccc;
}

     
    </style>
</head>
<body class="bg-light">

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3>📚 Gestion du Planning des Classes</h3>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddClasse">+ Nouvelle Classe</button>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show d-print-none">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($classes as $c): ?>
        <div class="col-12 mb-4" id="box-<?= $c['id'] ?>">
            <div class="card shadow-sm print-area 
                <?= in_array(strtoupper($c['nom']), ['PP2','PP1','AJE2','AJE1']) ? 'card-white' : '' ?>">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><?= strtoupper($c['nom']) ?> — <?= $c['niveau'] ?> (<?= $c['semestre'] ?>) — <?= $c['annee_scolaire'] ?></span>
                    <div class="d-print-none">
                        <button class="btn btn-sm btn-info text-white" onclick="imprimerSection('box-<?= $c['id'] ?>')">🖨️ Imprimer</button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $c['id'] ?>">✏️ Modifier</button>
                        <a href="?del_classe=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette classe définitivement ?')">🗑️</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                <h6 class="text-primary">👩‍🎓 Liste des Étudiants</h6>
                                <button class="btn btn-sm btn-outline-primary d-print-none" data-bs-toggle="modal" data-bs-target="#modalAddEtu<?= $c['id'] ?>">+ Ajouter</button>
                            </div>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nom Complet</th><th class="table-actions d-print-none"></th></tr></thead>
                                <tbody>
                                <?php
                                $stmt = $conn->prepare("SELECT u.id, u.nom FROM inscriptions i JOIN users u ON u.id=i.student_id WHERE i.classe_id=?");
                                $stmt->execute([$c['id']]);
                                foreach ($stmt->fetchAll() as $e): ?>
                                    <tr>
                                        <td><?= $e['nom'] ?></td>
                                        <td class="text-end d-print-none"><a href="?remove=1&type=student&id=<?= $e['id'] ?>&classe=<?= $c['id'] ?>" class="text-danger fw-bold text-decoration-none">×</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                <h6 class="text-success">👨‍🏫 Formateurs Assignés</h6>
                                <button class="btn btn-sm btn-outline-success d-print-none" data-bs-toggle="modal" data-bs-target="#modalAddForm<?= $c['id'] ?>">+ Ajouter</button>
                            </div>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light"><tr><th>Nom Formateur</th><th class="table-actions d-print-none"></th></tr></thead>
                                <tbody>
                                <?php
                                $stmt = $conn->prepare("SELECT f.id, f.nom FROM classes_formateurs cf JOIN formateurs f ON f.id=cf.formateur_id WHERE cf.classe_id=?");
                                $stmt->execute([$c['id']]);
                                foreach ($stmt->fetchAll() as $f): ?>
                                    <tr>
                                        <td><?= $f['nom'] ?></td>
                                        <td class="text-end d-print-none"><a href="?remove=1&type=formateur&id=<?= $f['id'] ?>&classe=<?= $c['id'] ?>" class="text-danger fw-bold text-decoration-none">×</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEdit<?= $c['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <form method="POST" class="modal-content">
              <div class="modal-header"><h5>Modifier la Classe</h5></div>
              <div class="modal-body">
                <input type="hidden" name="classe_id" value="<?= $c['id'] ?>">
                <div class="mb-3"><label class="form-label">Nom</label><input class="form-control" name="nom" value="<?= $c['nom'] ?>" required></div>
                <div class="mb-3">
                    <label class="form-label">Niveau</label>
                    <select class="form-select" name="niveau">
                        <option value="1ère année" <?= $c['niveau']=='1ère année'?'selected':'' ?>>1ère année</option>
                        <option value="2ème année" <?= $c['niveau']=='2ème année'?'selected':'' ?>>2ème année</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semestre</label>
                    <select class="form-select" name="semestre">
                        <option value="S1" <?= $c['semestre']=='S1'?'selected':'' ?>>S1</option>
                        <option value="S2" <?= $c['semestre']=='S2'?'selected':'' ?>>S2</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Année Scolaire</label><input class="form-control" name="annee_scolaire" value="<?= $c['annee_scolaire'] ?>" required></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button name="update_classe" type="submit" class="btn btn-primary">Sauvegarder</button>
              </div>
            </form>
          </div>
        </div>

       <div class="modal fade" id="modalAddEtu<?= $c['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5>➕ Ajouter un nouvel étudiant</h5>
      </div>

      <div class="modal-body">
        <input type="hidden" name="classe_id" value="<?= $c['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Nom complet</label>
          <input type="text" name="nom" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mot de passe</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" name="add_new_student" class="btn btn-success w-100">
          Créer & Inscrire
        </button>
      </div>
    </form>
  </div>
</div>


        <div class="modal fade" id="modalAddForm<?= $c['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <form method="POST" class="modal-content">
              <div class="modal-header"><h5>Assigner un formateur</h5></div>
              <div class="modal-body">
                <input type="hidden" name="classe_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="type" value="formateur">
                <select name="person_id" class="form-select" required>
                    <option value="">-- Sélectionner le formateur --</option>
                    <?php foreach($all_formateurs as $fo): ?>
                        <option value="<?= $fo['id'] ?>"><?= $fo['nom'] ?></option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="modal-footer"><button name="assign_person" class="btn btn-primary w-100">Assigner</button></div>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalAddClasse" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white"><h5>Créer une Nouvelle Classe</h5></div>
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nom de la classe</label><input class="form-control" name="nom" placeholder="ex: Dev Digital" required></div>
        <div class="mb-3">
            <label class="form-label">Niveau</label>
            <select class="form-select" name="niveau" required>
                <option value="1ère année">1ère année</option>
                <option value="2ème année">2ème année</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Semestre</label>
            <select class="form-select" name="semestre" required>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
            </select>
        </div>
        <div class="mb-3"><label class="form-label">Année Scolaire</label><input class="form-control" name="annee_scolaire" placeholder="2024-2025" required></div>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button name="add_classe" type="submit" class="btn btn-success">Confirmer</button>
      </div>
    </form>
  </div>
</div>

<script>
// Fonction pour imprimer uniquement une classe spécifique
function imprimerSection(id) {
    window.print();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>