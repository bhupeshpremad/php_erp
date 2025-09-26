/**
 * Multi-Supplier Purchase Management
 * Handles adding multiple suppliers for the same product
 */

class MultiSupplierManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Use container-based event delegation for better performance
        $('#bomTableContainer').on('click', '.add-supplier-btn', this.addSupplierRow.bind(this));
        $('#bomTableContainer').on('click', '.remove-supplier-btn', this.removeSupplierRow.bind(this));
        $('#bomTableContainer').on('input', '.assignQuantityInput', this.onQuantityChange.bind(this));
        $('#bomTableContainer').on('input', '.supplierNameInput', this.onSupplierNameChange.bind(this));
        $('#bomTableContainer').on('blur', '.assignQuantityInput', this.onQuantityChange.bind(this));
        $('#bomTableContainer').on('blur', '.supplierNameInput', this.onSupplierNameChange.bind(this));
    }

    addSupplierRow(event) {
        event.preventDefault();
        const $originalRow = $(event.target).closest('tr');
        
        // Validate original row has supplier name
        const originalSupplier = $originalRow.find('.supplierNameInput').val().trim();
        if (!originalSupplier) {
            toastr.error('Please enter supplier name in the current row first.');
            $originalRow.find('.supplierNameInput').focus();
            return;
        }
        
        const $newRow = this.cloneRowForNewSupplier($originalRow);
        
        // Insert new row after the original
        $originalRow.after($newRow);
        
        // Update row numbers
        this.updateRowNumbers($originalRow.closest('table'));
        
        // Show/hide buttons appropriately
        this.updateSupplierButtons($originalRow.closest('table'));
        
        // Update remaining quantity display
        this.updateRemainingQuantity();
        
        // Focus on supplier name input of new row
        $newRow.find('.supplierNameInput').focus();
        
        toastr.success('New supplier row added. Enter supplier details.');
    }

    removeSupplierRow(event) {
        event.preventDefault();
        const $row = $(event.target).closest('tr');
        const $table = $row.closest('table');
        
        // Only allow removing dynamically added rows (not original BOM rows)
        if (!$row.hasClass('multi-supplier-row')) {
            toastr.warning('Cannot remove original BOM rows. Only additional supplier rows can be removed.');
            return;
        }
        
        if (window.confirm('Are you sure you want to remove this supplier row?')) {
            $row.remove();
            this.updateRowNumbers($table);
            this.updateSupplierButtons($table);
            this.updateRemainingQuantity();
            toastr.success('Supplier row removed.');
        }
    }

    cloneRowForNewSupplier($originalRow) {
        const $newRow = $originalRow.clone();
        
        // Clear supplier-specific data
        $newRow.find('.supplierNameInput').val('');
        $newRow.find('.assignQuantityInput').val('0');
        $newRow.find('.invoiceNumberInput').val('');
        $newRow.find('.builtyNumberInput').val('');
        $newRow.find('.invoiceImageInput').val('');
        $newRow.find('.builtyImageInput').val('');
        $newRow.find('.existing-item-id').val('');
        $newRow.find('.existingInvoiceImage').val('');
        $newRow.find('.existingBuiltyImage').val('');
        
        // Remove any existing images
        $newRow.find('.invoiceImageThumb, .builtyImageThumb').remove();

        // Ensure file inputs are visible for the new row (original may have been hidden due to existing images)
        $newRow.find('.invoiceImageInput').removeAttr('style').show();
        $newRow.find('.builtyImageInput').removeAttr('style').show();
        
        // Uncheck the checkbox
        $newRow.find('.rowCheckbox').prop('checked', false);
        
        // Remove success styling
        $newRow.removeClass('table-success');
        
        // Update status to pending
        $newRow.find('.badge').removeClass('badge-success').addClass('badge-warning').text('Pending');
        
        // Re-enable all inputs
        $newRow.find('input, button').prop('disabled', false).prop('readonly', false);
        // Update button text
        $newRow.find('.saveRowBtn').text('Save');
        
        // Add supplier indicator class
        $newRow.addClass('multi-supplier-row');

        // Assign a new unique data-bom-index so file inputs map uniquely on backend
        const $table = $originalRow.closest('table');
        let maxIndex = -1;
        $table.find('tbody tr').each(function() {
            const idx = $(this).data('bom-index');
            if (typeof idx !== 'undefined' && idx !== null) {
                maxIndex = Math.max(maxIndex, parseInt(idx));
            }
        });
        const newIndex = (isNaN(maxIndex) ? 0 : maxIndex + 1);
        $newRow.attr('data-bom-index', newIndex);

        // Update action buttons: include Save + Remove for cloned rows
        const $actionButtons = $newRow.find('.action-buttons');
        if ($actionButtons.length) {
            $actionButtons.html(
                '<button type="button" class="btn btn-primary btn-sm saveRowBtn" title="Save"><i class="fas fa-save"></i></button> '
                + '<button type="button" class="btn btn-warning btn-sm remove-supplier-btn" title="Remove this supplier"><i class="fas fa-user-minus"></i></button>'
            );
        } else {
            // Fallback: append a new container if missing
            const $lastTd = $newRow.find('td').last();
            const $group = $('<div class="action-buttons d-flex align-items-center gap-1"></div>');
            $group.append('<button type="button" class="btn btn-primary btn-sm saveRowBtn" title="Save"><i class="fas fa-save"></i></button>');
            $group.append('<button type="button" class="btn btn-warning btn-sm remove-supplier-btn" title="Remove this supplier"><i class="fas fa-user-minus"></i> Remove</button>');
            $lastTd.append($group);
        }

        return $newRow;
    }

    updateRowNumbers($table) {
        let serialNumber = 1;
        $table.find('tbody tr').each(function() {
            $(this).find('td').eq(1).text(serialNumber++);
        });
    }
    updateSupplierButtons($table) {
        // Group rows by product name and calculate allocation
        const productGroups = {};
        
        $table.find('tbody tr').each(function() {
            const $row = $(this);
            const productName = $row.find('.productNameInput').val();
            const bomQuantity = parseFloat($row.find('.bomQuantityInput').val()) || 0;
            const assignedQuantity = parseFloat($row.find('.assignQuantityInput').val()) || 0;
            const supplierName = $row.find('.supplierNameInput').val().trim();
            
            if (!productGroups[productName]) {
                productGroups[productName] = {
                    rows: [],
                    bomQuantity: bomQuantity,
                    totalAssigned: 0
                };
            }
            productGroups[productName].rows.push($row);
            productGroups[productName].totalAssigned += assignedQuantity;
        });
        
        // Update buttons for each product group
        Object.keys(productGroups).forEach(productName => {
            const group = productGroups[productName];
            const remaining = group.bomQuantity - group.totalAssigned;
            
            group.rows.forEach(($row, index) => {
                // Remove existing supplier buttons
                $row.find('.add-supplier-btn, .remove-supplier-btn').remove();
                
                const $actionTd = $row.find('td').last();
                const isOriginalBomRow = !$row.hasClass('multi-supplier-row');
                const supplierName = $row.find('.supplierNameInput').val().trim();
                const assignedQty = parseFloat($row.find('.assignQuantityInput').val()) || 0;
                
                // Show "Add Supplier" button only if:
                // 1. This is the first row of the product
                // 2. Current row has supplier name and quantity > 0
                // 3. There's remaining quantity to assign
                if (index === 0 && isOriginalBomRow && supplierName && assignedQty > 0 && remaining > 0.001) {
                    let $group = $actionTd.find('.action-buttons');
                    if ($group.length === 0) {
                        $group = $('<div class="action-buttons d-flex align-items-center"></div>');
                        $actionTd.append($group);
                    }
                    $group.append(' <button type="button" class="btn btn-info btn-sm add-supplier-btn" title="Add another supplier"><i class="fas fa-user-plus"></i></button>');
                }
                
                // Add "Remove" button ONLY to dynamically added supplier rows
                if (!isOriginalBomRow) {
                    let $group = $actionTd.find('.action-buttons');
                    if ($group.length === 0) {
                        $group = $('<div class="action-buttons d-flex align-items-center"></div>');
                        $actionTd.append($group);
                    }
                    $group.append(' <button type="button" class="btn btn-warning btn-sm remove-supplier-btn" title="Remove this supplier"><i class="fas fa-user-minus"></i></button>');
                }
            });
        });
    }

    updateRemainingQuantity() {
        // Previously added a Remaining: X display; per request, suppress the UI display
        // Keep function as a no-op to avoid breaking calls
        return;
    }

    // Method to get supplier allocation summary
    getSupplierAllocation() {
        const allocation = {};
        
        $('#bomTableContainer table').each(function() {
            const $table = $(this);
            const jobCard = $table.find('thead th').first().text().replace('Job Card: ', '').trim();
            
            $table.find('tbody tr').each(function() {
                const $row = $(this);
                const productName = $row.find('.productNameInput').val();
                const supplierName = $row.find('.supplierNameInput').val();
                const assignedQuantity = parseFloat($row.find('.assignQuantityInput').val()) || 0;
                const bomQuantity = parseFloat($row.find('.bomQuantityInput').val()) || 0;
                
                if (supplierName && assignedQuantity > 0) {
                    const key = `${jobCard}_${productName}`;
                    
                    if (!allocation[key]) {
                        allocation[key] = {
                            jobCard: jobCard,
                            productName: productName,
                            bomQuantity: bomQuantity,
                            suppliers: [],
                            totalAssigned: 0
                        };
                    }
                    
                    allocation[key].suppliers.push({
                        name: supplierName,
                        quantity: assignedQuantity
                    });
                    
                    allocation[key].totalAssigned += assignedQuantity;
                }
            });
        });
        
        return allocation;
    }

    // Method to validate supplier allocation
    validateAllocation() {
        const allocation = this.getSupplierAllocation();
        const errors = [];
        
        Object.keys(allocation).forEach(key => {
            const item = allocation[key];
            
            if (item.totalAssigned > item.bomQuantity) {
                errors.push(`${item.productName} in ${item.jobCard}: Total assigned (${item.totalAssigned}) exceeds BOM quantity (${item.bomQuantity})`);
            }
        });
        
        return {
            isValid: errors.length === 0,
            errors: errors,
            allocation: allocation
        };
    }
    
    onQuantityChange(event) {
        const $input = $(event.target);
        const $row = $input.closest('tr');
        const $table = $row.closest('table');
        
        // Validate quantity doesn't exceed BOM limit across all suppliers
        this.validateProductQuantity($row);
        
        this.updateRemainingQuantity();
        this.updateSupplierButtons($table);
    }
    
    onSupplierNameChange(event) {
        const $input = $(event.target);
        const $table = $input.closest('table');
        this.updateSupplierButtons($table);
    }
    
    validateProductQuantity($row) {
        const productName = $row.find('.productNameInput').val();
        const bomQuantity = parseFloat($row.find('.bomQuantityInput').val()) || 0;
        const currentQuantity = parseFloat($row.find('.assignQuantityInput').val()) || 0;
        
        // Find all rows with same product name in same table
        const $table = $row.closest('table');
        let totalAssigned = 0;
        
        $table.find('tbody tr').each(function() {
            const $otherRow = $(this);
            const otherProductName = $otherRow.find('.productNameInput').val();
            
            if (otherProductName === productName) {
                const qty = parseFloat($otherRow.find('.assignQuantityInput').val()) || 0;
                totalAssigned += qty;
            }
        });
        
        // Check if total exceeds BOM quantity
        if (totalAssigned > bomQuantity + 0.001) {
            toastr.error(`Total assigned quantity (${totalAssigned}) exceeds BOM quantity (${bomQuantity}) for ${productName}`);
            
            // Reset current input to make total valid
            const maxAllowed = bomQuantity - (totalAssigned - currentQuantity);
            $row.find('.assignQuantityInput').val(Math.max(0, maxAllowed));
            
            return false;
        }
        
        return true;
    }
    
    onSupplierNameChange(event) {
        this.updateSupplierButtons($(event.target).closest('table'));
    }
}

// Initialize multi-supplier manager
$(document).ready(function() {
    window.multiSupplierManager = new MultiSupplierManager();
    
    // Add validation before save
    const originalSaveItems = window.saveItems;
    if (originalSaveItems) {
        window.saveItems = function(rowToSave) {
            // Validate allocation before saving
            const validation = window.multiSupplierManager.validateAllocation();
            
            if (!validation.isValid) {
                toastr.error('Allocation errors found:\n' + validation.errors.join('\n'));
                return;
            }
            
            // Call original save function
            return originalSaveItems.call(this, rowToSave);
        };
    }
});