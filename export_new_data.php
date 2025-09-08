<?php
/**
 * Export New Data from Live Server
 * Run this on LIVE server to export new data added in last 3 days
 */

// Live server database connection
$host = 'localhost';
$db = 'u404997496_crm_purewood';
$user = 'u404997496_crn_purewood';
$pass = 'Purewood@2025#';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-3 days'));
    $export_sql = "-- New data export from live server\n";
    $export_sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $export_sql .= "-- Cutoff date: $cutoff_date\n\n";
    
    // Tables to export with their relationships
    $tables = [
        'bom_main' => ['bom_wood', 'bom_glow', 'bom_hardware', 'bom_labour', 'bom_plynydf', 'bom_factory', 'bom_margin'],
        'po_main' => ['po_items'],
        'sell_order' => [],
        'jci_main' => ['jci_items'],
        'purchase_main' => ['purchase_items'],
        'payments' => ['payment_details']
    ];
    
    foreach ($tables as $main_table => $related_tables) {
        // Get new main records
        $stmt = $conn->prepare("SELECT * FROM $main_table WHERE created_at >= ? ORDER BY id");
        $stmt->execute([$cutoff_date]);
        $records = $stmt->fetchAll();
        
        if (empty($records)) {
            $export_sql .= "-- No new records in $main_table\n\n";
            continue;
        }
        
        $export_sql .= "-- New records in $main_table (" . count($records) . " records)\n";
        
        foreach ($records as $record) {
            $columns = array_keys($record);
            $values = array_map(function($val) use ($conn) {
                return $val === null ? 'NULL' : $conn->quote($val);
            }, array_values($record));
            
            $export_sql .= "INSERT INTO $main_table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            
            // Export related table records
            foreach ($related_tables as $related_table) {
                $foreign_key = getForeignKey($main_table);
                $rel_stmt = $conn->prepare("SELECT * FROM $related_table WHERE $foreign_key = ?");
                $rel_stmt->execute([$record['id']]);
                $rel_records = $rel_stmt->fetchAll();
                
                foreach ($rel_records as $rel_record) {
                    $rel_columns = array_keys($rel_record);
                    $rel_values = array_map(function($val) use ($conn) {
                        return $val === null ? 'NULL' : $conn->quote($val);
                    }, array_values($rel_record));
                    
                    $export_sql .= "INSERT INTO $related_table (" . implode(', ', $rel_columns) . ") VALUES (" . implode(', ', $rel_values) . ");\n";
                }
            }
        }
        
        $export_sql .= "\n";
    }
    
    // Save to file
    $filename = 'new_data_export_' . date('Y-m-d_H-i-s') . '.sql';
    file_put_contents($filename, $export_sql);
    
    echo "✅ Export completed: $filename\n";
    echo "📊 Records exported from tables:\n";
    
    foreach ($tables as $main_table => $related_tables) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM $main_table WHERE created_at >= ?");
        $stmt->execute([$cutoff_date]);
        $count = $stmt->fetchColumn();
        echo "- $main_table: $count records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function getForeignKey($main_table) {
    $foreign_keys = [
        'bom_main' => 'bom_main_id',
        'po_main' => 'po_id',
        'jci_main' => 'jci_id',
        'purchase_main' => 'purchase_main_id',
        'payments' => 'payment_id'
    ];
    
    return $foreign_keys[$main_table] ?? 'id';
}
?>