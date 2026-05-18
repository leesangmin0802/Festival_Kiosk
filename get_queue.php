<?php
// get_queue.php
include 'db.php';

$stmt = $pdo->prepare("SELECT * FROM kiosk_queue ORDER BY created_at ASC");
$stmt->execute();
$queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($queue);
?>