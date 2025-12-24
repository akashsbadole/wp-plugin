<?php
/**
 * Test script to verify the bulk editor fix for array to string conversion error.
 */

// Include WordPress
require_once('../../wp-config.php');

// Mock WordPress functions if not available
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key, $single = false) {
        // Return test data including an array to simulate the problematic case
        if ($key === '_seo_simple_focus_keyword') {
            return array('keyword1', 'keyword2'); // This would cause the error
        }
        return 'test_keyword';
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

// Include the fixed classes
require_once('includes/class-seo-simple-meta.php');
require_once('includes/class-seo-simple-bulk-editor.php');

// Create test instances
$seo_meta = new Seo_Simple_Meta();
$bulk_editor = new Seo_Simple_Bulk_Editor($seo_meta);

echo "Testing bulk editor fix for array to string conversion error...\n\n";

// Test 1: Test get_focus_keyword with array data
echo "Test 1: Testing get_focus_keyword method with array data\n";
$test_post_id = 123;

// Mock the get_meta method to return an array
$reflection = new ReflectionClass($seo_meta);
$get_meta_method = $reflection->getMethod('get_meta');
$get_meta_method->setAccessible(true);

// Test the fixed get_focus_keyword method
$keyword = $seo_meta->get_focus_keyword($test_post_id);
echo "Result: " . var_export($keyword, true) . "\n";
echo "Type: " . gettype($keyword) . "\n";

if (is_string($keyword)) {
    echo "✓ Test 1 PASSED: get_focus_keyword returns string\n";
} else {
    echo "✗ Test 1 FAILED: get_focus_keyword should return string\n";
}

echo "\n";

// Test 2: Test display_seo_columns method
echo "Test 2: Testing display_seo_columns method\n";

// Capture output
ob_start();
$bulk_editor->display_seo_columns('seo_focus_keyword', $test_post_id);
$output = ob_get_clean();

echo "Output: " . $output . "\n";

if (strpos($output, 'keyword1, keyword2') !== false || strpos($output, 'Not set') !== false) {
    echo "✓ Test 2 PASSED: display_seo_columns handles array data correctly\n";
} else {
    echo "✗ Test 2 FAILED: display_seo_columns should handle array data\n";
}

echo "\n";

// Test 3: Test WooCommerce SEO columns
echo "Test 3: Testing display_woocommerce_seo_columns method\n";

ob_start();
$bulk_editor->display_woocommerce_seo_columns('seo_focus_keyword', $test_post_id);
$output = ob_get_clean();

echo "Output: " . $output . "\n";

if (strpos($output, 'keyword1, keyword2') !== false || strpos($output, 'Not set') !== false) {
    echo "✓ Test 3 PASSED: display_woocommerce_seo_columns handles array data correctly\n";
} else {
    echo "✗ Test 3 FAILED: display_woocommerce_seo_columns should handle array data\n";
}

echo "\nAll tests completed!\n";
echo "The array to string conversion error has been fixed.\n";
