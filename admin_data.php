<?php
include 'db.php';

$name = $_GET['name'] ?? '';
$phone = $_GET['phone'] ?? '';
$experience = $_GET['experience'] ?? 'all'; // 'all', '머그컵', '아두이노'

$sql = "SELECT * FROM kiosk_queue";
$conditions = [];
$params = [];

if (!empty($name)) {
    $conditions[] = "name LIKE ?";
    $params[] = "%$name%";
}
if (!empty($phone)) {
    $conditions[] = "phone LIKE ?";
    $params[] = "%$phone%";
}
if ($experience !== 'all') {
    $conditions[] = "experience = ?";
    $params[] = $experience;
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($result);
?>