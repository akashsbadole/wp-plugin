<?php
/**
 * Test script to verify menu items are properly registered
 * This should be run in the WordPress admin context
 */

// Check if we're in WordPress admin
if (!defined('ABSPATH')) {
    // Load WordPress if not already loaded
    $wp_load_path = __DIR__ . '/../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        echo "Error: Could not find WordPress installation. This script must be run within a WordPress installation.\n";
        exit;
    }
}

// Check if user has admin capabilities
if (!current_user_can('manage_options')) {
    echo "Error: You need administrator privileges to run this test.\n";
    exit;
}

// Get the global menu array
global $menu, $submenu;

// Find the SEO Simple menu
$seo_menu_found = false;
$seo_menu_key = null;

if (is_array($menu)) {
    foreach ($menu as $key => $item) {
        if (is_array($item) && isset($item[2]) && $item[2] === 'seo-simple') {
            $seo_menu_found = true;
            $seo_menu_key = $key;
            break;
        }
    }
}

echo "=== SEO Simple Menu Test ===\n\n";

if ($seo_menu_found) {
    echo "✓ Main SEO Simple menu found\n";
    
    // Check submenu items
    if (isset($submenu['seo-simple']) && is_array($submenu['seo-simple'])) {
        echo "✓ Submenu items found:\n";
        
        $expected_items = array(
            'Dashboard',
            'Settings',
            'Redirects',
            'Internal Links',
            'Role Manager',
            'Page Builder SEO',
            'Security',
            'Bulk Editor',
            'Auto Config',
            'Code Snippets',
            'Google Analytics',
            'Search Console',
            'Instant Indexing',
            'Schema',
            'Speed Optimization',
            'WooCommerce SEO',
            '404 Monitor'
        );
        
        $found_items = array();
        
        foreach ($submenu['seo-simple'] as $item) {
            if (is_array($item) && isset($item[3])) {
                $found_items[] = $item[3];
                echo "  - " . $item[3] . "\n";
            }
        }
        
        // Check for missing items
        $missing_items = array_diff($expected_items, $found_items);
        if (!empty($missing_items)) {
            echo "\n⚠ Missing menu items:\n";
            foreach ($missing_items as $missing) {
                echo "  - " . $missing . "\n";
            }
        }
        
        // Check for extra items
        $extra_items = array_diff($found_items, $expected_items);
        if (!empty($extra_items)) {
            echo "\nℹ Extra menu items (not in expected list):\n";
            foreach ($extra_items as $extra) {
                echo "  - " . $extra . "\n";
            }
        }
        
        if (empty($missing_items)) {
            echo "\n✓ All expected menu items are present!\n";
        } else {
            echo "\n✗ Some menu items are missing.\n";
        }
    } else {
        echo "✗ No submenu items found for SEO Simple\n";
    }
} else {
    echo "✗ SEO Simple menu not found\n";
}

echo "\n=== Test Complete ===\n";