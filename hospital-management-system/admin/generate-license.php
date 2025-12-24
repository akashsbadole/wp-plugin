<?php
/**
 * HMS License Generator Script
 *
 * This script generates license keys for the Hospital Management System plugin.
 * It can be used to create valid license keys for testing or distribution.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    // If this file is accessed directly, try to load WordPress
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        exit('Direct access to this file is not allowed.');
    }
}

// Check if user has sufficient permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

class HMS_License_Generator {

    /**
     * Generate a random license key
     *
     * @param string $prefix Optional prefix for the license key
     * @return string Generated license key
     */
    public static function generate_license_key($prefix = 'HMS') {
        // Generate 4 segments of 8 characters each
        $segments = array();
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(wp_generate_password(8, false));
        }
        
        // Replace similar looking characters to avoid confusion
        $replacements = array(
            '0' => 'A', 'O' => 'B', 'I' => 'C', '1' => 'D', 'L' => 'E'
        );
        
        $license_key = $prefix . '-' . implode('-', $segments);
        foreach ($replacements as $from => $to) {
            $license_key = str_replace($from, $to, $license_key);
        }
        
        return $license_key;
    }

    /**
     * Generate a license with specific expiry date
     *
     * @param string $email Customer email
     * @param string $domain Registered domain
     * @param string $expiry_date Expiry date (Y-m-d H:i:s format)
     * @param string $license_type Type of license (trial, basic, premium, enterprise)
     * @return array License data
     */
    public static function generate_license_data($email, $domain, $expiry_date = null, $license_type = 'premium') {
        if ($expiry_date === null) {
            // Default to 1 year from now
            $expiry_date = date('Y-m-d H:i:s', strtotime('+1 year'));
        }

        $license_key = self::generate_license_key();
        
        $license_data = array(
            'license_key' => $license_key,
            'status' => 'active',
            'expiry_date' => $expiry_date,
            'registered_domain' => $domain,
            'customer_email' => $email,
            'license_type' => $license_type,
            'created_date' => current_time('mysql'),
            'activation_count' => 1,
            'max_activations' => 3, // Allow up to 3 domain activations
            'features' => self::get_features_for_license_type($license_type),
            'checksum' => self::generate_checksum($license_key, $domain, $email)
        );

        return $license_data;
    }

    /**
     * Get features available for a specific license type
     *
     * @param string $license_type
     * @return array
     */
    private static function get_features_for_license_type($license_type) {
        $features = array(
            'trial' => array('custom_fields', 'basic_reports'),
            'basic' => array('custom_fields', 'basic_reports', 'white_label_basic'),
            'premium' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine'),
            'enterprise' => array('custom_fields', 'advanced_reports', 'white_label', 'multi_location', 'telemedicine', 'api_access', 'priority_support')
        );

        return isset($features[$license_type]) ? $features[$license_type] : $features['premium'];
    }

    /**
     * Generate checksum for license validation
     *
     * @param string $license_key
     * @param string $domain
     * @param string $email
     * @return string
     */
    private static function generate_checksum($license_key, $domain, $email) {
        $data = $license_key . $domain . $email . 'HMS_SALT_2024';
        return hash('sha256', $data);
    }

    /**
     * Validate license checksum
     *
     * @param array $license_data
     * @return bool
     */
    public static function validate_license_checksum($license_data) {
        if (!isset($license_data['license_key'], $license_data['registered_domain'], $license_data['customer_email'], $license_data['checksum'])) {
            return false;
        }

        $expected_checksum = self::generate_checksum(
            $license_data['license_key'],
            $license_data['registered_domain'],
            $license_data['customer_email']
        );

        return hash_equals($expected_checksum, $license_data['checksum']);
    }

    /**
     * Save license to database (for local licensing system)
     *
     * @param array $license_data
     * @return bool
     */
    public static function save_license_to_database($license_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        
        // Create table if it doesn't exist
        self::create_licenses_table();
        
        // Check if license already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE license_key = %s",
            $license_data['license_key']
        ));
        
        if ($existing) {
            // Update existing license
            return $wpdb->update(
                $table_name,
                $license_data,
                array('license_key' => $license_data['license_key']),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'),
                array('%s')
            ) !== false;
        } else {
            // Insert new license
            return $wpdb->insert(
                $table_name,
                $license_data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
            ) !== false;
        }
    }

    /**
     * Create licenses table if it doesn't exist
     */
    private static function create_licenses_table() {
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
     * Get all licenses from database
     *
     * @return array
     */
    public static function get_all_licenses() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_date DESC", ARRAY_A);
    }

    /**
     * Delete a license from database
     *
     * @param string $license_key
     * @return bool
     */
    public static function delete_license($license_key) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_licenses';
        
        return $wpdb->delete(
            $table_name,
            array('license_key' => $license_key),
            array('%s')
        ) !== false;
    }
}

