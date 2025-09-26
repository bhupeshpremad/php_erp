<?php
// Multi-Supplier Allocation Widget
// Include this in the purchase add.php page to show allocation status

if (!isset($jci_number) || empty($jci_number)) {
    return;
}
?>

<div class="card mt-3" id="supplierAllocationWidget" style="display: none;">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users"></i> Multi-Supplier Allocation Status
            <button type="button" class="btn btn-sm btn-outline-primary float-right" id="refreshAllocationBtn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </h6>
    </div>
    <div class="card-body">
        <div id="allocationSummary">
            <div class="text-center">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Loading allocation data...
            </div>
        </div>
        
        <!-- Allocation Legend -->
        <div class="mt-3">
            <small class="text-muted">
                <span class="badge badge-success">Fully Allocated</span>
                <span class="badge badge-warning">Partially Allocated</span>
                <span class="badge badge-secondary">Not Allocated</span>
                <span class="badge badge-danger">Over Allocated</span>
            </small>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load allocation data when JCI is selected
    $('#jci_number_search').on('change', function() {
        var jciNumber = $(this).val();
        if (jciNumber) {
            loadSupplierAllocation(jciNumber);
            $('#supplierAllocationWidget').show();
        } else {
            $('#supplierAllocationWidget').hide();
        }
    });
    
    // Refresh button
    $('#refreshAllocationBtn').on('click', function() {
        var jciNumber = $('#jci_number_search').val();
        if (jciNumber) {
            loadSupplierAllocation(jciNumber);
        }
    });
    
    function loadSupplierAllocation(jciNumber) {
        $('#allocationSummary').html('<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading allocation data...</div>');
        
        $.ajax({
            url: 'ajax_get_supplier_allocation.php',
            method: 'POST',
            data: { jci_number: jciNumber },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderAllocationSummary(response);
                } else {
                    $('#allocationSummary').html('<div class="alert alert-danger">Error: ' + (response.error || 'Unknown error') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#allocationSummary').html('<div class="alert alert-danger">AJAX Error: ' + error + '</div>');
            }
        });
    }
    
    function renderAllocationSummary(data) {
        var html = '';
        
        // Summary stats
        if (data.summary_stats) {
            html += '<div class="row mb-3">';
            html += '<div class="col-md-3"><div class="text-center"><h6 class="text-success">' + data.summary_stats.fully_allocated + '</h6><small>Fully Allocated</small></div></div>';
            html += '<div class="col-md-3"><div class="text-center"><h6 class="text-warning">' + data.summary_stats.partially_allocated + '</h6><small>Partially Allocated</small></div></div>';
            html += '<div class="col-md-3"><div class="text-center"><h6 class="text-secondary">' + data.summary_stats.not_allocated + '</h6><small>Not Allocated</small></div></div>';
            html += '<div class="col-md-3"><div class="text-center"><h6 class="text-danger">' + data.summary_stats.over_allocated + '</h6><small>Over Allocated</small></div></div>';
            html += '</div>';
        }
        
        // Detailed allocation
        if (data.allocation_summary && data.allocation_summary.length > 0) {
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered">';
            html += '<thead class="thead-light">';
            html += '<tr><th>Product</th><th>BOM Qty</th><th>Assigned</th><th>Remaining</th><th>Suppliers</th><th>Status</th></tr>';
            html += '</thead><tbody>';
            
            data.allocation_summary.forEach(function(item) {
                var statusClass = '';
                var statusText = '';
                
                switch(item.allocation_status) {
                    case 'fully_allocated':
                        statusClass = 'badge-success';
                        statusText = 'Fully Allocated';
                        break;
                    case 'partially_allocated':
                        statusClass = 'badge-warning';
                        statusText = 'Partially Allocated';
                        break;
                    case 'over_allocated':
                        statusClass = 'badge-danger';
                        statusText = 'Over Allocated';
                        break;
                    default:
                        statusClass = 'badge-secondary';
                        statusText = 'Not Allocated';
                }
                
                var suppliersText = item.suppliers.length > 0 ? 
                    item.suppliers.map(function(s) { return s.name + ' (' + s.quantity + ')'; }).join(', ') : 
                    'None';
                
                html += '<tr>';
                html += '<td><strong>' + item.product_name + '</strong><br><small class="text-muted">' + item.product_type + '</small></td>';
                html += '<td>' + item.bom_quantity + '</td>';
                html += '<td>' + item.total_assigned + '</td>';
                html += '<td class="' + (item.remaining_quantity < 0 ? 'text-danger' : (item.remaining_quantity === 0 ? 'text-success' : 'text-warning')) + '">' + item.remaining_quantity.toFixed(3) + '</td>';
                html += '<td><small>' + suppliersText + '</small></td>';
                html += '<td><span class="badge ' + statusClass + '">' + statusText + '</span></td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            html += '</div>';
        } else {
            html += '<div class="alert alert-info">No allocation data found for this JCI.</div>';
        }
        
        $('#allocationSummary').html(html);
    }
});
</script>