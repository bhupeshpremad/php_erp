-- Optimize database tables for better performance

-- Add indexes for quotations table
ALTER TABLE quotations 
ADD INDEX idx_lead_id (lead_id),
ADD INDEX idx_quotation_number (quotation_number),
ADD INDEX idx_customer_email (customer_email),
ADD INDEX idx_approve_locked (approve, locked);

-- Add indexes for quotation_products table  
ALTER TABLE quotation_products
ADD INDEX idx_quotation_id (quotation_id),
ADD INDEX idx_item_name (item_name),
ADD INDEX idx_item_code (item_code);

-- Optimize table storage
OPTIMIZE TABLE quotations;
OPTIMIZE TABLE quotation_products;

-- Update table engine to InnoDB for better performance (if not already)
ALTER TABLE quotations ENGINE=InnoDB;
ALTER TABLE quotation_products ENGINE=InnoDB;