<?php

/**
 * Admin AJAX handlers for the Redirect Manager.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Admin AJAX handlers for the Redirect Manager.
 *
 * This class handles all AJAX requests for the redirect manager.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Redirect_Admin {

	/**
	 * The redirect manager instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Redirect_Manager    $redirect_manager    The redirect manager instance.
	 */
	private $redirect_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Create a new instance of redirect manager but prevent menu registration
		$this->redirect_manager = new Seo_Simple_Redirect_Manager();
		// Remove any admin_menu hook to prevent duplicate registration
		remove_action( 'admin_menu', array( $this->redirect_manager, 'add_admin_menu' ) );
		
		// Register AJAX handlers
		$this->register_ajax_handlers();
	}

	/**
	 * Register AJAX handlers.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_ajax_handlers() {
		// Redirect CRUD operations
		add_action( 'wp_ajax_seo_simple_save_redirect', array( $this, 'ajax_save_redirect' ) );
		add_action( 'wp_ajax_seo_simple_delete_redirect', array( $this, 'ajax_delete_redirect' ) );
		
		// Redirect suggestions
		add_action( 'wp_ajax_seo_simple_create_redirect_from_suggestion', array( $this, 'ajax_create_redirect_from_suggestion' ) );
		add_action( 'wp_ajax_seo_simple_dismiss_suggestion', array( $this, 'ajax_dismiss_suggestion' ) );
		
		// Import/Export
		add_action( 'wp_ajax_seo_simple_import_redirects', array( $this, 'ajax_import_redirects' ) );
		add_action( 'wp_ajax_seo_simple_export_redirects', array( $this, 'ajax_export_redirects' ) );
		
		// Clear 404 logs
		add_action( 'wp_ajax_seo_simple_clear_404_logs', array( $this, 'ajax_clear_404_logs' ) );
	}

	/**
	 * AJAX handler for saving a redirect.
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
		
		// Get the redirects handler
		$redirects = new Seo_Simple_Redirects();
		
		if ( $redirect_id > 0 ) {
			// Update existing redirect
			$result = $redirects->update_redirect( $redirect_id, $to_url, $redirect_type, $status );
		} else {
			// Add new redirect
			$result = $redirects->add_redirect( $from_url, $to_url, $redirect_type, $status );
		}
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Redirect saved successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for deleting a redirect.
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
		
		$redirects = new Seo_Simple_Redirects();
		$result = $redirects->delete_redirect( $redirect_id );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Redirect deleted successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete redirect.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for creating a redirect from a suggestion.
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
		
		global $wpdb;
		$suggestions_table = $wpdb->prefix . 'seo_simple_redirect_suggestions';
		
		// Start transaction
		$wpdb->query( 'START TRANSACTION' );
		
		try {
			// Create the redirect
			$redirects = new Seo_Simple_Redirects();
			$result = $redirects->add_redirect( $from_url, $to_url, 301, 1 );
			
			if ( ! $result ) {
				throw new Exception( __( 'Failed to create redirect.', 'seo-simple' ) );
			}
			
			// Update suggestion status
			$wpdb->update(
				$suggestions_table,
				array( 'status' => 'created' ),
				array( 'id' => $suggestion_id ),
				array( '%s' ),
				array( '%d' )
			);
			
			// Commit transaction
			$wpdb->query( 'COMMIT' );
			
			wp_send_json_success( array( 'message' => __( 'Redirect created successfully.', 'seo-simple' ) ) );
		} catch ( Exception $e ) {
			// Rollback transaction
			$wpdb->query( 'ROLLBACK' );
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for dismissing a suggestion.
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
		
		if ( $source === 'csv' ) {
			if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
				wp_send_json_error( array( 'message' => __( 'Please upload a valid CSV file.', 'seo-simple' ) ) );
			}
			
			$data = file_get_contents( $_FILES['csv_file']['tmp_name'] );
		} elseif ( $source === 'htaccess' ) {
			$data = isset( $_POST['data'] ) ? wp_kses_post( $_POST['data'] ) : '';
			
			if ( empty( $data ) ) {
				wp_send_json_error( array( 'message' => __( 'Please provide .htaccess content.', 'seo-simple' ) ) );
			}
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid import source.', 'seo-simple' ) ) );
		}
		
		// Import redirects
		$results = $this->redirect_manager->import_redirects( $source, $data );
		
		if ( $results['success'] > 0 ) {
			$message = sprintf(
				__( 'Successfully imported %d redirects.', 'seo-simple' ),
				$results['success']
			);
			
			if ( $results['errors'] > 0 ) {
				$message .= ' ' . sprintf(
					__( '%d redirects failed to import.', 'seo-simple' ),
					$results['errors']
				);
			}
			
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			$error_message = __( 'No redirects were imported.', 'seo-simple' );
			
			if ( ! empty( $results['messages'] ) ) {
				$error_message .= ' ' . implode( ' ', $results['messages'] );
			}
			
			wp_send_json_error( array( 'message' => $error_message ) );
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
		
		$redirects = new Seo_Simple_Redirects();
		$csv_data = $redirects->export_redirects();
		
		// Set headers for CSV download
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="seo-redirects-' . date( 'Y-m-d' ) . '.csv"' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
		
		// Output CSV data
		echo $csv_data;
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
		$log_table = $wpdb->prefix . 'seo_simple_404_log';
		
		$result = $wpdb->query( "TRUNCATE TABLE {$log_table}" );
		
		if ( $result !== false ) {
			// Clear cache
			$cache = Seo_Simple_Cache_Manager::get_instance();
			$cache->delete_by_pattern( 'seo_simple_404_logs_' );
			
			wp_send_json_success( array( 'message' => __( '404 logs cleared successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear 404 logs.', 'seo-simple' ) ) );
		}
	}


	/**
	 * Render the redirect manager page.
	 *
	 * @since    1.0.0
	 */
	public function render_redirect_manager_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/redirect-manager-page.php';
	}

	/**
	 * Handle AJAX requests for redirect admin.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// AJAX handlers are already registered in the constructor
		// This method is added for consistency with other admin classes
	}
}
