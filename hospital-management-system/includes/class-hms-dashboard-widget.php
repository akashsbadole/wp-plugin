<?php
// includes/class-hms-dashboard-widget.php

class HMS_Dashboard_Widget {

    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'hms_dashboard_widget',
            esc_html__('Hospital Management System Overview', 'hospital-management-system'),
            array($this, 'render_dashboard_widget')
        );
    }

    public function render_dashboard_widget() {
        $today_patients = HMS_Helper::get_today_patient_census();
        $today_appointments = HMS_Helper::get_today_appointments();
        $today_revenue = HMS_Helper::get_today_revenue();
        $low_stock_medicines = HMS_Helper::get_low_stock_medicines();
        $pending_claims = HMS_Helper::get_pending_insurance_claims();
        
        include HMS_PLUGIN_DIR . 'admin/templates/dashboard-widget.php';
    }
}