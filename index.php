<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Mise à jour auto des statuts
include "../clients/scripts/update_status.php";

/* STATISTIQUES */
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$clientsFideles = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='Fidèle'")->fetchColumn();
$clientsInactifs = $pdo->query("SELECT COUNT(*) FROM clients WHERE statut='Inactif'")->fetchColumn();
$totalInteractions = $pdo->query("SELECT COUNT(*) FROM interactions")->fetchColumn();

/* CLIENTS À RISQUE (inactifs >30 jours ou jamais contactés) */
$clientsRisque = $pdo->query("
    SELECT c.id, c.nom, MAX(i.date_interaction) AS derniere_interaction
    FROM clients c
    LEFT JOIN interactions i ON c.id = i.client_id
    GROUP BY c.id
    HAVING derniere_interaction IS NULL 
       OR derniere_interaction < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY derniere_interaction ASC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>CRM PRO | Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    font-family: Poppins, sans-serif;
    background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    color: #fff;
}
.card { border-radius: 18px; box-shadow: 0 15px 40px rgba(0,0,0,.2); }
.bg-blue{background:#1f8ef1}
.bg-green{background:#2dce89}
.bg-orange{background:#fb6340}
.bg-red{background:#f5365c}
.badge { border-radius: 15px; padding:5px 10px; font-size:0.85rem; }
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <span class="navbar-brand fw-bold">CRM PRO</span>
    <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
</nav>

<div class="container my-4">

    <!-- KPI -->
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card p-4 bg-blue text-center">Clients<br><h2><?= $totalClients ?></h2></div></div>
        <div class="col-md-3"><div class="card p-4 bg-green text-center">Fidèles<br><h2><?= $clientsFideles ?></h2></div></div>
        <div class="col-md-3"><div class="card p-4 bg-orange text-center">Inactifs<br><h2><?= $clientsInactifs ?></h2></div></div>
        <div class="col-md-3"><div class="card p-4 bg-red text-center">Interactions<br><h2><?= $totalInteractions ?></h2></div></div>
    </div>

    <!-- ALERTES CLIENTS À RISQUE -->
    <?php if(count($clientsRisque) > 0): ?>
    <div class="card p-4 mb-4">
        <h5>⚠️ Clients à rappeler (inactifs >30 jours)</h5>
        <ul class="list-group list-group-flush">
            <?php foreach($clientsRisque as $cr): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($cr['nom']) ?>
                    <span class="badge bg-danger">
                        <?= $cr['derniere_interaction'] ? date('d/m/Y', strtotime($cr['derniere_interaction'])) : 'Jamais contacté' ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ACTIONS RAPIDES -->
    <div class="card p-4 mb-4">
        <h5>Actions rapides</h5>
        <div class="d-flex gap-3 flex-wrap">
            <a href="../clients/add.php" class="btn btn-success">
                <i class="fa fa-plus"></i> Ajouter client
            </a>
            <a href="../clients/index.php" class="btn btn-primary">
                <i class="fa fa-users"></i> Tous les clients
            </a>
            <a href="../interactions/add.php" class="btn btn-warning">
                <i class="fa fa-plus-circle"></i> Ajouter interaction
            </a>
            <a href="../clients/stats.php" class="btn btn-dark">
                <i class="fa fa-chart-pie"></i> Statistiques
            </a>
        </div>
    </div>

    <!-- GRAPHIQUE -->
    <div class="card p-4 mb-4">
        <h5>Répartition des clients</h5>
        <canvas id="clientsChart"></canvas>
    </div>

</div>

<script>
new Chart(document.getElementById('clientsChart'), {
    type: 'doughnut',
    data: {
        labels: ['Fidèles','Inactifs','Autres'],
        datasets: [{
            data: [<?= $clientsFideles ?>, <?= $clientsInactifs ?>, <?= max($totalClients-($clientsFideles+$clientsInactifs),0) ?>],
            backgroundColor: ['#2dce89','#fb6340','#1f8ef1']
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom', labels:{ color:'#fff' } } }
    }
});
</script>

</body>
</html>
