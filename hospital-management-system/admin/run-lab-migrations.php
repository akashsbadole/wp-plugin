<?php
/**
 * Simple script to run lab test migrations
 * 
 * This script will run the migrations to fix the lab test table issues
 */

// Don't allow direct access outside WordPress
if (!defined('ABSPATH')) {
    // If we're not in WordPress, try to load it
    $wp_load_path = dirname(dirname(__FILE__)) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die('WordPress not found. Please run this script from within WordPress admin.');
    }
}

// Check if user has proper permissions
if (!current_user_can('manage_options')) {
    die('You do not have sufficient permissions to run this script.');
}

// Include the migration class
require_once HMS_PLUGIN_DIR . 'includes/class-hms-migration.php';

echo "<h2>HMS Lab Test Migration</h2>";
echo "<p>Running migrations to fix lab test table issues...</p>";

// Run the migrations
$messages = HMS_Migration::run_migrations();

// Display results
echo "<h3>Migration Results:</h3>";
echo "<ul>";
foreach ($messages as $message) {
    echo "<li>" . esc_html($message) . "</li>";
}
echo "</ul>";

echo "<p><strong>Migration completed!</strong></p>";
echo "<p><a href='" . admin_url('admin.php?page=hms_clinical') . "'>Go to Clinical Page</a></p>";
?>