<?php
/*
Plugin Name: Hospital Management System (HMS)
Plugin URI: https://example.com/hms/
Description: A HMS with integrated workflows for patients, appointments, billing, clinical records, pharmacy, and reporting.
Version: 1.0
Author: HMS
Author URI: https://example.com/
Text Domain: hospital-management-system
Domain Path: /languages
Network: false
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HMS_VERSION', '1.0');
define('HMS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HMS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('HMS_TEXT_DOMAIN', 'hospital-management-system'); // Added for internationalization

// Core plugin files - ensure they exist before including
$core_files = array(
    'includes/class-hms-activator.php',
    'includes/class-hms-deactivator.php',
    'includes/class-hms-loader.php',
    'includes/class-hms-i18n.php',
    'admin/class-hms-admin.php',
    'public/class-hms-public.php',
    'includes/class-hms-helper.php', // Helper class is used by activator and other parts
    'includes/class-hms-patient.php', // Patient management class
    'includes/class-hms-medical-records.php', // Medical records management class
    'includes/class-hms-license-manager.php', // License Manager for premium features
    'includes/class-hms-custom-fields.php', // Custom Fields & Forms
    'includes/class-hms-white-label.php', // White-Label Branding
    'includes/class-hms-clinical-features.php', // Basic Clinical Features
    'includes/class-hms-reporting-analytics.php', // Reporting & Analytics
    'includes/class-hms-multi-location.php', // Multi-Location Management
    'includes/class-hms-patient-portal-mobile.php', // Patient Portal & Mobile Experience
    'includes/class-hms-telemedicine.php', // Basic Telemedicine
    'includes/class-hms-migration.php', // Database migration class
    'includes/class-hms-notifications.php', // Notification management class
    'includes/class-hms-integrations.php', // Integration management class
    'includes/class-hms-user-roles.php', // User roles and permissions class
    'includes/class-hms-database-optimizer.php', // Database optimization utilities
);

foreach ($core_files as $file) {
    $path = HMS_PLUGIN_DIR . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        // Optionally, add an admin notice if a core file is missing.
        // This is a critical error, so the plugin might not function correctly.
        // Don't add admin notices during plugin activation to prevent output issues
        if (is_admin() && !defined('DOING_AJAX') && !defined('WP_INSTALLING') && !defined('WP_PLUGIN_ACTIVATING')) {
            add_action('admin_notices', function() use ($file) {
                echo '<div class="notice notice-error"><p>' . sprintf(
                    /* translators: %s: File path */
                    esc_html__('Hospital Management System: Critical error - Core plugin file missing: %s', 'hospital-management-system'),
                    '<code>' . esc_html($file) . '</code>'
                ) . '</p></div>';
            });
        }
    }
}

