ALTER TABLE `tbl_students`
ADD COLUMN `is_verified` tinyint(1) DEFAULT 0,
ADD COLUMN `verification_token` varchar(255) DEFAULT NULL;
