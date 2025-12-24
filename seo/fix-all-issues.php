<?php
/**
 * Comprehensive Fix Script for SEO Simple Plugin
 * 
 * This script fixes common issues and ensures the plugin works correctly
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

?>
<!DOCTYPE html>
<html>
<head>
    <title>SEO Simple - Fix All Issues</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { color: #46b450; font-weight: bold; }
        .warning { color: #ffb900; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #0073aa; }
        .button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 SEO Simple Plugin - Fix All Issues</h1>
    <p><strong>Started:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

$fixes_applied = array();
$fixes_failed = array();

// Fix 1: Create missing database tables
echo '<div class="section">';
echo '<h2>1. Creating Missing Database Tables</h2>';

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();

// Redirects table
$table_name = $wpdb->prefix . 'seo_simple_redirects';
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        source_url varchar(255) NOT NULL,
        target_url varchar(255) NOT NULL,
        redirect_type varchar(10) DEFAULT '301',
        hits bigint(20) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY source_url (source_url)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        echo '<p class="success">✓ Created redirects table</p>';
        $fixes_applied[] = 'Created redirects table';
    } else {
        echo '<p class="error">✗ Failed to create redirects table</p>';
        $fixes_failed[] = 'Failed to create redirects table';
    }
} else {
    echo '<p class="info">ℹ Redirects table already exists</p>';
}

// 404 Monitor table
$table_name = $wpdb->prefix . 'seo_simple_404_monitor';
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        referer varchar(255) DEFAULT NULL,
        user_agent varchar(255) DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        hits bigint(20) DEFAULT 1,
        first_seen datetime DEFAULT CURRENT_TIMESTAMP,
        last_seen datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY url (url),
        KEY last_seen (last_seen)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        echo '<p class="success">✓ Created 404 monitor table</p>';
        $fixes_applied[] = 'Created 404 monitor table';
    } else {
        echo '<p class="error">✗ Failed to create 404 monitor table</p>';
        $fixes_failed[] = 'Failed to create 404 monitor table';
    }
} else {
    echo '<p class="info">ℹ 404 monitor table already exists</p>';
}

// Code Snippets table
$table_name = $wpdb->prefix . 'seo_simple_code_snippets';
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    $sql = "CREATE TABLE $table_name (
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
        echo '<p class="success">✓ Created code snippets table</p>';
        $fixes_applied[] = 'Created code snippets table';
    } else {
        echo '<p class="error">✗ Failed to create code snippets table</p>';
        $fixes_failed[] = 'Failed to create code snippets table';
    }
} else {
    echo '<p class="info">ℹ Code snippets table already exists</p>';
}

echo '</div>';

// Fix 2: Initialize default settings
echo '<div class="section">';
echo '<h2>2. Initializing Default Settings</h2>';

$settings = get_option('seo_simple_settings', array());

if (empty($settings)) {
    $default_settings = array(
        // General Settings
        'seo_enable_posts' => 1,
        'seo_enable_pages' => 1,
        'seo_enable_categories' => 1,
        'seo_enable_tags' => 1,
        'seo_enable_custom_post_types' => 1,

        // Titles & Meta
        'seo_title_separator' => '-',
        'seo_title_format' => '%post_title% | %site_name%',
        'seo_description_template' => '',
        'seo_home_title' => get_bloginfo('name') . ' | ' . get_bloginfo('description'),
        'seo_home_description' => get_bloginfo('description'),
        'seo_title_length' => 60,
        'seo_description_length' => 160,

        // Social Settings
        'seo_enable_og' => 1,
        'seo_enable_twitter' => 1,
        'seo_enable_json_ld' => 1,
        'seo_og_image' => '',
        'seo_twitter_card_type' => 'summary_large_image',

        // Sitemap Settings
        'seo_enable_sitemap' => 1,
        'seo_sitemap_include_posts' => 1,
        'seo_sitemap_include_pages' => 1,
        'seo_sitemap_include_categories' => 1,
        'seo_sitemap_include_tags' => 1,

        // Advanced Settings
        'seo_show_metabox' => 1,
        'seo_enable_analysis' => 1,
        'seo_enable_readability' => 1,
        'seo_enable_robots' => 1,
        'seo_enable_canonical' => 1,
        
        // Redirect Settings
        'seo_enable_redirects' => 1,
        'seo_redirect_404_to_homepage' => 0,
        
        // 404 Monitor Settings
        'seo_enable_404_monitor' => 1,
        'seo_404_monitor_retention_days' => 30,
        
        // Security Settings
        'seo_enable_security' => 1,
        'seo_enable_waf' => 1,
        'seo_enable_brute_force_protection' => 1,
        
        // Speed Optimization Settings
        'seo_enable_speed_optimization' => 1,
        'seo_enable_page_caching' => 1,
        'seo_enable_lazy_loading' => 1,
        'seo_enable_gzip_compression' => 1,
        'seo_enable_minification' => 1,
    );
    
    $result = update_option('seo_simple_settings', $default_settings);
    
    if ($result) {
        echo '<p class="success">✓ Default settings initialized</p>';
        echo '<p><strong>Settings count:</strong> ' . count($default_settings) . '</p>';
        $fixes_applied[] = 'Initialized default settings';
    } else {
        echo '<p class="error">✗ Failed to initialize settings</p>';
        $fixes_failed[] = 'Failed to initialize settings';
    }
} else {
    echo '<p class="info">ℹ Settings already exist</p>';
    echo '<p><strong>Current settings count:</strong> ' . count($settings) . '</p>';
    
    // Add missing settings
    $default_settings = array(
        'seo_enable_posts' => 1,
        'seo_enable_pages' => 1,
        'seo_title_separator' => '-',
        'seo_title_format' => '%post_title% | %site_name%',
        'seo_title_length' => 60,
        'seo_description_length' => 160,
        'seo_enable_og' => 1,
        'seo_enable_twitter' => 1,
        'seo_enable_json_ld' => 1,
    );
    
    $updated = false;
    foreach ($default_settings as $key => $value) {
        if (!isset($settings[$key])) {
            $settings[$key] = $value;
            $updated = true;
        }
    }
    
    if ($updated) {
        update_option('seo_simple_settings', $settings);
        echo '<p class="success">✓ Added missing settings</p>';
        $fixes_applied[] = 'Added missing settings';
    }
}

echo '</div>';

// Fix 3: Create cache directory
echo '<div class="section">';
echo '<h2>3. Creating Cache Directory</h2>';

$cache_dir = plugin_dir_path(__FILE__) . 'cache';
if (!file_exists($cache_dir)) {
    if (mkdir($cache_dir, 0755, true)) {
        echo '<p class="success">✓ Cache directory created</p>';
        $fixes_applied[] = 'Created cache directory';
        
        // Create .htaccess to protect cache directory
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents($cache_dir . '/.htaccess', $htaccess_content);
        
        // Create index.php to prevent directory listing
        file_put_contents($cache_dir . '/index.php', '<?php // Silence is golden');
    } else {
        echo '<p class="error">✗ Failed to create cache directory</p>';
        $fixes_failed[] = 'Failed to create cache directory';
    }
} else {
    echo '<p class="info">ℹ Cache directory already exists</p>';
}

echo '</div>';

// Fix 4: Clear all caches
echo '<div class="section">';
echo '<h2>4. Clearing Caches</h2>';

// Clear WordPress cache
wp_cache_flush();
echo '<p class="success">✓ WordPress cache cleared</p>';
$fixes_applied[] = 'Cleared WordPress cache';

// Clear transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_seo_simple_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_seo_simple_%'");
echo '<p class="success">✓ Plugin transients cleared</p>';
$fixes_applied[] = 'Cleared plugin transients';

// Clear rewrite rules
flush_rewrite_rules();
echo '<p class="success">✓ Rewrite rules flushed</p>';
$fixes_applied[] = 'Flushed rewrite rules';

echo '</div>';

// Fix 5: Verify plugin activation
echo '<div class="section">';
echo '<h2>5. Verifying Plugin Activation</h2>';

if (class_exists('Seo_Simple')) {
    echo '<p class="success">✓ Plugin main class loaded</p>';
    $fixes_applied[] = 'Plugin main class verified';
} else {
    echo '<p class="error">✗ Plugin main class not found</p>';
    $fixes_failed[] = 'Plugin main class not found';
}

// Check if plugin is active
$active_plugins = get_option('active_plugins', array());
$plugin_file = 'seo-simple/seo-simple.php';

if (in_array($plugin_file, $active_plugins) || is_plugin_active($plugin_file)) {
    echo '<p class="success">✓ Plugin is active</p>';
    $fixes_applied[] = 'Plugin activation verified';
} else {
    echo '<p class="warning">⚠ Plugin is not active</p>';
    echo '<p>Please activate the plugin from the WordPress admin panel.</p>';
}

echo '</div>';

// Fix 6: Test database connectivity
echo '<div class="section">';
echo '<h2>6. Testing Database Connectivity</h2>';

$test_query = $wpdb->get_var("SELECT 1");
if ($test_query == 1) {
    echo '<p class="success">✓ Database connection working</p>';
    $fixes_applied[] = 'Database connection verified';
} else {
    echo '<p class="error">✗ Database connection failed</p>';
    $fixes_failed[] = 'Database connection failed';
}

echo '</div>';

// Summary
echo '<div class="section">';
echo '<h2>Summary</h2>';
echo '<p><strong>Fixes Applied:</strong> ' . count($fixes_applied) . '</p>';
echo '<p><strong>Fixes Failed:</strong> ' . count($fixes_failed) . '</p>';

if (count($fixes_failed) === 0) {
    echo '<p class="success" style="font-size: 18px;">✓ All fixes applied successfully!</p>';
    echo '<p>Your plugin should now work correctly. Please test it in the WordPress admin panel.</p>';
} else {
    echo '<p class="warning" style="font-size: 18px;">⚠ Some fixes failed. Please review the errors above.</p>';
}

echo '<h3>Applied Fixes:</h3>';
echo '<ul>';
foreach ($fixes_applied as $fix) {
    echo '<li class="success">' . esc_html($fix) . '</li>';
}
echo '</ul>';

if (count($fixes_failed) > 0) {
    echo '<h3>Failed Fixes:</h3>';
    echo '<ul>';
    foreach ($fixes_failed as $fix) {
        echo '<li class="error">' . esc_html($fix) . '</li>';
    }
    echo '</ul>';
}

echo '</div>';

// Next steps
echo '<div class="section">';
echo '<h2>Next Steps</h2>';
echo '<ul>';
echo '<li>Go to <a href="' . admin_url('admin.php?page=seo-simple-settings') . '">SEO Settings</a> to configure the plugin</li>';
echo '<li>Edit a post or page to see the SEO meta box</li>';
echo '<li>Run the <a href="comprehensive-diagnostic.php">Comprehensive Diagnostic</a> to verify everything is working</li>';
echo '<li>Check the <a href="' . admin_url('admin.php?page=seo-simple-dashboard') . '">SEO Dashboard</a> for an overview</li>';
echo '</ul>';
echo '</div>';

?>

<p><strong>Completed:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
</div>
</body>
</html>
