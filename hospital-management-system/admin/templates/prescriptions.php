<?php
/**
 * Admin template for listing and managing all digital prescriptions.
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

// Ensure the HMS_Clinical class is available
if (!class_exists('HMS_Clinical')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-clinical.php';
}
// Ensure HMS_Patient class is available for patient search
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}
// Ensure HMS_Pharmacy class is available for medicine list
if (!class_exists('HMS_Pharmacy')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-pharmacy.php';
}


$clinical_obj = new HMS_Clinical();
$patient_obj = new HMS_Patient();
$pharmacy_obj = new HMS_Pharmacy();

// Get filter values from URL
$patient_filter = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date_from_filter = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to_filter = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// Get doctors and patients for filter dropdowns
// Fetch doctors from hms_staff table
global $wpdb;
$doctors = $wpdb->get_results(
    "SELECT id, first_name, last_name FROM {$wpdb->prefix}hms_staff WHERE role = 'doctor' ORDER BY first_name, last_name"
);

// If no doctors found in staff table, fallback to WordPress users
if (empty($doctors)) {
    $doctors = get_users(array('role' => 'hms_doctor', 'fields' => array('ID', 'display_name')));
    if (empty($doctors)) {
        // If no doctors found, try getting administrators
        $doctors = get_users(array('role' => 'administrator', 'fields' => array('ID', 'display_name')));
    }
    if (empty($doctors)) {
        // If still no doctors found, get all users
        $doctors = get_users(array('fields' => array('ID', 'display_name')));
    }
}
$patients = $patient_obj->get_patients();
$medicines = $pharmacy_obj->get_medicines(); // Get all medicines for the prescription form

// Statuses for filter dropdown
$prescription_statuses = array(
    'active' => __('Active', 'hospital-management-system'),
    'completed' => __('Completed', 'hospital-management-system'),
    'cancelled' => __('Cancelled', 'hospital-management-system'),
);
?>

<!-- Display success/error messages -->
<?php if (isset($_GET['message'])) : ?>
    <div id="message" class="notice notice-<?php echo isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success'; ?> is-dismissible">
        <p><?php echo esc_html(sanitize_text_field($_GET['message'])); ?></p>
    </div>
<?php endif; ?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Prescriptions Management', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_prescription_details')); ?>" class="page-title-action"><?php esc_html_e('Add New Prescription', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="hms-card hms-mb-3">
        <div class="hms-card-body">
            <form method="get" id="hms-prescription-filter-form">
                <input type="hidden" name="page" value="hms_prescriptions">
                <div class="hms-row hms-g-3">
                    <div class="hms-col-md-3">
                        <label for="filter_patient"><?php esc_html_e('Patient', 'hospital-management-system'); ?></label>
                        <select name="patient_id" id="filter_patient" class="hms-form-control">
                            <option value=""><?php esc_html_e('All Patients', 'hospital-management-system'); ?></option>
                            <?php foreach ($patients as $patient) : ?>
                                <option value="<?php echo esc_attr($patient->id); ?>" <?php selected($patient_filter, $patient->id); ?>>
                                    <?php echo esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hms-col-md-3">
                        <label for="filter_doctor"><?php esc_html_e('Doctor', 'hospital-management-system'); ?></label>
                        <select name="doctor_id" id="filter_doctor" class="hms-form-control">
                            <option value=""><?php esc_html_e('All Doctors', 'hospital-management-system'); ?></option>
                            <?php foreach ($doctors as $doctor) : ?>
                                <?php if (isset($doctor->id)): ?>
                                    <option value="<?php echo esc_attr($doctor->id); ?>" <?php selected($doctor_filter, $doctor->id); ?>><?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?></option>
                                <?php else: ?>
                                    <option value="<?php echo esc_attr($doctor->ID); ?>" <?php selected($doctor_filter, $doctor->ID); ?>><?php echo esc_html($doctor->display_name); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hms-col-md-2">
                        <label for="filter_date_from"><?php esc_html_e('Date From', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_from" id="filter_date_from" class="hms-form-control" value="<?php echo esc_attr($date_from_filter); ?>">
                    </div>
                    <div class="hms-col-md-2">
                        <label for="filter_date_to"><?php esc_html_e('Date To', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_to" id="filter_date_to" class="hms-form-control" value="<?php echo esc_attr($date_to_filter); ?>">
                    </div>
                    <div class="hms-col-md-2">
                        <label for="filter_status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                        <select name="status" id="filter_status" class="hms-form-control">
                            <option value=""><?php esc_html_e('All Statuses', 'hospital-management-system'); ?></option>
                            <?php 
                            $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                            foreach ($prescription_statuses as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($status_filter, $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hms-col-md-2 hms-d-flex hms-align-items-end">
                        <button type="submit" class="hms-btn hms-btn-primary"><?php esc_html_e('Filter', 'hospital-management-system'); ?></button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_prescriptions')); ?>" class="hms-btn hms-btn-secondary hms-ms-2"><?php esc_html_e('Clear', 'hospital-management-system'); ?></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="hms-card">
        <div class="hms-card-body">
            <div class="hms-table-responsive">
                <table id="hms-prescriptions-table" class="hms-table hms-table-striped hms-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Patient', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Doctor', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Diagnosis', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Prescription Modal -->
<div class="hms-modal fade" id="hms-prescription-modal" tabindex="-1" aria-labelledby="prescriptionModalLabel" aria-hidden="true">
    <div class="hms-modal-dialog hms-modal-lg">
        <div class="hms-modal-content">
            <div class="hms-modal-header">
                <h5 class="hms-modal-title" id="hms-prescription-modal-title"><?php esc_html_e('Create New Prescription', 'hospital-management-system'); ?></h5>
                <button type="button" class="hms-btn-close hms-modal-close" aria-label="Close"></button>
            </div>
            <div class="hms-modal-body">
                <form id="hms-prescription-form">
                    <input type="hidden" name="prescription_id" id="prescription_id" value="">
                    <?php wp_nonce_field('hms_admin_nonce', 'prescription_nonce'); ?>
                    
                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-6">
                            <label for="patient_id"><?php esc_html_e('Patient*', 'hospital-management-system'); ?></label>
                            <select name="patient_id" id="patient_id" class="hms-form-control" required>
                                <option value=""><?php esc_html_e('Select Patient', 'hospital-management-system'); ?></option>
                                <?php foreach ($patients as $patient) : ?>
                                    <option value="<?php echo esc_attr($patient->id); ?>"><?php echo esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="hms-col-md-6">
                            <label for="doctor_id"><?php esc_html_e('Doctor*', 'hospital-management-system'); ?></label>
                            <select name="doctor_id" id="doctor_id" class="hms-form-control" required>
                                <option value=""><?php esc_html_e('Select Doctor', 'hospital-management-system'); ?></option>
                                <?php foreach ($doctors as $doctor) : ?>
                                    <?php if (isset($doctor->id)): ?>
                                        <option value="<?php echo esc_attr($doctor->id); ?>"><?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo esc_attr($doctor->ID); ?>"><?php echo esc_html($doctor->display_name); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-12">
                            <label for="appointment_id"><?php esc_html_e('Link to Appointment (Optional)', 'hospital-management-system'); ?></label>
                            <select name="appointment_id" id="appointment_id" class="hms-form-control">
                                <option value=""><?php esc_html_e('Select Appointment (Auto-fills Patient & Doctor)', 'hospital-management-system'); ?></option>
                                <?php
                                // Get recent appointments for dropdown
                                global $wpdb;
                                $recent_appointments = $wpdb->get_results(
                                    "SELECT a.id, a.appointment_date, a.appointment_time, a.patient_id, a.doctor_id,
                                            p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id as patient_number,
                                            s.first_name as doctor_first_name, s.last_name as doctor_last_name
                                     FROM {$wpdb->prefix}hms_appointments a
                                     LEFT JOIN {$wpdb->prefix}hms_patients p ON a.patient_id = p.id
                                     LEFT JOIN {$wpdb->prefix}hms_staff s ON a.doctor_id = s.id
                                     WHERE a.appointment_date >= CURDATE() - INTERVAL 30 DAY
                                     ORDER BY a.appointment_date DESC, a.appointment_time DESC
                                     LIMIT 50"
                                );
                                foreach ($recent_appointments as $appointment) :
                                    $patient_name = $appointment->patient_first_name . ' ' . $appointment->patient_last_name . ' (' . $appointment->patient_number . ')';
                                    $doctor_name = $appointment->doctor_first_name ? $appointment->doctor_first_name . ' ' . $appointment->doctor_last_name : 'N/A';
                                    $appointment_display = date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)) . ' ' . 
                                                         date_i18n(get_option('time_format'), strtotime($appointment->appointment_time)) . 
                                                         ' - ' . $patient_name . ' with Dr. ' . $doctor_name;
                                ?>
                                    <option value="<?php echo esc_attr($appointment->id); ?>" 
                                            data-patient-id="<?php echo esc_attr($appointment->patient_id); ?>" 
                                            data-doctor-id="<?php echo esc_attr($appointment->doctor_id); ?>">
                                        <?php echo esc_html($appointment_display); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="hms-form-text text-muted"><?php esc_html_e('Selecting an appointment will automatically fill patient and doctor fields', 'hospital-management-system'); ?></small>
                        </div>
                    </div>

                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-6">
                            <label for="issue_date"><?php esc_html_e('Prescription Date*', 'hospital-management-system'); ?></label>
                            <input type="date" name="issue_date" id="issue_date" class="hms-form-control" required>
                        </div>
                         <div class="hms-col-md-6">
                            <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                            <select name="status" id="status" class="hms-form-control">
                                <?php foreach ($prescription_statuses as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected('active', $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="hms-row hms-mb-3">
                         <div class="hms-col-md-12">
                            <label for="diagnosis"><?php esc_html_e('Diagnosis', 'hospital-management-system'); ?></label>
                            <textarea name="diagnosis" id="diagnosis" rows="2" class="hms-form-control"></textarea>
                        </div>
                    </div>

                    <hr>

                    <!-- Medicines Section -->
                    <div class="hms-mb-3">
                        <label><?php esc_html_e('Medicines', 'hospital-management-system'); ?></label>
                        <div id="hms-medicines-container">
                            <!-- Medicine rows will be added here by JS -->
                        </div>
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-secondary hms-mt-2" id="hms-add-medicine-btn"><?php esc_html_e('+ Add Medicine', 'hospital-management-system'); ?></button>
                    </div>

                    <div class="hms-row hms-mb-3">
                         <div class="hms-col-md-12">
                            <label for="notes"><?php esc_html_e('General Notes', 'hospital-management-system'); ?></label>
                            <textarea name="notes" id="notes" rows="3" class="hms-form-control"></textarea>
                        </div>
                    </div>

                    <div id="hms-prescription-feedback" class="hms-feedback hms-mt-3" style="display: none;"></div>
                </form>
            </div>
            <div class="hms-modal-footer">
                <button type="button" class="hms-btn hms-btn-secondary hms-modal-close"><?php esc_html_e('Close', 'hospital-management-system'); ?></button>
                <button type="button" class="hms-btn hms-btn-primary" id="hms-save-prescription-btn"><?php esc_html_e('Save Prescription', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Template for a single medicine row -->
<template id="hms-medicine-row-template">
    <div class="hms-row hms-mb-2 hms-medicine-row">
        <div class="hms-col-md-4">
            <input type="text" name="medicines[{index}][medicine_name]" class="hms-form-control" placeholder="<?php esc_html_e('Medicine Name', 'hospital-management-system'); ?>" required>
        </div>
        <div class="hms-col-md-2">
            <input type="text" name="medicines[{index}][dosage]" class="hms-form-control" placeholder="<?php esc_html_e('Dosage', 'hospital-management-system'); ?>" required>
        </div>
        <div class="hms-col-md-2">
            <input type="text" name="medicines[{index}][frequency]" class="hms-form-control" placeholder="<?php esc_html_e('Frequency', 'hospital-management-system'); ?>" required>
        </div>
        <div class="hms-col-md-2">
            <input type="text" name="medicines[{index}][duration]" class="hms-form-control" placeholder="<?php esc_html_e('Duration', 'hospital-management-system'); ?>" required>
        </div>
        <div class="hms-col-md-2">
            <input type="text" name="medicines[{index}][instructions]" class="hms-form-control" placeholder="<?php esc_html_e('Instructions', 'hospital-management-system'); ?>">
        </div>
        <div class="hms-col-md-1 hms-d-flex hms-align-items-center">
            <button type="button" class="hms-btn hms-btn-sm hms-btn-danger hms-remove-medicine-btn"><i class="dashicons dashicons-no-alt"></i></button>
        </div>
    </div>
</template>


<script type="text/javascript">
jQuery(document).ready(function($) {
    var prescriptionsTable;
    var prescriptionModal;
    var medicineRowTemplate = $('#hms-medicine-row-template').html();
    var medicineIndex = 0;

    // Initialize Bootstrap Modal when Bootstrap is loaded
    function initializeModal() {
        if (typeof bootstrap !== 'undefined') {
            prescriptionModal = new bootstrap.Modal(document.getElementById('hms-prescription-modal'));
        } else {
            // Fallback if Bootstrap is not loaded
            prescriptionModal = {
                show: function() {
                    $('#hms-prescription-modal').css('display', 'block').addClass('show');
                    $('body').addClass('modal-open');
                    $('<div class="modal-backdrop fade show"></div>').appendTo('body');
                },
                hide: function() {
                    $('#hms-prescription-modal').css('display', 'none').removeClass('show');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                }
            };
        }
    }

    // Initialize modal with a delay to ensure all scripts are loaded
    setTimeout(initializeModal, 100);
    
    // Handle modal close buttons
    $(document).on('click', '.hms-modal-close', function(e) {
        e.preventDefault();
        if (prescriptionModal) {
            prescriptionModal.hide();
        } else {
            // Fallback if modal is not initialized
            $('#hms-prescription-modal').css('display', 'none').removeClass('show');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });
    
    // Handle clicking outside the modal to close it
    $(document).on('click', '.modal-backdrop', function(e) {
        e.preventDefault();
        if (prescriptionModal) {
            prescriptionModal.hide();
        } else {
            // Fallback if modal is not initialized
            $('#hms-prescription-modal').css('display', 'none').removeClass('show');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });

    // Initialize DataTable
    console.log('Initializing DataTable...');
    
    // First, let's check if the prescriptions table exists and has data
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'hms_get_prescriptions_list',
            nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
            draw: 1,
            start: 0,
            length: 10
        },
        dataType: 'json',
        success: function(response) {
            console.log('Direct AJAX response:', response);
            if (response.data && response.data.length > 0) {
                console.log('Found ' + response.data.length + ' prescriptions');
            } else {
                console.log('No prescriptions found or error in response');
            }
        },
        error: function(xhr, error, thrown) {
            console.log('Direct AJAX error:', error, thrown);
            console.log('Response text:', xhr.responseText);
        }
    });
    
    prescriptionsTable = $('#hms-prescriptions-table').DataTable({
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function(d) {
                console.log('Sending AJAX request for prescriptions...');
                d.action = 'hms_get_prescriptions_list';
                d.nonce = '<?php echo wp_create_nonce('hms_admin_nonce'); ?>';
                d.patient_id = $('#filter_patient').val();
                d.doctor_id = $('#filter_doctor').val();
                d.date_from = $('#filter_date_from').val();
                d.date_to = $('#filter_date_to').val();
                d.status = $('#filter_status').val();
                console.log('AJAX data:', d);
            },
            dataSrc: function(json) {
                console.log('AJAX response:', json);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.log('DataTables error:', error, thrown);
                console.log('Response text:', xhr.responseText);
                $('#hms-prescriptions-table tbody').html('<tr><td colspan="7" class="text-center">Error loading data. Please try again.</td></tr>');
            }
        },
        columns: [
            { data: 'id' },
            { data: 'date' },
            { data: 'patient' },
            { data: 'doctor' },
            { data: 'diagnosis' },
            { data: 'status' },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return `
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-info hms-edit-prescription-btn" data-id="${row.id}" title="<?php esc_html_e('Edit', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-edit"></i>
                        </button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_prescription_details&action=view&id=')); ?>${row.id}" class="hms-btn hms-btn-sm hms-btn-primary" title="<?php esc_html_e('View & Print', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-visibility"></i>
                        </a>
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-warning hms-change-status-btn" data-id="${row.id}" data-current-status="${row.status.toLowerCase()}" title="<?php esc_html_e('Change Status', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-update"></i>
                        </button>
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-danger hms-delete-prescription-btn" data-id="${row.id}" title="<?php esc_html_e('Delete', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']], // Sort by date by default
        language: {
            emptyTable: "No prescriptions found",
            zeroRecords: "No matching prescriptions found"
        }
    });
    
    // Handle filter form submission
    $('#hms-prescription-filter-form').on('submit', function(e) {
        e.preventDefault();
        prescriptionsTable.ajax.reload();
    });
    
    // Handle filter changes
    $('#filter_patient, #filter_doctor, #filter_date_from, #filter_date_to, #filter_status').on('change', function() {
        prescriptionsTable.ajax.reload();
    });

    // Handle appointment selection for auto-filling patient and doctor
    $('#appointment_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var patientId = selectedOption.data('patient-id');
        var doctorId = selectedOption.data('doctor-id');
        
        if (patientId && doctorId) {
            $('#patient_id').val(patientId);
            $('#doctor_id').val(doctorId);
        }
    });

    // Add New Prescription button
    $('#hms-add-new-prescription-btn').on('click', function(e) {
        e.preventDefault();
        $('#hms-prescription-modal-title').text('<?php esc_html_e('Create New Prescription', 'hospital-management-system'); ?>');
        $('#hms-prescription-form')[0].reset();
        $('#prescription_id').val('');
        $('#hms-medicines-container').empty(); // Clear existing medicines
        medicineIndex = 0;
        $('#hms-prescription-feedback').hide();
        
        // Add one empty medicine row by default
        addMedicineRow();
        
        if (prescriptionModal) {
            // Ensure modal is properly shown
            setTimeout(function() {
                prescriptionModal.show();
            }, 100);
        } else {
            // Fallback if modal is not initialized
            $('#hms-prescription-modal').css('display', 'block').addClass('show');
            $('body').addClass('modal-open');
            $('<div class="modal-backdrop fade show"></div>').appendTo('body');
        }
    });

    // Edit Prescription button (delegated)
    $('#hms-prescriptions-table tbody').on('click', '.hms-edit-prescription-btn', function(e) {
        e.preventDefault();
        var prescriptionId = $(this).data('id');
        $('#hms-prescription-modal-title').text('<?php esc_html_e('Edit Prescription', 'hospital-management-system'); ?>');
        $('#hms-prescription-form')[0].reset();
        $('#prescription_id').val(prescriptionId);
        $('#hms-medicines-container').empty(); // Clear existing medicines
        medicineIndex = 0;
        $('#hms-prescription-feedback').hide();
        
        // Fetch prescription data and populate form
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'hms_get_prescription',
                nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                prescription_id: prescriptionId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var presc = response.data;
                    $('#patient_id').val(presc.patient_id);
                    $('#doctor_id').val(presc.doctor_id);
                    $('#issue_date').val(presc.issue_date);
                    $('#status').val(presc.status);
                    $('#diagnosis').val(presc.diagnosis);
                    $('#notes').val(presc.notes);

                    // Populate medicines
                    if (presc.medicines && presc.medicines.length > 0) {
                        $.each(presc.medicines, function(index, med) {
                            addMedicineRow(med);
                        });
                    }
                    if (prescriptionModal) {
                        // Ensure modal is properly shown
                        setTimeout(function() {
                            prescriptionModal.show();
                        }, 100);
                    } else {
                        // Fallback if modal is not initialized
                        $('#hms-prescription-modal').css('display', 'block').addClass('show');
                        $('body').addClass('modal-open');
                        $('<div class="modal-backdrop fade show"></div>').appendTo('body');
                    }
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Add Medicine button
    $('#hms-add-medicine-btn').on('click', function() {
        addMedicineRow();
    });

    // Remove Medicine button (delegated)
    $('#hms-medicines-container').on('click', '.hms-remove-medicine-btn', function() {
        $(this).closest('.hms-medicine-row').remove();
    });

    function addMedicineRow(medData = null) {
        var newRow = medicineRowTemplate.replace(/{index}/g, medicineIndex++);
        $('#hms-medicines-container').append(newRow);

        if (medData) {
            var $lastRow = $('#hms-medicines-container .hms-medicine-row').last();
            $lastRow.find('input[name*="[medicine_name]"]').val(medData.medicine_name);
            $lastRow.find('input[name*="[dosage]"]').val(medData.dosage);
            $lastRow.find('input[name*="[frequency]"]').val(medData.frequency);
            $lastRow.find('input[name*="[duration]"]').val(medData.duration);
            $lastRow.find('input[name*="[instructions]"]').val(medData.instructions);
        }
    }


    // Save Prescription button
    $('#hms-save-prescription-btn').on('click', function(e) {
        e.preventDefault();
        var $form = $('#hms-prescription-form');
        var $feedback = $('#hms-prescription-feedback');
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        // Validate required fields
        var patient_id = $('#patient_id').val();
        var doctor_id = $('#doctor_id').val();
        var issue_date = $('#issue_date').val();
        var diagnosis = $('#diagnosis').val();
        
        if (!patient_id) {
            $feedback.addClass('notice notice-error').html('<p><?php esc_html_e('Please select a patient.', 'hospital-management-system'); ?></p>').show();
            $('#hms-save-prescription-btn').prop('disabled', false).text('<?php esc_html_e('Save Prescription', 'hospital-management-system'); ?>');
            return;
        }
        
        if (!doctor_id) {
            $feedback.addClass('notice notice-error').html('<p><?php esc_html_e('Please select a doctor.', 'hospital-management-system'); ?></p>').show();
            $('#hms-save-prescription-btn').prop('disabled', false).text('<?php esc_html_e('Save Prescription', 'hospital-management-system'); ?>');
            return;
        }
        
        if (!issue_date) {
            $feedback.addClass('notice notice-error').html('<p><?php esc_html_e('Please select a prescription date.', 'hospital-management-system'); ?></p>').show();
            $('#hms-save-prescription-btn').prop('disabled', false).text('<?php esc_html_e('Save Prescription', 'hospital-management-system'); ?>');
            return;
        }
        
        // Diagnosis field is now optional - no validation needed

        // Collect medicines data
        var medicinesData = [];
        $('#hms-medicines-container .hms-medicine-row').each(function() {
            var $row = $(this);
            var medicine_name = $row.find('input[name*="[medicine_name]"]').val();
            var dosage = $row.find('input[name*="[dosage]"]').val();
            var frequency = $row.find('input[name*="[frequency]"]').val();
            var duration = $row.find('input[name*="[duration]"]').val();
            var instructions = $row.find('input[name*="[instructions]"]').val();
            
            if (medicine_name && dosage && frequency && duration) {
                medicinesData.push({
                    medicine_name: medicine_name,
                    dosage: dosage,
                    frequency: frequency,
                    duration: duration,
                    instructions: instructions
                });
            }
        });
        
        // Validate at least one medicine is added
        if (medicinesData.length === 0) {
            $feedback.addClass('notice notice-error').html('<p><?php esc_html_e('Please add at least one medicine.', 'hospital-management-system'); ?></p>').show();
            $('#hms-save-prescription-btn').prop('disabled', false).text('<?php esc_html_e('Save Prescription', 'hospital-management-system'); ?>');
            return;
        }

        var formData = $form.serialize();
        var action = $('#prescription_id').val() ? 'hms_update_prescription' : 'hms_add_prescription';
        
        // Add appointment_id to form data
        formData += '&appointment_id=' + encodeURIComponent($('#appointment_id').val());
        
        // Add action, nonce, and medicines to the serialized form data
        formData += '&action=' + action;
        formData += '&nonce=<?php echo wp_create_nonce('hms_admin_nonce'); ?>';
        formData += '&medicines=' + encodeURIComponent(JSON.stringify(medicinesData));


        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#hms-save-prescription-btn').prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                    prescriptionsTable.ajax.reload();
                    setTimeout(function() {
                        if (prescriptionModal) {
                            prescriptionModal.hide();
                        } else {
                            // Fallback if modal is not initialized
                            $('#hms-prescription-modal').css('display', 'none').removeClass('show');
                            $('body').removeClass('modal-open');
                            $('.modal-backdrop').remove();
                        }
                    }, 1500);
                } else {
                    $feedback.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                }
            },
            error: function(xhr) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : '<?php esc_html_e('An unexpected error occurred.', 'hospital-management-system'); ?>';
                $feedback.addClass('notice notice-error').html('<p>' + errorMessage + '</p>').show();
            },
            complete: function() {
                $('#hms-save-prescription-btn').prop('disabled', false).text('<?php esc_html_e('Save Prescription', 'hospital-management-system'); ?>');
            }
        });
    });

    // Delete Prescription button (delegated)
    $('#hms-prescriptions-table tbody').on('click', '.hms-delete-prescription-btn', function(e) {
        e.preventDefault();
        var prescriptionId = $(this).data('id');
        
        if (confirm('<?php esc_html_e('Are you sure you want to delete this prescription? This action cannot be undone.', 'hospital-management-system'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_delete_prescription',
                    nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                    prescription_id: prescriptionId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Reload the table to show updated data
                        prescriptionsTable.ajax.reload();
                        
                        // Show success message
                        alert(response.data.message);
                    } else {
                        alert(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert('<?php esc_html_e('An error occurred while deleting the prescription.', 'hospital-management-system'); ?>');
                }
            });
        }
    });

    // Change Status button (delegated)
    $('#hms-prescriptions-table tbody').on('click', '.hms-change-status-btn', function(e) {
        e.preventDefault();
        var prescriptionId = $(this).data('id');
        var currentStatus = $(this).data('current-status');
        
        // Create status options
        var statusOptions = {
            'active': '<?php esc_html_e('Active', 'hospital-management-system'); ?>',
            'completed': '<?php esc_html_e('Completed', 'hospital-management-system'); ?>',
            'cancelled': '<?php esc_html_e('Cancelled', 'hospital-management-system'); ?>'
        };
        
        var newStatus = prompt('<?php esc_html_e('Select new status:', 'hospital-management-system'); ?>\n\n' + 
                              'Current: ' + (statusOptions[currentStatus] || currentStatus) + '\n\n' +
                              'Enter: active, completed, or cancelled');
        
        if (newStatus && newStatus !== currentStatus && statusOptions[newStatus]) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_update_prescription_status',
                    nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                    prescription_id: prescriptionId,
                    status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Reload the table to show updated data
                        prescriptionsTable.ajax.reload();
                        
                        // Show success message
                        alert(response.data.message);
                    } else {
                        alert(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert('<?php esc_html_e('An error occurred while updating the prescription status.', 'hospital-management-system'); ?>');
                }
            });
        }
    });
});
</script>

<style>
/* Basic styling, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-mb-2 { margin-bottom: 0.5rem !important; }
.hms-mb-3 { margin-bottom: 1rem !important; }
.hms-mt-2 { margin-top: 0.5rem !important; }
.hms-mt-3 { margin-top: 1rem !important; }
.hms-ms-2 { margin-left: 0.5rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-1, .hms-col-md-2, .hms-col-md-3, .hms-col-md-5, .hms-col-md-6, .hms-col-md-12 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) {
    .hms-col-md-1 { flex: 0 0 8.333333%; max-width: 8.333333%; }
    .hms-col-md-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
    .hms-col-md-3 { flex: 0 0 25%; max-width: 25%; }
    .hms-col-md-5 { flex: 0 0 41.666667%; max-width: 41.666667%; }
    .hms-col-md-6 { flex: 0 0 50%; max-width: 50%; }
    .hms-col-md-12 { flex: 0 0 100%; max-width: 100%; }
}
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-body { padding: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-btn-secondary { color: #fff; background-color: #6c757d; border-color: #6c757d; }
.hms-btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }
.hms-btn-danger { color: #fff; background-color: #dc3545; border-color: #dc3545; }
.hms-btn-danger:hover { background-color: #c82333; border-color: #bd2130; }
.hms-btn-info { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }
.hms-btn-info:hover { background-color: #138496; border-color: #117a8b; }
.hms-btn-sm { padding: .25rem .5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
.hms-btn-close { box-sizing: content-box; width: 1em; height: 1em; padding: .25em .25em; color: #000; background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; border: 0; border-radius: .25rem; opacity: .5; }
.hms-btn-close:hover { color: #000; text-decoration: none; opacity: .75; }
.hms-modal { position: fixed; top: 0; left: 0; z-index: 1050; display: none; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.5); }
.hms-modal-dialog { position: relative; width: auto; margin: 1.75rem; pointer-events: none; }
@media (min-width: 576px) { .hms-modal-dialog { max-width: 500px; margin: 1.75rem auto; } }
.hms-modal-lg .hms-modal-dialog { max-width: 800px; }
.hms-modal-content { position: relative; display: flex; flex-direction: column; width: 100%; pointer-events: auto; background-color: #fff; background-clip: padding-box; border: 1px solid rgba(0,0,0,.2); border-radius: .3rem; outline: 0; }
.hms-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1rem; border-bottom: 1px solid #dee2e6; border-top-left-radius: calc(.3rem - 1px); border-top-right-radius: calc(.3rem - 1px); }
.hms-modal-title { margin-bottom: 0; line-height: 1.5; }
.hms-modal-body { position: relative; flex: 1 1 auto; padding: 1rem; }
.hms-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: .75rem; border-top: 1px solid #dee2e6; border-bottom-right-radius: calc(.3rem - 1px); border-bottom-left-radius: calc(.3rem - 1px); }
.hms-feedback { margin-top: 1rem; }
.hms-d-flex { display: flex !important; }
.hms-align-items-center { align-items: center !important; }
.hms-table-responsive { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hms-table { width: 100%; margin-bottom: 1rem; background-color: transparent; border-collapse: collapse; }
.hms-table th, .hms-table td { padding: .75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
.hms-table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
.hms-table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
</style>
