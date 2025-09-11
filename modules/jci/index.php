<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
session_start();
include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} elseif ($user_type === 'accounts') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
}

global $conn;

$sql = "SELECT j.id, j.jci_number, j.created_by, j.jci_date, p.po_number, p.sell_order_number, b.bom_number
        FROM jci_main j
        LEFT JOIN po_main p ON j.po_id = p.id
        LEFT JOIN bom_main b ON j.bom_id = b.id
        ORDER BY j.id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$jci_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Job Card List</h6>
            <div class="d-flex align-items-center gap-3">
                <input type="text" id="jciSearchInput" class="form-control form-control-sm" placeholder="Search JCI..." style="width: 250px;">
                <a href="add.php" class="btn btn-primary btn-sm">Add New Job Card</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive dataTables_wrapper_custom">
                <table class="table table-bordered table-striped" id="jciTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>JCI Number</th>
                            <th>Sale Order Number</th>
                            <th>PO Number</th>
                            <th>BOM Number</th>
                            <th>Created By</th>
                            <!-- <th>Job Card Date</th> -->
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jci_list as $jci): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($jci['id']); ?></td>
                            <td><?php echo htmlspecialchars($jci['jci_number']); ?></td>
                            <td><?php echo htmlspecialchars($jci['sell_order_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($jci['po_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($jci['bom_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($jci['created_by']); ?></td>
                            <!-- <td><?php echo htmlspecialchars($jci['jci_date']); ?></td> -->
                            <td>
                                <button class="btn btn-info btn-sm view-items-btn" data-jci-id="<?php echo $jci['id']; ?>">View Details</button>
                            </td>
                            <td>
                                <a href="add.php?id=<?php echo $jci['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemDetailsModalLabel">Job Card Item Details - JCI Number: <span id="modalJciNumber"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemDetailsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Job Card Number</th>
                                    <th>Product Name</th>
                                    <th>Product Image</th>
                                    <th>Item Code</th>
                                    <th>Assigned Qty</th>
                                    <th>Labour Cost</th>
                                    <th>Total Amount</th>
                                    <th>Delivery Date</th>
                                    <th>Job Card Date</th>
                                    <th>Job Card Type</th>
                                    <th>Contracture Name</th>
                                    <th>Print</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Product Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Product Image" class="img-fluid" style="max-height: 500px;">
                </div>
            </div>
        </div>
    </div>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<!-- JS + DataTable -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    var jciTable = $('#jciTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: true,
        dom: 'rt<"bottom"p>'
    });
    
    // Custom search functionality
    $('#jciSearchInput').on('keyup', function() {
        var searchValue = this.value;
        jciTable.search(searchValue).draw();
    });
    
    // Clear search when input is empty
    $('#jciSearchInput').on('input', function() {
        if (this.value === '') {
            jciTable.search('').draw();
        }
    });

    $(document).on('click', '.view-items-btn', function() {
        const jciId = $(this).attr('data-jci-id');
        const jciNumber = $(this).closest('tr').find('td:nth-child(2)').text();
        $('#modalJciNumber').text(jciNumber);
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/jci/ajax_fetch_jci_items.php',
            method: 'POST',
            data: { jci_id: jciId },
            dataType: 'json'
        }).done(function(data) {
            const tbody = $('#itemDetailsTable tbody');
            tbody.empty();
            if (data.success && data.items.length > 0) {
                data.items.forEach((item, idx) => {
                    const productImage = item.product_image ? 
                        `<img src="${item.product_image}" alt="Product" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" onclick="showImageModal('${item.product_image}', '${item.product_name}')" />` : 
                        '<span class="text-muted">No Image</span>';
                    
                    const row = `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${item.job_card_number || 'N/A'}</td>
                            <td>${item.product_name || 'N/A'}</td>
                            <td>${productImage}</td>
                            <td>${item.item_code || 'N/A'}</td>
                            <td>${item.quantity || '0'}</td>
                            <td>${item.labour_cost || '0.00'}</td>
                            <td>${item.total_amount || '0.00'}</td>
                            <td>${item.delivery_date || 'N/A'}</td>
                            <td>${item.job_card_date || 'N/A'}</td>
                            <td>${item.job_card_type || 'N/A'}</td>
                            <td>${item.contracture_name || 'N/A'}</td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>modules/jci/download_job_card_pdf.php?jci_number=${jciNumber}" class="btn btn-sm btn-success" target="_blank">Download PDF</a>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.html(`<tr><td colspan="13" class="text-center">${data.message || 'No details found.'}</td></tr>`);
            }
            $('#itemDetailsModal').modal('show');
        }).fail(function() {
            $('#itemDetailsTable tbody').html('<tr><td colspan="13" class="text-center text-danger">Error loading data</td></tr>');
            $('#itemDetailsModal').modal('show');
        });
    });
});

// Show image in modal
function showImageModal(imageSrc, productName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModalLabel').textContent = productName || 'Product Image';
    $('#imageModal').modal('show');
}

// Print job card with complete details
function printJobCard(jobCardNumber, jciNumber) {
    // Find the job card data from the current table
    const tableRows = document.querySelectorAll('#itemDetailsTable tbody tr');
    let jobCardData = null;
    
    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 1 && cells[1].textContent.trim() === jobCardNumber) {
            jobCardData = {
                jobCardNumber: cells[1].textContent.trim(),
                productName: cells[2].textContent.trim(),
                productImage: cells[3].querySelector('img') ? cells[3].querySelector('img').src : null,
                itemCode: cells[4].textContent.trim(),
                assignedQty: cells[5].textContent.trim(),
                labourCost: cells[6].textContent.trim(),
                totalAmount: cells[7].textContent.trim(),
                deliveryDate: cells[8].textContent.trim(),
                jobCardDate: cells[9].textContent.trim(),
                jobCardType: cells[10].textContent.trim(),
                contractureName: cells[11].textContent.trim()
            };
        }
    });
    
    if (!jobCardData) {
        alert('Job card data not found!');
        return;
    }
    
    const productImageHtml = jobCardData.productImage ? 
        `<div style="text-align: center; margin: 20px 0;">
            <img src="${jobCardData.productImage}" alt="Product Image" style="max-width: 200px; max-height: 200px; border: 1px solid #ccc;" />
        </div>` : 
        '<div style="text-align: center; margin: 20px 0; padding: 20px; border: 1px solid #ccc;">No Product Image</div>';
    
    const printContent = `
        <div style="padding: 20px; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">
            <h2 style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px;">Job Card Details</h2>
            
            <div style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold; width: 30%;">JCI Number:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jciNumber}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Job Card Number:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.jobCardNumber}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Product Name:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.productName}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Item Code:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.itemCode}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Assigned Quantity:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.assignedQty}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Labour Cost:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.labourCost}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Total Amount:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.totalAmount}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Delivery Date:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.deliveryDate}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Job Card Date:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.jobCardDate}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Job Card Type:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.jobCardType}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Contracture Name:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${jobCardData.contractureName}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ccc; background: #f5f5f5; font-weight: bold;">Print Date:</td>
                        <td style="padding: 8px; border: 1px solid #ccc;">${new Date().toLocaleDateString()}</td>
                    </tr>
                </table>
            </div>
            
            <div style="margin: 30px 0;">
                <h3 style="margin-bottom: 15px;">Product Image:</h3>
                ${productImageHtml}
            </div>
            
            <div style="margin-top: 40px;">
                <h3 style="margin-bottom: 15px;">Instructions:</h3>
                <div style="border: 1px solid #ccc; padding: 20px; min-height: 100px; background: #fafafa;">
                    <!-- Job card specific instructions -->
                    <p>Please follow the specifications mentioned above for this job card.</p>
                </div>
            </div>
        </div>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Job Card - ${jobCardData.jobCardNumber}</title>
                <style>
                    body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
                    @media print {
                        body { margin: 0; padding: 10px; }
                        .no-print { display: none; }
                    }
                    table { border-collapse: collapse; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>