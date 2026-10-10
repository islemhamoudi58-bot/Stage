<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

// 1. Sécurité : Vérifier le rôle étudiant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
// Filtrage par semestre (par défaut semestre 1)
$f_semestre = isset($_GET['f_semestre']) ? $_GET['f_semestre'] : '1';

/* RÉCUPÉRATION DES DONNÉES */
$query = "
    SELECT date_absence, absent
    FROM absences
    WHERE student_id = ?
    ORDER BY date_absence DESC
";
$stmt = $conn->prepare($query);
$stmt->execute([$student_id]);
$flux_absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul des statistiques pour le semestre sélectionné
$total_absences = 0;
foreach($flux_absences as $abs) {
    if($abs['absent'] == 1) $total_absences++;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi d'Appel | IZI FORM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --iziform-dark: #2c3e50; --iziform-blue: #3498db; }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }

        /* Sidebar & Layout */
        .main-content { margin-left: 250px; padding: 40px; transition: all 0.3s; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }

        /* Stats Cards */
        .stat-box { 
            background: white; border-radius: 15px; padding: 20px; 
            border-bottom: 4px solid var(--iziform-blue);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Table Styling */
        .table-container { 
            background: white; border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; 
        }
        .table thead { background: var(--iziform-dark); color: white; }
        .table th { padding: 18px; font-weight: 500; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        .table td { padding: 18px; vertical-align: middle; }

        /* Status Badges */
        .badge-status { 
            padding: 8px 16px; border-radius: 50px; font-weight: 600; 
            font-size: 0.75rem; min-width: 100px; display: inline-block;
        }
        .status-present { background: #e8f5e9; color: #2e7d32; }
        .status-absent { background: #ffebee; color: #c62828; }
        
        .date-cell { display: flex; align-items: center; gap: 12px; }
        .calendar-icon { background: #f0f4f8; padding: 10px; border-radius: 10px; color: var(--iziform-blue); }
    </style>
</head>
<body>

    <?php include "../includes/sidebar_student.php"; ?>

    <div class="main-content">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1" style="color: white;"> Mon Suivi de Présence</h2>
                <p style="color: white;"> Consultez votre assiduité en temps réel</p>            
            </div>
            <div class="col-md-6 text-md-end">
                <form method="GET" class="d-inline-flex gap-2 bg-white p-2 rounded-3 shadow-sm border">
                    <select name="f_semestre" class="form-select border-0 fw-bold" onchange="this.form.submit()">
                        <option value="1" <?= $f_semestre == '1' ? 'selected' : '' ?>>Semestre 1</option>
                        <option value="2" <?= $f_semestre == '2' ? 'selected' : '' ?>>Semestre 2</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-box d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted d-block mb-1 small uppercase fw-bold">Absences (S<?= $f_semestre ?>)</span>
                        <h3 class="fw-bold mb-0 <?= $total_absences > 3 ? 'text-danger' : 'text-dark' ?>"><?= $total_absences ?></h3>
                    </div>
                    <div class="fs-1 text-light"><i class="bi bi-exclamation-octagon"></i></div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($flux_absences) > 0): ?>
                            <?php foreach ($flux_absences as $row): ?>
                                <tr>
                                    <td>
                                        <div class="date-cell">
                                            <div class="calendar-icon d-none d-sm-block">
                                                <i class="bi bi-calendar3"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($row['date_absence'])) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['absent'] == 1): ?>
                                            <span class="badge-status status-absent">
                                                <i class="bi bi-x-circle me-1"></i> ABSENT
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status status-present">
                                                <i class="bi bi-check-circle me-1"></i> PRÉSENT
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" alt="No data" style="width: 80px; opacity: 0.5;">
                                    <p class="mt-3 text-muted">Aucune donnée trouvée pour le Semestre <?= $f_semestre ?>.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
