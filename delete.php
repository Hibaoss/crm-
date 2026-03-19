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

/* Vérification si le client existe */
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if ($client) {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id=?");
    $stmt->execute([$id]);
}

/* Retour à la liste */
header("Location: index.php");
exit;
