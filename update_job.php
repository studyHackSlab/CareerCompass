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

// 必要な情報がPOSTされているか確認
if (!isset($_POST['jobId']) || !isset($_POST['job_number']) || !isset($_POST['job_title']) || !isset($_POST['company_name'])) {
    echo json_encode(['status' => 'error', 'message' => '必要な情報が不足しています。']);
    exit;
}

$jobId = $_POST['jobId'];
$jobNumber = $_POST['job_number'] ?? '';
$jobTitle = $_POST['job_title'] ?? '';
$companyName = $_POST['company_name'] ?? '';
$employmentType = $_POST['employment_type'] ?? '';
$jobDescription = $_POST['job_description'] ?? '';

// データベースを更新するSQLクエリ
$sql = "UPDATE jobs SET job_number = ?, job_title = ?, company_name = ?, employment_type = ?, job_description = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $jobNumber, $jobTitle, $companyName, $employmentType, $jobDescription, $jobId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => '求人情報が正常に更新されました。']);
    } else {
        echo json_encode(['status' => 'success', 'message' => '更新する変更がありませんでした。']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '求人情報の更新に失敗しました: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
