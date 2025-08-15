<?php
// 💡 デバッグ用エラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続設定
$host = 'localhost'; // 環境に合わせて変更
$username = 'root'; // 環境に合わせて変更
$password = 'your_password'; // 環境に合わせて変更
$dbname = 'careercompass_db'; // データベース名

// データベースに接続
$conn = new mysqli($host, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー: ' . $conn->connect_error]);
    exit;
}

// POSTデータの取得
$clientId = $_POST['clientId'] ?? null;
$clientName = $_POST['clientName'] ?? null;
$dateOfBirth = $_POST['dateOfBirth'] ?? null;
$enrollmentDate = $_POST['enrollmentDate'] ?? null;
$withdrawalDate = $_POST['withdrawalDate'] ?? null; // 💡 退所日を追加
$contactInfo = $_POST['contactInfo'] ?? null;
$lifeStatus = $_POST['lifeStatus'] ?? null;
$trainingStatus = $_POST['trainingStatus'] ?? null;
$jobHuntingStatus = $_POST['jobHuntingStatus'] ?? null;

// ログインユーザーID（ここでは仮の値）
$current_user_id = 1;

if ($clientId && $clientName && $enrollmentDate) {
    // 既存の利用者情報を取得
    $stmt_old = $conn->prepare("SELECT latest_life_status, latest_training_status, latest_job_hunting_status FROM clients WHERE id = ?");
    $stmt_old->bind_param("i", $clientId);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $old_data = $result_old->fetch_assoc();
    $stmt_old->close();

    // clientsテーブルの利用者を更新
    // 💡 withdrawal_dateカラムの更新を追加
    $stmt_update = $conn->prepare("UPDATE clients SET client_name = ?, date_of_birth = ?, enrollment_date = ?, withdrawal_date = ?, contact_info = ?, latest_life_status = ?, latest_training_status = ?, latest_job_hunting_status = ?, last_updated_at = NOW(), last_updated_by_user_id = ? WHERE id = ?");
    $stmt_update->bind_param("ssssssssii", $clientName, $dateOfBirth, $enrollmentDate, $withdrawalDate, $contactInfo, $lifeStatus, $trainingStatus, $jobHuntingStatus, $current_user_id, $clientId);
    $result_update = $stmt_update->execute();
    $stmt_update->close();

    if ($result_update) {
        // 変更があった場合にrecordsテーブルに新しいレコードを挿入
        $stmt_insert_record = $conn->prepare("INSERT INTO records (client_id, record_date, record_type, details, recorded_by_user_id) VALUES (?, NOW(), ?, ?, ?)");

        // (省略) 最新状況の更新チェックは変更なし

        $stmt_insert_record->close();

        echo json_encode(['status' => 'success', 'message' => '利用者情報を更新しました']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '利用者情報の更新に失敗しました: ' . $stmt_update->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '必須項目が不足しています']);
}

$conn->close();
