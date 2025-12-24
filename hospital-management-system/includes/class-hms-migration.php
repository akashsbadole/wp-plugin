<?php
/**
 * HMS Migration Class
 *
 * Handles database migrations for existing installations.
 */

class HMS_Migration {

    /**
     * Fallback for WordPress __() function if WordPress is not loaded
     */
    private static function __fallback($text, $domain = 'default') {
        // Check if we're in a WordPress admin context and translations are loaded
        if (function_exists('__') && did_action('init')) {
            return __($text, $domain);
        }
        return $text; // Return the text as-is if __() is not available or translations aren't loaded
    }

    /**
     * Run all necessary migrations.
     */
    public static function run_migrations() {
        $messages = array();
        
        $messages[] = self::migrate_appointments_table();
        $messages[] = self::migrate_bills_table();
        $messages[] = self::migrate_prescriptions_table();
        $messages[] = self::migrate_bills_table_bill_id();
        $messages[] = self::migrate_bills_table_payment_method();
        $messages[] = self::migrate_bills_table_payment_status();
        $messages[] = self::migrate_bills_table_status();
        $messages[] = self::migrate_bills_table_insurance_provider();
        $messages[] = self::migrate_bills_table_insurance_covered();
        $messages[] = self::migrate_bills_table_notes();
        $messages[] = self::migrate_bills_table_tax_percentage();
        $messages[] = self::migrate_bills_table_discount_amount();
        $messages[] = self::migrate_bills_table_due_date();
        $messages[] = self::migrate_bill_items_table_price();
        $messages[] = self::migrate_lab_test_orders_table();
        
        return $messages;
    }

