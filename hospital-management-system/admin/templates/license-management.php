<?php
/**
 * Admin template for License Management.
 *
 * This template is used to display the license activation and management form
 * on the HMS settings page under the "Premium" tab.
 *
 * @package Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/templates
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

global $hms_license_manager;

$license_key = $hms_license_manager->get_license_key();
$license_status = $hms_license_manager->get_license_status();
$license_data = $hms_license_manager->get_license_data();

$status_class = 'inactive';
$status_text = __('Inactive', 'hospital-management-system');
$action_button_text = __('Activate License', 'hospital-management-system');
$license_details = '';

if ($license_status === 'active') {
    $status_class = 'active';
    $status_text = __('Active', 'hospital-management-system');
    $action_button_text = __('Deactivate License', 'hospital-management-system');

    if (!empty($license_data)) {
        $expiry_date = isset($license_data['expiry_date']) ? date_i18n(get_option('date_format'), strtotime($license_data['expiry_date'])) : __('N/A', 'hospital-management-system');
        $registered_domain = isset($license_data['registered_domain']) ? esc_html($license_data['registered_domain']) : __('N/A', 'hospital-management-system');
        $customer_email = isset($license_data['customer_email']) ? esc_html($license_data['customer_email']) : __('N/A', 'hospital-management-system');

        $license_details = sprintf(
            /* translators: 1: Expiry Date, 2: Registered Domain, 3: Customer Email */
            __('Expiry Date: %1$s<br>Registered Domain: %2$s<br>Customer Email: %3$s', 'hospital-management-system'),
            '<strong>' . $expiry_date . '</strong>',
            '<strong>' . $registered_domain . '</strong>',
            '<strong>' . $customer_email . '</strong>'
        );
    }
} elseif ($license_status === 'expired') {
    $status_class = 'expired';
    $status_text = __('Expired', 'hospital-management-system');
    $action_button_text = __('Renew License', 'hospital-management-system'); // Or reactivate if renewed
} elseif ($license_status === 'invalid') {
    $status_class = 'invalid';
    $status_text = __('Invalid', 'hospital-management-system');
    $action_button_text = __('Activate License', 'hospital-management-system');
}
?>

<div class="hms-license-management-wrapper">
    <h2><?php _e('Premium Features License', 'hospital-management-system'); ?></h2>
    <p><?php _e('Activate your license key to unlock premium features such as Custom Fields, White-Label Branding, and more.', 'hospital-management-system'); ?></p>
    
    <div class="hms-license-status-card">
        <div class="hms-license-status-header">
            <span class="hms-license-status-label"><?php _e('License Status:', 'hospital-management-system'); ?></span>
            <span class="hms-license-status-value <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span>
        </div>
        
        <?php if ($license_status === 'active' && !empty($license_details)) : ?>
            <div class="hms-license-details">
                <?php echo $license_details; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contains HTML from sprintf with escaped variables. ?>
            </div>
        <?php endif; ?>

        <div class="hms-license-form-container">
            <?php if ($license_status !== 'active') : ?>
                <form id="hms-license-activation-form" method="post" action="">
                    <?php wp_nonce_field('hms_activate_license_nonce'); ?>
                    <input type="hidden" name="hms_activate_license" value="1">
                    <div class="hms-form-field">
                        <label for="hms_license_key"><?php _e('Enter your license key:', 'hospital-management-system'); ?></label>
                        <input type="text" id="hms_license_key" name="hms_license_key" class="regular-text" value="<?php echo esc_attr($license_key); ?>" placeholder="<?php esc_attr_e('DEMO-1234-5678-9012', 'hospital-management-system'); ?>" required>
                        <p class="description">
                            <?php _e('For testing purposes, you can use the demo license key: <strong>DEMO-1234-5678-9012</strong>', 'hospital-management-system'); ?>
                            <br>
                            <?php _e('In a production environment, your license key can be found in your purchase confirmation email or on your account page.', 'hospital-management-system'); ?>
                            <a href="https://example.com/my-account" target="_blank"><?php _e('Find your license key', 'hospital-management-system'); ?></a>.
                        </p>
                    </div>
                    <p class="submit">
                        <button type="submit" name="submit" id="hms-activate-license-btn" class="button button-primary">
                            <?php echo esc_html($action_button_text); ?>
                        </button>
                        <span id="hms-license-spinner" class="spinner"></span>
                    </p>
                </form>
            <?php else : ?>
                <form id="hms-license-deactivation-form" method="post" action="">
                    <?php wp_nonce_field('hms_deactivate_license_nonce'); ?>
                    <input type="hidden" name="hms_deactivate_license" value="1">
                    <p class="submit">
                        <button type="submit" name="submit" id="hms-deactivate-license-btn" class="button">
                            <?php echo esc_html($action_button_text); ?>
                        </button>
                        <span id="hms-license-spinner" class="spinner"></span>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <h3><?php _e('How to Obtain a License', 'hospital-management-system'); ?></h3>
    <ol>
        <li><?php printf(__('Visit the <a href="%s" target="_blank">HMS Premium Features page</a> on our website.', 'hospital-management-system'), 'https://example.com/hms-premium'); ?></li>
        <li><?php _e('Choose the plan that best fits your hospital\'s needs.', 'hospital-management-system'); ?></li>
        <li><?php _e('Complete the purchase process.', 'hospital-management-system'); ?></li>
        <li><?php _e('You will receive a license key via email and it will also be available in your account dashboard.', 'hospital-management-system'); ?></li>
        <li><?php _e('Enter the license key in the field above and click "Activate License".', 'hospital-management-system'); ?></li>
    </ol>
    <p>
        <a href="https://example.com/hms-premium" target="_blank" class="button button-secondary">
            <?php _e('View Plans & Pricing', 'hospital-management-system'); ?>
        </a>
    </p>

    <hr>

    <h3><?php _e('Premium Features Overview', 'hospital-management-system'); ?></h3>
    <ul>
        <li><strong><?php _e('Custom Fields & Forms:', 'hospital-management-system'); ?></strong> <?php _e('Dynamically add custom fields to patient registration, appointments, and more.', 'hospital-management-system'); ?></li>
        <li><strong><?php _e('White-Label Branding:', 'hospital-management-system'); ?></strong> <?php _e('Customize colors, logos, and email templates to match your hospital\'s brand.', 'hospital-management-system'); ?></li>
        <li><strong><?php _e('Advanced Reporting:', 'hospital-management-system'); ?></strong> <?php _e('Generate PDF reports and use advanced charting libraries.', 'hospital-management-system'); ?></li>
        <li><strong><?php _e('Multi-Location Support:', 'hospital-management-system'); ?></strong> <?php _e('Manage multiple hospital branches from a single installation.', 'hospital-management-system'); ?></li>
        <li><strong><?php _e('Enhanced Telemedicine:', 'hospital-management-system'); ?></strong> <?php _e('Integrated video conferencing solutions.', 'hospital-management-system'); ?></li>
    </ul>
