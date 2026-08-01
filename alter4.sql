USE gec_placement_portal;

ALTER TABLE tbl_students
ADD COLUMN is_blocked BOOLEAN DEFAULT FALSE AFTER password;
