<?php
/**
 * Admin template for medical record details (add/edit).
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

// Ensure the HMS_Medical_Records class is available
if (!class_exists('HMS_Medical_Records')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
}

$medical_records = new HMS_Medical_Records();

// Get action and record ID
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'add';
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$preselected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Initialize record data
$record = null;
$page_title = __('Add New Medical Record', 'hospital-management-system');

if ($action === 'edit' || $action === 'view') {
    if ($record_id > 0) {
        $record = $medical_records->get_medical_record($record_id);
        if ($record) {
            $page_title = $action === 'view' 
                ? __('View Medical Record', 'hospital-management-system')
                : __('Edit Medical Record', 'hospital-management-system');
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Medical record not found.', 'hospital-management-system') . '</p></div>';
            return;
        }
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__('Invalid record ID.', 'hospital-management-system') . '</p></div>';
        return;
    }
}

// Get patients and doctors for dropdowns
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}
if (!class_exists('HMS_Staff')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-staff.php';
}

$patient_obj = new HMS_Patient();
$staff_obj = new HMS_Staff();
$patients = $patient_obj->get_patients();
$doctors = $staff_obj->get_staff_by_role('doctor');

// Get appointments for the selected patient (if editing)
$appointments = array();
if ($record && $record->patient_id) {
    if (!class_exists('HMS_Appointments')) {
        require_once HMS_PLUGIN_DIR . 'includes/class-hms-appointments.php';
    }
    $appointments_obj = new HMS_Appointments();
    $appointments = $appointments_obj->get_patient_appointments($record->patient_id);
}

$readonly = ($action === 'view') ? 'readonly' : '';
$disabled = ($action === 'view') ? 'disabled' : '';
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_records')); ?>" class="page-title-action"><?php esc_html_e('&larr; Back to Medical Records', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <?php
    // Display messages
    if (isset($_GET['message']) && isset($_GET['type'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = sanitize_text_field($_GET['type']);
        $class = $type === 'success' ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <div class="hms-form-container">
        <form method="post" action="" id="medical-record-form">
            <?php wp_nonce_field('hms_medical_record_nonce', 'medical_record_nonce'); ?>
            
            <?php if ($record) : ?>
                <input type="hidden" name="record_id" value="<?php echo esc_attr($record->id); ?>">
            <?php endif; ?>

            <div class="hms-form-section">
                <h2><?php esc_html_e('Basic Information', 'hospital-management-system'); ?></h2>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-form-group-half">
                        <label for="patient_id" class="required"><?php esc_html_e('Patient:', 'hospital-management-system'); ?></label>
                        <select name="patient_id" id="patient_id" required <?php echo esc_attr($disabled); ?>>
                            <option value=""><?php esc_html_e('-- Select Patient --', 'hospital-management-system'); ?></option>
                            <?php foreach ($patients as $patient) : ?>
                                <option value="<?php echo esc_attr($patient->id); ?>" 
                                        <?php selected($record ? $record->patient_id : $preselected_patient_id, $patient->id); ?>>
                                    <?php echo esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hms-form-group hms-form-group-half">
                        <label for="doctor_id" class="required"><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></label>
                        <select name="doctor_id" id="doctor_id" required <?php echo esc_attr($disabled); ?>>
                            <option value=""><?php esc_html_e('-- Select Doctor --', 'hospital-management-system'); ?></option>
                            <?php foreach ($doctors as $doctor) : ?>
                                <option value="<?php echo esc_attr($doctor->id); ?>" 
                                        <?php selected($record ? $record->doctor_id : 0, $doctor->id); ?>>
                                    <?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-form-group-half">
                        <label for="appointment_id"><?php esc_html_e('Related Appointment:', 'hospital-management-system'); ?></label>
                        <select name="appointment_id" id="appointment_id" <?php echo esc_attr($disabled); ?>>
                            <option value=""><?php esc_html_e('-- No Related Appointment --', 'hospital-management-system'); ?></option>
                            <?php foreach ($appointments as $appointment) : ?>
                                <option value="<?php echo esc_attr($appointment->id); ?>" 
                                        <?php selected($record ? $record->appointment_id : 0, $appointment->id); ?>>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)) . ' - ' . $appointment->appointment_time); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hms-form-group hms-form-group-half">
                        <label for="record_type" class="required"><?php esc_html_e('Record Type:', 'hospital-management-system'); ?></label>
                        <select name="record_type" id="record_type" required <?php echo esc_attr($disabled); ?>>
                            <option value=""><?php esc_html_e('-- Select Type --', 'hospital-management-system'); ?></option>
                            <option value="consultation" <?php selected($record ? $record->record_type : '', 'consultation'); ?>><?php esc_html_e('Consultation', 'hospital-management-system'); ?></option>
                            <option value="follow_up" <?php selected($record ? $record->record_type : '', 'follow_up'); ?>><?php esc_html_e('Follow-up', 'hospital-management-system'); ?></option>
                            <option value="emergency" <?php selected($record ? $record->record_type : '', 'emergency'); ?>><?php esc_html_e('Emergency', 'hospital-management-system'); ?></option>
                            <option value="surgery" <?php selected($record ? $record->record_type : '', 'surgery'); ?>><?php esc_html_e('Surgery', 'hospital-management-system'); ?></option>
                            <option value="discharge" <?php selected($record ? $record->record_type : '', 'discharge'); ?>><?php esc_html_e('Discharge', 'hospital-management-system'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="hms-form-section">
                <h2><?php esc_html_e('Clinical Information', 'hospital-management-system'); ?></h2>
                
                <div class="hms-form-row">
                    <div class="hms-form-group">
                        <label for="symptoms"><?php esc_html_e('Symptoms:', 'hospital-management-system'); ?></label>
                        <textarea name="symptoms" id="symptoms" rows="4" <?php echo esc_attr($readonly); ?>><?php echo $record ? esc_textarea($record->symptoms) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e('Describe the patient\'s presenting symptoms and complaints.', 'hospital-management-system'); ?></p>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group">
                        <label for="diagnosis"><?php esc_html_e('Diagnosis:', 'hospital-management-system'); ?></label>
                        <textarea name="diagnosis" id="diagnosis" rows="4" <?php echo esc_attr($readonly); ?>><?php echo $record ? esc_textarea($record->diagnosis) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e('Enter the medical diagnosis or assessment.', 'hospital-management-system'); ?></p>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group">
                        <label for="treatment_plan"><?php esc_html_e('Treatment Plan:', 'hospital-management-system'); ?></label>
                        <textarea name="treatment_plan" id="treatment_plan" rows="4" <?php echo esc_attr($readonly); ?>><?php echo $record ? esc_textarea($record->treatment_plan) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e('Outline the recommended treatment plan and procedures.', 'hospital-management-system'); ?></p>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group">
                        <label for="notes"><?php esc_html_e('Additional Notes:', 'hospital-management-system'); ?></label>
                        <textarea name="notes" id="notes" rows="6" <?php echo esc_attr($readonly); ?>><?php echo $record ? esc_textarea($record->notes) : ''; ?></textarea>
                        <p class="description"><?php esc_html_e('Any additional clinical notes, observations, or follow-up instructions.', 'hospital-management-system'); ?></p>
                    </div>
                </div>
            </div>

            <?php if ($record) : ?>
                <div class="hms-form-section">
                    <h2><?php esc_html_e('Record Information', 'hospital-management-system'); ?></h2>
                    
                    <div class="hms-form-row">
                        <div class="hms-form-group hms-form-group-half">
                            <label><?php esc_html_e('Created:', 'hospital-management-system'); ?></label>
                            <p class="hms-readonly-field">
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->created_at))); ?>
                            </p>
                        </div>
                        
                        <?php if ($record->updated_at && $record->updated_at !== $record->created_at) : ?>
                            <div class="hms-form-group hms-form-group-half">
                                <label><?php esc_html_e('Last Updated:', 'hospital-management-system'); ?></label>
                                <p class="hms-readonly-field">
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->updated_at))); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($action !== 'view') : ?>
                <div class="hms-form-actions">
                    <button type="submit" name="save_medical_record" class="button button-primary">
                        <?php echo $record ? esc_html__('Update Medical Record', 'hospital-management-system') : esc_html__('Save Medical Record', 'hospital-management-system'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_records')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'hospital-management-system'); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="hms-form-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=edit&id=' . $record->id)); ?>" class="button button-primary">
                        <?php esc_html_e('Edit Record', 'hospital-management-system'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_records')); ?>" class="button">
                        <?php esc_html_e('Back to Records', 'hospital-management-system'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Load appointments when patient is selected
    $('#patient_id').on('change', function() {
        var patientId = $(this).val();
        var $appointmentSelect = $('#appointment_id');
        
        // Clear existing options except the first one
        $appointmentSelect.find('option:not(:first)').remove();
        
        if (patientId) {
            // Show loading state
            $appointmentSelect.prop('disabled', true);
            $appointmentSelect.append('<option value=""><?php esc_html_e('Loading appointments...', 'hospital-management-system'); ?></option>');
            
            // Load appointments via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_patient_appointments',
                    nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                    patient_id: patientId
                },
                success: function(response) {
                    $appointmentSelect.find('option:not(:first)').remove();
                    $appointmentSelect.prop('disabled', false);
                    
                    if (response.success && response.data.appointments) {
                        $.each(response.data.appointments, function(index, appointment) {
                            var optionText = appointment.appointment_date + ' - ' + appointment.appointment_time;
                            $appointmentSelect.append('<option value="' + appointment.id + '">' + optionText + '</option>');
                        });
                    }
                },
                error: function() {
                    $appointmentSelect.find('option:not(:first)').remove();
                    $appointmentSelect.prop('disabled', false);
                }
            });
        }
    });
    
    // Form validation
    $('#medical-record-form').on('submit', function(e) {
        var patientId = $('#patient_id').val();
        var doctorId = $('#doctor_id').val();
        var recordType = $('#record_type').val();
        
        if (!patientId) {
            alert('<?php esc_html_e('Please select a patient.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (!doctorId) {
            alert('<?php esc_html_e('Please select a doctor.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
        
        if (!recordType) {
            alert('<?php esc_html_e('Please select a record type.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
.hms-form-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}

.hms-form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.hms-form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 20px;
}

.hms-form-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 18px;
    color: #333;
}

.hms-form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.hms-form-row:last-child {
    margin-bottom: 0;
}

.hms-form-group {
    flex: 1;
    min-width: 250px;
}

.hms-form-group-half {
    flex: 0 0 calc(50% - 10px);
}

.hms-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.hms-form-group label.required::after {
    content: ' *';
    color: #d63384;
}

.hms-form-group input,
.hms-form-group select,
.hms-form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.4;
}

.hms-form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.hms-form-group input:focus,
.hms-form-group select:focus,
.hms-form-group textarea:focus {
    border-color: #007cba;
    box-shadow: 0 0 0 1px #007cba;
    outline: none;
}

.hms-form-group input[readonly],
.hms-form-group select[disabled],
.hms-form-group textarea[readonly] {
    background-color: #f6f7f7;
    color: #666;
}

.hms-form-group .description {
    margin-top: 5px;
    margin-bottom: 0;
    font-size: 13px;
    color: #666;
    font-style: italic;
}

.hms-readonly-field {
    padding: 8px 12px;
    background-color: #f6f7f7;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 0;
    color: #666;
}

.hms-form-actions {
    padding-top: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

@media (max-width: 768px) {
    .hms-form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .hms-form-group-half {
        flex: 1;
    }
    
    .hms-form-actions {
        flex-direction: column;
    }
}
</style>