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

$jobId = $_GET['jobId'] ?? null;

if (!$jobId) {
    echo json_encode(['status' => 'error', 'message' => '求人IDが指定されていません。']);
    exit;
}

// 求人詳細を取得する
$sql = "SELECT id, job_number, job_title, company_name, employment_type, job_description FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $job = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'job' => $job]);
} else {
    echo json_encode(['status' => 'error', 'message' => '求人情報が見つかりません。']);
}

$stmt->close();
$conn->close();
