-- Add created_by field to purchase_main table
ALTER TABLE purchase_main ADD COLUMN created_by INT(11) NULL AFTER bom_number;

-- Update existing records to set created_by to 1 (superadmin) if NULL
UPDATE purchase_main SET created_by = 1 WHERE created_by IS NULL;

-- Add index for better performance
ALTER TABLE purchase_main ADD INDEX idx_created_by (created_by);