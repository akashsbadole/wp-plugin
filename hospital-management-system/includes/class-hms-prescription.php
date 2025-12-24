<?php
/**
 * HMS Prescription Class
 *
 * Handles prescription management, including CRUD operations for prescriptions and prescription items.
 */

class HMS_Prescription {

    public function __construct() {
        // AJAX hooks for prescription management
        add_action('wp_ajax_hms_add_prescription', array($this, 'ajax_add_prescription'));
        add_action('wp_ajax_hms_update_prescription', array($this, 'ajax_update_prescription'));
        add_action('wp_ajax_hms_delete_prescription', array($this, 'ajax_delete_prescription'));
        add_action('wp_ajax_hms_get_prescription', array($this, 'ajax_get_prescription'));
        add_action('wp_ajax_hms_get_prescriptions_list', array($this, 'ajax_get_prescriptions_list'));
        add_action('wp_ajax_hms_get_appointment_doctor', array($this, 'ajax_get_appointment_doctor'));
        add_action('wp_ajax_hms_get_available_doctors', array($this, 'ajax_get_available_doctors'));
        add_action('wp_ajax_hms_update_prescription_status', array($this, 'ajax_update_prescription_status'));
    }

    /**
     * Get a single prescription by ID.
     */
    public function get_prescription($prescription_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $prescription_id));
    }

    /**
     * Get all prescription items for a specific prescription.
     */
    public function get_prescription_items($prescription_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescription_items';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE prescription_id = %d ORDER BY id ASC", $prescription_id));
    }

    /**
     * Get all prescriptions, with optional filtering.
     */
    public function get_prescriptions($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';
        
        // Debug: Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        error_log('HMS: Prescriptions table exists: ' . ($table_exists ? 'Yes' : 'No'));
        
        if (!$table_exists) {
            error_log('HMS: Prescriptions table does not exist');
            return array();
        }

        $defaults = array(
            'patient_id' => 0,
            'doctor_id' => 0,
            'start_date' => '',
            'end_date' => '',
            'status' => '',
            'orderby' => 'issue_date',
            'order' => 'DESC',
            'offset' => 0,
            'per_page' => 20
        );
        $args = wp_parse_args($args, $defaults);

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['patient_id'])) {
            $where_clauses[] = "patient_id = %d";
            $prepare_values[] = intval($args['patient_id']);
        }
        if (!empty($args['doctor_id'])) {
            $where_clauses[] = "doctor_id = %d";
            $prepare_values[] = intval($args['doctor_id']);
        }
        if (!empty($args['start_date'])) {
            $where_clauses[] = "issue_date >= %s";
            $prepare_values[] = sanitize_text_field($args['start_date']);
        }
        if (!empty($args['end_date'])) {
            $where_clauses[] = "issue_date <= %s";
            $prepare_values[] = sanitize_text_field($args['end_date']);
        }
        if (!empty($args['status'])) {
            $where_clauses[] = "status = %s";
            $prepare_values[] = sanitize_text_field($args['status']);
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM $table_name $where_sql ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']) . " LIMIT %d OFFSET %d";
        $prepare_values[] = intval($args['per_page']);
        $prepare_values[] = intval($args['offset']);
        
        error_log('HMS: Query: ' . $query);
        error_log('HMS: Prepare values: ' . print_r($prepare_values, true));
        
        $results = $wpdb->get_results($wpdb->prepare($query, $prepare_values));
        error_log('HMS: Query results: ' . print_r($results, true));
        
        return $results;
    }

    /**
     * Get total count of prescriptions for pagination.
     */
    public function get_prescriptions_count($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['patient_id'])) {
            $where_clauses[] = "patient_id = %d";
            $prepare_values[] = intval($args['patient_id']);
        }
        if (!empty($args['doctor_id'])) {
            $where_clauses[] = "doctor_id = %d";
            $prepare_values[] = intval($args['doctor_id']);
        }
        if (!empty($args['start_date'])) {
            $where_clauses[] = "issue_date >= %s";
            $prepare_values[] = sanitize_text_field($args['start_date']);
        }
        if (!empty($args['end_date'])) {
            $where_clauses[] = "issue_date <= %s";
            $prepare_values[] = sanitize_text_field($args['end_date']);
        }
        if (!empty($args['status'])) {
            $where_clauses[] = "status = %s";
            $prepare_values[] = sanitize_text_field($args['status']);
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM $table_name $where_sql";
        if (!empty($prepare_values)) {
            return $wpdb->get_var($wpdb->prepare($query, $prepare_values));
        }
        return $wpdb->get_var($query);
    }

    /**
     * Generate a unique prescription number.
     */
    private function generate_prescription_number() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';
        $prefix = 'RX-' . gmdate('Ym') . '-';
        $last_prescription = $wpdb->get_row($wpdb->prepare("SELECT prescription_number FROM $table_name WHERE prescription_number LIKE %s ORDER BY id DESC LIMIT 1", $prefix . '%'));
        
        if ($last_prescription) {
            $last_number = intval(str_replace($prefix, '', $last_prescription->prescription_number));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        return $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Add a new prescription.
     */
    public function add_prescription($data) {
        global $wpdb;
        $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';
        $prescription_items_table = $wpdb->prefix . 'hms_prescription_items';

        $prescription_data = array(
            'prescription_number' => $this->generate_prescription_number(),
            'patient_id' => intval($data['patient_id']),
            'doctor_id' => intval($data['doctor_id']),
            'appointment_id' => !empty($data['appointment_id']) ? intval($data['appointment_id']) : null,
            'issue_date' => sanitize_text_field($data['issue_date']),
            'status' => sanitize_text_field($data['status']),
            'diagnosis' => sanitize_textarea_field($data['diagnosis']),
            'notes' => sanitize_textarea_field($data['notes']),
            'created_at' => current_time('mysql'),
        );

        $format = array('%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s');
        
        $result = $wpdb->insert($prescriptions_table, $prescription_data, $format);

        if ($result) {
            $prescription_id = $wpdb->insert_id;
            // Insert prescription items
            if (!empty($data['medicines']) && is_array($data['medicines'])) {
                foreach ($data['medicines'] as $item) {
                    $item_data = array(
                        'prescription_id' => $prescription_id,
                        'medicine_name' => sanitize_text_field($item['medicine_name']),
                        'dosage' => sanitize_text_field($item['dosage']),
                        'frequency' => sanitize_text_field($item['frequency']),
                        'duration' => sanitize_text_field($item['duration']),
                        'instructions' => sanitize_textarea_field($item['instructions']),
                    );
                    $wpdb->insert($prescription_items_table, $item_data, array('%d', '%s', '%s', '%s', '%s', '%s'));
                }
            }

            // Log the action
            HMS_Helper::log_action('Prescription Added', 'Prescription', $prescription_id, json_encode($prescription_data));
            
            return $prescription_id;
        }
        return false;
    }

    /**
     * Update an existing prescription.
     */
    public function update_prescription($prescription_id, $data) {
        global $wpdb;
        $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';
        $prescription_items_table = $wpdb->prefix . 'hms_prescription_items';

        $prescription_data = array(
            'patient_id' => intval($data['patient_id']),
            'doctor_id' => intval($data['doctor_id']),
            'appointment_id' => !empty($data['appointment_id']) ? intval($data['appointment_id']) : null,
            'issue_date' => sanitize_text_field($data['issue_date']),
            'status' => sanitize_text_field($data['status']),
            'diagnosis' => sanitize_textarea_field($data['diagnosis']),
            'notes' => sanitize_textarea_field($data['notes']),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => $prescription_id);
        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s');
        $where_format = array('%d');

        $result = $wpdb->update($prescriptions_table, $prescription_data, $where, $format, $where_format);
        
        if ($result !== false) {
            // Delete existing prescription items and re-insert new ones
            $wpdb->delete($prescription_items_table, array('prescription_id' => $prescription_id), array('%d'));
            if (!empty($data['medicines']) && is_array($data['medicines'])) {
                foreach ($data['medicines'] as $item) {
                    $item_data = array(
                        'prescription_id' => $prescription_id,
                        'medicine_name' => sanitize_text_field($item['medicine_name']),
                        'dosage' => sanitize_text_field($item['dosage']),
                        'frequency' => sanitize_text_field($item['frequency']),
                        'duration' => sanitize_text_field($item['duration']),
                        'instructions' => sanitize_textarea_field($item['instructions']),
                    );
                    $wpdb->insert($prescription_items_table, $item_data, array('%d', '%s', '%s', '%s', '%s', '%s'));
                }
            }
            
            // Log the action
            HMS_Helper::log_action('Prescription Updated', 'Prescription', $prescription_id, json_encode($prescription_data));
            
            return true;
        }
        return false;
    }

    /**
     * Delete a prescription.
     */
    public function delete_prescription($prescription_id) {
        global $wpdb;
        $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';
        $prescription_items_table = $wpdb->prefix . 'hms_prescription_items';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete prescription items first
            $wpdb->delete($prescription_items_table, array('prescription_id' => $prescription_id), array('%d'));
            // Then delete the prescription
            $result = $wpdb->delete($prescriptions_table, array('id' => $prescription_id), array('%d'));
            
            if ($result) {
                $wpdb->query('COMMIT');
                HMS_Helper::log_action('Prescription Deleted', 'Prescription', $prescription_id);
                return true;
            } else {
                $wpdb->query('ROLLBACK');
                return false;
            }
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

    /**
     * AJAX handler to add a new prescription.
     */
    public function ajax_add_prescription() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['issue_date'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'doctor_id' => intval($_POST['doctor_id']),
            'appointment_id' => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
            'issue_date' => sanitize_text_field($_POST['issue_date']),
            'status' => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active',
            'diagnosis' => sanitize_textarea_field($_POST['diagnosis']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'medicines' => isset($_POST['medicines']) ? json_decode(stripslashes($_POST['medicines']), true) : array(),
        );

        $prescription_id = $this->add_prescription($data);

        if ($prescription_id) {
            $prescription = $this->get_prescription($prescription_id);
            wp_send_json_success(array(
                'message' => esc_html__('Prescription created successfully.', 'hospital-management-system'),
                'prescription' => $prescription,
                'redirect_url' => admin_url('admin.php?page=hms-prescriptions&message=' . urlencode(esc_html__('Prescription created successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to create prescription.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update a prescription.
     */
    public function ajax_update_prescription() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $prescription_id = intval($_POST['prescription_id']);
        
        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['issue_date'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'doctor_id' => intval($_POST['doctor_id']),
            'appointment_id' => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
            'issue_date' => sanitize_text_field($_POST['issue_date']),
            'status' => sanitize_text_field($_POST['status']),
            'diagnosis' => sanitize_textarea_field($_POST['diagnosis']),
            'notes' => sanitize_textarea_field($_POST['notes']),
            'medicines' => isset($_POST['medicines']) ? json_decode(stripslashes($_POST['medicines']), true) : array(),
        );

        $updated = $this->update_prescription($prescription_id, $data);

        if ($updated) {
            $prescription = $this->get_prescription($prescription_id);
            wp_send_json_success(array(
                'message' => esc_html__('Prescription updated successfully.', 'hospital-management-system'),
                'prescription' => $prescription,
                'redirect_url' => admin_url('admin.php?page=hms-prescriptions&message=' . urlencode(esc_html__('Prescription updated successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update prescription.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to delete a prescription.
     */
    public function ajax_delete_prescription() {
        check_ajax_referer('hms_admin_nonce', 'nonce'); // Using a general nonce for now
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $prescription_id = intval($_POST['prescription_id']);
        $deleted = $this->delete_prescription($prescription_id);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Prescription deleted successfully.', 'hospital-management-system'),
                'redirect_url' => admin_url('admin.php?page=hms-prescriptions&message=' . urlencode(esc_html__('Prescription deleted successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete prescription.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single prescription's details.
     */
    public function ajax_get_prescription() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        $prescription_id = intval($_GET['prescription_id']);
        $prescription = $this->get_prescription($prescription_id);
        if ($prescription) {
            $prescription_items = $this->get_prescription_items($prescription_id);
            // Convert prescription_items to medicines format for frontend
            $medicines = array();
            foreach ($prescription_items as $item) {
                $medicines[] = array(
                    'medicine_name' => $item->medicine_name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration' => $item->duration,
                    'instructions' => $item->instructions
                );
            }
            wp_send_json_success(array('prescription' => $prescription, 'medicines' => $medicines));
        } else {
            wp_send_json_error(esc_html__('Prescription not found.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get doctors for appointment-based auto-filling.
     */
    public function ajax_get_appointment_doctor() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        
        $appointment_id = intval($_GET['appointment_id']);
        if (!$appointment_id) {
            wp_send_json_error(esc_html__('Invalid appointment ID.', 'hospital-management-system'));
        }
        
        global $wpdb;
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT doctor_id, patient_id FROM {$wpdb->prefix}hms_appointments WHERE id = %d",
            $appointment_id
        ));
        
        if ($appointment) {
            wp_send_json_success(array(
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id
            ));
        } else {
            wp_send_json_error(esc_html__('Appointment not found.', 'hospital-management-system'));
        }
    }

    /**
     * Get available doctors from both hms_staff table and WordPress users.
     */
    public function get_available_doctors() {
        global $wpdb;
        $doctors = array();
        
        // First, try to get doctors from hms_staff table
        $staff_doctors = $wpdb->get_results(
            "SELECT id, first_name, last_name, 'staff' as source FROM {$wpdb->prefix}hms_staff WHERE role = 'doctor' AND status = 'active' ORDER BY first_name, last_name"
        );
        
        foreach ($staff_doctors as $doctor) {
            $doctors[] = array(
                'id' => $doctor->id,
                'name' => $doctor->first_name . ' ' . $doctor->last_name,
                'source' => 'staff'
            );
        }
        
        // If no doctors found in staff table, fallback to WordPress users
        if (empty($doctors)) {
            $wp_doctors = get_users(array('role' => 'hms_doctor', 'fields' => array('ID', 'display_name')));
            if (empty($wp_doctors)) {
                // If no hms_doctor role users, try administrators
                $wp_doctors = get_users(array('role' => 'administrator', 'fields' => array('ID', 'display_name')));
            }
            if (empty($wp_doctors)) {
                // If still no doctors found, get all users
                $wp_doctors = get_users(array('fields' => array('ID', 'display_name')));
            }
            
            foreach ($wp_doctors as $doctor) {
                $doctors[] = array(
                    'id' => $doctor->ID,
                    'name' => $doctor->display_name,
                    'source' => 'wp_user'
                );
            }
        }
        
        return $doctors;
    }

    /**
     * AJAX handler to get available doctors.
     */
    public function ajax_get_available_doctors() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        
        $doctors = $this->get_available_doctors();
        wp_send_json_success($doctors);
    }

    /**
     * Update prescription status.
     */
    public function update_prescription_status($prescription_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';
        
        $valid_statuses = array('active', 'completed', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $prescription_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Log the action
            HMS_Helper::log_action('Prescription Status Updated', 'Prescription', $prescription_id, "Status changed to: $status");
            return true;
        }
        return false;
    }

    /**
     * AJAX handler to update prescription status.
     */
    public function ajax_update_prescription_status() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        
        $prescription_id = intval($_POST['prescription_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!$prescription_id || !$status) {
            wp_send_json_error(esc_html__('Invalid prescription ID or status.', 'hospital-management-system'));
        }
        
        $updated = $this->update_prescription_status($prescription_id, $status);
        
        if ($updated) {
            wp_send_json_success(array(
                'message' => esc_html__('Prescription status updated successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update prescription status.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a list of prescriptions for DataTables or similar.
     */
    public function ajax_get_prescriptions_list() {
        error_log('HMS: ajax_get_prescriptions_list called');
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            error_log('HMS: User does not have permission to manage clinical');
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        error_log('HMS: User has permission, proceeding to get prescriptions');

        $args = array(
            'patient_id' => isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0,
            'doctor_id' => isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0,
            'start_date' => isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '',
            'end_date' => isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '',
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'offset' => isset($_GET['start']) ? intval($_GET['start']) : 0,
            'per_page' => isset($_GET['length']) ? intval($_GET['length']) : 20,
        );
        
        $prescriptions = $this->get_prescriptions($args);
        $total_prescriptions = $this->get_prescriptions_count($args);
        
        error_log('HMS: Found ' . count($prescriptions) . ' prescriptions');
        error_log('HMS: Total prescriptions count: ' . $total_prescriptions);
        
        $data = array();
        foreach ($prescriptions as $prescription) {
            error_log('HMS: Processing prescription ID: ' . $prescription->id);
            // Get patient name
            global $wpdb;
            $patient = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name, patient_id FROM {$wpdb->prefix}hms_patients WHERE id = %d", $prescription->patient_id));
            $patient_name = $patient ? $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')' : esc_html__('N/A', 'hospital-management-system');
            
            // Get doctor name from hms_staff table first
            $doctor = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}hms_staff WHERE id = %d AND role = 'doctor'", $prescription->doctor_id));
            if ($doctor) {
                $doctor_name = $doctor->first_name . ' ' . $doctor->last_name;
            } else {
                // Fallback to WordPress user
                $wp_user = get_user_by('ID', $prescription->doctor_id);
                $doctor_name = $wp_user ? $wp_user->display_name : esc_html__('N/A', 'hospital-management-system');
            }
            
            $data[] = array(
                'id' => $prescription->id,
                'date' => date_i18n(get_option('date_format'), strtotime($prescription->issue_date)),
                'patient' => $patient_name,
                'doctor' => $doctor_name,
                'diagnosis' => $prescription->diagnosis,
                'status' => ucfirst($prescription->status),
                'actions' => '' // Action buttons will be added by JS
            );
        }

        $response = array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => $total_prescriptions,
            'recordsFiltered' => $total_prescriptions,
            'data' => $data
        );
        error_log('HMS: Sending response: ' . print_r($response, true));
        wp_send_json($response);
    }
}
