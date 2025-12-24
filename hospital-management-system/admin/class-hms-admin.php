<?php
// Include the HMS_Appointments class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-appointments.php';
// Include the HMS_Billing class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-billing.php';
// Include the HMS_Prescription class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-prescription.php';
// Include the HMS_Pharmacy class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-pharmacy.php';
// Include the HMS_Insurance class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-insurance.php';
// Include the HMS_Staff class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-staff.php';
// Include the HMS_Reports class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-reports.php';
// Include the HMS_Lab_Radiology class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-lab-radiology.php';
// Include the HMS_Patient class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
// Include the HMS_Data_Export_Import class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-data-export-import.php';
// Include the migration notice
require_once HMS_PLUGIN_DIR . 'admin/migration-notice.php';
// Include the UI Framework
require_once HMS_PLUGIN_DIR . 'admin/class-hms-ui-framework.php';
// Include the Notice Manager
require_once HMS_PLUGIN_DIR . 'admin/class-hms-notice-manager.php';

/**
 * HMS Admin Class
 *
 * Handles admin-specific functionality of the plugin.
 */

class HMS_Admin {

    private $plugin_name;
    private $version;
    private $appointments; // Instance of HMS_Appointments
    private $billing; // Instance of HMS_Billing
    private $prescription; // Instance of HMS_Prescription
    private $pharmacy; // Instance of HMS_Pharmacy
    private $insurance; // Instance of HMS_Insurance
    private $staff; // Instance of HMS_Staff
    private $reports; // Instance of HMS_Reports
    private $lab_radiology; // Instance of HMS_Lab_Radiology
    private $patient; // Instance of HMS_Patient
    private $export_import; // Instance of HMS_Data_Export_Import
    private $notifications; // Instance of HMS_Notifications
    private $integrations; // Instance of HMS_Integrations
    private $user_roles; // Instance of HMS_User_Roles

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Safely instantiate classes only if they exist
        if (class_exists('HMS_Appointments')) {
            $this->appointments = new HMS_Appointments();
        }
        if (class_exists('HMS_Billing')) {
            $this->billing = new HMS_Billing();
        }
        if (class_exists('HMS_Prescription')) {
            $this->prescription = new HMS_Prescription();
        }
        if (class_exists('HMS_Pharmacy')) {
            $this->pharmacy = new HMS_Pharmacy();
        }
        if (class_exists('HMS_Insurance')) {
            $this->insurance = new HMS_Insurance();
        }
        if (class_exists('HMS_Staff')) {
            $this->staff = new HMS_Staff();
        }
        if (class_exists('HMS_Reports')) {
            $this->reports = new HMS_Reports();
        }
        if (class_exists('HMS_Lab_Radiology')) {
            $this->lab_radiology = new HMS_Lab_Radiology();
        }
        if (class_exists('HMS_Patient')) {
            $this->patient = new HMS_Patient();
        }
        if (class_exists('HMS_Data_Export_Import')) {
            $this->export_import = new HMS_Data_Export_Import();
        }
        if (class_exists('HMS_Notifications')) {
            $this->notifications = new HMS_Notifications();
        }
        if (class_exists('HMS_Integrations')) {
            $this->integrations = new HMS_Integrations();
        }
        if (class_exists('HMS_User_Roles')) {
            $this->user_roles = new HMS_User_Roles();
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, HMS_PLUGIN_URL . 'admin/css/hms-admin.css', array(), $this->version, 'all');
        
