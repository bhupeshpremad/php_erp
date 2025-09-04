<?php
// Initialize notification system
require_once ROOT_PATH . '/core/NotificationSystem.php';
NotificationSystem::init($conn);

// Get current user info
$currentUserType = $_SESSION['user_type'] ?? 'superadmin';
$currentUserId = $_SESSION['admin_id'] ?? null;

// Get notifications
$notifications = NotificationSystem::getForUser($currentUserType, $currentUserId, 50);
$unreadCount = NotificationSystem::getUnreadCount($currentUserType, $currentUserId);

// Icon mapping
$iconMap = [
    'quotation' => 'fas fa-file-invoice',
    'po' => 'fas fa-file-alt',
    'lead' => 'fas fa-bullhorn',
    'customer' => 'fas fa-users',
    'payment' => 'fas fa-dollar-sign'
];

// Color mapping
$colorMap = [
    'info' => 'bg-primary',
    'success' => 'bg-success',
    'warning' => 'bg-warning',
    'danger' => 'bg-danger'
];
?>

<li class="nav-item dropdown no-arrow mx-1">
    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        <?php if ($unreadCount > 0): ?>
        <span class="badge badge-danger badge-counter"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
        <?php endif; ?>
    </a>
    
    <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown" style="max-height: 400px; overflow-y: auto; width: 350px;">
        <h6 class="dropdown-header">
            Notifications Center
            <?php if ($unreadCount > 0): ?>
            <small class="text-muted">(<?php echo $unreadCount; ?> unread)</small>
            <?php endif; ?>
        </h6>
        
        <?php if (empty($notifications)): ?>
        <div class="dropdown-item text-center text-muted py-3">
            <i class="fas fa-bell-slash fa-2x mb-2"></i><br>
            No notifications
        </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
            <a class="dropdown-item d-flex align-items-center <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>" 
               href="#" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                <div class="mr-3">
                    <div class="icon-circle <?php echo $colorMap[$notification['type']] ?? 'bg-primary'; ?>">
                        <i class="<?php echo $iconMap[$notification['module']] ?? 'fas fa-info'; ?> text-white"></i>
                    </div>
                </div>
                <div>
                    <div class="small text-gray-500"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></div>
                    <span class="font-weight-bold"><?php echo htmlspecialchars($notification['title']); ?></span>
                    <div class="small text-muted"><?php echo htmlspecialchars($notification['message']); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center small text-gray-500" href="#" onclick="markAllAsRead()">
            Mark All as Read
        </a>

    </div>
</li>

<script>
$(document).ready(function() {
    // Ensure Bootstrap dropdown functionality
    $('#alertsDropdown').dropdown();
    
    // Fix dropdown toggle
    $('#alertsDropdown').on('click', function(e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggle();
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').hide();
        }
    });
});

function markAsRead(notificationId) {
    $.post('<?php echo BASE_URL; ?>api/notifications/mark_read.php', {
        id: notificationId
    }, function() {
        location.reload();
    });
}

function markAllAsRead() {
    $.post('<?php echo BASE_URL; ?>api/notifications/mark_all_read.php', function() {
        location.reload();
    });
}

// Auto refresh notifications every 30 seconds
setInterval(function() {
    $('#alertsDropdown').load(location.href + ' #alertsDropdown > *');
}, 30000);
</script>