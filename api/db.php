<?php
// api/db.php - Database connection & auto-table manager

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'eaf_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

function getDBConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $tempPdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $pdo->exec("CREATE TABLE IF NOT EXISTS `registrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `registration_id` VARCHAR(50) UNIQUE NOT NULL,
            `fname` VARCHAR(150) NOT NULL,
            `mobile` VARCHAR(20) NOT NULL,
            `email` VARCHAR(150) NOT NULL,
            `company_name` VARCHAR(200) NOT NULL,
            `company_type` VARCHAR(100) NOT NULL,
            `gst_no` VARCHAR(50) DEFAULT NULL,
            `event_facility` VARCHAR(100) NOT NULL DEFAULT 'Not Required',
            `address` TEXT NOT NULL,
            `upi_id` VARCHAR(150) NOT NULL,
            `screenshot_path` VARCHAR(255) NOT NULL,
            `registration_fee` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
            `facility_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
            `attendance_type` ENUM('SEATED', 'STANDBY') NOT NULL DEFAULT 'SEATED',
            `seat_number` VARCHAR(10) DEFAULT NULL,
            `payment_status` VARCHAR(50) NOT NULL DEFAULT 'PENDING',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Safe migrations for missing columns if table already exists
        $columns = $pdo->query("SHOW COLUMNS FROM `registrations`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('event_facility', $columns)) {
            $pdo->exec("ALTER TABLE `registrations` ADD COLUMN `event_facility` VARCHAR(100) NOT NULL DEFAULT 'Not Required' AFTER `gst_no`");
        }
        if (!in_array('registration_fee', $columns)) {
            $pdo->exec("ALTER TABLE `registrations` ADD COLUMN `registration_fee` DECIMAL(10,2) NOT NULL DEFAULT 500.00 AFTER `screenshot_path`");
        }
        if (!in_array('facility_fee', $columns)) {
            $pdo->exec("ALTER TABLE `registrations` ADD COLUMN `facility_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `registration_fee`");
        }
        if (!in_array('total_amount', $columns)) {
            $pdo->exec("ALTER TABLE `registrations` ADD COLUMN `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 500.00 AFTER `facility_fee`");
        }

        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}
