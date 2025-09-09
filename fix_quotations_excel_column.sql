-- Add excel_file column to quotations table
ALTER TABLE quotations ADD COLUMN excel_file VARCHAR(255) NULL AFTER updated_at;

-- Verify the column was added
DESCRIBE quotations;