<?php
// includes/class-hms-helper.php

/**
 * HMS Helper Class
 *
 * A collection of static utility functions used throughout the plugin.
 * Includes database abstraction, audit logging, data encryption,
 * notification helpers, and other common utilities.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */
class HMS_Helper {

    /**
     * Allowed columns for ORDER BY clause to prevent SQL injection.
     * 
     * This whitelist ensures that only safe column names can be used in ORDER BY clauses,
     * preventing SQL injection attacks through dynamic ordering.
     *
     * @since 1.0.0
     * @var array List of allowed column names for ordering
     */
    private static $allowed_order_columns = array(
        'id', 'patient_id', 'doctor_id', 'appointment_date', 'status', 'created_at', 'updated_at',
        'token_number', 'queue_date', 'name', 'quantity_in_stock', 'bill_number', 'payment_status',
        'first_name', 'last_name', 'email', 'contact_number', 'registration_date'
    );

    /**
     * Generic Create function for HMS custom tables.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    array      $data          Data to insert (column => value).
     * @param    array|null $format        Optional. An array of formats to be mapped to each of the values in $data.
     * @return   int|false                 The ID of the new row, or false on error.
     */
    public static function hms_create(string $table_name, array $data, ?array $format = null) {
        global $wpdb;
        $full_table_name = $wpdb->prefix . $table_name;

        if (empty($data)) {
            return false;
        }

        $result = $wpdb->insert($full_table_name, $data, $format);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Generic Read (Get Row) function for HMS custom tables.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    array      $where         Optional. Where clause as an associative array (column => value).
     * @param    array|null $where_format  Optional. An array of formats to be mapped to each of the values in $where.
     * @return   object|false              The row as an object, or false if not found.
     */
    public static function hms_get_row(string $table_name, array $where = array(), ?array $where_format = null) {
        global $wpdb;
        $full_table_name = $wpdb->prefix . $table_name;

        if (empty($where)) {
            // Get the first row if no where clause is provided
            $sql = "SELECT * FROM {$full_table_name} ORDER BY id DESC LIMIT 1";
            return $wpdb->get_row($sql);
        }

        $where_clauses = array();
        $values = array();
        $i = 0;
        
        foreach ($where as $column => $value) {
            $format = ($where_format && isset($where_format[$i])) ? $where_format[$i] : '%s';
            $where_clauses[] = "`{$column}` = {$format}";
            $values[] = $value;
            $i++;
        }
        
        $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        $sql = "SELECT * FROM {$full_table_name} {$where_clause} LIMIT 1";
        
        return $wpdb->get_row($wpdb->prepare($sql, $values));
    }

    /**
     * Generic Read (Get Results) function for HMS custom tables.
     * 
     * Provides a secure and optimized way to query HMS tables with proper
     * input validation, SQL injection prevention, and performance optimization.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    array      $where         Optional. Where clause as an associative array (column => value).
     * @param    string|null $order_by      Optional. Column to order by. Must be in $allowed_order_columns.
     * @param    string     $order         Optional. Order direction (ASC or DESC).
     * @param    int|null   $limit         Optional. Number of rows to return. Max 1000 for performance.
     * @param    int|null   $offset        Optional. Offset for the rows.
     * @param    array|null $where_format  Optional. An array of formats to be mapped to each of the values in $where.
     * @return   array|false               An array of rows as objects, or false on error.
     */
    public static function hms_get_results(string $table_name, array $where = array(), ?string $order_by = null, string $order = 'ASC', ?int $limit = null, ?int $offset = null, ?array $where_format = null) {
        global $wpdb;
        
        // Validate table name to prevent SQL injection
        $table_name = sanitize_key($table_name);
        if (empty($table_name)) {
            return false;
        }
        
        $full_table_name = $wpdb->prefix . $table_name;
        $sql = "SELECT * FROM {$full_table_name}";
        $values = array();

        // Build WHERE clause with proper sanitization
        if (!empty($where)) {
            $where_clauses = array();
            $i = 0;
            foreach ($where as $column => $value) {
                // Sanitize column name to prevent SQL injection
                $column = sanitize_key($column);
                if (empty($column)) {
                    continue;
                }
                
                $format = ($where_format && isset($where_format[$i])) ? $where_format[$i] : '%s';
                $where_clauses[] = "`{$column}` = {$format}";
                $values[] = $value;
                $i++;
            }
            
            if (!empty($where_clauses)) {
                $sql .= " WHERE " . implode(' AND ', $where_clauses);
            }
        }

        // Add ORDER BY clause with validation
        if ($order_by && in_array($order_by, self::$allowed_order_columns, true)) {
            $order_direction = (strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `{$order_by}` {$order_direction}";
        }

        // Add LIMIT clause with validation (max 1000 for performance)
        if ($limit !== null) {
            $limit = max(1, min(1000, intval($limit))); // Ensure limit is between 1 and 1000
            $sql .= " LIMIT %d";
            $values[] = $limit;
            
            if ($offset !== null) {
                $offset = max(0, intval($offset)); // Ensure offset is non-negative
                $sql .= " OFFSET %d";
                $values[] = $offset;
            }
        }

        // Execute query with proper preparation
        if (!empty($values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            return $wpdb->get_results($sql);
        }
    }

    /**
     * Generic Update function for HMS custom tables.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    array      $data          Data to update (column => value).
     * @param    array      $where         Where clause as an associative array (column => value).
     * @param    array|null $format        Optional. An array of formats to be mapped to each of the values in $data.
     * @param    array|null $where_format  Optional. An array of formats to be mapped to each of the values in $where.
     * @return   int|false                 The number of rows updated, or false on error.
     */
    public static function hms_update(string $table_name, array $data, array $where, ?array $format = null, ?array $where_format = null) {
        global $wpdb;
        $full_table_name = $wpdb->prefix . $table_name;

        if (empty($data) || empty($where)) {
            return false;
        }

        return $wpdb->update($full_table_name, $data, $where, $format, $where_format);
    }

    /**
     * Generic Delete function for HMS custom tables.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    array      $where         Where clause as an associative array (column => value).
     * @param    array|null $where_format  Optional. An array of formats to be mapped to each of the values in $where.
     * @return   int|false                 The number of rows deleted, or false on error.
     */
    public static function hms_delete(string $table_name, array $where, ?array $where_format = null) {
        global $wpdb;
        $full_table_name = $wpdb->prefix . $table_name;

        if (empty($where)) {
            return false;
        }

        return $wpdb->delete($full_table_name, $where, $where_format);
    }

    /**
     * Get a single value from a custom table with caching support.
     * 
     * Provides optimized single value retrieval with proper input validation
     * and optional caching for frequently accessed data.
     *
     * @since    1.0.0
     * @param    string     $table_name    The table name without the WordPress prefix.
     * @param    string     $column        The column to retrieve.
     * @param    array      $where         Optional. Where clause as an associative array (column => value).
     * @param    array|null $where_format  Optional. An array of formats to be mapped to each of the values in $where.
     * @param    bool       $use_cache     Optional. Whether to use WordPress object cache. Default false.
     * @return   mixed|null                 The value, or null if not found.
     */
    public static function hms_get_var(string $table_name, string $column, array $where = array(), ?array $where_format = null, bool $use_cache = false) {
        global $wpdb;
        
        // Validate and sanitize inputs
        $table_name = sanitize_key($table_name);
        $column = sanitize_key($column);
        
        if (empty($table_name) || empty($column)) {
            return null;
        }
        
        // Generate cache key if caching is enabled
        $cache_key = '';
        if ($use_cache) {
            $cache_key = 'hms_var_' . md5($table_name . $column . serialize($where));
            $cached_value = wp_cache_get($cache_key, 'hms_queries');
            if ($cached_value !== false) {
                return $cached_value;
            }
        }
        
        $full_table_name = $wpdb->prefix . $table_name;
        $sql = "SELECT `{$column}` FROM {$full_table_name}";
        $values = array();

        // Build WHERE clause with proper sanitization
        if (!empty($where)) {
            $where_clauses = array();
            $i = 0;
            foreach ($where as $col => $value) {
                // Sanitize column name
                $col = sanitize_key($col);
                if (empty($col)) {
                    continue;
                }
                
                $format = ($where_format && isset($where_format[$i])) ? $where_format[$i] : '%s';
                $where_clauses[] = "`{$col}` = {$format}";
                $values[] = $value;
                $i++;
            }
            
            if (!empty($where_clauses)) {
                $sql .= " WHERE " . implode(' AND ', $where_clauses);
            }
        }
        
        $sql .= " LIMIT 1";
        
        // Execute query
        $result = null;
        if (!empty($values)) {
            $result = $wpdb->get_var($wpdb->prepare($sql, $values));
        } else {
            $result = $wpdb->get_var($sql);
        }
        
        // Cache the result if caching is enabled
        if ($use_cache && !empty($cache_key)) {
            wp_cache_set($cache_key, $result, 'hms_queries', 300); // Cache for 5 minutes
        }
        
        return $result;
    }

    /**
     * Clear HMS query cache.
     * 
     * Clears all cached HMS queries to ensure fresh data retrieval.
     * Should be called after data modifications.
     *
     * @since 1.0.0
     * @return void
     */
    public static function clear_query_cache() {
        wp_cache_flush_group('hms_queries');
    }

    /**
     * Get the client's IP address, handling potential proxies.
     *
     * @since    1.0.0
     * @return   string    The client's IP address.
     */
    private static function get_client_ip(): string {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return '127.0.0.1'; // Default fallback
    }

    /**
     * Log an action to the audit trail.
     *
     * @since    1.0.0
     * @param    string      $action        The action performed (e.g., 'Patient Added', 'Bill Updated').
     * @param    string|null $object_type   The type of object (e.g., 'Patient', 'Bill', 'Appointment').
     * @param    int|null    $object_id     The ID of the object, if applicable.
     * @param    mixed|null  $details       Additional details about the action (e.g., JSON string of changes).
     * @return   int|false                  False on failure, the log entry ID on success.
     */
    public static function log_action(string $action, ?string $object_type = null, ?int $object_id = null, $details = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_audit_log';

        $user_id = get_current_user_id();
        $ip_address = self::get_client_ip();

        $log_data = array(
            'user_id'     => $user_id,
            'action'      => sanitize_text_field($action),
            'object_type' => $object_type ? sanitize_text_field($object_type) : null,
            'object_id'   => $object_id,
            'details'     => is_string($details) ? $details : json_encode($details),
            'ip_address'  => $ip_address,
        );

        $format = array('%d', '%s', '%s', '%d', '%s', '%s');

        // Disable error reporting to prevent any output
        $error_reporting = error_reporting(0);
        $result = $wpdb->insert($table_name, $log_data, $format);
        // Restore error reporting
        error_reporting($error_reporting);
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Generate a unique patient ID for CPT.
     *
     * @since    1.0.0
     * @return   string    The unique patient ID.
     */
    public static function generate_patient_id(): string {
        $prefix = 'PT';
        $random_number = mt_rand(100000, 999999);
        $patient_id = $prefix . $random_number;
        
        // Check if ID already exists in CPT meta
        $args = array(
            'post_type'      => 'hms_patient',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_patient_id',
                    'value' => $patient_id,
                ),
            ),
            'fields'         => 'ids', // More efficient
        );
        
        $existing_patient_ids = get_posts($args);
        
        if ($existing_patient_ids) {
            // If ID exists, generate a new one recursively
            return self::generate_patient_id();
        }
        
        return $patient_id;
    }

    /**
     * Get patient CPT post object by its unique patient ID.
     *
     * @since    1.0.0
     * @param    string    $patient_id    The unique patient ID.
     * @return   WP_Post|false            The patient post object, or false on failure.
     */
    public static function get_patient_by_id(string $patient_id) {
        $args = array(
            'post_type'      => 'hms_patient',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_patient_id',
                    'value' => $patient_id,
                ),
            ),
        );
        
        $patients = get_posts($args);
        return $patients ? $patients[0] : false;
    }

