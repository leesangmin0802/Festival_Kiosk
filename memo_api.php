<?php
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'shared_memo'");
    $stmt->execute();
    $memo = $stmt->fetchColumn();
    echo json_encode(['memo' => $memo]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $memo = $data['memo'] ?? '';

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'shared_memo'");
    $stmt->execute([$memo]);
    echo json_encode(['success' => true]);
}
?>