<?php
/**
 * Capability Reset Script
 * 
 * This script can be used to manually reset HMS admin capabilities
 * if there are permission issues. Access this file directly in your browser
 * when logged in as an administrator, then delete it when done.
 * 
 * URL: http://yoursite.com/wp-content/plugins/hospital-management-system/reset-capabilities.php
 */

// Exit if accessed directly or if not in WordPress context
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
    require_once(ABSPATH . 'wp-config.php');
}

// Check if user is logged in and has administrator capabilities
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

// Include the HMS activator class
require_once('includes/class-hms-activator.php');

echo '<h1>HMS Capability Reset</h1>';

try {
    // Force refresh the role cache
    wp_roles()->for_site();
    
    // Call the create_roles method to reset all capabilities
    HMS_Activator::create_roles();
    
    echo '<div style="color: green; font-weight: bold;">✓ HMS capabilities have been successfully reset!</div>';
    echo '<p>The administrator role now has all necessary HMS capabilities.</p>';
    echo '<p><strong>Important:</strong> Please delete this file (reset-capabilities.php) from your server after use for security reasons.</p>';
    
} catch (Exception $e) {
    echo '<div style="color: red; font-weight: bold;">✗ Error resetting capabilities: ' . $e->getMessage() . '</div>';
}

echo '<p><a href="' . admin_url('admin.php?page=hms_dashboard') . '">Return to HMS Dashboard</a></p>';
