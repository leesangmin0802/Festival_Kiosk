 <?php
$servername = "satronix.co.kr";
$username = "kiosk";
$password = "iRg5B#_;_5Lh;q5wOj-&1";
$dbname = "kiosk";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB 연결 실패: " . $conn->connect_error);
}

$sql = "SELECT experience FROM kiosk_queue";
$result = $conn->query($sql);

// 🔹 쿼리 실패 시 에러 출력
if (!$result) {
    die("쿼리 오류: " . $conn->error);
}

$experience_counts = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $exp = $row['experience'];
        if (isset($experience_counts[$exp])) {
            $experience_counts[$exp]++;
        } else {
            $experience_counts[$exp] = 1;
        }
    }
} else {
    echo "데이터가 없습니다.";
}

// 테이블 출력
echo "<table border='1' cellspacing='0' cellpadding='8' style='border-collapse: collapse; font-family: Arial;'>";
echo "<tr style='background-color: #f2f2f2;'><th>체험명</th><th>신청자 수</th></tr>";

foreach ($experience_counts as $exp => $count) {
    echo "<tr><td>$exp</td><td>$count</td></tr>";
}
echo "</table>";

$conn->close();
?>
