<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>communicationadmin/communication_dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-comments"></i>
        </div>
        <div class="sidebar-brand-text mx-3" style="font-size: 0.9rem;">
            Communication Admin
        </div>
    </a>

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo BASE_URL; ?>communicationadmin/communication_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <div class="sidebar-heading">
        My Profile
    </div>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo BASE_URL; ?>superadmin/profile.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Profile</span></a>
    </li>

    <div class="sidebar-heading">
        Communication
    </div>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-bell"></i>
            <span>Notifications</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-envelope"></i>
            <span>Email Templates</span></a>
    </li>

    <div class="sidebar-heading">
        Supplier Management
    </div>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSupplier" aria-expanded="true" aria-controls="collapseSupplier">
            <i class="fas fa-fw fa-truck"></i> <span>Suppliers</span>
        </a>
        <div id="collapseSupplier" class="collapse" aria-labelledby="headingSupplier" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>superadmin/supplier/list.php">All Suppliers</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>superadmin/supplier/pending.php">Pending Approvals</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>superadmin/supplier/quotations.php">Quotations</a>
            </div>
        </div>
    </li>
    
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBuyer" aria-expanded="true" aria-controls="collapseBuyer">
            <i class="fas fa-fw fa-shopping-bag"></i> <span>Buyers</span>
        </a>
        <div id="collapseBuyer" class="collapse" aria-labelledby="headingBuyer" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>superadmin/buyer/list.php">All Buyers</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>superadmin/buyer/pending.php">Pending Approvals</a>
            </div>
        </div>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
    
    <hr class="sidebar-divider my-0">
    <hr class="sidebar-divider">
    <hr class="sidebar-divider">
</ul>