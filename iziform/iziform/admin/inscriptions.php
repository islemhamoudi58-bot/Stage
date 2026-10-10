<?php
require_once 'connexion.php';
include "../includes/sidebar_admin.php";

// STATISTIQUES 
$stats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(statut_paiement = 'paye') AS paye,
        SUM(statut_paiement = 'en_attente') AS attente
    FROM inscriptions
")->fetch();

//RECHERCHE 
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM inscriptions
        WHERE nom LIKE :search OR email LIKE :search
        ORDER BY date_inscription DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$inscriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Inscriptions – IZiForM</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* 🌙 BACKGROUND */
body {
    background: linear-gradient(135deg, #0b1220, #0f1b2e) !important;
    color: #e6edf3;
}

/* 🟡 TITRE */
h2 {
    background: linear-gradient(90deg, #00ffcc, #00aaff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
}

/* 🧩 CARDS */
.card {
    background: #111c2e !important;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    color: #fff;
}

/* 📊 COLORS */
.text-success {
    color: #00ffcc !important;
}

.text-warning {
    color: #ffaa00 !important;
}

/* 🔘 BUTTON */
.btn-success {
    background: linear-gradient(90deg, #00ffcc, #00aaff);
    border: none;
    color: #000;
}

/* 🔍 INPUT */
.form-control {
    background: #0f1b2e;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}

.form-control:focus {
    border-color: #00ffcc;
    box-shadow: 0 0 5px #00ffcc;
}

/* 📋 TABLE */
.table {
    color: #e6edf3;
    background: #111c2e;
}

.table thead {
    color: #00ffcc;
    background: transparent !important;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255,255,255,0.03);
}

.table-hover tbody tr:hover {
    background: rgba(255,255,255,0.05);
}

/* BADGES */
.badge.bg-success {
    background: #00ffcc !important;
    color: #000;
}

.badge.bg-warning {
    background: #ffaa00 !important;
    color: #000;
}

</style>
</head>

<body>

<div class="container mt-5">

<h2 class="mb-4">📊 Dashboard Inscriptions – IZiForM</h2>

<!-- STATS  -->
<div class="row mb-4 text-center">
  <div class="col-md-4">
    <div class="card p-3">
      <h5>Total inscriptions</h5>
      <h2><?= $stats['total'] ?></h2>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 text-success">
      <h5>Payées</h5>
      <h2><?= $stats['paye'] ?></h2>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-3 text-warning">
      <h5>En attente</h5>
      <h2><?= $stats['attente'] ?></h2>
    </div>
  </div>
</div>
<!--SEARCH -->
<form method="GET" class="mb-3">
  <div class="input-group">
    <input type="text" name="search" class="form-control"
           placeholder="Rechercher par nom ou email"
           value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-success">🔍 Rechercher</button>
  </div>
</form>

<!-- TABLE  -->
<div class="table-responsive">
<table class="table table-bordered table-hover table-striped">

<thead>
<tr>
  <th>ID</th>
  <th>Nom</th>
  <th>Prénom</th>
  <th>Email</th>
  <th>Téléphone</th>
  <th>Formation</th>
  <th>Type</th>
  <th>Paiement</th>
  <th>Date</th>
  <th>Actions</th>
</tr>
</thead>

<tbody>
<!--affiche les inscription dans tableau-->
<?php if (count($inscriptions) > 0): ?>
<?php foreach ($inscriptions as $row): ?>
<tr>
  <td><?= $row['id'] ?></td>
  <td><?= htmlspecialchars($row['nom']) ?></td>
  <td><?= htmlspecialchars($row['prenom']) ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= htmlspecialchars($row['telephone']) ?></td>
  <td><?= htmlspecialchars($row['formation']) ?></td>
  <td><?= $row['type_formation'] ?></td>



  <td>
    <span class="badge bg-<?= $row['statut_paiement'] === 'paye' ? 'success' : 'warning' ?>">
      <?= $row['statut_paiement'] ?>
    </span>
  </td>


  <td><?= $row['date_inscription'] ?></td>

  <td class="text-center">
    <a href="delete_inscription.php?id=<?= $row['id'] ?>"
       onclick="return confirm('Supprimer cette inscription ?')"
       class="btn btn-sm btn-danger">❌</a>

    <?php if ($row['statut_paiement'] === 'en_attente'): ?>
      <a href="payer_inscription.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">💳</a>
    <?php endif; ?>

    <button type="button" class="btn btn-sm btn-primary"
            onclick="imprimerEtudiant(<?= $row['id'] ?>)">
      🖨️
    </button>
  </td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
  <td colspan="10" class="text-center">Aucune inscription trouvée</td>
</tr>
<?php endif; ?>
</tbody>

</table>
</div>

</div>

<script>
function imprimerEtudiant(id) {
    const inscriptions = <?php echo json_encode($inscriptions); ?>;//convertir le tableau en json
    const etudiant = inscriptions.find(e => e.id == id);//find chercher letudiant dont lid

    if (!etudiant) return;//si non trouver arrete le fonction

    const popup = window.open('', '_blank');//ouvrir nouvelle page _blank nouvelle onglet 

    popup.document.write(`
    <html>
    <head>
      <title>Impression</title>
      <style>body{font-family:Arial;padding:20px}</style>
    </head>
    <body>
      <h2>Fiche Étudiant</h2>
      <p><b>Nom :</b> ${etudiant.nom}</p>
      <p><b>Prénom :</b> ${etudiant.prenom}</p>
      <p><b>Email :</b> ${etudiant.email}</p>
      <p><b>Téléphone :</b> ${etudiant.telephone}</p>
      <p><b>Formation :</b> ${etudiant.formation}</p>
      <p><b>Paiement :</b> ${etudiant.statut_paiement}</p>
      <button onclick="window.print()">Imprimer</button>
    </body>
    </html>
    `);
}
</script>

</body>
</html>