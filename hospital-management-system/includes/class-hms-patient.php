<?php
/**
 * HMS Patient Class
 *
 * Handles patient registration, profile management, and data retrieval.
 * This class primarily interacts with the custom `wp_hms_patients` table.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Patient {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for patient management
        add_action('wp_ajax_hms_register_patient', array($this, 'ajax_register_patient'));
        add_action('wp_ajax_hms_update_patient', array($this, 'ajax_update_patient'));
        add_action('wp_ajax_hms_get_patient', array($this, 'ajax_get_patient'));
        add_action('wp_ajax_hms_get_patients_list', array($this, 'ajax_get_patients_list'));
        add_action('wp_ajax_hms_delete_patient', array($this, 'ajax_delete_patient'));
        add_action('wp_ajax_hms_get_patient_medical_history', array($this, 'ajax_get_patient_medical_history'));
        add_action('wp_ajax_hms_add_medical_history_record', array($this, 'ajax_add_medical_history_record'));
        add_action('wp_ajax_hms_update_patient_medical_history', array($this, 'ajax_update_patient_medical_history'));
        add_action('wp_ajax_hms_search_patients', array($this, 'ajax_search_patients'));
        add_action('wp_ajax_hms_export_patients', array($this, 'ajax_export_patients'));
    }

    /**
     * Get a single patient by ID from the custom table.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @return   object|null                   The patient data object, or null if not found.
     */
    public function get_patient(int $patient_id): ?object {
        return HMS_Helper::hms_get_row('hms_patients', array('id' => $patient_id), array('%d'));
    }

    /**
     * Get all patients, with optional pagination, search, and filtering.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter patients.
     * @param    string    $date_from          Optional. Filter by registration date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by registration date to (Y-m-d).
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of patients to return per page.
     * @return   array                          An array of patient data objects.
     */
    public function get_patients(string $search = '', string $date_from = '', string $date_to = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(first_name LIKE %s OR last_name LIKE %s OR patient_id LIKE %s OR email LIKE %s OR contact_number LIKE %s)";
            $where_values = array_fill(0, 5, $search_term);
        }

        if (!empty($date_from)) {
            $where_clauses[] = "registration_date >= %s";
            $where_values[] = $date_from . ' 00:00:00';
        }

        if (!empty($date_to)) {
            $where_clauses[] = "registration_date <= %s";
            $where_values[] = $date_to . ' 23:59:59';
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY registration_date DESC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Get total count of patients for pagination.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter patients.
     * @param    string    $date_from          Optional. Filter by registration date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by registration date to (Y-m-d).
     * @return   int                             The total number of patients.
     */
    public function get_patients_count(string $search = '', string $date_from = '', string $date_to = ''): int {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';

        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(first_name LIKE %s OR last_name LIKE %s OR patient_id LIKE %s OR email LIKE %s OR contact_number LIKE %s)";
            $where_values = array_fill(0, 5, $search_term);
        }
        if (!empty($date_from)) {
            $where_clauses[] = "registration_date >= %s";
            $where_values[] = $date_from . ' 00:00:00';
        }
        if (!empty($date_to)) {
            $where_clauses[] = "registration_date <= %s";
            $where_values[] = $date_to . ' 23:59:59';
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        return (int) $wpdb->get_var($wpdb->prepare($query, $where_values));
    }
    
    /**
     * Get the last visit date for a patient.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @return   string|null                    The last visit date (Y-m-d), or null if not found.
     */
    public function get_last_visit_date(int $patient_id): ?string {
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        
        // Query for the most recent past appointment with a status that implies a visit occurred.
        $query = $wpdb->prepare(
            "SELECT appointment_date FROM {$appointments_table} 
             WHERE patient_id = %d AND appointment_date <= CURDATE() AND status IN ('completed', 'in-progress', 'no-show')
             ORDER BY appointment_date DESC LIMIT 1",
            array($patient_id)
        );
        
        $result = $wpdb->get_var($query);
        return $result ? gmdate('Y-m-d', strtotime($result)) : null;
    }

    /**
     * Get patient medical history.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @return   object|null                   The patient data object, or null if not found.
     */
    public function get_patient_medical_history($patient_id) {
        return $this->get_patient($patient_id);
    }

    /**
     * Get patient previous visits.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @return   array                          An array of visit data objects.
     */
    public function get_patient_previous_visits($patient_id) {
        global $wpdb;
        
        // Get appointments
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT id, appointment_date as date, 'appointment' as type,
                    CONCAT('Appointment with ', doctor_id) as details, status
             FROM {$appointments_table}
             WHERE patient_id = %d
             ORDER BY appointment_date DESC",
            $patient_id
        ));
        
        // Get bills
        $bills_table = $wpdb->prefix . 'hms_bills';
        $bills = $wpdb->get_results($wpdb->prepare(
            "SELECT id, created_at as date, 'bill' as type,
                    CONCAT('Bill #', bill_number) as details, payment_status as status
             FROM {$bills_table}
             WHERE patient_id = %d
             ORDER BY created_at DESC",
            $patient_id
        ));
        
        // Combine and sort by date
        $visits = array_merge($appointments, $bills);
        usort($visits, function($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });
        
        return $visits;
    }

    /**
     * Register a new patient in the hospital management system.
     * 
     * Creates a new patient record with comprehensive validation, automatic ID generation,
     * and proper data sanitization. Logs the registration action for audit purposes.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of patient data containing:
     *                                        - first_name (required): Patient's first name
     *                                        - last_name (required): Patient's last name  
     *                                        - date_of_birth (required): Date in Y-m-d format
     *                                        - gender (required): Patient's gender
     *                                        - blood_group (optional): Blood group (A+, B+, etc.)
     *                                        - contact_number (optional): Phone number
     *                                        - email (optional): Email address
     *                                        - address (optional): Full address
     *                                        - emergency_contact_name (optional): Emergency contact name
     *                                        - emergency_contact_number (optional): Emergency contact phone
     *                                        - national_id (optional): National ID/SSN
     *                                        - deposit_money (optional): Initial deposit amount
     *                                        - allergies (optional): Known allergies
     *                                        - medical_history (optional): Previous medical history
     * @return   int|false                     The new patient's database ID on success, false on failure.
     */
    public function register_patient(array $data) {
        $table_name = 'hms_patients';

        // Comprehensive validation
        $required_fields = array('first_name', 'last_name', 'date_of_birth', 'gender');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                HMS_Helper::log_action('Patient Registration Failed', 'Patient', 0, "Missing required field: {$field}");
                return false;
            }
        }
        
        // Validate date of birth format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_of_birth'])) {
            HMS_Helper::log_action('Patient Registration Failed', 'Patient', 0, 'Invalid date of birth format');
            return false;
        }
        
        // Validate email if provided
        if (!empty($data['email']) && !is_email($data['email'])) {
            HMS_Helper::log_action('Patient Registration Failed', 'Patient', 0, 'Invalid email format');
            return false;
        }
        
        // Validate gender
        $valid_genders = array('Male', 'Female', 'Other');
        if (!in_array($data['gender'], $valid_genders, true)) {
            HMS_Helper::log_action('Patient Registration Failed', 'Patient', 0, 'Invalid gender value');
            return false;
        }

        // Generate unique Patient ID
        global $wpdb;
        $full_table_name = $wpdb->prefix . $table_name;
        $last_patient = $wpdb->get_row("SELECT * FROM $full_table_name ORDER BY id DESC LIMIT 1");
        $last_id = $last_patient ? intval(substr($last_patient->patient_id, 3)) : 0;
        $data['patient_id'] = 'PID' . str_pad($last_id + 1, 6, '0', STR_PAD_LEFT);

        $patient_data = array(
            'patient_id'        => $data['patient_id'],
            'first_name'        => sanitize_text_field($data['first_name']),
            'last_name'         => sanitize_text_field($data['last_name']),
            'date_of_birth'     => sanitize_text_field($data['date_of_birth']),
            'gender'            => sanitize_text_field($data['gender']),
            'blood_group'       => !empty($data['blood_group']) ? sanitize_text_field($data['blood_group']) : null,
            'contact_number'    => !empty($data['contact_number']) ? sanitize_text_field($data['contact_number']) : null,
            'email'             => !empty($data['email']) ? sanitize_email($data['email']) : null,
            'address'           => !empty($data['address']) ? sanitize_textarea_field($data['address']) : null,
            'emergency_contact_name' => !empty($data['emergency_contact_name']) ? sanitize_text_field($data['emergency_contact_name']) : null,
            'emergency_contact_number' => !empty($data['emergency_contact_number']) ? sanitize_text_field($data['emergency_contact_number']) : null,
            'national_id'       => !empty($data['national_id']) ? sanitize_text_field($data['national_id']) : null,
            'deposit_money'     => !empty($data['deposit_money']) ? floatval($data['deposit_money']) : 0.00,
            'allergies'         => !empty($data['allergies']) ? sanitize_textarea_field($data['allergies']) : null,
            'medical_history'   => !empty($data['medical_history']) ? sanitize_textarea_field($data['medical_history']) : null,
            'registration_date' => current_time('mysql'),
            'updated_at'        => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s');
        
        $patient_id = HMS_Helper::hms_create($table_name, $patient_data, $format);

        if ($patient_id) {
            HMS_Helper::log_action('Patient Registered', 'Patient', $patient_id, json_encode($patient_data));
            // Optionally, send welcome email/SMS
            // $this->send_welcome_notification($patient_id);
            return $patient_id;
        }
        return false;
    }

    /**
     * Update an existing patient's profile.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @param    array     $data              An associative array of patient data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_patient(int $patient_id, array $data) {
        $table_name = 'hms_patients';
        $where = array('id' => $patient_id);
        $where_format = array('%d');

        $existing_patient = $this->get_patient($patient_id);
        if (!$existing_patient) {
            return new WP_Error('patient_not_found', esc_html__('Patient not found.', 'hospital-management-system'));
        }
        
        $patient_data = array();
        $format = array();

        $fields_to_update = array('first_name', 'last_name', 'date_of_birth', 'gender', 'blood_group', 'contact_number', 'email', 'address', 'emergency_contact_name', 'emergency_contact_number', 'national_id', 'deposit_money', 'allergies', 'medical_history', 'status');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('address', 'allergies', 'medical_history'))) {
                    $patient_data[$field] = sanitize_textarea_field($data[$field]);
                } elseif ($field === 'email') {
                    $patient_data[$field] = sanitize_email($data[$field]);
                } elseif ($field === 'deposit_money') {
                    $patient_data[$field] = floatval($data[$field]);
                    $format[] = '%f';
                } else {
                    $patient_data[$field] = sanitize_text_field($data[$field]);
                    $format[] = '%s';
                }
            }
        }
        
        if (empty($patient_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $patient_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Patient Profile Updated', 'Patient', $patient_id, json_encode($patient_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a patient and their related data.
     * This is a destructive action and should be handled with care.
     * It will delete patient records, medical history, and potentially other linked data.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_patient(int $patient_id) {
        global $wpdb;
        $patient = $this->get_patient($patient_id);
        if (!$patient) {
            return new WP_Error('patient_not_found', esc_html__('Patient not found.', 'hospital-management-system'));
        }

        $tables_to_clear = array(
            'hms_medical_records' => 'patient_id',
            // Add other tables here if patients are linked, e.g.:
            // 'hms_appointments' => 'patient_id',
            // 'hms_bills' => 'patient_id',
        );

        try {
            // Start transaction to ensure atomicity
            $wpdb->query('START TRANSACTION');

            // Delete related records first
            foreach ($tables_to_clear as $table => $column) {
                $table_name = $wpdb->prefix . $table;
                $deleted = $wpdb->delete($table_name, array($column => $patient_id), array('%d'));
                if ($deleted === false) {
                    /* translators: %s: Table name */
                    throw new Exception(sprintf(esc_html__('Failed to delete related records from %s.', 'hospital-management-system'), $table_name));
                }
            }

            // Delete the patient record
            $deleted = HMS_Helper::hms_delete('hms_patients', array('id' => $patient_id), array('%d'));
            if ($deleted === false) {
                throw new Exception(esc_html__('Failed to delete patient record.', 'hospital-management-system'));
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            HMS_Helper::log_action('Patient Deleted', 'Patient', $patient_id, json_encode($patient));
            return true;

        } catch (Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            return new WP_Error('patient_deletion_failed', $e->getMessage());
        }
    }


    // --- Medical History Management ---

    /**
     * Get a single medical history record by ID.
     *
     * @since    1.0.0
     * @param    int       $record_id          The medical record's database ID.
     * @return   object|null                   The medical record data object, or null if not found.
     */
    public function get_medical_history_record(int $record_id): ?object {
        return HMS_Helper::hms_get_row('hms_medical_records', array('id' => $record_id), array('%d'));
    }

    /**
     * Get all medical history records for a patient, with pagination.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's database ID.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of records to return per page.
     * @return   array                          An array of medical record data objects.
     */
    public function get_medical_history(int $patient_id, int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medical_records';

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE patient_id = %d ORDER BY record_date DESC, created_at DESC LIMIT %d OFFSET %d",
            array($patient_id, $per_page, $offset)
        );
        
        return $wpdb->get_results($query);
    }

    /**
     * Add a new medical history record for a patient.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of medical record data.
     * @return   int|false                     The new medical record's database ID, or false on failure.
     */
    public function add_medical_history_record(array $data) {
        $table_name = 'hms_medical_records';

        if (empty($data['patient_id']) || empty($data['recorded_by']) || empty($data['record_date']) || empty($data['notes'])) {
            return false;
        }

        $record_data = array(
            'patient_id'    => intval($data['patient_id']),
            'recorded_by'   => intval($data['recorded_by']), // User ID of doctor/nurse
            'appointment_id'=> !empty($data['appointment_id']) ? intval($data['appointment_id']) : null,
            'record_date'   => sanitize_text_field($data['record_date']),
            'diagnosis'     => !empty($data['diagnosis']) ? sanitize_textarea_field($data['diagnosis']) : null,
            'treatment'     => !empty($data['treatment']) ? sanitize_textarea_field($data['treatment']) : null,
            'notes'         => sanitize_textarea_field($data['notes']),
            'created_at'    => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s');
        
        $record_id = HMS_Helper::hms_create($table_name, $record_data, $format);

        if ($record_id) {
            HMS_Helper::log_action('Medical History Record Added', 'Medical Record', $record_id, json_encode($record_data));
            return $record_id;
        }
        return false;
    }


    // --- AJAX Handlers ---

    /**
     * AJAX handler to register or update a patient.
     */
    public function ajax_register_patient() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $is_edit = isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['patient_id']);
        
        // Use correct field names from the form
        $data = array(
            'first_name'            => sanitize_text_field($_POST['first_name']),
            'last_name'             => sanitize_text_field($_POST['last_name']),
            'date_of_birth'         => sanitize_text_field($_POST['dob']), // Form uses 'dob'
            'gender'                => sanitize_text_field($_POST['gender']),
            'blood_group'           => sanitize_text_field($_POST['blood_group']),
            'contact_number'        => sanitize_text_field($_POST['contact_number']), // Form uses 'contact_number'
            'email'                 => sanitize_email($_POST['email']),
            'address'               => sanitize_textarea_field($_POST['address']),
            'emergency_contact_name'=> sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_number'=> sanitize_text_field($_POST['emergency_contact_number']),
            'national_id'           => sanitize_text_field($_POST['national_id']),
            'deposit_money'         => floatval($_POST['deposit_money']),
            'allergies'             => sanitize_textarea_field($_POST['allergies']),
            'medical_history'       => sanitize_textarea_field($_POST['medical_history']),
            'status'                => sanitize_text_field($_POST['status'] ?? 'active'),
        );

        if ($is_edit) {
            $patient_id = intval($_POST['patient_id']);
            $result = $this->update_patient($patient_id, $data);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            } elseif ($result) {
                $patient = $this->get_patient($patient_id);
                wp_send_json_success(array(
                    'message' => esc_html__('Patient updated successfully.', 'hospital-management-system'),
                    'patient' => $patient
                ));
            } else {
                wp_send_json_error(esc_html__('Failed to update patient.', 'hospital-management-system'));
            }
        } else {
            $patient_id = $this->register_patient($data);

            if (is_wp_error($patient_id)) {
                wp_send_json_error($patient_id->get_error_message());
            } elseif ($patient_id) {
                $patient = $this->get_patient($patient_id);
                wp_send_json_success(array(
                    'message' => esc_html__('Patient registered successfully.', 'hospital-management-system'),
                    'patient' => $patient
                ));
            } else {
                wp_send_json_error(esc_html__('Failed to register patient.', 'hospital-management-system'));
            }
        }
    }

    /**
     * AJAX handler to update a patient.
     */
    public function ajax_update_patient() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        $data = array(
            'first_name'            => sanitize_text_field($_POST['first_name']),
            'last_name'             => sanitize_text_field($_POST['last_name']),
            'date_of_birth'         => sanitize_text_field($_POST['date_of_birth']),
            'gender'                => sanitize_text_field($_POST['gender']),
            'blood_group'           => sanitize_text_field($_POST['blood_group']),
            'contact_number'        => sanitize_text_field($_POST['phone']),
            'email'                 => sanitize_email($_POST['email']),
            'address'               => sanitize_textarea_field($_POST['address']),
            'emergency_contact_name'=> sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_number'=> sanitize_text_field($_POST['emergency_contact_number']),
            'national_id'           => sanitize_text_field($_POST['national_id']),
            'deposit_money'         => floatval($_POST['deposit_money']),
            'allergies'             => sanitize_textarea_field($_POST['allergies']),
            'medical_history'       => sanitize_textarea_field($_POST['medical_history']),
            'status'                => sanitize_text_field($_POST['status']),
        );

        $updated = $this->update_patient($patient_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $patient = $this->get_patient($patient_id);
            wp_send_json_success(array(
                'message' => esc_html__('Patient updated successfully.', 'hospital-management-system'),
                'patient' => $patient
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update patient.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single patient's details.
     */
    public function ajax_get_patient() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $patient_id = intval($_GET['patient_id']);
        $patient = $this->get_patient($patient_id);
        if ($patient) {
            wp_send_json_success($patient);
        } else {
            wp_send_json_error(esc_html__('Patient not found.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a list of patients for DataTables or similar.
     */
    public function ajax_get_patients_list() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        $offset = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $per_page = isset($_POST['length']) ? intval($_POST['length']) : 20;
        
        $patients = $this->get_patients($search, $date_from, $date_to, $offset, $per_page);
        $total_patients = $this->get_patients_count($search, $date_from, $date_to);
        
        $data = array();
        foreach ($patients as $patient) {
            $contact_info = [];
            if ($patient->email) $contact_info[] = $patient->email;
            if ($patient->contact_number) $contact_info[] = $patient->contact_number;
            $contact = implode('<br>', $contact_info);
            if (empty($contact)) $contact = 'N/A';

            $last_visit = $this->get_last_visit_date($patient->id);
            $last_visit_formatted = $last_visit ? date_i18n(get_option('date_format'), strtotime($last_visit)) : esc_html__('N/A', 'hospital-management-system');

            // Determine patient status based on recent activity
            $status = 'active'; // Default status
            if ($last_visit) {
                $days_since_visit = (time() - strtotime($last_visit)) / (60 * 60 * 24);
                if ($days_since_visit > 365) {
                    $status = 'inactive';
                }
            } else {
                // Check if patient has any appointments
                global $wpdb;
                $appointments_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}hms_appointments WHERE patient_id = %d",
                    $patient->id
                ));
                if ($appointments_count == 0) {
                    $status = 'new';
                }
            }

            $data[] = array(
                'id' => $patient->id,
                'patient_id' => $patient->patient_id,
                'name' => $patient->first_name . ' ' . $patient->last_name,
                'contact' => $contact,
                'registered_on' => date_i18n(get_option('date_format'), strtotime($patient->registration_date)),
                'last_visit' => $last_visit_formatted,
                'status' => $status,
                'actions' => '' // Action buttons will be added by JS
            );
        }

        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
        
        wp_send_json(array(
            'draw' => $draw,
            'recordsTotal' => $total_patients,
            'recordsFiltered' => $total_patients,
            'data' => $data
        ));
    }

    /**
     * AJAX handler to delete a patient.
     */
    public function ajax_delete_patient() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_patients')) { // Assuming a capability like 'delete_patients'
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        
        // Optional: Add a confirmation check here if needed, e.g., check for future appointments or unpaid bills.
        // For now, we proceed with the deletion.

        $deleted = $this->delete_patient($patient_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Patient and their related records have been deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete patient.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update patient medical history.
     */
    public function ajax_update_patient_medical_history() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        $medical_history = sanitize_textarea_field($_POST['medical_history']);
        $allergies = sanitize_textarea_field($_POST['allergies']);

        $updated = $this->update_patient($patient_id, array(
            'medical_history' => $medical_history,
            'allergies' => $allergies
        ));

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            wp_send_json_success(array(
                'message' => esc_html__('Patient medical history updated successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update patient medical history.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a patient's medical history.
     */
    public function ajax_get_patient_medical_history() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('view_patient_medical_history')) { // Assuming a capability
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $patient_id = intval($_GET['patient_id']);
        $offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $per_page = isset($_GET['length']) ? intval($_GET['length']) : 20;
        
        $records = $this->get_medical_history($patient_id, $offset, $per_page);
        // For simplicity, total count is not implemented here but would be similar.
        
        $data = array();
        foreach ($records as $record) {
            $recorded_by_user = get_user_by('ID', $record->recorded_by);
            $recorded_by_name = $recorded_by_user ? $recorded_by_user->display_name : esc_html__('N/A', 'hospital-management-system');

            $data[] = array(
                'id' => $record->id,
                'date' => date_i18n(get_option('date_format'), strtotime($record->record_date)),
                'recorded_by' => $recorded_by_name,
                'diagnosis' => wp_trim_words($record->diagnosis, 15, '...'),
                'treatment' => wp_trim_words($record->treatment, 15, '...'),
                'notes' => wp_trim_words($record->notes, 20, '...'),
                'actions' => '' // Action buttons will be added by JS
            );
        }

        wp_send_json(array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => count($records), // Simplified
            'recordsFiltered' => count($records), // Simplified
            'data' => $data
        ));
    }

    /**
     * AJAX handler to add a medical history record.
     */
    public function ajax_add_medical_history_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patient_medical_history')) { // Assuming a capability
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'    => intval($_POST['patient_id']),
            'recorded_by'   => get_current_user_id(), // Get current logged-in user ID
            'appointment_id'=> intval($_POST['appointment_id']),
            'record_date'   => sanitize_text_field($_POST['record_date']),
            'diagnosis'     => sanitize_textarea_field($_POST['diagnosis']),
            'treatment'     => sanitize_textarea_field($_POST['treatment']),
            'notes'         => sanitize_textarea_field($_POST['notes']),
        );

        $record_id = $this->add_medical_history_record($data);

        if (is_wp_error($record_id)) {
            wp_send_json_error($record_id->get_error_message());
        } elseif ($record_id) {
            $record = $this->get_medical_history_record($record_id);
            wp_send_json_success(array(
                'message' => esc_html__('Medical history record added successfully.', 'hospital-management-system'),
                'record' => $record
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to add medical history record.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to search patients.
     * Used for patient search functionality in medical history page.
     */
    public function ajax_search_patients() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        if (empty($search) || strlen($search) < 2) {
            wp_send_json_error(esc_html__('Please enter at least 2 characters to search.', 'hospital-management-system'));
        }
        
        // Get patients matching the search term
        $patients = $this->get_patients($search, '', '', 0, 20); // Limit to 20 results
        
        if (empty($patients)) {
            wp_send_json_success(array()); // Return empty array
        }
        
        // Format patient data for the response
        $formatted_patients = array();
        foreach ($patients as $patient) {
            $formatted_patients[] = array(
                'id' => $patient->id,
                'patient_id' => $patient->patient_id,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email,
                'contact_number' => $patient->contact_number
            );
        }
        
        wp_send_json_success($formatted_patients);
    }

    /**
     * AJAX handler to export patients data as CSV.
     */
    public function ajax_export_patients() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_patients')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        
        // Get all patients matching the criteria (no pagination for export)
        $patients = $this->get_patients($search, $date_from, $date_to, 0, 10000);
        
        if (empty($patients)) {
            wp_die(esc_html__('No patients found to export.', 'hospital-management-system'));
        }
        
        // Set headers for CSV download
        $filename = 'patients_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        $headers = array(
            __('Patient ID', 'hospital-management-system'),
            __('First Name', 'hospital-management-system'),
            __('Last Name', 'hospital-management-system'),
            __('Date of Birth', 'hospital-management-system'),
            __('Gender', 'hospital-management-system'),
            __('Blood Group', 'hospital-management-system'),
            __('Contact Number', 'hospital-management-system'),
            __('Email', 'hospital-management-system'),
            __('Address', 'hospital-management-system'),
            __('Emergency Contact Name', 'hospital-management-system'),
            __('Emergency Contact Number', 'hospital-management-system'),
            __('National ID', 'hospital-management-system'),
            __('Registration Date', 'hospital-management-system'),
            __('Medical History', 'hospital-management-system'),
            __('Allergies', 'hospital-management-system')
        );
        
        fputcsv($output, $headers);
        
        // Add patient data
        foreach ($patients as $patient) {
            $row = array(
                $patient->patient_id,
                $patient->first_name,
                $patient->last_name,
                $patient->date_of_birth,
                $patient->gender,
                $patient->blood_group,
                $patient->contact_number,
                $patient->email,
                $patient->address,
                $patient->emergency_contact_name,
                $patient->emergency_contact_number,
                $patient->national_id,
                $patient->registration_date,
                $patient->medical_history,
                $patient->allergies
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
