USE gec_placement_portal;

-- Update tbl_students to hold email and phone
ALTER TABLE tbl_students 
ADD COLUMN email VARCHAR(150) AFTER branch,
ADD COLUMN phone_number VARCHAR(15) AFTER email;

-- Expand tbl_student_profile
ALTER TABLE tbl_student_profile
ADD COLUMN gender ENUM('Male', 'Female', 'Other') AFTER student_id,
ADD COLUMN dob DATE AFTER gender,
ADD COLUMN physically_handicap BOOLEAN DEFAULT 0 AFTER dob,
ADD COLUMN category ENUM('General', 'OBC', 'SC', 'ST') AFTER physically_handicap,
ADD COLUMN district VARCHAR(100) AFTER category,
ADD COLUMN course VARCHAR(50) AFTER district,
ADD COLUMN cpi_percentage DECIMAL(5,2) AFTER sem6_cpi,
ADD COLUMN finishing_school BOOLEAN DEFAULT 0 AFTER cpi_percentage,
ADD COLUMN skill_training BOOLEAN DEFAULT 0 AFTER finishing_school,
ADD COLUMN training_details TEXT AFTER skill_training,
ADD COLUMN hsc_percentage DECIMAL(5,2) AFTER training_details,
ADD COLUMN ssc_percentage DECIMAL(5,2) AFTER hsc_percentage;

-- Create tbl_admins
CREATE TABLE IF NOT EXISTS tbl_admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone_number VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'subadmin') NOT NULL,
    branch VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_companies
CREATE TABLE IF NOT EXISTS tbl_companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    logo_path VARCHAR(255),
    document_path VARCHAR(255),
    last_date_to_apply DATE NOT NULL,
    added_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by) REFERENCES tbl_admins(admin_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_company_branches
CREATE TABLE IF NOT EXISTS tbl_company_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    branch_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (company_id) REFERENCES tbl_companies(company_id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_branch (company_id, branch_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_applications
CREATE TABLE IF NOT EXISTS tbl_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    company_id INT NOT NULL,
    status ENUM('Applied', 'In Progress', 'Selected', 'Rejected') DEFAULT 'Applied',
    round_details VARCHAR(255) DEFAULT 'Application Submitted.',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES tbl_students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES tbl_companies(company_id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (student_id, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Added on 2026-08-06
ALTER TABLE tbl_placement_team ADD COLUMN email VARCHAR(255) DEFAULT 'faculty.name@gecmodasa.ac.in' AFTER role;
