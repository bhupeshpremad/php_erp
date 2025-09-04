<?php

require_once 'Migration.php';

class AddProfilePictureToAdminUsers extends Migration {
    
    public function up() {
        $this->addColumn('admin_users', 'profile_picture', 'VARCHAR(255) DEFAULT NULL');
        echo "✅ Added 'profile_picture' column to 'admin_users' table\n";
    }
    
    public function down() {
        $this->dropColumn('admin_users', 'profile_picture');
        echo "✅ Dropped 'profile_picture' column from 'admin_users' table\n";
    }
}
