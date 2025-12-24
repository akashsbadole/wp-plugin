<?php
/**
 * HMS Insurance Class
 *
 * Handles insurance-related functionalities, including cashless claim processing,
 * TPA/Insurance portal integration, and claim tracking.
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

class HMS_Insurance {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for claim processing
        add_action('wp_ajax_hms_initiate_cashless_claim', array($this, 'ajax_initiate_cashless_claim'));
        add_action('wp_ajax_hms_update_claim_status', array($this, 'ajax_update_claim_status'));
        add_action('wp_ajax_hms_get_claim_details', array($this, 'ajax_get_claim_details'));
        add_action('wp_ajax_hms_delete_claim', array($this, 'ajax_delete_claim'));
    }

    // --- Cashless Claim Processing ---

    /**
     * Initiate a new cashless claim.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of claim data.
     * @return   int|false                     The new claim's database ID, or false on failure.
     */
    public function initiate_cashless_claim(array $data) {
        $table_name = 'hms_claims';

        if (empty($data['patient_id']) || empty($data['bill_id']) || empty($data['insurance_provider_id']) || empty($data['initiated_by'])) {
            return false;
        }

        $claim_data = array(
            'patient_id'            => intval($data['patient_id']),
            'bill_id'               => intval($data['bill_id']),
            'insurance_provider_id' => intval($data['insurance_provider_id']),
            'policy_number'         => !empty($data['policy_number']) ? sanitize_text_field($data['policy_number']) : null,
            'claim_amount'          => !empty($data['claim_amount']) ? floatval($data['claim_amount']) : 0.00,
            'status'                => !empty($data['status']) ? sanitize_text_field($data['status']) : 'pending_preauth',
            'preauth_request_id'    => !empty($data['preauth_request_id']) ? sanitize_text_field($data['preauth_request_id']) : null,
            'preauth_status'        => !empty($data['preauth_status']) ? sanitize_text_field($data['preauth_status']) : 'pending',
            'notes'                 => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'initiated_by'          => intval($data['initiated_by']),
            'initiated_at'          => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%s');
        
        $claim_id = HMS_Helper::hms_create($table_name, $claim_data, $format);

        if ($claim_id) {
            HMS_Helper::log_action('Cashless Claim Initiated', 'Insurance Claim', $claim_id, json_encode($claim_data));
            return $claim_id;
        }
        return false;
    }

    /**
     * Get a single claim by ID.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @return   object|null                   The claim data object, or null if not found.
     */
    public function get_claim(int $claim_id): ?object {
        return HMS_Helper::hms_get_row('hms_claims', array('id' => $claim_id), array('%d'));
    }

    /**
     * Get all claims, with optional filtering.
     *
     * @since    1.0.0
     * @param    string    $status             Optional. Filter by claim status.
     * @param    int       $patient_id         Optional. Filter by patient ID.
     * @param    int       $insurance_provider_id Optional. Filter by insurance provider ID.
     * @return   array                          An array of claim data objects.
     */
    public function get_all_claims(string $status = '', int $patient_id = 0, int $insurance_provider_id = 0): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_claims';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }
        if ($patient_id > 0) {
            $where_clauses[] = "patient_id = %d";
            $where_values[] = $patient_id;
        }
        if ($insurance_provider_id > 0) {
            $where_clauses[] = "insurance_provider_id = %d";
            $where_values[] = $insurance_provider_id;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY initiated_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Update an existing claim's status.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @param    array     $data              An associative array of claim data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_claim_status(int $claim_id, array $data) {
        $table_name = 'hms_claims';
        $where = array('id' => $claim_id);
        $where_format = array('%d');

        $existing_claim = $this->get_claim($claim_id);
        if (!$existing_claim) {
            return new WP_Error('claim_not_found', esc_html__('Claim not found.', 'hospital-management-system'));
        }
        
        $claim_data = array();
        $format = array();

        $fields_to_update = array('status', 'preauth_status', 'preauth_request_id', 'claim_amount', 'notes', 'settled_amount', 'settled_at');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('claim_amount', 'settled_amount'))) {
                    $claim_data[$field] = floatval($data[$field]);
                } elseif ($field === 'settled_at') {
                    $claim_data[$field] = sanitize_text_field($data[$field]);
                } elseif ($field === 'notes') {
                    $claim_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $claim_data[$field] = sanitize_text_field($data[$field]);
                }
                if (in_array($field, array('claim_amount', 'settled_amount'))) {
                    $format[] = '%f'; // Use %f for floats
                } else {
                    $format[] = '%s'; // Use %s for others
                }
            }
        }
        
        if (empty($claim_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $claim_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Claim Status Updated', 'Insurance Claim', $claim_id, json_encode($claim_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a claim.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_claim(int $claim_id) {
        $existing_claim = $this->get_claim($claim_id);
        if (!$existing_claim) {
            return new WP_Error('claim_not_found', esc_html__('Claim not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_claims', array('id' => $claim_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Claim Deleted', 'Insurance Claim', $claim_id, json_encode($existing_claim));
            return true;
        }
        return false;
    }


    // --- TPA/Insurance Portal Integration Framework (Abstracted) ---

    /**
     * Send pre-authorization request to TPA/Insurance portal.
     * This is an abstracted function. Actual implementation will depend on the specific portal's API.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @return   array|WP_Error                An array of response data from the portal, or WP_Error on failure.
     */
    public function send_preauth_request(int $claim_id) {
        $claim = $this->get_claim($claim_id);
        if (!$claim) {
            return new WP_Error('claim_not_found', esc_html__('Claim not found.', 'hospital-management-system'));
        }

        // Placeholder for actual API call logic
        // 1. Prepare data payload as per TPA/Insurance portal's requirements.
        // 2. Use cURL or WordPress HTTP API to send the request.
        // 3. Handle the response (success/failure).
        // 4. Update the claim with preauth_request_id and preauth_status.

        // Simulating a response for now
        $preauth_request_id = 'PREAUTH_' . time() . '_' . $claim_id;
        $response_data = array(
            'success' => true,
            'preauth_request_id' => $preauth_request_id,
            'message' => 'Pre-authorization request sent successfully.'
        );

        // Update claim with the new preauth details
        $this->update_claim_status($claim_id, array(
            'preauth_request_id' => $preauth_request_id,
            'preauth_status' => 'submitted'
        ));

        return $response_data;
    }

    /**
     * Check pre-authorization status from TPA/Insurance portal.
     * This is an abstracted function. Actual implementation will depend on the specific portal's API.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @return   array|WP_Error                An array of response data from the portal, or WP_Error on failure.
     */
    public function check_preauth_status(int $claim_id) {
        $claim = $this->get_claim($claim_id);
        if (!$claim || empty($claim->preauth_request_id)) {
            return new WP_Error('invalid_claim', esc_html__('Invalid claim or pre-auth request not found.', 'hospital-management-system'));
        }

        // Placeholder for actual API call logic
        // 1. Use the stored preauth_request_id to query the portal.
        // 2. Handle the response.
        // 3. Update the claim with the latest preauth_status.

        // Simulating a response for now
        $response_data = array(
            'success' => true,
            'preauth_status' => 'approved', // Can be 'pending', 'approved', 'rejected'
            'message' => 'Pre-authorization approved.'
        );

        // Update claim with the new preauth status
        $this->update_claim_status($claim_id, array(
            'preauth_status' => $response_data['preauth_status']
        ));
        
        // If preauth is approved, update main claim status
        if ($response_data['preauth_status'] === 'approved') {
            $this->update_claim_status($claim_id, array(
                'status' => 'preauth_approved'
            ));
        }

        return $response_data;
    }

    /**
     * Submit final claim for settlement to TPA/Insurance portal.
     * This is an abstracted function. Actual implementation will depend on the specific portal's API.
     *
     * @since    1.0.0
     * @param    int       $claim_id           The claim's database ID.
     * @return   array|WP_Error                An array of response data from the portal, or WP_Error on failure.
     */
    public function submit_claim_for_settlement(int $claim_id) {
        $claim = $this->get_claim($claim_id);
        if (!$claim || $claim->status !== 'preauth_approved') {
            return new WP_Error('invalid_claim', esc_html__('Claim not ready for settlement.', 'hospital-management-system'));
        }

        // Placeholder for actual API call logic
        // 1. Prepare final claim data payload.
        // 2. Send to portal for settlement.
        // 3. Handle response.

        // Simulating a response for now
        $response_data = array(
            'success' => true,
            'settlement_id' => 'SETTLE_' . time() . '_' . $claim_id,
            'settled_amount' => $claim->claim_amount, // Assuming full settlement for now
            'message' => 'Claim submitted for settlement successfully.'
        );

        // Update claim with settlement details
        $this->update_claim_status($claim_id, array(
            'status' => 'submitted_for_settlement',
            'settled_amount' => $response_data['settled_amount']
        ));

        return $response_data;
    }


    // --- AJAX Handlers for Claims ---

    public function ajax_initiate_cashless_claim() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_insurance_claims')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'            => intval($_POST['patient_id']),
            'bill_id'               => intval($_POST['bill_id']),
            'insurance_provider_id' => intval($_POST['insurance_provider_id']),
            'policy_number'         => sanitize_text_field($_POST['policy_number']),
            'claim_amount'          => floatval($_POST['claim_amount']),
            'notes'                 => sanitize_textarea_field($_POST['notes']),
            'initiated_by'          => get_current_user_id(),
        );

        $claim_id = $this->initiate_cashless_claim($data);

        if (is_wp_error($claim_id)) {
            wp_send_json_error($claim_id->get_error_message());
        } elseif ($claim_id) {
            $claim = $this->get_claim($claim_id);
            wp_send_json_success(array(
                'message' => esc_html__('Cashless claim initiated successfully.', 'hospital-management-system'),
                'claim'   => $claim
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to initiate cashless claim.', 'hospital-management-system'));
        }
    }

    public function ajax_update_claim_status() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_insurance_claims')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $claim_id = intval($_POST['claim_id']);
        $data = array(
            'status'         => sanitize_text_field($_POST['status']),
            'preauth_status' => sanitize_text_field($_POST['preauth_status']),
            'notes'          => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_claim_status($claim_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $claim = $this->get_claim($claim_id);
            wp_send_json_success(array(
                'message' => esc_html__('Claim status updated successfully.', 'hospital-management-system'),
                'claim'   => $claim
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update claim status.', 'hospital-management-system'));
        }
    }

    public function ajax_get_claim_details() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_insurance_claims')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $claim_id = intval($_GET['claim_id']);
        $claim = $this->get_claim($claim_id);
        if ($claim) {
            wp_send_json_success($claim);
        } else {
            wp_send_json_error(esc_html__('Claim not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_claim() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_insurance_claims')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $claim_id = intval($_POST['claim_id']);
        
        $deleted = $this->delete_claim($claim_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Claim deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete claim.', 'hospital-management-system'));
        }
    }

    // --- Insurance Provider CRUD Operations ---

    /**
     * Add a new insurance provider.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of provider data.
     * @return   int|false                     The new provider's database ID, or false on failure.
     */
    public function add_insurance_provider(array $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';

        // Validate required fields
        if (empty($data['name']) || empty($data['provider_id_code'])) {
            return false;
        }

        // Check if provider_id already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE provider_id = %s",
            sanitize_text_field($data['provider_id_code'])
        ));
        
        if ($existing) {
            return false; // Provider ID already exists
        }

        $provider_data = array(
            'name'           => sanitize_text_field($data['name']),
            'provider_id'    => sanitize_text_field($data['provider_id_code']),
            'contact_person' => !empty($data['contact_person']) ? sanitize_text_field($data['contact_person']) : null,
            'phone'          => !empty($data['phone']) ? sanitize_text_field($data['phone']) : null,
            'email'          => !empty($data['email']) ? sanitize_email($data['email']) : null,
            'address'        => !empty($data['address']) ? sanitize_textarea_field($data['address']) : null,
            'website'        => !empty($data['website']) ? esc_url_raw($data['website']) : null,
            'status'         => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'notes'          => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_at'     => current_time('mysql'),
            'updated_at'     => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $result = $wpdb->insert($table_name, $provider_data, $format);

        if ($result !== false) {
            $provider_id = $wpdb->insert_id;
            HMS_Helper::log_action('Insurance Provider Added', 'Insurance Provider', $provider_id, json_encode($provider_data));
            return $provider_id;
        }
        return false;
    }

    /**
     * Get a single insurance provider by ID.
     *
     * @since    1.0.0
     * @param    int       $provider_id        The provider's database ID.
     * @return   object|null                   The provider data object, or null if not found.
     */
    public function get_insurance_provider(int $provider_id): ?object {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $provider_id));
    }

    /**
     * Get all insurance providers, with optional filtering.
     *
     * @since    1.0.0
     * @param    array     $filters            Optional filters (status, name, etc.).
     * @return   array                          An array of provider data objects.
     */
    public function get_all_insurance_providers(array $filters = []): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($filters['status'])) {
            $where_clauses[] = "status = %s";
            $where_values[] = $filters['status'];
        }
        if (!empty($filters['name'])) {
            $where_clauses[] = "name LIKE %s";
            $where_values[] = '%' . $wpdb->esc_like($filters['name']) . '%';
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY name ASC";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_results($query);
        }
    }

    /**
     * Update an existing insurance provider.
     *
     * @since    1.0.0
     * @param    int       $provider_id        The provider's database ID.
     * @param    array     $data              An associative array of provider data to update.
     * @return   bool                          True on success, false on failure.
     */
    public function update_insurance_provider(int $provider_id, array $data): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';

        $existing_provider = $this->get_insurance_provider($provider_id);
        if (!$existing_provider) {
            return false;
        }

        // Check if provider_id already exists for another provider
        if (!empty($data['provider_id_code']) && $data['provider_id_code'] !== $existing_provider->provider_id) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE provider_id = %s AND id != %d",
                sanitize_text_field($data['provider_id_code']),
                $provider_id
            ));
            
            if ($existing) {
                return false; // Provider ID already exists for another provider
            }
        }
        
        $provider_data = array();
        $format = array();

        $fields_to_update = array('name', 'provider_id_code', 'contact_person', 'phone', 'email', 'address', 'website', 'status', 'notes');
        foreach ($fields_to_update as $field) {
            $db_field = ($field === 'provider_id_code') ? 'provider_id' : $field;
            
            if (isset($data[$field])) {
                if ($field === 'email') {
                    $provider_data[$db_field] = sanitize_email($data[$field]);
                } elseif ($field === 'website') {
                    $provider_data[$db_field] = esc_url_raw($data[$field]);
                } elseif (in_array($field, array('address', 'notes'))) {
                    $provider_data[$db_field] = sanitize_textarea_field($data[$field]);
                } else {
                    $provider_data[$db_field] = sanitize_text_field($data[$field]);
                }
                $format[] = '%s';
            }
        }
        
        if (empty($provider_data)) {
            return true; // No data to update
        }

        $provider_data['updated_at'] = current_time('mysql');
        $format[] = '%s';

        $where = array('id' => $provider_id);
        $where_format = array('%d');

        $updated = $wpdb->update($table_name, $provider_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Insurance Provider Updated', 'Insurance Provider', $provider_id, json_encode($provider_data));
            return true;
        }
        return false;
    }

    /**
     * Delete an insurance provider.
     *
     * @since    1.0.0
     * @param    int       $provider_id        The provider's database ID.
     * @return   bool                          True on success, false on failure.
     */
    public function delete_insurance_provider(int $provider_id): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';

        $existing_provider = $this->get_insurance_provider($provider_id);
        if (!$existing_provider) {
            return false;
        }

        // Check if provider has associated claims
        $claims_table = $wpdb->prefix . 'hms_claims';
        $has_claims = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $claims_table WHERE insurance_provider_id = %d",
            $provider_id
        ));

        if ($has_claims > 0) {
            // Don't delete if there are associated claims, just deactivate
            return $this->update_insurance_provider($provider_id, array('status' => 'inactive'));
        }

        $deleted = $wpdb->delete($table_name, array('id' => $provider_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Insurance Provider Deleted', 'Insurance Provider', $provider_id, json_encode($existing_provider));
            return true;
        }
        return false;
    }
} // End of class HMS_Insurance