// Ensure database tables are created, especially if activation hook failed.
// This function now directly creates tables and uses output buffering to prevent "headers already sent".
function hms_ensure_tables_exist() {
    // Don't run this function during plugin activation to prevent conflicts
    if (defined('WP_PLUGIN_ACTIVATING') && WP_PLUGIN_ACTIVATING) {
        return;
    }
    
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix;

    // Check for a core table to determine if setup is needed
    $core_table = $table_prefix . 'hms_patients';
    // Suppress errors during table existence check
    $suppress_errors = $wpdb->suppress_errors();
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$core_table'") == $core_table;
    $wpdb->suppress_errors($suppress_errors);

    if (!$table_exists) {
        // Start output buffering to catch any stray output
        ob_start();

        // We still need upgrade.php for some core WP functions if they were to be used, though not for dbDelta here.
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Suppress all database errors during table creation to prevent unwanted output
        $wpdb->suppress_errors(true);
        $wpdb->show_errors(false);

        // Patients Table
        $table_name = $table_prefix . 'hms_patients';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            patient_id varchar(50) NOT NULL UNIQUE,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            date_of_birth date DEFAULT NULL,
            gender varchar(20) DEFAULT NULL,
            blood_group varchar(10) DEFAULT NULL,
            contact_number varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            address text DEFAULT NULL,
            emergency_contact_name varchar(100) DEFAULT NULL,
            emergency_contact_number varchar(20) DEFAULT NULL,
            national_id varchar(100) DEFAULT NULL,
            deposit_money decimal(10,2) DEFAULT 0.00,
            medical_history text DEFAULT NULL,
            allergies text DEFAULT NULL,
            registration_date datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Appointments Table
        $table_name = $table_prefix . 'hms_appointments';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            doctor_id mediumint(9) NOT NULL,
            department_id mediumint(9) DEFAULT NULL,
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            status varchar(50) DEFAULT 'scheduled',
            reason_for_visit text DEFAULT NULL,
            notes text DEFAULT NULL,
            token_number int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY doctor_id (doctor_id),
            KEY appointment_date (appointment_date),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // Medical Records Table
        $table_name = $table_prefix . 'hms_medical_records';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) DEFAULT NULL,
            doctor_id mediumint(9) NOT NULL,
            record_type varchar(50) DEFAULT NULL,
            diagnosis text DEFAULT NULL,
            symptoms text DEFAULT NULL,
            treatment_plan text DEFAULT NULL,
            notes longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY doctor_id (doctor_id),
            KEY appointment_id (appointment_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Bills Table
        $table_name = $table_prefix . 'hms_bills';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) DEFAULT NULL,
            bill_number varchar(50) NOT NULL UNIQUE,
            bill_type varchar(50) DEFAULT 'OPD',
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            discount_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            tax_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            net_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            amount_paid decimal(10,2) NOT NULL DEFAULT 0.00,
            payment_status varchar(50) DEFAULT 'Unpaid',
            insurance_claim_id mediumint(9) DEFAULT NULL,
            created_by mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY appointment_id (appointment_id),
            KEY payment_status (payment_status)
        ) $charset_collate;";
        dbDelta($sql);

        // Bill Items Table
        $table_name = $table_prefix . 'hms_bill_items';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bill_id mediumint(9) NOT NULL,
            item_type varchar(50) NOT NULL,
            item_id mediumint(9) DEFAULT NULL,
            description varchar(255) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            unit_price decimal(10,2) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY bill_id (bill_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Payments Table
        $table_name = $table_prefix . 'hms_payments';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bill_id mediumint(9) NOT NULL,
            payment_method varchar(50) NOT NULL,
            transaction_id varchar(100) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            payment_date datetime DEFAULT CURRENT_TIMESTAMP,
            received_by mediumint(9) NOT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY bill_id (bill_id),
            KEY payment_date (payment_date)
        ) $charset_collate;";
        dbDelta($sql);

        // Prescriptions Table
        $table_name = $table_prefix . 'hms_prescriptions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            prescription_number varchar(50) DEFAULT NULL,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) DEFAULT NULL,
            doctor_id mediumint(9) NOT NULL,
            issue_date date DEFAULT NULL,
            diagnosis text DEFAULT NULL,
            status varchar(50) DEFAULT 'active',
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY prescription_number (prescription_number),
            KEY patient_id (patient_id),
            KEY appointment_id (appointment_id),
            KEY doctor_id (doctor_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Prescription Items Table
        $table_name = $table_prefix . 'hms_prescription_items';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            prescription_id mediumint(9) NOT NULL,
            medicine_name varchar(255) NOT NULL,
            dosage varchar(100) NOT NULL,
            frequency varchar(100) NOT NULL,
            duration varchar(100) NOT NULL,
            instructions text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY prescription_id (prescription_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Medicines Table
        $table_name = $table_prefix . 'hms_medicines';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            manufacturer varchar(100) DEFAULT NULL,
            unit varchar(50) DEFAULT NULL,
            strength varchar(50) DEFAULT NULL,
            supplier_id mediumint(9) DEFAULT NULL,
            purchase_price decimal(10,2) DEFAULT 0.00,
            selling_price decimal(10,2) DEFAULT 0.00,
            reorder_level int(11) DEFAULT 10,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY category (category)
        ) $charset_collate;";
        dbDelta($sql);

        // Pharmacy Stock Table
        $table_name = $table_prefix . 'hms_pharmacy_stock';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            medicine_id mediumint(9) NOT NULL,
            batch_number varchar(100) DEFAULT NULL,
            expiry_date date DEFAULT NULL,
            quantity_in_stock int(11) NOT NULL DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY medicine_id (medicine_id),
            KEY expiry_date (expiry_date)
        ) $charset_collate;";
        dbDelta($sql);

        // Purchase Orders Table
        $table_name = $table_prefix . 'hms_purchase_orders';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            supplier_id mediumint(9) NOT NULL,
            order_number varchar(50) NOT NULL UNIQUE,
            order_date datetime DEFAULT CURRENT_TIMESTAMP,
            expected_delivery_date date DEFAULT NULL,
            status varchar(50) DEFAULT 'Pending',
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            notes text DEFAULT NULL,
            created_by mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY supplier_id (supplier_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // Staff Table
        $table_name = $table_prefix . 'hms_staff';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            staff_id varchar(50) NOT NULL,
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
            KEY staff_id (staff_id),
            KEY role (role),
            KEY department (department),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // Departments Table
        $table_name = $table_prefix . 'hms_departments';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL UNIQUE,
            description text DEFAULT NULL,
            head_of_department_id mediumint(9) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY head_of_department_id (head_of_department_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Services Table
        $table_name = $table_prefix . 'hms_services';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            department_id mediumint(9) DEFAULT NULL,
            fee decimal(10,2) NOT NULL DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY category (category),
            KEY department_id (department_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Insurance Providers Table
        $table_name = $table_prefix . 'hms_insurance_providers';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL UNIQUE,
            contact_person varchar(100) DEFAULT NULL,
            contact_number varchar(20) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            address text DEFAULT NULL,
            tpa_name varchar(255) DEFAULT NULL,
            api_details text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);

        // Insurance Claims Table
        $table_name = $table_prefix . 'hms_claims';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            bill_id mediumint(9) NOT NULL,
            insurance_provider_id mediumint(9) NOT NULL,
            policy_number varchar(100) DEFAULT NULL,
            claim_number varchar(100) DEFAULT NULL UNIQUE,
            claim_amount decimal(10,2) NOT NULL,
            approved_amount decimal(10,2) DEFAULT NULL,
            status varchar(50) DEFAULT 'Pending',
            pre_authorization_id varchar(100) DEFAULT NULL,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            processed_date datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY bill_id (bill_id),
            KEY insurance_provider_id (insurance_provider_id),
            KEY claim_number (claim_number),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // OPD Queue Table
        $table_name = $table_prefix . 'hms_queue';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) DEFAULT NULL,
            doctor_id mediumint(9) NOT NULL,
            token_number varchar(20) NOT NULL,
            status varchar(50) DEFAULT 'waiting',
            queue_date date NOT NULL,
            estimated_time datetime DEFAULT NULL,
            called_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY token_date_doctor (token_number, queue_date, doctor_id),
            KEY patient_id (patient_id),
            KEY appointment_id (appointment_id),
            KEY doctor_id (doctor_id),
            KEY status (status),
            KEY queue_date (queue_date)
        ) $charset_collate;";
        dbDelta($sql);

        // Audit Log Table
        $table_name = $table_prefix . 'hms_audit_log';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            action varchar(255) NOT NULL,
            object_type varchar(50) DEFAULT NULL,
            object_id mediumint(9) DEFAULT NULL,
            details longtext DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY object_id (object_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);

        // Restore error reporting settings
        $wpdb->suppress_errors(false);
        $wpdb->show_errors(true);

        // After creating tables, ensure roles and default options are set.
        // Call these methods statically to avoid instantiating the activator class if not needed.
        if (class_exists('HMS_Activator') && method_exists('HMS_Activator', 'create_roles')) {
            HMS_Activator::create_roles();
        }
        if (class_exists('HMS_Activator') && method_exists('HMS_Activator', 'set_default_options')) {
            HMS_Activator::set_default_options();
        }
        
        flush_rewrite_rules(); // Good practice after major changes.

        // Clean the output buffer and discard its contents
        ob_end_clean();
    }
    
    // Always check if the staff table exists specifically, regardless of the core table
    hms_ensure_staff_table_exists();
}
// Run this check on every plugin load to be absolutely sure.
// We use 'plugins_loaded' because it's after all plugins are loaded but before most other hooks.
add_action('plugins_loaded', 'hms_ensure_tables_exist', 20); // Increased priority to ensure it runs after other plugins' initializations.

