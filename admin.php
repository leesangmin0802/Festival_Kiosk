<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>체험자 데이터베이스</title>
<style>
/* 폰트 적용 */
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

body { font-family:'LineSeed', sans-serif; margin:0; padding:2rem; background-color:#f8f9fa; }
h1 { color:#004a99; font-weight:700; font-size:2.5rem; margin-bottom:1.5rem; }
#search-bar { margin-bottom:1.5rem; display:flex; gap:1rem; align-items:center; }
#search-bar input { padding:0.8rem 1rem; font-size:1rem; border-radius:10px; border:1px solid #ccc; transition: 0.3s; }
#search-bar input:focus { border-color:#004a99; box-shadow:0 0 8px rgba(0, 74, 153, 0.2); outline: none; }

table { width:100%; border-collapse:collapse; background:white; border-radius:12px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.07); }
th, td { padding:1rem; text-align:center; border-bottom:1px solid #eee; }
th { background-color:#004a99; color:white; font-weight: 700; }
tr:last-child td { border-bottom: none; }
tr:hover { background-color: #f5faff; }

.status-cell span { padding: 0.3rem 0.6rem; border-radius: 8px; font-weight: 700; color: white; }
.status-cell .status-waiting { background-color: #ffc107; } /* 대기중 */
.status-cell .status-done { background-color: #28a745; } /* 체험완료 */
.status-cell .status-canceled { background-color: #dc3545; } /* 취소 */
.status-cell .status-doing { background-color: #8b35dcff; } /* 체험중 */
.status-cell .status-cupend { background-color: #101dd4ff; } /* 수령대기중 */
.status-cell .status-cupabsent { background-color: #414141ff; } /* 수령부재중 */
.status-cell .status-expabsent { background-color: #414141ff; } /* 체험부재중 */

.change-btn { background-color:#555; color:white; border:none; border-radius:8px; padding:0.4rem 0.8rem; cursor:pointer; margin-left:1rem; transition: background-color 0.3s; }
.change-btn:hover { background-color:#333; }

/* 모달 스타일 */
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:2rem; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.2); width:90%; max-width:400px; text-align:center; }
.modal-content h2 { margin-top:0; color:#004a99; }
.modal-content select { width:100%; padding:0.8rem; font-size:1.2rem; margin:1.5rem 0; border-radius: 8px; border: 1px solid #ccc; }
.modal-buttons { display:flex; gap:1rem; justify-content:center; }
.modal-buttons button { flex:1; padding:0.8rem; font-size:1rem; border:none; border-radius:10px; cursor:pointer; transition: 0.3s; }
.modal-save { background-color:#004a99; color:white; }
.modal-save:hover { background-color:#003366; }
.modal-cancel { background-color:#ccc; color:#333; }
.modal-cancel:hover { background-color:#bbb; }

/* 전체화면 버튼 */
#fullscreenBtn { position: fixed; bottom: 20px; right: 20px; background-color: transparent; border: none; color: transparent; cursor: pointer; z-index: 9999; width: 50px; height: 50px; }

</style>
</head>
<body>
<h1>체험자 데이터베이스</h1>

<div id="search-bar">
    <input type="text" id="search-name" placeholder="이름 검색">
    <input type="text" id="search-phone" placeholder="전화번호 검색">
</div>

<table id="queue-table">
    <thead>
        <tr>
            <th>순번</th><th>이름</th><th>전화번호</th><th>식별번호</th><th>체험종목</th><th>예약시간</th><th>상태</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div id="status-modal" class="modal-overlay">
    <div class="modal-content">
        <h2>상태 변경</h2>
        <input type="hidden" id="modal-queue-id">
        <select id="modal-status-select">
            <option value="대기중">대기중</option>
            <option value="체험완료">체험완료</option>
            <option value="취소">취소</option>
            <option value="체험중">체험중</option>
            <option value="수령대기중">수령대기중</option>
            <option value="수령부재중">수령부재중</option>
            <option value="체험부재중">체험부재중</option>
        </select>
        <div class="modal-buttons">
            <button class="modal-cancel" onclick="closeStatusModal()">취소</button>
            <button class="modal-save" onclick="saveStatusChange()">저장</button>
        </div>
    </div>
</div>

<button id="fullscreenBtn">전체화면</button>

<script>
    // 가나다 정렬 기능 (체험종목 기준)
let sortAsc = true; // 오름차순/내림차순 토글용 변수

document.addEventListener('DOMContentLoaded', () => {
    const headerCells = document.querySelectorAll('#queue-table th');
    headerCells.forEach((th, index) => {
        if (th.textContent.trim() === '체험종목') {
            th.style.cursor = 'pointer';
            th.title = '클릭하면 가나다순 정렬';
            th.addEventListener('click', () => {
                const tableBody = document.querySelector('#queue-table tbody');
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    const expA = a.children[index].textContent.trim();
                    const expB = b.children[index].textContent.trim();
                    return sortAsc ? expA.localeCompare(expB, 'ko') : expB.localeCompare(expA, 'ko');
                });

                // 정렬된 행 다시 테이블에 추가
                tableBody.innerHTML = '';
                rows.forEach(r => tableBody.appendChild(r));

                sortAsc = !sortAsc; // 다음 클릭 시 반대 방향 정렬
            });
        }
    });
});

function loadQueue() {
    const name = document.getElementById('search-name').value;
    const phone = document.getElementById('search-phone').value;
    
    fetch(`admin_data.php?name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`)
    .then(res => res.json())
    .then(data => {
        const tableBody = document.querySelector('#queue-table tbody');
        tableBody.innerHTML = ''; // 기존 내용 초기화
        
        data.forEach(row => {
            let statusClass = '';
            if (row.status === '대기중') statusClass = 'status-waiting';
            else if (row.status === '체험중') statusClass = 'status-doing';
            else if (row.status === '체험완료') statusClass = 'status-done';
            else if (row.status === '취소') statusClass = 'status-canceled';
            else if (row.status === '수령대기중') statusClass = 'status-cupend';
            else if (row.status === '수령부재중') statusClass = 'status-cupabsent';
            else if (row.status === '체험부재중') statusClass = 'status-expabsent';

            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.id}</td>
                <td>${row.name}</td>
                <td>${row.phone}</td>
                <td>${row.name}${row.phone_tail}</td>
                <td>${row.experience}</td>
                <td>${row.reservation_time}</td>
                <td class="status-cell">
                    <span class="${statusClass}">${row.status}</span>
                    <button class="change-btn" onclick="openStatusModal(${row.id}, '${row.status}')">변경</button>
                </td>
                `;
            tableBody.appendChild(tr);
        });
    });
}

function openStatusModal(id, currentStatus) {
    document.getElementById('modal-queue-id').value = id;
    document.getElementById('modal-status-select').value = currentStatus;
    document.getElementById('status-modal').style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('status-modal').style.display = 'none';
}

function saveStatusChange() {
    const id = document.getElementById('modal-queue-id').value;
    const status = document.getElementById('modal-status-select').value;
    
    fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${status}`
    }).then(() => {
        closeStatusModal();
        loadQueue(); // 변경 후 목록 새로고침
    });
}

// 전체화면 기능
const fullscreenBtn = document.getElementById("fullscreenBtn");
fullscreenBtn.addEventListener("click", () => {
    if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
        document.documentElement.webkitRequestFullscreen();
    }
});

// 주기적 새로고침 및 검색 이벤트 리스너
setInterval(loadQueue, 5000); // 5초마다
document.addEventListener('DOMContentLoaded', loadQueue);
document.getElementById('search-name').addEventListener('input', loadQueue);
document.getElementById('search-phone').addEventListener('input', loadQueue);
</script>
</body>
</html>