</div>

<style>
    .hms-license-management-wrapper .hms-license-status-card {
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 15px;
        background-color: #fff;
        margin-bottom: 20px;
    }
    .hms-license-management-wrapper .hms-license-status-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 1.1em;
        font-weight: bold;
    }
    .hms-license-management-wrapper .hms-license-status-value {
        margin-left: 10px;
        padding: 3px 8px;
        border-radius: 3px;
        color: #fff;
        font-size: 0.9em;
    }
    .hms-license-management-wrapper .hms-license-status-value.active {
        background-color: #46b450;
    }
    .hms-license-management-wrapper .hms-license-status-value.inactive,
    .hms-license-management-wrapper .hms-license-status-value.invalid {
        background-color: #dc3232;
    }
    .hms-license-management-wrapper .hms-license-status-value.expired {
        background-color: #ffb900;
        color: #333;
    }
    .hms-license-management-wrapper .hms-license-details {
        background-color: #f8f9f9;
        border-left: 4px solid #0073aa;
        padding: 10px 15px;
        margin-bottom: 15px;
        font-size: 0.95em;
    }
    .hms-license-management-wrapper .hms-form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .hms-license-management-wrapper .hms-form-field .description {
        margin-top: 5px;
        font-style: italic;
    }
    .hms-license-management-wrapper #hms-license-spinner {
        float: none;
        vertical-align: middle;
        margin-left: 10px;
        visibility: hidden;
    }
    .hms-license-management-wrapper .spinner.is-active {
        visibility: visible;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle license activation via AJAX
    $('#hms-license-activation-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitButton = $('#hms-activate-license-btn');
        var $spinner = $('#hms-license-spinner');
        var licenseKey = $('#hms_license_key').val();

        if (!licenseKey) {
            alert('<?php echo esc_js(__('Please enter a license key.', 'hospital-management-system')); ?>');
            return;
        }

        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_activate_license',
                license_key: licenseKey,
                nonce: '<?php echo wp_create_nonce('hms_license_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    window.location.reload(); // Reload to see updated status
                } else {
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : '<?php echo esc_js(__('An unknown error occurred.', 'hospital-management-system')); ?>';
                alert(errorMessage);
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });

    // Handle license deactivation via AJAX
    $('#hms-license-deactivation-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitButton = $('#hms-deactivate-license-btn');
        var $spinner = $('#hms-license-spinner');

        if (!confirm('<?php echo esc_js(__('Are you sure you want to deactivate your license? Premium features will be disabled.', 'hospital-management-system')); ?>')) {
            return;
        }

        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_deactivate_license',
                nonce: '<?php echo wp_create_nonce('hms_license_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    window.location.reload(); // Reload to see updated status
                } else {
                    alert(response.data.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : '<?php echo esc_js(__('An unknown error occurred.', 'hospital-management-system')); ?>';
                alert(errorMessage);
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
</script>