/**
 * Ensure the staff table exists with the correct structure.
 * This function specifically checks and creates the staff table.
 */
function hms_ensure_staff_table_exists() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix;
    
    // Check if the staff table exists
    $table_name = $table_prefix . 'hms_staff';
    $suppress_errors = $wpdb->suppress_errors();
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    $wpdb->suppress_errors($suppress_errors);
    
    if (!$table_exists) {
        // Start output buffering to catch any stray output
        ob_start();
        
        // Create the staff table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            staff_id varchar(50) NOT NULL,
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
            KEY staff_id (staff_id),
            KEY role (role),
            KEY department (department),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->suppress_errors(true);
        $wpdb->show_errors(false);
        dbDelta($sql);
        $wpdb->suppress_errors(false);
        $wpdb->show_errors(true);
        
        // Clean the output buffer and discard its contents
        ob_end_clean();
        
        return true;
    }
    
    return false;
}

/**
 * Run database migrations to ensure the database schema is up to date.
 */
function hms_run_migrations() {
    // Ensure the migration class is loaded
    if (!class_exists('HMS_Migration')) {
        require_once HMS_PLUGIN_DIR . 'includes/class-hms-migration.php';
    }
    
    if (class_exists('HMS_Migration')) {
        HMS_Migration::run_migrations();
    }
}
// Run migrations after ensuring tables exist
add_action('plugins_loaded', 'hms_run_migrations', 30); // Run after table creation

