<?php
// データベース接続設定
$host = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'careercompass';

// データベースに接続
$conn = new mysqli($host, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー']);
    exit;
}

// POSTデータの取得
$clientId = $_POST['clientId'] ?? null;
$recordType = $_POST['recordType'] ?? null;
$recordDetails = $_POST['recordDetails'] ?? null;

// 現在のログインユーザーID（ここでは仮の値を使用）
// 実際のシステムではセッションなどから取得
$current_user_id = 1;

if ($clientId && $recordType && $recordDetails) {
    // recordsテーブルに新しい記録を挿入
    $stmt_insert = $conn->prepare("INSERT INTO records (client_id, record_date, record_type, details, recorded_by_user_id) VALUES (?, NOW(), ?, ?, ?)");
    $stmt_insert->bind_param("issi", $clientId, $recordType, $recordDetails, $current_user_id);
    $result_insert = $stmt_insert->execute();

    if ($result_insert) {
        $new_record_id = $stmt_insert->insert_id;

        // clientsテーブルの最新状況を更新
        $latest_status_column = '';
        if ($recordType === '生活') {
            $latest_status_column = 'latest_life_status';
        } else if ($recordType === '職業訓練') {
            $latest_status_column = 'latest_training_status';
        } else if ($recordType === '就活') {
            $latest_status_column = 'latest_job_hunting_status';
        }

        if ($latest_status_column) {
            $stmt_update = $conn->prepare("UPDATE clients SET {$latest_status_column} = ?, last_updated_at = NOW(), last_updated_by_user_id = ? WHERE id = ?");
            $stmt_update->bind_param("sii", $recordDetails, $current_user_id, $clientId);
            $stmt_update->execute();
        }

        // 更新した記録の情報を取得して返す
        $sql_new_record = "SELECT id, client_id AS clientId, record_date AS recordDate, record_type AS recordType,
                            details, (SELECT name FROM users WHERE id = records.recorded_by_user_id) AS recordedBy
                            FROM records WHERE id = ?";
        $stmt_new_record = $conn->prepare($sql_new_record);
        $stmt_new_record->bind_param("i", $new_record_id);
        $stmt_new_record->execute();
        $result_new_record = $stmt_new_record->get_result();
        $new_record = $result_new_record->fetch_assoc();

        echo json_encode(['status' => 'success', 'message' => '記録を追加しました', 'newRecord' => $new_record]);
    } else {
        echo json_encode(['status' => 'error', 'message' => '記録の挿入に失敗しました']);
    }

    $stmt_insert->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '必須項目が不足しています']);
}

$conn->close();
