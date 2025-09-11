<?php
/**
 * newsetup.php
 *
 * Purpose:
 *  - Compare two SQL schema dumps (local vs live) and generate SQL to align the CURRENT database
 *    with the desired schema (defaults to LOCAL dump as source of truth)
 *  - Preview the generated SQL (dry-run) and optionally apply it transactionally
 *
 * Inputs (files placed in project root):
 *  - Local schema: php_erp3_db_live.sql
 *  - Live schema:  u404997496_erp_u404997496.sql
 *
 * Usage:
 *  - Open in browser: /newsetup.php (shows summary and dry-run)
 *  - Click "Apply" to execute the generated SQL against current DB
 */

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Bootstrap DB config
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

// Input SQL files
$localSchemaPath = $root . '/php_erp3_db_live.sql';        // Local dump (desired target)
$liveSchemaPath  = $root . '/u404997496_erp_u404997496.sql'; // Live dump (reference)

$errors = [];

function file_exists_or_err($path, &$errors) {
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
    $schema = [
        'tables' => [], // tableName => [ 'create' => originalCreate, 'columns' => [name=>def], 'keys' => [keyDef...]]
    ];

    // Normalize line endings
    $sql = str_replace(["\r\n", "\r"], "\n", $sql);

    // Extract CREATE TABLE blocks
    $re = '/CREATE\s+TABLE\s+`([^`]+)`\s*\((.*?)\)\s*[^;]*;/is';
    if (preg_match_all($re, $sql, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $table = $match[1];
            $inner = trim($match[2]);
            $createStmt = $match[0];

            $columns = [];
            $keys = [];

            // Split by lines respecting simple SQL dump (comma+newline)
            $parts = preg_split('/,\n/', $inner);
            foreach ($parts as $rawLine) {
                $line = trim($rawLine);
                if ($line === '') continue;

                if ($line[0] === '`') {
                    // Column definition line: `col` TYPE ...
                    if (preg_match('/^`([^`]+)`\s+(.*)$/', $line, $cm)) {
                        $colName = $cm[1];
                        $colDefRest = rtrim($cm[2], ',');
                        $columns[$colName] = $colDefRest; // store rest (type + attributes)
                    }
                } else {
                    // Key/constraint line
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

function normalize_space($s) {
    // collapse whitespace for comparison
    $s = preg_replace('/\s+/', ' ', trim($s));
    // normalize case for certain attributes
    return strtolower($s);
}

// Compute diff: target = local schema, reference = live schema
function compute_diff($target, $reference) {
    $sqlChanges = [];
    $notes = [];

    $targetTables = $target['tables'];
    $refTables = $reference['tables'];

    foreach ($targetTables as $table => $meta) {
        if (!isset($refTables[$table])) {
            // Table missing in reference: create it
            $sqlChanges[] = $meta['create'];
            $notes[] = "CREATE TABLE needed: `$table`";
            continue;
        }

        // Compare columns
        $tCols = $meta['columns'];
        $rCols = $refTables[$table]['columns'];
        foreach ($tCols as $col => $def) {
            if (!isset($rCols[$col])) {
                // Add column (append at end)
                $sqlChanges[] = "ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}";
                $notes[] = "ADD COLUMN `$table`.`$col`";
            } else {
                $tNorm = normalize_space($def);
                $rNorm = normalize_space($rCols[$col]);
                if ($tNorm !== $rNorm) {
                    // Modify column to match target
                    $sqlChanges[] = "ALTER TABLE `{$table}` MODIFY COLUMN `{$col}` {$def}";
                    $notes[] = "MODIFY COLUMN `$table`.`$col`";
                }
            }
        }

        // Warn about columns that exist in reference but not in target (no drop by default)
        foreach ($rCols as $col => $def) {
            if (!isset($tCols[$col])) {
                $notes[] = "(info) Column exists only in reference (live): `$table`.`$col` (no DROP generated)";
            }
        }

        // Compare keys: add keys present in target but missing in reference
        $tKeys = $meta['keys'];
        $rKeys = $refTables[$table]['keys'];

        // Normalize keys for comparison
        $tKeyNorm = array_map('normalize_space', $tKeys);
        $rKeyNorm = array_map('normalize_space', $rKeys);

        foreach ($tKeys as $idx => $keyDef) {
            $norm = $tKeyNorm[$idx];
            if (!in_array($norm, $rKeyNorm, true)) {
                // Build an ALTER for this key
                // If it's a PRIMARY KEY or UNIQUE/KEY line, we can prefix with ADD
                $sqlChanges[] = "ALTER TABLE `{$table}` ADD " . $keyDef;
                $notes[] = "ADD KEY on `$table`: " . $keyDef;
            }
        }
    }

    // Tables only in reference - no DROP, only note
    foreach ($refTables as $table => $_) {
        if (!isset($targetTables[$table])) {
            $notes[] = "(info) Table exists only in reference (live): `$table` (no DROP generated)";
        }
    }

    return [$sqlChanges, $notes];
}

// Read schemas
if (!file_exists_or_err($localSchemaPath, $errors) || !file_exists_or_err($liveSchemaPath, $errors)) {
    // continue to UI with errors printed
}

$localSql = file_exists($localSchemaPath) ? read_file_contents($localSchemaPath) : '';
$liveSql  = file_exists($liveSchemaPath)  ? read_file_contents($liveSchemaPath)  : '';

$localSchema = $localSql ? parse_schema_from_sql($localSql) : ['tables' => []];
$liveSchema  = $liveSql  ? parse_schema_from_sql($liveSql)  : ['tables' => []];

list($changesSql, $diffNotes) = compute_diff($localSchema, $liveSchema);

$apply = isset($_POST['apply']) && $_POST['apply'] == '1';
$results = [];
$failed  = false;

if ($apply && !empty($changesSql)) {
    try {
        $conn->beginTransaction();
        foreach ($changesSql as $stmtSql) {
            // Some CREATE TABLE includes ending semicolon; ensure single exec per statement
            // Split on semicolon only if multiple statements present in a single string
            $stmts = array_filter(array_map('trim', explode(';', $stmtSql)));
            foreach ($stmts as $single) {
                if ($single === '') continue;
                $sqlToRun = $single . ';';
                try {
                    $conn->exec($sqlToRun);
                    $results[] = ['sql' => $sqlToRun, 'status' => 'ok'];
                } catch (PDOException $e) {
                    $results[] = ['sql' => $sqlToRun, 'status' => 'error', 'error' => $e->getMessage()];
                    $failed = true;
                    throw $e; // break out to rollback
                }
            }
        }
        if (!$failed) {
            $conn->commit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
    }

    // Write log file
    $logDir = $root . '/tmp/schema_logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
    $logFile = $logDir . '/newsetup_' . date('Ymd_His') . '.sql';
    @file_put_contents($logFile, implode("\n\n", $changesSql));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database Schema Setup (newsetup.php)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        pre { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; }
        code { white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container-fluid">
    <h3>Database Schema Alignment</h3>
    <p class="text-muted">Target = Local dump (php_erp3_db_live.sql). Reference = Live dump (u404997496_erp_u404997496.sql).</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $err): ?><li><?php echo htmlspecialchars($err); ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">Summary</div>
        <div class="card-body">
            <ul>
                <li>Local tables parsed: <?php echo count($localSchema['tables']); ?></li>
                <li>Live tables parsed: <?php echo count($liveSchema['tables']); ?></li>
                <li>Planned SQL statements: <?php echo count($changesSql); ?></li>
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
            <?php if (empty($changesSql)): ?>
                <div class="alert alert-success">No changes detected between target (local) and reference (live) dumps.</div>
            <?php else: ?>
                <pre><code><?php echo htmlspecialchars(implode("\n\n", $changesSql)); ?></code></pre>
            <?php endif; ?>
        </div>
    </div>

    <form method="post">
        <input type="hidden" name="apply" value="1">
        <button type="submit" class="btn btn-primary" <?php echo empty($changesSql) ? 'disabled' : ''; ?>>Apply Changes</button>
        <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="btn btn-secondary">Refresh</a>
    </form>

    <?php if ($apply): ?>
        <div class="card mt-3">
            <div class="card-header">Execution Results</div>
            <div class="card-body">
                <?php if ($failed): ?>
                    <div class="alert alert-danger">One or more statements failed. Transaction rolled back.</div>
                <?php else: ?>
                    <div class="alert alert-success">All statements executed successfully.</div>
                <?php endif; ?>
                <?php if (!empty($results)): ?>
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
                    <p class="text-muted">A copy of the generated SQL was saved in tmp/schema_logs/.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
