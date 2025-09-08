<?php
/**
 * Complete ERP System Setup Script
 * This script sets up the entire ERP system with all required tables and data
 */

session_start();
require_once 'config/config.php';

// Setup authentication
$setup_password = 'purewood_setup_2025';
$authenticated = $_SESSION['setup_authenticated'] ?? false;

if ($_POST['setup_password'] ?? '' === $setup_password) {
    $_SESSION['setup_authenticated'] = true;
    $authenticated = true;
}

if (!$authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Complete ERP Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>🚀 Complete ERP System Setup</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Setup Password</label>
                                    <input type="password" class="form-control" name="setup_password" required>
                                    <small class="text-muted">Enter setup password to proceed</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Access Setup</button>
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

$action = $_GET['action'] ?? 'status';

function createCompleteDatabase($conn) {
    $results = [];
    
    try {
        // Read and execute the complete SQL file
        $sqlFile = __DIR__ . '/php_erp3_db.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Database SQL file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $conn->beginTransaction();
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(SET|START|COMMIT|\/\*|--)/i', $statement)) {
                try {
                    $conn->exec($statement);
                    if (preg_match('/CREATE TABLE.*`(\w+)`/i', $statement, $matches)) {
                        $results[] = "✅ Created table: {$matches[1]}";
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $results[] = "⚠️ Warning: " . $e->getMessage();
                    }
                }
            }
        }
        
        $conn->commit();
        $results[] = "✅ Database setup completed successfully!";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $results[] = "❌ Error: " . $e->getMessage();
    }
    
    return $results;
}

function createDirectories() {
    $results = [];
    $directories = [
        'uploads',
        'uploads/invoice',
        'uploads/builty',
        'uploads/po',
        'uploads/quotation',
        'modules/purchase/uploads',
        'modules/purchase/uploads/invoice',
        'modules/purchase/uploads/Builty'
    ];
    
    foreach ($directories as $dir) {
        $fullPath = __DIR__ . '/' . $dir;
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                $results[] = "✅ Created directory: $dir";
            } else {
                $results[] = "❌ Failed to create directory: $dir";
            }
        } else {
            $results[] = "✅ Directory exists: $dir";
        }
    }
    
    return $results;
}

function createDefaultAdmin($conn) {
    $results = [];
    
    try {
        // Check if superadmin exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE email = 'superadmin@purewood.in'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $password = password_hash('Admin@123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, department, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute(['Super Admin', 'superadmin@purewood.in', $password, 'accounts', 'approved']);
            $results[] = "✅ Created default superadmin account";
            $results[] = "📧 Email: superadmin@purewood.in";
            $results[] = "🔑 Password: Admin@123";
        } else {
            $results[] = "✅ Superadmin account already exists";
        }
        
    } catch (Exception $e) {
        $results[] = "❌ Error creating admin: " . $e->getMessage();
    }
    
    return $results;
}