/**
 * Ensure the administrator role has the necessary HMS capabilities.
 * This function is called on every plugin load to ensure capabilities are set.
 */
function hms_ensure_admin_capabilities() {
    // Force refresh the role cache to ensure we have the latest capabilities
    wp_roles()->for_site();
    
    // Get the administrator role
    $role = get_role('administrator');

    // If the role exists, add our custom capabilities
    if ($role) {
        // General management capabilities
        $role->add_cap('manage_hms', true);
        $role->add_cap('manage_patients', true);
        $role->add_cap('manage_billing', true);
        $role->add_cap('manage_clinical', true);
        $role->add_cap('manage_pharmacy', true);
        $role->add_cap('manage_reports', true);
        $role->add_cap('view_sensitive_data', true);
        
        // Force save the role
        wp_roles()->for_site();
    }
}
// Run this check on every plugin load to ensure capabilities are set.
add_action('plugins_loaded', 'hms_ensure_admin_capabilities', 15); // Run before table creation but after plugin initialization.

// Ensure the staff table exists specifically
add_action('plugins_loaded', 'hms_ensure_staff_table_exists', 25); // Run after general table creation

/**
 * The core plugin class
 */
class HMS {

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $instances = array();

    public function __construct() {
        $this->plugin_name = 'hospital-management-system';
        $this->version = HMS_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        
        // Ensure admin capabilities are set early in the loading process
        add_action('init', array($this, 'ensure_admin_capabilities_early'), 1);
        
        $this->instantiate_classes(); // Instantiate classes that register AJAX actions
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_shortcodes();
    }

    /**
     * Ensure the default Administrator role has all necessary HMS capabilities.
     * This is called early in the init process to ensure permissions are set.
     */
    public function ensure_admin_capabilities_early() {
        $this->ensure_admin_capabilities();
    }

