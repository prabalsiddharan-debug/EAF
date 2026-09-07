-- migrations/add_event_facilities.sql - Safe Database Migration Script

USE `eaf_db`;

-- Add event_facility column after gst_no
ALTER TABLE `registrations` 
ADD COLUMN IF NOT EXISTS `event_facility` VARCHAR(100) NOT NULL DEFAULT 'Not Required' AFTER `gst_no`;

-- Add pricing columns
ALTER TABLE `registrations` 
ADD COLUMN IF NOT EXISTS `registration_fee` DECIMAL(10,2) NOT NULL DEFAULT 500.00 AFTER `screenshot_path`;

ALTER TABLE `registrations` 
ADD COLUMN IF NOT EXISTS `facility_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `registration_fee`;

ALTER TABLE `registrations` 
ADD COLUMN IF NOT EXISTS `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 500.00 AFTER `facility_fee`;
