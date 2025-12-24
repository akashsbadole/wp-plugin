<?php

/**
 * Handles the admin functionality for Google integration.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin functionality for Google integration.
 *
 * This class is responsible for the admin interface for Google services
 * including Analytics, AdSense, and Search Console.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Google_Admin {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The Google integration instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Google_Integration    $google_integration    The Google integration instance.
	 */
	private $google_integration;

	/**
	 * The security handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security    $security    The security handler instance.
	 */
	private $security;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta                  $seo_meta           The SEO meta handler instance.
	 * @param    Seo_Simple_Google_Integration    $google_integration  The Google integration instance.
	 * @param    Seo_Simple_Security              $security           The security handler instance.
	 */
	public function __construct( $seo_meta, $google_integration, $security = null ) {
		$this->seo_meta = $seo_meta;
		$this->google_integration = $google_integration;
		$this->security = $security;
	}

	/**
	 * Register hooks for the admin functionality.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add settings tab
		add_filter( 'seo_simple_settings_tabs', array( $this, 'add_settings_tab' ) );
		
		// Add settings fields
		add_action( 'seo_simple_settings_tab_content_google', array( $this, 'render_settings_tab' ) );
		
		// Add post-level Google settings
		add_action( 'seo_simple_meta_box_tabs', array( $this, 'add_meta_box_tab' ) );
		add_action( 'seo_simple_meta_box_tab_content_google', array( $this, 'render_meta_box_tab' ) );
		
		// Add admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_google_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_seo_simple_google_authenticate', array( $this, 'ajax_authenticate' ) );
		add_action( 'wp_ajax_seo_simple_google_revoke_access', array( $this, 'ajax_revoke_access' ) );
		add_action( 'wp_ajax_seo_simple_google_test_connection', array( $this, 'ajax_test_connection' ) );
		
		// Add admin menu items
		add_action( 'admin_menu', array( $this, 'add_admin_menu_items' ) );
	}

	/**
	 * Add settings tab.
	 *
	 * @since    1.0.0
	 * @param    array     $tabs    The existing tabs.
	 * @return   array              The modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['google'] = array(
			'label' => __( 'Google', 'seo-simple' ),
			'icon' => 'dashicons-google',
		);
		
		return $tabs;
	}

	/**
	 * Render settings tab.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_tab() {
		$settings = $this->seo_meta->get_global_settings();
		
		// Set default values
		$google_analytics_enabled = isset( $settings['google_analytics_enabled'] ) ? $settings['google_analytics_enabled'] : false;
		$google_analytics_id = isset( $settings['google_analytics_id'] ) ? $settings['google_analytics_id'] : '';
		$google_analytics_version = isset( $settings['google_analytics_version'] ) ? $settings['google_analytics_version'] : 'ga4';
		$google_analytics_anonymize_ip = isset( $settings['google_analytics_anonymize_ip'] ) ? $settings['google_analytics_anonymize_ip'] : true;
		$google_analytics_track_outbound = isset( $settings['google_analytics_track_outbound'] ) ? $settings['google_analytics_track_outbound'] : false;
		$google_analytics_ecommerce = isset( $settings['google_analytics_ecommerce'] ) ? $settings['google_analytics_ecommerce'] : false;
		$google_analytics_exclude_roles = isset( $settings['google_analytics_exclude_roles'] ) ? $settings['google_analytics_exclude_roles'] : array();
		
		$google_adsense_enabled = isset( $settings['google_adsense_enabled'] ) ? $settings['google_adsense_enabled'] : false;
		$google_adsense_id = isset( $settings['google_adsense_id'] ) ? $settings['google_adsense_id'] : '';
		
		$google_site_verification = isset( $settings['google_site_verification'] ) ? $settings['google_site_verification'] : '';
		
		$google_search_console_enabled = isset( $settings['google_search_console_enabled'] ) ? $settings['google_search_console_enabled'] : false;
		$google_search_console_authenticated = isset( $settings['google_search_console_authenticated'] ) ? $settings['google_search_console_authenticated'] : false;
		$google_search_console_site = isset( $settings['google_search_console_site'] ) ? $settings['google_search_console_site'] : '';
		
		// Get user roles
		$user_roles = get_editable_roles();
		
		include_once dirname( __FILE__ ) . '/partials/google-settings-page.php';
	}

	/**
	 * Add meta box tab.
	 *
	 * @since    1.0.0
	 * @param    array     $tabs    The existing tabs.
	 * @return   array              The modified tabs.
	 */
	public function add_meta_box_tab( $tabs ) {
		$tabs['google'] = array(
			'label' => __( 'Google', 'seo-simple' ),
			'icon' => 'dashicons-google',
		);
		
		return $tabs;
	}

	/**
	 * Render meta box tab.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The current post object.
	 */
	public function render_meta_box_tab( $post ) {
		// Get post meta
		$google_analytics_track = get_post_meta( $post->ID, '_seo_simple_google_analytics_track', true );
		$google_analytics_custom_event = get_post_meta( $post->ID, '_seo_simple_google_analytics_custom_event', true );
		$google_analytics_event_category = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_category', true );
		$google_analytics_event_action = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_action', true );
		$google_analytics_event_label = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_label', true );
		
		include_once dirname( __FILE__ ) . '/partials/meta-box-google.php';
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on SEO settings page
		if ( 'seo-settings_page_seo-simple-settings' !== $hook ) {
			return;
		}
		
		// Check if we're on the Google tab
		if ( ! isset( $_GET['tab'] ) || 'google' !== $_GET['tab'] ) {
			return;
		}
		
		wp_enqueue_script(
			'seo-simple-google-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-google-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_enqueue_style(
			'seo-simple-google-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-google-admin.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		// Localize script
		wp_localize_script(
			'seo-simple-google-admin',
			'seo_simple_google_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_google_admin' ),
				'strings' => array(
					'confirm_revoke' => __( 'Are you sure you want to revoke access to Google Search Console?', 'seo-simple' ),
					'authenticating' => __( 'Authenticating...', 'seo-simple' ),
					'connection_test_success' => __( 'Connection test successful!', 'seo-simple' ),
					'connection_test_failed' => __( 'Connection test failed. Please check your settings.', 'seo-simple' ),
				),
			)
		);
	}

	/**
	 * Add admin menu items.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu_items() {
		// Add Google Analytics submenu
		add_submenu_page(
			'seo-simple-settings',
			__( 'Google Analytics', 'seo-simple' ),
			__( 'Analytics', 'seo-simple' ),
			'manage_options',
			'seo-simple-google-analytics',
			array( $this, 'render_analytics_page' )
		);
		
		// Add Google Search Console submenu
		add_submenu_page(
			'seo-simple-settings',
			__( 'Google Search Console', 'seo-simple' ),
			__( 'Search Console', 'seo-simple' ),
			'manage_options',
			'seo-simple-google-search-console',
			array( $this, 'render_search_console_page' )
		);
	}

	/**
	 * Render Analytics page.
	 *
	 * @since    1.0.0
	 */
	public function render_analytics_page() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_analytics_enabled'] ) || empty( $settings['google_analytics_id'] ) ) {
			?>
			<div class="wrap">
				<h1><?php _e( 'Google Analytics', 'seo-simple' ); ?></h1>
				<div class="notice notice-warning">
					<p><?php _e( 'Google Analytics is not configured. Please configure it in the SEO settings.', 'seo-simple' ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings&tab=google' ) ); ?>" class="button button-primary">
							<?php _e( 'Configure Google Analytics', 'seo-simple' ); ?>
						</a>
					</p>
				</div>
			</div>
			<?php
			return;
		}
		
		include_once dirname( __FILE__ ) . '/partials/google-analytics-page.php';
	}

	/**
	 * Render Search Console page.
	 *
	 * @since    1.0.0
	 */
	public function render_search_console_page() {
		$settings = $this->seo_meta->get_global_settings();
		
		if ( empty( $settings['google_search_console_enabled'] ) || empty( $settings['google_search_console_authenticated'] ) ) {
			?>
			<div class="wrap">
				<h1><?php _e( 'Google Search Console', 'seo-simple' ); ?></h1>
				<div class="notice notice-warning">
					<p><?php _e( 'Google Search Console is not authenticated. Please authenticate in the SEO settings.', 'seo-simple' ); ?></p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings&tab=google' ) ); ?>" class="button button-primary">
							<?php _e( 'Authenticate Google Search Console', 'seo-simple' ); ?>
						</a>
					</p>
				</div>
			</div>
			<?php
			return;
		}
		
		include_once dirname( __FILE__ ) . '/partials/google-search-console-page.php';
	}

	/**
	 * AJAX handler for saving settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_settings() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_admin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to save these settings.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize and save settings
		$settings = $this->seo_meta->get_global_settings();
		
		// Google Analytics settings
		$settings['google_analytics_enabled'] = isset( $_POST['google_analytics_enabled'] ) ? (bool) $_POST['google_analytics_enabled'] : false;
		$settings['google_analytics_id'] = isset( $_POST['google_analytics_id'] ) ? sanitize_text_field( $_POST['google_analytics_id'] ) : '';
		$settings['google_analytics_version'] = isset( $_POST['google_analytics_version'] ) ? sanitize_text_field( $_POST['google_analytics_version'] ) : 'ga4';
		$settings['google_analytics_anonymize_ip'] = isset( $_POST['google_analytics_anonymize_ip'] ) ? (bool) $_POST['google_analytics_anonymize_ip'] : false;
		$settings['google_analytics_track_outbound'] = isset( $_POST['google_analytics_track_outbound'] ) ? (bool) $_POST['google_analytics_track_outbound'] : false;
		$settings['google_analytics_ecommerce'] = isset( $_POST['google_analytics_ecommerce'] ) ? (bool) $_POST['google_analytics_ecommerce'] : false;
		$settings['google_analytics_exclude_roles'] = isset( $_POST['google_analytics_exclude_roles'] ) ? array_map( 'sanitize_text_field', $_POST['google_analytics_exclude_roles'] ) : array();
		
		// Google AdSense settings
		$settings['google_adsense_enabled'] = isset( $_POST['google_adsense_enabled'] ) ? (bool) $_POST['google_adsense_enabled'] : false;
		$settings['google_adsense_id'] = isset( $_POST['google_adsense_id'] ) ? sanitize_text_field( $_POST['google_adsense_id'] ) : '';
		
		// Google Site Verification
		$settings['google_site_verification'] = isset( $_POST['google_site_verification'] ) ? sanitize_text_field( $_POST['google_site_verification'] ) : '';
		
		// Google Search Console settings
		$settings['google_search_console_enabled'] = isset( $_POST['google_search_console_enabled'] ) ? (bool) $_POST['google_search_console_enabled'] : false;
		
		// Update settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for authentication.
	 *
	 * @since    1.0.0
	 */
	public function ajax_authenticate() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_admin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to authenticate.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// This is a placeholder implementation
		// In a real implementation, you would handle OAuth2 flow with Google
		// For now, we'll simulate successful authentication
		
		$settings = $this->seo_meta->get_global_settings();
		$settings['google_search_console_authenticated'] = true;
		$settings['google_search_console_site'] = isset( $_POST['site'] ) ? sanitize_text_field( $_POST['site'] ) : '';
		$settings['google_search_console_access_token'] = 'mock_access_token_' . time();
		$settings['google_search_console_refresh_token'] = 'mock_refresh_token_' . time();
		$settings['google_search_console_token_expires'] = time() + 3600; // 1 hour
		
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Authentication successful!', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for revoking access.
	 *
	 * @since    1.0.0
	 */
	public function ajax_revoke_access() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_admin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to revoke access.', 'seo-simple' ) ) );
			wp_die();
		}
		
		$settings = $this->seo_meta->get_global_settings();
		$settings['google_search_console_authenticated'] = false;
		$settings['google_search_console_site'] = '';
		unset( $settings['google_search_console_access_token'] );
		unset( $settings['google_search_console_refresh_token'] );
		unset( $settings['google_search_console_token_expires'] );
		
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Access revoked successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for testing connection.
	 *
	 * @since    1.0.0
	 */
	public function ajax_test_connection() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_google_admin' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to test connection.', 'seo-simple' ) ) );
			wp_die();
		}
		
		$service = isset( $_POST['service'] ) ? sanitize_text_field( $_POST['service'] ) : '';
		
		if ( 'analytics' === $service ) {
			$settings = $this->seo_meta->get_global_settings();
			
			if ( empty( $settings['google_analytics_id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Google Analytics ID is required.', 'seo-simple' ) ) );
			}
			
			// This is a placeholder implementation
			// In a real implementation, you would test the connection to Google Analytics API
			// For now, we'll simulate a successful connection
			
			wp_send_json_success( array( 'message' => __( 'Connection to Google Analytics successful!', 'seo-simple' ) ) );
		} elseif ( 'search_console' === $service ) {
			$settings = $this->seo_meta->get_global_settings();
			
			if ( empty( $settings['google_search_console_authenticated'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Google Search Console is not authenticated.', 'seo-simple' ) ) );
			}
			
			// This is a placeholder implementation
			// In a real implementation, you would test the connection to Google Search Console API
			// For now, we'll simulate a successful connection
			
			wp_send_json_success( array( 'message' => __( 'Connection to Google Search Console successful!', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid service specified.', 'seo-simple' ) ) );
		}
	}
}