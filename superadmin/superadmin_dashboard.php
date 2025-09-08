<?php
session_start();
include_once __DIR__ . '/../config/config.php';
include_once ROOT_DIR_PATH . 'core/NotificationSystem.php'; // Ensure NotificationSystem is available
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

// Check if user is logged in as superadmin
if ($user_type !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';
include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';

global $conn;

// Removed redundant has_module_access function as superadmin has access to all
// function has_module_access($module) {
//     global $user_type;
//     $superadmin_modules = [
//         'lead', 'quotation', 'customer', 'pi', 'bom', 'po', 'so', 'jci', 'make_payment', 'purchase'
//     ];
//     return $user_type === 'superadmin' && in_array($module, $superadmin_modules);
// }

$module_display_names = [
    'lead' => 'Lead',
    'quotation' => 'Quotation',
    'customer' => 'Customer',
    'pi' => 'Performa Invoice',
    'bom' => 'Bill Of Material',
    'po' => 'Purchase Order',
    'purchase' => 'Purchase',
    'so' => 'Sale Order',
    'jci' => 'Job Card',
    'payment' => 'Payment', // Renamed from make_payment for consistency
    'admin_users' => 'Admin Users',
    'buyers' => 'Buyers',
    'suppliers' => 'Suppliers',
];

$module_urls = [
    'lead' => BASE_URL . 'modules/lead/index.php',
    'quotation' => BASE_URL . 'modules/quotation/index.php',
    'customer' => BASE_URL . 'modules/customer/index.php',
    'pi' => BASE_URL . 'modules/pi/index.php',
    'bom' => BASE_URL . 'modules/bom/index.php',
    'po' => BASE_URL . 'modules/po/index.php',
    'purchase' => BASE_URL . 'modules/purchase/index.php',
    'so' => BASE_URL . 'modules/so/index.php',
    'jci' => BASE_URL . 'modules/jci/index.php',
    'payment' => BASE_URL . 'modules/payments/index.php', // Note: folder is 'payments'
    'admin_users' => BASE_URL . 'superadmin/admin/list.php',
    'buyers' => BASE_URL . 'superadmin/buyer/list.php',
    'suppliers' => BASE_URL . 'superadmin/supplier/list.php',
];

$all_modules = array_keys($module_display_names);

$module_data = [];
$module_status = [];

foreach ($all_modules as $mod) {
    $counts = ['total' => 0, 'pending' => 0, 'completed' => 0]; // Default counts
    
    switch ($mod) {
        case 'lead':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM leads");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'quotation':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN approve = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approve = 1 THEN 1 ELSE 0 END) as completed
                FROM quotations");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'customer':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total
                FROM customers"); // Corrected to use customers table
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => $total]; // Assuming all customers are 'completed'
            break;
        case 'pi':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pi");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'bom':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM bom_main");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'po':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as completed
                FROM po_main"); // Changed completed to Approved
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'purchase':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM purchase_main");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'so':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sell_order");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'jci':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jci_main");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'payment': // Changed from make_payment
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM payments");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            $counts = ['total' => $total, 'pending' => 0, 'completed' => 0];
            break;
        case 'admin_users':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as completed
                FROM admin_users");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'buyers':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as completed
                FROM buyers");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'suppliers':
            $stmt = $conn->prepare("SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as completed
                FROM suppliers"); // Assuming 'active' means completed for suppliers
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        default:
            $counts = ['total' => 0, 'pending' => 0, 'completed' => 0];
    }
    $module_data[$mod] = $counts;
    $module_status[$mod] = $counts;
}

$chart_modules = $module_display_names;
// No need to unset customer as it now has pending/completed logic

$chartLabels = array_values($chart_modules);
$chartKeys = array_keys($chart_modules);

$chartTotalData = [];
$chartPendingData = [];
$chartCompletedData = [];

foreach ($chartKeys as $key) {
    $chartTotalData[] = $module_status[$key]['total'];
    $chartPendingData[] = $module_status[$key]['pending'];
    $chartCompletedData[] = $module_status[$key]['completed'];
}

?>

<body id="page-top">
    <div id="wrapper" class="container-fluid">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Module Line Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="moduleLineChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Module Column Chart</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="moduleBarChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-md-4 g-4">
                        <?php foreach ($module_data as $module => $counts): ?>
                        <div class="col mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?php echo $module_display_names[$module] ?? ucfirst(str_replace('_', ' ', $module)); ?></div>
                                    <?php if (is_array($counts)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <tbody>
                                                    <?php foreach ($counts as $key => $value): ?>
                                                    <tr>
                                                        <td><?php echo ucfirst(str_replace('_', ' ', $key)); ?></td>
                                                        <td class="text-end"><?php echo $value; ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Count</span>
                                            <span class="fw-bold text-end" style="width:60px;"><?php echo $counts; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-center mt-3">
                                        <?php if (isset($module_urls[$module])): ?>
                                            <a href="<?php echo $module_urls[$module]; ?>" class="btn btn-sm btn-info">View Details</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
        </div>
    </div>
    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script>
        const chartLabels = <?php echo json_encode($chartLabels); ?>;
        const chartKeys = <?php echo json_encode($chartKeys); ?>;
        const chartTotalData = <?php echo json_encode($chartTotalData); ?>;
        const chartPendingData = <?php echo json_encode($chartPendingData); ?>;
        const chartCompletedData = <?php echo json_encode($chartCompletedData); ?>;

        const ctxLine = document.getElementById('moduleLineChart').getContext('2d');
        const moduleLineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Total',
                        data: chartTotalData,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Pending',
                        data: chartPendingData,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Completed',
                        data: chartCompletedData,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        fill: false,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });

        const ctxBar = document.getElementById('moduleBarChart').getContext('2d');
        let moduleBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Total',
                        data: chartTotalData,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pending',
                        data: chartPendingData,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Completed',
                        data: chartCompletedData,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    x: {
                        stacked: false,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        stacked: false
                    }
                }
            }
        });
    </script>
</body>
</html>