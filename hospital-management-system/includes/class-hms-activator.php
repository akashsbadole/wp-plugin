<?php
// includes/class-hms-activator.php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class HMS_Activator {

    /**
     * Plugin activation handler.
     * Creates database tables, roles, and sets up default data.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Start output buffering to catch any stray output during activation
        ob_start();
        
        try {
            // Create custom roles and capabilities
            self::create_roles();
            
            // Create custom database tables
            self::create_tables();
            
            // Populate default departments and sample data
            self::populate_default_data();
            
            // Run database migrations to ensure the database schema is up to date
            self::run_migrations();
            
            // Set default plugin options
            self::set_default_options();
            
            // Set database version for future migrations
            update_option('hms_db_version', '1.0');
            
            // Flush rewrite rules to ensure CPTs and other permalinks work correctly
            flush_rewrite_rules();
            
            // Set activation success flag
            update_option('hms_activation_success', true);
            
        } catch (Exception $e) {
            // Log any errors but don't output them
            error_log('HMS Activation Error: ' . $e->getMessage());
            // Set activation error flag
            update_option('hms_activation_error', $e->getMessage());
        }
        
        // Clean the output buffer to prevent any stray output from causing "headers already sent" errors
        ob_end_clean();
    }

    /**
     * Create custom user roles and assign capabilities.
     * Primarily, this adds HMS-specific capabilities to the default 'administrator' role
     * and creates the 'hms_patient' role for registered patients.
     */
    private static function create_roles() {
        // Force refresh the role cache to ensure we have the latest capabilities
        wp_roles()->for_site();
        
        // --- 1. Add HMS capabilities to the default Administrator role ---
        $admin_role = get_role('administrator');
        if ($admin_role) {
            // General management capabilities
            $admin_role->add_cap('manage_hms', true);
            $admin_role->add_cap('manage_patients', true);
            $admin_role->add_cap('manage_billing', true);
            $admin_role->add_cap('manage_clinical', true);
            $admin_role->add_cap('manage_pharmacy', true);
            $admin_role->add_cap('manage_reports', true);
            $admin_role->add_cap('export_data', true);
            $admin_role->add_cap('import_data', true);
            $admin_role->add_cap('view_sensitive_data', true);
            
            // Patient CPT capabilities
            $admin_role->add_cap('edit_hms_patient', true);
            $admin_role->add_cap('read_hms_patient', true);
            $admin_role->add_cap('delete_hms_patient', true);
            $admin_role->add_cap('edit_hms_patients', true);
            $admin_role->add_cap('edit_others_hms_patients', true);
            $admin_role->add_cap('publish_hms_patients', true);
            $admin_role->add_cap('read_private_hms_patients', true);
            $admin_role->add_cap('delete_hms_patients', true);
            $admin_role->add_cap('delete_private_hms_patients', true);
            $admin_role->add_cap('delete_published_hms_patients', true);
            $admin_role->add_cap('delete_others_hms_patients', true);
            $admin_role->add_cap('edit_private_hms_patients', true);
            $admin_role->add_cap('edit_published_hms_patients', true);
            
            // Appointment CPT capabilities
            $admin_role->add_cap('edit_hms_appointment', true);
            $admin_role->add_cap('read_hms_appointment', true);
            $admin_role->add_cap('delete_hms_appointment', true);
            $admin_role->add_cap('edit_hms_appointments', true);
            $admin_role->add_cap('edit_others_hms_appointments', true);
            $admin_role->add_cap('publish_hms_appointments', true);
            $admin_role->add_cap('read_private_hms_appointments', true);
            $admin_role->add_cap('delete_hms_appointments', true);
            $admin_role->add_cap('delete_private_hms_appointments', true);
            $admin_role->add_cap('delete_published_hms_appointments', true);
            $admin_role->add_cap('delete_others_hms_appointments', true);
            $admin_role->add_cap('edit_private_hms_appointments', true);
            $admin_role->add_cap('edit_published_hms_appointments', true);
            
            // Bill CPT capabilities
            $admin_role->add_cap('edit_hms_bill', true);
            $admin_role->add_cap('read_hms_bill', true);
            $admin_role->add_cap('delete_hms_bill', true);
            $admin_role->add_cap('edit_hms_bills', true);
            $admin_role->add_cap('edit_others_hms_bills', true);
            $admin_role->add_cap('publish_hms_bills', true);
            $admin_role->add_cap('read_private_hms_bills', true);
            $admin_role->add_cap('delete_hms_bills', true);
            $admin_role->add_cap('delete_private_hms_bills', true);
            $admin_role->add_cap('delete_published_hms_bills', true);
            $admin_role->add_cap('delete_others_hms_bills', true);
            $admin_role->add_cap('edit_private_hms_bills', true);
            $admin_role->add_cap('edit_published_hms_bills', true);
            
            // Prescription CPT capabilities
            $admin_role->add_cap('edit_hms_prescription', true);
            $admin_role->add_cap('read_hms_prescription', true);
            $admin_role->add_cap('delete_hms_prescription', true);
            $admin_role->add_cap('edit_hms_prescriptions', true);
            $admin_role->add_cap('edit_others_hms_prescriptions', true);
            $admin_role->add_cap('publish_hms_prescriptions', true);
            $admin_role->add_cap('read_private_hms_prescriptions', true);
            $admin_role->add_cap('delete_hms_prescriptions', true);
            $admin_role->add_cap('delete_private_hms_prescriptions', true);
            $admin_role->add_cap('delete_published_hms_prescriptions', true);
            $admin_role->add_cap('delete_others_hms_prescriptions', true);
            $admin_role->add_cap('edit_private_hms_prescriptions', true);
            $admin_role->add_cap('edit_published_hms_prescriptions', true);
            
            // Staff CPT capabilities
            $admin_role->add_cap('edit_hms_staff', true);
            $admin_role->add_cap('read_hms_staff', true);
            $admin_role->add_cap('delete_hms_staff', true);
            $admin_role->add_cap('edit_hms_staffs', true);
            $admin_role->add_cap('edit_others_hms_staffs', true);
            $admin_role->add_cap('publish_hms_staffs', true);
            $admin_role->add_cap('read_private_hms_staffs', true);
            $admin_role->add_cap('delete_hms_staffs', true);
            $admin_role->add_cap('delete_private_hms_staffs', true);
            $admin_role->add_cap('delete_published_hms_staffs', true);
            $admin_role->add_cap('delete_others_hms_staffs', true);
            $admin_role->add_cap('edit_private_hms_staffs', true);
            $admin_role->add_cap('edit_published_hms_staffs', true);
        }

        // --- 2. Add custom HMS roles ---
        // The 'hms_patient' role is used for registered patients accessing the portal.
        if (!get_role('hms_patient')) {
            $patient_role = add_role('hms_patient', __('Patient', 'hospital-management-system'));
            if ($patient_role) {
                $patient_role->add_cap('read'); // Basic capability to read content
            }
        }
        
        // Create HMS-specific roles for staff management
        self::create_hms_staff_roles();
        
        // Note: Other conceptual roles like 'hms_doctor', 'hms_nurse', etc., are managed
        // by assigning capabilities to the default WordPress roles (e.g., 'editor', 'author')
        // or by using a membership plugin. This activator focuses on the core 'administrator'
        // and 'hms_patient' roles to keep the setup simple and avoid conflicts.
    }

    /**
     * Create HMS-specific staff roles with appropriate capabilities.
     */
    private static function create_hms_staff_roles() {
        // HMS Doctor role
        if (!get_role('hms_doctor')) {
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
        }

        // HMS Nurse role
        if (!get_role('hms_nurse')) {
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
        }

        // HMS Technician role
        if (!get_role('hms_technician')) {
            add_role('hms_technician', __('HMS Technician', 'hospital-management-system'), array(
                'read' => true,
                'manage_hms' => true,
                'view_patients' => true,
                'view_lab_tests' => true,
                'manage_lab_tests' => true,
                'view_appointments' => true,
            ));
        }

        // HMS Admin role
        if (!get_role('hms_admin')) {
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
                'import_data' => true,
            ));
        }

        // HMS Receptionist role
        if (!get_role('hms_receptionist')) {
            add_role('hms_receptionist', __('HMS Receptionist', 'hospital-management-system'), array(
                'read' => true,
                'manage_hms' => true,
                'manage_patients' => true,
                'view_appointments' => true,
                'edit_appointments' => true,
                'manage_queue' => true,
                'view_billing' => true,
            ));
        }

        // HMS Staff (generic) role
        if (!get_role('hms_staff')) {
            add_role('hms_staff', __('HMS Staff', 'hospital-management-system'), array(
                'read' => true,
                'manage_hms' => true,
                'view_patients' => true,
            ));
        }
        
        // --- Create HMS-specific roles ---
        if (class_exists('HMS_User_Roles')) {
            $user_roles = new HMS_User_Roles();
            $user_roles->create_roles();
        }
    }

    /**
     * Create all necessary custom database tables for the plugin.
     * Uses dbDelta() for safe table creation and updates.
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix;

        $sql = [];
        
        // Suppress database errors to prevent unwanted output during table creation
        $wpdb->suppress_errors(true);
        $wpdb->show_errors(false);

        // Patients Table
        $table_name = $table_prefix . 'hms_patients';
        $sql[] = "CREATE TABLE $table_name (
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
            KEY patient_id (patient_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Appointments Table
        $table_name = $table_prefix . 'hms_appointments';
        $sql[] = "CREATE TABLE $table_name (
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

        // Medical Records Table
        $table_name = $table_prefix . 'hms_medical_records';
        $sql[] = "CREATE TABLE $table_name (
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

        // Patient Vitals Table
        $table_name = $table_prefix . 'hms_patient_vitals';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            temperature decimal(5,2) DEFAULT NULL,
            blood_pressure_systolic mediumint(9) DEFAULT NULL,
            blood_pressure_diastolic mediumint(9) DEFAULT NULL,
            pulse mediumint(9) DEFAULT NULL,
            respiratory_rate mediumint(9) DEFAULT NULL,
            spo2 mediumint(9) DEFAULT NULL,
            blood_sugar decimal(6,2) DEFAULT NULL,
            notes text DEFAULT NULL,
            recorded_by mediumint(9) NOT NULL,
            recorded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY recorded_at (recorded_at)
        ) $charset_collate;";

        // Medication Logs Table
        $table_name = $table_prefix . 'hms_medication_logs';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            medicine_id mediumint(9) NOT NULL,
            dosage varchar(100) NOT NULL,
            administered_by mediumint(9) NOT NULL,
            administered_at datetime DEFAULT CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY medicine_id (medicine_id),
            KEY administered_at (administered_at)
        ) $charset_collate;";

        // Bills Table
        $table_name = $table_prefix . 'hms_bills';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bill_id varchar(50) DEFAULT NULL,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) DEFAULT NULL,
            bill_number varchar(50) NOT NULL UNIQUE,
            bill_type varchar(50) DEFAULT 'OPD',
            issue_date date NOT NULL DEFAULT (CURRENT_DATE),
            due_date date NULL,
            total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            discount_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            tax_percentage decimal(5,2) NOT NULL DEFAULT 0.00,
            tax_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            net_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            paid_amount decimal(10,2) NOT NULL DEFAULT 0.00,
            payment_status varchar(50) DEFAULT 'Unpaid',
            insurance_claim_id mediumint(9) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_by mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY appointment_id (appointment_id),
            KEY bill_number (bill_number),
            KEY bill_id (bill_id),
            KEY payment_status (payment_status)
        ) $charset_collate;";

        // Bill Items Table
        $table_name = $table_prefix . 'hms_bill_items';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            bill_id mediumint(9) NOT NULL,
            item_type varchar(50) NOT NULL,
            item_id mediumint(9) DEFAULT NULL,
            description varchar(255) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            unit_price decimal(10,2) NOT NULL,
            price decimal(10,2) NOT NULL,
            total_price decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY bill_id (bill_id)
        ) $charset_collate;";

        // Payments Table
        $table_name = $table_prefix . 'hms_payments';
        $sql[] = "CREATE TABLE $table_name (
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

        // Prescriptions Table
        $table_name = $table_prefix . 'hms_prescriptions';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            prescription_number varchar(50) DEFAULT NULL,
            patient_id mediumint(9) NOT NULL,
            appointment_id mediumint(9) NOT NULL,
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
        
        // Prescription Items Table
        $table_name = $table_prefix . 'hms_prescription_items';
        $sql[] = "CREATE TABLE $table_name (
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

        // Medicines Table
        $table_name = $table_prefix . 'hms_medicines';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            generic_name varchar(255) DEFAULT NULL,
            description text DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            manufacturer varchar(100) DEFAULT NULL,
            unit varchar(50) DEFAULT NULL,
            strength varchar(50) DEFAULT NULL,
            supplier_id mediumint(9) DEFAULT NULL,
            stock_quantity int(11) DEFAULT 0,
            reorder_level int(11) DEFAULT 10,
            unit_price decimal(10,2) DEFAULT 0.00,
            selling_price decimal(10,2) DEFAULT 0.00,
            batch_number varchar(100) DEFAULT NULL,
            expiry_date date DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY category (category),
            KEY supplier_id (supplier_id),
            KEY status (status)
        ) $charset_collate;";

        // Pharmacy Stock Table
        $table_name = $table_prefix . 'hms_pharmacy_stock';
        $sql[] = "CREATE TABLE $table_name (
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

        // Pharmacy Dispenses Table
        $table_name = $table_prefix . 'hms_pharmacy_dispenses';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            prescription_id mediumint(9) DEFAULT NULL,
            medicine_id mediumint(9) NOT NULL,
            quantity_dispensed int(11) NOT NULL,
            unit_price decimal(10,2) NOT NULL DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            dispense_date datetime DEFAULT CURRENT_TIMESTAMP,
            dispensed_by mediumint(9) NOT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY prescription_id (prescription_id),
            KEY medicine_id (medicine_id),
            KEY dispense_date (dispense_date)
        ) $charset_collate;";

        // Suppliers Table
        $table_name = $table_prefix . 'hms_suppliers';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            contact_person varchar(255) DEFAULT NULL,
            email varchar(100) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            address text DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(100) DEFAULT NULL,
            postal_code varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            tax_number varchar(50) DEFAULT NULL,
            payment_terms varchar(100) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY name (name),
            KEY status (status)
        ) $charset_collate;";

        // Purchase Orders Table
        $table_name = $table_prefix . 'hms_purchase_orders';
        $sql[] = "CREATE TABLE $table_name (
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
            KEY order_number (order_number),
            KEY status (status)
        ) $charset_collate;";

        // Purchase Order Items Table
        $table_name = $table_prefix . 'hms_purchase_order_items';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            purchase_order_id mediumint(9) NOT NULL,
            medicine_id mediumint(9) NOT NULL,
            quantity_ordered int(11) NOT NULL,
            quantity_received int(11) DEFAULT 0,
            unit_price decimal(10,2) NOT NULL DEFAULT 0.00,
            total_price decimal(10,2) NOT NULL DEFAULT 0.00,
            batch_number varchar(100) DEFAULT NULL,
            expiry_date date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY purchase_order_id (purchase_order_id),
            KEY medicine_id (medicine_id)
        ) $charset_collate;";

        // Staff Table
        $table_name = $table_prefix . 'hms_staff';
        $sql[] = "CREATE TABLE $table_name (
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

        // Departments Table
        $table_name = $table_prefix . 'hms_departments';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL UNIQUE,
            description text DEFAULT NULL,
            head_of_department_id mediumint(9) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY head_of_department_id (head_of_department_id)
        ) $charset_collate;";

        // Services Table
        $table_name = $table_prefix . 'hms_services';
        $sql[] = "CREATE TABLE $table_name (
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

        // Insurance Providers Table
        $table_name = $table_prefix . 'hms_insurance_providers';
        $sql[] = "CREATE TABLE $table_name (
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
            PRIMARY KEY  (id),
            KEY name (name)
        ) $charset_collate;";

        // Insurance Claims Table
        $table_name = $table_prefix . 'hms_claims';
        $sql[] = "CREATE TABLE $table_name (
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

        // OPD Queue Table
        $table_name = $table_prefix . 'hms_queue';
        $sql[] = "CREATE TABLE $table_name (
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

        // Audit Log Table
        $table_name = $table_prefix . 'hms_audit_log';
        $sql[] = "CREATE TABLE $table_name (
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

        // Notification Log Table
        $table_name = $table_prefix . 'hms_notification_log';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type varchar(20) NOT NULL,
            recipient varchar(255) NOT NULL,
            message text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            error_message text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            sent_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY type (type),
            KEY status (status),
            KEY created_at (created_at),
            KEY recipient (recipient)
        ) $charset_collate;";

        // Patient Consents Table
        $table_name = $table_prefix . 'hms_patient_consents';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            consent_type varchar(100) NOT NULL,
            consent_text longtext DEFAULT NULL,
            status varchar(50) DEFAULT 'active',
            given_at datetime DEFAULT CURRENT_TIMESTAMP,
            given_by_user_id bigint(20) unsigned NOT NULL,
            revoked_at datetime DEFAULT NULL,
            revoked_by_user_id bigint(20) unsigned DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY consent_type (consent_type),
            KEY status (status),
            KEY given_at (given_at)
        ) $charset_collate;";

        // OT Schedules Table
        $table_name = $table_prefix . 'hms_ot_schedules';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            doctor_id mediumint(9) DEFAULT NULL,
            ot_id mediumint(9) DEFAULT NULL,
            surgery_type varchar(255) NOT NULL,
            scheduled_date date NOT NULL,
            scheduled_time time NOT NULL,
            duration int(11) DEFAULT NULL,
            status varchar(50) DEFAULT 'scheduled',
            notes text DEFAULT NULL,
            scheduled_by mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY doctor_id (doctor_id),
            KEY ot_id (ot_id),
            KEY scheduled_date (scheduled_date),
            KEY status (status)
        ) $charset_collate;";

        // OT Consumables Table
        $table_name = $table_prefix . 'hms_ot_consumables';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            surgery_id mediumint(9) NOT NULL,
            item_name varchar(255) NOT NULL,
            quantity_used int(11) NOT NULL,
            notes text DEFAULT NULL,
            logged_by mediumint(9) NOT NULL,
            logged_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY surgery_id (surgery_id)
        ) $charset_collate;";

        // Lab Tests Table
        $table_name = $table_prefix . 'hms_lab_tests';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            test_name varchar(255) NOT NULL,
            test_code varchar(50) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            normal_range varchar(255) DEFAULT NULL,
            unit varchar(50) DEFAULT NULL,
            cost decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY test_name (test_name),
            KEY category (category)
        ) $charset_collate;";

        // Lab Test Orders Table
        $table_name = $table_prefix . 'hms_lab_test_orders';
        $sql[] = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            doctor_id mediumint(9) NOT NULL,
            test_id mediumint(9) NOT NULL,
            order_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'pending',
            results text DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id),
            KEY doctor_id (doctor_id),
            KEY test_id (test_id),
            KEY status (status)
        ) $charset_collate;";

        // Include the necessary WordPress file for dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Include WordPress upgrade functions
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute each table creation query using dbDelta
        foreach ($sql as $query) {
            $result = dbDelta($query);
            
            // Log any issues with table creation
            if (empty($result)) {
                error_log('HMS Activation: Failed to create table with query: ' . $query);
            }
        }
        
        // Restore database error reporting settings
        $wpdb->suppress_errors(false);
        $wpdb->show_errors(true);
    }

    /**
     * Insert default departments if they don't exist.
     */
    private static function insert_default_departments() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_departments';
        
        // Check if departments already exist
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($existing_count > 0) {
            return; // Departments already exist
        }
        
        $default_departments = array(
            'Cardiology' => 'Heart-related issues and cardiovascular diseases',
            'Neurology' => 'Brain and nervous system disorders',
            'Oncology' => 'Cancer treatment and care',
            'Pediatrics' => 'Child health and medical care',
            'Orthopedics' => 'Musculoskeletal system disorders',
            'Dermatology' => 'Skin conditions and diseases',
            'Gastroenterology' => 'Digestive system disorders',
            'Nephrology' => 'Kidney health and diseases',
            'Endocrinology' => 'Hormone-related issues and disorders',
            'Psychiatry' => 'Mental health and psychiatric disorders',
            'Ophthalmology' => 'Eye care and vision problems',
            'ENT' => 'Ear, Nose, and Throat disorders',
            'Urology' => 'Urinary system and male reproductive health',
            'General Surgery' => 'General surgical procedures',
            'Anesthesiology' => 'Anesthesia and pain management',
            'Radiology' => 'Medical imaging and diagnostics',
            'Emergency Medicine' => 'Emergency and critical care',
            'Gynecology' => 'Women\'s health and reproductive care',
            'Pulmonology' => 'Lung health and respiratory diseases',
            'General Medicine' => 'General medical consultation and care'
        );
        
        foreach ($default_departments as $name => $description) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'description' => $description,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s')
            );
        }
    }

    /**
     * Insert sample staff if they don't exist.
     */
    private static function insert_sample_staff() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        
        // Check if staff already exist
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($existing_count > 0) {
            return; // Staff already exist
        }
        
        $sample_staff = array(
            array('DOC001', 'Dr. John', 'Smith', '+1234567890', 'john.smith@hospital.com', 'Male', 'Doctor', 'Cardiology', 'MD Cardiology', 'Interventional Cardiology', 15),
            array('DOC002', 'Dr. Sarah', 'Johnson', '+1234567891', 'sarah.johnson@hospital.com', 'Female', 'Doctor', 'Neurology', 'MD Neurology', 'Pediatric Neurology', 12),
            array('DOC003', 'Dr. Michael', 'Brown', '+1234567892', 'michael.brown@hospital.com', 'Male', 'Doctor', 'Orthopedics', 'MS Orthopedics', 'Joint Replacement', 10),
            array('NUR001', 'Mary', 'Wilson', '+1234567893', 'mary.wilson@hospital.com', 'Female', 'Nurse', 'General', 'BSc Nursing', 'Critical Care', 8),
            array('ADM001', 'Robert', 'Davis', '+1234567894', 'robert.davis@hospital.com', 'Male', 'Administrator', 'Administration', 'MBA Healthcare', 'Hospital Management', 5)
        );
        
        foreach ($sample_staff as $staff) {
            $wpdb->insert(
                $table_name,
                array(
                    'staff_id' => $staff[0],
                    'first_name' => $staff[1],
                    'last_name' => $staff[2],
                    'contact_number' => $staff[3],
                    'email' => $staff[4],
                    'gender' => $staff[5],
                    'role' => $staff[6],
                    'department' => $staff[7],
                    'qualification' => $staff[8],
                    'specialization' => $staff[9],
                    'experience_years' => $staff[10],
                    'joining_date' => current_time('mysql', true),
                    'status' => 'active',
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
            );
        }
    }

    /**
     * Insert sample medicines if they don't exist.
     */
    private static function insert_sample_medicines() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_medicines';
        
        // Check if medicines already exist
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($existing_count > 0) {
            return; // Medicines already exist
        }
        
        $sample_medicines = array(
            array('Paracetamol', 'Pain reliever and fever reducer', 'Analgesic', 'Generic Pharma', 'Tablet', '500mg', 2.50, 5.00),
            array('Amoxicillin', 'Antibiotic for bacterial infections', 'Antibiotic', 'Generic Pharma', 'Capsule', '250mg', 8.00, 15.00),
            array('Ibuprofen', 'Anti-inflammatory pain reliever', 'NSAID', 'Generic Pharma', 'Tablet', '400mg', 3.00, 6.00),
            array('Omeprazole', 'Proton pump inhibitor for acid reflux', 'PPI', 'Generic Pharma', 'Capsule', '20mg', 12.00, 25.00),
            array('Metformin', 'Diabetes medication', 'Antidiabetic', 'Generic Pharma', 'Tablet', '500mg', 5.00, 10.00)
        );
        
        foreach ($sample_medicines as $medicine) {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $medicine[0],
                    'description' => $medicine[1],
                    'category' => $medicine[2],
                    'manufacturer' => $medicine[3],
                    'unit' => $medicine[4],
                    'strength' => $medicine[5],
                    'purchase_price' => $medicine[6],
                    'selling_price' => $medicine[7],
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s')
            );
        }
    }

    /**
     * Set default options for the plugin.
     * These options can be changed by the administrator in the settings page.
     */
    private static function set_default_options() {
        // --- General Settings ---
        if (!get_option('hms_version')) {
            add_option('hms_version', HMS_VERSION);
        }
        if (!get_option('hms_hospital_name')) {
            add_option('hms_hospital_name', get_bloginfo('name'));
        }
        if (!get_option('hms_hospital_address')) {
            add_option('hms_hospital_address', '');
        }
        if (!get_option('hms_hospital_phone')) {
            add_option('hms_hospital_phone', '');
        }
        if (!get_option('hms_hospital_email')) {
            add_option('hms_hospital_email', get_option('admin_email'));
        }
        if (!get_option('hms_currency')) {
            add_option('hms_currency', 'INR');
        }
        if (!get_option('hms_time_zone')) {
            add_option('hms_time_zone', 'Asia/Kolkata');
        }
        
        // --- Appointment Settings ---
        if (!get_option('hms_appointment_duration')) {
            add_option('hms_appointment_duration', 30); // in minutes
        }
        if (!get_option('hms_advance_booking_days')) {
            add_option('hms_advance_booking_days', 30); // days in advance
        }
        if (!get_option('hms_cancellation_policy')) {
            add_option('hms_cancellation_policy', __('Appointments can be cancelled up to 24 hours in advance.', 'hospital-management-system'));
        }
        
        // --- Notification Settings ---
        if (!get_option('hms_enable_sms')) {
            add_option('hms_enable_sms', 0); // Disabled by default
        }
        if (!get_option('hms_enable_email')) {
            add_option('hms_enable_email', 1); // Enabled by default
        }
        if (!get_option('hms_enable_whatsapp')) {
            add_option('hms_enable_whatsapp', 0); // Disabled by default
        }
        // API keys for gateways would be empty by default and configured by admin
        if (!get_option('hms_sms_gateway')) {
            add_option('hms_sms_gateway', ''); // e.g., Twilio, MSG91
        }
        if (!get_option('hms_sms_api_key')) {
            add_option('hms_sms_api_key', '');
        }
        if (!get_option('hms_whatsapp_api_key')) {
            add_option('hms_whatsapp_api_key', '');
        }
    }
    
    /**
     * Populate default data including departments, sample staff, and medicines.
     */
    private static function populate_default_data() {
        // Insert default departments
        self::insert_default_departments();
        
        // Insert sample staff members (doctors, nurses, etc.)
        self::insert_sample_staff();
        
        // Insert sample medicines
        self::insert_sample_medicines();
    }
    
    /**
     * Insert default departments if they don't exist.
     */
    // private static function insert_default_departments() {
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'hms_departments';
        
    //     // Check if departments already exist
    //     $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    //     if ($existing_count > 0) {
    //         return; // Departments already exist
    //     }
        
    //     $default_departments = array(
    //         'Cardiology' => 'Heart-related issues and cardiovascular diseases',
    //         'Neurology' => 'Brain and nervous system disorders',
    //         'Oncology' => 'Cancer treatment and care',
    //         'Emergency' => 'Emergency and trauma care',
    //         'Pediatrics' => 'Child healthcare and development',
    //         'Orthopedics' => 'Bone, joint, and muscle disorders',
    //         'Dermatology' => 'Skin, hair, and nail conditions',
    //         'Gastroenterology' => 'Digestive system disorders',
    //         'Nephrology' => 'Kidney and urinary system care',
    //         'Endocrinology' => 'Hormone and metabolic disorders',
    //         'Pulmonology' => 'Lung and respiratory system care',
    //         'Psychiatry' => 'Mental health and behavioral disorders',
    //         'Ophthalmology' => 'Eye and vision care',
    //         'ENT' => 'Ear, nose, and throat disorders',
    //         'Gynecology' => 'Women\'s reproductive health',
    //         'Urology' => 'Urinary and male reproductive system',
    //         'Radiology' => 'Medical imaging and diagnostics',
    //         'Pathology' => 'Laboratory testing and analysis',
    //         'Anesthesiology' => 'Anesthesia and pain management',
    //         'General Medicine' => 'General medical care and consultation'
    //     );
        
    //     foreach ($default_departments as $name => $description) {
    //         $wpdb->insert(
    //             $table_name,
    //             array(
    //                 'name' => $name,
    //                 'description' => $description,
    //                 'created_at' => current_time('mysql')
    //             ),
    //             array('%s', '%s', '%s')
    //         );
    //     }
    // }
    
    /**
     * Insert sample staff members if none exist.
     */
    // private static function insert_sample_staff() {
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'hms_staff';
        
    //     // Check if staff already exist
    //     $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    //     if ($existing_count > 0) {
    //         return; // Staff already exist
    //     }
        
    //     $sample_staff = array(
    //         array(
    //             'staff_id' => 'DOC001',
    //             'first_name' => 'Dr. John',
    //             'last_name' => 'Smith',
    //             'email' => 'john.smith@hospital.com',
    //             'contact_number' => '+1234567890',
    //             'role' => 'Doctor',
    //             'department' => 'Cardiology',
    //             'specialization' => 'Interventional Cardiology',
    //             'qualification' => 'MD, DM Cardiology',
    //             'experience_years' => 15,
    //             'status' => 'active'
    //         ),
    //         array(
    //             'staff_id' => 'DOC002',
    //             'first_name' => 'Dr. Sarah',
    //             'last_name' => 'Johnson',
    //             'email' => 'sarah.johnson@hospital.com',
    //             'contact_number' => '+1234567891',
    //             'role' => 'Doctor',
    //             'department' => 'Pediatrics',
    //             'specialization' => 'Pediatric Medicine',
    //             'qualification' => 'MD Pediatrics',
    //             'experience_years' => 12,
    //             'status' => 'active'
    //         ),
    //         array(
    //             'staff_id' => 'NUR001',
    //             'first_name' => 'Mary',
    //             'last_name' => 'Williams',
    //             'email' => 'mary.williams@hospital.com',
    //             'contact_number' => '+1234567892',
    //             'role' => 'Nurse',
    //             'department' => 'Emergency',
    //             'qualification' => 'BSc Nursing',
    //             'experience_years' => 8,
    //             'status' => 'active'
    //         ),
    //         array(
    //             'staff_id' => 'ADM001',
    //             'first_name' => 'Robert',
    //             'last_name' => 'Brown',
    //             'email' => 'robert.brown@hospital.com',
    //             'contact_number' => '+1234567893',
    //             'role' => 'Administrator',
    //             'department' => 'Administration',
    //             'qualification' => 'MBA Healthcare Management',
    //             'experience_years' => 10,
    //             'status' => 'active'
    //         ),
    //         array(
    //             'staff_id' => 'PHR001',
    //             'first_name' => 'Lisa',
    //             'last_name' => 'Davis',
    //             'email' => 'lisa.davis@hospital.com',
    //             'contact_number' => '+1234567894',
    //             'role' => 'Pharmacist',
    //             'department' => 'Pharmacy',
    //             'qualification' => 'PharmD',
    //             'experience_years' => 6,
    //             'status' => 'active'
    //         )
    //     );
        
    //     foreach ($sample_staff as $staff) {
    //         $staff['created_at'] = current_time('mysql');
    //         $staff['joining_date'] = current_time('Y-m-d');
            
    //         $wpdb->insert($table_name, $staff);
    //     }
    // }
    
    /**
     * Insert sample medicines if none exist.
     */
    // private static function insert_sample_medicines() {
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'hms_medicines';
        
    //     // Check if medicines already exist
    //     $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    //     if ($existing_count > 0) {
    //         return; // Medicines already exist
    //     }
        
    //     $sample_medicines = array(
    //         array(
    //             'name' => 'Paracetamol',
    //             'description' => 'Pain reliever and fever reducer',
    //             'category' => 'Analgesic',
    //             'manufacturer' => 'Generic Pharma',
    //             'unit' => 'Tablet',
    //             'strength' => '500mg',
    //             'purchase_price' => 0.50,
    //             'selling_price' => 1.00,
    //             'reorder_level' => 100
    //         ),
    //         array(
    //             'name' => 'Amoxicillin',
    //             'description' => 'Antibiotic for bacterial infections',
    //             'category' => 'Antibiotic',
    //             'manufacturer' => 'MedCorp',
    //             'unit' => 'Capsule',
    //             'strength' => '250mg',
    //             'purchase_price' => 2.00,
    //             'selling_price' => 3.50,
    //             'reorder_level' => 50
    //         ),
    //         array(
    //             'name' => 'Aspirin',
    //             'description' => 'Blood thinner and pain reliever',
    //             'category' => 'Cardiovascular',
    //             'manufacturer' => 'CardioMed',
    //             'unit' => 'Tablet',
    //             'strength' => '75mg',
    //             'purchase_price' => 0.75,
    //             'selling_price' => 1.25,
    //             'reorder_level' => 75
    //         ),
    //         array(
    //             'name' => 'Insulin',
    //             'description' => 'Diabetes medication',
    //             'category' => 'Antidiabetic',
    //             'manufacturer' => 'DiabetesCare',
    //             'unit' => 'Vial',
    //             'strength' => '100IU/ml',
    //             'purchase_price' => 25.00,
    //             'selling_price' => 35.00,
    //             'reorder_level' => 20
    //         ),
    //         array(
    //             'name' => 'Omeprazole',
    //             'description' => 'Proton pump inhibitor for acid reflux',
    //             'category' => 'Gastrointestinal',
    //             'manufacturer' => 'GastroPharm',
    //             'unit' => 'Capsule',
    //             'strength' => '20mg',
    //             'purchase_price' => 1.50,
    //             'selling_price' => 2.75,
    //             'reorder_level' => 60
    //         )
    //     );
        
    //     foreach ($sample_medicines as $medicine) {
    //         $medicine['created_at'] = current_time('mysql');
            
    //         $wpdb->insert($table_name, $medicine);
    //     }
    // }
    
    /**
     * Check if all required tables exist.
     * 
     * @return array Array of missing tables
     */
    public static function check_required_tables() {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        
        $required_tables = array(
            'hms_patients',
            'hms_appointments', 
            'hms_medical_records',
            'hms_bills',
            'hms_bill_items',
            'hms_payments',
            'hms_prescriptions',
            'hms_prescription_items',
            'hms_medicines',
            'hms_pharmacy_stock',
            'hms_pharmacy_dispenses',
            'hms_staff',
            'hms_departments',
            'hms_services',
            'hms_insurance_providers',
            'hms_claims',
            'hms_queue',
            'hms_audit_log',
            'hms_lab_tests',
            'hms_lab_test_orders'
        );
        
        $missing_tables = array();
        
        foreach ($required_tables as $table) {
            $table_name = $table_prefix . $table;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if (!$table_exists) {
                $missing_tables[] = $table;
            }
        }
        
        return $missing_tables;
    }
    
    /**
     * Run database migrations to ensure the database schema is up to date.
     */
    private static function run_migrations() {
        // Include the migration class
        require_once HMS_PLUGIN_DIR . 'includes/class-hms-migration.php';
        
        // Create a new migration instance and run migrations
        $migration = new HMS_Migration();
        $migration->run_migrations();
    }
}
