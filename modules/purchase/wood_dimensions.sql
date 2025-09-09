-- Add Wood dimension columns to purchase_items table
-- Run this SQL on your server database

-- Check if columns exist and add them if they don't
SET @sql = '';
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_name = 'purchase_items' 
AND column_name = 'length_ft';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE purchase_items 
     ADD COLUMN length_ft DECIMAL(10,2) DEFAULT NULL AFTER builty_image,
     ADD COLUMN width_ft DECIMAL(10,2) DEFAULT NULL AFTER length_ft,
     ADD COLUMN thickness_inch DECIMAL(10,2) DEFAULT NULL AFTER width_ft',
    'SELECT "Wood dimension columns already exist" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify columns were added
SHOW COLUMNS FROM purchase_items LIKE '%_ft';
SHOW COLUMNS FROM purchase_items LIKE 'thickness_inch';