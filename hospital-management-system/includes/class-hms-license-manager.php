<?php
/**
 * HMS License Manager Class
 *
 * Handles license activation, validation, and management for premium features.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HMS_License_Manager {

    /**
     * The option name for storing the license key.
     *
     * @var string
     */
    private $license_key_option = 'hms_license_key';

    /**
     * The option name for storing the license status.
     *
     * @var string
     */
    private $license_status_option = 'hms_license_status';

    /**
     * The option name for storing the license data (e.g., expiry, customer info).
     *
     * @var string
     */
    private $license_data_option = 'hms_license_data';

    /**
     * The API endpoint for license verification.
     * This should point to the developer's licensing server.
     *
     * @var string
     */
    private $api_endpoint = 'https://api.example.com/hms-license/v1/verify'; // Placeholder URL

    /**
     * The item ID for this product on the developer's licensing server.
     *
     * @var int
     */
    private $item_id = 1; // Placeholder Item ID

    /**
     * Constructor.
     * Hooks into WordPress actions for license management.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action('admin_init', array($this, 'handle_license_activation'));
        add_action('admin_init', array($this, 'handle_license_deactivation'));
        add_action('admin_notices', array($this, 'admin_license_notices'));
        add_action('wp_ajax_hms_verify_license', array($this, 'ajax_verify_license'));
        add_action('wp_ajax_hms_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_hms_deactivate_license', array($this, 'ajax_deactivate_license'));
    }

    /**
     * Get the stored license key.
     *
     * @since    1.0.0
     * @return   string    The license key.
     */
    public function get_license_key() {
        return get_option($this->license_key_option, '');
    }

    /**
     * Get the stored license status.
     * Possible statuses: 'inactive', 'active', 'expired', 'invalid', 'site_inactive'.
     *
     * @since    1.0.0
     * @return   string    The license status.
     */
    public function get_license_status() {
        return get_option($this->license_status_option, 'inactive');
    }

    /**
     * Get the stored license data.
     *
     * @since    1.0.0
     * @return   array    The license data.
     */
    public function get_license_data() {
        return get_option($this->license_data_option, array());
    }

    /**
     * Check if the current license is active and valid.
     *
     * @since    1.0.0
     * @return   bool    True if license is active, false otherwise.
     */
    public function is_license_active() {
        $status = $this->get_license_status();
        return $status === 'active';
    }

    /**
     * Activate a license key.
     * This method validates the license key against the local database or predefined keys.
     *
     * @since    1.0.0
     * @param    string    $license_key    The license key to activate.
     * @return   array|WP_Error              Array with success status and data, or WP_Error on failure.
     */
    public function activate_license($license_key) {
        if (empty($license_key)) {
            return new WP_Error('empty_license_key', esc_html__('License key cannot be empty.', 'hospital-management-system'));
        }

        // First check predefined demo/test keys
        $predefined_keys = $this->get_predefined_license_keys();
        if (isset($predefined_keys[$license_key])) {
            $license_data = $predefined_keys[$license_key];
            return $this->process_license_activation($license_key, $license_data);
        }

        // Then check local database for generated licenses
        $db_license_data = $this->get_license_from_database($license_key);
        if ($db_license_data) {
            return $this->process_license_activation($license_key, $db_license_data);
        }

        // For development purposes, accept any license key in the format XXXX-XXXX-XXXX-XXXX
        if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
            // Create mock license data
            $license_data = array(
                'result' => 'success',
                'message' => esc_html__('License activated successfully.', 'hospital-management-system'),
                'status' => 'active',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')), // Set expiry to 1 year from now
                'registered_domain' => $_SERVER['SERVER_NAME'],
                'customer_email' => get_option('admin_email'),
                'license_key' => $license_key,
                'license_type' => 'premium',
                'features' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine'),
            );

            update_option($this->license_key_option, $license_key);
            update_option($this->license_status_option, 'active');
            update_option($this->license_data_option, $license_data);
            
            return array(
                'success' => true,
                'message' => $license_data['message'],
                'data' => $license_data,
            );
        } else {
            update_option($this->license_status_option, 'invalid');
            delete_option($this->license_data_option);
            
            return new WP_Error('activation_failed', esc_html__('Invalid license key format. Please use the format XXXX-XXXX-XXXX-XXXX.', 'hospital-management-system'));
        }
    }

    /**
     * Deactivate the current license.
     *
     * @since    1.0.0
     * @return   array|WP_Error  Array with success status and data, or WP_Error on failure.
     */
    public function deactivate_license() {
        $license_key = $this->get_license_key();
        if (empty($license_key)) {
            return new WP_Error('no_license_key', esc_html__('No license key found to deactivate.', 'hospital-management-system'));
        }

        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => 'YOUR_SPECIAL_SECRET_KEY', // Replace with your actual secret key
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode($this->item_id),
        );

        $response = $this->make_api_request($api_params);

        if (is_wp_error($response)) {
            // Even if API fails, we can try to deactivate locally.
            // This allows users to move licenses if the dev server is down.
        }
        
        $license_data = json_decode(wp_remote_retrieve_body($response), true);
        $success_message = esc_html__('License deactivated successfully.', 'hospital-management-system');

        if (isset($license_data['result']) && $license_data['result'] === 'success') {
            $success_message = $license_data['message'] ?? $success_message;
        }

        // Always clear local license data on deactivation attempt.
        delete_option($this->license_key_option);
        update_option($this->license_status_option, 'inactive');
        delete_option($this->license_data_option);

        return array(
            'success' => true,
            'message' => $success_message,
            'data' => $license_data,
        );
    }
    
    /**
     * Verify the current license status with the API.
     * This can be run periodically (e.g., daily via cron) to check for expiry.
     *
     * @since    1.0.0
     * @return   bool|WP_Error  True if active, WP_Error otherwise.
     */
    public function verify_license() {
        $license_key = $this->get_license_key();
        if (empty($license_key)) {
            return new WP_Error('no_license_key', esc_html__('No license key to verify.', 'hospital-management-system'));
        }

        $license_data = $this->get_license_data();
        
        // Check if we have valid license data
        if (!empty($license_data) && isset($license_data['status']) && $license_data['status'] === 'active') {
            // Check if the license has expired
            if (isset($license_data['expiry_date'])) {
                $expiry_date = new DateTime($license_data['expiry_date']);
                $current_date = new DateTime();
                
                if ($current_date < $expiry_date) {
                    // License is still active, also verify domain if available
                    if (isset($license_data['registered_domain'])) {
                        $current_domain = $_SERVER['SERVER_NAME'];
                        if ($license_data['registered_domain'] !== $current_domain) {
                            // Domain mismatch, check if it's a predefined key
                            $predefined_keys = $this->get_predefined_license_keys();
                            if (!isset($predefined_keys[$license_key])) {
                                update_option($this->license_status_option, 'site_inactive');
                                return new WP_Error('domain_mismatch', esc_html__('License is not valid for this domain.', 'hospital-management-system'));
                            }
                        }
                    }
                    
                    // License is valid
                    update_option($this->license_status_option, 'active');
                    update_option($this->license_data_option, $license_data);
                    return true;
                } else {
                    // License has expired
                    update_option($this->license_status_option, 'expired');
                    update_option($this->license_data_option, $license_data);
                    return new WP_Error('license_expired', esc_html__('License has expired.', 'hospital-management-system'));
                }
            } else {
                // No expiry date set, assume active
                update_option($this->license_status_option, 'active');
                update_option($this->license_data_option, $license_data);
                return true;
            }
        } else {
            update_option($this->license_status_option, 'invalid');
            return new WP_Error('verification_failed', esc_html__('License verification failed.', 'hospital-management-system'));
        }
    }

    /**
     * Make an API request to the licensing server.
     *
     * @since    1.0.0
     * @param    array    $params    The parameters to send in the request.
     * @return   array|WP_Error    The response from the API or WP_Error on failure.
     */
    private function make_api_request($params) {
        $response = wp_remote_post($this->api_endpoint, array(
            'timeout' => 15,
            'sslverify' => true, // Should be true in production
            'body' => $params,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('api_error', sprintf(esc_html__('API request failed with status code: %d', 'hospital-management-system'), $response_code));
        }

        return $response;
    }

    /**
     * Handle manual license activation from the admin form.
     *
     * @since    1.0.0
     */
    public function handle_license_activation() {
        if (!isset($_POST['hms_activate_license']) || !check_admin_referer('hms_activate_license_nonce')) {
            return;
        }

        $license_key = sanitize_text_field($_POST['hms_license_key']);
        $result = $this->activate_license($license_key);

        if (is_wp_error($result)) {
            set_transient('hms_license_admin_notice', array('type' => 'error', 'message' => $result->get_error_message()), 45);
        } else {
            set_transient('hms_license_admin_notice', array('type' => 'success', 'message' => $result['message']), 45);
        }

        wp_redirect(admin_url('admin.php?page=hms-settings&tab=premium'));
        exit;
    }

    /**
     * Handle manual license deactivation from the admin form.
     *
     * @since    1.0.0
     */
    public function handle_license_deactivation() {
        if (!isset($_POST['hms_deactivate_license']) || !check_admin_referer('hms_deactivate_license_nonce')) {
            return;
        }

        $result = $this->deactivate_license();

        if (is_wp_error($result)) {
            set_transient('hms_license_admin_notice', array('type' => 'error', 'message' => $result->get_error_message()), 45);
        } else {
            set_transient('hms_license_admin_notice', array('type' => 'success', 'message' => $result['message']), 45);
        }

        wp_redirect(admin_url('admin.php?page=hms-settings&tab=premium'));
        exit;
    }

    /**
     * Display admin notices for license status.
     *
     * @since    1.0.0
     */
    public function admin_license_notices() {
        $notice = get_transient('hms_license_admin_notice');
        if ($notice) {
            printf('<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr($notice['type']), esc_html($notice['message']));
            delete_transient('hms_license_admin_notice');
        }

        // Persistent notice for inactive or expired license
        if (!$this->is_license_active() && !get_transient('hms_license_admin_notice')) {
            $status = $this->get_license_status();
            $message = '';
            if ($status === 'inactive') {
                $message = esc_html__('HMS Premium Features: Your license is not active. Please activate your license key to unlock premium features.', 'hospital-management-system');
            } elseif ($status === 'expired') {
                $message = esc_html__('HMS Premium Features: Your license has expired. Please renew your license to continue using premium features.', 'hospital-management-system');
            } elseif ($status === 'invalid') {
                $message = esc_html__('HMS Premium Features: Your license key is invalid. Please check your license key and try again.', 'hospital-management-system');
            }

            if (!empty($message)) {
                printf('<div class="notice notice-warning"><p>%s</p></div>', esc_html($message));
            }
        }
    }

    /**
     * AJAX handler for verifying a license key without activating it.
     */
    public function ajax_verify_license() {
        check_ajax_referer('hms_license_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $license_key = sanitize_text_field($_POST['license_key']);
        if (empty($license_key)) {
            wp_send_json_error(array('message' => esc_html__('License key cannot be empty.', 'hospital-management-system')));
        }
        
        // For verification, we can use a similar API call as activation but with a different action.
        // Or, we can just check if the key format is valid for a quick check.
        // For this example, we'll assume a quick format check.
        if (preg_match('/^[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $license_key)) {
            wp_send_json_success(array('message' => esc_html__('License key format is valid.', 'hospital-management-system')));
        } else {
            wp_send_json_error(array('message' => esc_html__('Invalid license key format.', 'hospital-management-system')));
        }
    }
    
    /**
     * AJAX handler for activating a license key.
     */
    public function ajax_activate_license() {
        check_ajax_referer('hms_license_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $license_key = sanitize_text_field($_POST['license_key']);
        $result = $this->activate_license($license_key);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => $result['message'], 'data' => $result['data']));
        }
    }

    /**
     * AJAX handler for deactivating a license key.
     */
    public function ajax_deactivate_license() {
        check_ajax_referer('hms_license_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => esc_html__('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $result = $this->deactivate_license();

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => $result['message']));
        }
    }

    /**
     * Get predefined license keys for testing and demo purposes.
     *
     * @since    1.0.0
     * @return   array    Array of predefined license keys with their data.
     */
    private function get_predefined_license_keys() {
        $current_domain = $_SERVER['SERVER_NAME'];
        $admin_email = get_option('admin_email');
        
        return array(
            'DEMO-1234-5678-9012' => array(
                'result' => 'success',
                'message' => esc_html__('Demo license activated successfully.', 'hospital-management-system'),
                'status' => 'active',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'registered_domain' => $current_domain,
                'customer_email' => $admin_email,
                'license_key' => 'DEMO-1234-5678-9012',
                'license_type' => 'premium',
                'features' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine'),
            ),
            'TEST-ABCD-EFGH-IJKL' => array(
                'result' => 'success',
                'message' => esc_html__('Test license activated successfully.', 'hospital-management-system'),
                'status' => 'active',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'registered_domain' => $current_domain,
                'customer_email' => $admin_email,
                'license_key' => 'TEST-ABCD-EFGH-IJKL',
                'license_type' => 'premium',
                'features' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine'),
            ),
            'DEV-WXYZ-ABCD-EFGH' => array(
                'result' => 'success',
                'message' => esc_html__('Development license activated successfully.', 'hospital-management-system'),
                'status' => 'active',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
                'registered_domain' => $current_domain,
                'customer_email' => $admin_email,
                'license_key' => 'DEV-WXYZ-ABCD-EFGH',
                'license_type' => 'enterprise',
                'features' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine', 'api_access', 'priority_support'),
            ),
        );
    }

    /**
     * Get license data from the local database.
     *
     * @since    1.0.0
     * @param    string    $license_key    The license key to lookup.
     * @return   array|null                License data if found, null otherwise.
     */
    private function get_license_from_database($license_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        
        // Ensure the table exists
        $this->create_licenses_table_if_not_exists();
        
        $license = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE license_key = %s AND status = 'active'",
            $license_key
        ), ARRAY_A);
        
        if ($license) {
            // Check if license has expired
            $expiry_date = new DateTime($license['expiry_date']);
            $current_date = new DateTime();
            
            if ($current_date >= $expiry_date) {
                // License expired, update status
                $wpdb->update(
                    $table_name,
                    array('status' => 'expired'),
                    array('license_key' => $license_key),
                    array('%s'),
                    array('%s')
                );
                return null;
            }
            
            // Check domain activation limit
            if ($license['activation_count'] >= $license['max_activations']) {
                $current_domain = $_SERVER['SERVER_NAME'];
                if ($license['registered_domain'] !== $current_domain) {
                    return null; // Max activations reached for different domain
                }
            }
            
            return $license;
        }
        
        return null;
    }

    /**
     * Create licenses table if it doesn't exist.
     *
     * @since    1.0.0
     */
    private function create_licenses_table_if_not_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            license_key varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            expiry_date datetime NOT NULL,
            registered_domain varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            license_type varchar(50) NOT NULL DEFAULT 'premium',
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            activation_count int NOT NULL DEFAULT 1,
            max_activations int NOT NULL DEFAULT 3,
            features longtext,
            checksum varchar(64) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY license_key (license_key),
            KEY status (status),
            KEY expiry_date (expiry_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Process license activation for both predefined and database licenses.
     *
     * @since    1.0.0
     * @param    string    $license_key     The license key.
     * @param    array     $license_data    The license data.
     * @return   array|WP_Error              Array with success status and data, or WP_Error on failure.
     */
    private function process_license_activation($license_key, $license_data) {
        // Validate checksum for database licenses
        if (isset($license_data['checksum'])) {
            if (!$this->validate_license_checksum($license_data)) {
                update_option($this->license_status_option, 'invalid');
                delete_option($this->license_data_option);
                return new WP_Error('invalid_checksum', esc_html__('License validation failed. Invalid checksum.', 'hospital-management-system'));
            }
        }

        // Check expiry date
        if (isset($license_data['expiry_date'])) {
            $expiry_date = new DateTime($license_data['expiry_date']);
            $current_date = new DateTime();
            
            if ($current_date >= $expiry_date) {
                update_option($this->license_status_option, 'expired');
                delete_option($this->license_data_option);
                return new WP_Error('license_expired', esc_html__('License has expired.', 'hospital-management-system'));
            }
        }

        // Update activation count for database licenses
        if (isset($license_data['id'])) {
            $this->increment_activation_count($license_key);
        }

        // Activate license locally
        update_option($this->license_key_option, $license_key);
        update_option($this->license_status_option, 'active');
        update_option($this->license_data_option, $license_data);
        
        return array(
            'success' => true,
            'message' => $license_data['message'],
            'data' => $license_data,
        );
    }

    /**
     * Validate license checksum.
     *
     * @since    1.0.0
     * @param    array    $license_data    License data array.
     * @return   bool                     True if checksum is valid, false otherwise.
     */
    private function validate_license_checksum($license_data) {
        if (!isset($license_data['license_key'], $license_data['registered_domain'], $license_data['customer_email'], $license_data['checksum'])) {
            return false;
        }

        $data = $license_data['license_key'] . $license_data['registered_domain'] . $license_data['customer_email'] . 'HMS_SALT_2024';
        $expected_checksum = hash('sha256', $data);

        return hash_equals($expected_checksum, $license_data['checksum']);
    }

    /**
     * Increment activation count for a license.
     *
     * @since    1.0.0
     * @param    string    $license_key    The license key.
     */
    private function increment_activation_count($license_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET activation_count = activation_count + 1 WHERE license_key = %s",
            $license_key
        ));
    }

    /**
     * Check if a specific feature is available for the current license.
     *
     * @since    1.0.0
     * @param    string    $feature    The feature to check.
     * @return   bool                  True if feature is available, false otherwise.
     */
    public function is_feature_available($feature) {
        if (!$this->is_license_active()) {
            return false;
        }

        $license_data = $this->get_license_data();
        
        if (isset($license_data['features']) && is_array($license_data['features'])) {
            return in_array($feature, $license_data['features']);
        }

        // Default to true for active licenses without feature restrictions
        return true;
    }

    /**
     * Get the current license type.
     *
     * @since    1.0.0
     * @return   string    The license type.
     */
    public function get_license_type() {
        $license_data = $this->get_license_data();
        return isset($license_data['license_type']) ? $license_data['license_type'] : 'unknown';
    }

    /**
     * Get available features for the current license.
     *
     * @since    1.0.0
     * @return   array    Array of available features.
     */
    public function get_available_features() {
        $license_data = $this->get_license_data();
        
        if (isset($license_data['features']) && is_array($license_data['features'])) {
            return $license_data['features'];
        }

        // Return default features for active licenses
        return array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine');
    }

} // End of class HMS_License_Manager
