<?php
require "../includes/auth.php";
require "../includes/db.php";

check_auth('admin');

// 1. Récupération des Statistiques
$stats = [
    'students' => $conn->query("SELECT COUNT(DISTINCT student_id) FROM inscriptions")->fetchColumn(),
    'trainers' => $conn->query("SELECT COUNT(*) FROM formateurs")->fetchColumn(),
    'classes'  => $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
    'revenue'  => $conn->query("SELECT IFNULL(SUM(montant_paye),0) FROM paiements")->fetchColumn()
];

// 2. Récupération des Derniers paiements
$query = "SELECT u.nom, u.prenom, COALESCE(c.nom, 'N/A') AS classe, p.montant_paye, p.date_paiement
          FROM paiements p
          JOIN inscriptions i ON p.inscription_id = i.id
          JOIN users u ON i.student_id = u.id
          LEFT JOIN classes c ON i.classe_id = c.id
          ORDER BY p.date_paiement DESC LIMIT 5";
$last_payments = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);


include "../includes/sidebar_admin.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IZIFORM | Dashboard Admin</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0b1220;
            --card-bg: #111827;
            --border-color: #1f2937;
            --text-muted: #94a3b8;
            --accent-green: #10b981;
        }

        body {
            background-color: var(--bg-dark) !important;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        /* --- HEADER PRINCIPAL --- */
        .welcome-banner {
            background: linear-gradient(135deg, #111827 0%, #1e293b 100%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            position: relative;
        }

        .welcome-banner h2 {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .date-badge {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            font-weight: 600;
        }

        /* --- STAT CARDS --- */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }

        /* Bordures hautes colorées */
        .border-blue { border-top: 4px solid #3b82f6; }
        .border-green { border-top: 4px solid #10b981; }
        .border-yellow { border-top: 4px solid #f59e0b; }
        .border-pink { border-top: 4px solid #ec4899; }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            margin: 10px 0;
        }

        .view-link {
            font-size: 0.8rem;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
        }

        /* --- SECTIONS BAS --- */
        .content-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
        }

        .table { color: #e2e8f0; }
        .table thead th { 
            border: none; 
            color: var(--text-muted); 
            font-size: 0.7rem; 
            text-transform: uppercase;
        }
        .table td { border-color: #1f2937; vertical-align: middle; padding: 15px 10px; }

        .btn-all {
            background: var(--accent-green);
            color: #000;
            font-weight: 700;
            border-radius: 50px;
            padding: 5px 20px;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .action-item {
            background: #1f2937;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: 0.2s;
        }

        .action-item:hover {
            background: #334155;
            transform: scale(1.02);
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid p-4">

    <div class="welcome-banner mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">🚀 <span style="color:white">Tableau de</span> <span style="color:var(--accent-green)">Bord</span></h2>
            <p class="text-muted mb-0">Bienvenue, <span class="text-success fw-bold">Admin Iziform</span> — Voici l'état de votre centre de formation.</p>
        </div>
        <div class="date-badge px-3 py-2">
            <i class="bi bi-calendar3 me-2"></i> <?= date('d M. Y • H:i') ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card border-blue h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="stat-label">Étudiants Actifs</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">+12%</span>
                </div>
                <div class="stat-value"><?= $stats['students'] ?></div>
                <p class="text-muted small mb-3">Inscriptions validées</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="icon-box text-primary"><i class="bi bi-mortarboard-fill fs-5"></i></div>
                    <a href="inscriptions.php" class="view-link">Voir détails <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card border-green h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="stat-label">Formateurs</span>
                    <span class="badge bg-success bg-opacity-10 text-success">+5%</span>
                </div>
                <div class="stat-value"><?= $stats['trainers'] ?></div>
                <p class="text-muted small mb-3">Enseignants actifs</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="icon-box text-success"><i class="bi bi-person-video3 fs-5"></i></div>
                    <a href="formateurs.php" class="view-link text-success">Voir détails <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card border-yellow h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="stat-label">Classes</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning">+8%</span>
                </div>
                <div class="stat-value"><?= $stats['classes'] ?></div>
                <p class="text-muted small mb-3">Sessions en cours</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="icon-box text-warning"><i class="bi bi-pc-display fs-5"></i></div>
                    <a href="classes.php" class="view-link text-warning">Voir détails <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card border-pink h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="stat-label">Revenus Totaux</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger">+18%</span>
                </div>
                <div class="stat-value"><?= number_format($stats['revenue'], 2, '.', ' ') ?> <small style="font-size: 1rem">DT</small></div>
                <p class="text-muted small mb-3">Cumul des paiements</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="icon-box text-danger"><i class="bi bi-wallet2 fs-5"></i></div>
                    <a href="paiements.php" class="view-link text-danger">Voir détails <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-activity text-success me-2"></i> Flux Financiers Récents</h5>
                    <a href="paiements.php" class="btn-all">Tout voir</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ÉTUDIANT</th>
                                <th>CLASSE</th>
                                <th>HEURE</th>
                                <th class="text-end">MONTANT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($last_payments)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Aucun paiement enregistré</td></tr>
                            <?php else: foreach ($last_payments as $p): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-25"><?= htmlspecialchars($p['classe']) ?></span></td>
                                    <td class="text-muted"><?= date('H:i', strtotime($p['date_paiement'])) ?></td><!--strtoime transformer date dans la base en php pour php comprendre-->
                                    <td class="text-end fw-bold text-success">+ <?= number_format($p['montant_paye'], 2) ?> DT</td><!--afficher de montant -->
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card h-100">
                <h5 class="fw-bold mb-4">⚡ Actions Rapides</h5>
                
                <a href="inscriptions.php" class="action-item">
                    <div class="icon-box bg-primary bg-opacity-25 text-primary me-3"><i class="bi bi-person-plus-fill"></i></div>
                    <div>
                        <div class="fw-bold">Inscrire un étudiant</div>
                        <div class="small text-muted">Nouvelle inscription</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="paiements.php" class="action-item">
                    <div class="icon-box bg-success bg-opacity-25 text-success me-3"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <div class="fw-bold">Nouveau Paiement</div>
                        <div class="small text-muted">Encaisser des frais</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="classes.php" class="action-item">
                    <div class="icon-box bg-warning bg-opacity-25 text-warning me-3"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="fw-bold">Planning Classes</div>
                        <div class="small text-muted">Gérer les horaires</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>