<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

/* Statuts */
$total = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$fideles = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='Fidèle'")->fetchColumn();
$inactifs = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='Inactif'")->fetchColumn();
$nouveaux = max($total - ($fideles + $inactifs), 0);

/* Clients récents (7 jours) */
$recent = $pdo->query("
    SELECT DATE(date_inscription) as jour, COUNT(*) as nb
    FROM clients
    WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY jour
    ORDER BY jour ASC
")->fetchAll();

$jours = [];
$nbClients = [];

foreach ($recent as $r) {
    $jours[] = $r['jour'];
    $nbClients[] = $r['nb'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Statistiques Clients | CRM Pro</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
}
.card {
    border-radius: 18px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.12);
}
</style>
</head>

<body>

<div class="container mt-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">📊 Statistiques Clients</h3>
            <small class="text-muted">Analyse globale de votre base clients</small>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Retour Clients
        </a>
    </div>

    <div class="row g-4">

        <!-- Répartition -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">Répartition des statuts</h5>
                <canvas id="statutsChart"></canvas>
            </div>
        </div>

        <!-- Clients récents -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">Clients récents (7 jours)</h5>
                <canvas id="recentChart"></canvas>
            </div>
        </div>

    </div>

</div>

<script>
/* Statuts */
new Chart(document.getElementById('statutsChart'), {
    type: 'doughnut',
    data: {
        labels: ['Fidèles', 'Inactifs', 'Nouveaux'],
        datasets: [{
            data: [<?= $fideles ?>, <?= $inactifs ?>, <?= $nouveaux ?>],
            backgroundColor: ['#2dce89', '#f5365c', '#1f8ef1']
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});

/* Clients récents */
new Chart(document.getElementById('recentChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($jours) ?>,
        datasets: [{
            label: 'Nouveaux clients',
            data: <?= json_encode($nbClients) ?>,
            backgroundColor: '#1f8ef1'
        }]
    }
});
</script>

</body>
</html>