    /**
     * Ensure the default Administrator role has all necessary HMS capabilities.
     * This is called during plugin construction to ensure permissions are set.
     */
    private function ensure_admin_capabilities() {
        // Force refresh the role cache to ensure we have the latest capabilities
        wp_roles()->for_site();
        
        // Get the administrator role
        $role = get_role('administrator');

        // If the role exists, add our custom capabilities
        if ($role) {
            // General management capabilities
            $role->add_cap('manage_hms', true);
            $role->add_cap('manage_patients', true);
            $role->add_cap('manage_billing', true);
            $role->add_cap('manage_clinical', true);
            $role->add_cap('manage_pharmacy', true);
            $role->add_cap('manage_reports', true);
            $role->add_cap('view_sensitive_data', true);
            
            // Patient CPT capabilities
            $role->add_cap('edit_hms_patient', true);
            $role->add_cap('read_hms_patient', true);
            $role->add_cap('delete_hms_patient', true);
            $role->add_cap('edit_hms_patients', true);
            $role->add_cap('edit_others_hms_patients', true);
            $role->add_cap('publish_hms_patients', true);
            $role->add_cap('read_private_hms_patients', true);
            $role->add_cap('delete_hms_patients', true);
            $role->add_cap('delete_private_hms_patients', true);
            $role->add_cap('delete_published_hms_patients', true);
            $role->add_cap('delete_others_hms_patients', true);
            $role->add_cap('edit_private_hms_patients', true);
            $role->add_cap('edit_published_hms_patients', true);
            
            // Appointment CPT capabilities
            $role->add_cap('edit_hms_appointment', true);
            $role->add_cap('read_hms_appointment', true);
            $role->add_cap('delete_hms_appointment', true);
            $role->add_cap('edit_hms_appointments', true);
            $role->add_cap('edit_others_hms_appointments', true);
            $role->add_cap('publish_hms_appointments', true);
            $role->add_cap('read_private_hms_appointments', true);
            $role->add_cap('delete_hms_appointments', true);
            $role->add_cap('delete_private_hms_appointments', true);
            $role->add_cap('delete_published_hms_appointments', true);
            $role->add_cap('delete_others_hms_appointments', true);
            $role->add_cap('edit_private_hms_appointments', true);
            $role->add_cap('edit_published_hms_appointments', true);
            
            // Bill CPT capabilities
            $role->add_cap('edit_hms_bill', true);
            $role->add_cap('read_hms_bill', true);
            $role->add_cap('delete_hms_bill', true);
            $role->add_cap('edit_hms_bills', true);
            $role->add_cap('edit_others_hms_bills', true);
            $role->add_cap('publish_hms_bills', true);
            $role->add_cap('read_private_hms_bills', true);
            $role->add_cap('delete_hms_bills', true);
            $role->add_cap('delete_private_hms_bills', true);
            $role->add_cap('delete_published_hms_bills', true);
            $role->add_cap('delete_others_hms_bills', true);
            $role->add_cap('edit_private_hms_bills', true);
            $role->add_cap('edit_published_hms_bills', true);
            
            // Prescription CPT capabilities
            $role->add_cap('edit_hms_prescription', true);
            $role->add_cap('read_hms_prescription', true);
            $role->add_cap('delete_hms_prescription', true);
            $role->add_cap('edit_hms_prescriptions', true);
            $role->add_cap('edit_others_hms_prescriptions', true);
            $role->add_cap('publish_hms_prescriptions', true);
            $role->add_cap('read_private_hms_prescriptions', true);
            $role->add_cap('delete_hms_prescriptions', true);
            $role->add_cap('delete_private_hms_prescriptions', true);
            $role->add_cap('delete_published_hms_prescriptions', true);
            $role->add_cap('delete_others_hms_prescriptions', true);
            $role->add_cap('edit_private_hms_prescriptions', true);
            $role->add_cap('edit_published_hms_prescriptions', true);
            
            // Staff CPT capabilities
            $role->add_cap('edit_hms_staff', true);
            $role->add_cap('read_hms_staff', true);
            $role->add_cap('delete_hms_staff', true);
            $role->add_cap('edit_hms_staffs', true);
            $role->add_cap('edit_others_hms_staffs', true);
            $role->add_cap('publish_hms_staffs', true);
            $role->add_cap('read_private_hms_staffs', true);
            $role->add_cap('delete_hms_staffs', true);
            $role->add_cap('delete_private_hms_staffs', true);
            $role->add_cap('delete_published_hms_staffs', true);
            $role->add_cap('delete_others_hms_staffs', true);
            $role->add_cap('edit_private_hms_staffs', true);
            $role->add_cap('edit_published_hms_staffs', true);
            
            // Force save the role
            wp_roles()->for_site();
        }
    }

    private function load_dependencies() {
        if (class_exists('HMS_Loader')) {
            $this->loader = new HMS_Loader();
        }
    }

    private function set_locale() {
        if (class_exists('HMS_i18n')) {
            $plugin_i18n = new HMS_i18n();
            if ($this->loader) {
                $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
            }
        }
    }

