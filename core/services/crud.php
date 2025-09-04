<?php
class CrudService {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function create($tableName, $data) {
        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(", ", $columns);
        $placeholders = implode(", ", array_fill(0, count($values), "?"));

        $sql = "INSERT INTO $tableName ($columnsList) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($values);
            return ['success' => true, 'message' => 'Record added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding record: ' . $e->getMessage()];
        }
    }

    public function read($tableName, $columns = '*', $where = '', $params = []) {
        $columnsList = is_array($columns) ? implode(", ", $columns) : $columns;
        $sql = "SELECT $columnsList FROM $tableName";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error fetching records: ' . $e->getMessage()];
        }
    }

    public function update($tableName, $data, $where, $params) {
        $columns = array_keys($data);
        $values = array_values($data);

        $setClause = implode(", ", array_map(function($col) { return "$col = ?"; }, $columns));
        $sql = "UPDATE $tableName SET $setClause WHERE $where";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute(array_merge($values, $params));
            return ['success' => true, 'message' => 'Record updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating record: ' . $e->getMessage()];
        }
    }

    public function delete($tableName, $where, $params) {
        $sql = "DELETE FROM $tableName WHERE $where";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($params);
            return ['success' => true, 'message' => 'Record deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting record: ' . $e->getMessage()];
        }
    }
}
?>
