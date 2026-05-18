<?php
include 'db.php';

try {
    // '대기중' 상태인 예약을 체험 종목별로 카운트
    $stmt = $pdo->query("SELECT experience, COUNT(*) AS count FROM kiosk_queue WHERE status='대기중' GROUP BY experience");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 기본값 설정
    $waiting_counts = ['머그컵' => 0, '아두이노' => 0];

    // DB에서 가져온 값으로 업데이트
    foreach ($data as $row) {
        $waiting_counts[$row['experience']] = (int)$row['count'];
    }

    // 결과를 JSON 형태로 반환
    header('Content-Type: application/json');
    echo json_encode($waiting_counts);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database query failed']);
}
?>