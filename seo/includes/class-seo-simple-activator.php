<?php

/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Activator {

	/**
	 * Set up default plugin options on activation.
	 *
	 * Creates default options for the SEO plugin and sets up the database table for code snippets.
	 *
	 * @since    1.0.0
	 */
	public static function activate( $network_wide = false ) {
		// Check if this is a network activation
		if ( is_multisite() && $network_wide ) {
			// Activate for all sites in the network
			self::activate_network_wide();
		} else {
			// Activate for the current site only
			self::activate_single_site();
		}
	}
	
	/**
	 * Activate for a single site.
	 *
	 * @since    1.0.0
	 */
	private static function activate_single_site() {
		// Set default settings
		$default_settings = array(
			// General Settings
			'seo_enable_posts' => 1,
			'seo_enable_pages' => 1,
			'seo_enable_categories' => 1,
			'seo_enable_tags' => 1,

			// Titles & Meta
			'seo_title_separator' => '-',
			'seo_title_format' => '%post_title% | %site_name%',
			'seo_description_template' => '',
			'seo_home_title' => '',
			'seo_home_description' => '',
			'seo_title_length' => 60,
			'seo_description_length' => 160,

			// Social Settings
			'seo_enable_og' => 1,
			'seo_enable_twitter' => 1,
			'seo_enable_json_ld' => 1,
			'seo_og_image' => '',

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
		);
		
		// Add settings to database if they don't exist
		if ( get_option( 'seo_simple_settings' ) === false ) {
			add_option( 'seo_simple_settings', $default_settings );
		}
		
		// Set default options for backward compatibility
		$default_options = array(
			'seo_title_separator' => '-',
			'seo_title_format' => '%post_title% | %site_name%',
			'seo_description_length' => 160,
			'seo_title_length' => 60,
			'seo_show_metabox' => '1',
			'seo_enable_og' => '1',
			'seo_enable_twitter' => '1',
			'seo_enable_json_ld' => '1',
		);
		
		// Add options to database if they don't exist
		foreach ( $default_options as $option_name => $option_value ) {
			if ( get_option( $option_name ) === false ) {
				add_option( $option_name, $option_value );
			}
		}
		
		// Create the code snippets table
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-code-snippets.php';
		$code_snippets = new Seo_Simple_Code_Snippets();
		$code_snippets->create_table();
		
		// Create the redirect manager tables
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-redirect-manager.php';
		$redirect_manager = new Seo_Simple_Redirect_Manager();
		$redirect_manager->create_tables();
		
		// Create the 404 monitor table
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-404-monitor.php';
		$monitor_404 = Seo_Simple_404_Monitor::get_instance();
		$monitor_404->create_tables();
		
		// Schedule sitemap generation
		if ( ! wp_next_scheduled( 'seo_simple_generate_sitemap' ) ) {
			wp_schedule_event( time(), 'daily', 'seo_simple_generate_sitemap' );
		}
		
		// Set up multisite-specific options if in a multisite environment
		if ( is_multisite() ) {
			self::setup_multisite_options();
		}
	}
	
	/**
	 * Activate for all sites in a multisite network.
	 *
	 * @since    1.0.0
	 */
	private static function activate_network_wide() {
		// Get all sites in the network
		$sites = get_sites();
		
		foreach ( $sites as $site ) {
			// Switch to the site
			switch_to_blog( $site->blog_id );
			
			// Activate for this site
			self::activate_single_site();
			
			// Restore the current blog
			restore_current_blog();
		}
		
		// Set network-wide settings
		self::setup_network_settings();
	}
	
	/**
	 * Set up multisite-specific options.
	 *
	 * @since    1.0.0
	 */
	private static function setup_multisite_options() {
		// Add site-specific options
		$site_options = array(
			'seo_simple_site_enabled' => true,
			'seo_simple_inherit_network_settings' => true,
		);
		
		foreach ( $site_options as $option_name => $option_value ) {
			if ( get_option( $option_name ) === false ) {
				add_option( $option_name, $option_value );
			}
		}
	}
	
	/**
	 * Set up network-wide settings.
	 *
	 * @since    1.0.0
	 */
	private static function setup_network_settings() {
		// Set default network settings
		$network_settings = array(
			'seo_network_enable_posts' => 1,
			'seo_network_enable_pages' => 1,
			'seo_network_enable_categories' => 1,
			'seo_network_enable_tags' => 1,
			'seo_network_title_separator' => '-',
			'seo_network_title_format' => '%post_title% | %site_name%',
			'seo_network_description_length' => 160,
			'seo_network_title_length' => 60,
			'seo_network_enable_og' => 1,
			'seo_network_enable_twitter' => 1,
			'seo_network_enable_json_ld' => 1,
			'seo_network_enable_sitemap' => 1,
			'seo_network_show_metabox' => 1,
			'seo_network_enable_analysis' => 1,
			'seo_network_enable_readability' => 1,
		);
		
		// Add network settings if they don't exist
		if ( get_site_option( 'seo_simple_network_settings' ) === false ) {
			add_site_option( 'seo_simple_network_settings', $network_settings );
		}
	}
	
	/**
	 * Handle new site creation in a multisite network.
	 *
	 * @since    1.0.0
	 * @param    WP_Site   $site      The new site object.
	 * @param    array     $args      The arguments passed to wp_initialize_site().
	 */
	public static function new_site_setup( $site, $args ) {
		// Switch to the new site
		switch_to_blog( $site->blog_id );
		
		// Activate for the new site
		self::activate_single_site();
		
		// Restore the current blog
		restore_current_blog();
	}
	
	/**
	 * Handle site deletion in a multisite network.
	 *
	 * @since    1.0.0
	 * @param    int       $site_id   The site ID.
	 * @param    bool      $drop      Whether to drop the site's tables.
	 */
	public static function delete_site_cleanup( $site_id, $drop ) {
		// This is handled by WordPress automatically when $drop is true
		// Tables with the site's prefix will be dropped
		// If we need to clean up additional data, we can do it here
		
		// Example: Clean up any site options related to our plugin
		delete_site_option( 'seo_simple_site_' . $site_id . '_settings' );
	}
	
	/**
		* Register multisite activation hooks.
		*
		* @since    1.0.0
		*/
	public static function register_multisite_hooks() {
		// Hook into new site creation
		add_action( 'wp_initialize_site', array( __CLASS__, 'new_site_setup' ), 10, 2 );
		
		// Hook into site deletion
		add_action( 'wpmu_delete_blog', array( __CLASS__, 'delete_site_cleanup' ), 10, 2 );
		
		// Hook into site activation in a multisite network
		add_action( 'activate_' . plugin_basename( SEO_SIMPLE_PLUGIN_FILE ), array( __CLASS__, 'handle_network_activation' ), 10, 2 );
	}
	
	/**
		* Handle network activation.
		*
		* @since    1.0.0
		* @param    bool    $network_wide    Whether to activate network-wide.
		*/
	public static function handle_network_activation( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			// This is a network activation, our activate method already handles this
			// but we can add additional network-specific logic here if needed
		}
	}

}
