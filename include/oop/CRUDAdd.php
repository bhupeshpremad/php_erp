<?php
class CRUDAdd {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addRecord($tableName, $columns, $values) {
        if (count($columns) !== count($values)) {
            return ['success' => false, 'message' => 'Columns and values count mismatch.'];
        }

        $columnsList = implode(", ", $columns);
        $placeholders = implode(", ", array_fill(0, count($values), "?"));

        $sql = "INSERT INTO $tableName ($columnsList) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($values);
            return ['success' => true, 'message' => 'Record added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
