$(document).ready(function() {
    // Example Add AJAX call
    $('#addForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var table = 'your_table_name'; // Replace with your table name
        var columns = formData.map(item => item.name);
        var values = formData.map(item => item.value);

        $.ajax({
            url: 'include/ajax/add.php',
            type: 'POST',
            data: {
                table: table,
                columns: columns,
                values: values
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addForm')[0].reset();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('AJAX request failed');
            }
        });
    });

    // Similar AJAX calls can be created for update, delete, and view operations
});
