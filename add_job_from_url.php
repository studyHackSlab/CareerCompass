<?php
// デバッグ用エラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// データベース接続設定
$host = 'localhost';
$username = 'root';
$password = 'root'; // 💡 ご自身のパスワードに変更してください
$dbname = 'careercompass';

// データベースに接続
$conn = new mysqli($host, $username, $password, $dbname);

// 接続エラーの確認
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'データベース接続エラー: ' . $conn->connect_error]);
    exit;
}

// POSTデータの取得
$jobUrl = $_POST['jobUrl'] ?? null;

if (!$jobUrl) {
    echo json_encode(['status' => 'error', 'message' => 'URLが指定されていません。']);
    exit;
}

try {
    // URLからHTMLデータを取得し、Shift_JISからUTF-8に変換
    $html = file_get_contents($jobUrl);
    if ($html === false) {
        throw new Exception("URLからのデータ取得に失敗しました。");
    }
    // 文字化け対策: Shift_JISとEUC-JPを自動判別
    $html = mb_convert_encoding($html, 'UTF-8', 'SJIS, EUC-JP, auto');

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    @$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html);
    libxml_clear_errors();

    // 💡 1. IDから求人番号、職種名、企業名、業務内容、勤務地、給与を直接取得
    $jobNumberElement = $dom->getElementById('ID_kjNo');
    $jobNumber = $jobNumberElement ? trim($jobNumberElement->textContent) : '';

    $jobTitleElement = $dom->getElementById('ID_sksu');
    $jobTitle = $jobTitleElement ? trim($jobTitleElement->textContent) : '';

    $companyNameElement = $dom->getElementById('ID_jgshMei');
    // $companyName = $companyNameElement ? trim($companyNameElement->textContent) : '';
    $companyName = $companyNameElement ? trim($companyNameElement->textContent) : null;

    $jobDescriptionElement = $dom->getElementById('ID_shigotoNy');
    $jobDescription = $jobDescriptionElement ? trim($jobDescriptionElement->textContent) : '';
    // 💡 HTMLタグを除去する
    $jobDescription = strip_tags($jobDescription);

    $locationElement = $dom->getElementById('ID_szci');
    $location = $locationElement ? trim($locationElement->textContent) : '';

    $salaryElement = $dom->getElementById('ID_chgn');
    $salary = $salaryElement ? trim($salaryElement->textContent) : '';

    // 障がい者雇用の抽出 (URLのクエリパラメータから判定)
    $isShogai = strpos($jobUrl, 'shogaiKbn=1') !== false;
    $employmentType = $isShogai ? '障がい者雇用' : '一般雇用';

    // 抽出データの検証
    // if (empty($jobNumber) || empty($companyName) || empty($jobTitle)) {
    if (empty($jobNumber) || empty($jobTitle)) {
        throw new Exception("必要な求人情報が見つかりませんでした。（求人番号: " . ($jobNumber ? '取得済み' : '未取得') . ", 企業名: " . ($companyName ? '取得済み' : '未取得') . ", 職種名: " . ($jobTitle ? '取得済み' : '未取得') . "）");
    }

    // 💡 2. 抽出したデータをデータベースに登録/更新する
    // 新しいカラムを追加したINSERT...ON DUPLICATE KEY UPDATE文を使用
    $sql = "INSERT INTO jobs (job_number, company_name, job_title, job_description, location, salary, employment_type, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            company_name = VALUES(company_name), 
            job_title = VALUES(job_title), 
            job_description = VALUES(job_description), 
            location = VALUES(location), 
            salary = VALUES(salary), 
            employment_type = VALUES(employment_type),
            created_at = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $jobNumber, $companyName, $jobTitle, $jobDescription, $location, $salary, $employmentType);

    if ($stmt->execute()) {
        $message = ($stmt->affected_rows > 1) ? '求人情報を更新しました。' : '求人情報を登録しました。';
        echo json_encode(['status' => 'success', 'message' => $message]);
    } else {
        throw new Exception("求人情報のデータベース登録に失敗しました: " . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
