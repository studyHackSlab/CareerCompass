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

$clientJobId = $_POST['clientJobId'] ?? null;
$status = $_POST['status'] ?? null;
$note = $_POST['note'] ?? null;

// 💡 デバッグ用コードを追加
// print_r($_POST);
// exit;

if (!$clientJobId || !$status) {
    echo json_encode(['status' => 'error', 'message' => '必要な情報が不足しています。']);
    exit;
}

// client_jobsテーブルのステータスとメモを更新する
$sql = "UPDATE client_jobs SET status = ?, note = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $status, $note, $clientJobId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => '求人情報を更新しました。']);
} else {
    echo json_encode(['status' => 'error', 'message' => '求人情報の更新に失敗しました: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
