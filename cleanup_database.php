<?php
/**
 * Database Cleanup Script
 * Removes test data and optimizes database
 */

require_once 'config/config.php';

echo "🗄️ Starting Database Cleanup...\n\n";

global $conn;

try {
    // Remove test/demo data
    echo "🧹 Cleaning test data...\n";
    
    // Clean test leads (keep structure, remove test entries)
    $stmt = $conn->prepare("DELETE FROM leads WHERE customer_name LIKE '%test%' OR customer_name LIKE '%demo%' OR customer_email LIKE '%test%' OR customer_email LIKE '%demo%'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted test leads\n";
    
    // Clean test quotations
    $stmt = $conn->prepare("DELETE FROM quotations WHERE customer_name LIKE '%test%' OR customer_name LIKE '%demo%' OR customer_email LIKE '%test%'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted test quotations\n";
    
    // Clean orphaned quotation products
    $stmt = $conn->prepare("DELETE qp FROM quotation_products qp LEFT JOIN quotations q ON qp.quotation_id = q.id WHERE q.id IS NULL");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted orphaned quotation products\n";
    
    // Clean test customers
    $stmt = $conn->prepare("DELETE FROM customers WHERE company_name LIKE '%test%' OR company_name LIKE '%demo%' OR email LIKE '%test%'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted test customers\n";
    
    // Clean test buyers (keep approved ones)
    $stmt = $conn->prepare("DELETE FROM buyers WHERE (company_name LIKE '%test%' OR contact_person_email LIKE '%test%') AND status = 'pending'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted test buyers\n";
    
    // Clean test suppliers (keep verified ones)
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE (company_name LIKE '%test%' OR contact_person_email LIKE '%test%') AND email_verified = 0");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted test suppliers\n";
    
    // Clean expired OTPs
    $stmt = $conn->prepare("DELETE FROM admin_otps WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted expired admin OTPs\n";
    
    $stmt = $conn->prepare("DELETE FROM buyer_otps WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted expired buyer OTPs\n";
    
    // Clean old notifications (keep last 30 days)
    $stmt = $conn->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✅ Cleaned $deleted old notifications\n";
    
    // Reset auto increment values
    echo "\n🔧 Optimizing database...\n";
    
    $tables = [
        'leads', 'quotations', 'quotation_products', 'customers', 
        'pi', 'purchase_main', 'purchase_items', 'po_main', 'po_items',
        'bom_main', 'jci_main', 'jci_items', 'payments', 'payment_details'
    ];
    
    foreach ($tables as $table) {
        try {
            // Check if table exists
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                // Get max ID
                $stmt = $conn->prepare("SELECT MAX(id) as max_id FROM `$table`");
                $stmt->execute();
                $result = $stmt->fetch();
                $maxId = $result['max_id'] ?? 0;
                $nextId = $maxId + 1;
                
                // Reset auto increment
                $conn->exec("ALTER TABLE `$table` AUTO_INCREMENT = $nextId");
                echo "✅ Optimized $table (next ID: $nextId)\n";
            }
        } catch (Exception $e) {
            echo "⚠️ Skipped $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Optimize tables
    echo "\n⚡ Running table optimization...\n";
    
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        try {
            $conn->exec("OPTIMIZE TABLE `$table`");
            echo "✅ Optimized $table\n";
        } catch (Exception $e) {
            echo "⚠️ Could not optimize $table\n";
        }
    }
    
    // Create production admin if not exists
    echo "\n👤 Setting up production admin...\n";
    
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = 'admin@purewood.com'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $password = password_hash('Admin@2025', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, department, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['Super Admin', 'admin@purewood.com', $password, 'superadmin', 'approved']);
        echo "✅ Created production admin (admin@purewood.com / Admin@2025)\n";
    } else {
        echo "✅ Production admin already exists\n";
    }
    
    // Database statistics
    echo "\n📊 Database Statistics:\n";
    
    $stats = [
        'admin_users' => 'Admin Users',
        'leads' => 'Leads',
        'quotations' => 'Quotations',
        'customers' => 'Customers',
        'buyers' => 'Buyers',
        'suppliers' => 'Suppliers',
        'purchase_main' => 'Purchase Orders',
        'payments' => 'Payments'
    ];
    
    foreach ($stats as $table => $label) {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM `$table`");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "  📋 $label: $count\n";
        } catch (Exception $e) {
            echo "  ⚠️ $label: Table not found\n";
        }
    }
    
    echo "\n🎉 Database cleanup completed successfully!\n";
    echo "\n📋 Production Ready:\n";
    echo "  ✅ Test data removed\n";
    echo "  ✅ Tables optimized\n";
    echo "  ✅ Auto-increment reset\n";
    echo "  ✅ Production admin created\n";
    echo "  ✅ Database statistics updated\n";
    
} catch (Exception $e) {
    echo "❌ Database cleanup failed: " . $e->getMessage() . "\n";
}
?>