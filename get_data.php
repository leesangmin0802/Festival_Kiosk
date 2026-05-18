<?php
include 'db.php';

// 대기열 목록 가져오기
$queue_stmt = $pdo->prepare("SELECT * FROM kiosk_queue ORDER BY created_at ASC");
$queue_stmt->execute();
$queue = $queue_stmt->fetchAll(PDO::FETCH_ASSOC);

// 설정값들 (메모, 체험 상태) 가져오기
$settings_stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
$settings_stmt->execute();
$settings_raw = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$settings = [
    'shared_memo' => $settings_raw['shared_memo'] ?? '',
    'mug_status' => $settings_raw['mug_status'] ?? 'open',
    'arduino_status' => $settings_raw['arduino_status'] ?? 'open'
];

// 최종 데이터를 JSON 형태로 합치기
$response = [
    'queue' => $queue,
    'settings' => $settings
];

header('Content-Type: application/json');
echo json_encode($response);
?>