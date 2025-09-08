<?php
    include_once __DIR__ . '/../config/config.php'; 
    include_once __DIR__ . '/../include/inc/header.php';
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>accountsadmin/accounts_dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <?php
            $username = $_SESSION['username'] ?? null;
            echo 'Accounts';
            if ($username) {
                echo ' - ' . htmlspecialchars($username);
            }
            ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo BASE_URL; ?>accountsadmin/accounts_dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Accounts
    </div>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBOM" aria-expanded="false" aria-controls="collapseBOM">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Bill Of Material</span>
        </a>
        <div id="collapseBOM" class="collapse" aria-labelledby="headingAccounts" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/bom/add.php">Add Bill Of Material</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/bom/index.php">View Bill Of Material</a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO" aria-expanded="false" aria-controls="collapsePO">
            <i class="fas fa-fw fa-file-invoice"></i> <span>PO</span>
        </a>
        <div id="collapsePO" class="collapse" aria-labelledby="headingPO" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/po/add.php">Add PO</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/po/index.php">View PO</a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAccounts" aria-expanded="false" aria-controls="collapseAccounts">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Sale Order</span>
        </a>
        <div id="collapseAccounts" class="collapse" aria-labelledby="headingAccounts" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/so/index.php">View Sale Order</a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseJCI" aria-expanded="false" aria-controls="collapseJCI">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>JCI</span>
        </a>
        <div id="collapseJCI" class="collapse" aria-labelledby="headingJCI" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/jci/add.php">Add JCI</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/jci/index.php">View JCI</a>
            </div>
        </div>
    </li>
    <!-- <div class="sidebar-heading">
        PO
    </div> -->

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchase" aria-expanded="false" aria-controls="collapsePurchase">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Purchase</span>
        </a>
        <div id="collapsePurchase" class="collapse" aria-labelledby="headingPurchase" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/purchase/add.php">Add Purchase</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/purchase/index.php">View Purchase</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayment" aria-expanded="false" aria-controls="collapsePayment">
            <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Make Payment</span>
        </a>
        <div id="collapsePayment" class="collapse" aria-labelledby="headingPayment" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/payment/add.php">Add Make Payment</a>
                <a class="collapse-item" href="<?php echo BASE_URL; ?>accountsadmin/payment/index.php">View Make Payment</a>
            </div>
        </div>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>