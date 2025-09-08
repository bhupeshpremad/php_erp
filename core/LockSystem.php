<?php

class LockSystem {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Legacy-friendly wrappers
    public function getLockInfo($table, $recordId) {
        return $this->isLocked($table, $recordId);
    }

    public function lock($table, $recordId, $userId) {
        return $this->lockRecord($table, $recordId, $userId);
    }

    public function unlock($table, $recordId, $userId) {
        return $this->unlockRecord($table, $recordId, $userId);
    }

    // Lock a record
    public function lockRecord($table, $recordId, $userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE {$table} SET is_locked = 1, locked_by = ?, locked_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$userId, $recordId]);
            
            if ($result) {
                // Create notification
                $this->createLockNotification($table, $recordId, $userId, 'locked');
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Unlock a record
    public function unlockRecord($table, $recordId, $userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE {$table} SET is_locked = 0, locked_by = NULL, locked_at = NULL WHERE id = ?");
            $result = $stmt->execute([$recordId]);
            
            if ($result) {
                // Create notification
                $this->createLockNotification($table, $recordId, $userId, 'unlocked');
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Check if record is locked
    public function isLocked($table, $recordId) {
        try {
            $stmt = $this->conn->prepare("SELECT is_locked, locked_by, locked_at FROM {$table} WHERE id = ?");
            $stmt->execute([$recordId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Check if user can edit (superadmin can always edit)
    public function canEdit($table, $recordId, $userType, $userId = null) {
        if ($userType === 'superadmin') {
            return true; // Superadmin can always edit
        }
        
        $lockInfo = $this->isLocked($table, $recordId);
        if (!$lockInfo || !$lockInfo['is_locked']) {
            return true; // Not locked
        }
        
        // Check if locked by same user
        return $lockInfo['locked_by'] == $userId;
    }
    
    private function createLockNotification($table, $recordId, $userId, $action) {
        $tableName = ucfirst($table);
        $title = "{$tableName} {$action}";
        $message = "{$tableName} ID #{$recordId} has been {$action}";
        
        NotificationSystem::create([
            'user_type' => 'superadmin',
            'title' => $title,
            'message' => $message,
            'type' => $action === 'locked' ? 'warning' : 'info',
            'module' => $table,
            'reference_id' => $recordId
        ]);
    }
}