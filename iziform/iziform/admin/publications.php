<?php
session_start();
require "../includes/db.php";
require "../includes/auth.php";

// Vérifier que l'utilisateur est admin
check_auth('admin');

// Récupérer tous les formateurs et admin (pour choix auteur)
$users_stmt = $conn->query("SELECT id, nom, prenom, role FROM users WHERE role IN ('admin', 'formateur') ORDER BY nom");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une nouvelle publication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre'], $_POST['contenu'], $_POST['auteur_id'])) {
    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);
    $auteur_id = intval($_POST['auteur_id']);

    if (!empty($titre) && !empty($contenu)) {
        $stmt = $conn->prepare("INSERT INTO publications (titre, contenu, auteur_id, date_publication) VALUES (?, ?, ?, NOW())");
        $stmt->execute([htmlspecialchars($titre, ENT_QUOTES, 'UTF-8'), htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'), $auteur_id]);
        header("Location: publications.php");
        exit;
    }
}

// Supprimer une publication
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM publications WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: publications.php");
    exit;
}

// Récupérer toutes les publications
$pubs = $conn->query("
    SELECT p.id, p.titre, p.contenu, p.date_publication, u.nom, u.prenom, u.role
    FROM publications p
    JOIN users u ON p.auteur_id = u.id
    ORDER BY p.date_publication DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Publications | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #121212;
    color: #e0e0e0;
}

/* Cards */
.card {
    background-color: #1e1e2f;
    color: #e0e0e0;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,.3);
}

/* Card headers */
.card-header {
    background: linear-gradient(135deg, #198754, #0d6efd);
    color: #fff;
    font-weight: 600;
    border-radius: 15px 15px 0 0;
}

/* Form controls */
.form-control, .form-select, textarea {
    background-color: #2a2a40;
    color: #e0e0e0;
    border: 1px solid #444;
}
.form-control:focus, .form-select:focus, textarea:focus {
    border-color: #2575fc;
    box-shadow: 0 0 0 0.2rem rgba(37,117,252,.25);
    background-color: #2a2a40;
    color: #fff;
}

/* Buttons */
.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}
.btn-success:hover {
    background-color: #1e7e34;
    border-color: #1e7e34;
}
.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
.btn-danger:hover {
    background-color: #b02a37;
    border-color: #b02a37;
}

/* Small text */
.text-muted {
    color: #a0a0a0 !important;
}

/* Card content spacing */
.card-body p, .card-body small {
    color: #e0e0e0;
}
</style>
</head>
<body>

<?php include "../includes/sidebar_admin.php"; ?>

<div class="container p-4">

    <h3 class="mb-4">📰 Publications</h3>

    <!-- Formulaire ajout publication -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">➕ Ajouter une publication</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" name="titre" id="titre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="contenu" class="form-label">Contenu</label>
                    <textarea name="contenu" id="contenu" rows="4" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="auteur_id" class="form-label">Auteur</label>
                    <select name="auteur_id" id="auteur_id" class="form-select" required>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom'] . ' (' . $u['role'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Publier</button>
            </form>
        </div>
    </div>

    <!-- Liste des publications -->
    <?php foreach ($pubs as $p): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($p['titre']) ?></h5>
                <p class="card-text"><?= nl2br(htmlspecialchars($p['contenu'])) ?></p>
                <small class="text-muted">
                    Publié par <?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?> (<?= htmlspecialchars($p['role']) ?>)
                    le <?= date("d/m/Y H:i", strtotime($p['date_publication'])) ?>
                </small>
                <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger float-end" onclick="return confirm('Supprimer cette publication ?')">Supprimer</a>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>