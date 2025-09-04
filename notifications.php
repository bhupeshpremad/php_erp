<?php
include_once __DIR__ . '/config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} else {
    header('Location: index.php');
    exit;
}

// Initialize notification system
require_once ROOT_DIR_PATH . 'core/NotificationSystem.php';
NotificationSystem::init($conn);

$user_id = $_SESSION['user_id'] ?? null;
$notifications = NotificationSystem::getForUser($user_type, $user_id, 50);
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">All Notifications (<?= count($notifications) ?>)</h6>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Search notifications..." id="searchNotifications">
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 text-right">
                            <button class="btn btn-success btn-sm" onclick="markAllAsRead()">Mark All Read</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No notifications found</h5>
                <p class="text-muted">You're all caught up!</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="notificationsTable">
                    <thead>
                        <tr>
                            <th width="50">Status</th>
                            <th width="60">Module</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th width="150">Date</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                        <tr class="notification-row <?= $notification['is_read'] ? '' : 'table-light' ?>" data-id="<?= $notification['id'] ?>">
                            <td class="text-center">
                                <?php if ($notification['is_read']): ?>
                                <i class="fas fa-envelope-open text-muted" title="Read"></i>
                                <?php else: ?>
                                <i class="fas fa-envelope text-primary" title="Unread"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <i class="<?= NotificationSystem::getModuleIcon($notification['module']) ?> text-info" title="<?= ucfirst($notification['module']) ?>"></i>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                <?php if (!$notification['is_read']): ?>
                                <span class="badge badge-primary badge-sm ml-2">New</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($notification['message']) ?></td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M d, Y H:i', strtotime($notification['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!$notification['is_read']): ?>
                                <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(<?= $notification['id'] ?>)">
                                    <i class="fas fa-check"></i> Mark Read
                                </button>
                                <?php else: ?>
                                <span class="text-muted small">Read</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('<?= BASE_URL ?>ajax/mark_notifications_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_read', id: notificationId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-id="${notificationId}"]`);
            row.classList.remove('table-light');
            row.querySelector('.fas.fa-envelope').className = 'fas fa-envelope-open text-muted';
            row.querySelector('.badge-primary').remove();
            row.querySelector('td:last-child').innerHTML = '<span class="text-muted small">Read</span>';
        }
    });
}

function markAllAsRead() {
    fetch('<?= BASE_URL ?>ajax/mark_notifications_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_all_read'})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Search functionality
document.getElementById('searchNotifications').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#notificationsTable tbody tr');
    
    rows.forEach(row => {
        const title = row.cells[2].textContent.toLowerCase();
        const message = row.cells[3].textContent.toLowerCase();
        
        if (title.includes(searchTerm) || message.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>