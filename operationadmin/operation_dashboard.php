<?php
include_once __DIR__ . '/../config/config.php';
include_once ROOT_DIR_PATH . 'core/NotificationSystem.php'; // Ensure NotificationSystem is available
session_start();
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

// Check if user is logged in as operationadmin
if ($user_type !== 'operationadmin' || ($_SESSION['department'] ?? '') !== 'operation') {
    header('Location: ../index.php');
    exit;
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';
include_once ROOT_DIR_PATH . 'operationadmin/sidebar.php';

global $conn;

// Removed redundant has_module_access function as the data fetching below is conditional anyway
// function has_module_access($module) {
//     global $user_type;
//     $operation_modules = [
//         'bom', 'po', 'so', 'jci'
//     ];
//     if ($user_type === 'operation' && in_array($module, $operation_modules)) {
//         return true;
//     }
//     return false;
// }

$module_display_names = [
    'bom' => 'Bill Of Material',
    'po' => 'Purchase Order',
    'so' => 'Sale Order',
    'jci' => 'Job Card',
];

$all_modules = array_keys($module_display_names);

$module_data = [];
$module_status = [];

foreach ($all_modules as $mod) {
    $counts = ['total' => 0, 'pending' => 0, 'completed' => 0];

    switch ($mod) {
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
                FROM po_main");
            $stmt->execute();
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
        case 'so':
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sell_order"); // Corrected table name
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
        default:
            $counts = ['total' => 0, 'pending' => 0, 'completed' => 0];
    }
    $module_data[$mod] = $counts;
    $module_status[$mod] = $counts;
}

$chartLabels = array_values($module_display_names);
$chartTotalData = array_column($module_data, 'total');
$chartPendingData = array_column($module_data, 'pending');
$chartCompletedData = array_column($module_data, 'completed');

?>

<body id="page-top">

    <div class="container-fluid">

        <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Operation Dashboard</h1>
        </div>

        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Module Overview (Line Chart)</h6>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 350px;">
                            <canvas id="moduleLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Module Overview (Pie Chart)</h6>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 350px;">
                            <canvas id="modulePieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($module_data as $module => $counts): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            <?php echo $module_display_names[$module] ?? ucfirst(str_replace('_', ' ', $module)); ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr><td>Total</td><td class="text-end"><?php echo $counts['total']; ?></td></tr>
                                    <tr><td>Pending</td><td class="text-end"><?php echo $counts['pending']; ?></td></tr>
                                    <tr><td>Completed</td><td class="text-end"><?php echo $counts['completed']; ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <?php 
                                $module_urls = [
                                    'bom' => BASE_URL . 'modules/bom/index.php',
                                    'po' => BASE_URL . 'modules/po/index.php',
                                    'so' => BASE_URL . 'modules/so/index.php',
                                    'jci' => BASE_URL . 'modules/jci/index.php',
                                ];
                            if (isset($module_urls[$module])): ?>
                                <a href="<?php echo $module_urls[$module]; ?>" class="btn btn-sm btn-info">View Details</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script>
        const moduleLabels = <?php echo json_encode($chartLabels); ?>;
        const totalData = <?php echo json_encode($chartTotalData); ?>;
        const pendingData = <?php echo json_encode($chartPendingData); ?>;
        const completedData = <?php echo json_encode($chartCompletedData); ?>;

        const ctxLine = document.getElementById('moduleLineChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: moduleLabels,
                datasets: [
                    {
                        label: 'Total',
                        data: totalData,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Pending',
                        data: pendingData,
                        borderColor: 'rgba(255, 193, 7, 1)',
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Completed',
                        data: completedData,
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
                    y: { beginAtZero: true },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        const ctxPie = document.getElementById('modulePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: moduleLabels,
                datasets: [{
                    data: totalData,
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(220, 53, 69, 0.7)'
                    ],
                    borderColor: [
                        'rgba(78, 115, 223, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: `${label}: ${data.datasets[0].data[i]}`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    strokeStyle: data.datasets[0].borderColor[i],
                                    lineWidth: 1,
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>