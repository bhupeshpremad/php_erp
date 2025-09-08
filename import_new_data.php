<?php
/**
 * Import New Data to PHP ERP3
 * This script imports the exported SQL file from live server
 */

session_start();
require_once 'config/config.php';

$import_password = 'purewood_import_2025';
$authenticated = $_SESSION['import_authenticated'] ?? false;

if ($_POST['import_password'] ?? '' === $import_password) {
    $_SESSION['import_authenticated'] = true;
    $authenticated = true;
}

if (!$authenticated) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Import New Data</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>📥 Import New Data</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Import Password</label>
                                    <input type="password" class="form-control" name="import_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Access Import</button>
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

function importSqlFile($conn, $filename) {
    $results = [];
    
    if (!file_exists($filename)) {
        return ["❌ File not found: $filename"];
    }
    
    $sql = file_get_contents($filename);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $conn->beginTransaction();
    $imported = 0;
    $errors = 0;
    
    try {
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*)/', $statement)) {
                try {
                    $conn->exec($statement);
                    $imported++;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        // Skip duplicates
                        continue;
                    }
                    $errors++;
                    $results[] = "⚠️ Error: " . $e->getMessage();
                }
            }
        }
        
        $conn->commit();
        $results[] = "✅ Import completed: $imported statements executed";
        if ($errors > 0) {
            $results[] = "⚠️ Errors encountered: $errors";
        }
        
    } catch (Exception $e) {
        $conn->rollBack();
        $results[] = "❌ Import failed: " . $e->getMessage();
    }
    
    return $results;
}

function listExportFiles() {
    $files = glob('new_data_export_*.sql');
    return $files;
}

$action = $_GET['action'] ?? 'list';
$file = $_GET['file'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Import New Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>📥 Import New Data from Live Server</h4>
            </div>
            <div class="card-body">
                
                <?php if ($action === 'import' && !empty($file)): ?>
                    <div class="alert alert-info">
                        <h5>📥 Importing: <?php echo htmlspecialchars($file); ?></h5>
                    </div>
                    
                    <?php
                    $import_results = importSqlFile($conn, $file);
                    echo "<div class='alert alert-light'>";
                    foreach ($import_results as $result) {
                        echo "<div>$result</div>";
                    }
                    echo "</div>";
                    
                    echo "<div class='alert alert-success'>";
                    echo "<h6>✅ Import Process Complete!</h6>";
                    echo "<p>New data has been imported into php_erp3.</p>";
                    echo "<a href='?action=list' class='btn btn-primary'>Back to File List</a>";
                    echo "</div>";
                    ?>
                    
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5>📋 Instructions</h5>
                        <ol>
                            <li>Run <code>export_new_data.php</code> on your live server</li>
                            <li>Download the generated SQL file</li>
                            <li>Upload it to this directory</li>
                            <li>Select and import the file below</li>
                        </ol>
                    </div>
                    
                    <h5>📁 Available Export Files</h5>
                    <?php
                    $export_files = listExportFiles();
                    if (empty($export_files)):
                    ?>
                        <div class="alert alert-warning">
                            <p>No export files found. Please:</p>
                            <ol>
                                <li>Run <code>export_new_data.php</code> on your live server</li>
                                <li>Upload the generated <code>new_data_export_*.sql</code> file here</li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>File</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($export_files as $export_file): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($export_file); ?></code></td>
                                            <td><?php echo number_format(filesize($export_file) / 1024, 2); ?> KB</td>
                                            <td><?php echo date('Y-m-d H:i:s', filemtime($export_file)); ?></td>
                                            <td>
                                                <a href="?action=import&file=<?php echo urlencode($export_file); ?>" 
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Import this file? This will add new records to the database.')">
                                                    Import
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-warning mt-4">
                        <h6>⚠️ Important Notes</h6>
                        <ul class="mb-0">
                            <li>Backup your database before importing</li>
                            <li>Import will skip duplicate records automatically</li>
                            <li>Only import files from your live server</li>
                            <li>Check the results carefully after import</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>