<?php

/**
 * Handles the admin interface for security features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin interface for security features.
 *
 * This class handles the admin interface for security features such as
 * Web Application Firewall (WAF), malware scanner, and more.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Security_Admin {

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
	 * @param    Seo_Simple_Security    $security    The security handler instance.
	 */
	public function __construct( $security ) {
		$this->security = $security;
	}

	/**
	 * Register hooks for the admin interface.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		
		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		// Add dashboard widgets
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		
		// Add user profile fields
		add_action( 'show_user_profile', array( $this, 'show_user_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'show_user_profile_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_profile_fields' ) );
		
		// Handle form submissions
		add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_run_security_scan', array( $this, 'ajax_run_security_scan' ) );
		add_action( 'wp_ajax_seo_simple_block_ip', array( $this, 'ajax_block_ip' ) );
		add_action( 'wp_ajax_seo_simple_unblock_ip', array( $this, 'ajax_unblock_ip' ) );
		add_action( 'wp_ajax_seo_simple_get_security_logs', array( $this, 'ajax_get_security_logs' ) );
		
		// Add test page to admin menu
		add_action( 'admin_menu', array( $this, 'add_test_page' ) );
		add_action( 'wp_ajax_seo_simple_update_threat_feed', array( $this, 'ajax_update_threat_feed' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on security pages
		if ( strpos( $hook, 'seo-simple-security' ) === false ) {
			return;
		}
		
		wp_enqueue_script(
			'seo-simple-security-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-security-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_localize_script(
			'seo-simple-security-admin',
			'seo_simple_security_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce_scan' => wp_create_nonce( 'seo_simple_run_security_scan' ),
				'nonce_block' => wp_create_nonce( 'seo_simple_block_ip' ),
				'nonce_unblock' => wp_create_nonce( 'seo_simple_unblock_ip' ),
				'nonce_logs' => wp_create_nonce( 'seo_simple_get_security_logs' ),
				'nonce_threat_feed' => wp_create_nonce( 'seo_simple_update_threat_feed' ),
				'strings' => array(
					'confirm_block' => __( 'Are you sure you want to block this IP address?', 'seo-simple' ),
					'confirm_unblock' => __( 'Are you sure you want to unblock this IP address?', 'seo-simple' ),
					'scan_running' => __( 'Security scan is running...', 'seo-simple' ),
					'scan_complete' => __( 'Security scan complete.', 'seo-simple' ),
					'ip_blocked' => __( 'IP address blocked successfully.', 'seo-simple' ),
					'ip_unblocked' => __( 'IP address unblocked successfully.', 'seo-simple' ),
					'threat_feed_updating' => __( 'Updating threat defense feed...', 'seo-simple' ),
					'threat_feed_updated' => __( 'Threat defense feed updated successfully.', 'seo-simple' ),
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
		// Only load on security pages
		if ( strpos( $hook, 'seo-simple-security' ) === false ) {
			return;
		}
		
		wp_enqueue_style(
			'seo-simple-security-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-security-admin.css',
			array(),
			SEO_SIMPLE_VERSION
		);
	}

	/**
	 * Add meta boxes.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		// Add security meta box to posts and pages
		add_meta_box(
			'seo-simple-security',
			__( 'Security', 'seo-simple' ),
			array( $this, 'render_security_meta_box' ),
			array( 'post', 'page' ),
			'side',
			'low'
		);
	}

	/**
	 * Render security meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_security_meta_box( $post ) {
		// Get security data
		$security_data = get_post_meta( $post->ID, 'seo_simple_security_data', true );
		
		// Get visitor IP
		$visitor_ip = $this->security->get_visitor_ip();
		
		include_once dirname( __FILE__ ) . '/partials/meta-box-security.php';
	}

	/**
	 * Add dashboard widgets.
	 *
	 * @since    1.0.0
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'seo-simple-security-dashboard',
			__( 'Security Overview', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		// Get security scan results
		$scan_results = get_option( 'seo_simple_security_scan_results', array() );
		
		// Get blocked IPs
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Get recent security logs
		$security_logs = $this->security->get_security_logs( 5 );
		
		include_once dirname( __FILE__ ) . '/partials/dashboard-widget-security.php';
	}

	/**
	 * Show user profile fields.
	 *
	 * @since    1.0.0
	 * @param    WP_User    $user    The user object.
	 */
	public function show_user_profile_fields( $user ) {
		// Only show if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Get 2FA settings
		$two_fa_enabled = get_user_meta( $user->ID, 'seo_simple_2fa_enabled', true );
		$two_fa_secret = get_user_meta( $user->ID, 'seo_simple_2fa_secret', true );
		
		include_once dirname( __FILE__ ) . '/partials/user-profile-security.php';
	}

	/**
	 * Save user profile fields.
	 *
	 * @since    1.0.0
	 * @param    int       $user_id    The user ID.
	 */
	public function save_user_profile_fields( $user_id ) {
		// Only save if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// Save 2FA settings
		if ( isset( $_POST['seo_simple_2fa_enabled'] ) ) {
			update_user_meta( $user_id, 'seo_simple_2fa_enabled', (bool) $_POST['seo_simple_2fa_enabled'] );
		}
		
		// Generate new secret if requested
		if ( isset( $_POST['seo_simple_generate_2fa_secret'] ) && $_POST['seo_simple_generate_2fa_secret'] ) {
			$secret = $this->generate_2fa_secret();
			update_user_meta( $user_id, 'seo_simple_2fa_secret', $secret );
		}
	}

	/**
	 * Generate 2FA secret.
	 *
	 * @since    1.0.0
	 * @return   string    The secret.
	 */
	private function generate_2fa_secret() {
		// This is a simplified implementation
		// In a real implementation, you would use a library like PHPGangsta/GoogleAuthenticator
		
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret = '';
		
		for ( $i = 0; $i < 16; $i++ ) {
			$secret .= $chars[ mt_rand( 0, strlen( $chars ) - 1 ) ];
		}
		
		return $secret;
	}

	/**
	 * Render the security page.
	 *
	 * @since    1.0.0
	 */
	public function render_security_page() {
		// Get security scan results
		$scan_results = get_option( 'seo_simple_security_scan_results', array() );
		
		// Get blocked IPs
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Get recent security logs
		$security_logs = $this->security->get_security_logs( 10 );
		
		include_once dirname( __FILE__ ) . '/partials/security-page.php';
	}

	/**
	 * Render the security settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_security_settings_page() {
		// Get current settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Extract settings for easier access
		$traffic_monitoring = isset( $settings['traffic_monitoring'] ) ? (bool) $settings['traffic_monitoring'] : false;
		$comment_protection = isset( $settings['comment_protection'] ) ? (bool) $settings['comment_protection'] : false;
		$file_upload_protection = isset( $settings['file_upload_protection'] ) ? (bool) $settings['file_upload_protection'] : false;
		$xmlrpc_protection = isset( $settings['xmlrpc_protection'] ) ? (bool) $settings['xmlrpc_protection'] : false;
		$rest_api_protection = isset( $settings['rest_api_protection'] ) ? (bool) $settings['rest_api_protection'] : false;
		$security_headers = isset( $settings['security_headers'] ) ? (bool) $settings['security_headers'] : false;
		$waf_enabled = isset( $settings['waf_enabled'] ) ? (bool) $settings['waf_enabled'] : false;
		$malware_scan_enabled = isset( $settings['malware_scan_enabled'] ) ? (bool) $settings['malware_scan_enabled'] : false;
		$scan_interval = isset( $settings['scan_interval'] ) ? $settings['scan_interval'] : 'daily';
		$brute_force_protection = isset( $settings['brute_force_protection'] ) ? (bool) $settings['brute_force_protection'] : false;
		$max_login_attempts = isset( $settings['max_login_attempts'] ) ? (int) $settings['max_login_attempts'] : 5;
		$lockout_duration = isset( $settings['lockout_duration'] ) ? (int) $settings['lockout_duration'] : 15;
		$two_factor_auth = isset( $settings['two_factor_auth'] ) ? (bool) $settings['two_factor_auth'] : false;
		$email_alerts = isset( $settings['email_alerts'] ) ? (bool) $settings['email_alerts'] : false;
		$alert_email = isset( $settings['alert_email'] ) ? $settings['alert_email'] : get_option( 'admin_email' );
		$threat_feed_enabled = isset( $settings['threat_feed_enabled'] ) ? (bool) $settings['threat_feed_enabled'] : false;
		$threat_feed_api_key = isset( $settings['threat_feed_api_key'] ) ? $settings['threat_feed_api_key'] : '';
		$threat_feed_update_interval = isset( $settings['threat_feed_update_interval'] ) ? $settings['threat_feed_update_interval'] : 'daily';
		
		include_once dirname( __FILE__ ) . '/partials/security-settings-page.php';
	}
	
	/**
		* Handle form submissions.
		*
		* @since    1.0.0
		*/
	public function handle_form_submissions() {
		// Check if this is the security settings form submission
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'seo_simple_save_security_settings' ) ) {
			
			// Check user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
			}
			
			// Save settings
			$settings = array(
				'traffic_monitoring' => isset( $_POST['security_traffic_monitoring'] ),
				'comment_protection' => isset( $_POST['security_comment_protection'] ),
				'file_upload_protection' => isset( $_POST['security_file_upload_protection'] ),
				'xmlrpc_protection' => isset( $_POST['security_xmlrpc_protection'] ),
				'rest_api_protection' => isset( $_POST['security_rest_api_protection'] ),
				'security_headers' => isset( $_POST['security_security_headers'] ),
				'waf_enabled' => isset( $_POST['security_waf_enabled'] ),
				'malware_scan_enabled' => isset( $_POST['security_malware_scan_enabled'] ),
				'scan_interval' => isset( $_POST['security_scan_interval'] ) ? sanitize_text_field( $_POST['security_scan_interval'] ) : 'daily',
				'brute_force_protection' => isset( $_POST['security_brute_force_protection'] ),
				'max_login_attempts' => isset( $_POST['security_max_login_attempts'] ) ? intval( $_POST['security_max_login_attempts'] ) : 5,
				'lockout_duration' => isset( $_POST['security_lockout_duration'] ) ? intval( $_POST['security_lockout_duration'] ) : 15,
				'two_factor_auth' => isset( $_POST['security_two_factor_auth'] ),
				'email_alerts' => isset( $_POST['security_email_alerts'] ),
				'alert_email' => isset( $_POST['security_alert_email'] ) ? sanitize_email( $_POST['security_alert_email'] ) : get_option( 'admin_email' ),
				'threat_feed_enabled' => isset( $_POST['security_threat_feed_enabled'] ),
				'threat_feed_api_key' => isset( $_POST['security_threat_feed_api_key'] ) ? sanitize_text_field( $_POST['security_threat_feed_api_key'] ) : '',
				'threat_feed_update_interval' => isset( $_POST['security_threat_feed_update_interval'] ) ? sanitize_text_field( $_POST['security_threat_feed_update_interval'] ) : 'daily',
			);
			
			update_option( 'seo_simple_settings', $settings );
			
			// Redirect to avoid form resubmission
			wp_redirect( add_query_arg( array( 'page' => 'seo-simple-security-settings', 'updated' => 'true' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}
	
	/**
		* AJAX handler for running security scan.
		*
		* @since    1.0.0
		*/
	public function ajax_run_security_scan() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_run_security_scan', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Run security scan
		$scan_results = $this->security->run_security_scan();
		
		// Save scan results
		update_option( 'seo_simple_security_scan_results', $scan_results );
		
		wp_send_json_success( array(
			'message' => __( 'Security scan completed.', 'seo-simple' ),
			'results' => $scan_results
		) );
	}
	
	/**
		* AJAX handler for blocking IP address.
		*
		* @since    1.0.0
		*/
	public function ajax_block_ip() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_block_ip', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Get IP address
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		
		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'seo-simple' ) ) );
		}
		
		// Block IP
		$result = $this->security->block_ip( $ip );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'IP address blocked successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to block IP address.', 'seo-simple' ) ) );
		}
	}
	
	/**
		* AJAX handler for unblocking IP address.
		*
		* @since    1.0.0
		*/
	public function ajax_unblock_ip() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_unblock_ip', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Get IP address
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		
		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid IP address.', 'seo-simple' ) ) );
		}
		
		// Unblock IP
		$result = $this->security->unblock_ip( $ip );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'IP address unblocked successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to unblock IP address.', 'seo-simple' ) ) );
		}
	}
	
	/**
		* AJAX handler for getting security logs.
		*
		* @since    1.0.0
		*/
	public function ajax_get_security_logs() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_get_security_logs', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Get limit
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 50;
		
		// Get security logs
		$logs = $this->security->get_security_logs( $limit );
		
		wp_send_json_success( array( 'logs' => $logs ) );
	}
	
	/**
		* AJAX handler for updating threat defense feed.
		*
		* @since    1.0.0
		*/
	public function ajax_update_threat_feed() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_update_threat_feed', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		// Update threat feed
		$result = $this->security->update_threat_feed();
		
		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Threat defense feed updated successfully.', 'seo-simple' ),
				'rules_count' => $this->security->get_threat_feed_status( 'rules_count' ),
				'blocked_ips_count' => $this->security->get_threat_feed_status( 'blocked_ips_count' )
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update threat defense feed.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * Add test page to admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_test_page() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Security Test', 'seo-simple' ),
			__( 'Security Test', 'seo-simple' ),
			'manage_options',
			'seo-simple-security-test',
			array( $this, 'render_test_page' )
		);
	}
	
	/**
	 * Render the security test page.
	 *
	 * @since    1.0.0
	 */
	public function render_test_page() {
		// Run tests
		$results = array();
		
		// Test 1: Check if security hooks are registered
		$results['hooks_registered'] = array();
		$global_hooks = $GLOBALS['wp_filter'];
		
		$security_hooks = array(
			'init' => 'init_security_features',
			'init' => 'init_waf',
			'wp_login_failed' => 'handle_failed_login',
			'authenticate' => 'check_brute_force',
			'preprocess_comment' => 'check_comment_security',
			'wp_handle_upload_prefilter' => 'check_upload_security',
			'admin_init' => 'protect_admin_area',
			'xmlrpc_enabled' => 'protect_xmlrpc',
			'rest_authentication_errors' => 'protect_rest_api',
			'send_headers' => 'add_security_headers'
		);
		
		$hooks_registered = 0;
		$total_hooks = count($security_hooks);
		
		foreach ($security_hooks as $hook => $method) {
			if (isset($global_hooks[$hook]) && !empty($global_hooks[$hook]->callbacks)) {
				foreach ($global_hooks[$hook]->callbacks as $priority => $callbacks) {
					foreach ($callbacks as $callback) {
						if (is_array($callback['function']) && 
							$callback['function'][0] instanceof Seo_Simple_Security &&
							$callback['function'][1] === $method) {
							$hooks_registered++;
							$results['hooks_registered'][] = array(
								'hook' => $hook,
								'method' => $method,
								'priority' => $priority,
								'status' => 'Registered'
							);
							break 2;
						}
					}
				}
			} else {
				$results['hooks_registered'][] = array(
					'hook' => $hook,
					'method' => $method,
					'status' => 'Not Registered'
				);
			}
		}
		
		$results['hooks_summary'] = "$hooks_registered/$total_hooks hooks registered";
		
		// Test 2: Check security settings
		$results['security_settings'] = array();
		$settings = get_option('seo_simple_settings', array());
		
		$security_settings = array(
			'security_waf_enabled' => 'WAF Enabled',
			'security_malware_scan_enabled' => 'Malware Scan Enabled',
			'security_brute_force_protection' => 'Brute Force Protection',
			'security_two_factor_auth' => 'Two-Factor Authentication',
			'security_comment_protection' => 'Comment Protection',
			'security_file_upload_protection' => 'File Upload Protection',
			'security_xmlrpc_protection' => 'XML-RPC Protection',
			'security_rest_api_protection' => 'REST API Protection',
			'security_security_headers' => 'Security Headers',
			'security_traffic_monitoring' => 'Traffic Monitoring',
			'security_email_alerts' => 'Email Alerts'
		);
		
		foreach ($security_settings as $key => $label) {
			$value = isset($settings[$key]) ? ($settings[$key] ? 'Enabled' : 'Disabled') : 'Not Set';
			$results['security_settings'][] = array(
				'label' => $label,
				'value' => $value,
				'status' => isset($settings[$key]) ? ($settings[$key] ? 'Good' : 'Warning') : 'Info'
			);
		}
		
		// Test 3: Check security scan results
		$results['scan_results'] = get_option('seo_simple_security_scan_results', array());
		
		// Test 4: Check blocked IPs
		$results['blocked_ips'] = get_option('seo_simple_blocked_ips', array());
		
		// Test 5: Check security logs
		$results['security_logs'] = get_option('seo_simple_security_logs', array());
		
		// Test 6: Test WAF functionality
		$results['waf_test'] = array(
			'visitor_ip' => $this->security->get_visitor_ip(),
			'method_exists' => method_exists($this->security, 'get_visitor_ip')
		);
		
		// Test 7: Check threat feed status
		if (method_exists($this->security, 'get_threat_feed_status')) {
			$results['threat_feed_status'] = $this->security->get_threat_feed_status();
		} else {
			$results['threat_feed_status'] = array('error' => 'Method not found');
		}
		
		include_once dirname(__FILE__) . '/partials/test-security-page.php';
	}
}