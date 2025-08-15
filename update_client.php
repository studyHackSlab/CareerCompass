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
    // echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー']);
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

// 💡 withdrawalDateが空の場合はNULLに設定
if (empty($withdrawalDate)) {
    $withdrawalDate = null;
}

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
    // $stmt_update = $conn->prepare("UPDATE clients SET client_name = ?, date_of_birth = ?, enrollment_date = ?, contact_info = ?, latest_life_status = ?, latest_training_status = ?, latest_job_hunting_status = ?, last_updated_at = NOW(), last_updated_by_user_id = ? WHERE id = ?");
    // $stmt_update->bind_param("sssssssii", $clientName, $dateOfBirth, $enrollmentDate, $contactInfo, $lifeStatus, $trainingStatus, $jobHuntingStatus, $current_user_id, $clientId);
    $stmt_update = $conn->prepare("UPDATE clients SET client_name = ?, date_of_birth = ?, enrollment_date = ?, withdrawal_date = ?, contact_info = ?, latest_life_status = ?, latest_training_status = ?, latest_job_hunting_status = ?, last_updated_at = NOW(), last_updated_by_user_id = ? WHERE id = ?");
    $stmt_update->bind_param("ssssssssii", $clientName, $dateOfBirth, $enrollmentDate, $withdrawalDate, $contactInfo, $lifeStatus, $trainingStatus, $jobHuntingStatus, $current_user_id, $clientId);


    $result_update = $stmt_update->execute();
    $stmt_update->close();

    if ($result_update) {
        // 変更があった場合にrecordsテーブルに新しいレコードを挿入
        $stmt_insert_record = $conn->prepare("INSERT INTO records (client_id, record_date, record_type, details, recorded_by_user_id) VALUES (?, NOW(), ?, ?, ?)");

        // 生活状況に更新があった場合
        if ($old_data['latest_life_status'] != $lifeStatus) {
            $recordType = '生活';
            $details = $lifeStatus;
            $stmt_insert_record->bind_param("issi", $clientId, $recordType, $details, $current_user_id);
            $stmt_insert_record->execute();
        }

        // 職業訓練状況に更新があった場合
        if ($old_data['latest_training_status'] != $trainingStatus) {
            $recordType = '職業訓練';
            $details = $trainingStatus;
            $stmt_insert_record->bind_param("issi", $clientId, $recordType, $details, $current_user_id);
            $stmt_insert_record->execute();
        }

        // 就活状況に更新があった場合
        if ($old_data['latest_job_hunting_status'] != $jobHuntingStatus) {
            $recordType = '就活';
            $details = $jobHuntingStatus;
            $stmt_insert_record->bind_param("issi", $clientId, $recordType, $details, $current_user_id);
            $stmt_insert_record->execute();
        }
        $stmt_insert_record->close();

        echo json_encode(['status' => 'success', 'message' => '利用者情報を更新しました']);
    } else {
        // echo json_encode(['status' => 'error', 'message' => '利用者情報の更新に失敗しました']);
        echo json_encode(['status' => 'error', 'message' => '利用者情報の更新に失敗しました: ' . $stmt_update->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '必須項目が不足しています']);
}

$conn->close();
