-- Add grade_4th column to grades table
ALTER TABLE grades ADD COLUMN grade_4th decimal(5,2) DEFAULT 0.00 AFTER grade_3rd;
