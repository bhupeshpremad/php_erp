<?php
/**
 * newsetup.php (enhanced with One-Click Structure Setup)
 *
 * Purpose:
 *  - Option A: Compare two SQL schema dumps (local vs live) and generate SQL to align the CURRENT database
 *              with the desired schema (defaults to LOCAL dump as source of truth)
 *  - Option B: Run One-Click Setup to align critical table structure (no data changes) without requiring dumps
 *  - Preview the generated SQL (dry-run) and optionally apply it transactionally
 *
 * Default file names (in project root):
 *  - Local schema (target): php_erp3_db_live.sql
 *  - Live schema (reference): u404997496_erp_u404997496.sql
 *
 * Optional overrides via query:
 *  - ?local=filename.sql&live=filename.sql (relative paths resolved from project root)
 */

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = __DIR__;
require_once $root . '/config/config.php';

// Ensure DB connection
if (!isset($conn)) {
    $host = DB_HOST; $dbname = DB_NAME; $username = DB_USER; $password = DB_PASS;
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

// Helpers
function norm_path($base, $candidate) {
    if ($candidate === null || $candidate === '') return null;
    if (preg_match('~^[/\\]~', $candidate)) return $candidate; // absolute
    return rtrim($base, '/\\') . DIRECTORY_SEPARATOR . $candidate;
}
function list_sql_files($dir) {
    $files = glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.sql');
    sort($files);
    return $files;
}
function file_exists_or_err($path, &$errors) {
    if (!$path) return false;
    if (!file_exists($path)) {
        $errors[] = "File not found: $path";
        return false;
    }
    return true;
}
function read_file_contents($path) {
    $content = @file_get_contents($path);
    return $content === false ? '' : $content;
}
// Simple SQL parser for CREATE TABLE blocks
function parse_schema_from_sql($sql) {
    $schema = ['tables' => []];
    $sql = str_replace(["\r\n", "\r"], "\n", (string)$sql);
    $re = '/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*[^;]*;/is';
    if (preg_match_all($re, $sql, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $table = $match[1];
            $inner = trim($match[2]);
            $createStmt = $match[0];
            $columns = [];
            $keys = [];
            $parts = preg_split('/,\n/', $inner);
            foreach ($parts as $rawLine) {
                $line = trim($rawLine);
                if ($line === '') continue;
                if (isset($line[0]) && $line[0] === '`') {
                    if (preg_match('/^`([^`]+)`\s+(.*)$/', $line, $cm)) {
                        $colName = $cm[1];
                        $colDefRest = rtrim($cm[2], ',');
                        $columns[$colName] = $colDefRest;
                    }
                } else {
                    $keys[] = rtrim($line, ',');
                }
            }
            $schema['tables'][$table] = [
                'create' => $createStmt,
                'columns' => $columns,
                'keys' => $keys,
            ];
        }
    }
    return $schema;
}
function normalize_space($s) { return strtolower(preg_replace('/\s+/', ' ', trim((string)$s))); }
function compute_diff($target, $reference) {
    $sqlChanges = [];
    $notes = [];
    $targetTables = $target['tables'] ?? [];
    $refTables = $reference['tables'] ?? [];
    foreach ($targetTables as $table => $meta) {
        if (!isset($refTables[$table])) {
            $sqlChanges[] = $meta['create'];
            $notes[] = "CREATE TABLE needed: `$table`";
            continue;
        }
        $tCols = $meta['columns'];
        $rCols = $refTables[$table]['columns'];
        foreach ($tCols as $col => $def) {
            if (!isset($rCols[$col])) {
                $sqlChanges[] = "ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}";
                $notes[] = "ADD COLUMN `$table`.`$col`";
            } else {
                if (normalize_space($def) !== normalize_space($rCols[$col])) {
                    $sqlChanges[] = "ALTER TABLE `{$table}` MODIFY COLUMN `{$col}` {$def}";
                    $notes[] = "MODIFY COLUMN `$table`.`$col`";
                }
            }
        }
        foreach ($rCols as $col => $def) {
            if (!isset($tCols[$col])) {
                $notes[] = "(info) Column exists only in reference: `$table`.`$col` (no DROP generated)";
            }
        }
        $tKeys = $meta['keys'];
        $rKeys = $refTables[$table]['keys'];
        $tKeyNorm = array_map('normalize_space', $tKeys);
        $rKeyNorm = array_map('normalize_space', $rKeys);
        foreach ($tKeys as $idx => $keyDef) {
            if (!in_array($tKeyNorm[$idx], $rKeyNorm, true)) {
                $sqlChanges[] = "ALTER TABLE `{$table}` ADD " . $keyDef;
                $notes[] = "ADD KEY on `$table`: " . $keyDef;
            }
        }
    }
    foreach ($refTables as $table => $_) {
        if (!isset($targetTables[$table])) {
            $notes[] = "(info) Table exists only in reference: `$table` (no DROP generated)";
        }
    }
    return [$sqlChanges, $notes];
}

// ------- One-Click Setup helpers (structure only, no data changes) -------
function column_exists(PDO $conn, $table, $column) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return $stmt->fetchColumn() > 0;
}
function index_exists(PDO $conn, $table, $indexName) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $stmt->execute([$table, $indexName]);
    return $stmt->fetchColumn() > 0;
}
function add_if_missing(&$sql, PDO $conn, $table, $column, $definition) {
    if (!column_exists($conn, $table, $column)) {
        $sql[] = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
    }
}
function modify_if_diff(&$sql, PDO $conn, $table, $column, $definition) {
    if (column_exists($conn, $table, $column)) {
        // Be conservative: try to align type/default (won't drop data)
        $sql[] = "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$definition}";
    } else {
        $sql[] = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
    }
}
function add_index_if_missing(&$sql, PDO $conn, $table, $indexName, $colsDef) {
    if (!index_exists($conn, $table, $indexName)) {
        $sql[] = "ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$colsDef})";
    }
}
function build_oneclick_sql(PDO $conn) {
    $sql = [];

    // purchase_main
    add_if_missing($sql, $conn, 'purchase_main', 'approval_status', "ENUM('pending','approved','rejected') DEFAULT 'pending'");
    add_if_missing($sql, $conn, 'purchase_main', 'created_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    add_if_missing($sql, $conn, 'purchase_main', 'updated_at', "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    add_if_missing($sql, $conn, 'purchase_main', 'bom_number', "VARCHAR(50) NOT NULL");
    // created_by is optional - add if missing for future filtering
    add_if_missing($sql, $conn, 'purchase_main', 'created_by', "INT NULL");

    // purchase_items (align fields used in purchase module)
    add_if_missing($sql, $conn, 'purchase_items', 'row_id', 'INT NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'date', 'DATE NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'invoice_number', 'VARCHAR(100) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'amount', 'DECIMAL(10,2) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'invoice_image', 'VARCHAR(255) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'builty_number', 'VARCHAR(100) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'builty_image', 'VARCHAR(255) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'item_approval_status', "ENUM('pending','approved','rejected') DEFAULT 'pending'");
    add_if_missing($sql, $conn, 'purchase_items', 'length_ft', 'DECIMAL(10,2) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'width_ft', 'DECIMAL(10,2) NULL');
    add_if_missing($sql, $conn, 'purchase_items', 'thickness_inch', 'DECIMAL(10,2) NULL');
    add_index_if_missing($sql, $conn, 'purchase_items', 'idx_purchase_main_id', '`purchase_main_id`');

    // jci_main flags
    add_if_missing($sql, $conn, 'jci_main', 'purchase_created', 'TINYINT(1) DEFAULT 0');
    add_if_missing($sql, $conn, 'jci_main', 'payment_completed', 'TINYINT(1) DEFAULT 0');

    // payments (linking info used by UI)
    add_if_missing($sql, $conn, 'payments', 'jci_number', 'VARCHAR(255) NULL');
    add_if_missing($sql, $conn, 'payments', 'po_number', 'VARCHAR(255) NULL');
    add_if_missing($sql, $conn, 'payments', 'sell_order_number', 'VARCHAR(255) NULL');

    // payment_details (GST-related fields + categories)
    add_if_missing($sql, $conn, 'payment_details', 'gst_percent', 'DECIMAL(6,2) DEFAULT 0.00');
    add_if_missing($sql, $conn, 'payment_details', 'gst_amount', 'DECIMAL(15,2) DEFAULT 0.00');
    add_if_missing($sql, $conn, 'payment_details', 'total_with_gst', 'DECIMAL(15,2) DEFAULT 0.00');
    add_if_missing($sql, $conn, 'payment_details', 'payment_invoice_date', 'DATE NULL');
    add_if_missing($sql, $conn, 'payment_details', 'payment_category', 'VARCHAR(20) NULL');

    // po_main (ensure sell_order_number reference; jci_number optional)
    add_if_missing($sql, $conn, 'po_main', 'sell_order_number', 'VARCHAR(50) NULL');
    add_if_missing($sql, $conn, 'po_main', 'jci_number', 'VARCHAR(50) NULL');

    return $sql;
}

// Handle uploads (file-based diff)
$uploadMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_sql'])) {
    $allowed = ['sql'];
    foreach (['local_sql', 'live_sql'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $name = basename($_FILES[$field]['name']);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) { $uploadMsg .= "Invalid file type for $name. Only .sql allowed. "; continue; }
            $dest = $root . DIRECTORY_SEPARATOR . $name;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) { $uploadMsg .= "Uploaded: $name. "; }
            else { $uploadMsg .= "Failed to upload: $name. "; }
        }
    }
}

