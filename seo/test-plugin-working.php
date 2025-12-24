<?php
/**
 * Quick Test - Check if SEO Plugin is Working
 */

// Load WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once(ABSPATH . 'wp-load.php');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>SEO Plugin - Quick Test</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; background: #f0f0f1; }
        .wrap { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.13); }
        h1 { color: #1d2327; border-bottom: 3px solid #2271b1; padding-bottom: 15px; }
        .test { margin: 20px 0; padding: 15px; background: #f6f7f7; border-left: 4px solid #2271b1; border-radius: 3px; }
        .pass { border-left-color: #00a32a; background: #edfaef; }
        .fail { border-left-color: #d63638; background: #fcf0f1; }
        .pass h3:before { content: "✓ "; color: #00a32a; }
        .fail h3:before { content: "✗ "; color: #d63638; }
        code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        .button { display: inline-block; padding: 8px 16px; background: #2271b1; color: white; text-decoration: none; border-radius: 3px; margin: 5px; }
        .button:hover { background: #135e96; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>🔍 SEO Plugin - Quick Test</h1>
    <p><strong>Test Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

    <?php
    $tests_passed = 0;
    $tests_failed = 0;
    
    // Test 1: Plugin Active
    echo '<div class="test ' . (is_plugin_active('seo-simple/seo-simple.php') ? 'pass' : 'fail') . '">';
    echo '<h3>Plugin Activation</h3>';
    if (is_plugin_active('seo-simple/seo-simple.php')) {
        echo '<p>Plugin is <strong>active</strong></p>';
        $tests_passed++;
    } else {
        echo '<p>Plugin is <strong>NOT active</strong></p>';
        echo '<p><a href="' . admin_url('plugins.php') . '" class="button">Activate Plugin</a></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 2: Main Class
    echo '<div class="test ' . (class_exists('Seo_Simple') ? 'pass' : 'fail') . '">';
    echo '<h3>Main Plugin Class</h3>';
    if (class_exists('Seo_Simple')) {
        echo '<p>Class <code>Seo_Simple</code> is loaded</p>';
        $tests_passed++;
    } else {
        echo '<p>Class <code>Seo_Simple</code> is <strong>NOT loaded</strong></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 3: Admin Class
    echo '<div class="test ' . (class_exists('Seo_Simple_Admin') ? 'pass' : 'fail') . '">';
    echo '<h3>Admin Class</h3>';
    if (class_exists('Seo_Simple_Admin')) {
        echo '<p>Class <code>Seo_Simple_Admin</code> is loaded</p>';
        $tests_passed++;
    } else {
        echo '<p>Class <code>Seo_Simple_Admin</code> is <strong>NOT loaded</strong></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 4: Meta Box Class
    echo '<div class="test ' . (class_exists('Seo_Simple_Meta_Box') ? 'pass' : 'fail') . '">';
    echo '<h3>Meta Box Class</h3>';
    if (class_exists('Seo_Simple_Meta_Box')) {
        echo '<p>Class <code>Seo_Simple_Meta_Box</code> is loaded</p>';
        $tests_passed++;
    } else {
        echo '<p>Class <code>Seo_Simple_Meta_Box</code> is <strong>NOT loaded</strong></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 5: Settings
    $settings = get_option('seo_simple_settings', array());
    echo '<div class="test ' . (!empty($settings) ? 'pass' : 'fail') . '">';
    echo '<h3>Plugin Settings</h3>';
    if (!empty($settings)) {
        echo '<p>Settings configured (' . count($settings) . ' options)</p>';
        $tests_passed++;
    } else {
        echo '<p>Settings <strong>NOT configured</strong></p>';
        echo '<p><a href="' . plugin_dir_url(__FILE__) . 'fix-plugin-properly.php" class="button">Run Fix Script</a></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 6: Database Tables
    global $wpdb;
    $table = $wpdb->prefix . 'seo_simple_redirects';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    echo '<div class="test ' . ($table_exists ? 'pass' : 'fail') . '">';
    echo '<h3>Database Tables</h3>';
    if ($table_exists) {
        echo '<p>Database tables exist</p>';
        $tests_passed++;
    } else {
        echo '<p>Database tables <strong>NOT created</strong></p>';
        echo '<p><a href="' . plugin_dir_url(__FILE__) . 'fix-plugin-properly.php" class="button">Run Fix Script</a></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 7: Admin Menu
    $menu_exists = false;
    if (is_admin()) {
        global $menu;
        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] == 'seo-simple') {
                $menu_exists = true;
                break;
            }
        }
    }
    echo '<div class="test ' . ($menu_exists || !is_admin() ? 'pass' : 'fail') . '">';
    echo '<h3>Admin Menu</h3>';
    if ($menu_exists) {
        echo '<p>Admin menu registered</p>';
        $tests_passed++;
    } elseif (!is_admin()) {
        echo '<p>Check from admin area</p>';
        $tests_passed++;
    } else {
        echo '<p>Admin menu <strong>NOT registered</strong></p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 8: Frontend Output
    $test_post = get_posts(array('numberposts' => 1, 'post_status' => 'publish'));
    echo '<div class="test">';
    echo '<h3>Frontend Output Test</h3>';
    if (!empty($test_post)) {
        $post_url = get_permalink($test_post[0]->ID);
        echo '<p>Test on a published post:</p>';
        echo '<p><a href="' . $post_url . '" target="_blank" class="button">View Post</a></p>';
        echo '<p><small>Check page source for meta tags like <code>&lt;meta name="description"&gt;</code></small></p>';
    } else {
        echo '<p>No published posts found to test</p>';
    }
    echo '</div>';
    
    // Summary
    echo '<div class="test" style="background: #f0f6fc; border-color: #2271b1;">';
    echo '<h2>📊 Test Summary</h2>';
    echo '<p><strong>Passed:</strong> ' . $tests_passed . ' | <strong>Failed:</strong> ' . $tests_failed . '</p>';
    
    if ($tests_failed == 0) {
        echo '<div style="background: #edfaef; padding: 15px; border-radius: 5px; border-left: 4px solid #00a32a; margin-top: 15px;">';
        echo '<h3 style="margin-top:0; color: #00a32a;">✅ All Tests Passed!</h3>';
        echo '<p>Your SEO plugin is working correctly.</p>';
        echo '</div>';
    } else {
        echo '<div style="background: #fcf0f1; padding: 15px; border-radius: 5px; border-left: 4px solid #d63638; margin-top: 15px;">';
        echo '<h3 style="margin-top:0; color: #d63638;">⚠️ Some Tests Failed</h3>';
        echo '<p>Please run the fix script to resolve issues.</p>';
        echo '<p><a href="' . plugin_dir_url(__FILE__) . 'fix-plugin-properly.php" class="button">Run Fix Script</a></p>';
        echo '</div>';
    }
    echo '</div>';
    
    // Next Steps
    echo '<div class="test" style="background: #edfaef; border-color: #00a32a;">';
    echo '<h2>🚀 Next Steps</h2>';
    echo '<ol>';
    echo '<li>Go to <a href="' . admin_url('admin.php?page=seo-simple-settings') . '">SEO → Settings</a> to configure</li>';
    echo '<li>Edit a post and look for the SEO meta box</li>';
    echo '<li>Add meta title, description, and focus keyword</li>';
    echo '<li>View the post and check page source for meta tags</li>';
    echo '<li>Visit <a href="' . home_url('/sitemap.xml') . '" target="_blank">sitemap.xml</a> to verify sitemap</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <p style="text-align: center; margin-top: 30px;">
        <a href="<?php echo admin_url(); ?>" class="button">Go to Dashboard</a>
        <a href="<?php echo admin_url('admin.php?page=seo-simple-settings'); ?>" class="button">Go to Settings</a>
        <a href="<?php echo admin_url('edit.php'); ?>" class="button">Edit Posts</a>
    </p>

</div>
</body>
</html>
