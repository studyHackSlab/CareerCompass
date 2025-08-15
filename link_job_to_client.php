<?php
// デバッグ用エラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続設定
$host = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'careercompass';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー: ' . $conn->connect_error]);
    exit;
}

header('Content-Type: application/json');

$clientId = $_POST['clientId'] ?? null;
$jobId = $_POST['jobId'] ?? null;

// 💡 デバッグ用コード
// print_r($_POST);
// exit;

if (empty($clientId) || empty($jobId)) {
    echo json_encode(['status' => 'error', 'message' => '必要な情報が不足しています。']);
    exit;
}

// 紐づけ情報をデータベースに登録
$sql = "INSERT INTO client_jobs (client_id, job_id, status) VALUES (?, ?, '未記録')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $clientId, $jobId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => '紐づけが完了しました。']);
} else {
    // 重複エラー（既に紐づいている）の場合も考慮
    if ($stmt->errno == 1062) { // 1062はMySQLのUNIQUE KEY重複エラーコード
        echo json_encode(['status' => 'error', 'message' => 'この求人はすでにこの利用者に紐づけられています。']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '紐づけに失敗しました: ' . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
