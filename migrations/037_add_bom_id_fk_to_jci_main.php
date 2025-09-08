<?php

require_once 'Migration.php';

class AddBomIdFkToJciMain extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE `jci_main` ADD CONSTRAINT `fk_jci_bom` FOREIGN KEY (`bom_id`) REFERENCES `bom_main` (`id`) ON DELETE SET NULL;";
        $this->conn->exec($sql);
    }
    
    public function down() {
        $sql = "ALTER TABLE `jci_main` DROP FOREIGN KEY `fk_jci_bom`;";
        $this->conn->exec($sql);
    }
}
