-- `CareerCompass` データベース
-- CREATE DATABASE CareerCompass;

-- USE DATABASE CareerCompass;

-- `users` テーブル
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL
);

-- `clients` テーブル
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    contact_info TEXT,
    enrollment_date DATE NOT NULL,
    latest_life_status TEXT,
    latest_training_status TEXT,
    latest_job_hunting_status TEXT,
    last_updated_at DATETIME,
    last_updated_by_user_id INT,
    FOREIGN KEY (last_updated_by_user_id) REFERENCES users(id)
);

-- `records` テーブル
CREATE TABLE records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    record_date DATETIME NOT NULL,
    record_type ENUM('生活', '職業訓練', '就活') NOT NULL,
    details TEXT NOT NULL,
    recorded_by_user_id INT NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (recorded_by_user_id) REFERENCES users(id)
);

INSERT INTO users (username, password_hash, name) VALUES
('tanaka', 'hashed_password_1', '田中 太郎'),
('sato', 'hashed_password_2', '佐藤 花子'),
('suzuki', 'hashed_password_3', '鈴木 次郎'),
('takahashi', 'hashed_password_4', '高橋 恵子'),
('yamada', 'hashed_password_5', '山田 健太');

INSERT INTO clients (client_name, date_of_birth, enrollment_date, latest_life_status, latest_training_status, latest_job_hunting_status, last_updated_at, last_updated_by_user_id) VALUES
('利用者 A', '1995-03-15', '2024-01-10', '規則正しい生活を送れています。', 'PCスキル研修を継続中。', '履歴書を作成中。', NOW(), 1),
('利用者 B', '1998-07-22', '2024-02-20', '体調に波があるため観察が必要。', 'ビジネスマナー研修に参加。', '就職フェアへの参加を検討中。', NOW(), 2),
('利用者 C', '2000-11-05', '2024-03-01', '安定している。', 'Word, Excelの資格取得を目指している。', '求人情報を収集している。', NOW(), 3),
('利用者 D', '1997-05-18', '2024-04-15', '生活リズムが乱れ気味。', 'プログラミング研修を開始。', '自己分析を進めている。', NOW(), 4),
('利用者 E', '1996-09-30', '2024-05-01', '問題なく過ごしている。', '簿記の学習を開始。', '面接練習を開始した。', NOW(), 5),
('利用者 F', '1994-02-14', '2024-06-10', '---', '---', '---', NULL, NULL),
('利用者 G', '1999-08-01', '2024-07-05', '---', '---', '---', NULL, NULL),
('利用者 H', '2001-12-25', '2024-08-20', '---', '---', '---', NULL, NULL),
('利用者 I', '1993-06-07', '2024-09-01', '---', '---', '---', NULL, NULL),
('利用者 J', '1992-04-19', '2024-10-15', '---', '---', '---', NULL, NULL),
('利用者 K', '1995-10-28', '2024-11-03', '---', '---', '---', NULL, NULL),
('利用者 L', '1998-01-09', '2024-12-12', '---', '---', '---', NULL, NULL),
('利用者 M', '1997-03-08', '2025-01-21', '---', '---', '---', NULL, NULL),
('利用者 N', '2000-04-29', '2025-02-05', '---', '---', '---', NULL, NULL),
('利用者 O', '1996-07-11', '2025-03-18', '---', '---', '---', NULL, NULL),
('利用者 P', '1994-11-20', '2025-04-02', '---', '---', '---', NULL, NULL),
('利用者 Q', '1999-02-28', '2025-05-14', '---', '---', '---', NULL, NULL),
('利用者 R', '2001-08-03', '2025-06-25', '---', '---', '---', NULL, NULL),
('利用者 S', '1993-09-17', '2025-07-08', '---', '---', '---', NULL, NULL),
('利用者 T', '1992-12-06', '2025-08-19', '---', '---', '---', NULL, NULL),
('利用者 U', '1995-01-26', '2025-09-30', '---', '---', '---', NULL, NULL),
('利用者 V', '1998-05-04', '2025-10-11', '---', '---', '---', NULL, NULL),
('利用者 W', '1997-06-29', '2025-11-22', '---', '---', '---', NULL, NULL),
('利用者 X', '2000-10-08', '2025-12-01', '---', '---', '---', NULL, NULL),
('利用者 Y', '1996-03-21', '2026-01-05', '---', '---', '---', NULL, NULL);

INSERT INTO records (client_id, record_date, record_type, details, recorded_by_user_id) VALUES
(1, '2024-01-15 10:00:00', '生活', '朝の体操に参加し、元気な様子でした。', 1),
(1, '2024-01-20 14:30:00', '職業訓練', 'PCのタイピング練習で目標を達成しました。', 1),
(1, '2024-01-25 16:00:00', '就活', '希望職種についてヒアリングを行いました。', 1),
(2, '2024-02-25 11:00:00', '生活', '体調不良を訴え、早めに帰宅しました。', 2),
(2, '2024-03-05 13:00:00', '職業訓練', 'ビジネスマナー研修で積極的に発言していました。', 2),
(2, '2024-03-10 15:00:00', '就活', '就職フェアの情報を共有しました。', 2),
(3, '2024-03-05 09:00:00', '生活', '規則正しい生活リズムが定着している。', 3),
(3, '2024-03-12 14:00:00', '職業訓練', 'Wordの学習が進み、模擬試験で高得点を取った。', 3),
(3, '2024-03-20 11:30:00', '就活', '興味のある求人について相談があった。', 3),
(4, '2024-04-20 10:30:00', '生活', '朝の遅刻が目立つため、声かけを実施。', 4),
(4, '2024-04-28 15:30:00', '職業訓練', 'プログラミングの基礎学習でつまずいている様子。', 4),
(4, '2024-05-05 14:00:00', '就活', '自己分析シートの進捗を確認。', 4),
(5, '2024-05-03 10:00:00', '生活', '体調に問題なく、安定して通所している。', 5),
(5, '2024-05-10 13:30:00', '職業訓練', '簿記の学習計画を立てた。', 5),
(5, '2024-05-15 16:00:00', '就活', '模擬面接を実施。改善点を指摘した。', 5);

ALTER TABLE clients ADD withdrawal_date DATE NULL AFTER enrollment_date;

CREATE TABLE jobs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

ALTER TABLE jobs ADD employment_type VARCHAR(50) NULL;
ALTER TABLE jobs ADD job_number VARCHAR(255) NULL AFTER id;
ALTER TABLE jobs ADD UNIQUE (job_number);

ALTER TABLE jobs MODIFY job_title VARCHAR(512);
ALTER TABLE jobs MODIFY job_title TEXT;


CREATE TABLE `client_jobs` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `client_id` INT(11) NOT NULL,
    `job_id` INT(11) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT '未記録',
    `note` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `client_job_unique` (`client_id`,`job_id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE
);

ALTER TABLE `jobs`
ADD COLUMN `job_description` TEXT NULL AFTER `job_title`,
ADD COLUMN `location` VARCHAR(255) NULL AFTER `job_description`,
ADD COLUMN `salary` VARCHAR(255) NULL AFTER `location`;

ALTER TABLE jobs MODIFY company_name VARCHAR(255) NULL;