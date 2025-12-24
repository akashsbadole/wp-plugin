<?php
/**
 * Import/Export admin functionality
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Import/Export admin class.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Import_Export_Admin {

	/**
	 * The import/export handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Import_Export    $import_export
	 */
	private $import_export;

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-import-export.php';
		$this->import_export = new Seo_Simple_Import_Export();
	}

	/**
	 * Register hooks.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_post_seo_simple_export_csv', array( $this, 'handle_export_csv' ) );
		add_action( 'admin_post_seo_simple_export_json', array( $this, 'handle_export_json' ) );
		add_action( 'admin_post_seo_simple_import', array( $this, 'handle_import' ) );
		add_action( 'admin_post_seo_simple_import_yoast', array( $this, 'handle_import_yoast' ) );
		add_action( 'admin_post_seo_simple_import_rankmath', array( $this, 'handle_import_rankmath' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'seo-simple',
			__( 'Import/Export', 'seo-simple' ),
			__( 'Import/Export', 'seo-simple' ),
			'manage_options',
			'seo-simple-import-export',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render import/export page.
	 *
	 * @since    1.0.0
	 */
	public function render_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seo-simple' ) );
		}

		// Check if we have results to display
		$import_results = get_transient( 'seo_simple_import_results' );
		if ( $import_results ) {
			delete_transient( 'seo_simple_import_results' );
		}

		include plugin_dir_path( __FILE__ ) . 'partials/import-export-page.php';
	}

	/**
	 * Handle CSV export.
	 *
	 * @since    1.0.0
	 */
	public function handle_export_csv() {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_export_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_export_nonce'], 'seo_simple_export' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}

		// Get post IDs if specified
		$post_ids = array();
		if ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) {
			$post_ids = array_map( 'intval', explode( ',', $_POST['post_ids'] ) );
		}

		// Generate CSV
		$csv_content = $this->import_export->export_to_csv( $post_ids );

		// Send download headers
		$filename = 'seo-simple-export-' . date( 'Y-m-d-H-i-s' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output CSV
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $csv_content;
		exit;
	}

	/**
	 * Handle JSON export.
	 *
	 * @since    1.0.0
	 */
	public function handle_export_json() {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_export_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_export_nonce'], 'seo_simple_export' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}

		// Get post IDs if specified
		$post_ids = array();
		if ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) {
			$post_ids = array_map( 'intval', explode( ',', $_POST['post_ids'] ) );
		}

		// Generate JSON
		$json_content = $this->import_export->export_to_json( $post_ids );

		// Send download headers
		$filename = 'seo-simple-export-' . date( 'Y-m-d-H-i-s' ) . '.json';
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Output JSON
		echo $json_content;
		exit;
	}

	/**
	 * Handle import.
	 *
	 * @since    1.0.0
	 */
	public function handle_import() {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_import_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_import_nonce'], 'seo_simple_import' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}

		// Check if file was uploaded
		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_die( __( 'No file uploaded or upload error.', 'seo-simple' ) );
		}

		$file = $_FILES['import_file'];
		$file_content = file_get_contents( $file['tmp_name'] );
		$file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

		// Import based on file type
		if ( $file_extension === 'csv' ) {
			$results = $this->import_export->import_from_csv( $file_content );
		} elseif ( $file_extension === 'json' ) {
			$results = $this->import_export->import_from_json( $file_content );
		} else {
			wp_die( __( 'Invalid file format. Please upload CSV or JSON file.', 'seo-simple' ) );
		}

		// Store results in transient
		set_transient( 'seo_simple_import_results', $results, 60 );

		// Redirect back
		wp_redirect( admin_url( 'admin.php?page=seo-simple-import-export&imported=1' ) );
		exit;
	}

	/**
	 * Handle Yoast import.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_yoast() {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_import_yoast_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_import_yoast_nonce'], 'seo_simple_import_yoast' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}

		// Import from Yoast
		$results = $this->import_export->import_from_yoast();

		// Store results in transient
		set_transient( 'seo_simple_import_results', $results, 60 );

		// Redirect back
		wp_redirect( admin_url( 'admin.php?page=seo-simple-import-export&imported=1' ) );
		exit;
	}

	/**
	 * Handle Rank Math import.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_rankmath() {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_import_rankmath_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_import_rankmath_nonce'], 'seo_simple_import_rankmath' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions.', 'seo-simple' ) );
		}

		// Import from Rank Math
		$results = $this->import_export->import_from_rankmath();

		// Store results in transient
		set_transient( 'seo_simple_import_results', $results, 60 );

		// Redirect back
		wp_redirect( admin_url( 'admin.php?page=seo-simple-import-export&imported=1' ) );
		exit;
	}
}