// Resolve file paths (defaults + GET overrides)
$defaultLocal = $root . '/php_erp3_db_live.sql';
$defaultLive  = $root . '/u404997496_erp_u404997496.sql';
$localOverride = isset($_GET['local']) ? norm_path($root, $_GET['local']) : null;
$liveOverride  = isset($_GET['live'])  ? norm_path($root, $_GET['live'])  : null;
$localSchemaPath = $localOverride ?: $defaultLocal;
$liveSchemaPath  = $liveOverride  ?: $defaultLive;

$errors = [];
$localExists = file_exists_or_err($localSchemaPath, $errors);
$liveExists  = file_exists_or_err($liveSchemaPath, $errors);

$localSql = $localExists ? read_file_contents($localSchemaPath) : '';
$liveSql  = $liveExists  ? read_file_contents($liveSchemaPath)  : '';

$localSchema = $localSql ? parse_schema_from_sql($localSql) : ['tables' => []];
$liveSchema  = $liveSql  ? parse_schema_from_sql($liveSql)  : ['tables' => []];

list($changesSql, $diffNotes) = ($localExists && $liveExists)
    ? compute_diff($localSchema, $liveSchema)
    : [[], []];

$apply = isset($_POST['apply']) && $_POST['apply'] == '1';
$runOneClick = isset($_POST['run_oneclick']) && $_POST['run_oneclick'] == '1';
$results = [];
$failed  = false;
$oneClickSql = [];

