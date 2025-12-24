<?php
/**
 * Template for Patient Portal Login.
 *
 * This template can be overridden by copying it to yourtheme/hospital-management-system/public/templates/patient-portal-login.php
 *
 * @package Hospital_Management_System
 * @subpackage Public/Templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>

<div class="hms-patient-portal-container">
    <div class="hms-patient-portal-login-form">
        <h2><?php esc_html_e('Patient Login', 'hospital-management-system'); ?></h2>
        
        <?php
        // Display WordPress login form errors if any
        // Note: This is a simplified version. A more robust solution would involve custom login logic.
        if (isset($_GET['login']) && $_GET['login'] == 'failed') {
            echo '<p class="hms-error-message">' . esc_html__('Login failed. Please check your credentials.', 'hospital-management-system') . '</p>';
        }
        ?>

        <form id="hms-patient-login-form" action="<?php echo esc_url(wp_login_url()); ?>" method="post">
            <p class="hms-form-field">
                <label for="hms_user_login"><?php esc_html_e('Username or Email', 'hospital-management-system'); ?></label>
                <input type="text" name="log" id="hms_user_login" class="hms-input" required>
            </p>
            <p class="hms-form-field">
                <label for="hms_user_pass"><?php esc_html_e('Password', 'hospital-management-system'); ?></label>
                <input type="password" name="pwd" id="hms_user_pass" class="hms-input" required>
            </p>
            <p class="hms-form-field">
                <input type="checkbox" name="rememberme" id="hms_rememberme" value="forever">
                <label for="hms_rememberme"><?php esc_html_e('Remember Me', 'hospital-management-system'); ?></label>
            </p>
            <p class="hms-form-submit">
                <input type="submit" name="wp-submit" id="hms_wp-submit" class="hms-button hms-button-primary" value="<?php esc_attresc_html_e('Log In', 'hospital-management-system'); ?>">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/patient-dashboard/')); // Replace with actual dashboard page URL ?>">
                <?php wp_nonce_field('hms_patient_login_action', 'hms_login_nonce'); ?>
            </p>
        </form>

        <p class="hms-form-links">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Forgot your password?', 'hospital-management-system'); ?></a>
            <?php // Add a link to patient registration page if it exists ?>
            <?php $registration_page_url = get_permalink(get_page_by_path('patient-registration')); // Adjust page slug as needed ?>
            <?php if ($registration_page_url) : ?>
                | <a href="<?php echo esc_url($registration_page_url); ?>"><?php esc_html_e('Register', 'hospital-management-system'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
