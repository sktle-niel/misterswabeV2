-- Remove redundant columns from inventory table
-- Run this SQL in your database

ALTER TABLE inventory 
DROP COLUMN size, 
DROP COLUMN size_quantities, 
DROP COLUMN color;
