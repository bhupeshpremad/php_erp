<?php

class AddExcelFileToQuotations extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE quotations ADD COLUMN excel_file VARCHAR(255) NULL AFTER updated_at";
        $this->conn->exec($sql);
        echo "✅ Added excel_file column to quotations table\n";
    }
    
    public function down() {
        $sql = "ALTER TABLE quotations DROP COLUMN excel_file";
        $this->conn->exec($sql);
        echo "✅ Removed excel_file column from quotations table\n";
    }
}
?>