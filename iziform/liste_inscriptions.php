<?php
require_once 'connexion.php';

/* ================== STATISTIQUES ================== */
$stats = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(statut_paiement = 'paye') AS paye,
        SUM(statut_paiement = 'en_attente') AS attente
    FROM inscriptions
")->fetch();

/* ================== RECHERCHE ================== */
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
</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="mb-4 text-success">📊 Dashboard Inscriptions – IZiForM</h2>

<!-- ================== STATISTIQUES ================== -->
<div class="row mb-4 text-center">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Total inscriptions</h5>
        <h2><?= $stats['total'] ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm text-success">
      <div class="card-body">
        <h5>Payées</h5>
        <h2><?= $stats['paye'] ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm text-warning">
      <div class="card-body">
        <h5>En attente</h5>
        <h2><?= $stats['attente'] ?></h2>
      </div>
    </div>
  </div>
</div>

<!-- ================== RECHERCHE ================== -->
<form method="GET" class="mb-3">
  <div class="input-group">
    <input type="text" name="search" class="form-control"
           placeholder="Rechercher par nom ou email"
           value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-success">🔍 Rechercher</button>
  </div>
</form>

<!-- ================== TABLEAU ================== -->
<div class="table-responsive">
<table class="table table-bordered table-hover table-striped">
<thead class="table-success">
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
       class="btn btn-sm btn-danger">
      ❌
    </a>

    <?php if ($row['statut_paiement'] === 'en_attente'): ?>
      <a href="payer_inscription.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
        💳
      </a>
    <?php endif; ?>

    <!-- BOUTON IMPRIMER -->
    <button type="button" class="btn btn-sm btn-primary"
  onclick="imprimerEtudiant(<?= $row['id'] ?>)">
  🖨️ Imprimer
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
const form = document.getElementById('validationForm');

form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    try {
        const response = await fetch('/SITE/iziform/liste_inscriptions.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            // La sauvegarde est OK → lancer l'impression
            window.print();
        } else {
            alert("Erreur lors de l'enregistrement !");
        }
    } catch (err) {
        console.error(err);
        alert("Erreur lors de l'enregistrement !");
    }
});







</script>

<script>
function imprimerEtudiant(id) {
    const inscriptions = <?php echo json_encode($inscriptions); ?>;
    const etudiant = inscriptions.find(e => e.id == id);

    if (!etudiant) {
        alert("Étudiant introuvable !");
        return;
    }

    // Ouvrir la popup
    const popup = window.open('', '_blank', 'width=800,height=600');

    // Contenu HTML simplifié pour fiabilité
    const html = `
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8">
      <title>Validation Inscription - ${etudiant.nom} ${etudiant.prenom}</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
      <style>
        body { font-family: 'Poppins', sans-serif; padding: 30px; }
        h2 { color: #00c853; margin-bottom: 20px; }
        .info-user p { font-size: 1.1rem; line-height: 1.5; margin: 6px 0; }
        .badge-success { background-color: #00c853 !important; }
        .badge-warning { background-color: #ffc107 !important; }
      </style>
    </head>
    <body>
      <h2>Validation de l'inscription</h2>
      <div class="info-user">
        <p><b>Nom :</b> ${etudiant.nom}</p>
        <p><b>Prénom :</b> ${etudiant.prenom}</p>
        <p><b>Email :</b> ${etudiant.email}</p>
        <p><b>Téléphone :</b> ${etudiant.telephone}</p>
        <p><b>Formation :</b> ${etudiant.formation}</p>
        <p><b>Type de formation :</b> ${etudiant.type_formation}</p>
        <p><b>Statut paiement :</b> <span class="badge ${etudiant.statut_paiement === 'paye' ? 'badge-success' : 'badge-warning'}">
          ${etudiant.statut_paiement}</span></p>
        <p><b>Date inscription :</b> ${etudiant.date_inscription}</p>
      </div>
      <button onclick="window.print()" class="btn btn-success mt-3">🖨️ Imprimer</button>
    </body>
    </html>
    `;

    popup.document.open();
    popup.document.write(html);
    popup.document.close();
}
</script>


</body>
</html>