    /**
     * Migration for appointments table to add appointment_time column.
     */
    private static function migrate_appointments_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        
        // Check if the appointment_time column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'appointment_time'
        ));
        
        if (empty($column_exists)) {
            // Add the appointment_time column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN appointment_time TIME NOT NULL DEFAULT '00:00:00' AFTER appointment_date");
            
            // Update existing records to extract time from appointment_date if it's a datetime
            $wpdb->query("UPDATE $table_name SET appointment_time = TIME(appointment_date) WHERE appointment_time = '00:00:00'");
            
            // Convert appointment_date from datetime to date
            $wpdb->query("UPDATE $table_name SET appointment_date = DATE(appointment_date)");
            
            // Log the migration
            error_log('HMS Migration: Added appointment_time column to appointments table');
            return self::__fallback('Added appointment_time column to appointments table', 'hospital-management-system');
        }
        
        return self::__fallback('appointment_time column already exists in appointments table', 'hospital-management-system');
    }

    /**
     * Migration for bills table to add issue_date and due_date columns.
     */
    private static function migrate_bills_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the issue_date column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'issue_date'
        ));
        
        $message = '';
        
        if (empty($column_exists)) {
            // Add the issue_date column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN issue_date DATE NOT NULL DEFAULT (CURRENT_DATE) AFTER bill_type");
            
            // Update existing records to set issue_date from created_at
            $wpdb->query("UPDATE $table_name SET issue_date = DATE(created_at) WHERE issue_date IS NULL");
            
            // Log the migration
            error_log('HMS Migration: Added issue_date column to bills table');
            $message = self::__fallback('Added issue_date column to bills table', 'hospital-management-system');
        } else {
            $message = self::__fallback('issue_date column already exists in bills table', 'hospital-management-system');
        }
        
        // Check if the due_date column exists
        $due_date_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'due_date'
        ));
        
        if (empty($due_date_column_exists)) {
            // Add the due_date column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN due_date DATE NULL AFTER issue_date");
            
            // Set due_date to 7 days after issue_date for existing records
            $wpdb->query("UPDATE $table_name SET due_date = DATE_ADD(issue_date, INTERVAL 7 DAY) WHERE due_date IS NULL");
            
            // Log the migration
            error_log('HMS Migration: Added due_date column to bills table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added due_date column to bills table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('due_date column already exists in bills table', 'hospital-management-system');
        }
        
        return $message;
    }

    /**
     * Migration for prescriptions table to add issue_date column.
     */
    private static function migrate_prescriptions_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_prescriptions';
        
        // Check if the issue_date column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'issue_date'
        ));
        
        $message = '';
        
        if (empty($column_exists)) {
            // Add the issue_date column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN issue_date DATE NOT NULL DEFAULT (CURRENT_DATE) AFTER appointment_id");
            
            // Update existing records to set issue_date from created_at
            $wpdb->query("UPDATE $table_name SET issue_date = DATE(created_at) WHERE issue_date IS NULL");
            
            // Log the migration
            error_log('HMS Migration: Added issue_date column to prescriptions table');
            $message = self::__fallback('Added issue_date column to prescriptions table', 'hospital-management-system');
        } else {
            $message = self::__fallback('issue_date column already exists in prescriptions table', 'hospital-management-system');
        }
        
        // Check if the prescription_number column exists
        $prescription_number_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'prescription_number'
        ));
        
        if (empty($prescription_number_column_exists)) {
            // Add the prescription_number column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN prescription_number VARCHAR(50) NULL AFTER id");
            
            // Generate prescription numbers for existing records
            $prescriptions = $wpdb->get_results("SELECT id, created_at FROM $table_name WHERE prescription_number IS NULL");
            foreach ($prescriptions as $prescription) {
                $prefix = 'RX-' . date('Ym', strtotime($prescription->created_at)) . '-';
                $last_prescription = $wpdb->get_row($wpdb->prepare("SELECT prescription_number FROM $table_name WHERE prescription_number LIKE %s ORDER BY id DESC LIMIT 1", $prefix . '%'));
                
                if ($last_prescription) {
                    $last_number = intval(str_replace($prefix, '', $last_prescription->prescription_number));
                    $new_number = $last_number + 1;
                } else {
                    $new_number = 1;
                }
                
                $prescription_number = $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
                $wpdb->update($table_name, array('prescription_number' => $prescription_number), array('id' => $prescription->id), array('%s'), array('%d'));
            }
            
            // Log the migration
            error_log('HMS Migration: Added prescription_number column to prescriptions table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added prescription_number column to prescriptions table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('prescription_number column already exists in prescriptions table', 'hospital-management-system');
        }
        
        return $message;
    }
    
    /**
     * Migration for bills table to add bill_id column if it doesn't exist.
     */
    private static function migrate_bills_table_bill_id() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the bill_id column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'bill_id'
        ));
        
        $message = '';
        
        if (empty($column_exists)) {
            // Add the bill_id column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN bill_id VARCHAR(50) NULL AFTER id");
            
            // Generate bill IDs for existing records
            $bills = $wpdb->get_results("SELECT id, created_at FROM $table_name WHERE bill_id IS NULL");
            foreach ($bills as $bill) {
                $prefix = 'BIL' . date('Ym', strtotime($bill->created_at)) . '-';
                $last_bill = $wpdb->get_row($wpdb->prepare("SELECT bill_id FROM $table_name WHERE bill_id LIKE %s ORDER BY id DESC LIMIT 1", $prefix . '%'));
                
                if ($last_bill) {
                    $last_number = intval(str_replace($prefix, '', $last_bill->bill_id));
                    $new_number = $last_number + 1;
                } else {
                    $new_number = 1;
                }
                
                $bill_id = $prefix . str_pad($new_number, 4, '0', STR_PAD_LEFT);
                $wpdb->update($table_name, array('bill_id' => $bill_id), array('id' => $bill->id), array('%s'), array('%d'));
            }
            
            // Log the migration
            error_log('HMS Migration: Added bill_id column to bills table');
            $message = self::__fallback('Added bill_id column to bills table', 'hospital-management-system');
        } else {
            $message = self::__fallback('bill_id column already exists in bills table', 'hospital-management-system');
        }
        
        // Check if the tax_amount column exists
        $tax_amount_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'tax_amount'
        ));
        
        if (empty($tax_amount_column_exists)) {
            // Add the tax_amount column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
            
            // Log the migration
            error_log('HMS Migration: Added tax_amount column to bills table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added tax_amount column to bills table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('tax_amount column already exists in bills table', 'hospital-management-system');
        }
        
        // Check if the paid_amount column exists
        $paid_amount_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'paid_amount'
        ));
        
        if (empty($paid_amount_column_exists)) {
            // Add the paid_amount column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tax_amount");
            
            // Log the migration
            error_log('HMS Migration: Added paid_amount column to bills table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added paid_amount column to bills table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('paid_amount column already exists in bills table', 'hospital-management-system');
        }
        
        // Check if the payment_method column exists
        $payment_method_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'payment_method'
        ));
        
        if (empty($payment_method_column_exists)) {
            // Add the payment_method column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN payment_method VARCHAR(50) NULL AFTER paid_amount");
            
            // Log the migration
            error_log('HMS Migration: Added payment_method column to bills table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added payment_method column to bills table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('payment_method column already exists in bills table', 'hospital-management-system');
        }
        
        return $message;
    }
    
    /**
     * Migration for bills table to add payment_method column.
     */
    private static function migrate_bills_table_payment_method() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the payment_method column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'payment_method'
        ));
        
        if (empty($column_exists)) {
            // Add the payment_method column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN payment_method VARCHAR(50) NULL AFTER paid_amount");
            
            // Log the migration
            error_log('HMS Migration: Added payment_method column to bills table');
            return self::__fallback('Added payment_method column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('payment_method column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add payment_status column.
     */
    private static function migrate_bills_table_payment_status() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the payment_status column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'payment_status'
        ));
        
        if (empty($column_exists)) {
            // Add the payment_status column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid' AFTER payment_method");
            
            // Update existing records to set payment_status based on status if status exists
            $status_column_exists = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
                $wpdb->dbname,
                $table_name,
                'status'
            ));
            
            if (!empty($status_column_exists)) {
                $wpdb->query("UPDATE $table_name SET payment_status = status WHERE payment_status = 'unpaid' AND status IS NOT NULL");
            }
            
            // Log the migration
            error_log('HMS Migration: Added payment_status column to bills table');
            return self::__fallback('Added payment_status column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('payment_status column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add status column.
     */
    private static function migrate_bills_table_status() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the status column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'status'
        ));
        
        if (empty($column_exists)) {
            // Add the status column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'unpaid' AFTER payment_status");
            
            // Log the migration
            error_log('HMS Migration: Added status column to bills table');
            return self::__fallback('Added status column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('status column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add insurance_provider_id column.
     */
    private static function migrate_bills_table_insurance_provider() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the insurance_provider_id column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'insurance_provider_id'
        ));
        
        if (empty($column_exists)) {
            // Add the insurance_provider_id column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN insurance_provider_id INT NULL AFTER status");
            
            // Log the migration
            error_log('HMS Migration: Added insurance_provider_id column to bills table');
            return self::__fallback('Added insurance_provider_id column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('insurance_provider_id column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add insurance_covered column.
     */
    private static function migrate_bills_table_insurance_covered() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the insurance_covered column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'insurance_covered'
        ));
        
        if (empty($column_exists)) {
            // Add the insurance_covered column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN insurance_covered DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER insurance_provider_id");
            
            // Log the migration
            error_log('HMS Migration: Added insurance_covered column to bills table');
            return self::__fallback('Added insurance_covered column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('insurance_covered column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add notes column.
     */
    private static function migrate_bills_table_notes() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the notes column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'notes'
        ));
        
        if (empty($column_exists)) {
            // Add the notes column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN notes TEXT NULL AFTER insurance_provider_id");
            
            // Log the migration
            error_log('HMS Migration: Added notes column to bills table');
            return self::__fallback('Added notes column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('notes column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add tax_percentage column.
     */
    private static function migrate_bills_table_tax_percentage() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the tax_percentage column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'tax_percentage'
        ));
        
        if (empty($column_exists)) {
            // Add the tax_percentage column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN tax_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER tax_amount");
            
            // Log the migration
            error_log('HMS Migration: Added tax_percentage column to bills table');
            return self::__fallback('Added tax_percentage column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('tax_percentage column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add discount_amount column.
     */
    private static function migrate_bills_table_discount_amount() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the discount_amount column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'discount_amount'
        ));
        
        if (empty($column_exists)) {
            // Add the discount_amount column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount");
            
            // Log the migration
            error_log('HMS Migration: Added discount_amount column to bills table');
            return self::__fallback('Added discount_amount column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('discount_amount column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for bill_items table to add price column.
     */
    private static function migrate_bill_items_table_price() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bill_items';
        
        // Check if the price column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'price'
        ));
        
        if (empty($column_exists)) {
            // Add the price column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER unit_price");
            
            // Update existing records to set price equal to unit_price
            $wpdb->query("UPDATE $table_name SET price = unit_price WHERE price = 0.00 AND unit_price > 0");
            
            // Log the migration
            error_log('HMS Migration: Added price column to bill_items table');
            return self::__fallback('Added price column to bill_items table', 'hospital-management-system');
        }
        
        return self::__fallback('price column already exists in bill_items table', 'hospital-management-system');
    }
    
    /**
     * Migration for bills table to add due_date column.
     */
    private static function migrate_bills_table_due_date() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        
        // Check if the due_date column exists
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'due_date'
        ));
        
        if (empty($column_exists)) {
            // Add the due_date column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN due_date DATE NULL AFTER notes");
            
            // Log the migration
            error_log('HMS Migration: Added due_date column to bills table');
            return self::__fallback('Added due_date column to bills table', 'hospital-management-system');
        }
        
        return self::__fallback('due_date column already exists in bills table', 'hospital-management-system');
    }
    
    /**
     * Migration for lab_test_orders table to add missing columns.
     */
    private static function migrate_lab_test_orders_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_lab_test_orders';
        
        $message = '';
        
        // Check if the is_critical column exists
        $is_critical_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'is_critical'
        ));
        
        if (empty($is_critical_column_exists)) {
            // Add the is_critical column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN is_critical tinyint(1) NOT NULL DEFAULT 0 AFTER status");
            
            // Log the migration
            error_log('HMS Migration: Added is_critical column to lab_test_orders table');
            $message = self::__fallback('Added is_critical column to lab_test_orders table', 'hospital-management-system');
        } else {
            $message = self::__fallback('is_critical column already exists in lab_test_orders table', 'hospital-management-system');
        }
        
        // Check if the notified column exists
        $notified_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'notified'
        ));
        
        if (empty($notified_column_exists)) {
            // Add the notified column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN notified tinyint(1) NOT NULL DEFAULT 0 AFTER is_critical");
            
            // Log the migration
            error_log('HMS Migration: Added notified column to lab_test_orders table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added notified column to lab_test_orders table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('notified column already exists in lab_test_orders table', 'hospital-management-system');
        }
        
        // Check if the completed_at column exists
        $completed_at_column_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
            $wpdb->dbname,
            $table_name,
            'completed_at'
        ));
        
        if (empty($completed_at_column_exists)) {
            // Add the completed_at column
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN completed_at datetime NULL AFTER notified");
            
            // Update existing completed records to set completed_at from updated_at
            $wpdb->query("UPDATE $table_name SET completed_at = updated_at WHERE status = 'completed' AND completed_at IS NULL");
            
            // Log the migration
            error_log('HMS Migration: Added completed_at column to lab_test_orders table');
            $message .= ($message ? ' ' : '') . self::__fallback('Added completed_at column to lab_test_orders table', 'hospital-management-system');
        } else {
            $message .= ($message ? ' ' : '') . self::__fallback('completed_at column already exists in lab_test_orders table', 'hospital-management-system');
        }
        
        return $message;
    }
}
