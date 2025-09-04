<?php
class CRUDAdd {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function addRecord($tableName, $columns, $values) {
        if (count($columns) !== count($values)) {
            return ['success' => false, 'message' => 'Columns count does not match values count.'];
        }

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
}

class CRUDUpdate {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function updateRecord($tableName, $columns, $values, $whereCondition, $whereParams) {
        if (count($columns) !== count($values)) {
            return ['success' => false, 'message' => 'Columns count does not match values count.'];
        }

        $setClause = implode(", ", array_map(function($col) { return "$col = ?"; }, $columns));
        $sql = "UPDATE $tableName SET $setClause WHERE $whereCondition";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute(array_merge($values, $whereParams));
            return ['success' => true, 'message' => 'Record updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating record: ' . $e->getMessage()];
        }
    }
}

class CRUDDelete {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function deleteRecord($tableName, $whereCondition, $whereParams) {
        $sql = "DELETE FROM $tableName WHERE $whereCondition";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($whereParams);
            return ['success' => true, 'message' => 'Record deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting record: ' . $e->getMessage()];
        }
    }
}

class CRUDView {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function viewRecords($tableName, $columns = null, $whereCondition = null, $whereParams = null, $searchKeyword = null, $searchColumns = [], $limit = 10, $offset = 0) {
        $columnsList = $columns ? implode(", ", $columns) : "*";
        $sql = "SELECT $columnsList FROM $tableName";

        $params = [];

        $whereClauses = [];
        if ($whereCondition) {
            $whereClauses[] = "($whereCondition)";
            if ($whereParams) {
                $params = array_merge($params, $whereParams);
            }
        }

        if ($searchKeyword && !empty($searchColumns)) {
            $searchConditions = [];
            foreach ($searchColumns as $col) {
                $searchConditions[] = "$col LIKE ?";
                $params[] = "%$searchKeyword%";
            }
            $whereClauses[] = "(" . implode(" OR ", $searchConditions) . ")";
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Adjust limit and offset for pagination if total count > 20
        $effectiveLimit = $limit;
        if ($limit > 20) {
            $effectiveLimit = 20;
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$effectiveLimit;
        $params[] = (int)$offset;

        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) FROM $tableName";
            if (!empty($whereClauses)) {
                $countSql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            $countStmt = $this->conn->prepare($countSql);
            $countStmt->execute(array_slice($params, 0, count($params) - 2));
            $totalCount = $countStmt->fetchColumn();

            return [
                'success' => true,
                'message' => 'Records fetched successfully.',
                'data' => $data,
                'totalCount' => $totalCount,
                'limit' => $effectiveLimit,
                'offset' => $offset,
                'showPagination' => $totalCount > 20
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error fetching records: ' . $e->getMessage(), 'data' => []];
        }
    }
}
?>
