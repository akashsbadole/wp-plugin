<?php
/**
 * Comprehensive Plugin Fix Script
 * This script fixes all issues and ensures the plugin works according to WordPress guidelines
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once(ABSPATH . 'wp-load.php');
}

// Check if user has admin privileges
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

set_time_limit(300); // 5 minutes

?>
<!DOCTYPE html>
<html>
<head>
    <title>SEO Simple - Complete Fix</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; margin: 20px; background: #f0f0f1; }
        .wrap { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.13); }
        h1 { color: #1d2327; border-bottom: 3px solid #2271b1; padding-bottom: 15px; margin-top: 0; }
        h2 { color: #2271b1; margin-top: 30px; font-size: 20px; }
        .notice { padding: 12px; margin: 20px 0; border-left: 4px solid; }
        .notice-success { background: #edfaef; border-color: #00a32a; }
        .notice-error { background: #fcf0f1; border-color: #d63638; }
        .notice-warning { background: #fcf9e8; border-color: #dba617; }
        .notice-info { background: #f0f6fc; border-color: #2271b1; }
        .success { color: #00a32a; font-weight: 600; }
        .error { color: #d63638; font-weight: 600; }
        .warning { color: #dba617; font-weight: 600; }
        .info { color: #2271b1; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dcdcde; }
        th { background: #f6f7f7; font-weight: 600; }
        tr:hover { background: #f6f7f7; }
        .button { display: inline-block; padding: 8px 16px; background: #2271b1; color: white; text-decoration: none; border-radius: 3px; margin: 5px; border: none; cursor: pointer; }
        .button:hover { background: #135e96; }
        .button-secondary { background: #f6f7f7; color: #2c3338; border: 1px solid #8c8f94; }
        .button-secondary:hover { background: #f0f0f1; }
        code { background: #f6f7f7; padding: 3px 6px; border-radius: 3px; font-family: Consolas, Monaco, monospace; font-size: 13px; }
        .progress { background: #f0f0f1; height: 30px; border-radius: 3px; overflow: hidden; margin: 15px 0; }
        .progress-bar { background: #2271b1; height: 100%; line-height: 30px; color: white; text-align: center; transition: width 0.3s; }
        ul { line-height: 1.8; }
        .step { margin: 20px 0; padding: 20px; background: #f6f7f7; border-radius: 5px; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #2271b1; color: white; border-radius: 50%; text-align: center; line-height: 30px; margin-right: 10px; font-weight: 600; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🔧 SEO Simple Plugin - Complete Fix & Setup</h1>
    <p><strong>Started:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

$fixes_applied = array();
$fixes_failed = array();
$total_steps = 10;
$current_step = 0;

// Helper function to update progress
function show_progress($step, $total, $message) {
    $percentage = ($step / $total) * 100;
    echo '<div class="progress">';
    echo '<div class="progress-bar" style="width: ' . $percentage . '%">';
    echo round($percentage) . '% - ' . esc_html($message);
    echo '</div></div>';
    flush();
}

// STEP 1: Check WordPress Environment
echo '<div class="step">';
echo '<h2><span class="step-number">1</span>Checking WordPress Environment</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Checking environment');

$wp_version = get_bloginfo('version');
$php_version = phpversion();
global $wpdb;
$mysql_version = $wpdb->db_version();

echo '<table>';
echo '<tr><th>Component</th><th>Current</th><th>Required</th><th>Status</th></tr>';
echo '<tr><td>WordPress</td><td>' . $wp_version . '</td><td>5.0+</td><td>' . (version_compare($wp_version, '5.0', '>=') ? '<span class="success">✓ OK</span>' : '<span class="error">✗ Update Required</span>') . '</td></tr>';
echo '<tr><td>PHP</td><td>' . $php_version . '</td><td>7.0+</td><td>' . (version_compare($php_version, '7.0', '>=') ? '<span class="success">✓ OK</span>' : '<span class="error">✗ Update Required</span>') . '</td></tr>';
echo '<tr><td>MySQL</td><td>' . $mysql_version . '</td><td>5.6+</td><td>' . (version_compare($mysql_version, '5.6', '>=') ? '<span class="success">✓ OK</span>' : '<span class="error">✗ Update Required</span>') . '</td></tr>';
echo '</table>';

if (version_compare($wp_version, '5.0', '>=') && version_compare($php_version, '7.0', '>=')) {
    echo '<div class="notice notice-success"><p class="success">✓ Environment meets all requirements</p></div>';
    $fixes_applied[] = 'Environment check passed';
} else {
    echo '<div class="notice notice-error"><p class="error">✗ Environment does not meet minimum requirements</p></div>';
    $fixes_failed[] = 'Environment check failed';
}
echo '</div>';

// STEP 2: Create Database Tables
echo '<div class="step">';
echo '<h2><span class="step-number">2</span>Creating Database Tables</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Creating tables');

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
$charset_collate = $wpdb->get_charset_collate();

$tables_created = 0;
$tables_failed = 0;

// Redirects table
$table_name = $wpdb->prefix . 'seo_simple_redirects';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    source_url varchar(500) NOT NULL,
    target_url varchar(500) NOT NULL,
    redirect_type varchar(10) DEFAULT '301',
    hits bigint(20) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY source_url (source_url(191))
) $charset_collate;";
dbDelta($sql);
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    echo '<p class="success">✓ Redirects table created/verified</p>';
    $tables_created++;
} else {
    echo '<p class="error">✗ Failed to create redirects table</p>';
    $tables_failed++;
}

// 404 Monitor table
$table_name = $wpdb->prefix . 'seo_simple_404_monitor';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    url varchar(500) NOT NULL,
    referer varchar(500) DEFAULT NULL,
    user_agent varchar(255) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    hits bigint(20) DEFAULT 1,
    first_seen datetime DEFAULT CURRENT_TIMESTAMP,
    last_seen datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY url (url(191)),
    KEY last_seen (last_seen)
) $charset_collate;";
dbDelta($sql);
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    echo '<p class="success">✓ 404 Monitor table created/verified</p>';
    $tables_created++;
} else {
    echo '<p class="error">✗ Failed to create 404 monitor table</p>';
    $tables_failed++;
}

// Code Snippets table
$table_name = $wpdb->prefix . 'seo_simple_code_snippets';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    code text NOT NULL,
    location varchar(50) NOT NULL,
    active tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id)
) $charset_collate;";
dbDelta($sql);
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    echo '<p class="success">✓ Code Snippets table created/verified</p>';
    $tables_created++;
} else {
    echo '<p class="error">✗ Failed to create code snippets table</p>';
    $tables_failed++;
}

echo '<div class="notice notice-info"><p><strong>Tables Created:</strong> ' . $tables_created . ' | <strong>Failed:</strong> ' . $tables_failed . '</p></div>';
$fixes_applied[] = "Created $tables_created database tables";
echo '</div>';

// STEP 3: Initialize Plugin Settings
echo '<div class="step">';
echo '<h2><span class="step-number">3</span>Initializing Plugin Settings</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Initializing settings');

$settings = get_option('seo_simple_settings', array());
$settings_count = count($settings);

$default_settings = array(
    // General
    'seo_enable_posts' => 1,
    'seo_enable_pages' => 1,
    'seo_enable_categories' => 1,
    'seo_enable_tags' => 1,
    'seo_enable_custom_post_types' => 1,
    
    // Titles & Meta
    'seo_title_separator' => '|',
    'seo_title_format' => '%post_title% | %site_name%',
    'seo_description_template' => '',
    'seo_home_title' => get_bloginfo('name') . ' | ' . get_bloginfo('description'),
    'seo_home_description' => get_bloginfo('description'),
    'seo_title_length' => 60,
    'seo_description_length' => 160,
    
    // Social
    'seo_enable_og' => 1,
    'seo_enable_twitter' => 1,
    'seo_enable_json_ld' => 1,
    'seo_og_image' => '',
    'seo_twitter_card_type' => 'summary_large_image',
    'seo_twitter_username' => '',
    
    // Sitemap
    'seo_enable_sitemap' => 1,
    'seo_sitemap_include_posts' => 1,
    'seo_sitemap_include_pages' => 1,
    'seo_sitemap_include_categories' => 1,
    'seo_sitemap_include_tags' => 1,
    
    // Advanced
    'seo_show_metabox' => 1,
    'seo_enable_analysis' => 1,
    'seo_enable_readability' => 1,
    'seo_enable_robots' => 1,
    'seo_enable_canonical' => 1,
    
    // Features
    'seo_enable_redirects' => 1,
    'seo_enable_404_monitor' => 1,
    'seo_404_monitor_retention_days' => 30,
    'seo_enable_breadcrumbs' => 1,
    'seo_enable_internal_links' => 1,
    
    // Performance
    'seo_enable_speed_optimization' => 0,
    'seo_enable_page_caching' => 0,
    'seo_enable_lazy_loading' => 0,
    'seo_enable_gzip_compression' => 0,
    'seo_enable_minification' => 0,
    
    // Security
    'seo_enable_security' => 0,
    'seo_enable_waf' => 0,
    'seo_enable_brute_force_protection' => 0,
);

if (empty($settings)) {
    update_option('seo_simple_settings', $default_settings);
    echo '<div class="notice notice-success"><p class="success">✓ Default settings initialized (' . count($default_settings) . ' settings)</p></div>';
    $fixes_applied[] = 'Initialized default settings';
} else {
    // Merge with existing settings
    $updated = false;
    foreach ($default_settings as $key => $value) {
        if (!isset($settings[$key])) {
            $settings[$key] = $value;
            $updated = true;
        }
    }
    
    if ($updated) {
        update_option('seo_simple_settings', $settings);
        echo '<div class="notice notice-success"><p class="success">✓ Settings updated with missing defaults</p></div>';
        $fixes_applied[] = 'Updated settings with defaults';
    } else {
        echo '<div class="notice notice-info"><p class="info">ℹ Settings already configured (' . count($settings) . ' settings)</p></div>';
    }
}

echo '<p><strong>Current Settings Count:</strong> ' . count(get_option('seo_simple_settings', array())) . '</p>';
echo '</div>';

// STEP 4: Create Required Directories
echo '<div class="step">';
echo '<h2><span class="step-number">4</span>Creating Required Directories</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Creating directories');

$plugin_dir = plugin_dir_path(__FILE__);
$directories = array(
    'cache' => $plugin_dir . 'cache',
    'logs' => $plugin_dir . 'logs',
);

foreach ($directories as $name => $dir) {
    if (!file_exists($dir)) {
        if (wp_mkdir_p($dir)) {
            // Create .htaccess
            file_put_contents($dir . '/.htaccess', "Order deny,allow\nDeny from all");
            // Create index.php
            file_put_contents($dir . '/index.php', '<?php // Silence is golden');
            echo '<p class="success">✓ Created ' . $name . ' directory</p>';
            $fixes_applied[] = "Created $name directory";
        } else {
            echo '<p class="error">✗ Failed to create ' . $name . ' directory</p>';
            $fixes_failed[] = "Failed to create $name directory";
        }
    } else {
        echo '<p class="info">ℹ ' . ucfirst($name) . ' directory already exists</p>';
    }
}
echo '</div>';

// STEP 5: Clear All Caches
echo '<div class="step">';
echo '<h2><span class="step-number">5</span>Clearing Caches</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Clearing caches');

// Clear WordPress cache
wp_cache_flush();
echo '<p class="success">✓ WordPress object cache cleared</p>';

// Clear transients
$deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_seo_simple_%' OR option_name LIKE '_transient_timeout_seo_simple_%'");
echo '<p class="success">✓ Plugin transients cleared (' . $deleted . ' items)</p>';

// Flush rewrite rules
flush_rewrite_rules();
echo '<p class="success">✓ Rewrite rules flushed</p>';

$fixes_applied[] = 'Cleared all caches';
echo '</div>';

// STEP 6: Verify Plugin Files
echo '<div class="step">';
echo '<h2><span class="step-number">6</span>Verifying Plugin Files</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Verifying files');

$required_files = array(
    'seo-simple.php' => 'Main plugin file',
    'includes/class-seo-simple.php' => 'Core class',
    'includes/class-seo-simple-meta.php' => 'Meta handler',
    'includes/class-seo-simple-frontend.php' => 'Frontend handler',
    'admin/class-seo-simple-admin.php' => 'Admin class',
    'admin/class-seo-simple-meta-box.php' => 'Meta box',
    'admin/class-seo-simple-settings.php' => 'Settings',
);

$files_ok = 0;
$files_missing = 0;

echo '<table>';
echo '<tr><th>File</th><th>Description</th><th>Status</th></tr>';
foreach ($required_files as $file => $desc) {
    $exists = file_exists($plugin_dir . $file);
    echo '<tr>';
    echo '<td><code>' . esc_html($file) . '</code></td>';
    echo '<td>' . esc_html($desc) . '</td>';
    echo '<td>' . ($exists ? '<span class="success">✓ OK</span>' : '<span class="error">✗ Missing</span>') . '</td>';
    echo '</tr>';
    
    if ($exists) {
        $files_ok++;
    } else {
        $files_missing++;
        $fixes_failed[] = "Missing file: $file";
    }
}
echo '</table>';

if ($files_missing == 0) {
    echo '<div class="notice notice-success"><p class="success">✓ All required files present</p></div>';
    $fixes_applied[] = 'All files verified';
} else {
    echo '<div class="notice notice-error"><p class="error">✗ ' . $files_missing . ' required files missing</p></div>';
}
echo '</div>';

// STEP 7: Test Plugin Activation
echo '<div class="step">';
echo '<h2><span class="step-number">7</span>Testing Plugin Activation</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Testing activation');

if (class_exists('Seo_Simple')) {
    echo '<p class="success">✓ Main plugin class loaded</p>';
    $fixes_applied[] = 'Plugin class verified';
} else {
    echo '<p class="error">✗ Main plugin class not found</p>';
    $fixes_failed[] = 'Plugin class not found';
}

if (is_plugin_active('seo-simple/seo-simple.php')) {
    echo '<p class="success">✓ Plugin is active</p>';
    $fixes_applied[] = 'Plugin is active';
} else {
    echo '<p class="warning">⚠ Plugin is not active - please activate it</p>';
}

echo '</div>';

// STEP 8: Test Database Connectivity
echo '<div class="step">';
echo '<h2><span class="step-number">8</span>Testing Database</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Testing database');

$test = $wpdb->get_var("SELECT 1");
if ($test == 1) {
    echo '<p class="success">✓ Database connection working</p>';
    $fixes_applied[] = 'Database connection verified';
} else {
    echo '<p class="error">✗ Database connection failed</p>';
    $fixes_failed[] = 'Database connection failed';
}

// Test table access
$table_name = $wpdb->prefix . 'seo_simple_redirects';
$test_table = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
if ($test_table == $table_name) {
    echo '<p class="success">✓ Can access plugin tables</p>';
} else {
    echo '<p class="error">✗ Cannot access plugin tables</p>';
}

echo '</div>';

// STEP 9: Set Proper Permissions
echo '<div class="step">';
echo '<h2><span class="step-number">9</span>Setting Permissions</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Setting permissions');

$writable_dirs = array('cache', 'logs');
foreach ($writable_dirs as $dir) {
    $dir_path = $plugin_dir . $dir;
    if (file_exists($dir_path)) {
        if (is_writable($dir_path)) {
            echo '<p class="success">✓ ' . ucfirst($dir) . ' directory is writable</p>';
        } else {
            echo '<p class="warning">⚠ ' . ucfirst($dir) . ' directory is not writable</p>';
            @chmod($dir_path, 0755);
        }
    }
}

$fixes_applied[] = 'Permissions checked';
echo '</div>';

// STEP 10: Final Verification
echo '<div class="step">';
echo '<h2><span class="step-number">10</span>Final Verification</h2>';
$current_step++;
show_progress($current_step, $total_steps, 'Final checks');

$all_good = true;

// Check if settings exist
$settings = get_option('seo_simple_settings');
if (!empty($settings)) {
    echo '<p class="success">✓ Plugin settings configured</p>';
} else {
    echo '<p class="error">✗ Plugin settings not found</p>';
    $all_good = false;
}

// Check if tables exist
$tables_exist = true;
$required_tables = array(
    $wpdb->prefix . 'seo_simple_redirects',
    $wpdb->prefix . 'seo_simple_404_monitor',
    $wpdb->prefix . 'seo_simple_code_snippets',
);

foreach ($required_tables as $table) {
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $tables_exist = false;
        break;
    }
}

if ($tables_exist) {
    echo '<p class="success">✓ All database tables exist</p>';
} else {
    echo '<p class="error">✗ Some database tables missing</p>';
    $all_good = false;
}

// Check if plugin is active
if (is_plugin_active('seo-simple/seo-simple.php')) {
    echo '<p class="success">✓ Plugin is active</p>';
} else {
    echo '<p class="warning">⚠ Plugin needs to be activated</p>';
}

echo '</div>';

// Summary
echo '<div class="step" style="background: #f0f6fc; border: 2px solid #2271b1;">';
echo '<h2>📊 Summary</h2>';
echo '<table>';
echo '<tr><th>Category</th><th>Count</th></tr>';
echo '<tr><td class="success">Fixes Applied</td><td><strong>' . count($fixes_applied) . '</strong></td></tr>';
echo '<tr><td class="error">Fixes Failed</td><td><strong>' . count($fixes_failed) . '</strong></td></tr>';
echo '</table>';

if (count($fixes_failed) == 0 && $all_good) {
    echo '<div class="notice notice-success">';
    echo '<h3 style="margin-top:0;">✅ Success! Plugin is Ready</h3>';
    echo '<p>All fixes have been applied successfully. Your SEO plugin should now work correctly.</p>';
    echo '</div>';
} elseif (count($fixes_failed) > 0) {
    echo '<div class="notice notice-error">';
    echo '<h3 style="margin-top:0;">⚠️ Some Issues Remain</h3>';
    echo '<p>Please review the failed items above and fix them manually.</p>';
    echo '</div>';
} else {
    echo '<div class="notice notice-warning">';
    echo '<h3 style="margin-top:0;">⚠️ Action Required</h3>';
    echo '<p>Please activate the plugin to complete the setup.</p>';
    echo '</div>';
}

echo '<h3>Applied Fixes:</h3>';
echo '<ul>';
foreach ($fixes_applied as $fix) {
    echo '<li class="success">✓ ' . esc_html($fix) . '</li>';
}
echo '</ul>';

if (count($fixes_failed) > 0) {
    echo '<h3>Failed Fixes:</h3>';
    echo '<ul>';
    foreach ($fixes_failed as $fix) {
        echo '<li class="error">✗ ' . esc_html($fix) . '</li>';
    }
    echo '</ul>';
}

echo '</div>';

// Next Steps
echo '<div class="step" style="background: #edfaef; border: 2px solid #00a32a;">';
echo '<h2>🚀 Next Steps</h2>';
echo '<ol style="font-size: 16px; line-height: 2;">';

if (!is_plugin_active('seo-simple/seo-simple.php')) {
    echo '<li><strong>Activate the plugin:</strong> Go to <a href="' . admin_url('plugins.php') . '">Plugins</a> and activate "Simple SEO"</li>';
}

echo '<li><strong>Configure settings:</strong> Go to <a href="' . admin_url('admin.php?page=seo-simple-settings') . '">SEO → Settings</a></li>';
echo '<li><strong>Test on a post:</strong> Edit any post and look for the SEO meta box</li>';
echo '<li><strong>Verify output:</strong> View a page and check the source code for meta tags</li>';
echo '<li><strong>Check sitemap:</strong> Visit <a href="' . home_url('/sitemap.xml') . '" target="_blank">' . home_url('/sitemap.xml') . '</a></li>';
echo '</ol>';

echo '<p style="margin-top: 20px;"><strong>⚠️ Security Note:</strong> Delete this file (<code>fix-plugin-properly.php</code>) after running it!</p>';

echo '</div>';

?>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?php echo admin_url('plugins.php'); ?>" class="button">Go to Plugins</a>
    <a href="<?php echo admin_url('admin.php?page=seo-simple-settings'); ?>" class="button">Go to Settings</a>
    <a href="<?php echo admin_url(); ?>" class="button button-secondary">Go to Dashboard</a>
</p>

<p style="text-align: center; color: #646970; margin-top: 20px;">
    <strong>Completed:</strong> <?php echo date('Y-m-d H:i:s'); ?>
</p>

</div>
</body>
</html>
