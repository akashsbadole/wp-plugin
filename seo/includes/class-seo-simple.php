<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Seo_Simple_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin components.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $i18n;
	protected $admin;
	protected $public;
	protected $frontend;
	protected $meta;
	protected $schema;
	protected $cache_manager;
	protected $redirect_manager;
	protected $redirects;
	protected $robots;
	protected $sitemap;
	protected $shortcodes;
	protected $compatibility;
	protected $cache;
	protected $direct_output;
	protected $security;
	protected $security_admin;
	protected $instant_indexing;
	protected $instant_indexing_admin;
	protected $google_integration;
	protected $google_admin;
	protected $bulk_editor;
	protected $bulk_editor_admin;
	protected $schema_enhanced;
	protected $schema_enhanced_admin;
	protected $speed_optimization;
	protected $speed_optimization_admin;
	protected $page_builders;
	protected $page_builders_admin;
	protected $analyzer;
	protected $analyzer_admin;
	protected $keyword_suggestions;
	protected $multisite;
	protected $roles;
	protected $roles_admin;
	protected $internal_links;
	protected $internal_links_admin;
	protected $monitor_404;
	protected $monitor_404_admin;
	protected $breadcrumbs;
	protected $local_business;
	protected $local_business_admin;
	protected $woocommerce;
	protected $woocommerce_admin;
	protected $code_snippets;
	protected $code_snippets_admin;
	protected $auto_config;
	protected $auto_config_admin;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SEO_SIMPLE_VERSION' ) ) {
			$this->version = SEO_SIMPLE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'seo-simple';

		$this->load_dependencies();
		
		// Initialize all components before defining hooks to avoid undefined property errors
		$this->initialize_components();
		
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		// Initialize cache manager
		$this->initialize_cache_manager();

	}
	
	/**
	 * Initialize all plugin components.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function initialize_components() {
		// Initialize local business SEO
		$this->local_business = Seo_Simple_Local_Business::get_instance();
		
		// Initialize local business admin
		$this->local_business_admin = new Seo_Simple_Local_Business_Admin();
		
		// Initialize breadcrumbs
		$this->breadcrumbs = Seo_Simple_Breadcrumbs::get_instance();
		
		// Initialize 404 monitor
		$this->monitor_404 = Seo_Simple_404_Monitor::get_instance();
		
		// Initialize 404 monitor admin
		$this->monitor_404_admin = new Seo_Simple_404_Monitor_Admin();
		
		// Initialize internal links
		$this->internal_links = new Seo_Simple_Internal_Links();
		
		// Initialize internal links admin
		$this->internal_links_admin = new Seo_Simple_Internal_Links_Admin();
		
		// Initialize role manager
		$this->roles = new Seo_Simple_Roles();
		
		// Initialize role manager admin
		$this->roles_admin = new Seo_Simple_Roles_Admin( $this->get_plugin_name(), $this->get_version() );
		
		// Initialize multisite functionality
		$this->multisite = Seo_Simple_Multisite::get_instance();
		
		// Initialize keyword suggestions
		$this->keyword_suggestions = Seo_Simple_Keyword_Suggestions::get_instance();
		
		// Initialize SEO analyzer
		$this->analyzer = new Seo_Simple_Analyzer();
		
		// Initialize SEO analyzer admin
		$this->analyzer_admin = new Seo_Simple_Analyzer_Admin( $this->get_plugin_name(), $this->get_version() );
		
		// Initialize page builder integration
		$this->page_builders = Seo_Simple_Page_Builders::get_instance();
		
		// Initialize page builder admin
		$this->page_builders_admin = new Seo_Simple_Page_Builders_Admin( $this->get_plugin_name(), $this->get_version() );
		
		// Initialize SEO meta for schema
		$seo_meta = new Seo_Simple_Meta();
		
		// Initialize enhanced schema generator
		// Note: Seo_Simple_Schema_Enhanced class is missing - commented out to prevent fatal error
		// $this->schema_enhanced = new Seo_Simple_Schema_Enhanced( $seo_meta );
		$this->schema_enhanced = null;
		
		// Initialize enhanced schema admin
		// Note: Seo_Simple_Schema_Enhanced class is missing - commented out to prevent fatal error
		// $this->schema_enhanced_admin = new Seo_Simple_Schema_Admin( $seo_meta, $this->schema_enhanced );
		$this->schema_enhanced_admin = null;
		
		// Initialize WooCommerce SEO if WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$this->woocommerce = new Seo_Simple_WooCommerce( $seo_meta, null );
			
			// Initialize WooCommerce SEO admin
			$this->woocommerce_admin = new Seo_Simple_WooCommerce_Admin( $seo_meta );
		}
		
		// Initialize bulk editor
		$this->bulk_editor = new Seo_Simple_Bulk_Editor( $seo_meta );
		
		// Initialize bulk editor admin
		$this->bulk_editor_admin = new Seo_Simple_Bulk_Editor_Admin( $seo_meta );
		
		// Initialize Google integration
		$this->google_integration = new Seo_Simple_Google_Integration( $seo_meta );
		
		// Initialize Google admin
		$this->google_admin = new Seo_Simple_Google_Admin( $seo_meta, $this->google_integration );
		
		// Initialize Instant Indexing
		$this->instant_indexing = new Seo_Simple_Instant_Indexing( $seo_meta );
		
		// Initialize Instant Indexing admin
		$this->instant_indexing_admin = new Seo_Simple_Instant_Indexing_Admin( $seo_meta, $this->instant_indexing );
		
		// Initialize Speed Optimization
		$this->speed_optimization = new Seo_Simple_Speed_Optimization();
		
		// Initialize Speed Optimization admin
		$this->speed_optimization_admin = new Seo_Simple_Speed_Optimization_Admin( $this->speed_optimization );
		
		// Initialize Security
		$this->security = new Seo_Simple_Security();
		
		// Initialize Security admin
		$this->security_admin = new Seo_Simple_Security_Admin( $this->security );
		
		// Initialize Auto Configuration
		$this->auto_config = new Seo_Simple_Auto_Config();
		
		// Initialize Auto Configuration admin
		$this->auto_config_admin = new Seo_Simple_Auto_Config_Admin( $this->auto_config );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Seo_Simple_Loader. Orchestrates the hooks of the plugin.
	 * - Seo_Simple_i18n. Defines internationalization functionality.
	 * - Seo_Simple_Admin. Defines all hooks for the admin area.
	 * - Seo_Simple_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-loader.php';

		/**
		 * The class responsible for handling SEO meta data operations.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-meta.php';
		
		/**
		 * The class responsible for frontend SEO functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-frontend.php';
		
		/**
		 * The class responsible for content analysis.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-analysis.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-admin.php';

		/**
		 * The class responsible for handling the SEO meta box.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-meta-box.php';

		/**
		 * The class responsible for handling the plugin settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-seo-simple-public.php';

		/**
		 * The class responsible for handling code snippets functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-code-snippets.php';

		/**
		 * The class responsible for handling the admin interface for code snippets.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-code-snippets-admin.php';

		/**
		 * The class responsible for XML sitemap generation.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-sitemap.php';

		/**
		 * The class responsible for robots.txt management.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-robots.php';

		/**
		 * The class responsible for redirect management.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-redirects.php';
		
		/**
		 * The class responsible for smart redirect management.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-redirect-manager.php';
		
		/**
		 * The class responsible for redirect admin AJAX handlers.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-redirect-admin.php';

		/**
		 * The class responsible for local business SEO functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-local-business.php';

		/**
		 * The class responsible for local business admin AJAX handlers.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-local-business-admin.php';

		/**
		 * The class responsible for breadcrumbs functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-breadcrumbs.php';

		/**
		 * The class responsible for 404 monitoring.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-404-monitor.php';

		/**
		 * The class responsible for 404 monitor admin interface.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-seo-simple-404-monitor-admin.php';

		/**
		 * The class responsible for compatibility features.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-compatibility.php';

		/**
		 * The class responsible for schema generation.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-schema.php';

		/**
		 * The class responsible for shortcodes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-shortcodes.php';

		/**
		 * The class responsible for REST API functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-seo-simple-rest-api.php';

		/**
		 * The class responsible for meta-specific REST API endpoints.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/endpoints/meta.php';

		/**
		 * The class responsible for analysis-specific REST API endpoints.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/endpoints/analysis.php';

		/**
		 * The class responsible for cache management.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-cache-manager.php';

		/**
		 * The class responsible for direct SEO output as a fallback.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-direct-output.php';

		$this->loader = new Seo_Simple_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Textdomain is loaded directly in seo-simple.php on plugins_loaded with priority 0
	 * before any translation functions are called.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		// Textdomain is now loaded directly in seo-simple.php on plugins_loaded with priority 0
		// No need to load it here anymore
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Seo_Simple_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_meta_box' );
		
		// Register admin menu and settings
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		
		// Handle AJAX requests
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_ajax_requests' );
		
		// Handle settings actions
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_settings_actions' );

		// Code snippets functionality is now integrated into settings
		// No need to initialize separate admin menu
		
		// Initialize SEO meta
		$seo_meta = new Seo_Simple_Meta();
		
		// Initialize enhanced schema functionality
		$schema = $this->schema_enhanced; // May be null if class is missing
		
		// Initialize compatibility features
		$compatibility = new Seo_Simple_Compatibility( $seo_meta );
		
		// Initialize meta box with compatibility and enhanced schema support
		$meta_box = new Seo_Simple_Meta_Box( $seo_meta, $compatibility, $schema );
		
		// Register meta box
		$this->loader->add_action( 'add_meta_boxes', $meta_box, 'register_meta_box' );
		
		// Register REST API fields for Gutenberg
		$this->loader->add_action( 'rest_api_init', $meta_box, 'register_rest_fields' );
		
		// Initialize multisite functionality
		$this->loader->add_action( 'init', $this->multisite, 'init' );
		
		// Register enhanced schema admin hooks
		if ( $this->schema_enhanced_admin ) {
			$this->loader->add_action( 'admin_init', $this->schema_enhanced_admin, 'register_hooks' );
		}
		
		// Register WooCommerce SEO hooks if WooCommerce is active
		if ( isset( $this->woocommerce ) ) {
			$this->loader->add_action( 'init', $this->woocommerce, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->woocommerce_admin, 'register_hooks' );
		}
		
		// Register bulk editor hooks
		if ( isset( $this->bulk_editor ) ) {
			$this->loader->add_action( 'init', $this->bulk_editor, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->bulk_editor_admin, 'register_hooks' );
		}
		
		// Register Google integration hooks
		if ( isset( $this->google_integration ) ) {
			$this->loader->add_action( 'init', $this->google_integration, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->google_admin, 'register_hooks' );
		}
		
		// Register Instant Indexing hooks
		if ( isset( $this->instant_indexing ) ) {
			$this->loader->add_action( 'init', $this->instant_indexing, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->instant_indexing_admin, 'register_hooks' );
		}
		
		// Register Security hooks
		if ( isset( $this->security ) ) {
			$this->loader->add_action( 'init', $this->security, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->security_admin, 'register_hooks' );
		}
		// Register Speed Optimization hooks
		if ( isset( $this->speed_optimization ) ) {
			$this->loader->add_action( 'init', $this->speed_optimization, 'register_hooks' );
			$this->loader->add_action( 'admin_init', $this->speed_optimization_admin, 'register_hooks' );
		}
		
		// Register Auto Configuration hooks
		if ( isset( $this->auto_config_admin ) ) {
			$this->loader->add_action( 'admin_init', $this->auto_config_admin, 'register_hooks' );
		}
		
		// Register 404 Monitor hooks
		if ( isset( $this->monitor_404 ) ) {
			$this->loader->add_action( 'init', $this->monitor_404, 'create_tables' );
		}
		
		// Register 404 Monitor Admin hooks
		if ( isset( $this->monitor_404_admin ) ) {
			$this->loader->add_action( 'admin_init', $this->monitor_404_admin, 'register_settings' );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Seo_Simple_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Initialize SEO meta for frontend
		$seo_meta = new Seo_Simple_Meta();
		
		// Initialize enhanced schema functionality
		$schema = $this->schema_enhanced; // May be null if class is missing
		
		// Initialize frontend SEO functionality with enhanced schema support
		$seo_frontend = new Seo_Simple_Frontend( $seo_meta, $schema );
		
		// Initialize direct output as a fallback
		$direct_output = new Seo_Simple_Direct_Output();
		
		// Register frontend hooks through the loader with higher priorities to ensure they run early
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_meta_tags', 1 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_canonical_url', 2 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_robots_meta', 3 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_network_codes', 4 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_open_graph_tags', 5 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_twitter_card_tags', 6 );
		$this->loader->add_action( 'wp_head', $seo_frontend, 'output_schema', 7 );
		
		// Add WooCommerce-specific hooks if WooCommerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$this->loader->add_action( 'wp_head', $seo_frontend, 'output_woocommerce_schema', 25 );
		}
		
		// Ensure SEO settings are properly loaded and applied
		$this->loader->add_action( 'init', $this, 'ensure_seo_settings_loaded', 1 );
		
		// Initialize shortcodes with enhanced schema support
		$shortcodes = new Seo_Simple_Shortcodes( $schema );

		// Initialize code snippets for frontend
		$code_snippets = new Seo_Simple_Code_Snippets();
		
		// Create database table on plugin load (as fallback)
		$this->loader->add_action( 'init', $code_snippets, 'create_table' );
		
		// Inject header, body, and footer scripts
		$this->loader->add_action( 'wp_head', $code_snippets, 'inject_header_scripts' );
		$this->loader->add_action( 'wp_body_open', $code_snippets, 'inject_body_scripts' );
		$this->loader->add_action( 'wp_footer', $code_snippets, 'inject_footer_scripts' );

		// Initialize sitemap functionality
		$sitemap = new Seo_Simple_Sitemap();

		// Initialize robots.txt functionality
		$robots = new Seo_Simple_Robots();

		// Initialize redirect functionality
		$redirects = new Seo_Simple_Redirects();
		
		// Initialize smart redirect manager (without admin menu registration)
		// The admin menu is handled by Seo_Simple_Admin class
		
		$redirect_manager = new Seo_Simple_Redirect_Manager( $redirects );
		// Prevent duplicate menu registration by removing the admin_menu hook
		remove_action( 'admin_menu', array( $redirect_manager, 'add_admin_menu' ) );
		
		// Initialize redirect admin AJAX handlers
		$redirect_admin = new Seo_Simple_Redirect_Admin();
		
		// Initialize REST API
		$rest_api = new Seo_Simple_REST_API( $seo_meta );
		
		// Initialize REST API controllers
		$meta_controller = new Seo_Simple_REST_Meta_Controller( $seo_meta );
		$analysis_controller = new Seo_Simple_REST_Analysis_Controller( $seo_meta );
		
		// Register REST API routes
		$this->loader->add_action( 'rest_api_init', $meta_controller, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $analysis_controller, 'register_routes' );
		
		// Register Security hooks for frontend
		if ( isset( $this->security ) ) {
			$this->loader->add_action( 'init', $this->security, 'init_security_features' );
			$this->loader->add_action( 'init', $this->security, 'init_waf' );
			$this->loader->add_action( 'send_headers', $this->security, 'add_security_headers' );
		}
		
		// Register Speed Optimization hooks for frontend
		if ( isset( $this->speed_optimization ) ) {
			$this->loader->add_action( 'init', $this->speed_optimization, 'init_speed_optimization' );
			$this->loader->add_filter( 'the_content', $this->speed_optimization, 'add_lazy_loading' );
			$this->loader->add_action( 'wp_enqueue_scripts', $this->speed_optimization, 'enqueue_minified_assets', 999 );
			$this->loader->add_action( 'init', $this->speed_optimization, 'enable_gzip_compression' );
			$this->loader->add_action( 'send_headers', $this->speed_optimization, 'add_browser_caching_headers' );
			$this->loader->add_action( 'wp', $this->speed_optimization, 'schedule_database_cleanup' );
			$this->loader->add_action( 'wp_head', $this->speed_optimization, 'add_preconnect_headers' );
			$this->loader->add_action( 'wp_head', $this->speed_optimization, 'add_critical_css' );
			$this->loader->add_action( 'wp_head', $this->speed_optimization, 'add_dns_prefetch' );
		}
		
		// Register developer-friendly hooks and filters
		$this->register_developer_hooks( $seo_meta );

	}
	
	/**
	 * Register developer-friendly hooks and filters.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private function register_developer_hooks( $seo_meta ) {
		// Hook for saving meta data
		add_filter( 'seo_plugin_save_meta', array( $this, 'filter_save_meta' ), 10, 3 );
		
		// Hook for calculating scores
		add_filter( 'seo_plugin_calculate_score', array( $this, 'filter_calculate_score' ), 10, 3 );
		
		// Hook for adding schema data
		add_filter( 'seo_plugin_add_schema', array( $this, 'filter_add_schema' ), 10, 3 );
		
		// Hook for filtering meta data before output
		add_filter( 'seo_simple_filter_meta', array( $this, 'filter_meta_data' ), 10, 2 );
		
		// Hook for filtering SEO title
		add_filter( 'seo_simple_filter_title', array( $this, 'filter_seo_title' ), 10, 2 );
		
		// Hook for filtering SEO description
		add_filter( 'seo_simple_filter_description', array( $this, 'filter_seo_description' ), 10, 2 );
		
		// Hook for filtering canonical URL
		add_filter( 'seo_simple_filter_canonical', array( $this, 'filter_canonical_url' ), 10, 2 );
		
		// Hook for filtering robots meta
		add_filter( 'seo_simple_filter_robots', array( $this, 'filter_robots_meta' ), 10, 2 );
	}
	
	/**
	 * Filter for saving meta data.
	 *
	 * @since    1.0.0
	 * @param    bool      $result    Whether to continue saving.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to save.
	 * @return   bool                 Whether to continue saving.
	 */
	public function filter_save_meta( $result, $post_id, $data ) {
		// Developers can modify the data before saving
		return $result;
	}
	
	/**
	 * Filter for calculating scores.
	 *
	 * @since    1.0.0
	 * @param    array     $score     The current score data.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to calculate score from.
	 * @return   array                The modified score data.
	 */
	public function filter_calculate_score( $score, $post_id, $data ) {
		// Developers can modify the score calculation
		return $score;
	}
	
	/**
	 * Filter for adding schema data.
	 *
	 * @since    1.0.0
	 * @param    array     $schema    The current schema data.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to build schema from.
	 * @return   array                The modified schema data.
	 */
	public function filter_add_schema( $schema, $post_id, $data ) {
		// Developers can add or modify schema data
		return $schema;
	}
	
	/**
	 * Filter for meta data before output.
	 *
	 * @since    1.0.0
	 * @param    array     $meta      The meta data.
	 * @param    int       $post_id   The post ID.
	 * @return   array                The filtered meta data.
	 */
	public function filter_meta_data( $meta, $post_id ) {
		// Developers can filter meta data before output
		return $meta;
	}
	
	/**
	 * Filter for SEO title.
	 *
	 * @since    1.0.0
	 * @param    string    $title     The SEO title.
	 * @param    int       $post_id   The post ID.
	 * @return   string               The filtered SEO title.
	 */
	public function filter_seo_title( $title, $post_id ) {
		// Developers can filter the SEO title
		return $title;
	}
	
	/**
	 * Filter for SEO description.
	 *
	 * @since    1.0.0
	 * @param    string    $description  The SEO description.
	 * @param    int       $post_id      The post ID.
	 * @return   string                   The filtered SEO description.
	 */
	public function filter_seo_description( $description, $post_id ) {
		// Developers can filter the SEO description
		return $description;
	}
	
	/**
	 * Filter for canonical URL.
	 *
	 * @since    1.0.0
	 * @param    string    $canonical  The canonical URL.
	 * @param    int       $post_id    The post ID.
	 * @return   string                The filtered canonical URL.
	 */
	public function filter_canonical_url( $canonical, $post_id ) {
		// Developers can filter the canonical URL
		return $canonical;
	}
	
	/**
	 * Filter for robots meta.
	 *
	 * @since    1.0.0
	 * @param    string    $robots     The robots meta.
	 * @param    int       $post_id    The post ID.
	 * @return   string                The filtered robots meta.
	 */
	public function filter_robots_meta( $robots, $post_id ) {
		// Developers can filter the robots meta
		return $robots;
	}

	/**
	 * Ensure SEO settings are properly loaded and applied to frontend.
	 *
	 * @since    1.0.0
	 */
	public function ensure_seo_settings_loaded() {
		// Only run on frontend
		if (is_admin()) {
			return;
		}
		
		// Check if settings exist
		$settings = get_option('seo_simple_settings', array());
		
		// If settings are empty, set defaults
		if (empty($settings)) {
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
				'seo_home_title' => get_bloginfo('name') . ' | ' . get_bloginfo('description'),
				'seo_home_description' => get_bloginfo('description'),
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
			
			update_option('seo_simple_settings', $default_settings);
			
			// Default settings applied
		}
		
		// Ensure required settings exist
		$required_settings = array(
			'seo_enable_og' => 1,
			'seo_enable_twitter' => 1,
			'seo_enable_json_ld' => 1,
			'seo_title_separator' => '-',
			'seo_title_format' => '%post_title% | %site_name%',
			'seo_title_length' => 60,
			'seo_description_length' => 160,
		);
		
		$updated = false;
		foreach ($required_settings as $key => $default_value) {
			if (!isset($settings[$key])) {
				$settings[$key] = $default_value;
				$updated = true;
			}
		}
		
		if ($updated) {
			update_option('seo_simple_settings', $settings);
			
			// Missing settings added
		}
		
		// Clear any cache that might prevent settings from being applied
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}
		
		// Settings ensured and loaded
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Seo_Simple_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Initialize the cache manager.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function initialize_cache_manager() {
		// Initialize the cache manager
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		// Set up cache clearing hooks
		$cache_manager->setup_hooks();
		
		// Make the cache manager available globally for other classes
		if (!function_exists('Seo_Simple_Cache')) {
			function Seo_Simple_Cache() {
				return Seo_Simple_Cache_Manager::get_instance();
			}
		}
	}

}
