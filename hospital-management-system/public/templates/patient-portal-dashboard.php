<?php
/**
 * Template for Patient Portal Dashboard.
 *
 * This template can be overridden by copying it to yourtheme/hospital-management-system/public/templates/patient-portal-dashboard.php
 *
 * @package Hospital_Management_System
 * @subpackage Public/Templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure user is logged in and is a patient. This check is also done in the shortcode renderer.
if (!is_user_logged_in() || !in_array('hms_patient', wp_get_current_user()->roles)) {
    echo '<p>' . esc_html__('You do not have permission to view this page.', 'hospital-management-system') . '</p>';
    return;
}

// Get patient ID
$hms_patient_portal = new HMS_Patient_Portal();
$patient_id = $hms_patient_portal->get_current_patient_id();
$current_user = wp_get_current_user();

if (!$patient_id) {
    echo '<p>' . esc_html__('Your patient profile could not be found. Please contact support.', 'hospital-management-system') . '</p>';
    return;
}
?>
<div class="hms-patient-portal-dashboard-container">
    <header class="hms-dashboard-header">
        <?php 
        /* translators: %s: Patient display name */
        printf(esc_html__('Welcome, %s!', 'hospital-management-system'), esc_html($current_user->display_name)); 
        ?>
        <p><?php esc_html_e('Here you can manage your appointments, view bills, access medical records, and update your profile.', 'hospital-management-system'); ?></p>
    </header>

    <div class="hms-dashboard-content">
        <!-- Upcoming Appointments -->
        <section class="hms-dashboard-section hms-appointments-summary">
            <h2><?php esc_html_e('Upcoming Appointments', 'hospital-management-system'); ?></h2>
            <div id="hms-upcoming-appointments-list">
                <?php // Placeholder for AJAX loading of appointments ?>
                <p class="hms-loading-text"><?php esc_html_e('Loading appointments...', 'hospital-management-system'); ?></p>
            </div>
            <a href="#" class="hms-button hms-button-secondary" id="hms-book-new-appointment-btn"><?php esc_html_e('Book New Appointment', 'hospital-management-system'); ?></a>
        </section>

        <!-- Recent Bills -->
        <section class="hms-dashboard-section hms-bills-summary">
            <h2><?php esc_html_e('Recent Bills & Payments', 'hospital-management-system'); ?></h2>
            <div id="hms-recent-bills-list">
                <?php // Placeholder for AJAX loading of bills ?>
                <p class="hms-loading-text"><?php esc_html_e('Loading bills...', 'hospital-management-system'); ?></p>
            </div>
            <a href="#" class="hms-button hms-button-secondary" id="hms-view-all-bills-btn"><?php esc_html_e('View All Bills', 'hospital-management-system'); ?></a>
        </section>

        <!-- Medical Records Summary -->
        <section class="hms-dashboard-section hms-records-summary">
            <h2><?php esc_html_e('Medical Records', 'hospital-management-system'); ?></h2>
            <div id="hms-medical-records-list">
                <?php // Placeholder for AJAX loading of records ?>
                <p class="hms-loading-text"><?php esc_html_e('Loading records...', 'hospital-management-system'); ?></p>
            </div>
            <a href="#" class="hms-button hms-button-secondary" id="hms-view-all-records-btn"><?php esc_html_e('View All Records', 'hospital-management-system'); ?></a>
        </section>

        <!-- Profile Management -->
        <section class="hms-dashboard-section hms-profile-management">
            <h2><?php esc_html_e('Manage Profile', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('Keep your contact information and preferences up to date.', 'hospital-management-system'); ?></p>
            <div class="hms-profile-actions">
                <a href="#" class="hms-button hms-button-secondary" id="hms-edit-profile-btn"><?php esc_html_e('Edit Profile', 'hospital-management-system'); ?></a>
                <a href="#" class="hms-button hms-button-secondary" id="hms-change-password-btn"><?php esc_html_e('Change Password', 'hospital-management-system'); ?></a>
            </div>
        </section>
    </div>

    <!-- Modal for detailed views (e.g., full appointment list, bill details) -->
    <div id="hms-dashboard-modal" class="hms-modal" style="display: none;">
        <div class="hms-modal-content">
            <span class="hms-modal-close">&times;</span>
            <div id="hms-modal-body">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var patient_id = <?php echo json_encode($patient_id); ?>;
    var ajax_url = hms_ajax_obj.ajax_url;
    var nonce = hms_ajax_obj.nonce;

    // Function to load content into a modal
    function loadModalContent(title, contentHtml) {
        $('#hms-modal-body').html('<h2>' + title + '</h2>' + contentHtml);
        $('#hms-dashboard-modal').show();
    }

    // Function to load content via AJAX and display in modal
    function loadAjaxContentInModal(action, title, data) {
        $('#hms-modal-body').html('<p class="hms-loading-text"><?php esc_html_e('Loading...', 'hospital-management-system'); ?></p>');
        $('#hms-dashboard-modal').show();

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: $.extend({ action: action, patient_id: patient_id, nonce: nonce }, data),
            success: function(response) {
                if (response.success) {
                    $('#hms-modal-body').html(response.data);
                } else {
                    $('#hms-modal-body').html('<p class="hms-error-message">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#hms-modal-body').html('<p class="hms-error-message"><?php esc_html_e('An error occurred. Please try again.', 'hospital-management-system'); ?></p>');
            }
        });
    }

    // Load Upcoming Appointments
    function loadUpcomingAppointments() {
        $('#hms-upcoming-appointments-list').html('<p class="hms-loading-text"><?php esc_html_e('Loading appointments...', 'hospital-management-system'); ?></p>');
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: { action: 'hms_patient_get_upcoming_appointments', patient_id: patient_id, nonce: nonce },
            success: function(response) {
                if (response.success) {
                    $('#hms-upcoming-appointments-list').html(response.data);
                } else {
                    $('#hms-upcoming-appointments-list').html('<p class="hms-error-message">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#hms-upcoming-appointments-list').html('<p class="hms-error-message"><?php esc_html_e('Failed to load appointments.', 'hospital-management-system'); ?></p>');
            }
        });
    }

    // Load Recent Bills
    function loadRecentBills() {
        $('#hms-recent-bills-list').html('<p class="hms-loading-text"><?php esc_html_e('Loading bills...', 'hospital-management-system'); ?></p>');
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: { action: 'hms_patient_get_recent_bills', patient_id: patient_id, nonce: nonce },
            success: function(response) {
                if (response.success) {
                    $('#hms-recent-bills-list').html(response.data);
                } else {
                    $('#hms-recent-bills-list').html('<p class="hms-error-message">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#hms-recent-bills-list').html('<p class="hms-error-message"><?php esc_html_e('Failed to load bills.', 'hospital-management-system'); ?></p>');
            }
        });
    }

    // Load Medical Records Summary
    function loadMedicalRecordsSummary() {
        $('#hms-medical-records-list').html('<p class="hms-loading-text"><?php esc_html_e('Loading records...', 'hospital-management-system'); ?></p>');
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: { action: 'hms_patient_get_medical_records_summary', patient_id: patient_id, nonce: nonce },
            success: function(response) {
                if (response.success) {
                    $('#hms-medical-records-list').html(response.data);
                } else {
                    $('#hms-medical-records-list').html('<p class="hms-error-message">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#hms-medical-records-list').html('<p class="hms-error-message"><?php esc_html_e('Failed to load records.', 'hospital-management-system'); ?></p>');
            }
        });
    }

    // Initial load of dashboard sections
    loadUpcomingAppointments();
    loadRecentBills();
    loadMedicalRecordsSummary();

    // Event Listeners
    $('#hms-book-new-appointment-btn').on('click', function(e) {
        e.preventDefault();
        // Redirect to appointment booking page or load form in modal
        window.location.href = '<?php echo esc_url(get_permalink(get_page_by_path('book-appointment'))); // Adjust page slug ?>';
    });

    $('#hms-view-all-bills-btn').on('click', function(e) {
        e.preventDefault();
        loadAjaxContentInModal('hms_patient_view_bills', '<?php esc_html_e('All Bills', 'hospital-management-system'); ?>');
    });
    
    $('#hms-view-all-records-btn').on('click', function(e) {
        e.preventDefault();
        loadAjaxContentInModal('hms_patient_view_records', '<?php esc_html_e('All Medical Records', 'hospital-management-system'); ?>');
    });

    $('#hms-edit-profile-btn').on('click', function(e) {
        e.preventDefault();
        loadAjaxContentInModal('hms_patient_update_profile_form', '<?php esc_html_e('Edit Profile', 'hospital-management-system'); ?>');
    });

    $('#hms-change-password-btn').on('click', function(e) {
        e.preventDefault();
        loadAjaxContentInModal('hms_patient_change_password_form', '<?php esc_html_e('Change Password', 'hospital-management-system'); ?>');
    });
    
    // Modal close functionality
    $('.hms-modal-close').on('click', function() {
        $('#hms-dashboard-modal').hide();
    });
    $(window).on('click', function(event) {
        if ($(event.target).is('#hms-dashboard-modal')) {
            $('#hms-dashboard-modal').hide();
        }
    });

    // Handle form submissions within modals (delegated event)
    $('#hms-modal-body').on('submit', 'form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.text();
        
        submitBtn.prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: form.serialize() + '&nonce=' + nonce,
            success: function(response) {
                if (response.success) {
                    form.replaceWith('<p class="hms-success-message">' + response.data.message + '</p>');
                    // Optionally, reload parts of the dashboard or close modal after a delay
                    setTimeout(function(){
                        $('#hms-dashboard-modal').hide();
                        // Reload dashboard sections if needed (e.g., after profile update)
                        // loadUpcomingAppointments(); // etc.
                    }, 2000);
                } else {
                    form.find('.hms-form-errors').remove(); // Remove previous errors
                    form.prepend('<div class="hms-form-errors hms-error-message">' + response.data + '</div>');
                }
            },
            error: function() {
                form.find('.hms-form-errors').remove();
                form.prepend('<div class="hms-form-errors hms-error-message"><?php esc_html_e('An unexpected error occurred.', 'hospital-management-system'); ?></div>');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalBtnText);
            }
        });
    });
});
</script>
