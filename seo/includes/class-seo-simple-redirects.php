<?php

/**
 * Handles redirect management for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles redirect management.
 *
 * This class handles all operations related to URL redirects including
 * adding, updating, deleting, and implementing 301 redirects.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Redirects {

	/**
	 * The table name for redirects.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $table_name    The table name.
	 */
	private $table_name;

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache    $cache    The cache handler instance.
	 */
	private $cache;

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'seo_simple_redirects';
		
		// Initialize cache handler if available
		if ( class_exists( 'Seo_Simple_Cache' ) ) {
			$this->cache = new Seo_Simple_Cache();
		}
		
		// Hook into init to create table
		add_action( 'init', array( $this, 'create_table' ) );
		
		// Hook into template_redirect to handle redirects
		add_action( 'template_redirect', array( $this, 'handle_redirects' ), 1 );
		
		// Register admin settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Create the redirects table.
	 *
	 * @since    1.0.0
	 */
	public function create_table() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			from_url varchar(255) NOT NULL,
			to_url varchar(255) NOT NULL,
			redirect_type int(3) NOT NULL DEFAULT 301,
			status tinyint(1) NOT NULL DEFAULT 1,
			hit_count mediumint(9) NOT NULL DEFAULT 0,
			last_hit datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY from_url (from_url(191)),
			KEY status (status),
			KEY hit_count (hit_count)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Handle redirects.
	 *
	 * @since    1.0.0
	 */
	public function handle_redirects() {
		// Don't redirect admin, login, or register pages
		if ( is_admin() || is_login() || $GLOBALS['pagenow'] === 'wp-register.php' ) {
			return;
		}
		
		// Get current URL
		$current_url = $this->get_current_url();
		
		// Check for redirect
		$redirect = $this->get_redirect( $current_url );
		
		if ( $redirect ) {
			// Update hit count
			$this->update_hit_count( $redirect['id'] );
			
			// Perform redirect
			wp_redirect( $redirect['to_url'], $redirect['redirect_type'] );
			exit;
		}
	}

	/**
	 * Get current URL.
	 *
	 * @since    1.0.0
	 * @return   string    The current URL.
	 */
	private function get_current_url() {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host = $_SERVER['HTTP_HOST'];
		$path = $_SERVER['REQUEST_URI'];
		
		return $protocol . $host . $path;
	}

	/**
	 * Get a redirect by from_url.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url    The URL to redirect from.
	 * @return   array|null              The redirect data or null if not found.
	 */
	public function get_redirect( $from_url ) {
		// Remove query string and fragments for matching
		$from_url = preg_replace( '/\?.*$/', '', $from_url );
		$from_url = rtrim( $from_url, '/' );
		
		// Check cache first
		$cache_key = 'redirect_' . md5( $from_url );
		
		if ( $this->cache ) {
			$cached_redirect = $this->cache->get( $cache_key );
			if ( $cached_redirect !== false ) {
				return $cached_redirect;
			}
		} else {
			// Fallback to WordPress transients
			$cached_redirect = get_transient( $cache_key );
			if ( $cached_redirect !== false ) {
				return $cached_redirect;
			}
		}
		
		global $wpdb;
		
		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE status = 1 AND from_url = %s LIMIT 1",
			$from_url
		);
		
		$redirect = $wpdb->get_row( $sql, ARRAY_A );
		
		$result = null;
		if ( $redirect ) {
			$result = array(
				'id' => intval( $redirect['id'] ),
				'from_url' => $redirect['from_url'],
				'to_url' => $redirect['to_url'],
				'redirect_type' => intval( $redirect['redirect_type'] ),
				'status' => intval( $redirect['status'] ),
				'hit_count' => intval( $redirect['hit_count'] ),
				'last_hit' => $redirect['last_hit'],
				'created_at' => $redirect['created_at'],
				'updated_at' => $redirect['updated_at'],
			);
		}
		
		// Cache the result
		if ( $this->cache ) {
			$this->cache->set( $cache_key, $result, 3600 ); // Cache for 1 hour
		} else {
			// Fallback to WordPress transients
			set_transient( $cache_key, $result, 3600 );
		}
		
		return $result;
	}

	/**
	 * Add a new redirect.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url       The URL to redirect from.
	 * @param    string    $to_url         The URL to redirect to.
	 * @param    int       $redirect_type  The redirect type (301, 302, etc.).
	 * @param    int       $status         The redirect status (1 for active, 0 for inactive).
	 * @return   int|false                The redirect ID on success, false on failure.
	 */
	public function add_redirect( $from_url, $to_url, $redirect_type = 301, $status = 1 ) {
		global $wpdb;
		
		// Validate URLs
		if ( ! $this->validate_redirect( $from_url, $to_url ) ) {
			return false;
		}
		
		// Sanitize URLs
		$from_url = $this->sanitize_url( $from_url );
		$to_url = $this->sanitize_url( $to_url );
		
		// Check for existing redirect
		$existing = $this->get_redirect( $from_url );
		if ( $existing ) {
			return $this->update_redirect( $existing['id'], $to_url, $redirect_type, $status );
		}
		
		// Check for redirect loops
		if ( $this->would_create_loop( $from_url, $to_url ) ) {
			return false;
		}
		
		// Insert redirect
		$result = $wpdb->insert(
			$this->table_name,
			array(
				'from_url' => $from_url,
				'to_url' => $to_url,
				'redirect_type' => intval( $redirect_type ),
				'status' => intval( $status ),
			),
			array( '%s', '%s', '%d', '%d' )
		);
		
		if ( $result ) {
			// Clear cache
			$this->clear_redirect_cache( $from_url );
			return $wpdb->insert_id;
		}
		
		return false;
	}

	/**
	 * Update an existing redirect.
	 *
	 * @since    1.0.0
	 * @param    int       $id             The redirect ID.
	 * @param    string    $to_url         The URL to redirect to.
	 * @param    int       $redirect_type  The redirect type (301, 302, etc.).
	 * @param    int       $status         The redirect status (1 for active, 0 for inactive).
	 * @return   bool                      True on success, false on failure.
	 */
	public function update_redirect( $id, $to_url, $redirect_type = 301, $status = 1 ) {
		global $wpdb;
		
		// Get current redirect
		$current = $this->get_redirect_by_id( $id );
		if ( ! $current ) {
			return false;
		}
		
		// Validate URLs
		if ( ! $this->validate_redirect( $current['from_url'], $to_url ) ) {
			return false;
		}
		
		// Sanitize URL
		$to_url = $this->sanitize_url( $to_url );
		
		// Check for redirect loops
		if ( $this->would_create_loop( $current['from_url'], $to_url, $id ) ) {
			return false;
		}
		
		// Update redirect
		$result = $wpdb->update(
			$this->table_name,
			array(
				'to_url' => $to_url,
				'redirect_type' => intval( $redirect_type ),
				'status' => intval( $status ),
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%d', '%d' ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			// Clear cache
			$this->clear_redirect_cache( $current['from_url'] );
		}
		
		return $result !== false;
	}

	/**
	 * Update an existing redirect with all fields.
	 *
	 * @since    1.0.0
	 * @param    int       $id             The redirect ID.
	 * @param    string    $from_url       The URL to redirect from.
	 * @param    string    $to_url         The URL to redirect to.
	 * @param    int       $redirect_type  The redirect type (301, 302, etc.).
	 * @param    int       $status         The redirect status (1 for active, 0 for inactive).
	 * @return   bool                      True on success, false on failure.
	 */
	public function update_redirect_full( $id, $from_url, $to_url, $redirect_type = 301, $status = 1 ) {
		global $wpdb;
		
		// Get current redirect
		$current = $this->get_redirect_by_id( $id );
		if ( ! $current ) {
			return false;
		}
		
		// Validate URLs
		if ( ! $this->validate_redirect( $from_url, $to_url ) ) {
			return false;
		}
		
		// Sanitize URLs
		$from_url = $this->sanitize_url( $from_url );
		$to_url = $this->sanitize_url( $to_url );
		
		// Check for redirect loops
		if ( $this->would_create_loop( $from_url, $to_url, $id ) ) {
			return false;
		}
		
		// Check if from_url is being changed and if it conflicts with existing redirect
		if ( $from_url !== $current['from_url'] ) {
			$existing = $this->get_redirect( $from_url );
			if ( $existing && $existing['id'] != $id ) {
				return false; // Conflict with existing redirect
			}
		}
		
		// Update redirect
		$result = $wpdb->update(
			$this->table_name,
			array(
				'from_url' => $from_url,
				'to_url' => $to_url,
				'redirect_type' => intval( $redirect_type ),
				'status' => intval( $status ),
			),
			array( 'id' => intval( $id ) ),
			array( '%s', '%s', '%d', '%d' ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			// Clear cache for both old and new URLs
			$this->clear_redirect_cache( $current['from_url'] );
			if ( $from_url !== $current['from_url'] ) {
				$this->clear_redirect_cache( $from_url );
			}
		}
		
		return $result !== false;
	}

	/**
	 * Delete a redirect.
	 *
	 * @since    1.0.0
	 * @param    int       $id    The redirect ID.
	 * @return   bool              True on success, false on failure.
	 */
	public function delete_redirect( $id ) {
		global $wpdb;
		
		// Get current redirect to clear cache
		$current = $this->get_redirect_by_id( $id );
		if ( ! $current ) {
			return false;
		}
		
		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => intval( $id ) ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			// Clear cache
			$this->clear_redirect_cache( $current['from_url'] );
		}
		
		return $result !== false;
	}

	/**
	 * Get a redirect by ID.
	 *
	 * @since    1.0.0
	 * @param    int       $id    The redirect ID.
	 * @return   array|null        The redirect data or null if not found.
	 */
	public function get_redirect_by_id( $id ) {
		global $wpdb;
		
		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE id = %d LIMIT 1",
			intval( $id )
		);
		
		$redirect = $wpdb->get_row( $sql, ARRAY_A );
		
		if ( $redirect ) {
			return array(
				'id' => intval( $redirect['id'] ),
				'from_url' => $redirect['from_url'],
				'to_url' => $redirect['to_url'],
				'redirect_type' => intval( $redirect['redirect_type'] ),
				'status' => intval( $redirect['status'] ),
				'hit_count' => intval( $redirect['hit_count'] ),
				'last_hit' => $redirect['last_hit'],
				'created_at' => $redirect['created_at'],
				'updated_at' => $redirect['updated_at'],
			);
		}
		
		return null;
	}

	/**
	 * Get all redirects.
	 *
	 * @since    1.0.0
	 * @param    array     $args    Query arguments.
	 * @return   array              Array of redirects.
	 */
	public function get_redirects( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'status' => null,
			'orderby' => 'created_at',
			'order' => 'DESC',
			'limit' => null,
			'offset' => 0,
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$sql = "SELECT * FROM {$this->table_name}";
		$where = array();
		$prepare_args = array();
		
		// Add status filter
		if ( $args['status'] !== null ) {
			$where[] = "status = %d";
			$prepare_args[] = intval( $args['status'] );
		}
		
		if ( ! empty( $where ) ) {
			$sql .= " WHERE " . implode( " AND ", $where );
		}
		
		// Add order by
		$allowed_orderby = array( 'id', 'from_url', 'to_url', 'redirect_type', 'status', 'hit_count', 'created_at', 'updated_at' );
		if ( in_array( $args['orderby'], $allowed_orderby ) ) {
			$sql .= " ORDER BY " . $args['orderby'] . " " . strtoupper( $args['order'] );
		}
		
		// Add limit
		if ( $args['limit'] !== null ) {
			$sql .= " LIMIT %d OFFSET %d";
			$prepare_args[] = intval( $args['limit'] );
			$prepare_args[] = intval( $args['offset'] );
		}
		
		if ( ! empty( $prepare_args ) ) {
			$sql = $wpdb->prepare( $sql, $prepare_args );
		}
		
		$redirects = $wpdb->get_results( $sql, ARRAY_A );
		
		$results = array();
		if ( $redirects ) {
			foreach ( $redirects as $redirect ) {
				$results[] = array(
					'id' => intval( $redirect['id'] ),
					'from_url' => $redirect['from_url'],
					'to_url' => $redirect['to_url'],
					'redirect_type' => intval( $redirect['redirect_type'] ),
					'status' => intval( $redirect['status'] ),
					'hit_count' => intval( $redirect['hit_count'] ),
					'last_hit' => $redirect['last_hit'],
					'created_at' => $redirect['created_at'],
					'updated_at' => $redirect['updated_at'],
				);
			}
		}
		
		return $results;
	}

	/**
	 * Count redirects.
	 *
	 * @since    1.0.0
	 * @param    int       $status    The status to count (1 for active, 0 for inactive, null for all).
	 * @return   int                  The number of redirects.
	 */
	public function count_redirects( $status = null ) {
		global $wpdb;
		
		$sql = "SELECT COUNT(*) FROM {$this->table_name}";
		
		if ( $status !== null ) {
			$sql .= $wpdb->prepare( " WHERE status = %d", intval( $status ) );
		}
		
		return intval( $wpdb->get_var( $sql ) );
	}

	/**
	 * Update hit count for a redirect.
	 *
	 * @since    1.0.0
	 * @param    int       $id    The redirect ID.
	 * @return   bool              True on success, false on failure.
	 */
	public function update_hit_count( $id ) {
		global $wpdb;
		
		$result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->table_name} SET hit_count = hit_count + 1, last_hit = CURRENT_TIMESTAMP WHERE id = %d",
				intval( $id )
			)
		);
		
		// Clear cache for this redirect
		if ( $result !== false ) {
			$redirect = $this->get_redirect_by_id( $id );
			if ( $redirect ) {
				$this->clear_redirect_cache( $redirect['from_url'] );
			}
		}
		
		return $result !== false;
	}
	
	/**
	 * Clear redirect cache for a specific URL.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url    The URL to clear cache for.
	 */
	private function clear_redirect_cache( $from_url ) {
		$from_url = preg_replace( '/\?.*$/', '', $from_url );
		$from_url = rtrim( $from_url, '/' );
		$cache_key = 'redirect_' . md5( $from_url );
		
		if ( $this->cache ) {
			$this->cache->delete( $cache_key );
		} else {
			// Fallback to WordPress transients
			delete_transient( $cache_key );
		}
	}
	
	/**
	 * Clear all redirect cache.
	 *
	 * @since    1.0.0
	 */
	public function clear_all_redirect_cache() {
		if ( $this->cache ) {
			// Since we don't have a way to get all keys with our cache class,
			// we'll just clear the settings which will force a refresh
			$this->cache->delete_settings();
		} else {
			// Fallback to WordPress transients
			// This is not ideal but necessary without a central cache management
			global $wpdb;
			$transient_keys = $wpdb->get_col( 
				"SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_redirect_%'" 
			);
			
			if ( $transient_keys ) {
				foreach ( $transient_keys as $key ) {
					$transient_name = str_replace( '_transient_', '', $key );
					delete_transient( $transient_name );
				}
			}
		}
	}

	/**
	 * Validate redirect URLs.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url    The URL to redirect from.
	 * @param    string    $to_url      The URL to redirect to.
	 * @return   bool                   True if valid, false otherwise.
	 */
	private function validate_redirect( $from_url, $to_url ) {
		// Check if URLs are not empty
		if ( empty( $from_url ) || empty( $to_url ) ) {
			return false;
		}
		
		// Check if URLs are the same
		if ( $from_url === $to_url ) {
			return false;
		}
		
		// Check if from_url is a valid URL format
		if ( ! filter_var( $from_url, FILTER_VALIDATE_URL ) && ! $this->is_relative_url( $from_url ) ) {
			return false;
		}
		
		// Check if to_url is a valid URL
		if ( ! filter_var( $to_url, FILTER_VALIDATE_URL ) && ! $this->is_relative_url( $to_url ) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Check if a URL is relative.
	 *
	 * @since    1.0.0
	 * @param    string    $url    The URL to check.
	 * @return   bool              True if relative, false otherwise.
	 */
	private function is_relative_url( $url ) {
		return ! preg_match( '#^(https?://|ftp://|//)#', $url );
	}

	/**
	 * Sanitize URL.
	 *
	 * @since    1.0.0
	 * @param    string    $url    The URL to sanitize.
	 * @return   string            The sanitized URL.
	 */
	private function sanitize_url( $url ) {
		// Remove query string and fragments
		$url = preg_replace( '/\?.*$/', '', $url );
		$url = preg_replace( '/#.*$/', '', $url );
		
		// Remove trailing slash
		$url = rtrim( $url, '/' );
		
		// Ensure URL starts with / for relative URLs
		if ( $this->is_relative_url( $url ) && strpos( $url, '/' ) !== 0 ) {
			$url = '/' . $url;
		}
		
		return esc_url_raw( $url );
	}

	/**
	 * Check if a redirect would create a loop.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url    The URL to redirect from.
	 * @param    string    $to_url      The URL to redirect to.
	 * @param    int       $exclude_id  The ID to exclude from the check (for updates).
	 * @return   bool                   True if would create a loop, false otherwise.
	 */
	private function would_create_loop( $from_url, $to_url, $exclude_id = null ) {
		// Check if from_url equals to_url
		if ( $from_url === $to_url ) {
			return true;
		}
		
		// Check if to_url redirects back to from_url
		$redirect = $this->get_redirect( $to_url );
		
		if ( $redirect ) {
			// Skip if this is the redirect we're updating
			if ( $exclude_id && $redirect['id'] == $exclude_id ) {
				return false;
			}
			
			// Recursively check for loops
			return $this->would_create_loop( $from_url, $redirect['to_url'], $exclude_id );
		}
		
		return false;
	}

	/**
	 * Register admin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		// Settings will be handled by the admin class
	}

	/**
	 * Get redirect types.
	 *
	 * @since    1.0.0
	 * @return   array    Array of redirect types.
	 */
	public function get_redirect_types() {
		return array(
			301 => '301 Permanent Redirect',
			302 => '302 Temporary Redirect',
			303 => '303 See Other',
			307 => '307 Temporary Redirect',
			308 => '308 Permanent Redirect',
		);
	}

	/**
	 * Import redirects from CSV.
	 *
	 * @since    1.0.0
	 * @param    string    $file_path    The CSV file path.
	 * @return   array                   Import results.
	 */
	public function import_redirects( $file_path ) {
		$results = array(
			'success' => 0,
			'errors' => 0,
			'messages' => array(),
		);
		
		if ( ! file_exists( $file_path ) ) {
			$results['errors']++;
			$results['messages'][] = __( 'File not found.', 'seo-simple' );
			return $results;
		}
		
		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			$results['errors']++;
			$results['messages'][] = __( 'Could not open file.', 'seo-simple' );
			return $results;
		}
		
		// Skip header row if exists
		$header = fgetcsv( $handle );
		
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) < 2 ) {
				$results['errors']++;
				$results['messages'][] = __( 'Invalid row format.', 'seo-simple' );
				continue;
			}
			
			$from_url = $row[0];
			$to_url = $row[1];
			$redirect_type = isset( $row[2] ) ? intval( $row[2] ) : 301;
			
			if ( $this->add_redirect( $from_url, $to_url, $redirect_type ) ) {
				$results['success']++;
			} else {
				$results['errors']++;
				$results['messages'][] = sprintf( __( 'Failed to add redirect: %s → %s', 'seo-simple' ), $from_url, $to_url );
			}
		}
		
		fclose( $handle );
		
		return $results;
	}

	/**
	 * Export redirects to CSV.
	 *
	 * @since    1.0.0
	 * @param    array     $args    Query arguments.
	 * @return   string             CSV content.
	 */
	public function export_redirects( $args = array() ) {
		$redirects = $this->get_redirects( $args );
		
		$csv = "from_url,to_url,redirect_type,status,hit_count\n";
		
		foreach ( $redirects as $redirect ) {
			$csv .= '"' . (is_scalar($redirect['from_url']) ? $redirect['from_url'] : '') . '","' . (is_scalar($redirect['to_url']) ? $redirect['to_url'] : '') . '",' . intval($redirect['redirect_type']) . ',' . intval($redirect['status']) . ',' . intval($redirect['hit_count']) . "\n";
		}
		
		return $csv;
	}
}