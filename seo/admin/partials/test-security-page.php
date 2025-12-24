<?php
/**
 * Test page template for security feature testing.
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get the security instance
$cache_manager = class_exists('Seo_Simple_Cache_Manager') ? Seo_Simple_Cache_Manager::get_instance() : null;
$security = new Seo_Simple_Security($cache_manager);

// Run tests
$results = array();

// Test 1: Check if security hooks are registered
$results['hooks_registered'] = array();
$global_hooks = $GLOBALS['wp_filter'];

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
                    $results['hooks_registered'][] = array(
                        'hook' => $hook,
                        'method' => $method,
                        'priority' => $priority,
                        'status' => 'Registered'
                    );
                    break 2;
                }
            }
        }
    } else {
        $results['hooks_registered'][] = array(
            'hook' => $hook,
            'method' => $method,
            'status' => 'Not Registered'
        );
    }
}

$results['hooks_summary'] = "$hooks_registered/$total_hooks hooks registered";

// Test 2: Check security settings
$results['security_settings'] = array();
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
    $results['security_settings'][] = array(
        'label' => $label,
        'value' => $value,
        'status' => isset($settings[$key]) ? ($settings[$key] ? 'Good' : 'Warning') : 'Info'
    );
}

// Test 3: Check security scan results
$results['scan_results'] = get_option('seo_simple_security_scan_results', array());

// Test 4: Check blocked IPs
$results['blocked_ips'] = get_option('seo_simple_blocked_ips', array());

// Test 5: Check security logs
$results['security_logs'] = get_option('seo_simple_security_logs', array());

// Test 6: Test WAF functionality
$results['waf_test'] = array(
    'visitor_ip' => $security->get_visitor_ip(),
    'method_exists' => method_exists($security, 'get_visitor_ip')
);

// Test 7: Check threat feed status
if (method_exists($security, 'get_threat_feed_status')) {
    $results['threat_feed_status'] = $security->get_threat_feed_status();
} else {
    $results['threat_feed_status'] = array('error' => 'Method not found');
}
?>

<div class="wrap">
    <h1><?php _e('Security Feature Test', 'seo-simple'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('This page tests the functionality of the SEO Simple security features.', 'seo-simple'); ?></p>
    </div>
    
    <div class="seo-simple-test-results">
        <!-- Test 1: Hooks Registration -->
        <div class="card">
            <h2><?php _e('1. Security Hooks Registration', 'seo-simple'); ?></h2>
            <p><strong><?php _e('Status:', 'seo-simple'); ?></strong> <?php echo esc_html($results['hooks_summary']); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Hook', 'seo-simple'); ?></th>
                        <th><?php _e('Method', 'seo-simple'); ?></th>
                        <th><?php _e('Priority', 'seo-simple'); ?></th>
                        <th><?php _e('Status', 'seo-simple'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['hooks_registered'] as $hook): ?>
                        <tr>
                            <td><?php echo esc_html($hook['hook']); ?></td>
                            <td><?php echo esc_html($hook['method']); ?></td>
                            <td><?php echo isset($hook['priority']) ? esc_html($hook['priority']) : '-'; ?></td>
                            <td>
                                <span class="status-<?php echo $hook['status'] === 'Registered' ? 'good' : 'error'; ?>">
                                    <?php echo esc_html($hook['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Test 2: Security Settings -->
        <div class="card">
            <h2><?php _e('2. Security Settings', 'seo-simple'); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Setting', 'seo-simple'); ?></th>
                        <th><?php _e('Value', 'seo-simple'); ?></th>
                        <th><?php _e('Status', 'seo-simple'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['security_settings'] as $setting): ?>
                        <tr>
                            <td><?php echo esc_html($setting['label']); ?></td>
                            <td><?php echo esc_html($setting['value']); ?></td>
                            <td>
                                <span class="status-<?php echo $setting['status']; ?>">
                                    <?php echo esc_html($setting['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Test 3: Security Scan Results -->
        <div class="card">
            <h2><?php _e('3. Security Scan Results', 'seo-simple'); ?></h2>
            
            <?php if (empty($results['scan_results'])): ?>
                <p><?php _e('No security scan results found. Try running a scan from the Security page.', 'seo-simple'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <tr>
                        <th><?php _e('Last Scan', 'seo-simple'); ?></th>
                        <td><?php echo esc_html($results['scan_results']['scan_time']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Files Scanned', 'seo-simple'); ?></th>
                        <td><?php echo esc_html($results['scan_results']['scanned_files']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Issues Found', 'seo-simple'); ?></th>
                        <td><?php echo isset($results['scan_results']['issues']) ? count($results['scan_results']['issues']) : '0'; ?></td>
                    </tr>
                </table>
                
                <?php if (!empty($results['scan_results']['issues'])): ?>
                    <h3><?php _e('Issues Found:', 'seo-simple'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Type', 'seo-simple'); ?></th>
                                <th><?php _e('File', 'seo-simple'); ?></th>
                                <th><?php _e('Message', 'seo-simple'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['scan_results']['issues'] as $issue): ?>
                                <tr>
                                    <td><?php echo esc_html($issue['type']); ?></td>
                                    <td><?php echo esc_html($issue['file']); ?></td>
                                    <td><?php echo esc_html($issue['message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Test 4: Blocked IPs -->
        <div class="card">
            <h2><?php _e('4. Blocked IPs', 'seo-simple'); ?></h2>
            
            <?php if (empty($results['blocked_ips'])): ?>
                <p><?php _e('No IPs are currently blocked.', 'seo-simple'); ?></p>
            <?php else: ?>
                <p><?php printf(_n('%d IP is currently blocked.', '%d IPs are currently blocked.', count($results['blocked_ips']), 'seo-simple'), count($results['blocked_ips'])); ?></p>
                <ul>
                    <?php foreach ($results['blocked_ips'] as $ip): ?>
                        <li><?php echo esc_html($ip); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <!-- Test 5: Security Logs -->
        <div class="card">
            <h2><?php _e('5. Security Logs', 'seo-simple'); ?></h2>
            
            <?php if (empty($results['security_logs'])): ?>
                <p><?php _e('No security logs found.', 'seo-simple'); ?></p>
            <?php else: ?>
                <p><?php printf(_n('%d log entry found.', '%d log entries found.', count($results['security_logs']), 'seo-simple'), count($results['security_logs'])); ?></p>
                
                <h3><?php _e('Recent Logs:', 'seo-simple'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Timestamp', 'seo-simple'); ?></th>
                            <th><?php _e('Event', 'seo-simple'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recent_logs = array_slice($results['security_logs'], -10);
                        foreach ($recent_logs as $log): 
                        ?>
                            <tr>
                                <td><?php echo esc_html($log['timestamp']); ?></td>
                                <td><?php echo esc_html($log['event']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Test 6: WAF Functionality -->
        <div class="card">
            <h2><?php _e('6. WAF Functionality', 'seo-simple'); ?></h2>
            
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th><?php _e('Visitor IP Detection', 'seo-simple'); ?></th>
                    <td><?php echo $results['waf_test']['method_exists'] ? esc_html($results['waf_test']['visitor_ip']) : 'Not Working'; ?></td>
                </tr>
                <tr>
                    <th><?php _e('IP Detection Method', 'seo-simple'); ?></th>
                    <td><?php echo $results['waf_test']['method_exists'] ? 'Working' : 'Not Working'; ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Test 7: Threat Feed Status -->
        <div class="card">
            <h2><?php _e('7. Threat Feed Status', 'seo-simple'); ?></h2>
            
            <?php if (isset($results['threat_feed_status']['error'])): ?>
                <p><?php echo esc_html($results['threat_feed_status']['error']); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <tr>
                        <th><?php _e('Threat Feed Enabled', 'seo-simple'); ?></th>
                        <td><?php echo $results['threat_feed_status']['enabled'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Last Update', 'seo-simple'); ?></th>
                        <td><?php echo esc_html($results['threat_feed_status']['last_update']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Rules Loaded', 'seo-simple'); ?></th>
                        <td><?php echo esc_html($results['threat_feed_status']['rules_count']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Blocked IPs from Feed', 'seo-simple'); ?></th>
                        <td><?php echo esc_html($results['threat_feed_status']['blocked_ips_count']); ?></td>
                    </tr>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .status-good { color: green; font-weight: bold; }
        .status-warning { color: orange; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        .status-Info { color: blue; font-weight: bold; }
        .seo-simple-test-results .card {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccd0d4;
            background: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .seo-simple-test-results .card h2 {
            margin-top: 0;
        }
    </style>
</div>