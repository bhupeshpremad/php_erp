<?php
session_start();
require_once 'config/config.php';

// Simple authentication for setup
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
        <title>ERP Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>ERP System Setup</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Setup Password</label>
                                    <input type="password" class="form-control" name="setup_password" required>
                                    <small class="text-muted">Contact admin for setup password</small>
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

// Setup interface
$action = $_GET['action'] ?? 'status';

// Include migration classes
require_once 'migrations/Migration.php';
$migrationFiles = glob('migrations/*.php');
foreach ($migrationFiles as $file) {
    if (basename($file) !== 'Migration.php') {
        require_once $file;
    }
}

// Create migrations table
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    // Table might already exist
}

function getExecutedMigrations($conn) {
    try {
        $stmt = $conn->query("SELECT TRIM(migration) FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

function runMigrations($conn) {
    $migrationFiles = glob('migrations/*.php');
    sort($migrationFiles);
    
    $executedMigrations = getExecutedMigrations($conn);
    $batch = 1;
    
    try {
        $stmt = $conn->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch();
        $batch = ($result['max_batch'] ?? 0) + 1;
    } catch (Exception $e) {
        // Use default batch
    }
    
    $results = [];
    
    foreach ($migrationFiles as $file) {
        $migrationName = basename($file, '.php');
        
        if ($migrationName === 'Migration' || in_array($migrationName, $executedMigrations)) {
            continue;
        }
        
        // Try different class name patterns
        $classNames = [
            // Pattern 1: Direct class name from file
            ucwords(str_replace(['_', '-'], ' ', $migrationName)),
            // Pattern 2: CamelCase conversion
            str_replace(' ', '', ucwords(str_replace('_', ' ', $migrationName))),
            // Pattern 3: Migration_ prefix
            'Migration_' . $migrationName
        ];
        
        $migrationExecuted = false;
        
        foreach ($classNames as $className) {
            $className = str_replace(' ', '', $className);
            
            if (class_exists($className)) {
                try {
                    $migration = new $className($conn);
                    $migration->up();
                    
                    // Record migration
                    $stmt = $conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $stmt->execute([$migrationName, $batch]);
                    
                    $results[] = "✅ {$migrationName} - Success";
                    $migrationExecuted = true;
                    break;
                } catch (Exception $e) {
                    $results[] = "❌ {$migrationName} - Error: " . $e->getMessage();
                    $migrationExecuted = true;
                    break;
                }
            }
        }
        
        if (!$migrationExecuted) {
            $results[] = "⚠️ {$migrationName} - Class not found";
        }
    }
    
    return $results;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ERP System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-success { color: #28a745; }
        .status-pending { color: #ffc107; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>🚀 ERP System Setup & Migration</h4>
                        <div>
                            <a href="?action=status" class="btn btn-info btn-sm">Status</a>
                            <a href="?action=migrate" class="btn btn-success btn-sm">Run Migrations</a>
                            <a href="?action=logout" class="btn btn-secondary btn-sm">Logout</a>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($action === 'logout'): ?>
                            <?php unset($_SESSION['setup_authenticated']); ?>
                            <script>window.location.href = 'setup.php';</script>
                        <?php endif; ?>
                        
                        <?php if ($action === 'migrate'): ?>
                            <h5>🔄 Running Migrations...</h5>
                            <div class="alert alert-info">
                                <pre><?php
                                ob_start();
                                include 'run_migrations.php';
                                $output = ob_get_clean();
                                echo htmlspecialchars($output);
                                ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <h5>📊 Migration Status</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Migration</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $migrationFiles = glob('migrations/*.php');
                                    sort($migrationFiles);
                                    $executedMigrations = getExecutedMigrations($conn);
                                    
                                    $descriptions = [
                                        '001_create_admin_users_table' => 'Dynamic admin user management',
                                        '002_create_suppliers_table' => 'Supplier registration system',
                                        '003_create_leads_table' => 'Lead management system',
                                        '004_create_quotations_table' => 'Quotation management',
                                        '005_create_quotation_products_table' => 'Quotation product details',
                                        '018_add_description_finish_to_quotation_products' => 'Add description and finish fields to quotation_products table'
                                    ];
                                    
                                    foreach ($migrationFiles as $file) {
                                        $migrationName = basename($file, '.php');
                                        $migrationName = trim($migrationName);
                                        if ($migrationName === 'Migration') continue;

                                        // Debug output
                                        // echo "<pre>Comparing '{$migrationName}' with: " . print_r($executedMigrations, true) . "</pre>";

                                        $status = in_array($migrationName, $executedMigrations) ? 'Executed' : 'Pending';
                                        $statusClass = $status === 'Executed' ? 'status-success' : 'status-pending';
                                        $description = $descriptions[$migrationName] ?? 'Database table creation';
                                        
                                        echo "<tr>";
                                        echo "<td><code>{$migrationName}</code></td>";
                                        echo "<td><span class='{$statusClass}'>● {$status}</span></td>";
                                        echo "<td>{$description}</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <h6>⚠️ Important Notes:</h6>
                            <ul class="mb-0">
                                <li>Run migrations only once in production</li>
                                <li>Backup your database before running migrations</li>
                                <li>All existing functionality will continue to work</li>
                                <li>New features require migration completion</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6>🔧 Environment Setup:</h6>
                            <p class="mb-1">Create <code>.env</code> file in project root for enhanced security:</p>
                            <pre class="mb-0">DB_HOST=localhost
DB_NAME=crm.purewood
DB_USER=root
DB_PASS=
SUPERADMIN_EMAIL=superadmin@purewood.in
SUPERADMIN_PASSWORD=Admin@123</pre>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>