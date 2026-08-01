USE gec_placement_portal;

ALTER TABLE tbl_student_profile
ADD COLUMN first_name VARCHAR(100) AFTER student_id,
ADD COLUMN middle_name VARCHAR(100) AFTER first_name,
ADD COLUMN surname VARCHAR(100) AFTER middle_name,
ADD COLUMN father_name VARCHAR(100) AFTER surname,
ADD COLUMN mother_name VARCHAR(100) AFTER father_name;
