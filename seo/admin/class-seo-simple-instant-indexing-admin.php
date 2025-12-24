<?php

/**
 * Handles the admin functionality for Instant Indexing.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin functionality for Instant Indexing.
 *
 * This class is responsible for the admin interface for instant indexing
 * of URLs to search engines.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Instant_Indexing_Admin {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The instant indexing instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Instant_Indexing    $instant_indexing    The instant indexing instance.
	 */
	private $instant_indexing;

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
	 * @param    Seo_Simple_Instant_Indexing      $instant_indexing   The instant indexing instance.
	 * @param    Seo_Simple_Security              $security           The security handler instance.
	 */
	public function __construct( $seo_meta, $instant_indexing, $security = null ) {
		$this->seo_meta = $seo_meta;
		$this->instant_indexing = $instant_indexing;
		$this->security = $security;
	}

	/**
	 * Register hooks for the admin functionality.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_bulk_indexing', array( $this, 'ajax_bulk_indexing' ) );
		add_action( 'wp_ajax_seo_simple_get_indexing_history', array( $this, 'ajax_get_indexing_history' ) );
		add_action( 'wp_ajax_seo_simple_clear_indexing_history', array( $this, 'ajax_clear_indexing_history' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on Instant Indexing pages
		if ( 'seo-settings_page_seo-simple-instant-indexing' !== $hook && 'seo-settings_page_seo-simple-settings' !== $hook ) {
			return;
		}
		
		// Check if we're on the Instant Indexing tab
		if ( 'seo-settings_page_seo-simple-settings' === $hook && ( ! isset( $_GET['tab'] ) || 'indexing' !== $_GET['tab'] ) ) {
			return;
		}
		
		wp_enqueue_script(
			'seo-simple-instant-indexing-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-instant-indexing-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_enqueue_style(
			'seo-simple-instant-indexing-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-instant-indexing-admin.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		// Localize script
		wp_localize_script(
			'seo-simple-instant-indexing-admin',
			'seo_simple_instant_indexing_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_instant_indexing_admin' ),
				'strings' => array(
					'confirm_bulk_indexing' => __( 'Are you sure you want to submit these URLs for indexing? This will use your daily quota.', 'seo-simple' ),
					'confirm_clear_history' => __( 'Are you sure you want to clear the indexing history?', 'seo-simple' ),
					'submitting' => __( 'Submitting...', 'seo-simple' ),
					'submission_success' => __( 'URLs submitted successfully!', 'seo-simple' ),
					'submission_failed' => __( 'Some submissions failed. Please check the results.', 'seo-simple' ),
				),
			)
		);
	}

	/**
	 * AJAX handler for bulk indexing.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_indexing() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_bulk_indexing' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform bulk indexing.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
		$engines = isset( $_POST['engines'] ) ? array_map( 'sanitize_text_field', $_POST['engines'] ) : array();
		
		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected.', 'seo-simple' ) ) );
		}
		
		if ( empty( $engines ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select at least one search engine.', 'seo-simple' ) ) );
		}
		
		$settings = $this->seo_meta->get_global_settings();
		$daily_limit = isset( $settings['indexing_daily_limit'] ) ? intval( $settings['indexing_daily_limit'] ) : 100;
		
		// Check daily limit
		$total_submissions = count( $post_ids ) * count( $engines );
		if ( ! $this->instant_indexing->check_daily_limit( $total_submissions ) ) {
			wp_send_json_error( array( 'message' => __( 'Daily indexing limit reached.', 'seo-simple' ) ) );
		}
		
		$results = array();
		$success_count = 0;
		$failure_count = 0;
		
		foreach ( $post_ids as $post_id ) {
			$url = get_permalink( $post_id );
			$post_results = array();
			$post_success = false;
			
			foreach ( $engines as $engine ) {
				$result = $this->instant_indexing->submit_to_search_engine( $url, $engine );
				$post_results[$engine] = $result;
				
				if ( $result['success'] ) {
					$success_count++;
					$post_success = true;
				} else {
					$failure_count++;
				}
			}
			
			// Update post meta
			if ( $post_success ) {
				update_post_meta( $post_id, '_seo_simple_last_indexed', current_time( 'mysql' ) );
				
				// Save indexing status
				$current_status = get_post_meta( $post_id, '_seo_simple_indexing_status', true );
				if ( ! is_array( $current_status ) ) {
					$current_status = array();
				}
				
				foreach ( $engines as $engine ) {
					$current_status[$engine] = array(
						'submitted' => current_time( 'mysql' ),
						'success' => $post_results[$engine]['success'],
						'message' => $post_results[$engine]['message']
					);
				}
				
				update_post_meta( $post_id, '_seo_simple_indexing_status', $current_status );
			}
			
			$results[$post_id] = array(
				'url' => $url,
				'title' => get_the_title( $post_id ),
				'results' => $post_results,
				'success' => $post_success
			);
		}
		
		// Update submission count
		$this->instant_indexing->update_daily_submission_count( $success_count );
		
		// Log to history
		$this->log_indexing_history( $post_ids, $engines, $results );
		
		wp_send_json_success( array(
			'message' => sprintf( __( 'Successfully submitted %d URLs. %d submissions failed.', 'seo-simple' ), $success_count, $failure_count ),
			'results' => $results,
			'success_count' => $success_count,
			'failure_count' => $failure_count
		) );
	}

	/**
	 * AJAX handler for getting indexing history.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_indexing_history() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_get_indexing_history' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to access indexing history.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Get pagination parameters
		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 20;
		
		// Get history
		$history = $this->get_indexing_history( $page, $per_page );
		
		wp_send_json_success( $history );
	}

	/**
	 * AJAX handler for clearing indexing history.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_indexing_history() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_clear_indexing_history' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to clear indexing history.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Clear history
		$this->clear_indexing_history();
		
		wp_send_json_success( array( 'message' => __( 'Indexing history cleared successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Log indexing history.
	 *
	 * @since    1.0.0
	 * @param    array     $post_ids    The post IDs.
	 * @param    array     $engines     The search engines.
	 * @param    array     $results     The results.
	 */
	private function log_indexing_history( $post_ids, $engines, $results ) {
		$history = get_option( 'seo_simple_indexing_history', array() );
		
		$entry = array(
			'timestamp' => current_time( 'mysql' ),
			'post_ids' => $post_ids,
			'engines' => $engines,
			'results' => $results,
			'user_id' => get_current_user_id()
		);
		
		// Add to beginning of array
		array_unshift( $history, $entry );
		
		// Keep only last 100 entries
		$history = array_slice( $history, 0, 100 );
		
		update_option( 'seo_simple_indexing_history', $history );
	}

	/**
	 * Get indexing history.
	 *
	 * @since    1.0.0
	 * @param    int       $page       The page number.
	 * @param    int       $per_page   The number of items per page.
	 * @return   array                 The history data.
	 */
	private function get_indexing_history( $page = 1, $per_page = 20 ) {
		$history = get_option( 'seo_simple_indexing_history', array() );
		
		// Calculate offset
		$offset = ( $page - 1 ) * $per_page;
		
		// Get page of history
		$page_history = array_slice( $history, $offset, $per_page );
		
		// Format for display
		$formatted_history = array();
		
		foreach ( $page_history as $entry ) {
			$success_count = 0;
			$total_count = 0;
			
			foreach ( $entry['results'] as $result ) {
				foreach ( $result['results'] as $engine_result ) {
					$total_count++;
					if ( $engine_result['success'] ) {
						$success_count++;
					}
				}
			}
			
			$formatted_history[] = array(
				'timestamp' => $entry['timestamp'],
				'user' => get_userdata( $entry['user_id'] )->display_name,
				'post_count' => count( $entry['post_ids'] ),
				'engines' => implode( ', ', $entry['engines'] ),
				'success_count' => $success_count,
				'total_count' => $total_count,
				'success_rate' => $total_count > 0 ? round( ( $success_count / $total_count ) * 100 ) : 0
			);
		}
		
		return array(
			'history' => $formatted_history,
			'total' => count( $history ),
			'page' => $page,
			'per_page' => $per_page,
			'total_pages' => ceil( count( $history ) / $per_page )
		);
	}

	/**
	 * Clear indexing history.
	 *
	 * @since    1.0.0
	 */
	private function clear_indexing_history() {
		update_option( 'seo_simple_indexing_history', array() );
	}

	/**
	 * Get daily submission statistics.
	 *
	 * @since    1.0.0
	 * @return   array     The statistics.
	 */
	public function get_daily_stats() {
		$today = date( 'Y-m-d' );
		$submission_count = get_option( 'seo_simple_indexing_count_' . $today, 0 );
		
		$settings = $this->seo_meta->get_global_settings();
		$daily_limit = isset( $settings['indexing_daily_limit'] ) ? intval( $settings['indexing_daily_limit'] ) : 100;
		
		$percentage = $daily_limit > 0 ? round( ( $submission_count / $daily_limit ) * 100 ) : 0;
		
		return array(
			'submitted' => $submission_count,
			'limit' => $daily_limit,
			'remaining' => max( 0, $daily_limit - $submission_count ),
			'percentage' => $percentage
		);
	}

	/**
	 * Get recent submissions.
	 *
	 * @since    1.0.0
	 * @param    int       $limit    The number of items to retrieve.
	 * @return   array               The recent submissions.
	 */
	public function get_recent_submissions( $limit = 10 ) {
		global $wpdb;
		
		$limit = intval( $limit );
		
		// Get recent posts with indexing status
		$posts = $wpdb->get_results( $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_status, pm.meta_value as indexing_status
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_seo_simple_indexing_status'
			WHERE p.post_status = 'publish' AND pm.meta_value IS NOT NULL
			ORDER BY p.post_modified DESC
			LIMIT %d",
			$limit
		) );
		
		$recent = array();
		
		foreach ( $posts as $post ) {
			$indexing_status = maybe_unserialize( $post->indexing_status );
			$last_indexed = get_post_meta( $post->ID, '_seo_simple_last_indexed', true );
			
			$recent[] = array(
				'id' => $post->ID,
				'title' => $post->post_title,
				'url' => get_permalink( $post->ID ),
				'last_indexed' => $last_indexed,
				'indexing_status' => $indexing_status
			);
		}
		
		return $recent;
	}
}