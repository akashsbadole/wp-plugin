<?php

/**
 * 404 Monitor functionality
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * 404 Monitor class
 *
 * This class handles tracking and managing 404 errors on the site.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_404_Monitor {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @static
	 * @var      Seo_Simple_404_Monitor    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @static
	 * @return   Seo_Simple_404_Monitor    The single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function __construct() {
		// Register hooks
		add_action( 'template_redirect', array( $this, 'track_404_errors' ) );
		add_action( 'admin_init', array( $this, 'create_tables' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_clear_logs', array( $this, 'ajax_clear_logs' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_ignore_url', array( $this, 'ajax_ignore_url' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_create_redirect', array( $this, 'ajax_create_redirect' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_get_stats', array( $this, 'ajax_get_stats' ) );
		
		// Schedule cleanup
		if ( ! wp_next_scheduled( 'seo_simple_404_monitor_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'seo_simple_404_monitor_cleanup' );
		}
		add_action( 'seo_simple_404_monitor_cleanup', array( $this, 'cleanup_old_logs' ) );
	}

	/**
	 * Create the necessary database tables.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function create_tables() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			url varchar(500) NOT NULL,
			referrer varchar(500) DEFAULT '',
			user_agent text,
			ip_address varchar(45) DEFAULT '',
			hit_count int(11) NOT NULL DEFAULT 1,
			first_hit datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			last_hit datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			is_ignored tinyint(1) NOT NULL DEFAULT 0,
			redirect_to varchar(500) DEFAULT '',
			PRIMARY KEY  (id),
			KEY url (url(191)),
			KEY last_hit (last_hit),
			KEY is_ignored (is_ignored)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Track 404 errors.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function track_404_errors() {
		if ( ! is_404() ) {
			return;
		}
		
		// Check if 404 monitoring is enabled
		$settings = get_option( 'seo_simple_settings', array() );
		if ( ! isset( $settings['seo_enable_404_monitor'] ) || ! $settings['seo_enable_404_monitor'] ) {
			return;
		}
		
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		// Get current URL
		$url = $_SERVER['REQUEST_URI'];
		$url = home_url( $url );
		
		// Get referrer
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		
		// Get user agent
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		
		// Get IP address
		$ip_address = $this->get_user_ip();
		
		// Check if URL already exists in logs
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table_name WHERE url = %s",
			$url
		) );
		
		$current_time = current_time( 'mysql' );
		
		if ( $existing ) {
			// Update existing record
			$wpdb->update(
				$table_name,
				array(
					'hit_count' => $existing->hit_count + 1,
					'last_hit' => $current_time,
				),
				array( 'id' => $existing->id ),
				array( '%d', '%s' ),
				array( '%d' )
			);
		} else {
			// Insert new record
			$wpdb->insert(
				$table_name,
				array(
					'url' => $url,
					'referrer' => $referrer,
					'user_agent' => $user_agent,
					'ip_address' => $ip_address,
					'hit_count' => 1,
					'first_hit' => $current_time,
					'last_hit' => $current_time,
				),
				array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Get user IP address.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   string    The IP address.
	 */
	private function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	/**
	 * Get 404 logs.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $args    Query arguments.
	 * @return   array              The 404 logs.
	 */
	public function get_logs( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'limit' => 50,
			'offset' => 0,
			'orderby' => 'last_hit',
			'order' => 'DESC',
			'search' => '',
			'is_ignored' => null,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		$where = 'WHERE 1=1';
		$values = array();
		
		if ( ! empty( $args['search'] ) ) {
			$where .= ' AND url LIKE %s';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		}
		
		if ( null !== $args['is_ignored'] ) {
			$where .= ' AND is_ignored = %d';
			$values[] = $args['is_ignored'];
		}
		
		$sql = "SELECT * FROM $table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
		$values[] = $args['limit'];
		$values[] = $args['offset'];
		
		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}
		
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get 404 logs count.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $args    Query arguments.
	 * @return   int               The 404 logs count.
	 */
	public function get_logs_count( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'search' => '',
			'is_ignored' => null,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		$where = 'WHERE 1=1';
		$values = array();
		
		if ( ! empty( $args['search'] ) ) {
			$where .= ' AND url LIKE %s';
			$values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		}
		
		if ( null !== $args['is_ignored'] ) {
			$where .= ' AND is_ignored = %d';
			$values[] = $args['is_ignored'];
		}
		
		$sql = "SELECT COUNT(*) FROM $table_name $where";
		
		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}
		
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get 404 statistics.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    The statistics.
	 */
	public function get_stats() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		// Total 404s
		$total_404s = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		
		// Total hits
		$total_hits = (int) $wpdb->get_var( "SELECT SUM(hit_count) FROM $table_name" );
		
		// Ignored 404s
		$ignored_404s = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE is_ignored = 1" );
		
		// 404s with redirects
		$redirects = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE redirect_to != ''" );
		
		// Top 404 URLs
		$top_urls = $wpdb->get_results(
			"SELECT url, hit_count FROM $table_name WHERE is_ignored = 0 ORDER BY hit_count DESC LIMIT 10"
		);
		
		// Recent 404s
		$recent = $wpdb->get_results(
			"SELECT url, last_hit, hit_count FROM $table_name ORDER BY last_hit DESC LIMIT 10"
		);
		
		return array(
			'total_404s' => $total_404s,
			'total_hits' => $total_hits,
			'ignored_404s' => $ignored_404s,
			'redirects' => $redirects,
			'top_urls' => $top_urls,
			'recent' => $recent,
		);
	}

	/**
	 * Ignore a URL.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $id    The log ID.
	 * @return   bool             True on success, false on failure.
	 */
	public function ignore_url( $id ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		return (bool) $wpdb->update(
			$table_name,
			array( 'is_ignored' => 1 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Create a redirect for a 404 URL.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $id           The log ID.
	 * @param    string    $redirect_to  The URL to redirect to.
	 * @return   bool                    True on success, false on failure.
	 */
	public function create_redirect( $id, $redirect_to ) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		// Get the URL from the log
		$url = $wpdb->get_var( $wpdb->prepare(
			"SELECT url FROM $table_name WHERE id = %d",
			$id
		) );
		
		if ( ! $url ) {
			return false;
		}
		
		// Create redirect in redirect manager
		if ( class_exists( 'Seo_Simple_Redirect_Manager' ) ) {
			$redirect_manager = Seo_Simple_Redirect_Manager::get_instance();
			$redirect_manager->add_redirect( $url, $redirect_to, 301 );
		}
		
		// Update the log
		return (bool) $wpdb->update(
			$table_name,
			array( 'redirect_to' => $redirect_to ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Clear all logs.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   bool    True on success, false on failure.
	 */
	public function clear_logs() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		return (bool) $wpdb->query( "TRUNCATE TABLE $table_name" );
	}

	/**
	 * Clean up old logs.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function cleanup_old_logs() {
		global $wpdb;
		
		$settings = get_option( 'seo_simple_settings', array() );
		$retention_days = isset( $settings['seo_404_monitor_retention'] ) ? intval( $settings['seo_404_monitor_retention'] ) : 30;
		
		if ( $retention_days < 1 ) {
			return;
		}
		
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$retention_days days" ) );
		
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name WHERE last_hit < %s",
				$cutoff_date
			)
		);
	}

	/**
	 * AJAX handler for clearing logs.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_clear_logs() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		if ( $this->clear_logs() ) {
			wp_send_json_success( array( 'message' => __( 'All 404 logs have been cleared.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear 404 logs.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for ignoring a URL.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_ignore_url() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if ( $this->ignore_url( $id ) ) {
			wp_send_json_success( array( 'message' => __( 'URL has been ignored.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to ignore URL.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for creating a redirect.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_create_redirect() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : '';
		
		if ( empty( $redirect_to ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid redirect URL.', 'seo-simple' ) ) );
		}
		
		if ( $this->create_redirect( $id, $redirect_to ) ) {
			wp_send_json_success( array( 'message' => __( 'Redirect has been created.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for getting statistics.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_get_stats() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$stats = $this->get_stats();
		wp_send_json_success( $stats );
	}
}