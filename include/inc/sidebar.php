<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_type = $_SESSION['user_type'] ?? 'guest';

$base_path = '';
$dashboard_link = '';
$lead_add = '';
$lead_view = '';
$quotation_add = '';
$quotation_view = '';
$customer_view = '';
$pi_add = '';
$pi_view = '';
$purchase_add = '';
$purchase_view = '';
$make_Payment_add = '';
$make_Payment_view = '';

$bom_add = '';
$bom_view = '';
$jci_add = '';
$jci_view = '';
$so_add = '';
$so_view = '';

if ($user_type === 'superadmin') {
        $base_path = 'superadmin';
        $dashboard_link = BASE_URL . $base_path . '/superadmin_dashboard.php';
        $lead_add = BASE_URL . $base_path . '/sales/lead/add.php';
        $lead_view = BASE_URL . $base_path . '/sales/lead/index.php';
        $quotation_add = BASE_URL . $base_path . '/sales/quotation/add.php';
        $quotation_view = BASE_URL . $base_path . '/sales/quotation/index.php';
        $customer_view = BASE_URL . $base_path . '/sales/customer/index.php';
        $pi_add = BASE_URL . $base_path . '/sales/pi/add.php';
        $pi_view = BASE_URL . $base_path . '/sales/pi/index.php';
        $bom_add = BASE_URL . $base_path . '/bom/add.php';
        $bom_view = BASE_URL . $base_path . '/bom/index.php';
        $jci_add = BASE_URL . $base_path . '/jci/add.php';
        $jci_view = BASE_URL . $base_path . '/jci/index.php';
        $so_add = BASE_URL . $base_path . '/accounts/so/add.php';
        $so_view = BASE_URL . $base_path . '/accounts/so/index.php';
        $purchase_add = BASE_URL . $base_path . '/accounts/purchase/add.php';
        $purchase_view = BASE_URL . $base_path . '/accounts/purchase/index.php';
        $make_Payment_add = BASE_URL . $base_path . '/accounts/make_Payment/add.php';
        $make_Payment_view = BASE_URL . $base_path . '/accounts/make_Payment/index.php';
    } elseif ($user_type === 'salesadmin') {
        $base_path = 'salesadmin';
        $dashboard_link = BASE_URL . $base_path . '/salesadmin_dashboard.php';
        $lead_add = BASE_URL . $base_path . '/sales/lead/add.php';
        $lead_view = BASE_URL . $base_path . '/sales/lead/index.php';
        $quotation_add = BASE_URL . $base_path . '/sales/quotation/add.php';
        $quotation_view = BASE_URL . $base_path . '/sales/quotation/index.php';
        $customer_view = BASE_URL . $base_path . '/sales/customer/index.php';
        $pi_add = BASE_URL . $base_path . '/sales/pi/add.php';
        $pi_view = BASE_URL . $base_path . '/sales/pi/index.php';
    } elseif ($user_type === 'operation') {
        $base_path = 'operationadmin';
        $dashboard_link = BASE_URL . $base_path . '/operation_dashboard.php';
        $bom_add = BASE_URL . $base_path . '/bom/add.php';
        $bom_view = BASE_URL . $base_path . '/bom/index.php';
        $so_add = BASE_URL . $base_path . '/so/index.php';
        $so_view = BASE_URL . $base_path . '/so/index.php';
        $jci_add = BASE_URL . $base_path . '/jci/add.php';
        $jci_view = BASE_URL . $base_path . '/jci/index.php';
        $purchase_add = '';
        $purchase_view = '';
        $make_Payment_add = '';
        $make_Payment_view = '';
    } elseif ($user_type === 'accounts') {
        $base_path = 'accountsadmin';
        $dashboard_link = BASE_URL . $base_path . '/accounts_dashboard.php';
        $purchase_add = BASE_URL . $base_path . '/purchase/add.php';
        $purchase_view = BASE_URL . $base_path . '/purchase/index.php';
        $make_Payment_add = BASE_URL . $base_path . '/payment/add.php';
        $make_Payment_view = BASE_URL . $base_path . '/payment/index.php';
} else {
    $dashboard_link = BASE_URL;
    $lead_add = '#';
    $lead_view = '#';
    $quotation_add = '#';
    $quotation_view = '#';
    $customer_view = '#';
    $pi_add = '#';
    $pi_view = '#';
}
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $dashboard_link; ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">
            <?php
            $username = $_SESSION['username'] ?? null;
            if ($user_type === 'superadmin') {
                echo 'Super Admin';
                if ($username) {
                    echo ' - ' . htmlspecialchars($username);
                }
            } elseif ($user_type === 'salesadmin') {
                echo 'Sales Admin';
                if ($username) {
                    echo ' - ' . htmlspecialchars($username);
                }
            } elseif ($user_type === 'accounts') {
                echo 'Accounts';
                if ($username) {
                    echo ' - ' . htmlspecialchars($username);
                }
            } else {
                echo 'Dashboard';
            }
            ?>
        </div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item active">
        <a class="nav-link" href="<?php echo $dashboard_link; ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <?php if ($user_type === 'superadmin' || $user_type === 'salesadmin') : ?>
        <div class="sidebar-heading">
            SALES
        </div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLead" aria-expanded="true" aria-controls="collapseLead">
                <i class="fas fa-fw fa-bullhorn"></i> <span>Lead</span>
            </a>
            <div id="collapseLead" class="collapse" aria-labelledby="headingLead" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $lead_add; ?>">Add Lead</a>
                    <a class="collapse-item" href="<?php echo $lead_view; ?>">View Lead</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseQuote" aria-expanded="true" aria-controls="collapseQuote">
                <i class="fas fa-fw fa-file-invoice"></i> <span>Quotation</span>
            </a>
            <div id="collapseQuote" class="collapse" aria-labelledby="headingQuote" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $quotation_add; ?>">Add Quotation</a>
                    <a class="collapse-item" href="<?php echo $quotation_view; ?>">View Quotation</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer" aria-expanded="true" aria-controls="collapseCustomer">
                <i class="fas fa-fw fa-users"></i> <span>Customer</span>
            </a>
            <div id="collapseCustomer" class="collapse" aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $customer_view; ?>">View Customer Details</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePerforma" aria-expanded="true" aria-controls="collapsePerforma">
                <i class="fas fa-fw fa-file-invoice-dollar"></i> <span>Performa Invoice</span>
            </a>
            <div id="collapsePerforma" class="collapse" aria-labelledby="headingPerforma" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $pi_add; ?>">Add Performa Invoice</a>
                    <a class="collapse-item" href="<?php echo $pi_view; ?>">View Performa Invoice</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <?php if ($user_type === 'superadmin') : ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
            Accounts
        </div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBOM" aria-expanded="true" aria-controls="collapseBOM">
                <i class="fas fa-fw fa-cubes"></i> <span>Bill Of Material</span>
            </a>
            <div id="collapseBOM" class="collapse" aria-labelledby="headingBOM" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $bom_add; ?>">Add BOM</a>
                    <a class="collapse-item" href="<?php echo $bom_view; ?>">View BOM</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO" aria-expanded="true" aria-controls="collapsePO">
                <i class="fas fa-fw fa-file-alt"></i> <span>PO Number</span>
            </a>
            <div id="collapsePO" class="collapse" aria-labelledby="headingPurchase" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $purchase_add; ?>">Add PO</a>
                    <a class="collapse-item" href="<?php echo $purchase_view; ?>">View PO</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchaseSuperadmin" aria-expanded="true" aria-controls="collapsePurchaseSuperadmin">
                <i class="fas fa-fw fa-bullhorn"></i> <span>Purchase (Superadmin)</span>
            </a>
            <div id="collapsePurchaseSuperadmin" class="collapse" aria-labelledby="headingPurchaseSuperadmin" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/purchase/add.php">Add Purchase</a>
                    <a class="collapse-item" href="<?php echo BASE_URL; ?>modules/purchase/index.php">View Purchase</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayment" aria-expanded="true" aria-controls="collapsePayment">
                <i class="fas fa-fw fa-file-invoice"></i> <span>Make Payment</span>
            </a>
            <div id="collapsePayment" class="collapse" aria-labelledby="headingPayment" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $make_Payment_add; ?>">Add Make Payment</a>
                    <a class="collapse-item" href="<?php echo $make_Payment_view; ?>">View Payments</a>
                </div>
            </div>
        </li>

        
    <?php endif; ?>

    <?php if ($user_type === 'accounts') : ?>
        <div class="sidebar-heading">
            Accounts
        </div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePurchase" aria-expanded="true" aria-controls="collapsePurchase">
                <i class="fas fa-fw fa-bullhorn"></i> <span>Purchase</span>
            </a>
            <div id="collapsePurchase" class="collapse" aria-labelledby="headingPurchase" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $purchase_add; ?>">Add Purchase</a>
                    <a class="collapse-item" href="<?php echo $purchase_view; ?>">View Purchase</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayment" aria-expanded="true" aria-controls="collapsePayment">
                <i class="fas fa-fw fa-file-invoice"></i> <span>Make Payment</span>
            </a>
            <div id="collapsePayment" class="collapse" aria-labelledby="headingPayment" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="<?php echo $make_Payment_add; ?>">Add Make Payment</a>
                    <a class="collapse-item" href="<?php echo $make_Payment_view; ?>">View Payments</a>
                </div>
            </div>
        </li>
    <?php endif; ?>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>