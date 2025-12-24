<?php
/**
 * Test script to verify speed optimization fixes
 */

// Test 1: Check if CSS file is valid
echo "Test 1: Checking CSS file syntax...\n";
$css_file = __DIR__ . '/admin/css/seo-simple-404-monitor.css';
$css_content = file_get_contents($css_file);

// Simple check for balanced braces
$open_braces = substr_count($css_content, '{');
$close_braces = substr_count($css_content, '}');

if ($open_braces === $close_braces) {
    echo "✓ CSS file has balanced braces\n";
} else {
    echo "✗ CSS file has unbalanced braces: $open_braces open, $close_braces close\n";
}

// Test 2: Check if nonce is properly defined
echo "\nTest 2: Checking nonce definition...\n";
$js_file = __DIR__ . '/admin/js/seo-simple-speed-optimization-admin.js';
$js_content = file_get_contents($js_file);

if (strpos($js_content, 'seo_simple_speed_optimization_nonce') !== false) {
    echo "✓ Nonce reference found in JavaScript file\n";
} else {
    echo "✗ Nonce reference not found in JavaScript file\n";
}

// Test 3: Check if AJAX actions are properly registered
echo "\nTest 3: Checking AJAX action registration...\n";
$admin_file = __DIR__ . '/admin/class-seo-simple-speed-optimization-admin.php';
$admin_content = file_get_contents($admin_file);

$actions_to_check = [
    'wp_ajax_seo_simple_test_page_speed',
    'wp_ajax_seo_simple_update_speed_data',
    'wp_ajax_seo_simple_save_single_speed_setting',
    'wp_ajax_seo_simple_test_feature',
    'wp_ajax_seo_simple_export_speed_settings',
    'wp_ajax_seo_simple_import_speed_settings',
    'wp_ajax_seo_simple_reset_speed_settings',
    'wp_ajax_seo_simple_get_cache_details',
    'wp_ajax_seo_simple_delete_cache_file',
    'wp_ajax_seo_simple_optimize_images',
    'wp_ajax_seo_simple_generate_webp',
    'wp_ajax_seo_simple_download_google_fonts',
    'wp_ajax_seo_simple_get_cache_stats'
];

foreach ($actions_to_check as $action) {
    if (strpos($admin_content, $action) !== false) {
        echo "✓ $action is registered\n";
    } else {
        echo "✗ $action is not registered\n";
    }
}

// Test 4: Check if nonce verification is consistent
echo "\nTest 4: Checking nonce verification consistency...\n";
$files_to_check = [
    __DIR__ . '/admin/class-seo-simple-speed-optimization-admin.php',
    __DIR__ . '/includes/class-seo-simple-speed-optimization.php'
];

$nonce_name = 'seo_simple_speed_optimization_nonce';
$issues_found = [];

foreach ($files_to_check as $file) {
    $content = file_get_contents($file);
    
    // Find all nonce verification patterns
    preg_match_all('/wp_verify_nonce\(\s*\$_POST\[\'nonce\'\]\s*,\s*\'([^\']+)\'\s*\)/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $verified_nonce) {
            if ($verified_nonce !== $nonce_name) {
                $issues_found[] = "Inconsistent nonce '$verified_nonce' found in " . basename($file);
            }
        }
    }
}

if (empty($issues_found)) {
    echo "✓ All nonce verifications are consistent\n";
} else {
    foreach ($issues_found as $issue) {
        echo "✗ $issue\n";
    }
}

// Test 5: Check if AJAX actions match between frontend and backend
echo "\nTest 5: Checking AJAX action consistency...\n";
$speed_page = __DIR__ . '/admin/partials/speed-page.php';
$speed_page_content = file_get_contents($speed_page);

// Find all AJAX actions in the frontend
preg_match_all('/action:\s*\'([^\']+)\'/', $speed_page_content, $frontend_actions);

if (!empty($frontend_actions[1])) {
    foreach ($frontend_actions[1] as $action) {
        // Check if the action is registered in the backend
        if (strpos($admin_content, "wp_ajax_$action") !== false || strpos($admin_content, "'$action'") !== false) {
            echo "✓ Action '$action' is registered in backend\n";
        } else {
            echo "✗ Action '$action' is not registered in backend\n";
        }
    }
}

echo "\nAll tests completed!\n";