-- Add geolocation tracking fields to tblattendance table
-- This migration adds support for tracking employee location during clock in/out

ALTER TABLE `tblattendance` 
ADD COLUMN `clock_in_latitude` DECIMAL(10, 8) DEFAULT NULL AFTER `time_out`,
ADD COLUMN `clock_in_longitude` DECIMAL(11, 8) DEFAULT NULL AFTER `clock_in_latitude`,
ADD COLUMN `clock_out_latitude` DECIMAL(10, 8) DEFAULT NULL AFTER `clock_in_longitude`,
ADD COLUMN `clock_out_longitude` DECIMAL(11, 8) DEFAULT NULL AFTER `clock_out_latitude`,
ADD COLUMN `clocked_out_offsite` TINYINT(1) DEFAULT 0 AFTER `clock_out_longitude`;

-- Update existing records to have default offsite value as 0
UPDATE `tblattendance` SET `clocked_out_offsite` = 0 WHERE `clocked_out_offsite` IS NULL;
