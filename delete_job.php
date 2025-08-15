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

$jobId = $_POST['jobId'] ?? '';

if (empty($jobId)) {
    echo json_encode(['status' => 'error', 'message' => '求人IDが指定されていません。']);
    exit;
}

// 求人情報をデータベースから削除する
$sql = "DELETE FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => '求人情報が正常に削除されました。']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '該当する求人情報が見つかりませんでした。']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '求人情報の削除に失敗しました: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
