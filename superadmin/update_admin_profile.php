<?php
session_start();
include_once __DIR__ . '/../config/config.php';
require_once ROOT_DIR_PATH . 'core/auth.php';
// require_once ROOT_DIR_PATH . 'core/utils.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;

// Check user type
$userType = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

if (!$userId && $userType !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'User ID not found in session.']);
    exit;
}

global $conn;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$existingProfilePicture = $_POST['existing_profile_picture'] ?? null;

// Basic validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

// Define upload directory
$uploadDir = ROOT_DIR_PATH . 'assets/images/upload/admin_profiles/';

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
        exit;
    }
}

try {
    $conn->beginTransaction();

    if ($userType === 'superadmin') {
        // Handle superadmin profile update (limited to session updates)
        $_SESSION['username'] = $name;
        $_SESSION['email'] = $email;
        
        // For superadmin, we can only update session data
        echo json_encode(['success' => true, 'message' => 'Superadmin profile updated in session.']);
        $conn->commit();
        exit;
    }

    // Fetch current user data to compare email and password hash
    $stmt = $conn->prepare("SELECT name, email, password, profile_picture FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentAdminData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentAdminData) {
        throw new Exception("User not found for update.");
    }

    $updateFields = [];
    $updateValues = [];

    // Update name
    if ($name && $name !== ($currentAdminData['name'] ?? '')) {
        $updateFields[] = 'name = ?';
        $updateValues[] = $name;
    }

    // Update email - check for uniqueness if changed
    if ($email !== $currentAdminData['email']) {
        $stmtCheckEmail = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ? AND id != ?");
        $stmtCheckEmail->execute([$email, $userId]);
        if ($stmtCheckEmail->fetchColumn() > 0) {
            throw new Exception("Email already in use by another admin.");
        }
        $updateFields[] = 'email = ?';
        $updateValues[] = $email;
    }

    // Update password
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            throw new Exception("New password and confirm password do not match.");
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateFields[] = 'password = ?';
        $updateValues[] = $hashedPassword;
    }

    // Handle profile picture
    $profilePictureName = $currentAdminData['profile_picture'];

    // If a new file is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpName = $_FILES['profile_picture']['tmp_name'];
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['profile_picture']['name']));
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        if (in_array($fileExt, $allowedExtensions) && $_FILES['profile_picture']['size'] <= $maxFileSize) {
            // Delete old picture if exists
            if (!empty($profilePictureName) && file_exists($uploadDir . $profilePictureName)) {
                unlink($uploadDir . $profilePictureName);
            }

            $newFileName = 'admin_profile_' . $userId . '_' . time() . '.' . $fileExt;
            $targetFilePath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                $profilePictureName = $newFileName;
            } else {
                throw new Exception("Failed to upload profile picture.");
            }
        } else {
            throw new Exception("Invalid file type or size for profile picture.");
        }
    } elseif ($existingProfilePicture === 'removed') {
        // If user chose to remove picture
        if (!empty($profilePictureName) && file_exists($uploadDir . $profilePictureName)) {
            unlink($uploadDir . $profilePictureName);
        }
        $profilePictureName = null;
    }

    // Only update profile_picture if it has changed
    if ($profilePictureName !== $currentAdminData['profile_picture']) {
        $updateFields[] = 'profile_picture = ?';
        $updateValues[] = $profilePictureName;
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => true, 'message' => 'No changes detected.']);
        $conn->commit();
        exit;
    }

    $updateFields[] = 'updated_at = NOW()';
    
    $stmt = $conn->prepare("UPDATE admin_users SET " . implode(', ', $updateFields) . " WHERE id = ?");
    $stmt->execute(array_merge($updateValues, [$userId]));

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
}

?>
