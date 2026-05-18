<?php
include 'db.php';

// 전화번호 자동 하이픈(-) 추가 함수
function format_phone_number($phone) {
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    if ($length == 11) {
        return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
    } else if ($length == 10) {
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
    } else {
        return $phone; // 형식에 맞지 않으면 원본 반환
    }
}

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$experience = $_POST['experience'] ?? '';

// 필수 정보 확인
if (empty($name) || empty($phone) || empty($experience)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '이름, 전화번호, 체험종목은 필수입니다.']);
    exit;
}

// --- 마감 기능 ---
$status_key = ($experience === '머그컵') ? 'mug_status' : 'arduino_status';
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmt->execute([$status_key]);
$experience_status = $stmt->fetchColumn();

if ($experience_status === 'closed') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '선택하신 체험은 현재 마감되었습니다.']);
    exit;
}
// --- 마감 기능 끝 ---

try {
    $formatted_phone = format_phone_number($phone); // 전화번호 형식 변환

    $stmt = $pdo->prepare("INSERT INTO kiosk_queue (name, phone, experience) VALUES (?, ?, ?)");
    $stmt->execute([$name, $formatted_phone, $experience]);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => '예약이 완료되었습니다.']);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '데이터베이스 오류가 발생했습니다.']);
}
?>