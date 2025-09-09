<?php
/**
 * Project Cleanup Script
 * Removes unused testing files and cleans up the project
 */

echo "🧹 Starting Project Cleanup...\n\n";

// Files to delete (testing/debug/unused files)
$filesToDelete = [
    // Debug files
    'debug_purchase.php',
    'debug_purchase__.php',
    'test_db__.php',
    'check_jci_tables.php',
    
    // Setup/migration files (keep only essential ones)
    'setup.php',
    'setup_php_erp3.php',
    'create_db.php',
    'import_database.php',
    'import_new_data.php',
    'export_new_data.php',
    'manual_data_sync.php',
    'sync_live_data.php',
    'deploy__.php',
    'fix_live__.php',
    'fix_excel_column.php',
    
    // SQL dump files (keep only main ones)
    'crm_purewood.sql',
    'php_erp3_combined.sql',
    'bom_main.sql',
    'jci_main.sql',
    'po_main.sql',
    'get_live_data.sql',
    'missing_data_queries.sql',
    'add_excel_column.sql',
    'fix_quotations_excel_column.sql',
    
    // Text/log files
    'create_table_output.txt',
    
    // Duplicate migration files
    'migrations/047_add_excel_file_to_quotations.php',
    'migrations/create_communication_admin_tables.sql',
    
    // Module specific unused files
    'modules/quotation/export_quotation_excel_error.log',
    'modules/quotation/export_quotation_pdf_error.log',
    'modules/quotation/upload_debug.log',
    'modules/quotation/optimize_config.php',
    'modules/quotation/fix_image_paths.php',
    
    // Purchase module setup files
    'modules/purchase/add_created_by_field.sql',
    'modules/purchase/run_setup.php',
    'modules/purchase/setup_user_filtering.php',
    'modules/purchase/check_duplicates.php',
    'modules/purchase/check_po_ids.php',
    
    // Payment module debug files
    'modules/payments/debug_payment.php',
    'modules/payments/fix_payment_data.php',
    'modules/payments/fix_payment_structure.php',
    'modules/payments/index_backup.php',
    
    // JCI module unused files
    'modules/jci/index.php____',
    'modules/jci/migrate_jci_status.php',
    
    // Supplier admin debug files
    'supplieradmin/check_db.php',
    'supplieradmin/process_excel.php',
    
    // Cypress testing
    'cypress/integration/payment_module_spec.js',
    
    // Scripts
    'scripts/run_module_sql.php',
    
    // Old migration runner
    'migrate.php',
    'run_migration.php',
];

// Directories to clean (remove if empty after file cleanup)
$dirsToCheck = [
    'cypress/integration',
    'cypress',
    'scripts',
    'database',
];

$deletedFiles = 0;
$deletedDirs = 0;
$errors = [];

// Delete files
foreach ($filesToDelete as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "✅ Deleted: $file\n";
            $deletedFiles++;
        } else {
            echo "❌ Failed to delete: $file\n";
            $errors[] = $file;
        }
    }
}

// Clean empty directories
foreach ($dirsToCheck as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath) && count(scandir($fullPath)) == 2) { // Only . and ..
        if (rmdir($fullPath)) {
            echo "✅ Removed empty directory: $dir\n";
            $deletedDirs++;
        }
    }
}

// Clean up specific module files
echo "\n🔧 Cleaning module-specific files...\n";

// Remove backup files
$backupFiles = glob(__DIR__ . '/**/*_backup.php');
$backupFiles = array_merge($backupFiles, glob(__DIR__ . '/**/*.bak'));
$backupFiles = array_merge($backupFiles, glob(__DIR__ . '/**/*~'));

foreach ($backupFiles as $file) {
    if (unlink($file)) {
        echo "✅ Deleted backup: " . basename($file) . "\n";
        $deletedFiles++;
    }
}

// Clean temp directories
$tempDirs = [
    'modules/quotation/temp',
    'temp',
    'tmp'
];

foreach ($tempDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '/*');
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                echo "✅ Cleaned temp file: " . basename($file) . "\n";
                $deletedFiles++;
            }
        }
    }
}

echo "\n📊 Cleanup Summary:\n";
echo "✅ Files deleted: $deletedFiles\n";
echo "✅ Directories removed: $deletedDirs\n";
echo "❌ Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n⚠️ Files that couldn't be deleted:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n🎉 Project cleanup completed!\n";
echo "\n📋 Remaining essential files:\n";
echo "  ✅ php_erp3_db.sql (main database)\n";
echo "  ✅ run_migrations.php (migration runner)\n";
echo "  ✅ setup_complete.php (production setup)\n";
echo "  ✅ All module files (cleaned)\n";
echo "  ✅ All admin interfaces\n";
echo "  ✅ Configuration files\n";

// Create a final project structure report
file_put_contents(__DIR__ . '/PROJECT_STRUCTURE.md', generateProjectStructure());
echo "\n📄 Created PROJECT_STRUCTURE.md with clean project overview\n";

function generateProjectStructure() {
    return "# 🏗️ PHP ERP3 - Clean Project Structure

## 📁 Core Directories

### **Admin Interfaces**
- `superadmin/` - Super admin dashboard & controls
- `salesadmin/` - Sales team interface  
- `accountsadmin/` - Accounts team interface
- `operationadmin/` - Operations team interface
- `productionadmin/` - Production team interface
- `communicationadmin/` - Communication team interface
- `buyeradmin/` - Buyer portal
- `supplieradmin/` - Supplier portal

### **Core Modules**
- `modules/lead/` - Lead management
- `modules/quotation/` - Quotation system
- `modules/customer/` - Customer management
- `modules/pi/` - Proforma Invoice
- `modules/po/` - Purchase Orders
- `modules/bom/` - Bill of Materials
- `modules/jci/` - Job Card Instructions
- `modules/purchase/` - Purchase management
- `modules/payments/` - Payment processing
- `modules/so/` - Sales Orders

### **System Core**
- `config/` - Configuration files
- `core/` - Core services & utilities
- `include/` - Common includes & functions
- `migrations/` - Database migrations
- `assets/` - CSS, JS, images
- `uploads/` - File uploads

### **Libraries**
- `libs/` - Third-party libraries
- `vendor/` - Composer packages

## 🗄️ Database
- **Main DB**: `php_erp3_db.sql`
- **Migrations**: Automated via `run_migrations.php`

## 🚀 Setup
- **Production**: `setup_complete.php`
- **Development**: `run_migrations.php`

## 📊 Key Features
- ✅ Complete ERP workflow (Lead → Payment)
- ✅ Multi-user role system
- ✅ Buyer & Supplier portals
- ✅ Excel import/export
- ✅ PDF generation
- ✅ Email notifications
- ✅ File upload system
- ✅ Approval workflows

---
**Project cleaned and optimized for production use! 🎯**
";
}
?>