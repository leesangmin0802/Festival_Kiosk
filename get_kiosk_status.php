<?php
include 'db.php';

try {
    // 1. 대기자 수 계산
    $stmt = $pdo->query("SELECT experience, COUNT(*) AS count FROM kiosk_queue WHERE status='체험대기중' GROUP BY experience");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $waiting_counts = ['머그컵' => 0, '아두이노' => 0];
    foreach ($data as $row) {
        $waiting_counts[$row['experience']] = (int)$row['count'];
    }

    // 2. 마감 상태 가져오기
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('mug_status', 'arduino_status')");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. 최종 데이터 조합
    $response = [
        'mug' => [
            'count' => $waiting_counts['머그컵'],
            'status' => $settings['mug_status'] ?? 'open'
        ],
        'arduino' => [
            'count' => $waiting_counts['아두이노'],
            'status' => $settings['arduino_status'] ?? 'open'
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database query failed']);
}
?>