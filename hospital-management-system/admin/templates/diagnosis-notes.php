<?php
/**
 * Admin template for creating and managing diagnosis and progress notes.
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

$clinical_obj = new HMS_Clinical();

// Get appointment ID from URL
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

if (empty($appointment_id)) {
    wp_die(esc_html__('Appointment ID is required.', 'hospital-management-system'));
}

// Get appointment details
$appointment_obj = new HMS_Appointment();
$appointment = $appointment_obj->get_appointment($appointment_id);

if (!$appointment) {
    wp_die(__('Appointment not found.', 'hospital-management-system'));
}

// Get patient and staff info for display
$patient_obj = new HMS_Patient();
$patient = $patient_obj->get_patient($appointment->patient_id);
$doctor_user = get_user_by('ID', $appointment->doctor_id);
$doctor_name = $doctor_user ? $doctor_user->display_name : __('N/A', 'hospital-management-system');

// Get existing diagnosis notes for this appointment
$notes = $clinical_obj->get_diagnosis_notes($appointment_id);
$existing_note = null;
if (!empty($notes)) {
    // Assuming one primary set of notes per appointment for simplicity
    $existing_note = $notes[0]; 
}

?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Diagnosis & Progress Notes', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-appointment-details&id=' . $appointment_id)); ?>" class="page-title-action"><?php esc_html_e('Back to Appointment Details', 'hospital-management-system'); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-appointments')); ?>" class="page-title-action"><?php esc_html_e('Back to Appointments', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <!-- Appointment & Patient Summary Card -->
    <div class="hms-card hms-mb-3">
        <div class="hms-card-body">
            <div class="hms-row">
                <div class="hms-col-md-6">
                    <h4><?php esc_html_e('Appointment Information', 'hospital-management-system'); ?></h4>
                    <p><strong><?php esc_html_e('Appointment ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($appointment->appointment_id); ?></p>
                    <p><strong><?php esc_html_e('Date & Time:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time))); ?></p>
                    <p><strong><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></strong> <?php echo esc_html($doctor_name); ?></p>
                    <p><strong><?php esc_html_e('Status:', 'hospital-management-system'); ?></strong> <span class="hms-badge hms-badge-<?php echo esc_attr($appointment->status); ?>"><?php echo esc_html(ucfirst($appointment->status)); ?></span></p>
                </div>
                <div class="hms-col-md-6">
                    <h4><?php esc_html_e('Patient Information', 'hospital-management-system'); ?></h4>
                    <p><strong><?php esc_html_e('Patient ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->patient_id); ?></p>
                    <p><strong><?php esc_html_e('Name:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?></p>
                    <p><strong><?php esc_html_e('Age/Gender:', 'hospital-management-system'); ?></strong> <?php echo esc_html(calculate_age($patient->date_of_birth) . ' / ' . $patient->gender); ?></p>
                    <p><strong><?php esc_html_e('Blood Group:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->blood_group ?: 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnosis Notes Form -->
    <div class="hms-card">
        <div class="hms-card-header">
            <h4 class="hms-mb-0"><?php esc_html_e('Clinical Notes', 'hospital-management-system'); ?></h4>
        </div>
        <div class="hms-card-body">
            <form id="hms-diagnosis-notes-form">
                <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment_id); ?>">
                <?php if ($existing_note) : ?>
                    <input type="hidden" name="note_id" value="<?php echo esc_attr($existing_note->id); ?>">
                <?php endif; ?>
                <?php wp_nonce_field('hms_admin_nonce', 'diagnosis_notes_nonce'); ?>
                
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-12">
                        <label for="diagnosis"><?php esc_html_e('Diagnosis*', 'hospital-management-system'); ?></label>
                        <textarea name="diagnosis" id="diagnosis" class="hms-form-control" rows="4" required><?php echo $existing_note ? esc_textarea($existing_note->diagnosis) : ''; ?></textarea>
                    </div>
                </div>
                    <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-12">
                        <label for="symptoms"><?php esc_html_e('Symptoms / History', 'hospital-management-system'); ?></label>
                        <textarea name="symptoms" id="symptoms" class="hms-form-control" rows="3"><?php echo $existing_note ? esc_textarea($existing_note->symptoms) : ''; ?></textarea>
                    </div>
                </div>
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-12">
                        <label for="treatment_plan"><?php esc_html_e('Treatment Plan / Progress Notes*', 'hospital-management-system'); ?></label>
                        <textarea name="treatment_plan" id="treatment_plan" class="hms-form-control" rows="5" required><?php echo $existing_note ? esc_textarea($existing_note->treatment_plan) : ''; ?></textarea>
                    </div>
                </div>
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-6">
                        <label for="follow_up_date"><?php esc_html_e('Follow-up Date', 'hospital-management-system'); ?></label>
                        <input type="date" name="follow_up_date" id="follow_up_date" class="hms-form-control" value="<?php echo $existing_note && $existing_note->follow_up_date ? esc_attr(gmdate('Y-m-d', strtotime($existing_note->follow_up_date))) : ''; ?>">
                    </div>
                    <div class="hms-col-md-6">
                        <label for="status"><?php esc_html_e('Condition Status', 'hospital-management-system'); ?></label>
                        <select name="status" id="status" class="hms-form-control">
                            <option value="critical" <?php echo ($existing_note && $existing_note->status === 'critical') ? 'selected' : ''; ?>><?php esc_html_e('Critical', 'hospital-management-system'); ?></option>
                            <option value="serious" <?php echo ($existing_note && $existing_note->status === 'serious') ? 'selected' : ''; ?>><?php esc_html_e('Serious', 'hospital-management-system'); ?></option>
                            <option valuestable" <?php echo ($existing_note && $existing_note->status === 'stable') ? 'selected' : ''; ?>><?php esc_html_e('Stable', 'hospital-management-system'); ?></option>
                            <option value="improving" <?php echo ($existing_note && $existing_note->status === 'improving') ? 'selected' : ''; ?>><?php esc_html_e('Improving', 'hospital-management-system'); ?></option>
                            <option value="recovered" <?php echo ($existing_note && $existing_note->status === 'recovered') ? 'selected' : ''; ?>><?php esc_html_e('Recovered', 'hospital-management-system'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div id="hms-diagnosis-notes-feedback" class="hms-feedback hms-mt-3" style="display: none;"></div>

                <div class="hms-mt-4">
                    <button type="submit" class="hms-btn hms-btn-primary" id="hms-save-notes-btn">
                        <?php echo $existing_note ? esc_html__('Update Notes', 'hospital-management-system') : esc_html__('Save Notes', 'hospital-management-system'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var $form = $('#hms-diagnosis-notes-form');
    var $feedback = $('#hms-diagnosis-notes-feedback');
    var $saveBtn = $('#hms-save-notes-btn');

    $form.on('submit', function(e) {
        e.preventDefault();
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        var formData = $form.serializeArray();
        var action = $form.find('input[name="note_id"]').length ? 'hms_update_diagnosis_notes' : 'hms_create_diagnosis_notes';
        
        // Add action and nonce to formData
        formData.push({name: 'action', value: action});
        formData.push({name: 'nonce', value: '<?php echo esc_js(wp_create_nonce('hms_admin_nonce')); ?>'});

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: $.param(formData),
            dataType: 'json',
            beforeSend: function() {
                $saveBtn.prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                    // If it was a create action, we might want to add the hidden note_id field
                    if (action === 'hms_create_diagnosis_notes' && response.data.note_id) {
                        $form.append('<input type="hidden" name="note_id" value="' + response.data.note_id + '">');
                        $saveBtn.text('<?php esc_html_e('Update Notes', 'hospital-management-system'); ?>');
                    }
                } else {
                    $feedback.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                }
            },
            error: function(xhr) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : '<?php esc_html_e('An unexpected error occurred.', 'hospital-management-system'); ?>';
                $feedback.addClass('notice notice-error').html('<p>' + errorMessage + '</p>').show();
            },
            complete: function() {
                $saveBtn.prop('disabled', false).text('<?php echo $existing_note ? esc_html__('Update Notes', 'hospital-management-system') : esc_html__('Save Notes', 'hospital-management-system'); ?>');
            }
        });
    });
});

// Helper function to calculate age (can be moved to a global JS file or PHP function)
function calculate_age(dobString) {
    var dob = new Date(dobString);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var monthDiff = today.getMonth() - dob.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    return age + ' years';
}
</script>

<style>
/* Basic styling, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-mb-0 { margin-bottom: 0 !important; }
.hms-mb-3 { margin-bottom: 1rem !important; }
.hms-mt-3 { margin-top: 1rem !important; }
.hms-mt-4 { margin-top: 1.5rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-6 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) { .hms-col-md-6 { flex: 0 0 50%; max-width: 50%; } }
.hms-col-md-12 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-header { padding: 1rem 1rem; border-bottom: 1px solid #dee2e6; border-top-left-radius: calc(.3rem - 1px); border-top-right-radius: calc(.3rem - 1px); background-color: #f8f9fa; }
.hms-card-body { padding: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-feedback { margin-top: 1rem; }
.hms-text-center { text-align: center !important; }
.hms-badge { display: inline-block; padding: .25em .4em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
.hms-badge-scheduled { color: #fff; background-color: #17a2b8; }
.hms-badge-confirmed { color: #212529; background-color: #ffc107; }
.hms-badge-in-progress { color: #fff; background-color: #007bff; }
.hms-badge-completed { color: #fff; background-color: #28a745; }
.hms-badge-cancelled { color: #fff; background-color: #dc3545; }
.hms-badge-no-show { color: #fff; background-color: #6c757d; }
</style>

<?php
// Helper function to calculate age (can be moved to includes/functions.php)
if (!function_exists('calculate_age')) {
    function calculate_age($dob) {
        $birthDate = new DateTime($dob);
        $today = new DateTime("today");
        if ($birthDate > $today) {
            return "0 years";
        }
        $y = $today->diff($birthDate)->y;
        $m = $today->diff($birthDate)->m;
        $d = $today->diff($birthDate)->d;
        return $y . " years";
    }
}
?>
