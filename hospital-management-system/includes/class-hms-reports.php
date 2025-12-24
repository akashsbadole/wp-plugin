<?php
/**
 * HMS Reports Class
 *
 * Handles Reports & Analytics features.
 */

class HMS_Reports {

    public function __construct() {
        // AJAX hooks for report generation
        add_action('wp_ajax_hms_generate_patient_census_report', array($this, 'ajax_generate_patient_census_report'));
        add_action('wp_ajax_hms_generate_revenue_report', array($this, 'ajax_generate_revenue_report'));
        add_action('wp_ajax_hms_generate_insurance_pending_report', array($this, 'ajax_generate_insurance_pending_report'));
        add_action('wp_ajax_hms_generate_pharmacy_sales_report', array($this, 'ajax_generate_pharmacy_sales_report'));
        add_action('wp_ajax_hms_generate_disease_statistics_report', array($this, 'ajax_generate_disease_statistics_report'));
        add_action('wp_ajax_hms_generate_claim_tracking_report', array($this, 'ajax_generate_claim_tracking_report'));
        // AJAX hook for CSV export
        add_action('wp_ajax_hms_export_report_csv', array($this, 'ajax_export_report_csv'));
        
        // Dashboard AJAX hooks
        add_action('wp_ajax_hms_get_dashboard_kpis', array($this, 'ajax_get_dashboard_kpis'));
        add_action('wp_ajax_hms_get_patient_trend_data', array($this, 'ajax_get_patient_trend_data'));
        add_action('wp_ajax_hms_get_revenue_department_data', array($this, 'ajax_get_revenue_department_data'));
        add_action('wp_ajax_hms_get_appointment_status_data', array($this, 'ajax_get_appointment_status_data'));
        add_action('wp_ajax_hms_get_top_diagnoses_data', array($this, 'ajax_get_top_diagnoses_data'));
        add_action('wp_ajax_hms_get_todays_summary', array($this, 'ajax_get_todays_summary'));
        add_action('wp_ajax_hms_get_department_performance', array($this, 'ajax_get_department_performance'));
        add_action('wp_ajax_hms_get_recent_activities', array($this, 'ajax_get_recent_activities'));
    }

