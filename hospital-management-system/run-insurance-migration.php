<?php
/**
 * Run Insurance Migration Script
 * 
 * This script specifically runs the migration to add the insurance_covered column
 * to the wp_hms_bills table to fix the database error.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    // If not in WordPress context, try to load WordPress
    $wp_load_path = __DIR__ . '/../../../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die("WordPress not found. Please run this script within WordPress or from the admin dashboard.");
    }
}

// Check if user has appropriate permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Include the migration class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-migration.php';

// Run the specific migration
echo "<h2>Running Insurance Migration</h2>";

try {
    // Call the specific migration method
    $migration = new HMS_Migration();
    $result = $migration->migrate_bills_table_insurance_covered();
    
    echo "<div class='notice notice-success is-dismissible'>";
    echo "<p><strong>Migration Result:</strong> " . esc_html($result) . "</p>";
    echo "</div>";
    
    // Verify the column was added
    global $wpdb;
    $table_name = $wpdb->prefix . 'hms_bills';
    $column_exists = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        $wpdb->dbname,
        $table_name,
        'insurance_covered'
    ));
    
    if (!empty($column_exists)) {
        echo "<div class='notice notice-success is-dismissible'>";
        echo "<p><strong>Verification:</strong> The 'insurance_covered' column has been successfully added to the bills table.</p>";
        echo "</div>";
    } else {
        echo "<div class='notice notice-error is-dismissible'>";
        echo "<p><strong>Error:</strong> The 'insurance_covered' column was not found after migration.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='notice notice-error is-dismissible'>";
    echo "<p><strong>Migration Error:</strong> " . esc_html($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<p><a href='" . admin_url('admin.php?page=hms_billing') . "' class='button button-primary'>Return to Billing</a></p>";