<?php

/**
 * Handle code snippets functionality for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handle code snippets functionality.
 *
 * This class handles all operations related to code snippets including
 * database operations and script injection.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Code_Snippets {

	/**
	 * The table name for storing code snippets.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $table_name    The table name for storing code snippets.
	 */
	private $table_name;

	/**
	 * Whitelist of allowed HTML tags for code snippets.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $allowed_html    Allowed HTML tags.
	 */
	private $allowed_html;

	/**
	 * Whitelist of allowed JavaScript patterns.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $allowed_js_patterns    Allowed JavaScript patterns.
	 */
	private $allowed_js_patterns;

	/**
	 * Blacklist of dangerous patterns.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $dangerous_patterns    Dangerous patterns to block.
	 */
	private $dangerous_patterns;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'seo_simple_code_snippets';
		
		// Initialize security patterns
		$this->init_security_patterns();
	}

	/**
	 * Initialize security patterns for code validation.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_security_patterns() {
		// Allowed HTML tags - very restrictive whitelist
		$this->allowed_html = array(
			'script' => array(
				'type' => true,
				'src' => true,
				'async' => true,
				'defer' => true,
				'charset' => true,
			),
			'style' => array(
				'type' => true,
				'media' => true,
			),
			'link' => array(
				'rel' => true,
				'href' => true,
				'type' => true,
				'media' => true,
			),
			'meta' => array(
				'charset' => true,
				'name' => true,
				'content' => true,
				'http-equiv' => true,
				'property' => true,
			),
			'noscript' => array(),
		);
		
		// Allowed JavaScript patterns - whitelist approach
		$this->allowed_js_patterns = array(
			// Basic variable declarations
			'/^(?:var|let|const)\s+[a-zA-Z_$][a-zA-Z0-9_$]*\s*=\s*.*;?\s*$/m',
			// Function declarations
			'/^function\s+[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*\{[\s\S]*\}\s*$/m',
			// Function calls to safe objects
			'/(?:window|document|console|navigator|location|history|localStorage|sessionStorage)\.[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*;?/m',
			// Event listeners
			'/addEventListener\s*\(\s*[\'"][a-zA-Z_$][a-zA-Z0-9_$]*[\'"]\s*,\s*function\s*\([^)]*\)\s*\{[\s\S]*\}\s*\)\s*;?/m',
			// jQuery patterns (if jQuery is available)
			'/\$\s*\(\s*[\'"][^\'\"]*[\'"]\s*\)\.[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*;?/m',
			'/jQuery\s*\(\s*[\'"][^\'\"]*[\'"]\s*\)\.[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*;?/m',
			// Google Analytics patterns
			'/(?:gtag|ga|_gaq)\.[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*;?/m',
			// Facebook Pixel patterns
			'/fbq\s*\(\s*[\'"][a-zA-Z_$][a-zA-Z0-9_$]*[\'"]\s*,\s*[^)]*\)\s*;?/m',
		);
		
		// Dangerous patterns to block - blacklist approach
		$this->dangerous_patterns = array(
			// PHP tags
			'/<\?php/i',
			'/<\?=/i',
			'/\?>/',
			// Dangerous JavaScript functions
			'/eval\s*\(/i',
			'/Function\s*\(/i',
			'/setTimeout\s*\(\s*[\'"]\s*[^\'"]*[\'"]\s*,\s*[^)]*\)/i',
			'/setInterval\s*\(\s*[\'"]\s*[^\'"]*[\'"]\s*,\s*[^)]*\)/i',
			// Document write
			'/document\.write\s*\(/i',
			'/document\.writeln\s*\(/i',
			// InnerHTML with potential XSS
			'/\.innerHTML\s*=/i',
			'/\.outerHTML\s*=/i',
			// Dangerous DOM manipulation
			'/\.insertAdjacentHTML\s*\(/i',
			// Cookie manipulation
			'/document\.cookie\s*=/i',
			// LocalStorage manipulation
			'/localStorage\.setItem\s*\(/i',
			'/localStorage\.getItem\s*\(/i',
			'/localStorage\.removeItem\s*\(/i',
			'/localStorage\.clear\s*\(/i',
			// SessionStorage manipulation
			'/sessionStorage\.setItem\s*\(/i',
			'/sessionStorage\.getItem\s*\(/i',
			'/sessionStorage\.removeItem\s*\(/i',
			'/sessionStorage\.clear\s*\(/i',
			// Form submission
			'/\.submit\s*\(\s*\)/i',
			// Window location manipulation
			'/window\.location\s*=/i',
			'/window\.location\.href\s*=/i',
			'/window\.location\.replace\s*\(/i',
			'/window\.location\.assign\s*\(/i',
			// Base64 encoding/decoding (often used for obfuscation)
			'/atob\s*\(/i',
			'/btoa\s*\(/i',
			// Script src with non-HTTPS or non-whitelisted domains
			'/<script[^>]*src\s*=\s*[\'"](?!https:\/\/)[^\'"]*[\'"][^>]*>/i',
			// Inline event handlers
			'/on\w+\s*=\s*[\'"][^\'\"]*[\'"]/i',
			// Data URLs
			'/data:\s*text\/html/i',
			'/data:\s*application\/javascript/i',
			// JavaScript pseudo-protocol
			'/javascript:/i',
			// VBScript
			'/vbscript:/i',
		);
	}

	/**
	 * Create the database table for code snippets.
	 *
	 * @since    1.0.0
	 */
	public function create_table() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $this->table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			code text NOT NULL,
			location varchar(20) NOT NULL DEFAULT 'header',
			status varchar(20) NOT NULL DEFAULT 'active',
			priority int(11) NOT NULL DEFAULT 10,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY location (location),
			KEY status (status),
			KEY priority (priority)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Get all code snippets from the database.
	 *
	 * @since    1.0.0
	 * @return   array    Array of code snippets.
	 */
	public function get_all_snippets() {
		global $wpdb;
		
		$results = $wpdb->get_results(
			"SELECT * FROM $this->table_name ORDER BY priority ASC, id DESC"
		);
		
		return $results;
	}

	/**
	 * Get a single code snippet by ID.
	 *
	 * @since    1.0.0
	 * @param    int      $id    The snippet ID.
	 * @return   object|null    The snippet object or null if not found.
	 */
	public function get_snippet( $id ) {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %d", $id )
		);
		
		return $result;
	}

	/**
	 * Validate and sanitize code snippet before saving.
	 *
	 * @since    1.0.0
	 * @param    string    $code    The code to validate.
	 * @return   array              Array with 'valid' boolean and 'message' string.
	 */
	public function validate_snippet_code( $code ) {
		// Check for dangerous patterns
		foreach ( $this->dangerous_patterns as $pattern ) {
			if ( preg_match( $pattern, $code ) ) {
				return array(
					'valid' => false,
					'message' => __( 'Code contains potentially dangerous elements and cannot be saved.', 'seo-simple' )
				);
			}
		}
		
		// Check if code contains HTML tags
		if ( preg_match( '/<[^>]+>/', $code ) ) {
			// Use wp_kses to validate HTML with our restrictive whitelist
			$filtered_code = wp_kses( $code, $this->allowed_html );
			
			// If anything was stripped, show an error
			if ( strlen( $filtered_code ) !== strlen( $code ) ) {
				return array(
					'valid' => false,
					'message' => __( 'HTML contains disallowed tags or attributes. Only basic script, style, link, meta, and noscript tags are allowed.', 'seo-simple' )
				);
			}
		} else {
			// For non-HTML code, check if it's JavaScript
			$trimmed_code = trim( $code );
			
			// Check if it looks like JavaScript
			$js_patterns = array(
				'/^(?:var|let|const|function|window|document|console|jQuery|\$)\s/',
				'/\{[\s\S]*\}/',  // Contains curly braces
				'/;[\s]*$/',      // Ends with semicolon
				'/\(\s*function\s*\(/',  // Function expression
			);
			
			$is_javascript = false;
			foreach ( $js_patterns as $pattern ) {
				if ( preg_match( $pattern, $trimmed_code ) ) {
					$is_javascript = true;
					break;
				}
			}
			
			// If it's JavaScript, validate against our allowed patterns
			if ( $is_javascript ) {
				$allowed = false;
				foreach ( $this->allowed_js_patterns as $pattern ) {
					if ( preg_match( $pattern, $trimmed_code ) ) {
						$allowed = true;
						break;
					}
				}
				
				// Also allow simple assignments and function calls
				if ( ! $allowed && 
				     ( preg_match( '/^[a-zA-Z_$][a-zA-Z0-9_$]*\s*=\s*.*;?\s*$/', $trimmed_code ) || // Simple assignment
				       preg_match( '/^[a-zA-Z_$][a-zA-Z0-9_$]*\s*\([^)]*\)\s*;?\s*$/', $trimmed_code ) ) ) { // Simple function call
					$allowed = true;
				}
				
				if ( ! $allowed ) {
					return array(
						'valid' => false,
						'message' => __( 'JavaScript code contains patterns that are not allowed. Only safe JavaScript patterns are permitted.', 'seo-simple' )
					);
				}
			}
		}
		
		return array(
			'valid' => true,
			'message' => __( 'Code validation passed.', 'seo-simple' )
		);
	}

	/**
	 * Add a new code snippet.
	 *
	 * @since    1.0.0
	 * @param    array    $data    The snippet data.
	 * @return   int|false    The new snippet ID or false on failure.
	 */
	public function add_snippet( $data ) {
		global $wpdb;
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->log_security_event( 'unauthorized_add_attempt', $data );
			return false;
		}
		
		// Validate the code before saving
		$validation = $this->validate_snippet_code( $data['code'] );
		if ( ! $validation['valid'] ) {
			$this->log_security_event( 'invalid_code_add_attempt', $data );
			return false;
		}
		
		$default_data = array(
			'name' => '',
			'code' => '',
			'location' => 'header',
			'status' => 'active',
			'priority' => 10,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' )
		);
		
		$data = wp_parse_args( $data, $default_data );
		
		// Sanitize the code
		$data['code'] = $this->sanitize_snippet_code( $data['code'] );
		
		$result = $wpdb->insert(
			$this->table_name,
			$data,
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
		
		if ( $result ) {
			$this->log_security_event( 'snippet_added', array( 'id' => $wpdb->insert_id, 'name' => $data['name'] ) );
			return $wpdb->insert_id;
		}
		
		return false;
	}

	/**
	 * Update an existing code snippet.
	 *
	 * @since    1.0.0
	 * @param    int      $id     The snippet ID.
	 * @param    array    $data   The snippet data.
	 * @return   bool     True on success, false on failure.
	 */
	public function update_snippet( $id, $data ) {
		global $wpdb;
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->log_security_event( 'unauthorized_update_attempt', array( 'id' => $id ) );
			return false;
		}
		
		// Validate the code before saving
		if ( isset( $data['code'] ) ) {
			$validation = $this->validate_snippet_code( $data['code'] );
			if ( ! $validation['valid'] ) {
				$this->log_security_event( 'invalid_code_update_attempt', array( 'id' => $id, 'code' => $data['code'] ) );
				return false;
			}
			
			// Sanitize the code
			$data['code'] = $this->sanitize_snippet_code( $data['code'] );
		}
		
		$data['updated_at'] = current_time( 'mysql' );
		
		$result = $wpdb->update(
			$this->table_name,
			$data,
			array( 'id' => $id ),
			array( '%s', '%s', '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			$this->log_security_event( 'snippet_updated', array( 'id' => $id ) );
		}
		
		return $result !== false;
	}

	/**
	 * Delete a code snippet.
	 *
	 * @since    1.0.0
	 * @param    int      $id    The snippet ID.
	 * @return   bool     True on success, false on failure.
	 */
	public function delete_snippet( $id ) {
		global $wpdb;
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->log_security_event( 'unauthorized_delete_attempt', array( 'id' => $id ) );
			return false;
		}
		
		$result = $wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			$this->log_security_event( 'snippet_deleted', array( 'id' => $id ) );
		}
		
		return $result !== false;
	}

	/**
	 * Get active code snippets by location.
	 *
	 * @since    1.0.0
	 * @param    string    $location    The location (header, footer, etc.).
	 * @return   array     Array of active code snippets.
	 */
	public function get_active_snippets_by_location( $location ) {
		global $wpdb;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $this->table_name WHERE location = %s AND status = %s ORDER BY priority ASC",
				$location,
				'active'
			)
		);
		
		return $results;
	}

	/**
	 * Inject header scripts.
	 *
	 * @since    1.0.0
	 */
	public function inject_header_scripts() {
		// Only run if not in admin
		if ( is_admin() ) {
			return;
		}
		
		$snippets = $this->get_active_snippets_by_location( 'header' );
		
		foreach ( $snippets as $snippet ) {
			// Log the execution attempt
			$this->log_security_event( 'snippet_executed', array( 'id' => $snippet->id, 'location' => 'header' ) );
			
			// Output the code with proper HTML handling
			echo $this->process_snippet_code( $snippet->code ) . "\n";
		}
	}

	/**
	 * Inject footer scripts.
	 *
	 * @since    1.0.0
	 */
	public function inject_footer_scripts() {
		// Only run if not in admin
		if ( is_admin() ) {
			return;
		}
		
		$snippets = $this->get_active_snippets_by_location( 'footer' );
		
		foreach ( $snippets as $snippet ) {
			// Log the execution attempt
			$this->log_security_event( 'snippet_executed', array( 'id' => $snippet->id, 'location' => 'footer' ) );
			
			// Output the code with proper HTML handling
			echo $this->process_snippet_code( $snippet->code ) . "\n";
		}
	}

	/**
	 * Inject body scripts.
	 *
	 * @since    1.0.0
	 */
	public function inject_body_scripts() {
		// Only run if not in admin
		if ( is_admin() ) {
			return;
		}
		
		$snippets = $this->get_active_snippets_by_location( 'body' );
		
		foreach ( $snippets as $snippet ) {
			// Log the execution attempt
			$this->log_security_event( 'snippet_executed', array( 'id' => $snippet->id, 'location' => 'body' ) );
			
			// Output the code with proper HTML handling
			echo $this->process_snippet_code( $snippet->code ) . "\n";
		}
	}

	/**
	 * Process snippet code to ensure proper HTML/JavaScript execution.
	 *
	 * @since    1.0.0
	 * @param    string    $code    The raw code from the snippet.
	 * @return   string             The processed code ready for output.
	 */
	private function process_snippet_code( $code ) {
		// Double-check the code is safe before processing
		$validation = $this->validate_snippet_code( $code );
		if ( ! $validation['valid'] ) {
			// Log the security violation
			$code_preview = is_string( $code ) ? substr( $code, 0, 100 ) : 'non-string value';
			$this->log_security_event( 'unsafe_code_blocked', array( 'code_preview' => $code_preview ) );
			
			// Return empty string to prevent execution
			return '';
		}
		
		// Sanitize the code
		$code = $this->sanitize_snippet_code( $code );
		
		// Check if the code already contains script tags
		if ( strpos( $code, '<script' ) !== false || strpos( $code, '<style' ) !== false || strpos( $code, '<noscript' ) !== false ) {
			// Code already contains HTML tags, output as-is after sanitization
			return $code;
		}
		
		// Check if the code looks like JavaScript (common patterns)
		$js_patterns = array(
			'/^(?:var|let|const|function|window|document|console|jQuery|\$)\s/',
			'/\{[\s\S]*\}/',  // Contains curly braces
			'/;[\s]*$/',      // Ends with semicolon
			'/\(\s*function\s*\(/',  // Function expression
		);
		
		$is_javascript = false;
		foreach ( $js_patterns as $pattern ) {
			if ( preg_match( $pattern, trim( $code ) ) ) {
				$is_javascript = true;
				break;
			}
		}
		
		// If it looks like JavaScript but doesn't have script tags, wrap it
		if ( $is_javascript ) {
			return '<script>' . "\n" . $code . "\n" . '</script>';
		}
		
		// For plain text or other content, output as-is
		return $code;
	}

	/**
	 * Sanitize snippet code.
	 *
	 * @since    1.0.0
	 * @param    string    $code    The code to sanitize.
	 * @return   string             The sanitized code.
	 */
	private function sanitize_snippet_code( $code ) {
		// If the code contains HTML, use wp_kses with our allowed HTML
		if ( preg_match( '/<[^>]+>/', $code ) ) {
			return wp_kses( $code, $this->allowed_html );
		}
		
		// For non-HTML code, just return as-is since we've already validated it
		return $code;
	}

	/**
	 * Log security events for monitoring.
	 *
	 * @since    1.0.0
	 * @param    string    $event_type    The type of event.
	 * @param    array     $data          Event data.
	 * @access   private
	 */
	private function log_security_event( $event_type, $data = array() ) {
		// Only log if WP_DEBUG is enabled or if this is a security-related event
		$security_events = array(
			'unauthorized_add_attempt',
			'unauthorized_update_attempt',
			'unauthorized_delete_attempt',
			'invalid_code_add_attempt',
			'invalid_code_update_attempt',
			'unsafe_code_blocked'
		);
		
		$log_level = in_array( $event_type, $security_events ) ? 'warning' : 'info';
		
		$log_message = sprintf(
			'[SEO Simple Code Snippets] %s: %s',
			$event_type,
			json_encode( $data )
		);
		
		// Log to WordPress debug log
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $log_message );
		}
		
		// For security events, also log to a dedicated security log if possible
		if ( in_array( $event_type, $security_events ) ) {
			// Check if a security log file is writable
			$security_log = WP_CONTENT_DIR . '/seo-simple-security.log';
			if ( is_writable( WP_CONTENT_DIR ) || file_exists( $security_log ) && is_writable( $security_log ) ) {
				$timestamp = current_time( 'mysql' );
				$user_id = get_current_user_id();
				$user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
				
				$log_entry = sprintf(
					"[%s] [%s] User ID: %s, IP: %s - %s\n",
					$timestamp,
					strtoupper( $log_level ),
					$user_id,
					$user_ip,
					$log_message
				);
				
				file_put_contents( $security_log, $log_entry, FILE_APPEND | LOCK_EX );
			}
		}
	}
}