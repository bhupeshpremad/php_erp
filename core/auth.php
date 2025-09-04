<?php
// Helper functions
function isLoggedIn() {
    return (isset($_SESSION['role']) && !empty($_SESSION['role'])) || 
           (isset($_SESSION['user_type']) && !empty($_SESSION['user_type']));
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'role' => $_SESSION['role'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'id' => $_SESSION['user_id'] ?? null
    ];
}

function requireAuth($allowedRoles = []) {
    if (!isLoggedIn()) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'index.php');
        exit;
    }
    
    if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/') . 'index.php');
        exit;
    }
}

class Auth {
    private $conn;
    
    // Get superadmin credentials from environment with fallbacks
    private function getSuperadminCredentials() {
        return [
            'email' => $_ENV['SUPERADMIN_EMAIL'] ?? 'superadmin@purewood.in',
            'password' => $_ENV['SUPERADMIN_PASSWORD'] ?? 'Admin@123'
        ];
    }
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function login($role, $inputEmail, $inputPassword) {
        // Handle superadmin login (from environment)
        if ($role === 'superadmin') {
            $superadmin = $this->getSuperadminCredentials();
            $stored = $superadmin['password'];
            $passwordMatches = false;
            // Support either a bcrypt hash in env or a plain text password
            if (preg_match('/^\$2y\$\d{2}\$.{53}$/', $stored)) { // looks like bcrypt hash
                $passwordMatches = password_verify($inputPassword, $stored);
            } else {
                $passwordMatches = hash_equals($stored, $inputPassword);
            }

            if ($inputEmail === $superadmin['email'] && $passwordMatches) {
                $_SESSION['role'] = 'superadmin';
                $_SESSION['username'] = 'Super Admin';
                $_SESSION['email'] = $inputEmail;
                $_SESSION['user_id'] = 1;
                $_SESSION['user_type'] = 'superadmin';
                return true;
            }
            return false;
        }
        
        // Handle buyer login
        if ($role === 'buyer') {
            return $this->loginBuyer($inputEmail, $inputPassword);
        }

        // Handle dynamic admin login from database
        try {
            error_log('Auth Debug: Attempting dynamic admin login for email: ' . $inputEmail . ' with role: ' . $role);
            $stmt = $this->conn->prepare("SELECT * FROM admin_users WHERE email = ? AND department = ? AND status = 'approved'");
            $stmt->execute([$inputEmail, $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($inputPassword, $user['password'])) {
                error_log('Auth Debug: User found. Hashed password: ' . $user['password']);
                error_log('Auth Debug: Password verified successfully for user: ' . $inputEmail);

                // Clear any existing session data
                session_regenerate_id(true);
                
                // Store user info in session
                $_SESSION['role'] = $role;
                $_SESSION['username'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_department'] = $user['department'];
                $_SESSION['user_type'] = $role . 'admin';
                $_SESSION['department'] = $role;
                return true;
            }
            error_log('Auth Debug: User not found or password mismatch for email: ' . $inputEmail);
            return false;
        } catch (Exception $e) {
            error_log('Auth Error: ' . $e->getMessage());
            return false;
        }
    }

    public function loginBuyer($inputEmail, $inputPassword) {
        try {
            $stmt = $this->conn->prepare("SELECT id, contact_person_name, company_name, password, status FROM buyers WHERE contact_person_email = ? AND status = 'approved'");
            $stmt->execute([$inputEmail]);
            $buyer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($buyer && password_verify($inputPassword, $buyer['password'])) {
                session_regenerate_id(true);
                $_SESSION['role'] = 'buyer';
                $_SESSION['username'] = $buyer['contact_person_name'];
                $_SESSION['email'] = $inputEmail;
                $_SESSION['user_id'] = $buyer['id'];
                $_SESSION['buyer_id'] = $buyer['id'];
                $_SESSION['buyer_name'] = $buyer['contact_person_name'];
                $_SESSION['company_name'] = $buyer['company_name'];
                $_SESSION['user_type'] = 'buyer';
                return true;
            }
        } catch (Exception $e) {
            error_log('Buyer Auth error: ' . $e->getMessage());
        }
        return false;
    }
}
?>