<?php
// データベース接続設定
$host = 'localhost'; // 環境に合わせて変更
$username = 'root'; // 環境に合わせて変更
$password = 'root'; // 環境に合わせて変更
$dbname = 'careercompass'; // データベース名

// データベースに接続
$conn = new mysqli($host, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 現在のログインユーザー情報を取得（ここでは仮の値を使用）
// 実際のシステムではセッションなどから取得
$current_user_id = 1;
$current_user_name = "田中 太郎";

// クライアントデータの取得
$sql_clients = "SELECT id, client_name AS name, date_of_birth AS dateOfBirth, enrollment_date AS enrollmentDate,
                  latest_life_status AS latestLifeStatus, latest_training_status AS latestTrainingStatus,
                  latest_job_hunting_status AS latestJobHuntingStatus, last_updated_at AS lastUpdated,
                  (SELECT name FROM users WHERE id = clients.last_updated_by_user_id) AS lastUpdatedBy
                  FROM clients";
$result_clients = $conn->query($sql_clients);

$clients = [];
if ($result_clients->num_rows > 0) {
    while ($row = $result_clients->fetch_assoc()) {
        $clients[] = $row;
    }
}

// 記録データの取得
$sql_records = "SELECT id, client_id AS clientId, record_date AS recordDate, record_type AS recordType,
                  details, (SELECT name FROM users WHERE id = records.recorded_by_user_id) AS recordedBy
                  FROM records ORDER BY record_date DESC";
$result_records = $conn->query($sql_records);

$records = [];
if ($result_records->num_rows > 0) {
    while ($row = $result_records->fetch_assoc()) {
        $records[] = $row;
    }
}

// 接続を閉じる
$conn->close();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareerCompass - 就労支援管理システム</title>
    <style>
        :root {
            --color-1: #1b1f3b;
            --color-2: #2b2d54;
            --color-3: #3b3d72;
            --color-4: #4c4f94;
            --color-5: #5c5ea3;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--color-1), var(--color-2));
            color: #fff;
            min-height: 100vh;
        }

        .header {
            background: var(--color-1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            font-weight: 500;
        }

        .logout-btn {
            background: var(--color-4);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--color-5);
            transform: translateY(-2px);
        }

        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--color-5);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }

        .clients-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-box {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .search-box::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .client-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .client-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .client-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .client-item.active {
            background: var(--color-4);
            border-color: var(--color-5);
        }

        .client-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .client-meta {
            font-size: 0.85rem;
            opacity: 0.8;
            display: flex;
            justify-content: space-between;
        }

        .client-details {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .detail-section {
            margin-bottom: 1.5rem;
        }

        .detail-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--color-5);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-content {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            line-height: 1.5;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .record-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid var(--color-4);
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .record-type {
            background: var(--color-4);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .record-type.life {
            background: #2ecc71;
        }

        .record-type.training {
            background: #3498db;
        }

        .record-type.job {
            background: #e74c3c;
        }

        .record-date {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .record-content {
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--color-4);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-5);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--color-2);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: white;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-5);
            box-shadow: 0 0 0 2px rgba(92, 94, 163, 0.3);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: var(--color-3);
            color: white;
            font-size: 0.9rem;
        }

        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
            min-height: 100px;
            resize: vertical;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .main-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo">CareerCompass</div>
        <div class="user-info">
            <span class="user-name" id="currentUser"><?= htmlspecialchars($current_user_name) ?></span>
            <button class="logout-btn" onclick="logout()">ログアウト</button>
        </div>
    </header>

    <div class="main-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalClients">
                    <?php echo count($clients); ?>
                </div>
                <div class="stat-label">総利用者数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="activeClients">
                    <?php echo count($clients); ?>
                </div>
                <div class="stat-label">アクティブ利用者</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayRecords">
                    <?php
                    $today_records_count = 0;
                    $today = date('Y-m-d');
                    foreach ($records as $record) {
                        if (substr($record['recordDate'], 0, 10) === $today) {
                            $today_records_count++;
                        }
                    }
                    echo $today_records_count;
                    ?>
                </div>
                <div class="stat-label">本日の記録数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalRecords">
                    <?php echo count($records); ?>
                </div>
                <div class="stat-label">総記録数</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="clients-section">
                <h2 class="section-title">
                    <span>👥</span>
                    利用者一覧
                </h2>
                <input type="text" class="search-box" placeholder="利用者名で検索..." id="searchInput">
                <div class="client-list" id="clientList">
                </div>
            </div>

            <div class="client-details" id="clientDetails">
                <h2 class="section-title">
                    <span>📊</span>
                    利用者詳細
                </h2>
                <div id="clientDetailsContent">
                    <p style="text-align: center;
