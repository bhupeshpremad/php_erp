$(document).ready(function() {
    $('.confirmLockBtn').click(function() {
        var quotationId = $(this).data('quotation-id');
        var modal = $('#lockQuotationModal_' + quotationId);
        var button = $(this);
        button.prop('disabled', true).text('Locking...');

        $.ajax({
            url: '/php_erp/modules/quotation/ajax_lock_quotation.php',
            type: 'POST',
            data: { quotation_id: quotationId },
            dataType: 'json',
            success: function(response) {
                console.log('Lock response:', response);
                if (response.success) {
                    modal.modal('hide');
                    // Reload page to reflect all changes
                    window.location.reload(true);
                } else {
                    alert('Failed to lock quotation: ' + response.message);
                }
            },
            error: function() {
                alert('Error occurred while locking quotation.');
            },
            complete: function() {
                button.prop('disabled', false).text('Lock');
            }
        });
    });
});
