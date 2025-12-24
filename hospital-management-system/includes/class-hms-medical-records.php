<?php
/**
 * HMS Medical Records Class
 *
 * Handles medical records management including creation, retrieval, 
 * updating, and deletion of patient medical records.
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

class HMS_Medical_Records {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for medical records
        add_action('wp_ajax_hms_add_medical_record', array($this, 'ajax_add_medical_record'));
        add_action('wp_ajax_hms_update_medical_record', array($this, 'ajax_update_medical_record'));
        add_action('wp_ajax_hms_get_medical_record', array($this, 'ajax_get_medical_record'));
        add_action('wp_ajax_hms_delete_medical_record', array($this, 'ajax_delete_medical_record'));
        add_action('wp_ajax_hms_search_medical_records', array($this, 'ajax_search_medical_records'));
        
        // Enhanced AJAX hooks for comprehensive medical history
        add_action('wp_ajax_hms_quick_edit_medical_record', array($this, 'ajax_quick_edit_medical_record'));
        add_action('wp_ajax_hms_export_medical_records', array($this, 'ajax_export_medical_records'));
        add_action('wp_ajax_hms_load_more_medical_records', array($this, 'ajax_load_more_medical_records'));
    }

    /**
     * Create a new medical record.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of medical record data.
     * @return   int|false                     The new record's database ID, or false on failure.
     */
    public function create_medical_record(array $data) {
        if (empty($data['patient_id']) || empty($data['doctor_id'])) {
            return false;
        }

        $record_data = array(
            'patient_id'        => intval($data['patient_id']),
            'appointment_id'    => !empty($data['appointment_id']) ? intval($data['appointment_id']) : null,
            'doctor_id'         => intval($data['doctor_id']),
            'record_type'       => !empty($data['record_type']) ? sanitize_text_field($data['record_type']) : 'consultation',
            'diagnosis'         => !empty($data['diagnosis']) ? sanitize_textarea_field($data['diagnosis']) : null,
            'symptoms'          => !empty($data['symptoms']) ? sanitize_textarea_field($data['symptoms']) : null,
            'treatment_plan'    => !empty($data['treatment_plan']) ? sanitize_textarea_field($data['treatment_plan']) : null,
            'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_at'        => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $record_id = HMS_Helper::hms_create('hms_medical_records', $record_data, $format);

        if ($record_id) {
            HMS_Helper::log_action('Medical Record Created', 'Medical Record', $record_id, json_encode($record_data));
            return $record_id;
        }
        return false;
    }

    /**
     * Get a single medical record by ID.
     *
     * @since    1.0.0
     * @param    int       $record_id          The record's database ID.
     * @return   object|null                   The record data object, or null if not found.
     */
    public function get_medical_record(int $record_id): ?object {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medical_records';
        
        $query = $wpdb->prepare(
            "SELECT mr.*, 
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.patient_id as patient_code,
                    CONCAT(s.first_name, ' ', s.last_name) as doctor_name
             FROM {$table_name} mr
             LEFT JOIN {$wpdb->prefix}hms_patients p ON mr.patient_id = p.id
             LEFT JOIN {$wpdb->prefix}hms_staff s ON mr.doctor_id = s.id
             WHERE mr.id = %d",
            $record_id
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Get all medical records with patient and doctor names.
     *
     * @since    1.0.0
     * @param    array     $filters            Optional filters (patient_id, doctor_id, record_type, date_from, date_to).
     * @param    int       $limit              Optional limit for pagination.
     * @param    int       $offset             Optional offset for pagination.
     * @return   array                          An array of medical record objects.
     */
    public function get_medical_records(array $filters = array(), int $limit = 0, int $offset = 0): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medical_records';
        
        $where_clauses = array();
        $where_values = array();

        // Build WHERE clauses based on filters
        if (!empty($filters['patient_id'])) {
            $where_clauses[] = "mr.patient_id = %d";
            $where_values[] = intval($filters['patient_id']);
        }
        
        if (!empty($filters['doctor_id'])) {
            $where_clauses[] = "mr.doctor_id = %d";
            $where_values[] = intval($filters['doctor_id']);
        }
        
        if (!empty($filters['record_type'])) {
            $where_clauses[] = "mr.record_type = %s";
            $where_values[] = sanitize_text_field($filters['record_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(mr.created_at) >= %s";
            $where_values[] = sanitize_text_field($filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(mr.created_at) <= %s";
            $where_values[] = sanitize_text_field($filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $where_clauses[] = "(mr.diagnosis LIKE %s OR mr.symptoms LIKE %s OR CONCAT(p.first_name, ' ', p.last_name) LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $limit_clause = '';
        if ($limit > 0) {
            $limit_clause = $wpdb->prepare("LIMIT %d", $limit);
            if ($offset > 0) {
                $limit_clause = $wpdb->prepare("LIMIT %d, %d", $offset, $limit);
            }
        }

        $query = "SELECT mr.*, 
                         CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                         p.patient_id as patient_code,
                         CONCAT(s.first_name, ' ', s.last_name) as doctor_name
                  FROM {$table_name} mr
                  LEFT JOIN {$wpdb->prefix}hms_patients p ON mr.patient_id = p.id
                  LEFT JOIN {$wpdb->prefix}hms_staff s ON mr.doctor_id = s.id
                  {$where_clause}
                  ORDER BY mr.created_at DESC
                  {$limit_clause}";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_results($query);
        }
    }

    /**
     * Get medical records for a specific patient in chronological order.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @param    int       $limit              Optional limit for records.
     * @return   array                          An array of medical record objects.
     */
    public function get_patient_medical_history(int $patient_id, int $limit = 0): array {
        return $this->get_medical_records(array('patient_id' => $patient_id), $limit);
    }

    /**
     * Update an existing medical record.
     *
     * @since    1.0.0
     * @param    int       $record_id          The record's database ID.
     * @param    array     $data              An associative array of record data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_medical_record(int $record_id, array $data) {
        $existing_record = $this->get_medical_record($record_id);
        if (!$existing_record) {
            return new WP_Error('record_not_found', esc_html__('Medical record not found.', 'hospital-management-system'));
        }
        
        $record_data = array();
        $format = array();

        $fields_to_update = array('record_type', 'diagnosis', 'symptoms', 'treatment_plan', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('diagnosis', 'symptoms', 'treatment_plan', 'notes'))) {
                    $record_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $record_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = '%s';
            }
        }
        
        if (empty($record_data)) {
            return true; // No data to update
        }

        // Add updated timestamp
        $record_data['updated_at'] = current_time('mysql');
        $format[] = '%s';

        $updated = HMS_Helper::hms_update(
            'hms_medical_records', 
            $record_data, 
            array('id' => $record_id), 
            $format, 
            array('%d')
        );
        
        if ($updated !== false) {
            HMS_Helper::log_action('Medical Record Updated', 'Medical Record', $record_id, json_encode($record_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a medical record.
     *
     * @since    1.0.0
     * @param    int       $record_id          The record's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_medical_record(int $record_id) {
        $existing_record = $this->get_medical_record($record_id);
        if (!$existing_record) {
            return new WP_Error('record_not_found', esc_html__('Medical record not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_medical_records', array('id' => $record_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Medical Record Deleted', 'Medical Record', $record_id, json_encode($existing_record));
            return true;
        }
        return false;
    }

    /**
     * Get count of medical records with optional filters.
     *
     * @since    1.0.0
     * @param    array     $filters            Optional filters.
     * @return   int                            Count of records.
     */
    public function get_medical_records_count(array $filters = array()): int {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medical_records';
        
        $where_clauses = array();
        $where_values = array();

        // Build WHERE clauses based on filters (same as get_medical_records)
        if (!empty($filters['patient_id'])) {
            $where_clauses[] = "mr.patient_id = %d";
            $where_values[] = intval($filters['patient_id']);
        }
        
        if (!empty($filters['doctor_id'])) {
            $where_clauses[] = "mr.doctor_id = %d";
            $where_values[] = intval($filters['doctor_id']);
        }
        
        if (!empty($filters['record_type'])) {
            $where_clauses[] = "mr.record_type = %s";
            $where_values[] = sanitize_text_field($filters['record_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = "DATE(mr.created_at) >= %s";
            $where_values[] = sanitize_text_field($filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = "DATE(mr.created_at) <= %s";
            $where_values[] = sanitize_text_field($filters['date_to']);
        }
        
        if (!empty($filters['search'])) {
            $where_clauses[] = "(mr.diagnosis LIKE %s OR mr.symptoms LIKE %s OR CONCAT(p.first_name, ' ', p.last_name) LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) 
                  FROM {$table_name} mr
                  LEFT JOIN {$wpdb->prefix}hms_patients p ON mr.patient_id = p.id
                  {$where_clause}";
        
        if (!empty($where_values)) {
            return intval($wpdb->get_var($wpdb->prepare($query, $where_values)));
        } else {
            return intval($wpdb->get_var($query));
        }
    }

    // --- AJAX Handlers ---

    public function ajax_add_medical_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'        => intval($_POST['patient_id']),
            'appointment_id'    => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
            'doctor_id'         => intval($_POST['doctor_id']),
            'record_type'       => sanitize_text_field($_POST['record_type']),
            'diagnosis'         => sanitize_textarea_field($_POST['diagnosis']),
            'symptoms'          => sanitize_textarea_field($_POST['symptoms']),
            'treatment_plan'    => sanitize_textarea_field($_POST['treatment_plan']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $record_id = $this->create_medical_record($data);

        if (is_wp_error($record_id)) {
            wp_send_json_error($record_id->get_error_message());
        } elseif ($record_id) {
            $record = $this->get_medical_record($record_id);
            wp_send_json_success(array(
                'message' => esc_html__('Medical record created successfully.', 'hospital-management-system'),
                'record'  => $record
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to create medical record.', 'hospital-management-system'));
        }
    }

    public function ajax_update_medical_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $record_id = intval($_POST['record_id']);
        $data = array(
            'record_type'       => sanitize_text_field($_POST['record_type']),
            'diagnosis'         => sanitize_textarea_field($_POST['diagnosis']),
            'symptoms'          => sanitize_textarea_field($_POST['symptoms']),
            'treatment_plan'    => sanitize_textarea_field($_POST['treatment_plan']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $result = $this->update_medical_record($record_id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } elseif ($result) {
            $record = $this->get_medical_record($record_id);
            wp_send_json_success(array(
                'message' => esc_html__('Medical record updated successfully.', 'hospital-management-system'),
                'record'  => $record
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update medical record.', 'hospital-management-system'));
        }
    }

    public function ajax_get_medical_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $record_id = intval($_POST['record_id']);
        $record = $this->get_medical_record($record_id);

        if ($record) {
            wp_send_json_success($record);
        } else {
            wp_send_json_error(esc_html__('Medical record not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_medical_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $record_id = intval($_POST['record_id']);
        $result = $this->delete_medical_record($record_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } elseif ($result) {
            wp_send_json_success(array(
                'message' => esc_html__('Medical record deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete medical record.', 'hospital-management-system'));
        }
    }

    public function ajax_search_medical_records() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $filters = array();
        
        if (!empty($_POST['search'])) {
            $filters['search'] = sanitize_text_field($_POST['search']);
        }
        
        if (!empty($_POST['patient_id'])) {
            $filters['patient_id'] = intval($_POST['patient_id']);
        }
        
        if (!empty($_POST['doctor_id'])) {
            $filters['doctor_id'] = intval($_POST['doctor_id']);
        }
        
        if (!empty($_POST['record_type'])) {
            $filters['record_type'] = sanitize_text_field($_POST['record_type']);
        }
        
        if (!empty($_POST['date_from'])) {
            $filters['date_from'] = sanitize_text_field($_POST['date_from']);
        }
        
        if (!empty($_POST['date_to'])) {
            $filters['date_to'] = sanitize_text_field($_POST['date_to']);
        }

        $limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 20;
        $offset = !empty($_POST['offset']) ? intval($_POST['offset']) : 0;

        $records = $this->get_medical_records($filters, $limit, $offset);
        $total = $this->get_medical_records_count($filters);

        wp_send_json_success(array(
            'records' => $records,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ));
    }



    /**
     * AJAX handler for quick editing medical records (inline editing).
     *
     * @since    1.0.0
     */
    public function ajax_quick_edit_medical_record() {
        check_ajax_referer('hms_quick_edit_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $record_id = intval($_POST['record_id']);
        $data = array(
            'diagnosis'         => sanitize_textarea_field($_POST['diagnosis']),
            'treatment_plan'    => sanitize_textarea_field($_POST['treatment_plan']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $result = $this->update_medical_record($record_id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } elseif ($result) {
            $record = $this->get_medical_record($record_id);
            
            // Format the updated timestamp for display
            $record->updated_at_formatted = date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'), 
                strtotime($record->updated_at)
            );
            
            wp_send_json_success(array(
                'message' => esc_html__('Medical record updated successfully.', 'hospital-management-system'),
                'record'  => $record
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update medical record.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for exporting filtered medical records.
     *
     * @since    1.0.0
     */
    public function ajax_export_medical_records() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        $record_ids = !empty($_POST['record_ids']) ? explode(',', sanitize_text_field($_POST['record_ids'])) : array();
        $filters = !empty($_POST['filters']) ? json_decode(stripslashes($_POST['filters']), true) : array();

        if (empty($record_ids)) {
            wp_send_json_error(esc_html__('No records selected for export.', 'hospital-management-system'));
        }

        // Get patient information
        if (!class_exists('HMS_Patient')) {
            require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
        }
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient($patient_id);

        if (!$patient) {
            wp_send_json_error(esc_html__('Patient not found.', 'hospital-management-system'));
        }

        // Get the filtered records
        $records = array();
        foreach ($record_ids as $record_id) {
            $record = $this->get_medical_record(intval($record_id));
            if ($record) {
                $records[] = $record;
            }
        }

        if (empty($records)) {
            wp_send_json_error(esc_html__('No valid records found for export.', 'hospital-management-system'));
        }

        // Generate CSV content
        $csv_content = $this->generate_medical_records_csv($patient, $records, $filters);
        
        // Set headers for file download
        $filename = 'medical_records_' . $patient->patient_id . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        echo $csv_content;
        exit;
    }

    /**
     * Generate CSV content for medical records export.
     *
     * @since    1.0.0
     * @param    object    $patient    Patient object
     * @param    array     $records    Array of medical record objects
     * @param    array     $filters    Applied filters for context
     * @return   string                CSV content
     */
    private function generate_medical_records_csv($patient, $records, $filters = array()) {
        $csv_lines = array();
        
        // Add header information
        $csv_lines[] = '"Medical Records Export"';
        $csv_lines[] = '"Patient: ' . $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')"';
        $csv_lines[] = '"Export Date: ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . '"';
        $csv_lines[] = '"Total Records: ' . count($records) . '"';
        
        if (!empty($filters)) {
            $csv_lines[] = '"Applied Filters: ' . json_encode($filters) . '"';
        }
        
        $csv_lines[] = ''; // Empty line
        
        // Add column headers
        $headers = array(
            'Record ID',
            'Date',
            'Time',
            'Record Type',
            'Doctor',
            'Diagnosis',
            'Symptoms',
            'Treatment Plan',
            'Notes',
            'Created At',
            'Last Updated'
        );
        $csv_lines[] = '"' . implode('","', $headers) . '"';
        
        // Add record data
        foreach ($records as $record) {
            $row = array(
                $record->id,
                date_i18n(get_option('date_format'), strtotime($record->created_at)),
                date_i18n(get_option('time_format'), strtotime($record->created_at)),
                ucfirst(str_replace('_', ' ', $record->record_type)),
                $record->doctor_name,
                $record->diagnosis,
                $record->symptoms,
                $record->treatment_plan,
                $record->notes,
                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->created_at)),
                $record->updated_at ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->updated_at)) : ''
            );
            
            // Escape and quote each field
            $escaped_row = array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row);
            
            $csv_lines[] = implode(',', $escaped_row);
        }
        
        return implode("\n", $csv_lines);
    }

    /**
     * Enhanced load more medical records with better pagination.
     *
     * @since    1.0.0
     */
    public function ajax_load_more_medical_records() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        $offset = intval($_POST['offset']);
        $limit = intval($_POST['limit']);

        if ($patient_id <= 0) {
            wp_send_json_error(esc_html__('Invalid patient ID.', 'hospital-management-system'));
        }

        // Get additional records with offset
        $records = $this->get_medical_records(array('patient_id' => $patient_id), $limit, $offset);
        
        // Format records for frontend display
        $formatted_records = array();
        foreach ($records as $record) {
            $formatted_record = (array) $record;
            $formatted_record['created_at_formatted'] = date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'), 
                strtotime($record->created_at)
            );
            $formatted_record['updated_at_formatted'] = $record->updated_at ? date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'), 
                strtotime($record->updated_at)
            ) : '';
            $formatted_records[] = $formatted_record;
        }
        
        wp_send_json_success(array(
            'records' => $formatted_records,
            'has_more' => count($records) === $limit
        ));
    }

    /**
     * Get comprehensive medical history statistics for a patient.
     *
     * @since    1.0.0
     * @param    int       $patient_id    Patient ID
     * @return   array                     Statistics array
     */
    public function get_patient_medical_statistics($patient_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medical_records';
        
        $stats = array(
            'total_records' => 0,
            'by_type' => array(),
            'recent_activity' => array(),
            'most_common_diagnoses' => array()
        );
        
        // Get total records count
        $stats['total_records'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE patient_id = %d",
            $patient_id
        ));
        
        // Get records by type
        $type_counts = $wpdb->get_results($wpdb->prepare(
            "SELECT record_type, COUNT(*) as count 
             FROM {$table_name} 
             WHERE patient_id = %d 
             GROUP BY record_type 
             ORDER BY count DESC",
            $patient_id
        ));
        
        foreach ($type_counts as $type_count) {
            $stats['by_type'][$type_count->record_type] = intval($type_count->count);
        }
        
        // Get recent activity (last 30 days)
        $recent_activity = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM {$table_name} 
             WHERE patient_id = %d 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) 
             ORDER BY date DESC",
            $patient_id
        ));
        
        foreach ($recent_activity as $activity) {
            $stats['recent_activity'][$activity->date] = intval($activity->count);
        }
        
        // Get most common diagnoses (top 5)
        $common_diagnoses = $wpdb->get_results($wpdb->prepare(
            "SELECT diagnosis, COUNT(*) as count 
             FROM {$table_name} 
             WHERE patient_id = %d 
             AND diagnosis IS NOT NULL 
             AND diagnosis != ''
             GROUP BY diagnosis 
             ORDER BY count DESC 
             LIMIT 5",
            $patient_id
        ));
        
        foreach ($common_diagnoses as $diagnosis) {
            $stats['most_common_diagnoses'][] = array(
                'diagnosis' => $diagnosis->diagnosis,
                'count' => intval($diagnosis->count)
            );
        }
        
        return $stats;
    }
}