<?php
/**
 * Admin Dashboard Page Template for Hospital Management System
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Include template wrapper
require_once HMS_PLUGIN_DIR . 'admin/templates/template-wrapper.php';

// Get current user info
$current_user = wp_get_current_user();
$today = gmdate('Y-m-d');

// Fetch some basic data for the dashboard
// NOTE: These are placeholder functions. You will need to implement them in your respective classes.
// For example, $this->reports->get_today_appointments_count();

// Appointments
$today_appointments_count = 5; // Placeholder: HMS_Reports::get_appointments_count($today);
$upcoming_appointments = get_posts(array(
    'post_type' => 'hms_appointment',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'meta_query' => array(
        array(
            'key' => '_appointment_date',
            'value' => $today,
            'compare' => '>='
        )
    )
));

// Patients
$new_patients_count = 3; // Placeholder: HMS_Reports::get_new_patients_count($today);

// Billing
$today_revenue = 15000.00; // Placeholder: HMS_Billing::get_today_revenue();
$outstanding_bills = 25000.00; // Placeholder: HMS_Billing::get_outstanding_bills_total();

// Queue
$queue_count = 10; // Placeholder: HMS_Helper::get_queue_count($today);

// Activity Log
$recent_activities = HMS_Helper::get_recent_activities(5); // Placeholder

// Data for charts (example data)
$revenue_chart_data = array(
    'labels' => array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
    'data' => array(12000, 19000, 15000, 25000, 22000, 30000, 28000)
);
$appointment_chart_data = array(
    'labels' => array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
    'data' => array(10, 15, 8, 20, 18, 25, 22)
);

// Prepare page arguments
$page_args = array(
    'title' => get_admin_page_title(),
    'description' => sprintf(__('Welcome back, %s!', 'hospital-management-system'), $current_user->display_name),
    'show_breadcrumbs' => false, // Dashboard doesn't need breadcrumbs
    'content_template' => __FILE__ . '_content',
    'template_vars' => compact(
        'today_appointments_count', 'new_patients_count', 'today_revenue', 'queue_count',
        'upcoming_appointments', 'recent_activities', 'revenue_chart_data', 'appointment_chart_data'
    ),
    'page_class' => 'hms-dashboard-page'
);

// Render the dashboard using the standardized wrapper
hms_render_admin_page(array(
    'title' => get_admin_page_title(),
    'description' => sprintf(__('Welcome back, %s!', 'hospital-management-system'), $current_user->display_name),
    'show_breadcrumbs' => false, // Dashboard doesn't need breadcrumbs
    'content_template' => HMS_PLUGIN_DIR . 'admin/templates/dashboard-content.php',
    'template_vars' => compact(
        'today_appointments_count', 'new_patients_count', 'today_revenue', 'queue_count',
        'upcoming_appointments', 'recent_activities', 'revenue_chart_data', 'appointment_chart_data'
    ),
    'page_class' => 'hms-dashboard-page'
));
?>




