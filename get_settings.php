<?php
// get_settings.php
include 'db.php';

$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$settings_raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$settings = [
    'shared_memo' => $settings_raw['shared_memo'] ?? '',
    'mug_status' => $settings_raw['mug_status'] ?? 'open',
    'arduino_status' => $settings_raw['arduino_status'] ?? 'open'
];

header('Content-Type: application/json');
echo json_encode($settings);
?>