<?php

/**
 * Smart Redirection Manager for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Smart Redirection Manager.
 *
 * This class handles advanced redirect management features including
 * automatic redirect suggestions, 404 monitoring, and bulk operations.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Redirect_Manager {

	/**
	 * The redirects handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Redirects    $redirects    The redirects handler instance.
	 */
	private $redirects;

	/**
	 * The 404 log table name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $log_table_name    The 404 log table name.
	 */
	private $log_table_name;

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache_Manager    $cache    The cache handler instance.
	 */
	private $cache;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Redirects    $redirects    The redirects handler instance.
	 */
	public function __construct( $redirects = null ) {
		$this->redirects = $redirects;
		
		if ( ! $this->redirects && class_exists( 'Seo_Simple_Redirects' ) ) {
			$this->redirects = new Seo_Simple_Redirects();
		}
		
		global $wpdb;
		$this->log_table_name = $wpdb->prefix . 'seo_simple_404_log';
		
		// Initialize cache handler
		$this->cache = Seo_Simple_Cache_Manager::get_instance();
		
		// Initialize hooks
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_hooks() {
		// Create tables on activation
		register_activation_hook( SEO_SIMPLE_PLUGIN_FILE, array( $this, 'create_tables' ) );
		
		// Log 404 errors
		add_action( '404_template', array( $this, 'log_404_error' ) );
		
		// Admin menu items - Commented out to avoid duplicate registration
		// This is handled in Seo_Simple_Admin class
		// add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_seo_simple_suggest_redirect', array( $this, 'ajax_suggest_redirect' ) );
		add_action( 'wp_ajax_seo_simple_bulk_create_redirects', array( $this, 'ajax_bulk_create_redirects' ) );
		add_action( 'wp_ajax_seo_simple_save_redirect', array( $this, 'ajax_save_redirect' ) );
		add_action( 'wp_ajax_seo_simple_delete_redirect', array( $this, 'ajax_delete_redirect' ) );
		add_action( 'wp_ajax_seo_simple_edit_redirect', array( $this, 'ajax_edit_redirect' ) );
		add_action( 'wp_ajax_seo_simple_create_redirect_from_suggestion', array( $this, 'ajax_create_redirect_from_suggestion' ) );
		add_action( 'wp_ajax_seo_simple_dismiss_suggestion', array( $this, 'ajax_dismiss_suggestion' ) );
		add_action( 'wp_ajax_seo_simple_import_redirects', array( $this, 'ajax_import_redirects' ) );
		add_action( 'wp_ajax_seo_simple_export_redirects', array( $this, 'ajax_export_redirects' ) );
		add_action( 'wp_ajax_seo_simple_clear_404_logs', array( $this, 'ajax_clear_404_logs' ) );
		add_action( 'wp_ajax_seo_simple_refresh_suggestions', array( $this, 'ajax_refresh_suggestions' ) );
		
		// Cron job for cleaning up old logs
		add_action( 'seo_simple_cleanup_404_logs', array( $this, 'cleanup_old_logs' ) );
		
		// Schedule cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'seo_simple_cleanup_404_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'seo_simple_cleanup_404_logs' );
		}
	}

	/**
	 * Create database tables.
	 *
	 * @since    1.0.0
	 */
	public function create_tables() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// Create 404 log table
		$sql = "CREATE TABLE IF NOT EXISTS {$this->log_table_name} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			url varchar(500) NOT NULL,
			referrer varchar(500) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			ip varchar(45) DEFAULT NULL,
			hit_count mediumint(9) NOT NULL DEFAULT 1,
			last_hit datetime DEFAULT CURRENT_TIMESTAMP,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY url (url(191)),
			KEY hit_count (hit_count),
			KEY last_hit (last_hit)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// Create redirect suggestions table
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		$sql = "CREATE TABLE IF NOT EXISTS {$suggestions_table} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			from_url varchar(500) NOT NULL,
			to_url varchar(500) NOT NULL,
			suggestion_type varchar(20) NOT NULL DEFAULT 'auto',
			confidence_score decimal(3,2) NOT NULL DEFAULT 0.00,
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY from_url (from_url(191)),
			KEY status (status),
			KEY confidence_score (confidence_score)
		) $charset_collate;";
		
		dbDelta( $sql );
	}

	/**
	 * Log 404 errors.
	 *
	 * @since    1.0.0
	 */
	public function log_404_error() {
		global $wpdb;
		
		// Don't log in admin
		if ( is_admin() ) {
			return;
		}
		
		// Get current URL
		$url = $this->get_current_url();
		
		// Don't log if already redirected
		if ( $this->redirects && $this->redirects->get_redirect( $url ) ) {
			return;
		}
		
		// Sanitize URL
		$url = esc_url_raw( $url );
		$url = substr( $url, 0, 500 ); // Limit to 500 characters
		
		if ( empty( $url ) ) {
			return;
		}
		
		// Get referrer, user agent, and IP
		$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : null;
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ) : null;
		$ip = $this->get_user_ip();
		
		// Check if URL already exists in log
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$this->log_table_name} WHERE url = %s",
			$url
		) );
		
		if ( $existing ) {
			// Update existing record
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$this->log_table_name} SET hit_count = hit_count + 1, last_hit = CURRENT_TIMESTAMP WHERE id = %d",
				$existing->id
			) );
		} else {
			// Insert new record
			$wpdb->insert(
				$this->log_table_name,
				array(
					'url' => $url,
					'referrer' => $referrer,
					'user_agent' => $user_agent,
					'ip' => $ip,
				),
				array( '%s', '%s', '%s', '%s' )
			);
		}
		
		// Check if we should suggest a redirect
		$this->maybe_suggest_redirect( $url );
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
	 * Get user IP address.
	 *
	 * @since    1.0.0
	 * @return   string    The user IP address.
	 */
	private function get_user_ip() {
		$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ips = explode( ',', $_SERVER[ $key ] );
				$ip = trim( $ips[0] );
				
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}
		
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
	}

	/**
	 * Suggest redirect for a 404 URL.
	 *
	 * @since    1.0.0
	 * @param    string    $url    The 404 URL.
	 */
	private function maybe_suggest_redirect( $url ) {
		global $wpdb;
		
		// Check if we already have a suggestion for this URL
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$suggestions_table} WHERE from_url = %s AND status = %s",
			$url,
			'pending'
		) );
		
		if ( $existing ) {
			return;
		}
		
		// Try to find a matching URL
		$suggested_url = $this->find_best_match( $url );
		
		if ( $suggested_url ) {
			$confidence = $this->calculate_confidence_score( $url, $suggested_url );
			
			// Only suggest if confidence is above threshold
			if ( $confidence >= 0.7 ) {
				$wpdb->insert(
					$suggestions_table,
					array(
						'from_url' => $url,
						'to_url' => $suggested_url,
						'suggestion_type' => 'auto',
						'confidence_score' => $confidence,
						'status' => 'pending',
					),
					array( '%s', '%s', '%s', '%f', '%s' )
				);
			}
		}
	}

	/**
	 * Find the best matching URL for a 404.
	 *
	 * @since    1.0.0
	 * @param    string    $url    The 404 URL.
	 * @return   string|false        The best matching URL or false if no match found.
	 */
	private function find_best_match( $url ) {
		// Parse URL to get path
		$url_parts = parse_url( $url );
		$path = isset( $url_parts['path'] ) ? $url_parts['path'] : '';
		$path = trailingslashit( $path );
		
		// Try different matching strategies
		$matches = array();
		
		// Strategy 1: Check for posts with similar slugs
		$slug = basename( $path );
		if ( ! empty( $slug ) ) {
			$post = get_page_by_path( $slug, OBJECT, array( 'post', 'page' ) );
			if ( $post ) {
				$matches[] = array(
					'url' => get_permalink( $post ),
					'score' => 0.9,
				);
			}
		}
		
		// Strategy 2: Check for similar paths
		$possible_matches = $this->find_similar_paths( $path );
		$matches = array_merge( $matches, $possible_matches );
		
		// Strategy 3: Check for common typos
		$typo_matches = $this->find_typo_matches( $path );
		$matches = array_merge( $matches, $typo_matches );
		
		// Sort by score and return the best match
		if ( ! empty( $matches ) ) {
			usort( $matches, function( $a, $b ) {
				return $b['score'] <=> $a['score'];
			} );
			
			return $matches[0]['url'];
		}
		
		return false;
	}

	/**
	 * Find similar paths.
	 *
	 * @since    1.0.0
	 * @param    string    $path    The path to match.
	 * @return   array              Array of similar paths with scores.
	 */
	private function find_similar_paths( $path ) {
		$matches = array();
		
		// Get all published posts and pages
		$posts = get_posts( array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		foreach ( $posts as $post ) {
			$post_path = parse_url( get_permalink( $post ), PHP_URL_PATH );
			
			// Calculate similarity
			$similarity = 0;
			similar_text( $path, $post_path, $similarity );
			
			if ( $similarity > 70 ) {
				$matches[] = array(
					'url' => get_permalink( $post ),
					'score' => $similarity / 100,
				);
			}
		}
		
		return $matches;
	}

	/**
	 * Find typo matches.
	 *
	 * @since    1.0.0
	 * @param    string    $path    The path to match.
	 * @return   array              Array of typo matches with scores.
	 */
	private function find_typo_matches( $path ) {
		$matches = array();
		
		// Get all published posts and pages
		$posts = get_posts( array(
			'post_type' => array( 'post', 'page' ),
			'post_status' => 'publish',
			'numberposts' => -1,
		) );
		
		foreach ( $posts as $post ) {
			$post_path = parse_url( get_permalink( $post ), PHP_URL_PATH );
			
			// Check for common typo patterns
			if ( $this->is_likely_typo( $path, $post_path ) ) {
				$matches[] = array(
					'url' => get_permalink( $post ),
					'score' => 0.8,
				);
			}
		}
		
		return $matches;
	}

	/**
	 * Check if a path is likely a typo of another.
	 *
	 * @since    1.0.0
	 * @param    string    $path1    The first path.
	 * @param    string    $path2    The second path.
	 * @return   bool                True if likely a typo.
	 */
	private function is_likely_typo( $path1, $path2 ) {
		// Remove common prefixes and suffixes
		$path1 = trim( $path1, '/' );
		$path2 = trim( $path2, '/' );
		
		// Check if they have similar lengths
		$len_diff = abs( strlen( $path1 ) - strlen( $path2 ) );
		if ( $len_diff > 3 ) {
			return false;
		}
		
		// Calculate Levenshtein distance
		$distance = levenshtein( $path1, $path2 );
		$max_len = max( strlen( $path1 ), strlen( $path2 ) );
		
		// If distance is less than 30% of the max length, it's likely a typo
		return ( $distance / $max_len ) < 0.3;
	}

	/**
	 * Calculate confidence score for a redirect suggestion.
	 *
	 * @since    1.0.0
	 * @param    string    $from_url    The source URL.
	 * @param    string    $to_url      The target URL.
	 * @return   float                  The confidence score (0-1).
	 */
	private function calculate_confidence_score( $from_url, $to_url ) {
		$score = 0.5; // Base score
		
		// Check if paths are similar
		$from_path = parse_url( $from_url, PHP_URL_PATH );
		$to_path = parse_url( $to_url, PHP_URL_PATH );
		
		similar_text( $from_path, $to_path, $similarity );
		$score += ( $similarity / 100 ) * 0.3;
		
		// Check if slugs are similar
		$from_slug = basename( $from_path );
		$to_slug = basename( $to_path );
		
		if ( $from_slug === $to_slug ) {
			$score += 0.2;
		} else {
			similar_text( $from_slug, $to_slug, $slug_similarity );
			$score += ( $slug_similarity / 100 ) * 0.1;
		}
		
		// Ensure score is between 0 and 1
		return min( 1, max( 0, $score ) );
	}

	/**
	 * Add admin menu items.
	 *
	 * @since    1.0.0
	 * @deprecated 1.0.0 Menu registration moved to Seo_Simple_Admin class to avoid duplicates
	 */
	public function add_admin_menu() {
		// This method is deprecated - menu registration is handled in Seo_Simple_Admin class
		// to prevent duplicate registration of the redirect manager page
		return;
	}

	/**
	 * Render admin page.
	 *
	 * @since    1.0.0
	 * @deprecated 1.0.0 Page rendering moved to Seo_Simple_Redirect_Admin class
	 */
	public function render_admin_page() {
		// This method is deprecated - page rendering is handled in Seo_Simple_Redirect_Admin class
		// to avoid duplicate page rendering
		return;
	}

	/**
	 * Render the redirects tab.
	 *
	 * @since    1.0.0
	 */
	public function render_redirects_tab() {
		// Get redirects if the class exists
		$redirects = array();
		if ( class_exists( 'Seo_Simple_Redirects' ) ) {
			$redirect_handler = new Seo_Simple_Redirects();
			$redirects = $redirect_handler->get_redirects( array( 'limit' => 100 ) );
		}
		?>
		<div class="seo-simple-redirects-tab">
			<div class="tab-header">
				<h3><?php esc_html_e( 'Manage Redirects', 'seo-simple' ); ?></h3>
				<button type="button" class="button button-primary" id="seo-simple-add-redirect">
					<?php esc_html_e( 'Add New Redirect', 'seo-simple' ); ?>
				</button>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-from"><?php esc_html_e( 'From URL', 'seo-simple' ); ?></th>
						<th scope="col" class="manage-column column-to"><?php esc_html_e( 'To URL', 'seo-simple' ); ?></th>
						<th scope="col" class="manage-column column-type"><?php esc_html_e( 'Type', 'seo-simple' ); ?></th>
						<th scope="col" class="manage-column column-hits"><?php esc_html_e( 'Hits', 'seo-simple' ); ?></th>
						<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'seo-simple' ); ?></th>
						<th scope="col" class="manage-column column-actions"><?php esc_html_e( 'Actions', 'seo-simple' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $redirects ) ) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'No redirects found.', 'seo-simple' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $redirects as $redirect ) : ?>
							<tr>
								<td><?php echo esc_html( $redirect['from_url'] ); ?></td>
								<td><?php echo esc_html( $redirect['to_url'] ); ?></td>
								<td><?php echo esc_html( $redirect['redirect_type'] ); ?></td>
								<td><?php echo esc_html( $redirect['hit_count'] ); ?></td>
								<td>
									<span class="status-<?php echo $redirect['status'] ? 'active' : 'inactive'; ?>">
										<?php echo $redirect['status'] ? esc_html__( 'Active', 'seo-simple' ) : esc_html__( 'Inactive', 'seo-simple' ); ?>
									</span>
								</td>
								<td>
									<button type="button" class="button button-small seo-simple-edit-redirect" data-id="<?php echo esc_attr( $redirect['id'] ); ?>">
										<?php esc_html_e( 'Edit', 'seo-simple' ); ?>
									</button>
									<button type="button" class="button button-small seo-simple-delete-redirect" data-id="<?php echo esc_attr( $redirect['id'] ); ?>">
										<?php esc_html_e( 'Delete', 'seo-simple' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			
			<!-- Add/Edit Redirect Modal -->
			<div id="seo-simple-redirect-modal" class="seo-simple-modal" style="display: none;">
				<div class="seo-simple-modal-content">
					<div class="seo-simple-modal-header">
						<h3><?php esc_html_e( 'Add Redirect', 'seo-simple' ); ?></h3>
						<button type="button" class="seo-simple-modal-close">&times;</button>
					</div>
					<div class="seo-simple-modal-body">
						<form id="seo-simple-redirect-form">
							<div class="form-row">
								<label for="seo-simple-from-url"><?php esc_html_e( 'From URL:', 'seo-simple' ); ?></label>
								<input type="text" id="seo-simple-from-url" name="from_url" class="regular-text" required>
								<p class="description"><?php esc_html_e( 'The URL to redirect from (without domain).', 'seo-simple' ); ?></p>
							</div>
							
							<div class="form-row">
								<label for="seo-simple-to-url"><?php esc_html_e( 'To URL:', 'seo-simple' ); ?></label>
								<input type="text" id="seo-simple-to-url" name="to_url" class="regular-text" required>
								<p class="description"><?php esc_html_e( 'The URL to redirect to (with domain for external URLs).', 'seo-simple' ); ?></p>
							</div>
							
							<div class="form-row">
								<label for="seo-simple-redirect-type"><?php esc_html_e( 'Redirect Type:', 'seo-simple' ); ?></label>
								<select id="seo-simple-redirect-type" name="redirect_type">
									<option value="301"><?php esc_html_e( '301 Permanent', 'seo-simple' ); ?></option>
									<option value="302"><?php esc_html_e( '302 Temporary', 'seo-simple' ); ?></option>
									<option value="307"><?php esc_html_e( '307 Temporary', 'seo-simple' ); ?></option>
									<option value="308"><?php esc_html_e( '308 Permanent', 'seo-simple' ); ?></option>
								</select>
							</div>
							
							<div class="form-row">
								<label>
									<input type="checkbox" id="seo-simple-redirect-status" name="status" value="1" checked>
									<?php esc_html_e( 'Active', 'seo-simple' ); ?>
								</label>
							</div>
							
							<div class="form-row">
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Redirect', 'seo-simple' ); ?></button>
								<button type="button" class="button seo-simple-modal-cancel"><?php esc_html_e( 'Cancel', 'seo-simple' ); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the 404s tab.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Redirect_Manager    $redirect_manager    The redirect manager instance.
	 */
	public function render_404s_tab( $redirect_manager ) {
		$logs = $redirect_manager->get_404_logs( array( 'limit' => 50 ) );
		$redirect_stats = $redirect_manager->get_redirect_stats();
		$top_404s = $redirect_stats['top_404s'] ?? array();
		?>
		<div class="seo-simple-404s-tab">
			<div class="tab-header">
				<h3><?php esc_html_e( '404 Error Monitor', 'seo-simple' ); ?></h3>
				<button type="button" class="button" id="seo-simple-clear-404-logs">
					<?php esc_html_e( 'Clear Logs', 'seo-simple' ); ?>
				</button>
			</div>
			
			<?php if ( ! empty( $top_404s ) ) : ?>
				<div class="seo-simple-top-404s">
					<h4><?php esc_html_e( 'Top 404 URLs', 'seo-simple' ); ?></h4>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'URL', 'seo-simple' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Hits', 'seo-simple' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Actions', 'seo-simple' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_404s as $log ) : ?>
								<tr>
									<td><?php echo esc_html( $log->url ); ?></td>
									<td><?php echo esc_html( $log->hit_count ); ?></td>
									<td>
										<button type="button" class="button button-small seo-simple-suggest-redirect" data-url="<?php echo esc_attr( $log->url ); ?>">
											<?php esc_html_e( 'Suggest Redirect', 'seo-simple' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
			
			<h4><?php esc_html_e( 'Recent 404 Logs', 'seo-simple' ); ?></h4>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'URL', 'seo-simple' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Referrer', 'seo-simple' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Hits', 'seo-simple' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Last Hit', 'seo-simple' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Actions', 'seo-simple' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No 404 errors logged yet.', 'seo-simple' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( $log->url ); ?></td>
								<td><?php echo $log->referrer ? esc_html( $log->referrer ) : '-'; ?></td>
								<td><?php echo esc_html( $log->hit_count ); ?></td>
								<td><?php echo esc_html( $log->last_hit ); ?></td>
								<td>
									<button type="button" class="button button-small seo-simple-suggest-redirect" data-url="<?php echo esc_attr( $log->url ); ?>">
										<?php esc_html_e( 'Suggest Redirect', 'seo-simple' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render the suggestions tab.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Redirect_Manager    $redirect_manager    The redirect manager instance.
	 */
	public function render_suggestions_tab( $redirect_manager ) {
		$suggestions = $redirect_manager->get_redirect_suggestions();
		?>
		<div class="seo-simple-suggestions-tab">
			<div class="tab-header">
				<h3><?php esc_html_e( 'Redirect Suggestions', 'seo-simple' ); ?></h3>
				<div class="tab-actions">
					<button type="button" class="button" id="seo-simple-refresh-suggestions">
						<?php esc_html_e( 'Refresh', 'seo-simple' ); ?>
					</button>
					<button type="button" class="button button-primary" id="seo-simple-create-all-redirects">
						<?php esc_html_e( 'Create All', 'seo-simple' ); ?>
					</button>
				</div>
			</div>
			
			<form id="seo-simple-suggestions-form">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th class="check-column">
								<input type="checkbox" id="seo-simple-select-all-suggestions">
							</th>
							<th scope="col"><?php esc_html_e( 'From URL', 'seo-simple' ); ?></th>
							<th scope="col"><?php esc_html_e( 'To URL', 'seo-simple' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Confidence', 'seo-simple' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'seo-simple' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $suggestions ) ) : ?>
							<tr>
								<td colspan="5"><?php esc_html_e( 'No redirect suggestions found.', 'seo-simple' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $suggestions as $suggestion ) : ?>
								<tr>
									<th class="check-column">
										<input type="checkbox" name="suggestions[]" value="<?php echo esc_attr( $suggestion->id ); ?>" class="seo-simple-suggestion-checkbox">
									</th>
									<td>
										<?php echo esc_html( $suggestion->from_url ); ?>
										<div class="confidence-indicator" style="width: <?php echo esc_attr( $suggestion->confidence_score * 100 ); ?>%;"></div>
									</td>
									<td><?php echo esc_html( $suggestion->to_url ); ?></td>
									<td>
										<span class="confidence-score"><?php echo esc_html( round( $suggestion->confidence_score * 100 ) ); ?>%</span>
									</td>
									<td>
										<button type="button" class="button button-small seo-simple-create-redirect" data-id="<?php echo esc_attr( $suggestion->id ); ?>" data-from="<?php echo esc_attr( $suggestion->from_url ); ?>" data-to="<?php echo esc_attr( $suggestion->to_url ); ?>">
											<?php esc_html_e( 'Create', 'seo-simple' ); ?>
										</button>
										<button type="button" class="button button-small seo-simple-dismiss-suggestion" data-id="<?php echo esc_attr( $suggestion->id ); ?>">
											<?php esc_html_e( 'Dismiss', 'seo-simple' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				
				<div class="tab-footer">
					<button type="button" class="button button-primary" id="seo-simple-create-selected-redirects">
						<?php esc_html_e( 'Create Selected', 'seo-simple' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the import/export tab.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Redirect_Manager    $redirect_manager    The redirect manager instance.
	 */
	public function render_import_tab( $redirect_manager ) {
		?>
		<div class="seo-simple-import-tab">
			<div class="import-section">
				<h3><?php esc_html_e( 'Import Redirects', 'seo-simple' ); ?></h3>
				
				<div class="import-method">
					<h4><?php esc_html_e( 'Import from CSV', 'seo-simple' ); ?></h4>
					<p><?php esc_html_e( 'Upload a CSV file with columns: from_url, to_url, redirect_type (optional).', 'seo-simple' ); ?></p>
					<form id="seo-simple-import-csv-form" enctype="multipart/form-data">
						<input type="file" name="csv_file" accept=".csv" class="regular-text">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Import CSV', 'seo-simple' ); ?></button>
					</form>
				</div>
				
				<div class="import-method">
					<h4><?php esc_html_e( 'Import from .htaccess', 'seo-simple' ); ?></h4>
					<p><?php esc_html_e( 'Paste your .htaccess redirect rules below.', 'seo-simple' ); ?></p>
					<form id="seo-simple-import-htaccess-form">
						<textarea name="htaccess_content" rows="10" class="large-text"></textarea>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Import .htaccess', 'seo-simple' ); ?></button>
					</form>
				</div>
			</div>
			
			<div class="export-section">
				<h3><?php esc_html_e( 'Export Redirects', 'seo-simple' ); ?></h3>
				<p><?php esc_html_e( 'Export your current redirects to a CSV file.', 'seo-simple' ); ?></p>
				<form id="seo-simple-export-form">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Export to CSV', 'seo-simple' ); ?></button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Get 404 logs.
	 *
	 * @since    1.0.0
	 * @param    array     $args    Query arguments.
	 * @return   array              Array of 404 logs.
	 */
	public function get_404_logs( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'limit' => 50,
			'offset' => 0,
			'orderby' => 'hit_count',
			'order' => 'DESC',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$cache_key = 'seo_simple_404_logs_' . md5( serialize( $args ) );
		
		return $this->cache->remember( $cache_key, function() use ( $args, $wpdb ) {
			$sql = "SELECT * FROM {$this->log_table_name}";
			
			// Add order by
			$allowed_orderby = array( 'id', 'url', 'hit_count', 'last_hit', 'created_at' );
			if ( in_array( $args['orderby'], $allowed_orderby ) ) {
				$sql .= " ORDER BY {$args['orderby']} " . strtoupper( $args['order'] );
			}
			
			// Add limit
			$sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );
			
			return $wpdb->get_results( $sql );
		}, 3600 );
	}

	/**
	 * Get redirect suggestions.
	 *
	 * @since    1.0.0
	 * @param    array     $args    Query arguments.
	 * @return   array              Array of redirect suggestions.
	 */
	public function get_redirect_suggestions( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'status' => 'pending',
			'limit' => 50,
			'offset' => 0,
			'orderby' => 'confidence_score',
			'order' => 'DESC',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		$sql = "SELECT * FROM {$suggestions_table}";
		
		$where = array();
		$prepare_args = array();
		
		// Add status filter
		if ( $args['status'] ) {
			$where[] = "status = %s";
			$prepare_args[] = $args['status'];
		}
		
		if ( ! empty( $where ) ) {
			$sql .= " WHERE " . implode( " AND ", $where );
		}
		
		// Add order by
		$allowed_orderby = array( 'id', 'from_url', 'to_url', 'confidence_score', 'created_at' );
		if ( in_array( $args['orderby'], $allowed_orderby ) ) {
			$sql .= " ORDER BY {$args['orderby']} " . strtoupper( $args['order'] );
		}
		
		// Add limit
		$sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );
		
		if ( ! empty( $prepare_args ) ) {
			$sql = $wpdb->prepare( $sql, $prepare_args );
		}
		
		return $wpdb->get_results( $sql );
	}

	/**
	 * AJAX handler for suggesting redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_suggest_redirect() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}
		
		$from_url = isset( $_POST['from_url'] ) ? esc_url_raw( $_POST['from_url'] ) : '';
		
		if ( empty( $from_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide a URL.', 'seo-simple' ) ) );
		}
		
		$suggested_url = $this->find_best_match( $from_url );
		
		if ( $suggested_url ) {
			$confidence = $this->calculate_confidence_score( $from_url, $suggested_url );
			wp_send_json_success( array(
				'suggested_url' => $suggested_url,
				'confidence' => $confidence,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'No suitable redirect found.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for bulk creating redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_create_redirects() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}
		
		$suggestions = isset( $_POST['suggestions'] ) ? $_POST['suggestions'] : array();
		
		if ( empty( $suggestions ) || ! is_array( $suggestions ) ) {
			wp_send_json_error( array( 'message' => __( 'No suggestions selected.', 'seo-simple' ) ) );
		}
		
		$created = 0;
		$failed = 0;
		global $wpdb;
		
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		
		foreach ( $suggestions as $suggestion_id ) {
			$suggestion = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$suggestions_table} WHERE id = %d",
				$suggestion_id
			) );
			
			if ( $suggestion && $this->redirects ) {
				// Create the redirect
				$result = $this->redirects->add_redirect(
					$suggestion->from_url,
					$suggestion->to_url,
					301,
					1
				);
				
				if ( $result ) {
					// Update suggestion status
					$wpdb->update(
						$suggestions_table,
						array( 'status' => 'created' ),
						array( 'id' => $suggestion_id ),
						array( '%s' ),
						array( '%d' )
					);
					$created++;
				} else {
					$failed++;
				}
			} else {
				$failed++;
			}
		}
		
		wp_send_json_success( array(
			'created' => $created,
			'failed' => $failed,
			'message' => sprintf(
				__( 'Created %d redirects. %d failed.', 'seo-simple' ),
				$created,
				$failed
			),
		) );
	}

	/**
	 * Clean up old 404 logs.
	 *
	 * @since    1.0.0
	 */
	public function cleanup_old_logs() {
		global $wpdb;
		
		// Delete logs older than 30 days
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$this->log_table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		) );
		
		// Delete old suggestions (created more than 7 days ago)
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$suggestions_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = %s",
			'pending'
		) );
	}

	/**
	 * Get redirect statistics.
	 *
	 * @since    1.0.0
	 * @return   array    Redirect statistics.
	 */
	public function get_redirect_stats() {
		global $wpdb;
		
		$cache_key = 'seo_simple_redirect_stats';
		
		return $this->cache->remember( $cache_key, function() use ( $wpdb ) {
			$stats = array();
			
			// Total redirects
			if ( $this->redirects ) {
				$stats['total_redirects'] = $this->redirects->count_redirects();
			} else {
				$stats['total_redirects'] = 0;
			}
			
			// Total 404s
			$stats['total_404s'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->log_table_name}" );
			
			// Top 404 URLs
			$stats['top_404s'] = $wpdb->get_results(
				"SELECT url, hit_count FROM {$this->log_table_name} ORDER BY hit_count DESC LIMIT 10"
			);
			
			// Pending suggestions
			$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
			$stats['pending_suggestions'] = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$suggestions_table} WHERE status = %s",
				'pending'
			) );
			
			return $stats;
		}, 3600 );
	}

	/**
	 * Import redirects from various sources.
	 *
	 * @since    1.0.0
	 * @param    string    $source    The import source (csv, htaccess, etc.).
	 * @param    string    $data      The data to import.
	 * @return   array               Import results.
	 */
	public function import_redirects( $source, $data ) {
		$results = array(
			'success' => 0,
			'errors' => 0,
			'messages' => array(),
		);
		
		switch ( $source ) {
			case 'csv':
				$results = $this->import_from_csv( $data );
				break;
			
			case 'htaccess':
				$results = $this->import_from_htaccess( $data );
				break;
			
			default:
				$results['errors']++;
				$results['messages'][] = __( 'Unknown import source.', 'seo-simple' );
				break;
		}
		
		return $results;
	}

	/**
	 * Import redirects from CSV.
	 *
	 * @since    1.0.0
	 * @param    string    $csv_data    The CSV data.
	 * @return   array                  Import results.
	 */
	private function import_from_csv( $csv_data ) {
		$results = array(
			'success' => 0,
			'errors' => 0,
			'messages' => array(),
		);
		
		// Parse CSV
		$lines = explode( "\n", $csv_data );
		$header = array_shift( $lines );
		
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}
			
			$fields = str_getcsv( $line );
			
			if ( count( $fields ) < 2 ) {
				$results['errors']++;
				$results['messages'][] = __( 'Invalid CSV format.', 'seo-simple' );
				continue;
			}
			
			$from_url = $fields[0];
			$to_url = $fields[1];
			$redirect_type = isset( $fields[2] ) ? intval( $fields[2] ) : 301;
			
			if ( $this->redirects && $this->redirects->add_redirect( $from_url, $to_url, $redirect_type ) ) {
				$results['success']++;
			} else {
				$results['errors']++;
				$results['messages'][] = sprintf( __( 'Failed to add redirect: %s → %s', 'seo-simple' ), $from_url, $to_url );
			}
		}
		
		return $results;
	}

	/**
	 * Import redirects from .htaccess.
	 *
	 * @since    1.0.0
	 * @param    string    $htaccess_data    The .htaccess data.
	 * @return   array                        Import results.
	 */
	private function import_from_htaccess( $htaccess_data ) {
		$results = array(
			'success' => 0,
			'errors' => 0,
			'messages' => array(),
		);
		
		// Match redirect rules
		preg_match_all( '/RewriteRule\s+(.+?)\s+(.+?)\s+\[R=([0-9]+),?([^\]]*)\]/i', $htaccess_data, $matches, PREG_SET_ORDER );
		
		foreach ( $matches as $match ) {
			$from_url = $match[1];
			$to_url = $match[2];
			$redirect_type = intval( $match[3] );
			
			// Clean up URLs
			$from_url = str_replace( '\\', '', $from_url );
			$to_url = str_replace( '\\', '', $to_url );
			
			if ( $this->redirects && $this->redirects->add_redirect( $from_url, $to_url, $redirect_type ) ) {
				$results['success']++;
			} else {
				$results['errors']++;
				$results['messages'][] = sprintf( __( 'Failed to add redirect: %s → %s', 'seo-simple' ), $from_url, $to_url );
			}
		}
		
		return $results;
	}

	/**
	 * AJAX handler for saving redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_redirect() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$from_url = isset( $_POST['from_url'] ) ? sanitize_text_field( $_POST['from_url'] ) : '';
		$to_url = isset( $_POST['to_url'] ) ? esc_url_raw( $_POST['to_url'] ) : '';
		$redirect_type = isset( $_POST['redirect_type'] ) ? intval( $_POST['redirect_type'] ) : 301;
		$status = isset( $_POST['status'] ) ? intval( $_POST['status'] ) : 1;
		$redirect_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if ( empty( $from_url ) || empty( $to_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide both from and to URLs.', 'seo-simple' ) ) );
		}
		
		if ( ! $this->redirects ) {
			wp_send_json_error( array( 'message' => __( 'Redirect handler not available.', 'seo-simple' ) ) );
		}
		
		$result = false;
		if ( $redirect_id > 0 ) {
			// Update existing redirect
			$result = $this->redirects->update_redirect_full( $redirect_id, $from_url, $to_url, $redirect_type, $status );
		} else {
			// Add new redirect
			$result = $this->redirects->add_redirect( $from_url, $to_url, $redirect_type, $status );
		}
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Redirect saved successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for deleting redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_delete_redirect() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$redirect_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if ( $redirect_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid redirect ID.', 'seo-simple' ) ) );
		}
		
		if ( ! $this->redirects ) {
			wp_send_json_error( array( 'message' => __( 'Redirect handler not available.', 'seo-simple' ) ) );
		}
		
		$result = $this->redirects->delete_redirect( $redirect_id );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Redirect deleted successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for editing redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_edit_redirect() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$redirect_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		
		if ( $redirect_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid redirect ID.', 'seo-simple' ) ) );
		}
		
		if ( ! $this->redirects ) {
			wp_send_json_error( array( 'message' => __( 'Redirect handler not available.', 'seo-simple' ) ) );
		}
		
		$redirect = $this->redirects->get_redirect_by_id( $redirect_id );
		
		if ( $redirect ) {
			wp_send_json_success( array( 'redirect' => $redirect ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Redirect not found.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for creating redirect from suggestion.
	 *
	 * @since    1.0.0
	 */
	public function ajax_create_redirect_from_suggestion() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$suggestion_id = isset( $_POST['suggestion_id'] ) ? intval( $_POST['suggestion_id'] ) : 0;
		$from_url = isset( $_POST['from_url'] ) ? sanitize_text_field( $_POST['from_url'] ) : '';
		$to_url = isset( $_POST['to_url'] ) ? esc_url_raw( $_POST['to_url'] ) : '';
		
		if ( $suggestion_id <= 0 || empty( $from_url ) || empty( $to_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'seo-simple' ) ) );
		}
		
		if ( ! $this->redirects ) {
			wp_send_json_error( array( 'message' => __( 'Redirect handler not available.', 'seo-simple' ) ) );
		}
		
		// Create the redirect
		$result = $this->redirects->add_redirect( $from_url, $to_url, 301, 1 );
		
		if ( $result ) {
			// Update suggestion status
			global $wpdb;
			$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
			$wpdb->update(
				$suggestions_table,
				array( 'status' => 'created' ),
				array( 'id' => $suggestion_id ),
				array( '%s' ),
				array( '%d' )
			);
			
			wp_send_json_success( array( 'message' => __( 'Redirect created successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for dismissing suggestions.
	 *
	 * @since    1.0.0
	 */
	public function ajax_dismiss_suggestion() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$suggestion_id = isset( $_POST['suggestion_id'] ) ? intval( $_POST['suggestion_id'] ) : 0;
		
		if ( $suggestion_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid suggestion ID.', 'seo-simple' ) ) );
		}
		
		// Update suggestion status
		global $wpdb;
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		$result = $wpdb->update(
			$suggestions_table,
			array( 'status' => 'dismissed' ),
			array( 'id' => $suggestion_id ),
			array( '%s' ),
			array( '%d' )
		);
		
		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => __( 'Suggestion dismissed.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to dismiss suggestion.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for importing redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_import_redirects() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$source = isset( $_POST['source'] ) ? sanitize_text_field( $_POST['source'] ) : '';
		$data = '';
		
		if ( $source === 'csv' && isset( $_FILES['csv_file'] ) ) {
			// Handle file upload
			if ( $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
				wp_send_json_error( array( 'message' => __( 'File upload failed.', 'seo-simple' ) ) );
			}
			
			$data = file_get_contents( $_FILES['csv_file']['tmp_name'] );
		} elseif ( $source === 'htaccess' && isset( $_POST['data'] ) ) {
			$data = wp_unslash( $_POST['data'] );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid import source or missing data.', 'seo-simple' ) ) );
		}
		
		$results = $this->import_redirects( $source, $data );
		
		if ( $results['success'] > 0 || $results['errors'] > 0 ) {
			$message = sprintf(
				__( 'Import completed: %d redirects imported successfully, %d errors.', 'seo-simple' ),
				$results['success'],
				$results['errors']
			);
			
			wp_send_json_success( array(
				'message' => $message,
				'success' => $results['success'],
				'errors' => $results['errors'],
				'details' => $results['messages']
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'No redirects were imported.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for exporting redirects.
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_redirects() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}
		
		if ( ! $this->redirects ) {
			wp_die( __( 'Redirect handler not available.', 'seo-simple' ) );
		}
		
		// Get all redirects
		$redirects = $this->redirects->get_redirects( array( 'limit' => 10000 ) );
		
		// Generate CSV
		$csv = "from_url,to_url,redirect_type,status,hit_count\n";
		
		foreach ( $redirects as $redirect ) {
			$csv .= sprintf(
				"%s,%s,%d,%d,%d\n",
				$redirect['from_url'],
				$redirect['to_url'],
				$redirect['redirect_type'],
				$redirect['status'],
				$redirect['hit_count']
			);
		}
		
		// Set headers for download
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="seo-simple-redirects-' . date( 'Y-m-d' ) . '.csv"' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		
		echo $csv;
		exit;
	}

	/**
	 * AJAX handler for clearing 404 logs.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_404_logs() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		global $wpdb;
		
		// Delete all 404 logs
		$result = $wpdb->query( "TRUNCATE TABLE {$this->log_table_name}" );
		
		if ( $result !== false ) {
			// Also clear any pending suggestions
			$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$suggestions_table} WHERE status = %s",
					'pending'
				)
			);
			
			wp_send_json_success( array( 'message' => __( '404 logs cleared successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear 404 logs.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for refreshing suggestions.
	 *
	 * @since    1.0.0
	 */
	public function ajax_refresh_suggestions() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_redirect_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		global $wpdb;
		
		// Get recent 404s that don't have suggestions yet
		$recent_404s = $wpdb->get_results(
			"SELECT url FROM {$this->log_table_name}
			 WHERE hit_count >= 3
			 ORDER BY hit_count DESC
			 LIMIT 20"
		);
		
		$suggestions_created = 0;
		
		foreach ( $recent_404s as $log ) {
			// Check if we already have a suggestion for this URL
			$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
			$existing = $wpdb->get_row( $wpdb->prepare(
				"SELECT id FROM {$suggestions_table} WHERE from_url = %s AND status = %s",
				$log->url,
				'pending'
			) );
			
			if ( ! $existing ) {
				// Try to find a matching URL
				$suggested_url = $this->find_best_match( $log->url );
				
				if ( $suggested_url ) {
					$confidence = $this->calculate_confidence_score( $log->url, $suggested_url );
					
					// Only suggest if confidence is above threshold
					if ( $confidence >= 0.7 ) {
						$wpdb->insert(
							$suggestions_table,
							array(
								'from_url' => $log->url,
								'to_url' => $suggested_url,
								'suggestion_type' => 'auto',
								'confidence_score' => $confidence,
								'status' => 'pending',
							),
							array( '%s', '%s', '%s', '%f', '%s' )
						);
						$suggestions_created++;
					}
				}
			}
		}
		
		wp_send_json_success( array(
			'message' => sprintf(
				__( 'Created %d new redirect suggestions.', 'seo-simple' ),
				$suggestions_created
			),
			'count' => $suggestions_created
		) );
	}
}