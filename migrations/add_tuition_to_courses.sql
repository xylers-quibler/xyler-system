-- Add tuition settings columns to courses table
ALTER TABLE courses ADD COLUMN fee_per_unit decimal(10,2) DEFAULT 500.00 AFTER max_units;
ALTER TABLE courses ADD COLUMN misc_fee decimal(10,2) DEFAULT 50.00 AFTER fee_per_unit;