    /**
     * Log error with context information.
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param string $level Error level (error, warning, info)
     */
    private function log_error($message, $context = array(), $level = 'error') {
        $log_message = sprintf(
            '[HMS Reports %s] %s | Context: %s',
            strtoupper($level),
            $message,
            wp_json_encode($context)
        );
        
        if (function_exists('error_log')) {
            error_log($log_message);
        }
        
        // Also log to WordPress debug log if WP_DEBUG_LOG is enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($log_message);
        }
    }

    /**
     * Handle database errors with proper logging and user feedback.
     *
     * @param string $query_type Type of query that failed
     * @param string $error_message Database error message
     * @param array $context Additional context
     * @return array Error response for AJAX
     */
    private function handle_database_error($query_type, $error_message, $context = array()) {
        global $wpdb;
        
        $full_error = $wpdb->last_error ? $wpdb->last_error : $error_message;
        
        $this->log_error(
            sprintf('Database error in %s: %s', $query_type, $full_error),
            array_merge($context, array(
                'query_type' => $query_type,
                'last_query' => $wpdb->last_query,
                'db_error' => $full_error
            ))
        );
        
        return array(
            'success' => false,
            'message' => sprintf(
                /* translators: %s: query type */
                esc_html__('Database error occurred while generating %s report. Please try again or contact administrator.', 'hospital-management-system'),
                $query_type
            )
        );
    }

    /**
     * Validate date range inputs.
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|true Returns error array if invalid, true if valid
     */
    private function validate_date_range($start_date, $end_date) {
        if (empty($start_date) || empty($end_date)) {
            return array(
                'success' => false,
                'message' => esc_html__('Start date and end date are required.', 'hospital-management-system')
            );
        }
        
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        if ($start_timestamp === false || $end_timestamp === false) {
            return array(
                'success' => false,
                'message' => esc_html__('Invalid date format. Please use YYYY-MM-DD format.', 'hospital-management-system')
            );
        }
        
        if ($start_timestamp > $end_timestamp) {
            return array(
                'success' => false,
                'message' => esc_html__('Start date cannot be later than end date.', 'hospital-management-system')
            );
        }
        
        // Check if date range is not too large (more than 2 years)
        $max_range = 2 * 365 * 24 * 60 * 60; // 2 years in seconds
        if (($end_timestamp - $start_timestamp) > $max_range) {
            return array(
                'success' => false,
                'message' => esc_html__('Date range cannot exceed 2 years. Please select a smaller range.', 'hospital-management-system')
            );
        }
        
        return true;
    }

    /**
     * Execute database query with error handling.
     *
     * @param string $query SQL query
     * @param string $query_type Type of query for error reporting
     * @param array $context Additional context for logging
     * @return array|object Returns error array on failure, query results on success
     */
    private function execute_query($query, $query_type, $context = array()) {
        global $wpdb;
        
        try {
            $results = $wpdb->get_results($query);
            
            if ($wpdb->last_error) {
                return $this->handle_database_error($query_type, $wpdb->last_error, $context);
            }
            
            if ($results === null) {
                $this->log_error(
                    sprintf('Query returned null for %s', $query_type),
                    array_merge($context, array('query' => $query)),
                    'warning'
                );
                return array();
            }
            
            return $results;
            
        } catch (Exception $e) {
            return $this->handle_database_error($query_type, $e->getMessage(), $context);
        }
    }

    /**
     * Get patient census data.
     */
    public function get_patient_census_data($start_date, $end_date) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';

        // Validate table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            $this->log_error('Patient table does not exist', array('table' => $table_name));
            return array(
                'success' => false,
                'message' => esc_html__('Patient data table not found. Please contact administrator.', 'hospital-management-system')
            );
        }

        $query = $wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as new_patients
             FROM $table_name
             WHERE created_at BETWEEN %s AND %s
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            $start_date,
            $end_date
        );

        return $this->execute_query($query, 'patient census', array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'table' => $table_name
        ));
    }

    /**
     * Get claim tracking and settlement data.
     */
    public function get_claim_tracking_data($start_date, $end_date) {
        global $wpdb;
        $claims_table = $wpdb->prefix . 'hms_claims';
        $insurance_providers_table = $wpdb->prefix . 'hms_insurance_providers';
        $patients_table = $wpdb->prefix . 'hms_patients';

        $query = $wpdb->prepare(
            "SELECT c.id as claim_id, c.claim_amount, c.status, c.preauth_status, c.initiated_at, c.settled_at, c.settled_amount,
                    ip.name as insurance_provider, p.first_name, p.last_name, p.patient_id as patient_reg_id
             FROM $claims_table c
             JOIN $insurance_providers_table ip ON c.insurance_provider_id = ip.id
             JOIN $patients_table p ON c.patient_id = p.id
             WHERE c.initiated_at BETWEEN %s AND %s
             ORDER BY c.initiated_at DESC",
            $start_date,
            $end_date
        );

        return $wpdb->get_results($query);
    }

    /**
     * Get revenue data by department/doctor.
     */
    public function get_revenue_data($start_date, $end_date, $group_by = 'department') {
        global $wpdb;
        $bills_table = $wpdb->prefix . 'hms_bills';
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        $staff_table = $wpdb->prefix . 'hms_staff';

        // Validate required tables exist
        $required_tables = array($bills_table, $appointments_table, $staff_table);
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                $this->log_error('Required table does not exist for revenue report', array('table' => $table));
                return array(
                    'success' => false,
                    'message' => esc_html__('Required database tables not found. Please contact administrator.', 'hospital-management-system')
                );
            }
        }

        // Validate group_by parameter
        if (!in_array($group_by, array('department', 'doctor'), true)) {
            $this->log_error('Invalid group_by parameter', array('group_by' => $group_by));
            return array(
                'success' => false,
                'message' => esc_html__('Invalid grouping parameter.', 'hospital-management-system')
            );
        }

        $select_clause = ($group_by === 'doctor') ? "s.first_name, s.last_name, s.staff_id" : "b.department";
        $group_by_clause = ($group_by === 'doctor') ? "s.id" : "b.department";

        $query = $wpdb->prepare(
            "SELECT $select_clause, SUM(b.total_amount) as total_revenue, COUNT(b.id) as total_bills
             FROM $bills_table b
             LEFT JOIN $appointments_table a ON b.appointment_id = a.id
             LEFT JOIN $staff_table s ON a.doctor_id = s.id
             WHERE b.created_at BETWEEN %s AND %s AND b.status = 'paid'
             GROUP BY $group_by_clause
             ORDER BY total_revenue DESC",
            $start_date,
            $end_date
        );

        return $this->execute_query($query, 'revenue', array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'group_by' => $group_by
        ));
    }
    
    /**
     * Get insurance pending claims data.
     */
    public function get_insurance_pending_data() {
        global $wpdb;
        $bills_table = $wpdb->prefix . 'hms_bills';
        $insurance_providers_table = $wpdb->prefix . 'hms_insurance_providers';

        // Validate required tables exist
        $required_tables = array($bills_table, $insurance_providers_table);
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                $this->log_error('Required table does not exist for insurance pending report', array('table' => $table));
                return array(
                    'success' => false,
                    'message' => esc_html__('Required database tables not found. Please contact administrator.', 'hospital-management-system')
                );
            }
        }

        $query = "SELECT ip.name as provider_name, b.bill_id, b.patient_id, b.total_amount, b.due_amount, b.created_at
                 FROM $bills_table b
                 JOIN $insurance_providers_table ip ON b.insurance_provider_id = ip.id
                 WHERE b.status = 'pending_insurance' AND b.due_amount > 0
                 ORDER BY b.created_at ASC";

        return $this->execute_query($query, 'insurance pending', array(
            'tables' => $required_tables
        ));
    }

    /**
     * Get pharmacy sales data.
     */
    public function get_pharmacy_sales_data($start_date, $end_date) {
        global $wpdb;
        $dispense_table = $wpdb->prefix . 'hms_pharmacy_dispense';
        $medicines_table = $wpdb->prefix . 'hms_medicines';

        // Validate required tables exist
        $required_tables = array($dispense_table, $medicines_table);
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                $this->log_error('Required table does not exist for pharmacy sales report', array('table' => $table));
                return array(
                    'success' => false,
                    'message' => esc_html__('Required pharmacy tables not found. Please contact administrator.', 'hospital-management-system')
                );
            }
        }

        $query = $wpdb->prepare(
            "SELECT m.name as medicine_name, SUM(d.quantity) as total_quantity, SUM(d.total_price) as total_sales
             FROM $dispense_table d
             JOIN $medicines_table m ON d.medicine_id = m.id
             WHERE d.dispensed_at BETWEEN %s AND %s
             GROUP BY d.medicine_id
             ORDER BY total_sales DESC",
            $start_date,
            $end_date
        );

        return $this->execute_query($query, 'pharmacy sales', array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tables' => $required_tables
        ));
    }

    /**
     * Get disease/diagnosis statistics.
     * Note: This requires a table for diagnoses, which is not yet implemented.
     * For now, we can use prescription data as a proxy.
     */
    public function get_disease_statistics_data($start_date, $end_date) {
        global $wpdb;
        // This is a placeholder. A proper implementation would require a diagnoses table.
        // For now, we'll use the 'notes' field from prescriptions as a proxy.
        $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';

        $query = $wpdb->prepare(
            "SELECT diagnosis_notes, COUNT(*) as count
             FROM $prescriptions_table
             WHERE created_at BETWEEN %s AND %s AND diagnosis_notes != ''
             GROUP BY diagnosis_notes
             ORDER BY count DESC
             LIMIT 10", // Top 10 diagnoses
            $start_date,
            $end_date
        );

        return $wpdb->get_results($query);
    }

    /**
     * AJAX handler for Patient Census Report.
     */
    public function ajax_generate_patient_census_report() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            // Validate date range
            $date_validation = $this->validate_date_range($start_date, $end_date);
            if ($date_validation !== true) {
                wp_send_json_error($date_validation['message']);
                return;
            }

            $data = $this->get_patient_census_data($start_date, $end_date);
            
            // Check if data retrieval failed
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            $report_html = $this->render_patient_census_report_html($data, $start_date, $end_date);
            wp_send_json_success(array('report_html' => $report_html));
            
        } catch (Exception $e) {
            $this->log_error('Exception in patient census report generation', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred. Please try again.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for Claim Tracking Report.
     */
    public function ajax_generate_claim_tracking_report() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_reports')) {
            wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
        }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

        $data = $this->get_claim_tracking_data($start_date, $end_date);
        $report_html = $this->render_claim_tracking_report_html($data, $start_date, $end_date);

        wp_send_json_success(array('report_html' => $report_html));
    }

    /**
     * AJAX handler for Revenue Report.
     */
    public function ajax_generate_revenue_report() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');
            $group_by = isset($_POST['group_by']) ? sanitize_text_field($_POST['group_by']) : 'department';

            // Validate date range
            $date_validation = $this->validate_date_range($start_date, $end_date);
            if ($date_validation !== true) {
                wp_send_json_error($date_validation['message']);
                return;
            }

            $data = $this->get_revenue_data($start_date, $end_date, $group_by);
            
            // Check if data retrieval failed
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            $report_html = $this->render_revenue_report_html($data, $start_date, $end_date, $group_by);
            wp_send_json_success(array('report_html' => $report_html));
            
        } catch (Exception $e) {
            $this->log_error('Exception in revenue report generation', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred. Please try again.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for Insurance Pending Report.
     */
    public function ajax_generate_insurance_pending_report() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_reports')) {
            wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
        }

        $data = $this->get_insurance_pending_data();
        $report_html = $this->render_insurance_pending_report_html($data);

        wp_send_json_success(array('report_html' => $report_html));
    }

    /**
     * AJAX handler for Pharmacy Sales Report.
     */
    public function ajax_generate_pharmacy_sales_report() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_reports')) {
            wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
        }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

        $data = $this->get_pharmacy_sales_data($start_date, $end_date);
        $report_html = $this->render_pharmacy_sales_report_html($data, $start_date, $end_date);

        wp_send_json_success(array('report_html' => $report_html));
    }

    /**
     * AJAX handler for Disease Statistics Report.
     */
    public function ajax_generate_disease_statistics_report() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_reports')) {
            wp_send_json_error(esc_html__('You do not have permission to generate reports.', 'hospital-management-system'));
        }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

        $data = $this->get_disease_statistics_data($start_date, $end_date);
        $report_html = $this->render_disease_statistics_report_html($data, $start_date, $end_date);

        wp_send_json_success(array('report_html' => $report_html));
    }

    /**
     * AJAX handler for CSV Export.
     */
    public function ajax_export_report_csv() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to export reports.', 'hospital-management-system'));
                return;
            }

            $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : '';
            if (empty($report_type)) {
                wp_send_json_error(esc_html__('Report type is missing.', 'hospital-management-system'));
                return;
            }

            // Validate report type
            $valid_report_types = array(
                'patient_census', 'revenue', 'insurance_pending', 
                'pharmacy_sales', 'disease_statistics', 'claim_tracking'
            );
            
            if (!in_array($report_type, $valid_report_types, true)) {
                $this->log_error('Invalid report type for CSV export', array('report_type' => $report_type));
                wp_send_json_error(esc_html__('Invalid report type.', 'hospital-management-system'));
                return;
            }

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');
        $group_by = isset($_POST['group_by']) ? sanitize_text_field($_POST['group_by']) : 'department';

        $data = array();
        $headers = array();
        $filename = 'hms-report.csv';

        switch ($report_type) {
            case 'patient_census':
                $data = $this->get_patient_census_data($start_date, $end_date);
                $headers = array(esc_html__('Date', 'hospital-management-system'), esc_html__('New Patients', 'hospital-management-system'));
                $filename = 'patient-census-' . $start_date . '-to-' . $end_date . '.csv';
                break;
            case 'revenue':
                $data = $this->get_revenue_data($start_date, $end_date, $group_by);
                $headers = array(
                    ($group_by === 'doctor') ? esc_html__('Doctor', 'hospital-management-system') : esc_html__('Department', 'hospital-management-system'),
                    esc_html__('Total Revenue', 'hospital-management-system'),
                    esc_html__('Total Bills', 'hospital-management-system')
                );
                $filename = 'revenue-report-' . $start_date . '-to-' . $end_date . '.csv';
                break;
            case 'insurance_pending':
                $data = $this->get_insurance_pending_data();
                $headers = array(
                    esc_html__('Provider', 'hospital-management-system'),
                    esc_html__('Bill ID', 'hospital-management-system'),
                    esc_html__('Patient ID', 'hospital-management-system'),
                    esc_html__('Total Amount', 'hospital-management-system'),
                    esc_html__('Due Amount', 'hospital-management-system'),
                    esc_html__('Created At', 'hospital-management-system')
                );
                $filename = 'insurance-pending-report.csv';
                break;
            case 'pharmacy_sales':
                $data = $this->get_pharmacy_sales_data($start_date, $end_date);
                $headers = array(
                    esc_html__('Medicine', 'hospital-management-system'),
                    esc_html__('Total Quantity', 'hospital-management-system'),
                    esc_html__('Total Sales', 'hospital-management-system')
                );
                $filename = 'pharmacy-sales-' . $start_date . '-to-' . $end_date . '.csv';
                break;
            case 'disease_statistics':
                $data = $this->get_disease_statistics_data($start_date, $end_date);
                $headers = array(
                    esc_html__('Diagnosis', 'hospital-management-system'),
                    esc_html__('Count', 'hospital-management-system')
                );
                $filename = 'disease-statistics-' . $start_date . '-to-' . $end_date . '.csv';
                break;
            case 'claim_tracking':
                $data = $this->get_claim_tracking_data($start_date, $end_date);
                $headers = array(
                    esc_html__('Claim ID', 'hospital-management-system'),
                    esc_html__('Patient', 'hospital-management-system'),
                    esc_html__('Insurance Provider', 'hospital-management-system'),
                    esc_html__('Claim Amount', 'hospital-management-system'),
                    esc_html__('Status', 'hospital-management-system'),
                    esc_html__('Pre-Auth Status', 'hospital-management-system'),
                    esc_html__('Settled Amount', 'hospital-management-system'),
                    esc_html__('Initiated At', 'hospital-management-system'),
                    esc_html__('Settled At', 'hospital-management-system')
                );
                $filename = 'claim-tracking-' . $start_date . '-to-' . $end_date . '.csv';
                break;
            default:
                wp_send_json_error(esc_html__('Invalid report type.', 'hospital-management-system'));
                break;
        }

        if (empty($data)) {
            wp_send_json_error(esc_html__('No data available to export.', 'hospital-management-system'));
        }

        // Generate CSV
        $csv_output = fopen('php://output', 'w');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        // Add UTF-8 BOM to fix Excel issues
        fprintf($csv_output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add headers
        fputcsv($csv_output, $headers);

        // Add data rows
        foreach ($data as $row) {
            $csv_row = array();
            foreach ($headers as $header_key => $header_value) {
                // Access object properties dynamically
                $property_key = strtolower(str_replace(' ', '_', $header_value));
                if (property_exists($row, $property_key)) {
                    $csv_row[] = $row->$property_key;
                } else {
                    // Handle special cases like combined fields (e.g., Doctor)
                    if ($report_type === 'revenue' && $group_by === 'doctor' && $header_key === 0) {
                        $csv_row[] = $row->first_name . ' ' . $row->last_name . ' (' . $row->staff_id . ')';
                    } elseif ($report_type === 'claim_tracking' && $header_key === 1) {
                         $csv_row[] = $row->first_name . ' ' . $row->last_name . ' (' . $row->patient_reg_id . ')';
                    } else {
                        $csv_row[] = ''; // Default empty if property not found
                    }
                }
            }
            fputcsv($csv_output, $csv_row);
        }

        fclose($csv_output);
        exit();
        
        } catch (Exception $e) {
            $this->log_error('Exception in CSV export', array(
                'error' => $e->getMessage(),
                'report_type' => $report_type ?? 'unknown'
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred during export.', 'hospital-management-system'));
        }
    }

    /**
     * Render HTML for Patient Census Report.
     */
    private function render_patient_census_report_html($data, $start_date, $end_date) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Patient Census Report', 'hospital-management-system'); ?></h3>
        <p><strong><?php esc_html_e('Period:', 'hospital-management-system'); ?></strong> <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('New Patients', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->date); ?></td>
                            <td><?php echo esc_html($row->new_patients); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"><?php esc_html_e('No data found for the selected period.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render HTML for Revenue Report.
     */
    private function render_revenue_report_html($data, $start_date, $end_date, $group_by) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Revenue Report', 'hospital-management-system'); ?></h3>
        <p><strong><?php esc_html_e('Period:', 'hospital-management-system'); ?></strong> <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></p>
        <p><strong><?php esc_html_e('Grouped By:', 'hospital-management-system'); ?></strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $group_by))); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo ($group_by === 'doctor') ? esc_html__('Doctor', 'hospital-management-system') : esc_html__('Department', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Total Revenue', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Total Bills', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td>
                                <?php
                                if ($group_by === 'doctor') {
                                    echo esc_html($row->first_name . ' ' . $row->last_name . ' (' . $row->staff_id . ')');
                                } else {
                                    echo esc_html(ucwords(str_replace('_', ' ', $row->department)));
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html(number_format($row->total_revenue, 2)); ?></td>
                            <td><?php echo esc_html($row->total_bills); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><?php esc_html_e('No data found for the selected period.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render HTML for Insurance Pending Report.
     */
    private function render_insurance_pending_report_html($data) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Insurance Pending Claims Report', 'hospital-management-system'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Provider', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Bill ID', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Patient ID', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Total Amount', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Due Amount', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Created At', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->provider_name); ?></td>
                            <td><?php echo esc_html($row->bill_id); ?></td>
                            <td><?php echo esc_html($row->patient_id); ?></td>
                            <td><?php echo esc_html(number_format($row->total_amount, 2)); ?></td>
                            <td><?php echo esc_html(number_format($row->due_amount, 2)); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e('No pending insurance claims found.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render HTML for Pharmacy Sales Report.
     */
    private function render_pharmacy_sales_report_html($data, $start_date, $end_date) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Pharmacy Sales Report', 'hospital-management-system'); ?></h3>
        <p><strong><?php esc_html_e('Period:', 'hospital-management-system'); ?></strong> <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Medicine', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Total Quantity', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Total Sales', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->medicine_name); ?></td>
                            <td><?php echo esc_html($row->total_quantity); ?></td>
                            <td><?php echo esc_html(number_format($row->total_sales, 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><?php esc_html_e('No sales data found for the selected period.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render HTML for Disease Statistics Report.
     */
    private function render_disease_statistics_report_html($data, $start_date, $end_date) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Disease/Diagnosis Statistics Report', 'hospital-management-system'); ?></h3>
        <p><strong><?php esc_html_e('Period:', 'hospital-management-system'); ?></strong> <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></p>
        <p><em><?php esc_html_e('Note: This report is based on diagnosis notes from prescriptions and may not represent all cases.', 'hospital-management-system'); ?></em></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Diagnosis', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Count', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->diagnosis_notes); ?></td>
                            <td><?php echo esc_html($row->count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"><?php esc_html_e('No diagnosis data found for the selected period.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render HTML for Claim Tracking Report.
     */
    private function render_claim_tracking_report_html($data, $start_date, $end_date) {
        ob_start();
        ?>
        <h3><?php esc_html_e('Claim Tracking and Settlement Report', 'hospital-management-system'); ?></h3>
        <p><strong><?php esc_html_e('Period:', 'hospital-management-system'); ?></strong> <?php echo esc_html($start_date); ?> to <?php echo esc_html($end_date); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Claim ID', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Patient', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Insurance Provider', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Claim Amount', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Pre-Auth Status', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Settled Amount', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Initiated At', 'hospital-management-system'); ?></th>
                    <th><?php esc_html_e('Settled At', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data): ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->claim_id); ?></td>
                            <td><?php echo esc_html($row->first_name . ' ' . $row->last_name . ' (' . $row->patient_reg_id . ')'); ?></td>
                            <td><?php echo esc_html($row->insurance_provider); ?></td>
                            <td><?php echo esc_html(number_format($row->claim_amount, 2)); ?></td>
                            <td><?php echo esc_html(ucwords(str_replace('_', ' ', $row->status))); ?></td>
                            <td><?php echo esc_html(ucwords(str_replace('_', ' ', $row->preauth_status))); ?></td>
                            <td><?php echo ($row->settled_amount > 0) ? esc_html(number_format($row->settled_amount, 2)) : 'N/A'; ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->initiated_at))); ?></td>
                            <td><?php echo (!empty($row->settled_at)) ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->settled_at))) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9"><?php esc_html_e('No claim data found for the selected period.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Dashboard Methods for Analytics
     */

    /**
     * Get dashboard KPIs data.
     */
    public function get_dashboard_kpis($start_date, $end_date) {
        global $wpdb;
        
        $patients_table = $wpdb->prefix . 'hms_patients';
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        $bills_table = $wpdb->prefix . 'hms_bills';
        
        $kpis = array();
        
        // Total patients in date range
        $patients_query = $wpdb->prepare(
            "SELECT COUNT(*) as total FROM $patients_table WHERE created_at BETWEEN %s AND %s",
            $start_date, $end_date
        );
        $kpis['total_patients'] = (int) $wpdb->get_var($patients_query);
        
        // Total appointments in date range
        $appointments_query = $wpdb->prepare(
            "SELECT COUNT(*) as total FROM $appointments_table WHERE appointment_date BETWEEN %s AND %s",
            $start_date, $end_date
        );
        $kpis['total_appointments'] = (int) $wpdb->get_var($appointments_query);
        
        // Total revenue in date range
        $revenue_query = $wpdb->prepare(
            "SELECT SUM(total_amount) as total FROM $bills_table WHERE created_at BETWEEN %s AND %s AND status = 'paid'",
            $start_date, $end_date
        );
        $kpis['total_revenue'] = (float) $wpdb->get_var($revenue_query) ?: 0;
        
        // Pending bills
        $pending_query = "SELECT COUNT(*) as total FROM $bills_table WHERE status IN ('pending', 'pending_insurance')";
        $kpis['pending_bills'] = (int) $wpdb->get_var($pending_query);
        
        // Calculate trends (compare with previous period)
        $period_diff = strtotime($end_date) - strtotime($start_date);
        $prev_start = date('Y-m-d', strtotime($start_date) - $period_diff);
        $prev_end = date('Y-m-d', strtotime($end_date) - $period_diff);
        
        $kpis['patients_trend'] = $this->calculate_trend($patients_table, 'created_at', $start_date, $end_date, $prev_start, $prev_end);
        $kpis['appointments_trend'] = $this->calculate_trend($appointments_table, 'appointment_date', $start_date, $end_date, $prev_start, $prev_end);
        $kpis['revenue_trend'] = $this->calculate_revenue_trend($start_date, $end_date, $prev_start, $prev_end);
        
        return $kpis;
    }

    /**
     * Calculate trend percentage between two periods.
     */
    private function calculate_trend($table, $date_field, $current_start, $current_end, $prev_start, $prev_end) {
        global $wpdb;
        
        $current_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE $date_field BETWEEN %s AND %s",
            $current_start, $current_end
        );
        $current_count = (int) $wpdb->get_var($current_query);
        
        $prev_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE $date_field BETWEEN %s AND %s",
            $prev_start, $prev_end
        );
        $prev_count = (int) $wpdb->get_var($prev_query);
        
        if ($prev_count == 0) {
            return array('percentage' => 0, 'direction' => 'neutral');
        }
        
        $percentage = round((($current_count - $prev_count) / $prev_count) * 100, 1);
        $direction = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral');
        
        return array('percentage' => abs($percentage), 'direction' => $direction);
    }

    /**
     * Calculate revenue trend between two periods.
     */
    private function calculate_revenue_trend($current_start, $current_end, $prev_start, $prev_end) {
        global $wpdb;
        $bills_table = $wpdb->prefix . 'hms_bills';
        
        $current_query = $wpdb->prepare(
            "SELECT SUM(total_amount) FROM $bills_table WHERE created_at BETWEEN %s AND %s AND status = 'paid'",
            $current_start, $current_end
        );
        $current_revenue = (float) $wpdb->get_var($current_query) ?: 0;
        
        $prev_query = $wpdb->prepare(
            "SELECT SUM(total_amount) FROM $bills_table WHERE created_at BETWEEN %s AND %s AND status = 'paid'",
            $prev_start, $prev_end
        );
        $prev_revenue = (float) $wpdb->get_var($prev_query) ?: 0;
        
        if ($prev_revenue == 0) {
            return array('percentage' => 0, 'direction' => 'neutral');
        }
        
        $percentage = round((($current_revenue - $prev_revenue) / $prev_revenue) * 100, 1);
        $direction = $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral');
        
        return array('percentage' => abs($percentage), 'direction' => $direction);
    }

    /**
     * AJAX handler for dashboard KPIs.
     */
    public function ajax_get_dashboard_kpis() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view dashboard data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $date_validation = $this->validate_date_range($start_date, $end_date);
            if ($date_validation !== true) {
                wp_send_json_error($date_validation['message']);
                return;
            }

            $kpis = $this->get_dashboard_kpis($start_date, $end_date);
            wp_send_json_success($kpis);
            
        } catch (Exception $e) {
            $this->log_error('Exception in dashboard KPIs', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for patient trend chart data.
     */
    public function ajax_get_patient_trend_data() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view chart data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $data = $this->get_patient_census_data($start_date, $end_date);
            
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            // Format data for Chart.js
            $chart_data = array(
                'labels' => array(),
                'values' => array()
            );

            foreach ($data as $row) {
                $chart_data['labels'][] = date('M j', strtotime($row->date));
                $chart_data['values'][] = (int) $row->new_patients;
            }

            wp_send_json_success($chart_data);
            
        } catch (Exception $e) {
            $this->log_error('Exception in patient trend data', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for revenue by department chart data.
     */
    public function ajax_get_revenue_department_data() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view chart data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $data = $this->get_revenue_data($start_date, $end_date, 'department');
            
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            // Format data for Chart.js
            $chart_data = array(
                'labels' => array(),
                'values' => array()
            );

            foreach ($data as $row) {
                $chart_data['labels'][] = ucwords(str_replace('_', ' ', $row->department));
                $chart_data['values'][] = (float) $row->total_revenue;
            }

            wp_send_json_success($chart_data);
            
        } catch (Exception $e) {
            $this->log_error('Exception in revenue department data', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * Get appointment status distribution data.
     */
    public function get_appointment_status_data($start_date, $end_date) {
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'hms_appointments';

        $query = $wpdb->prepare(
            "SELECT status, COUNT(*) as count
             FROM $appointments_table
             WHERE appointment_date BETWEEN %s AND %s
             GROUP BY status
             ORDER BY count DESC",
            $start_date, $end_date
        );

        return $this->execute_query($query, 'appointment status', array(
            'start_date' => $start_date,
            'end_date' => $end_date
        ));
    }

    /**
     * AJAX handler for appointment status chart data.
     */
    public function ajax_get_appointment_status_data() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view chart data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $data = $this->get_appointment_status_data($start_date, $end_date);
            
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            // Format data for Chart.js
            $chart_data = array(
                'labels' => array(),
                'values' => array()
            );

            foreach ($data as $row) {
                $chart_data['labels'][] = ucwords(str_replace('_', ' ', $row->status));
                $chart_data['values'][] = (int) $row->count;
            }

            wp_send_json_success($chart_data);
            
        } catch (Exception $e) {
            $this->log_error('Exception in appointment status data', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for top diagnoses chart data.
     */
    public function ajax_get_top_diagnoses_data() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view chart data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $data = $this->get_disease_statistics_data($start_date, $end_date);
            
            if (is_array($data) && isset($data['success']) && $data['success'] === false) {
                wp_send_json_error($data['message']);
                return;
            }

            // Format data for Chart.js
            $chart_data = array(
                'labels' => array(),
                'values' => array()
            );

            foreach ($data as $row) {
                $chart_data['labels'][] = $row->diagnosis_notes;
                $chart_data['values'][] = (int) $row->count;
            }

            wp_send_json_success($chart_data);
            
        } catch (Exception $e) {
            $this->log_error('Exception in top diagnoses data', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for today's summary.
     */
    public function ajax_get_todays_summary() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view summary data.', 'hospital-management-system'));
                return;
            }

            $today = gmdate('Y-m-d');
            $summary_html = $this->generate_todays_summary_html($today);

            wp_send_json_success(array('html' => $summary_html));
            
        } catch (Exception $e) {
            $this->log_error('Exception in today\'s summary', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * Generate today's summary HTML.
     */
    private function generate_todays_summary_html($date) {
        global $wpdb;
        
        $patients_table = $wpdb->prefix . 'hms_patients';
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        $bills_table = $wpdb->prefix . 'hms_bills';
        
        // Get today's stats
        $new_patients = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $patients_table WHERE DATE(created_at) = %s", $date
        ));
        
        $appointments = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $appointments_table WHERE DATE(appointment_date) = %s", $date
        ));
        
        $revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_amount) FROM $bills_table WHERE DATE(created_at) = %s AND status = 'paid'", $date
        ));
        
        ob_start();
        ?>
        <div class="hms-summary-stats">
            <div class="hms-summary-item">
                <strong><?php echo esc_html($new_patients ?: 0); ?></strong>
                <span><?php esc_html_e('New Patients', 'hospital-management-system'); ?></span>
            </div>
            <div class="hms-summary-item">
                <strong><?php echo esc_html($appointments ?: 0); ?></strong>
                <span><?php esc_html_e('Appointments', 'hospital-management-system'); ?></span>
            </div>
            <div class="hms-summary-item">
                <strong><?php echo esc_html(number_format($revenue ?: 0, 2)); ?></strong>
                <span><?php esc_html_e('Revenue', 'hospital-management-system'); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler for department performance.
     */
    public function ajax_get_department_performance() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view performance data.', 'hospital-management-system'));
                return;
            }

            $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : gmdate('Y-m-01');
            $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : gmdate('Y-m-t');

            $performance_html = $this->generate_department_performance_html($start_date, $end_date);

            wp_send_json_success(array('html' => $performance_html));
            
        } catch (Exception $e) {
            $this->log_error('Exception in department performance', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * Generate department performance HTML.
     */
    private function generate_department_performance_html($start_date, $end_date) {
        $data = $this->get_revenue_data($start_date, $end_date, 'department');
        
        if (is_array($data) && isset($data['success']) && $data['success'] === false) {
            return '<p>' . esc_html__('Unable to load department performance data.', 'hospital-management-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="hms-department-performance">
            <?php if (!empty($data)): ?>
                <?php foreach (array_slice($data, 0, 5) as $dept): ?>
                    <div class="hms-dept-item">
                        <strong><?php echo esc_html(ucwords(str_replace('_', ' ', $dept->department))); ?></strong>
                        <span><?php echo esc_html(number_format($dept->total_revenue, 2)); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php esc_html_e('No department data available.', 'hospital-management-system'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler for recent activities.
     */
    public function ajax_get_recent_activities() {
        try {
            check_ajax_referer('hms_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_reports')) {
                wp_send_json_error(esc_html__('You do not have permission to view activity data.', 'hospital-management-system'));
                return;
            }

            $activities_html = $this->generate_recent_activities_html();

            wp_send_json_success(array('html' => $activities_html));
            
        } catch (Exception $e) {
            $this->log_error('Exception in recent activities', array(
                'error' => $e->getMessage()
            ));
            wp_send_json_error(esc_html__('An unexpected error occurred.', 'hospital-management-system'));
        }
    }

    /**
     * Generate recent activities HTML.
     */
    private function generate_recent_activities_html() {
        global $wpdb;
        
        $patients_table = $wpdb->prefix . 'hms_patients';
        $appointments_table = $wpdb->prefix . 'hms_appointments';
        
        // Get recent patients (last 5)
        $recent_patients = $wpdb->get_results(
            "SELECT first_name, last_name, created_at FROM $patients_table ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get recent appointments (last 5)
        $recent_appointments = $wpdb->get_results(
            "SELECT appointment_date, appointment_time, status FROM $appointments_table ORDER BY created_at DESC LIMIT 5"
        );
        
        ob_start();
        ?>
        <div class="hms-recent-activities">
            <h5><?php esc_html_e('Recent Patients', 'hospital-management-system'); ?></h5>
            <?php if (!empty($recent_patients)): ?>
                <ul>
                    <?php foreach ($recent_patients as $patient): ?>
                        <li>
                            <?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?>
                            <small><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($patient->created_at))); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No recent patients.', 'hospital-management-system'); ?></p>
            <?php endif; ?>
            
            <h5><?php esc_html_e('Recent Appointments', 'hospital-management-system'); ?></h5>
            <?php if (!empty($recent_appointments)): ?>
                <ul>
                    <?php foreach ($recent_appointments as $appointment): ?>
                        <li>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appointment->appointment_date))); ?>
                            <small><?php echo esc_html(ucwords(str_replace('_', ' ', $appointment->status))); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?php esc_html_e('No recent appointments.', 'hospital-management-system'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}