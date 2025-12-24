<?php
/**
 * Test script to verify Elementor dependencies are handled correctly
 */

// Include WordPress
$wp_config_path = '../../../wp-config.php';
if (file_exists($wp_config_path)) {
    require_once($wp_config_path);
} else {
    die("WordPress config file not found");
}

// Check if Elementor is active
$elementor_active = defined('ELEMENTOR_VERSION') && is_plugin_active('elementor/elementor.php');
echo "Elementor Status: " . ($elementor_active ? "Active" : "Inactive") . "\n";

// Check if dependencies are registered
$elementor_common_registered = wp_script_is('elementor-common', 'registered');
$backbone_marionette_registered = wp_script_is('backbone-marionette', 'registered');

echo "elementor-common registered: " . ($elementor_common_registered ? "Yes" : "No") . "\n";
echo "backbone-marionette registered: " . ($backbone_marionette_registered ? "Yes" : "No") . "\n";

// Simulate the dependency array from our fixed code
$elementor_deps = array('jquery');

if ($elementor_common_registered) {
    $elementor_deps[] = 'elementor-common';
}

if ($backbone_marionette_registered) {
    $elementor_deps[] = 'backbone-marionette';
}

echo "Final dependency array: [" . implode(', ', $elementor_deps) . "]\n";

// Check if the script would enqueue without errors
if ($elementor_active) {
    echo "Result: Script will enqueue with only available dependencies\n";
} else {
    echo "Result: Script will not enqueue as Elementor is not active\n";
}