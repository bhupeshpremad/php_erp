<?php
/**
 * Manual Data Sync - Paste SQL results here
 */
session_start();
require_once 'config/config.php';

$sync_password = 'purewood_manual_2025';
$authenticated = $_SESSION['manual_authenticated'] ?? false;

if ($_POST['sync_password'] ?? '' === $sync_password) {
    $_SESSION['manual_authenticated'] = true;
    $authenticated = true;
}

if (!$authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Manual Data Sync</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>📥 Manual Data Sync</h4>
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

if ($_POST['sql_data'] ?? '') {
    $sql_data = $_POST['sql_data'];
    $statements = array_filter(array_map('trim', explode(';', $sql_data)));
    
    $results = [];
    $conn->beginTransaction();
    
    try {
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|SELECT|SHOW)/i', $statement)) {
                try {
                    $conn->exec($statement);
                    $results[] = "✅ Executed: " . substr($statement, 0, 50) . "...";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $results[] = "⚠️ Skipped duplicate: " . substr($statement, 0, 50) . "...";
                    } else {
                        $results[] = "❌ Error: " . $e->getMessage();
                    }
                }
            }
        }
        $conn->commit();
        $results[] = "✅ Sync completed successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $results[] = "❌ Sync failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manual Data Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>📥 Manual Data Sync</h4>
            </div>
            <div class="card-body">
                
                <?php if (isset($results)): ?>
                    <div class="alert alert-light">
                        <h6>📊 Sync Results:</h6>
                        <?php foreach ($results as $result): ?>
                            <div><?php echo $result; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h6>📋 Instructions:</h6>
                    <ol>
                        <li>Go to your live server phpMyAdmin</li>
                        <li>Run this query to get new data:</li>
                        <code>SELECT * FROM bom_main WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY) ORDER BY id;</code>
                        <li>Copy the results and convert to INSERT statements</li>
                        <li>Paste the INSERT statements below</li>
                    </ol>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">SQL INSERT Statements</label>
                        <textarea class="form-control" name="sql_data" rows="15" placeholder="Paste your INSERT statements here...
Example:
INSERT INTO bom_main (id, bom_number, costing_sheet_number, client_name, prepared_by, order_date, delivery_date, created_at, updated_at) VALUES (22, 'BOM-2025-0022', '22', 'New Client', 'JS CHOUHAN', '2025-09-06', '2025-09-06', '2025-09-06 10:00:00', '2025-09-06 10:00:00');"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Execute SQL</button>
                </form>
                
                <div class="alert alert-warning mt-4">
                    <h6>⚠️ Quick SQL Queries for Live Server:</h6>
                    <p><strong>BOM (expecting 6 new):</strong></p>
                    <code>SELECT * FROM bom_main WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY);</code>
                    
                    <p class="mt-2"><strong>PO (expecting 2 new):</strong></p>
                    <code>SELECT * FROM po_main WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY);</code>
                    
                    <p class="mt-2"><strong>JCI (expecting 7 new):</strong></p>
                    <code>SELECT * FROM jci_main WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY);</code>
                    
                    <p class="mt-2"><strong>SO (expecting 2 new):</strong></p>
                    <code>SELECT * FROM sell_order WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY);</code>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>