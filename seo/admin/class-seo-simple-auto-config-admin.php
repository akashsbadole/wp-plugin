<?php

/**
 * Handles the admin interface for auto configuration features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin interface for auto configuration features.
 *
 * This class handles the admin interface for auto configuration of SEO settings.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Auto_Config_Admin {

	/**
	 * The auto config handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Auto_Config    $auto_config    The auto config handler instance.
	 */
	private $auto_config;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Auto_Config    $auto_config    The auto config handler instance.
	 */
	public function __construct( $auto_config ) {
		$this->auto_config = $auto_config;
	}

	/**
	 * Register hooks for the admin interface.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add menu item
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		
		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_run_auto_config', array( $this, 'ajax_run_auto_config' ) );
		add_action( 'wp_ajax_seo_simple_get_site_analysis', array( $this, 'ajax_get_site_analysis' ) );
		
		// Handle form submissions
		add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
	}

	/**
	 * Add menu item.
	 *
	 * @since    1.0.0
	 */
	public function add_menu_item() {
		add_submenu_page(
			'seo-simple',
			__( 'Auto Configuration', 'seo-simple' ),
			__( 'Auto Config', 'seo-simple' ),
			'manage_options',
			'seo-simple-auto-config',
			array( $this, 'render_auto_config_page' )
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on auto config page
		if ( $hook !== 'seo-simple_page_seo-simple-auto-config' ) {
			return;
		}
		
		wp_enqueue_script(
			'seo-simple-auto-config-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-auto-config-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_localize_script(
			'seo-simple-auto-config-admin',
			'seo_simple_auto_config_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce_run_config' => wp_create_nonce( 'seo_simple_run_auto_config' ),
				'nonce_get_analysis' => wp_create_nonce( 'seo_simple_get_site_analysis' ),
				'strings' => array(
					'confirm_run' => __( 'Are you sure you want to run auto configuration? This will update your SEO settings.', 'seo-simple' ),
					'config_running' => __( 'Analyzing site and configuring settings...', 'seo-simple' ),
					'config_complete' => __( 'Auto configuration completed successfully.', 'seo-simple' ),
					'error' => __( 'An error occurred. Please try again.', 'seo-simple' ),
				),
			)
		);
	}

	/**
	 * Enqueue styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_styles( $hook ) {
		// Only load on auto config page
		if ( $hook !== 'seo-simple_page_seo-simple-auto-config' ) {
			return;
		}
		
		wp_enqueue_style(
			'seo-simple-auto-config-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-auto-config-admin.css',
			array(),
			SEO_SIMPLE_VERSION
		);
	}

	/**
	 * Handle form submissions.
	 *
	 * @since    1.0.0
	 */
	public function handle_form_submissions() {
		// Check if our form was submitted
		if ( isset( $_POST['seo_simple_auto_config_nonce'] ) && 
		     wp_verify_nonce( $_POST['seo_simple_auto_config_nonce'], 'seo_simple_auto_config' ) ) {
			
			// Check user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
			}
			
			// Run auto configuration
			$result = $this->auto_config->apply_auto_configuration();
			
			// Redirect to avoid form resubmission
			if ( $result ) {
				wp_redirect( add_query_arg( array( 'page' => 'seo-simple-auto-config', 'config' => 'success' ), admin_url( 'admin.php' ) ) );
			} else {
				wp_redirect( add_query_arg( array( 'page' => 'seo-simple-auto-config', 'config' => 'error' ), admin_url( 'admin.php' ) ) );
			}
			exit;
		}
	}

	/**
	 * Render the auto configuration page.
	 *
	 * @since    1.0.0
	 */
	public function render_auto_config_page() {
		// Get site analysis
		$site_analysis = $this->auto_config->get_site_analysis();
		
		// Get recommended settings
		$recommended_settings = $this->auto_config->generate_optimal_settings();
		
		// Check for configuration status
		$config_status = isset( $_GET['config'] ) ? sanitize_text_field( $_GET['config'] ) : '';
		
		include_once dirname( __FILE__ ) . '/partials/auto-config-page.php';
	}

	/**
	 * AJAX handler for running auto configuration.
	 *
	 * @since    1.0.0
	 */
	public function ajax_run_auto_config() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_run_auto_config', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Run auto configuration
		$result = $this->auto_config->apply_auto_configuration();
		
		if ( $result ) {
			wp_send_json_success( array( 
				'message' => __( 'Auto configuration completed successfully.', 'seo-simple' ),
				'redirect' => add_query_arg( array( 'page' => 'seo-simple-auto-config', 'config' => 'success' ), admin_url( 'admin.php' ) )
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to apply auto configuration.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for getting site analysis.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_site_analysis() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_get_site_analysis', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Get site analysis
		$analysis = $this->auto_config->get_site_analysis();
		
		wp_send_json_success( array( 'analysis' => $analysis ) );
	}
}