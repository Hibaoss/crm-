<?php
session_start();
require_once "../config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && sha1($password) === $user['mot_de_passe']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Identifiants incorrects";
        }
    } else {
        $error = "Tous les champs sont obligatoires";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CRM | Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }
        .login-left {
            background: linear-gradient(135deg, #1f4037, #99f2c8);
            color: white;
            padding: 40px;
        }
        .login-left h2 {
            font-weight: 600;
        }
        .login-right {
            padding: 40px;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-login {
            background: #1f4037;
            color: #fff;
            border-radius: 8px;
            padding: 10px;
            font-weight: 500;
        }
        .btn-login:hover {
            background: #16342d;
        }
        .brand {
            font-weight: 600;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="login-box row g-0">
    <!-- LEFT SIDE -->
    <div class="col-md-5 login-left d-flex flex-column justify-content-center">
        <h2 class="brand">CRM PRO</h2>
        <p class="mt-3">
            Plateforme intelligente de gestion et d’optimisation de la relation client.
        </p>
        <ul class="mt-4">
            <li>✔ Suivi des interactions clients</li>
            <li>✔ Analyse de fidélité</li>
            <li>✔ Actions marketing ciblées</li>
        </ul>
    </div>

    <!-- RIGHT SIDE -->
    <div class="col-md-7 login-right">
        <h4 class="mb-4">Connexion à votre espace</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control" placeholder="exemple@entreprise.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" name="login" class="btn btn-login w-100 mt-3">
                Se connecter
            </button>
        </form>

        <p class="text-muted mt-4" style="font-size: 13px;">
            © <?= date('Y') ?> CRM Professionnel – Tous droits réservés
        </p>
    </div>
</div>

</body>
</html>
