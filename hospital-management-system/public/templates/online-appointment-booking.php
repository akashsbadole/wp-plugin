<?php
/**
 * Template for Online Appointment Booking.
 *
 * This template can be overridden by copying it to yourtheme/hospital-management-system/public/templates/online-appointment-booking.php
 *
 * @package Hospital_Management_System
 * @subpackage Public/Templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if user is logged in and is a patient to pre-fill some data
$current_user = wp_get_current_user();
$is_patient = (is_user_logged_in() && in_array('hms_patient', (array) $current_user->roles));
$patient_id = 0;
if ($is_patient) {
    // Assuming a function to get patient ID from user ID exists in HMS_Patient_Portal or a helper
    $hms_patient_portal = new HMS_Patient_Portal();
    $patient_id = $hms_patient_portal->get_current_patient_id();
}
?>
<div class="hms-appointment-booking-container">
    <h2><?php esc_html_e('Book an Appointment', 'hospital-management-system'); ?></h2>
    
    <form id="hms-appointment-booking-form" method="post">
        <!-- Patient Details (can be hidden/auto-filled if logged in) -->
        <fieldset class="hms-appointment-section">
            <legend><?php esc_html_e('Patient Information', 'hospital-management-system'); ?></legend>
            <?php if ($is_patient && $patient_id) : ?>
                <?php 
                /* translators: %s: User display name */
                printf(esc_html__('Logged in as: %s', 'hospital-management-system'), esc_html($current_user->display_name)); 
                ?>
                <input type="hidden" name="patient_id" id="hms_patient_id" value="<?php echo esc_attr($patient_id); ?>">
            <?php else : ?>
                <p class="hms-form-field">
                    <label for="hms_patient_name"><?php esc_html_e('Full Name', 'hospital-management-system'); ?> <span class="required">*</span></label>
                    <input type="text" name="patient_name" id="hms_patient_name" class="hms-input" required>
                </p>
                <p class="hms-form-field">
                    <label for="hms_patient_email"><?php esc_html_e('Email Address', 'hospital-management-system'); ?> <span class="required">*</span></label>
                    <input type="email" name="patient_email" id="hms_patient_email" class="hms-input" required>
                </p>
                <p class="hms-form-field">
                    <label for="hms_patient_phone"><?php esc_html_e('Phone Number', 'hospital-management-system'); ?> <span class="required">*</span></label>
                    <input type="tel" name="patient_phone" id="hms_patient_phone" class="hms-input" required>
                </p>
            <?php endif; ?>
        </fieldset>

        <!-- Appointment Details -->
        <fieldset class="hms-appointment-section">
            <legend><?php esc_html_e('Appointment Details', 'hospital-management-system'); ?></legend>
            <p class="hms-form-field">
                <label for="hms_department_id"><?php esc_html_e('Department', 'hospital-management-system'); ?> <span class="required">*</span></label>
                <?php
                // Fetch departments from the database
                global $wpdb;
                $departments = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}hms_departments WHERE status = 'active' ORDER BY name ASC");
                ?>
                <select name="department_id" id="hms_department_id" class="hms-select" required>
                    <option value=""><?php esc_html_e('Select Department', 'hospital-management-system'); ?></option>
                    <?php foreach ($departments as $dept) : ?>
                        <option value="<?php echo esc_attr($dept->id); ?>"><?php echo esc_html($dept->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="hms-form-field">
                <label for="hms_doctor_id"><?php esc_html_e('Doctor (Optional)', 'hospital-management-system'); ?></label>
                <select name="doctor_id" id="hms_doctor_id" class="hms-select">
                    <option value=""><?php esc_html_e('Select Doctor', 'hospital-management-system'); ?></option>
                    <!-- Options will be populated via AJAX based on department selection -->
                </select>
            </p>
            <p class="hms-form-field">
                <label for="hms_appointment_date"><?php esc_html_e('Preferred Date', 'hospital-management-system'); ?> <span class="required">*</span></label>
                <input type="date" name="appointment_date" id="hms_appointment_date" class="hms-input" required min="<?php echo gmdate('Y-m-d'); ?>">
            </p>
            <p class="hms-form-field">
                <label for="hms_appointment_time"><?php esc_html_e('Preferred Time', 'hospital-management-system'); ?> <span class="required">*</span></label>
                <input type="time" name="appointment_time" id="hms_appointment_time" class="hms-input" required>
            </p>
             <p class="hms-form-field">
                <label for="hms_appointment_type"><?php esc_html_e('Appointment Type', 'hospital-management-system'); ?></label>
                <select name="appointment_type" id="hms_appointment_type" class="hms-select">
                    <option value="in-person"><?php esc_html_e('In-Person', 'hospital-management-system'); ?></option>
                    <option value="telemedicine"><?php esc_html_e('Telemedicine', 'hospital-management-system'); ?></option>
                </select>
            </p>
        </fieldset>

        <!-- Reason for Visit -->
        <fieldset class="hms-appointment-section">
            <legend><?php esc_html_e('Reason for Visit', 'hospital-management-system'); ?></legend>
            <p class="hms-form-field">
                <label for="hms_appointment_reason"><?php esc_html_e('Please describe your symptoms or reason for the appointment', 'hospital-management-system'); ?></label>
                <textarea name="appointment_reason" id="hms_appointment_reason" class="hms-textarea" rows="4"></textarea>
            </p>
        </fieldset>

        <p class="hms-form-submit">
            <?php wp_nonce_field('hms_book_appointment_nonce', 'hms_appointment_nonce'); ?>
            <input type="hidden" name="action" value="hms_patient_book_appointment">
            <button type="submit" class="hms-button hms-button-primary"><?php esc_html_e('Request Appointment', 'hospital-management-system'); ?></button>
            <span id="hms-booking-loader" class="hms-loader" style="display: none;"><?php esc_html_e('Processing...', 'hospital-management-system'); ?></span>
        </p>
    </form>

    <div id="hms-booking-message" class="hms-message" style="display: none;"></div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Populate doctors based on department selection
    $('#hms_department_id').on('change', function() {
        var department_id = $(this).val();
        var doctor_select = $('#hms_doctor_id');
        doctor_select.empty().append('<option value=""><?php esc_html_e('Loading...', 'hospital-management-system'); ?></option>');

        if (department_id) {
            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'hms_get_doctors_by_department',
                    department_id: department_id,
                    nonce: hms_ajax_obj.nonce
                },
                success: function(response) {
                    if (response.success) {
                        doctor_select.empty().append('<option value=""><?php esc_html_e('Select Doctor', 'hospital-management-system'); ?></option>');
                        $.each(response.data, function(index, doctor) {
                            doctor_select.append('<option value="' + doctor.id + '">' + doctor.first_name + ' ' + doctor.last_name + ' (' + doctor.staff_id + ')</option>');
                        });
                    } else {
                        doctor_select.empty().append('<option value=""><?php esc_html_e('No doctors found', 'hospital-management-system'); ?></option>');
                    }
                },
                error: function() {
                    doctor_select.empty().append('<option value=""><?php esc_html_e('Error loading doctors', 'hospital-management-system'); ?></option>');
                }
            });
        } else {
            doctor_select.empty().append('<option value=""><?php esc_html_e('Select Department First', 'hospital-management-system'); ?></option>');
        }
    });

    // Handle form submission via AJAX
    $('#hms-appointment-booking-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var loader = $('#hms-booking-loader');
        var messageDiv = $('#hms-booking-message');

        submitButton.prop('disabled', true);
        loader.show();
        messageDiv.hide().removeClass('hms-success hms-error').empty();

        $.ajax({
            url: hms_ajax_obj.ajax_url,
            type: 'POST',
            data: form.serialize() + '&action=hms_patient_book_appointment&nonce=' + hms_ajax_obj.nonce,
            success: function(response) {
                if (response.success) {
                    messageDiv.addClass('hms-success').html(response.data.message).show();
                    form[0].reset(); // Reset form fields
                    // Optionally, redirect to a confirmation page or dashboard
                    // window.location.href = response.data.redirect_url;
                } else {
                    messageDiv.addClass('hms-error').html(response.data.message).show();
                }
            },
            error: function(xhr, status, error) {
                messageDiv.addClass('hms-error').html('<?php esc_html_e('An unexpected error occurred. Please try again.', 'hospital-management-system'); ?>').show();
            },
            complete: function() {
                submitButton.prop('disabled', false);
                loader.hide();
            }
        });
    });
});
</script>
