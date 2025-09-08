-- Run this SQL on your LIVE server to get new data from last 3 days
-- Copy the results and run them on your local php_erp3 database

-- Set the cutoff date (3 days ago)
SET @cutoff_date = DATE_SUB(NOW(), INTERVAL 3 DAY);

-- Export new BOM records (6 new records expected)
SELECT CONCAT('INSERT INTO bom_main (', 
    GROUP_CONCAT(COLUMN_NAME ORDER BY ORDINAL_POSITION), 
    ') VALUES (', 
    GROUP_CONCAT(
        CASE 
            WHEN DATA_TYPE IN ('varchar', 'text', 'date', 'datetime', 'timestamp') 
            THEN CONCAT('"', REPLACE(COLUMN_NAME, '"', '""'), '"')
            ELSE COLUMN_NAME 
        END 
        ORDER BY ORDINAL_POSITION
    ), 
    ');'
) as sql_statement
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'u404997496_crm_purewood' 
AND TABLE_NAME = 'bom_main'
LIMIT 1;

-- Get actual BOM data
SELECT * FROM bom_main WHERE created_at >= @cutoff_date ORDER BY id;

-- Export new PO records (2 new records expected)
SELECT * FROM po_main WHERE created_at >= @cutoff_date ORDER BY id;

-- Export new SO records (2 new records expected)  
SELECT * FROM sell_order WHERE created_at >= @cutoff_date ORDER BY id;

-- Export new JCI records (7 new records expected)
SELECT * FROM jci_main WHERE created_at >= @cutoff_date ORDER BY id;

-- Export new Purchase records (should be same)
SELECT * FROM purchase_main WHERE created_at >= @cutoff_date ORDER BY id;

-- Export new Payment records (should be same, but check)
SELECT * FROM payments WHERE created_at >= @cutoff_date ORDER BY id;

-- Get related records for new BOMs
SELECT bw.* FROM bom_wood bw 
JOIN bom_main bm ON bw.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bg.* FROM bom_glow bg 
JOIN bom_main bm ON bg.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bh.* FROM bom_hardware bh 
JOIN bom_main bm ON bh.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bl.* FROM bom_labour bl 
JOIN bom_main bm ON bl.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bp.* FROM bom_plynydf bp 
JOIN bom_main bm ON bp.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bf.* FROM bom_factory bf 
JOIN bom_main bm ON bf.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

SELECT bmg.* FROM bom_margin bmg 
JOIN bom_main bm ON bmg.bom_main_id = bm.id 
WHERE bm.created_at >= @cutoff_date;

-- Get related records for new POs
SELECT pi.* FROM po_items pi 
JOIN po_main pm ON pi.po_id = pm.id 
WHERE pm.created_at >= @cutoff_date;

-- Get related records for new JCIs
SELECT ji.* FROM jci_items ji 
JOIN jci_main jm ON ji.jci_id = jm.id 
WHERE jm.created_at >= @cutoff_date;

-- Get related records for new Purchases
SELECT pi.* FROM purchase_items pi 
JOIN purchase_main pm ON pi.purchase_main_id = pm.id 
WHERE pm.created_at >= @cutoff_date;

-- Get related records for new Payments
SELECT pd.* FROM payment_details pd 
JOIN payments p ON pd.payment_id = p.id 
WHERE p.created_at >= @cutoff_date;