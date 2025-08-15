<?php
// デバッグ用エラー表示設定
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// PHPの警告やエラーが画面に出力されるのを防ぐ
ini_set('display_errors', 0);

// 出力バッファリングを開始
ob_start();

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

$sql = "SELECT id, client_name FROM clients ORDER BY client_name ASC";
$result = $conn->query($sql);

$clients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

$conn->close();
ob_end_clean();
echo json_encode(['status' => 'success', 'clients' => $clients]);
