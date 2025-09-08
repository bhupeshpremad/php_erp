$(document).ready(function () {
    // Initialize the purchase form
    initPurchaseForm();

    function initPurchaseForm() {
        // Bind events for dynamic editable states
        bindEditableEvents();
    }

    function bindEditableEvents() {
        // Check invoice upload status for each BOM item
        $('#jci_number_search').on('change', function () {
            var selectedJciNumber = $(this).val();
            if (selectedJciNumber) {
                checkInvoiceStatus(selectedJciNumber);
            } else {
                $('#invoice_status_msg').text('').hide();
            }
        });
    }

    function checkInvoiceStatus(jci_number) {
        $.ajax({
            url: 'ajax_check_invoice_status.php',
            type: 'POST',
            dataType: 'json', // Expect JSON from PHP
            data: { jci_number: jci_number },
            beforeSend: function () {
                $('#invoice_status_msg').text('Checking...').show();
            },
            success: function (response) {
                if (response.success) {
                    $('#invoice_status_msg')
                        .text('Invoice status: ' + response.status)
                        .css('color', response.status === 'Uploaded' ? 'green' : 'red')
                        .show();
                } else {
                    $('#invoice_status_msg')
                        .text('Error: ' + (response.message || 'Unknown error'))
                        .css('color', 'red')
                        .show();
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#invoice_status_msg')
                    .text('Failed to check invoice status. Please try again.')
                    .css('color', 'red')
                    .show();
            }
        });
    }
});
