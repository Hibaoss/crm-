<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

/* Récupération client */
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    header("Location: index.php");
    exit;
}

$success = "";
$error = "";

/* Mise à jour */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom       = trim($_POST['nom']);
    $email     = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $statut    = $_POST['statut'];

    if ($nom && $email && $telephone && $statut) {
        $stmt = $pdo->prepare("
            UPDATE clients 
            SET nom=?, email=?, telephone=?, statut=?
            WHERE id=?
        ");
        $stmt->execute([$nom, $email, $telephone, $statut, $id]);

        $success = "Client mis à jour avec succès ✅";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
    } else {
        $error = "Tous les champs sont obligatoires";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Client | CRM PRO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 600px;">

    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fa fa-user-edit"></i> Modifier le client
            </h5>
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
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="nom" class="form-control"
                           value="<?= htmlspecialchars($client['nom']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($client['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                           value="<?= htmlspecialchars($client['telephone']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select" required>
                        <option value="Nouveau" <?= $client['statut']=="Nouveau"?'selected':'' ?>>Nouveau</option>
                        <option value="Fidèle" <?= $client['statut']=="Fidèle"?'selected':'' ?>>Fidèle</option>
                        <option value="Inactif" <?= $client['statut']=="Inactif"?'selected':'' ?>>Inactif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

</body>
</html>
