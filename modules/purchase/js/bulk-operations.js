// Bulk operations for purchase module
function initBulkOperations() {
    // Bulk save selected rows
    $('#saveAllSelectedBtn').on('click', function(e) {
        e.preventDefault();
        var checkedRows = $('#bomTableContainer .rowCheckbox:checked');
        
        if (checkedRows.length === 0) {
            toastr.warning('Please select at least one row to save.');
            return;
        }
        
        var confirmMsg = 'Save ' + checkedRows.length + ' selected rows?';
        if (confirm(confirmMsg)) {
            $(this).prop('disabled', true).text('Saving...');
            saveItems(null); // Existing bulk save function
        }
    });
    
    // Bulk delete selected rows (superadmin only)
    if (window.isSuperAdmin) {
        var bulkDeleteBtn = $('<button type="button" class="btn btn-danger ml-2" id="bulkDeleteBtn">Delete Selected</button>');
        $('#saveAllSelectedBtn').after(bulkDeleteBtn);
        
        bulkDeleteBtn.on('click', function() {
            var checkedRows = $('#bomTableContainer .rowCheckbox:checked');
            var rowsWithData = checkedRows.filter(function() {
                var row = $(this).closest('tr');
                var supplier = row.find('.supplierNameInput').val().trim();
                return supplier !== '';
            });
            
            if (rowsWithData.length === 0) {
                toastr.warning('No saved rows selected for deletion.');
                return;
            }
            
            var confirmMsg = 'Delete ' + rowsWithData.length + ' saved rows? This action cannot be undone.';
            if (confirm(confirmMsg)) {
                bulkDeleteRows(rowsWithData);
            }
        });
    }
    
    // Select all functionality
    $('#bomTableContainer').on('change', '.selectAllRows', function() {
        var table = $(this).closest('table');
        var checked = $(this).is(':checked');
        table.find('.rowCheckbox:not(:disabled)').prop('checked', checked);
        updateBulkButtonStates();
    });
    
    // Update button states when individual checkboxes change
    $('#bomTableContainer').on('change', '.rowCheckbox', function() {
        updateBulkButtonStates();
    });
}

function bulkDeleteRows(rows) {
    var jciNumber = $('#jci_number').val();
    var deletePromises = [];
    
    rows.each(function() {
        var row = $(this).closest('tr');
        var supplier = row.find('.supplierNameInput').val().trim();
        var product = row.find('.productNameInput').val().trim();
        var jobCard = row.closest('table').find('thead th').first().text().replace('Job Card: ', '').trim();
        
        if (supplier && product) {
            var promise = $.ajax({
                url: 'ajax_delete_row_by_details.php',
                method: 'POST',
                data: {
                    supplier_name: supplier,
                    product_name: product,
                    job_card_number: jobCard,
                    jci_number: jciNumber
                },
                dataType: 'json'
            });
            deletePromises.push(promise);
        }
    });
    
    Promise.all(deletePromises).then(function(responses) {
        var successCount = responses.filter(r => r.success).length;
        var failCount = responses.length - successCount;
        
        if (successCount > 0) {
            toastr.success(successCount + ' rows deleted successfully!');
        }
        if (failCount > 0) {
            toastr.error(failCount + ' rows failed to delete.');
        }
        
        $('#jci_number_search').trigger('change'); // Reload table
    }).catch(function() {
        toastr.error('Bulk delete operation failed.');
    });
}

function updateBulkButtonStates() {
    var totalChecked = $('#bomTableContainer .rowCheckbox:checked').length;
    var totalWithData = $('#bomTableContainer .rowCheckbox:checked').filter(function() {
        var row = $(this).closest('tr');
        return row.find('.supplierNameInput').val().trim() !== '';
    }).length;
    
    $('#saveAllSelectedBtn').prop('disabled', totalChecked === 0);
    $('#bulkDeleteBtn').prop('disabled', totalWithData === 0);
    
    // Update button text with counts
    $('#saveAllSelectedBtn').text('Save Selected (' + totalChecked + ')');
    if ($('#bulkDeleteBtn').length) {
        $('#bulkDeleteBtn').text('Delete Selected (' + totalWithData + ')');
    }
}

// Initialize when document is ready
$(document).ready(function() {
    initBulkOperations();
});