// Handle form submissions
if (isset($_POST['hms_generate_license'])) {
    check_admin_referer('hms_generate_license_nonce');
    
    $email = sanitize_email($_POST['customer_email']);
    $domain = sanitize_text_field($_POST['registered_domain']);
    $expiry_date = sanitize_text_field($_POST['expiry_date']);
    $license_type = sanitize_text_field($_POST['license_type']);
    
    if (empty($email) || empty($domain)) {
        $error = 'Email and domain are required fields.';
    } else {
        $license_data = HMS_License_Generator::generate_license_data($email, $domain, $expiry_date, $license_type);
        
        if (HMS_License_Generator::save_license_to_database($license_data)) {
            $success = 'License generated successfully: ' . $license_data['license_key'];
        } else {
            $error = 'Failed to save license to database.';
        }
    }
}

if (isset($_POST['hms_delete_license'])) {
    check_admin_referer('hms_delete_license_nonce');
    
    $license_key = sanitize_text_field($_POST['license_key']);
    
    if (HMS_License_Generator::delete_license($license_key)) {
        $success = 'License deleted successfully.';
    } else {
        $error = 'Failed to delete license.';
    }
}

// Get current domain for default value
$current_domain = $_SERVER['SERVER_NAME'];
?>
<div class="wrap">
    <h1>HMS License Generator</h1>
    
    <?php if (isset($error)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Generate New License</h2>
        <form method="post" action="">
            <?php wp_nonce_field('hms_generate_license_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="customer_email">Customer Email</label>
                    </th>
                    <td>
                        <input type="email" id="customer_email" name="customer_email" class="regular-text" required>
                        <p class="description">Email address of the license holder</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="registered_domain">Registered Domain</label>
                    </th>
                    <td>
                        <input type="text" id="registered_domain" name="registered_domain" class="regular-text" value="<?php echo esc_attr($current_domain); ?>" required>
                        <p class="description">Domain where the license will be used</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="expiry_date">Expiry Date</label>
                    </th>
                    <td>
                        <input type="datetime-local" id="expiry_date" name="expiry_date" class="regular-text" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 year')); ?>">
                        <p class="description">Leave empty for 1 year from now</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="license_type">License Type</label>
                    </th>
                    <td>
                        <select id="license_type" name="license_type">
                            <option value="trial">Trial (30 days)</option>
                            <option value="basic">Basic (1 year)</option>
                            <option value="premium" selected>Premium (1 year)</option>
                            <option value="enterprise">Enterprise (1 year)</option>
                        </select>
                        <p class="description">Type of license to generate</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Generate License', 'primary', 'hms_generate_license'); ?>
        </form>
    </div>
    
    <div class="card">
        <h2>Generated Licenses</h2>
        <?php
        $licenses = HMS_License_Generator::get_all_licenses();
        if (!empty($licenses)):
        ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Email</th>
                        <th>Domain</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $license): ?>
                        <tr>
                            <td><code><?php echo esc_html($license['license_key']); ?></code></td>
                            <td><?php echo esc_html($license['customer_email']); ?></td>
                            <td><?php echo esc_html($license['registered_domain']); ?></td>
                            <td><?php echo esc_html(ucfirst($license['license_type'])); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($license['status']); ?>">
                                    <?php echo esc_html(ucfirst($license['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($license['expiry_date']))); ?></td>
                            <td>
                                <form method="post" action="" style="display:inline;">
                                    <?php wp_nonce_field('hms_delete_license_nonce'); ?>
                                    <input type="hidden" name="license_key" value="<?php echo esc_attr($license['license_key']); ?>">
                                    <input type="submit" name="hms_delete_license" class="button button-small" value="Delete" onclick="return confirm('Are you sure?');">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No licenses generated yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2>Quick License Keys for Testing</h2>
        <p>Here are some pre-generated license keys you can use for testing:</p>
        <ul>
            <li><code>DEMO-1234-5678-9012</code> - Demo license (expires in 1 year)</li>
            <li><code>TEST-ABCD-EFGH-IJKL</code> - Test license (expires in 1 year)</li>
            <li><code>DEV-WXYZ-ABCD-EFGH</code> - Development license (expires in 1 year)</li>
        </ul>
        <p><strong>Note:</strong> These keys work with any domain and email for testing purposes.</p>
    </div>
</div>

<style>
    .status-active {
        color: #46b450;
        font-weight: bold;
    }
    .status-expired {
        color: #ffb900;
        font-weight: bold;
    }
    .status-inactive {
        color: #dc3232;
        font-weight: bold;
    }
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin-top: 20px;
        padding: 15px;
    }
    .card h2 {
        margin-top: 0;
    }
</style>
