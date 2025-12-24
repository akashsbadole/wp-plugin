<?php
/**
 * Admin template for listing and managing all patients.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/templates
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Ensure the HMS_Patient class is available
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}

$patient_obj = new HMS_Patient();

// Get filter values from URL
$search_filter = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$date_from_filter = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to_filter = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Patients Management', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_patient_registration')); ?>" class="page-title-action"><?php esc_html_e('Add New Patient', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">
    
    <?php
    // Display success message if redirected from patient registration
    if (isset($_GET['success'])) {
        $success_type = sanitize_text_field($_GET['success']);
        $patient_id = isset($_GET['patient_id']) ? sanitize_text_field($_GET['patient_id']) : '';
        
        switch ($success_type) {
            case 'patient_registered':
                $message = $patient_id ? 
                    sprintf(__('Patient registered successfully! Patient ID: %s', 'hospital-management-system'), $patient_id) :
                    __('Patient registered successfully!', 'hospital-management-system');
                break;
            case '1':
            default:
                $message = __('Patient registered successfully!', 'hospital-management-system');
                break;
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    
    // Get patient statistics
    global $wpdb;
    $patients_table = $wpdb->prefix . 'hms_patients';
    $appointments_table = $wpdb->prefix . 'hms_appointments';
    
    $total_patients = $wpdb->get_var("SELECT COUNT(*) FROM {$patients_table}");
    $new_patients_today = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$patients_table} WHERE DATE(registration_date) = %s",
        current_time('Y-m-d')
    ));
    $new_patients_this_month = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$patients_table} WHERE YEAR(registration_date) = %d AND MONTH(registration_date) = %d",
        current_time('Y'), current_time('n')
    ));
    $patients_with_appointments_today = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT patient_id) FROM {$appointments_table} WHERE DATE(appointment_date) = %s",
        current_time('Y-m-d')
    ));
    ?>
    
    <!-- Patient Statistics -->
    <div class="hms-stats-row" style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
        <div class="hms-stat-card" style="flex: 1; min-width: 200px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <div class="hms-stat-number" style="font-size: 2em; font-weight: bold; color: #0073aa; margin-bottom: 5px;"><?php echo number_format_i18n($total_patients); ?></div>
            <div class="hms-stat-label" style="color: #666; font-size: 14px;"><?php esc_html_e('Total Patients', 'hospital-management-system'); ?></div>
        </div>
        <div class="hms-stat-card" style="flex: 1; min-width: 200px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <div class="hms-stat-number" style="font-size: 2em; font-weight: bold; color: #00a32a; margin-bottom: 5px;"><?php echo number_format_i18n($new_patients_today); ?></div>
            <div class="hms-stat-label" style="color: #666; font-size: 14px;"><?php esc_html_e('New Today', 'hospital-management-system'); ?></div>
        </div>
        <div class="hms-stat-card" style="flex: 1; min-width: 200px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <div class="hms-stat-number" style="font-size: 2em; font-weight: bold; color: #ff6900; margin-bottom: 5px;"><?php echo number_format_i18n($new_patients_this_month); ?></div>
            <div class="hms-stat-label" style="color: #666; font-size: 14px;"><?php esc_html_e('New This Month', 'hospital-management-system'); ?></div>
        </div>
        <div class="hms-stat-card" style="flex: 1; min-width: 200px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <div class="hms-stat-number" style="font-size: 2em; font-weight: bold; color: #826eb4; margin-bottom: 5px;"><?php echo number_format_i18n($patients_with_appointments_today); ?></div>
            <div class="hms-stat-label" style="color: #666; font-size: 14px;"><?php esc_html_e('Appointments Today', 'hospital-management-system'); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="hms-card hms-mb-3">
        <div class="hms-card-body">
            <form method="get" id="hms-patient-filter-form" action="<?php echo esc_url(admin_url('admin.php?page=hms_patients')); ?>">
                <input type="hidden" name="page" value="hms_patients">
                <div class="hms-row hms-g-3">
                    <div class="hms-col-md-4">
                        <label for="filter_search"><?php esc_html_e('Search (Name, ID, Email, Phone)', 'hospital-management-system'); ?></label>
                        <input type="text" name="search" id="filter_search" class="hms-form-control" value="<?php echo esc_attr($search_filter); ?>">
                    </div>
                    <div class="hms-col-md-3">
                        <label for="filter_date_from"><?php esc_html_e('Registered From', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_from" id="filter_date_from" class="hms-form-control" value="<?php echo esc_attr($date_from_filter); ?>">
                    </div>
                    <div class="hms-col-md-3">
                        <label for="filter_date_to"><?php esc_html_e('Registered To', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_to" id="filter_date_to" class="hms-form-control" value="<?php echo esc_attr($date_to_filter); ?>">
                    </div>
                    <div class="hms-col-md-2 hms-d-flex hms-align-items-end" style="flex-direction: column; gap: 5px;">
                        <div style="display: flex; gap: 5px; width: 100%;">
                            <button type="submit" class="hms-btn hms-btn-primary" style="flex: 1;"><?php esc_html_e('Filter', 'hospital-management-system'); ?></button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_patients')); ?>" class="hms-btn hms-btn-secondary" style="flex: 1;"><?php esc_html_e('Clear', 'hospital-management-system'); ?></a>
                        </div>
                        <button type="button" id="hms-export-patients" class="hms-btn hms-btn-success" style="width: 100%; font-size: 12px;">
                            <i class="dashicons dashicons-download" style="font-size: 14px; margin-right: 3px;"></i>
                            <?php esc_html_e('Export CSV', 'hospital-management-system'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="hms-card">
        <div class="hms-card-body">
            <div class="hms-table-responsive">
                <table id="hms-patients-table" class="hms-table hms-table-striped hms-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Patient ID', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Name', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Contact', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Registered On', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Last Visit', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="sr-only"><?php esc_html_e('Loading...', 'hospital-management-system'); ?></span>
                                </div>
                                <p><?php esc_html_e('Loading patients data...', 'hospital-management-system'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Load DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- Load jQuery if not already loaded -->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Load DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('Initializing DataTables for patients table');
    
    // Check if jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        $('#hms-patients-table tbody').html('<tr><td colspan="7" class="text-center text-danger"><?php esc_html_e('Error: jQuery is not loaded. Please contact your administrator.', 'hospital-management-system'); ?></td></tr>');
        return;
    }
    
    // Check if DataTables is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables library is not loaded');
        $('#hms-patients-table tbody').html('<tr><td colspan="7" class="text-center text-danger"><?php esc_html_e('Error: DataTables library is not loaded. Please contact your administrator.', 'hospital-management-system'); ?></td></tr>');
        return;
    }
    
    console.log('DataTables is loaded, initializing table');
    
    // Initialize DataTable
    var patientsTable = $('#hms-patients-table').DataTable({
        ajax: {
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: function(d) {
                // Add required parameters for the HMS AJAX handler
                d.action = 'hms_get_patients_list';
                d.nonce = '<?php echo wp_create_nonce('hms_admin_nonce'); ?>';
                d.search = '<?php echo esc_js($search_filter); ?>';
                d.date_from = '<?php echo esc_js($date_from_filter); ?>';
                d.date_to = '<?php echo esc_js($date_to_filter); ?>';
                console.log('Sending AJAX data:', d);
                return d;
            },
            dataSrc: function(json) {
                console.log('AJAX response:', json);
                if (json.data) {
                    return json.data;
                }
                // If no data property exists, check if the response itself is the data
                if (Array.isArray(json)) {
                    return json;
                }
                return [];
            },
            error: function(xhr, error, thrown) {
                console.error('AJAX error:', error, thrown);
                console.error('Response text:', xhr.responseText);
                $('#hms-patients-table tbody').html('<tr><td colspan="7" class="text-center text-danger"><?php esc_html_e('Error loading data. Please try again or contact your administrator.', 'hospital-management-system'); ?></td></tr>');
            }
        },
        columns: [
            { data: 'patient_id' },
            { data: 'name' },
            { data: 'contact' },
            { data: 'registered_on' },
            { data: 'last_visit' },
            {
                data: 'status',
                render: function(data, type, row, meta) {
                    // Check if status is null or undefined
                    if (!data) {
                        data = 'unknown';
                    }
                    
                    var statusClass = 'hms-status-badge';
                    switch(data) {
                        case 'active':
                            statusClass += ' hms-status-active';
                            break;
                        case 'inactive':
                            statusClass += ' hms-status-inactive';
                            break;
                        case 'new':
                            statusClass += ' hms-status-new';
                            break;
                        default:
                            statusClass += ' hms-status-inactive';
                    }
                    
                    return `<span class="${statusClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    // Check if row.id exists
                    if (!row.id) {
                        return '';
                    }
                    
                    // Build URLs properly using the patient registration page with an ID parameter for viewing
                    var viewUrl = '<?php echo esc_url(admin_url('admin.php?page=hms_patient_registration&id=')); ?>' + row.id;
                    var medicalHistoryUrl = '<?php echo esc_url(admin_url('admin.php?page=hms_comprehensive_medical_history&patient_id=')); ?>' + row.id;
                    var appointmentsUrl = '<?php echo esc_url(admin_url('admin.php?page=hms_appointments&patient_id=')); ?>' + row.id;
                    
                    return `
                        <div class="hms-action-buttons">
                            <a href="${viewUrl}" class="hms-btn hms-btn-sm hms-btn-info" title="<?php esc_attr_e('View/Edit Details', 'hospital-management-system'); ?>">
                                <i class="dashicons dashicons-visibility"></i>
                            </a>
                            <a href="${medicalHistoryUrl}" class="hms-btn hms-btn-sm hms-btn-primary" title="<?php esc_attr_e('Medical History', 'hospital-management-system'); ?>">
                                <i class="dashicons dashicons-media-text"></i>
                            </a>
                            <a href="${appointmentsUrl}" class="hms-btn hms-btn-sm hms-btn-warning" title="<?php esc_attr_e('View Appointments', 'hospital-management-system'); ?>">
                                <i class="dashicons dashicons-calendar-alt"></i>
                            </a>
                            <button type="button" class="hms-btn hms-btn-sm hms-btn-danger hms-delete-patient" data-patient-id="${row.id}" data-patient-name="${row.name}" title="<?php esc_attr_e('Delete Patient', 'hospital-management-system'); ?>">
                                <i class="dashicons dashicons-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        order: [[3, 'desc']], // Sort by registered_on by default
        initComplete: function(settings, json) {
            console.log('DataTable initialization complete');
            // Hide any loading indicators that might be showing
            this.api().columns.adjust().draw();
        }
    });
    
    // Handle filter form submission
    $('#hms-patient-filter-form').on('submit', function(e) {
        e.preventDefault();
        // Get the form action URL
        var actionUrl = $(this).attr('action');
        // Get the serialized form data
        var formData = $(this).serialize();
        // Redirect to the action URL with form data
        window.location.href = actionUrl + (actionUrl.indexOf('?') !== -1 ? '&' : '?') + formData;
    });
    
    // Handle patient deletion
    $(document).on('click', '.hms-delete-patient', function(e) {
        e.preventDefault();
        
        var patientId = $(this).data('patient-id');
        var patientName = $(this).data('patient-name');
        
        if (!patientId) {
            alert('<?php esc_html_e('Error: Patient ID not found.', 'hospital-management-system'); ?>');
            return;
        }
        
        var confirmMessage = '<?php esc_html_e('Are you sure you want to delete patient', 'hospital-management-system'); ?>' + ' "' + patientName + '"?\n\n' + 
                           '<?php esc_html_e('This will permanently delete all patient data including medical records, appointments, and billing information.', 'hospital-management-system'); ?>\n\n' +
                           '<?php esc_html_e('This action cannot be undone.', 'hospital-management-system'); ?>';
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        var $button = $(this);
        var originalHtml = $button.html();
        
        // Show loading state
        $button.prop('disabled', true).html('<i class="dashicons dashicons-update-alt"></i>');
        
        // Send AJAX request
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'hms_delete_patient',
                nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                patient_id: patientId
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('<?php esc_html_e('Patient deleted successfully.', 'hospital-management-system'); ?>');
                    // Reload the DataTable
                    patientsTable.ajax.reload();
                } else {
                    alert('<?php esc_html_e('Error:', 'hospital-management-system'); ?> ' + (response.data || '<?php esc_html_e('Unknown error occurred.', 'hospital-management-system'); ?>'));
                    // Restore button state
                    $button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('<?php esc_html_e('Error: Failed to delete patient. Please try again.', 'hospital-management-system'); ?>');
                // Restore button state
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Add quick search functionality with debouncing
    var searchTimeout;
    $('#filter_search').on('keyup', function() {
        var searchTerm = $(this).val();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout for debounced search
        searchTimeout = setTimeout(function() {
            if (searchTerm.length >= 2 || searchTerm.length === 0) {
                // Update the DataTable search
                patientsTable.search(searchTerm).draw();
            }
        }, 500); // 500ms delay
    });
    
    // Add export functionality
    $('#hms-export-patients').on('click', function(e) {
        e.preventDefault();
        
        var searchTerm = $('#filter_search').val();
        var dateFrom = $('#filter_date_from').val();
        var dateTo = $('#filter_date_to').val();
        
        // Build export URL with current filters
        var exportUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var exportData = {
            action: 'hms_export_patients',
            nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
            search: searchTerm,
            date_from: dateFrom,
            date_to: dateTo,
            format: 'csv'
        };
        
        // Create a form and submit it to trigger download
        var form = $('<form>', {
            method: 'POST',
            action: exportUrl
        });
        
        $.each(exportData, function(key, value) {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: value
            }));
        });
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
});
</script>

<style>
/* Basic styling, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-mb-3 { margin-bottom: 1rem !important; }
.hms-ms-2 { margin-left: 0.5rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-2, .hms-col-md-3, .hms-col-md-4 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) {
    .hms-col-md-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
    .hms-col-md-3 { flex: 0 0 25%; max-width: 25%; }
    .hms-col-md-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
}
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-body { padding: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-btn-secondary { color: #fff; background-color: #6c757d; border-color: #6c757d; }
.hms-btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }
.hms-btn-success { color: #fff; background-color: #28a745; border-color: #28a745; }
.hms-btn-success:hover { background-color: #218838; border-color: #1e7e34; }
.hms-btn-info { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }
.hms-btn-info:hover { background-color: #138496; border-color: #117a8b; }
.hms-btn-warning { color: #212529; background-color: #ffc107; border-color: #ffc107; }
.hms-btn-warning:hover { background-color: #e0a800; border-color: #d39e00; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-btn-sm { padding: .25rem .5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
.hms-d-flex { display: flex !important; }
.hms-align-items-end { align-items: flex-end !important; }
.hms-table-responsive { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hms-table { width: 100%; margin-bottom: 1rem; background-color: transparent; border-collapse: collapse; }
.hms-table th, .hms-table td { padding: .75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
.hms-table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
.hms-table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
.text-success { color: #28a745 !important; }
.text-muted { color: #6c757d !important; }
.text-center { text-align: center !important; }
.text-danger { color: #dc3545 !important; }
.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
}
@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
    white-space: nowrap;
    border: 0;
}

/* Enhanced action buttons styling */
.hms-action-buttons {
    display: flex;
    gap: 5px;
    justify-content: center;
    flex-wrap: wrap;
}

.hms-action-buttons .hms-btn {
    min-width: 32px;
    height: 32px;
    padding: 4px 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.hms-action-buttons .hms-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hms-action-buttons .hms-btn .dashicons {
    font-size: 16px;
    line-height: 1;
}

.hms-btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.hms-btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
    color: #fff;
}

.hms-btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Status badges */
.hms-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hms-status-active {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.hms-status-inactive {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.hms-status-new {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Improved table styling */
#hms-patients-table {
    font-size: 14px;
}

#hms-patients-table th {
    background-color: #f8f9fa !important;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6 !important;
    color: #495057;
}

#hms-patients-table td {
    vertical-align: middle;
    padding: 12px 8px;
}

#hms-patients-table tbody tr:hover {
    background-color: #f8f9fa !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .hms-action-buttons {
        flex-direction: column;
        gap: 2px;
    }
    
    .hms-action-buttons .hms-btn {
        width: 100%;
        justify-content: flex-start;
        padding: 6px 12px;
    }
    
    .hms-action-buttons .hms-btn .dashicons {
        margin-right: 5px;
    }
}

/* Loading state for buttons */
.hms-btn .dashicons-update-alt {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
