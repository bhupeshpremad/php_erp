// Fix for duplicate invoice/builty display in Job Card BOM table
// This script prevents same invoice/builty from showing on multiple rows

function preventDuplicateInvoiceDisplay() {
    $('#bomTableContainer table').each(function() {
        var table = $(this);
        var invoiceTracker = {};
        var builtyTracker = {};
        
        table.find('tbody tr').each(function(index) {
            var row = $(this);
            var invoiceInput = row.find('.invoiceNumberInput');
            var builtyInput = row.find('.builtyNumberInput');
            var invoiceImageContainer = row.find('td').eq(9); // Invoice Image column
            var builtyImageContainer = row.find('td').eq(11); // Builty Image column
            
            if (invoiceInput.length && invoiceInput.val().trim() !== '') {
                var invoiceNumber = invoiceInput.val().trim();
                
                if (invoiceTracker[invoiceNumber]) {
                    // This invoice already shown, clear it from this row
                    invoiceInput.val('');
                    invoiceImageContainer.find('.invoiceImageThumb').remove();
                    invoiceImageContainer.find('.existingInvoiceImage').val('');
                    
                    // Add a note that this is covered by another row
                    if (!invoiceInput.siblings('.invoice-note').length) {
                        invoiceInput.after('<small class="invoice-note text-muted">Covered by row ' + (invoiceTracker[invoiceNumber] + 1) + '</small>');
                    }
                } else {
                    invoiceTracker[invoiceNumber] = index;
                }
            }
            
            if (builtyInput.length && builtyInput.val().trim() !== '') {
                var builtyNumber = builtyInput.val().trim();
                
                if (builtyTracker[builtyNumber]) {
                    // This builty already shown, clear it from this row
                    builtyInput.val('');
                    builtyImageContainer.find('.builtyImageThumb').remove();
                    builtyImageContainer.find('.existingBuiltyImage').val('');
                    
                    // Add a note that this is covered by another row
                    if (!builtyInput.siblings('.builty-note').length) {
                        builtyInput.after('<small class="builty-note text-muted">Covered by row ' + (builtyTracker[builtyNumber] + 1) + '</small>');
                    }
                } else {
                    builtyTracker[builtyNumber] = index;
                }
            }
        });
    });
}

// Apply fix when BOM table is loaded
$(document).ready(function() {
    // Apply fix after table is rendered
    setTimeout(function() {
        preventDuplicateInvoiceDisplay();
    }, 1000);
    
    // Also apply when JCI changes
    $('#jci_number_search').on('change', function() {
        setTimeout(function() {
            preventDuplicateInvoiceDisplay();
        }, 2000);
    });
});