    /**
     * Get doctors by department.
     * Note: This relies on a 'hms_doctor' role and '_department' user meta.
     * The 'hms_doctor' role is not created by the current activator.
     *
     * @since    1.0.0
     * @param    int       $department_id    The ID of the department.
     * @return   array                      An array of WP_User objects.
     */
    public static function get_doctors_by_department(int $department_id): array {
        // Ensure the role exists before querying, or handle its absence.
        if (!get_role('hms_doctor')) {
            return array(); // Return empty if role doesn't exist.
        }

        $doctors = get_users(array(
            'role'       => 'hms_doctor',
            'meta_query' => array(
                array(
                    'key'   => '_department',
                    'value' => $department_id,
                ),
            ),
        ));
        
        return $doctors;
    }

    /**
     * Get available appointment slots for a doctor on a given date.
     * Note: This function is conceptual and relies on CPT meta for appointments.
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @param    string    $date         The date (Y-m-d format).
     * @return   array                   An array of available time slots.
     */
    public static function get_available_slots(int $doctor_id, string $date): array {
        $slots = array();
        $appointment_duration = (int) get_option('hms_appointment_duration', 30); // in minutes
        
        // Define working hours (e.g., 9 AM to 5 PM)
        $start_time = strtotime('9:00 AM');
        $end_time = strtotime('5:00 PM');
        $interval = $appointment_duration * 60; // in seconds
        
        for ($time = $start_time; $time < $end_time; $time += $interval) {
            $slot_time_str = gmdate('H:i:s', $time);
            $slot_display_str = gmdate('h:i A', $time);
            
            if (self::is_slot_available($doctor_id, $date, $slot_time_str)) {
                $slots[] = $slot_display_str;
            }
        }
        
        return $slots;
    }

