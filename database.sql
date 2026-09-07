-- database.sql - Full Database Schema for EAF Entrepreneurs Awareness Day 2026

CREATE DATABASE IF NOT EXISTS `eaf_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eaf_db`;

CREATE TABLE IF NOT EXISTS `registrations` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