    private function define_admin_hooks() {
        if (class_exists('HMS_Admin')) {
            $plugin_admin = new HMS_Admin($this->get_plugin_name(), $this->get_version());
            
            if ($this->loader) {
                $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
                $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
                $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
                $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
                $this->loader->add_action('admin_notices', $plugin_admin, 'admin_license_notices');
                $this->loader->add_action('admin_notices', $plugin_admin, 'display_activation_notices');
                
                // AJAX hooks for logged-in users
                $this->loader->add_action('wp_ajax_hms_manage_queue', $plugin_admin, 'handle_manage_queue_ajax');
                $this->loader->add_action('wp_ajax_hms_add_patient_to_queue', $plugin_admin, 'handle_add_patient_to_queue_ajax');
                $this->loader->add_action('wp_ajax_hms_clear_completed_queue', $plugin_admin, 'handle_clear_completed_queue_ajax');
                $this->loader->add_action('wp_ajax_hms_reset_queue', $plugin_admin, 'handle_reset_queue_ajax');
                $this->loader->add_action('wp_ajax_hms_export_queue', $plugin_admin, 'handle_export_queue');
                $this->loader->add_action('wp_ajax_nopriv_hms_export_queue', $plugin_admin, 'handle_export_queue');
                $this->loader->add_action('wp_ajax_hms_add_staff', $plugin_admin, 'ajax_add_staff');
                $this->loader->add_action('wp_ajax_hms_update_staff', $plugin_admin, 'ajax_update_staff');
                
                // Settings AJAX handlers
                $this->loader->add_action('wp_ajax_hms_update_role_capability', $plugin_admin, 'ajax_update_role_capability');
                $this->loader->add_action('wp_ajax_hms_test_service_connection', $plugin_admin, 'ajax_test_service_connection');
                $this->loader->add_action('wp_ajax_hms_reset_settings_defaults', $plugin_admin, 'ajax_reset_settings_defaults');
                $this->loader->add_action('wp_ajax_hms_auto_save_settings', $plugin_admin, 'ajax_auto_save_settings');
                
                // License management AJAX hooks
                if (isset($this->instances['HMS_License_Manager'])) {
                    $this->loader->add_action('wp_ajax_hms_activate_license', $this->instances['HMS_License_Manager'], 'ajax_activate_license');
                    $this->loader->add_action('wp_ajax_hms_deactivate_license', $this->instances['HMS_License_Manager'], 'ajax_deactivate_license');
                }
                
                // Register patient AJAX handlers
                if (class_exists('HMS_Patient')) {
                    $patient_obj = new HMS_Patient();
                    $this->loader->add_action('wp_ajax_hms_get_patients_list', $patient_obj, 'ajax_get_patients_list');
                    $this->loader->add_action('wp_ajax_hms_search_patients', $patient_obj, 'ajax_search_patients');
                    $this->loader->add_action('wp_ajax_hms_update_patient_medical_history', $patient_obj, 'ajax_update_patient_medical_history');
                    $this->loader->add_action('wp_ajax_hms_get_patient_medical_history', $patient_obj, 'ajax_get_patient_medical_history');
                    $this->loader->add_action('wp_ajax_hms_add_medical_history_record', $patient_obj, 'ajax_add_medical_history_record');
                    $this->loader->add_action('wp_ajax_hms_export_patients', $patient_obj, 'ajax_export_patients');
                    $this->loader->add_action('wp_ajax_hms_delete_patient', $patient_obj, 'ajax_delete_patient');
                }
                
                // Register appointments AJAX handlers
                if (class_exists('HMS_Appointments')) {
                    $appointments_obj = new HMS_Appointments();
                    $this->loader->add_action('wp_ajax_hms_get_appointments_list', $appointments_obj, 'ajax_get_appointments_list');
                    $this->loader->add_action('wp_ajax_hms_add_appointment', $appointments_obj, 'ajax_add_appointment');
                    $this->loader->add_action('wp_ajax_hms_update_appointment', $appointments_obj, 'ajax_update_appointment');
                    $this->loader->add_action('wp_ajax_hms_cancel_appointment', $appointments_obj, 'ajax_cancel_appointment');
                    $this->loader->add_action('wp_ajax_hms_delete_appointment', $appointments_obj, 'ajax_delete_appointment');
                    $this->loader->add_action('wp_ajax_hms_get_appointment', $appointments_obj, 'ajax_get_appointment');
                    $this->loader->add_action('wp_ajax_hms_get_patient_appointments', $appointments_obj, 'ajax_get_patient_appointments');
                }
                
                // Register medical records AJAX handlers
                if (class_exists('HMS_Medical_Records')) {
                    $medical_records_obj = new HMS_Medical_Records();
                    $this->loader->add_action('wp_ajax_hms_add_medical_record', $medical_records_obj, 'ajax_add_medical_record');
                    $this->loader->add_action('wp_ajax_hms_update_medical_record', $medical_records_obj, 'ajax_update_medical_record');
                    $this->loader->add_action('wp_ajax_hms_get_medical_record', $medical_records_obj, 'ajax_get_medical_record');
                    $this->loader->add_action('wp_ajax_hms_delete_medical_record', $medical_records_obj, 'ajax_delete_medical_record');
                    $this->loader->add_action('wp_ajax_hms_search_medical_records', $medical_records_obj, 'ajax_search_medical_records');
                    $this->loader->add_action('wp_ajax_hms_load_more_medical_records', $medical_records_obj, 'ajax_load_more_medical_records');
                }
            }
        }
        
        // Add license manager notice
        if (isset($this->instances['HMS_License_Manager']) && $this->loader) {
            $this->loader->add_action('admin_notices', $this->instances['HMS_License_Manager'], 'admin_license_notices');
        }
    }