    /**
     * Check if a time slot is available for a doctor on a given date.
     * Note: This function is conceptual and relies on CPT meta for appointments.
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @param    string    $date         The date (Y-m-d format).
     * @param    string    $time         The time slot (H:i:s format).
     * @return   bool                     True if available, false otherwise.
     */
    public static function is_slot_available(int $doctor_id, string $date, string $time): bool {
        $args = array(
            'post_type'      => 'hms_appointment',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_doctor_id',
                    'value' => $doctor_id,
                ),
                array(
                    'key'   => '_appointment_date',
                    'value' => $date,
                ),
                array(
                    'key'   => '_appointment_time',
                    'value' => $time,
                ),
                array(
                    'key'   => '_status',
                    'value' => 'Cancelled',
                    'compare' => '!=',
                ),
            ),
            'fields' => 'ids',
        );
        
        $appointments = get_posts($args);
        return empty($appointments);
    }

    /**
     * Send SMS notification (placeholder).
     *
     * @since    1.0.0
     * @param    string    $phone         The phone number.
     * @param    string    $message       The message content.
     * @return   bool                     True for demonstration.
     */
    public static function send_sms(string $phone, string $message): bool {
        // Integration with an SMS gateway API (e.g., Twilio, MSG91) would go here.
        // For now, log the message.
        error_log("HMS SMS Notification to {$phone}: {$message}");
        return true;
    }

    /**
     * Send email notification.
     *
     * @since    1.0.0
     * @param    string    $to            The recipient email address.
     * @param    string    $subject       The email subject.
     * @param    string    $message       The email body (HTML).
     * @param    array     $attachments   Optional. An array of file paths to attach.
     * @return   bool                     True on success, false on failure.
     */
    public static function send_email(string $to, string $subject, string $message, array $attachments = array()): bool {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }

    /**
     * Send WhatsApp notification (placeholder).
     *
     * @since    1.0.0
     * @param    string    $phone         The phone number.
     * @param    string    $message       The message content.
     * @return   bool                     True for demonstration.
     */
    public static function send_whatsapp(string $phone, string $message): bool {
        // Integration with a WhatsApp Business API would go here.
        // For now, log the message.
        error_log("HMS WhatsApp Notification to {$phone}: {$message}");
        return true;
    }

    /**
     * Update the status of a queue token in the custom table.
     *
     * @since    1.0.0
     * @param    int       $queue_id      The ID of the queue entry.
     * @param    string    $status        The new status.
     * @return   bool                     True on success, false on failure.
     */
    public static function update_queue_status(int $queue_id, string $status): bool {
        $data = array('status' => sanitize_text_field($status));
        $where = array('id' => $queue_id);
        $format = array('%s');
        $where_format = array('%d');

        $updated = self::hms_update('hms_queue', $data, $where, $format, $where_format);

        if ($updated !== false) {
            $timestamp_data = array();
            if ($status === 'in-progress') {
                $timestamp_data['called_at'] = current_time('mysql');
            } elseif ($status === 'completed') {
                $timestamp_data['completed_at'] = current_time('mysql');
            }
            
            if (!empty($timestamp_data)) {
                self::hms_update('hms_queue', $timestamp_data, $where, array('%s'), $where_format);
            }

            self::log_action('Queue Status Updated', 'Queue', $queue_id, array('new_status' => $status));

            // Send notification for status changes
            if (in_array($status, array('in-progress', 'completed', 'cancelled'))) {
                self::send_queue_notification($queue_id, $status);
            }

            // Trigger notification if patient is added to the queue.
            // Check for HMS_Notifications class to avoid fatal error if it doesn't exist.
            if ($status === 'waiting' && class_exists('HMS_Notifications')) {
                $queue_entry = self::hms_get_row('hms_queue', $where, $where_format);
                if ($queue_entry && !empty($queue_entry->appointment_id) && method_exists('HMS_Notifications', 'send_appointment_confirmation')) {
                    HMS_Notifications::send_appointment_confirmation($queue_entry->appointment_id);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Get patient's upcoming appointments (CPT based).
     *
     * @since    1.0.0
     * @param    int       $patient_id    The patient's post ID.
     * @return   array                     An array of appointment post objects.
     */
    public static function get_upcoming_appointments(int $patient_id): array {
        $args = array(
            'post_type'      => 'hms_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_patient_id',
                    'value' => $patient_id,
                ),
                array(
                    'key'   => '_appointment_date',
                    'value' => gmdate('Y-m-d'),
                    'compare' => '>=',
                ),
                array(
                    'key'   => '_status',
                    'value' => 'Cancelled',
                    'compare' => '!=',
                ),
            ),
            'orderby'        => 'meta_value',
            'meta_key'       => '_appointment_date',
            'order'          => 'ASC',
        );
        
        return get_posts($args);
    }

    /**
     * Get patient's unpaid bills (CPT based).
     *
     * @since    1.0.0
     * @param    int       $patient_id    The patient's post ID.
     * @return   array                     An array of bill post objects.
     */
    public static function get_unpaid_bills(int $patient_id): array {
        $args = array(
            'post_type'      => 'hms_bill',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_patient_id',
                    'value' => $patient_id,
                ),
                array(
                    'key'   => '_payment_status',
                    'value' => 'Paid',
                    'compare' => '!=',
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        
        return get_posts($args);
    }

    /**
     * Get today's patient census (CPT based).
     *
     * @since    1.0.0
     * @return   int                       The number of patients registered today.
     */
    public static function get_today_patient_census(): int {
        $args = array(
            'post_type'      => 'hms_patient',
            'posts_per_page' => -1,
            'date_query'     => array(
                array(
                    'year'  => gmdate('Y'),
                    'month' => gmdate('m'),
                    'day'   => gmdate('d'),
                ),
            ),
            'fields'         => 'ids',
        );
        $patients = get_posts($args);
        return count($patients);
    }

    /**
     * Get today's appointments (CPT based).
     *
     * @since    1.0.0
     * @return   array                     An array of appointment post objects.
     */
    public static function get_today_appointments(): array {
        $args = array(
            'post_type'      => 'hms_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_appointment_date',
                    'value' => gmdate('Y-m-d'),
                ),
            ),
            'orderby'        => 'meta_value', // Assuming _appointment_time is stored as meta
            'meta_key'       => '_appointment_time',
            'order'          => 'ASC',
        );
        
        return get_posts($args);
    }

    /**
     * Get today's revenue (CPT based).
     *
     * @since    1.0.0
     * @return   float                     The total revenue collected today.
     */
    public static function get_today_revenue(): float {
        $args = array(
            'post_type'      => 'hms_bill',
            'posts_per_page' => -1,
            'date_query'     => array(
                array(
                    'year'  => gmdate('Y'),
                    'month' => gmdate('m'),
                    'day'   => gmdate('d'),
                ),
            ),
            'fields'         => 'ids',
        );
        
        $bill_ids = get_posts($args);
        $revenue = 0.0;
        
        foreach ($bill_ids as $bill_id) {
            $paid_amount = get_post_meta($bill_id, '_paid_amount', true);
            $revenue += floatval($paid_amount);
        }
        
        return $revenue;
    }

    /**
     * Get low stock medicines (CPT based).
     *
     * @since    1.0.0
     * @return   array                     An array of medicine post objects.
     */
    public static function get_low_stock_medicines(): array {
        $args = array(
            'post_type'      => 'hms_medicine',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_stock_quantity',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
                array(
                    'key'     => '_reorder_level',
                    'value'   => '_stock_quantity', // Compare against the value of _stock_quantity
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ),
            ),
        );
        
        return get_posts($args);
    }

    /**
     * Get pending insurance claims (CPT based).
     *
     * @since    1.0.0
     * @return   array                     An array of bill post objects with pending claims.
     */
    public static function get_pending_insurance_claims(): array {
        $args = array(
            'post_type'      => 'hms_bill',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_insurance_id',
                    'value'   => '',
                    'compare' => '!=',
                ),
                array(
                    'key'     => '_insurance_status',
                    'value'   => 'Pending',
                ),
            ),
        );
        
        return get_posts($args);
    }

    /**
     * Generate a unique token number for the queue (custom table).
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @param    string    $date         The date for the queue (Y-m-d format).
     * @return   string                    The generated token number.
     */
    public static function generate_queue_token(int $doctor_id, string $date): string {
        $last_token = self::hms_get_var('hms_queue', 'token_number', array('doctor_id' => $doctor_id, 'queue_date' => $date), array('%d', '%s'));
        $last_number = $last_token ? (int)preg_replace('/[^0-9]/', '', $last_token) : 0;
        $new_number = $last_number + 1;
        $prefix = 'T'; // T for Token
        return $prefix . str_pad($new_number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Add a patient to the OPD queue (custom table).
     *
     * @since    1.0.0
     * @param    int       $patient_id    The ID of the patient (from custom table).
     * @param    int       $doctor_id     The ID of the doctor (from custom table).
     * @param    int|null  $appointment_id The ID of the appointment (from CPT).
     * @param    string    $status        The initial status of the token.
     * @return   int|false                 The ID of the new queue entry, or false on failure.
     */
    public static function add_to_queue(int $patient_id, int $doctor_id, ?int $appointment_id = null, string $status = 'waiting') {
        $queue_date = gmdate('Y-m-d');
        $token_number = self::generate_queue_token($doctor_id, $queue_date);

        $data = array(
            'patient_id'      => $patient_id,
            'appointment_id'  => $appointment_id,
            'doctor_id'       => $doctor_id,
            'token_number'    => $token_number,
            'status'          => $status,
            'queue_date'      => $queue_date,
        );
        $format = array('%d', '%d', '%d', '%s', '%s', '%s');

        $queue_id = self::hms_create('hms_queue', $data, $format);

        if ($queue_id) {
            self::log_action('Patient Added to Queue', 'Queue', $queue_id, array(
                'patient_id'   => $patient_id,
                'doctor_id'    => $doctor_id,
                'token_number' => $token_number,
            ));
        }
        return $queue_id;
    }

    /**
     * Get the current queue for a specific doctor for today (custom table).
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @param    string    $date         The date for the queue (Y-m-d format). Defaults to today.
     * @param    string|null $status      Optional. Filter by status.
     * @return   array                     An array of queue entry objects.
     */
    public static function get_doctor_queue(int $doctor_id, string $date = null, ?string $status = null): array {
        $date = $date ?: gmdate('Y-m-d');
        $where = array('doctor_id' => $doctor_id, 'queue_date' => $date);
        $where_format = array('%d', '%s');

        if ($status !== null) {
            $where['status'] = $status;
            $where_format[] = '%s';
        }

        return self::hms_get_results('hms_queue', $where, 'token_number', 'ASC', null, null, $where_format);
    }

    /**
     * Get the currently active token for a doctor (in-progress) (custom table).
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @return   object|false              The active queue entry object, or false if none.
     */
    public static function get_active_token(int $doctor_id) {
        global $wpdb;
        $today = gmdate('Y-m-d');
        
        $query = $wpdb->prepare(
            "SELECT q.*, p.first_name, p.last_name, p.patient_id as unique_patient_id 
             FROM {$wpdb->prefix}hms_queue q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.doctor_id = %d AND q.queue_date = %s AND q.status = 'in-progress'
             LIMIT 1",
            $doctor_id, $today
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Get the next patient in the queue for a doctor (custom table).
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The ID of the doctor.
     * @return   object|false              The next queue entry object, or false if none.
     */
    public static function get_next_in_queue(int $doctor_id) {
        global $wpdb;
        $today = gmdate('Y-m-d');
        
        $query = $wpdb->prepare(
            "SELECT q.*, p.first_name, p.last_name, p.patient_id as unique_patient_id 
             FROM {$wpdb->prefix}hms_queue q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.doctor_id = %d AND q.queue_date = %s AND q.status = 'waiting' 
             ORDER BY CAST(q.token_number AS UNSIGNED) ASC 
             LIMIT 1",
            $doctor_id, $today
        );
        
        return $wpdb->get_row($query);
    }

    /**
     * Send queue notification to patient (if SMS/WhatsApp is configured).
     *
     * @since    1.0.0
     * @param    int       $queue_id     The queue entry ID.
     * @param    string    $status       The new status.
     * @return   bool                    True if notification sent, false otherwise.
     */
    public static function send_queue_notification($queue_id, $status) {
        global $wpdb;
        
        // Get queue entry with patient information
        $queue_entry = $wpdb->get_row($wpdb->prepare(
            "SELECT q.*, p.first_name, p.last_name, p.phone 
             FROM {$wpdb->prefix}hms_queue q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.id = %d",
            $queue_id
        ));

        if (!$queue_entry || empty($queue_entry->phone)) {
            return false;
        }

        $message = '';
        switch ($status) {
            case 'in-progress':
                $message = sprintf(
                    __('Dear %s, your token number %s is now being called. Please proceed to the doctor.', 'hospital-management-system'),
                    $queue_entry->first_name,
                    $queue_entry->token_number
                );
                break;
            case 'completed':
                $message = sprintf(
                    __('Dear %s, your consultation for token %s has been completed. Thank you for visiting.', 'hospital-management-system'),
                    $queue_entry->first_name,
                    $queue_entry->token_number
                );
                break;
            case 'cancelled':
                $message = sprintf(
                    __('Dear %s, your token %s has been cancelled. Please contact reception for rescheduling.', 'hospital-management-system'),
                    $queue_entry->first_name,
                    $queue_entry->token_number
                );
                break;
        }

        if ($message) {
            // Check if SMS gateway is available and configured
            if (class_exists('HMS_SMS_Gateway')) {
                return HMS_SMS_Gateway::send_sms($queue_entry->phone, $message);
            }
            
            // Check if WhatsApp gateway is available and configured
            if (class_exists('HMS_WhatsApp_Gateway')) {
                return HMS_WhatsApp_Gateway::send_message($queue_entry->phone, $message);
            }
        }

        return false;
    }

    /**
     * Calculate estimated wait time for a queue position.
     *
     * @since    1.0.0
     * @param    int       $doctor_id           The doctor ID.
     * @param    int       $token_number        The token number to calculate wait time for.
     * @param    int       $avg_consultation    Average consultation time in minutes.
     * @return   int                            Estimated wait time in minutes.
     */
    public static function calculate_wait_time($doctor_id, $token_number, $avg_consultation = 15) {
        global $wpdb;
        $today = gmdate('Y-m-d');
        
        // Count patients ahead in queue
        $patients_ahead = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_queue 
             WHERE doctor_id = %d AND queue_date = %s 
             AND status IN ('waiting', 'in-progress') 
             AND CAST(token_number AS UNSIGNED) < %d",
            $doctor_id, $today, intval($token_number)
        ));

        return $patients_ahead * $avg_consultation;
    }

    /**
     * Get queue statistics for a doctor or all doctors.
     *
     * @since    1.0.0
     * @param    int|null  $doctor_id    The doctor ID, or null for all doctors.
     * @param    string    $date         The date to get statistics for.
     * @return   array                   Array of queue statistics.
     */
    public static function get_queue_statistics($doctor_id = null, $date = null) {
        global $wpdb;
        $date = $date ?: gmdate('Y-m-d');
        
        $where_clause = "WHERE queue_date = %s";
        $where_values = array($date);
        
        if ($doctor_id) {
            $where_clause .= " AND doctor_id = %d";
            $where_values[] = $doctor_id;
        }
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_patients,
                SUM(CASE WHEN status = 'waiting' THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                AVG(CASE WHEN completed_at IS NOT NULL AND called_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, called_at, completed_at) 
                    ELSE NULL END) as avg_consultation_time
             FROM {$wpdb->prefix}hms_queue 
             $where_clause",
            ...$where_values
        ));
        
        return array(
            'total_patients' => intval($stats->total_patients),
            'waiting' => intval($stats->waiting),
            'in_progress' => intval($stats->in_progress),
            'completed' => intval($stats->completed),
            'cancelled' => intval($stats->cancelled),
            'avg_consultation_time' => round(floatval($stats->avg_consultation_time), 1),
            'estimated_wait_time' => intval($stats->waiting) * 15 // 15 minutes per patient
        );
    }

    /**
     * Get or create the encryption key.
     *
     * @since    1.0.0
     * @return   string|false    The encryption key, or false on failure.
     */
    private static function get_encryption_key() {
        $key_option = get_option('hms_encryption_key');
        if ($key_option) {
            $key = base64_decode($key_option);
            // Check if the key is of valid length for aes-256-cbc (32 bytes)
            if (strlen($key) === 32) {
                return $key;
            }
        }

        // Generate a new key if one doesn't exist or is invalid
        $new_key = openssl_random_pseudo_bytes(32);
        if ($new_key === false) {
            return false; // Failed to generate key
        }
        update_option('hms_encryption_key', base64_encode($new_key));
        return $new_key;
    }

    /**
     * Encrypt data using AES-256-CBC.
     *
     * @since    1.0.0
     * @param    string    $data    The data to encrypt.
     * @return   string|false       The base64 encoded encrypted data (IV + ciphertext), or false on failure.
     */
    public static function encrypt_data(string $data) {
        if (empty($data)) {
            return $data; // Return empty if data is empty
        }

        $encryption_key = self::get_encryption_key();
        if ($encryption_key === false) {
            return false; // Cannot encrypt without a key
        }

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        if ($iv === false) {
            return false; // Failed to generate IV
        }

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        if ($encrypted === false) {
            return false; // Encryption failed
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data using AES-256-CBC.
     *
     * @since    1.0.0
     * @param    string    $encrypted_data    The base64 encoded encrypted data (IV + ciphertext).
     * @return   string|false               The decrypted data, or false on failure.
     */
    public static function decrypt_data(string $encrypted_data) {
        if (empty($encrypted_data)) {
            return $encrypted_data;
        }

        $encryption_key = self::get_encryption_key();
        if ($encryption_key === false) {
            return false; // Cannot decrypt without a key
        }
        
        $data = base64_decode($encrypted_data);
        if ($data === false) {
            return false; // Failed to base64 decode
        }

        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        if (strlen($data) < $iv_length) {
            return false; // Data is too short to contain IV
        }
        
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $encryption_key, 0, $iv);
        return $decrypted !== false ? $decrypted : false;
    }

    /**
     * Format currency with proper symbol and formatting.
     *
     * @since    1.0.0
     * @param    float     $amount        The amount to format.
     * @param    string    $currency      Optional. The currency code. Defaults to site currency.
     * @return   string                   The formatted currency string.
     */
    public static function format_currency(float $amount, string $currency = ''): string {
        if (empty($currency)) {
            $currency = get_option('hms_currency', 'INR');
        }

        $formatted_amount = number_format($amount, 2, '.', ',');

        $currency_symbols = array(
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'INR' => '₹',
            'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'Fr', 'CNY' => '¥', 'SEK' => 'kr',
            'NZD' => 'NZ$', 'MXN' => '$', 'SGD' => 'S$', 'HKD' => 'HK$', 'NOK' => 'kr',
            'KRW' => '₩', 'TRY' => '₺', 'RUB' => '₽', 'BRL' => 'R$', 'ZAR' => 'R'
        );

        $symbol = $currency_symbols[$currency] ?? $currency . ' ';
        return $symbol . $formatted_amount;
    }

    /**
     * Calculate age from date of birth.
     *
     * @since    1.0.0
     * @param    string    $dob           Date of birth in Y-m-d format.
     * @return   int                       The calculated age.
     */
    public static function calculate_age(string $dob): int {
        if (empty($dob) || !strtotime($dob)) {
            return 0;
        }

        $birth_date = new DateTime($dob);
        $today = new DateTime();
        // Ensure the birthday has occurred this year
        if ($today->format('m-d') < $birth_date->format('m-d')) {
            $today->sub(new DateInterval('P1Y'));
        }
        return $today->diff($birth_date)->y;
    }

    /**
     * Get recent activities from the audit log.
     *
     * @since    1.0.0
     * @param    int       $limit         Optional. Number of activities to return. Default 5.
     * @return   array                    An array of recent activity objects.
     */
    public static function get_recent_activities(int $limit = 5): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_audit_log';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($sql);
    }
}
