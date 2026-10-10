<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

check_auth('admin');

$msg = "";
$matieres = $conn->query("SELECT * FROM matieres ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- 1. TRAITEMENT DES ACTIONS ---

if (isset($_POST['add'])) {
    $nom = htmlspecialchars($_POST['nom']);//supprimer le balise html dangereux
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);//nettoie lemail
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);//chiffre le mdp
    $matieres_ids = $_POST['matieres'] ?? [];//liste de matier

    try {
        $conn->beginTransaction();//demare la trasaction
    //insere dans user
        $stmtUser = $conn->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'formateur')");
        $stmtUser->execute([$nom, $email, $password]);
        $user_id = $conn->lastInsertId();//recupere lid genere
    //inserer dans formateur utiliser le meme id de user
        $stmtForm = $conn->prepare("INSERT INTO formateurs (id, nom, email) VALUES (?, ?, ?)");
        $stmtForm->execute([$user_id, $nom, $email]);

        if (!empty($matieres_ids)) {
            $stmtMat = $conn->prepare("INSERT INTO formateurs_matieres (formateur_id, matiere_id) VALUES (?, ?)");
            //inserer matier
            foreach ($matieres_ids as $m_id) {
                 $stmtMat->execute([$user_id, $m_id]); 
                 }
        }

        $conn->commit();//tout reeusi valider
        $msg = "<div class='alert alert-success'>Formateur enregistré avec succès.</div>";
    } catch (Exception $e) {
        $conn->rollBack();//erreur
        $msg = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}
