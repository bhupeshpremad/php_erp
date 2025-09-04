<?php

require_once 'Migration.php';

class AddFkJciPoToJciMain extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE `jci_main` ADD CONSTRAINT `fk_jci_po` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE SET NULL;";
        $this->conn->exec($sql);
    }
    
    public function down() {
        $sql = "ALTER TABLE `jci_main` DROP FOREIGN KEY `fk_jci_po`;";
        $this->conn->exec($sql);
    }
}


