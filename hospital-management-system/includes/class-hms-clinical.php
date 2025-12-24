<?php
/**
 * HMS Clinical Class
 *
 * Handles clinical data management like patient vitals, medication logs,
 * and other clinical notes.
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

class HMS_Clinical {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for patient vitals
        add_action('wp_ajax_hms_add_patient_vitals', array($this, 'ajax_add_patient_vitals'));
        add_action('wp_ajax_hms_update_patient_vitals', array($this, 'ajax_update_patient_vitals'));
        add_action('wp_ajax_hms_get_patient_vitals', array($this, 'ajax_get_patient_vitals'));
        add_action('wp_ajax_hms_delete_patient_vitals', array($this, 'ajax_delete_patient_vitals'));

        // AJAX hooks for medication logs
        add_action('wp_ajax_hms_log_medication_administration', array($this, 'ajax_log_medication_administration'));
        add_action('wp_ajax_hms_update_medication_log', array($this, 'ajax_update_medication_log'));
        add_action('wp_ajax_hms_get_medication_log', array($this, 'ajax_get_medication_log'));
        add_action('wp_ajax_hms_delete_medication_log', array($this, 'ajax_delete_medication_log'));

        // AJAX hooks for OT scheduling
        add_action('wp_ajax_hms_schedule_surgery', array($this, 'ajax_schedule_surgery'));
        add_action('wp_ajax_hms_update_surgery_schedule', array($this, 'ajax_update_surgery_schedule'));
        add_action('wp_ajax_hms_get_surgery_schedule', array($this, 'ajax_get_surgery_schedule'));
        add_action('wp_ajax_hms_delete_surgery_schedule', array($this, 'ajax_delete_surgery_schedule'));

        // AJAX hooks for OT consumables
        add_action('wp_ajax_hms_add_ot_consumable', array($this, 'ajax_add_ot_consumable'));
        add_action('wp_ajax_hms_update_ot_consumable', array($this, 'ajax_update_ot_consumable'));
        add_action('wp_ajax_hms_get_ot_consumable', array($this, 'ajax_get_ot_consumable'));
        add_action('wp_ajax_hms_delete_ot_consumable', array($this, 'ajax_delete_ot_consumable'));
    }

    // --- Patient Vitals Management ---

    /**
     * Add a new patient vitals record.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of vitals data.
     * @return   int|false                     The new vitals record's database ID, or false on failure.
     */
    public function add_patient_vitals(array $data) {
        $table_name = 'hms_patient_vitals';

        if (empty($data['patient_id']) || empty($data['recorded_by'])) {
            return false;
        }

        $vitals_data = array(
            'patient_id'     => intval($data['patient_id']),
            'temperature'    => !empty($data['temperature']) ? floatval($data['temperature']) : null,
            'blood_pressure_systolic' => !empty($data['blood_pressure_systolic']) ? intval($data['blood_pressure_systolic']) : null,
            'blood_pressure_diastolic' => !empty($data['blood_pressure_diastolic']) ? intval($data['blood_pressure_diastolic']) : null,
            'pulse'          => !empty($data['pulse']) ? intval($data['pulse']) : null,
            'respiratory_rate' => !empty($data['respiratory_rate']) ? intval($data['respiratory_rate']) : null,
            'spo2'           => !empty($data['spo2']) ? intval($data['spo2']) : null,
            'blood_sugar'    => !empty($data['blood_sugar']) ? floatval($data['blood_sugar']) : null,
            'notes'          => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'recorded_by'    => intval($data['recorded_by']),
            'recorded_at'    => !empty($data['recorded_at']) ? sanitize_text_field($data['recorded_at']) : current_time('mysql'),
        );

        $format = array('%d', '%f', '%d', '%d', '%d', '%d', '%d', '%f', '%s', '%d', '%s');
        
        $vitals_id = HMS_Helper::hms_create($table_name, $vitals_data, $format);

        if ($vitals_id) {
            HMS_Helper::log_action('Patient Vitals Recorded', 'Patient Vitals', $vitals_id, json_encode($vitals_data));
            return $vitals_id;
        }
        return false;
    }

    /**
     * Get a single patient vitals record by ID.
     *
     * @since    1.0.0
     * @param    int       $vitals_id          The vitals record's database ID.
     * @return   object|null                   The vitals data object, or null if not found.
     */
    public function get_patient_vitals(int $vitals_id): ?object {
        return HMS_Helper::hms_get_row('hms_patient_vitals', array('id' => $vitals_id), array('%d'));
    }

    /**
     * Get all vitals records for a specific patient.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @param    int       $limit              Optional. The number of records to return.
     * @return   array                          An array of vitals data objects.
     */
    public function get_all_patient_vitals(int $patient_id, int $limit = 0): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patient_vitals';
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE patient_id = %d ORDER BY recorded_at DESC", array($patient_id));
        
        if ($limit > 0) {
            $query .= $wpdb->prepare(" LIMIT %d", array($limit));
        }
        
        return $wpdb->get_results($query);
    }

    /**
     * Update an existing patient vitals record.
     *
     * @since    1.0.0
     * @param    int       $vitals_id          The vitals record's database ID.
     * @param    array     $data              An associative array of vitals data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_patient_vitals(int $vitals_id, array $data) {
        $table_name = 'hms_patient_vitals';
        $where = array('id' => $vitals_id);
        $where_format = array('%d');

        $existing_vitals = $this->get_patient_vitals($vitals_id);
        if (!$existing_vitals) {
            return new WP_Error('vitals_not_found', esc_html__('Vitals record not found.', 'hospital-management-system'));
        }
        
        $vitals_data = array();
        $format = array();

        $fields_to_update = array('temperature', 'blood_pressure_systolic', 'blood_pressure_diastolic', 'pulse', 'respiratory_rate', 'spo2', 'blood_sugar', 'notes', 'recorded_at');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('temperature', 'blood_sugar'))) {
                    $vitals_data[$field] = floatval($data[$field]);
                } elseif (in_array($field, array('blood_pressure_systolic', 'blood_pressure_diastolic', 'pulse', 'respiratory_rate', 'spo2'))) {
                    $vitals_data[$field] = intval($data[$field]);
                } elseif ($field === 'notes') {
                    $vitals_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $vitals_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = is_numeric($vitals_data[$field]) ? '%f' : '%s';
            }
        }
        
        if (empty($vitals_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $vitals_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Patient Vitals Updated', 'Patient Vitals', $vitals_id, json_encode($vitals_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a patient vitals record.
     *
     * @since    1.0.0
     * @param    int       $vitals_id          The vitals record's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_patient_vitals(int $vitals_id) {
        $existing_vitals = $this->get_patient_vitals($vitals_id);
        if (!$existing_vitals) {
            return new WP_Error('vitals_not_found', esc_html__('Vitals record not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_patient_vitals', array('id' => $vitals_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Patient Vitals Deleted', 'Patient Vitals', $vitals_id, json_encode($existing_vitals));
            return true;
        }
        return false;
    }


    // --- Medication Administration Log Management ---

    /**
     * Log medication administration.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of medication log data.
     * @return   int|false                     The new log's database ID, or false on failure.
     */
    public function log_medication_administration(array $data) {
        $table_name = 'hms_medication_logs';

        if (empty($data['patient_id']) || empty($data['medicine_id']) || empty($data['administered_by'])) {
            return false;
        }

        $log_data = array(
            'patient_id'        => intval($data['patient_id']),
            'medicine_id'        => intval($data['medicine_id']),
            'dosage'            => sanitize_text_field($data['dosage']),
            'administered_by'    => intval($data['administered_by']),
            'administered_at'    => !empty($data['administered_at']) ? sanitize_text_field($data['administered_at']) : current_time('mysql'),
            'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
        );

        $format = array('%d', '%d', '%s', '%d', '%s', '%s');
        
        $log_id = HMS_Helper::hms_create($table_name, $log_data, $format);

        if ($log_id) {
            HMS_Helper::log_action('Medication Administration Logged', 'Medication Log', $log_id, json_encode($log_data));
            return $log_id;
        }
        return false;
    }

    /**
     * Get a single medication log by ID.
     *
     * @since    1.0.0
     * @param    int       $log_id             The log's database ID.
     * @return   object|null                   The log data object, or null if not found.
     */
    public function get_medication_log(int $log_id): ?object {
        return HMS_Helper::hms_get_row('hms_medication_logs', array('id' => $log_id), array('%d'));
    }

    /**
     * Get all medication logs for a specific patient.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @param    int       $limit              Optional. The number of records to return.
     * @return   array                          An array of log data objects.
     */
    public function get_all_medication_logs(int $patient_id, int $limit = 0): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medication_logs';
        // Join with medicines table to get medicine name
        $query = $wpdb->prepare(
            "SELECT ml.*, m.name as medicine_name FROM {$table_name} ml 
             LEFT JOIN {$wpdb->prefix}hms_medicines m ON ml.medicine_id = m.id 
             WHERE ml.patient_id = %d ORDER BY ml.administered_at DESC", 
            array($patient_id)
        );
        
        if ($limit > 0) {
            $query .= $wpdb->prepare(" LIMIT %d", array($limit));
        }
        
        return $wpdb->get_results($query);
    }

    /**
     * Update an existing medication log.
     *
     * @since    1.0.0
     * @param    int       $log_id             The log's database ID.
     * @param    array     $data              An associative array of log data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_medication_log(int $log_id, array $data) {
        $table_name = 'hms_medication_logs';
        $where = array('id' => $log_id);
        $where_format = array('%d');

        $existing_log = $this->get_medication_log($log_id);
        if (!$existing_log) {
            return new WP_Error('log_not_found', esc_html__('Medication log not found.', 'hospital-management-system'));
        }
        
        $log_data = array();
        $format = array();

        $fields_to_update = array('medicine_id', 'dosage', 'administered_at', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if ($field === 'medicine_id') {
                    $log_data[$field] = intval($data[$field]);
                } elseif ($field === 'notes') {
                    $log_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $log_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = is_numeric($log_data[$field]) ? '%f' : '%s';
            }
        }
        
        if (empty($log_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $log_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Medication Log Updated', 'Medication Log', $log_id, json_encode($log_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a medication log.
     *
     * @since    1.0.0
     * @param    int       $log_id             The log's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_medication_log(int $log_id) {
        $existing_log = $this->get_medication_log($log_id);
        if (!$existing_log) {
            return new WP_Error('log_not_found', esc_html__('Medication log not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_medication_logs', array('id' => $log_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Medication Log Deleted', 'Medication Log', $log_id, json_encode($existing_log));
            return true;
        }
        return false;
    }


    // --- Operation Theatre (OT) Scheduling Management ---

    /**
     * Schedule a new surgery.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of surgery data.
     * @return   int|false                     The new surgery's database ID, or false on failure.
     */
    public function schedule_surgery(array $data) {
        $table_name = 'hms_ot_schedules';

        if (empty($data['patient_id']) || empty($data['surgery_type']) || empty($data['scheduled_by'])) {
            return false;
        }

        $surgery_data = array(
            'patient_id'        => intval($data['patient_id']),
            'doctor_id'         => !empty($data['doctor_id']) ? intval($data['doctor_id']) : null,
            'ot_id'             => !empty($data['ot_id']) ? intval($data['ot_id']) : null,
            'surgery_type'      => sanitize_text_field($data['surgery_type']),
            'scheduled_date'    => sanitize_text_field($data['scheduled_date']),
            'scheduled_time'    => sanitize_text_field($data['scheduled_time']),
            'duration'          => !empty($data['duration']) ? intval($data['duration']) : null, // in minutes
            'status'            => !empty($data['status']) ? sanitize_text_field($data['status']) : 'scheduled',
            'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'scheduled_by'      => intval($data['scheduled_by']),
            'created_at'        => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s');
        
        $surgery_id = HMS_Helper::hms_create($table_name, $surgery_data, $format);

        if ($surgery_id) {
            HMS_Helper::log_action('Surgery Scheduled', 'OT Schedule', $surgery_id, json_encode($surgery_data));
            return $surgery_id;
        }
        return false;
    }

    /**
     * Get a single surgery schedule by ID.
     *
     * @since    1.0.0
     * @param    int       $surgery_id         The surgery's database ID.
     * @return   object|null                   The surgery data object, or null if not found.
     */
    public function get_surgery_schedule(int $surgery_id): ?object {
        return HMS_Helper::hms_get_row('hms_ot_schedules', array('id' => $surgery_id), array('%d'));
    }

    /**
     * Get all surgery schedules, with optional filtering.
     *
     * @since    1.0.0
     * @param    string    $date_from          Optional. Filter by date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by date to (Y-m-d).
     * @param    string    $status             Optional. Filter by status.
     * @param    int       $ot_id              Optional. Filter by OT ID.
     * @return   array                          An array of surgery data objects.
     */
    public function get_all_surgery_schedules(string $date_from = '', string $date_to = '', string $status = '', int $ot_id = 0): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_ot_schedules';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($date_from)) {
            $where_clauses[] = "scheduled_date >= %s";
            $where_values[] = $date_from;
        }
        if (!empty($date_to)) {
            $where_clauses[] = "scheduled_date <= %s";
            $where_values[] = $date_to;
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }
        if ($ot_id > 0) {
            $where_clauses[] = "ot_id = %d";
            $where_values[] = $ot_id;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY scheduled_date, scheduled_time ASC";
        
        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Update an existing surgery schedule.
     *
     * @since    1.0.0
     * @param    int       $surgery_id         The surgery's database ID.
     * @param    array     $data              An associative array of surgery data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_surgery_schedule(int $surgery_id, array $data) {
        $table_name = 'hms_ot_schedules';
        $where = array('id' => $surgery_id);
        $where_format = array('%d');

        $existing_surgery = $this->get_surgery_schedule($surgery_id);
        if (!$existing_surgery) {
            return new WP_Error('surgery_not_found', esc_html__('Surgery schedule not found.', 'hospital-management-system'));
        }
        
        $surgery_data = array();
        $format = array();

        $fields_to_update = array('patient_id', 'doctor_id', 'ot_id', 'surgery_type', 'scheduled_date', 'scheduled_time', 'duration', 'status', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('patient_id', 'doctor_id', 'ot_id', 'duration'))) {
                    $surgery_data[$field] = intval($data[$field]);
                } elseif ($field === 'notes') {
                    $surgery_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $surgery_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = is_numeric($surgery_data[$field]) ? '%f' : '%s';
            }
        }
        
        if (empty($surgery_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $surgery_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Surgery Schedule Updated', 'OT Schedule', $surgery_id, json_encode($surgery_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a surgery schedule.
     *
     * @since    1.0.0
     * @param    int       $surgery_id         The surgery's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_surgery_schedule(int $surgery_id) {
        $existing_surgery = $this->get_surgery_schedule($surgery_id);
        if (!$existing_surgery) {
            return new WP_Error('surgery_not_found', esc_html__('Surgery schedule not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_ot_schedules', array('id' => $surgery_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Surgery Schedule Deleted', 'OT Schedule', $surgery_id, json_encode($existing_surgery));
            return true;
        }
        return false;
    }


    // --- OT Consumables Management ---

    /**
     * Add a new OT consumable.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of consumable data.
     * @return   int|false                     The new consumable's database ID, or false on failure.
     */
    public function add_ot_consumable(array $data) {
        $table_name = 'hms_ot_consumables';

        if (empty($data['surgery_id']) || empty($data['item_name']) || empty($data['quantity_used'])) {
            return false;
        }

        $consumable_data = array(
            'surgery_id'        => intval($data['surgery_id']),
            'item_name'         => sanitize_text_field($data['item_name']),
            'quantity_used'     => intval($data['quantity_used']),
            'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'logged_by'         => get_current_user_id(),
            'logged_at'         => current_time('mysql'),
        );

        $format = array('%d', '%s', '%d', '%s', '%d', '%s');
        
        $consumable_id = HMS_Helper::hms_create($table_name, $consumable_data, $format);

        if ($consumable_id) {
            HMS_Helper::log_action('OT Consumable Logged', 'OT Consumable', $consumable_id, json_encode($consumable_data));
            return $consumable_id;
        }
        return false;
    }

    /**
     * Get a single OT consumable by ID.
     *
     * @since    1.0.0
     * @param    int       $consumable_id      The consumable's database ID.
     * @return   object|null                   The consumable data object, or null if not found.
     */
    public function get_ot_consumable(int $consumable_id): ?object {
        return HMS_Helper::hms_get_row('hms_ot_consumables', array('id' => $consumable_id), array('%d'));
    }

    /**
     * Get all OT consumables for a specific surgery.
     *
     * @since    1.0.0
     * @param    int       $surgery_id         The surgery's database ID.
     * @return   array                          An array of consumable data objects.
     */
    public function get_all_ot_consumables(int $surgery_id): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_ot_consumables';
        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE surgery_id = %d ORDER BY logged_at ASC", array($surgery_id));
        return $wpdb->get_results($query);
    }

    /**
     * Update an existing OT consumable.
     *
     * @since    1.0.0
     * @param    int       $consumable_id      The consumable's database ID.
     * @param    array     $data              An associative array of consumable data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_ot_consumable(int $consumable_id, array $data) {
        $table_name = 'hms_ot_consumables';
        $where = array('id' => $consumable_id);
        $where_format = array('%d');

        $existing_consumable = $this->get_ot_consumable($consumable_id);
        if (!$existing_consumable) {
            return new WP_Error('consumable_not_found', esc_html__('OT consumable not found.', 'hospital-management-system'));
        }
        
        $consumable_data = array();
        $format = array();

        $fields_to_update = array('item_name', 'quantity_used', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if ($field === 'quantity_used') {
                    $consumable_data[$field] = intval($data[$field]);
                } elseif ($field === 'notes') {
                    $consumable_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $consumable_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = is_numeric($consumable_data[$field]) ? '%f' : '%s';
            }
        }
        
        if (empty($consumable_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $consumable_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('OT Consumable Updated', 'OT Consumable', $consumable_id, json_encode($consumable_data));
            return true;
        }
        return false;
    }

    /**
     * Delete an OT consumable.
     *
     * @since    1.0.0
     * @param    int       $consumable_id      The consumable's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_ot_consumable(int $consumable_id) {
        $existing_consumable = $this->get_ot_consumable($consumable_id);
        if (!$existing_consumable) {
            return new WP_Error('consumable_not_found', esc_html__('OT consumable not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_ot_consumables', array('id' => $consumable_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('OT Consumable Deleted', 'OT Consumable', $consumable_id, json_encode($existing_consumable));
            return true;
        }
        return false;
    }


    // --- AJAX Handlers for Patient Vitals ---

    public function ajax_add_patient_vitals() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'             => intval($_POST['patient_id']),
            'temperature'            => isset($_POST['temperature']) ? floatval($_POST['temperature']) : null,
            'blood_pressure_systolic' => isset($_POST['blood_pressure_systolic']) ? intval($_POST['blood_pressure_systolic']) : null,
            'blood_pressure_diastolic' => isset($_POST['blood_pressure_diastolic']) ? intval($_POST['blood_pressure_diastolic']) : null,
            'pulse'                  => isset($_POST['pulse']) ? intval($_POST['pulse']) : null,
            'respiratory_rate'       => isset($_POST['respiratory_rate']) ? intval($_POST['respiratory_rate']) : null,
            'spo2'                   => isset($_POST['spo2']) ? intval($_POST['spo2']) : null,
            'blood_sugar'            => isset($_POST['blood_sugar']) ? floatval($_POST['blood_sugar']) : null,
            'notes'                  => sanitize_textarea_field($_POST['notes']),
            'recorded_by'            => get_current_user_id(),
        );

        $vitals_id = $this->add_patient_vitals($data);

        if (is_wp_error($vitals_id)) {
            wp_send_json_error($vitals_id->get_error_message());
        } elseif ($vitals_id) {
            $vitals = $this->get_patient_vitals($vitals_id);
            wp_send_json_success(array(
                'message' => esc_html__('Patient vitals recorded successfully.', 'hospital-management-system'),
                'vitals'  => $vitals
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to record patient vitals.', 'hospital-management-system'));
        }
    }

    public function ajax_update_patient_vitals() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $vitals_id = intval($_POST['vitals_id']);
        $data = array(
            'temperature'            => isset($_POST['temperature']) ? floatval($_POST['temperature']) : null,
            'blood_pressure_systolic' => isset($_POST['blood_pressure_systolic']) ? intval($_POST['blood_pressure_systolic']) : null,
            'blood_pressure_diastolic' => isset($_POST['blood_pressure_diastolic']) ? intval($_POST['blood_pressure_diastolic']) : null,
            'pulse'                  => isset($_POST['pulse']) ? intval($_POST['pulse']) : null,
            'respiratory_rate'       => isset($_POST['respiratory_rate']) ? intval($_POST['respiratory_rate']) : null,
            'spo2'                   => isset($_POST['spo2']) ? intval($_POST['spo2']) : null,
            'blood_sugar'            => isset($_POST['blood_sugar']) ? floatval($_POST['blood_sugar']) : null,
            'notes'                  => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_patient_vitals($vitals_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $vitals = $this->get_patient_vitals($vitals_id);
            wp_send_json_success(array(
                'message' => esc_html__('Patient vitals updated successfully.', 'hospital-management-system'),
                'vitals'  => $vitals
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update patient vitals.', 'hospital-management-system'));
        }
    }
    
    public function ajax_get_patient_vitals() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $vitals_id = intval($_GET['vitals_id']);
        $vitals = $this->get_patient_vitals($vitals_id);
        if ($vitals) {
            wp_send_json_success($vitals);
        } else {
            wp_send_json_error(esc_html__('Vitals record not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_patient_vitals() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $vitals_id = intval($_POST['vitals_id']);
        
        $deleted = $this->delete_patient_vitals($vitals_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Vitals record deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete vitals record.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for Medication Logs ---

    public function ajax_log_medication_administration() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'        => intval($_POST['patient_id']),
            'medicine_id'        => intval($_POST['medicine_id']),
            'dosage'            => sanitize_text_field($_POST['dosage']),
            'administered_by'    => get_current_user_id(),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $log_id = $this->log_medication_administration($data);

        if (is_wp_error($log_id)) {
            wp_send_json_error($log_id->get_error_message());
        } elseif ($log_id) {
            $log = $this->get_medication_log($log_id);
            wp_send_json_success(array(
                'message' => esc_html__('Medication administration logged successfully.', 'hospital-management-system'),
                'log'     => $log
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to log medication administration.', 'hospital-management-system'));
        }
    }

    public function ajax_update_medication_log() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $log_id = intval($_POST['log_id']);
        $data = array(
            'medicine_id'        => intval($_POST['medicine_id']),
            'dosage'            => sanitize_text_field($_POST['dosage']),
            'notes'              => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_medication_log($log_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $log = $this->get_medication_log($log_id);
            wp_send_json_success(array(
                'message' => esc_html__('Medication log updated successfully.', 'hospital-management-system'),
                'log'     => $log
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update medication log.', 'hospital-management-system'));
        }
    }

    public function ajax_get_medication_log() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $log_id = intval($_GET['log_id']);
        $log = $this->get_medication_log($log_id);
        if ($log) {
            wp_send_json_success($log);
        } else {
            wp_send_json_error(esc_html__('Medication log not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_medication_log() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $log_id = intval($_POST['log_id']);
        
        $deleted = $this->delete_medication_log($log_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Medication log deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete medication log.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for OT Scheduling ---

    public function ajax_schedule_surgery() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'        => intval($_POST['patient_id']),
            'doctor_id'         => isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null,
            'ot_id'             => isset($_POST['ot_id']) ? intval($_POST['ot_id']) : null,
            'surgery_type'      => sanitize_text_field($_POST['surgery_type']),
            'scheduled_date'    => sanitize_text_field($_POST['scheduled_date']),
            'scheduled_time'    => sanitize_text_field($_POST['scheduled_time']),
            'duration'          => isset($_POST['duration']) ? intval($_POST['duration']) : null,
            'status'            => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'scheduled',
            'notes'             => sanitize_textarea_field($_POST['notes']),
            'scheduled_by'      => get_current_user_id(),
        );

        $surgery_id = $this->schedule_surgery($data);

        if (is_wp_error($surgery_id)) {
            wp_send_json_error($surgery_id->get_error_message());
        } elseif ($surgery_id) {
            $surgery = $this->get_surgery_schedule($surgery_id);
            wp_send_json_success(array(
                'message' => esc_html__('Surgery scheduled successfully.', 'hospital-management-system'),
                'surgery'  => $surgery
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to schedule surgery.', 'hospital-management-system'));
        }
    }

    public function ajax_update_surgery_schedule() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $surgery_id = intval($_POST['surgery_id']);
        $data = array(
            'patient_id'        => isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null,
            'doctor_id'         => isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null,
            'ot_id'             => isset($_POST['ot_id']) ? intval($_POST['ot_id']) : null,
            'surgery_type'      => sanitize_text_field($_POST['surgery_type']),
            'scheduled_date'    => sanitize_text_field($_POST['scheduled_date']),
            'scheduled_time'    => sanitize_text_field($_POST['scheduled_time']),
            'duration'          => isset($_POST['duration']) ? intval($_POST['duration']) : null,
            'status'            => sanitize_text_field($_POST['status']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_surgery_schedule($surgery_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $surgery = $this->get_surgery_schedule($surgery_id);
            wp_send_json_success(array(
                'message' => esc_html__('Surgery schedule updated successfully.', 'hospital-management-system'),
                'surgery'  => $surgery
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update surgery schedule.', 'hospital-management-system'));
        }
    }

    public function ajax_get_surgery_schedule() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $surgery_id = intval($_GET['surgery_id']);
        $surgery = $this->get_surgery_schedule($surgery_id);
        if ($surgery) {
            wp_send_json_success($surgery);
        } else {
            wp_send_json_error(esc_html__('Surgery schedule not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_surgery_schedule() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $surgery_id = intval($_POST['surgery_id']);
        
        $deleted = $this->delete_surgery_schedule($surgery_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Surgery schedule deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete surgery schedule.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for OT Consumables ---

    public function ajax_add_ot_consumable() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'surgery_id'        => intval($_POST['surgery_id']),
            'item_name'         => sanitize_text_field($_POST['item_name']),
            'quantity_used'     => intval($_POST['quantity_used']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $consumable_id = $this->add_ot_consumable($data);

        if (is_wp_error($consumable_id)) {
            wp_send_json_error($consumable_id->get_error_message());
        } elseif ($consumable_id) {
            $consumable = $this->get_ot_consumable($consumable_id);
            wp_send_json_success(array(
                'message' => esc_html__('OT consumable logged successfully.', 'hospital-management-system'),
                'consumable' => $consumable
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to log OT consumable.', 'hospital-management-system'));
        }
    }

    public function ajax_update_ot_consumable() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $consumable_id = intval($_POST['consumable_id']);
        $data = array(
            'item_name'         => sanitize_text_field($_POST['item_name']),
            'quantity_used'     => intval($_POST['quantity_used']),
            'notes'             => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_ot_consumable($consumable_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $consumable = $this->get_ot_consumable($consumable_id);
            wp_send_json_success(array(
                'message' => esc_html__('OT consumable updated successfully.', 'hospital-management-system'),
                'consumable' => $consumable
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update OT consumable.', 'hospital-management-system'));
        }
    }

    public function ajax_get_ot_consumable() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $consumable_id = intval($_GET['consumable_id']);
        $consumable = $this->get_ot_consumable($consumable_id);
        if ($consumable) {
            wp_send_json_success($consumable);
        } else {
            wp_send_json_error(esc_html__('OT consumable not found.', 'hospital-management-system'));
        }
    }

    public function ajax_delete_ot_consumable() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_clinical_data')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $consumable_id = intval($_POST['consumable_id']);
        
        $deleted = $this->delete_ot_consumable($consumable_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('OT consumable deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete OT consumable.', 'hospital-management-system'));
        }
    }
} // End of class HMS_Clinical
