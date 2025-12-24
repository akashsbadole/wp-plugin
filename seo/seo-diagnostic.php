<?php
/**
 * SEO Diagnostic Tool
 * 
 * This tool helps diagnose why SEO tags might not be appearing in the source code.
 * Run this file in your WordPress root directory to check for issues.
 */

// Simulate WordPress environment if not already loaded
if (!function_exists('get_bloginfo')) {
    echo "<h1>WordPress Not Detected</h1>";
    echo "<p>Please run this diagnostic tool within a WordPress installation.</p>";
    exit;
}

// Check if plugin is active
if (!is_plugin_active('seo-simple/seo-simple.php')) {
    echo "<h1>Plugin Not Active</h1>";
    echo "<p>The SEO Simple plugin is not active. Please activate it first.</p>";
    exit;
}

echo "<h1>SEO Simple Diagnostic Tool</h1>";

// Check if hooks are registered
echo "<h2>Hook Registration Check</h2>";
global $wp_filter;

if (isset($wp_filter['wp_head'])) {
    $seo_hooks = array();
    
    foreach ($wp_filter['wp_head'] as $priority => $hooks) {
        foreach ($hooks as $hook) {
            if (is_array($hook['function']) && isset($hook['function'][0])) {
                $object = $hook['function'][0];
                if (is_object($object) && get_class($object) === 'Seo_Simple_Frontend') {
                    $method = $hook['function'][1];
                    $seo_hooks[$priority] = $method;
                }
            }
        }
    }
    
    if (!empty($seo_hooks)) {
        echo "<p>✓ SEO hooks are registered with wp_head:</p>";
        echo "<ul>";
        foreach ($seo_hooks as $priority => $method) {
            echo "<li>Priority $priority: $method</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>✗ No SEO hooks found registered with wp_head.</p>";
    }
} else {
    echo "<p>✗ No hooks registered with wp_head at all.</p>";
}

// Check if settings exist
echo "<h2>Settings Check</h2>";
$settings = get_option('seo_simple_settings', array());

if (empty($settings)) {
    echo "<p>⚠ No settings found. Using defaults.</p>";
    
    // Check if individual options exist
    $individual_settings = array(
        'seo_enable_og' => get_option('seo_enable_og', 1),
        'seo_enable_twitter' => get_option('seo_enable_twitter', 1),
        'seo_enable_json_ld' => get_option('seo_enable_json_ld', 1),
        'seo_title_separator' => get_option('seo_title_separator', '-'),
        'seo_title_format' => get_option('seo_title_format', '%post_title% | %site_name%'),
    );
    
    echo "<p>Individual settings found:</p>";
    echo "<ul>";
    foreach ($individual_settings as $key => $value) {
        echo "<li>$key: " . ($value ? 'Enabled' : 'Disabled') . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>✓ Settings found:</p>";
    echo "<ul>";
    foreach ($settings as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? 'Enabled' : 'Disabled';
        }
        echo "<li>$key: $value</li>";
    }
    echo "</ul>";
}

// Check if meta data exists for current post
echo "<h2>Post Meta Check</h2>";
global $post;

if ($post) {
    $meta_fields = array(
        '_seo_simple_title',
        '_seo_simple_description',
        '_seo_simple_keywords',
        '_seo_simple_noindex',
        '_seo_simple_nofollow',
        '_seo_simple_canonical',
        '_seo_simple_social_title',
        '_seo_simple_social_description',
        '_seo_simple_social_image',
    );
    
    echo "<p>Meta data for post #{$post->ID} ({$post->post_title}):</p>";
    echo "<ul>";
    
    $has_meta = false;
    foreach ($meta_fields as $field) {
        $value = get_post_meta($post->ID, $field, true);
        if (!empty($value) && is_string($value)) {
            echo "<li>$field: " . esc_html(substr($value, 0, 100)) . (strlen($value) > 100 ? '...' : '') . "</li>";
            $has_meta = true;
        }
    }
    
    if (!$has_meta) {
        echo "<li>No custom SEO meta data found for this post.</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No post object available.</p>";
}

// Test direct output of meta tags
echo "<h2>Direct Output Test</h2>";
echo "<p>Testing direct output of meta tags:</p>";

// Create a simple test instance
if (class_exists('Seo_Simple_Meta')) {
    $seo_meta = new Seo_Simple_Meta();
    
    if ($post) {
        $title = $seo_meta->get_title($post->ID);
        $description = $seo_meta->get_description($post->ID);
        
        echo "<div style='font-family: monospace; background: #f0f0f0; padding: 10px;'>";
        if (!empty($title)) {
            echo "<title>" . esc_html($title) . "</title><br>";
        }
        if (!empty($description)) {
            echo '<meta name="description" content="' . esc_attr($description) . '"><br>';
        }
        echo "</div>";
    }
} else {
    echo "<p>✗ Seo_Simple_Meta class not found.</p>";
}

// Check for conflicts with other SEO plugins
echo "<h2>Plugin Conflict Check</h2>";
$active_plugins = get_option('active_plugins', array());
$seo_plugins = array();

foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'seo') !== false || strpos($plugin, 'yoast') !== false || strpos($plugin, 'all-in-one') !== false) {
        $seo_plugins[] = $plugin;
    }
}

if (!empty($seo_plugins)) {
    echo "<p>⚠ Other SEO plugins detected that might conflict:</p>";
    echo "<ul>";
    foreach ($seo_plugins as $plugin) {
        echo "<li>$plugin</li>";
    }
    echo "</ul>";
    echo "<p>Consider deactivating other SEO plugins to test for conflicts.</p>";
} else {
    echo "<p>✓ No conflicting SEO plugins detected.</p>";
}

// Provide recommendations
echo "<h2>Recommendations</h2>";
echo "<ol>";
echo "<li>Clear all caches (plugin cache, WordPress cache, browser cache).</li>";
echo "<li>Check if your theme is properly calling wp_head() in header.php.</li>";
echo "<li>Temporarily switch to a default theme like Twenty Twenty-One to test.</li>";
echo "<li>Deactivate other SEO plugins to check for conflicts.</li>";
echo "<li>Check for any errors in your debug.log file.</li>";
echo "</ol>";

echo "<p><strong>If the issue persists after trying these steps, please contact support.</strong></p>";