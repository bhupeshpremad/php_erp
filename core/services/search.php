<?php
class SearchService {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function advancedSearch($tableName, $searchColumns, $searchKeyword, $limit = 10, $offset = 0) {
        $columnsList = implode(", ", $searchColumns);
        $sql = "SELECT * FROM $tableName WHERE ";

        $searchConditions = [];
        $params = [];
        foreach ($searchColumns as $col) {
            $searchConditions[] = "$col LIKE ?";
            $params[] = "%$searchKeyword%";
        }
        $sql .= implode(" OR ", $searchConditions);
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $data];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error during search: ' . $e->getMessage()];
        }
    }
}
?>
