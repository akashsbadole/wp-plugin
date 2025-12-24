<?php
/**
 * Manual fix script for creating missing analytics table
 * 
 * This script can be run directly to create the missing wp_clr_analytics_events table.
 * Place this file in the root directory of your WordPress installation and access it
 * via browser: http://yoursite.com/fix-analytics-table.php
 * 
 * IMPORTANT: Delete this file after running it successfully for security reasons.
 */

// Load WordPress
require_once('wp-config.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You need to be an administrator to run this script.');
}

// Include install functions
require_once('wp-content/plugins/custom-login-redirect/includes/install.php');

// Create the missing table
$result = clr_create_table_if_not_exists('clr_analytics_events');

if ($result) {
    echo '<h1>Success!</h1>';
    echo '<p>The analytics table has been created successfully.</p>';
    echo '<p><strong>IMPORTANT:</strong> Please delete this file (fix-analytics-table.php) from your server for security reasons.</p>';
} else {
    echo '<h1>Error</h1>';
    echo '<p>The analytics table could not be created. It may already exist or there might be a database error.</p>';
    
    // Check if table already exists
    global $wpdb;
    $table_name = $wpdb->prefix . 'clr_analytics_events';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    
    if ($table_exists == $table_name) {
        echo '<p>The table already exists in your database.</p>';
    } else {
        echo '<p>The table does not exist. Please check your database permissions and try again.</p>';
    }
}