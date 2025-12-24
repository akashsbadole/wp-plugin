<?php
/**
 * Plugin Name: SEO Simple Security Test
 * Description: Adds a test page to verify SEO Simple security features are working
 * Author: Test
 * Version: 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item for security test
add_action('admin_menu', 'seo_simple_security_test_menu');

function seo_simple_security_test_menu() {
    add_submenu_page(
        null, // Hide from menu
        'SEO Simple Security Test',
        'Security Test',
        'manage_options',
        'seo-simple-security-test',
        'seo_simple_security_test_page'
    );
}

// Display the test page
function seo_simple_security_test_page() {
    // Check if the SEO Simple plugin is active
    if (!class_exists('Seo_Simple_Security')) {
        echo '<div class="wrap"><h1>SEO Simple Security Test</h1>';
        echo '<div class="notice notice-error"><p>The SEO Simple plugin is not active. Please activate it first.</p></div>';
        echo '</div>';
        return;
    }
    
    // Include the test page template
    include_once dirname(__FILE__) . '/admin/partials/test-security-page.php';
}

// Add a direct access link in the SEO Simple admin menu
add_action('admin_menu', 'seo_simple_security_test_link', 20);

function seo_simple_security_test_link() {
    add_submenu_page(
        'seo-simple-settings',
        'Security Test',
        'Security Test',
        'manage_options',
        'seo-simple-security-test'
    );
}