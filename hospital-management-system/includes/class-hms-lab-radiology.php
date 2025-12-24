<?php
/**
 * Lab and Radiology Management Class
 *
 * Handles the creation, management, and result entry for lab and radiology tests.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Lab_Radiology {

    /**
     * Get available lab tests from the database.
     *
     * @return array Array of available lab tests.
     */
    public function get_available_tests() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_lab_tests';
        
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY category, test_name");
        return $results ? $results : array();
    }

    /**
     * Get all lab test orders, optionally filtered by status.
     *
     * @param string $status The status to filter by (e.g., 'pending', 'in-progress', 'completed').
     * @param string $search Search term for patient name or test name.
     * @return array Array of lab test order objects.
     */
    public function get_lab_test_orders($status = '', $search = '') {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $patients_table = $wpdb->prefix . 'hms_patients';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $sql = "SELECT 
                    o.*, 
                    t.test_name, 
                    t.category, 
                    t.normal_range,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    s.first_name as doctor_first_name,
                    s.last_name as doctor_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $patients_table p ON o.patient_id = p.id
                LEFT JOIN $staff_table s ON o.doctor_id = s.id";
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($status)) {
            $where_conditions[] = "o.status = %s";
            $where_values[] = $status;
        }
        
        if (!empty($search)) {
            $where_conditions[] = "(CONCAT(p.first_name, ' ', p.last_name) LIKE %s OR t.test_name LIKE %s)";
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY o.order_date DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql);
        return $results ? $results : array();
    }

    /**
     * Get a single lab test order by its ID.
     *
     * @param int $order_id The ID of the lab test order.
     * @return object|null The lab test order object or null if not found.
     */
    public function get_lab_test_order($order_id) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $patients_table = $wpdb->prefix . 'hms_patients';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $sql = "SELECT 
                    o.*, 
                    t.test_name, 
                    t.category, 
                    t.normal_range,
                    t.unit,
                    t.cost,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    s.first_name as doctor_first_name,
                    s.last_name as doctor_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $patients_table p ON o.patient_id = p.id
                LEFT JOIN $staff_table s ON o.doctor_id = s.id
                WHERE o.id = %d";
        
        $result = $wpdb->get_row($wpdb->prepare($sql, $order_id));
        return $result;
    }

    /**
     * Create a new lab test order.
     *
     * @param array $data The data for the new lab test order.
     * @return int|false The ID of the new lab test order or false on failure.
     */
    public function create_lab_test_order($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['test_id'])) {
            return false;
        }
        
        $table_name = $wpdb->prefix . 'hms_lab_test_orders';
        
        $insert_data = array(
            'patient_id' => intval($data['patient_id']),
            'doctor_id' => intval($data['doctor_id']),
            'test_id' => intval($data['test_id']),
            'status' => sanitize_text_field($data['status'] ?? 'pending'),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'order_date' => current_time('mysql'),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        if ($result !== false) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update an existing lab test order, primarily for entering results.
     *
     * @param int   $order_id The ID of the lab test order to update.
     * @param array $data     The data to update.
     * @return bool True on success, false on failure.
     */
    public function update_lab_test_order($order_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_lab_test_orders';
        
        $update_data = array(
            'updated_at' => current_time('mysql')
        );
        
        // Update status if provided
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }
        
        // Update results if provided
        if (isset($data['results'])) {
            $update_data['results'] = wp_kses_post($data['results']);
        }
        
        // Update notes if provided
        if (isset($data['notes'])) {
            $update_data['notes'] = sanitize_textarea_field($data['notes']);
        }
        
        $where = array('id' => intval($order_id));
        
        $result = $wpdb->update($table_name, $update_data, $where);
        
        return $result !== false;
    }

    /**
     * Delete a lab test order.
     *
     * @param int $order_id The ID of the lab test order to delete.
     * @return bool True on success, false on failure.
     */
    public function delete_lab_test_order($order_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_lab_test_orders';
        
        $result = $wpdb->delete($table_name, array('id' => intval($order_id)), array('%d'));
        
        return $result !== false;
    }

    /**
     * Get patients for dropdown selection.
     *
     * @return array Array of patient objects.
     */
    public function get_patients() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_patients';
        
        $results = $wpdb->get_results("SELECT id, first_name, last_name, phone FROM $table_name ORDER BY first_name, last_name");
        return $results ? $results : array();
    }

    /**
     * Get doctors/staff for dropdown selection.
     *
     * @return array Array of doctor objects.
     */
    public function get_doctors() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_staff';
        
        $results = $wpdb->get_results("SELECT id, first_name, last_name, role FROM $table_name WHERE role IN ('doctor', 'physician') ORDER BY first_name, last_name");
        return $results ? $results : array();
    }

    /**
     * Generate lab report for a test order.
     *
     * @param int $order_id The ID of the lab test order.
     * @param string $format The format of the report ('html', 'pdf', 'print').
     * @return string|false HTML content of the report or false on failure.
     */
    public function generate_lab_report($order_id, $format = 'html') {
        $order = $this->get_lab_test_order($order_id);
        
        if (!$order || $order->status !== 'completed') {
            return false;
        }
        
        // Get hospital settings for branding
        $hospital_name = get_option('hms_hospital_name', 'Hospital Management System');
        $hospital_address = get_option('hms_hospital_address', '');
        $hospital_phone = get_option('hms_hospital_phone', '');
        $hospital_email = get_option('hms_hospital_email', '');
        $hospital_logo = get_option('hms_hospital_logo', '');
        
        // Get additional patient information
        $patient_info = $this->get_patient_details($order->patient_id);
        
        ob_start();
        ?>
        <div class="lab-report <?php echo esc_attr('format-' . $format); ?>">
            <div class="report-header">
                <div class="hospital-branding">
                    <?php if ($hospital_logo): ?>
                        <img src="<?php echo esc_url($hospital_logo); ?>" alt="<?php echo esc_attr($hospital_name); ?>" class="hospital-logo">
                    <?php endif; ?>
                    <div class="hospital-info">
                        <h2><?php echo esc_html($hospital_name); ?></h2>
                        <?php if ($hospital_address): ?>
                            <p class="hospital-address"><?php echo esc_html($hospital_address); ?></p>
                        <?php endif; ?>
                        <div class="hospital-contact">
                            <?php if ($hospital_phone): ?>
                                <span>Phone: <?php echo esc_html($hospital_phone); ?></span>
                            <?php endif; ?>
                            <?php if ($hospital_email): ?>
                                <span>Email: <?php echo esc_html($hospital_email); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <hr class="header-divider">
                <div class="report-title">
                    <h3>Laboratory Report</h3>
                    <div class="report-meta">
                        <span class="report-id">Report ID: <?php echo esc_html(sprintf('LAB-%06d', $order->id)); ?></span>
                        <span class="report-date">Generated: <?php echo esc_html(date('F j, Y g:i A')); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="patient-section">
                <h4>Patient Information</h4>
                <div class="patient-details">
                    <div class="patient-row">
                        <div class="patient-col">
                            <strong>Name:</strong> <?php echo esc_html($order->patient_first_name . ' ' . $order->patient_last_name); ?>
                        </div>
                        <div class="patient-col">
                            <strong>Patient ID:</strong> <?php echo esc_html(sprintf('P-%06d', $order->patient_id)); ?>
                        </div>
                    </div>
                    <?php if ($patient_info): ?>
                        <div class="patient-row">
                            <div class="patient-col">
                                <strong>Age:</strong> <?php echo esc_html($this->calculate_age($patient_info->date_of_birth ?? '')); ?>
                            </div>
                            <div class="patient-col">
                                <strong>Gender:</strong> <?php echo esc_html(ucfirst($patient_info->gender ?? 'N/A')); ?>
                            </div>
                        </div>
                        <?php if ($patient_info->phone): ?>
                            <div class="patient-row">
                                <div class="patient-col">
                                    <strong>Contact:</strong> <?php echo esc_html($patient_info->phone); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="test-section">
                <h4>Test Information</h4>
                <div class="test-details">
                    <div class="test-row">
                        <div class="test-col">
                            <strong>Test Name:</strong> <?php echo esc_html($order->test_name); ?>
                        </div>
                        <div class="test-col">
                            <strong>Category:</strong> <?php echo esc_html($order->category); ?>
                        </div>
                    </div>
                    <div class="test-row">
                        <div class="test-col">
                            <strong>Ordered By:</strong> Dr. <?php echo esc_html($order->doctor_first_name . ' ' . $order->doctor_last_name); ?>
                        </div>
                        <div class="test-col">
                            <strong>Order Date:</strong> <?php echo esc_html(date('F j, Y g:i A', strtotime($order->order_date))); ?>
                        </div>
                    </div>
                    <div class="test-row">
                        <div class="test-col">
                            <strong>Completed Date:</strong> <?php echo esc_html(date('F j, Y g:i A', strtotime($order->updated_at))); ?>
                        </div>
                        <div class="test-col">
                            <strong>Status:</strong> <span class="status-completed"><?php echo esc_html(ucwords($order->status)); ?></span>
                        </div>
                    </div>
                    <?php if ($order->normal_range): ?>
                        <div class="test-row">
                            <div class="test-col-full">
                                <strong>Normal Range:</strong> <?php echo esc_html($order->normal_range); ?>
                                <?php if ($order->unit): ?>
                                    (<?php echo esc_html($order->unit); ?>)
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="results-section">
                <h4>Test Results</h4>
                <div class="results-content">
                    <?php echo wp_kses_post($order->results); ?>
                </div>
            </div>
            
            <?php if ($order->notes): ?>
                <div class="notes-section">
                    <h4>Clinical Notes & Recommendations</h4>
                    <div class="notes-content">
                        <?php echo wp_kses_post(wpautop($order->notes)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="report-footer">
                <div class="footer-content">
                    <div class="footer-left">
                        <p><strong>Laboratory Department</strong></p>
                        <p><?php echo esc_html($hospital_name); ?></p>
                    </div>
                    <div class="footer-right">
                        <p class="disclaimer">
                            <em>This report is computer generated and does not require a signature. 
                            For any queries, please contact the laboratory department.</em>
                        </p>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p class="generation-info">
                        Report generated on <?php echo esc_html(date('F j, Y \a\t g:i A')); ?> | 
                        Page 1 of 1 | 
                        Report ID: <?php echo esc_html(sprintf('LAB-%06d', $order->id)); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <?php if ($format === 'print'): ?>
        <style type="text/css" media="print">
            .lab-report { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
            .hospital-branding { display: flex; align-items: center; margin-bottom: 20px; }
            .hospital-logo { max-height: 60px; margin-right: 20px; }
            .hospital-info h2 { margin: 0; font-size: 18px; }
            .hospital-contact span { margin-right: 15px; }
            .header-divider { border: 1px solid #333; margin: 15px 0; }
            .report-title { text-align: center; margin-bottom: 20px; }
            .report-meta { font-size: 10px; margin-top: 5px; }
            .patient-section, .test-section, .results-section, .notes-section { margin-bottom: 20px; }
            .patient-row, .test-row { display: flex; margin-bottom: 8px; }
            .patient-col, .test-col { flex: 1; }
            .test-col-full { width: 100%; }
            .results-content, .notes-content { border: 1px solid #ddd; padding: 10px; }
            .status-completed { color: #28a745; font-weight: bold; }
            .footer-content { display: flex; justify-content: space-between; margin-top: 30px; }
            .footer-bottom { text-align: center; font-size: 10px; margin-top: 15px; }
            @page { margin: 1in; }
        </style>
        <?php endif; ?>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get detailed patient information.
     *
     * @param int $patient_id The patient ID.
     * @return object|null Patient details or null if not found.
     */
    private function get_patient_details($patient_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_patients';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $patient_id
        ));
        
        return $result;
    }
    
    /**
     * Calculate age from date of birth.
     *
     * @param string $date_of_birth The date of birth.
     * @return string The calculated age or 'N/A' if invalid date.
     */
    private function calculate_age($date_of_birth) {
        if (empty($date_of_birth) || $date_of_birth === '0000-00-00') {
            return 'N/A';
        }
        
        $birth_date = new DateTime($date_of_birth);
        $current_date = new DateTime();
        $age = $current_date->diff($birth_date);
        
        return $age->y . ' years';
    }

    /**
     * Get lab test statistics for dashboard.
     *
     * @return array Array of statistics.
     */
    public function get_lab_statistics() {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        
        $stats = array();
        
        // Total orders
        $stats['total_orders'] = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table");
        
        // Orders by status
        $stats['pending'] = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'pending'");
        $stats['in_progress'] = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'in-progress'");
        $stats['completed'] = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table WHERE status = 'completed'");
        
        // Today's orders
        $today = date('Y-m-d');
        $stats['today_orders'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $orders_table WHERE DATE(order_date) = %s",
            $today
        ));
        
        // This week's completed tests
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $stats['week_completed'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $orders_table WHERE status = 'completed' AND DATE(order_date) >= %s",
            $week_start
        ));
        
        return $stats;
    }

    /**
     * Get recent lab test activities.
     *
     * @param int $limit Number of activities to retrieve.
     * @return array Array of recent activities.
     */
    public function get_recent_activities($limit = 10) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $patients_table = $wpdb->prefix . 'hms_patients';
        
        $sql = "SELECT 
                    o.id,
                    o.status,
                    o.order_date,
                    o.updated_at,
                    t.test_name,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $patients_table p ON o.patient_id = p.id
                ORDER BY o.updated_at DESC
                LIMIT %d";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $limit));
        return $results ? $results : array();
    }
    
    /**
     * Update test status with workflow validation.
     *
     * @param int $order_id The lab test order ID.
     * @param string $new_status The new status to set.
     * @param array $additional_data Additional data to update.
     * @return bool True on success, false on failure.
     */
    public function update_test_status($order_id, $new_status, $additional_data = array()) {
        global $wpdb;
        
        // Validate status transition
        $current_order = $this->get_lab_test_order($order_id);
        if (!$current_order) {
            return false;
        }
        
        $valid_statuses = array('pending', 'in-progress', 'completed', 'cancelled');
        if (!in_array($new_status, $valid_statuses)) {
            return false;
        }
        
        // Validate workflow transitions
        $valid_transitions = array(
            'pending' => array('in-progress', 'cancelled'),
            'in-progress' => array('completed', 'cancelled'),
            'completed' => array(), // Cannot change from completed
            'cancelled' => array('pending') // Can restart cancelled tests
        );
        
        if (!empty($valid_transitions[$current_order->status]) && 
            !in_array($new_status, $valid_transitions[$current_order->status])) {
            return false;
        }
        
        // Prepare update data
        $update_data = array_merge($additional_data, array(
            'status' => $new_status,
            'updated_at' => current_time('mysql')
        ));
        
        // Add completion timestamp if marking as completed
        if ($new_status === 'completed') {
            $update_data['completed_at'] = current_time('mysql');
        }
        
        $table_name = $wpdb->prefix . 'hms_lab_test_orders';
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => intval($order_id)),
            null,
            array('%d')
        );
        
        // Log the status change
        if ($result !== false) {
            $this->log_status_change($order_id, $current_order->status, $new_status);
        }
        
        return $result !== false;
    }
    
    /**
     * Log status changes for audit trail.
     *
     * @param int $order_id The lab test order ID.
     * @param string $old_status The previous status.
     * @param string $new_status The new status.
     */
    private function log_status_change($order_id, $old_status, $new_status) {
        global $wpdb;
        
        $audit_table = $wpdb->prefix . 'hms_audit_log';
        
        // Check if audit table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$audit_table'");
        if (!$table_exists) {
            return;
        }
        
        $wpdb->insert(
            $audit_table,
            array(
                'table_name' => 'hms_lab_test_orders',
                'record_id' => $order_id,
                'action' => 'status_change',
                'old_values' => json_encode(array('status' => $old_status)),
                'new_values' => json_encode(array('status' => $new_status)),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Get lab test orders by date range.
     *
     * @param string $start_date Start date (Y-m-d format).
     * @param string $end_date End date (Y-m-d format).
     * @param string $status Optional status filter.
     * @return array Array of lab test orders.
     */
    public function get_orders_by_date_range($start_date, $end_date, $status = '') {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $patients_table = $wpdb->prefix . 'hms_patients';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $sql = "SELECT 
                    o.*, 
                    t.test_name, 
                    t.category,
                    t.cost,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    s.first_name as doctor_first_name,
                    s.last_name as doctor_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $patients_table p ON o.patient_id = p.id
                LEFT JOIN $staff_table s ON o.doctor_id = s.id
                WHERE DATE(o.order_date) BETWEEN %s AND %s";
        
        $params = array($start_date, $end_date);
        
        if (!empty($status)) {
            $sql .= " AND o.status = %s";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.order_date DESC";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));
        return $results ? $results : array();
    }
    
    /**
     * Generate comprehensive lab report with multiple test results.
     *
     * @param int $patient_id The patient ID.
     * @param string $start_date Start date for report period.
     * @param string $end_date End date for report period.
     * @return string|false HTML content of the comprehensive report.
     */
    public function generate_comprehensive_report($patient_id, $start_date = '', $end_date = '') {
        global $wpdb;
        
        // Get patient information
        $patient = $this->get_patient_details($patient_id);
        if (!$patient) {
            return false;
        }
        
        // Build date filter
        $date_filter = '';
        $params = array($patient_id);
        
        if (!empty($start_date) && !empty($end_date)) {
            $date_filter = " AND DATE(o.order_date) BETWEEN %s AND %s";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        // Get all completed tests for the patient
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $sql = "SELECT 
                    o.*, 
                    t.test_name, 
                    t.category,
                    t.normal_range,
                    t.unit,
                    s.first_name as doctor_first_name,
                    s.last_name as doctor_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $staff_table s ON o.doctor_id = s.id
                WHERE o.patient_id = %d AND o.status = 'completed'
                $date_filter
                ORDER BY o.order_date DESC";
        
        $test_results = $wpdb->get_results($wpdb->prepare($sql, $params));
        
        if (empty($test_results)) {
            return false;
        }
        
        // Get hospital settings
        $hospital_name = get_option('hms_hospital_name', 'Hospital Management System');
        $hospital_address = get_option('hms_hospital_address', '');
        $hospital_phone = get_option('hms_hospital_phone', '');
        
        ob_start();
        ?>
        <div class="comprehensive-lab-report">
            <div class="report-header">
                <h2><?php echo esc_html($hospital_name); ?></h2>
                <?php if ($hospital_address): ?>
                    <p><?php echo esc_html($hospital_address); ?></p>
                <?php endif; ?>
                <?php if ($hospital_phone): ?>
                    <p>Phone: <?php echo esc_html($hospital_phone); ?></p>
                <?php endif; ?>
                <hr>
                <h3>Comprehensive Laboratory Report</h3>
            </div>
            
            <div class="patient-summary">
                <h4>Patient Information</h4>
                <p><strong>Name:</strong> <?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?></p>
                <p><strong>Patient ID:</strong> <?php echo esc_html(sprintf('P-%06d', $patient->id)); ?></p>
                <p><strong>Age:</strong> <?php echo esc_html($this->calculate_age($patient->date_of_birth ?? '')); ?></p>
                <p><strong>Gender:</strong> <?php echo esc_html(ucfirst($patient->gender ?? 'N/A')); ?></p>
                <?php if (!empty($start_date) && !empty($end_date)): ?>
                    <p><strong>Report Period:</strong> <?php echo esc_html(date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date))); ?></p>
                <?php endif; ?>
                <p><strong>Total Tests:</strong> <?php echo count($test_results); ?></p>
            </div>
            
            <div class="test-results-summary">
                <h4>Test Results Summary</h4>
                <?php
                $categories = array();
                foreach ($test_results as $result) {
                    $categories[$result->category][] = $result;
                }
                
                foreach ($categories as $category => $tests):
                ?>
                    <div class="category-section">
                        <h5><?php echo esc_html($category ?: 'General Tests'); ?></h5>
                        <table class="test-results-table">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Date</th>
                                    <th>Ordered By</th>
                                    <th>Results</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tests as $test): ?>
                                    <tr>
                                        <td><?php echo esc_html($test->test_name); ?></td>
                                        <td><?php echo esc_html(date('M j, Y', strtotime($test->order_date))); ?></td>
                                        <td>Dr. <?php echo esc_html($test->doctor_first_name . ' ' . $test->doctor_last_name); ?></td>
                                        <td>
                                            <div class="test-result-summary">
                                                <?php echo wp_kses_post(wp_trim_words($test->results, 20, '...')); ?>
                                                <?php if ($test->normal_range): ?>
                                                    <br><small><em>Normal: <?php echo esc_html($test->normal_range); ?></em></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="report-footer">
                <p><em>Comprehensive report generated on <?php echo esc_html(date('F j, Y g:i A')); ?></em></p>
                <p><small>This report contains <?php echo count($test_results); ?> completed laboratory test(s) for the specified period.</small></p>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get critical/abnormal test results that need attention.
     *
     * @param int $days Number of days to look back (default 7).
     * @return array Array of critical test results.
     */
    public function get_critical_results($days = 7) {
        global $wpdb;
        
        $orders_table = $wpdb->prefix . 'hms_lab_test_orders';
        $tests_table = $wpdb->prefix . 'hms_lab_tests';
        $patients_table = $wpdb->prefix . 'hms_patients';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $sql = "SELECT 
                    o.*, 
                    t.test_name, 
                    t.category,
                    p.first_name as patient_first_name,
                    p.last_name as patient_last_name,
                    s.first_name as doctor_first_name,
                    s.last_name as doctor_last_name
                FROM $orders_table o
                LEFT JOIN $tests_table t ON o.test_id = t.id
                LEFT JOIN $patients_table p ON o.patient_id = p.id
                LEFT JOIN $staff_table s ON o.doctor_id = s.id
                WHERE o.status = 'completed' 
                AND o.updated_at >= %s
                AND (o.results LIKE %s OR o.results LIKE %s OR o.results LIKE %s OR o.results LIKE %s)
                ORDER BY o.updated_at DESC";
        
        $critical_keywords = array(
            '%critical%',
            '%abnormal%',
            '%high%',
            '%urgent%'
        );
        
        $results = $wpdb->get_results($wpdb->prepare($sql, array_merge(array($date_threshold), $critical_keywords)));
        return $results ? $results : array();
    }
}
