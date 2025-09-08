<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} else {
    // Default or guest sidebar or no sidebar
    // include_once ROOT_DIR_PATH . 'include/inc/sidebar.php';
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary" id="formTitle">Add Lead</h6>
        </div>
        <div class="card-body">
            <form id="leadForm" autocomplete="off">
                <input type="hidden" name="lead_id" id="lead_id" value="">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="leadNumber" class="form-label">Lead Number</label>
                        <input type="text" class="form-control" id="leadNumber" name="lead_number" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="entryDate" class="form-label">Entry Date</label>
                        <input type="date" class="form-control" id="entryDate" name="entry_date" required>
                    </div>
                    <div class="col-md-4">
                        <label for="companyName" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="companyName" name="company_name" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="contactName" class="form-label">Contact Name</label>
                        <input type="text" class="form-control" id="contactName" name="contact_person" required>
                    </div>
                    <div class="col-md-4">
                        <label for="contactPhone" class="form-label">Contact Phone</label>
                        <input type="tel" class="form-control" id="contactPhone" name="phone" required>
                    </div>
                    <div class="col-md-4">
                        <label for="contactEmail" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="contactEmail" name="email" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="country" class="form-label">Country</label>
                        <select id="country" name="country" class="form-control" required></select>
                    </div>
                    <div class="col-md-4">
                        <label for="state" class="form-label">State</label>
                        <select id="state" name="state" class="form-control" required></select>
                    </div>
                    <div class="col-md-4">
                        <label for="city" class="form-label">City</label>
                        <select id="city" name="city" class="form-control"></select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="leadSource" class="form-label">Lead Source</label>
                        <input type="text" class="form-control" id="leadSource" name="lead_source" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>
</div>



<!-- Country-State-City Library -->
<script src="https://cdn.jsdelivr.net/npm/country-state-city@3.0.1/dist/country-state-city.min.js"></script>
<!-- jQuery (First) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Popper.js (for Bootstrap 4 compatibility) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<!-- Bootstrap 4 JS (for modal) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
const countrySelect = document.getElementById('country');
const stateSelect = document.getElementById('state');
const citySelect = document.getElementById('city');

// Load countries
fetch('https://countriesnow.space/api/v0.1/countries/positions')
    .then(res => res.json())
    .then(data => {
        countrySelect.innerHTML = '<option value="">Select Country</option>';
        data.data.forEach(country => {
            countrySelect.innerHTML += `<option value="${country.name}">${country.name}</option>`;
        });
    });

// When country changes, fetch states
countrySelect.addEventListener('change', function () {
    const selectedCountry = this.value;
    stateSelect.innerHTML = '<option value="">Loading...</option>';
    citySelect.innerHTML = '<option value="">Select City</option>';

    fetch('https://countriesnow.space/api/v0.1/countries/states', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ country: selectedCountry })
    })
    .then(res => res.json())
    .then(data => {
        stateSelect.innerHTML = '<option value="">Select State</option>';
        if (data.data.states && data.data.states.length > 0) {
            data.data.states.forEach(state => {
                stateSelect.innerHTML += `<option value="${state.name}">${state.name}</option>`;
            });
        } else {
            stateSelect.innerHTML = '<option value="">No states available</option>';
        }
    });
});

// When state changes, fetch cities
stateSelect.addEventListener('change', function () {
    const selectedCountry = countrySelect.value;
    const selectedState = this.value;
    citySelect.innerHTML = '<option value="">Loading...</option>';

    fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ country: selectedCountry, state: selectedState })
    })
    .then(res => res.json())
    .then(data => {
        citySelect.innerHTML = '<option value="">Select City</option>';
        if (data.data && data.data.length > 0) {
            data.data.forEach(city => {
                citySelect.innerHTML += `<option value="${city}">${city}</option>`;
            });
        } else {
            citySelect.innerHTML = '<option value="">No cities available</option>';
        } 
    });
});

// Utility: Get URL parameter
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// On page load: fetch next lead number or lead data for edit
$(document).ready(function() {
    const leadId = getUrlParameter('lead_id');
    if (leadId) {
        // Edit mode: fetch lead data
    $.ajax({
        url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
        type: 'POST',
        data: { action: 'get_lead', lead_id: leadId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.lead) {
                $('#formTitle').text('Edit Lead');
                $('#lead_id').val(response.lead.id);
                $('#leadNumber').val(response.lead.lead_number);
                $('#entryDate').val(response.lead.entry_date);
                $('#companyName').val(response.lead.company_name);
                $('#contactName').val(response.lead.contact_name);
                $('#contactPhone').val(response.lead.contact_phone);
                $('#contactEmail').val(response.lead.contact_email);
                $('#country').val(response.lead.country).trigger('change');
                setTimeout(function() {
                    $('#state').val(response.lead.state).trigger('change');
                    setTimeout(function() {
                        $('#city').val(response.lead.city);
                    }, 800);
                }, 800);
                $('#leadSource').val(response.lead.lead_source);
                $('#submitBtn').text('Update');
            } else {
                toastr.error(response.message || 'Failed to fetch lead data.');
            }
        },
        error: function() {
            toastr.error('AJAX error while fetching lead data.');
        }
    });
    } else {
        // Add mode: fetch next lead number
    $.ajax({
        url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
        type: 'POST',
        data: { action: 'get_next_lead_number' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#leadNumber').val(response.lead_number);
            } else {
                toastr.error(response.message || 'Failed to fetch lead number.');
            }
        },
        error: function() {
            toastr.error('AJAX error while fetching lead number.');
        }
    });
    }
});

// Form submit handler
$('#leadForm').on('submit', function(e) {
    e.preventDefault();
    const leadId = $('#lead_id').val();
    const action = leadId ? 'update' : 'create';
    const formData = {
        action: action,
        lead_id: leadId,
        lead_number: $('#leadNumber').val(),
        entry_date: $('#entryDate').val(),
        company_name: $('#companyName').val(),
        contact_person: $('#contactName').val(),
        phone: $('#contactPhone').val(),
        email: $('#contactEmail').val(),
        country: $('#country').val(),
        state: $('#state').val(),
        city: $('#city').val(),
        lead_source: $('#leadSource').val()
    };

    $.ajax({
        url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                if (!leadId) {
                    $('#leadForm')[0].reset();
                    // Fetch new lead number for next entry
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
                        type: 'POST',
                        data: { action: 'get_next_lead_number' },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                $('#leadNumber').val(res.lead_number);
                            }
                        }
                    });
                }
            } else {
                toastr.error(response.message || 'Failed to save lead.');
            }
        },
        error: function() {
            toastr.error('AJAX error while saving lead.');
        }
    });
});
</script>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
