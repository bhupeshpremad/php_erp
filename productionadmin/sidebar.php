<?php
include_once __DIR__ . '/../config/config.php'; 
include_once __DIR__ . '/../include/inc/header.php';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>productionadmin/production_dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3" style="font-size: 0.9rem;">
            <?php
            $user_type_display = ucwords(str_replace('admin', ' Admin', $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'Admin'));
            $username = $_SESSION['username'] ?? '';
            
            if ($user_type_display === 'Production Admin' && $username === 'Production Admin') {
                echo 'Production Admin';
            } elseif ($username) {
                echo htmlspecialchars($username) . ' - ' . htmlspecialchars($user_type_display);
            } else {
                echo htmlspecialchars($user_type_display);
            }
            ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo BASE_URL; ?>productionadmin/production_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Production
    </div>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-industry"></i> <span>Production Module</span>
        </a>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
