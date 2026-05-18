<?php
include 'db.php';

// POST 방식으로 전달된 id와 status 값을 받습니다.
$id = $_POST['id'] ?? 0;
$status = $_POST['status'] ?? '';

// id가 유효하고 status 값이 비어있지 않은 경우에만 실행합니다.
if ($id > 0 && !empty($status)) {
    try {
        // SQL의 WHERE절을 통해 정확히 해당 id를 가진 행(사람)만 업데이트합니다.
        $stmt = $pdo->prepare("UPDATE kiosk_queue SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        // 성공 응답 (클라이언트 측에서 별도 처리는 안 함)
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // 실패 시 에러 응답
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // 파라미터가 잘못된 경우
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>