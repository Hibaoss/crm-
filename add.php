<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Récupérer l’ID du client depuis l’URL
$client_id = $_GET['client_id'] ?? null;
if (!$client_id) {
    header("Location: ../clients/index.php");
    exit;
}

// Récupérer les infos du client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();
if (!$client) {
    header("Location: ../clients/index.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($type) {
        $stmt = $pdo->prepare("INSERT INTO interactions (client_id, type, notes, date_interaction) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$client_id, $type, $notes])) {
            $success = "Interaction ajoutée avec succès ✅";

            // Mettre à jour le statut client automatiquement
            $stmtUpdate = $pdo->prepare("
                UPDATE clients SET statut = 
                CASE 
                    WHEN (SELECT COUNT(*) FROM interactions WHERE client_id=? ) >= 2 THEN 'Fidèle'
                    ELSE 'Nouveau'
                END
                WHERE id=?
            ");
            $stmtUpdate->execute([$client_id, $client_id]);
        } else {
            $error = "Erreur lors de l'ajout. Veuillez réessayer.";
        }
    } else {
        $error = "Veuillez choisir le type d'interaction";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Nouvelle interaction | CRM PRO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
.card { border-radius: 15px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
.btn-success { background-color: #2dce89; border: none; }
.btn-success:hover { background-color: #28b77b; }
.alert { border-radius: 12px; }
</style>
</head>
<body>

<div class="container mt-5" style="max-width: 600px;">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <i class="fa fa-plus"></i> Ajouter une interaction pour <strong><?= htmlspecialchars($client['nom']) ?></strong>
        </div>

        <div class="card-body">

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Type d'interaction</label>
                    <select name="type" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <option value="Appel">Appel</option>
                        <option value="Email">Email</option>
                        <option value="Message">Message</option>
                        <option value="Visite">Rendez-vous</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes / détails</label>
                    <textarea name="notes" class="form-control" placeholder="Ex: Appel pour relance facture..." rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="../clients/index.php" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Ajouter
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

</body>
</html>