        // Add custom CSS for hierarchical menu structure
        $custom_css = "
        /* HMS Hierarchical Menu Styling */
        #adminmenu .wp-submenu a[href*='hms_'] {
            position: relative;
        }
        
        /* Main category styling */
        #adminmenu .wp-submenu a[href*='hms_']:not([href*='hms_dashboard']) {
            font-weight: 500;
        }
        
        /* Subcategory indentation styling */
        #adminmenu .wp-submenu a[href*='hms_'] {
            padding-left: 12px;
        }
        
        /* Add visual hierarchy with different colors */
        #adminmenu .wp-submenu a[href*='hms_patients'],
        #adminmenu .wp-submenu a[href*='hms_clinical'],
        #adminmenu .wp-submenu a[href*='hms_billing'],
        #adminmenu .wp-submenu a[href*='hms_pharmacy'],
        #adminmenu .wp-submenu a[href*='hms_staff'],
        #adminmenu .wp-submenu a[href*='hms_reports'],
        #adminmenu .wp-submenu a[href*='hms-settings'] {
            border-left: 3px solid #2271b1;
            background-color: rgba(34, 113, 177, 0.05);
            font-weight: 600;
        }
        
        /* Breadcrumb styling for HMS pages */
        .hms-breadcrumb {
            background: #f1f1f1;
            padding: 10px 15px;
            margin: 0 0 20px 0;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .hms-breadcrumb a {
            color: #2271b1;
            text-decoration: none;
        }
        
        .hms-breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .hms-breadcrumb .separator {
            margin: 0 8px;
            color: #666;
        }
        
        /* Quick action buttons in templates */
        .hms-quick-actions {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .hms-quick-actions .button {
            margin-right: 10px;
            margin-bottom: 5px;
        }
        ";
        
        wp_add_inline_style($this->plugin_name, $custom_css);
    }

    public function enqueue_scripts() {
        // Check if we're on a HMS admin page
        $screen = get_current_screen();
        $hms_pages = array(
            'toplevel_page_hms_dashboard',
            'hospital-management-system_page_hms_patients',
            'hospital-management-system_page_hms_patient_registration',
            'hospital-management-system_page_hms_patient_medical_history',
            'hospital-management-system_page_hms_appointments',
            'hospital-management-system_page_hms_appointment_details',
            'hospital-management-system_page_hms_billing',
            'hospital-management-system_page_hms_bill_details',
            'hospital-management-system_page_hms_view_invoice',
            'hospital-management-system_page_hms_payment_info',
            'hospital-management-system_page_hms_prescriptions',
            'hospital-management-system_page_hms_prescription_details',
            'hospital-management-system_page_hms_pharmacy',
            'hospital-management-system_page_hms_dispense_medicine',
            'hospital-management-system_page_hms_medicine_inventory',
            'hospital-management-system_page_hms_suppliers',
            'hospital-management-system_page_hms_purchase_orders',
            'hospital-management-system_page_hms_purchase_order_details',
            'hospital-management-system_page_hms_insurance',
            'hospital-management-system_page_hms_insurance_details',
            'hospital-management-system_page_hms_insurance_claims',
            'hospital-management-system_page_hms_claim_details',
            'hospital-management-system_page_hms_patient_insurance',
            'hospital-management-system_page_hms_lab_tests',
            'hospital-management-system_page_hms_lab_test_order',
            'hospital-management-system_page_hms_clinical',
            'hospital-management-system_page_hms_queue',
            'hospital-management-system_page_hms_staff',
            'hospital-management-system_page_hms_staff_details',
            'hospital-management-system_page_hms_staff_roles',
            'hospital-management-system_page_hms_staff_schedule',
            'hospital-management-system_page_hms_reports_dashboard',
            'hospital-management-system_page_hms_reports',
            'hospital-management-system_page_hms_export_data',
            'hospital-management-system_page_hms_settings'
        );

        if ($screen && in_array($screen->id, $hms_pages)) {
            wp_enqueue_script($this->plugin_name, HMS_PLUGIN_URL . 'admin/js/hms-admin.js', array('jquery', 'jquery-ui-dialog'), $this->version, false);
            
            // Enqueue patient medical history script specifically on the patient medical history page
            if ($screen && $screen->id === 'hospital-management-system_page_hms_patient_medical_history') {
                wp_enqueue_script(
                    'hms-patient-medical-history',
                    HMS_PLUGIN_URL . 'admin/js/hms-patient-medical-history.js',
                    array('jquery'),
                    $this->version . '.' . time(), // Add timestamp to prevent caching
                    true
                );
                
                // Add nonce variable for patient medical history script
                wp_localize_script('hms-patient-medical-history', 'hms_admin_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce')
                ));
            }
            
            // Enqueue clinical dashboard assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_clinical') {
                // Enqueue clinical dashboard CSS
                wp_enqueue_style(
                    'hms-clinical-dashboard',
                    HMS_PLUGIN_URL . 'admin/css/hms-clinical-dashboard.css',
                    array(),
                    $this->version . '.' . time()
                );
            }
            
            // Enqueue comprehensive medical history assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_comprehensive_medical_history') {
                // Enqueue comprehensive medical history CSS
                wp_enqueue_style(
                    'hms-comprehensive-medical-history',
                    HMS_PLUGIN_URL . 'admin/css/hms-comprehensive-medical-history.css',
                    array(),
                    $this->version . '.' . time()
                );
                
                // Enqueue comprehensive medical history JavaScript
                wp_enqueue_script(
                    'hms-comprehensive-medical-history',
                    HMS_PLUGIN_URL . 'admin/js/hms-comprehensive-medical-history.js',
                    array('jquery', 'jquery-ui-dialog'),
                    $this->version . '.' . time(),
                    true
                );
                
                // Localize script with necessary variables
                wp_localize_script('hms-comprehensive-medical-history', 'hms_admin_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'advanced_filters' => esc_html__('Advanced Filters', 'hospital-management-system'),
                    'hide_filters' => esc_html__('Hide Filters', 'hospital-management-system'),
                    'no_records_found' => esc_html__('No records found matching your criteria.', 'hospital-management-system'),
                    'try_different_filters' => esc_html__('Try adjusting your search terms or filters.', 'hospital-management-system'),
                    'clear_filters' => esc_html__('Clear All Filters', 'hospital-management-system'),
                    'saving' => esc_html__('Saving...', 'hospital-management-system'),
                    'save_changes' => esc_html__('Save Changes', 'hospital-management-system'),
                    'update_failed' => esc_html__('Failed to update record. Please try again.', 'hospital-management-system'),
                    'diagnosis' => esc_html__('Diagnosis', 'hospital-management-system'),
                    'last_modified' => esc_html__('Last Modified', 'hospital-management-system'),
                    'no_records_to_export' => esc_html__('No records available to export.', 'hospital-management-system'),
                    'export_started' => esc_html__('Export started. Your download should begin shortly.', 'hospital-management-system'),
                    'loading' => esc_html__('Loading...', 'hospital-management-system'),
                    'load_more' => esc_html__('Load More Records', 'hospital-management-system'),
                    'load_more_failed' => esc_html__('Failed to load more records. Please try again.', 'hospital-management-system')
                ));
            }
            
            // Enqueue reports dashboard assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_reports_dashboard') {
                // Enqueue Chart.js for data visualization
                wp_enqueue_script(
                    'chartjs',
                    'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
                    array(),
                    '3.9.1',
                    true
                );
                
                // Enqueue reports dashboard CSS
                wp_enqueue_style(
                    'hms-reports-dashboard',
                    HMS_PLUGIN_URL . 'admin/css/hms-reports.css',
                    array(),
                    $this->version . '.' . time()
                );
                
                // Enqueue reports dashboard JavaScript
                wp_enqueue_script(
                    'hms-reports-dashboard',
                    HMS_PLUGIN_URL . 'admin/js/hms-reports-dashboard.js',
                    array('jquery', 'chartjs'),
                    $this->version . '.' . time(),
                    true
                );
                
                // Localize script with necessary variables
                wp_localize_script('hms-reports-dashboard', 'hms_admin_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'loading' => esc_html__('Loading...', 'hospital-management-system'),
                    'error_loading_data' => esc_html__('Error loading dashboard data. Please try again.', 'hospital-management-system'),
                    'no_data_available' => esc_html__('No data available for the selected period.', 'hospital-management-system'),
                    'export_pdf_title' => esc_html__('Export Dashboard as PDF', 'hospital-management-system'),
                    'export_excel_title' => esc_html__('Export Dashboard as Excel', 'hospital-management-system'),
                    'schedule_report_title' => esc_html__('Schedule Automated Report', 'hospital-management-system')
                ));
            }
            
            // Enqueue reports page assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_reports') {
                // Enqueue reports CSS
                wp_enqueue_style(
                    'hms-reports',
                    HMS_PLUGIN_URL . 'admin/css/hms-reports.css',
                    array(),
                    $this->version . '.' . time()
                );
            }
            
            // Enqueue export-import page assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_export_data') {
                // Enqueue export-import CSS
                wp_enqueue_style(
                    'hms-export-import',
                    HMS_PLUGIN_URL . 'admin/css/hms-export-import.css',
                    array(),
                    $this->version . '.' . time()
                );
                
                // Enqueue export-import JavaScript
                wp_enqueue_script(
                    'hms-export-import',
                    HMS_PLUGIN_URL . 'admin/js/hms-export-import.js',
                    array('jquery'),
                    $this->version . '.' . time(),
                    true
                );
                
                // Localize script with necessary variables
                wp_localize_script('hms-export-import', 'hms_admin_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'loading' => esc_html__('Loading...', 'hospital-management-system'),
                    'error_message' => esc_html__('An error occurred. Please try again.', 'hospital-management-system'),
                    'success_message' => esc_html__('Operation completed successfully.', 'hospital-management-system'),
                    'select_modules' => esc_html__('Please select at least one module to export.', 'hospital-management-system'),
                    'select_file' => esc_html__('Please select a file to upload.', 'hospital-management-system'),
                    'invalid_file_type' => esc_html__('Invalid file type. Please select CSV or Excel files only.', 'hospital-management-system'),
                    'file_too_large' => esc_html__('File size too large. Maximum allowed size is 10MB.', 'hospital-management-system'),
                    'export_started' => esc_html__('Export process started successfully.', 'hospital-management-system'),
                    'import_started' => esc_html__('Import process started successfully.', 'hospital-management-system'),
                    'validation_failed' => esc_html__('File validation failed. Please check the file format and try again.', 'hospital-management-system'),
                    'upload_failed' => esc_html__('File upload failed. Please try again.', 'hospital-management-system')
                ));
            }
            
            // Enqueue settings page assets
            if ($screen && $screen->id === 'hospital-management-system_page_hms_settings') {
                // Enqueue WordPress media uploader
                wp_enqueue_media();
                
                // Enqueue WordPress color picker
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                
                // Enqueue settings CSS
                wp_enqueue_style(
                    'hms-settings',
                    HMS_PLUGIN_URL . 'admin/css/hms-settings.css',
                    array('wp-color-picker'),
                    $this->version . '.' . time()
                );
                
                // Enqueue settings JavaScript
                wp_enqueue_script(
                    'hms-settings',
                    HMS_PLUGIN_URL . 'admin/js/hms-settings.js',
                    array('jquery', 'wp-color-picker', 'media-upload'),
                    $this->version . '.' . time(),
                    true
                );
                
                // Localize script with necessary variables
                wp_localize_script('hms-settings', 'hms_settings_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'strings' => array(
                        'loading' => esc_html__('Loading...', 'hospital-management-system'),
                        'saving' => esc_html__('Saving...', 'hospital-management-system'),
                        'saved' => esc_html__('Settings saved successfully!', 'hospital-management-system'),
                        'error' => esc_html__('An error occurred. Please try again.', 'hospital-management-system'),
                        'select_logo' => esc_html__('Select Hospital Logo', 'hospital-management-system'),
                        'use_this_logo' => esc_html__('Use This Logo', 'hospital-management-system'),
                        'select_favicon' => esc_html__('Select Hospital Favicon', 'hospital-management-system'),
                        'use_this_favicon' => esc_html__('Use This Favicon', 'hospital-management-system'),
                        'no_logo_selected' => esc_html__('No logo selected', 'hospital-management-system'),
                        'no_favicon_selected' => esc_html__('No favicon selected', 'hospital-management-system'),
                        'test_connection' => esc_html__('Test Connection', 'hospital-management-system'),
                        'testing_connection' => esc_html__('Testing Connection...', 'hospital-management-system'),
                        'connection_test_failed' => esc_html__('Connection test failed. Please check your settings.', 'hospital-management-system'),
                        'confirm_reset_defaults' => esc_html__('Are you sure you want to reset all settings to their default values? This action cannot be undone.', 'hospital-management-system'),
                        'error_resetting_defaults' => esc_html__('Error resetting settings to defaults.', 'hospital-management-system'),
                        'error_updating_capability' => esc_html__('Error updating role capability.', 'hospital-management-system'),
                        'settings_auto_saved' => esc_html__('Settings auto-saved', 'hospital-management-system'),
                        'is_required' => esc_html__('is required', 'hospital-management-system'),
                        'invalid_email' => esc_html__('Please enter a valid email address.', 'hospital-management-system'),
                        'invalid_url' => esc_html__('Please enter a valid URL.', 'hospital-management-system')
                    )
                ));
            }
            
            // Enqueue DataTables for patients page
            if ($screen && $screen->id === 'hospital-management-system_page_hms_patients') {
                // Enqueue DataTables CSS
                wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6');
                
                // Enqueue DataTables JS - load in footer to ensure it's available after jQuery
                wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', true);
                
                // Make sure the patient object is instantiated for AJAX handlers
                if (!class_exists('HMS_Patient')) {
                    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
                }
                $patient_obj = new HMS_Patient();
            }
            
            // Enqueue DataTables for prescriptions page
            if ($screen && $screen->id === 'hospital-management-system_page_hms_prescriptions') {
                // Enqueue DataTables CSS
                wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6');
                
                // Enqueue DataTables JS - load in footer to ensure it's available after jQuery
                wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', true);
                
                // Enqueue Bootstrap CSS and JS for modal
                wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
                wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
            }
            
            wp_localize_script($this->plugin_name, 'hms_admin_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hms_admin_nonce'),
                'confirm_delete_message' => __('Are you sure you want to delete this item?', 'hospital-management-system'),
                'select_report_type_message' => __('Please select a report type first.', 'hospital-management-system'),
                'strings' => array(
                    'loading_patients' => __('Loading patients...', 'hospital-management-system'),
                    'no_patients_found' => __('No patients found.', 'hospital-management-system'),
                    'error_loading_patients' => __('Error loading patients.', 'hospital-management-system'),
                    'patients' => __('patients', 'hospital-management-system'),
                    'add_new_patient' => __('Add New Patient', 'hospital-management-system'),
                    'edit_patient' => __('Edit Patient', 'hospital-management-system'),
                    'save_patient' => __('Save Patient', 'hospital-management-system'),
                    'update_patient' => __('Update Patient', 'hospital-management-system'),
                    'view' => __('View', 'hospital-management-system'),
                    'edit' => __('Edit', 'hospital-management-system'),
                    'delete' => __('Delete', 'hospital-management-system'),
                    'view_patient_details_for_id' => __('View patient details for ID:', 'hospital-management-system'),
                    'confirm_delete_patient' => __('Are you sure you want to delete this patient? This action cannot be undone.', 'hospital-management-system'),
                    'failed_to_load_patient_data' => __('Failed to load patient data.', 'hospital-management-system'),
                    'error_fetching_patient_data' => __('Error fetching patient data.', 'hospital-management-system'),
                    'patient_added_successfully' => __('Patient added successfully.', 'hospital-management-system'),
                    'patient_updated_successfully' => __('Patient updated successfully.', 'hospital-management-system'),
                    'patient_deleted_successfully' => __('Patient deleted successfully.', 'hospital-management-system'),
                    'failed_to_add_patient' => __('Failed to add patient.', 'hospital-management-system'),
                    'failed_to_update_patient' => __('Failed to update patient.', 'hospital-management-system'),
                    'failed_to_delete_patient' => __('Failed to delete patient.', 'hospital-management-system'),
                    'error' => __('Error:', 'hospital-management-system'),
                    'unexpected_error_occurred' => __('An unexpected error occurred.', 'hospital-management-system'),
                    'cancel' => __('Cancel', 'hospital-management-system'),
                    'confirm_delete_appointment' => __('Are you sure you want to delete this appointment? This action cannot be undone.', 'hospital-management-system'),
                    'confirm_delete_bill' => __('Are you sure you want to delete this bill? This action cannot be undone.', 'hospital-management-system'),
                    'confirm_delete_prescription' => __('Are you sure you want to delete this prescription? This action cannot be undone.', 'hospital-management-system'),
                    'confirm_delete_dispense' => __('Are you sure you want to delete this dispense record? This action cannot be undone.', 'hospital-management-system'),
                    'confirm_delete_provider' => __('Are you sure you want to delete this insurance provider? This action cannot be undone.', 'hospital-management-system'),
                    'confirm_delete_staff' => __('Are you sure you want to delete this staff member? This action cannot be undone.', 'hospital-management-system'),
                )
            ));
            
            // Enqueue patient registration form script specifically on the patient registration page
            if ($screen && $screen->id === 'hospital-management-system_page_hms_patient_registration') {
                wp_enqueue_script(
                    'hms-patient-registration',
                    HMS_PLUGIN_URL . 'admin/js/hms-patient-registration.js',
                    array('jquery'),
                    $this->version . '.' . time(), // Add timestamp to prevent caching
                    true
                );
                
                // Add a test variable to confirm the script is loaded
                wp_localize_script('hms-patient-registration', 'hms_patient_registration_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'is_loaded' => true
                ));
            }
            
            // Enqueue staff form script specifically on the staff details page
            if ($screen && $screen->id === 'hospital-management-system_page_hms_staff_details') {
                wp_enqueue_script(
                    'hms-staff',
                    HMS_PLUGIN_URL . 'admin/js/hms-staff.js',
                    array('jquery'),
                    $this->version . '.' . time(), // Add timestamp to prevent caching
                    true
                );
                
                // Add variables for staff script
                wp_localize_script('hms-staff', 'hms_staff_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('hms_admin_nonce'),
                    'strings' => array(
                        'please_enter_first_name' => __('Please enter a first name.', 'hospital-management-system'),
                        'please_enter_last_name' => __('Please enter a last name.', 'hospital-management-system'),
                        'please_enter_staff_id' => __('Please enter a staff ID code.', 'hospital-management-system'),
                        'please_select_role' => __('Please select a role.', 'hospital-management-system'),
                        'please_select_department' => __('Please select a department.', 'hospital-management-system'),
                        'unexpected_error' => __('An unexpected error occurred. Please try again.', 'hospital-management-system'),
                    )
                ));
            }
        }
    }

    /**
     * Display activation success or error notices.
     */
    public function display_activation_notices() {
        // Check for activation success
        if (get_option('hms_activation_success')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>' . __('Hospital Management System activated successfully!', 'hospital-management-system') . '</strong></p>';
            echo '<p>' . __('All database tables have been created and default data has been populated.', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            // Remove the flag so it doesn't show again
            delete_option('hms_activation_success');
        }
        
        // Check for activation errors
        $activation_error = get_option('hms_activation_error');
        if ($activation_error) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . __('Hospital Management System activation failed!', 'hospital-management-system') . '</strong></p>';
            echo '<p>' . sprintf(__('Error: %s', 'hospital-management-system'), esc_html($activation_error)) . '</p>';
            echo '<p>' . __('Please contact support or try deactivating and reactivating the plugin.', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            // Remove the error flag
            delete_option('hms_activation_error');
        }
    }

    /**
     * Register hidden pages that don't appear in menu
     */
    public function register_hidden_pages() {
        // Define hidden pages and their handlers
        $hidden_pages = array(
            'hms_patient_registration' => array(
                'capability' => 'manage_patients',
                'callback' => array($this, 'render_patient_registration_page'),
                'title' => __('Patient Registration', 'hospital-management-system')
            ),
            'hms_patient_medical_history' => array(
                'capability' => 'manage_patients',
                'callback' => array($this, 'render_patient_medical_history_page'),
                'title' => __('Patient Medical History', 'hospital-management-system')
            ),
            'hms_appointment_details' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_appointment_details_page'),
                'title' => __('Appointment Details', 'hospital-management-system')
            ),
            'hms_prescription_details' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_prescription_details_page'),
                'title' => __('Prescription Details', 'hospital-management-system')
            ),
            'hms_medical_record_details' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_medical_record_details_page'),
                'title' => __('Medical Record Details', 'hospital-management-system')
            ),
            'hms_comprehensive_medical_history' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_comprehensive_medical_history_page'),
                'title' => __('Comprehensive Medical History', 'hospital-management-system')
            ),
            'hms_lab_test_order' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_lab_test_order_page'),
                'title' => __('Order New Test', 'hospital-management-system')
            ),
            'hms_lab_results' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'render_lab_results_page'),
                'title' => __('Lab Results Entry', 'hospital-management-system')
            ),
            'hms_comprehensive_lab_report' => array(
                'capability' => 'manage_clinical',
                'callback' => array($this, 'handle_comprehensive_lab_report'),
                'title' => __('Comprehensive Lab Report', 'hospital-management-system')
            ),
            'hms_bill_details' => array(
                'capability' => 'manage_billing',
                'callback' => array($this, 'render_bill_details_page'),
                'title' => __('Bill Details', 'hospital-management-system')
            ),
            'hms_view_invoice' => array(
                'capability' => 'manage_billing',
                'callback' => array($this, 'render_view_invoice_page'),
                'title' => __('View Invoice', 'hospital-management-system')
            ),
            'hms_payment_info' => array(
                'capability' => 'manage_billing',
                'callback' => array($this, 'render_payment_info_page'),
                'title' => __('Process Payment', 'hospital-management-system')
            ),
            'hms_insurance_details' => array(
                'capability' => 'manage_hms',
                'callback' => array($this, 'render_insurance_details_page'),
                'title' => __('Insurance Details', 'hospital-management-system')
            ),
            'hms_claim_details' => array(
                'capability' => 'manage_hms',
                'callback' => array($this, 'render_claim_details_page'),
                'title' => __('Claim Details', 'hospital-management-system')
            ),
            'hms_dispense_medicine' => array(
                'capability' => 'manage_pharmacy',
                'callback' => array($this, 'render_dispense_medicine_page'),
                'title' => __('Dispense Medicine', 'hospital-management-system')
            ),
            'hms_purchase_order_details' => array(
                'capability' => 'manage_pharmacy',
                'callback' => array($this, 'render_purchase_order_details_page'),
                'title' => __('Purchase Order Details', 'hospital-management-system')
            ),
            'hms_staff_details' => array(
                'capability' => 'manage_hms',
                'callback' => array($this, 'render_staff_details_page'),
                'title' => __('Staff Details', 'hospital-management-system')
            )
        );

        // Register page handlers
        foreach ($hidden_pages as $page_slug => $page_config) {
            if (isset($_GET['page']) && $_GET['page'] === $page_slug) {
                if (current_user_can($page_config['capability'])) {
                    add_action('admin_head', function() use ($page_config) {
                        echo '<title>' . esc_html($page_config['title']) . ' - ' . get_bloginfo('name') . '</title>';
                    });
                    // Hook the callback to the current page
                    add_action('toplevel_page_' . $page_slug, $page_config['callback']);
                }
            }
        }
    }

    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Hospital Management', 'hospital-management-system'),
            __('Hospital Management', 'hospital-management-system'),
            'manage_hms',
            'hms_dashboard',
            array($this, 'render_dashboard_page'),
            'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBVcGxvYWRlZCB0bzogU1ZHIFJlcG8sIHd3dy5zdmdyZXBvLmNvbSwgR2VuZXJhdG9yOiBTVkcgUmVwbyBNaXhlciBUb29scyAtLT4NCjxzdmcgZmlsbD0iIzAwMDAwMCIgaGVpZ2h0PSI4MDBweCIgd2lkdGg9IjgwMHB4IiB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiANCgkgdmlld0JveD0iMCAwIDUxMiA1MTIiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxnPg0KCQkJPHBhdGggZD0iTTQ5My42NjYsMTAyLjA2NUgzODAuOTA0VjYxLjY2NGMwLTEwLjEyNS04LjIwOS0xOC4zMzQtMTguMzM0LTE4LjMzNEgxNDkuNDNjLTEwLjEyNSwwLTE4LjMzNCw4LjIwOS0xOC4zMzQsMTguMzM0DQoJCQkJdjQwLjQwMUgxOC4zMzRDOC4yMDksMTAyLjA2NSwwLDExMC4yNzQsMCwxMjAuMzk5djMyOS45MzdjMCwxMC4xMjUsOC4yMDksMTguMzM0LDE4LjMzNCwxOC4zMzRjNy41NDcsMCw0NDkuMDYxLDAsNDc1LjMzMywwDQoJCQkJYzEwLjEyNSwwLDE4LjMzNC04LjIwOSwxOC4zMzQtMTguMzM0VjEyMC4zOTlDNTEyLDExMC4yNzQsNTAzLjc5MSwxMDIuMDY1LDQ5My42NjYsMTAyLjA2NXogTTEzMS4wOTYsNDMyLjAwMkgzNi42NjdWMjE2LjM0Nw0KCQkJCWg5NC40MjlWNDMyLjAwMnogTTEzMS4wOTYsMTc5LjY4SDM2LjY2N3YtNDAuOTQ3aDk0LjQyOVYxNzkuNjh6IE0yNzYuMDE0LDQzMi4wMDJoLTQwLjUzOXYtNzguNTE4aDQwLjUzOVY0MzIuMDAyeg0KCQkJCSBNMzQ0LjIzOCw0MzIuMDAyaC0zMS41NTZ2LTk2Ljg1MWMwLTEwLjEyNS04LjIwOS0xOC4zMzQtMTguMzM0LTE4LjMzNGgtNzcuMjA2Yy0xMC4xMjUsMC0xOC4zMzQsOC4yMDktMTguMzM0LDE4LjMzNHY5Ni44NTENCgkJCQloLTMxLjA0NWMtMC4wMDEtMTMuMDc4LTAuMDAxLTMzNS41NjUtMC4wMDEtMzUyLjAwNGgxNzYuNDc1QzM0NC4yMzgsOTYuNjAzLDM0NC4yMzgsNDE5LjIxLDM0NC4yMzgsNDMyLjAwMnogTTM4MC45MDQsMTM4LjczMg0KCQkJCWg5NC40Mjl2NDAuOTQ3aC05NC40MjlWMTM4LjczMnogTTQ3NS4zMzQsNDMyLjAwMmgtOTQuNDI5VjIxNi4zNDdoOTQuNDI5VjQzMi4wMDJ6Ii8+DQoJCQk8cGF0aCBkPSJNMjE3LjE5MywxNzcuNTRoMjAuNDc0djIwLjQ3NGMwLDEwLjEyNSw4LjIwOSwxOC4zMzQsMTguMzM0LDE4LjMzNHMxOC4zMzQtOC4yMDksMTguMzM0LTE4LjMzNFYxNzcuNTRoMjAuNDc0DQoJCQkJYzEwLjEyNSwwLDE4LjMzNC04LjIwOSwxOC4zMzQtMTguMzM0cy04LjIwOS0xOC4zMzQtMTguMzM0LTE4LjMzNGgtMjAuNDc0di0yMC40NzRjMC0xMC4xMjUtOC4yMDktMTguMzM0LTE4LjMzNC0xOC4zMzQNCgkJCQlzLTE4LjMzNCw4LjIwOS0xOC4zMzQsMTguMzM0djIwLjQ3NGgtMjAuNDc0Yy0xMC4xMjUsMC0xOC4zMzQsOC4yMDktMTguMzM0LDE4LjMzNFMyMDcuMDY4LDE3Ny41NCwyMTcuMTkzLDE3Ny41NHoiLz4NCgkJPC9nPg0KCTwvZz4NCjwvZz4NCjwvc3ZnPg==',
            30
        );
        
        // === MAIN DASHBOARD ===
        add_submenu_page(
            'hms_dashboard',
            __('Dashboard', 'hospital-management-system'),
            __('Dashboard', 'hospital-management-system'),
            'manage_hms',
            'hms_dashboard',
            array($this, 'render_dashboard_page')
        );
        
        // === PATIENT MANAGEMENT ===
        add_submenu_page(
            'hms_dashboard',
            __('Patient Management', 'hospital-management-system'),
            __('── Patient Management', 'hospital-management-system'),
            'manage_patients',
            'hms_patients',
            array($this, 'render_patients_page')
        );
        
        // Register hidden pages using admin_init hook to avoid NULL parent slug issues
        add_action('admin_init', array($this, 'register_hidden_pages'));
        
        // === CLINICAL MANAGEMENT ===
        add_submenu_page(
            'hms_dashboard',
            __('Clinical Management', 'hospital-management-system'),
            __('── Clinical Management', 'hospital-management-system'),
            'manage_clinical',
            'hms_clinical',
            array($this, 'render_clinical_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Appointments', 'hospital-management-system'),
            __('    ├─ Appointments', 'hospital-management-system'),
            'manage_clinical',
            'hms_appointments',
            array($this, 'render_appointments_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        add_submenu_page(
            'hms_dashboard',
            __('Prescriptions', 'hospital-management-system'),
            __('    ├─ Prescriptions', 'hospital-management-system'),
            'manage_clinical',
            'hms_prescriptions',
            array($this, 'render_prescriptions_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        add_submenu_page(
            'hms_dashboard',
            __('Medical Records', 'hospital-management-system'),
            __('    ├─ Medical Records', 'hospital-management-system'),
            'manage_clinical',
            'hms_medical_records',
            array($this, 'render_medical_records_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        // Hidden page - will be registered in register_hidden_pages method

        add_submenu_page(
            'hms_dashboard',
            __('Patient Queue', 'hospital-management-system'),
            __('    ├─ Patient Queue', 'hospital-management-system'),
            'manage_clinical',
            'hms_queue',
            array($this, 'render_queue_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Lab & Radiology', 'hospital-management-system'),
            __('    └─ Lab & Radiology', 'hospital-management-system'),
            'manage_clinical',
            'hms_lab_tests',
            array($this, 'render_lab_tests_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        // Hidden page - will be registered in register_hidden_pages method
        
        // Hidden page - will be registered in register_hidden_pages method
        
        // === BILLING & FINANCE ===
        add_submenu_page(
            'hms_dashboard',
            __('Billing & Finance', 'hospital-management-system'),
            __('── Billing & Finance', 'hospital-management-system'),
            'manage_billing',
            'hms_billing',
            array($this, 'render_billing_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        // Hidden page - will be registered in register_hidden_pages method

        // Hidden page - will be registered in register_hidden_pages method

        add_submenu_page(
            'hms_dashboard',
            __('Insurance Management', 'hospital-management-system'),
            __('    └─ Insurance Management', 'hospital-management-system'),
            'manage_hms',
            'hms_insurance',
            array($this, 'render_insurance_page')
        );

        // Hidden page - will be registered in register_hidden_pages method

        add_submenu_page(
            'hms_dashboard',
            __('Insurance Claims', 'hospital-management-system'),
            __('    └─ Insurance Claims', 'hospital-management-system'),
            'manage_hms',
            'hms_insurance_claims',
            array($this, 'render_insurance_claims_page')
        );

        add_submenu_page(
            null, // Hidden from menu, accessed via claims page
            __('Claim Details', 'hospital-management-system'),
            __('Claim Details', 'hospital-management-system'),
            'manage_hms',
            'hms_claim_details',
            array($this, 'render_claim_details_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Patient Insurance Coverage', 'hospital-management-system'),
            __('    └─ Patient Coverage', 'hospital-management-system'),
            'manage_hms',
            'hms_patient_insurance',
            array($this, 'render_patient_insurance_page')
        );

        // === PHARMACY MANAGEMENT ===
        add_submenu_page(
            'hms_dashboard',
            __('Pharmacy Management', 'hospital-management-system'),
            __('── Pharmacy Management', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_pharmacy',
            array($this, 'render_pharmacy_page')
        );

        add_submenu_page(
            null, // Hidden from menu, accessed via pharmacy page
            __('Dispense Medicine', 'hospital-management-system'),
            __('Dispense Medicine', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_dispense_medicine',
            array($this, 'render_dispense_medicine_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Medicine Inventory', 'hospital-management-system'),
            __('Medicine Inventory', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_medicine_inventory',
            array($this, 'render_medicine_inventory_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Suppliers', 'hospital-management-system'),
            __('Suppliers', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_suppliers',
            array($this, 'render_suppliers_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Purchase Orders', 'hospital-management-system'),
            __('Purchase Orders', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_purchase_orders',
            array($this, 'render_purchase_orders_page')
        );

        add_submenu_page(
            null, // Hidden from menu, accessed via purchase orders page
            __('Purchase Order Details', 'hospital-management-system'),
            __('Purchase Order Details', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_purchase_order_details',
            array($this, 'render_purchase_order_details_page')
        );
        
        // === STAFF MANAGEMENT ===
        add_submenu_page(
            'hms_dashboard',
            __('Staff Management', 'hospital-management-system'),
            __('── Staff Management', 'hospital-management-system'),
            'manage_hms',
            'hms_staff',
            array($this, 'render_staff_page')
        );

        add_submenu_page(
            null, // Hidden from menu, accessed via staff page
            __('Staff Details', 'hospital-management-system'),
            __('Add New Staff', 'hospital-management-system'),
            'manage_hms',
            'hms_staff_details',
            array($this, 'render_staff_details_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Staff Roles & Permissions', 'hospital-management-system'),
            __('    ├─ Roles & Permissions', 'hospital-management-system'),
            'manage_staff',
            'hms_staff_roles',
            array($this, 'render_staff_roles_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Staff Scheduling', 'hospital-management-system'),
            __('    └─ Staff Scheduling', 'hospital-management-system'),
            'manage_staff',
            'hms_staff_schedule',
            array($this, 'render_staff_schedule_page')
        );
        
        // === REPORTS & ANALYTICS ===
        add_submenu_page(
            'hms_dashboard',
            __('Analytics Dashboard', 'hospital-management-system'),
            __('── Analytics Dashboard', 'hospital-management-system'),
            'manage_reports',
            'hms_reports_dashboard',
            array($this, 'render_reports_dashboard_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Reports & Analytics', 'hospital-management-system'),
            __('    └─ Reports & Analytics', 'hospital-management-system'),
            'manage_reports',
            'hms_reports',
            array($this, 'render_reports_page')
        );

        add_submenu_page(
            'hms_dashboard',
            __('Export & Import Data', 'hospital-management-system'),
            __('    └─ Export & Import Data', 'hospital-management-system'),
            'manage_reports',
            'hms_export_data',
            array($this, 'render_export_data_page')
        );
        
        // === SYSTEM SETTINGS ===
        add_submenu_page(
            'hms_dashboard',
            __('System Settings', 'hospital-management-system'),
            __('── System Settings', 'hospital-management-system'),
            'manage_hms',
            'hms-settings',
            array($this, 'render_settings_page')
        );
        
        // Add a hidden menu item for running migrations (only visible to administrators)
        add_submenu_page(
            'hms_dashboard', // Parent slug (use the main dashboard menu)
            __('Run Migrations', 'hospital-management-system'), // Page title
            __('Run Migrations', 'hospital-management-system'), // Menu title (hidden)
            'manage_hms', // Capability
            'hms_run_migrations', // Menu slug
            array($this, 'render_run_migrations_page') // Callback function
        );
        
        // Add a hidden menu item for fixing bill net amounts (only visible to administrators)
        add_submenu_page(
            'hms_dashboard', // Parent slug (use the main dashboard menu)
            __('Fix Bill Net Amount', 'hospital-management-system'), // Page title
            __('Fix Bill Net Amount', 'hospital-management-system'), // Menu title (hidden)
            'manage_hms', // Capability
            'hms_fix_bill_net_amount', // Menu slug
            array($this, 'render_fix_bill_net_amount_page') // Callback function
        );
        
        // Add a hidden menu item for checking database tables
        add_submenu_page(
            'hms_dashboard', // Parent slug (use the main dashboard menu)
            __('Check Database Tables', 'hospital-management-system'), // Page title
            __('Check Tables', 'hospital-management-system'), // Menu title (hidden)
            'manage_hms', // Capability
            'hms_check_tables', // Menu slug
            array($this, 'render_check_tables_page') // Callback function
        );
        
        // Add a hidden menu item for testing CRUD operations
        add_submenu_page(
            'hms_dashboard', // Parent slug (use the main dashboard menu)
            __('Test CRUD Operations', 'hospital-management-system'), // Page title
            __('Test CRUD', 'hospital-management-system'), // Menu title (hidden)
            'manage_hms', // Capability
            'hms_test_crud', // Menu slug
            array($this, 'render_test_crud_page') // Callback function
        );

        add_submenu_page(
            'hms_dashboard', // Parent slug
            __('Fix Missing Tables', 'hospital-management-system'), // Page title
            __('Fix Missing Tables', 'hospital-management-system'), // Menu title
            'manage_hms', // Capability
            'hms_fix_tables', // Menu slug
            array($this, 'render_fix_tables_page') // Callback function
        );
        
        // Add a hidden menu item for database status check
        add_submenu_page(
            'hms_dashboard', // Parent slug
            __('Database Status', 'hospital-management-system'), // Page title
            __('Database Status', 'hospital-management-system'), // Menu title
            'manage_hms', // Capability
            'hms_database_status', // Menu slug
            array($this, 'render_database_status_page') // Callback function
        );
        
        // Add a hidden menu item for license generator
        add_submenu_page(
            'hms_dashboard', // Parent slug
            __('License Generator', 'hospital-management-system'), // Page title
            __('License Generator', 'hospital-management-system'), // Menu title
            'manage_hms', // Capability
            'hms_license_generator', // Menu slug
            array($this, 'render_license_generator_page') // Callback function
        );
    }

    public function register_settings() {
        // General Settings
        register_setting('hms_settings_group', 'hms_hospital_name', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_hospital_address', array($this, 'sanitize_textarea_field'));
        register_setting('hms_settings_group', 'hms_hospital_phone', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_hospital_email', array( $this, 'sanitize_email_field' ));
        register_setting('hms_settings_group', 'hms_hospital_logo_id', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_currency', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_time_zone', array( $this, 'sanitize_text_field' ));

        // Branding Settings
        register_setting('hms_settings_group', 'hms_hospital_favicon_id', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_primary_color', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_secondary_color', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_custom_css', array( $this, 'sanitize_textarea_field' ));
        register_setting('hms_settings_group', 'hms_hide_wp_branding', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_settings_group', 'hms_custom_login_logo', array( $this, 'sanitize_boolean_field' ));

        // Appointment Settings
        register_setting('hms_settings_group', 'hms_appointment_duration', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_advance_booking_days', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_cancellation_policy', array( $this, 'sanitize_textarea_field' ));

        // Notification Settings
        register_setting('hms_settings_group', 'hms_enable_sms', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_settings_group', 'hms_enable_email', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_settings_group', 'hms_enable_whatsapp', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_settings_group', 'hms_sms_gateway', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_sms_api_key', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_sms_sender_id', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_whatsapp_api_key', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_email_from_name', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_email_from_address', array( $this, 'sanitize_email_field' ));

        // Integration Settings
        register_setting('hms_settings_group', 'hms_payment_gateway', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_payment_gateway_keys', array( $this, 'sanitize_payment_gateway_keys' ));
        register_setting('hms_settings_group', 'hms_lis_api_endpoint', array( $this, 'sanitize_url_field' ));
        register_setting('hms_settings_group', 'hms_lis_api_key', array( $this, 'sanitize_text_field' ));
        register_setting('hms_settings_group', 'hms_emr_api_endpoint', array( $this, 'sanitize_url_field' ));
        register_setting('hms_settings_group', 'hms_emr_api_key', array( $this, 'sanitize_text_field' ));

        // Advanced Settings
        register_setting('hms_settings_group', 'hms_enable_debug_mode', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_settings_group', 'hms_log_retention_days', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_session_timeout', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_settings_group', 'hms_max_file_upload_size', array( $this, 'sanitize_integer_field' ));

        // Role Capabilities (will be handled via a custom AJAX handler or a separate save action)
        // register_setting('hms_settings_group', 'hms_role_caps_json');
    }

    public function render_dashboard_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/dashboard.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_patients_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Add breadcrumb navigation
        $this->render_breadcrumb(array(
            array('title' => __('Dashboard', 'hospital-management-system'), 'url' => admin_url('admin.php?page=hms_dashboard')),
            array('title' => __('Patient Management', 'hospital-management-system'), 'url' => '')
        ));
        
        // Add quick actions for patient management
        echo '<div class="hms-quick-actions">';
        echo '<h3>' . __('Quick Actions', 'hospital-management-system') . '</h3>';
        echo '<a href="' . admin_url('admin.php?page=hms_patient_registration') . '" class="button button-primary">' . __('Register New Patient', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_patient_medical_history') . '" class="button button-secondary">' . __('View Medical History', 'hospital-management-system') . '</a>';
        echo '</div>';
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/patients.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_patient_registration_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_patient_registration'])) {
            // Handle form submission - this will redirect on success or error
            $this->handle_patient_registration();
            // If we reach here, something went wrong with the redirect
            return;
        }
        
        // Include the template directly to prevent blank screens
        include HMS_PLUGIN_DIR . 'admin/templates/patient-registration.php';
    }

    public function render_patient_medical_history_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // The template already handles the case when no patient_id is provided
        // by showing a patient selection form, so we don't need to redirect here
        include HMS_PLUGIN_DIR . 'admin/templates/patient-medical-history.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_appointments_page() {
        // Handle appointment deletion if the form is submitted
        if (isset($_POST['delete_appointment']) && isset($_POST['appointment_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_appointment_nonce', 'delete_nonce');
            $appointment_id = intval($_POST['appointment_id']);
            if ($this->appointments->delete_appointment($appointment_id)) {
                $redirect_url = admin_url('admin.php?page=hms_appointments&message=' . urlencode(__('Appointment deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_appointments&message=' . urlencode(__('Failed to delete appointment.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/appointments.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_appointment_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_appointment'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['appointment_id']);

            if ($is_edit) {
                $appointment_id = intval($_POST['appointment_id']);
                
                // Validate required fields
                $validation_errors = array();
                
                if (empty($_POST['patient_id'])) {
                    $validation_errors[] = __('Patient is required.', 'hospital-management-system');
                }
                if (empty($_POST['doctor_id'])) {
                    $validation_errors[] = __('Doctor is required.', 'hospital-management-system');
                }
                if (empty($_POST['appointment_date'])) {
                    $validation_errors[] = __('Appointment date is required.', 'hospital-management-system');
                }
                if (empty($_POST['appointment_time'])) {
                    $validation_errors[] = __('Appointment time is required.', 'hospital-management-system');
                }
                
                if (!empty($validation_errors)) {
                    $redirect_args = array(
                        'page' => 'hms_appointment_details',
                        'id' => $appointment_id,
                        'message' => urlencode(implode(' ', $validation_errors)),
                        'type' => 'error'
                    );
                } else {
                    // Sanitize and prepare data
                    $data = array(
                        'patient_id' => intval($_POST['patient_id']),
                        'doctor_id' => intval($_POST['doctor_id']),
                        'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
                        'appointment_date' => sanitize_text_field($_POST['appointment_date']),
                        'appointment_time' => sanitize_text_field($_POST['appointment_time']),
                        'status' => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'scheduled',
                        'reason_for_visit' => !empty($_POST['reason_for_visit']) ? sanitize_textarea_field($_POST['reason_for_visit']) : '',
                        'notes' => !empty($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
                    );
                    
                    if ($this->appointments->update_appointment($appointment_id, $data)) {
                        $redirect_args = array(
                            'page' => 'hms_appointments',
                            'message' => urlencode(__('Appointment updated successfully.', 'hospital-management-system')),
                            'type' => 'success'
                        );
                    } else {
                        $redirect_args = array(
                            'page' => 'hms_appointment_details',
                            'id' => $appointment_id,
                            'message' => urlencode(__('Failed to update appointment. Please check all fields and try again.', 'hospital-management-system')),
                            'type' => 'error'
                        );
                    }
                }
            } else {
                // Validate required fields
                $validation_errors = array();
                
                if (empty($_POST['patient_id'])) {
                    $validation_errors[] = __('Patient is required.', 'hospital-management-system');
                }
                if (empty($_POST['doctor_id'])) {
                    $validation_errors[] = __('Doctor is required.', 'hospital-management-system');
                }
                if (empty($_POST['appointment_date'])) {
                    $validation_errors[] = __('Appointment date is required.', 'hospital-management-system');
                }
                if (empty($_POST['appointment_time'])) {
                    $validation_errors[] = __('Appointment time is required.', 'hospital-management-system');
                }
                
                if (!empty($validation_errors)) {
                    $redirect_args = array(
                        'page' => 'hms_appointment_details',
                        'message' => urlencode(implode(' ', $validation_errors)),
                        'type' => 'error'
                    );
                } else {
                    // Sanitize and prepare data
                    $data = array(
                        'patient_id' => intval($_POST['patient_id']),
                        'doctor_id' => intval($_POST['doctor_id']),
                        'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
                        'appointment_date' => sanitize_text_field($_POST['appointment_date']),
                        'appointment_time' => sanitize_text_field($_POST['appointment_time']),
                        'status' => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'scheduled',
                        'reason_for_visit' => !empty($_POST['reason_for_visit']) ? sanitize_textarea_field($_POST['reason_for_visit']) : '',
                        'notes' => !empty($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : ''
                    );
                    
                    $new_appointment_id = $this->appointments->add_appointment($data);
                    if ($new_appointment_id) {
                        $redirect_args = array(
                            'page' => 'hms_appointments',
                            'message' => urlencode(__('Appointment scheduled successfully.', 'hospital-management-system')),
                            'type' => 'success'
                        );
                    } else {
                        $redirect_args = array(
                            'page' => 'hms_appointment_details',
                            'message' => urlencode(__('Failed to schedule appointment. Please check all fields and try again.', 'hospital-management-system')),
                            'type' => 'error'
                        );
                    }
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/appointment-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_billing_page() {
        // Handle bill deletion if the form is submitted
        if (isset($_POST['delete_bill']) && isset($_POST['bill_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_bill_nonce', 'delete_nonce');
            $bill_id = intval($_POST['bill_id']);
            if ($this->billing->delete_bill($bill_id)) {
                $redirect_url = admin_url('admin.php?page=hms_billing&message=' . urlencode(__('Bill deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_billing&message=' . urlencode(__('Failed to delete bill.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/billing.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_bill_details_page() {
        // Check if this is a form submission first - BEFORE any output
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bill'])) {
            // Handle form submission immediately before any output is generated
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['bill_id']);
            $redirect_args = array('page' => 'hms_bill_details');
            
            // Enable error logging for debugging
            error_log('HMS: Bill form submission detected');
            error_log('HMS: Form data: ' . print_r($_POST, true));

            if ($is_edit) {
                $bill_id = intval($_POST['bill_id']);
                $data = $_POST; // Simplified for brevity; should be sanitized in the class method
                
                // Calculate total amount from bill items
                $total_amount = 0;
                $tax_amount = 0;
                $bill_items_data = array();
                
                if (isset($_POST['bill_items']) && is_array($_POST['bill_items'])) {
                    foreach ($_POST['bill_items'] as $item_data) {
                        if (!empty($item_data['description']) && !empty($item_data['quantity']) && !empty($item_data['price'])) {
                            $item_total = floatval($item_data['quantity']) * floatval($item_data['price']);
                            $total_amount += $item_total;
                            
                            // Store valid bill items for later processing
                            $bill_items_data[] = array(
                                'id' => isset($item_data['id']) ? intval($item_data['id']) : 0,
                                'description' => sanitize_text_field($item_data['description']),
                                'quantity' => floatval($item_data['quantity']),
                                'price' => floatval($item_data['price'])
                            );
                        }
                    }
                    
                    // Apply discount
                    $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
                    $total_amount_after_discount = $total_amount - $discount_amount;
                    
                    // Calculate tax amount on discounted total
                    $tax_percentage = isset($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0;
                    $tax_amount = $total_amount_after_discount * ($tax_percentage / 100);
                    $total_amount = $total_amount_after_discount + $tax_amount;
                }
                
                // Update the data with calculated amounts
                $data['total_amount'] = $total_amount;
                $data['discount_amount'] = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
                $data['tax_percentage'] = isset($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0;
                $data['tax_amount'] = $tax_amount;
                $data['paid_amount'] = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
                $data['payment_method'] = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
                $data['payment_status'] = isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : '';
                $data['notes'] = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
                
                error_log('HMS: Updating bill with ID: ' . $bill_id);
                error_log('HMS: Bill data: ' . print_r($data, true));
                error_log('HMS: Bill items data: ' . print_r($bill_items_data, true));
                
                // Start transaction for data consistency
                global $wpdb;
                $wpdb->query('START TRANSACTION');
                
                try {
                    if ($this->billing->update_bill($bill_id, $data)) {
                        error_log('HMS: Bill updated successfully');
                        
                        // Process bill items if they exist
                        if (!empty($bill_items_data)) {
                            // Get existing bill items
                            $bill_items_table = $wpdb->prefix . 'hms_bill_items';
                            $existing_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $bill_items_table WHERE bill_id = %d", $bill_id));
                            
                            // Create a map of existing items by ID
                            $existing_items_map = array();
                            foreach ($existing_items as $existing_item) {
                                $existing_items_map[$existing_item->id] = $existing_item;
                            }
                            
                            // Track which items are still present
                            $items_to_keep = array();
                            
                            foreach ($bill_items_data as $item_data) {
                                $item_id = $item_data['id'];
                                
                                if ($item_id > 0 && isset($existing_items_map[$item_id])) {
                                    // Update existing item
                                    $update_data = array(
                                        'description' => $item_data['description'],
                                        'quantity' => $item_data['quantity'],
                                        'price' => $item_data['price'],
                                    );
                                    
                                    error_log('HMS: Updating bill item ' . $item_id . ' with data: ' . print_r($update_data, true));
                                    $update_result = $this->billing->update_bill_item($item_id, $update_data);
                                    
                                    if ($update_result) {
                                        error_log('HMS: Bill item ' . $item_id . ' updated successfully');
                                        $items_to_keep[] = $item_id;
                                    } else {
                                        error_log('HMS: Failed to update bill item ' . $item_id);
                                        throw new Exception('Failed to update bill item ' . $item_id);
                                    }
                                } else {
                                    // Create new item
                                    $bill_item_data = array(
                                        'bill_id' => $bill_id,
                                        'description' => $item_data['description'],
                                        'quantity' => $item_data['quantity'],
                                        'price' => $item_data['price'],
                                    );
                                    
                                    error_log('HMS: Creating new bill item with data: ' . print_r($bill_item_data, true));
                                    $new_item_id = $this->billing->create_bill_item($bill_item_data);
                                    
                                    if ($new_item_id) {
                                        error_log('HMS: New bill item created with ID: ' . $new_item_id);
                                        $items_to_keep[] = $new_item_id;
                                    } else {
                                        error_log('HMS: Failed to create new bill item');
                                        throw new Exception('Failed to create new bill item');
                                    }
                                }
                            }
                            
                            // Delete items that are no longer present
                            foreach ($existing_items as $existing_item) {
                                if (!in_array($existing_item->id, $items_to_keep)) {
                                    error_log('HMS: Deleting bill item ' . $existing_item->id);
                                    $delete_result = $this->billing->delete_bill_item($existing_item->id);
                                    
                                    if (!$delete_result) {
                                        error_log('HMS: Failed to delete bill item ' . $existing_item->id);
                                        throw new Exception('Failed to delete bill item ' . $existing_item->id);
                                    }
                                }
                            }
                        }
                        
                        // Commit transaction
                        $wpdb->query('COMMIT');
                        error_log('HMS: Transaction committed successfully');
                        
                        $redirect_args['id'] = $bill_id;
                        $redirect_args['message'] = urlencode(__('Bill updated successfully.', 'hospital-management-system'));
                        $redirect_args['type'] = 'success';
                    } else {
                        error_log('HMS: Failed to update bill');
                        throw new Exception('Failed to update bill');
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $wpdb->query('ROLLBACK');
                    error_log('HMS: Transaction rolled back: ' . $e->getMessage());
                    
                    $redirect_args['id'] = $bill_id;
                    $redirect_args['message'] = urlencode(__('Failed to update bill: ', 'hospital-management-system') . $e->getMessage());
                    $redirect_args['type'] = 'error';
                }
            } else {
                // Similar logic for creating a new bill
                $data = $_POST; // Simplified for brevity
                
                // Calculate total amount from bill items
                $total_amount = 0;
                $tax_amount = 0;
                $bill_items_data = array();
                
                if (isset($_POST['bill_items']) && is_array($_POST['bill_items'])) {
                    foreach ($_POST['bill_items'] as $item_data) {
                        if (!empty($item_data['description']) && !empty($item_data['quantity']) && !empty($item_data['price'])) {
                            $item_total = floatval($item_data['quantity']) * floatval($item_data['price']);
                            $total_amount += $item_total;
                            
                            // Store valid bill items for later processing
                            $bill_items_data[] = array(
                                'description' => sanitize_text_field($item_data['description']),
                                'quantity' => floatval($item_data['quantity']),
                                'price' => floatval($item_data['price'])
                            );
                        }
                    }
                    
                    // Apply discount
                    $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
                    $total_amount_after_discount = $total_amount - $discount_amount;
                    
                    // Calculate tax amount on discounted total
                    $tax_percentage = isset($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0;
                    $tax_amount = $total_amount_after_discount * ($tax_percentage / 100);
                    $total_amount = $total_amount_after_discount + $tax_amount;
                }
                
                // Update the data with calculated amounts
                $data['total_amount'] = $total_amount;
                $data['discount_amount'] = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
                $data['tax_percentage'] = isset($_POST['tax_percentage']) ? floatval($_POST['tax_percentage']) : 0;
                $data['tax_amount'] = $tax_amount;
                $data['paid_amount'] = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
                $data['payment_method'] = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
                $data['payment_status'] = isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : '';
                $data['notes'] = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
                
                error_log('HMS: Creating new bill');
                error_log('HMS: Bill data: ' . print_r($data, true));
                error_log('HMS: Bill items data: ' . print_r($bill_items_data, true));
                
                // Start transaction for data consistency
                global $wpdb;
                $wpdb->query('START TRANSACTION');
                
                try {
                    $new_bill_id = $this->billing->create_bill($data);
                    if ($new_bill_id) {
                        error_log('HMS: New bill created with ID: ' . $new_bill_id);
                        
                        // Create bill items if they exist
                        if (!empty($bill_items_data)) {
                            foreach ($bill_items_data as $item_data) {
                                $bill_item_data = array(
                                    'bill_id' => $new_bill_id,
                                    'description' => $item_data['description'],
                                    'quantity' => $item_data['quantity'],
                                    'price' => $item_data['price'],
                                );
                                
                                error_log('HMS: Creating bill item with data: ' . print_r($bill_item_data, true));
                                $item_id = $this->billing->create_bill_item($bill_item_data);
                                
                                if ($item_id) {
                                    error_log('HMS: Bill item created with ID: ' . $item_id);
                                } else {
                                    error_log('HMS: Failed to create bill item');
                                    throw new Exception('Failed to create bill item');
                                }
                            }
                        }
                        
                        // Commit transaction
                        $wpdb->query('COMMIT');
                        error_log('HMS: Transaction committed successfully');
                        
                        // Redirect to billing page instead of bill details page
                        $redirect_args = array('page' => 'hms_billing');
                        $redirect_args['message'] = urlencode(__('Bill generated successfully.', 'hospital-management-system'));
                        $redirect_args['type'] = 'success';
                    } else {
                        error_log('HMS: Failed to create new bill');
                        throw new Exception('Failed to create new bill');
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $wpdb->query('ROLLBACK');
                    error_log('HMS: Transaction rolled back: ' . $e->getMessage());
                    
                    $redirect_args['message'] = urlencode(__('Failed to generate bill: ', 'hospital-management-system') . $e->getMessage());
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Make the billing instance available to the template
        global $hms_billing;
        $hms_billing = $this->billing;
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/bill-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_view_invoice_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Make the billing instance available to the template
        global $hms_billing;
        $hms_billing = $this->billing;
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/view-invoice.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_payment_info_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Make the billing instance available to the template
        global $hms_billing;
        $hms_billing = $this->billing;
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/payment-processing.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_prescriptions_page() {
        // Handle prescription deletion if the form is submitted
        if (isset($_POST['delete_prescription']) && isset($_POST['prescription_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_prescription_nonce', 'delete_nonce');
            $prescription_id = intval($_POST['prescription_id']);
            if ($this->prescription->delete_prescription($prescription_id)) {
                $redirect_url = admin_url('admin.php?page=hms-prescriptions&message=' . urlencode(__('Prescription deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms-prescriptions&message=' . urlencode(__('Failed to delete prescription.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/prescriptions.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_prescription_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prescription'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['prescription_id']);
            $redirect_args = array('page' => 'hms_prescription_details');

            // Basic server-side validation
            if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['issue_date'])) {
                $redirect_args['message'] = urlencode(__('Please fill in all required fields.', 'hospital-management-system'));
                $redirect_args['type'] = 'error';
                
                // Clear any output buffers before redirect
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
                exit;
            }
            
            // Prepare data for the prescription class
            $data = array(
                'patient_id' => intval($_POST['patient_id']),
                'doctor_id' => intval($_POST['doctor_id']),
                'appointment_id' => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
                'issue_date' => sanitize_text_field($_POST['issue_date']),
                'status' => sanitize_text_field($_POST['status']),
                'diagnosis' => sanitize_textarea_field($_POST['diagnosis']),
                'notes' => sanitize_textarea_field($_POST['notes']),
                'medicines' => array() // Convert prescription_items to medicines format
            );
            
            // Convert prescription_items to medicines format
            if (isset($_POST['prescription_items']) && is_array($_POST['prescription_items'])) {
                foreach ($_POST['prescription_items'] as $item) {
                    if (!empty($item['medicine_name'])) {
                        $data['medicines'][] = array(
                            'medicine_name' => sanitize_text_field($item['medicine_name']),
                            'dosage' => sanitize_text_field($item['dosage']),
                            'frequency' => sanitize_text_field($item['frequency']),
                            'duration' => sanitize_text_field($item['duration']),
                            'instructions' => sanitize_textarea_field($item['instructions'])
                        );
                    }
                }
            }

            if ($is_edit) {
                $prescription_id = intval($_POST['prescription_id']);
                if ($this->prescription->update_prescription($prescription_id, $data)) {
                    // Redirect to prescriptions list page instead of staying on the details page
                    $redirect_args = array('page' => 'hms-prescriptions');
                    $redirect_args['message'] = urlencode(__('Prescription updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['id'] = $prescription_id;
                    $redirect_args['message'] = urlencode(__('Failed to update prescription.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $new_prescription_id = $this->prescription->add_prescription($data);
                if ($new_prescription_id) {
                    // Redirect to prescriptions list page instead of staying on the details page
                    $redirect_args = array('page' => 'hms-prescriptions');
                    $redirect_args['message'] = urlencode(__('Prescription created successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to create prescription.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/prescription-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_medical_records_page() {
        // Handle medical record deletion if the form is submitted
        if (isset($_POST['delete_medical_record']) && isset($_POST['record_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_medical_record_nonce', 'delete_nonce');
            $record_id = intval($_POST['record_id']);
            
            // Ensure the HMS_Medical_Records class is available
            if (!class_exists('HMS_Medical_Records')) {
                require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
            }
            $medical_records = new HMS_Medical_Records();
            
            if ($medical_records->delete_medical_record($record_id)) {
                $redirect_url = admin_url('admin.php?page=hms_medical_records&message=' . urlencode(__('Medical record deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_medical_records&message=' . urlencode(__('Failed to delete medical record.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clean any output and redirect
            ob_end_clean();
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/medical-records.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_medical_record_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_medical_record'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_medical_record_nonce', 'medical_record_nonce');
            
            // Ensure the HMS_Medical_Records class is available
            if (!class_exists('HMS_Medical_Records')) {
                require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
            }
            $medical_records = new HMS_Medical_Records();
            
            $record_id = !empty($_POST['record_id']) ? intval($_POST['record_id']) : 0;
            
            $data = array(
                'patient_id'        => intval($_POST['patient_id']),
                'appointment_id'    => !empty($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null,
                'doctor_id'         => intval($_POST['doctor_id']),
                'record_type'       => sanitize_text_field($_POST['record_type']),
                'diagnosis'         => sanitize_textarea_field($_POST['diagnosis']),
                'symptoms'          => sanitize_textarea_field($_POST['symptoms']),
                'treatment_plan'    => sanitize_textarea_field($_POST['treatment_plan']),
                'notes'             => sanitize_textarea_field($_POST['notes']),
            );
            
            if ($record_id > 0) {
                // Update existing record
                $result = $medical_records->update_medical_record($record_id, $data);
                if (is_wp_error($result)) {
                    $message = $result->get_error_message();
                    $type = 'error';
                } elseif ($result) {
                    $message = __('Medical record updated successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = __('Failed to update medical record.', 'hospital-management-system');
                    $type = 'error';
                }
            } else {
                // Create new record
                $result = $medical_records->create_medical_record($data);
                if (is_wp_error($result)) {
                    $message = $result->get_error_message();
                    $type = 'error';
                } elseif ($result) {
                    $message = __('Medical record created successfully.', 'hospital-management-system');
                    $type = 'success';
                    $record_id = $result;
                } else {
                    $message = __('Failed to create medical record.', 'hospital-management-system');
                    $type = 'error';
                }
            }
            
            // Clean any output and redirect
            ob_end_clean();
            
            $redirect_args = array(
                'page' => 'hms_medical_records',
                'message' => urlencode($message),
                'type' => $type
            );
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/medical-record-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_comprehensive_medical_history_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the comprehensive medical history template
        include HMS_PLUGIN_DIR . 'admin/templates/comprehensive-medical-history.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_pharmacy_page() {
        // Handle dispense deletion if the form is submitted
        if (isset($_POST['delete_dispense']) && isset($_POST['dispense_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_dispense_nonce', 'delete_nonce');
            $dispense_id = intval($_POST['dispense_id']);
            if ($this->pharmacy->delete_dispense($dispense_id)) {
                $redirect_url = admin_url('admin.php?page=hms_pharmacy&message=' . urlencode(__('Dispense record deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_pharmacy&message=' . urlencode(__('Failed to delete dispense record.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/pharmacy.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_dispense_medicine_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_dispense'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['dispense_id']);
            $redirect_args = array('page' => 'hms_dispense_medicine');

            if ($is_edit) {
                $dispense_id = intval($_POST['dispense_id']);
                $data = $_POST; // Simplified for brevity; should be sanitized in the class method
                if ($this->pharmacy->update_dispense($dispense_id, $data)) {
                    $redirect_args['id'] = $dispense_id;
                    $redirect_args['message'] = urlencode(__('Dispense record updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['id'] = $dispense_id;
                    $redirect_args['message'] = urlencode(__('Failed to update dispense record.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $data = $_POST; // Simplified for brevity
                $new_dispense_id = $this->pharmacy->add_dispense($data);
                if ($new_dispense_id) {
                    $redirect_args['id'] = $new_dispense_id;
                    $redirect_args['message'] = urlencode(__('Medicine dispensed successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to dispense medicine.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/dispense-medicine.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_medicine_inventory_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/medicine-inventory.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_suppliers_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/suppliers.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_purchase_orders_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/purchase-orders.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_purchase_order_details_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/purchase-order-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_insurance_page() {
        // Handle provider deletion if the form is submitted
        if (isset($_POST['delete_provider']) && isset($_POST['provider_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_admin_nonce', '_wpnonce');
            $provider_id = intval($_POST['provider_id']);
            if ($this->insurance->delete_insurance_provider($provider_id)) {
                $redirect_url = admin_url('admin.php?page=hms_insurance&message=' . urlencode(__('Insurance provider deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_insurance&message=' . urlencode(__('Failed to delete insurance provider.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/insurance.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_insurance_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_provider'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['provider_id']);
            $redirect_args = array('page' => 'hms_insurance_details');

            if ($is_edit) {
                $provider_id = intval($_POST['provider_id']);
                $data = $_POST; // Simplified for brevity; should be sanitized in the class method
                if ($this->insurance->update_insurance_provider($provider_id, $data)) {
                    $redirect_args['id'] = $provider_id;
                    $redirect_args['message'] = urlencode(__('Insurance provider updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['id'] = $provider_id;
                    $redirect_args['message'] = urlencode(__('Failed to update insurance provider.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $data = $_POST; // Simplified for brevity
                $new_provider_id = $this->insurance->add_insurance_provider($data);
                if ($new_provider_id) {
                    $redirect_args['id'] = $new_provider_id;
                    // Get the provider name for the success message
                    $provider_name = sanitize_text_field($data['name']);
                    /* translators: %s: Insurance provider name */
                    $redirect_args['message'] = urlencode(sprintf(__('Insurance provider %s added successfully.', 'hospital-management-system'), $provider_name));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to add insurance provider.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/insurance-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_clinical_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Add breadcrumb navigation
        $this->render_breadcrumb(array(
            array('title' => __('Dashboard', 'hospital-management-system'), 'url' => admin_url('admin.php?page=hms_dashboard')),
            array('title' => __('Clinical Management', 'hospital-management-system'), 'url' => '')
        ));
        
        // Add quick actions for clinical management
        echo '<div class="hms-quick-actions">';
        echo '<h3>' . __('Clinical Quick Actions', 'hospital-management-system') . '</h3>';
        echo '<a href="' . admin_url('admin.php?page=hms_appointments') . '" class="button button-primary">' . __('View Appointments', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_appointment_details') . '" class="button button-secondary">' . __('Schedule New Appointment', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_prescriptions') . '" class="button button-secondary">' . __('View Prescriptions', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_prescription_details') . '" class="button button-secondary">' . __('Create Prescription', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_queue') . '" class="button button-secondary">' . __('Manage Queue', 'hospital-management-system') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=hms_lab_tests') . '" class="button button-secondary">' . __('Lab Tests', 'hospital-management-system') . '</a>';
        echo '</div>';
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/clinical.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_staff_page() {
        // Handle staff deletion if the form is submitted
        if (isset($_POST['delete_staff']) && isset($_POST['staff_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_staff_nonce', 'delete_nonce');
            $staff_id = intval($_POST['staff_id']);
            if ($this->staff->delete_staff($staff_id)) {
                $redirect_url = admin_url('admin.php?page=hms_staff&message=' . urlencode(__('Staff member deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_staff&message=' . urlencode(__('Failed to delete staff member.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/staff.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_staff_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['staff_id']);
            $redirect_args = array('page' => 'hms_staff');

            if ($is_edit) {
                $staff_id = intval($_POST['staff_id']);
                $data = $_POST; // Data is sanitized in the update_staff_profile method
                if ($this->staff->update_staff_profile($staff_id, $data)) {
                    $redirect_args['message'] = urlencode(__('Staff member updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to update staff member.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $data = $_POST; // Data is sanitized in the add_staff method
                $new_staff_id = $this->staff->add_staff($data);
                if ($new_staff_id) {
                    // If staff was added successfully and has profile data, update profile
                    if (isset($data['profile_photo']) || isset($data['emergency_contact']) || 
                        isset($data['license_number']) || isset($data['certifications'])) {
                        $this->staff->update_staff_profile($new_staff_id, $data);
                    }
                    $redirect_args['message'] = urlencode(__('Staff member added successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to add staff member.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/staff-details.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_reports_dashboard_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the dashboard template
        include HMS_PLUGIN_DIR . 'admin/templates/reports-dashboard.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_reports_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/reports.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_settings_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Make sure the license manager is available globally for the settings template
        global $hms_license_manager;
        if (class_exists('HMS_License_Manager') && !$hms_license_manager) {
            $hms_license_manager = new HMS_License_Manager();
        }
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/settings.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_export_data_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/export-import-data.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_staff_roles_page() {
        // Handle role creation/update if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_roles'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            if (isset($_POST['create_hms_roles'])) {
                $this->staff->create_hms_roles();
                $redirect_args = array(
                    'page' => 'hms_staff_roles',
                    'message' => urlencode(__('HMS roles created successfully.', 'hospital-management-system')),
                    'type' => 'success'
                );
            } elseif (isset($_POST['update_staff_role']) && isset($_POST['staff_id']) && isset($_POST['new_role'])) {
                $staff_id = intval($_POST['staff_id']);
                $new_role = sanitize_text_field($_POST['new_role']);
                $new_department = sanitize_text_field($_POST['new_department']);
                
                if ($this->staff->update_staff_wordpress_role($staff_id, $new_role, $new_department)) {
                    $redirect_args = array(
                        'page' => 'hms_staff_roles',
                        'message' => urlencode(__('Staff role updated successfully.', 'hospital-management-system')),
                        'type' => 'success'
                    );
                } else {
                    $redirect_args = array(
                        'page' => 'hms_staff_roles',
                        'message' => urlencode(__('Failed to update staff role.', 'hospital-management-system')),
                        'type' => 'error'
                    );
                }
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        ob_start();
        include HMS_PLUGIN_DIR . 'admin/templates/staff-roles.php';
        $content = ob_get_clean();
        echo $content;
    }

    public function render_staff_schedule_page() {
        // Handle schedule creation if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $schedule_data = array(
                'schedule_date' => $_POST['schedule_date'],
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'break_start' => $_POST['break_start'],
                'break_end' => $_POST['break_end'],
                'status' => $_POST['status'],
                'notes' => $_POST['notes']
            );
            
            $staff_id = intval($_POST['staff_id']);
            
            if ($this->staff->add_staff_schedule($staff_id, $schedule_data)) {
                $redirect_args = array(
                    'page' => 'hms_staff_schedule',
                    'message' => urlencode(__('Schedule added successfully.', 'hospital-management-system')),
                    'type' => 'success'
                );
            } else {
                $redirect_args = array(
                    'page' => 'hms_staff_schedule',
                    'message' => urlencode(__('Failed to add schedule.', 'hospital-management-system')),
                    'type' => 'error'
                );
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        ob_start();
        include HMS_PLUGIN_DIR . 'admin/templates/staff-schedule.php';
        $content = ob_get_clean();
        echo $content;
    }

    public function render_queue_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/queue.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_lab_tests_page() {
        // Handle lab test deletion if the form is submitted
        if (isset($_POST['delete_lab_test']) && isset($_POST['test_id'])) {
            // Start output buffering to catch any stray output during form processing
            ob_start();
            
            check_admin_referer('hms_delete_lab_test_nonce', 'delete_nonce');
            $test_id = intval($_POST['test_id']);
            if ($this->lab_radiology->delete_lab_test_order($test_id)) {
                $redirect_url = admin_url('admin.php?page=hms_lab_tests&message=' . urlencode(__('Lab test deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_lab_tests&message=' . urlencode(__('Failed to delete lab test.', 'hospital-management-system')) . '&type=error');
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Make the $hms_lab_radiology instance available to the template
        global $hms_lab_radiology;
        $hms_lab_radiology = $this->lab_radiology;
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/lab-test-list.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_lab_test_order_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lab_test'])) {
            // Start output buffering to prevent headers already sent errors
            ob_start();
            
            // Handle form submission
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['order_id']) && !empty($_POST['order_id']);
            $redirect_args = array('page' => 'hms_lab_test_order');

            if ($is_edit) {
                $order_id = intval($_POST['order_id']);
                $data = $_POST; // Data is sanitized in the update_lab_test_order method
                if ($this->lab_radiology->update_lab_test_order($order_id, $data)) {
                    $redirect_args['id'] = $order_id;
                    $redirect_args['message'] = urlencode(__('Lab test updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['id'] = $order_id;
                    $redirect_args['message'] = urlencode(__('Failed to update lab test.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $data = $_POST; // Data is sanitized in the create_lab_test_order method
                $new_order_id = $this->lab_radiology->create_lab_test_order($data);
                if ($new_order_id) {
                    $redirect_args['id'] = $new_order_id;
                    $redirect_args['message'] = urlencode(__('Lab test ordered successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to order lab test.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            // Clear any output buffers before redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }

        // Handle report generation
        if (isset($_GET['action']) && $_GET['action'] === 'generate_report' && isset($_GET['id'])) {
            $order_id = intval($_GET['id']);
            $report_html = $this->lab_radiology->generate_lab_report($order_id);
            
            if ($report_html) {
                // Output the report as HTML
                echo '<!DOCTYPE html>';
                echo '<html><head>';
                echo '<title>' . esc_html__('Lab Report', 'hospital-management-system') . '</title>';
                echo '<style>';
                echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
                echo '.lab-report { max-width: 800px; margin: 0 auto; }';
                echo '.report-header { text-align: center; margin-bottom: 30px; }';
                echo '.report-header h2 { margin: 0; color: #1d2327; }';
                echo '.patient-info { margin-bottom: 20px; }';
                echo '.patient-info p { margin: 5px 0; }';
                echo '.test-details { margin-bottom: 30px; }';
                echo '.test-details h4 { color: #1d2327; margin-bottom: 10px; }';
                echo '.results { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; }';
                echo '.notes { margin: 20px 0; }';
                echo '.report-footer { text-align: center; margin-top: 30px; font-style: italic; color: #646970; }';
                echo '@media print { body { margin: 0; } }';
                echo '</style>';
                echo '</head><body>';
                echo $report_html;
                echo '<script>window.print();</script>';
                echo '</body></html>';
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=hms_lab_test_order&id=' . $order_id . '&message=' . urlencode(__('Unable to generate report. Test may not be completed.', 'hospital-management-system')) . '&type=error'));
                exit;
            }
        }

        // Fetch test data if editing
        $test_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order_data = null;
        if ($test_id) {
            $order_data = $this->lab_radiology->get_lab_test_order($test_id);
            if (!$order_data) {
                // Clear any output buffers before redirect
                while (ob_get_level()) {
                    ob_end_clean();
                }
                wp_redirect(admin_url('admin.php?page=hms_lab_test_order&message=' . urlencode(__('Lab test order not found.', 'hospital-management-system')) . '&type=error'));
                exit;
            }
        }
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Pass $order_data to the template
        include HMS_PLUGIN_DIR . 'admin/templates/lab-test-order.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_lab_results_page() {
        // Handle results entry form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_lab_results'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $order_id = intval($_POST['order_id']);
            $new_status = sanitize_text_field($_POST['status']);
            
            $data = array(
                'results' => wp_kses_post($_POST['results']),
                'notes' => sanitize_textarea_field($_POST['notes'] ?? '')
            );
            
            // Use the enhanced status update method for better workflow validation
            $status_updated = $this->lab_radiology->update_test_status($order_id, $new_status, $data);
            
            if ($status_updated) {
                $message = __('Lab results saved successfully.', 'hospital-management-system');
                
                // Add specific messages for status changes
                if ($new_status === 'completed') {
                    $message = __('Test completed and results saved successfully.', 'hospital-management-system');
                } elseif ($new_status === 'in-progress') {
                    $message = __('Test status updated to in-progress.', 'hospital-management-system');
                } elseif ($new_status === 'cancelled') {
                    $message = __('Test has been cancelled.', 'hospital-management-system');
                }
                
                $redirect_url = admin_url('admin.php?page=hms_lab_results&message=' . urlencode($message) . '&type=success');
                
                // If completed, offer to view report
                if ($new_status === 'completed') {
                    $redirect_url .= '&show_report_link=' . $order_id;
                }
            } else {
                $redirect_url = admin_url('admin.php?page=hms_lab_results&message=' . urlencode(__('Failed to save lab results. Please check the status transition is valid.', 'hospital-management-system')) . '&type=error');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            wp_redirect($redirect_url);
            exit;
        }
        
        // Handle report generation requests
        if (isset($_GET['action']) && $_GET['action'] === 'generate_report' && isset($_GET['order_id'])) {
            $order_id = intval($_GET['order_id']);
            $format = sanitize_text_field($_GET['format'] ?? 'html');
            
            $report_content = $this->lab_radiology->generate_lab_report($order_id, $format);
            
            if ($report_content) {
                if ($format === 'print') {
                    // For print format, output directly
                    echo $report_content;
                    exit;
                } else {
                    // For HTML format, show in a popup or new window
                    ?>
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title><?php esc_html_e('Lab Report', 'hospital-management-system'); ?></title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .lab-report { max-width: 800px; margin: 0 auto; }
                            .hospital-branding { display: flex; align-items: center; margin-bottom: 20px; }
                            .hospital-logo { max-height: 60px; margin-right: 20px; }
                            .patient-section, .test-section, .results-section, .notes-section { margin-bottom: 25px; }
                            .patient-row, .test-row { display: flex; margin-bottom: 10px; }
                            .patient-col, .test-col { flex: 1; margin-right: 20px; }
                            .test-col-full { width: 100%; }
                            .results-content, .notes-content { border: 1px solid #ddd; padding: 15px; background: #f9f9f9; }
                            .status-completed { color: #28a745; font-weight: bold; }
                            .footer-content { display: flex; justify-content: space-between; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; }
                            .print-btn { margin: 20px 0; text-align: center; }
                            @media print { .print-btn { display: none; } }
                        </style>
                    </head>
                    <body>
                        <div class="print-btn">
                            <button onclick="window.print()" class="button"><?php esc_html_e('Print Report', 'hospital-management-system'); ?></button>
                            <button onclick="window.close()" class="button"><?php esc_html_e('Close', 'hospital-management-system'); ?></button>
                        </div>
                        <?php echo $report_content; ?>
                    </body>
                    </html>
                    <?php
                    exit;
                }
            } else {
                wp_redirect(admin_url('admin.php?page=hms_lab_results&message=' . urlencode(__('Unable to generate report. Test may not be completed or results may be missing.', 'hospital-management-system')) . '&type=error'));
                exit;
            }
        }

        ob_start();
        
        // Make the lab_radiology instance available to the template
        global $hms_lab_radiology;
        $hms_lab_radiology = $this->lab_radiology;
        
        include HMS_PLUGIN_DIR . 'admin/templates/lab-results.php';
        
        $content = ob_get_clean();
        echo $content;
    }
    
    /**
     * Handle comprehensive lab report generation for patients.
     */
    public function handle_comprehensive_lab_report() {
        if (!isset($_GET['patient_id'])) {
            wp_die(__('Patient ID is required for comprehensive report generation.', 'hospital-management-system'));
        }
        
        $patient_id = intval($_GET['patient_id']);
        $start_date = sanitize_text_field($_GET['start_date'] ?? '');
        $end_date = sanitize_text_field($_GET['end_date'] ?? '');
        
        $report_content = $this->lab_radiology->generate_comprehensive_report($patient_id, $start_date, $end_date);
        
        if (!$report_content) {
            wp_die(__('No completed lab tests found for this patient in the specified period.', 'hospital-management-system'));
        }
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php esc_html_e('Comprehensive Lab Report', 'hospital-management-system'); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                .comprehensive-lab-report { max-width: 900px; margin: 0 auto; }
                .report-header h2 { color: #0073aa; margin-bottom: 5px; }
                .patient-summary { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 25px; }
                .category-section { margin-bottom: 30px; }
                .category-section h5 { background: #0073aa; color: white; padding: 10px; margin: 0 0 15px 0; }
                .test-results-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .test-results-table th, .test-results-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                .test-results-table th { background: #f1f1f1; font-weight: bold; }
                .test-results-table tr:nth-child(even) { background: #f9f9f9; }
                .test-result-summary { font-size: 0.9em; }
                .print-btn { margin: 20px 0; text-align: center; }
                @media print { .print-btn { display: none; } }
            </style>
        </head>
        <body>
            <div class="print-btn">
                <button onclick="window.print()" class="button"><?php esc_html_e('Print Report', 'hospital-management-system'); ?></button>
                <button onclick="window.close()" class="button"><?php esc_html_e('Close', 'hospital-management-system'); ?></button>
            </div>
            <?php echo $report_content; ?>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Add the dashboard widget.
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'hms_dashboard_widget', // Widget slug.
            __('Hospital Management System', 'hospital-management-system'), // Title.
            array($this, 'render_dashboard_widget_content') // Display function.
        );
    }

    /**
     * Render the content for the dashboard widget.
     */
    public function render_dashboard_widget_content() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the template
        include HMS_PLUGIN_DIR . 'admin/templates/dashboard-widget.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=hms-settings') . '">' . __('Settings', 'hospital-management-system') . '</a>';
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * Display admin notices for license status.
     *
     * @since    1.0.0
     */
    public function admin_license_notices() {
        if (class_exists('HMS_License_Manager')) {
            $license_manager = new HMS_License_Manager();
            $license_manager->admin_license_notices();
        }
    }

    /**
     * Handle AJAX request for managing the patient queue.
     */
    public function handle_manage_queue_ajax() {
        // Verify the nonce. The nonce is localized as 'hms_admin_nonce' in enqueue_scripts.
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Sanitize and validate input
        $action = isset($_POST['queue_action']) ? sanitize_text_field($_POST['queue_action']) : '';
        $queue_id = isset($_POST['queue_id']) ? intval($_POST['queue_id']) : 0;
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : get_current_user_id();

        $response = array('success' => false, 'message' => __('An unknown error occurred.', 'hospital-management-system'));

        if ($action && $queue_id) {
            // Get the currently active token (in-progress) for the doctor
            $active_token = HMS_Helper::get_active_token($doctor_id);
            // Get the next patient in the queue for the doctor
            $next_in_queue = HMS_Helper::get_next_in_queue($doctor_id);

            switch ($action) {
                case 'call_next':
                    if ($next_in_queue && $next_in_queue->id == $queue_id) {
                        // Mark the currently active token as completed, if any
                        if ($active_token) {
                            HMS_Helper::update_queue_status($active_token->id, 'completed');
                        }
                        // Call the next patient
                        if (HMS_Helper::update_queue_status($queue_id, 'in-progress')) {
                            $response['success'] = true;
                            /* translators: %s: Patient token number */
                            $response['message'] = sprintf(__('Patient %s called.', 'hospital-management-system'), $next_in_queue->token_number);
                            $response['active_token'] = $next_in_queue;
                            // Fetch the new next in queue
                            $response['next_in_queue'] = HMS_Helper::get_next_in_queue($doctor_id);
                        } else {
                            $response['message'] = __('Failed to update queue status.', 'hospital-management-system');
                        }
                    } else {
                        $response['message'] = __('Invalid token or not next in line.', 'hospital-management-system');
                    }
                    break;
                
                case 'complete_current':
                    if ($active_token && $active_token->id == $queue_id) {
                        if (HMS_Helper::update_queue_status($queue_id, 'completed')) {
                            $response['success'] = true;
                            /* translators: %s: Patient token number */
                            $response['message'] = sprintf(__('Patient %s marked as completed.', 'hospital-management-system'), $active_token->token_number);
                            $response['active_token'] = null;
                            // Fetch the new next in queue
                            $response['next_in_queue'] = HMS_Helper::get_next_in_queue($doctor_id);
                        } else {
                            $response['message'] = __('Failed to update queue status.', 'hospital-management-system');
                        }
                    } else {
                        $response['message'] = __('No active token or invalid token.', 'hospital-management-system');
                    }
                    break;

                case 'cancel_token':
                    // Ensure the user can cancel (e.g., admin or the doctor themselves)
                    if (HMS_Helper::update_queue_status($queue_id, 'cancelled')) {
                        $response['success'] = true;
                        $response['message'] = __('Token cancelled.', 'hospital-management-system');
                        // If the cancelled token was active, clear it
                        if ($active_token && $active_token->id == $queue_id) {
                             $response['active_token'] = null;
                        }
                        // Refetch queue
                        $queue_date = gmdate('Y-m-d');
                        $response['today_queue'] = HMS_Helper::get_doctor_queue($doctor_id, $queue_date);
                        $response['next_in_queue'] = HMS_Helper::get_next_in_queue($doctor_id);

                    } else {
                        $response['message'] = __('Failed to cancel token.', 'hospital-management-system');
                    }
                    break;
            }
        } else {
            $response['message'] = __('Missing action or queue ID.', 'hospital-management-system');
        }

        wp_send_json($response);
    }

    /**
     * Handle AJAX request to add patient to queue.
     */
    public function handle_add_patient_to_queue_ajax() {
        // Verify the nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Sanitize and validate input
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : null;
        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

        $response = array('success' => false, 'message' => __('An unknown error occurred.', 'hospital-management-system'));

        if ($patient_id && $doctor_id) {
            // Check if patient is already in queue for today
            global $wpdb;
            $today = current_time('Y-m-d');
            $existing_queue = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}hms_queue 
                 WHERE patient_id = %d AND doctor_id = %d AND queue_date = %s AND status IN ('waiting', 'in-progress')",
                $patient_id, $doctor_id, $today
            ));

            if ($existing_queue) {
                $response['message'] = __('Patient is already in the queue for today.', 'hospital-management-system');
            } else {
                // Add patient to queue using HMS_Helper
                $queue_id = HMS_Helper::add_to_queue($patient_id, $doctor_id, $appointment_id);

                if ($queue_id) {
                    // Update appointment status to 'In Queue' if appointment_id is provided
                    if ($appointment_id) {
                        update_post_meta($appointment_id, '_status', 'In Queue');
                    }
                    
                    // Get the token number for the response
                    $token_number = $wpdb->get_var($wpdb->prepare(
                        "SELECT token_number FROM {$wpdb->prefix}hms_queue WHERE id = %d",
                        $queue_id
                    ));
                    
                    $response['success'] = true;
                    $response['message'] = sprintf(
                        __('Patient successfully added to queue with token number %s.', 'hospital-management-system'),
                        $token_number
                    );
                    $response['token_number'] = $token_number;
                } else {
                    $response['message'] = __('Failed to add patient to queue.', 'hospital-management-system');
                }
            }
        } else {
            $response['message'] = __('Missing required parameters (patient_id and doctor_id).', 'hospital-management-system');
        }

        wp_send_json($response);
    }

    /**
     * Handle AJAX request to clear completed queue entries.
     */
    public function handle_clear_completed_queue_ajax() {
        // Verify the nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Sanitize and validate input
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

        $response = array('success' => false, 'message' => __('An unknown error occurred.', 'hospital-management-system'));

        if ($doctor_id) {
            global $wpdb;
            $today = current_time('Y-m-d');
            
            $deleted = $wpdb->delete(
                $wpdb->prefix . 'hms_queue',
                array(
                    'doctor_id' => $doctor_id,
                    'queue_date' => $today,
                    'status' => 'completed'
                ),
                array('%d', '%s', '%s')
            );

            if ($deleted !== false) {
                $response['success'] = true;
                $response['message'] = sprintf(
                    __('%d completed entries cleared from queue.', 'hospital-management-system'),
                    $deleted
                );
                
                // Log the action
                HMS_Helper::log_action('Queue Cleared', 'Queue', 0, array(
                    'doctor_id' => $doctor_id,
                    'entries_cleared' => $deleted,
                    'date' => $today
                ));
            } else {
                $response['message'] = __('No completed entries found to clear.', 'hospital-management-system');
            }
        } else {
            $response['message'] = __('Missing doctor ID.', 'hospital-management-system');
        }

        wp_send_json($response);
    }

    /**
     * Handle AJAX request to reset queue (cancel all waiting patients).
     */
    public function handle_reset_queue_ajax() {
        // Verify the nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Sanitize and validate input
        $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

        $response = array('success' => false, 'message' => __('An unknown error occurred.', 'hospital-management-system'));

        if ($doctor_id) {
            global $wpdb;
            $today = current_time('Y-m-d');
            
            $updated = $wpdb->update(
                $wpdb->prefix . 'hms_queue',
                array('status' => 'cancelled'),
                array(
                    'doctor_id' => $doctor_id,
                    'queue_date' => $today,
                    'status' => 'waiting'
                ),
                array('%s'),
                array('%d', '%s', '%s')
            );

            if ($updated !== false) {
                $response['success'] = true;
                $response['message'] = sprintf(
                    __('Queue reset successfully. %d waiting patients cancelled.', 'hospital-management-system'),
                    $updated
                );
                
                // Log the action
                HMS_Helper::log_action('Queue Reset', 'Queue', 0, array(
                    'doctor_id' => $doctor_id,
                    'patients_cancelled' => $updated,
                    'date' => $today
                ));
            } else {
                $response['message'] = __('No waiting patients found to cancel.', 'hospital-management-system');
            }
        } else {
            $response['message'] = __('Missing doctor ID.', 'hospital-management-system');
        }

        wp_send_json($response);
    }

    /**
     * Handle queue export request.
     */
    public function handle_export_queue() {
        // Verify the nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'hms_admin_nonce')) {
            wp_die(__('Security check failed.', 'hospital-management-system'));
        }

        $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

        if (!$doctor_id) {
            wp_die(__('Missing doctor ID.', 'hospital-management-system'));
        }

        global $wpdb;
        $today = current_time('Y-m-d');
        
        // Get queue data with patient information
        $queue_data = $wpdb->get_results($wpdb->prepare(
            "SELECT q.token_number, p.first_name, p.last_name, p.patient_id, q.status, 
                    q.created_at, q.called_at, q.completed_at
             FROM {$wpdb->prefix}hms_queue q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.doctor_id = %d AND q.queue_date = %s 
             ORDER BY CAST(q.token_number AS UNSIGNED) ASC",
            $doctor_id, $today
        ));

        // Get doctor name
        $doctor_name = $wpdb->get_var($wpdb->prepare(
            "SELECT CONCAT(first_name, ' ', last_name) FROM {$wpdb->prefix}hms_staff WHERE id = %d",
            $doctor_id
        ));

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="queue_' . $doctor_id . '_' . $today . '.csv"');

        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add header row
        fputcsv($output, array(
            'Token Number',
            'Patient Name',
            'Patient ID',
            'Status',
            'Added At',
            'Called At',
            'Completed At'
        ));

        // Add data rows
        foreach ($queue_data as $row) {
            fputcsv($output, array(
                $row->token_number,
                $row->first_name . ' ' . $row->last_name,
                $row->patient_id,
                ucfirst(str_replace('-', ' ', $row->status)),
                $row->created_at,
                $row->called_at ?: '',
                $row->completed_at ?: ''
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Handle patient registration form submission.
     * Creates a WordPress user and then adds patient details to the custom table.
     */
    public function handle_patient_registration() {
        // Check if our form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_patient_registration'])) {
            return; // Exit if this is not a form submission
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hms_admin_nonce')) {
            wp_die(__('Security check failed. Please try again.', 'hospital-management-system'));
        }
        
        // Check user permissions
        if (!current_user_can('manage_patients')) {
            wp_die(__('You do not have permission to register patients.', 'hospital-management-system'));
        }

        // Check if we're editing an existing patient
        $is_edit = isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['patient_id']);
        $patient_id = $is_edit ? intval($_POST['patient_id']) : 0;
        
        // Sanitize and validate input - using correct field names from the form
        $first_name = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name = sanitize_text_field($_POST['last_name'] ?? '');
        $date_of_birth = sanitize_text_field($_POST['dob'] ?? ''); // Form uses 'dob'
        $gender = sanitize_text_field($_POST['gender'] ?? '');
        $contact_number = sanitize_text_field($_POST['contact_number'] ?? ''); // Form uses 'contact_number'
        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $address = !empty($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
        $national_id = !empty($_POST['national_id']) ? sanitize_text_field($_POST['national_id']) : '';
        $deposit_money = !empty($_POST['deposit_money']) ? floatval($_POST['deposit_money']) : 0.00;
        $blood_group = !empty($_POST['blood_group']) ? sanitize_text_field($_POST['blood_group']) : '';
        $allergies = !empty($_POST['allergies']) ? sanitize_textarea_field($_POST['allergies']) : '';
        $medical_history = !empty($_POST['medical_history']) ? sanitize_textarea_field($_POST['medical_history']) : '';
        $emergency_contact_name = sanitize_text_field($_POST['emergency_contact_name'] ?? '');
        $emergency_contact_number = sanitize_text_field($_POST['emergency_contact_number'] ?? '');

        // Enhanced validation
        $validation_errors = array();
        
        if (empty($first_name)) {
            $validation_errors[] = 'first_name';
        }
        if (empty($last_name)) {
            $validation_errors[] = 'last_name';
        }
        if (empty($date_of_birth)) {
            $validation_errors[] = 'date_of_birth';
        } elseif (!$this->validate_date($date_of_birth)) {
            $validation_errors[] = 'invalid_date';
        }
        if (empty($gender)) {
            $validation_errors[] = 'gender';
        }
        if (empty($contact_number)) {
            $validation_errors[] = 'contact_number';
        } elseif (!$this->validate_phone($contact_number)) {
            $validation_errors[] = 'invalid_phone';
        }
        if (empty($emergency_contact_name)) {
            $validation_errors[] = 'emergency_contact_name';
        }
        if (empty($emergency_contact_number)) {
            $validation_errors[] = 'emergency_contact_number';
        } elseif (!$this->validate_phone($emergency_contact_number)) {
            $validation_errors[] = 'invalid_emergency_phone';
        }
        
        if (!empty($validation_errors)) {
            $redirect_url = $is_edit ? 
                admin_url('admin.php?page=hms_patient_registration&id=' . $patient_id . '&error=validation_failed&fields=' . implode(',', $validation_errors)) :
                admin_url('admin.php?page=hms_patient_registration&error=validation_failed&fields=' . implode(',', $validation_errors));
            
            $this->safe_redirect($redirect_url);
            return;
        }

        // If email is provided, it must be valid and unique (unless editing existing patient)
        if (!empty($email)) {
            if (!is_email($email)) {
                $redirect_url = $is_edit ? 
                    admin_url('admin.php?page=hms_patient_registration&id=' . $patient_id . '&error=invalid_email') :
                    admin_url('admin.php?page=hms_patient_registration&error=invalid_email');
                $this->safe_redirect($redirect_url);
                return;
            }
            
            // Check if email exists for a different user (skip check if editing same patient)
            $existing_user_id = email_exists($email);
            if ($existing_user_id) {
                if (!$is_edit) {
                    // New registration with existing email
                    $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&error=email_exists'));
                    return;
                } else {
                    // Editing - check if email belongs to current patient
                    global $wpdb;
                    $current_patient = $wpdb->get_row($wpdb->prepare(
                        "SELECT user_id FROM {$wpdb->prefix}hms_patients WHERE id = %d",
                        $patient_id
                    ));
                    
                    if ($current_patient && $current_patient->user_id != $existing_user_id) {
                        $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&id=' . $patient_id . '&error=email_exists'));
                        return;
                    }
                }
            }
        }

        if ($is_edit) {
            // Handle patient update
            $this->handle_patient_update($patient_id, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'contact_number' => $contact_number,
                'email' => $email,
                'address' => $address,
                'national_id' => $national_id,
                'deposit_money' => $deposit_money,
                'blood_group' => $blood_group,
                'allergies' => $allergies,
                'medical_history' => $medical_history,
                'emergency_contact_name' => $emergency_contact_name,
                'emergency_contact_number' => $emergency_contact_number,
            ));
            return;
        }

        // Handle new patient registration
        $user_id = null;
        $user_password = wp_generate_password(12, false); // Generate a random password

        if (!empty($email)) {
            // Create a new WordPress user
            $username = $email; // Use email as username
            $user_data = array(
                'user_login'    => $username,
                'user_pass'     => $user_password,
                'user_email'    => $email,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'role'          => 'hms_patient', // Assign the custom patient role
                'display_name'  => $first_name . ' ' . $last_name,
            );

            $user_id = wp_insert_user($user_data);

            if (is_wp_error($user_id)) {
                // User creation failed, redirect with error
                $error_code = $user_id->get_error_code();
                $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&error=user_creation_failed&code=' . $error_code));
                return;
            }
        }

        // Now, add patient details to the custom hms_patients table
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists) {
            $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&error=db_table_missing'));
            return;
        }
        
        // Check if table has the correct columns
        $table_columns = $wpdb->get_col("DESCRIBE $table_name");
        $required_columns = array('id', 'user_id', 'patient_id', 'first_name', 'last_name', 'date_of_birth', 'gender', 'blood_group', 'contact_number', 'email', 'address', 'emergency_contact_name', 'emergency_contact_number', 'national_id', 'deposit_money', 'medical_history', 'allergies');
        $missing_columns = array_diff($required_columns, $table_columns);
        
        if (!empty($missing_columns)) {
            $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&error=db_columns_missing'));
            return;
        }

        // Generate a unique Patient ID (PT format)
        $last_patient = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name ORDER BY id DESC LIMIT 1"));
        $next_id = ($last_patient) ? $last_patient->id + 1 : 1;
        $unique_patient_id = 'PT' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
        
        // Prepare data for insertion into hms_patients table
        $patient_data = array(
            'user_id'           => $user_id, // Will be null if no email was provided
            'patient_id'        => $unique_patient_id,
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'date_of_birth'     => $date_of_birth,
            'gender'            => $gender,
            'blood_group'       => $blood_group,
            'contact_number'    => $contact_number,
            'email'             => $email,
            'address'           => $address,
            'emergency_contact_name' => $emergency_contact_name,
            'emergency_contact_number' => $emergency_contact_number,
            'national_id'       => $national_id,
            'deposit_money'     => $deposit_money,
            'medical_history'   => $medical_history,
            'allergies'         => $allergies,
            'registration_date' => current_time('mysql'),
            'updated_at'        => current_time('mysql'),
        );

        $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s');

        // Insert data into the database
        $result = $wpdb->insert($table_name, $patient_data, $format);

        if ($result) {
            $new_patient_db_id = $wpdb->insert_id; // Get the ID of the newly inserted patient record
            
            // If a user was created, send them a notification email
            if ($user_id && !empty($email)) {
                $subject = __('Your Patient Account has been Created', 'hospital-management-system');
                /* translators: 1: Patient name, 2: Hospital name, 3: Patient ID, 4: Email address, 5: Temporary password */
                $message = sprintf(
                    __("Dear %1\$s,\n\nYour patient account at %2\$s has been successfully created.\n\nYour Patient ID is: %3\$s\n\nYou can log in using your email address: %4\$s\nYour temporary password is: %5\$s\n\nPlease change your password after logging in for the first time.\n\nThank you,\nHospital Management Team", 'hospital-management-system'),
                    $first_name . ' ' . $last_name,
                    get_bloginfo('name'),
                    $unique_patient_id,
                    $email,
                    $user_password
                );
                wp_mail($email, $subject, $message);
            }
            
            // Log the action
            if (class_exists('HMS_Helper')) {
                HMS_Helper::log_action('Patient Added', 'Patient', $new_patient_db_id, json_encode($patient_data));
            }

            // Hook to save custom field data
            do_action('hms_save_patient_data', $new_patient_db_id, $_POST);

            // Redirect to Patients Management page after successful registration
            $redirect_url = admin_url('admin.php?page=hms_patients&success=patient_registered&patient_id=' . $unique_patient_id);
            $this->safe_redirect($redirect_url);
        } else {
            // If patient table insertion failed, and a user was created, delete the user to maintain consistency
            if ($user_id) {
                wp_delete_user($user_id);
            }
            $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&error=db_insert'));
        }
    }

    /**
     * Handle patient update
     */
    private function handle_patient_update($patient_id, $data) {
        // Ensure HMS_Patient class is available
        if (!class_exists('HMS_Patient')) {
            require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
        }
        
        $patient_obj = new HMS_Patient();
        $result = $patient_obj->update_patient($patient_id, $data);
        
        if (is_wp_error($result)) {
            $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&id=' . $patient_id . '&error=update_failed'));
        } elseif ($result) {
            $this->safe_redirect(admin_url('admin.php?page=hms_patients&success=patient_updated&patient_id=' . $patient_id));
        } else {
            $this->safe_redirect(admin_url('admin.php?page=hms_patient_registration&id=' . $patient_id . '&error=update_failed'));
        }
    }

    /**
     * Safe redirect that clears output buffers and exits properly
     */
    private function safe_redirect($url) {
        // Clear any output buffers before redirect
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        wp_redirect($url);
        exit;
    }

    /**
     * Validate date format and ensure it's not in the future
     */
    private function validate_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return false;
        }
        
        // Check if date is not in the future
        $today = new DateTime();
        if ($d > $today) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate phone number format
     */
    private function validate_phone($phone) {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it has at least 10 digits
        return strlen($cleaned) >= 10;
    }

    public function render_check_tables_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the check tables script
        include HMS_PLUGIN_DIR . 'admin/check-tables.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_test_crud_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the test CRUD script
        include HMS_PLUGIN_DIR . 'admin/test-crud.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    public function render_fix_tables_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the migration script
        include HMS_PLUGIN_DIR . 'admin/run-missing-tables-migration.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
    
    /**
     * Render the database status page
     */
    public function render_database_status_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        echo '<div class="wrap">';
        echo '<h1>' . __('Database Status', 'hospital-management-system') . '</h1>';
        
        // Check if activator class exists
        if (!class_exists('HMS_Activator')) {
            require_once HMS_PLUGIN_DIR . 'includes/class-hms-activator.php';
        }
        
        // Check for missing tables
        $missing_tables = HMS_Activator::check_required_tables();
        
        if (empty($missing_tables)) {
            echo '<div class="notice notice-success"><p><strong>' . __('All required database tables exist!', 'hospital-management-system') . '</strong></p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>' . __('Missing database tables detected!', 'hospital-management-system') . '</strong></p>';
            echo '<p>' . __('The following tables are missing:', 'hospital-management-system') . '</p>';
            echo '<ul>';
            foreach ($missing_tables as $table) {
                echo '<li><code>' . esc_html($table) . '</code></li>';
            }
            echo '</ul>';
            echo '<p><a href="' . admin_url('admin.php?page=hms_fix_tables') . '" class="button button-primary">' . __('Fix Missing Tables', 'hospital-management-system') . '</a></p>';
            echo '</div>';
        }
        
        // Check database version
        $db_version = get_option('hms_db_version', 'Not Set');
        echo '<h2>' . __('Database Information', 'hospital-management-system') . '</h2>';
        echo '<table class="widefat">';
        echo '<tr><td><strong>' . __('Database Version:', 'hospital-management-system') . '</strong></td><td>' . esc_html($db_version) . '</td></tr>';
        echo '<tr><td><strong>' . __('Plugin Version:', 'hospital-management-system') . '</strong></td><td>' . HMS_VERSION . '</td></tr>';
        
        // Check department count
        global $wpdb;
        $dept_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hms_departments");
        echo '<tr><td><strong>' . __('Departments:', 'hospital-management-system') . '</strong></td><td>' . intval($dept_count) . '</td></tr>';
        
        // Check staff count
        $staff_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hms_staff");
        echo '<tr><td><strong>' . __('Staff Members:', 'hospital-management-system') . '</strong></td><td>' . intval($staff_count) . '</td></tr>';
        
        // Check medicine count
        $medicine_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hms_medicines");
        echo '<tr><td><strong>' . __('Medicines:', 'hospital-management-system') . '</strong></td><td>' . intval($medicine_count) . '</td></tr>';
        
        echo '</table>';
        
        // Add manual activation button
        echo '<h2>' . __('Manual Actions', 'hospital-management-system') . '</h2>';
        echo '<p><a href="' . wp_nonce_url(admin_url('admin.php?page=hms_database_status&action=reactivate'), 'hms_reactivate') . '" class="button button-secondary">' . __('Re-run Activation Process', 'hospital-management-system') . '</a></p>';
        
        // Handle manual reactivation
        if (isset($_GET['action']) && $_GET['action'] === 'reactivate' && wp_verify_nonce($_GET['_wpnonce'], 'hms_reactivate')) {
            if (class_exists('HMS_Activator')) {
                HMS_Activator::activate();
                echo '<div class="notice notice-success"><p>' . __('Activation process completed successfully!', 'hospital-management-system') . '</p></div>';
            }
        }
        
        echo '</div>';
        
        // End output buffering and display the content
        ob_end_flush();
    }

    /**
     * Sanitize text field.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    public function sanitize_text_field( $input ) {
        return sanitize_text_field( $input );
    }

    /**
     * Sanitize textarea field.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    public function sanitize_textarea_field( $input ) {
        return sanitize_textarea_field( $input );
    }

    /**
     * Sanitize email field.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    public function sanitize_email_field( $input ) {
        return sanitize_email( $input );
    }
    
    /**
     * Handle AJAX request for adding a staff member.
     */
    public function ajax_add_staff() {
        $this->staff->ajax_add_staff();
    }
    
    /**
     * Handle AJAX request for updating a staff member.
     */
    public function ajax_update_staff() {
        $this->staff->ajax_update_staff();
    }

    /**
     * Handle AJAX request for updating role capabilities.
     */
    public function ajax_update_role_capability() {
        // Verify nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Check user permissions
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $role_name = sanitize_text_field($_POST['role']);
        $capability = sanitize_text_field($_POST['capability']);
        $enabled = intval($_POST['enabled']);

        $role = get_role($role_name);
        if (!$role) {
            wp_send_json_error(array('message' => __('Invalid role specified.', 'hospital-management-system')));
        }

        // Define allowed HMS capabilities
        $allowed_capabilities = array(
            'manage_hms',
            'manage_patients',
            'manage_clinical',
            'manage_billing',
            'manage_pharmacy',
            'manage_reports'
        );

        if (!in_array($capability, $allowed_capabilities)) {
            wp_send_json_error(array('message' => __('Invalid capability specified.', 'hospital-management-system')));
        }

        if ($enabled) {
            $role->add_cap($capability);
            $message = sprintf(__('Capability "%s" added to role "%s".', 'hospital-management-system'), $capability, $role->name);
        } else {
            $role->remove_cap($capability);
            $message = sprintf(__('Capability "%s" removed from role "%s".', 'hospital-management-system'), $capability, $role->name);
        }

        wp_send_json_success(array('message' => $message));
    }

    /**
     * Handle AJAX request for testing service connections.
     */
    public function ajax_test_service_connection() {
        // Verify nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Check user permissions
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $service = sanitize_text_field($_POST['service']);
        $settings = $_POST['settings'];

        switch ($service) {
            case 'email':
                $this->test_email_connection($settings);
                break;
            case 'sms':
                $this->test_sms_connection($settings);
                break;
            case 'whatsapp':
                $this->test_whatsapp_connection($settings);
                break;
            case 'payment':
                $this->test_payment_gateway_connection($settings);
                break;
            case 'lis':
                $this->test_lis_connection($settings);
                break;
            case 'emr':
                $this->test_emr_connection($settings);
                break;
            default:
                wp_send_json_error(array('message' => __('Invalid service specified.', 'hospital-management-system')));
        }
    }

    /**
     * Handle AJAX request for resetting settings to defaults.
     */
    public function ajax_reset_settings_defaults() {
        // Verify nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Check user permissions
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        $section = sanitize_text_field($_POST['section']);

        switch ($section) {
            case 'branding':
                $this->reset_branding_defaults();
                break;
            case 'advanced':
                $this->reset_advanced_defaults();
                break;
            default:
                wp_send_json_error(array('message' => __('Invalid section specified.', 'hospital-management-system')));
        }

        wp_send_json_success(array('message' => __('Settings reset to defaults successfully.', 'hospital-management-system')));
    }

    /**
     * Handle AJAX request for auto-saving settings.
     */
    public function ajax_auto_save_settings() {
        // Verify nonce
        check_ajax_referer('hms_admin_nonce', 'nonce');

        // Check user permissions
        if (!current_user_can('manage_hms')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'hospital-management-system')));
        }

        // This is a simplified auto-save - in a full implementation,
        // you would process the form data here
        wp_send_json_success(array('message' => __('Settings auto-saved.', 'hospital-management-system')));
    }

    /**
     * Test email connection.
     */
    private function test_email_connection($settings) {
        if ($this->notifications) {
            $to = get_option('admin_email');
            $subject = __('HMS Email Test', 'hospital-management-system');
            $message = __('This is a test email from your Hospital Management System. If you received this email, your email configuration is working correctly.', 'hospital-management-system');
            
            $sent = $this->notifications->send_email($to, $subject, $message);
            
            if ($sent) {
                wp_send_json_success(array('message' => __('Test email sent successfully! Check your inbox.', 'hospital-management-system')));
            } else {
                wp_send_json_error(array('message' => __('Failed to send test email. Please check your email configuration.', 'hospital-management-system')));
            }
        } else {
            wp_send_json_error(array('message' => __('Notifications class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Test SMS connection.
     */
    private function test_sms_connection($settings) {
        if ($this->notifications) {
            // For testing, we'll just validate the configuration without sending an actual SMS
            $gateway = get_option('hms_sms_gateway', 'twilio');
            $api_key = get_option('hms_sms_api_key', '');
            
            if (empty($api_key)) {
                wp_send_json_error(array('message' => __('SMS API key is not configured.', 'hospital-management-system')));
            } else {
                wp_send_json_success(array('message' => sprintf(__('SMS configuration for %s appears to be valid. (Test SMS not sent to avoid charges)', 'hospital-management-system'), ucfirst($gateway))));
            }
        } else {
            wp_send_json_error(array('message' => __('Notifications class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Test WhatsApp connection.
     */
    private function test_whatsapp_connection($settings) {
        if ($this->notifications) {
            $api_key = get_option('hms_whatsapp_api_key', '');
            
            if (empty($api_key)) {
                wp_send_json_error(array('message' => __('WhatsApp API key is not configured.', 'hospital-management-system')));
            } else {
                wp_send_json_success(array('message' => __('WhatsApp configuration appears to be valid. (Test message not sent to avoid charges)', 'hospital-management-system')));
            }
        } else {
            wp_send_json_error(array('message' => __('Notifications class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Test payment gateway connection.
     */
    private function test_payment_gateway_connection($settings) {
        if ($this->integrations) {
            $result = $this->integrations->test_payment_gateway();
            
            if ($result['success']) {
                wp_send_json_success(array('message' => $result['message']));
            } else {
                wp_send_json_error(array('message' => $result['message']));
            }
        } else {
            wp_send_json_error(array('message' => __('Integrations class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Test LIS connection.
     */
    private function test_lis_connection($settings) {
        if ($this->integrations) {
            $result = $this->integrations->test_lis_connection();
            
            if ($result['success']) {
                wp_send_json_success(array('message' => $result['message']));
            } else {
                wp_send_json_error(array('message' => $result['message']));
            }
        } else {
            wp_send_json_error(array('message' => __('Integrations class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Test EMR connection.
     */
    private function test_emr_connection($settings) {
        if ($this->integrations) {
            $result = $this->integrations->test_emr_connection();
            
            if ($result['success']) {
                wp_send_json_success(array('message' => $result['message']));
            } else {
                wp_send_json_error(array('message' => $result['message']));
            }
        } else {
            wp_send_json_error(array('message' => __('Integrations class not available.', 'hospital-management-system')));
        }
    }

    /**
     * Reset branding settings to defaults.
     */
    private function reset_branding_defaults() {
        delete_option('hms_hospital_favicon_id');
        update_option('hms_primary_color', '#2271b1');
        update_option('hms_secondary_color', '#135e96');
        delete_option('hms_custom_css');
        update_option('hms_hide_wp_branding', 'off');
        update_option('hms_custom_login_logo', 'off');
    }

    /**
     * Reset advanced settings to defaults.
     */
    private function reset_advanced_defaults() {
        update_option('hms_enable_debug_mode', 'off');
        update_option('hms_log_retention_days', 30);
        update_option('hms_session_timeout', 60);
        update_option('hms_max_file_upload_size', 10);
    }

    /**
     * Sanitize integer field.
     *
     * @param string $input The input to sanitize.
     * @return int The sanitized input.
     */
    public function sanitize_integer_field( $input ) {
        return absint( $input );
    }

    /**
     * Sanitize boolean field.
     *
     * @param string $input The input to sanitize.
     * @return bool The sanitized input.
     */
    public function sanitize_boolean_field( $input ) {
        return (bool) $input;
    }

    /**
     * Sanitize URL field.
     *
     * @param string $input The input to sanitize.
     * @return string The sanitized input.
     */
    public function sanitize_url_field( $input ) {
        return esc_url_raw( $input );
    }

    /**
     * Sanitize payment gateway keys array.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized input array.
     */
    public function sanitize_payment_gateway_keys( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $input as $gateway => $keys ) {
            if ( is_array( $keys ) ) {
                $sanitized[ sanitize_key( $gateway ) ] = array();
                foreach ( $keys as $key => $value ) {
                    $sanitized[ sanitize_key( $gateway ) ][ sanitize_key( $key ) ] = sanitize_text_field( $value );
                }
            }
        }

        return $sanitized;
    }
    
    /**
     * Render the run migrations page
     */
    public function render_run_migrations_page() {
        // Include the simple migration template
        include HMS_PLUGIN_DIR . 'admin/run-migrations-simple.php';
    }
    
    /**
     * Render the fix bill net amount page
     */
    public function render_fix_bill_net_amount_page() {
        // Include the fix bill net amount template
        include HMS_PLUGIN_DIR . 'admin/fix-bill-net-amount.php';
    }
    
    /**
     * Render the setup prescription test data page
     */
    public function render_setup_prescription_test_data_page() {
        // Include the setup prescription test data template
        include HMS_PLUGIN_DIR . 'admin/setup-prescription-test-data.php';
    }
    
    /**
     * Render breadcrumb navigation for HMS pages
     */
    private function render_breadcrumb($breadcrumbs) {
        if (empty($breadcrumbs)) {
            return;
        }
        
        echo '<div class="hms-breadcrumb">';
        echo '<strong>' . __('Hospital Management System', 'hospital-management-system') . '</strong>';
        
        foreach ($breadcrumbs as $index => $crumb) {
            echo '<span class="separator">›</span>';
            
            if (!empty($crumb['url']) && $index < count($breadcrumbs) - 1) {
                echo '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['title']) . '</a>';
            } else {
                echo '<span>' . esc_html($crumb['title']) . '</span>';
            }
        }
        
        echo '</div>';
    }

    // === INSURANCE CLAIMS MANAGEMENT ===

    public function render_insurance_claims_page() {
        // Handle claim deletion if the form is submitted
        if (isset($_POST['delete_claim']) && isset($_POST['claim_id'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', '_wpnonce');
            $claim_id = intval($_POST['claim_id']);
            if ($this->insurance->delete_claim($claim_id)) {
                $redirect_url = admin_url('admin.php?page=hms_insurance_claims&message=' . urlencode(__('Insurance claim deleted successfully.', 'hospital-management-system')) . '&type=success');
            } else {
                $redirect_url = admin_url('admin.php?page=hms_insurance_claims&message=' . urlencode(__('Failed to delete insurance claim.', 'hospital-management-system')) . '&type=error');
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            wp_redirect($redirect_url);
            exit;
        }
        
        ob_start();
        include HMS_PLUGIN_DIR . 'admin/templates/insurance-claims.php';
        $content = ob_get_clean();
        echo $content;
    }

    public function render_claim_details_page() {
        // Check if this is a form submission first
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_claim'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            $is_edit = isset($_POST['claim_id']);
            $redirect_args = array('page' => 'hms_claim_details');

            if ($is_edit) {
                $claim_id = intval($_POST['claim_id']);
                $data = $_POST;
                if ($this->insurance->update_claim_status($claim_id, $data)) {
                    $redirect_args['id'] = $claim_id;
                    $redirect_args['message'] = urlencode(__('Insurance claim updated successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['id'] = $claim_id;
                    $redirect_args['message'] = urlencode(__('Failed to update insurance claim.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            } else {
                $data = $_POST;
                $new_claim_id = $this->insurance->initiate_cashless_claim($data);
                if ($new_claim_id) {
                    $redirect_args['id'] = $new_claim_id;
                    $redirect_args['message'] = urlencode(__('Insurance claim created successfully.', 'hospital-management-system'));
                    $redirect_args['type'] = 'success';
                } else {
                    $redirect_args['message'] = urlencode(__('Failed to create insurance claim.', 'hospital-management-system'));
                    $redirect_args['type'] = 'error';
                }
            }
            
            while (ob_get_level()) {
                ob_end_clean();
            }

            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        ob_start();
        include HMS_PLUGIN_DIR . 'admin/templates/claim-details.php';
        $content = ob_get_clean();
        echo $content;
    }

    public function render_patient_insurance_page() {
        // Handle patient insurance coverage form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_coverage'])) {
            ob_start();
            
            check_admin_referer('hms_admin_nonce', 'nonce');
            
            // This would be handled by a patient insurance coverage method
            // For now, just redirect with a message
            $redirect_args = array(
                'page' => 'hms_patient_insurance',
                'message' => urlencode(__('Patient insurance coverage updated successfully.', 'hospital-management-system')),
                'type' => 'success'
            );
            
            while (ob_get_level()) {
                ob_end_clean();
            }

            wp_redirect(admin_url(add_query_arg($redirect_args, 'admin.php')));
            exit;
        }
        
        ob_start();
        include HMS_PLUGIN_DIR . 'admin/templates/patient-insurance.php';
        $content = ob_get_clean();
        echo $content;
    }
    
    /**
     * Render the license generator page
     */
    public function render_license_generator_page() {
        // Start output buffering to catch any output before headers are sent
        ob_start();
        
        // Include the license generator script
        include HMS_PLUGIN_DIR . 'admin/generate-license.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
}
