<?php

class CreateSupplierQuotationsTable extends Migration {
    
    public function up() {
        $sql = "CREATE TABLE IF NOT EXISTS supplier_quotations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NOT NULL,
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
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $this->conn->exec($sql);
        echo "✅ Table 'supplier_quotations' created successfully\n";
        
        // Add indexes
        try {
            $this->conn->exec("CREATE INDEX idx_supplier_id ON supplier_quotations (supplier_id)");
            echo "✅ Index 'idx_supplier_id' created on 'supplier_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_supplier_id': " . $e->getMessage() . "\n";
        }
        
        try {
            $this->conn->exec("CREATE INDEX idx_status ON supplier_quotations (status)");
            echo "✅ Index 'idx_status' created on 'supplier_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_status': " . $e->getMessage() . "\n";
        }
        
        try {
            $this->conn->exec("CREATE INDEX idx_rfq_reference ON supplier_quotations (rfq_reference)");
            echo "✅ Index 'idx_rfq_reference' created on 'supplier_quotations'\n";
        } catch (Exception $e) {
            echo "❌ Error creating index 'idx_rfq_reference': " . $e->getMessage() . "\n";
        }
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS supplier_quotations");
    }
}
?>