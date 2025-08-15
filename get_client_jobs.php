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

$clientId = $_GET['clientId'] ?? null;

if (!$clientId) {
    echo json_encode(['status' => 'error', 'message' => 'クライアントIDが指定されていません。']);
    exit;
}

// 紐づいている求人情報を取得する
$sql = "SELECT 
            cj.id AS client_job_id,
            j.id AS job_id,
            j.job_number,
            j.company_name,
            j.job_title,
            cj.status,
            cj.note
        FROM client_jobs cj
        INNER JOIN jobs j ON cj.job_id = j.id
        WHERE cj.client_id = ?
        ORDER BY cj.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $clientId);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'jobs' => $jobs]);
