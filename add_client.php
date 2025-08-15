<?php
// データベース接続設定
$host = 'localhost'; // 環境に合わせて変更
$username = 'root'; // 環境に合わせて変更
$password = 'root'; // 環境に合わせて変更
$dbname = 'careercompass'; // データベース名

// データベースに接続
$conn = new mysqli($host, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー']);
    exit;
}

// POSTデータの取得
$clientName = $_POST['clientName'] ?? null;
$dateOfBirth = $_POST['dateOfBirth'] ?? null;
$enrollmentDate = $_POST['enrollmentDate'] ?? null;
$contactInfo = $_POST['contactInfo'] ?? null;

if ($clientName && $enrollmentDate) {
    // clientsテーブルに新しい利用者を挿入
    $stmt = $conn->prepare("INSERT INTO clients (client_name, date_of_birth, enrollment_date, contact_info) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $clientName, $dateOfBirth, $enrollmentDate, $contactInfo);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => '利用者を追加しました']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '利用者の追加に失敗しました']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => '必須項目が不足しています']);
}

$conn->close();
