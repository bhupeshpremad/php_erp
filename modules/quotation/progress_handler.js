// Progress handler for large Excel uploads
function showProgressModal() {
    const modalHtml = `
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Processing Data</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3" id="progressText">Please wait while we process your data...</p>
                    <div class="progress mt-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    
    if (!$('#progressModal').length) {
        $('body').append(modalHtml);
    }
    $('#progressModal').modal('show');
}

function hideProgressModal() {
    $('#progressModal').modal('hide');
}

function updateProgressText(text) {
    $('#progressText').text(text);
}

// Export for use in other files
window.ProgressHandler = {
    show: showProgressModal,
    hide: hideProgressModal,
    updateText: updateProgressText
};