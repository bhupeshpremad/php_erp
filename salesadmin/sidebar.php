<?php
    include_once __DIR__ . '/../config/config.php'; 
    include_once __DIR__ . '/../include/inc/header.php';
?>

<ul class="navbar-nav bg-gradient-success sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>salesadmin/salesadmin_dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="sidebar-brand-text mx-3" style="font-size: 0.9rem;">
            <?php
            $user_type_display = ucwords(str_replace('admin', ' Admin', $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'Admin'));
            $username = $_SESSION['username'] ?? '';
            
            if ($user_type_display === 'Sales Admin' && $username === 'Sales Admin') {
                echo 'Sales Admin';
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
        <a class="nav-link" href="<?php echo BASE_URL; ?>salesadmin/salesadmin_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        My Profile
    </div>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/profile.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Profile</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        SALES MANAGEMENT
    </div>

    <!-- Lead -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLead" aria-expanded="true" aria-controls="collapseLead">
            <i class="fas fa-fw fa-bullhorn"></i> <span>Lead Management</span>
        </a>
        <div id="collapseLead" class="collapse" aria-labelledby="headingLead" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/lead/add.php">Add Lead</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/lead/index.php">View Leads</a>
            </div>
        </div>
    </li>

    <!-- Quotation -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseQuote" aria-expanded="true" aria-controls="collapseQuote">
            <i class="fas fa-fw fa-file-invoice"></i> <span>Quotations</span>
        </a>
        <div id="collapseQuote" class="collapse" aria-labelledby="headingQuote" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/quotation/add.php">Create Quotation</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/quotation/index.php">View Quotations</a>
            </div>
        </div>
    </li>

    <!-- Customer -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer" aria-expanded="true" aria-controls="collapseCustomer">
            <i class="fas fa-fw fa-users"></i> <span>Customers</span>
        </a>
        <div id="collapseCustomer" class="collapse" aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/customer/index.php">View Customers</a>
            </div>
        </div>
    </li>

    <!-- Proforma Invoice -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePI" aria-expanded="true" aria-controls="collapsePI">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Proforma Invoice</span>
        </a>
        <div id="collapsePI" class="collapse" aria-labelledby="headingPI" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/pi/index.php">View PIs</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>