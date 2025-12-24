<?php
/**
 * HMS Admin Class
 *
 * Handles admin-specific functionality of the plugin.
 */

class HMS_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, HMS_PLUGIN_URL . 'admin/css/hms-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, HMS_PLUGIN_URL . 'admin/js/hms-admin.js', array('jquery'), $this->version, false);
        
        wp_localize_script($this->plugin_name, 'hms_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hms_admin_nonce'),
        ));
    }

    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Hospital Management', 'hospital-management-system'),
            __('Hospital Management', 'hospital-management-system'),
            'manage_hms',
            'hms_dashboard',
            array($this, 'render_dashboard_page'),
            'dashicons-hospital',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'hms_dashboard',
            __('Dashboard', 'hospital-management-system'),
            __('Dashboard', 'hospital-management-system'),
            'manage_hms',
            'hms_dashboard',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Patients', 'hospital-management-system'),
            __('Patients', 'hospital-management-system'),
            'manage_patients',
            'hms_patients',
            array($this, 'render_patients_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Appointments', 'hospital-management-system'),
            __('Appointments', 'hospital-management-system'),
            'manage_clinical',
            'hms_appointments',
            array($this, 'render_appointments_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Billing', 'hospital-management-system'),
            __('Billing', 'hospital-management-system'),
            'manage_billing',
            'hms_billing',
            array($this, 'render_billing_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Pharmacy', 'hospital-management-system'),
            __('Pharmacy', 'hospital-management-system'),
            'manage_pharmacy',
            'hms_pharmacy',
            array($this, 'render_pharmacy_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Staff', 'hospital-management-system'),
            __('Staff', 'hospital-management-system'),
            'manage_hms',
            'hms_staff',
            array($this, 'render_staff_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Reports', 'hospital-management-system'),
            __('Reports', 'hospital-management-system'),
            'manage_reports',
            'hms_reports',
            array($this, 'render_reports_page')
        );
        
        add_submenu_page(
            'hms_dashboard',
            __('Settings', 'hospital-management-system'),
            __('Settings', 'hospital-management-system'),
            'manage_hms',
            'hms_settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('hms_general_settings', 'hms_hospital_name', array( $this, 'sanitize_text_field' ));
        register_setting('hms_general_settings', 'hms_hospital_address', array( $this, 'sanitize_textarea_field' ));
        register_setting('hms_general_settings', 'hms_hospital_phone', array( $this, 'sanitize_text_field' ));
        register_setting('hms_general_settings', 'hms_hospital_email', array( $this, 'sanitize_email_field' ));
        register_setting('hms_general_settings', 'hms_currency', array( $this, 'sanitize_text_field' ));
        register_setting('hms_general_settings', 'hms_time_zone', array( $this, 'sanitize_text_field' ));
        
        register_setting('hms_appointment_settings', 'hms_appointment_duration', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_appointment_settings', 'hms_advance_booking_days', array( $this, 'sanitize_integer_field' ));
        register_setting('hms_appointment_settings', 'hms_cancellation_policy', array( $this, 'sanitize_textarea_field' ));
        
        register_setting('hms_notification_settings', 'hms_enable_sms', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_notification_settings', 'hms_enable_email', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_notification_settings', 'hms_enable_whatsapp', array( $this, 'sanitize_boolean_field' ));
        register_setting('hms_notification_settings', 'hms_sms_gateway', array( $this, 'sanitize_text_field' ));
        register_setting('hms_notification_settings', 'hms_sms_api_key', array( $this, 'sanitize_text_field' ));
        register_setting('hms_notification_settings', 'hms_whatsapp_api_key', array( $this, 'sanitize_text_field' ));
    }

    public function render_dashboard_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }

    public function render_patients_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/patients.php';
    }

    public function render_appointments_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/appointments.php';
    }

    public function render_billing_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/billing.php';
    }

    public function render_pharmacy_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/pharmacy.php';
    }

    public function render_staff_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/staff.php';
    }

    public function render_reports_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/reports.php';
    }

    public function render_settings_page() {
        include HMS_PLUGIN_DIR . 'admin/templates/settings.php';
    }

    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=hms_settings') . '">' . __('Settings', 'hospital-management-system') . '</a>';
        array_unshift($links, $settings_link);
        
        return $links;
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
}