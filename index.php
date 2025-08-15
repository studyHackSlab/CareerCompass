<?php
// 💡 デバッグ用エラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
// $sql_clients = "SELECT id, client_name AS name, date_of_birth AS dateOfBirth, contact_info AS contactInfo, enrollment_date AS enrollmentDate,
//                   latest_life_status AS latestLifeStatus, latest_training_status AS latestTrainingStatus,
//                   latest_job_hunting_status AS latestJobHuntingStatus, last_updated_at AS lastUpdated,
//                   (SELECT name FROM users WHERE id = clients.last_updated_by_user_id) AS lastUpdatedBy
//                   FROM clients";
$sql_clients = "SELECT id, client_name AS name, date_of_birth AS dateOfBirth, contact_info AS contactInfo, enrollment_date AS enrollmentDate, withdrawal_date AS withdrawalDate,
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

        /* :root {
            --color-1: #1e1e76;
            --color-2: #4b4bc3;
            --color-3: #707ff5;
            --color-4: #a195f9;
            --color-5: #f2a1f2;
        } */

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

        /* 💡 テーマカラーを変更するボタンのスタイル */
        .theme-change-btn-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 5px;
        }

        .theme-change-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #fff;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* 各ボタンの色 */
        #blue-theme-btn {
            background-color: #007bff;
        }

        #green-theme-btn {
            background-color: #28a745;
        }

        #red-theme-btn {
            background-color: #dc3545;
        }

        #orange-theme-btn {
            background-color: #ffc107;
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

            /* Flexboxを適用して子要素を中央に配置 */
            display: flex;
            justify-content: center;
            align-items: center;

            /* その他の既存のスタイルはそのまま */
            flex-direction: column;
            /* 必要に応じて調整 */

            cursor: pointer;
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
            /* grid-template-columns: 1fr 350px; */
            grid-template-columns: 400px 1fr;
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

        /* 💡 チェックボックスとラベルのためのスタイル */
        .title-with-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--color-4);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .slider-label {
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
            font-size: 0.9rem;
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
            /* max-height: 500px; */
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 1rem;
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
            margin-bottom: 1rem;
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

        /* 編集モーダルの中身をスクロール可能にするCSS */
        #editClientModal .modal-content {
            max-height: 90vh;
            /* 画面の高さの90%を最大高さとする */
            overflow-y: auto;
            /* 中身がはみ出した場合にスクロールバーを表示 */
        }

        /* フォームグループ間のマージンを調整して見やすくする */
        #editClientModal .form-group {
            margin-bottom: 1.2rem;
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

        /* 選択肢（オプション）の背景色と文字色を明示的に設定 */
        .form-input option {
            background-color: #fff;
            color: #333;
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
            /* min-height: 200px; */
            min-height: 100px;
            resize: vertical;
        }

        /* 💡 紐づいている求人情報モーダルのスタイル */
        .client-job-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
            /* 各求人情報アイテムの下に余白 */
        }

        /* 💡 奇数番目のアイテムに背景色を設定 */
        .client-job-item:nth-child(odd) {
            background: rgba(255, 255, 255, 0.1);
            /* 薄いグレーの背景色 */
        }

        /* 💡 偶数番目のアイテムに背景色を設定 */
        .client-job-item:nth-child(even) {
            background: rgba(255, 255, 255, 0.15);
            /* 少し濃いグレーの背景色 */
        }

        .client-job-item h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .client-job-item p {
            margin: 10px 0;
            /* pタグの上下に余白 */
        }

        /* 状態編集ボタンの余白 */
        .client-job-item .btn {
            margin-top: 10px;
        }

        #searchResults {
            max-height: 500px;
            /* 💡 任意の高さを設定 */
            overflow-y: auto;
            /* border: 1px solid #ccc; */
            padding: 10px;
        }

        /* 求人登録ボタンのスタイル */
        #registerJobBtn {
            background: none;
            /* 背景色なし */
            border: none;
            /* 枠線なし */
            padding: 0;
            /* パディングをリセット */
            cursor: pointer;
            /* マウスカーソルをポインターに */
            font-size: 1.4rem;
            /* 必要に応じてサイズ調整 */
            /* color: var(--color-5); */
            font-weight: bold;
            width: 100%;
            height: 100%;
        }

        #editJobDescription{
            min-height: 200px;
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
            <div class="theme-change-btn-container">
                <button id="blue-theme-btn" class="theme-change-btn" color-1="#1e1e76" color-2="#4b4bc3" color-3="#707ff5" color-4="#a195f9" color-5="#f2a1f2"></button>
                <button id="green-theme-btn" class="theme-change-btn" color-1="#44562f" color-2="#83934d" color-3="#b7c88d" color-4="#e9e8b478" color-5="#efbfb3"></button>
                <button id="red-theme-btn" class="theme-change-btn" color-1="#4d1919" color-2="#783a3a" color-3="#b95b3c" color-4="#d67f4c" color-5="#f4caa9"></button>
                <button id="orange-theme-btn" class="theme-change-btn" color-1="#ff6e61" color-2="#ffb84d" color-3="#6d9dc5" color-4="#8a6ecbff" color-5="#da1b61"></button>
            </div>
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
            <!-- <div class="stat-card">
                <div class="stat-number" id="totalRecords">
                    <?php echo count($records); ?>
                </div>
                <div class="stat-label">総記録数</div>
            </div> -->
            <div class="stat-card">
                <!-- <button class="add-button" onclick="openAddJobModal()">求人登録</button> -->
                <!-- <button id="registerJobBtn" class="btn btn-primary" onclick="openAddJobModal()" style="white-space: nowrap;">求人登録</button> -->
                <button type="button" class="btn btn-primary" id="registerJobBtn" onclick="openJobRegistrationOptions()">求人登録</button>
            </div>
            <div class="stat-card">
                <!-- <button class="add-button" onclick="openAddJobModal()">求人登録</button> -->
                <button class="btn btn-primary" onclick="openJobSearchModal()" style="white-space: nowrap;">求人検索</button>
            </div>
        </div>

        <div class="content-grid">
            <div class="clients-section">
                <!-- <h2 class="section-title">
                    <span>👥</span>
                    利用者一覧
                </h2> -->
                <div class="title-with-toggle">
                    <h2 class="section-title">
                        <span>👥</span>
                        利用者一覧
                    </h2>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showAllClientsCheckbox">
                        <span class="slider"></span>
                    </label>
                    <label class="slider-label" for="showAllClientsCheckbox">退所者を表示</label>
                </div>
                <!-- 編集前 -->
                <input type="text" class="search-box" placeholder="利用者名で検索..." id="searchInput">

                <!-- 編集後 -->
                <!-- <div style="display: flex; gap: 10px; margin-bottom: 1rem;">
                    <input type="text" class="search-box" placeholder="利用者名で検索..." id="searchInput">
                    <button class="btn btn-primary" onclick="openClientModal()" style="white-space: nowrap;">利用者を追加</button>
                </div> -->

                <div class="client-list" id="clientList">
                </div>
                <button class="btn btn-primary" onclick="openClientModal()" style="white-space: nowrap;">利用者を追加</button>

            </div>

            <div class="client-details" id="clientDetails">
                <h2 class="section-title">
                    <span>📊</span>
                    利用者詳細
                </h2>
                <div id="clientDetailsContent">
                    <p style="text-align: center; opacity: 0.7; padding: 2rem;">利用者を選択してください</p>
                </div>
            </div>
        </div>
    </div>

    <div id="addJobModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">ハローワーク求人登録</h3>
                <span class="close" onclick="closeAddJobModal()">&times;</span>
            </div>
            <form id="addJobForm">
                <div class="form-group">
                    <label class="form-label" for="jobUrl">求人情報URL:</label>
                    <input type="text" class="form-input" id="jobUrl" name="jobUrl" required>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">登録</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddJobModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="jobSearchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">求人検索</h3>
                <span class="close" onclick="closeJobSearchModal()">&times;</span>
            </div>
            <form id="jobSearchForm">
                <div class="form-group">
                    <label class="form-label" for="searchKeyword">キーワード:</label>
                    <input type="text" class="form-input" id="searchKeyword" name="searchKeyword" placeholder="企業名または職種">
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">検索</button>
                    <button type="button" class="btn btn-secondary" onclick="closeJobSearchModal()">キャンセル</button>
                </div>
            </form>
            <div id="searchResults" style="margin-top: 20px;">
            </div>
        </div>
    </div>

    <div id="editJobModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">求人情報を編集</h3>
                <span class="close" onclick="closeEditJobModal()">&times;</span>
            </div>
            <form id="editJobForm">
                <input type="hidden" id="editJobId">
                <div class="form-group">
                    <label class="form-label" for="editJobNumber">求人番号</label>
                    <input type="text" class="form-input" id="editJobNumber" name="job_number">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editEmploymentType">雇用形態</label>
                    <select class="form-input" id="editEmploymentType" name="employment_type">
                        <option value="障がい者雇用">障がい者雇用</option>
                        <option value="一般雇用">一般雇用</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editJobTitle">求人名</label>
                    <input type="text" class="form-input" id="editJobTitle" name="job_title">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editCompanyName">企業名</label>
                    <input type="text" class="form-input" id="editCompanyName" name="company_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editJobDescription">求人情報</label>
                    <textarea class="form-textarea" id="editJobDescription" name="job_description"></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditJobModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="newJobModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">新規求人登録</h3>
                <span class="close" onclick="closeNewJobModal()">&times;</span>
            </div>
            <form id="newJobForm">
                <div class="form-group">
                    <label class="form-label" for="newJobNumber">求人番号</label>
                    <input type="text" class="form-input" id="newJobNumber" name="job_number">
                </div>
                <div class="form-group">
                    <label class="form-label" for="newEmploymentType">雇用形態</label>
                    <select class="form-input" id="newEmploymentType" name="employment_type">
                        <option value="障がい者雇用">障がい者雇用</option>
                        <option value="一般雇用">一般雇用</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="newJobTitle">求人名</label>
                    <input type="text" class="form-input" id="newJobTitle" name="job_title">
                </div>
                <div class="form-group">
                    <label class="form-label" for="newCompanyName">企業名</label>
                    <input type="text" class="form-input" id="newCompanyName" name="company_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="newJobDescription">求人情報</label>
                    <textarea class="form-textarea" id="newJobDescription" name="job_description"></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">登録</button>
                    <button type="button" class="btn btn-secondary" onclick="closeNewJobModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="registrationOptionsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">求人登録方法を選択</h3>
                <span class="close" onclick="closeRegistrationOptionsModal()">&times;</span>
            </div>
            <div class="action-buttons">
                <button type="button" class="btn btn-primary" onclick="openAddJobModal()">ハローワークで登録</button>
                <!-- <a href="https://www.hellowork.mhlw.go.jp/" target="_blank" class="btn btn-primary">ハローワークで登録</a> -->

                <button type="button" class="btn btn-secondary" onclick="openNewJobModal()">URLを使わないで登録</button>
            </div>
        </div>
    </div>

    <div id="linkClientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">利用者と求人を紐づける</h3>
                <span class="close" onclick="closeLinkClientModal()">&times;</span>
            </div>
            <div class="modal-body">
                <h4 id="linkingJobTitle"></h4>
                <div id="linkClientList" class="client-list">
                </div>
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeLinkClientModal()">キャンセル</button>
                </div>
            </div>
        </div>
    </div>

    <div id="recordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">記録を追加</h3>
                <span class="close" onclick="closeRecordModal()">&times;</span>
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
                    <button type="button" class="btn btn-secondary" onclick="closeRecordModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="clientJobsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">紐づいている求人情報</h3>
                <span class="close" onclick="closeClientJobsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <h4 id="clientJobsTitle"></h4>
                <div id="clientJobsList">
                </div>
            </div>
        </div>
    </div>


    <div id="clientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">利用者を追加</h3>
                <span class="close" onclick="closeClientModal()">&times;</span>
            </div>
            <form id="clientForm">
                <div class="form-group">
                    <label class="form-label" for="clientName">利用者名</label>
                    <input type="text" class="form-input" id="clientName" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="dateOfBirth">生年月日</label>
                    <input type="date" class="form-input" id="dateOfBirth">
                </div>
                <div class="form-group">
                    <label class="form-label" for="enrollmentDate">利用開始日</label>
                    <input type="date" class="form-input" id="enrollmentDate" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contactInfo">連絡先</label>
                    <textarea class="form-textarea" id="contactInfo" placeholder="連絡先やその他情報を入力してください"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="withdrawalDate">退所日</label>
                    <input type="date" class="form-input" id="withdrawalDate">
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">利用者を追加</button>
                    <button type="button" class="btn btn-secondary" onclick="closeClientModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editClientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">利用者情報を編集</h3>
                <span class="close" onclick="closeEditClientModal()">&times;</span>
            </div>
            <form id="editClientForm">
                <input type="hidden" id="editClientId">
                <div class="form-group">
                    <label class="form-label" for="editClientName">利用者名</label>
                    <input type="text" class="form-input" id="editClientName" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editDateOfBirth">生年月日</label>
                    <input type="date" class="form-input" id="editDateOfBirth">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editEnrollmentDate">利用開始日</label>
                    <input type="date" class="form-input" id="editEnrollmentDate" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editWithdrawalDate">退所日</label>
                    <input type="date" class="form-input" id="editWithdrawalDate">
                </div>
                <div class="form-group">
                    <label class="form-label" for="editContactInfo">連絡先</label>
                    <textarea class="form-textarea" id="editContactInfo" placeholder="連絡先やその他情報を入力してください"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editLifeStatus">生活状況</label>
                    <textarea class="form-textarea" id="editLifeStatus" placeholder="最新の生活状況"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editTrainingStatus">職業訓練状況</label>
                    <textarea class="form-textarea" id="editTrainingStatus" placeholder="最新の職業訓練状況"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editJobHuntingStatus">就活状況</label>
                    <textarea class="form-textarea" id="editJobHuntingStatus" placeholder="最新の就活状況"></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditClientModal()">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editJobStatusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">状態を編集</h3>
                <span class="close" onclick="closeEditJobStatusModal()">&times;</span>
            </div>
            <form id="editJobStatusForm">
                <input type="hidden" id="editClientJobId">
                <div class="form-group">
                    <h4 id="editJobTitle"></h4>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editStatus">状態:</label>
                    <select class="form-select" id="editStatus" name="status" required>
                        <option value="未記録">未記録</option>
                        <option value="未記録">企業研究中</option>
                        <option value="未記録">応募書類作成中</option>
                        <option value="応募済み">応募済み</option>
                        <option value="書類選考中">書類選考中</option>
                        <option value="面接待ち">面接待ち</option>
                        <option value="面接済み">面接済み</option>
                        <option value="採用">採用</option>
                        <option value="不採用">不採用</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editNote">メモ:</label>
                    <textarea class="form-textarea" id="editNote" name="note" rows="4"></textarea>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditJobStatusModal()">キャンセル</button>
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

        // 💡 クライアントをフィルタリングし、レンダリングする新しい関数
        function filterAndRenderClients() {
            const showAllClients = document.getElementById('showAllClientsCheckbox').checked;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            let filteredClients = clients;

            // 退所者を表示しない場合
            if (!showAllClients) {
                filteredClients = filteredClients.filter(client => !client.withdrawalDate);
            }

            // 検索ワードでフィルタリング
            filteredClients = filteredClients.filter(client =>
                client.name.toLowerCase().includes(searchTerm)
            );

            renderClients(filteredClients);
        }

        function renderClients(filteredClients) {
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
                    <p><strong>退所日:</strong> ${formatDate(client.withdrawalDate)}</p>
                    <p><strong>最終更新:</strong> ${client.lastUpdated ? formatDateTime(client.lastUpdated) : '未更新'} (${client.lastUpdatedBy ? client.lastUpdatedBy : '---'})</p>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🏠 生活状況</div>
                <div class="detail-content">${formatTextWithLineBreaks(client.latestLifeStatus) || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">🎓 職業訓練状況</div>
                <div class="detail-content">${formatTextWithLineBreaks(client.latestTrainingStatus) || '記録がありません'}</div>
            </div>

            <div class="detail-section">
                <div class="detail-title">💼 就活状況</div>
                <div class="detail-content">${formatTextWithLineBreaks(client.latestJobHuntingStatus) || '記録がありません'}</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openRecordModal()">記録を追加</button>
                <button class="btn btn-secondary" onclick="editClient(${client.id})">情報を編集</button>
                <button class="btn btn-secondary" onclick="openClientJobsModal(${client.id})">求人情報</button>
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
                        <div class="record-content">${formatTextWithLineBreaks(record.details)}</div>
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
                    // renderClients(); // アクティブ状態を更新するために再レンダリング
                    filterAndRenderClients(); // アクティブ状態を更新するために再レンダリング
                    renderClientDetails(client);
                } else {
                    console.error('指定されたclientIdの利用者が見つかりませんでした。', selectedClientId);
                }
            } catch (e) {
                console.error('selectClient実行中にエラーが発生しました:', e);
            }
        }

        // function searchClients() {
        //     const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        //     const filteredClients = clients.filter(client =>
        //         client.name.toLowerCase().includes(searchTerm)
        //     );
        //     renderClients(filteredClients);
        // }

        // 求人登録モーダルの開閉関数
        function openAddJobModal() {
            document.getElementById('registrationOptionsModal').style.display = 'none';
            document.getElementById('addJobModal').style.display = 'block';
        }

        function closeAddJobModal() {
            document.getElementById('addJobModal').style.display = 'none';
        }

        // 求人登録フォームの送信処理
        document.getElementById('addJobForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const jobUrl = document.getElementById('jobUrl').value;

            // 💡 サーバーサイドの新しいPHPファイルにURLを送信
            const response = await fetch('add_job_from_url.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'jobUrl': jobUrl
                })
            });

            const result = await response.json();
            alert(result.message);
            if (result.status === 'success') {
                location.reload(); // 成功したらページを再読み込み
            }

            closeAddJobModal();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const themeButtons = document.querySelectorAll('.theme-change-btn');
            themeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const newColor1 = this.getAttribute('color-1');
                    const newColor2 = this.getAttribute('color-2');
                    const newColor3 = this.getAttribute('color-3');
                    const newColor4 = this.getAttribute('color-4');
                    const newColor5 = this.getAttribute('color-5');

                    document.documentElement.style.setProperty('--color-1', newColor1);
                    document.documentElement.style.setProperty('--color-2', newColor2);
                    document.documentElement.style.setProperty('--color-3', newColor3);
                    document.documentElement.style.setProperty('--color-4', newColor4);
                    document.documentElement.style.setProperty('--color-5', newColor5);
                });
            });
        });

        function openJobSearchModal() {
            document.getElementById('jobSearchModal').style.display = 'block';
        }

        function closeJobSearchModal() {
            document.getElementById('jobSearchModal').style.display = 'none';
        }

        document.getElementById('editJobStatusForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const clientId = document.getElementById('editClientId').value;
            const clientJobId = document.getElementById('editClientJobId').value;
            const status = document.getElementById('editStatus').value;
            const note = document.getElementById('editNote').value;

            fetch('update_client_job_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `clientJobId=${encodeURIComponent(clientJobId)}&status=${encodeURIComponent(status)}&note=${encodeURIComponent(note)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('状態を更新しました。');
                        closeEditJobStatusModal();
                        // fetchClientJobs(currentClient.id);
                        // fetchClientJobs(clientId);
                        closeEditJobStatusModal();
                        closeClientJobsModal();
                    } else {
                        alert('更新に失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('状態の更新中にエラーが発生しました。');
                });
        });

        // 求人編集モーダルを開く
        function openEditJobModal(jobId) {
            fetch(`get_job_details.php?jobId=${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const job = data.job;
                        document.getElementById('editJobModal').style.display = 'block';
                        document.getElementById('editJobId').value = job.id;
                        document.getElementById('editJobNumber').value = job.job_number;
                        document.getElementById('editJobTitle').value = job.job_title;
                        document.getElementById('editCompanyName').value = job.company_name;
                        document.getElementById('editEmploymentType').value = job.employment_type;
                        document.getElementById('editJobDescription').value = job.job_description;
                    } else {
                        alert('求人情報の取得に失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('求人情報の取得中にエラーが発生しました。');
                });
        }

        // 求人編集モーダルを閉じる
        function closeEditJobModal() {
            document.getElementById('editJobModal').style.display = 'none';
        }

        // 登録方法選択モーダルを開く
        function openJobRegistrationOptions() {
            document.getElementById('registrationOptionsModal').style.display = 'block';
        }

        // 登録方法選択モーダルを閉じる
        function closeRegistrationOptionsModal() {
            document.getElementById('registrationOptionsModal').style.display = 'none';
        }

        // 新規求人登録モーダルを開く（まだ作成していません）
        function openNewJobModal() {
            // ここに新規求人登録フォームのモーダルを開く処理を記述
            // 例: document.getElementById('newJobModal').style.display = 'block';
            closeRegistrationOptionsModal(); // 選択モーダルを閉じる
        }

        // 新規求人登録モーダルを開く
        function openNewJobModal() {
            document.getElementById('newJobModal').style.display = 'block';
            closeRegistrationOptionsModal(); // 登録方法選択モーダルを閉じる
        }

        // 新規求人登録モーダルを閉じる
        function closeNewJobModal() {
            document.getElementById('newJobModal').style.display = 'none';
            // フォームをリセット
            document.getElementById('newJobForm').reset();
        }

        // 💡 削除関数を追加
        function deleteJob(jobId) {
            if (confirm('本当にこの求人情報を削除しますか？')) {
                fetch('delete_job.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `jobId=${encodeURIComponent(jobId)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('求人情報を削除しました。');
                            // 検索結果を再読み込み
                            const currentQuery = document.getElementById('searchKeyword').value;
                            searchJobs(currentQuery);
                        } else {
                            alert('削除に失敗しました: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('求人情報の削除中にエラーが発生しました。');
                    });
            }
        }

        // 求人検索の実行
        function searchJobs(query = '') {
            const jobListDiv = document.getElementById('searchResults');
            jobListDiv.innerHTML = '<p>検索中...</p>';
            fetch(`search_jobs.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    jobListDiv.innerHTML = '';
                    if (data.status === 'success' && data.jobs.length > 0) {
                        data.jobs.forEach(job => {
                            const jobElement = document.createElement('div');
                            jobElement.className = 'job-item';

                            // 💡 job_descriptionをJSON.stringify()でエスケープ
                            const safeJobDescription = JSON.stringify(job.job_description || '');

                            jobElement.innerHTML = `
                        <h4 class="job-title">${job.job_title}</h4>
                        <p class="job-number"><strong>求人番号:</strong> ${job.job_number || ''}</p>
                        <p class="company-name"><strong>企業名:</strong> ${job.company_name}</p>
                        <p class="job-description"><strong>詳細:</strong> ${job.job_description || 'なし'}</p>
                        <div class="job-actions">
                            <button class="btn btn-success" onclick="openLinkClientModal('${job.id}', '${job.job_title}')">紐づける</button>
                            <button class="btn btn-secondary" onclick="openEditJobModal('${job.id}', '${job.job_number}', '${job.job_title}', '${job.company_name}', ${safeJobDescription})">編集</button>
                            </div>
                    `;
                            jobListDiv.appendChild(jobElement);
                        });
                    } else {
                        jobListDiv.innerHTML = '<p>一致する求人は見つかりませんでした。</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    jobListDiv.innerHTML = '<p>求人検索中にエラーが発生しました。</p>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {

            // 求人編集フォームの送信処理
            document.getElementById('editJobForm').addEventListener('submit', function(event) {
                event.preventDefault();

                const jobId = document.getElementById('editJobId').value;
                const jobNumber = document.getElementById('editJobNumber').value;
                const jobTitle = document.getElementById('editJobTitle').value;
                const companyName = document.getElementById('editCompanyName').value;
                const employmentType = document.getElementById('editEmploymentType').value; // 💡 追加
                const jobDescription = document.getElementById('editJobDescription').value;

                fetch('update_job.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `jobId=${encodeURIComponent(jobId)}&job_number=${encodeURIComponent(jobNumber)}&job_title=${encodeURIComponent(jobTitle)}&company_name=${encodeURIComponent(companyName)}&employment_type=${encodeURIComponent(employmentType)}&job_description=${encodeURIComponent(jobDescription)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('求人情報を更新しました。');
                            closeEditJobModal();
                            // 検索結果を再読み込み
                            const currentQuery = document.getElementById('searchKeyword').value;
                            searchJobs(currentQuery);
                        } else {
                            alert('更新に失敗しました: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        console.log(jobId);
                        console.log(jobNumber);
                        console.log(jobTitle);
                        console.log(companyName);
                        console.log(employmentType);
                        console.log(jobDescription);
                        alert('求人情報の更新中にエラーが発生しました。bbbb');
                    });
            });

            document.getElementById('jobSearchForm').addEventListener('submit', function(event) {
                event.preventDefault();
                const keyword = document.getElementById('searchKeyword').value;

                fetch('search_jobs.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `keyword=${encodeURIComponent(keyword)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('searchResults');
                        resultsDiv.innerHTML = '';

                        if (data.status === 'success' && data.jobs.length > 0) {
                            data.jobs.forEach(job => {
                                const jobElement = document.createElement('div');
                                jobElement.className = 'search-result-item';

                                // 💡 onclick属性をjob.idのみ渡すように修正
                                const safeId = JSON.stringify(job.id);

                                jobElement.innerHTML = `
                    <h4>${job.job_title}</h4>
                    <p><strong>企業名:</strong> ${job.company_name}</p>
                    <p><strong>求人番号:</strong> ${job.job_number || ''}</p>
                    <p><strong>雇用形態:</strong> ${job.employment_type}</p>
                    <p><strong>勤務地:</strong> ${job.location || '情報なし'}</p>
                    <p><strong>給与:</strong> ${job.salary || '情報なし'}</p>
                    <details>
                        <summary>業務内容</summary>
                        <p>${job.job_description || '情報なし'}</p>
                    </details>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-primary" onclick='openLinkClientModal(${safeId}, ${JSON.stringify(job.job_title)})'>紐づける</button>
                        <button type="button" class="btn btn-secondary" onclick="openEditJobModal(${job.id})">編集</button>
                        <button type="button" class="btn btn-secondary" onclick="deleteJob(${job.id})">削除</button>
                    </div>
                `;
                                resultsDiv.appendChild(jobElement);
                            });
                        } else {
                            resultsDiv.innerHTML = '<p>該当する求人は見つかりませんでした。</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('検索中にエラーが発生しました。');
                    });
            });

            document.getElementById('newJobForm').addEventListener('submit', function(event) {
                event.preventDefault();

                const jobNumber = document.getElementById('newJobNumber').value;
                const jobTitle = document.getElementById('newJobTitle').value;
                const companyName = document.getElementById('newCompanyName').value;
                const employmentType = document.getElementById('newEmploymentType').value; // 💡 追加
                const jobDescription = document.getElementById('newJobDescription').value;

                fetch('add_job.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `job_number=${encodeURIComponent(jobNumber)}&job_title=${encodeURIComponent(jobTitle)}&company_name=${encodeURIComponent(companyName)}&employment_type=${encodeURIComponent(employmentType)}&job_description=${encodeURIComponent(jobDescription)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('新しい求人情報を登録しました。');
                            closeNewJobModal();
                        } else {
                            alert('登録に失敗しました: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('求人情報の登録中にエラーが発生しました。');
                    });
            });

        });

        function openRecordModal() {
            if (!selectedClientId) {
                alert('利用者を選択してください');
                return;
            }
            document.getElementById('recordModal').style.display = 'block';
        }

        // 編集モーダルを開く関数
        function openEditClientModal(client) {
            document.getElementById('editClientId').value = client.id;
            document.getElementById('editClientName').value = client.name;
            document.getElementById('editDateOfBirth').value = client.dateOfBirth;
            document.getElementById('editEnrollmentDate').value = client.enrollmentDate;
            document.getElementById('editWithdrawalDate').value = client.withdrawalDate; // 💡 この行を追加
            document.getElementById('editContactInfo').value = client.contactInfo;
            document.getElementById('editLifeStatus').value = client.latestLifeStatus || '';
            document.getElementById('editTrainingStatus').value = client.latestTrainingStatus || '';
            document.getElementById('editJobHuntingStatus').value = client.latestJobHuntingStatus || '';

            document.getElementById('editClientModal').style.display = 'block';
        }

        // 💡 編集モーダルを閉じる関数
        function closeEditClientModal() {
            document.getElementById('editClientModal').style.display = 'none';
        }

        // function closeModal() {
        //     document.getElementById('recordModal').style.display = 'none';
        //     document.getElementById('recordForm').reset();
        // }

        function closeRecordModal() {
            // (省略) closeModal関数をcloseRecordModalに名称変更
            document.getElementById('recordModal').style.display = 'none';
            document.getElementById('recordForm').reset();
        }

        // 利用者追加モーダルを開く関数
        function openClientModal() {
            document.getElementById('clientModal').style.display = 'block';
        }

        // 利用者追加モーダルを閉じる関数
        function closeClientModal() {
            document.getElementById('clientModal').style.display = 'none';
            document.getElementById('clientForm').reset();
        }

        // 紐づけ対象の求人IDを一時的に保持する変数
        let currentJobId = null;

        // 利用者紐づけモーダルを開く
        function openLinkClientModal(jobId, jobTitle) {
            currentJobId = jobId;
            document.getElementById('linkingJobTitle').textContent = `求人「${jobTitle}」を紐づける利用者を選択してください`;
            document.getElementById('linkClientModal').style.display = 'block';
            fetchClientList();
        }

        // 利用者紐づけモーダルを閉じる
        function closeLinkClientModal() {
            document.getElementById('linkClientModal').style.display = 'none';
        }

        function fetchClientList() {
            fetch('get_clients.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('サーバーからの応答が正常ではありません。');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('取得した利用者データ:', data);
                    const clientListDiv = document.getElementById('linkClientList');
                    console.log('clientListDiv:', clientListDiv); // 💡 clientListDivが取得できているか確認

                    clientListDiv.innerHTML = '';

                    if (data.status === 'success' && data.clients.length > 0) {
                        data.clients.forEach(client => {
                            const clientButton = document.createElement('button');
                            clientButton.className = 'btn btn-secondary';
                            clientButton.textContent = client.client_name;
                            clientButton.style.margin = '5px';
                            clientButton.onclick = () => {
                                console.log(`紐づけボタンがクリックされました。ClientId: ${client.id}, JobId: ${currentJobId}`);
                                linkJobToClient(client.id, currentJobId);
                            };
                            clientListDiv.appendChild(clientButton);
                            console.log(`ボタンを追加しました: ${client.client_name}`); // 💡 ボタンが追加されたか確認
                        });
                    } else {
                        clientListDiv.innerHTML = '<p>登録されている利用者がいません。</p>';
                        console.log('利用者データがありません。');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('利用者一覧の取得中にエラーが発生しました。詳細はコンソールを確認してください。');
                });
        }

        // 求人と利用者を紐づける
        function linkJobToClient(clientId, jobId) {
            fetch('link_job_to_client.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `clientId=${encodeURIComponent(clientId)}&jobId=${encodeURIComponent(jobId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('求人情報を利用者に紐づけました。');
                        closeLinkClientModal();
                    } else {
                        alert('紐づけに失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('紐づけ中にエラーが発生しました。');
                });
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
                        closeRecordModal();
                        // closeModal();
                        alert('記録を追加しました');
                    } else {
                        alert('記録の追加に失敗しました: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // 利用者を追加する処理
        function addClient(event) {
            event.preventDefault();
            const clientName = document.getElementById('clientName').value;
            const dateOfBirth = document.getElementById('dateOfBirth').value;
            const enrollmentDate = document.getElementById('enrollmentDate').value;
            const contactInfo = document.getElementById('contactInfo').value;

            if (!clientName || !enrollmentDate) {
                alert('利用者名と利用開始日は必須項目です');
                return;
            }

            const formData = new FormData();
            formData.append('clientName', clientName);
            formData.append('dateOfBirth', dateOfBirth);
            formData.append('enrollmentDate', enrollmentDate);
            formData.append('contactInfo', contactInfo);

            fetch('add_client.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('利用者を追加しました');
                        closeClientModal();
                        // ページの再読み込み、またはclients配列を更新して再レンダリング
                        window.location.reload();
                    } else {
                        alert('利用者の追加に失敗しました: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // 利用者情報を更新する処理
        function updateClient(event) {
            event.preventDefault();
            const clientId = document.getElementById('editClientId').value;
            const clientName = document.getElementById('editClientName').value;
            const dateOfBirth = document.getElementById('editDateOfBirth').value;
            const enrollmentDate = document.getElementById('editEnrollmentDate').value;
            const withdrawalDate = document.getElementById('editWithdrawalDate').value;
            const contactInfo = document.getElementById('editContactInfo').value;
            const lifeStatus = document.getElementById('editLifeStatus').value;
            const trainingStatus = document.getElementById('editTrainingStatus').value;
            const jobHuntingStatus = document.getElementById('editJobHuntingStatus').value;

            if (!clientName || !enrollmentDate) {
                alert('利用者名と利用開始日は必須項目です');
                return;
            }

            const formData = new FormData();
            formData.append('clientId', clientId);
            formData.append('clientName', clientName);
            formData.append('dateOfBirth', dateOfBirth);
            formData.append('enrollmentDate', enrollmentDate);
            formData.append('withdrawalDate', withdrawalDate);
            formData.append('contactInfo', contactInfo);
            formData.append('lifeStatus', lifeStatus);
            formData.append('trainingStatus', trainingStatus);
            formData.append('jobHuntingStatus', jobHuntingStatus);

            fetch('update_client.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // レスポンスのテキストをまず確認
                    return response.text().then(text => {
                        try {
                            // JSONとしてパースを試みる
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSONパースエラー:', e);
                            console.error('サーバーからの応答:', text);
                            // エラーメッセージを返す
                            throw new Error('サーバーから無効な応答が返されました。詳細をコンソールで確認してください。');
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('利用者情報を更新しました');
                        closeEditClientModal();
                        // 画面を再読み込みして最新情報を反映
                        window.location.reload();
                    } else {
                        alert('利用者情報の更新に失敗しました: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('通信エラーが発生しました。詳細はコンソールを確認してください。');
                });

            // fetch('update_client.php', {
            //         method: 'POST',
            //         body: formData
            //     })
            //     .then(response => response.json())
            //     .then(data => {
            //         if (data.status === 'success') {
            //             alert('利用者情報を更新しました');
            //             closeEditClientModal();
            //             // 画面を再読み込みして最新情報を反映
            //             window.location.reload();
            //         } else {
            //             alert('利用者情報の更新に失敗しました: ' + data.message);
            //         }
            //     })
            //     .catch(error => console.error('Error:', error));
        }

        // 編集ボタンのクリックイベントハンドラを修正
        function editClient(clientId) {
            const client = clients.find(c => Number(c.id) === Number(clientId));
            if (client) {
                openEditClientModal(client);
            } else {
                alert('利用者情報が見つかりませんでした');
            }
        }

        // function editClient(clientId) {
        //     alert('編集機能は実装予定です');
        // }

        // 紐づいている求人情報モーダルを開く
        function openClientJobsModal(clientId) {
            document.getElementById('clientJobsModal').style.display = 'block';
            // document.getElementById('clientJobsTitle').textContent = `${clientName}さんに紐づいている求人情報`;
            fetchClientJobs(clientId);
        }

        // 紐づいている求人情報モーダルを閉じる
        function closeClientJobsModal() {
            document.getElementById('clientJobsModal').style.display = 'none';
        }

        // 状態編集モーダルを開く
        function openEditJobStatusModal(clientJobId, jobTitle, currentStatus, note) {
            document.getElementById('editJobStatusModal').style.display = 'block';
            document.getElementById('editClientJobId').value = clientJobId;
            document.getElementById('editJobTitle').textContent = `求人: ${jobTitle}`;
            document.getElementById('editStatus').value = currentStatus;
            document.getElementById('editNote').value = note;
        }

        // 状態編集モーダルを閉じる
        function closeEditJobStatusModal() {
            document.getElementById('editJobStatusModal').style.display = 'none';
        }

        // 紐づいている求人情報を取得して表示する
        function fetchClientJobs(clientId) {
            fetch(`get_client_jobs.php?clientId=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    const jobsListDiv = document.getElementById('clientJobsList');
                    jobsListDiv.innerHTML = '';

                    if (data.status === 'success' && data.jobs.length > 0) {
                        data.jobs.forEach(job => {
                            const jobElement = document.createElement('div');
                            jobElement.className = 'client-job-item';
                            jobElement.innerHTML = `
                        <h4>${job.job_title}</h4>
                        <p><strong>企業名:</strong> ${job.company_name}</p>
                        <p><strong>求人番号:</strong> ${job.job_number}</p>
                        <p><strong>状態:</strong> ${job.status}</p>
                        <p><strong>メモ:</strong> ${job.note || 'なし'}</p>
                        <button class="btn btn-primary" onclick="openEditJobStatusModal('${job.client_job_id}', '${job.job_title}', '${job.status}', '${job.note}')">状態を編集</button>
                    `;
                            jobsListDiv.appendChild(jobElement);
                        });
                    } else {
                        jobsListDiv.innerHTML = '<p>紐づいている求人情報はありません。</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('求人情報の取得中にエラーが発生しました。');
                });
        }

        // 💡 状態編集用のモーダル（ここではダミー）
        // function openEditJobStatusModal(clientJobId, jobTitle, currentStatus, note) {
        //     alert(`求人ID: ${clientJobId} の状態を編集します。\n現在の状態: ${currentStatus}\nメモ: ${note}`);
        //     // 実際の編集モーダルを開く処理をここに実装
        // }


        function logout() {
            if (confirm('ログアウトしますか？')) {
                window.location.href = 'logout.php';
            }
        }

        // イベントリスナーの設定
        // document.getElementById('searchInput').addEventListener('input', searchClients);
        document.getElementById('searchInput').addEventListener('input', filterAndRenderClients);
        document.getElementById('showAllClientsCheckbox').addEventListener('change', filterAndRenderClients);
        document.getElementById('recordForm').addEventListener('submit', addRecord);
        document.getElementById('clientForm').addEventListener('submit', addClient);
        document.getElementById('editClientForm').addEventListener('submit', updateClient);

        // ページ読み込み時の初期化
        document.addEventListener('DOMContentLoaded', function() {
            filterAndRenderClients(); // 💡 初期表示は退所者を表示しない
            // renderClients();
            renderClientDetails(null);
        });

        // モーダル外クリックで閉じる
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('recordModal');
            const recordModal = document.getElementById('recordModal');
            const clientModal = document.getElementById('clientModal');
            const editClientModal = document.getElementById('editClientModal');

            // if (event.target === modal) {
            //     closeModal();
            // }

            if (event.target === recordModal) {
                closeRecordModal();
            }
            if (event.target === clientModal) {
                closeClientModal();
            }
            if (event.target === editClientModal) {
                closeEditClientModal();
            }
        });

        // 改行をHTMLの<br>タグに変換する関数
        function formatTextWithLineBreaks(text) {
            if (!text) return '';
            return text.replace(/\n/g, '<br>');
        }
    </script>

</body>

</html>