    private function define_public_hooks() {
        if (class_exists('HMS_Public')) {
            $plugin_public = new HMS_Public($this->get_plugin_name(), $this->get_version());
            if ($this->loader) {
                $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
                $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
                $this->loader->add_action('init', $plugin_public, 'register_custom_post_types');
                $this->loader->add_action('template_redirect', $plugin_public, 'restrict_hms_cpt_access');
            }
        }
    }


    private function define_shortcodes() {
        // Register shortcodes for patient portal if the class exists
        if (class_exists('HMS_Patient_Portal')) {
            add_shortcode('hms_patient_portal', array('HMS_Patient_Portal', 'patient_portal_shortcode'));
            add_shortcode('hms_appointment_booking', array('HMS_Patient_Portal', 'appointment_booking_shortcode'));
            add_shortcode('hms_bill_payment', array('HMS_Patient_Portal', 'bill_payment_shortcode'));
        }
        
        // Register telemedicine shortcode if the class exists
        if (class_exists('HMS_Telemedicine')) {
            add_shortcode('hms_telemedicine', array('HMS_Telemedicine', 'telemedicine_shortcode'));
        }
    }


    public function run() {
        if ($this->loader) {
            $this->loader->run();
        }
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    /**
     * Instantiate classes that register AJAX actions in their constructors
     */
    private function instantiate_classes() {
        // Instantiate classes only if they exist to avoid fatal errors
        $classes_to_instantiate = array(
            'HMS_Patient',
            'HMS_Billing',
            'HMS_Appointments', // Corrected from HMS_Clinical
            'HMS_Prescription', // Instantiate the prescription manager
            'HMS_Pharmacy',
            'HMS_Staff', // Corrected from HMS_Admin_HR
            'HMS_Insurance',
            'HMS_Reports',
            'HMS_Security',
            'HMS_License_Manager', // Instantiate the license manager
            'HMS_Custom_Fields', // Instantiate the custom fields manager
            'HMS_White_Label', // Instantiate the white-label branding manager
            'HMS_Clinical_Features', // Instantiate the basic clinical features manager
            'HMS_Reporting_Analytics', // Instantiate the reporting & analytics manager
            'HMS_Multi_Location', // Instantiate the multi-location manager
            'HMS_Patient_Portal_Mobile', // Instantiate the patient portal & mobile experience manager
            'HMS_Telemedicine', // Instantiate the basic telemedicine manager
        );

        foreach ($classes_to_instantiate as $class_name) {
            if (class_exists($class_name)) {
                $ref = new ReflectionClass($class_name);
                
                // Special handling for classes that require constructor parameters
                if ($class_name === 'HMS_Custom_Fields' ||
                    $class_name === 'HMS_White_Label' ||
                    $class_name === 'HMS_Clinical_Features' ||
                    $class_name === 'HMS_Reporting_Analytics' ||
                    $class_name === 'HMS_Telemedicine' ||
                    $class_name === 'HMS_Patient_Portal_Mobile' ||
                    $class_name === 'HMS_Multi_Location') {
                    // These classes require plugin_name and version parameters
                    $this->instances[$class_name] = $ref->newInstance($this->plugin_name, $this->version);
                } else {
                    // For other classes, use the default constructor
                    $this->instances[$class_name] = $ref->newInstance();
                }
            }
        }
    }

    /**
     * Encrypt patient medical history before saving to the database.
     *
     * @param null|bool $check     Whether to allow updating metadata for the given type.
     * @param int      $object_id  Object ID.
     * @param string   $meta_key   Meta key.
     * @param mixed    $meta_value Meta value. Will be serialized if non-scalar.
     * @return null|bool
     */
    public function encrypt_medical_history_on_save($check, $object_id, $meta_key, $meta_value) {
        // Check if the meta key is for medical history and the post type is hms_patient
        if ($meta_key === '_medical_history' && get_post_type($object_id) === 'hms_patient') {
            if (class_exists('HMS_Helper') && method_exists('HMS_Helper', 'encrypt_data')) {
                $encrypted_value = HMS_Helper::encrypt_data($meta_value);
                if ($encrypted_value !== false) {
                    // Return the encrypted value to be saved
                    return $encrypted_value;
                }
            }
        }
        // Return the original check value for all other cases
        return $check;
    }

    /**
     * Decrypt patient medical history when retrieving from the database.
     *
     * @param null|array|string $value     The value get_post_meta() should return - a single metadata value,
     *                                     or an array of values.
     * @param int               $object_id  Object ID.
     * @param string            $meta_key   Meta key.
     * @param bool              $single     Whether to return a single value.
     * @return null|array|string
     */
    public function decrypt_medical_history_on_get($value, $object_id, $meta_key, $single) {
        // Check if the meta key is for medical history and the post type is hms_patient
        if ($meta_key === '_medical_history' && get_post_type($object_id) === 'hms_patient') {
            if (class_exists('HMS_Helper') && method_exists('HMS_Helper', 'decrypt_data')) {
                if ($single) {
                    $decrypted_value = HMS_Helper::decrypt_data($value);
                    return $decrypted_value !== false ? $decrypted_value : $value;
                } else {
                    // If $single is false, $value is an array of meta values.
                    // We need to decrypt each one.
                    if (is_array($value)) {
                        $decrypted_values = array();
                        foreach ($value as $val) {
                            $decrypted_val = HMS_Helper::decrypt_data($val);
                            $decrypted_values[] = $decrypted_val !== false ? $decrypted_val : $val;
                        }
                        return $decrypted_values;
                    }
                }
            }
        }
        // Return the original value for all other cases
        return $value;
    }
}

/**
 * Begins execution of the plugin
 */
function hms_run() {
    $plugin = new HMS();
    $plugin->run();
}
hms_run();

// Register activation and deactivation hooks if the classes exist
if (class_exists('HMS_Activator')) {
    // Define a constant to indicate plugin activation
    define('WP_PLUGIN_ACTIVATING', true);
    register_activation_hook(__FILE__, array('HMS_Activator', 'activate'));
}
if (class_exists('HMS_Deactivator')) {
    register_deactivation_hook(__FILE__, array('HMS_Deactivator', 'deactivate'));
}

// Enqueue scripts and styles for the custom fields admin page
function hms_enqueue_custom_fields_admin_assets($hook) {
    // Check if we are on the custom fields admin page
    if ('hospital-management-system_page_hms-custom-fields' !== $hook) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'hms-custom-fields-admin',
        HMS_PLUGIN_URL . 'admin/css/hms-custom-fields.css',
        array(),
        HMS_VERSION
    );

