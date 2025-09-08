<?php
include_once __DIR__ . '/../config/config.php'; 
include_once __DIR__ . '/../include/inc/header.php';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>operationadmin/operation_dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3" style="font-size: 0.9rem;">
            <?php
            $user_type_display = ucwords(str_replace('admin', ' Admin', $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'Admin'));
            $username = $_SESSION['username'] ?? '';
            
            if ($user_type_display === 'Operation Admin' && $username === 'Operation Admin') {
                echo 'Operation Admin';
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
        <a class="nav-link" href="<?php echo BASE_URL; ?>operationadmin/operation_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Operations
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBOM" aria-expanded="false" aria-controls="collapseBOM">
            <i class="fas fa-fw fa-cubes"></i> <span>Bill Of Material</span>
        </a>
        <div id="collapseBOM" class="collapse" aria-labelledby="headingBOM" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/bom/add.php">Add BOM</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/bom/index.php">View BOM</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO" aria-expanded="false" aria-controls="collapsePO">
            <i class="fas fa-fw fa-file-alt"></i> <span>PO Number</span>
        </a>
        <div id="collapsePO" class="collapse" aria-labelledby="headingPO" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/po/add.php">Add PO</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/po/index.php">View PO</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSO" aria-expanded="false" aria-controls="collapseSO">
            <i class="fas fa-fw fa-shopping-cart"></i> <span>Sale Order</span>
        </a>
        <div id="collapseSO" class="collapse" aria-labelledby="headingSO" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/so/index.php">View Sale Order</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseJCI" aria-expanded="false" aria-controls="collapseJCI">
            <i class="fas fa-fw fa-file-signature"></i> <span>JCI</span>
        </a>
        <div id="collapseJCI" class="collapse" aria-labelledby="headingJCI" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/jci/add.php">Add JCI</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>operationadmin/jci/index.php">View JCI</a>
            </div>
        </div>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
