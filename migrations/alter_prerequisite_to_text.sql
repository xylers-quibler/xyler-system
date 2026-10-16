-- Migration: Change prerequisite_id to prerequisite (varchar) for free text input
-- This allows the dean to write any prerequisite text instead of selecting from existing subjects

USE school_system;

-- Add new prerequisite column
ALTER TABLE subjects ADD COLUMN prerequisite VARCHAR(255) DEFAULT NULL AFTER prerequisite_id;

-- Migrate existing data: copy prerequisite_id values to prerequisite as text
UPDATE subjects s
JOIN subjects p ON s.prerequisite_id = p.id
SET s.prerequisite = p.code
WHERE s.prerequisite_id IS NOT NULL;

-- Drop the old prerequisite_id column
ALTER TABLE subjects DROP COLUMN prerequisite_id;
