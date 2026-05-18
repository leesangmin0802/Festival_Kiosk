<?php
$host = "localhost";
$dbname = "kiosk";
$user = "kiosk"; // DB 사용자명
$pass = "password"; // DB 비밀번호

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}

// 체험 항목과 소요시간 설정
$durations = [
    "mug" => 15,      // 머그컵 만들기 15분
    "arduino" => 20   // 아두이노 체험 20분
];
$slot_capacity = 2; // 한 번에 체험 가능한 인원 수

// 체험별 대기 예상 시간 계산 함수
function estimate_wait_time($pdo, $type, $durations, $slot_capacity) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE type=? AND status='대기중'");
    $stmt->execute([$type]);
    $waiting = $stmt->fetchColumn();
    $slots_ahead = ceil($waiting / $slot_capacity);
    return $slots_ahead * $durations[$type];
}
?>
