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

$keyword = $_POST['keyword'] ?? '';
$keyword = '%' . $conn->real_escape_string($keyword) . '%';

if (empty(trim($keyword, '%'))) {
    echo json_encode(['status' => 'success', 'jobs' => []]);
    exit;
}

$sql = "SELECT id, job_number, company_name, job_title, job_description, location, salary, employment_type 
        FROM jobs 
        WHERE company_name LIKE ? OR job_title LIKE ? 
        ORDER BY created_at DESC 
        LIMIT 50"; // 最新の50件に限定

// $sql = "SELECT job_number, company_name, job_title, employment_type 
//         FROM jobs 
//         WHERE company_name LIKE ? OR job_title LIKE ? 
//         ORDER BY created_at DESC 
//         LIMIT 50"; // 最新の50件に限定

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $keyword, $keyword);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['status' => 'success', 'jobs' => $jobs]);
