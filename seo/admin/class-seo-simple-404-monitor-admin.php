<?php

/**
 * 404 Monitor Admin functionality
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * 404 Monitor Admin class
 *
 * This class handles the admin interface for the 404 monitor.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_404_Monitor_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		// Handle AJAX requests
		add_action( 'wp_ajax_seo_simple_404_monitor_get_logs', array( $this, 'ajax_get_logs' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_get_stats', array( $this, 'ajax_get_stats' ) );
		add_action( 'wp_ajax_seo_simple_clear_all_404s', array( $this, 'ajax_clear_all_404s' ) );
		add_action( 'wp_ajax_seo_simple_export_404s', array( $this, 'ajax_export_404s' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_ignore_url', array( $this, 'ajax_ignore_url' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_create_redirect', array( $this, 'ajax_create_redirect' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_bulk_action', array( $this, 'ajax_bulk_action' ) );
		add_action( 'wp_ajax_seo_simple_404_monitor_bulk_redirect', array( $this, 'ajax_bulk_redirect' ) );
		
		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add admin menu page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'seo-simple-settings',
			__( '404 Monitor', 'seo-simple' ),
			__( '404 Monitor', 'seo-simple' ),
			'manage_options',
			'seo-simple-404-monitor',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_settings() {
		register_setting(
			'seo_simple_404_settings',
			'seo_simple_settings'
		);
	}

	/**
	 * Render the admin page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function render_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/404-monitor-page.php';
	}

	/**
	 * AJAX handler for getting logs.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_get_logs() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$limit = 20;
		$offset = ( $page - 1 ) * $limit;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$filter = isset( $_POST['filter'] ) ? sanitize_text_field( $_POST['filter'] ) : '';
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		
		$args = array(
			'limit' => $limit,
			'offset' => $offset,
			'search' => $search,
		);
		
		if ( $filter !== '' ) {
			$args['is_ignored'] = intval( $filter );
		}
		
		$logs = $monitor->get_logs( $args );
		$total = $monitor->get_logs_count( array(
			'search' => $search,
			'is_ignored' => $filter !== '' ? intval( $filter ) : null,
		) );
		
		$total_pages = ceil( $total / $limit );
		
		$html = '';
		if ( ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				$status = $log->is_ignored ? '<span class="status-ignored">' . __( 'Ignored', 'seo-simple' ) . '</span>' : '<span class="status-active">' . __( 'Active', 'seo-simple' ) . '</span>';
				if ( $log->redirect_to ) {
					$status .= ' <span class="status-redirect">' . __( 'Redirected', 'seo-simple' ) . '</span>';
				}
				
				$html .= '<tr>';
				$html .= '<th class="check-column">';
				$html .= '<input type="checkbox" name="log[]" value="' . esc_attr( $log->id ) . '" />';
				$html .= '</th>';
				$html .= '<td>';
				$html .= '<a href="' . esc_url( $log->url ) . '" target="_blank">' . esc_html( $log->url ) . '</a>';
				$html .= '</td>';
				$html .= '<td>' . number_format( $log->hit_count ) . '</td>';
				$html .= '<td>' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log->last_hit ) ) ) . '</td>';
				$html .= '<td>' . $status . '</td>';
				$html .= '<td>';
				$html .= '<div class="row-actions">';
				$html .= '<span class="ignore"><a href="#" class="ignore-url" data-id="' . esc_attr( $log->id ) . '">' . ( $log->is_ignored ? __( 'Unignore', 'seo-simple' ) : __( 'Ignore', 'seo-simple' ) ) . '</a> | </span>';
				$html .= '<span class="redirect"><a href="#" class="create-redirect" data-url="' . esc_attr( $log->url ) . '">' . __( 'Create Redirect', 'seo-simple' ) . '</a></span>';
				$html .= '</div>';
				$html .= '</td>';
				$html .= '</tr>';
			}
		} else {
			$html .= '<tr><td colspan="6" class="text-center">' . __( 'No 404 logs found.', 'seo-simple' ) . '</td></tr>';
		}
		
		// Pagination
		$pagination = '';
		if ( $total_pages > 1 ) {
			$pagination .= '<span class="displaying-num">' . sprintf( __( '%d items', 'seo-simple' ), $total ) . '</span>';
			$pagination .= '<span class="pagination-links">';
			
			if ( $page > 1 ) {
				$pagination .= '<a class="prev-page button" href="#" data-page="' . ( $page - 1 ) . '"><span class="screen-reader-text">' . __( 'Previous page', 'seo-simple' ) . '</span><span aria-hidden="true">‹</span></a>';
			}
			
			$pagination .= '<span class="paging-input">';
			$pagination .= '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'seo-simple' ) . '</label>';
			$pagination .= '<input class="current-page" id="current-page-selector" type="text" name="paged" value="' . $page . '" size="1" aria-describedby="table-paging" />';
			$pagination .= '<span class="tablenav-paging-text"> ' . __( 'of', 'seo-simple' ) . ' <span class="total-pages">' . $total_pages . '</span></span>';
			$pagination .= '</span>';
			
			if ( $page < $total_pages ) {
				$pagination .= '<a class="next-page button" href="#" data-page="' . ( $page + 1 ) . '"><span class="screen-reader-text">' . __( 'Next page', 'seo-simple' ) . '</span><span aria-hidden="true">›</span></a>';
			}
			
			$pagination .= '</span>';
		}
		
		wp_send_json_success( array(
			'html' => $html,
			'pagination' => $pagination,
		) );
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
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		$stats = $monitor->get_stats();
		
		// Calculate additional stats
		$last_24h = $monitor->get_logs_count( array(
			'date_after' => date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) )
		) );
		
		$last_7days = $monitor->get_logs_count( array(
			'date_after' => date( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
		) );
		
		// Get top referrer
		global $wpdb;
		$table_name = $wpdb->prefix . 'seo_simple_404_logs';
		$top_referrer = $wpdb->get_var(
			"SELECT referrer FROM $table_name WHERE referrer != '' GROUP BY referrer ORDER BY COUNT(*) DESC LIMIT 1"
		);
		
		$stats['last_24h'] = $last_24h;
		$stats['last_7days'] = $last_7days;
		$stats['top_referrer'] = $top_referrer ?: '-';
		
		wp_send_json_success( $stats );
	}
	
	/**
	 * AJAX handler for clearing all 404s.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_clear_all_404s() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_clear_404s', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		
		if ( $monitor->clear_logs() ) {
			wp_send_json_success( array( 'message' => __( 'All 404 logs have been cleared.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear 404 logs.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * AJAX handler for exporting 404s.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_export_404s() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_export_404s', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		$logs = $monitor->get_logs( array( 'limit' => 10000 ) );
		
		if ( empty( $logs ) ) {
			wp_send_json_error( array( 'message' => __( 'No 404 logs found to export.', 'seo-simple' ) ) );
		}
		
		// Create CSV
		$filename = '404-logs-' . date( 'Y-m-d' ) . '.csv';
		$handle = fopen( 'php://output', 'w' );
		
		// Headers
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		
		// CSV Header
		fputcsv( $handle, array( 'URL', 'Referrer', 'User Agent', 'IP Address', 'Hit Count', 'First Hit', 'Last Hit', 'Status', 'Redirect To' ) );
		
		// CSV Data
		foreach ( $logs as $log ) {
			$status = $log->is_ignored ? 'Ignored' : 'Active';
			if ( $log->redirect_to ) {
				$status .= ' - Redirected';
			}
			
			fputcsv( $handle, array(
				$log->url,
				$log->referrer,
				$log->user_agent,
				$log->ip_address,
				$log->hit_count,
				$log->first_hit,
				$log->last_hit,
				$status,
				$log->redirect_to
			) );
		}
		
		fclose( $handle );
		exit;
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
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		
		if ( $monitor->ignore_url( $id ) ) {
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
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		
		if ( $monitor->create_redirect( $id, $redirect_to ) ) {
			wp_send_json_success( array( 'message' => __( 'Redirect has been created.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to create redirect.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * AJAX handler for bulk actions.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_bulk_action() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
		$ids = isset( $_POST['ids'] ) ? array_map( 'intval', $_POST['ids'] ) : array();
		
		if ( empty( $ids ) || ! in_array( $bulk_action, array( 'delete' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'seo-simple' ) ) );
		}
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		$success = 0;
		
		foreach ( $ids as $id ) {
			if ( $bulk_action === 'delete' ) {
				if ( $monitor->delete_log( $id ) ) {
					$success++;
				}
			}
		}
		
		if ( $success > 0 ) {
			wp_send_json_success( array( 'message' => sprintf( __( '%d items processed successfully.', 'seo-simple' ), $success ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'No items were processed.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * AJAX handler for bulk redirects.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_bulk_redirect() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_404_monitor_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$ids = isset( $_POST['ids'] ) ? array_map( 'intval', $_POST['ids'] ) : array();
		$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : '';
		
		if ( empty( $ids ) || empty( $redirect_to ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'seo-simple' ) ) );
		}
		
		$monitor = Seo_Simple_404_Monitor::get_instance();
		$success = 0;
		
		foreach ( $ids as $id ) {
			if ( $monitor->create_redirect( $id, $redirect_to ) ) {
				$success++;
			}
		}
		
		if ( $success > 0 ) {
			wp_send_json_success( array( 'message' => sprintf( __( '%d redirects created successfully.', 'seo-simple' ), $success ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'No redirects were created.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * Enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'seo-simple_page_seo-simple-404-monitor' ) {
			return;
		}
		
		wp_enqueue_style(
			'seo-simple-404-monitor',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-404-monitor.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		wp_enqueue_script(
			'seo-simple-404-monitor',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-404-monitor.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_localize_script(
			'seo-simple-404-monitor',
			'seo_simple_404_monitor',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_404_monitor_nonce' ),
				'clear_nonce' => wp_create_nonce( 'seo_simple_clear_404s' ),
				'export_nonce' => wp_create_nonce( 'seo_simple_export_404s' ),
				'confirm_clear_all' => __( 'Are you sure you want to clear all 404 errors? This action cannot be undone.', 'seo-simple' ),
				'confirm_bulk_delete' => __( 'Are you sure you want to delete the selected 404 errors? This action cannot be undone.', 'seo-simple' ),
				'prompt_redirect' => __( 'Enter the URL to redirect to:', 'seo-simple' ),
				'prompt_bulk_redirect' => __( 'Enter the URL to redirect all selected 404s to:', 'seo-simple' ),
				'text_unignore' => __( 'Unignore', 'seo-simple' ),
				'success_cleared' => __( 'All 404 logs have been cleared.', 'seo-simple' ),
				'success_ignored' => __( 'URL has been ignored.', 'seo-simple' ),
				'success_unignored' => __( 'URL has been unignored.', 'seo-simple' ),
				'success_redirect' => __( 'Redirect has been created.', 'seo-simple' ),
				'success_bulk_action' => __( 'Bulk action completed successfully.', 'seo-simple' ),
				'success_bulk_redirect' => __( 'Bulk redirects have been created.', 'seo-simple' ),
				'success_settings' => __( 'Settings have been saved.', 'seo-simple' ),
				'error_general' => __( 'An error occurred.', 'seo-simple' ),
				'error_connection' => __( 'Connection error. Please try again.', 'seo-simple' ),
				'error_no_selection' => __( 'Please select at least one item.', 'seo-simple' ),
			)
		);
	}
}
