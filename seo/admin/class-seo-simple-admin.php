<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The SEO meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The SEO meta box instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta_Box    $meta_box    The SEO meta box instance.
	 */
	private $meta_box;

	/**
	 * The SEO settings instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Settings    $settings    The SEO settings instance.
	 */
	private $settings;

	/**
	 * The SEO dashboard instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Dashboard    $dashboard    The SEO dashboard instance.
	 */
	private $dashboard;

	/**
	 * The SEO bulk editor instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Bulk_Editor    $bulk_editor    The SEO bulk editor instance.
	 */
	private $bulk_editor;
	
	/**
	 * The SEO redirect admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Redirect_Admin    $redirect_admin    The SEO redirect admin instance.
	 */
	private $redirect_admin;
	
	/**
	 * The internal links admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Internal_Links_Admin    $internal_links_admin    The internal links admin instance.
	 */
	private $internal_links_admin;
	
	/**
	 * The role manager admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Roles_Admin    $roles_admin    The role manager admin instance.
	 */
	private $roles_admin;
	
	/**
	 * The keyword suggestions admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Keyword_Suggestions_Admin    $keyword_suggestions_admin    The keyword suggestions admin instance.
	 */
	private $keyword_suggestions_admin;
	
	/**
	 * The analyzer admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Analyzer_Admin    $analyzer_admin    The analyzer admin instance.
	 */
	private $analyzer_admin;
	
	/**
	 * The page builder admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Page_Builders_Admin    $page_builders_admin    The page builder admin instance.
	 */
	private $page_builders_admin;
	
	/**
	 * The 404 monitor admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_404_Monitor_Admin    $monitor_404_admin    The 404 monitor admin instance.
	 */
	private $monitor_404_admin;
	
	/**
	 * The auto config admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Auto_Config_Admin    $auto_config_admin    The auto config admin instance.
	 */
	private $auto_config_admin;
	
	/**
	 * The code snippets admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Code_Snippets_Admin    $code_snippets_admin    The code snippets admin instance.
	 */
	private $code_snippets_admin;
	
	/**
	 * The Google admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Google_Admin    $google_admin    The Google admin instance.
	 */
	private $google_admin;
	
	/**
	 * The instant indexing admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Instant_Indexing_Admin    $instant_indexing_admin    The instant indexing admin instance.
	 */
	private $instant_indexing_admin;
	
	/**
	 * The schema admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema_Admin    $schema_admin    The schema admin instance.
	 */
	private $schema_admin;
	
	/**
	 * The speed optimization admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Speed_Optimization_Admin    $speed_optimization_admin    The speed optimization admin instance.
	 */
	private $speed_optimization_admin;
	
	/**
	 * The WooCommerce admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_WooCommerce_Admin    $woocommerce_admin    The WooCommerce admin instance.
	 */
	private $woocommerce_admin;


	/**
	 * The security admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security_Admin    $security_admin    The security admin instance.
	 */
	private $security_admin;
	

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Initialize SEO meta handler
		$this->seo_meta = new Seo_Simple_Meta();
		
		// Initialize SEO meta box
		$this->meta_box = new Seo_Simple_Meta_Box( $this->seo_meta );
		
		// Initialize SEO settings
		if ( class_exists( 'Seo_Simple_Settings' ) ) {
			$this->settings = new Seo_Simple_Settings( $plugin_name, $version );
		}
		
		// Initialize SEO dashboard
		if ( class_exists( 'Seo_Simple_Dashboard' ) ) {
			$this->dashboard = new Seo_Simple_Dashboard( $this->seo_meta );
		}
		
		// Initialize SEO bulk editor
		if ( class_exists( 'Seo_Simple_Bulk_Editor_Admin' ) ) {
			$this->bulk_editor = new Seo_Simple_Bulk_Editor_Admin( $this->seo_meta );
		} elseif ( class_exists( 'Seo_Simple_Bulk_Editor' ) ) {
			$this->bulk_editor = new Seo_Simple_Bulk_Editor( $this->seo_meta );
		}
		
		// Initialize SEO redirect admin
		if ( class_exists( 'Seo_Simple_Redirect_Admin' ) ) {
			$this->redirect_admin = new Seo_Simple_Redirect_Admin();
		}
		
		// Initialize internal links admin
		if ( class_exists( 'Seo_Simple_Internal_Links_Admin' ) ) {
			$this->internal_links_admin = new Seo_Simple_Internal_Links_Admin();
		}
		
		// Initialize role manager admin
		if ( class_exists( 'Seo_Simple_Roles_Admin' ) ) {
			$this->roles_admin = new Seo_Simple_Roles_Admin( $plugin_name, $version );
		}
		
		// Initialize keyword suggestions admin
		if ( class_exists( 'Seo_Simple_Keyword_Suggestions_Admin' ) ) {
			$this->keyword_suggestions_admin = new Seo_Simple_Keyword_Suggestions_Admin( $plugin_name, $version );
		}
		
		// Initialize SEO analyzer admin
		if ( class_exists( 'Seo_Simple_Analyzer_Admin' ) ) {
			$this->analyzer_admin = new Seo_Simple_Analyzer_Admin( $plugin_name, $version );
		}
		
		// Initialize page builder admin
		if ( class_exists( 'Seo_Simple_Page_Builders_Admin' ) ) {
			$this->page_builders_admin = new Seo_Simple_Page_Builders_Admin( $plugin_name, $version );
		}
		
		// Initialize 404 monitor admin
		if ( class_exists( 'Seo_Simple_404_Monitor_Admin' ) ) {
			$this->monitor_404_admin = new Seo_Simple_404_Monitor_Admin();
		}

		// Initialize security admin
		if ( class_exists( 'Seo_Simple_Security' ) && class_exists( 'Seo_Simple_Security_Admin' ) ) {
			$security = new Seo_Simple_Security();
			$this->security_admin = new Seo_Simple_Security_Admin( $security );
			$this->security_admin->register_hooks();
		}
		
		// Initialize auto config admin
		if ( class_exists( 'Seo_Simple_Auto_Config_Admin' ) && class_exists( 'Seo_Simple_Auto_Config' ) ) {
			$auto_config = new Seo_Simple_Auto_Config();
			$this->auto_config_admin = new Seo_Simple_Auto_Config_Admin( $auto_config );
		}
		
		// Initialize code snippets admin
		if ( class_exists( 'Seo_Simple_Code_Snippets_Admin' ) ) {
			$this->code_snippets_admin = new Seo_Simple_Code_Snippets_Admin( $plugin_name, $version );
		}
		
		// Initialize Google admin
		if ( class_exists( 'Seo_Simple_Google_Admin' ) ) {
			$google_integration = class_exists( 'Seo_Simple_Google_Integration' ) ? new Seo_Simple_Google_Integration( $this->seo_meta ) : null;
			$this->google_admin = new Seo_Simple_Google_Admin( $this->seo_meta, $google_integration, $this->security_admin );
		}
		
		// Initialize instant indexing admin
		if ( class_exists( 'Seo_Simple_Instant_Indexing_Admin' ) ) {
			$instant_indexing = class_exists( 'Seo_Simple_Instant_Indexing' ) ? new Seo_Simple_Instant_Indexing( $this->seo_meta ) : null;
			$this->instant_indexing_admin = new Seo_Simple_Instant_Indexing_Admin( $this->seo_meta, $instant_indexing, $this->security_admin );
		}
		
		// Initialize schema admin
		if ( class_exists( 'Seo_Simple_Schema_Admin' ) ) {
			$schema_enhanced = class_exists( 'Seo_Simple_Schema_Enhanced' ) ? new Seo_Simple_Schema_Enhanced() : null;
			$this->schema_admin = new Seo_Simple_Schema_Admin( $this->seo_meta, $schema_enhanced );
		}
		
		// Initialize speed optimization admin
		if ( class_exists( 'Seo_Simple_Speed_Optimization_Admin' ) ) {
			$speed_optimization = class_exists( 'Seo_Simple_Speed_Optimization' ) ? new Seo_Simple_Speed_Optimization() : null;
			$this->speed_optimization_admin = new Seo_Simple_Speed_Optimization_Admin( $speed_optimization );
		}
		
		// Initialize WooCommerce admin
		if ( class_exists( 'Seo_Simple_WooCommerce_Admin' ) ) {
			$this->woocommerce_admin = new Seo_Simple_WooCommerce_Admin( $this->seo_meta );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Simple_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Simple_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/seo-simple-admin.css', array(), $this->version, 'all' );
		
		// Enqueue analyzer styles
		wp_enqueue_style( $this->plugin_name . '-analyzer', plugin_dir_url( __FILE__ ) . 'css/seo-simple-analyzer.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Simple_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Simple_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/seo-simple-admin.js', array( 'jquery' ), $this->version, false );
		
		// Enqueue enhanced SEO features script on post editing screens
		$screen = get_current_screen();
		if ( $screen && (
			$screen->base === 'post' ||
			$screen->base === 'edit' ||
			strpos( $screen->id, 'post' ) !== false
		) ) {
			wp_enqueue_script(
				$this->plugin_name . '-enhanced',
				plugin_dir_url( __FILE__ ) . 'js/seo-simple-enhanced.js',
				array( 'jquery', $this->plugin_name ),
				$this->version,
				false
			);
		}
		
		// Enqueue WordPress media scripts for settings page and any page that might need image upload
		if ( $screen && (
			$screen->id === 'settings_page_seo-simple-settings' ||
			$screen->id === 'seo-simple_page_seo-simple-settings' ||
			strpos( $screen->id, 'seo-simple' ) !== false
		) ) {
			wp_enqueue_media();
		}
		
		// Also enqueue media scripts if we're on any admin page with our script
		// This ensures media uploader is available when needed
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'seo-simple-settings' ) {
			wp_enqueue_media();
		}
		
		// Localize script for AJAX and other data
		wp_localize_script( $this->plugin_name, 'seo_simple_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'seo_simple_admin_nonce' ),
		) );
		
		// Localize enhanced script for deep analysis
		if ( wp_script_is( $this->plugin_name . '-enhanced', 'enqueued' ) ) {
			wp_localize_script( $this->plugin_name . '-enhanced', 'seo_simple_enhanced', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_enhanced_nonce' ),
				'analyze_text' => __( 'Analyze Content', 'seo-simple' ),
				'analyzing_text' => __( 'Analyzing...', 'seo-simple' ),
				'error_text' => __( 'Error occurred during analysis', 'seo-simple' ),
			) );
		}
		
		// Enqueue internal links scripts and styles
		if ( $this->internal_links_admin ) {
			$this->internal_links_admin->enqueue_scripts();
		}
		
		// Enqueue keyword suggestions scripts and styles
		if ( $this->keyword_suggestions_admin ) {
			$this->keyword_suggestions_admin->enqueue_styles();
			$this->keyword_suggestions_admin->enqueue_scripts();
		}
		
		// Enqueue analyzer scripts
		wp_enqueue_script( $this->plugin_name . '-analyzer', plugin_dir_url( __FILE__ ) . 'js/seo-simple-analyzer.js', array( 'jquery' ), $this->version, false );
		
		// Localize analyzer script
		wp_localize_script( $this->plugin_name . '-analyzer', 'seoSimpleAnalyzer', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'seo_simple_analyzer_nonce' ),
		) );
		
		// Enqueue page builder scripts only if the page builders are active
		if ( defined('ELEMENTOR_VERSION') && is_plugin_active('elementor/elementor.php') ) {
			// Only enqueue elementor-compat if Elementor is active
			$elementor_deps = array('jquery');
			
			// Add elementor-common if available
			if ( wp_script_is('elementor-common', 'registered') ) {
				$elementor_deps[] = 'elementor-common';
			}
			
			// Add backbone-marionette if available
			if ( wp_script_is('backbone-marionette', 'registered') ) {
				$elementor_deps[] = 'backbone-marionette';
			}
			
			wp_enqueue_script(
				$this->plugin_name . '-elementor-compat',
				plugin_dir_url( __FILE__ ) . 'js/seo-simple-elementor-compat.js',
				$elementor_deps,
				$this->version,
				false
			);
		}
		
		wp_enqueue_script( $this->plugin_name . '-divi-compat', plugin_dir_url( __FILE__ ) . 'js/seo-simple-divi-compat.js', array( 'jquery' ), $this->version, false );
		
		// Localize page builder scripts only if they're enqueued
		if ( defined('ELEMENTOR_VERSION') && is_plugin_active('elementor/elementor.php') ) {
			wp_localize_script( $this->plugin_name . '-elementor-compat', 'seoSimpleElementor', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_elementor_nonce' ),
			) );
		}
		
		wp_localize_script( $this->plugin_name . '-divi-compat', 'seoSimpleDivi', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'seo_simple_divi_nonce' ),
		) );

	}

	/**
	 * Register the SEO meta box.
	 *
	 * @since    1.0.0
	 */
	public function register_meta_box() {
		$this->meta_box->register_meta_box();
	}

	/**
	 * Register the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'SEO Simple', 'seo-simple' ),
			__( 'SEO Simple', 'seo-simple' ),
			'manage_options',
			'seo-simple',
			array( $this, 'render_dashboard_page' ),
			'dashicons-search',
			30
		);

		// Dashboard - First submenu item
		add_submenu_page(
			'seo-simple',
			__( 'SEO Dashboard', 'seo-simple' ),
			__( 'Dashboard', 'seo-simple' ),
			'manage_options',
			'seo-simple',
			array( $this, 'render_dashboard_page' )
		);

		// Settings
		add_submenu_page(
			'seo-simple',
			__( 'SEO Settings', 'seo-simple' ),
			__( 'Settings', 'seo-simple' ),
			'manage_options',
			'seo-simple-settings',
			array( $this, 'render_settings_page' )
		);

		// Redirects
		if ( $this->redirect_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Redirect Manager', 'seo-simple' ),
				__( 'Redirects', 'seo-simple' ),
				'manage_options',
				'seo-simple-redirects',
				array( $this->redirect_admin, 'render_redirect_manager_page' )
			);
		}

		// Internal Links
		if ( $this->internal_links_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Internal Links', 'seo-simple' ),
				__( 'Internal Links', 'seo-simple' ),
				'manage_options',
				'seo-simple-internal-links',
				array( $this->internal_links_admin, 'render_internal_links_page' )
			);
		}

		// Role Manager
		if ( $this->roles_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Role Manager', 'seo-simple' ),
				__( 'Role Manager', 'seo-simple' ),
				'manage_options',
				'seo-simple-roles',
				array( $this->roles_admin, 'render_settings_page' )
			);
		}

		// Page Builder SEO
		if ( $this->page_builders_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Page Builder SEO', 'seo-simple' ),
				__( 'Page Builder SEO', 'seo-simple' ),
				'manage_options',
				'seo-simple-page-builders',
				array( $this->page_builders_admin, 'render_page_builder_page' )
			);
		}

		// Security
		if ( $this->security_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Security', 'seo-simple' ),
				__( 'Security', 'seo-simple' ),
				'manage_options',
				'seo-simple-security',
				array( $this->security_admin, 'render_security_page' )
			);
			
			// Security Settings
			add_submenu_page(
				'seo-simple',
				__( '____Security Settings', 'seo-simple' ),
				__( '____Security Settings', 'seo-simple' ),
				'manage_options',
				'seo-simple-security-settings',
				array( $this->security_admin, 'render_security_settings_page' )
			);
		}


		// Bulk Editor
		if ( $this->bulk_editor ) {
			if ( method_exists( $this->bulk_editor, 'render_bulk_editor_page' ) ) {
				add_submenu_page(
					'seo-simple',
					__( 'Bulk Editor', 'seo-simple' ),
					__( 'Bulk Editor', 'seo-simple' ),
					'manage_options',
					'seo-simple-bulk-editor',
					array( $this->bulk_editor, 'render_bulk_editor_page' )
				);
			} elseif ( method_exists( $this->bulk_editor, 'register_hooks' ) ) {
				$this->bulk_editor->register_hooks();
			}
		}

		// Auto Config
		if ( $this->auto_config_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Auto Configuration', 'seo-simple' ),
				__( 'Auto Config', 'seo-simple' ),
				'manage_options',
				'seo-simple-auto-config',
				array( $this->auto_config_admin, 'render_auto_config_page' )
			);
		}

		// Code Snippets
		if ( $this->code_snippets_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Code Snippets', 'seo-simple' ),
				__( 'Code Snippets', 'seo-simple' ),
				'manage_options',
				'seo-simple-code-snippets',
				array( $this->code_snippets_admin, 'display_admin_page' )
			);
		}

		// Google Analytics
		if ( $this->google_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Google Analytics', 'seo-simple' ),
				__( 'Google Analytics', 'seo-simple' ),
				'manage_options',
				'seo-simple-google-analytics',
				array( $this->google_admin, 'render_analytics_page' )
			);
			
			// Google Search Console
			add_submenu_page(
				'seo-simple',
				__( 'Google Search Console', 'seo-simple' ),
				__( 'Search Console', 'seo-simple' ),
				'manage_options',
				'seo-simple-search-console',
				array( $this->google_admin, 'render_search_console_page' )
			);
		}

		// Instant Indexing
		if ( $this->instant_indexing_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Instant Indexing', 'seo-simple' ),
				__( 'Instant Indexing', 'seo-simple' ),
				'manage_options',
				'seo-simple-instant-indexing',
				array( $this, 'render_instant_indexing_page' )
			);
		}

		// Schema Markup
		if ( $this->schema_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Schema Markup', 'seo-simple' ),
				__( 'Schema', 'seo-simple' ),
				'manage_options',
				'seo-simple-schema',
				array( $this, 'render_schema_page' )
			);
		}

		// Speed Optimization
		if ( $this->speed_optimization_admin ) {
			add_submenu_page(
				'seo-simple',
				__( 'Speed Optimization', 'seo-simple' ),
				__( 'Speed Optimization', 'seo-simple' ),
				'manage_options',
				'seo-simple-speed-optimization',
				array( $this->speed_optimization_admin, 'render_speed_page' )
			);
		}

		// WooCommerce SEO
		if ( $this->woocommerce_admin && class_exists( 'WooCommerce' ) ) {
			add_submenu_page(
				'seo-simple',
				__( 'WooCommerce SEO', 'seo-simple' ),
				__( 'WooCommerce SEO', 'seo-simple' ),
				'manage_options',
				'seo-simple-woocommerce',
				array( $this->woocommerce_admin, 'render_woocommerce_settings' )
			);
		}

		// 404 Monitor
		if ( $this->monitor_404_admin ) {
			add_submenu_page(
				'seo-simple',
				__( '404 Monitor', 'seo-simple' ),
				__( '404 Monitor', 'seo-simple' ),
				'manage_options',
				'seo-simple-404-monitor',
				array( $this->monitor_404_admin, 'render_page' )
			);
		}
	}

	/**
	 * Register the settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		if ( $this->settings ) {
			$this->settings->register_settings();
		}
		
		// Register schema settings
		register_setting(
			'seo_simple_schema_settings',
			'seo_simple_schema_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_schema_settings' ),
				'default' => array()
			)
		);
	}
	
	/**
	 * Sanitize schema settings.
	 *
	 * @since    1.0.0
	 * @param    array    $input    The input data to sanitize.
	 * @return   array              The sanitized data.
	 */
	public function sanitize_schema_settings( $input ) {
		$sanitized = array();
		
		$checkbox_fields = array(
			'enable_auto_schema',
			'enable_faq_schema',
			'enable_breadcrumb_schema',
			'enable_organization_schema'
		);
		
		foreach ( $checkbox_fields as $field ) {
			$sanitized[$field] = isset( $input[$field] ) ? 1 : 0;
		}
		
		$text_fields = array(
			'default_schema_type',
			'organization_name',
			'organization_logo',
			'organization_phone',
			'organization_address'
		);
		
		foreach ( $text_fields as $field ) {
			if ( isset( $input[$field] ) ) {
				if ( $field === 'organization_logo' ) {
					$sanitized[$field] = esc_url_raw( $input[$field] );
				} else {
					$sanitized[$field] = sanitize_text_field( $input[$field] );
				}
			}
		}
		
		return $sanitized;
	}

	/**
	 * Register the dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {
		if ( $this->dashboard ) {
			$this->dashboard->register_dashboard_widget();
		}
		
		// Register internal links dashboard widget
		if ( $this->internal_links_admin ) {
			$this->internal_links_admin->register_dashboard_widget();
		}
		
		// Register keyword suggestions dashboard widget
		if ( $this->keyword_suggestions_admin ) {
			$this->keyword_suggestions_admin->register_dashboard_widget();
		}
		
		// Register analyzer dashboard widget
		if ( $this->analyzer_admin ) {
			$this->analyzer_admin->register_dashboard_widget();
		}
	}

	/**
	 * Render the dashboard page.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/dashboard-page.php';
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		if ( $this->settings ) {
			$this->settings->render_settings_page();
		}
	}

	/**
	 * Handle AJAX requests for settings operations.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// Regenerate sitemap
		add_action( 'wp_ajax_seo_simple_regenerate_sitemap', array( $this, 'ajax_regenerate_sitemap' ) );
		
		// Export settings
		add_action( 'wp_ajax_seo_simple_export_settings', array( $this, 'ajax_export_settings' ) );

		// Handle security admin AJAX requests
		if ( $this->security_admin ) {
			$this->security_admin->register_hooks();
		}

		
		// Import settings
		add_action( 'wp_ajax_seo_simple_import_settings', array( $this, 'ajax_import_settings' ) );
		
		// Reset settings
		add_action( 'wp_ajax_seo_simple_reset_settings', array( $this, 'ajax_reset_settings' ) );
		
		// Handle dashboard AJAX requests
		if ( $this->dashboard ) {
			$this->dashboard->handle_ajax_requests();
		}
		
		// Handle bulk editor AJAX requests
		if ( $this->bulk_editor && method_exists( $this->bulk_editor, 'handle_ajax_requests' ) ) {
			$this->bulk_editor->handle_ajax_requests();
		}
		
		// Handle redirect admin AJAX requests
		if ( $this->redirect_admin ) {
			$this->redirect_admin->handle_ajax_requests();
		}
		
		// Handle internal links admin AJAX requests
		if ( $this->internal_links_admin ) {
			$this->internal_links_admin->handle_ajax_requests();
		}
		
		// Handle role manager admin AJAX requests
		if ( $this->roles_admin ) {
			$this->roles_admin->handle_ajax_requests();
		}
		
		// Handle keyword suggestions admin AJAX requests
		if ( $this->keyword_suggestions_admin ) {
			$this->keyword_suggestions_admin->handle_ajax_requests();
		}
		
		// Handle analyzer admin AJAX requests
		if ( $this->analyzer_admin ) {
			$this->analyzer_admin->handle_ajax_requests();
		}
	}

	/**
	 * AJAX handler for regenerating sitemap.
	 *
	 * @since    1.0.0
	 */
	public function ajax_regenerate_sitemap() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_regenerate_sitemap', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		// Regenerate sitemap
		if ( class_exists( 'Seo_Simple_Sitemap' ) ) {
			$sitemap = new Seo_Simple_Sitemap();
			if ( $sitemap->generate_sitemap() ) {
				update_option( 'seo_simple_sitemap_last_generated', current_time( 'timestamp' ) );
				wp_send_json_success( array( 'message' => __( 'Sitemap regenerated successfully.', 'seo-simple' ) ) );
			}
		
		}
		
		wp_send_json_error( array( 'message' => __( 'Error regenerating sitemap.', 'seo-simple' ) ) );
	}

	/**
		* Render the instant indexing page.
		*
		* @since    1.0.0
		*/
	public function render_instant_indexing_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/instant-indexing-page.php';
	}

	/**
		* Render the schema page.
		*
		* @since    1.0.0
		*/
	public function render_schema_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/schema-page.php';
	}

	/**
	 * AJAX handler for exporting settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_settings() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_export_settings', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'seo-simple' ) ) );
			wp_die();
		}
		
		if ( $this->settings ) {
			$this->settings->export_settings();
		}
		
		wp_die();
	}

	/**
	 * AJAX handler for importing settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_import_settings() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_import_settings', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		if ( $this->settings ) {
			$this->settings->import_settings();
		}
		
		wp_die();
	}

	/**
	 * AJAX handler for resetting settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_reset_settings() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_reset_settings', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		// Delete settings option
		delete_option( 'seo_simple_settings' );
		
		// Re-register default settings
		if ( class_exists( 'Seo_Simple_Activator' ) ) {
			Seo_Simple_Activator::activate();
		}
		
		wp_send_json_success( array( 'message' => __( 'Settings reset to defaults successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Handle settings actions like export, import, etc.
	 *
	 * @since    1.0.0
	 */
	public function handle_settings_actions() {
		if ( isset( $_GET['action'] ) && isset( $_GET['page'] ) && $_GET['page'] === 'seo-simple-settings' ) {
			$action = sanitize_text_field( $_GET['action'] );
			
			switch ( $action ) {
				case 'export_settings':
					if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'seo_simple_export_settings' ) ) {
						if ( $this->settings ) {
							$this->settings->export_settings();
						}
					}
					break;
					
				case 'import_settings':
					if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'seo_simple_import_settings' ) ) {
						if ( $this->settings ) {
							$this->settings->import_settings();
						}
					}
					break;
			}
		}
	}

}
