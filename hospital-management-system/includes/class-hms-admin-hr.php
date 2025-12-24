<?php
/**
 * HMS Admin HR Class
 *
 * Handles Administration & HR functionalities, including attendance tracking,
 * duty rosters, and payroll integration.
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

class HMS_Admin_HR {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for attendance
        add_action('wp_ajax_hms_clock_in', array($this, 'ajax_clock_in'));
        add_action('wp_ajax_hms_clock_out', array($this, 'ajax_clock_out'));
        add_action('wp_ajax_hms_get_attendance_records', array($this, 'ajax_get_attendance_records'));
        add_action('wp_ajax_hms_delete_attendance_record', array($this, 'ajax_delete_attendance_record'));

        // AJAX hooks for duty rosters
        add_action('wp_ajax_hms_create_roster', array($this, 'ajax_create_roster'));
        add_action('wp_ajax_hms_update_roster', array($this, 'ajax_update_roster'));
        add_action('wp_ajax_hms_get_roster', array($this, 'ajax_get_roster'));
        add_action('wp_ajax_hms_get_rosters_list', array($this, 'ajax_get_rosters_list'));
        add_action('wp_ajax_hms_delete_roster', array($this, 'ajax_delete_roster'));
    }

    // --- Attendance Tracking ---

    /**
     * Log staff clock in.
     *
     * @since    1.0.0
     * @param    int       $staff_id           The staff member's ID.
     * @param    string    $clock_in_time      Optional. The clock-in time. Defaults to current time.
     * @param    string    $notes              Optional. Notes for the attendance record.
     * @return   int|false                     The new attendance record's database ID, or false on failure.
     */
    public function clock_in(int $staff_id, string $clock_in_time = '', string $notes = '') {
        $table_name = 'hms_attendance';

        // Check if already clocked in today without a clock out
        $existing_record = $this->get_today_attendance($staff_id);
        if ($existing_record && empty($existing_record->clock_out_time)) {
            return new WP_Error('already_clocked_in', __('Staff member is already clocked in.', 'hospital-management-system'));
        }

        $attendance_data = array(
            'staff_id'       => $staff_id,
            'clock_in_time'  => !empty($clock_in_time) ? sanitize_text_field($clock_in_time) : current_time('mysql'),
            'clock_out_time' => null, // Not clocked out yet
            'notes'          => !empty($notes) ? sanitize_textarea_field($notes) : null,
            'logged_by'      => get_current_user_id(),
        );

        $format = array('%d', '%s', '%s', '%d');
        
        $attendance_id = HMS_Helper::hms_create($table_name, $attendance_data, $format);

        if ($attendance_id) {
            HMS_Helper::log_action('Staff Clocked In', 'Attendance', $attendance_id, json_encode($attendance_data));
            return $attendance_id;
        }
        return false;
    }

    /**
     * Log staff clock out.
     *
     * @since    1.0.0
     * @param    int       $staff_id           The staff member's ID.
     * @param    string    $clock_out_time     Optional. The clock-out time. Defaults to current time.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function clock_out(int $staff_id, string $clock_out_time = '') {
        $table_name = 'hms_attendance';
        $today_record = $this->get_today_attendance($staff_id);

        if (!$today_record || !empty($today_record->clock_out_time)) {
            return new WP_Error('not_clocked_in', __('Staff member is not clocked in or has already clocked out.', 'hospital-management-system'));
        }

        $update_data = array(
            'clock_out_time' => !empty($clock_out_time) ? sanitize_text_field($clock_out_time) : current_time('mysql'),
        );

        $where = array('id' => $today_record->id);
        $where_format = array('%d');

        $updated = HMS_Helper::hms_update($table_name, $update_data, $where, array('%s'), $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Staff Clocked Out', 'Attendance', $today_record->id, json_encode($update_data));
            return true;
        }
        return false;
    }

    /**
     * Get a single attendance record by ID.
     *
     * @since    1.0.0
     * @param    int       $attendance_id     The attendance record's database ID.
     * @return   object|null                   The attendance data object, or null if not found.
     */
    public function get_attendance_record(int $attendance_id): ?object {
        return HMS_Helper::hms_get_row('hms_attendance', array('id' => $attendance_id), array('%d'));
    }

    /**
     * Get today's attendance record for a staff member.
     *
     * @since    1.0.0
     * @param    int       $staff_id           The staff member's ID.
     * @return   object|null                   The attendance data object for today, or null if not found.
     */
    public function get_today_attendance(int $staff_id): ?object {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_attendance';
        $today_start = gmdate('Y-m-d 00:00:00');
        $today_end = gmdate('Y-m-d 23:59:59');

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE staff_id = %d AND clock_in_time BETWEEN %s AND %s ORDER BY clock_in_time DESC LIMIT 1",
            $staff_id,
            $today_start,
            $today_end
        );
        return $wpdb->get_row($query);
    }

    /**
     * Get all attendance records, with optional filtering.
     *
     * @since    1.0.0
     * @param    string    $date_from          Optional. Filter by start date (Y-m-d).
     * @param    string    $date_to            Optional. Filter by end date (Y-m-d).
     * @param    int       $staff_id           Optional. Filter by staff ID.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of records to return per page.
     * @return   array                          An array of attendance data objects.
     */
    public function get_all_attendance_records(string $date_from = '', string $date_to = '', int $staff_id = 0, int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_attendance';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($date_from)) {
            $where_clauses[] = "a.clock_in_time >= %s";
            $where_values[] = $date_from . ' 00:00:00';
        }
        if (!empty($date_to)) {
            $where_clauses[] = "a.clock_in_time <= %s";
            $where_values[] = $date_to . ' 23:59:59';
        }
        if ($staff_id > 0) {
            $where_clauses[] = "a.staff_id = %d";
            $where_values[] = $staff_id;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT a.*, s.first_name, s.last_name, s.staff_id FROM {$table_name} a
                  JOIN {$staff_table} s ON a.staff_id = s.id
                  {$where_clause}
                  ORDER BY a.clock_in_time DESC
                  LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($per_page, $offset));
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Delete an attendance record.
     *
     * @since    1.0.0
     * @param    int       $attendance_id     The attendance record's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_attendance_record(int $attendance_id) {
        $existing_record = $this->get_attendance_record($attendance_id);
        if (!$existing_record) {
            return new WP_Error('record_not_found', __('Attendance record not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_attendance', array('id' => $attendance_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Attendance Record Deleted', 'Attendance', $attendance_id, json_encode($existing_record));
            return true;
        }
        return false;
    }


    // --- Duty Rosters ---

    /**
     * Create a new duty roster.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of roster data.
     * @return   int|false                     The new roster's database ID, or false on failure.
     */
    public function create_roster(array $data) {
        $table_name = 'hms_rosters';

        if (empty($data['staff_id']) || empty($data['shift_name']) || empty($data['roster_date']) || empty($data['start_time']) || empty($data['end_time'])) {
            return false;
        }

        $roster_data = array(
            'staff_id'      => intval($data['staff_id']),
            'shift_name'    => sanitize_text_field($data['shift_name']),
            'roster_date'   => sanitize_text_field($data['roster_date']),
            'start_time'    => sanitize_text_field($data['start_time']),
            'end_time'      => sanitize_text_field($data['end_time']),
            'department_id' => !empty($data['department_id']) ? intval($data['department_id']) : null,
            'notes'         => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_by'    => get_current_user_id(),
            'created_at'    => current_time('mysql'),
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s');
        
        $roster_id = HMS_Helper::hms_create($table_name, $roster_data, $format);

        if ($roster_id) {
            HMS_Helper::log_action('Duty Roster Created', 'Roster', $roster_id, json_encode($roster_data));
            return $roster_id;
        }
        return false;
    }

    /**
     * Get a single roster by ID.
     *
     * @since    1.0.0
     * @param    int       $roster_id          The roster's database ID.
     * @return   object|null                   The roster data object, or null if not found.
     */
    public function get_roster(int $roster_id): ?object {
        return HMS_Helper::hms_get_row('hms_rosters', array('id' => $roster_id), array('%d'));
    }

    /**
     * Get all duty rosters, with optional filtering.
     *
     * @since    1.0.0
     * @param    string    $date_from          Optional. Filter by start date (Y-m-d).
     * @param    string    $date_to            Optional. Filter by end date (Y-m-d).
     * @param    int       $staff_id           Optional. Filter by staff ID.
     * @param    int       $department_id      Optional. Filter by department ID.
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of records to return per page.
     * @return   array                          An array of roster data objects.
     */
    public function get_all_rosters(string $date_from = '', string $date_to = '', int $staff_id = 0, int $department_id = 0, int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_rosters';
        $staff_table = $wpdb->prefix . 'hms_staff';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($date_from)) {
            $where_clauses[] = "roster_date >= %s";
            $where_values[] = $date_from;
        }
        if (!empty($date_to)) {
            $where_clauses[] = "roster_date <= %s";
            $where_values[] = $date_to;
        }
        if ($staff_id > 0) {
            $where_clauses[] = "r.staff_id = %d";
            $where_values[] = $staff_id;
        }
        if ($department_id > 0) {
            $where_clauses[] = "r.department_id = %d";
            $where_values[] = $department_id;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT r.*, s.first_name, s.last_name, s.staff_id FROM {$table_name} r
                  JOIN {$staff_table} s ON r.staff_id = s.id
                  {$where_clause}
                  ORDER BY r.roster_date ASC, r.start_time ASC
                  LIMIT %d OFFSET %d";
        
        $values = array_merge($where_values, array($per_page, $offset));
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Update an existing duty roster.
     *
     * @since    1.0.0
     * @param    int       $roster_id          The roster's database ID.
     * @param    array     $data              An associative array of roster data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_roster(int $roster_id, array $data) {
        $table_name = 'hms_rosters';
        $where = array('id' => $roster_id);
        $where_format = array('%d');

        $existing_roster = $this->get_roster($roster_id);
        if (!$existing_roster) {
            return new WP_Error('roster_not_found', __('Roster not found.', 'hospital-management-system'));
        }
        
        $roster_data = array();
        $format = array();

        $fields_to_update = array('staff_id', 'shift_name', 'roster_date', 'start_time', 'end_time', 'department_id', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                if (in_array($field, array('staff_id', 'department_id'))) {
                    $roster_data[$field] = intval($data[$field]);
                } elseif ($field === 'notes') {
                    $roster_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $roster_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = is_numeric($roster_data[$field]) ? '%d' : '%s';
            }
        }
        
        if (empty($roster_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $roster_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Duty Roster Updated', 'Roster', $roster_id, json_encode($roster_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a duty roster.
     *
     * @since    1.0.0
     * @param    int       $roster_id          The roster's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function delete_roster(int $roster_id) {
        $existing_roster = $this->get_roster($roster_id);
        if (!$existing_roster) {
            return new WP_Error('roster_not_found', __('Roster not found.', 'hospital-management-system'));
        }

        $deleted = HMS_Helper::hms_delete('hms_rosters', array('id' => $roster_id), array('%d'));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Duty Roster Deleted', 'Roster', $roster_id, json_encode($existing_roster));
            return true;
        }
        return false;
    }


    // --- Payroll Integration Framework (Abstracted) ---

    /**
     * Generate salary data for a given period.
     * This is an abstracted function. Actual implementation will depend on the payroll system.
     *
     * @since    1.0.0
     * @param    string    $start_date         The start date of the payroll period (Y-m-d).
     * @param    string    $end_date           The end date of the payroll period (Y-m-d).
     * @param    int       $department_id      Optional. Filter by department ID.
     * @return   array|WP_Error                An array of salary data, or WP_Error on failure.
     */
    public function generate_payroll_data(string $start_date, string $end_date, int $department_id = 0) {
        // This function would typically:
        // 1. Fetch all staff members (optionally filtered by department).
        // 2. For each staff member, calculate:
        //    - Total hours worked (from attendance records).
        //    - Basic salary (could be from staff profile or a salary grades table).
        //    - Overtime pay.
        //    - Deductions (e.g., taxes, insurance, advances).
        //    - Net salary.
        // 3. Format the data for export to the payroll system.

        // Placeholder logic:
        $staff_members = $this->get_staff_for_payroll($department_id);
        $payroll_data = array();

        foreach ($staff_members as $staff) {
            $attendance_summary = $this->get_attendance_summary_for_staff($staff->id, $start_date, $end_date);
            
            $salary_record = array(
                'staff_id' => $staff->id,
                'staff_name' => $staff->first_name . ' ' . $staff->last_name,
                'staff_code' => $staff->staff_id,
                'basic_salary' => $staff->basic_salary, // Assuming this field exists
                'days_worked' => $attendance_summary['days_worked'],
                'overtime_hours' => $attendance_summary['overtime_hours'],
                'gross_salary' => $staff->basic_salary, // Placeholder, add overtime etc.
                'deductions' => 0, // Placeholder
                'net_salary' => $staff->basic_salary, // Placeholder
            );
            $payroll_data[] = $salary_record;
        }

        return $payroll_data;
    }

    /**
     * Helper to get staff members for payroll processing.
     *
     * @since    1.0.0
     * @param    int       $department_id      Optional. Filter by department ID.
     * @return   array                          An array of staff data objects.
     */
    private function get_staff_for_payroll(int $department_id = 0): array {
        global $wpdb;
        $staff_table = $wpdb->prefix . 'hms_staff';
        $where_clause = '';
        $where_value = array();

        if ($department_id > 0) {
            $where_clause = "WHERE department_id = %d";
            $where_value = array($department_id);
        }

        $query = "SELECT id, first_name, last_name, staff_id, basic_salary FROM {$staff_table} {$where_clause} ORDER BY last_name, first_name";
        return $wpdb->get_results($wpdb->prepare($query, $where_value));
    }

    /**
     * Helper to get attendance summary for a staff member.
     *
     * @since    1.0.0
     * @param    int       $staff_id           The staff member's ID.
     * @param    string    $start_date         The start date (Y-m-d).
     * @param    string    $end_date           The end date (Y-m-d).
     * @return   array                          An associative array of summary data.
     */
    private function get_attendance_summary_for_staff(int $staff_id, string $start_date, string $end_date): array {
        global $wpdb;
        $attendance_table = $wpdb->prefix . 'hms_attendance';
        
        $query = $wpdb->prepare(
            "SELECT clock_in_time, clock_out_time FROM {$attendance_table}
             WHERE staff_id = %d AND DATE(clock_in_time) BETWEEN %s AND %s
             ORDER BY clock_in_time ASC",
            $staff_id,
            $start_date,
            $end_date
        );
        $records = $wpdb->get_results($query);

        $days_worked = 0;
        $overtime_hours = 0;
        $standard_hours_per_day = 8; // This could be a setting

        foreach ($records as $record) {
            if (!empty($record->clock_in_time) && !empty($record->clock_out_time)) {
                $days_worked++;
                $clock_in = new DateTime($record->clock_in_time);
                $clock_out = new DateTime($record->clock_out_time);
                $interval = $clock_in->diff($clock_out);
                $hours_worked = $interval->h + ($interval->i / 60);
                if ($hours_worked > $standard_hours_per_day) {
                    $overtime_hours += ($hours_worked - $standard_hours_per_day);
                }
            }
        }
        
        return array(
            'days_worked' => $days_worked,
            'overtime_hours' => $overtime_hours,
        );
    }


    // --- AJAX Handlers for Attendance ---

    public function ajax_clock_in() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_attendance')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $staff_id = intval($_POST['staff_id']);
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';

        $attendance_id = $this->clock_in($staff_id, '', $notes);

        if (is_wp_error($attendance_id)) {
            wp_send_json_error($attendance_id->get_error_message());
        } elseif ($attendance_id) {
            $record = $this->get_attendance_record($attendance_id);
            wp_send_json_success(array(
                'message' => __('Clocked in successfully.', 'hospital-management-system'),
                'record' => $record
            ));
        } else {
            wp_send_json_error(__('Failed to clock in.', 'hospital-management-system'));
        }
    }

    public function ajax_clock_out() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_attendance')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $staff_id = intval($_POST['staff_id']);
        $clocked_out = $this->clock_out($staff_id);

        if (is_wp_error($clocked_out)) {
            wp_send_json_error($clocked_out->get_error_message());
        } elseif ($clocked_out) {
            $record = $this->get_today_attendance($staff_id); // Get the updated record
            wp_send_json_success(array(
                'message' => __('Clocked out successfully.', 'hospital-management-system'),
                'record' => $record
            ));
        } else {
            wp_send_json_error(__('Failed to clock out.', 'hospital-management-system'));
        }
    }

    public function ajax_get_attendance_records() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_attendance')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;

        $records = $this->get_all_attendance_records($date_from, $date_to, $staff_id, $offset, $per_page);
        wp_send_json_success($records);
    }

    public function ajax_delete_attendance_record() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_hr_attendance')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $record_id = intval($_POST['record_id']);
        
        $deleted = $this->delete_attendance_record($record_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => __('Attendance record deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to delete attendance record.', 'hospital-management-system'));
        }
    }


    // --- AJAX Handlers for Duty Rosters ---

    public function ajax_create_roster() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_rosters')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'staff_id'      => intval($_POST['staff_id']),
            'shift_name'    => sanitize_text_field($_POST['shift_name']),
            'roster_date'   => sanitize_text_field($_POST['roster_date']),
            'start_time'    => sanitize_text_field($_POST['start_time']),
            'end_time'      => sanitize_text_field($_POST['end_time']),
            'department_id' => intval($_POST['department_id']),
            'notes'         => sanitize_textarea_field($_POST['notes']),
        );

        $roster_id = $this->create_roster($data);

        if (is_wp_error($roster_id)) {
            wp_send_json_error($roster_id->get_error_message());
        } elseif ($roster_id) {
            $roster = $this->get_roster($roster_id);
            wp_send_json_success(array(
                'message' => __('Duty roster created successfully.', 'hospital-management-system'),
                'roster'  => $roster
            ));
        } else {
            wp_send_json_error(__('Failed to create duty roster.', 'hospital-management-system'));
        }
    }

    public function ajax_update_roster() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_rosters')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $roster_id = intval($_POST['roster_id']);
        $data = array(
            'staff_id'      => intval($_POST['staff_id']),
            'shift_name'    => sanitize_text_field($_POST['shift_name']),
            'roster_date'   => sanitize_text_field($_POST['roster_date']),
            'start_time'    => sanitize_text_field($_POST['start_time']),
            'end_time'      => sanitize_text_field($_POST['end_time']),
            'department_id' => intval($_POST['department_id']),
            'notes'         => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_roster($roster_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $roster = $this->get_roster($roster_id);
            wp_send_json_success(array(
                'message' => __('Duty roster updated successfully.', 'hospital-management-system'),
                'roster'  => $roster
            ));
        } else {
            wp_send_json_error(__('Failed to update duty roster.', 'hospital-management-system'));
        }
    }

    public function ajax_get_roster() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_rosters')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $roster_id = intval($_GET['roster_id']);
        $roster = $this->get_roster($roster_id);
        if ($roster) {
            wp_send_json_success($roster);
        } else {
            wp_send_json_error(__('Roster not found.', 'hospital-management-system'));
        }
    }

    public function ajax_get_rosters_list() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hr_rosters')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
        $staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
        $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;

        $rosters = $this->get_all_rosters($date_from, $date_to, $staff_id, $department_id, $offset, $per_page);
        wp_send_json_success($rosters);
    }

    public function ajax_delete_roster() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('delete_hr_rosters')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $roster_id = intval($_POST['roster_id']);
        
        $deleted = $this->delete_roster($roster_id);

        if (is_wp_error($deleted)) {
            wp_send_json_error($deleted->get_error_message());
        } elseif ($deleted) {
            wp_send_json_success(array(
                'message' => __('Duty roster deleted successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to delete duty roster.', 'hospital-management-system'));
        }
    }
} // End of class HMS_Admin_HR
