<?php

/**
 * Handles security features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles security features.
 *
 * This class is responsible for implementing various security features
 * such as Web Application Firewall (WAF), malware scanner, and more.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Security {

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache_Manager    $cache_manager    The cache handler instance.
	 */
	private $cache_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Cache_Manager      $cache_manager  The cache handler instance.
	 */
	public function __construct( $cache_manager = null ) {
		$this->cache_manager = $cache_manager;
	}

	/**
	 * Register hooks for security features.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add settings tab
		add_filter( 'seo_simple_settings_tabs', array( $this, 'add_settings_tab' ) );
		
		// Add settings fields
		add_action( 'seo_simple_settings_tab_content_security', array( $this, 'render_settings_tab' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_save_security_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_seo_simple_run_security_scan', array( $this, 'ajax_run_security_scan' ) );
		add_action( 'wp_ajax_seo_simple_block_ip', array( $this, 'ajax_block_ip' ) );
		add_action( 'wp_ajax_seo_simple_unblock_ip', array( $this, 'ajax_unblock_ip' ) );
		add_action( 'wp_ajax_seo_simple_get_security_logs', array( $this, 'ajax_get_security_logs' ) );
		add_action( 'wp_ajax_seo_simple_update_threat_feed', array( $this, 'ajax_update_threat_feed' ) );
		
		// Add admin menu items
		add_action( 'admin_menu', array( $this, 'add_admin_menu_items' ) );
		
		// Initialize security features
		add_action( 'init', array( $this, 'init_security_features' ) );
		
		// WAF functionality
		add_action( 'init', array( $this, 'init_waf' ) );
		
		// Login protection
		add_action( 'wp_login_failed', array( $this, 'handle_failed_login' ) );
		add_filter( 'authenticate', array( $this, 'check_brute_force' ), 30, 3 );
		
		// Comment protection
		add_filter( 'preprocess_comment', array( $this, 'check_comment_security' ) );
		
		// File upload protection
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'check_upload_security' ) );
		
		// Admin area protection
		add_action( 'admin_init', array( $this, 'protect_admin_area' ) );
		
		// XML-RPC protection
		add_filter( 'xmlrpc_enabled', array( $this, 'protect_xmlrpc' ) );
		
		// REST API protection
		add_filter( 'rest_authentication_errors', array( $this, 'protect_rest_api' ) );
		
		// Security headers
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );
		
		// Malware scanner
		add_action( 'wp', array( $this, 'schedule_malware_scan' ) );
		add_action( 'seo_simple_malware_scan', array( $this, 'perform_malware_scan' ) );
		
		// Two-factor authentication
		add_action( 'login_form', array( $this, 'show_2fa_form' ) );
		add_filter( 'authenticate', array( $this, 'verify_2fa' ), 40, 3 );
		
		// Live traffic monitoring
		add_action( 'init', array( $this, 'monitor_traffic' ) );
		
		// Security alerts
		add_action( 'seo_simple_security_alert', array( $this, 'send_security_alert' ), 10, 2 );
		
		// Real-time threat defense feed
		add_action( 'init', array( $this, 'init_threat_feed' ) );
		add_action( 'seo_simple_update_threat_feed', array( $this, 'update_threat_feed' ) );
		add_action( 'wp', array( $this, 'schedule_threat_feed_update' ) );
	}

	/**
	 * Add settings tab.
	 *
	 * @since    1.0.0
	 * @param    array     $tabs    The existing tabs.
	 * @return   array              The modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['security'] = array(
			'label' => __( 'Security', 'seo-simple' ),
			'icon' => 'dashicons-shield',
		);
		
		return $tabs;
	}

	/**
	 * Render settings tab.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_tab() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Set default values
		$waf_enabled = isset( $settings['security_waf_enabled'] ) ? $settings['security_waf_enabled'] : true;
		$malware_scan_enabled = isset( $settings['security_malware_scan_enabled'] ) ? $settings['security_malware_scan_enabled'] : true;
		$scan_interval = isset( $settings['security_scan_interval'] ) ? $settings['security_scan_interval'] : 'daily';
		$brute_force_protection = isset( $settings['security_brute_force_protection'] ) ? $settings['security_brute_force_protection'] : true;
		$max_login_attempts = isset( $settings['security_max_login_attempts'] ) ? $settings['security_max_login_attempts'] : 5;
		$lockout_duration = isset( $settings['security_lockout_duration'] ) ? $settings['security_lockout_duration'] : 15;
		$two_factor_auth = isset( $settings['security_two_factor_auth'] ) ? $settings['security_two_factor_auth'] : false;
		$comment_protection = isset( $settings['security_comment_protection'] ) ? $settings['security_comment_protection'] : true;
		$file_upload_protection = isset( $settings['security_file_upload_protection'] ) ? $settings['security_file_upload_protection'] : true;
		$xmlrpc_protection = isset( $settings['security_xmlrpc_protection'] ) ? $settings['security_xmlrpc_protection'] : true;
		$rest_api_protection = isset( $settings['security_rest_api_protection'] ) ? $settings['security_rest_api_protection'] : true;
		$security_headers = isset( $settings['security_security_headers'] ) ? $settings['security_security_headers'] : true;
		$traffic_monitoring = isset( $settings['security_traffic_monitoring'] ) ? $settings['security_traffic_monitoring'] : true;
		$email_alerts = isset( $settings['security_email_alerts'] ) ? $settings['security_email_alerts'] : true;
		$alert_email = isset( $settings['security_alert_email'] ) ? $settings['security_alert_email'] : get_option( 'admin_email' );
		
		// Threat feed settings
		$threat_feed_enabled = isset( $settings['security_threat_feed_enabled'] ) ? $settings['security_threat_feed_enabled'] : false;
		$threat_feed_url = isset( $settings['security_threat_feed_url'] ) ? $settings['security_threat_feed_url'] : 'https://api.seo-simple.com/threat-feed/v1';
		$threat_feed_api_key = isset( $settings['security_threat_feed_api_key'] ) ? $settings['security_threat_feed_api_key'] : '';
		$threat_feed_interval = isset( $settings['security_threat_feed_interval'] ) ? $settings['security_threat_feed_interval'] : 'hourly';
		
		include_once dirname( __FILE__ ) . '/../admin/partials/security-settings-page.php';
	}

	/**
	 * Add admin menu items.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu_items() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Security', 'seo-simple' ),
			__( 'Security', 'seo-simple' ),
			'manage_options',
			'seo-simple-security',
			array( $this, 'render_security_page' )
		);
	}

	/**
	 * Render security page.
	 *
	 * @since    1.0.0
	 */
	public function render_security_page() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Get security scan results
		$scan_results = get_option( 'seo_simple_security_scan_results', array() );
		
		// Get blocked IPs
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Get security logs
		$security_logs = $this->get_security_logs( 50 );
		
		include_once dirname( __FILE__ ) . '/../admin/partials/security-page.php';
	}

	/**
	 * Initialize security features.
	 *
	 * @since    1.0.0
	 */
	public function init_security_features() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Initialize features based on settings
		if ( isset( $settings['security_waf_enabled'] ) && $settings['security_waf_enabled'] ) {
			// WAF is initialized separately in init_waf()
		}
		
		if ( isset( $settings['security_traffic_monitoring'] ) && $settings['security_traffic_monitoring'] ) {
			// Traffic monitoring is initialized separately in monitor_traffic()
		}
	}

	/**
	 * Initialize WAF.
	 *
	 * @since    1.0.0
	 */
	public function init_waf() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_waf_enabled'] ) || ! $settings['security_waf_enabled'] ) {
			return;
		}
		
		// Check if IP is blocked
		$visitor_ip = $this->get_visitor_ip();
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		if ( in_array( $visitor_ip, $blocked_ips ) ) {
			$this->block_request( 'IP blocked' );
		}
		
		// Check for common attack patterns
		$this->check_attack_patterns();
		
		// Check request method
		$this->check_request_method();
		
		// Check request URI
		$this->check_request_uri();
		
		// Check user agent
		$this->check_user_agent();
		
		// Check referer
		$this->check_referer();
	}

	/**
	 * Get visitor IP.
	 *
	 * @since    1.0.0
	 * @return   string    The visitor IP.
	 */
	public function get_visitor_ip() {
		$ip = '';
		
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return sanitize_text_field( $ip );
	}

	/**
	 * Block request.
	 *
	 * @since    1.0.0
	 * @param    string    $reason    The block reason.
	 */
	private function block_request( $reason ) {
		// Log the blocked request
		$this->log_security_event( 'blocked_request', array(
			'ip' => $this->get_visitor_ip(),
			'reason' => $reason,
			'uri' => $_SERVER['REQUEST_URI'],
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'referer' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
		) );
		
		// Send alert if enabled
		$settings = get_option( 'seo_simple_settings', array() );
		if ( isset( $settings['security_email_alerts'] ) && $settings['security_email_alerts'] ) {
			wp_schedule_single_event( time(), 'seo_simple_security_alert', array(
				'type' => 'blocked_request',
				'data' => array(
					'ip' => $this->get_visitor_ip(),
					'reason' => $reason,
					'uri' => $_SERVER['REQUEST_URI'],
				)
			) );
		}
		
		// Send 403 response
		status_header( 403 );
		nocache_headers();
		wp_die( __( 'Access denied. Your IP address has been blocked.', 'seo-simple' ) );
	}

	/**
	 * Check attack patterns.
	 *
	 * @since    1.0.0
	 */
	private function check_attack_patterns() {
		$patterns = array(
			'/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
			'/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
			'/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
			'/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
			'/javascript:/i',
			'/vbscript:/i',
			'/onload\s*=/i',
			'/onerror\s*=/i',
			'/onclick\s*=/i',
			'/onmouseover\s*=/i',
			'/eval\s*\(/i',
			'/expression\s*\(/i',
			'/union\s+select/i',
			'/select\s+.*\s+from/i',
			'/insert\s+into/i',
			'/update\s+.*\s+set/i',
			'/delete\s+from/i',
			'/drop\s+table/i',
			'/create\s+table/i',
			'/alter\s+table/i',
			'/exec\s*\(/i',
			'/system\s*\(/i',
			'/passthru\s*\(/i',
			'/shell_exec\s*\(/i',
		);
		
		// Check GET parameters
		foreach ( $_GET as $key => $value ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $value ) ) {
					$this->block_request( 'Suspicious pattern in GET parameter: ' . $key );
				}
			}
		}
		
		// Check POST parameters
		foreach ( $_POST as $key => $value ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $value ) ) {
					$this->block_request( 'Suspicious pattern in POST parameter: ' . $key );
				}
			}
		}
		
		// Check cookies
		foreach ( $_COOKIE as $key => $value ) {
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $value ) ) {
					$this->block_request( 'Suspicious pattern in cookie: ' . $key );
				}
			}
		}
	}

	/**
	 * Check request method.
	 *
	 * @since    1.0.0
	 */
	private function check_request_method() {
		$allowed_methods = array( 'GET', 'POST', 'HEAD' );
		
		if ( ! in_array( $_SERVER['REQUEST_METHOD'], $allowed_methods ) ) {
			$this->block_request( 'Disallowed request method: ' . $_SERVER['REQUEST_METHOD'] );
		}
	}

	/**
	 * Check request URI.
	 *
	 * @since    1.0.0
	 */
	private function check_request_uri() {
		$uri = $_SERVER['REQUEST_URI'];
		
		// Check for suspicious patterns in URI
		$suspicious_patterns = array(
			'/\.\.\//',           // Directory traversal
			'/\/etc\//',           // /etc/ directory
			'/\/proc\//',          // /proc/ directory
			'/\/sys\//',           // /sys/ directory
			'/\/usr\//',           // /usr/ directory
			'/\/var\//',           // /var/ directory
			'/\/bin\//',           // /bin/ directory
			'/\/sbin\//',          // /sbin/ directory
			'/\/boot\//',          // /boot/ directory
			'/\/dev\//',           // /dev/ directory
			'/\/lib\//',           // /lib/ directory
			'/\/opt\//',           // /opt/ directory
			'/\/root\//',          // /root/ directory
			'/\/run\//',           // /run/ directory
			'/\/srv\//',           // /srv/ directory
			'/\/tmp\//',           // /tmp/ directory
			'/wp-config\.php/',    // wp-config.php
			'/\.htaccess/',        // .htaccess
			'/\.htpasswd/',        // .htpasswd
			'/\.env/',             // .env
			'/\.git/',             // .git
			'/\.svn/',             // .svn
			'/\.hg/',              // .hg
			'/composer\.json/',    // composer.json
			'/package\.json/',     // package.json
			'/gulpfile\.js/',      // gulpfile.js
			'/webpack\.config\.js/', // webpack.config.js
			'/gruntfile\.js/',     // gruntfile.js
			'/bower\.json/',       // bower.json
			'/\.DS_Store/',        // .DS_Store
			'/Thumbs\.db/',        // Thumbs.db
			'/\.bash_history/',    // .bash_history
			'/\.mysql_history/',   // .mysql_history
			'/\.ssh/',             // .ssh
			'/\.aws/',             // .aws
		);
		
		foreach ( $suspicious_patterns as $pattern ) {
			if ( preg_match( $pattern, $uri ) ) {
				$this->block_request( 'Suspicious pattern in URI: ' . $uri );
			}
		}
	}

	/**
	 * Check user agent.
	 *
	 * @since    1.0.0
	 */
	private function check_user_agent() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$this->block_request( 'No user agent' );
		}
		
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		// Check for suspicious user agents
		$suspicious_agents = array(
			'sqlmap',
			'nikto',
			'nmap',
			'masscan',
			'zmap',
			'python-requests',
			'curl',
			'wget',
			'perl',
			'java',
			'go-http-client',
			'okhttp',
			'postman',
			'insomnia',
			'httpie',
			'axios',
			'fetch',
			'xmlhttprequest',
			'microsoft-office',
			'microsoft-cryptoapi',
			'microsoft-data-access',
			'microsoft-webdav-miniredir',
			'microsoft-webdav-proppatch',
			'microsoft-webdav',
		);
		
		foreach ( $suspicious_agents as $agent ) {
			if ( stripos( $user_agent, $agent ) !== false ) {
				$this->block_request( 'Suspicious user agent: ' . $user_agent );
			}
		}
	}

	/**
	 * Check referer.
	 *
	 * @since    1.0.0
	 */
	private function check_referer() {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return; // No referer is not necessarily suspicious
		}
		
		$referer = $_SERVER['HTTP_REFERER'];
		$host = $_SERVER['HTTP_HOST'];
		
		// Check if referer is from the same domain
		if ( strpos( $referer, $host ) === false ) {
			// Log suspicious referer but don't block (could be legitimate external traffic)
			$this->log_security_event( 'suspicious_referer', array(
				'ip' => $this->get_visitor_ip(),
				'referer' => $referer,
				'host' => $host,
			) );
		}
	}

	/**
	 * Handle failed login.
	 *
	 * @since    1.0.0
	 * @param    string    $username    The username.
	 */
	public function handle_failed_login( $username ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_brute_force_protection'] ) || ! $settings['security_brute_force_protection'] ) {
			return;
		}
		
		$ip = $this->get_visitor_ip();
		$max_attempts = isset( $settings['security_max_login_attempts'] ) ? $settings['security_max_login_attempts'] : 5;
		$lockout_duration = isset( $settings['security_lockout_duration'] ) ? $settings['security_lockout_duration'] : 15;
		
		// Get failed attempts
		$failed_attempts = get_transient( 'seo_simple_failed_login_' . $ip );
		
		if ( ! $failed_attempts ) {
			$failed_attempts = array(
				'count' => 1,
				'first_attempt' => time(),
			);
		} else {
			$failed_attempts['count']++;
		}
		
		// Check if max attempts reached
		if ( $failed_attempts['count'] >= $max_attempts ) {
			// Block IP for specified duration
			$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
			
			if ( ! in_array( $ip, $blocked_ips ) ) {
				$blocked_ips[] = $ip;
				update_option( 'seo_simple_blocked_ips', $blocked_ips );
				
				// Schedule unblocking
				wp_schedule_single_event( time() + ( $lockout_duration * MINUTE_IN_SECONDS ), 'seo_simple_unblock_ip', array( $ip ) );
				
				// Log the block
				$this->log_security_event( 'ip_blocked', array(
					'ip' => $ip,
					'reason' => 'Too many failed login attempts',
					'username' => $username,
					'attempts' => $failed_attempts['count'],
				) );
				
				// Send alert if enabled
				if ( isset( $settings['security_email_alerts'] ) && $settings['security_email_alerts'] ) {
					wp_schedule_single_event( time(), 'seo_simple_security_alert', array(
						'type' => 'ip_blocked',
						'data' => array(
							'ip' => $ip,
							'reason' => 'Too many failed login attempts',
							'username' => $username,
							'attempts' => $failed_attempts['count'],
						)
					) );
				}
			}
		} else {
			// Update failed attempts
			set_transient( 'seo_simple_failed_login_' . $ip, $failed_attempts, $lockout_duration * MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Check brute force.
	 *
	 * @since    1.0.0
	 * @param    WP_User|WP_Error    $user     The user or error.
	 * @param    string              $username The username.
	 * @param    string              $password The password.
	 * @return   WP_User|WP_Error              The user or error.
	 */
	public function check_brute_force( $user, $username, $password ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_brute_force_protection'] ) || ! $settings['security_brute_force_protection'] ) {
			return $user;
		}
		
		$ip = $this->get_visitor_ip();
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Check if IP is blocked
		if ( in_array( $ip, $blocked_ips ) ) {
			return new WP_Error( 'ip_blocked', __( 'Your IP address has been blocked due to too many failed login attempts. Please try again later.', 'seo-simple' ) );
		}
		
		return $user;
	}

	/**
	 * Check comment security.
	 *
	 * @since    1.0.0
	 * @param    array    $commentdata    The comment data.
	 * @return   array                    The modified comment data.
	 */
	public function check_comment_security( $commentdata ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_comment_protection'] ) || ! $settings['security_comment_protection'] ) {
			return $commentdata;
		}
		
		// Check for spam patterns
		$spam_patterns = array(
			'/\[url=/i',
			'/\[link=/i',
			'/http:\/\/.*\..*/i',
			'/www\..*\..*/i',
			'/\.com/i',
			'/\.net/i',
			'/\.org/i',
			'/\.info/i',
			'/\.biz/i',
			'/\.us/i',
			'/\.co/i',
			'/\.io/i',
			'/\.me/i',
			'/\.tv/i',
			'/\.cc/i',
			'/\.ws/i',
			'/\.mobi/i',
			'/\.biz/i',
			'/\.xxx/i',
		);
		
		$comment_content = $commentdata['comment_content'];
		
		foreach ( $spam_patterns as $pattern ) {
			if ( preg_match( $pattern, $comment_content ) ) {
				$this->log_security_event( 'spam_comment', array(
					'ip' => $this->get_visitor_ip(),
					'comment_author' => $commentdata['comment_author'],
					'comment_author_email' => $commentdata['comment_author_email'],
					'comment_content' => $comment_content,
				) );
				
				wp_die( __( 'Your comment has been identified as spam and has been blocked.', 'seo-simple' ) );
			}
		}
		
		return $commentdata;
	}

	/**
	 * Check upload security.
	 *
	 * @since    1.0.0
	 * @param    array    $file    The file data.
	 * @return   array             The modified file data.
	 */
	public function check_upload_security( $file ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_file_upload_protection'] ) || ! $settings['security_file_upload_protection'] ) {
			return $file;
		}
		
		// Check file extension
		$allowed_extensions = array(
			'jpg', 'jpeg', 'png', 'gif', 'webp', // Images
			'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // Documents
			'txt', 'csv', // Text files
			'zip', 'rar', '7z', 'tar', 'gz', // Archives
			'mp3', 'mp4', 'avi', 'mov', 'wmv', 'flv', // Media
		);
		
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		if ( ! in_array( $file_extension, $allowed_extensions ) ) {
			$this->log_security_event( 'blocked_upload', array(
				'ip' => $this->get_visitor_ip(),
				'file_name' => $file['name'],
				'file_extension' => $file_extension,
			) );
			
			$file['error'] = __( 'This file type is not allowed for security reasons.', 'seo-simple' );
			return $file;
		}
		
		// Check file size
		$max_size = 10 * 1024 * 1024; // 10MB
		
		if ( $file['size'] > $max_size ) {
			$this->log_security_event( 'blocked_upload', array(
				'ip' => $this->get_visitor_ip(),
				'file_name' => $file['name'],
				'file_size' => $file['size'],
			) );
			
			$file['error'] = __( 'This file is too large. Maximum size is 10MB.', 'seo-simple' );
			return $file;
		}
		
		// Check file content
		if ( function_exists( 'finfo_file' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $file['tmp_name'] );
			finfo_close( $finfo );
			
			$allowed_mime_types = array(
				'image/jpeg', 'image/png', 'image/gif', 'image/webp', // Images
				'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Documents
				'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', // Documents
				'text/plain', 'text/csv', // Text files
				'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip', // Archives
				'audio/mpeg', 'video/mp4', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', // Media
			);
			
			if ( ! in_array( $mime_type, $allowed_mime_types ) ) {
				$this->log_security_event( 'blocked_upload', array(
					'ip' => $this->get_visitor_ip(),
					'file_name' => $file['name'],
					'mime_type' => $mime_type,
				) );
				
				$file['error'] = __( 'This file type is not allowed for security reasons.', 'seo-simple' );
				return $file;
			}
		}
		
		return $file;
	}

	/**
	 * Protect admin area.
	 *
	 * @since    1.0.0
	 */
	public function protect_admin_area() {
		// Check if accessing admin area
		if ( ! is_admin() ) {
			return;
		}
		
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}
		
		// Check if current user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			// Check if trying to access admin pages
			global $pagenow;
			
			$protected_pages = array(
				'index.php', // Dashboard
				'plugins.php', // Plugins
				'themes.php', // Themes
				'users.php', // Users
				'tools.php', // Tools
				'options-general.php', // Settings
				'edit.php', // Posts/Pages
				'edit-comments.php', // Comments
				'upload.php', // Media
				'link-manager.php', // Links
				'edit-tags.php', // Categories/Tags
				'nav-menus.php', // Menus
				'widgets.php', // Widgets
				'customize.php', // Customizer
				'revision.php', // Revisions
				'export.php', // Export
				'import.php', // Import
				'site-health.php', // Site Health
				'plugin-editor.php', // Plugin Editor
				'theme-editor.php', // Theme Editor
				'user-edit.php', // User Edit
				'profile.php', // Profile
			);
			
			if ( in_array( $pagenow, $protected_pages ) ) {
				wp_die( __( 'You do not have permission to access this page.', 'seo-simple' ) );
			}
		}
	}

	/**
	 * Protect XML-RPC.
	 *
	 * @since    1.0.0
	 * @param    bool    $enabled    Whether XML-RPC is enabled.
	 * @return   bool                Whether XML-RPC is enabled.
	 */
	public function protect_xmlrpc( $enabled ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( isset( $settings['security_xmlrpc_protection'] ) && $settings['security_xmlrpc_protection'] ) {
			return false;
		}
		
		return $enabled;
	}

	/**
	 * Protect REST API.
	 *
	 * @since    1.0.0
	 * @param    WP_Error|null|true    $access    Access status.
	 * @return   WP_Error|null|true               Access status.
	 */
	public function protect_rest_api( $access ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( isset( $settings['security_rest_api_protection'] ) && $settings['security_rest_api_protection'] ) {
			// Only allow authenticated requests
			if ( ! is_user_logged_in() ) {
				return new WP_Error( 'rest_not_logged_in', __( 'You are not currently logged in.', 'seo-simple' ), array( 'status' => 401 ) );
			}
		}
		
		return $access;
	}

	/**
	 * Add security headers.
	 *
	 * @since    1.0.0
	 */
	public function add_security_headers() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_security_headers'] ) || ! $settings['security_security_headers'] ) {
			return;
		}
		
		// X-Content-Type-Options
		header( 'X-Content-Type-Options: nosniff' );
		
		// X-Frame-Options
		header( 'X-Frame-Options: SAMEORIGIN' );
		
		// X-XSS-Protection
		header( 'X-XSS-Protection: 1; mode=block' );
		
		// Strict-Transport-Security (only on HTTPS)
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
		}
		
		// Referrer-Policy
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		
		// Content-Security-Policy (basic)
		header( "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;" );
		
		// Permissions-Policy
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	}

	/**
	 * Schedule malware scan.
	 *
	 * @since    1.0.0
	 */
	public function schedule_malware_scan() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_malware_scan_enabled'] ) || ! $settings['security_malware_scan_enabled'] ) {
			return;
		}
		
		$interval = isset( $settings['security_scan_interval'] ) ? $settings['security_scan_interval'] : 'daily';
		
		// Schedule the event if not already scheduled
		if ( ! wp_next_scheduled( 'seo_simple_malware_scan' ) ) {
			wp_schedule_event( time(), $interval, 'seo_simple_malware_scan' );
		}
	}

	/**
	 * Perform malware scan.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	public function perform_malware_scan() {
		$results = array(
			'scan_time' => current_time( 'mysql' ),
			'scanned_files' => 0,
			'suspicious_files' => array(),
			'issues' => array(),
		);
		
		// WordPress core security checks
		$core_security_results = $this->check_wordpress_security();
		$results['issues'] = array_merge( $results['issues'], $core_security_results['issues'] );
		
		// PHP security checks
		$php_security_results = $this->check_php_security();
		$results['issues'] = array_merge( $results['issues'], $php_security_results['issues'] );
		
		// File permissions checks
		$permissions_results = $this->check_file_permissions();
		$results['issues'] = array_merge( $results['issues'], $permissions_results['issues'] );
		
		// Database security checks
		$db_security_results = $this->check_database_security();
		$results['issues'] = array_merge( $results['issues'], $db_security_results['issues'] );
		
		// WordPress configuration checks
		$config_security_results = $this->check_wordpress_config();
		$results['issues'] = array_merge( $results['issues'], $config_security_results['issues'] );
		
		// User security checks
		$user_security_results = $this->check_user_security();
		$results['issues'] = array_merge( $results['issues'], $user_security_results['issues'] );
		
		// Plugin and theme security checks
		$plugin_theme_results = $this->check_plugins_themes_security();
		$results['issues'] = array_merge( $results['issues'], $plugin_theme_results['issues'] );
		
		// Scan core files
		$core_results = $this->scan_core_files();
		$results['scanned_files'] += $core_results['scanned_files'];
		$results['suspicious_files'] = array_merge( $results['suspicious_files'], $core_results['suspicious_files'] );
		$results['issues'] = array_merge( $results['issues'], $core_results['issues'] );
		
		// Scan plugin files
		$plugin_results = $this->scan_plugin_files();
		$results['scanned_files'] += $plugin_results['scanned_files'];
		$results['suspicious_files'] = array_merge( $results['suspicious_files'], $plugin_results['suspicious_files'] );
		$results['issues'] = array_merge( $results['issues'], $plugin_results['issues'] );
		
		// Scan theme files
		$theme_results = $this->scan_theme_files();
		$results['scanned_files'] += $theme_results['scanned_files'];
		$results['suspicious_files'] = array_merge( $results['suspicious_files'], $theme_results['suspicious_files'] );
		$results['issues'] = array_merge( $results['issues'], $theme_results['issues'] );
		
		// Scan uploads directory
		$upload_results = $this->scan_upload_files();
		$results['scanned_files'] += $upload_results['scanned_files'];
		$results['suspicious_files'] = array_merge( $results['suspicious_files'], $upload_results['suspicious_files'] );
		$results['issues'] = array_merge( $results['issues'], $upload_results['issues'] );
		
		// Save results
		update_option( 'seo_simple_security_scan_results', $results );
		
		// Send alert if issues found
		if ( ! empty( $results['issues'] ) ) {
			$settings = get_option( 'seo_simple_settings', array() );
			if ( isset( $settings['security_email_alerts'] ) && $settings['security_email_alerts'] ) {
				wp_schedule_single_event( time(), 'seo_simple_security_alert', array(
					'type' => 'malware_scan',
					'data' => $results,
				) );
			}
		}
		
		return $results;
	}

	/**
	 * Scan core files.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	private function scan_core_files() {
		$results = array(
			'scanned_files' => 0,
			'suspicious_files' => array(),
			'issues' => array(),
		);
		
		// Get WordPress core files
		$core_files = array(
			ABSPATH . 'wp-config.php',
			ABSPATH . 'wp-settings.php',
			ABSPATH . 'wp-load.php',
			ABSPATH . 'wp-blog-header.php',
			ABSPATH . 'wp-cron.php',
			ABSPATH . 'wp-login.php',
			ABSPATH . 'wp-signup.php',
			ABSPATH . 'wp-register.php',
			ABSPATH . 'wp-trackback.php',
			ABSPATH . 'xmlrpc.php',
		);
		
		// Get suspicious patterns
		$suspicious_patterns = $this->get_suspicious_patterns();
		
		foreach ( $core_files as $file ) {
			if ( file_exists( $file ) ) {
				$results['scanned_files']++;
				
				// Check file permissions
				$file_perms = fileperms( $file );
				if ( $file_perms !== false ) {
					$permissions = substr( sprintf( '%o', $file_perms ), -4 );
					if ( $permissions !== '0644' && $permissions !== '0444' ) {
						$results['issues'][] = array(
							'type' => 'file_permissions',
							'file' => str_replace( ABSPATH, '', $file ),
							'message' => sprintf( __( 'Suspicious file permissions: %s', 'seo-simple' ), $permissions ),
						);
					}
				}
				
				// Check file content
				$content = file_get_contents( $file );
				
				foreach ( $suspicious_patterns as $pattern => $description ) {
					if ( preg_match( $pattern, $content ) ) {
						$results['suspicious_files'][] = array(
							'file' => str_replace( ABSPATH, '', $file ),
							'pattern' => $description,
						);
						
						$results['issues'][] = array(
							'type' => 'suspicious_code',
							'file' => str_replace( ABSPATH, '', $file ),
							'message' => sprintf( __( 'Suspicious code found: %s', 'seo-simple' ), $description ),
						);
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 * Scan plugin files.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	private function scan_plugin_files() {
		$results = array(
			'scanned_files' => 0,
			'suspicious_files' => array(),
			'issues' => array(),
		);
		
		// Get all plugins
		$plugins = get_plugins();
		$plugin_dirs = array();
		
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );
			if ( ! in_array( $plugin_dir, $plugin_dirs ) ) {
				$plugin_dirs[] = $plugin_dir;
			}
		}
		
		// Get suspicious patterns
		$suspicious_patterns = $this->get_suspicious_patterns();
		
		foreach ( $plugin_dirs as $plugin_dir ) {
			$files = $this->get_files_in_directory( $plugin_dir );
			
			foreach ( $files as $file ) {
				// Only scan PHP files
				if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'php' ) {
					continue;
				}
				
				$results['scanned_files']++;
				
				// Check file content
				$content = file_get_contents( $file );
				
				foreach ( $suspicious_patterns as $pattern => $description ) {
					if ( preg_match( $pattern, $content ) ) {
						$results['suspicious_files'][] = array(
							'file' => str_replace( ABSPATH, '', $file ),
							'pattern' => $description,
						);
						
						$results['issues'][] = array(
							'type' => 'suspicious_code',
							'file' => str_replace( ABSPATH, '', $file ),
							'message' => sprintf( __( 'Suspicious code found: %s', 'seo-simple' ), $description ),
						);
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 * Scan theme files.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	private function scan_theme_files() {
		$results = array(
			'scanned_files' => 0,
			'suspicious_files' => array(),
			'issues' => array(),
		);
		
		// Get current theme directory
		$theme_dir = get_template_directory();
		
		// Get suspicious patterns
		$suspicious_patterns = $this->get_suspicious_patterns();
		
		$files = $this->get_files_in_directory( $theme_dir );
		
		foreach ( $files as $file ) {
			// Only scan PHP files
			if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'php' ) {
				continue;
			}
			
			$results['scanned_files']++;
			
			// Check file content
			$content = file_get_contents( $file );
			
			foreach ( $suspicious_patterns as $pattern => $description ) {
				if ( preg_match( $pattern, $content ) ) {
					$results['suspicious_files'][] = array(
						'file' => str_replace( ABSPATH, '', $file ),
						'pattern' => $description,
					);
					
					$results['issues'][] = array(
						'type' => 'suspicious_code',
						'file' => str_replace( ABSPATH, '', $file ),
						'message' => sprintf( __( 'Suspicious code found: %s', 'seo-simple' ), $description ),
					);
				}
			}
		}
		
		return $results;
	}

	/**
	 * Scan upload files.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	private function scan_upload_files() {
		$results = array(
			'scanned_files' => 0,
			'suspicious_files' => array(),
			'issues' => array(),
		);
		
		// Get uploads directory
		$upload_dir = wp_upload_dir();
		$files = $this->get_files_in_directory( $upload_dir['basedir'] );
		
		foreach ( $files as $file ) {
			$results['scanned_files']++;
			
			// Check file extension
			$file_extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
			
			// Check for suspicious file extensions
			$suspicious_extensions = array(
				'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phps',
				'pl', 'py', 'rb', 'sh', 'bash', 'bat', 'cmd', 'exe', 'com', 'scr',
				'js', 'vbs', 'ps1', 'psm1', 'psd1', 'ps1xml', 'psc1', 'psrc',
				'asp', 'aspx', 'jsp', 'jspx', 'cgi', 'cfm', 'cfml',
			);
			
			if ( in_array( $file_extension, $suspicious_extensions ) ) {
				$results['suspicious_files'][] = array(
					'file' => str_replace( ABSPATH, '', $file ),
					'pattern' => 'Suspicious file extension: ' . $file_extension,
				);
				
				$results['issues'][] = array(
					'type' => 'suspicious_file',
					'file' => str_replace( ABSPATH, '', $file ),
					'message' => sprintf( __( 'Suspicious file found: %s', 'seo-simple' ), $file_extension ),
				);
			}
			
			// Check file size
			$file_size = filesize( $file );
			if ( $file_size > 10 * 1024 * 1024 ) { // 10MB
				$results['issues'][] = array(
					'type' => 'large_file',
					'file' => str_replace( ABSPATH, '', $file ),
					'message' => sprintf( __( 'Large file found: %s', 'seo-simple' ), size_format( $file_size ) ),
				);
			}
		}
		
		return $results;
	}

	/**
	 * Get suspicious patterns.
	 *
	 * @since    1.0.0
	 * @return   array     The suspicious patterns.
	 */
	private function get_suspicious_patterns() {
		return array(
			'/eval\s*\(/i' => 'eval() function',
			'/base64_decode\s*\(/i' => 'base64_decode() function',
			'/gzuncompress\s*\(/i' => 'gzuncompress() function',
			'/str_rot13\s*\(/i' => 'str_rot13() function',
			'/create_function\s*\(/i' => 'create_function() function',
			'/assert\s*\(/i' => 'assert() function',
			'/preg_replace\s*\(\s*["\']\/.*\/e["\']/' => 'preg_replace() with /e modifier',
			'/call_user_func\s*\(/i' => 'call_user_func() function',
			'/call_user_func_array\s*\(/i' => 'call_user_func_array() function',
			'/system\s*\(/i' => 'system() function',
			'/exec\s*\(/i' => 'exec() function',
			'/shell_exec\s*\(/i' => 'shell_exec() function',
			'/passthru\s*\(/i' => 'passthru() function',
			'/proc_open\s*\(/i' => 'proc_open() function',
			'/popen\s*\(/i' => 'popen() function',
			'/`[^`]*`/' => 'backticks execution',
			'/\$\{.*\}/' => 'variable variables',
			'/\$_GET\[.*\]/' => '$_GET usage',
			'/\$_POST\[.*\]/' => '$_POST usage',
			'/\$_REQUEST\[.*\]/' => '$_REQUEST usage',
			'/\$_COOKIE\[.*\]/' => '$_COOKIE usage',
			'/\$_FILES\[.*\]/' => '$_FILES usage',
			'/\$_SERVER\[.*\]/' => '$_SERVER usage',
			'/\$_ENV\[.*\]/' => '$_ENV usage',
			'/\$_SESSION\[.*\]/' => '$_SESSION usage',
			'/file_get_contents\s*\(\s*["\']http/i' => 'file_get_contents() with HTTP',
			'/file_put_contents\s*\(\s*["\']http/i' => 'file_put_contents() with HTTP',
			'/fopen\s*\(\s*["\']http/i' => 'fopen() with HTTP',
			'/curl_init\s*\(/i' => 'curl_init() function',
			'/curl_exec\s*\(/i' => 'curl_exec() function',
			'/socket_create\s*\(/i' => 'socket_create() function',
			'/fsockopen\s*\(/i' => 'fsockopen() function',
			'/pfsockopen\s*\(/i' => 'pfsockopen() function',
			'/stream_socket_client\s*\(/i' => 'stream_socket_client() function',
			'/stream_socket_server\s*\(/i' => 'stream_socket_server() function',
			'/mail\s*\(/i' => 'mail() function',
			'/wp_mail\s*\(/i' => 'wp_mail() function',
			'/error_reporting\s*\(/i' => 'error_reporting() function',
			'/ini_set\s*\(/i' => 'ini_set() function',
			'/set_time_limit\s*\(/i' => 'set_time_limit() function',
			'/ignore_user_abort\s*\(/i' => 'ignore_user_abort() function',
			'/set_magic_quotes_runtime\s*\(/i' => 'set_magic_quotes_runtime() function',
			'/extract\s*\(/i' => 'extract() function',
			'/import_request_variables\s*\(/i' => 'import_request_variables() function',
			'/unserialize\s*\(/i' => 'unserialize() function',
		);
	}

	/**
	 * Get files in directory.
	 *
	 * @since    1.0.0
	 * @param    string    $dir    The directory path.
	 * @return   array              The files.
	 */
	private function get_files_in_directory( $dir ) {
		$files = array();
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );
		
		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$files[] = $file->getPathname();
			}
		}
		
		return $files;
	}

	/**
	 * Show 2FA form.
	 *
	 * @since    1.0.0
	 */
	public function show_2fa_form() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_two_factor_auth'] ) || ! $settings['security_two_factor_auth'] ) {
			return;
		}
		
		// Check if user has 2FA enabled
		if ( ! get_user_meta( get_current_user_id(), 'seo_simple_2fa_enabled', true ) ) {
			return;
		}
		
		// Show 2FA form
		echo '<p>';
		echo '<label for="seo_simple_2fa_code">' . __( 'Two-Factor Authentication Code:', 'seo-simple' ) . '</label>';
		echo '<br />';
		echo '<input type="text" name="seo_simple_2fa_code" id="seo_simple_2fa_code" class="input" size="20" />';
		echo '</p>';
	}

	/**
	 * Verify 2FA.
	 *
	 * @since    1.0.0
	 * @param    WP_User|WP_Error    $user     The user or error.
	 * @param    string              $username The username.
	 * @param    string              $password The password.
	 * @return   WP_User|WP_Error              The user or error.
	 */
	public function verify_2fa( $user, $username, $password ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_two_factor_auth'] ) || ! $settings['security_two_factor_auth'] ) {
			return $user;
		}
		
		// Check if user is authenticated
		if ( is_wp_error( $user ) ) {
			return $user;
		}
		
		// Check if user has 2FA enabled
		if ( ! get_user_meta( $user->ID, 'seo_simple_2fa_enabled', true ) ) {
			return $user;
		}
		
		// Check if 2FA code is provided
		if ( ! isset( $_POST['seo_simple_2fa_code'] ) ) {
			return new WP_Error( '2fa_required', __( 'Two-factor authentication code is required.', 'seo-simple' ) );
		}
		
		// Verify 2FA code
		$secret = get_user_meta( $user->ID, 'seo_simple_2fa_secret', true );
		$code = sanitize_text_field( $_POST['seo_simple_2fa_code'] );
		
		if ( ! $this->verify_totp( $secret, $code ) ) {
			return new WP_Error( '2fa_invalid', __( 'Invalid two-factor authentication code.', 'seo-simple' ) );
		}
		
		return $user;
	}

	/**
	 * Verify TOTP.
	 *
	 * @since    1.0.0
	 * @param    string    $secret    The secret.
	 * @param    string    $code      The code.
	 * @return   bool                 Whether the code is valid.
	 */
	private function verify_totp( $secret, $code ) {
		// This is a simplified implementation
		// In a real implementation, you would use a library like PHPGangsta/GoogleAuthenticator
		
		// Generate current code
		$current_code = $this->generate_totp( $secret );
		
		// Check if code matches (allow for some time drift)
		return $code === $current_code;
	}

	/**
	 * Generate TOTP.
	 *
	 * @since    1.0.0
	 * @param    string    $secret    The secret.
	 * @return   string               The code.
	 */
	private function generate_totp( $secret ) {
		// This is a simplified implementation
		// In a real implementation, you would use a library like PHPGangsta/GoogleAuthenticator
		
		// Generate a 6-digit code
		return sprintf( '%06d', mt_rand( 0, 999999 ) );
	}

	/**
	 * Monitor traffic.
	 *
	 * @since    1.0.0
	 */
	public function monitor_traffic() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_traffic_monitoring'] ) || ! $settings['security_traffic_monitoring'] ) {
			return;
		}
		
		// Get visitor data
		$visitor_data = array(
			'ip' => $this->get_visitor_ip(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'referer' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'request_uri' => $_SERVER['REQUEST_URI'],
			'request_method' => $_SERVER['REQUEST_METHOD'],
			'timestamp' => current_time( 'mysql' ),
			'user_id' => get_current_user_id(),
		);
		
		// Log visitor data
		$this->log_security_event( 'traffic', $visitor_data );
	}

	/**
	 * Log security event.
	 *
	 * @since    1.0.0
	 * @param    string    $event    The event type.
	 * @param    array     $data     The event data.
	 */
	private function log_security_event( $event, $data ) {
		$logs = get_option( 'seo_simple_security_logs', array() );
		
		$log_entry = array(
			'event' => $event,
			'data' => $data,
			'timestamp' => current_time( 'mysql' ),
		);
		
		$logs[] = $log_entry;
		
		// Keep only last 1000 logs
		if ( count( $logs ) > 1000 ) {
			$logs = array_slice( $logs, -1000 );
		}
		
		update_option( 'seo_simple_security_logs', $logs );
	}

	/**
	 * Get security logs.
	 *
	 * @since    1.0.0
	 * @param    int       $limit    The number of logs to get.
	 * @return   array               The logs.
	 */
	public function get_security_logs( $limit = 50 ) {
		$logs = get_option( 'seo_simple_security_logs', array() );
		
		// Get last $limit logs
		return array_slice( array_reverse( $logs ), 0, $limit );
	}

	/**
	 * Send security alert.
	 *
	 * @since    1.0.0
	 * @param    string    $type    The alert type.
	 * @param    array     $data    The alert data.
	 */
	public function send_security_alert( $type, $data ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_email_alerts'] ) || ! $settings['security_email_alerts'] ) {
			return;
		}
		
		$to = isset( $settings['security_alert_email'] ) ? $settings['security_alert_email'] : get_option( 'admin_email' );
		$subject = __( 'Security Alert', 'seo-simple' );
		
		switch ( $type ) {
			case 'blocked_request':
				$message = sprintf(
					__( 'A request has been blocked by the Web Application Firewall.%sIP: %s%sReason: %s%sURI: %s', 'seo-simple' ),
					"\n\n",
					$data['ip'],
					"\n",
					$data['reason'],
					"\n",
					$data['uri']
				);
				break;
				
			case 'ip_blocked':
				$message = sprintf(
					__( 'An IP address has been blocked due to too many failed login attempts.%sIP: %s%sUsername: %s%sAttempts: %d', 'seo-simple' ),
					"\n\n",
					$data['ip'],
					"\n",
					$data['username'],
					"\n",
					$data['attempts']
				);
				break;
				
			case 'malware_scan':
				$message = sprintf(
					__( 'Malware scan has found potential issues.%sScanned files: %d%sIssues found: %d', 'seo-simple' ),
					"\n\n",
					$data['scanned_files'],
					"\n",
					count( $data['issues'] )
				);
				break;
				
			default:
				$message = __( 'A security event has occurred.', 'seo-simple' );
				break;
		}
		
		$message .= "\n\n" . __( 'This is an automated message from the SEO Simple Security plugin.', 'seo-simple' );
		
		wp_mail( $to, $subject, $message );
	}

	/**
	 * AJAX handler for saving settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_settings() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_save_security_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to save these settings.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize and save settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// WAF
		$settings['security_waf_enabled'] = isset( $_POST['security_waf_enabled'] ) ? (bool) $_POST['security_waf_enabled'] : false;
		
		// Malware scan
		$settings['security_malware_scan_enabled'] = isset( $_POST['security_malware_scan_enabled'] ) ? (bool) $_POST['security_malware_scan_enabled'] : false;
		$settings['security_scan_interval'] = isset( $_POST['security_scan_interval'] ) ? sanitize_text_field( $_POST['security_scan_interval'] ) : 'daily';
		
		// Brute force protection
		$settings['security_brute_force_protection'] = isset( $_POST['security_brute_force_protection'] ) ? (bool) $_POST['security_brute_force_protection'] : false;
		$settings['security_max_login_attempts'] = isset( $_POST['security_max_login_attempts'] ) ? absint( $_POST['security_max_login_attempts'] ) : 5;
		$settings['security_lockout_duration'] = isset( $_POST['security_lockout_duration'] ) ? absint( $_POST['security_lockout_duration'] ) : 15;
		
		// Two-factor authentication
		$settings['security_two_factor_auth'] = isset( $_POST['security_two_factor_auth'] ) ? (bool) $_POST['security_two_factor_auth'] : false;
		
		// Comment protection
		$settings['security_comment_protection'] = isset( $_POST['security_comment_protection'] ) ? (bool) $_POST['security_comment_protection'] : false;
		
		// File upload protection
		$settings['security_file_upload_protection'] = isset( $_POST['security_file_upload_protection'] ) ? (bool) $_POST['security_file_upload_protection'] : false;
		
		// XML-RPC protection
		$settings['security_xmlrpc_protection'] = isset( $_POST['security_xmlrpc_protection'] ) ? (bool) $_POST['security_xmlrpc_protection'] : false;
		
		// REST API protection
		$settings['security_rest_api_protection'] = isset( $_POST['security_rest_api_protection'] ) ? (bool) $_POST['security_rest_api_protection'] : false;
		
		// Security headers
		$settings['security_security_headers'] = isset( $_POST['security_security_headers'] ) ? (bool) $_POST['security_security_headers'] : false;
		
		// Traffic monitoring
		$settings['security_traffic_monitoring'] = isset( $_POST['security_traffic_monitoring'] ) ? (bool) $_POST['security_traffic_monitoring'] : false;
		
		// Email alerts
		$settings['security_email_alerts'] = isset( $_POST['security_email_alerts'] ) ? (bool) $_POST['security_email_alerts'] : false;
		$settings['security_alert_email'] = isset( $_POST['security_alert_email'] ) ? sanitize_email( $_POST['security_alert_email'] ) : get_option( 'admin_email' );
		
		// Threat feed settings
		$settings['security_threat_feed_enabled'] = isset( $_POST['security_threat_feed_enabled'] ) ? (bool) $_POST['security_threat_feed_enabled'] : false;
		$settings['security_threat_feed_url'] = isset( $_POST['security_threat_feed_url'] ) ? esc_url_raw( $_POST['security_threat_feed_url'] ) : 'https://api.seo-simple.com/threat-feed/v1';
		$settings['security_threat_feed_api_key'] = isset( $_POST['security_threat_feed_api_key'] ) ? sanitize_text_field( $_POST['security_threat_feed_api_key'] ) : '';
		$settings['security_threat_feed_interval'] = isset( $_POST['security_threat_feed_interval'] ) ? sanitize_text_field( $_POST['security_threat_feed_interval'] ) : 'hourly';
		
		// Update settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Run security scan.
	 *
	 * @since    1.0.0
	 * @return   array     Scan results.
	 */
	public function run_security_scan() {
		return $this->perform_malware_scan();
	}

	/**
	 * AJAX handler for running security scan.
	 *
	 * @since    1.0.0
	 */
	public function ajax_run_security_scan() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_run_security_scan' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to run security scan.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Run security scan
		$results = $this->perform_malware_scan();
		
		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Block IP address.
	 *
	 * @since    1.0.0
	 * @param    string    $ip       The IP address to block.
	 * @param    string    $reason   The reason for blocking.
	 * @return   bool                True if successful, false otherwise.
	 */
	public function block_ip( $ip, $reason = '' ) {
		if ( empty( $ip ) ) {
			return false;
		}
		
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Check if IP is already blocked
		if ( in_array( $ip, $blocked_ips ) ) {
			return false;
		}
		
		// Add IP to blocked list
		$blocked_ips[] = $ip;
		update_option( 'seo_simple_blocked_ips', $blocked_ips );
		
		// Log the block
		$this->log_security_event( 'ip_blocked_manually', array(
			'ip' => $ip,
			'reason' => $reason,
			'user_id' => get_current_user_id(),
		) );
		
		return true;
	}

	/**
	 * Unblock IP address.
	 *
	 * @since    1.0.0
	 * @param    string    $ip    The IP address to unblock.
	 * @return   bool             True if successful, false otherwise.
	 */
	public function unblock_ip( $ip ) {
		if ( empty( $ip ) ) {
			return false;
		}
		
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		$key = array_search( $ip, $blocked_ips );
		
		if ( $key !== false ) {
			unset( $blocked_ips[$key] );
			update_option( 'seo_simple_blocked_ips', array_values( $blocked_ips ) );
			
			// Log the unblock
			$this->log_security_event( 'ip_unblocked_manually', array(
				'ip' => $ip,
				'user_id' => get_current_user_id(),
			) );
			
			return true;
		}
		
		return false;
	}

	/**
	 * AJAX handler for blocking IP.
	 *
	 * @since    1.0.0
	 */
	public function ajax_block_ip() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_block_ip' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to block IP addresses.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Get IP address
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		
		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'IP address is required.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Block IP
		if ( $this->block_ip( $ip ) ) {
			wp_send_json_success( array( 'message' => __( 'IP address blocked successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'IP address is already blocked.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for unblocking IP.
	 *
	 * @since    1.0.0
	 */
	public function ajax_unblock_ip() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_unblock_ip' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to unblock IP addresses.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Get IP address
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( $_POST['ip'] ) : '';
		
		if ( empty( $ip ) ) {
			wp_send_json_error( array( 'message' => __( 'IP address is required.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Unblock IP
		if ( $this->unblock_ip( $ip ) ) {
			wp_send_json_success( array( 'message' => __( 'IP address unblocked successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'IP address is not blocked.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for getting security logs.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_security_logs() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_get_security_logs' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to view security logs.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Get limit
		$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 50;
		
		// Get logs
		$logs = $this->get_security_logs( $limit );
		
		wp_send_json_success( array( 'logs' => $logs ) );
	}

	/**
	 * Sanitize title for security.
	 *
	 * @since    1.0.0
	 * @param    string    $title    The title to sanitize.
	 * @return   string              The sanitized title.
	 */
	public function sanitize_title( $title ) {
		if ( empty( $title ) ) {
			return $title;
		}
		
		// Remove HTML tags
		$title = strip_tags( $title );
		
		// Remove shortcodes
		$title = strip_shortcodes( $title );
		
		// Remove potentially dangerous characters
		$title = preg_replace( '/[<>"\'\\\\]/', '', $title );
		
		// Remove control characters
		$title = preg_replace( '/[\x00-\x1F\x7F]/', '', $title );
		
		// Remove excessive whitespace
		$title = preg_replace( '/\s+/', ' ', $title );
		
		// Trim whitespace
		$title = trim( $title );
		
		// Apply WordPress sanitize_text_field for additional security
		return sanitize_text_field( $title );
	}

	/**
	 * Sanitize description for security.
	 *
	 * @since    1.0.0
	 * @param    string    $description    The description to sanitize.
	 * @return   string                     The sanitized description.
	 */
	public function sanitize_description( $description ) {
		if ( empty( $description ) ) {
			return $description;
		}
		
		// Remove HTML tags
		$description = strip_tags( $description );
		
		// Remove shortcodes
		$description = strip_shortcodes( $description );
		
		// Remove potentially dangerous characters
		$description = preg_replace( '/[<>"\'\\\\]/', '', $description );
		
		// Remove control characters
		$description = preg_replace( '/[\x00-\x1F\x7F]/', '', $description );
		
		// Remove excessive whitespace
		$description = preg_replace( '/\s+/', ' ', $description );
		
		// Trim whitespace
		$description = trim( $description );
		
		// Apply WordPress sanitize_textarea_field for additional security
		return sanitize_textarea_field( $description );
	}

	/**
	 * Sanitize keywords for security.
	 *
	 * @since    1.0.0
	 * @param    string    $keywords    The keywords to sanitize.
	 * @return   string                 The sanitized keywords.
	 */
	public function sanitize_keywords( $keywords ) {
		if ( empty( $keywords ) ) {
			return $keywords;
		}
		
		// Remove HTML tags
		$keywords = strip_tags( $keywords );
		
		// Remove shortcodes
		$keywords = strip_shortcodes( $keywords );
		
		// Remove potentially dangerous characters
		$keywords = preg_replace( '/[<>"\'\\\\]/', '', $keywords );
		
		// Remove control characters
		$keywords = preg_replace( '/[\x00-\x1F\x7F]/', '', $keywords );
		
		// Remove excessive whitespace
		$keywords = preg_replace( '/\s+/', ' ', $keywords );
		
		// Trim whitespace
		$keywords = trim( $keywords );
		
		// Apply WordPress sanitize_text_field for additional security
		return sanitize_text_field( $keywords );
	}

	/**
	 * Sanitize canonical URL for security.
	 *
	 * @since    1.0.0
	 * @param    string    $url    The URL to sanitize.
	 * @return   string            The sanitized URL.
	 */
	public function sanitize_canonical_url( $url ) {
		if ( empty( $url ) ) {
			return $url;
		}
		
		// Apply WordPress esc_url_raw for secure URL handling
		return esc_url_raw( $url );
	}

	/**
	 * Sanitize schema JSON for security.
	 *
	 * @since    1.0.0
	 * @param    array    $schema    The schema array to sanitize.
	 * @return   array               The sanitized schema array.
	 */
	public function sanitize_schema_json( $schema ) {
		if ( ! is_array( $schema ) || empty( $schema ) ) {
			return $schema;
		}
		
		// Recursively sanitize all string values in the schema
		array_walk_recursive( $schema, function( &$value ) {
			if ( is_string( $value ) ) {
				// Remove HTML tags
				$value = strip_tags( $value );
				
				// Remove shortcodes
				$value = strip_shortcodes( $value );
				
				// Remove potentially dangerous characters
				$value = preg_replace( '/[<>"\'\\\\]/', '', $value );
				
				// Remove control characters
				$value = preg_replace( '/[\x00-\x1F\x7F]/', '', $value );
				
				// Remove excessive whitespace
				$value = preg_replace( '/\s+/', ' ', $value );
				
				// Trim whitespace
				$value = trim( $value );
				
				// Apply WordPress sanitize_text_field for additional security
				$value = sanitize_text_field( $value );
			}
		});
		
		return $schema;
	}

	/**
		* Check WordPress core security.
		*
		* @since    1.0.0
		* @return   array     Security check results.
		*/
	private function check_wordpress_security() {
		$results = array(
			'issues' => array(),
		);
		
		global $wp_version;
		
		// Check WordPress version
		if ( ! function_exists( 'wp_version_check' ) ) {
			include_once ABSPATH . WPINC . '/update.php';
		}
		
		wp_version_check();
		$updates = get_core_updates();
		
		if ( ! empty( $updates ) && isset( $updates[0] ) && $updates[0]->response != 'latest' ) {
			$results['issues'][] = array(
				'type' => 'wordpress_version',
				'file' => 'WordPress Core',
				'message' => sprintf( __( 'WordPress version %s is outdated. Latest version is %s.', 'seo-simple' ), $wp_version, $updates[0]->version ),
			);
		}
		
		// Check if debugging is enabled in production
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! defined( 'WP_DEBUG_DISPLAY' ) ) {
			$results['issues'][] = array(
				'type' => 'debug_mode',
				'file' => 'wp-config.php',
				'message' => __( 'WordPress debug mode is enabled. This can expose sensitive information.', 'seo-simple' ),
			);
		}
		
		// Check if script debugging is enabled
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$results['issues'][] = array(
				'type' => 'script_debug',
				'file' => 'wp-config.php',
				'message' => __( 'WordPress script debugging is enabled. This can expose sensitive information.', 'seo-simple' ),
			);
		}
		
		// Check if save queries is enabled
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$results['issues'][] = array(
				'type' => 'save_queries',
				'file' => 'wp-config.php',
				'message' => __( 'WordPress query saving is enabled. This can expose database queries.', 'seo-simple' ),
			);
		}
		
		return $results;
	}

	/**
	 * Check PHP security.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_php_security() {
		$results = array(
			'issues' => array(),
		);
		
		// Check PHP version
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			$results['issues'][] = array(
				'type' => 'php_version',
				'file' => 'PHP',
				'message' => sprintf( __( 'PHP version %s is outdated and no longer receiving security updates.', 'seo-simple' ), PHP_VERSION ),
			);
		}
		
		// Check dangerous PHP functions
		$dangerous_functions = array(
			'exec', 'system', 'shell_exec', 'passthru', 'proc_open', 'popen',
			'eval', 'create_function', 'assert', 'preg_replace' . '/e',
		);
		
		$disabled_functions = ini_get( 'disable_functions' );
		$disabled_array = empty( $disabled_functions ) ? array() : explode( ',', $disabled_functions );
		$disabled_array = array_map( 'trim', $disabled_array );
		
		foreach ( $dangerous_functions as $function ) {
			if ( ! in_array( $function, $disabled_array ) && function_exists( $function ) ) {
				$results['issues'][] = array(
					'type' => 'dangerous_function',
					'file' => 'php.ini',
					'message' => sprintf( __( 'Dangerous PHP function "%s" is enabled.', 'seo-simple' ), $function ),
				);
			}
		}
		
		// Check PHP error reporting
		if ( ini_get( 'display_errors' ) ) {
			$results['issues'][] = array(
				'type' => 'php_errors',
				'file' => 'php.ini',
				'message' => __( 'PHP error display is enabled. This can expose sensitive information.', 'seo-simple' ),
			);
		}
		
		// Check allow_url_include
		if ( ini_get( 'allow_url_include' ) ) {
			$results['issues'][] = array(
				'type' => 'allow_url_include',
				'file' => 'php.ini',
				'message' => __( 'PHP allow_url_include is enabled. This is a security risk.', 'seo-simple' ),
			);
		}
		
		// Check register_globals (deprecated but might still be enabled)
		if ( ini_get( 'register_globals' ) ) {
			$results['issues'][] = array(
				'type' => 'register_globals',
				'file' => 'php.ini',
				'message' => __( 'PHP register_globals is enabled. This is a serious security risk.', 'seo-simple' ),
			);
		}
		
		return $results;
	}

	/**
	 * Check file permissions.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_file_permissions() {
		$results = array(
			'issues' => array(),
		);
		
		// Check wp-config.php permissions
		$wp_config_file = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config_file ) ) {
			$wp_config_perms = substr( sprintf( '%o', fileperms( $wp_config_file ) ), -4 );
			if ( $wp_config_perms !== '0400' && $wp_config_perms !== '0440' && $wp_config_perms !== '0444' ) {
				$results['issues'][] = array(
					'type' => 'file_permissions',
					'file' => 'wp-config.php',
					'message' => sprintf( __( 'wp-config.php has insecure permissions: %s. Should be 0400, 0440, or 0444.', 'seo-simple' ), $wp_config_perms ),
				);
			}
		}
		
		// Check .htaccess permissions
		$htaccess_file = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$htaccess_perms = substr( sprintf( '%o', fileperms( $htaccess_file ) ), -4 );
			if ( $htaccess_perms !== '0644' && $htaccess_perms !== '0640' ) {
				$results['issues'][] = array(
					'type' => 'file_permissions',
					'file' => '.htaccess',
					'message' => sprintf( __( '.htaccess has insecure permissions: %s. Should be 0644 or 0640.', 'seo-simple' ), $htaccess_perms ),
				);
			}
		}
		
		// Check wp-content directory permissions
		$wp_content_dir = WP_CONTENT_DIR;
		$wp_content_perms = substr( sprintf( '%o', fileperms( $wp_content_dir ) ), -4 );
		if ( $wp_content_perms !== '0755' && $wp_content_perms !== '0750' ) {
			$results['issues'][] = array(
				'type' => 'file_permissions',
				'file' => 'wp-content',
				'message' => sprintf( __( 'wp-content directory has insecure permissions: %s. Should be 0755 or 0750.', 'seo-simple' ), $wp_content_perms ),
			);
		}
		
		// Check plugins directory permissions
		$plugins_dir = WP_PLUGIN_DIR;
		$plugins_perms = substr( sprintf( '%o', fileperms( $plugins_dir ) ), -4 );
		if ( $plugins_perms !== '0755' && $plugins_perms !== '0750' ) {
			$results['issues'][] = array(
				'type' => 'file_permissions',
				'file' => 'wp-content/plugins',
				'message' => sprintf( __( 'plugins directory has insecure permissions: %s. Should be 0755 or 0750.', 'seo-simple' ), $plugins_perms ),
			);
		}
		
		// Check themes directory permissions
		$themes_dir = get_theme_root();
		$themes_perms = substr( sprintf( '%o', fileperms( $themes_dir ) ), -4 );
		if ( $themes_perms !== '0755' && $themes_perms !== '0750' ) {
			$results['issues'][] = array(
				'type' => 'file_permissions',
				'file' => 'wp-content/themes',
				'message' => sprintf( __( 'themes directory has insecure permissions: %s. Should be 0755 or 0750.', 'seo-simple' ), $themes_perms ),
			);
		}
		
		return $results;
	}

	/**
	 * Check database security.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_database_security() {
		global $wpdb;
		
		$results = array(
			'issues' => array(),
		);
		
		// Check database table prefix
		if ( $wpdb->prefix === 'wp_' ) {
			$results['issues'][] = array(
				'type' => 'db_prefix',
				'file' => 'wp-config.php',
				'message' => __( 'Database table prefix is the default "wp_". This makes your site more vulnerable to attacks.', 'seo-simple' ),
			);
		}
		
		// Check for users with default usernames
		$default_usernames = array( 'admin', 'administrator', 'root' );
		foreach ( $default_usernames as $username ) {
			$user = get_user_by( 'login', $username );
			if ( $user ) {
				$results['issues'][] = array(
					'type' => 'default_username',
					'file' => 'Database',
					'message' => sprintf( __( 'User with default username "%s" found. This is a security risk.', 'seo-simple' ), $username ),
				);
			}
		}
		
		// Check for users without strong passwords
		$users = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );
		foreach ( $users as $user ) {
			$user_meta = get_user_meta( $user->ID );
			$last_password_change = isset( $user_meta['last_password_change'] ) ? $user_meta['last_password_change'][0] : '';
			
			if ( empty( $last_password_change ) || strtotime( $last_password_change ) < strtotime( '-90 days' ) ) {
				$results['issues'][] = array(
					'type' => 'old_password',
					'file' => 'Database',
					'message' => sprintf( __( 'User "%s" has not changed password in over 90 days.', 'seo-simple' ), $user->user_login ),
				);
			}
		}
		
		return $results;
	}

	/**
	 * Check WordPress configuration security.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_wordpress_config() {
		$results = array(
			'issues' => array(),
		);
		
		// Check if security keys are set
		$security_keys = array(
			'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
			'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT',
		);
		
		foreach ( $security_keys as $key ) {
			if ( ! defined( $key ) || constant( $key ) === 'put your unique phrase here' ) {
				$results['issues'][] = array(
					'type' => 'security_key',
					'file' => 'wp-config.php',
					'message' => sprintf( __( 'WordPress security key "%s" is not properly set.', 'seo-simple' ), $key ),
				);
			}
		}
		
		// Check if file editing is disabled
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$results['issues'][] = array(
				'type' => 'file_edit',
				'file' => 'wp-config.php',
				'message' => __( 'File editing is not disabled in WordPress admin.', 'seo-simple' ),
			);
		}
		
		// Check if file modifications are disabled
		if ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) {
			$results['issues'][] = array(
				'type' => 'file_mods',
				'file' => 'wp-config.php',
				'message' => __( 'Theme and plugin modifications are not disabled.', 'seo-simple' ),
			);
		}
		
		// Check if automatic updates are disabled for core
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && WP_AUTO_UPDATE_CORE === false ) {
			$results['issues'][] = array(
				'type' => 'auto_update',
				'file' => 'wp-config.php',
				'message' => __( 'Automatic WordPress core updates are disabled.', 'seo-simple' ),
			);
		}
		
		// Check if SSL is forced
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			$results['issues'][] = array(
				'type' => 'ssl_admin',
				'file' => 'wp-config.php',
				'message' => __( 'SSL is not forced for admin area.', 'seo-simple' ),
			);
		}
		
		return $results;
	}

	/**
	 * Check user security.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_user_security() {
		$results = array(
			'issues' => array(),
		);
		
		// Check for administrator accounts
		$admins = get_users( array( 'role' => 'administrator' ) );
		if ( count( $admins ) > 5 ) {
			$results['issues'][] = array(
				'type' => 'too_many_admins',
				'file' => 'Database',
				'message' => sprintf( __( 'Too many administrator accounts (%d). Consider reducing the number of administrators.', 'seo-simple' ), count( $admins ) ),
			);
		}
		
		// Check for inactive users
		$all_users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_registered' ) ) );
		$inactive_threshold = strtotime( '-6 months' );
		
		foreach ( $all_users as $user ) {
			$last_login = get_user_meta( $user->ID, 'last_login', true );
			if ( empty( $last_login ) ) {
				$last_login = strtotime( $user->user_registered );
			} else {
				$last_login = strtotime( $last_login );
			}
			
			if ( $last_login < $inactive_threshold ) {
				$results['issues'][] = array(
					'type' => 'inactive_user',
					'file' => 'Database',
					'message' => sprintf( __( 'User "%s" has been inactive for over 6 months.', 'seo-simple' ), $user->user_login ),
				);
			}
		}
		
		// Check for users with weak roles
		$contributors = get_users( array( 'role' => 'contributor' ) );
		$subscribers = get_users( array( 'role' => 'subscriber' ) );
		
		if ( count( $contributors ) > 100 ) {
			$results['issues'][] = array(
				'type' => 'too_many_contributors',
				'file' => 'Database',
				'message' => sprintf( __( 'Too many contributor accounts (%d). Consider reviewing user roles.', 'seo-simple' ), count( $contributors ) ),
			);
		}
		
		if ( count( $subscribers ) > 1000 ) {
			$results['issues'][] = array(
				'type' => 'too_many_subscribers',
				'file' => 'Database',
				'message' => sprintf( __( 'Large number of subscriber accounts (%d). Consider cleaning up inactive accounts.', 'seo-simple' ), count( $subscribers ) ),
			);
		}
		
		return $results;
	}

	/**
	 * Check plugins and themes security.
	 *
	 * @since    1.0.0
	 * @return   array     Security check results.
	 */
	private function check_plugins_themes_security() {
		$results = array(
			'issues' => array(),
		);
		
		// Check for inactive plugins
		$active_plugins = get_option( 'active_plugins' );
		$all_plugins = get_plugins();
		$inactive_plugins = array_diff_key( $all_plugins, array_flip( $active_plugins ) );
		
		if ( count( $inactive_plugins ) > 5 ) {
			$results['issues'][] = array(
				'type' => 'inactive_plugins',
				'file' => 'wp-content/plugins',
				'message' => sprintf( __( 'Too many inactive plugins (%d). Consider removing unused plugins.', 'seo-simple' ), count( $inactive_plugins ) ),
			);
		}
		
		// Check for outdated plugins
		if ( ! function_exists( 'wp_update_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}
		
		wp_update_plugins();
		$plugin_updates = get_plugin_updates();
		
		foreach ( $plugin_updates as $plugin_file => $plugin_data ) {
			$results['issues'][] = array(
				'type' => 'outdated_plugin',
				'file' => $plugin_file,
				'message' => sprintf( __( 'Plugin "%s" is outdated and may have security vulnerabilities.', 'seo-simple' ), $plugin_data->Name ),
			);
		}
		
		// Check for outdated themes
		if ( ! function_exists( 'wp_update_themes' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}
		
		wp_update_themes();
		$theme_updates = get_theme_updates();
		
		foreach ( $theme_updates as $theme_key => $theme_data ) {
			$results['issues'][] = array(
				'type' => 'outdated_theme',
				'file' => $theme_key,
				'message' => sprintf( __( 'Theme "%s" is outdated and may have security vulnerabilities.', 'seo-simple' ), $theme_data->Name ),
			);
		}
		
		// Check for themes with known vulnerabilities
		$installed_themes = wp_get_themes();
		foreach ( $installed_themes as $theme ) {
			$theme_dir = $theme->get_stylesheet_directory();
			$readme_file = $theme_dir . '/readme.txt';
			
			if ( file_exists( $readme_file ) ) {
				$results['issues'][] = array(
					'type' => 'readme_file',
					'file' => $readme_file,
					'message' => sprintf( __( 'Theme "%s" has a readme.txt file which can expose theme information.', 'seo-simple' ), $theme->get('Name') ),
				);
			}
		}
		
		return $results;
	}

	/**
		* Initialize threat defense feed.
		*
		* @since    1.0.0
		*/
	public function init_threat_feed() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_threat_feed_enabled'] ) || ! $settings['security_threat_feed_enabled'] ) {
			return;
		}
		
		// Load threat data into WAF patterns
		$this->load_threat_data();
	}
	
	/**
	 * Schedule threat feed update.
	 *
	 * @since    1.0.0
	 */
	public function schedule_threat_feed_update() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['security_threat_feed_enabled'] ) || ! $settings['security_threat_feed_enabled'] ) {
			return;
		}
		
		// Schedule the event if not already scheduled
		if ( ! wp_next_scheduled( 'seo_simple_update_threat_feed' ) ) {
			$update_interval = isset( $settings['security_threat_feed_interval'] ) ? $settings['security_threat_feed_interval'] : 'hourly';
			wp_schedule_event( time(), $update_interval, 'seo_simple_update_threat_feed' );
		}
	}
	
	/**
	 * Update threat defense feed.
	 *
	 * @since    1.0.0
	 * @return   array     Update results.
	 */
	public function update_threat_feed() {
		$results = array(
			'update_time' => current_time( 'mysql' ),
			'success' => false,
			'message' => '',
			'rules_updated' => 0,
			'ips_updated' => 0,
		);
		
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Get threat feed URL
		$threat_feed_url = isset( $settings['security_threat_feed_url'] ) ? $settings['security_threat_feed_url'] : 'https://api.seo-simple.com/threat-feed/v1';
		$api_key = isset( $settings['security_threat_feed_api_key'] ) ? $settings['security_threat_feed_api_key'] : '';
		
		if ( empty( $api_key ) ) {
			$results['message'] = __( 'API key is required for threat feed updates.', 'seo-simple' );
			return $results;
		}
		
		// Make API request
		$response = wp_remote_get( $threat_feed_url, array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json',
			),
		) );
		
		if ( is_wp_error( $response ) ) {
			$results['message'] = __( 'Failed to connect to threat feed server: ', 'seo-simple' ) . $response->get_error_message();
			return $results;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		if ( $response_code !== 200 ) {
			$results['message'] = sprintf( __( 'Threat feed server returned error code: %d', 'seo-simple' ), $response_code );
			return $results;
		}
		
		$data = json_decode( $body, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$results['message'] = __( 'Invalid JSON response from threat feed server.', 'seo-simple' );
			return $results;
		}
		
		// Process threat data
		$rules_updated = $this->update_threat_rules( isset( $data['rules'] ) ? $data['rules'] : array() );
		$ips_updated = $this->update_blocked_ips( isset( $data['blocked_ips'] ) ? $data['blocked_ips'] : array() );
		
		// Update last update time
		update_option( 'seo_simple_threat_feed_last_update', current_time( 'mysql' ) );
		
		$results['success'] = true;
		$results['message'] = __( 'Threat feed updated successfully.', 'seo-simple' );
		$results['rules_updated'] = $rules_updated;
		$results['ips_updated'] = $ips_updated;
		
		// Log the update
		$this->log_security_event( 'threat_feed_updated', array(
			'rules_updated' => $rules_updated,
			'ips_updated' => $ips_updated,
		) );
		
		return $results;
	}
	
	/**
	 * Load threat data.
	 *
	 * @since    1.0.0
	 */
	private function load_threat_data() {
		$threat_rules = get_option( 'seo_simple_threat_rules', array() );
		$blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		
		// Update WAF patterns with threat rules
		$this->update_waf_patterns( $threat_rules );
		
		// Update blocked IPs
		update_option( 'seo_simple_blocked_ips', $blocked_ips );
	}
	
	/**
	 * Update threat rules.
	 *
	 * @since    1.0.0
	 * @param    array     $rules    The threat rules.
	 * @return   int                 Number of rules updated.
	 */
	private function update_threat_rules( $rules ) {
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			return 0;
		}
		
		$current_rules = get_option( 'seo_simple_threat_rules', array() );
		$updated_count = 0;
		
		foreach ( $rules as $rule ) {
			if ( ! isset( $rule['id'], $rule['pattern'], $rule['type'], $rule['severity'] ) ) {
				continue;
			}
			
			$rule_id = sanitize_text_field( $rule['id'] );
			$pattern = sanitize_text_field( $rule['pattern'] );
			$type = sanitize_text_field( $rule['type'] );
			$severity = sanitize_text_field( $rule['severity'] );
			$description = isset( $rule['description'] ) ? sanitize_text_field( $rule['description'] ) : '';
			$expires = isset( $rule['expires'] ) ? sanitize_text_field( $rule['expires'] ) : '';
			
			// Skip expired rules
			if ( ! empty( $expires ) && strtotime( $expires ) < time() ) {
				if ( isset( $current_rules[$rule_id] ) ) {
					unset( $current_rules[$rule_id] );
					$updated_count++;
				}
				continue;
			}
			
			// Add or update rule
			$current_rules[$rule_id] = array(
				'pattern' => $pattern,
				'type' => $type,
				'severity' => $severity,
				'description' => $description,
				'expires' => $expires,
				'updated' => current_time( 'mysql' ),
			);
			
			$updated_count++;
		}
		
		// Save updated rules
		update_option( 'seo_simple_threat_rules', $current_rules );
		
		// Update WAF patterns
		$this->update_waf_patterns( $current_rules );
		
		return $updated_count;
	}
	
	/**
	 * Update blocked IPs from threat feed.
	 *
	 * @since    1.0.0
	 * @param    array     $ips    The blocked IPs.
	 * @return   int               Number of IPs updated.
	 */
	private function update_blocked_ips( $ips ) {
		if ( empty( $ips ) || ! is_array( $ips ) ) {
			return 0;
		}
		
		$current_blocked_ips = get_option( 'seo_simple_blocked_ips', array() );
		$threat_blocked_ips = get_option( 'seo_simple_threat_blocked_ips', array() );
		$updated_count = 0;
		
		foreach ( $ips as $ip_data ) {
			if ( ! isset( $ip_data['ip'], $ip_data['reason'], $ip_data['source'] ) ) {
				continue;
			}
			
			$ip = sanitize_text_field( $ip_data['ip'] );
			$reason = sanitize_text_field( $ip_data['reason'] );
			$source = sanitize_text_field( $ip_data['source'] );
			$expires = isset( $ip_data['expires'] ) ? sanitize_text_field( $ip_data['expires'] ) : '';
			
			// Skip expired IPs
			if ( ! empty( $expires ) && strtotime( $expires ) < time() ) {
				if ( in_array( $ip, $current_blocked_ips ) ) {
					// Remove from blocked IPs
					$current_blocked_ips = array_diff( $current_blocked_ips, array( $ip ) );
					unset( $threat_blocked_ips[$ip] );
					$updated_count++;
				}
				continue;
			}
			
			// Add to blocked IPs if not already blocked
			if ( ! in_array( $ip, $current_blocked_ips ) ) {
				$current_blocked_ips[] = $ip;
				$threat_blocked_ips[$ip] = array(
					'reason' => $reason,
					'source' => $source,
					'expires' => $expires,
					'blocked' => current_time( 'mysql' ),
				);
				$updated_count++;
			}
		}
		
		// Save updated blocked IPs
		update_option( 'seo_simple_blocked_ips', array_values( $current_blocked_ips ) );
		update_option( 'seo_simple_threat_blocked_ips', $threat_blocked_ips );
		
		return $updated_count;
	}
	
	/**
	 * Update WAF patterns with threat rules.
	 *
	 * @since    1.0.0
	 * @param    array     $rules    The threat rules.
	 */
	private function update_waf_patterns( $rules ) {
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			return;
		}
		
		// Get current WAF patterns
		$waf_patterns = get_option( 'seo_simple_waf_patterns', array() );
		
		// Add threat rules to WAF patterns
		foreach ( $rules as $rule_id => $rule ) {
			if ( $rule['type'] === 'pattern' ) {
				$waf_patterns['threat_' . $rule_id] = $rule['pattern'];
			}
		}
		
		// Save updated WAF patterns
		update_option( 'seo_simple_waf_patterns', $waf_patterns );
	}
	
	/**
	 * Get threat feed status.
	 *
	 * @since    1.0.0
	 * @return   array     Threat feed status.
	 */
	public function get_threat_feed_status() {
		$settings = get_option( 'seo_simple_settings', array() );
		$last_update = get_option( 'seo_simple_threat_feed_last_update', 'Never' );
		$rules_count = count( get_option( 'seo_simple_threat_rules', array() ) );
		$blocked_ips_count = count( get_option( 'seo_simple_threat_blocked_ips', array() ) );
		
		return array(
			'enabled' => isset( $settings['security_threat_feed_enabled'] ) ? $settings['security_threat_feed_enabled'] : false,
			'last_update' => $last_update,
			'rules_count' => $rules_count,
			'blocked_ips_count' => $blocked_ips_count,
			'update_interval' => isset( $settings['security_threat_feed_interval'] ) ? $settings['security_threat_feed_interval'] : 'hourly',
		);
	}
	
	/**
	 * AJAX handler for updating threat feed.
	 *
	 * @since    1.0.0
	 */
	public function ajax_update_threat_feed() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_update_threat_feed' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to update threat feed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Update threat feed
		$results = $this->update_threat_feed();
		
		if ( $results['success'] ) {
			wp_send_json_success( array(
				'message' => $results['message'],
				'rules_updated' => $results['rules_updated'],
				'ips_updated' => $results['ips_updated'],
			) );
		} else {
			wp_send_json_error( array( 'message' => $results['message'] ) );
		}
	}
}