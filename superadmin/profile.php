<?php
session_start();
include_once __DIR__ . '/../config/config.php';

// Ensure ROOT_DIR_PATH is defined if not already from config.php
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../');
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';
require_once ROOT_DIR_PATH . 'core/auth.php';

$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$admin_department = $_SESSION['admin_department'] ?? 'N/A';

// The sidebar is now included conditionally in header.php
// include_once ROOT_DIR_PATH . 'superadmin/sidebar.php'; // THIS LINE IS NOW REMOVED

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$userType = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$adminData = null;
$error = '';
$success = '';

global $conn;

if ($userType === 'superadmin') {
    // Handle superadmin (not in database)
    $adminData = [
        'id' => 1,
        'name' => 'Super Admin',
        'email' => $_ENV['SUPERADMIN_EMAIL'] ?? 'superadmin@purewood.in',
        'department' => 'superadmin',
        'profile_picture' => null
    ];
} elseif ($userId) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email, department, profile_picture FROM admin_users WHERE id = ?");
        $stmt->execute([$userId]);
        $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$adminData) {
            $error = "Admin user not found.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "User ID not found in session.";
}

// Sidebar is now handled in header.php

// Define base URL for profile images
$baseProfileImageUrl = BASE_URL . 'assets/images/upload/admin_profiles/';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-img-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px auto;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ddd;
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-header h6 {
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Admin Profile</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <?php if ($adminData): ?>
                            <form id="adminProfileForm" enctype="multipart/form-data">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($adminData['id']); ?>">
                                
                                <div class="mb-3 text-center">
                                    <div class="profile-img-container">
                                        <img id="profilePicturePreview" class="profile-img"
                                             src="<?php echo !empty($adminData['profile_picture']) ? $baseProfileImageUrl . htmlspecialchars($adminData['profile_picture']) . '?t=' . time() : BASE_URL . 'assets/images/undraw_profile.svg'; ?>"
                                             alt="Profile Picture">
                                    </div>
                                    <label for="profilePictureInput" class="btn btn-primary btn-sm">Change Profile Picture</label>
                                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" class="d-none">
                                    <?php if (!empty($adminData['profile_picture'])): ?>
                                        <button type="button" id="removeProfilePicture" class="btn btn-danger btn-sm ms-2">Remove Picture</button>
                                    <?php endif; ?>
                                    <input type="hidden" name="existing_profile_picture" value="<?php echo htmlspecialchars($adminData['profile_picture'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($adminData['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($adminData['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" value="<?php echo htmlspecialchars(ucfirst($adminData['department'] ?? 'N/A')); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        <?php else: ?>
                            <p>Unable to load profile data.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#profilePictureInput').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profilePicturePreview').attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#removeProfilePicture').click(function() {
                if (confirm('Are you sure you want to remove your profile picture?')) {
                    $('#profilePicturePreview').attr('src', '<?php echo BASE_URL; ?>assets/images/undraw_profile.svg').show();
                    $('#profilePictureInput').val(''); // Clear the file input
                    $('[name="existing_profile_picture"]').val('removed'); // Indicate removal to backend
                }
            });

            $('#adminProfileForm').submit(function(e) {
                e.preventDefault();

                const newPassword = $('#password').val();
                const confirmPassword = $('#confirm_password').val();

                if (newPassword && newPassword !== confirmPassword) {
                    alert('New password and confirm password do not match.');
                    return;
                }

                const formData = new FormData(this);

                $.ajax({
                    url: '<?php echo BASE_URL; ?>superadmin/update_admin_profile.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload(); // Reload to reflect changes
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        try {
                            const response = JSON.parse(xhr.responseText);
                            alert(response.message || 'An error occurred');
                        } catch (e) {
                            alert('An error occurred: ' + error);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
