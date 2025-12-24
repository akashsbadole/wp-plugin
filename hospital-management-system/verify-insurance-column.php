<?php
/**
 * Verify Insurance Column Script
 * 
 * This script checks if the insurance_covered column exists in the wp_hms_bills table
 * and provides instructions on how to fix the issue if it doesn't.
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

echo "<div class='wrap'>";
echo "<h1>Insurance Column Verification</h1>";

// Check if the column exists
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
    echo "<p><strong>Success:</strong> The 'insurance_covered' column exists in the bills table.</p>";
    echo "</div>";
    
    // Show column details
    $column_info = $column_exists[0];
    echo "<div class='card'>";
    echo "<h2>Column Details</h2>";
    echo "<table class='wp-list-table widefat fixed striped'>";
    echo "<tr><th>Column Name</th><td>" . esc_html($column_info->COLUMN_NAME) . "</td></tr>";
    echo "<tr><th>Data Type</th><td>" . esc_html($column_info->COLUMN_TYPE) . "</td></tr>";
    echo "<tr><th>Default Value</th><td>" . esc_html($column_info->COLUMN_DEFAULT) . "</td></tr>";
    echo "<tr><th>Is Nullable</th><td>" . esc_html($column_info->IS_NULLABLE) . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
} else {
    echo "<div class='notice notice-error is-dismissible'>";
    echo "<p><strong>Error:</strong> The 'insurance_covered' column is missing from the bills table.</p>";
    echo "<p>This is causing the database error: <code>Unknown column 'insurance_covered' in 'field list'</code></p>";
    echo "</div>";
    
    // Provide fix instructions
    echo "<div class='card'>";
    echo "<h2>How to Fix This Issue</h2>";
    echo "<p>You need to run a database migration to add the missing column. Follow these steps:</p>";
    echo "<ol>";
    echo "<li>Navigate to <strong>Admin Dashboard</strong> → <strong>Hospital Management System</strong></li>";
    echo "<li>Look for a <strong>Run Migrations</strong> option in the menu</li>";
    echo "<li>Click on <strong>Run Database Migrations</strong></li>";
    echo "<li>Review the migrations that will be applied (look for 'Add insurance_covered column to bills table')</li>";
    echo "<li>Click the <strong>Run Migrations Now</strong> button</li>";
    echo "</ol>";
    echo "<p><strong>Alternative:</strong> If you can't find the migrations option, contact your system administrator to run the migration manually.</p>";
    echo "</div>";
    
    // Show the error query
    echo "<div class='card'>";
    echo "<h2>Problematic Query</h2>";
    echo "<p>The following query is failing because the column doesn't exist:</p>";
    echo "<code style='display:block; padding:10px; background:#f1f1f1; border:1px solid #ccc;'>";
    echo "SELECT id, bill_id, total_amount, paid_amount, insurance_covered, due_amount, ";
    echo "(total_amount - COALESCE(paid_amount, 0) - COALESCE(insurance_covered, 0)) as calculated_due ";
    echo "FROM wp_hms_bills WHERE total_amount > 0 HAVING ABS(calculated_due - due_amount) > 0.01 ORDER BY id DESC LIMIT 10";
    echo "</code>";
    echo "</div>";
}

echo "<p><a href='" . admin_url('admin.php?page=hms_dashboard') . "' class='button button-primary'>Return to Dashboard</a></p>";
echo "</div>";