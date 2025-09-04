<?php

class NotificationSystem {
    private static $conn;
    
    public static function init($connection) {
        self::$conn = $connection;
    }
    
    // Create notification - supports both array and individual parameters
    public static function create($module, $title = null, $message = null, $userType = null, $userId = null, $type = 'info', $referenceId = null) {
        try {
            // If first parameter is array, use array format
            if (is_array($module)) {
                $data = $module;
                $stmt = self::$conn->prepare("INSERT INTO notifications (user_type, user_id, title, message, type, module, reference_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $data['user_type'],
                    $data['user_id'] ?? null,
                    $data['title'],
                    $data['message'],
                    $data['type'] ?? 'info',
                    $data['module'] ?? null,
                    $data['reference_id'] ?? null
                ]);
            } else {
                // Use individual parameters
                $stmt = self::$conn->prepare("INSERT INTO notifications (user_type, user_id, title, message, type, module, reference_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                return $stmt->execute([
                    $userType,
                    $userId,
                    $title,
                    $message,
                    $type,
                    $module,
                    $referenceId
                ]);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Get notifications for user
    public static function getForUser($userType, $userId = null, $limit = 10) {
        try {
            $sql = "SELECT * FROM notifications WHERE user_type = ?";
            $params = [$userType];
            
            if ($userId) {
                $sql .= " AND (user_id IS NULL OR user_id = ?)";
                $params[] = $userId;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = self::$conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Get unread count
    public static function getUnreadCount($userType, $userId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM notifications WHERE user_type = ? AND is_read = 0";
            $params = [$userType];
            
            if ($userId) {
                $sql .= " AND (user_id IS NULL OR user_id = ?)";
                $params[] = $userId;
            }
            
            $stmt = self::$conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
    
    // Mark as read
    public static function markAsRead($notificationId) {
        try {
            $stmt = self::$conn->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?");
            return $stmt->execute([$notificationId]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Mark all as read for user
    public static function markAllAsRead($userType, $userId = null) {
        try {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_type = ? AND is_read = 0";
            $params = [$userType];
            
            if ($userId) {
                $sql .= " AND (user_id IS NULL OR user_id = ?)";
                $params[] = $userId;
            }
            
            $stmt = self::$conn->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Auto-create notifications for all modules
    public static function autoNotify($module, $action, $data = []) {
        // Defensive defaults to avoid undefined index notices
        $safe = function($key, $default = 'N/A') use ($data) {
            return isset($data[$key]) && $data[$key] !== '' ? $data[$key] : $default;
        };
        $notifications = [
            'supplier' => [
                'registration' => ['title' => 'New Supplier Registration', 'message' => "Supplier '" . $safe('company_name') . "' registered", 'icon' => 'fas fa-truck']
            ],
            'buyer' => [
                'registration' => ['title' => 'New Buyer Registration', 'message' => "Buyer '" . $safe('company_name') . "' registered", 'icon' => 'fas fa-shopping-bag']
            ],
            'admin' => [
                'registration' => ['title' => 'New Admin Registration', 'message' => "Admin '" . $safe('name') . "' registered for " . $safe('department') , 'icon' => 'fas fa-user-cog']
            ],
            'lead' => [
                'created' => ['title' => 'New Lead', 'message' => "Lead '" . $safe('lead_number') . "' created", 'icon' => 'fas fa-bullhorn']
            ],
            'quotation' => [
                'created' => ['title' => 'New Quotation', 'message' => "Quotation '" . $safe('quotation_number') . "' created", 'icon' => 'fas fa-file-invoice'],
                'approved' => ['title' => 'Quotation Approved', 'message' => "Quotation '" . $safe('quotation_number') . "' approved", 'icon' => 'fas fa-check-circle'],
                'locked' => ['title' => 'Quotation Locked', 'message' => "Quotation '" . $safe('quotation_number') . "' locked", 'icon' => 'fas fa-lock'],
                'unlocked' => ['title' => 'Quotation Unlocked', 'message' => "Quotation '" . $safe('quotation_number') . "' unlocked", 'icon' => 'fas fa-unlock']
            ],
            'pi' => [
                'created' => ['title' => 'New PI', 'message' => "PI '" . $safe('pi_number') . "' created", 'icon' => 'fas fa-file-invoice-dollar']
            ],
            'po' => [
                'created' => ['title' => 'New PO', 'message' => "PO '" . $safe('po_number') . "' created", 'icon' => 'fas fa-file-alt'],
                'locked' => ['title' => 'PO Locked', 'message' => "PO '" . $safe('po_number') . "' locked", 'icon' => 'fas fa-lock'],
                'unlocked' => ['title' => 'PO Unlocked', 'message' => "PO '" . $safe('po_number') . "' unlocked", 'icon' => 'fas fa-unlock']
            ],
            'so' => [
                'created' => ['title' => 'New Sale Order', 'message' => "SO '" . $safe('so_number') . "' created", 'icon' => 'fas fa-shopping-cart']
            ],
            'bom' => [
                'created' => ['title' => 'New BOM', 'message' => "BOM '" . $safe('bom_number') . "' created", 'icon' => 'fas fa-cubes']
            ],
            'purchase' => [
                'created' => ['title' => 'New Purchase', 'message' => "Purchase entry created", 'icon' => 'fas fa-shopping-basket']
            ],
            'payment' => [
                'created' => ['title' => 'New Payment', 'message' => "Payment entry created", 'icon' => 'fas fa-credit-card']
            ]
        ];
        
        if (isset($notifications[$module][$action])) {
            $notif = $notifications[$module][$action];
            self::create([
                'user_type' => 'superadmin',
                'title' => $notif['title'],
                'message' => $notif['message'],
                'type' => 'info',
                'module' => $module,
                'reference_id' => $data['id'] ?? null
            ]);
        }
    }
    
    public static function getModuleIcon($module) {
        $icons = [
            'supplier' => 'fas fa-truck',
            'buyer' => 'fas fa-shopping-bag',
            'admin' => 'fas fa-user-cog',
            'lead' => 'fas fa-bullhorn',
            'quotation' => 'fas fa-file-invoice',
            'pi' => 'fas fa-file-invoice-dollar',
            'po' => 'fas fa-file-alt',
            'so' => 'fas fa-shopping-cart',
            'bom' => 'fas fa-cubes',
            'purchase' => 'fas fa-shopping-basket',
            'payment' => 'fas fa-credit-card'
        ];
        return $icons[$module] ?? 'fas fa-bell';
    }
}