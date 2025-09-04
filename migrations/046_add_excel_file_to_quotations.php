<?php

require_once __DIR__ . '/Migration.php';

class AddExcelFileToQuotations extends Migration
{
    public function up()
    {
        $sql = "ALTER TABLE quotations ADD COLUMN excel_file VARCHAR(255) NULL AFTER terms_of_delivery";
        $this->execute($sql);
        echo "Added excel_file column to quotations table.\n";
    }

    public function down()
    {
        $sql = "ALTER TABLE quotations DROP COLUMN excel_file";
        $this->execute($sql);
        echo "Removed excel_file column from quotations table.\n";
    }
}