<?php
include "../includes/sidebar_admin.php";//inclsion du sidebar admin

// ===== CONNEXION DB =====
$pdo = new PDO(
  "mysql:host=localhost;dbname=izi_contact;charset=utf8",
  "root",
  "",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);//lire les message depuis le base

// ===== RÉCUPÉRATION MESSAGES =====
$messages = $pdo->query(
  "SELECT * FROM commentaires ORDER BY date_envoi DESC"
);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin – Messages reçus</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>
body {
  background: #121212;
  color: #e0e0e0;
}

/* CARDS */
.admin-card {
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0,0,0,.3);
  background-color: #1e1e2f;
}

/* HEADER GRADIENT */
.admin-header {
  background: linear-gradient(135deg, #198754, #0d6efd);
  color: #fff;
  border-radius: 15px 15px 0 0;
}

/* TABLE */
.table {
  color: #e0e0e0;
}
.table-hover tbody tr:hover {
  background-color: #2a2a40;
}
.table thead {
  background-color: #2c2c3e;
  color: #fff;
}
.badge.bg-success {
  background-color: #28a745;
  color: #fff;
}

/* MESSAGE BOX */
.message-box {
  max-width: 420px;
  white-space: pre-wrap;
  color: #f0f0f0;
}

/* DATE */
.date {
  font-size: 0.85rem;
  color: #a0a0a0;
}

/* BUTTONS */
.btn-primary {
  background-color: #2575fc;
  border-color: #2575fc;
}
.btn-primary:hover {
  background-color: #1a5edb;
  border-color: #1a5edb;
}
.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}
.btn-danger:hover {
  background-color: #b02a37;
  border-color: #b02a37;
}
</style>
</head>

<body>

<div class="container py-5">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">
      <i class="fas fa-envelope-open-text me-2 text-success"></i>
      Messages reçus
    </h3>
  </div>

  <!-- CARD -->
  <div class="card admin-card">
    <div class="card-header admin-header text-center">
      <h5 class="mb-0">
        <i class="fas fa-inbox me-2"></i>
        Boîte de réception
      </h5>
    </div>

    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Formation</th>
            <th>Message</th>
            <th>Date</th>
           
          </tr>
        </thead>

        <tbody>
        <?php if ($messages->rowCount() == 0): ?>
          <tr>
            <td colspan="5" class="text-center py-4 text-muted">
              Aucun message reçu
            </td>
          </tr>
        <?php endif; ?>

        <?php foreach($messages as $m): ?><!--affiche chaque mesage dans ligne de tab-->
          <tr>
            <td class="fw-semibold">
              <?= htmlspecialchars($m['nom']." ".$m['prenom']) ?><!--protege contre attaque-->
            </td>

            <td>
              <span class="badge bg-success">
                <?= htmlspecialchars($m['formation']) ?>
              </span>
            </td>

            <td class="message-box">
              <?= nl2br(htmlspecialchars($m['message'])) ?>
            </td>

            <td class="date">
              <?= date("d/m/Y H:i", strtotime($m['date_envoi'])) ?>
            </td>

            <td class="text-center">

             

            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>

      </table>
    </div>
  </div>

</div>

</body>
</html>