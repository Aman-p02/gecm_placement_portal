-- SQL Script to Create tbl_placement_team Table & Populate Exact File Name Data

CREATE TABLE IF NOT EXISTS `tbl_placement_team` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `designation` VARCHAR(150) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `role` VARCHAR(200) NOT NULL,
    `photo` VARCHAR(255) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert 14 Faculty Members Initial Data with User's Exact File Names
INSERT INTO `tbl_placement_team` (`name`, `designation`, `department`, `role`, `photo`, `sort_order`) VALUES
('Dr. M M Goyani', 'Associate Professor', 'CE Department', 'Placement Coordinator - Institute', 'assets/images/team/MMG.jpg', 1),
('Prof. P M Mistri', 'Assistant Professor', 'ME Department', 'Placement Co-Coordinator - Institute', 'assets/images/team/mech_PMM.jpg', 2),
('Prof. A. J Patel', 'Assistant Professor', 'Civil Department', 'Departmental Placement Coordinator', 'assets/images/team/app_AJP.jpg', 3),
('Prof. M. G. Patel', 'Asst. Prof.', 'ME Department', 'Departmental Placement coordinator', 'assets/images/team/mech_MGP.jpg', 4),
('Prof. J. C. Gamit', 'Asst. Prof.', 'ME Department', 'Departmental Placement coordinator', 'assets/images/team/mech_JCG.jpg', 5),
('Prof. S. L. Ghanchi', 'Asst. Prof.', 'ME Department', 'Departmental Placement coordinator', 'assets/images/team/mech_SLG.jpg', 6),
('Prof. H. K. Sharma', 'Asst. Prof.', 'Civil Department', 'Departmental Placement coordinator', 'assets/images/team/civil_HKS.jpg', 7),
('Prof. A. D. Chaudhari', 'Asst. Prof.', 'IT Department', 'Departmental Placement coordinator', 'assets/images/team/it_ac.jpg', 8),
('Prof. S. R. Patel', 'Asst. Prof.', 'CE Department', 'Departmental Placement coordinator', 'assets/images/team/ce_srp.jpg', 9),
('Prof. N. V. Nagekar', 'Asst. Prof.', 'CE Department', 'Departmental Placement coordinator', 'assets/images/team/ce_nvn.jpg', 10),
('Prof. M. V. Chauhan', 'Asst. Prof.', 'IT Department', 'Departmental Placement coordinator', 'assets/images/team/CE_MC.jpg', 11),
('Prof. P. V. Patel', 'Asst. Prof.', 'EC Department', 'Departmental Placement coordinator', 'assets/images/team/ec_PVP.jpg', 12),
('Prof. B. A. Brahmbhatt', 'Asst. Prof.', 'EC Department', 'Departmental Placement coordinator', 'assets/images/team/ec_BAB.jpg', 13),
('Prof. D. U. Thakkar', 'Asst. Prof.', 'EE Department', 'Departmental Placement coordinator', 'assets/images/team/ee_darshan.jpg', 14);