    // Enqueue Handlebars.js for templating
    wp_enqueue_script(
        'handlebars',
        'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js',
        array(),
        '4.7.7',
        true
    );

    // Enqueue custom JS
    wp_enqueue_script(
        'hms-custom-fields-admin',
        HMS_PLUGIN_URL . 'admin/js/hms-custom-fields.js',
        array('jquery', 'handlebars'),
        HMS_VERSION,
        true
    );

    // Localize script to pass PHP variables to JS
    wp_localize_script('hms-custom-fields-admin', 'hms_custom_fields_vars', array(
        'nonce' => wp_create_nonce('hms_custom_fields_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('admin_enqueue_scripts', 'hms_enqueue_custom_fields_admin_assets');

// AJAX handler to get all field groups for the custom fields admin page
function hms_ajax_get_field_groups() {
    check_ajax_referer('hms_custom_fields_nonce', 'nonce');
    if (!current_user_can('manage_hms')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'hospital-management-system')));
    }

    if (class_exists('HMS_Custom_Fields')) {
        $custom_fields_manager = new HMS_Custom_Fields(); // Instantiate to call method
        $field_groups = $custom_fields_manager->get_field_groups();
        // Convert associative array to indexed array for easier JS handling
        $indexed_groups = array_values($field_groups);
        wp_send_json_success($indexed_groups);
    } else {
        wp_send_json_error(array('message' => __('Custom Fields manager not found.', 'hospital-management-system')));
    }
}
add_action('wp_ajax_hms_get_field_groups', 'hms_ajax_get_field_groups');
