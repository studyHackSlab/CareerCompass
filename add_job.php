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

// 💡 デバッグ用: データベース接続チェック
if ($conn->connect_error) {
    // 接続に失敗した場合、JSONエラーではなく、わかりやすいメッセージを表示してスクリプトを終了
    die('データベース接続エラー: ' . $conn->connect_error);
}

header('Content-Type: application/json');

$jobNumber = $_POST['job_number'] ?? '';
$jobTitle = $_POST['job_title'] ?? '';
$companyName = $_POST['company_name'] ?? '';
$employmentType = $_POST['employment_type'] ?? '';
$jobDescription = $_POST['job_description'] ?? '';

if ($jobNumber === '') {
    $jobNumber = null;
}

// 新しい求人情報をデータベースに挿入する
$sql = "INSERT INTO jobs (job_number, job_title, company_name, employment_type, job_description) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// 💡 デバッグ用: プリペアドステートメントの準備チェック
if ($stmt === false) {
    // ステートメントの準備に失敗した場合、SQLエラーを表示して終了
    die('プリペアドステートメントの準備に失敗しました: ' . $conn->error);
}

$stmt->bind_param("sssss", $jobNumber, $jobTitle, $companyName, $employmentType, $jobDescription);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => '求人情報が正常に登録されました。']);
} else {
    // 💡 デバッグ用: 実行失敗時のエラーメッセージをより詳細に
    echo json_encode(['status' => 'error', 'message' => '求人情報の登録に失敗しました: ' . $stmt->error . ' (SQL: ' . $sql . ')']);
}

$stmt->close();
$conn->close();