opacity: 0.7; padding: 2rem;">利用者を選択してください</p>
                </div>
            </div>
        </div>
    </div>

    <div id="recordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">記録を追加</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="recordForm">
                <div class="form-group">
                    <label class="form-label">記録種別</label>
                    <select class="form-select" id="recordType" required>
                        <option value="">選択してください</option>
                        <option value="生活">生活</option>
                        <option value="職業訓練">職業訓練</option>
                        <option value="就活">就活</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">詳細</label>
                    <textarea class="form-textarea" id="recordDetails" placeholder="記録の詳細を入力してください..." required></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">記録を追加</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // PHPから取得したデータをJavaScript変数に格納
        const clients = <?= json_encode($clients, JSON_UNESCAPED_UNICODE); ?>;
        const records = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
        const currentUserId = <?= json_encode($current_user_id); ?>;
        const currentUserName = <?= json_encode($current_user_name, JSON_UNESCAPED_UNICODE); ?>;

        let selectedClientId = null;

        function formatDate(dateString) {
            if (!dateString) return '---';
            const date = new Date(dateString);
            return date.toLocaleDateString('ja-JP', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '---';
            const date = new Date(dateString);
            return date.toLocaleString('ja-JP', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function getRecordTypeClass(type) {
            switch (type) {
                case '生活':
                    return 'life';
                case '職業訓練':
                    return 'training';
                case '就活':
                    return 'job';
                default:
                    return '';
            }
        }

        function renderClients(filteredClients = clients) {
            console.log('renderClientsが実行されました。');
            const clientList = document.getElementById('clientList');
            if (filteredClients.length === 0) {
                clientList.innerHTML = '<p style="text-align: center; opacity: 0.7; padding: 1rem;">該当する利用者が見つかりません</p>';
                return;
            }

            clientList.innerHTML = filteredClients.map(client => `
            <div class="client-item ${selectedClientId === client.id ? 'active' : ''}" onclick="selectClient(${client.id})">
                <div class="client-name">${client.name}</div>
                <div class="client-meta">
                    <span>入所: ${formatDate(client.enrollmentDate)}</span>
                    <span>年齢: ${client.dateOfBirth ? new Date().getFullYear() - new Date(client.dateOfBirth).getFullYear() + '歳' : '---'}</span>
                </div>
            </div>
        `).join('');
            console.log('クライアントリストがレンダリングされました。');
        }

        function renderClientDetails(client) {
            console.log('renderClientDetailsが実行されました。', client);
            const clientDetailsContent = document.getElementById('clientDetailsContent');
            if (!client) {
                clientDetailsContent.innerHTML =
                    '<p style="text-align: center; opacity: 0.7; padding: 2rem;">利用者を選択してください</p>';
                return;
            }

            const clientRecords = records.filter(record => record.clientId === client.id);

            clientDetailsContent.innerHTML = `
            <div class="detail-section">
                <div class="detail-title">📝 基本情報</div>
                <div class="detail-content">
                    <p><strong>名前:</strong> ${client.name}</p>
                    <p><strong>生年月日:</strong> ${formatDate(client.dateOfBirth)}</p>
                    <p><strong>入所日:</strong> ${formatDate(client.enrollmentDate)}</p>
                    <p><strong>最終更新:</strong> ${client.lastUpdated ? formatDateTime(client.lastUpdated) : '未更新'} (${client.lastUpdatedBy ? client.lastUpdatedBy : '---'})</p>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🏠 生活状況</div>
                <div class="detail-content">${client.latestLifeStatus || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🎓 職業訓練状況</div>
                <div class="detail-content">${client.latestTrainingStatus || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">💼 就活状況</div>
                <div class="detail-content">${client.latestJobHuntingStatus || '記録がありません'}</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openRecordModal()">記録を追加</button>
                <button class="btn btn-secondary" onclick="editClient(${client.id})">情報を編集</button>
            </div>

            <div class="detail-section">
                <div class="detail-title">📋 過去の記録</div>
                ${clientRecords.length > 0 ?
                    clientRecords.map(record => `
                    <div class="record-item">
                        <div class="record-header">
                            <span class="record-type ${getRecordTypeClass(record.recordType)}">${record.recordType}</span>
                            <span class="record-date">${formatDateTime(record.recordDate)}</span>
                        </div>
                        <div class="record-content">${record.details}</div>
                    </div>
                `).join('') : '<div class="detail-content">記録がありません</div>'}
            </div>
        `;
            console.log('利用者詳細がレンダリングされました。');
        }

        // 修正: clientIdをNumber型に変換し、エラーハンドリングを追加
        function selectClient(clientId) {
            try {
                console.log('selectClientが実行されました。clientId:', clientId);
                selectedClientId = Number(clientId);
                const client = clients.find(c => Number(c.id) === selectedClientId);

                if (client) {
                    renderClients(); // アクティブ状態を更新するために再レンダリング
                    renderClientDetails(client);
                } else {
                    console.error('指定されたclientIdの利用者が見つかりませんでした。', selectedClientId);
                }
            } catch (e) {
                console.error('selectClient実行中にエラーが発生しました:', e);
            }
        }

        function searchClients() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filteredClients = clients.filter(client =>
                client.name.toLowerCase().includes(searchTerm)
            );
            renderClients(filteredClients);
        }

        function openRecordModal() {
            if (!selectedClientId) {
                alert('利用者を選択してください');
                return;
            }
            document.getElementById('recordModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('recordModal').style.display = 'none';
            document.getElementById('recordForm').reset();
        }

        function addRecord(event) {
            event.preventDefault();
            const recordType = document.getElementById('recordType').value;
            const recordDetails = document.getElementById('recordDetails').value;

            if (!recordType || !recordDetails) {
                alert('すべての項目を入力してください');
                return;
            }

            // AJAXでPHPにデータを送信
            const formData = new FormData();
            formData.append('clientId', selectedClientId);
            formData.append('recordType', recordType);
            formData.append('recordDetails', recordDetails);
            formData.append('recorded_by_user_id', currentUserId); // ユーザーIDを追加

            fetch('add_record.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 成功した場合、フロントエンドのデータを更新
                        const newRecord = data.newRecord;
                        records.unshift(newRecord);

                        // 最新の記録としてclientsテーブルも更新
                        const client = clients.find(c => Number(c.id) === selectedClientId);
                        if (client) {
                            if (recordType === '生活') {
                                client.latestLifeStatus = recordDetails;
                            } else if (recordType === '職業訓練') {
                                client.latestTrainingStatus = recordDetails;
                            } else if (recordType === '就活') {
                                client.latestJobHuntingStatus = recordDetails;
                            }
                            client.lastUpdated = newRecord.recordDate;
                            client.lastUpdatedBy = newRecord.recordedBy; // 記録したユーザー名を更新
                        }

                        renderClientDetails(client);
                        closeModal();
                        alert('記録を追加しました');
                    } else {
                        alert('記録の追加に失敗しました: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function editClient(clientId) {
            alert('編集機能は実装予定です');
        }

        function logout() {
            if (confirm('ログアウトしますか？')) {
                window.location.href = 'logout.php';
            }
        }

        // イベントリスナーの設定
        document.getElementById('searchInput').addEventListener('input', searchClients);
        document.getElementById('recordForm').addEventListener('submit', addRecord);

        // ページ読み込み時の初期化
        document.addEventListener('DOMContentLoaded', function() {
            renderClients();
            renderClientDetails(null);
        });

        // モーダル外クリックで閉じる
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('recordModal');
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>

</body>

</html>