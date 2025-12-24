<?php
/**
 * Comprehensive WordPress SEO Plugin Diagnostic Tool
 * 
 * This script checks for common issues in the SEO Simple plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once(ABSPATH . 'wp-load.php');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>SEO Simple - Comprehensive Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { color: #46b450; font-weight: bold; }
        .warning { color: #ffb900; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #0073aa; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0073aa; color: white; }
        tr:hover { background: #f5f5f5; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }
        ul { line-height: 1.8; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 SEO Simple Plugin - Comprehensive Diagnostic Report</h1>
    <p><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

$issues = array();
$warnings = array();
$successes = array();

// 1. Check if plugin is active
echo '<div class="section">';
echo '<h2>1. Plugin Status</h2>';
if (is_plugin_active('seo-simple/seo-simple.php') || class_exists('Seo_Simple')) {
    echo '<p class="success">✓ Plugin is active</p>';
    $successes[] = 'Plugin is active';
} else {
    echo '<p class="error">✗ Plugin is NOT active</p>';
    $issues[] = 'Plugin is not active';
}
echo '</div>';

// 2. Check required files
echo '<div class="section">';
echo '<h2>2. Required Files Check</h2>';
$required_files = array(
    'seo-simple.php' => 'Main plugin file',
    'includes/class-seo-simple.php' => 'Core plugin class',
    'includes/class-seo-simple-activator.php' => 'Activator class',
    'includes/class-seo-simple-deactivator.php' => 'Deactivator class',
    'includes/class-seo-simple-loader.php' => 'Loader class',
    'includes/class-seo-simple-meta.php' => 'Meta handler',
    'includes/class-seo-simple-frontend.php' => 'Frontend handler',
    'admin/class-seo-simple-admin.php' => 'Admin class',
    'public/class-seo-simple-public.php' => 'Public class',
);

echo '<table>';
echo '<tr><th>File</th><th>Description</th><th>Status</th></tr>';
foreach ($required_files as $file => $description) {
    $file_path = plugin_dir_path(__FILE__) . $file;
    $exists = file_exists($file_path);
    $status = $exists ? '<span class="success">✓ Exists</span>' : '<span class="error">✗ Missing</span>';
    echo "<tr><td>$file</td><td>$description</td><td>$status</td></tr>";
    
    if (!$exists) {
        $issues[] = "Missing file: $file";
    } else {
        $successes[] = "File exists: $file";
    }
}
echo '</table>';
echo '</div>';

// 3. Check class definitions
echo '<div class="section">';
echo '<h2>3. Class Definitions Check</h2>';
$required_classes = array(
    'Seo_Simple',
    'Seo_Simple_Loader',
    'Seo_Simple_Meta',
    'Seo_Simple_Frontend',
    'Seo_Simple_Admin',
    'Seo_Simple_Public',
    'Seo_Simple_Activator',
    'Seo_Simple_Deactivator',
);

echo '<table>';
echo '<tr><th>Class</th><th>Status</th></tr>';
foreach ($required_classes as $class) {
    $exists = class_exists($class);
    $status = $exists ? '<span class="success">✓ Loaded</span>' : '<span class="error">✗ Not Found</span>';
    echo "<tr><td>$class</td><td>$status</td></tr>";
    
    if (!$exists) {
        $issues[] = "Class not found: $class";
    } else {
        $successes[] = "Class loaded: $class";
    }
}
echo '</table>';
echo '</div>';

// 4. Check database tables
echo '<div class="section">';
echo '<h2>4. Database Tables Check</h2>';
global $wpdb;
$required_tables = array(
    $wpdb->prefix . 'seo_simple_redirects' => 'Redirects table',
    $wpdb->prefix . 'seo_simple_404_monitor' => '404 Monitor table',
    $wpdb->prefix . 'seo_simple_code_snippets' => 'Code Snippets table',
);

echo '<table>';
echo '<tr><th>Table</th><th>Description</th><th>Status</th></tr>';
foreach ($required_tables as $table => $description) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    $status = $exists ? '<span class="success">✓ Exists</span>' : '<span class="warning">⚠ Missing (will be created on activation)</span>';
    echo "<tr><td>$table</td><td>$description</td><td>$status</td></tr>";
    
    if (!$exists) {
        $warnings[] = "Table missing: $table (will be created on activation)";
    } else {
        $successes[] = "Table exists: $table";
    }
}
echo '</table>';
echo '</div>';

// 5. Check plugin options
echo '<div class="section">';
echo '<h2>5. Plugin Options Check</h2>';
$options = get_option('seo_simple_settings', array());
if (!empty($options)) {
    echo '<p class="success">✓ Plugin settings found</p>';
    echo '<p><strong>Settings count:</strong> ' . count($options) . '</p>';
    $successes[] = 'Plugin settings configured';
} else {
    echo '<p class="warning">⚠ No plugin settings found (will use defaults)</p>';
    $warnings[] = 'No plugin settings found';
}
echo '</div>';

// 6. Check for conflicts
echo '<div class="section">';
echo '<h2>6. Plugin Conflicts Check</h2>';
$seo_plugins = array(
    'wordpress-seo/wp-seo.php' => 'Yoast SEO',
    'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
    'seo-by-rank-math/rank-math.php' => 'Rank Math',
);

$conflicts = array();
foreach ($seo_plugins as $plugin => $name) {
    if (is_plugin_active($plugin)) {
        $conflicts[] = $name;
    }
}

if (empty($conflicts)) {
    echo '<p class="success">✓ No conflicting SEO plugins detected</p>';
    $successes[] = 'No plugin conflicts';
} else {
    echo '<p class="warning">⚠ Conflicting SEO plugins detected:</p>';
    echo '<ul>';
    foreach ($conflicts as $plugin) {
        echo "<li>$plugin</li>";
        $warnings[] = "Conflicting plugin: $plugin";
    }
    echo '</ul>';
}
echo '</div>';

// 7. Check PHP version and requirements
echo '<div class="section">';
echo '<h2>7. System Requirements Check</h2>';
echo '<table>';
echo '<tr><th>Requirement</th><th>Current</th><th>Required</th><th>Status</th></tr>';

// PHP Version
$php_version = phpversion();
$php_required = '7.0';
$php_ok = version_compare($php_version, $php_required, '>=');
echo '<tr><td>PHP Version</td><td>' . $php_version . '</td><td>' . $php_required . '+</td><td>' . 
     ($php_ok ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</td></tr>';

// WordPress Version
$wp_version = get_bloginfo('version');
$wp_required = '5.0';
$wp_ok = version_compare($wp_version, $wp_required, '>=');
echo '<tr><td>WordPress Version</td><td>' . $wp_version . '</td><td>' . $wp_required . '+</td><td>' . 
     ($wp_ok ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</td></tr>';

// MySQL Version
$mysql_version = $wpdb->db_version();
$mysql_required = '5.6';
$mysql_ok = version_compare($mysql_version, $mysql_required, '>=');
echo '<tr><td>MySQL Version</td><td>' . $mysql_version . '</td><td>' . $mysql_required . '+</td><td>' . 
     ($mysql_ok ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</td></tr>';

echo '</table>';

if ($php_ok && $wp_ok && $mysql_ok) {
    $successes[] = 'All system requirements met';
} else {
    $issues[] = 'System requirements not met';
}
echo '</div>';

// 8. Check permissions
echo '<div class="section">';
echo '<h2>8. File Permissions Check</h2>';
$writable_dirs = array(
    plugin_dir_path(__FILE__) . 'cache' => 'Cache directory',
    WP_CONTENT_DIR . '/uploads' => 'Uploads directory',
);

echo '<table>';
echo '<tr><th>Directory</th><th>Description</th><th>Status</th></tr>';
foreach ($writable_dirs as $dir => $description) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    $writable = is_writable($dir);
    $status = $writable ? '<span class="success">✓ Writable</span>' : '<span class="error">✗ Not Writable</span>';
    echo "<tr><td>$dir</td><td>$description</td><td>$status</td></tr>";
    
    if (!$writable) {
        $issues[] = "Directory not writable: $dir";
    } else {
        $successes[] = "Directory writable: $dir";
    }
}
echo '</table>';
echo '</div>';

// 9. Summary
echo '<div class="section">';
echo '<h2>9. Summary</h2>';
echo '<table>';
echo '<tr><th>Category</th><th>Count</th></tr>';
echo '<tr><td class="success">Successes</td><td>' . count($successes) . '</td></tr>';
echo '<tr><td class="warning">Warnings</td><td>' . count($warnings) . '</td></tr>';
echo '<tr><td class="error">Critical Issues</td><td>' . count($issues) . '</td></tr>';
echo '</table>';

if (count($issues) === 0 && count($warnings) === 0) {
    echo '<p class="success" style="font-size: 18px;">✓ All checks passed! Your plugin should work correctly.</p>';
} elseif (count($issues) === 0) {
    echo '<p class="warning" style="font-size: 18px;">⚠ Plugin should work, but there are some warnings to review.</p>';
} else {
    echo '<p class="error" style="font-size: 18px;">✗ Critical issues found. Please fix them before using the plugin.</p>';
}
echo '</div>';

// 10. Recommendations
echo '<div class="section">';
echo '<h2>10. Recommendations</h2>';
echo '<ul>';
if (count($issues) > 0) {
    echo '<li class="error">Fix all critical issues listed above</li>';
}
if (count($warnings) > 0) {
    echo '<li class="warning">Review and address warnings</li>';
}
if (count($conflicts) > 0) {
    echo '<li class="warning">Consider deactivating conflicting SEO plugins</li>';
}
echo '<li class="info">Enable WordPress debug mode to catch any runtime errors</li>';
echo '<li class="info">Test the plugin on a staging site before production</li>';
echo '<li class="info">Keep WordPress, PHP, and MySQL updated</li>';
echo '<li class="info">Regularly backup your database</li>';
echo '</ul>';
echo '</div>';

?>

</div>
</body>
</html>
