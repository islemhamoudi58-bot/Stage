<?php
require "../includes/auth.php";
require "../includes/db.php";

check_auth('admin');

$error = "";
$success = "";

/* --- 1. TRAITEMENT DES ACTIONS (PHP) --- */

// A. AJOUT D'UN NOUVEAU PAIEMENT
if (isset($_POST['add_payment'])) {//si bouton ajouter paiment a ete cliquer
    $ins_id = $_POST['inscription_id'];//recuperation de donnes
    $m_total = (float)$_POST['montant_total'];
    $m_paye = (float)$_POST['montant_paye'];
    $mois = $_POST['mois_paiement'];
    $date = $_POST['date_paiement'];
    $statut = ($m_paye >= $m_total) ? 'payé' : 'partiel';//calculer le status

    try {
        $stmt = $conn->prepare("INSERT INTO paiements (inscription_id, montant_total, montant_paye, mois_paiement, date_paiement, statut) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ins_id, $m_total, $m_paye, $mois, $date, $statut]);
        $success = "Nouveau paiement enregistré avec succès !";
    } catch (Exception $e) { 
        $error = "Erreur lors de l'ajout : " . $e->getMessage(); 
    }
}

// B. MODIFICATION RAPIDE (MONTANT PAYÉ)
if (isset($_POST['update_payment'])) {
    $paiement_id = $_POST['paiement_id'];
    $nouveau_paye = (float)$_POST['nouveau_paye'];
    $montant_total = (float)$_POST['montant_total'];
    $statut = ($nouveau_paye >= $montant_total) ? 'payé' : 'partiel';

    try {
        $stmt = $conn->prepare("UPDATE paiements SET montant_paye = ?, statut = ? WHERE id = ?");
        $stmt->execute([$nouveau_paye, $statut, $paiement_id]);
        $success = "Mise à jour effectuée.";
    } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
}

// C. SUPPRESSION D'UN PAIEMENT
if (isset($_POST['delete_payment'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM paiements WHERE id = ?");
        $stmt->execute([$_POST['paiement_id']]);
        $success = "Le paiement a été supprimé.";
    } catch (Exception $e) { 
        $error = "Erreur de suppression."; 
    }
}

/* --- 2. FILTRES ET RECHERCHE --- */
$search = isset($_GET['search']) ? "%".$_GET['search']."%" : "%";//recuperer le filtre si remplit :%ali% dinon %tous
$f_mois = !empty($_GET['f_mois']) ? $_GET['f_mois'] : null;
$f_annee = !empty($_GET['f_annee']) ? $_GET['f_annee'] : date('Y');//si vide anne actuel
$f_classe = !empty($_GET['f_classe']) ? $_GET['f_classe'] : null;

$sql = "SELECT p.*, u.nom AS etudiant, u.telephone, c.nom AS classe_nom 
        FROM paiements p
        JOIN inscriptions i ON p.inscription_id = i.id
        JOIN users u ON i.student_id = u.id
        LEFT JOIN classes c ON i.classe_id = c.id
        WHERE (u.nom LIKE ?) AND YEAR(p.date_paiement) = ?";
//filtre dynamique ajouter seulement sil ont remplit et 
// ajouter la valeur ala liste $params[]
$params = [$search, $f_annee];
if ($f_mois) { $sql .= " AND p.mois_paiement = ?"; $params[] = $f_mois; }
if ($f_classe) { $sql .= " AND i.classe_id = ?"; $params[] = $f_classe; }
$sql .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$paiements = $stmt->fetchAll();

/* --- 3. DONNÉES POUR LES LISTES DÉROULANTES --- */
$total_encaisse = 0;//calculer commence a 0
foreach ($paiements as $p) { 
    $total_encaisse += $p['montant_paye']; //additioner le montant dns la totle encaise
    }

$classes = $conn->query("SELECT id, nom FROM classes ORDER BY nom ASC")->fetchAll();
$inscriptions = $conn->query("SELECT i.id, u.nom FROM inscriptions i JOIN users u ON i.student_id = u.id ORDER BY u.nom ASC")->fetchAll();
$mois_liste = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];


include "../includes/sidebar_admin.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Trésorerie | iziform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { background-color: #121212; color: #f0f0f0; }
    h2.gradient-title {
        background: linear-gradient(90deg, #00c6ff, #0072ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .card { background-color: #1e1e2f; color: #f0f0f0; border-radius: 12px; }
    .btn-primary { background: linear-gradient(90deg, #00c6ff, #0072ff); border: none; }
    .btn-primary:hover { opacity: 0.9; }
    .table-dark th, .table-dark td { border-color: #333; }
    .table-dark thead { background-color: #1a1a2e; }
    .reste-alerte { color: #e74c3c; font-weight: bold; }
    .reste-zero { color: #27ae60; font-weight: bold; }
    .form-control, .form-select { background-color: #2c2c3c; color: #f0f0f0; border: 1px solid #444; }
    .form-control:focus, .form-select:focus { border-color: #0072ff; box-shadow: 0 0 5px #0072ff; }
    .badge { background-color: #0072ff; }
    .btn-outline-dark { border-color: #444; color: #f0f0f0; }
    .btn-outline-dark:hover { background-color: #0072ff; color: #fff; }
</style>
</head>
<body>

<div class="container-fluid p-4">
    <div class="row align-items-center mb-4 no-print">
        <div class="col-md-6">
            <h2 class="fw-bold m-0 gradient-title"><i class="bi bi-journal-check"></i> Trésorerie</h2>
            <span class="badge shadow-sm">Total affiché : <?= number_format($total_encaisse,2) ?> DT</span>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-outline-dark me-2" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer</button>
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addForm"><i class="bi bi-plus-circle"></i> Nouveau Paiement</button>
        </div>
    </div>

    <?php if($success): ?><div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <!-- Formulaire ajout paiement -->
    <div class="collapse mb-4 no-print" id="addForm">
        <div class="card p-3 shadow-sm">
            <h5 class="fw-bold mb-3 text-primary">Ajouter un nouveau règlement</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold">Étudiant</label>
                    <select name="inscription_id" class="form-select" required>
                        <option value="">Choisir un étudiant...</option>
                        <?php foreach($inscriptions as $ins): ?>
                            <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Montant Total (DT)</label>
                    <input type="number" step="0.01" name="montant_total" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Montant Versé (DT)</label>
                    <input type="number" step="0.01" name="montant_paye" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Mois de</label>
                    <select name="mois_paiement" class="form-select">
                        <?php foreach($mois_liste as $m): ?>
                            <option value="<?= $m ?>"><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Date</label>
                    <input type="date" name="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#addForm">Annuler</button>
                    <button type="submit" name="add_payment" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table paiements -->
    <div class="card shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Étudiant</th>
                        <th>Mois</th>
                        <th>Total</th>
                        <th>Payé</th>
                        <th>Reste</th>
                        <th class="text-center no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($paiements as $p): $reste = $p['montant_total'] - $p['montant_paye']; ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['etudiant']) ?></div>
                                <div class="small text-muted"><?= $p['classe_nom'] ?? 'N/A' ?></div>
                            </td>
                            <td><?= $p['mois_paiement'] ?></td>
                            <td class="fw-bold"><?= number_format($p['montant_total'],2) ?> DT</td>
                            <td>
                                <form method="POST" class="d-flex gap-1 no-print">
                                    <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="montant_total" value="<?= $p['montant_total'] ?>">
                                    <input type="number" step="0.01" name="nouveau_paye" value="<?= $p['montant_paye'] ?>" class="form-control form-control-sm" style="width:80px;">
                                    <button type="submit" name="update_payment" class="btn btn-sm btn-success"><i class="bi bi-save"></i></button>
                                </form>
                                <span class="d-none d-print-block fw-bold text-success"><?= number_format($p['montant_paye'],2) ?> DT</span>
                            </td>
                            <td class="<?= $reste > 0 ? 'reste-alerte' : 'reste-zero' ?>"><?= number_format($reste,2) ?> DT</td>
                            <td class="text-center no-print">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border" onclick="printReceipt('<?= addslashes($p['etudiant']) ?>','<?= $p['telephone'] ?>','<?= $p['mois_paiement'] ?>','<?= $p['montant_paye'] ?>','<?= $reste ?>')">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Supprimer définitivement ce paiement ?');">
                                        <input type="hidden" name="paiement_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="delete_payment" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printReceipt(nom, tel, mois, paye, reste){
    const w = window.open('','', 'height=600,width=450');
    w.document.write('<html><head><title>Reçu iziform</title><style>body{font-family:"Courier New", monospace; width:300px; margin:auto;text-align:center;padding:10px;} .header{border-bottom:2px solid #000;margin-bottom:15px;} .details{text-align:left;font-size:14px;}.hr{border-bottom:1px dashed #000;margin:10px 0;}</style></head><body>');
    w.document.write('<div class="header"><h2>IZIFORM</h2><p>REÇU DE PAIEMENT</p></div>');
    w.document.write('<div class="details"><p><b>Candidat:</b> '+nom+'</p><p><b>Mois:</b> '+mois+'</p><div class="hr"></div><p><b>MONTANT PAYÉ: '+paye+' DT</b></p><p>Reste: '+reste+' DT</p></div><p style="font-size:10px;margin-top:20px;">Merci !</p></body></html>');
    w.document.close();
    setTimeout(()=>{ w.print(); w.close(); },500);
}
</script>

</body>
</html>