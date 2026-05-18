<?php
include 'db.php';

// 페이지 최초 로딩 시 대기자 수를 가져옵니다. (이후 JavaScript가 실시간으로 업데이트)
$stmt = $pdo->query("SELECT experience, COUNT(*) AS count FROM kiosk_queue WHERE status='대기중' GROUP BY experience");
$waitingData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$waiting = ['머그컵' => 0, '아두이노' => 0];
foreach ($waitingData as $row) {
    $waiting[$row['experience']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>체험 예약 키오스크</title>
<style>
/* 폰트 적용 */
@font-face {
    font-family: 'LineSeed';
    src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_11-01@1.0/LINESeedKR-Th.woff2') format('woff2');
    font-weight: 100; font-display: swap;
}
@font-face {
    font-family: 'LineSeed';
    src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_11-01@1.0/LINESeedKR-Rg.woff2') format('woff2');
    font-weight: 400; font-display: swap;
}
@font-face {
    font-family: 'LineSeed';
    src: url('https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_11-01@1.0/LINESeedKR-Bd.woff2') format('woff2');
    font-weight: 700; font-display: swap;
}

/* 기본 애니메이션 */
@keyframes pulse-glow {
    0% { transform: scale(1); text-shadow: 0 0 5px rgba(0, 80, 150, 0.2); }
    50% { transform: scale(1.05); text-shadow: 0 0 15px rgba(0, 80, 150, 0.5); }
    100% { transform: scale(1); text-shadow: 0 0 5px rgba(0, 80, 150, 0.2); }
}

/* 새로운 테두리 애니메이션 */
@keyframes border-reveal {
    0% {
        box-shadow: 0 10px 30px rgba(0, 74, 153, 0.1),
                    0 0 0 0px rgba(0, 74, 153, 0.5); /* 초기 그림자 */
    }
    50% {
        box-shadow: 0 10px 30px rgba(0, 74, 153, 0.1),
                    0 0 0 8px rgba(0, 74, 153, 0.5),
                    0 0 0 16px rgba(0, 74, 153, 0.2); /* 확장되는 테두리 */
    }
    100% {
        box-shadow: 0 10px 30px rgba(0, 74, 153, 0.1),
                    0 0 0 0px rgba(0, 74, 153, 0); /* 원상 복구 */
    }
}


/* 기본 스타일 */
body, html { margin:0; padding:0; width:100%; height:100%; font-family:'LineSeed', sans-serif; background-color:#f8f9fa; overflow: hidden; }
.container { display:flex; flex-direction:row; height:100vh; }
.left-panel { flex:1; background: linear-gradient(135deg, #e9f5ff, #ffffff); display:flex; flex-direction:column; justify-content:center; align-items:center; padding:2rem; }
.right-panel { flex:1; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:3rem; background-color:#ffffff; box-shadow:-10px 0 30px rgba(0,0,0,0.03); }

h1 { font-weight:700; color:#004a99; margin-bottom:1.5rem; font-size: 2.5rem; }
.card {
    position: relative;
    width:85%; max-width:400px;
    background:white;
    padding:2rem;
    border-radius:24px;
    box-shadow:0 10px 30px rgba(0, 74, 153, 0.1); /* 기본 그림자 */
    margin:1rem 0; text-align:center;
    transition: transform 0.3s, box-shadow 0.3s; /* 기본 전환 효과 */
}
.card.animate-border {
    animation: border-reveal 1s ease-out forwards; /* 애니메이션 적용 */
}
.card:hover { transform: translateY(-5px); box-shadow:0 15px 35px rgba(0, 74, 153, 0.15); } /* 호버 효과 */

.card h2 { margin:0 0 0.5rem 0; font-size:1.8rem; color:#004a99; font-weight: 700; }
.card .waiting-info { position: relative; display: flex; align-items: baseline; justify-content: center; color: #333; }
.card .count { font-size:4rem; font-weight:700; color:#004a99; animation: pulse-glow 2s infinite ease-in-out; }
.card .unit { font-size: 1.5rem; margin-left: 0.5rem; }
.card .wait-time { font-size: 1rem; color: #555; margin-top: 0.5rem; font-weight: 400; height: 1.2rem; }

/* 입력 필드 및 버튼 */
.input-group { width:90%; max-width:450px; }
input { font-family:'LineSeed', sans-serif; font-size:1.5rem; margin:0.5rem 0; padding:1rem; width:100%; border-radius:16px; border:1px solid #ddd; box-sizing: border-box; transition: 0.3s; }
input:focus { outline:none; border-color:#004a99; box-shadow:0 0 10px rgba(0, 74, 153, 0.2); }

.experience-selector { display:flex; gap:1rem; margin:1rem 0; width: 90%; max-width: 450px; }
.exp-btn { flex:1; font-family:'LineSeed', sans-serif; background-color:#f0f0f0; color:#555; border:2px solid #ddd; border-radius:16px; padding:1rem; font-size:1.2rem; cursor:pointer; transition:0.3s; }
.exp-btn.selected { background-color:#e0f0ff; color:#004a99; border-color:#004a99; font-weight: 700; }

#reserveBtn { font-family:'LineSeed', sans-serif; background-color:#004a99; color:white; border:none; cursor:pointer; border-radius:16px; transition:0.3s; width:90%; max-width: 450px; font-size:1.8rem; padding:1rem; margin-top: 1rem; }
#reserveBtn:hover { background-color:#003366; transform: scale(1.02); }

/* 전체화면 버튼 */
#fullscreenBtn { position: fixed; bottom: 20px; right: 20px; background-color: transparent; border: none; color: transparent; cursor: pointer; z-index: 9999; width: 50px; height: 50px; }

/* 커스텀 확인 모달 스타일 */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:2rem 2.5rem; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.2); width:90%; max-width:450px; text-align:center; }
.modal-content h2 { margin-top:0; color:#004a99; font-size: 1.8rem; }
.modal-content p { margin: 1rem 0 2rem 0; line-height: 1.6; color: #333; }
.modal-buttons { display:flex; gap:1rem; justify-content:center; }
.modal-buttons button { flex:1; padding:0.8rem; font-size:1.1rem; border:none; border-radius:12px; cursor:pointer; transition: 0.3s; font-family: 'LineSeed', sans-serif; font-weight: 700; }
#confirm-agree-btn { background-color:#004a99; color:white; }
#confirm-agree-btn:hover { background-color:#003366; }
#confirm-cancel-btn { background-color:#ccc; color:#333; }
#confirm-cancel-btn:hover { background-color:#bbb; }

@media(max-width:768px){ .container { flex-direction:column; } }

.red {
    color: red !important;
}
</style>
</head>
<body>
<div class="container">
    <div class="left-panel">
        <h1>경상고등학교<br>체험부스 대기자 현황</h1>
        <div class="card" id="card-mug">
            <h2>머그컵 만들기(마감)</h2>
            <div class="waiting-info">
                <span id="waiting-mug" class="count"><?= $waiting['머그컵'] ?></span>
                <span class="unit">명</span>
            </div>
            <p id="mug-wait-time" class="wait-time">&nbsp;</p>
        </div>
        <div class="card" id="card-arduino">
            <h2>AI 코딩 디바이스</h2>
            <div class="waiting-info">
                <span id="waiting-arduino" class="count"><?= $waiting['아두이노'] ?></span>
                <span class="unit">명</span>
            </div>
            <p id="arduino-wait-time" class="wait-time">&nbsp;</p>
        </div>
    </div>
    <div class="right-panel">
        <h1>접수 신청</h1>
        <div class="input-group">
            <input type="text" id="name" placeholder="이름">
            <input type="text" id="phone" placeholder="전화번호">
        </div>
        <div class="experience-selector">
            <button class="exp-btn" data-value="머그컵" disabled>AI 아트상품<br>(머그컵 만들기)</button>
            <button class="exp-btn" data-value="아두이노">AI코딩 디바이스<br>(아두이노 체험)</button>
        </div>
        <button id="reserveBtn" onclick="reserve()">접수하기</button>
    </div>
</div>

<button id="fullscreenBtn">전체화면</button>

<div id="confirm-modal" class="modal-overlay">
    <div class="modal-content">
        <h2>개인정보 수집 동의</h2>
        <p>개인정보 수집에 동의하시겠습니까?<br>예약이 완료되면 문자로 안내를 받을 수 있습니다.</p>
        <div class="modal-buttons">
            <button id="confirm-cancel-btn">취소</button>
            <button id="confirm-agree-btn">동의 및 접수</button>
        </div>
    </div>
</div>

<script>
let selectedExperience = '';
let currentWaitingCounts = { '머그컵': <?= $waiting['머그컵'] ?>, '아두이노': <?= $waiting['아두이노'] ?> }; // 초기값 설정
let reservationData = {};

// --- 이벤트 리스너 설정 ---
document.addEventListener('DOMContentLoaded', () => {
    // 체험 버튼 선택
    document.querySelectorAll('.exp-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.exp-btn').forEach(btn => btn.classList.remove('selected'));
            button.classList.add('selected');
            selectedExperience = button.dataset.value;
        });
    });

    // 전체화면 버튼
    document.getElementById("fullscreenBtn").addEventListener("click", toggleFullScreen);
    
    // 모달 버튼
    document.getElementById('confirm-cancel-btn').addEventListener('click', closeConfirmModal);
    document.getElementById('confirm-agree-btn').addEventListener('click', processReservation);

    // 초기 데이터 로드 및 주기적 업데이트
    updateWaitingCounts();
    setInterval(updateWaitingCounts, 3000);
});


// --- 함수 정의 ---

// 실시간 대기자 수 업데이트
function updateWaitingCounts() {
    fetch('get_counts.php')
        .then(response => response.json())
        .then(data => {
            const newMugCount = data['머그컵'] || 0;
            const newArduinoCount = data['아두이노'] || 0;

            // 머그컵 숫자 업데이트
            if (newMugCount > currentWaitingCounts['머그컵']) {
                triggerBorderAnimation('card-mug');
            }
            const mugElem = document.getElementById('waiting-mug');
            mugElem.innerText = newMugCount;

            // 숫자가 10 초과 시 빨간색 클래스 적용
            if (newMugCount > 10) {
                mugElem.classList.add('red');
            } else {
                mugElem.classList.remove('red');
            }

            document.getElementById('mug-wait-time').innerText = newMugCount > 0 ? `(예상 대기시간 ${newMugCount * 6}분)` : '\u00A0';
            currentWaitingCounts['머그컵'] = newMugCount;

            // 아두이노 숫자 업데이트
            if (newArduinoCount > currentWaitingCounts['아두이노']) {
                triggerBorderAnimation('card-arduino');
            }
            const arduinoElem = document.getElementById('waiting-arduino');
            arduinoElem.innerText = newArduinoCount;

            if (newArduinoCount > 10) {
                arduinoElem.classList.add('red');
            } else {
                arduinoElem.classList.remove('red');
            }

            document.getElementById('arduino-wait-time').innerText = newArduinoCount > 0 ? `(예상 대기시간 ${newArduinoCount * 14}분)` : '\u00A0';
            currentWaitingCounts['아두이노'] = newArduinoCount;
        })
        .catch(error => console.error('대기자 수 업데이트 중 오류 발생:', error));
}



// 예약 버튼 클릭 시 처리
function reserve() {
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;

    if (!name || !phone) {
        return;
    }
    if (!selectedExperience) {
        return;
    }

    // 예약 데이터를 임시 저장하고 모달 열기
    reservationData = { name, phone, experience: selectedExperience };
    openConfirmModal();
}

// 모달 열기/닫기
function openConfirmModal() { document.getElementById('confirm-modal').style.display = 'flex'; }
function closeConfirmModal() { document.getElementById('confirm-modal').style.display = 'none'; }

// 실제 예약 처리 (모달에서 '동의' 클릭 시)
function processReservation() {
    const formData = new URLSearchParams();
    formData.append('name', reservationData.name);
    formData.append('phone', reservationData.phone);
    formData.append('experience', reservationData.experience);

    fetch('reserve.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 입력 필드 초기화
            document.getElementById('name').value = "";
            document.getElementById('phone').value = "";
            document.querySelectorAll('.exp-btn').forEach(btn => btn.classList.remove('selected'));
            selectedExperience = '';
            reservationData = {}; // 임시 데이터 초기화
            // 예약 성공 후 즉시 업데이트 (테두리 애니메이션 효과를 위해)
            updateWaitingCounts(); 
        } else {
            alert("예약에 실패했습니다: " + data.message);
        }
    })
    .catch(error => console.error('예약 처리 중 오류 발생:', error))
    .finally(() => {
        closeConfirmModal();
    });
}

// 전체화면 함수
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
        });
    } else {
        document.exitFullscreen();
    }
}

// 테두리 애니메이션 트리거 함수
function triggerBorderAnimation(cardId) {
    const cardElement = document.getElementById(cardId);
    if (cardElement) {
        // 기존 애니메이션 클래스가 있다면 제거 (재실행을 위해)
        cardElement.classList.remove('animate-border');
        // 강제로 리플로우 발생 (애니메이션 재시작을 위해)
        void cardElement.offsetWidth; 
        // 애니메이션 클래스 추가
        cardElement.classList.add('animate-border');
    }
}

function increaseNumber() {
  const p = document.getElementById('number');
  let value = parseInt(p.textContent); // 현재 숫자 가져오기
  value += 1; // 1 증가
  p.textContent = value;

  if (value > 10) {
    p.classList.add('red'); // 10 초과 시 빨간색
  } else {
    p.classList.remove('red'); // 10 이하 시 원래 색
  }
}
</script>
</body>
</html>