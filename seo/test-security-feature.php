<?php
/**
 * Test script to check if the SEO Simple security feature is working properly
 */

// Prevent direct access outside WordPress
if (!defined('ABSPATH')) {
    // Try to load WordPress if not already loaded
    $wp_load = dirname(__FILE__) . '/../../../wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    } else {
        echo "Error: Could not load WordPress.\n";
        exit(1);
    }
}

// Check if the security class exists
if (!class_exists('Seo_Simple_Security')) {
    echo "Error: Seo_Simple_Security class not found. Is the plugin active?\n";
    exit(1);
}

// Initialize the security class
$cache_manager = class_exists('Seo_Simple_Cache_Manager') ? Seo_Simple_Cache_Manager::get_instance() : null;
$security = new Seo_Simple_Security($cache_manager);

echo "=== SEO Simple Security Feature Test ===\n\n";

// Test 1: Check if security hooks are registered
echo "1. Checking if security hooks are registered...\n";
$registered_hooks = array();
$global_hooks = $GLOBALS['wp_filter'];

// Check some key security hooks
$security_hooks = array(
    'init' => 'init_security_features',
    'init' => 'init_waf',
    'wp_login_failed' => 'handle_failed_login',
    'authenticate' => 'check_brute_force',
    'preprocess_comment' => 'check_comment_security',
    'wp_handle_upload_prefilter' => 'check_upload_security',
    'admin_init' => 'protect_admin_area',
    'xmlrpc_enabled' => 'protect_xmlrpc',
    'rest_authentication_errors' => 'protect_rest_api',
    'send_headers' => 'add_security_headers'
);

$hooks_registered = 0;
$total_hooks = count($security_hooks);

foreach ($security_hooks as $hook => $method) {
    if (isset($global_hooks[$hook]) && !empty($global_hooks[$hook]->callbacks)) {
        foreach ($global_hooks[$hook]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && 
                    $callback['function'][0] instanceof Seo_Simple_Security &&
                    $callback['function'][1] === $method) {
                    $hooks_registered++;
                    echo "   ✓ Hook: $hook -> $method (Priority: $priority)\n";
                    break 2;
                }
            }
        }
    } else {
        echo "   ✗ Hook: $hook -> $method (Not registered)\n";
    }
}

echo "\nHooks registered: $hooks_registered/$total_hooks\n\n";

// Test 2: Check security settings
echo "2. Checking security settings...\n";
$settings = get_option('seo_simple_settings', array());

$security_settings = array(
    'security_waf_enabled' => 'WAF Enabled',
    'security_malware_scan_enabled' => 'Malware Scan Enabled',
    'security_brute_force_protection' => 'Brute Force Protection',
    'security_two_factor_auth' => 'Two-Factor Authentication',
    'security_comment_protection' => 'Comment Protection',
    'security_file_upload_protection' => 'File Upload Protection',
    'security_xmlrpc_protection' => 'XML-RPC Protection',
    'security_rest_api_protection' => 'REST API Protection',
    'security_security_headers' => 'Security Headers',
    'security_traffic_monitoring' => 'Traffic Monitoring',
    'security_email_alerts' => 'Email Alerts'
);

foreach ($security_settings as $key => $label) {
    $value = isset($settings[$key]) ? ($settings[$key] ? 'Enabled' : 'Disabled') : 'Not Set';
    echo "   $label: $value\n";
}

echo "\n";

// Test 3: Check if security scan results exist
echo "3. Checking security scan results...\n";
$scan_results = get_option('seo_simple_security_scan_results', array());

if (empty($scan_results)) {
    echo "   No security scan results found. Try running a scan from the admin panel.\n";
} else {
    echo "   Last scan: " . (isset($scan_results['scan_time']) ? $scan_results['scan_time'] : 'Unknown') . "\n";
    echo "   Files scanned: " . (isset($scan_results['scanned_files']) ? $scan_results['scanned_files'] : '0') . "\n";
    echo "   Issues found: " . (isset($scan_results['issues']) ? count($scan_results['issues']) : '0') . "\n";
}

echo "\n";

// Test 4: Check blocked IPs
echo "4. Checking blocked IPs...\n";
$blocked_ips = get_option('seo_simple_blocked_ips', array());

if (empty($blocked_ips)) {
    echo "   No IPs are currently blocked.\n";
} else {
    echo "   Blocked IPs: " . count($blocked_ips) . "\n";
    foreach ($blocked_ips as $ip) {
        echo "   - $ip\n";
    }
}

echo "\n";

// Test 5: Check security logs
echo "5. Checking security logs...\n";
$security_logs = get_option('seo_simple_security_logs', array());

if (empty($security_logs)) {
    echo "   No security logs found.\n";
} else {
    echo "   Total log entries: " . count($security_logs) . "\n";
    echo "   Recent logs:\n";
    $recent_logs = array_slice($security_logs, -5);
    foreach ($recent_logs as $log) {
        echo "   - [" . $log['timestamp'] . "] " . $log['event'] . "\n";
    }
}

echo "\n";

// Test 6: Test WAF functionality
echo "6. Testing WAF functionality...\n";

// Test visitor IP detection
$visitor_ip = $security->get_visitor_ip();
echo "   Visitor IP detection: $visitor_ip\n";

// Test attack pattern detection (using a safe test)
$_GET['test_param'] = '<script>alert("test")</script>';
echo "   Attack pattern detection: ";
ob_start();
try {
    // We can't actually call the private method, but we can check if the class has it
    $reflection = new ReflectionClass($security);
    if ($reflection->hasMethod('check_attack_patterns')) {
        echo "Method exists (can't test directly without causing block)\n";
    } else {
        echo "Method not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
ob_end_clean();

// Clean up test
unset($_GET['test_param']);

echo "\n";

// Test 7: Check threat feed status
echo "7. Checking threat feed status...\n";
if (method_exists($security, 'get_threat_feed_status')) {
    $threat_status = $security->get_threat_feed_status();
    echo "   Threat feed enabled: " . ($threat_status['enabled'] ? 'Yes' : 'No') . "\n";
    echo "   Last update: " . $threat_status['last_update'] . "\n";
    echo "   Rules loaded: " . $threat_status['rules_count'] . "\n";
    echo "   Blocked IPs from feed: " . $threat_status['blocked_ips_count'] . "\n";
} else {
    echo "   Threat feed status method not found\n";
}

echo "\n=== Test Complete ===\n";
echo "If you see any errors above, the security feature may not be working properly.\n";
echo "For more detailed testing, check the WordPress admin panel under SEO Simple > Security.\n";