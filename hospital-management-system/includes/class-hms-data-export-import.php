<?php
/**
 * HMS Data Export and Import Class
 *
 * Handles modular data export and import functionality for HMS system.
 */

class HMS_Data_Export_Import {

    private $available_modules = array();
    private $export_progress_key = 'hms_export_progress';
    private $import_progress_key = 'hms_import_progress';

    public function __construct() {
        // Initialize available modules for export/import - delay until init
        add_action('init', array($this, 'init_available_modules'));
        
        // AJAX hooks for export functionality
        add_action('wp_ajax_hms_get_export_modules', array($this, 'ajax_get_export_modules'));
        add_action('wp_ajax_hms_get_export_statistics', array($this, 'ajax_get_export_statistics'));
        add_action('wp_ajax_hms_start_data_export', array($this, 'ajax_start_data_export'));
        add_action('wp_ajax_hms_get_export_progress', array($this, 'ajax_get_export_progress'));
        add_action('wp_ajax_hms_download_export_file', array($this, 'ajax_download_export_file'));
        
        // AJAX hooks for import functionality
        add_action('wp_ajax_hms_start_data_import', array($this, 'ajax_start_data_import'));
        add_action('wp_ajax_hms_get_import_progress', array($this, 'ajax_get_import_progress'));
        add_action('wp_ajax_hms_validate_import_file', array($this, 'ajax_validate_import_file'));
        
        // Hook for handling file uploads
        add_action('wp_ajax_hms_upload_import_file', array($this, 'ajax_upload_import_file'));
        
        // Cron hooks for background processing
        add_action('hms_process_data_export', array($this, 'process_data_export'));
        add_action('hms_process_data_import', array($this, 'process_data_import'));
        
        // Cleanup hooks
        add_action('hms_cleanup_export_import_files', array($this, 'cleanup_old_files'));
        add_action('hms_cleanup_failed_export', array($this, 'cleanup_failed_export'));
    }

    /**
     * Initialize available modules for export/import.
     */
    public function init_available_modules() {
        $this->available_modules = array(
            'patients' => array(
                'name' => __('Patients', 'hospital-management-system'),
                'description' => __('Patient registration data, contact information, and medical history', 'hospital-management-system'),
                'table' => 'hms_patients',
                'icon' => 'dashicons-groups',
                'fields' => array('id', 'patient_id', 'first_name', 'last_name', 'date_of_birth', 'gender', 'blood_group', 'contact_number', 'email', 'address', 'emergency_contact_name', 'emergency_contact_number', 'national_id', 'medical_history', 'allergies', 'registration_date')
            ),
            'appointments' => array(
                'name' => __('Appointments', 'hospital-management-system'),
                'description' => __('Appointment schedules, doctor assignments, and status information', 'hospital-management-system'),
                'table' => 'hms_appointments',
                'icon' => 'dashicons-calendar-alt',
                'fields' => array('id', 'patient_id', 'doctor_id', 'department_id', 'appointment_date', 'appointment_time', 'status', 'reason_for_visit', 'notes', 'token_number', 'created_at')
            ),
            'medical_records' => array(
                'name' => __('Medical Records', 'hospital-management-system'),
                'description' => __('Patient medical records, diagnoses, and treatment plans', 'hospital-management-system'),
                'table' => 'hms_medical_records',
                'icon' => 'dashicons-clipboard',
                'fields' => array('id', 'patient_id', 'appointment_id', 'doctor_id', 'record_type', 'diagnosis', 'symptoms', 'treatment_plan', 'notes', 'created_at')
            ),
            'prescriptions' => array(
                'name' => __('Prescriptions', 'hospital-management-system'),
                'description' => __('Prescription data and medication details', 'hospital-management-system'),
                'table' => 'hms_prescriptions',
                'icon' => 'dashicons-media-document',
                'fields' => array('id', 'prescription_number', 'patient_id', 'appointment_id', 'doctor_id', 'issue_date', 'diagnosis', 'status', 'notes', 'created_at')
            ),
            'prescription_items' => array(
                'name' => __('Prescription Items', 'hospital-management-system'),
                'description' => __('Individual medication items within prescriptions', 'hospital-management-system'),
                'table' => 'hms_prescription_items',
                'icon' => 'dashicons-list-view',
                'fields' => array('id', 'prescription_id', 'medicine_name', 'dosage', 'frequency', 'duration', 'instructions', 'created_at')
            ),
            'bills' => array(
                'name' => __('Bills & Invoices', 'hospital-management-system'),
                'description' => __('Billing information, invoices, and payment records', 'hospital-management-system'),
                'table' => 'hms_bills',
                'icon' => 'dashicons-money-alt',
                'fields' => array('id', 'bill_id', 'patient_id', 'appointment_id', 'bill_number', 'bill_type', 'issue_date', 'due_date', 'total_amount', 'discount_amount', 'tax_percentage', 'tax_amount', 'net_amount', 'paid_amount', 'payment_status', 'notes', 'created_at')
            ),
            'payments' => array(
                'name' => __('Payments', 'hospital-management-system'),
                'description' => __('Payment transactions and billing settlements', 'hospital-management-system'),
                'table' => 'hms_payments',
                'icon' => 'dashicons-bank',
                'fields' => array('id', 'bill_id', 'payment_method', 'transaction_id', 'amount', 'payment_date', 'received_by', 'notes')
            ),
            'pharmacy_dispenses' => array(
                'name' => __('Pharmacy Dispenses', 'hospital-management-system'),
                'description' => __('Medicine dispensing records and pharmacy transactions', 'hospital-management-system'),
                'table' => 'hms_pharmacy_dispenses',
                'icon' => 'dashicons-store',
                'fields' => array('id', 'patient_id', 'prescription_id', 'medicine_id', 'quantity_dispensed', 'unit_price', 'total_price', 'dispense_date', 'dispensed_by', 'notes')
            ),
            'medicines' => array(
                'name' => __('Medicines Inventory', 'hospital-management-system'),
                'description' => __('Medicine catalog and inventory information', 'hospital-management-system'),
                'table' => 'hms_medicines',
                'icon' => 'dashicons-admin-generic',
                'fields' => array('id', 'name', 'generic_name', 'description', 'category', 'manufacturer', 'unit', 'strength', 'stock_quantity', 'reorder_level', 'unit_price', 'selling_price', 'batch_number', 'expiry_date', 'status', 'created_at')
            ),
            'staff' => array(
                'name' => __('Staff Members', 'hospital-management-system'),
                'description' => __('Hospital staff information and roles', 'hospital-management-system'),
                'table' => 'hms_staff',
                'icon' => 'dashicons-admin-users',
                'fields' => array('id', 'staff_id', 'first_name', 'last_name', 'contact_number', 'email', 'date_of_birth', 'gender', 'address', 'role', 'department', 'qualification', 'specialization', 'experience_years', 'joining_date', 'status', 'notes', 'created_at')
            ),
            'departments' => array(
                'name' => __('Departments', 'hospital-management-system'),
                'description' => __('Hospital departments and organizational structure', 'hospital-management-system'),
                'table' => 'hms_departments',
                'icon' => 'dashicons-building',
                'fields' => array('id', 'name', 'description', 'head_of_department_id', 'created_at')
            ),
            'insurance_providers' => array(
                'name' => __('Insurance Providers', 'hospital-management-system'),
                'description' => __('Insurance company information and coverage details', 'hospital-management-system'),
                'table' => 'hms_insurance_providers',
                'icon' => 'dashicons-shield',
                'fields' => array('id', 'name', 'contact_person', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'policy_types', 'coverage_details', 'status', 'notes', 'created_at')
            ),
            'lab_tests' => array(
                'name' => __('Lab Tests', 'hospital-management-system'),
                'description' => __('Laboratory test orders and results', 'hospital-management-system'),
                'table' => 'hms_lab_tests',
                'icon' => 'dashicons-analytics',
                'fields' => array('id', 'patient_id', 'doctor_id', 'test_name', 'test_type', 'test_date', 'status', 'results', 'normal_range', 'notes', 'created_at')
            )
        );
    }

