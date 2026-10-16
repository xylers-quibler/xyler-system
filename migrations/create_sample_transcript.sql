-- Sample Transcript Data for Testing TOR Print/PDF Functionality
-- This script creates sample subjects, enrollments, and grades for student ID 11

-- First, check if we have the necessary data
-- Assuming student ID 11 exists, we'll create sample transcript data

-- Insert sample subjects if they don't exist
INSERT IGNORE INTO subjects (code, name, units, prerequisite_id) VALUES
('ENG 101', 'English Communication I', 3, NULL),
('MATH 101', 'College Algebra', 3, NULL),
('SCI 101', 'General Biology', 3, NULL),
('PE 101', 'Physical Education 1', 2, NULL),
('CS 101', 'Introduction to Computing', 3, NULL);

-- Get or create school year 2023-2024
INSERT IGNORE INTO school_years (name) VALUES ('2023-2024');
SET @sy_id = (SELECT id FROM school_years WHERE name = '2023-2024' LIMIT 1);

-- Get or create semesters
INSERT IGNORE INTO semesters (name) VALUES ('1st Semester'), ('2nd Semester');
SET @sem1_id = (SELECT id FROM semesters WHERE name = '1st Semester' LIMIT 1);
SET @sem2_id = (SELECT id FROM semesters WHERE name = '2nd Semester' LIMIT 1);

-- Enroll student ID 11 in subjects for 1st Semester
INSERT IGNORE INTO student_subjects (student_id, subject_id, sy_id, sem_id, status)
SELECT 11, id, @sy_id, @sem1_id, 'Enrolled'
FROM subjects WHERE code IN ('ENG 101', 'MATH 101', 'SCI 101', 'PE 101');

-- Enroll student ID 11 in subjects for 2nd Semester
INSERT IGNORE INTO student_subjects (student_id, subject_id, sy_id, sem_id, status)
SELECT 11, id, @sy_id, @sem2_id, 'Enrolled'
FROM subjects WHERE code IN ('CS 101');

-- Add grades for 1st Semester subjects
INSERT IGNORE INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks)
SELECT ss.id, 85, 88, 90, 87, 87.5, 'Passed'
FROM student_subjects ss
WHERE ss.student_id = 11 AND ss.sy_id = @sy_id AND ss.sem_id = @sem1_id AND ss.subject_id IN (SELECT id FROM subjects WHERE code = 'ENG 101');

INSERT IGNORE INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks)
SELECT ss.id, 78, 82, 85, 80, 81.25, 'Passed'
FROM student_subjects ss
WHERE ss.student_id = 11 AND ss.sy_id = @sy_id AND ss.sem_id = @sem1_id AND ss.subject_id IN (SELECT id FROM subjects WHERE code = 'MATH 101');

INSERT IGNORE INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks)
SELECT ss.id, 90, 92, 88, 91, 90.25, 'Passed'
FROM student_subjects ss
WHERE ss.student_id = 11 AND ss.sy_id = @sy_id AND ss.sem_id = @sem1_id AND ss.subject_id IN (SELECT id FROM subjects WHERE code = 'SCI 101');

INSERT IGNORE INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks)
SELECT ss.id, 95, 95, 95, 95, 95, 'Passed'
FROM student_subjects ss
WHERE ss.student_id = 11 AND ss.sy_id = @sy_id AND ss.sem_id = @sem1_id AND ss.subject_id IN (SELECT id FROM subjects WHERE code = 'PE 101');

-- Add grades for 2nd Semester subject
INSERT IGNORE INTO grades (student_subject_id, grade_1st, grade_2nd, grade_3rd, grade_4th, grade_final, remarks)
SELECT ss.id, 88, 90, 92, 89, 89.75, 'Passed'
FROM student_subjects ss
WHERE ss.student_id = 11 AND ss.sy_id = @sy_id AND ss.sem_id = @sem2_id AND ss.subject_id IN (SELECT id FROM subjects WHERE code = 'CS 101');

-- Verify the data
SELECT 'Sample transcript data created successfully!' AS Status;
