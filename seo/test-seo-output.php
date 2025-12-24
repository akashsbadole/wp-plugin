<?php
/**
 * SEO Output Test Script
 * 
 * This script tests the SEO tag output to ensure they're being generated correctly.
 * Place this file in your WordPress root directory and access it directly.
 */

// Prevent direct access if WordPress is not loaded
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    $wp_load_path = __DIR__ . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    } else {
        die('WordPress not found. Please place this file in the WordPress root directory.');
    }
}

// Check if the plugin is active
if (!is_plugin_active('seo-simple/seo-simple.php')) {
    echo '<h1>SEO Simple Plugin Not Active</h1>';
    echo '<p>Please activate the SEO Simple plugin first.</p>';
    exit;
}

// Set up a test environment
global $wp_query, $post;

// Get the first published post as a test
$test_posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 1
));

if (empty($test_posts)) {
    echo '<h1>No Published Posts Found</h1>';
    echo '<p>Please create at least one published post to test SEO output.</p>';
    exit;
}

// Set up the global post object
$post = $test_posts[0];
$wp_query->post = $post;
$wp_query->is_single = true;
$wp_query->is_page = false;

// Start output buffering to capture SEO tags
ob_start();

// Simulate wp_head action to trigger SEO output
do_action('wp_head');

$seo_output = ob_get_clean();

// Analyze the output
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>SEO Output Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; }';
echo '.success { color: green; }';
echo '.error { color: red; }';
echo '.warning { color: orange; }';
echo 'code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }';
echo 'pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }';
echo '.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }';
echo '</style>';
echo '</head>';
echo '<body>';

echo '<h1>SEO Simple Plugin Output Test</h1>';
echo '<p>Testing SEO tag generation for post: <strong>' . esc_html($post->post_title) . '</strong></p>';

// Check for title tag
if (preg_match('/<title>(.*?)<\/title>/i', $seo_output, $matches)) {
    $title = $matches[1];
    echo '<div class="section">';
    echo '<h2 class="success">✓ Title Tag Found</h2>';
    echo '<p><code><title>' . esc_html($title) . '</title></code></p>';
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Title Tag Missing</h2>';
    echo '</div>';
}

// Check for description meta tag
if (preg_match('/<meta name="description" content="(.*?)"\s*\/?>/i', $seo_output, $matches)) {
    $description = $matches[1];
    echo '<div class="section">';
    echo '<h2 class="success">✓ Description Meta Tag Found</h2>';
    echo '<p><code><meta name="description" content="' . esc_attr($description) . '"></code></p>';
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Description Meta Tag Missing</h2>';
    echo '</div>';
}

// Check for robots meta tag
if (preg_match('/<meta name="robots" content="(.*?)"\s*\/?>/i', $seo_output, $matches)) {
    $robots = $matches[1];
    echo '<div class="section">';
    echo '<h2 class="success">✓ Robots Meta Tag Found</h2>';
    echo '<p><code><meta name="robots" content="' . esc_attr($robots) . '"></code></p>';
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Robots Meta Tag Missing</h2>';
    echo '</div>';
}

// Check for canonical URL
if (preg_match('/<link rel="canonical" href="(.*?)"\s*\/?>/i', $seo_output, $matches)) {
    $canonical = $matches[1];
    echo '<div class="section">';
    echo '<h2 class="success">✓ Canonical URL Found</h2>';
    echo '<p><code><link rel="canonical" href="' . esc_url($canonical) . '"></code></p>';
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Canonical URL Missing</h2>';
    echo '</div>';
}

// Check for Open Graph tags
$og_tags = array();
if (preg_match_all('/<meta property="og:(.*?)" content="(.*?)"\s*\/?>/i', $seo_output, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $og_tags[$match[1]] = $match[2];
    }
}

if (!empty($og_tags)) {
    echo '<div class="section">';
    echo '<h2 class="success">✓ Open Graph Tags Found</h2>';
    foreach ($og_tags as $property => $content) {
        echo '<p><code><meta property="og:' . esc_html($property) . '" content="' . esc_attr($content) . '"></code></p>';
    }
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Open Graph Tags Missing</h2>';
    echo '</div>';
}

// Check for Twitter Card tags
$twitter_tags = array();
if (preg_match_all('/<meta name="twitter:(.*?)" content="(.*?)"\s*\/?>/i', $seo_output, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $twitter_tags[$match[1]] = $match[2];
    }
}

if (!empty($twitter_tags)) {
    echo '<div class="section">';
    echo '<h2 class="success">✓ Twitter Card Tags Found</h2>';
    foreach ($twitter_tags as $name => $content) {
        echo '<p><code><meta name="twitter:' . esc_html($name) . '" content="' . esc_attr($content) . '"></code></p>';
    }
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ Twitter Card Tags Missing</h2>';
    echo '</div>';
}

// Check for JSON-LD schema
if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/is', $seo_output, $matches)) {
    $schema = $matches[1];
    echo '<div class="section">';
    echo '<h2 class="success">✓ JSON-LD Schema Found</h2>';
    echo '<pre>' . esc_html($schema) . '</pre>';
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<h2 class="error">✗ JSON-LD Schema Missing</h2>';
    echo '</div>';
}

// Show full output for debugging
echo '<div class="section">';
echo '<h2>Full SEO Output</h2>';
echo '<pre>' . esc_html($seo_output) . '</pre>';
echo '</div>';

// Provide troubleshooting steps
echo '<div class="section">';
echo '<h2>Troubleshooting Steps</h2>';
echo '<ol>';
echo '<li>Clear all caches (plugin cache, WordPress cache, browser cache).</li>';
echo '<li>Check if your theme is properly calling wp_head() in header.php.</li>';
echo '<li>Temporarily switch to a default theme like Twenty Twenty-One to test.</li>';
echo '<li>Deactivate other SEO plugins to check for conflicts.</li>';
echo '<li>Check for any errors in your debug.log file.</li>';
echo '<li>Run the <a href="seo-diagnostic.php">SEO Diagnostic Tool</a> for more information.</li>';
echo '</ol>';
echo '</div>';

echo '</body>';
echo '</html>';