    /**
     * Get available modules for export.
     */
    public function get_available_modules() {
        return $this->available_modules;
    }

    /**
     * AJAX handler to get available export modules.
     */
    public function ajax_get_export_modules() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('export_data')) {
            wp_send_json_error(__('You do not have permission to export data.', 'hospital-management-system'));
            return;
        }

        wp_send_json_success(array(
            'modules' => $this->available_modules
        ));
    }

    /**
     * AJAX handler to get export statistics.
     */
    public function ajax_get_export_statistics() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('export_data')) {
            wp_send_json_error(__('You do not have permission to view export statistics.', 'hospital-management-system'));
            return;
        }

        $selected_modules = isset($_POST['modules']) ? array_map('sanitize_text_field', $_POST['modules']) : array();
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : 'all';
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        if (empty($selected_modules)) {
            wp_send_json_error(__('Please select at least one module.', 'hospital-management-system'));
            return;
        }

        try {
            $statistics = $this->get_export_statistics($selected_modules, $date_range, $start_date, $end_date);
            wp_send_json_success($statistics);
        } catch (Exception $e) {
            wp_send_json_error(__('Failed to get export statistics: ', 'hospital-management-system') . $e->getMessage());
        }
    }

    /**
     * AJAX handler to start data export process.
     */
    public function ajax_start_data_export() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('export_data')) {
            wp_send_json_error(__('You do not have permission to export data.', 'hospital-management-system'));
            return;
        }

        $selected_modules = isset($_POST['modules']) ? array_map('sanitize_text_field', $_POST['modules']) : array();
        $export_format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : 'all';
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        if (empty($selected_modules)) {
            wp_send_json_error(__('Please select at least one module to export.', 'hospital-management-system'));
            return;
        }

        // Validate selected modules
        $validation_result = $this->validate_export_modules($selected_modules);
        if (!$validation_result['valid']) {
            wp_send_json_error($validation_result['message']);
            return;
        }

        // Validate export format
        if (!in_array($export_format, array('csv', 'excel'), true)) {
            wp_send_json_error(__('Invalid export format selected.', 'hospital-management-system'));
            return;
        }

        // Start export process
        $export_id = $this->start_export_process($selected_modules, $export_format, $date_range, $start_date, $end_date);
        
        if ($export_id) {
            wp_send_json_success(array(
                'export_id' => $export_id,
                'message' => __('Export process started successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to start export process.', 'hospital-management-system'));
        }
    }

    /**
     * Start the export process and return export ID.
     */
    private function start_export_process($selected_modules, $format, $date_range, $start_date, $end_date) {
        $export_id = uniqid('hms_export_');
        $export_data = array(
            'id' => $export_id,
            'modules' => $selected_modules,
            'format' => $format,
            'date_range' => $date_range,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => 'processing',
            'progress' => 0,
            'total_records' => 0,
            'processed_records' => 0,
            'created_at' => current_time('mysql'),
            'file_path' => '',
            'file_size' => 0
        );

        // Store export progress
        update_option($this->export_progress_key . '_' . $export_id, $export_data);

        // Schedule background processing
        wp_schedule_single_event(time(), 'hms_process_data_export', array($export_id));

        return $export_id;
    }

    /**
     * Process data export in background.
     */
    public function process_data_export($export_id) {
        $export_data = get_option($this->export_progress_key . '_' . $export_id);
        
        if (!$export_data) {
            return false;
        }

        try {
            // Update status to processing
            $export_data['status'] = 'processing';
            $export_data['current_step'] = 'initializing';
            $export_data['step_details'] = __('Initializing export process...', 'hospital-management-system');
            update_option($this->export_progress_key . '_' . $export_id, $export_data);

            // Calculate total records
            $export_data['current_step'] = 'calculating';
            $export_data['step_details'] = __('Calculating total records to export...', 'hospital-management-system');
            update_option($this->export_progress_key . '_' . $export_id, $export_data);
            
            $total_records = $this->calculate_total_records($export_data['modules'], $export_data['date_range'], $export_data['start_date'], $export_data['end_date']);
            $export_data['total_records'] = $total_records;
            $export_data['processed_records'] = 0;
            $export_data['current_module'] = '';
            $export_data['modules_completed'] = 0;
            $export_data['total_modules'] = count($export_data['modules']);
            update_option($this->export_progress_key . '_' . $export_id, $export_data);

            // Create export file
            $export_data['current_step'] = 'exporting';
            $export_data['step_details'] = __('Creating export file...', 'hospital-management-system');
            update_option($this->export_progress_key . '_' . $export_data['id'], $export_data);
            
            $file_path = $this->create_export_file($export_data);
            
            if ($file_path) {
                $export_data['status'] = 'completed';
                $export_data['progress'] = 100;
                $export_data['current_step'] = 'completed';
                $export_data['step_details'] = __('Export completed successfully!', 'hospital-management-system');
                $export_data['file_path'] = $file_path;
                $export_data['file_size'] = filesize($file_path);
                $export_data['completed_at'] = current_time('mysql');
                $export_data['download_url'] = $this->get_download_url($export_id);
            } else {
                $export_data['status'] = 'failed';
                $export_data['current_step'] = 'failed';
                $export_data['error'] = __('Failed to create export file.', 'hospital-management-system');
                $export_data['step_details'] = __('Export failed during file creation.', 'hospital-management-system');
            }

            update_option($this->export_progress_key . '_' . $export_id, $export_data);
            
        } catch (Exception $e) {
            $export_data['status'] = 'failed';
            $export_data['current_step'] = 'failed';
            $export_data['error'] = $e->getMessage();
            $export_data['step_details'] = sprintf(__('Export failed: %s', 'hospital-management-system'), $e->getMessage());
            update_option($this->export_progress_key . '_' . $export_id, $export_data);
            
            // Log the error
            error_log("HMS Export failed for ID $export_id: " . $e->getMessage());
            
            // Schedule cleanup of failed export after 1 hour
            wp_schedule_single_event(time() + 3600, 'hms_cleanup_failed_export', array($export_id));
        }
    }

    /**
     * Get download URL for export file.
     */
    private function get_download_url($export_id) {
        return admin_url('admin-ajax.php') . '?action=hms_download_export_file&export_id=' . urlencode($export_id) . '&nonce=' . wp_create_nonce('hms_admin_nonce');
    }

    /**
     * Validate selected modules for export.
     */
    private function validate_export_modules($selected_modules) {
        global $wpdb;
        
        $invalid_modules = array();
        $missing_tables = array();
        
        foreach ($selected_modules as $module_key) {
            // Check if module exists in available modules
            if (!isset($this->available_modules[$module_key])) {
                $invalid_modules[] = $module_key;
                continue;
            }
            
            // Check if corresponding table exists
            $module = $this->available_modules[$module_key];
            $table_name = $wpdb->prefix . $module['table'];
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                $missing_tables[] = $module['name'] . " ($table_name)";
            }
        }
        
        if (!empty($invalid_modules)) {
            return array(
                'valid' => false,
                'message' => sprintf(
                    __('Invalid modules selected: %s', 'hospital-management-system'),
                    implode(', ', $invalid_modules)
                )
            );
        }
        
        if (!empty($missing_tables)) {
            return array(
                'valid' => false,
                'message' => sprintf(
                    __('Database tables missing for modules: %s. Please check your database setup.', 'hospital-management-system'),
                    implode(', ', $missing_tables)
                )
            );
        }
        
        return array('valid' => true);
    }

    /**
     * Get export statistics for selected modules.
     */
    public function get_export_statistics($modules, $date_range = 'all', $start_date = '', $end_date = '') {
        global $wpdb;
        
        $statistics = array();
        $total_records = 0;
        
        foreach ($modules as $module_key) {
            if (!isset($this->available_modules[$module_key])) {
                continue;
            }
            
            $module = $this->available_modules[$module_key];
            $table_name = $wpdb->prefix . $module['table'];
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                $statistics[$module_key] = array(
                    'name' => $module['name'],
                    'count' => 0,
                    'status' => 'table_missing'
                );
                continue;
            }
            
            try {
                $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);
                $query = "SELECT COUNT(*) FROM $table_name" . ($where_clause ? " WHERE $where_clause" : "");
                
                $count = intval($wpdb->get_var($query));
                $total_records += $count;
                
                $statistics[$module_key] = array(
                    'name' => $module['name'],
                    'count' => $count,
                    'status' => 'ready'
                );
                
            } catch (Exception $e) {
                $statistics[$module_key] = array(
                    'name' => $module['name'],
                    'count' => 0,
                    'status' => 'error',
                    'error' => $e->getMessage()
                );
            }
        }
        
        return array(
            'modules' => $statistics,
            'total_records' => $total_records,
            'estimated_size' => $this->estimate_export_size($total_records)
        );
    }

    /**
     * Estimate export file size based on record count.
     */
    private function estimate_export_size($record_count) {
        // Rough estimate: 200 bytes per record on average
        $estimated_bytes = $record_count * 200;
        
        if ($estimated_bytes < 1024) {
            return $estimated_bytes . ' B';
        } elseif ($estimated_bytes < 1024 * 1024) {
            return round($estimated_bytes / 1024, 1) . ' KB';
        } else {
            return round($estimated_bytes / (1024 * 1024), 1) . ' MB';
        }
    }

    /**
     * Calculate total records for export.
     */
    private function calculate_total_records($modules, $date_range, $start_date, $end_date) {
        global $wpdb;
        $total = 0;
        $valid_modules = 0;

        foreach ($modules as $module_key) {
            if (!isset($this->available_modules[$module_key])) {
                continue;
            }

            $module = $this->available_modules[$module_key];
            $table_name = $wpdb->prefix . $module['table'];

            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                error_log("HMS Export: Table $table_name does not exist for module $module_key");
                continue;
            }

            try {
                $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);
                $query = "SELECT COUNT(*) FROM $table_name" . ($where_clause ? " WHERE $where_clause" : "");
                
                $count = $wpdb->get_var($query);
                
                if ($wpdb->last_error) {
                    error_log("HMS Export: Database error in count query for $module_key: " . $wpdb->last_error);
                    continue;
                }
                
                $total += intval($count);
                $valid_modules++;
                
            } catch (Exception $e) {
                error_log("HMS Export: Exception in calculate_total_records for $module_key: " . $e->getMessage());
                continue;
            }
        }

        if ($valid_modules === 0) {
            throw new Exception(__('No valid modules found for export. Please check your database tables.', 'hospital-management-system'));
        }

        return $total;
    }

    /**
     * Build date WHERE clause for queries.
     */
    private function build_date_where_clause($date_range, $start_date, $end_date) {
        if ($date_range === 'all') {
            return '';
        }

        $date_field = 'created_at'; // Default date field
        
        switch ($date_range) {
            case 'last_30_days':
                return "$date_field >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'last_90_days':
                return "$date_field >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'last_year':
                return "$date_field >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            case 'custom':
                if ($start_date && $end_date) {
                    return "$date_field BETWEEN '" . esc_sql($start_date) . "' AND '" . esc_sql($end_date) . "'";
                }
                break;
        }

        return '';
    }

    /**
     * Create export file based on format.
     */
    private function create_export_file($export_data) {
        $upload_dir = wp_upload_dir();
        
        if (!$upload_dir || isset($upload_dir['error'])) {
            throw new Exception(__('Unable to access WordPress upload directory.', 'hospital-management-system'));
        }
        
        $hms_export_dir = $upload_dir['basedir'] . '/hms-exports';
        
        // Create directory if it doesn't exist
        if (!file_exists($hms_export_dir)) {
            if (!wp_mkdir_p($hms_export_dir)) {
                throw new Exception(__('Unable to create export directory. Please check file permissions.', 'hospital-management-system'));
            }
        }

        // Check if directory is writable
        if (!is_writable($hms_export_dir)) {
            throw new Exception(__('Export directory is not writable. Please check file permissions.', 'hospital-management-system'));
        }

        $filename = 'hms-export-' . $export_data['id'] . '-' . date('Y-m-d-H-i-s');
        
        try {
            if ($export_data['format'] === 'excel') {
                return $this->create_excel_export($export_data, $hms_export_dir, $filename);
            } else {
                return $this->create_csv_export($export_data, $hms_export_dir, $filename);
            }
        } catch (Exception $e) {
            error_log("HMS Export: File creation failed for {$export_data['id']}: " . $e->getMessage());
            throw new Exception(sprintf(__('Export file creation failed: %s', 'hospital-management-system'), $e->getMessage()));
        }
    }

    /**
     * Create CSV export file.
     */
    private function create_csv_export($export_data, $export_dir, $filename) {
        global $wpdb;
        
        $file_path = $export_dir . '/' . $filename . '.csv';
        $file_handle = fopen($file_path, 'w');
        
        if (!$file_handle) {
            return false;
        }

        // Add UTF-8 BOM for Excel compatibility
        fprintf($file_handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $processed_records = 0;
        $modules_completed = 0;

        foreach ($export_data['modules'] as $module_key) {
            if (!isset($this->available_modules[$module_key])) {
                continue;
            }

            $module = $this->available_modules[$module_key];
            $table_name = $wpdb->prefix . $module['table'];

            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                $modules_completed++;
                continue;
            }

            // Update progress with current module
            $current_export_data = get_option($this->export_progress_key . '_' . $export_data['id']);
            if ($current_export_data) {
                $current_export_data['current_module'] = $module['name'];
                $current_export_data['modules_completed'] = $modules_completed;
                $current_export_data['step_details'] = sprintf(__('Exporting %s data...', 'hospital-management-system'), $module['name']);
                update_option($this->export_progress_key . '_' . $export_data['id'], $current_export_data);
            }

            // Write module header
            fputcsv($file_handle, array('=== ' . $module['name'] . ' ==='));
            
            // Write column headers
            $headers = array();
            foreach ($module['fields'] as $field) {
                $headers[] = ucwords(str_replace('_', ' ', $field));
            }
            fputcsv($file_handle, $headers);

            // Build query
            $where_clause = $this->build_date_where_clause($export_data['date_range'], $export_data['start_date'], $export_data['end_date']);
            $query = "SELECT " . implode(', ', $module['fields']) . " FROM $table_name" . ($where_clause ? " WHERE $where_clause" : "") . " ORDER BY id";

            $results = $wpdb->get_results($query, ARRAY_A);

            if ($results) {
                foreach ($results as $row) {
                    $csv_row = array();
                    foreach ($module['fields'] as $field) {
                        $csv_row[] = isset($row[$field]) ? $row[$field] : '';
                    }
                    fputcsv($file_handle, $csv_row);
                    $processed_records++;

                    // Update progress periodically
                    if ($processed_records % 50 === 0) {
                        $progress = ($processed_records / $export_data['total_records']) * 95; // Cap at 95% until completion
                        $current_export_data = get_option($this->export_progress_key . '_' . $export_data['id']);
                        if ($current_export_data) {
                            $current_export_data['progress'] = $progress;
                            $current_export_data['processed_records'] = $processed_records;
                            $current_export_data['step_details'] = sprintf(
                                __('Exporting %s data... (%d of %d records)', 'hospital-management-system'), 
                                $module['name'], 
                                $processed_records, 
                                $export_data['total_records']
                            );
                            update_option($this->export_progress_key . '_' . $export_data['id'], $current_export_data);
                        }
                    }
                }
            }

            // Add empty line between modules
            fputcsv($file_handle, array(''));
            $modules_completed++;
        }

        fclose($file_handle);
        return $file_path;
    }

    /**
     * Create Excel export file using proper Excel format.
     * Creates a proper Excel workbook with multiple sheets for each module.
     */
    private function create_excel_export($export_data, $export_dir, $filename) {
        global $wpdb;
        
        $file_path = $export_dir . '/' . $filename . '.xlsx';
        
        // Create a simple Excel XML format
        $excel_content = $this->create_excel_xml_content($export_data);
        
        if ($excel_content && file_put_contents($file_path, $excel_content)) {
            return $file_path;
        }
        
        // Fallback to CSV with .xlsx extension if Excel creation fails
        $csv_path = $this->create_csv_export($export_data, $export_dir, $filename);
        
        if ($csv_path) {
            $excel_path = $export_dir . '/' . $filename . '.xlsx';
            copy($csv_path, $excel_path);
            unlink($csv_path); // Remove CSV file
            return $excel_path;
        }
        
        return false;
    }

    /**
     * Create Excel XML content for proper Excel format.
     */
    private function create_excel_xml_content($export_data) {
        global $wpdb;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Styles>' . "\n";
        $xml .= '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#E0E0E0" ss:Pattern="Solid"/></Style>' . "\n";
        $xml .= '</Styles>' . "\n";
        
        $processed_records = 0;
        
        foreach ($export_data['modules'] as $module_key) {
            if (!isset($this->available_modules[$module_key])) {
                continue;
            }

            $module = $this->available_modules[$module_key];
            $table_name = $wpdb->prefix . $module['table'];

            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
                continue;
            }

            // Start worksheet
            $xml .= '<Worksheet ss:Name="' . htmlspecialchars($module['name']) . '">' . "\n";
            $xml .= '<Table>' . "\n";
            
            // Add header row
            $xml .= '<Row ss:StyleID="Header">' . "\n";
            foreach ($module['fields'] as $field) {
                $header = ucwords(str_replace('_', ' ', $field));
                $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";

            // Build query
            $where_clause = $this->build_date_where_clause($export_data['date_range'], $export_data['start_date'], $export_data['end_date']);
            $query = "SELECT " . implode(', ', $module['fields']) . " FROM $table_name" . ($where_clause ? " WHERE $where_clause" : "") . " ORDER BY id";

            $results = $wpdb->get_results($query, ARRAY_A);

            if ($results) {
                foreach ($results as $row) {
                    $xml .= '<Row>' . "\n";
                    foreach ($module['fields'] as $field) {
                        $value = isset($row[$field]) ? $row[$field] : '';
                        $data_type = $this->get_excel_data_type($field, $value);
                        $xml .= '<Cell><Data ss:Type="' . $data_type . '">' . htmlspecialchars($value) . '</Data></Cell>' . "\n";
                    }
                    $xml .= '</Row>' . "\n";
                    $processed_records++;

                    // Update progress periodically
                    if ($processed_records % 100 === 0) {
                        $progress = ($processed_records / $export_data['total_records']) * 100;
                        $export_data['progress'] = min(95, $progress);
                        $export_data['processed_records'] = $processed_records;
                        update_option($this->export_progress_key . '_' . $export_data['id'], $export_data);
                    }
                }
            }

            $xml .= '</Table>' . "\n";
            $xml .= '</Worksheet>' . "\n";
        }
        
        $xml .= '</Workbook>';
        
        return $xml;
    }

    /**
     * Determine Excel data type based on field name and value.
     */
    private function get_excel_data_type($field, $value) {
        // Check for numeric fields
        if (in_array($field, array('id', 'patient_id', 'doctor_id', 'quantity', 'amount', 'price', 'total_amount', 'paid_amount'))) {
            return 'Number';
        }
        
        // Check for date fields
        if (strpos($field, 'date') !== false || strpos($field, '_at') !== false) {
            return 'DateTime';
        }
        
        // Check if value is numeric
        if (is_numeric($value)) {
            return 'Number';
        }
        
        return 'String';
    }

    /**
     * AJAX handler to get export progress.
     */
    public function ajax_get_export_progress() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('export_data')) {
            wp_send_json_error(__('You do not have permission to check export progress.', 'hospital-management-system'));
            return;
        }

        $export_id = isset($_POST['export_id']) ? sanitize_text_field($_POST['export_id']) : '';
        
        if (empty($export_id)) {
            wp_send_json_error(__('Export ID is required.', 'hospital-management-system'));
            return;
        }

        $export_data = get_option($this->export_progress_key . '_' . $export_id);
        
        if (!$export_data) {
            wp_send_json_error(__('Export not found.', 'hospital-management-system'));
            return;
        }

        wp_send_json_success($export_data);
    }

    /**
     * AJAX handler to download export file.
     */
    public function ajax_download_export_file() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('export_data')) {
            wp_die(__('You do not have permission to download export files.', 'hospital-management-system'));
            return;
        }

        $export_id = isset($_GET['export_id']) ? sanitize_text_field($_GET['export_id']) : '';
        
        if (empty($export_id)) {
            wp_die(__('Export ID is required.', 'hospital-management-system'));
            return;
        }

        $export_data = get_option($this->export_progress_key . '_' . $export_id);
        
        if (!$export_data || $export_data['status'] !== 'completed') {
            wp_die(__('Export file not found or not ready for download.', 'hospital-management-system'));
            return;
        }

        $file_path = $export_data['file_path'];
        
        if (!file_exists($file_path)) {
            wp_die(__('Export file not found on server.', 'hospital-management-system'));
            return;
        }

        // Set headers for file download
        $filename = basename($file_path);
        $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        if ($file_extension === 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } else {
            header('Content-Type: text/csv; charset=utf-8');
        }
        
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        // Output file
        readfile($file_path);
        exit;
    }

    /**
     * AJAX handler to upload import file.
     */
    public function ajax_upload_import_file() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('import_data')) {
            wp_send_json_error(__('You do not have permission to import data.', 'hospital-management-system'));
            return;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('No file uploaded or upload error occurred.', 'hospital-management-system'));
            return;
        }

        $file = $_FILES['import_file'];
        $allowed_types = array('text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        if (!in_array($file['type'], $allowed_types, true)) {
            wp_send_json_error(__('Invalid file type. Please upload CSV or Excel files only.', 'hospital-management-system'));
            return;
        }

        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error(__('File size too large. Maximum allowed size is 10MB.', 'hospital-management-system'));
            return;
        }

        // Create upload directory
        $upload_dir = wp_upload_dir();
        $hms_import_dir = $upload_dir['basedir'] . '/hms-imports';
        
        if (!file_exists($hms_import_dir)) {
            wp_mkdir_p($hms_import_dir);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $import_id = uniqid('hms_import_');
        $filename = $import_id . '.' . $file_extension;
        $file_path = $hms_import_dir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Store import file info
            $import_data = array(
                'id' => $import_id,
                'original_filename' => sanitize_file_name($file['name']),
                'file_path' => $file_path,
                'file_size' => $file['size'],
                'status' => 'uploaded',
                'uploaded_at' => current_time('mysql')
            );
            
            update_option($this->import_progress_key . '_' . $import_id, $import_data);
            
            wp_send_json_success(array(
                'import_id' => $import_id,
                'filename' => $file['name'],
                'file_size' => size_format($file['size']),
                'message' => __('File uploaded successfully. Ready for validation.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to save uploaded file.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to validate import file.
     */
    public function ajax_validate_import_file() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('import_data')) {
            wp_send_json_error(__('You do not have permission to validate import files.', 'hospital-management-system'));
            return;
        }

        $import_id = isset($_POST['import_id']) ? sanitize_text_field($_POST['import_id']) : '';
        $target_module = isset($_POST['target_module']) ? sanitize_text_field($_POST['target_module']) : '';
        
        if (empty($import_id) || empty($target_module)) {
            wp_send_json_error(__('Import ID and target module are required.', 'hospital-management-system'));
            return;
        }

        if (!isset($this->available_modules[$target_module])) {
            wp_send_json_error(__('Invalid target module selected.', 'hospital-management-system'));
            return;
        }

        $import_data = get_option($this->import_progress_key . '_' . $import_id);
        
        if (!$import_data || !file_exists($import_data['file_path'])) {
            wp_send_json_error(__('Import file not found.', 'hospital-management-system'));
            return;
        }

        // Validate file format and structure
        $validation_result = $this->validate_import_file_structure($import_data['file_path'], $target_module);
        
        if ($validation_result['success']) {
            // Update import data with validation results
            $import_data['target_module'] = $target_module;
            $import_data['status'] = 'validated';
            $import_data['validation_result'] = $validation_result;
            $import_data['validated_at'] = current_time('mysql');
            
            update_option($this->import_progress_key . '_' . $import_id, $import_data);
            
            wp_send_json_success($validation_result);
        } else {
            wp_send_json_error($validation_result['message']);
        }
    }

    /**
     * Validate import file structure and format.
     */
    private function validate_import_file_structure($file_path, $target_module) {
        $module = $this->available_modules[$target_module];
        $required_fields = $module['fields'];
        
        // Remove 'id' from required fields as it's auto-generated
        $required_fields = array_filter($required_fields, function($field) {
            return $field !== 'id';
        });

        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $validation_errors = array();
        $preview_data = array();
        $total_rows = 0;

        try {
            if (in_array($file_extension, array('csv', 'txt'), true)) {
                $validation_result = $this->validate_csv_file($file_path, $required_fields);
            } else {
                // For Excel files, convert to CSV first or use appropriate library
                $validation_result = $this->validate_excel_file($file_path, $required_fields);
            }
            
            return $validation_result;
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => array($e->getMessage())
            );
        }
    }

    /**
     * Validate CSV file format and structure.
     */
    private function validate_csv_file($file_path, $required_fields) {
        $file_handle = fopen($file_path, 'r');
        
        if (!$file_handle) {
            return array(
                'success' => false,
                'message' => __('Unable to read the uploaded file.', 'hospital-management-system'),
                'errors' => array(__('File is not readable or corrupted.', 'hospital-management-system'))
            );
        }

        $validation_errors = array();
        $preview_data = array();
        $total_rows = 0;
        $headers = array();
        
        // Read first line as headers
        $first_line = fgetcsv($file_handle);
        if ($first_line === false) {
            fclose($file_handle);
            return array(
                'success' => false,
                'message' => __('File appears to be empty or corrupted.', 'hospital-management-system'),
                'errors' => array(__('No data found in the file.', 'hospital-management-system'))
            );
        }
        
        $headers = array_map('trim', $first_line);
        
        // Validate headers against required fields
        $missing_fields = array();
        foreach ($required_fields as $field) {
            $field_found = false;
            foreach ($headers as $header) {
                if (strtolower(str_replace(' ', '_', $header)) === strtolower($field)) {
                    $field_found = true;
                    break;
                }
            }
            if (!$field_found && $field !== 'id') { // Skip 'id' as it's auto-generated
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            $validation_errors[] = sprintf(
                __('Missing required fields: %s', 'hospital-management-system'),
                implode(', ', $missing_fields)
            );
        }
        
        // Read preview data (first 5 rows)
        $preview_count = 0;
        while (($row = fgetcsv($file_handle)) !== false && $preview_count < 5) {
            $preview_data[] = $row;
            $preview_count++;
            $total_rows++;
        }
        
        // Count remaining rows
        while (($row = fgetcsv($file_handle)) !== false) {
            $total_rows++;
        }
        
        fclose($file_handle);
        
        if (empty($validation_errors)) {
            return array(
                'success' => true,
                'message' => __('File validation successful.', 'hospital-management-system'),
                'headers' => $headers,
                'preview_data' => $preview_data,
                'total_rows' => $total_rows,
                'valid_records' => $total_rows
            );
        } else {
            return array(
                'success' => false,
                'message' => __('File validation failed.', 'hospital-management-system'),
                'errors' => $validation_errors,
                'headers' => $headers,
                'preview_data' => $preview_data,
                'total_rows' => $total_rows
            );
        }
    }

    /**
     * Validate Excel file (simplified - converts to CSV first).
     */
    private function validate_excel_file($file_path, $required_fields) {
        // For now, return error as Excel support requires additional libraries
        return array(
            'success' => false,
            'message' => __('Excel file validation is not yet supported. Please convert your file to CSV format.', 'hospital-management-system'),
            'errors' => array(__('Excel files require additional libraries for processing. Please use CSV format instead.', 'hospital-management-system'))
        );
    }

    /**
     * AJAX handler to start data import process.
     */
    public function ajax_start_data_import() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('import_data')) {
            wp_send_json_error(__('You do not have permission to import data.', 'hospital-management-system'));
            return;
        }

        $import_id = isset($_POST['import_id']) ? sanitize_text_field($_POST['import_id']) : '';
        $duplicate_handling = isset($_POST['duplicate_handling']) ? sanitize_text_field($_POST['duplicate_handling']) : 'skip';
        
        if (empty($import_id)) {
            wp_send_json_error(__('Import ID is required.', 'hospital-management-system'));
            return;
        }

        $import_data = get_option($this->import_progress_key . '_' . $import_id);
        
        if (!$import_data || $import_data['status'] !== 'validated') {
            wp_send_json_error(__('Import file not found or not validated.', 'hospital-management-system'));
            return;
        }

        // Update import data with processing status
        $import_data['status'] = 'processing';
        $import_data['progress'] = 0;
        $import_data['duplicate_handling'] = $duplicate_handling;
        $import_data['started_at'] = current_time('mysql');
        
        update_option($this->import_progress_key . '_' . $import_id, $import_data);

        // Schedule background processing
        wp_schedule_single_event(time(), 'hms_process_data_import', array($import_id));

        wp_send_json_success(array(
            'message' => __('Import process started successfully.', 'hospital-management-system')
        ));
    }

    /**
     * Process data import in background.
     */
    public function process_data_import($import_id) {
        $import_data = get_option($this->import_progress_key . '_' . $import_id);
        
        if (!$import_data) {
            return false;
        }

        try {
            // Update status to processing
            $import_data['status'] = 'processing';
            update_option($this->import_progress_key . '_' . $import_id, $import_data);

            // Process the import
            $result = $this->process_import_file($import_data);
            
            if ($result['success']) {
                $import_data['status'] = 'completed';
                $import_data['progress'] = 100;
                $import_data['imported_records'] = $result['imported_records'];
                $import_data['skipped_records'] = $result['skipped_records'];
                $import_data['error_records'] = $result['error_records'];
                $import_data['errors'] = $result['errors'];
                $import_data['completed_at'] = current_time('mysql');
            } else {
                $import_data['status'] = 'failed';
                $import_data['error'] = $result['message'];
            }

            update_option($this->import_progress_key . '_' . $import_id, $import_data);
            
        } catch (Exception $e) {
            $import_data['status'] = 'failed';
            $import_data['error'] = $e->getMessage();
            update_option($this->import_progress_key . '_' . $import_id, $import_data);
        }
    }

    /**
     * Process import file and insert data into database.
     */
    private function process_import_file($import_data) {
        global $wpdb;
        
        $file_path = $import_data['file_path'];
        $target_module = $import_data['target_module'];
        $duplicate_handling = $import_data['duplicate_handling'];
        
        if (!isset($this->available_modules[$target_module])) {
            return array(
                'success' => false,
                'message' => __('Invalid target module.', 'hospital-management-system')
            );
        }

        $module = $this->available_modules[$target_module];
        $table_name = $wpdb->prefix . $module['table'];
        
        $file_handle = fopen($file_path, 'r');
        if (!$file_handle) {
            return array(
                'success' => false,
                'message' => __('Unable to read import file.', 'hospital-management-system')
            );
        }

        // Skip header row
        $headers = fgetcsv($file_handle);
        
        $imported_records = 0;
        $skipped_records = 0;
        $error_records = 0;
        $errors = array();
        $row_number = 1; // Start from 1 (after header)

        while (($row = fgetcsv($file_handle)) !== false) {
            $row_number++;
            
            try {
                // Map CSV row to database fields
                $data = array();
                foreach ($headers as $index => $header) {
                    $field_name = strtolower(str_replace(' ', '_', trim($header)));
                    if (in_array($field_name, $module['fields']) && isset($row[$index])) {
                        $data[$field_name] = sanitize_text_field($row[$index]);
                    }
                }
                
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }
                
                // Handle duplicates based on strategy
                $existing_record = null;
                if ($duplicate_handling !== 'create_new') {
                    // Check for existing record (simplified - check by name or first unique field)
                    $check_field = 'name';
                    if (!isset($data[$check_field])) {
                        $check_field = array_keys($data)[0]; // Use first field
                    }
                    
                    if (isset($data[$check_field])) {
                        $existing_record = $wpdb->get_row(
                            $wpdb->prepare(
                                "SELECT * FROM $table_name WHERE $check_field = %s",
                                $data[$check_field]
                            )
                        );
                    }
                }
                
                if ($existing_record) {
                    if ($duplicate_handling === 'skip') {
                        $skipped_records++;
                        continue;
                    } elseif ($duplicate_handling === 'update') {
                        // Update existing record
                        $result = $wpdb->update(
                            $table_name,
                            $data,
                            array('id' => $existing_record->id)
                        );
                        
                        if ($result !== false) {
                            $imported_records++;
                        } else {
                            $error_records++;
                            $errors[] = sprintf(__('Row %d: Failed to update record.', 'hospital-management-system'), $row_number);
                        }
                    }
                } else {
                    // Insert new record
                    $data['created_at'] = current_time('mysql');
                    
                    $result = $wpdb->insert($table_name, $data);
                    
                    if ($result !== false) {
                        $imported_records++;
                    } else {
                        $error_records++;
                        $errors[] = sprintf(__('Row %d: Failed to insert record - %s', 'hospital-management-system'), $row_number, $wpdb->last_error);
                    }
                }
                
            } catch (Exception $e) {
                $error_records++;
                $errors[] = sprintf(__('Row %d: %s', 'hospital-management-system'), $row_number, $e->getMessage());
            }
        }
        
        fclose($file_handle);
        
        return array(
            'success' => true,
            'imported_records' => $imported_records,
            'skipped_records' => $skipped_records,
            'error_records' => $error_records,
            'errors' => $errors
        );
    }

    /**
     * Parse date field.
     */
    private function parse_date_field($value) {
        if (empty($value)) {
            return null;
        }
        
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date) {
            $date = DateTime::createFromFormat('m/d/Y', $value);
        }
        if (!$date) {
            $date = DateTime::createFromFormat('d/m/Y', $value);
        }
        
        if (!$date) {
            throw new Exception(sprintf(__('Invalid date format: %s', 'hospital-management-system'), $value));
        }
        
        return $date->format('Y-m-d');
    }

    /**
     * Parse decimal field.
     */
    private function parse_decimal_field($value) {
        if (empty($value)) {
            return 0.00;
        }
        
        // Remove currency symbols and spaces
        $value = preg_replace('/[^\d.-]/', '', $value);
        
        if (!is_numeric($value)) {
            throw new Exception(sprintf(__('Invalid decimal value: %s', 'hospital-management-system'), $value));
        }
        
        return floatval($value);
    }



    /**
     * Check for duplicate records.
     */
    private function check_for_duplicates($table_name, $record_data, $module) {
        global $wpdb;
        
        // Define unique fields for each module
        $unique_fields = array(
            'hms_patients' => array('patient_id', 'email'),
            'hms_staff' => array('staff_id', 'email'),
            'hms_medicines' => array('name', 'batch_number'),
            'hms_departments' => array('name'),
            'hms_insurance_providers' => array('name')
        );
        
        $table_key = str_replace($wpdb->prefix, '', $table_name);
        
        if (!isset($unique_fields[$table_key])) {
            return false; // No duplicate checking for this table
        }
        
        $where_conditions = array();
        $where_values = array();
        
        foreach ($unique_fields[$table_key] as $field) {
            if (isset($record_data[$field]) && !empty($record_data[$field])) {
                $where_conditions[] = "$field = %s";
                $where_values[] = $record_data[$field];
            }
        }
        
        if (empty($where_conditions)) {
            return false;
        }
        
        $query = "SELECT id FROM $table_name WHERE " . implode(' OR ', $where_conditions);
        $existing_id = $wpdb->get_var($wpdb->prepare($query, $where_values));
        
        return $existing_id ? intval($existing_id) : false;
    }


    /**
     * Parse time field from various formats.
     */
    private function parse_time_field($value) {
        if (empty($value)) {
            return null;
        }
        
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new Exception(sprintf(__('Invalid time format: %s', 'hospital-management-system'), $value));
        }
        
        return date('H:i:s', $timestamp);
    }

    

    /**
     * Parse integer field.
     */
    private function parse_integer_field($value) {
        if (empty($value)) {
            return 0;
        }
        
        if (!is_numeric($value)) {
            throw new Exception(sprintf(__('Invalid integer value: %s', 'hospital-management-system'), $value));
        }
        
        return intval($value);
    }

    /**
     * AJAX handler to get import progress.
     */
    public function ajax_get_import_progress() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('import_data')) {
            wp_send_json_error(__('You do not have permission to check import progress.', 'hospital-management-system'));
            return;
        }

        $import_id = isset($_POST['import_id']) ? sanitize_text_field($_POST['import_id']) : '';
        
        if (empty($import_id)) {
            wp_send_json_error(__('Import ID is required.', 'hospital-management-system'));
            return;
        }

        $import_data = get_option($this->import_progress_key . '_' . $import_id);
        
        if (!$import_data) {
            wp_send_json_error(__('Import not found.', 'hospital-management-system'));
            return;
        }

        wp_send_json_success($import_data);
    }

    /**
     * Clean up old export/import files (should be called via cron).
     */
    public function cleanup_old_files() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/hms-exports';
        $import_dir = $upload_dir['basedir'] . '/hms-imports';
        
        $directories = array($export_dir, $import_dir);
        $max_age = 7 * 24 * 60 * 60; // 7 days
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $max_age) {
                    unlink($file);
                }
            }
        }
        
        // Also clean up old progress data
        $this->cleanup_old_progress_data();
    }

    /**
     * Clean up old progress data from options table.
     */
    private function cleanup_old_progress_data() {
        global $wpdb;
        
        // Get all export/import progress options older than 7 days
        $old_options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} 
                WHERE (option_name LIKE %s OR option_name LIKE %s) 
                AND option_value LIKE %s",
                $this->export_progress_key . '_%',
                $this->import_progress_key . '_%',
                '%"created_at"%'
            )
        );
        
        foreach ($old_options as $option) {
            $data = get_option($option->option_name);
            if ($data && isset($data['created_at'])) {
                $created_time = strtotime($data['created_at']);
                if ($created_time && (time() - $created_time) > (7 * 24 * 60 * 60)) {
                    delete_option($option->option_name);
                }
            }
        }
    }

    /**
     * Clean up failed export data and files.
     */
    public function cleanup_failed_export($export_id) {
        // Remove progress data
        delete_option($this->export_progress_key . '_' . $export_id);
        
        // Remove any partial files
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/hms-exports';
        
        if (is_dir($export_dir)) {
            $pattern = $export_dir . '/hms-export-' . $export_id . '-*';
            $files = glob($pattern);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}