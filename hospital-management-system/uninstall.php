<?php
/**
 * Uninstall script for Hospital Management System
 *
 * This file is executed when the plugin is uninstalled.
 * It removes all database tables, options, post types, and roles created by the plugin.
 */

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$table_prefix = $wpdb->prefix;

// --- 1. Drop Custom Database Tables ---
// Define all custom tables created by the plugin.
// This list should match the tables created in the main plugin file and activator.
$custom_tables = array(
    $table_prefix . 'hms_patients',
    $table_prefix . 'hms_appointments',
    $table_prefix . 'hms_medical_records',
    $table_prefix . 'hms_bills',
    $table_prefix . 'hms_bill_items',
    $table_prefix . 'hms_payments',
    $table_prefix . 'hms_prescriptions',
    $table_prefix . 'hms_medicines',
    $table_prefix . 'hms_pharmacy_stock',
    $table_prefix . 'hms_suppliers',
    $table_prefix . 'hms_purchase_orders',
    $table_prefix . 'hms_purchase_order_items',
    $table_prefix . 'hms_staff',
    $table_prefix . 'hms_departments',
    $table_prefix . 'hms_services',
    $table_prefix . 'hms_insurance_providers',
    $table_prefix . 'hms_claims',
    $table_prefix . 'hms_queue', // Added missing queue table
    $table_prefix . 'hms_audit_log'
);

// Drop each custom table if it exists
foreach ($custom_tables as $table_name) {
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}


// --- 2. Delete Custom Post Types (CPTs) ---
// Define all CPTs registered by the plugin.
// Note: Some data like Medicines, Departments are stored in custom tables, not CPTs.
$post_types = array(
    'hms_patient',
    'hms_appointment',
    'hms_bill',
    'hms_prescription', // Added
    'hms_staff'       // Added
);

foreach ($post_types as $post_type) {
    $items = get_posts(array(
        'post_type'   => $post_type,
        'numberposts' => -1,
        'post_status' => 'any' // Include all statuses: publish, draft, trash, etc.
    ));
    
    foreach ($items as $item) {
        // Force delete, bypassing trash
        wp_delete_post($item->ID, true);
    }
}


// --- 3. Delete Plugin Options ---
// Define all options created by the plugin.
// This list should match options set in HMS_Activator and the settings page.
$options_to_delete = array(
    // General Settings
    'hms_hospital_name',
    'hms_hospital_address',
    'hms_hospital_phone',
    'hms_hospital_email',
    'hms_currency',
    'hms_time_zone',
    
    // Appointment Settings
    'hms_appointment_duration',
    'hms_advance_booking_days',
    'hms_cancellation_policy',
    
    // Notification Settings
    'hms_enable_sms',
    'hms_enable_email',
    'hms_enable_whatsapp',
    'hms_sms_gateway',
    'hms_sms_api_key',
    'hms_whatsapp_api_key',
    
    // Plugin Version (if stored as an option)
    'hms_version',
);

foreach ($options_to_delete as $option) {
    delete_option($option);
}


// --- 4. Delete User Meta Data ---
// Delete any user meta data added by the plugin.
$user_meta_to_delete = array(
    '_patient_id', // Links WordPress user to HMS patient record
    '_staff_id',   // Links WordPress user to HMS staff record
);

$users = get_users();
foreach ($users as $user) {
    foreach ($user_meta_to_delete as $meta_key) {
        delete_user_meta($user->ID, $meta_key);
    }
}


// --- 5. Delete Custom Roles ---
// Define all custom roles created by the plugin.
// Currently, only 'hms_patient' is created. Others are handled via capabilities on default roles.
$roles_to_remove = array(
    'hms_patient',
);

foreach ($roles_to_remove as $role) {
    remove_role($role);
}


// --- 6. Clean Up ---
// Flush rewrite rules to remove any CPT rewrite rules.
flush_rewrite_rules();
