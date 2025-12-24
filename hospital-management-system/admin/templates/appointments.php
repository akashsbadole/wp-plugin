<?php
/**
 * Admin template for listing and managing all appointments.
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

// Ensure the HMS_Appointments class is available
if (!class_exists('HMS_Appointments')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-appointments.php';
}
// Ensure HMS_Patient class is available for patient search
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}

$appointment_obj = new HMS_Appointments();
$patient_obj = new HMS_Patient();

// Get filter values from URL
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$patient_filter = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$date_from_filter = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to_filter = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

// Get doctors and patients for filter dropdowns
global $wpdb;
$staff_table = $wpdb->prefix . 'hms_staff';
$doctors = $wpdb->get_results("SELECT id, first_name, last_name FROM $staff_table WHERE role = 'Doctor' AND status = 'active' ORDER BY first_name, last_name");
$patients = $patient_obj->get_patients('', '', '', 0, 100); // Get first 100 patients for dropdown

// Statuses for filter dropdown
$appointment_statuses = array(
    'scheduled' => __('Scheduled', 'hospital-management-system'),
    'confirmed' => __('Confirmed', 'hospital-management-system'),
    'in-progress' => __('In Progress', 'hospital-management-system'),
    'completed' => __('Completed', 'hospital-management-system'),
    'cancelled' => __('Cancelled', 'hospital-management-system'),
    'no-show' => __('No Show', 'hospital-management-system'),
);
?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Appointments Management', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_appointment_details')); ?>" class="page-title-action"><?php esc_html_e('Add New Appointment', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">
    
    <?php
    // Display success or error messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        $notice_class = $type === 'error' ? 'notice-error' : 'notice-success';
        echo '<div class="notice ' . $notice_class . ' is-dismissible"><p>' . esc_html(urldecode($message)) . '</p></div>';
    }
    ?>

    <!-- Filters -->
    <div class="hms-card hms-mb-3">
        <div class="hms-card-body">
            <form method="get" id="hms-appointment-filter-form">
                <input type="hidden" name="page" value="hms-appointments">
                <div class="hms-row hms-g-3">
                    <div class="hms-col-md-3">
                        <label for="filter_status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                        <select name="status" id="filter_status" class="hms-form-control">
                            <option value=""><?php esc_html_e('All Statuses', 'hospital-management-system'); ?></option>
                            <?php foreach ($appointment_statuses as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($status_filter, $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hms-col-md-3">
                        <label for="filter_doctor"><?php esc_html_e('Doctor', 'hospital-management-system'); ?></label>
                        <select name="doctor_id" id="filter_doctor" class="hms-form-control">
                            <option value=""><?php esc_html_e('All Doctors', 'hospital-management-system'); ?></option>
                            <?php foreach ($doctors as $doctor) : ?>
                                <option value="<?php echo esc_attr($doctor->id); ?>" <?php selected($doctor_filter, $doctor->id); ?>><?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                    <div class="hms-col-md-2">
                        <label for="filter_date_from"><?php esc_html_e('Date From', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_from" id="filter_date_from" class="hms-form-control" value="<?php echo esc_attr($date_from_filter); ?>">
                    </div>
                    <div class="hms-col-md-2">
                        <label for="filter_date_to"><?php esc_html_e('Date To', 'hospital-management-system'); ?></label>
                        <input type="date" name="date_to" id="filter_date_to" class="hms-form-control" value="<?php echo esc_attr($date_to_filter); ?>">
                    </div>
                    <div class="hms-col-md-2 hms-d-flex hms-align-items-end">
                        <button type="submit" class="hms-btn hms-btn-primary"><?php esc_html_e('Filter', 'hospital-management-system'); ?></button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms-appointments')); ?>" class="hms-btn hms-btn-secondary hms-ms-2"><?php esc_html_e('Clear', 'hospital-management-system'); ?></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="hms-card">
        <div class="hms-card-body">
            <div class="hms-table-responsive">
                <table id="hms-appointments-table" class="hms-table hms-table-striped hms-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Time', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Patient', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Doctor', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
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

<!-- Add/Edit Appointment Modal -->
<div class="hms-modal fade" id="hms-appointment-modal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="hms-modal-dialog hms-modal-lg">
        <div class="hms-modal-content">
            <div class="hms-modal-header">
                <h5 class="hms-modal-title" id="hms-appointment-modal-title"><?php esc_html_e('Schedule New Appointment', 'hospital-management-system'); ?></h5>
                <button type="button" class="hms-btn-close" aria-label="Close"></button>
            </div>
            <div class="hms-modal-body">
                <form id="hms-appointment-form">
                    <input type="hidden" name="appointment_id" id="appointment_id" value="">
                    <?php wp_nonce_field('hms_admin_nonce', 'appointment_nonce'); ?>
                    
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
                                    <option value="<?php echo esc_attr($doctor->id); ?>"><?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-12">
                            <label for="department_id"><?php esc_html_e('Department', 'hospital-management-system'); ?></label>
                            <select name="department_id" id="department_id" class="hms-form-control">
                                <option value=""><?php esc_html_e('Select Department', 'hospital-management-system'); ?></option>
                                <?php
                                global $wpdb;
                                $departments_table = $wpdb->prefix . 'hms_departments';
                                $departments = $wpdb->get_results("SELECT id, name FROM $departments_table ORDER BY name");
                                foreach ($departments as $department) : ?>
                                    <option value="<?php echo esc_attr($department->id); ?>"><?php echo esc_html($department->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-4">
                            <label for="appointment_date"><?php esc_html_e('Date*', 'hospital-management-system'); ?></label>
                            <input type="date" name="appointment_date" id="appointment_date" class="hms-form-control" required>
                        </div>
                        <div class="hms-col-md-4">
                            <label for="appointment_time"><?php esc_html_e('Time*', 'hospital-management-system'); ?></label>
                            <select name="appointment_time" id="appointment_time" class="hms-form-control" required>
                                <option value=""><?php esc_html_e('Select Time', 'hospital-management-system'); ?></option>
                            </select>
                        </div>
                         <div class="hms-col-md-4">
                            <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                            <select name="status" id="status" class="hms-form-control">
                                <?php foreach ($appointment_statuses as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected('scheduled', $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="hms-row hms-mb-3">
                         <div class="hms-col-md-12">
                            <label for="notes"><?php esc_html_e('Notes', 'hospital-management-system'); ?></label>
                            <textarea name="notes" id="notes" rows="3" class="hms-form-control"></textarea>
                        </div>
                    </div>

                    <div id="hms-appointment-feedback" class="hms-feedback hms-mt-3" style="display: none;"></div>
                </form>
            </div>
            <div class="hms-modal-footer">
                <button type="button" class="hms-btn hms-btn-secondary hms-btn-close"><?php esc_html_e('Close', 'hospital-management-system'); ?></button>
                <button type="button" class="hms-btn hms-btn-primary" id="hms-save-appointment-btn"><?php esc_html_e('Save Appointment', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="hms-modal fade" id="hms-cancel-appointment-modal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="hms-modal-dialog">
        <div class="hms-modal-content">
            <div class="hms-modal-header">
                <h5 class="hms-modal-title"><?php esc_html_e('Confirm Cancellation', 'hospital-management-system'); ?></h5>
                <button type="button" class="hms-btn-close" aria-label="Close"></button>
            </div>
            <div class="hms-modal-body">
                <p><?php esc_html_e('Are you sure you want to cancel this appointment?', 'hospital-management-system'); ?></p>
                <input type="hidden" id="cancel_appointment_id" value="">
            </div>
            <div class="hms-modal-footer">
                <button type="button" class="hms-btn hms-btn-secondary hms-btn-close"><?php esc_html_e('No', 'hospital-management-system'); ?></button>
                <button type="button" class="hms-btn hms-btn-danger" id="hms-confirm-cancel-btn"><?php esc_html_e('Yes, Cancel', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="hms-modal fade" id="hms-delete-appointment-modal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="hms-modal-dialog">
        <div class="hms-modal-content">
            <div class="hms-modal-header">
                <h5 class="hms-modal-title"><?php esc_html_e('Confirm Deletion', 'hospital-management-system'); ?></h5>
                <button type="button" class="hms-btn-close" aria-label="Close"></button>
            </div>
            <div class="hms-modal-body">
                <p><?php esc_html_e('Are you sure you want to permanently delete this appointment? This action cannot be undone.', 'hospital-management-system'); ?></p>
                <input type="hidden" id="delete_appointment_id" value="">
            </div>
            <div class="hms-modal-footer">
                <button type="button" class="hms-btn hms-btn-secondary hms-btn-close"><?php esc_html_e('No', 'hospital-management-system'); ?></button>
                <button type="button" class="hms-btn hms-btn-danger" id="hms-confirm-delete-btn"><?php esc_html_e('Yes, Delete', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
jQuery(document).ready(function($) {
    var appointmentsTable;
    
    // Simple modal functions
    function showModal(modalId) {
        $('#' + modalId).show();
    }
    
    function hideModal(modalId) {
        $('#' + modalId).hide();
    }
    
    // Close modal when clicking close button or outside
    $(document).on('click', '.hms-btn-close', function(e) {
        $(this).closest('.hms-modal').hide();
    });
    
    $(document).on('click', '.hms-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });

    // Initialize DataTable
    appointmentsTable = $('#hms-appointments-table').DataTable({
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function(d) {
                d.action = 'hms_get_appointments_list';
                d.nonce = '<?php echo wp_create_nonce('hms_admin_nonce'); ?>';
                d.status = '<?php echo esc_js($status_filter); ?>';
                d.doctor_id = '<?php echo esc_js($doctor_filter); ?>';
                d.patient_id = '<?php echo esc_js($patient_filter); ?>';
                d.date_from = '<?php echo esc_js($date_from_filter); ?>';
                d.date_to = '<?php echo esc_js($date_to_filter); ?>';
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'date' },
            { data: 'time' },
            { data: 'patient' },
            { data: 'doctor' },
            { data: 'department' },
            { 
                data: 'status',
                render: function(data, type, row, meta) {
                    var statusBadgeClass = '';
                    switch(data.toLowerCase()) {
                        case 'scheduled': statusBadgeClass = 'hms-badge-primary'; break;
                        case 'confirmed': statusBadgeClass = 'hms-badge-info'; break;
                        case 'in progress': statusBadgeClass = 'hms-badge-warning'; break;
                        case 'completed': statusBadgeClass = 'hms-badge-success'; break;
                        case 'cancelled': statusBadgeClass = 'hms-badge-danger'; break;
                        case 'no show': statusBadgeClass = 'hms-badge-secondary'; break;
                        default: statusBadgeClass = 'hms-badge-light';
                    }
                    return `<span class="hms-badge ${statusBadgeClass}">${data}</span>`;
                }
            },
            { 
                data: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    var statusBadgeClass = '';
                    switch(row.status.toLowerCase()) {
                        case 'scheduled': statusBadgeClass = 'hms-badge-primary'; break;
                        case 'confirmed': statusBadgeClass = 'hms-badge-info'; break;
                        case 'in progress': statusBadgeClass = 'hms-badge-warning'; break;
                        case 'completed': statusBadgeClass = 'hms-badge-success'; break;
                        case 'cancelled': statusBadgeClass = 'hms-badge-danger'; break;
                        case 'no show': statusBadgeClass = 'hms-badge-secondary'; break;
                        default: statusBadgeClass = 'hms-badge-light';
                    }
                    
                    return `
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-info hms-edit-appointment-btn" data-id="${row.id}" title="<?php esc_attr_e('Edit', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-edit"></i>
                        </button>
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-warning hms-cancel-appointment-btn" data-id="${row.id}" title="<?php esc_attr_e('Cancel', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-no-alt"></i>
                        </button>
                        <button type="button" class="hms-btn hms-btn-sm hms-btn-danger hms-delete-appointment-btn" data-id="${row.id}" title="<?php esc_attr_e('Delete', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-trash"></i>
                        </button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_appointment_details&id=')); ?>${row.id}" class="hms-btn hms-btn-sm hms-btn-primary" title="<?php esc_attr_e('View Details', 'hospital-management-system'); ?>">
                            <i class="dashicons dashicons-visibility"></i>
                        </a>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']] // Sort by date by default
    });

    // Add New Appointment button
    $('#hms-add-new-appointment-btn').on('click', function(e) {
        e.preventDefault();
        $('#hms-appointment-modal-title').text('<?php esc_html_e('Schedule New Appointment', 'hospital-management-system'); ?>');
        $('#hms-appointment-form')[0].reset();
        $('#appointment_id').val('');
        $('#hms-appointment-feedback').hide();
        showModal('hms-appointment-modal');
    });

    // Edit Appointment button (delegated)
    $('#hms-appointments-table tbody').on('click', '.hms-edit-appointment-btn', function(e) {
        e.preventDefault();
        var appointmentId = $(this).data('id');
        $('#hms-appointment-modal-title').text('<?php esc_html_e('Edit Appointment', 'hospital-management-system'); ?>');
        $('#hms-appointment-feedback').hide();
        
        // Fetch appointment data and populate form
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'hms_get_appointment',
                nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                appointment_id: appointmentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var appt = response.data;
                    $('#appointment_id').val(appt.id);
                    $('#patient_id').val(appt.patient_id);
                    $('#doctor_id').val(appt.doctor_id);
                    $('#department_id').val(appt.department_id || '');
                    $('#appointment_date').val(appt.appointment_date);
                    // Trigger time slot load for the selected doctor and date
                    $('#doctor_id').trigger('change'); 
                    // Set time after options are loaded
                    setTimeout(function() {
                        $('#appointment_time').val(appt.appointment_time);
                    }, 500);
                    $('#status').val(appt.status);
                    $('#notes').val(appt.notes);
                    showModal('hms-appointment-modal');
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Cancel Appointment button (delegated)
    $('#hms-appointments-table tbody').on('click', '.hms-cancel-appointment-btn', function(e) {
        e.preventDefault();
        var appointmentId = $(this).data('id');
        $('#cancel_appointment_id').val(appointmentId);
        showModal('hms-cancel-appointment-modal');
    });

    // Delete Appointment button (delegated)
    $('#hms-appointments-table tbody').on('click', '.hms-delete-appointment-btn', function(e) {
        e.preventDefault();
        var appointmentId = $(this).data('id');
        $('#delete_appointment_id').val(appointmentId);
        showModal('hms-delete-appointment-modal');
    });

    // Confirm Cancel button
    $('#hms-confirm-cancel-btn').on('click', function() {
        var appointmentId = $('#cancel_appointment_id').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_cancel_appointment',
                nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                appointment_id: appointmentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    hideModal('hms-cancel-appointment-modal');
                    appointmentsTable.ajax.reload();
                    // Show success message
                    alert(response.data.message);
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Confirm Delete button
    $('#hms-confirm-delete-btn').on('click', function() {
        var appointmentId = $('#delete_appointment_id').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_delete_appointment',
                nonce: '<?php echo wp_create_nonce('hms_delete_appointment_nonce'); ?>',
                appointment_id: appointmentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    hideModal('hms-delete-appointment-modal');
                    appointmentsTable.ajax.reload();
                    // Show success message
                    alert(response.data.message);
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Doctor change event to load available time slots
    $('#doctor_id, #appointment_date').on('change', function() {
        var doctorId = $('#doctor_id').val();
        var date = $('#appointment_date').val();
        var $timeSelect = $('#appointment_time');
        var currentTime = $('#appointment_time').val(); // Preserve current selection if possible

        if (doctorId && date) {
            $timeSelect.prop('disabled', true);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_doctor_availability',
                    nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                    doctor_id: doctorId,
                    date: date
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $timeSelect.empty().append('<option value=""><?php esc_html_e('Select Time', 'hospital-management-system'); ?></option>');
                        $.each(response.data, function(index, time) {
                            var timeFormatted = new Date('1970-01-01T' + time + 'Z').toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            $timeSelect.append('<option value="' + time + '">' + timeFormatted + '</option>');
                        });
                        $timeSelect.val(currentTime); // Try to re-select
                    } else {
                        $timeSelect.empty().append('<option value=""><?php esc_html_e('Error loading slots', 'hospital-management-system'); ?></option>');
                    }
                    $timeSelect.prop('disabled', false);
                },
                error: function() {
                    $timeSelect.empty().append('<option value=""><?php esc_html_e('Error loading slots', 'hospital-management-system'); ?></option>');
                    $timeSelect.prop('disabled', false);
                }
            });
        } else {
            $timeSelect.empty().append('<option value=""><?php esc_html_e('Select Time', 'hospital-management-system'); ?></option>');
        }
    });


    // Save Appointment button
    $('#hms-save-appointment-btn').on('click', function(e) {
        e.preventDefault();
        var $form = $('#hms-appointment-form');
        var $feedback = $('#hms-appointment-feedback');
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        var formData = $form.serialize();
        var action = $('#appointment_id').val() ? 'hms_update_appointment' : 'hms_add_appointment';
        formData += '&action=' + action + '&nonce=' + '<?php echo wp_create_nonce('hms_admin_nonce'); ?>';

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#hms-save-appointment-btn').prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                    appointmentsTable.ajax.reload();
                    setTimeout(function() {
                        hideModal('hms-appointment-modal');
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
                $('#hms-save-appointment-btn').prop('disabled', false).text('<?php esc_html_e('Save Appointment', 'hospital-management-system'); ?>');
            }
        });
    });
});
</script>

<style>
/* Basic styling, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-mb-3 { margin-bottom: 1rem !important; }
.hms-ms-2 { margin-left: 0.5rem !important; }
.hms-mt-3 { margin-top: 1rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-2, .hms-col-md-3, .hms-col-md-4, .hms-col-md-6, .hms-col-md-12 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) {
    .hms-col-md-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
    .hms-col-md-3 { flex: 0 0 25%; max-width: 25%; }
    .hms-col-md-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
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
.hms-btn-warning { color: #212529; background-color: #ffc107; border-color: #ffc107; }
.hms-btn-warning:hover { background-color: #e0a800; border-color: #d39e00; }
.hms-btn-sm { padding: .25rem .5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
.hms-btn-close { box-sizing: content-box; width: 1em; height: 1em; padding: .25em .25em; color: #000; background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; border: 0; border-radius: .25rem; opacity: .5; }
.hms-btn-close:hover { color: #000; text-decoration: none; opacity: .75; }
.hms-modal { position: fixed; top: 0; left: 0; z-index: 1050; display: none; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
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
.hms-align-items-end { align-items: flex-end !important; }
.hms-badge { display: inline-block; padding: .25em .4em; font-size: .75em; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
.hms-badge-primary { color: #fff; background-color: #007bff; }
.hms-badge-secondary { color: #fff; background-color: #6c757d; }
.hms-badge-success { color: #fff; background-color: #28a745; }
.hms-badge-danger { color: #fff; background-color: #dc3545; }
.hms-badge-warning { color: #212529; background-color: #ffc107; }
.hms-badge-info { color: #fff; background-color: #17a2b8; }
.hms-badge-light { color: #212529; background-color: #f8f9fa; }
</style>
