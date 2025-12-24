<?php
/**
 * HMS Security & Compliance Class
 *
 * Handles security and compliance features like data encryption,
 * consent logging, and audit trails for regulations such as
 * HIPAA, GDPR, and DISHA.
 *
 * @package Hospital_Management_System
 * @subpackage Includes
 */

class HMS_Security {

    /**
     * Constructor.
     */
    public function __construct() {
        // Hooks for data encryption/decryption
        add_filter('hms_encrypt_data', array($this, 'encrypt_data'), 10, 1);
        add_filter('hms_decrypt_data', array($this, 'decrypt_data'), 10, 1);

        // Hooks for logging data access
        add_action('hms_log_data_access', array($this, 'log_data_access'), 10, 3);

        // Hooks for managing patient consent
        add_action('wp_ajax_hms_save_patient_consent', array($this, 'ajax_save_patient_consent'));
        add_action('wp_ajax_hms_get_patient_consents', array($this, 'ajax_get_patient_consents'));
        add_action('wp_ajax_hms_revoke_patient_consent', array($this, 'ajax_revoke_patient_consent'));
    }

    /**
     * Encrypt data.
     * Note: This is a basic implementation. For production, use a robust library like Defuse or libsodium.
     *
     * @param string $data Data to encrypt.
     * @return string|false Encrypted data or false on failure.
     */
    public function encrypt_data($data) {
        if (empty($data)) {
            return false;
        }

        $key = $this->get_encryption_key();
        if (!$key) {
            // Fallback: if key is not set, return data as is (or log error)
            error_log('HMS Security: Encryption key is not set. Data will not be encrypted.');
            return $data; 
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt data.
     *
     * @param string $data Encrypted data.
     * @return string|false Decrypted data or false on failure.
     */
    public function decrypt_data($data) {
        if (empty($data)) {
            return false;
        }

        $key = $this->get_encryption_key();
        if (!$key) {
            error_log('HMS Security: Encryption key is not set. Data cannot be decrypted.');
            // If data wasn't encrypted, it might be plain text.
            // This is a risky fallback and should be handled carefully.
            return $data;
        }

        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
        
        return $decrypted;
    }

    /**
     * Get the encryption key.
     * Stores the key in a WordPress option if not already set.
     * In a real-world scenario, this key should be stored securely, e.g., in wp-config.php or using a secrets management service.
     *
     * @return string|false The encryption key or false on failure.
     */
    private function get_encryption_key() {
        $key = get_option('hms_encryption_key');
        if (empty($key)) {
            // Generate a new key
            $key = bin2hex(openssl_random_pseudo_bytes(32)); // 256-bit key
            update_option('hms_encryption_key', $key);
        }
        return $key;
    }

    /**
     * Log data access for audit purposes.
     *
     * @param int    $user_id   The ID of the user accessing the data.
     * @param string $data_type The type of data being accessed (e.g., 'patient_record', 'billing_info').
     * @param int    $record_id The ID of the record being accessed.
     */
    public function log_data_access($user_id, $data_type, $record_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_audit_log';

        $data = array(
            'user_id' => $user_id,
            'action' => 'data_access',
            'object_type' => $data_type,
            'object_id' => $record_id,
            /* translators: 1: User ID, 2: Data type, 3: Record ID */
            'details' => sprintf(esc_html__('User ID %1$d accessed %2$s with ID %3$d.', 'hospital-management-system'), $user_id, $data_type, $record_id),
            'ip_address' => $this->get_user_ip(),
            'created_at' => current_time('mysql'),
        );

        $format = array('%d', '%s', '%s', '%d', '%s', '%s', '%s');
        $wpdb->insert($table_name, $data, $format);
    }

    /**
     * Get the user's IP address.
     *
     * @return string The IP address.
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Save patient consent.
     * AJAX handler.
     */
    public function ajax_save_patient_consent() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to manage patient consents.', 'hospital-management-system'));
        }

        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        $consent_type = isset($_POST['consent_type']) ? sanitize_text_field($_POST['consent_type']) : '';
        $consent_text = isset($_POST['consent_text']) ? wp_kses_post($_POST['consent_text']) : ''; // Allows basic HTML
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';

        if (empty($patient_id) || empty($consent_type)) {
            wp_send_json_error(esc_html__('Patient ID and Consent Type are required.', 'hospital-management-system'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patient_consents';

        $data = array(
            'patient_id' => $patient_id,
            'consent_type' => $consent_type,
            'consent_text' => $consent_text,
            'status' => $status,
            'given_at' => current_time('mysql'),
            'given_by_user_id' => get_current_user_id(),
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%d');

        // Check if a consent of this type already exists for the patient
        $existing_consent_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE patient_id = %d AND consent_type = %s",
            $patient_id, $consent_type
        ));

        if ($existing_consent_id) {
            // Update existing consent
            $where = array('id' => $existing_consent_id);
            $where_format = array('%d');
            $updated = $wpdb->update($table_name, $data, $where, $format, $where_format);
            if ($updated !== false) {
                wp_send_json_success(array('message' => esc_html__('Consent updated successfully.', 'hospital-management-system')));
            }
        } else {
            // Insert new consent
            $inserted = $wpdb->insert($table_name, $data, $format);
            if ($inserted) {
                wp_send_json_success(array('message' => esc_html__('Consent saved successfully.', 'hospital-management-system')));
            }
        }

        wp_send_json_error(esc_html__('Failed to save consent.', 'hospital-management-system'));
    }

    /**
     * Get patient consents.
     * AJAX handler.
     */
    public function ajax_get_patient_consents() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to view patient consents.', 'hospital-management-system'));
        }

        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        if (empty($patient_id)) {
            wp_send_json_error(esc_html__('Patient ID is required.', 'hospital-management-system'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patient_consents';
        $users_table = $wpdb->users;

        $consents = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name as given_by_name
             FROM $table_name c
             LEFT JOIN $users_table u ON c.given_by_user_id = u.ID
             WHERE c.patient_id = %d
             ORDER BY c.given_at DESC",
            $patient_id
        ));

        wp_send_json_success($consents);
    }

    /**
     * Revoke patient consent.
     * AJAX handler.
     */
    public function ajax_revoke_patient_consent() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to revoke patient consents.', 'hospital-management-system'));
        }

        $consent_id = isset($_POST['consent_id']) ? intval($_POST['consent_id']) : 0;
        if (empty($consent_id)) {
            wp_send_json_error(esc_html__('Consent ID is required.', 'hospital-management-system'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patient_consents';
        
        $updated = $wpdb->update(
            $table_name,
            array(
                'status' => 'revoked',
                'revoked_at' => current_time('mysql'),
                'revoked_by_user_id' => get_current_user_id(),
            ),
            array('id' => $consent_id),
            array('%s', '%s', '%d'),
            array('%d')
        );

        if ($updated !== false) {
            wp_send_json_success(array('message' => esc_html__('Consent revoked successfully.', 'hospital-management-system')));
        }

        wp_send_json_error(esc_html__('Failed to revoke consent.', 'hospital-management-system'));
    }
}
