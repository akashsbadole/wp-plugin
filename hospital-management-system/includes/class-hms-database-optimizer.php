<?php
/**
 * HMS Database Optimizer Class
 *
 * Provides database optimization utilities including index management,
 * query performance analysis, and maintenance operations.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Database_Optimizer {

    /**
     * Recommended indexes for HMS tables to improve query performance.
     *
     * @since 1.0.0
     * @var array Array of table names and their recommended indexes
     */
    private static $recommended_indexes = array(
        'hms_patients' => array(
            'idx_patient_id' => 'patient_id',
            'idx_email' => 'email',
            'idx_contact_number' => 'contact_number',
            'idx_registration_date' => 'registration_date',
            'idx_name_search' => array('first_name', 'last_name'),
        ),
        'hms_appointments' => array(
            'idx_patient_doctor' => array('patient_id', 'doctor_id'),
            'idx_appointment_date' => 'appointment_date',
            'idx_status' => 'status',
            'idx_department_date' => array('department_id', 'appointment_date'),
        ),
        'hms_bills' => array(
            'idx_patient_id' => 'patient_id',
            'idx_bill_number' => 'bill_number',
            'idx_payment_status' => 'payment_status',
            'idx_created_date' => 'created_at',
        ),
        'hms_prescriptions' => array(
            'idx_patient_doctor' => array('patient_id', 'doctor_id'),
            'idx_prescription_number' => 'prescription_number',
            'idx_issue_date' => 'issue_date',
            'idx_status' => 'status',
        ),
        'hms_medical_records' => array(
            'idx_patient_id' => 'patient_id',
            'idx_doctor_id' => 'doctor_id',
            'idx_appointment_id' => 'appointment_id',
            'idx_created_date' => 'created_at',
        ),
        'hms_pharmacy_dispenses' => array(
            'idx_patient_prescription' => array('patient_id', 'prescription_id'),
            'idx_medicine_id' => 'medicine_id',
            'idx_dispense_date' => 'dispense_date',
        ),
        'hms_queue' => array(
            'idx_doctor_date' => array('doctor_id', 'queue_date'),
            'idx_patient_id' => 'patient_id',
            'idx_status' => 'status',
            'idx_token_date_doctor' => array('token_number', 'queue_date', 'doctor_id'),
        ),
        'hms_audit_log' => array(
            'idx_user_action' => array('user_id', 'action'),
            'idx_object_type_id' => array('object_type', 'object_id'),
            'idx_created_date' => 'created_at',
        ),
    );

    /**
     * Check if recommended indexes exist and create missing ones.
     *
     * @since 1.0.0
     * @param bool $create_missing Whether to create missing indexes automatically
     * @return array Status report of index checks and creations
     */
    public static function check_and_create_indexes($create_missing = false) {
        global $wpdb;
        $results = array();

        foreach (self::$recommended_indexes as $table_name => $indexes) {
            $full_table_name = $wpdb->prefix . $table_name;
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") !== $full_table_name) {
                $results[$table_name] = array(
                    'status' => 'table_not_found',
                    'message' => "Table {$full_table_name} does not exist"
                );
                continue;
            }

            $table_results = array();
            
            foreach ($indexes as $index_name => $columns) {
                $index_exists = self::index_exists($full_table_name, $index_name);
                
                if ($index_exists) {
                    $table_results[$index_name] = array(
                        'status' => 'exists',
                        'columns' => $columns
                    );
                } else {
                    if ($create_missing) {
                        $created = self::create_index($full_table_name, $index_name, $columns);
                        $table_results[$index_name] = array(
                            'status' => $created ? 'created' : 'failed',
                            'columns' => $columns
                        );
                    } else {
                        $table_results[$index_name] = array(
                            'status' => 'missing',
                            'columns' => $columns
                        );
                    }
                }
            }
            
            $results[$table_name] = $table_results;
        }

        return $results;
    }

    /**
     * Check if an index exists on a table.
     *
     * @since 1.0.0
     * @param string $table_name Full table name with prefix
     * @param string $index_name Index name to check
     * @return bool True if index exists, false otherwise
     */
    private static function index_exists($table_name, $index_name) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND INDEX_NAME = %s",
            DB_NAME, $table_name, $index_name
        ));
        
        return $result > 0;
    }

    /**
     * Create an index on a table.
     *
     * @since 1.0.0
     * @param string $table_name Full table name with prefix
     * @param string $index_name Index name
     * @param string|array $columns Column name(s) for the index
     * @return bool True on success, false on failure
     */
    private static function create_index($table_name, $index_name, $columns) {
        global $wpdb;
        
        // Prepare column list
        if (is_array($columns)) {
            $column_list = '`' . implode('`, `', $columns) . '`';
        } else {
            $column_list = "`{$columns}`";
        }
        
        // Create index SQL
        $sql = "CREATE INDEX `{$index_name}` ON `{$table_name}` ({$column_list})";
        
        $result = $wpdb->query($sql);
        
        if ($result === false) {
            HMS_Helper::log_action('Index Creation Failed', 'Database', 0, 
                "Failed to create index {$index_name} on {$table_name}: " . $wpdb->last_error);
            return false;
        }
        
        HMS_Helper::log_action('Index Created', 'Database', 0, 
            "Created index {$index_name} on {$table_name}");
        
        return true;
    }

    /**
     * Analyze query performance and suggest optimizations.
     *
     * @since 1.0.0
     * @param string $query SQL query to analyze
     * @return array Analysis results with suggestions
     */
    public static function analyze_query_performance($query) {
        global $wpdb;
        
        $analysis = array(
            'query' => $query,
            'suggestions' => array(),
            'execution_time' => 0,
            'rows_examined' => 0,
        );
        
        // Execute EXPLAIN on the query
        $explain_query = "EXPLAIN " . $query;
        $start_time = microtime(true);
        $explain_results = $wpdb->get_results($explain_query);
        $end_time = microtime(true);
        
        $analysis['execution_time'] = ($end_time - $start_time) * 1000; // Convert to milliseconds
        
        if ($explain_results) {
            foreach ($explain_results as $row) {
                $analysis['rows_examined'] += intval($row->rows);
                
                // Check for potential issues
                if ($row->type === 'ALL') {
                    $analysis['suggestions'][] = "Full table scan detected on table '{$row->table}'. Consider adding an index.";
                }
                
                if ($row->key === null) {
                    $analysis['suggestions'][] = "No index used for table '{$row->table}'. Consider adding appropriate indexes.";
                }
                
                if (intval($row->rows) > 1000) {
                    $analysis['suggestions'][] = "Large number of rows examined ({$row->rows}) for table '{$row->table}'. Consider query optimization.";
                }
            }
        }
        
        // Performance thresholds
        if ($analysis['execution_time'] > 100) {
            $analysis['suggestions'][] = "Query execution time is high ({$analysis['execution_time']}ms). Consider optimization.";
        }
        
        if ($analysis['rows_examined'] > 10000) {
            $analysis['suggestions'][] = "High number of rows examined ({$analysis['rows_examined']}). Consider adding LIMIT clause or better indexes.";
        }
        
        return $analysis;
    }

    /**
     * Get table statistics for optimization insights.
     *
     * @since 1.0.0
     * @param string $table_name Table name without prefix
     * @return array Table statistics
     */
    public static function get_table_statistics($table_name) {
        global $wpdb;
        
        $full_table_name = $wpdb->prefix . $table_name;
        
        $stats = array(
            'table_name' => $full_table_name,
            'row_count' => 0,
            'data_size' => 0,
            'index_size' => 0,
            'total_size' => 0,
            'indexes' => array(),
        );
        
        // Get table status
        $table_status = $wpdb->get_row($wpdb->prepare(
            "SHOW TABLE STATUS LIKE %s", $full_table_name
        ));
        
        if ($table_status) {
            $stats['row_count'] = intval($table_status->Rows);
            $stats['data_size'] = intval($table_status->Data_length);
            $stats['index_size'] = intval($table_status->Index_length);
            $stats['total_size'] = $stats['data_size'] + $stats['index_size'];
        }
        
        // Get index information
        $indexes = $wpdb->get_results($wpdb->prepare(
            "SHOW INDEX FROM %s", $full_table_name
        ));
        
        foreach ($indexes as $index) {
            if (!isset($stats['indexes'][$index->Key_name])) {
                $stats['indexes'][$index->Key_name] = array(
                    'columns' => array(),
                    'unique' => $index->Non_unique == 0,
                    'type' => $index->Index_type,
                );
            }
            $stats['indexes'][$index->Key_name]['columns'][] = $index->Column_name;
        }
        
        return $stats;
    }

    /**
     * Clean up old audit log entries to maintain performance.
     *
     * @since 1.0.0
     * @param int $days_to_keep Number of days of audit logs to keep (default: 90)
     * @return int Number of rows deleted
     */
    public static function cleanup_audit_logs($days_to_keep = 90) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hms_audit_log';
        $cutoff_date = gmdate('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE created_at < %s",
            $cutoff_date
        ));
        
        if ($deleted !== false) {
            HMS_Helper::log_action('Audit Log Cleanup', 'Database', 0, 
                "Cleaned up {$deleted} audit log entries older than {$days_to_keep} days");
        }
        
        return $deleted !== false ? $deleted : 0;
    }

    /**
     * Optimize all HMS tables.
     *
     * @since 1.0.0
     * @return array Results of optimization operations
     */
    public static function optimize_tables() {
        global $wpdb;
        
        $results = array();
        $hms_tables = array_keys(self::$recommended_indexes);
        
        foreach ($hms_tables as $table_name) {
            $full_table_name = $wpdb->prefix . $table_name;
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") !== $full_table_name) {
                $results[$table_name] = array(
                    'status' => 'table_not_found',
                    'message' => "Table does not exist"
                );
                continue;
            }
            
            // Optimize table
            $optimize_result = $wpdb->query("OPTIMIZE TABLE `{$full_table_name}`");
            
            $results[$table_name] = array(
                'status' => $optimize_result !== false ? 'optimized' : 'failed',
                'message' => $optimize_result !== false ? 'Table optimized successfully' : 'Optimization failed'
            );
        }
        
        return $results;
    }
}