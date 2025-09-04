<?php

require_once 'Migration.php';

class CreateCommunicationAdminUser extends Migration {
    
    public function up() {
        $hashedPassword = '$2y$10$6Qt4O7J0RHFKQ.2B2KLfZOO.p5t.qMR87VJkdDoxTB9i7/AOSDkaS'; // Hashed 'Admin@123'
        $this->conn->prepare("INSERT INTO admin_users (name, email, password, department, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([
            'Communication Admin',
            'communication@purewood.in',
            $hashedPassword,
            'communication',
            'approved'
        ]);
        echo "✅ Communication Admin user created\n";
    }
    
    public function down() {
        $this->conn->exec("DELETE FROM admin_users WHERE email = 'communication@purewood.in'");
        echo "✅ Communication Admin user deleted\n";
    }
}