if ($runOneClick) {
    // Build structure-only SQL for critical tables
    $oneClickSql = build_oneclick_sql($conn);
    if (empty($oneClickSql)) {
        $uploadMsg .= ' Nothing to change for One-Click Setup. ';
    } else {
        try {
            $conn->beginTransaction();
            foreach ($oneClickSql as $stmtSql) {
                $stmts = array_filter(array_map('trim', explode(';', $stmtSql)));
                foreach ($stmts as $single) {
                    if ($single === '') continue;
                    $sqlToRun = $single . ';';
                    try { $conn->exec($sqlToRun); $results[] = ['sql' => $sqlToRun, 'status' => 'ok']; }
                    catch (PDOException $e) { $results[] = ['sql' => $sqlToRun, 'status' => 'error', 'error' => $e->getMessage()]; $failed = true; throw $e; }
                }
            }
            if (!$failed) { $conn->commit(); }
        } catch (Exception $e) {
            $conn->rollBack();
        }
        $logDir = $root . '/tmp/schema_logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
        $logFile = $logDir . '/oneclick_' . date('Ymd_His') . '.sql';
        @file_put_contents($logFile, implode("\n\n", $oneClickSql));
    }
}

if ($apply && !empty($changesSql)) {
    try {
        $conn->beginTransaction();
        foreach ($changesSql as $stmtSql) {
            $stmts = array_filter(array_map('trim', explode(';', $stmtSql)));
            foreach ($stmts as $single) {
                if ($single === '') continue;
                $sqlToRun = $single . ';';
                try { $conn->exec($sqlToRun); $results[] = ['sql' => $sqlToRun, 'status' => 'ok']; }
                catch (PDOException $e) { $results[] = ['sql' => $sqlToRun, 'status' => 'error', 'error' => $e->getMessage()]; $failed = true; throw $e; }
            }
        }
        if (!$failed) { $conn->commit(); }
    } catch (Exception $e) {
        $conn->rollBack();
    }
    $logDir = $root . '/tmp/schema_logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $logFile = $logDir . '/newsetup_' . date('Ymd_His') . '.sql';
    @file_put_contents($logFile, implode("\n\n", $changesSql));
}

