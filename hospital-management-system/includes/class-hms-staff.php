<?php
/**
 * HMS Staff Class
 *
 * Handles Staff Management features.
 */

class HMS_Staff {

    public function __construct() {
        // Ensure the staff table exists
        $this->ensure_staff_table_exists();
    }

    /**
     * Get a single staff member by ID with proper error handling.
     */
    public function get_staff($staff_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        
        $staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $staff_id));
        
        if ($wpdb->last_error) {
            error_log('HMS Staff Error: ' . $wpdb->last_error);
            return false;
        }
        
        return $staff;
    }

    /**
     * Get staff member by staff ID code.
     */
    public function get_staff_by_code($staff_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        
        $staff = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE staff_id = %s", $staff_code));
        
        if ($wpdb->last_error) {
            error_log('HMS Staff Error: ' . $wpdb->last_error);
            return false;
        }
        
        return $staff;
    }

    /**
     * Get staff member with WordPress user data joined.
     */
    public function get_staff_with_user_data($staff_id) {
        global $wpdb;
        $staff_table = $wpdb->prefix . 'hms_staff';
        $users_table = $wpdb->users;
        
        $query = "
            SELECT s.*, u.user_login, u.user_email as wp_email, u.display_name, u.user_registered
            FROM $staff_table s
            LEFT JOIN $users_table u ON s.email = u.user_email
            WHERE s.id = %d
        ";
        
        $staff = $wpdb->get_row($wpdb->prepare($query, $staff_id));
        
        if ($wpdb->last_error) {
            error_log('HMS Staff Error: ' . $wpdb->last_error);
            return false;
        }
        
        return $staff;
    }

    /**
     * Ensure the staff table exists with the correct structure.
     */
    private function ensure_staff_table_exists() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix;
        
        // Check if the staff table exists
        $table_name = $table_prefix . 'hms_staff';
        $suppress_errors = $wpdb->suppress_errors();
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        $wpdb->suppress_errors($suppress_errors);
        
        if (!$table_exists) {
            // Create the staff table
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                staff_id varchar(50) NOT NULL,
                wp_user_id bigint(20) DEFAULT NULL,
                first_name varchar(100) NOT NULL,
                last_name varchar(100) NOT NULL,
                contact_number varchar(20) DEFAULT NULL,
                email varchar(100) DEFAULT NULL,
                date_of_birth date DEFAULT NULL,
                gender varchar(20) DEFAULT NULL,
                address text DEFAULT NULL,
                role varchar(50) NOT NULL,
                department varchar(50) DEFAULT NULL,
                qualification varchar(255) DEFAULT NULL,
                specialization varchar(255) DEFAULT NULL,
                experience_years int(11) DEFAULT NULL,
                joining_date date DEFAULT NULL,
                status varchar(50) DEFAULT 'active',
                notes text DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY staff_id (staff_id),
                KEY wp_user_id (wp_user_id),
                KEY role (role),
                KEY department (department),
                KEY status (status),
                KEY email (email)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->suppress_errors(true);
            $wpdb->show_errors(false);
            dbDelta($sql);
            $wpdb->suppress_errors(false);
            $wpdb->show_errors(true);
        }
    }
    
    /**
     * Legacy method name for backward compatibility
     */
    private function ensure_staff_table() {
        return $this->ensure_staff_table_exists();
    }

    /**
     * Get staff profile with extended information.
     */
    public function get_staff_profile($staff_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        
        $staff = $this->get_staff_with_user_data($staff_id);
        
        if ($staff) {
            // Get additional profile data
            $staff->profile_photo = get_user_meta($staff->user_login ? get_user_by('login', $staff->user_login)->ID : 0, 'hms_profile_photo', true);
            $staff->emergency_contact = get_user_meta($staff->user_login ? get_user_by('login', $staff->user_login)->ID : 0, 'hms_emergency_contact', true);
            $staff->license_number = get_user_meta($staff->user_login ? get_user_by('login', $staff->user_login)->ID : 0, 'hms_license_number', true);
            $staff->certifications = get_user_meta($staff->user_login ? get_user_by('login', $staff->user_login)->ID : 0, 'hms_certifications', true);
        }
        
        return $staff;
    }

    /**
     * Update staff profile with extended information.
     */
    public function update_staff_profile($staff_id, $profile_data) {
        $staff = $this->get_staff($staff_id);
        if (!$staff) {
            return false;
        }

        // Update basic staff information
        $basic_data = array(
            'first_name' => $profile_data['first_name'],
            'last_name' => $profile_data['last_name'],
            'contact_number' => $profile_data['contact_number'],
            'email' => $profile_data['email'],
            'date_of_birth' => $profile_data['date_of_birth'],
            'gender' => $profile_data['gender'],
            'address' => $profile_data['address'],
            'role' => $profile_data['role'],
            'department' => $profile_data['department'],
            'qualification' => $profile_data['qualification'],
            'specialization' => $profile_data['specialization'],
            'experience_years' => $profile_data['experience_years'],
            'joining_date' => $profile_data['joining_date'],
            'status' => $profile_data['status'],
            'notes' => $profile_data['notes']
        );

        $updated = $this->update_staff($staff_id, $basic_data);

        if ($updated && !empty($profile_data['email'])) {
            // Update WordPress user meta if user exists
            $user = get_user_by('email', $profile_data['email']);
            if ($user) {
                if (isset($profile_data['profile_photo'])) {
                    update_user_meta($user->ID, 'hms_profile_photo', sanitize_text_field($profile_data['profile_photo']));
                }
                if (isset($profile_data['emergency_contact'])) {
                    update_user_meta($user->ID, 'hms_emergency_contact', sanitize_text_field($profile_data['emergency_contact']));
                }
                if (isset($profile_data['license_number'])) {
                    update_user_meta($user->ID, 'hms_license_number', sanitize_text_field($profile_data['license_number']));
                }
                if (isset($profile_data['certifications'])) {
                    update_user_meta($user->ID, 'hms_certifications', sanitize_textarea_field($profile_data['certifications']));
                }
            }
        }

        return $updated;
    }

    /**
     * Get all staff members, with optional filtering.
     */
    public function get_staff_members($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';

        $defaults = array(
            'name' => '',
            'role' => '',
            'department' => '',
            'status' => '',
            'orderby' => 'last_name',
            'order' => 'ASC',
            'offset' => 0,
            'per_page' => 20
        );
        $args = wp_parse_args($args, $defaults);

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['name'])) {
            $where_clauses[] = "(first_name LIKE %s OR last_name LIKE %s)";
            $prepare_values[] = '%' . $wpdb->esc_like($args['name']) . '%';
            $prepare_values[] = '%' . $wpdb->esc_like($args['name']) . '%';
        }

        if (!empty($args['role'])) {
            $where_clauses[] = "role = %s";
            $prepare_values[] = sanitize_text_field($args['role']);
        }

        if (!empty($args['department'])) {
            $where_clauses[] = "department = %s";
            $prepare_values[] = sanitize_text_field($args['department']);
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

        return $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    }

    /**
     * Get total count of staff members for pagination.
     */
    public function get_staff_members_count($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['name'])) {
            $where_clauses[] = "(first_name LIKE %s OR last_name LIKE %s)";
            $prepare_values[] = '%' . $wpdb->esc_like($args['name']) . '%';
            $prepare_values[] = '%' . $wpdb->esc_like($args['name']) . '%';
        }

        if (!empty($args['role'])) {
            $where_clauses[] = "role = %s";
            $prepare_values[] = sanitize_text_field($args['role']);
        }

        if (!empty($args['department'])) {
            $where_clauses[] = "department = %s";
            $prepare_values[] = sanitize_text_field($args['department']);
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
     * Create WordPress user account for staff member.
     */
    public function create_wordpress_user($staff_data) {
        if (empty($staff_data['email'])) {
            return false;
        }

        // Check if user already exists
        $existing_user = get_user_by('email', $staff_data['email']);
        if ($existing_user) {
            return $existing_user->ID;
        }

        // Generate username from email
        $username = sanitize_user(current(explode('@', $staff_data['email'])));
        $counter = 1;
        $original_username = $username;
        
        // Ensure unique username
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }

        // Create user
        $user_data = array(
            'user_login' => $username,
            'user_email' => $staff_data['email'],
            'display_name' => $staff_data['first_name'] . ' ' . $staff_data['last_name'],
            'first_name' => $staff_data['first_name'],
            'last_name' => $staff_data['last_name'],
            'user_pass' => wp_generate_password(12, false),
            'role' => $this->get_wordpress_role_from_hms_role($staff_data['role'])
        );

        $user_id = wp_insert_user($user_data);

        if (!is_wp_error($user_id)) {
            // Add HMS-specific user meta
            update_user_meta($user_id, 'hms_staff_id', $staff_data['staff_id']);
            update_user_meta($user_id, 'hms_role', $staff_data['role']);
            update_user_meta($user_id, 'hms_department', $staff_data['department']);
            
            return $user_id;
        }

        return false;
    }

    /**
     * Map HMS roles to WordPress roles.
     */
    public function get_wordpress_role_from_hms_role($hms_role) {
        $role_mapping = array(
            'doctor' => 'hms_doctor',
            'nurse' => 'hms_nurse',
            'technician' => 'hms_technician',
            'admin' => 'hms_admin',
            'receptionist' => 'hms_receptionist'
        );

        return isset($role_mapping[$hms_role]) ? $role_mapping[$hms_role] : 'hms_staff';
    }

    /**
     * Create HMS-specific WordPress roles and capabilities.
     */
    public function create_hms_roles() {
        // Remove existing roles first
        $this->remove_hms_roles();

        // HMS Doctor role
        add_role('hms_doctor', __('HMS Doctor', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'manage_patients' => true,
            'manage_clinical' => true,
            'view_medical_records' => true,
            'edit_medical_records' => true,
            'manage_prescriptions' => true,
            'view_appointments' => true,
            'edit_appointments' => true,
            'view_lab_tests' => true,
            'order_lab_tests' => true,
        ));

        // HMS Nurse role
        add_role('hms_nurse', __('HMS Nurse', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'manage_patients' => true,
            'view_medical_records' => true,
            'edit_medical_records' => true,
            'view_prescriptions' => true,
            'view_appointments' => true,
            'manage_queue' => true,
        ));

        // HMS Technician role
        add_role('hms_technician', __('HMS Technician', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'view_patients' => true,
            'view_lab_tests' => true,
            'manage_lab_tests' => true,
            'view_appointments' => true,
        ));

        // HMS Admin role
        add_role('hms_admin', __('HMS Administrator', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'manage_patients' => true,
            'manage_clinical' => true,
            'manage_staff' => true,
            'manage_billing' => true,
            'manage_pharmacy' => true,
            'manage_insurance' => true,
            'manage_reports' => true,
            'manage_settings' => true,
            'view_all_data' => true,
            'export_data' => true,
        ));

        // HMS Receptionist role
        add_role('hms_receptionist', __('HMS Receptionist', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'manage_patients' => true,
            'view_appointments' => true,
            'edit_appointments' => true,
            'manage_queue' => true,
            'view_billing' => true,
        ));

        // HMS Staff (generic) role
        add_role('hms_staff', __('HMS Staff', 'hospital-management-system'), array(
            'read' => true,
            'manage_hms' => true,
            'view_patients' => true,
        ));

        // Add HMS capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_hms');
            $admin_role->add_cap('manage_patients');
            $admin_role->add_cap('manage_clinical');
            $admin_role->add_cap('manage_staff');
            $admin_role->add_cap('manage_billing');
            $admin_role->add_cap('manage_pharmacy');
            $admin_role->add_cap('manage_insurance');
            $admin_role->add_cap('manage_reports');
            $admin_role->add_cap('manage_settings');
            $admin_role->add_cap('view_all_data');
            $admin_role->add_cap('export_data');
        }
    }

    /**
     * Remove HMS-specific WordPress roles.
     */
    public function remove_hms_roles() {
        remove_role('hms_doctor');
        remove_role('hms_nurse');
        remove_role('hms_technician');
        remove_role('hms_admin');
        remove_role('hms_receptionist');
        remove_role('hms_staff');
    }

    /**
     * Update staff member's WordPress user role and permissions.
     */
    public function update_staff_wordpress_role($staff_id, $new_role, $new_department = null) {
        $staff = $this->get_staff($staff_id);
        if (!$staff || empty($staff->email)) {
            return false;
        }

        $user = get_user_by('email', $staff->email);
        if (!$user) {
            return false;
        }

        // Update WordPress role
        $wp_role = $this->get_wordpress_role_from_hms_role($new_role);
        $user->set_role($wp_role);

        // Update HMS-specific meta
        update_user_meta($user->ID, 'hms_role', $new_role);
        if ($new_department) {
            update_user_meta($user->ID, 'hms_department', $new_department);
        }

        return true;
    }

    /**
     * Get staff scheduling and availability.
     */
    public function get_staff_schedule($staff_id, $date_from = null, $date_to = null) {
        global $wpdb;
        $schedule_table = $wpdb->prefix . 'hms_staff_schedule';
        
        // Create schedule table if it doesn't exist
        $this->ensure_schedule_table_exists();

        $where_clauses = array('staff_id = %d');
        $prepare_values = array($staff_id);

        if ($date_from) {
            $where_clauses[] = 'schedule_date >= %s';
            $prepare_values[] = $date_from;
        }

        if ($date_to) {
            $where_clauses[] = 'schedule_date <= %s';
            $prepare_values[] = $date_to;
        }

        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        $query = "SELECT * FROM $schedule_table $where_sql ORDER BY schedule_date ASC, start_time ASC";

        return $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    }

    /**
     * Add staff schedule entry.
     */
    public function add_staff_schedule($staff_id, $schedule_data) {
        global $wpdb;
        $schedule_table = $wpdb->prefix . 'hms_staff_schedule';
        
        $this->ensure_schedule_table_exists();

        $data = array(
            'staff_id' => intval($staff_id),
            'schedule_date' => sanitize_text_field($schedule_data['schedule_date']),
            'start_time' => sanitize_text_field($schedule_data['start_time']),
            'end_time' => sanitize_text_field($schedule_data['end_time']),
            'break_start' => sanitize_text_field($schedule_data['break_start']),
            'break_end' => sanitize_text_field($schedule_data['break_end']),
            'status' => sanitize_text_field($schedule_data['status']),
            'notes' => sanitize_textarea_field($schedule_data['notes']),
            'created_at' => current_time('mysql')
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $result = $wpdb->insert($schedule_table, $data, $format);
        
        if ($result) {
            HMS_Helper::log_action('Staff Schedule Added', 'Staff Schedule', $wpdb->insert_id, json_encode($data));
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Ensure staff schedule table exists.
     */
    private function ensure_schedule_table_exists() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'hms_staff_schedule';
        
        $suppress_errors = $wpdb->suppress_errors();
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        $wpdb->suppress_errors($suppress_errors);
        
        if (!$table_exists) {
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                staff_id mediumint(9) NOT NULL,
                schedule_date date NOT NULL,
                start_time time NOT NULL,
                end_time time NOT NULL,
                break_start time DEFAULT NULL,
                break_end time DEFAULT NULL,
                status varchar(20) DEFAULT 'scheduled',
                notes text DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY staff_id (staff_id),
                KEY schedule_date (schedule_date),
                KEY status (status)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->suppress_errors(true);
            $wpdb->show_errors(false);
            dbDelta($sql);
            $wpdb->suppress_errors(false);
            $wpdb->show_errors(true);
        }
    }

    /**
     * Add a new staff member.
     */
    public function add_staff($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';

        // Generate a unique Staff ID if not provided
        if (empty($data['staff_id_code'])) {
            $last_staff = $wpdb->get_row("SELECT id FROM $table_name ORDER BY id DESC LIMIT 1");
            $next_id = ($last_staff) ? $last_staff->id + 1 : 1;
            $staff_id_code = 'ST' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
        } else {
            $staff_id_code = sanitize_text_field($data['staff_id_code']);
        }

        $staff_data = array(
            'staff_id' => $staff_id_code,
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'contact_number' => sanitize_text_field($data['contact_number']),
            'email' => sanitize_email($data['email']),
            'date_of_birth' => sanitize_text_field($data['date_of_birth']),
            'gender' => sanitize_text_field($data['gender']),
            'address' => sanitize_textarea_field($data['address']),
            'role' => sanitize_text_field($data['role']),
            'department' => sanitize_text_field($data['department']),
            'qualification' => sanitize_text_field($data['qualification']),
            'specialization' => sanitize_text_field($data['specialization']),
            'experience_years' => intval($data['experience_years']),
            'joining_date' => sanitize_text_field($data['joining_date']),
            'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'notes' => sanitize_textarea_field($data['notes']),
            'created_at' => current_time('mysql'),
        );

        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');
        
        $result = $wpdb->insert($table_name, $staff_data, $format);

        if ($result) {
            $staff_id = $wpdb->insert_id;
            
            // Create WordPress user account if email is provided and create_wp_user is enabled
            if (!empty($staff_data['email']) && (isset($data['create_wp_user']) && $data['create_wp_user'])) {
                $user_id = $this->create_wordpress_user($staff_data);
                if ($user_id) {
                    // Update staff record with WordPress user ID
                    $wpdb->update(
                        $table_name,
                        array('wp_user_id' => $user_id),
                        array('id' => $staff_id),
                        array('%d'),
                        array('%d')
                    );
                }
            }
            
            HMS_Helper::log_action('Staff Member Added', 'Staff', $staff_id, json_encode($staff_data));
            return $staff_id;
        }
        return false;
    }

    /**
     * Update an existing staff member.
     */
    public function update_staff($staff_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';

        $staff_data = array(
            'staff_id' => sanitize_text_field($data['staff_id_code']),
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'contact_number' => sanitize_text_field($data['contact_number']),
            'email' => sanitize_email($data['email']),
            'date_of_birth' => sanitize_text_field($data['date_of_birth']),
            'gender' => sanitize_text_field($data['gender']),
            'address' => sanitize_textarea_field($data['address']),
            'role' => sanitize_text_field($data['role']),
            'department' => sanitize_text_field($data['department']),
            'qualification' => sanitize_text_field($data['qualification']),
            'specialization' => sanitize_text_field($data['specialization']),
            'experience_years' => intval($data['experience_years']),
            'joining_date' => sanitize_text_field($data['joining_date']),
            'status' => sanitize_text_field($data['status']),
            'notes' => sanitize_textarea_field($data['notes']),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => $staff_id);
        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s');
        $where_format = array('%d');

        $result = $wpdb->update($table_name, $staff_data, $where, $format, $where_format);
        
        if ($result !== false) {
            HMS_Helper::log_action('Staff Member Updated', 'Staff', $staff_id, json_encode($staff_data));
            return true;
        }
        return false;
    }

    /**
     * Delete a staff member.
     */
    public function delete_staff($staff_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        
        $result = $wpdb->delete($table_name, array('id' => $staff_id), array('%d'));
        
        if ($result) {
            HMS_Helper::log_action('Staff Member Deleted', 'Staff', $staff_id);
            return true;
        }
        return false;
    }

    /**
     * AJAX handler to add a new staff member.
     */
    public function ajax_add_staff() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hms')) { // Or a more specific capability
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        // Basic server-side validation
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['staff_id_code']) || empty($_POST['role']) || empty($_POST['department'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'staff_id_code' => sanitize_text_field($_POST['staff_id_code']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'contact_number' => sanitize_text_field($_POST['contact_number']),
            'email' => sanitize_email($_POST['email']),
            'date_of_birth' => sanitize_text_field($_POST['date_of_birth']),
            'gender' => sanitize_text_field($_POST['gender']),
            'address' => sanitize_textarea_field($_POST['address']),
            'role' => sanitize_text_field($_POST['role']),
            'department' => sanitize_text_field($_POST['department']),
            'qualification' => sanitize_text_field($_POST['qualification']),
            'specialization' => sanitize_text_field($_POST['specialization']),
            'experience_years' => intval($_POST['experience_years']),
            'joining_date' => sanitize_text_field($_POST['joining_date']),
            'status' => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active',
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $staff_id = $this->add_staff($data);

        if ($staff_id) {
            $staff = $this->get_staff($staff_id);
            wp_send_json_success(array(
                'message' => esc_html__('Staff member added successfully.', 'hospital-management-system'),
                'staff' => $staff,
                'redirect_url' => admin_url('admin.php?page=hms_staff&message=' . urlencode(esc_html__('Staff member added successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to add staff member.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update a staff member.
     */
    public function ajax_update_staff() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $staff_id = intval($_POST['staff_id']);
        
        // Basic server-side validation
        if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['staff_id_code']) || empty($_POST['role']) || empty($_POST['department'])) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'staff_id_code' => sanitize_text_field($_POST['staff_id_code']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'contact_number' => sanitize_text_field($_POST['contact_number']),
            'email' => sanitize_email($_POST['email']),
            'date_of_birth' => sanitize_text_field($_POST['date_of_birth']),
            'gender' => sanitize_text_field($_POST['gender']),
            'address' => sanitize_textarea_field($_POST['address']),
            'role' => sanitize_text_field($_POST['role']),
            'department' => sanitize_text_field($_POST['department']),
            'qualification' => sanitize_text_field($_POST['qualification']),
            'specialization' => sanitize_text_field($_POST['specialization']),
            'experience_years' => intval($_POST['experience_years']),
            'joining_date' => sanitize_text_field($_POST['joining_date']),
            'status' => sanitize_text_field($_POST['status']),
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_staff($staff_id, $data);

        if ($updated) {
            $staff = $this->get_staff($staff_id);
            wp_send_json_success(array(
                'message' => esc_html__('Staff member updated successfully.', 'hospital-management-system'),
                'staff' => $staff,
                'redirect_url' => admin_url('admin.php?page=hms_staff&message=' . urlencode(esc_html__('Staff member updated successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to update staff member.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to delete a staff member.
     */
    public function ajax_delete_staff() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $staff_id = intval($_POST['staff_id']);
        $deleted = $this->delete_staff($staff_id);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Staff member deleted successfully.', 'hospital-management-system'),
                'redirect_url' => admin_url('admin.php?page=hms_staff&message=' . urlencode(esc_html__('Staff member deleted successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to delete staff member.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single staff member's details.
     */
    public function ajax_get_staff() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }
        $staff_id = intval($_GET['staff_id']);
        $staff = $this->get_staff($staff_id);
        if ($staff) {
            wp_send_json_success(array('staff' => $staff));
        } else {
            wp_send_json_error(esc_html__('Staff member not found.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a list of staff members for DataTables or similar.
     */
    public function ajax_get_staff_list() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $args = array(
            'name' => isset($_GET['name']) ? sanitize_text_field($_GET['name']) : '',
            'role' => isset($_GET['role']) ? sanitize_text_field($_GET['role']) : '',
            'department' => isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '',
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'offset' => isset($_GET['start']) ? intval($_GET['start']) : 0,
            'per_page' => isset($_GET['length']) ? intval($_GET['length']) : 20,
        );
        
        $staff_members = $this->get_staff_members($args);
        $total_staff = $this->get_staff_members_count($args);
        
        $data = array();
        foreach ($staff_members as $staff) {
            $data[] = array(
                'id' => $staff->id,
                'staff_id' => $staff->staff_id,
                'name' => $staff->first_name . ' ' . $staff->last_name,
                'role' => $staff->role,
                'department' => $staff->department,
                'contact_number' => $staff->contact_number,
                'email' => $staff->email,
                'status' => $staff->status,
                'actions' => '' // Action buttons will be added by JS
            );
        }

        wp_send_json(array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => $total_staff,
            'recordsFiltered' => $total_staff,
            'data' => $data
        ));
    }
}
