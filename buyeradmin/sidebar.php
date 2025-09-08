<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Buyer Panel</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Quotations
    </div>

    <!-- Nav Item - Add Quotation -->
    <li class="nav-item <?php echo ($current_page == 'add.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>buyeradmin/quotation/add.php">
            <i class="fas fa-fw fa-plus"></i>
            <span>Add Quotation</span>
        </a>
    </li>

    <!-- Nav Item - View Quotations -->
    <li class="nav-item <?php echo ($current_page == 'list.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>buyeradmin/quotation/list.php">
            <i class="fas fa-fw fa-file-invoice"></i>
            <span>View Quotations</span>
        </a>
    </li>

    <!-- Nav Item - View PIs -->
    <li class="nav-item">
        <a class="nav-link" href="../modules/pi/index.php">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>View PIs</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Account
    </div>

    <!-- Nav Item - Profile -->
    <li class="nav-item">
        <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/profile.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Profile</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>