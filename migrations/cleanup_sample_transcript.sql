-- Cleanup Script to Remove Sample Transcript Data
-- This removes the sample data added for student ID 11

-- Remove grades for student ID 11 for sample subjects
DELETE FROM grades 
WHERE student_subject_id IN (
    SELECT id FROM student_subjects 
    WHERE student_id = 11 
    AND subject_id IN (
        SELECT id FROM subjects 
        WHERE code IN ('ENG 101', 'MATH 101', 'SCI 101', 'PE 101', 'CS 101')
    )
);

-- Remove student_subjects for student ID 11 with sample subjects
DELETE FROM student_subjects 
WHERE student_id = 11 
AND subject_id IN (
    SELECT id FROM subjects 
    WHERE code IN ('ENG 101', 'MATH 101', 'SCI 101', 'PE 101', 'CS 101')
);

-- Remove sample subjects
DELETE FROM subjects 
WHERE code IN ('ENG 101', 'MATH 101', 'SCI 101', 'PE 101', 'CS 101');

-- Remove duplicate school year 2023-2024 (keep only one entry)
DELETE FROM school_years 
WHERE name = '2023-2024' 
AND id NOT IN (
    SELECT MIN(id) FROM school_years WHERE name = '2023-2024'
);

-- Remove duplicate semesters (keep only one entry each)
DELETE FROM semesters 
WHERE name = '1st Semester' 
AND id NOT IN (
    SELECT MIN(id) FROM semesters WHERE name = '1st Semester'
);

DELETE FROM semesters 
WHERE name = '2nd Semester' 
AND id NOT IN (
    SELECT MIN(id) FROM semesters WHERE name = '2nd Semester'
);

SELECT 'Sample transcript data cleaned up successfully!' AS Status;
