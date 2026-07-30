-- GEC Placement Portal - Student Module Database Schema
CREATE DATABASE IF NOT EXISTS `gec_placement_portal` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gec_placement_portal`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Students Table (Authentication & Basic Info)
CREATE TABLE IF NOT EXISTS `tbl_students` (
  `student_id` INT AUTO_INCREMENT PRIMARY KEY,
  `enrollment_no` VARCHAR(50) UNIQUE NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `branch` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Student Profile Table (Extended Details & Files)
CREATE TABLE IF NOT EXISTS `tbl_student_profile` (
  `profile_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `profile_pic` VARCHAR(255) DEFAULT NULL,
  `sem5_cgpa` DECIMAL(4,2) DEFAULT NULL,
  `sem5_cpi` DECIMAL(4,2) DEFAULT NULL,
  `sem6_cgpa` DECIMAL(4,2) DEFAULT NULL,
  `sem6_cpi` DECIMAL(4,2) DEFAULT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `resume_path` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `tbl_students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Student Skills Table
CREATE TABLE IF NOT EXISTS `tbl_student_skills` (
  `skill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`student_id`) REFERENCES `tbl_students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
