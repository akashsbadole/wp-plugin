<?php
/**
 * Template for Joining a Telemedicine Session.
 *
 * This template can be overridden by copying it to yourtheme/hospital-management-system/public/templates/join-telemedicine-session.php
 *
 * @package Hospital_Management_System
 * @subpackage Public/Templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Get the telemedicine session data passed from the shortcode renderer
$session = get_query_var('telemedicine_session');

if (!$session) {
    echo '<p>' . esc_html__('Invalid session details provided.', 'hospital-management-system') . '</p>';
    return;
}

$current_time = new DateTime();
$appointment_datetime = new DateTime($session->appointment_date . ' ' . $session->appointment_time);
$can_join = ($current_time >= $appointment_datetime->modify('-15 minutes')) && ($session->status === 'scheduled' || $session->status === 'in_progress');
?>
<div class="hms-telemedicine-session-container">
    <h1><?php esc_html_e('Telemedicine Session', 'hospital-management-system'); ?></h1>
    
    <div class="hms-session-details">
        <h2><?php esc_html_e('Appointment Details', 'hospital-management-system'); ?></h2>
        <p><strong><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></strong> <?php echo esc_html($session->first_name . ' ' . $session->last_name); ?> (<?php echo esc_html($session->doctor_staff_id); ?>)</p>
        <p><strong><?php esc_html_e('Date:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($session->appointment_date))); ?></p>
        <p><strong><?php esc_html_e('Time:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($session->appointment_time))); ?></p>
        <?php if (!empty($session->notes)) : ?>
            <p><strong><?php esc_html_e('Notes from Doctor:', 'hospital-management-system'); ?></strong> <?php echo esc_html($session->notes); ?></p>
        <?php endif; ?>
    </div>

    <div class="hms-session-join-area">
        <?php if ($can_join && !empty($session->meeting_link)) : ?>
            <h2><?php esc_html_e('Join Your Session', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('You can now join your telemedicine session by clicking the button below.', 'hospital-management-system'); ?></p>
            <p class="hms-important-notice">
                <?php esc_html_e('Please ensure you have a stable internet connection, a webcam, and a microphone for the best experience.', 'hospital-management-system'); ?>
            </p>
            <a href="<?php echo esc_url($session->meeting_link); ?>" target="_blank" class="hms-button hms-button-primary hms-join-session-btn">
                <?php esc_html_e('Join Telemedicine Session', 'hospital-management-system'); ?>
            </a>
            <p class="hms-disclaimer"><?php esc_html_e('You will be redirected to an external video conferencing service.', 'hospital-management-system'); ?></p>

        <?php elseif ($session->status === 'completed') : ?>
            <h2><?php esc_html_e('Session Completed', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('This telemedicine session has already been completed.', 'hospital-management-system'); ?></p>
        
        <?php elseif ($session->status === 'cancelled') : ?>
            <h2><?php esc_html_e('Session Cancelled', 'hospital-management-system'); ?></h2>
            <p><?php esc_html_e('This telemedicine session has been cancelled.', 'hospital-management-system'); ?></p>
            <p><?php esc_html_e('Please contact the hospital or your doctor for more information.', 'hospital-management-system'); ?></p>

        <?php else : // Not yet time to join or link not generated ?>
            <h2><?php esc_html_e('Session Not Yet Available', 'hospital-management-system'); ?></h2>
            <?php if ($current_time < $appointment_datetime) : ?>
                <p><?php esc_html_e('The session is not yet open for joining. You can join starting 15 minutes before the scheduled appointment time.', 'hospital-management-system'); ?></p>
                <p><strong><?php esc_html_e('Scheduled Join Time:', 'hospital-management-system'); ?></strong> <?php echo esc_html($appointment_datetime->format('Y-m-d H:i:s')); ?></p>
            <?php else : ?>
                 <p><?php esc_html_e('The session details are not yet available or the link is being generated. Please refresh this page in a moment.', 'hospital-management-system'); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="hms-session-help">
        <h3><?php esc_html_e('Need Help?', 'hospital-management-system'); ?></h3>
        <p><?php esc_html_e('If you encounter any technical difficulties, please contact our support team.', 'hospital-management-system'); ?></p>
        <?php 
        /* translators: %s: Support phone number */
        printf(esc_html__('Support Phone: %s', 'hospital-management-system'), esc_html(get_option('hms_hospital_phone', '+1 (555) 123-4567'))); 
        ?>
        <?php 
        /* translators: %s: Support email address */
        printf(esc_html__('Support Email: %s', 'hospital-management-system'), esc_html(get_option('hms_hospital_email', 'support@hospital.com'))); 
        ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Optionally, add a timer or auto-refresh logic if the session is about to start.
    // For simplicity, we'll just rely on the user refreshing the page.
    // A more advanced version could poll the server for status changes.
    var sessionStatus = '<?php echo esc_js($session->status); ?>';
    var joinButton = $('.hms-join-session-btn');

    // Example: Disable join button after click to prevent multiple clicks
    joinButton.on('click', function(e) {
        $(this).prop('disabled', true).text('<?php esc_html_e('Joining...', 'hospital-management-system'); ?>');
        // The actual redirect is handled by the href attribute
    });
});
</script>
