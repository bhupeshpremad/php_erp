<?php

require_once 'Migration.php';

class AddPurchaseItemDimensionsAndRowId extends Migration {
    public function up() {
        // Add missing columns used by purchase module if they don't exist
        $sql = [];
        $sql[] = "ALTER TABLE `purchase_items` ADD COLUMN IF NOT EXISTS `row_id` INT NULL AFTER `builty_image`";
        $sql[] = "ALTER TABLE `purchase_items` ADD COLUMN IF NOT EXISTS `length_ft` DECIMAL(10,3) NULL AFTER `builty_image`";
        $sql[] = "ALTER TABLE `purchase_items` ADD COLUMN IF NOT EXISTS `width_ft` DECIMAL(10,3) NULL AFTER `length_ft`";
        $sql[] = "ALTER TABLE `purchase_items` ADD COLUMN IF NOT EXISTS `thickness_inch` DECIMAL(10,3) NULL AFTER `width_ft`";

        // Helpful composite index for matching/rendering
        $sql[] = "ALTER TABLE `purchase_items` ADD INDEX IF NOT EXISTS `idx_purchase_match` (`purchase_main_id`, `job_card_number`, `product_type`, `product_name`, `row_id`)";

        foreach ($sql as $q) {
            try {
                $this->conn->exec($q);
            } catch (\Throwable $e) {
                // Some MySQL/MariaDB versions may not support IF NOT EXISTS for columns; fallback check
                if (stripos($e->getMessage(), 'IF NOT EXISTS') !== false) {
                    // Retry without IF NOT EXISTS guarded by information_schema
                    if (strpos($q, 'ADD COLUMN') !== false) {
                        if (preg_match('/ADD COLUMN IF NOT EXISTS `([^`]+)`/i', $q, $m)) {
                            $col = $m[1];
                            $exists = $this->columnExists('purchase_items', $col);
                            if (!$exists) {
                                $q2 = str_replace(' IF NOT EXISTS', '', $q);
                                $this->conn->exec($q2);
                            }
                        }
                    } elseif (strpos($q, 'ADD INDEX') !== false) {
                        if (preg_match('/ADD INDEX IF NOT EXISTS `([^`]+)`/i', $q, $m)) {
                            $idx = $m[1];
                            $exists = $this->indexExists('purchase_items', $idx);
                            if (!$exists) {
                                $q2 = str_replace(' IF NOT EXISTS', '', $q);
                                $this->conn->exec($q2);
                            }
                        }
                    }
                } else if (stripos($e->getMessage(), 'Duplicate') !== false) {
                    // Ignore duplicates
                } else {
                    throw $e;
                }
            }
        }

        echo "✅ purchase_items updated with row_id and wood dimensions (if missing)\n";
    }

    public function down() {
        // Drop index then columns (if exist)
        try {
            $this->conn->exec("ALTER TABLE `purchase_items` DROP INDEX `idx_purchase_match`");
        } catch (\Throwable $e) { /* ignore */ }
        foreach (['thickness_inch','width_ft','length_ft','row_id'] as $col) {
            try {
                if ($this->columnExists('purchase_items', $col)) {
                    $this->conn->exec("ALTER TABLE `purchase_items` DROP COLUMN `$col`");
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        echo "✅ Reverted purchase_items dimension and row_id changes\n";
    }
}
