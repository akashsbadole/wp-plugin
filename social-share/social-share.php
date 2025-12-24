<?php
/**
 * Plugin Name: Social Share
 * Plugin URI: https://yourwebsite.com/social-share
 * Description: Lightweight social media sharing buttons for your posts and pages.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: social-share
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('ESS_VERSION', '1.0.0');
define('ESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ESS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main plugin class
require_once ESS_PLUGIN_DIR . 'includes/class-easy-social-share.php';

/**
 * Initialize the plugin
 */
function easy_social_share_light_init() {
    return new Easy_Social_Share_Light();
}

// Initialize on init hook to reduce memory usage
add_action('plugins_loaded', 'easy_social_share_light_init');

/**
 * Register activation hook
 */
function easy_social_share_light_activate() {
    // Add default settings if not exist
    if (!get_option('ess_settings')) {
        $default_settings = array(
            'auto_insert' => true,
            'position' => 'after',
            'networks' => array(
                'facebook' => true,
                'twitter' => true,
                'linkedin' => true,
                'pinterest' => false,
                'whatsapp' => true
            ),
            'button_style' => 'icon-text',
            'button_size' => 'medium',
            'button_shape' => 'rounded'
        );
        
        add_option('ess_settings', $default_settings);
    }
}
register_activation_hook(__FILE__, 'easy_social_share_light_activate');

/**
 * Register deactivation hook
 */
function easy_social_share_light_deactivate() {
    // Do nothing for now
}
register_deactivation_hook(__FILE__, 'easy_social_share_light_deactivate');

/**
 * Register uninstall hook
 */
function easy_social_share_light_uninstall() {
    // Remove plugin options
    delete_option('ess_settings');
}
register_uninstall_hook(__FILE__, 'easy_social_share_light_uninstall');