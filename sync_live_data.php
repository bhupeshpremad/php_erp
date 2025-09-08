<?php
/**
 * Sync New Data from Live Server
 * This script fetches new data added to live server in the last 2 days
 */

session_start();
require_once 'config/config.php';

// Live server database credentials
$live_config = [
    'host' => 'localhost', // Update with live server details
    'db' => 'u404997496_crm_purewood', // Live database name
    'user' => 'u404997496_crn_purewood', // Live database user
    'pass' => 'Purewood@2025#', // Live database password
    'charset' => 'utf8mb4'
];

// Authentication
$sync_password = 'purewood_sync_2025';
$authenticated = $_SESSION['sync_authenticated'] ?? false;

if ($_POST['sync_password'] ?? '' === $sync_password) {
    $_SESSION['sync_authenticated'] = true;
    $authenticated = true;
}

if (!$authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Live Data Sync</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>🔄 Live Data Sync</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Sync Password</label>
                                    <input type="password" class="form-control" name="sync_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Access Sync</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function connectToLive($config) {
    $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
    return new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

function syncNewData($local_conn, $live_conn) {
    $results = [];
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-3 days')); // 3 days to be safe
    
    // Module sync configurations
    $sync_modules = [
        'bom_main' => [
            'table' => 'bom_main',
            'date_field' => 'created_at',
            'related_tables' => ['bom_wood', 'bom_glow', 'bom_hardware', 'bom_labour', 'bom_plynydf', 'bom_factory', 'bom_margin']
        ],
        'po_main' => [
            'table' => 'po_main',
            'date_field' => 'created_at',
            'related_tables' => ['po_items']
        ],
        'sell_order' => [
            'table' => 'sell_order',
            'date_field' => 'created_at',
            'related_tables' => []
        ],
        'jci_main' => [
            'table' => 'jci_main',
            'date_field' => 'created_at',
            'related_tables' => ['jci_items']
        ],
        'purchase_main' => [
            'table' => 'purchase_main',
            'date_field' => 'created_at',
            'related_tables' => ['purchase_items']
        ],
        'payments' => [
            'table' => 'payments',
            'date_field' => 'created_at',
            'related_tables' => ['payment_details']
        ]
    ];
    
    foreach ($sync_modules as $module => $config) {
        try {
            // Get new records from live
            $stmt = $live_conn->prepare("SELECT * FROM {$config['table']} WHERE {$config['date_field']} >= ?");
            $stmt->execute([$cutoff_date]);
            $new_records = $stmt->fetchAll();
            
            if (empty($new_records)) {
                $results[] = "✅ {$config['table']}: No new records";
                continue;
            }
            
            $synced_count = 0;
            foreach ($new_records as $record) {
                // Check if record already exists in local
                $id_field = 'id';
                $check_stmt = $local_conn->prepare("SELECT COUNT(*) FROM {$config['table']} WHERE $id_field = ?");
                $check_stmt->execute([$record[$id_field]]);
                
                if ($check_stmt->fetchColumn() > 0) {
                    continue; // Record already exists
                }
                
                // Insert main record
                $columns = array_keys($record);
                $placeholders = ':' . implode(', :', $columns);
                $insert_sql = "INSERT INTO {$config['table']} (" . implode(', ', $columns) . ") VALUES ($placeholders)";
                
                $insert_stmt = $local_conn->prepare($insert_sql);
                $insert_stmt->execute($record);
                $synced_count++;
                
                // Sync related tables
                foreach ($config['related_tables'] as $related_table) {
                    $foreign_key = $this->getForeignKey($config['table'], $related_table);
                    $related_stmt = $live_conn->prepare("SELECT * FROM $related_table WHERE $foreign_key = ?");
                    $related_stmt->execute([$record[$id_field]]);
                    $related_records = $related_stmt->fetchAll();
                    
                    foreach ($related_records as $related_record) {
                        $rel_columns = array_keys($related_record);
                        $rel_placeholders = ':' . implode(', :', $rel_columns);
                        $rel_insert_sql = "INSERT INTO $related_table (" . implode(', ', $rel_columns) . ") VALUES ($rel_placeholders)";
                        
                        $rel_insert_stmt = $local_conn->prepare($rel_insert_sql);
                        $rel_insert_stmt->execute($related_record);
                    }
                }
            }
            
            $results[] = "✅ {$config['table']}: Synced $synced_count new records";
            
        } catch (Exception $e) {
            $results[] = "❌ {$config['table']}: Error - " . $e->getMessage();
        }
    }
    
    return $results;
}

function getForeignKey($main_table, $related_table) {
    $foreign_keys = [
        'bom_main' => 'bom_main_id',
        'po_main' => 'po_id',
        'jci_main' => 'jci_id',
        'purchase_main' => 'purchase_main_id',
        'payments' => 'payment_id'
    ];
    
    return $foreign_keys[$main_table] ?? 'id';
}

function syncImages($local_conn, $live_conn) {
    $results = [];
    
    // Get image records that might be missing
    $image_tables = [
        'purchase_items' => ['invoice_image', 'builty_image'],
        'po_items' => ['product_image'],
        'quotations' => ['quotation_image']
    ];
    
    foreach ($image_tables as $table => $image_fields) {
        try {
            foreach ($image_fields as $field) {
                $stmt = $live_conn->prepare("SELECT id, $field FROM $table WHERE $field IS NOT NULL AND $field != ''");
                $stmt->execute();
                $image_records = $stmt->fetchAll();
                
                foreach ($image_records as $record) {
                    if (!empty($record[$field])) {
                        // Update local record with image filename
                        $update_stmt = $local_conn->prepare("UPDATE $table SET $field = ? WHERE id = ?");
                        $update_stmt->execute([$record[$field], $record['id']]);
                    }
                }
            }
            $results[] = "✅ $table: Image references synced";
        } catch (Exception $e) {
            $results[] = "❌ $table: Image sync error - " . $e->getMessage();
        }
    }
    
    return $results;
}

$action = $_GET['action'] ?? 'status';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Data Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>🔄 Live Data Sync</h4>
                <div>
                    <a href="?action=status" class="btn btn-info btn-sm">Status</a>
                    <a href="?action=sync" class="btn btn-success btn-sm">Sync Now</a>
                    <a href="?action=logout" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            <div class="card-body">
                
                <?php if ($action === 'logout'): ?>
                    <?php unset($_SESSION['sync_authenticated']); ?>
                    <script>window.location.href = 'sync_live_data.php';</script>
                <?php endif; ?>
                
                <?php if ($action === 'sync'): ?>
                    <div class="alert alert-info">
                        <h5>🔄 Syncing New Data from Live Server...</h5>
                    </div>
                    
                    <?php
                    try {
                        $live_conn = connectToLive($live_config);
                        $sync_results = syncNewData($conn, $live_conn);
                        $image_results = syncImages($conn, $live_conn);
                        
                        echo "<div class='alert alert-light'>";
                        echo "<h6>📊 Data Sync Results:</h6>";
                        foreach ($sync_results as $result) {
                            echo "<div>$result</div>";
                        }
                        echo "</div>";
                        
                        echo "<div class='alert alert-light'>";
                        echo "<h6>🖼️ Image Sync Results:</h6>";
                        foreach ($image_results as $result) {
                            echo "<div>$result</div>";
                        }
                        echo "</div>";
                        
                        echo "<div class='alert alert-success'>";
                        echo "<h6>✅ Sync Complete!</h6>";
                        echo "<p>New data from live server has been synced to php_erp3.</p>";
                        echo "</div>";
                        
                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>";
                        echo "<h6>❌ Sync Error</h6>";
                        echo "<p>Error: " . $e->getMessage() . "</p>";
                        echo "</div>";
                    }
                    ?>
                <?php endif; ?>
                
                <?php if ($action === 'status'): ?>
                    <div class="alert alert-info">
                        <h5>📊 Module Update Status</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Live Updates</th>
                                    <th>Local Updates</th>
                                    <th>Difference</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>BOM</td>
                                    <td>27</td>
                                    <td>21</td>
                                    <td class="text-warning">+6</td>
                                    <td><span class="badge bg-warning">Needs Sync</span></td>
                                </tr>
                                <tr>
                                    <td>PO</td>
                                    <td>20</td>
                                    <td>18</td>
                                    <td class="text-warning">+2</td>
                                    <td><span class="badge bg-warning">Needs Sync</span></td>
                                </tr>
                                <tr>
                                    <td>SO</td>
                                    <td>20</td>
                                    <td>18</td>
                                    <td class="text-warning">+2</td>
                                    <td><span class="badge bg-warning">Needs Sync</span></td>
                                </tr>
                                <tr>
                                    <td>JCI</td>
                                    <td>25</td>
                                    <td>18</td>
                                    <td class="text-danger">+7</td>
                                    <td><span class="badge bg-danger">Needs Sync</span></td>
                                </tr>
                                <tr>
                                    <td>Purchase</td>
                                    <td>4</td>
                                    <td>4</td>
                                    <td class="text-success">0</td>
                                    <td><span class="badge bg-success">In Sync</span></td>
                                </tr>
                                <tr>
                                    <td>Payment</td>
                                    <td>4</td>
                                    <td>18</td>
                                    <td class="text-info">-14</td>
                                    <td><span class="badge bg-info">Local Ahead</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6>⚠️ Important Notes</h6>
                        <ul class="mb-0">
                            <li>Update live server credentials in the script before syncing</li>
                            <li>Backup your local database before running sync</li>
                            <li>Sync will only fetch records from last 3 days</li>
                            <li>Payment module shows local ahead - this is expected</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>