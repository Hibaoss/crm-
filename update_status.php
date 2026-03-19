<?php
// ⚠️ Ce fichier est inclus depuis le dashboard
// donc $pdo est déjà disponible
if (!isset($pdo)) {
    require_once __DIR__ . "/../../config/db.php";
}

/*
 RÈGLE CRM :
 - Statut manuel (Fidèle / Inactif) = PRIORITAIRE
 - Auto-update UNIQUEMENT pour les clients "Nouveau"
*/

$clients = $pdo->query("
    SELECT id 
    FROM clients 
    WHERE statut = 'Nouveau'
")->fetchAll();

foreach ($clients as $c) {
    $id = $c['id'];

    // Dernière interaction + nombre
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as nb,
            MAX(date_interaction) as last_interaction
        FROM interactions
        WHERE client_id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $nbInteractions  = $data['nb'];
    $lastInteraction = $data['last_interaction'];

    // Aucun changement par défaut
    $newStatus = "Nouveau";

    if ($lastInteraction) {
        $days = (time() - strtotime($lastInteraction)) / 86400;

        if ($days > 30) {
            $newStatus = "Inactif";
        } elseif ($nbInteractions >= 2) {
            $newStatus = "Fidèle";
        }
    }

    // 🔒 Mise à jour uniquement si changement réel
    if ($newStatus !== "Nouveau") {
        $stmtUpdate = $pdo->prepare("
            UPDATE clients 
            SET statut = ?
            WHERE id = ?
        ");
        $stmtUpdate->execute([$newStatus, $id]);
    }
}
