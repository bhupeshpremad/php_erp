<?php
echo "<h2>🧹 Project Cleanup</h2>";

// Files to delete (debug, temp, and documentation files)
$filesToDelete = [
    'check_files.php',
    'check_structure.php', 
    'check_workflow.php',
    'cleanup_self.php',
    'cleanup.php',
    'comprehensive_system_check.php',
    'create_missing_modules.php',
    'debug_image_paths.php',
    'debug_login.php',
    'debug.php',
    'final_system_report.php',
    'fix_admin_departments.php',
    'fix_db_config.php',
    'fix_lock_columns.php',
    'fix_login.php',
    'import_database.php',
    'project_status.php',
    'system_check.php',
    'temp_local_config.php',
    'DEPLOYMENT.md',
    'FINAL_UPLOAD_LIST.txt',
    'LIVE_DEPLOYMENT.md',
    'LIVE_UPLOAD_FINAL.txt',
    'PROJECT_REVIEW_SUMMARY.md',
    'PROJECT_STRUCTURE.md',
    'TODO.md',
    'UPLOAD_CHECKLIST.md',
    'UPLOAD_LIST.txt'
];

$deletedCount = 0;
$errors = [];

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✅ Deleted: $file<br>";
            $deletedCount++;
        } else {
            echo "❌ Failed to delete: $file<br>";
            $errors[] = $file;
        }
    } else {
        echo "⚠️ Not found: $file<br>";
    }
}

echo "<hr>";
echo "<h3>📊 Cleanup Summary</h3>";
echo "<p><strong>Files deleted:</strong> $deletedCount</p>";
echo "<p><strong>Errors:</strong> " . count($errors) . "</p>";

if (count($errors) > 0) {
    echo "<p><strong>Failed files:</strong> " . implode(', ', $errors) . "</p>";
}

echo "<h3>✅ Remaining Important Files</h3>";
echo "<ul>";
echo "<li>index.php - Main login</li>";
echo "<li>admin_register.php - Admin registration</li>";
echo "<li>buyer_register.php - Buyer registration</li>";
echo "<li>forgot_password_handler.php - Password reset</li>";
echo "<li>logout.php - Logout handler</li>";
echo "<li>migrate.php - Database migrations</li>";
echo "<li>notifications.php - Notifications page</li>";
echo "<li>setup.php - Initial setup</li>";
echo "<li>crm_purewood.sql - Database backup</li>";
echo "<li>README.md - Project documentation</li>";
echo "</ul>";

echo "<p><strong>✅ Project is now clean and organized!</strong></p>";
echo "<p><em>Delete this cleanup file after running.</em></p>";
?>