$availableSql = list_sql_files($root);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database Schema Setup (newsetup.php)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style> body { padding: 20px; } pre { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; } code { white-space: pre-wrap; } .small{font-size:.875rem;} </style>
</head>
<body>
<div class="container-fluid">
    <h3>Database Schema Alignment</h3>
    <p class="text-muted">Target = Local dump (php_erp3_db_live.sql). Reference = Live dump (u404997496_erp_u404997496.sql).</p>

    <?php if (!empty($uploadMsg)): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($uploadMsg); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">Select or Upload Schema Dumps</div>
        <div class="card-body">
            <form method="get" class="mb-3">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Local schema file (target)</label>
                        <input type="text" class="form-control" name="local" value="<?php echo htmlspecialchars(isset($_GET['local']) ? $_GET['local'] : basename($localSchemaPath)); ?>" placeholder="php_erp3_db_live.sql">
                        <small class="form-text text-muted">Path relative to project root. Current: <?php echo htmlspecialchars($localSchemaPath); ?></small>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Live schema file (reference)</label>
                        <input type="text" class="form-control" name="live" value="<?php echo htmlspecialchars(isset($_GET['live']) ? $_GET['live'] : basename($liveSchemaPath)); ?>" placeholder="u404997496_erp_u404997496.sql">
                        <small class="form-text text-muted">Path relative to project root. Current: <?php echo htmlspecialchars($liveSchemaPath); ?></small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Use Selected Files</button>
            </form>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="upload_sql" value="1">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Upload local schema (.sql)</label>
                        <input type="file" class="form-control-file" name="local_sql" accept=".sql">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Upload live schema (.sql)</label>
                        <input type="file" class="form-control-file" name="live_sql" accept=".sql">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary">Upload</button>
            </form>
            <?php if (!empty($availableSql)): ?>
                <hr>
                <div class="small">
                    <strong>Available .sql files in project root:</strong>
                    <ul class="mb-0">
                        <?php foreach ($availableSql as $f): $bn = basename($f); ?>
                            <li>
                                <?php echo htmlspecialchars($bn); ?>
                                <a class="ml-2" href="?local=<?php echo urlencode($bn); ?>&live=<?php echo urlencode(basename($liveSchemaPath)); ?>">Use as Local</a>
                                <a class="ml-2" href="?local=<?php echo urlencode(basename($localSchemaPath)); ?>&live=<?php echo urlencode($bn); ?>">Use as Live</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Summary</div>
        <div class="card-body">
            <ul>
                <li>Local tables parsed: <?php echo count($localSchema['tables']); ?></li>
                <li>Live tables parsed: <?php echo count($liveSchema['tables']); ?></li>
                <li>Planned SQL statements (file diff): <?php echo count($changesSql); ?></li>
            </ul>
            <?php if (!empty($diffNotes)): ?>
                <details>
                    <summary>Diff notes</summary>
                    <ul>
                        <?php foreach ($diffNotes as $n): ?><li><?php echo htmlspecialchars($n); ?></li><?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Generated SQL (Dry Run)</div>
        <div class="card-body">
            <?php if (!$localExists || !$liveExists): ?>
                <div class="alert alert-warning mb-0">Please select or upload both schema files to generate SQL.</div>
            <?php elseif (empty($changesSql)): ?>
                <div class="alert alert-success mb-0">No changes detected between target (local) and reference (live) dumps.</div>
            <?php else: ?>
                <pre><code><?php echo htmlspecialchars(implode("\n\n", $changesSql)); ?></code></pre>
            <?php endif; ?>
        </div>
    </div>

    <form method="post" class="mb-4">
        <input type="hidden" name="apply" value="1">
        <button type="submit" class="btn btn-primary" <?php echo (!$localExists || !$liveExists || empty($changesSql)) ? 'disabled' : ''; ?>>Apply Changes (from files)</button>
        <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="btn btn-secondary">Refresh</a>
    </form>

    <div class="card mb-3">
        <div class="card-header">One-Click Structure Setup (No Data Changes)</div>
        <div class="card-body">
            <p class="mb-2">Runs safe ALTER/CREATE statements to ensure required columns and indexes exist for Purchase, JCI, and Payments modules. Data remains intact.</p>
            <form method="post">
                <input type="hidden" name="run_oneclick" value="1">
                <button type="submit" class="btn btn-success">Run One-Click Setup</button>
            </form>
            <?php if ($runOneClick): ?>
                <hr>
                <h6>Executed SQL</h6>
                <?php if (!empty($oneClickSql)): ?>
                    <pre><code><?php echo htmlspecialchars(implode("\n\n", $oneClickSql)); ?></code></pre>
                <?php else: ?>
                    <div class="alert alert-info mb-0">No structure changes were required.</div>
                <?php endif; ?>
                <?php if (!empty($results)): ?>
                    <h6 class="mt-3">Results</h6>
                    <ul>
                        <?php foreach ($results as $r): ?>
                            <li>
                                <strong><?php echo $r['status'] === 'ok' ? 'OK' : 'ERROR'; ?></strong> -
                                <code><?php echo htmlspecialchars($r['sql']); ?></code>
                                <?php if (!empty($r['error'])): ?>
                                    <div class="text-danger">Error: <?php echo htmlspecialchars($r['error']); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
