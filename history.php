<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// --- Filtres ---
$client_id = $_GET['client_id'] ?? '';
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$sql = "SELECT i.*, c.nom AS client_nom 
        FROM interactions i
        JOIN clients c ON i.client_id = c.id
        WHERE 1";
$params = [];

if (!empty($client_id)) {
    $sql .= " AND client_id = ?";
    $params[] = $client_id;
}

if (!empty($search)) {
    $sql .= " AND (c.nom LIKE ? OR i.commentaire LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($type)) {
    $sql .= " AND i.type = ?";
    $params[] = $type;
}

if (!empty($from_date)) {
    $sql .= " AND i.date_interaction >= ?";
    $params[] = $from_date;
}

if (!empty($to_date)) {
    $sql .= " AND i.date_interaction <= ?";
    $params[] = $to_date;
}

$sql .= " ORDER BY i.date_interaction DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$interactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Historique Interactions | CRM Pro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background:#f4f6f9; }
.card { border-radius:18px; box-shadow:0 20px 45px rgba(0,0,0,0.12); }
.table th { font-weight:600; cursor:pointer; }
.badge { border-radius:20px; padding:5px 10px; font-size:0.85rem; }
</style>
</head>
<body>
<div class="container mt-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">📝 Historique des interactions</h3>
    <a href="../clients/index.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Retour clients</a>
</div>

<!-- FILTRES -->
<form method="GET" class="card mb-4">
    <div class="card-body row g-3 align-items-end">
        <input type="hidden" name="client_id" value="<?= htmlspecialchars($client_id) ?>">
        <div class="col-md-3">
            <label class="form-label">Recherche</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Nom client ou commentaire">
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">Tous</option>
                <option value="Appel" <?= $type=='Appel'?'selected':'' ?>>Appel</option>
                <option value="Email" <?= $type=='Email'?'selected':'' ?>>Email</option>
                <option value="Message" <?= $type=='Message'?'selected':'' ?>>Message</option>
                <option value="Visite" <?= $type=='Visite'?'selected':'' ?>>Visite</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date min</label>
            <input type="date" name="from_date" value="<?= $from_date ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">Date max</label>
            <input type="date" name="to_date" value="<?= $to_date ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-dark w-100"><i class="fa fa-search"></i> Filtrer</button>
            <a href="history.php?client_id=<?= $client_id ?>" class="btn btn-outline-secondary w-100 mt-2">Réinitialiser</a>
        </div>
    </div>
</form>

<!-- TABLE -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="interactionsTable">
                <thead class="table-dark">
                    <tr>
                        <th onclick="sortTable(0)">Client</th>
                        <th onclick="sortTable(1)">Type</th>
                        <th onclick="sortTable(2)">Commentaire</th>
                        <th onclick="sortTable(3)">Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($interactions): foreach ($interactions as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['client_nom']) ?></td>
                        <td>
                            <?php 
                                $b='secondary';
                                if($i['type']=='Appel') $b='primary';
                                elseif($i['type']=='Email') $b='info';
                                elseif($i['type']=='Message') $b='warning';
                                elseif($i['type']=='Visite') $b='success';
                            ?>
                            <span class="badge bg-<?= $b ?>"><?= $i['type'] ?></span>
                        </td>
                        <td><?= htmlspecialchars($i['commentaire']) ?></td>
                        <td><?= $i['date_interaction'] ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $i['id'] ?>" class="btn btn-warning btn-sm me-1"><i class="fa fa-edit"></i></a>
                            <a href="delete.php?id=<?= $i['id'] ?>" onclick="return confirm('Supprimer cette interaction ?')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune interaction</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<script>
// Tri dynamique simple
function sortTable(n) {
    let table = document.getElementById("interactionsTable");
    let rows = Array.from(table.rows).slice(1);
    let asc = table.getAttribute("data-sort-"+n) !== "asc";
    rows.sort((a,b) => {
        let x = a.cells[n].innerText.toLowerCase();
        let y = b.cells[n].innerText.toLowerCase();
        if(!isNaN(Date.parse(x))) x = new Date(x);
        if(!isNaN(Date.parse(y))) y = new Date(y);
        return asc ? (x>y?1:-1) : (x<y?1:-1);
    });
    rows.forEach(r => table.tBodies[0].appendChild(r));
    table.setAttribute("data-sort-"+n, asc?"asc":"desc");
}
</script>

</body>
</html>
