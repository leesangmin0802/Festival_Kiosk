<?php
include 'db.php';

$experience = $_POST['experience'] ?? '';
$status = $_POST['status'] ?? ''; // 'open' or 'closed'

if (in_array($experience, ['mug', 'arduino']) && in_array($status, ['open', 'closed'])) {
    $key = $experience . '_status';
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$status, $key]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>