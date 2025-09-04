<?php
require_once 'config/config.php';

echo "🚀 Starting ERP Migrations...\n\n";

// Create migrations table
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Migrations table ready\n";
} catch (Exception $e) {
    echo "❌ Error creating migrations table: " . $e->getMessage() . "\n";
    exit;
}

// Get executed migrations
function getExecuted($conn) {
    try {
        $stmt = $conn->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

$executed = getExecuted($conn);
$batch = 1;

try {
    $stmt = $conn->query("SELECT MAX(batch) as max_batch FROM migrations");
    $result = $stmt->fetch();
    $batch = ($result['max_batch'] ?? 0) + 1;
} catch (Exception $e) {
    // Use default
}

// Load migration base class
require_once 'migrations/Migration.php';

// Get all migration files
$files = glob('migrations/*.php');
sort($files);

$success = 0;
$failed = 0;

foreach ($files as $file) {
    $name = basename($file, '.php');
    
    if ($name === 'Migration' || in_array($name, $executed)) {
        continue;
    }
    
    echo "🔄 Running: {$name}\n";
    
    try {
        require_once $file;
        
        // Map file names to actual class names
        $classMap = [
            '001_create_admin_users_table' => 'CreateAdminUsersTable',
            '002_create_suppliers_table' => 'CreateSuppliersTable',
            '002_fix_admin_department_enum' => 'FixAdminDepartmentEnum',
            '003_create_leads_table' => 'CreateLeadsTable',
            '003_normalize_admin_departments' => 'NormalizeAdminDepartments',
            '004_create_quotations_table' => 'CreateQuotationsTable',
            '005_create_quotation_products_table' => 'CreateQuotationProductsTable',
            '006_create_customers_table' => 'CreateCustomersTable',
            '007_create_pi_table' => 'CreatePiTable',
            '008_create_purchase_details_table' => 'CreatePurchaseDetailsTable',
            '009_create_po_details_table' => 'CreatePoDetailsTable',
            '010_create_so_details_table' => 'CreateSoDetailsTable',
            '011_create_bom_details_table' => 'CreateBomDetailsTable',
            '012_create_jci_details_table' => 'CreateJciDetailsTable',
            '013_create_payments_table' => 'CreatePaymentsTable',
            '014_add_lock_system' => 'AddLockSystem',
            '015_create_admin_otps_table' => 'CreateAdminOtpsTable',
            '015_create_notifications_table' => 'CreateNotificationsTable',
            '016_create_buyer_otps_table' => 'CreateBuyerOtpsTable',
            '016_create_buyers_table' => 'CreateBuyersTable',
            '017_create_supplier_quotations_table' => 'CreateSupplierQuotationsTable',
            '018_add_description_finish_to_quotation_products' => 'AddDescriptionFinishToQuotationProducts',
            '019_create_purchase_main_table' => 'CreatePurchaseMainTable',
            '020_create_purchase_items_table' => 'CreatePurchaseItemsTable',
            '021_create_jci_main_table' => 'CreateJciMainTable',
            '022_create_jci_items_table' => 'CreateJciItemsTable',
            '023_create_po_main_table' => 'CreatePoMainTable',
            '024_create_po_items_table' => 'CreatePoItemsTable',
            '025_create_sell_order_table' => 'CreateSellOrderTable',
            '026_create_bom_main_table' => 'CreateBomMainTable',
            '026_create_payment_details_table' => 'CreatePaymentDetailsTable',
            '027_create_bom_wood_table' => 'CreateBomWoodTable',
            '028_create_bom_glow_table' => 'CreateBomGlowTable',
            '029_create_bom_plynydf_table' => 'CreateBomPlynydfTable',
            '030_create_bom_hardware_table' => 'CreateBomHardwareTable',
            '031_create_bom_labour_table' => 'CreateBomLabourTable',
            '032_create_bom_factory_table' => 'CreateBomFactoryTable',
            '033_create_bom_margin_table' => 'CreateBomMarginTable',
            '034_create_communication_admin_user' => 'CreateCommunicationAdminUser',
            '035_add_profile_picture_to_admin_users' => 'AddProfilePictureToAdminUsers',
            '036_create_buyer_quotations_table' => 'CreateBuyerQuotationsTable',
            '037_add_bom_id_fk_to_jci_main' => 'AddBomIdFkToJciMain',
            '038_add_fk_jci_po_to_jci_main' => 'AddFkJciPoToJciMain',
            '039_add_supplier_name_to_bom_tables' => 'AddSupplierNameToBomTables',
            '040_add_approval_status_to_purchase_main' => 'AddApprovalStatusToPurchaseMain',
            '041_add_item_approval_status_to_purchase_items' => 'AddItemApprovalStatusToPurchaseItems',
            '042_add_jci_number_to_payments_table' => 'AddJciNumberToPaymentsTable',
            '043_add_po_number_and_sell_order_number_to_payments_table' => 'AddPoNumberAndSellOrderNumberToPaymentsTable',
            '044_alter_payment_details_cheque_number_to_null' => 'AlterPaymentDetailsChequeNumberToNull',
            '045_alter_payments_payment_number_to_null' => 'Migration_045_alter_payments_payment_number_to_null'
        ];
        
        $patterns = [
            $classMap[$name] ?? str_replace(' ', '', ucwords(str_replace('_', ' ', $name))),
            'Migration_' . $name,
            $name
        ];
        
        $migrationRun = false;
        
        foreach ($patterns as $className) {
            if (class_exists($className)) {
                $migration = new $className($conn);
                $migration->up();
                
                // Record migration
                $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$name, $batch]);
                
                echo "✅ Completed: {$name}\n";
                $success++;
                $migrationRun = true;
                break;
            }
        }
        
        if (!$migrationRun) {
            echo "⚠️ Class not found for: {$name}\n";
            $failed++;
        }
        
    } catch (Exception $e) {
        echo "❌ Failed: {$name} - " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

echo "🎉 Migration Summary:\n";
echo "✅ Success: {$success}\n";
echo "❌ Failed: {$failed}\n";
echo "\nDone!\n";
?>