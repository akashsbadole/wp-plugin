<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://example.com
 * @since             1.0.0
 * @package           Seo_Simple
 *
 * @wordpress-plugin
 * Plugin Name:       Simple SEO
 * Plugin URI:        https://example.com
 * Description:       Simple SEO provides essential SEO functionality for your WordPress site.
 * Version:           1.0.0
 * Author:            akash
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seo-simple
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEO_SIMPLE_VERSION', '1.0.0' );
define( 'SEO_SIMPLE_PLUGIN_FILE', __FILE__ );

/**
 * Store translation strings temporarily until textdomain is loaded
 */
class Seo_Simple_Translation_Holder {
    private static $translations = array();
    private static $loaded = false;
    
    public static function add($key, $text, $domain = 'seo-simple') {
        if (!self::$loaded) {
            self::$translations[$domain][$key] = $text;
        }
    }
    
    public static function get($key, $domain = 'seo-simple') {
        return isset(self::$translations[$domain][$key]) ? self::$translations[$domain][$key] : $key;
    }
    
    public static function set_loaded($loaded = true) {
        self::$loaded = $loaded;
    }
    
    public static function is_loaded() {
        return self::$loaded;
    }
}

/**
 * Custom translation function that works before textdomain is loaded
 */
function seo_simple_early_translate( $text, $domain = 'seo-simple' ) {
    if (Seo_Simple_Translation_Holder::is_loaded() && function_exists('__')) {
        return __($text, $domain);
    }
    // Store for later translation or return untranslated
    Seo_Simple_Translation_Holder::add(md5($text), $text, $domain);
    return $text;
}

/**
 * Load textdomain at the appropriate time - on init action
 * This prevents the _load_textdomain_just_in_time error
 */
function seo_simple_load_textdomain_properly() {
    // Log when textdomain is being loaded
    error_log('[SEO Simple] Loading textdomain on init action at: ' . current_action());
    
    $loaded = load_plugin_textdomain(
        'seo-simple',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
    
    // Mark translations as loaded
    Seo_Simple_Translation_Holder::set_loaded($loaded);
    
    error_log('[SEO Simple] Textdomain load result: ' . ($loaded ? 'SUCCESS' : 'FAILED'));
}
add_action( 'init', 'seo_simple_load_textdomain_properly', -5 );

// Add filter to catch just-in-time loading attempts
add_filter('override_load_textdomain', function($override, $domain, $mofile) {
    if ($domain === 'seo-simple') {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = 'unknown';
        $file = 'unknown';
        $line = 'unknown';
        
        // Find the first non-WordPress function in the backtrace
        foreach ($backtrace as $trace) {
            if (isset($trace['function']) &&
                !strpos($trace['function'], 'load_plugin_textdomain') &&
                !strpos($trace['function'], '_load_textdomain_just_in_time') &&
                !strpos($trace['function'], 'get_translations_for_domain')) {
                $caller = $trace['function'];
                $file = isset($trace['file']) ? basename($trace['file']) : 'unknown';
                $line = isset($trace['line']) ? $trace['line'] : 'unknown';
                break;
            }
        }
        
        error_log("[SEO Simple] Just-in-time loading triggered by: $caller in $file:$line");
        error_log("[SEO Simple] Current action: " . current_action());
        error_log("[SEO Simple] MO file: $mofile");
    }
    return $override;
}, 10, 3);

/**
 * Debug function to log when translation functions might be called too early
 */
function seo_simple_debug_translation_call() {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    $caller = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : 'unknown';
    $file = isset($backtrace[0]['file']) ? basename($backtrace[0]['file']) : 'unknown';
    $line = isset($backtrace[0]['line']) ? $backtrace[0]['line'] : 'unknown';
    
    error_log("[SEO Simple Debug] Possible early translation call: $caller in $file:$line");
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-seo-simple-activator.php
 */
function activate_seo_simple( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-activator.php';
	Seo_Simple_Activator::activate( $network_wide );
	
	// Register multisite hooks if in a multisite environment
	if ( is_multisite() ) {
		Seo_Simple_Activator::register_multisite_hooks();
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-seo-simple-deactivator.php
 */
function deactivate_seo_simple() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-deactivator.php';
	Seo_Simple_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_seo_simple' );
register_deactivation_hook( __FILE__, 'deactivate_seo_simple' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple.php';

/**
 * Include base schema class before enhanced schema class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-schema.php';

/**
 * Include cache and security classes for performance and security.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-cache.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-cache-manager.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-security.php';

/**
 * Include redirect manager class for advanced redirection management.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-redirect-manager.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-redirect-admin.php';

/**
 * Include local business SEO class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-local-business.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-local-business-admin.php';

/**
 * Include breadcrumbs class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-breadcrumbs.php';

/**
 * Include 404 monitor class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-404-monitor.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-404-monitor-admin.php';

/**
 * Include internal links class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-internal-links.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-internal-links-admin.php';

/**
 * Include role manager class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-roles.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-roles-admin.php';

/**
 * Include multisite class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-multisite.php';

/**
 * Include keyword suggestions class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-keyword-suggestions.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-keyword-suggestions-admin.php';

/**
 * Include SEO analyzer class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-analyzer.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-analyzer-admin.php';

/**
 * Include page builder integration class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-page-builders.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-page-builders-admin.php';

/**
 * Include enhanced schema generator class.
 * Note: class-seo-simple-schema-enhanced.php is currently missing - commented out to prevent fatal error
 * require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-schema-enhanced.php';
 */
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-schema-admin.php';

/**
 * Include WooCommerce SEO class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-woocommerce.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-woocommerce-admin.php';

/**
 * Include bulk editor class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-bulk-editor.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-bulk-editor-admin.php';

/**
 * Include Google integration class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-google-integration.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-google-admin.php';

/**
 * Include Instant Indexing class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-instant-indexing.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-instant-indexing-admin.php';

/**
 * Include Speed Optimization class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-speed-optimization.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-speed-optimization-admin.php';
/**
 * Include Auto Configuration class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-auto-config.php';
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-auto-config-admin.php';

/**
 * Include Elementor widgets.
 */
if ( defined( 'ELEMENTOR_VERSION' ) ) {
	require plugin_dir_path( __FILE__ ) . 'includes/class-seo-simple-elementor-widgets.php';
}

/**
 * Include Security admin class.
 */
require plugin_dir_path( __FILE__ ) . 'admin/class-seo-simple-security-admin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_seo_simple() {
	$plugin = new Seo_Simple();
	$plugin->run();
}

// Hook the plugin initialization to the 'init' action after textdomain is loaded
// Textdomain is loaded on init with priority -5, so run plugin after that
add_action( 'init', 'run_seo_simple', 5 );