//metre a jour users
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nom = htmlspecialchars($_POST['nom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $matieres_ids = $_POST['matieres'] ?? [];

    try {
        $conn->beginTransaction();

        $sqlUser = "UPDATE users SET nom=?, email=? ";
        $paramsUser = [$nom, $email];
        if (!empty($_POST['password'])) {
            $sqlUser .= ", password=? ";
            $paramsUser[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        $sqlUser .= " WHERE id=?";
        $paramsUser[] = $id;
        $conn->prepare($sqlUser)->execute($paramsUser);
//metre ajour formateur
        $conn->prepare("UPDATE formateurs SET nom=?, email=? WHERE id=?")->execute([$nom, $email, $id]);

        $conn->prepare("DELETE FROM formateurs_matieres WHERE formateur_id=?")->execute([$id]);
        if (!empty($matieres_ids)) {
            $stmt = $conn->prepare("INSERT INTO formateurs_matieres (formateur_id, matiere_id) VALUES (?, ?)");
            foreach ($matieres_ids as $m_id) { $stmt->execute([$id, $m_id]); }
        }

        $conn->commit();
        $msg = "<div class='alert alert-info'>Modifications enregistrées.</div>";
    } catch (Exception $e) {
        $conn->rollBack();
        $msg = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
    }
}
//supprimer de user et formateur
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    $conn->prepare("DELETE FROM formateurs WHERE id=?")->execute([$id]);
    header("Location: formateurs.php");
    exit;
}

// --- 2. RÉCUPÉRATION DES DONNÉES ---

$sql = "SELECT f.*, GROUP_CONCAT(m.nom SEPARATOR ', ') as liste_matieres, GROUP_CONCAT(m.id) as ids_matieres
        FROM formateurs f
        LEFT JOIN formateurs_matieres fm ON f.id = fm.formateur_id
        LEFT JOIN matieres m ON fm.matiere_id = m.id
        GROUP BY f.id ORDER BY f.nom ASC";
$formateurs = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);


include "../includes/sidebar_admin.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Formateurs | Iziform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* BODY & GENERAL */
        body { background-color: #121212; color: #e0e0e0; }
        h3.fw-bold { 
            background: linear-gradient(90deg, #6a11cb, #2575fc); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
        }

        /* CARDS */
        .card { background-color: #1e1e2f; border-radius: 10px; border: none; }
        .card-body { background-color: #1e1e2f; color: #e0e0e0; }

        /* ALERTS */
        .alert { border-radius: 8px; }

        /* BUTTONS */
        .btn-primary { background-color: #2575fc; border-color: #2575fc; }
        .btn-primary:hover { background-color: #1a5edb; border-color: #1a5edb; }
        .btn-success { background-color: #28a745; border-color: #28a745; }
        .btn-success:hover { background-color: #218838; border-color: #1e7e34; }

        /* TABLE */
        .table-dark { background-color: #1e1e2f; color: #e0e0e0; }
        .table-dark thead { background: #2c2c3e; color: #fff; }
        .table-dark tbody tr { background-color: #1e1e2f; color: #e0e0e0; }
        .table-dark tbody tr:hover { background-color: #2a2a40; }

        /* BADGES */
        .matiere-badge { font-size: 0.75rem; border-radius: 12px; padding: 4px 10px; background: #33334d; color: #fff; border: 1px solid #444; margin:2px; }

        /* CHECKBOX LIST */
        .checkbox-list { max-height: 150px; overflow-y: auto; border: 1px solid #444; padding: 10px; border-radius: 6px; background: #1e1e2f; }

        /* FORMS */
        .form-control, .form-select { background-color: #2a2a40; color: #e0e0e0; border: 1px solid #444; }
        .form-control:focus, .form-select:focus { background-color: #2a2a40; color: #fff; box-shadow: none; border-color: #2575fc; }

        /* MODAL */
        .modal-content { background-color: #1e1e2f; color: #e0e0e0; }
        .modal .form-control { background-color: #2a2a40; color: #e0e0e0; border: 1px solid #444; }
        .modal .form-control:focus { background-color: #2a2a40; color: #fff; border-color: #2575fc; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="bi bi-people-fill"></i> Formateurs</h3>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="collapse" data-bs-target="#addForm">
            <i class="bi bi-plus-circle"></i> Nouveau Formateur
        </button>
    </div>

    <?= $msg ?>

    <!-- FORMULAIRE AJOUT -->
    <div class="collapse mb-4" id="addForm">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nom complet</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Matières assignées</label>
                        <div class="checkbox-list row m-0">
                            <?php foreach ($matieres as $m): ?>
                                <div class="col-md-3 form-check">
                                    <input class="form-check-input" type="checkbox" name="matieres[]" value="<?= $m['id'] ?>" id="m<?= $m['id'] ?>">
                                    <label class="form-check-label" for="m<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12 text-end">
                        <button name="add" class="btn btn-success px-4">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABLE FORMATEURS -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Identité</th>
                        <th>Email</th>
                        <th>Matières</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                            <!--pour chaque formateur trouve dans la bd -->
                    <?php foreach ($formateurs as $f): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($f['nom']) ?></td>
                        <td><?= htmlspecialchars($f['email']) ?></td>
                        <td>
                            <?php 
                            //la gestiion de matier 
                            if (!empty($f['liste_matieres'])) {
                                $mats = explode(', ', $f['liste_matieres']);//separer plusieur matier entre vergule 
                                foreach($mats as $mat) echo "<span class='badge matiere-badge'>$mat</span> ";
                            } else { echo "<small class='text-muted'>Aucune matière</small>"; }
                            ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $f['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce formateur ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODALS DÉPLACÉS EN DEHORS DU TABLEAU -->
<?php foreach ($formateurs as $f): ?><!--fentre invisibe pour chque prof-->
<div class="modal fade" id="editModal<?= $f['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Éditer le formateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($f['nom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($f['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Changer le mot de passe</label>
                        <input type="password" name="password" class="form-control" placeholder="Laisser vide pour garder l'actuel">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Matières</label>
                        <div class="checkbox-list">
                            <?php 
                            $checked_ids = explode(',', $f['ids_matieres'] ?? '');
                            foreach ($matieres as $m): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="matieres[]" value="<?= $m['id'] ?>" id="ed_<?= $f['id'] ?>_m<?= $m['id'] ?>" <?= in_array($m['id'], $checked_ids) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ed_<?= $f['id'] ?>_m<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="edit" class="btn btn-primary w-100">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>