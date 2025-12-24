<?php
/**
 * Plugin Name: Custom Login Redirect
 * Plugin URI: https://example.com/
 * Description: Advanced login redirect with AI, geolocation, A/B testing, and page builder integration.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com/
 * License: GPL2
 * Text Domain: clr
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLR_VERSION', '1.0.0');
define('CLR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Core functionality
require_once CLR_PLUGIN_DIR . 'includes/class-clr-redirect.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-ai.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-geolocation.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-ab-testing.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-time-based.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-device-based.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-referral-based.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-analytics.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-conditional.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-import-export.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-email-notifications.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-page-builder.php';

// Premium features
require_once CLR_PLUGIN_DIR . 'includes/class-clr-premium-analytics.php';
require_once CLR_PLUGIN_DIR . 'includes/class-clr-integrations.php';

// Custom code management
require_once CLR_PLUGIN_DIR . 'includes/class-clr-custom-code.php';

// Installation
require_once CLR_PLUGIN_DIR . 'includes/install.php';

// Admin interface
require_once CLR_PLUGIN_DIR . 'admin/class-clr-admin.php';

// Premium license check function
function clr_is_premium() {
    $license_key = get_option('clr_license_key', '');
    $license_status = get_option('clr_license_status', 'invalid');
    
    // For demo purposes, consider any non-empty license key as valid
    // In a real implementation, you would validate the license key against your server
    return !empty($license_key) && $license_status === 'valid';
}

// AJAX handler for license activation
function clr_activate_license() {
    check_ajax_referer('clr_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die();
    }
    
    $license_key = sanitize_text_field($_POST['license_key']);
    
    if (empty($license_key)) {
        wp_send_json_error(__('Please enter a license key.', 'clr'));
    }
    
    // For demo purposes, consider any non-empty license key as valid
    // In a real implementation, you would validate the license key against your server
    $is_valid = !empty($license_key);
    
    if ($is_valid) {
        update_option('clr_license_key', $license_key);
        update_option('clr_license_status', 'valid');
        wp_send_json_success(__('License activated successfully!', 'clr'));
    } else {
        update_option('clr_license_status', 'invalid');
        wp_send_json_error(__('Invalid license key.', 'clr'));
    }
}

// Initialize plugin
function custom_login_redirect_init() {
    if (is_admin()) {
        new CLR_Admin();
    }
    new CLR_Redirect();
    new CLR_AI();
    new CLR_Geolocation();
    new CLR_AB_Testing();
    new CLR_Time_Based();
    new CLR_Device_Based();
    new CLR_Referral_Based();
    new CLR_Analytics();
    new CLR_Conditional();
    new CLR_Import_Export();
    new CLR_Email_Notifications();
    new CLR_Page_Builder();
    
    // Initialize premium features
    new CLR_Premium_Analytics();
    new CLR_Integrations();
    
    // Initialize custom code management
    new CLR_Custom_Code();
}

// Installation and uninstallation hooks
register_activation_hook(__FILE__, 'clr_install');
register_uninstall_hook(__FILE__, 'clr_uninstall');
add_action('plugins_loaded', 'custom_login_redirect_init');
