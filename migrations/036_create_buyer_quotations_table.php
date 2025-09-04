<?php

require_once 'Migration.php';

class CreateBuyerQuotationsTable extends Migration {
    
    public function up() {
        $sql = "CREATE TABLE IF NOT EXISTS buyer_quotations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            buyer_id INT NOT NULL,
            rfq_reference VARCHAR(100) NOT NULL,
            quotation_number VARCHAR(100) NOT NULL,
            total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            currency VARCHAR(10) DEFAULT 'INR',
            validity_days INT DEFAULT 30,
            delivery_time VARCHAR(100),
            payment_terms TEXT,
            notes TEXT,
            file_path VARCHAR(500),
            status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
            submitted_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
        )";
        
        $this->conn->exec($sql);
        echo "✅ Table 'buyer_quotations' created successfully\n";
        
        // Add indexes
        try {
            $this->conn->exec("CREATE INDEX idx_buyer_id ON buyer_quotations (buyer_id)");
            echo "✅ Index 'idx_buyer_id' created on 'buyer_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_buyer_id': " . $e->getMessage() . "\n";
        }
        
        try {
            $this->conn->exec("CREATE INDEX idx_status_buyer_quotations ON buyer_quotations (status)");
            echo "✅ Index 'idx_status_buyer_quotations' created on 'buyer_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_status_buyer_quotations': " . $e->getMessage() . "\n";
        }
        
        try {
            $this->conn->exec("CREATE INDEX idx_rfq_reference_buyer_quotations ON buyer_quotations (rfq_reference)");
            echo "✅ Index 'idx_rfq_reference_buyer_quotations' created on 'buyer_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_rfq_reference_buyer_quotations': " . $e->getMessage() . "\n";
        }
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS buyer_quotations");
    }
}