function checkSystemRequirements() {
    $results = [];
    
    // PHP Version
    $phpVersion = PHP_VERSION;
    if (version_compare($phpVersion, '7.4.0', '>=')) {
        $results[] = "✅ PHP Version: $phpVersion";
    } else {
        $results[] = "❌ PHP Version: $phpVersion (Requires 7.4+)";
    }
    
    // Required Extensions
    $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            $results[] = "✅ Extension: $ext";
        } else {
            $results[] = "❌ Missing Extension: $ext";
        }
    }
    
    // Database Connection
    try {
        global $conn;
        $stmt = $conn->query("SELECT VERSION() as version");
        $version = $stmt->fetch()['version'];
        $results[] = "✅ Database Connection: MySQL $version";
    } catch (Exception $e) {
        $results[] = "❌ Database Connection Failed: " . $e->getMessage();
    }
    
    return $results;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete ERP System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-success { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
        .setup-section { margin-bottom: 2rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>🚀 Complete ERP System Setup</h4>
                        <div>
                            <a href="?action=status" class="btn btn-info btn-sm">System Status</a>
                            <a href="?action=setup" class="btn btn-success btn-sm">Complete Setup</a>
                            <a href="?action=logout" class="btn btn-secondary btn-sm">Logout</a>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($action === 'logout'): ?>
                            <?php unset($_SESSION['setup_authenticated']); ?>
                            <script>window.location.href = 'setup_complete.php';</script>
                        <?php endif; ?>
                        
                        <?php if ($action === 'setup'): ?>
                            <div class="setup-section">
                                <h5>🔧 System Requirements Check</h5>
                                <div class="alert alert-light">
                                    <?php foreach (checkSystemRequirements() as $result): ?>
                                        <div><?php echo $result; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="setup-section">
                                <h5>📁 Creating Directories</h5>
                                <div class="alert alert-light">
                                    <?php foreach (createDirectories() as $result): ?>
                                        <div><?php echo $result; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="setup-section">
                                <h5>🗄️ Setting up Database</h5>
                                <div class="alert alert-light">
                                    <?php foreach (createCompleteDatabase($conn) as $result): ?>
                                        <div><?php echo $result; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="setup-section">
                                <h5>👤 Creating Default Admin</h5>
                                <div class="alert alert-light">
                                    <?php foreach (createDefaultAdmin($conn) as $result): ?>
                                        <div><?php echo $result; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="alert alert-success">
                                <h6>🎉 Setup Complete!</h6>
                                <p>Your ERP system is now ready to use.</p>
                                <a href="index.php" class="btn btn-success">Go to ERP System</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($action === 'status'): ?>
                            <div class="setup-section">
                                <h5>🔧 System Requirements</h5>
                                <div class="alert alert-light">
                                    <?php foreach (checkSystemRequirements() as $result): ?>
                                        <div><?php echo $result; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="setup-section">
                                <h5>📊 Database Tables Status</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Table</th>
                                                <th>Status</th>
                                                <th>Records</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $tables = [
                                                'admin_users' => 'Admin Users',
                                                'leads' => 'Leads',
                                                'quotations' => 'Quotations',
                                                'quotation_products' => 'Quotation Products',
                                                'po_main' => 'Purchase Orders',
                                                'po_items' => 'PO Items',
                                                'bom_main' => 'BOM Main',
                                                'jci_main' => 'Job Card Instructions',
                                                'purchase_main' => 'Purchase Main',
                                                'purchase_items' => 'Purchase Items',
                                                'payments' => 'Payments',
                                                'payment_details' => 'Payment Details'
                                            ];
                                            
                                            foreach ($tables as $table => $description) {
                                                try {
                                                    $stmt = $conn->query("SELECT COUNT(*) FROM `$table`");
                                                    $count = $stmt->fetchColumn();
                                                    echo "<tr>";
                                                    echo "<td><code>$table</code><br><small class='text-muted'>$description</small></td>";
                                                    echo "<td><span class='status-success'>✅ Exists</span></td>";
                                                    echo "<td>$count records</td>";
                                                    echo "</tr>";
                                                } catch (Exception $e) {
                                                    echo "<tr>";
                                                    echo "<td><code>$table</code><br><small class='text-muted'>$description</small></td>";
                                                    echo "<td><span class='status-error'>❌ Missing</span></td>";
                                                    echo "<td>-</td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <h6>📋 Project Features</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="mb-0">
                                        <li>✅ Lead Management</li>
                                        <li>✅ Quotation System</li>
                                        <li>✅ Purchase Orders</li>
                                        <li>✅ BOM Management</li>
                                        <li>✅ Job Card Instructions</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="mb-0">
                                        <li>✅ Purchase Management</li>
                                        <li>✅ Payment System</li>
                                        <li>✅ Image Upload Support</li>
                                        <li>✅ Approval Workflows</li>
                                        <li>✅ Multi-user Access</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6>⚠️ Important Notes</h6>
                            <ul class="mb-0">
                                <li>Backup your database before running setup</li>
                                <li>Ensure proper file permissions for uploads directory</li>
                                <li>Update config.php with correct database credentials</li>
                                <li>Change default admin password after first login</li>
                            </ul>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>