<?php

/**
 * Handles bulk SEO editing functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles bulk SEO editing functionality.
 *
 * This class is responsible for providing bulk editing capabilities for SEO metadata
 * across multiple posts, pages, and custom post types.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Bulk_Editor {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

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
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	public function __construct( $seo_meta ) {
		$this->seo_meta = $seo_meta;
		
		// Initialize security handler if available
		if ( class_exists( 'Seo_Simple_Security' ) ) {
			$this->security = new Seo_Simple_Security();
		}
	}

	/**
	 * Register hooks for bulk editing.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Clear all WordPress caches to ensure fixes take effect
		wp_cache_flush();
		
		// Add bulk actions to post lists
		add_filter( 'bulk_actions-edit-post', array( $this, 'add_bulk_actions' ) );
		add_filter( 'bulk_actions-edit-page', array( $this, 'add_bulk_actions' ) );
		
		// Handle bulk actions
		add_filter( 'handle_bulk_actions-edit-post', array( $this, 'handle_bulk_action' ), 10, 3 );
		add_filter( 'handle_bulk_actions-edit-page', array( $this, 'handle_bulk_action' ), 10, 3 );
		
		// Add custom bulk actions for WooCommerce if active
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'bulk_actions-edit-product', array( $this, 'add_woocommerce_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-product', array( $this, 'handle_bulk_action' ), 10, 3 );
		}
		
		// Add custom columns to post lists
		add_filter( 'manage_posts_columns', array( $this, 'add_seo_columns' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'display_seo_columns' ), 10, 2 );
		add_filter( 'manage_pages_columns', array( $this, 'add_seo_columns' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'display_seo_columns' ), 10, 2 );
		
		// Add WooCommerce SEO columns if active
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'manage_product_posts_columns', array( $this, 'add_woocommerce_seo_columns' ) );
			add_action( 'manage_product_posts_custom_column', array( $this, 'display_woocommerce_seo_columns' ), 10, 2 );
		}
		
		// Add AJAX handlers for bulk editing
		add_action( 'wp_ajax_seo_simple_bulk_edit', array( $this, 'ajax_bulk_edit' ) );
		add_action( 'wp_ajax_seo_simple_bulk_analyze', array( $this, 'ajax_bulk_analyze' ) );
		add_action( 'wp_ajax_seo_simple_bulk_optimize', array( $this, 'ajax_bulk_optimize' ) );
		
		// Add inline edit support
		add_action( 'wp_ajax_seo_simple_inline_edit', array( $this, 'ajax_inline_edit' ) );
		
		// Add export/import functionality
		add_action( 'wp_ajax_seo_simple_export_seo', array( $this, 'ajax_export_seo' ) );
		add_action( 'wp_ajax_seo_simple_import_seo', array( $this, 'ajax_import_seo' ) );
		add_action( 'wp_ajax_seo_simple_preview_import', array( $this, 'ajax_preview_import' ) );
		add_action( 'wp_ajax_seo_simple_process_import', array( $this, 'ajax_process_import' ) );
	}

	/**
	 * Add bulk actions to post lists.
	 *
	 * @since    1.0.0
	 * @param    array    $actions    The existing bulk actions.
	 * @return   array                The modified bulk actions.
	 */
	public function add_bulk_actions( $actions ) {
		$actions['seo_simple_edit_seo'] = __( 'Edit SEO', 'seo-simple' );
		$actions['seo_simple_analyze_seo'] = __( 'Analyze SEO', 'seo-simple' );
		$actions['seo_simple_optimize_seo'] = __( 'Auto Optimize SEO', 'seo-simple' );
		$actions['seo_simple_reset_seo'] = __( 'Reset SEO', 'seo-simple' );
		
		return $actions;
	}

	/**
	 * Add WooCommerce-specific bulk actions.
	 *
	 * @since    1.0.0
	 * @param    array    $actions    The existing bulk actions.
	 * @return   array                The modified bulk actions.
	 */
	public function add_woocommerce_bulk_actions( $actions ) {
		$actions['seo_simple_edit_seo'] = __( 'Edit SEO', 'seo-simple' );
		$actions['seo_simple_analyze_seo'] = __( 'Analyze SEO', 'seo-simple' );
		$actions['seo_simple_optimize_seo'] = __( 'Auto Optimize SEO', 'seo-simple' );
		$actions['seo_simple_reset_seo'] = __( 'Reset SEO', 'seo-simple' );
		$actions['seo_simple_generate_schema'] = __( 'Generate Schema', 'seo-simple' );
		
		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @since    1.0.0
	 * @param    string    $redirect_to    The redirect URL.
	 * @param    string    $action         The action being performed.
	 * @param    array     $post_ids       The post IDs.
	 * @return   string                    The modified redirect URL.
	 */
	public function handle_bulk_action( $redirect_to, $action, $post_ids ) {
		// Check if this is one of our bulk actions
		if ( strpos( $action, 'seo_simple_' ) !== 0 ) {
			return $redirect_to;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
		}
		
		// Process the bulk action
		$processed = 0;
		$errors = 0;
		
		foreach ( $post_ids as $post_id ) {
			$result = false;
			
			switch ( $action ) {
				case 'seo_simple_edit_seo':
					// This will be handled via AJAX, just set up the redirect
					break;
					
				case 'seo_simple_analyze_seo':
					$result = $this->analyze_post_seo( $post_id );
					break;
					
				case 'seo_simple_optimize_seo':
					$result = $this->auto_optimize_post_seo( $post_id );
					break;
					
				case 'seo_simple_reset_seo':
					$result = $this->reset_post_seo( $post_id );
					break;
					
				case 'seo_simple_generate_schema':
					$result = $this->generate_post_schema( $post_id );
					break;
			}
			
			if ( $result ) {
				$processed++;
			} else {
				$errors++;
			}
		}
		
		// Add query args to the redirect URL
		$redirect_to = add_query_arg( array(
			'seo_simple_bulk_action' => $action,
			'processed' => $processed,
			'errors' => $errors,
			'_wpnonce' => wp_create_nonce( 'seo_simple_bulk_action' ),
		), $redirect_to );
		
		return $redirect_to;
	}

	/**
	 * Add SEO columns to post lists.
	 *
	 * @since    1.0.0
	 * @param    array    $columns    The existing columns.
	 * @return   array                The modified columns.
	 */
	public function add_seo_columns( $columns ) {
		$columns['seo_title'] = __( 'SEO Title', 'seo-simple' );
		$columns['seo_description'] = __( 'SEO Description', 'seo-simple' );
		$columns['seo_score'] = __( 'SEO Score', 'seo-simple' );
		$columns['seo_focus_keyword'] = __( 'Focus Keyword', 'seo-simple' );
		
		return $columns;
	}

	/**
	 * Add WooCommerce SEO columns.
	 *
	 * @since    1.0.0
	 * @param    array    $columns    The existing columns.
	 * @return   array                The modified columns.
	 */
	public function add_woocommerce_seo_columns( $columns ) {
		$columns['seo_title'] = __( 'SEO Title', 'seo-simple' );
		$columns['seo_description'] = __( 'SEO Description', 'seo-simple' );
		$columns['seo_score'] = __( 'SEO Score', 'seo-simple' );
		$columns['seo_focus_keyword'] = __( 'Focus Keyword', 'seo-simple' );
		$columns['seo_schema_type'] = __( 'Schema Type', 'seo-simple' );
		
		return $columns;
	}

	/**
	 * Display SEO columns in post lists.
	 *
	 * @since    1.0.0
	 * @param    string    $column    The column name.
	 * @param    int       $post_id   The post ID.
	 */
	public function display_seo_columns( $column, $post_id ) {
		// Clear any potential cache for this post
		wp_cache_delete( 'seo_simple_focus_keyword_' . $post_id, 'seo_simple' );
		switch ( $column ) {
			case 'seo_title':
				$title = $this->seo_meta->get_title( $post_id );
				if ( $title && is_string( $title ) ) {
					echo esc_html( substr( $title, 0, 50 ) . ( strlen( $title ) > 50 ? '...' : '' ) );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_description':
				$description = $this->seo_meta->get_description( $post_id );
				if ( $description && is_string( $description ) ) {
					echo esc_html( substr( $description, 0, 50 ) . ( strlen( $description ) > 50 ? '...' : '' ) );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_score':
				$score = $this->seo_meta->get_overall_seo_score( $post_id );
				// Handle case where score might be returned as an array
				if ( is_array( $score ) ) {
					$score_value = isset( $score['value'] ) ? $score['value'] : 0;
				} else {
					$score_value = is_numeric( $score ) ? $score : 0;
				}
				$color = $score_value >= 80 ? 'green' : ( $score_value >= 50 ? 'orange' : 'red' );
				echo '<span class="seo-simple-score seo-simple-score-' . $color . '">' . $score_value . '</span>';
				break;
				
			case 'seo_focus_keyword':
				$keyword = $this->seo_meta->get_focus_keyword( $post_id );
				
				// Debug: Log what we're working with
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'SEO Bulk Editor Debug: keyword type = ' . gettype( $keyword ) . ', value = ' . print_r( $keyword, true ) );
				}
				
				if ( $keyword && is_string( $keyword ) ) {
					echo esc_html( $keyword );
				} elseif ( is_array( $keyword ) ) {
					// Handle case where keyword is stored as array
					echo esc_html( implode( ', ', array_filter( $keyword ) ) );
				} elseif ( is_object( $keyword ) ) {
					// Handle case where keyword is an object
					echo esc_html( 'Invalid data type' );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Not set', 'seo-simple' ) . '</span>';
				}
				break;
		}
	}

	/**
	 * Display WooCommerce SEO columns.
	 *
	 * @since    1.0.0
	 * @param    string    $column    The column name.
	 * @param    int       $post_id   The post ID.
	 */
	public function display_woocommerce_seo_columns( $column, $post_id ) {
		// Clear any potential cache for this post
		wp_cache_delete( 'seo_simple_focus_keyword_' . $post_id, 'seo_simple' );
		switch ( $column ) {
			case 'seo_title':
				$title = get_post_meta( $post_id, '_seo_simple_product_title', true );
				if ( ! $title ) {
					$title = $this->seo_meta->get_title( $post_id );
				}
				if ( $title && is_string( $title ) ) {
					echo esc_html( substr( $title, 0, 50 ) . ( strlen( $title ) > 50 ? '...' : '' ) );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_description':
				$description = get_post_meta( $post_id, '_seo_simple_product_description', true );
				if ( ! $description ) {
					$description = $this->seo_meta->get_description( $post_id );
				}
				if ( $description && is_string( $description ) ) {
					echo esc_html( substr( $description, 0, 50 ) . ( strlen( $description ) > 50 ? '...' : '' ) );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_score':
				$score = $this->calculate_product_seo_score( $post_id );
				// Handle case where score might be returned as an array
				if ( is_array( $score ) ) {
					$score_value = isset( $score['value'] ) ? $score['value'] : 0;
				} else {
					$score_value = is_numeric( $score ) ? $score : 0;
				}
				$color = $score_value >= 80 ? 'green' : ( $score_value >= 50 ? 'orange' : 'red' );
				echo '<span class="seo-simple-score seo-simple-score-' . $color . '">' . $score_value . '</span>';
				break;
				
			case 'seo_focus_keyword':
				$keyword = get_post_meta( $post_id, '_seo_simple_product_focus_keyword', true );
				if ( ! $keyword ) {
					$keyword = $this->seo_meta->get_focus_keyword( $post_id );
				}
				if ( $keyword && is_string( $keyword ) ) {
					echo esc_html( $keyword );
				} elseif ( is_array( $keyword ) ) {
					// Handle case where keyword is stored as array
					echo esc_html( implode( ', ', array_filter( $keyword ) ) );
				} elseif ( is_object( $keyword ) ) {
					// Handle case where keyword is an object
					echo esc_html( 'Invalid data type' );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Not set', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_schema_type':
				$schema_type = get_post_meta( $post_id, '_seo_simple_product_schema_type', true );
				echo $schema_type ? esc_html( $schema_type ) : '<span class="seo-simple-missing">' . __( 'Product', 'seo-simple' ) . '</span>';
				break;
		}
	}

	/**
	 * AJAX handler for bulk editing SEO.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_edit() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_bulk_edit' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
		$seo_data = isset( $_POST['seo_data'] ) ? $_POST['seo_data'] : array();
		
		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected.', 'seo-simple' ) ) );
		}
		
		$processed = 0;
		$errors = 0;
		
		foreach ( $post_ids as $post_id ) {
			$result = $this->update_post_seo( $post_id, $seo_data );
			
			if ( $result ) {
				$processed++;
			} else {
				$errors++;
			}
		}
		
		wp_send_json_success( array(
			'message' => sprintf(
				__( 'Updated SEO data for %d posts. %d errors occurred.', 'seo-simple' ),
				$processed,
				$errors
			),
			'processed' => $processed,
			'errors' => $errors,
		) );
	}

	/**
	 * AJAX handler for bulk analyzing SEO.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_analyze() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_bulk_analyze' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
		
		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected.', 'seo-simple' ) ) );
		}
		
		$processed = 0;
		$errors = 0;
		$results = array();
		
		foreach ( $post_ids as $post_id ) {
			$result = $this->analyze_post_seo( $post_id );
			
			if ( $result ) {
				$processed++;
				$results[$post_id] = $result;
			} else {
				$errors++;
			}
		}
		
		wp_send_json_success( array(
			'message' => sprintf(
				__( 'Analyzed SEO for %d posts. %d errors occurred.', 'seo-simple' ),
				$processed,
				$errors
			),
			'processed' => $processed,
			'errors' => $errors,
			'results' => $results,
		) );
	}

	/**
	 * AJAX handler for bulk optimizing SEO.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_optimize() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_bulk_optimize' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
		
		if ( empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No posts selected.', 'seo-simple' ) ) );
		}
		
		$processed = 0;
		$errors = 0;
		
		foreach ( $post_ids as $post_id ) {
			$result = $this->auto_optimize_post_seo( $post_id );
			
			if ( $result ) {
				$processed++;
			} else {
				$errors++;
			}
		}
		
		wp_send_json_success( array(
			'message' => sprintf(
				__( 'Optimized SEO for %d posts. %d errors occurred.', 'seo-simple' ),
				$processed,
				$errors
			),
			'processed' => $processed,
			'errors' => $errors,
		) );
	}

	/**
	 * AJAX handler for inline editing.
	 *
	 * @since    1.0.0
	 */
	public function ajax_inline_edit() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_inline_edit' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : '';
		$value = isset( $_POST['value'] ) ? sanitize_text_field( $_POST['value'] ) : '';
		
		if ( ! $post_id || ! $field ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'seo-simple' ) ) );
		}
		
		// Update the field
		$result = $this->update_post_seo_field( $post_id, $field, $value );
		
		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Field updated successfully.', 'seo-simple' ),
				'value' => $value,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update field.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for exporting SEO data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_export_seo() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_export_seo' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_types = isset( $_POST['export_post_types'] ) ? array_map( 'sanitize_text_field', $_POST['export_post_types'] ) : array( 'post' );
		$format = isset( $_POST['export_format'] ) ? sanitize_text_field( $_POST['export_format'] ) : 'csv';
		$post_status = isset( $_POST['export_status'] ) ? sanitize_text_field( $_POST['export_status'] ) : 'publish';
		$export_fields = isset( $_POST['export_fields'] ) ? array_map( 'sanitize_text_field', $_POST['export_fields'] ) : array();
		$export_only_missing = isset( $_POST['export_only_missing'] ) && $_POST['export_only_missing'] === 'on';
		$include_backup = isset( $_POST['export_include_backup'] ) && $_POST['export_include_backup'] === 'on';
		
		// Get SEO data
		$data = $this->export_seo_data_advanced( $post_types, $post_status, $export_fields, $export_only_missing, $include_backup );
		
		if ( empty( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'No data found to export.', 'seo-simple' ) ) );
		}
		
		// Generate file
		$file = $this->generate_export_file( $data, $format );
		
		if ( $file ) {
			// Save to recent exports
			$this->save_recent_export( $file, $post_types, count( $data ) );
			
			wp_send_json_success( array(
				'message' => __( 'Export file generated successfully.', 'seo-simple' ),
				'file_url' => $file,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to generate export file.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for importing SEO data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_import_seo() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_import_seo' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check if file was uploaded
		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'File upload failed.', 'seo-simple' ) ) );
		}
		
		// Get import settings
		$import_mode = isset( $_POST['import_mode'] ) ? sanitize_text_field( $_POST['import_mode'] ) : 'update';
		$create_backup = isset( $_POST['import_backup'] ) && $_POST['import_backup'] === 'on';
		
		// Process the file
		$result = $this->process_import_file( $_FILES['import_file'], $import_mode, $create_backup );
		
		if ( $result ) {
			// Determine status
			$status = 'success';
			if ( $result['errors'] > 0 && $result['updated'] > 0 ) {
				$status = 'partial';
			} elseif ( $result['errors'] > 0 && $result['updated'] === 0 ) {
				$status = 'failed';
			}
			
			// Save to recent imports
			$upload_dir = wp_upload_dir();
			$file_url = $upload_dir['url'] . '/' . basename( $_FILES['import_file']['name'] );
			$backup_file = $create_backup ? $upload_dir['path'] . '/seo-backup-' . date( 'Y-m-d-H-i-s' ) . '.json' : '';
			
			$this->save_recent_import( $file_url, $result['processed'], $status, $backup_file );
			
			wp_send_json_success( array(
				'message' => sprintf(
					__( 'Import completed. %d records processed, %d records updated, %d errors.', 'seo-simple' ),
					$result['processed'],
					$result['updated'],
					$result['errors']
				),
				'result' => $result,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Import failed.', 'seo-simple' ) ) );
		}
	}

	/**
	 * Analyze SEO for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function analyze_post_seo( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return false;
		}
		
		// Load the analysis class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-seo-simple-analysis.php';
		$analysis = new Seo_Simple_Analysis();
		
		// Get SEO data
		$title = $this->seo_meta->get_title( $post_id );
		$description = $this->seo_meta->get_description( $post_id );
		$focus_keyword = $this->seo_meta->get_focus_keyword( $post_id );
		
		// Perform analysis
		$results = $analysis->analyze_seo_cached( $post_id, $title, $description, $post->post_content, $focus_keyword );
		
		// Save results
		$this->seo_meta->save_seo_analysis( $post_id, $results );
		
		return true;
	}

	/**
	 * Auto-optimize SEO for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function auto_optimize_post_seo( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return false;
		}
		
		// Get global settings
		$settings = $this->seo_meta->get_global_settings();
		
		// Generate SEO title if missing
		$title = $this->seo_meta->get_title( $post_id );
		if ( empty( $title ) ) {
			$template = isset( $settings['seo_title_format'] ) ? $settings['seo_title_format'] : '%post_title% | %site_name%';
			$generated_title = str_replace(
				array( '%post_title%', '%site_name%' ),
				array( get_the_title( $post_id ), get_bloginfo( 'name' ) ),
				$template
			);
			
			$this->seo_meta->save_title( $post_id, $generated_title );
		}
		
		// Generate SEO description if missing
		$description = $this->seo_meta->get_description( $post_id );
		if ( empty( $description ) ) {
			$excerpt = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
			$generated_description = wp_trim_words( strip_tags( $excerpt ), 30 );
			
			$this->seo_meta->save_description( $post_id, $generated_description );
		}
		
		// Generate focus keyword if missing
		$focus_keyword = $this->seo_meta->get_focus_keyword( $post_id );
		if ( empty( $focus_keyword ) ) {
			// Use the post title as focus keyword
			$this->seo_meta->save_focus_keyword( $post_id, get_the_title( $post_id ) );
		}
		
		// Analyze the post
		$this->analyze_post_seo( $post_id );
		
		return true;
	}

	/**
	 * Reset SEO for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function reset_post_seo( $post_id ) {
		// Delete all SEO meta
		$this->seo_meta->delete_all_meta( $post_id );
		
		return true;
	}

	/**
	 * Generate schema for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function generate_post_schema( $post_id ) {
		// Get schema data
		$schema_data = $this->seo_meta->get_schema( $post_id );
		
		// Generate schema based on post type
		$post_type = get_post_type( $post_id );
		
		switch ( $post_type ) {
			case 'product':
				// Generate Product schema
				$schema = array(
					'@context' => 'https://schema.org',
					'@type' => 'Product',
					'name' => get_the_title( $post_id ),
					'description' => get_the_excerpt( $post_id ),
				);
				break;
				
			case 'post':
				// Generate Article schema
				$schema = array(
					'@context' => 'https://schema.org',
					'@type' => 'Article',
					'headline' => get_the_title( $post_id ),
					'description' => get_the_excerpt( $post_id ),
					'author' => array(
						'@type' => 'Person',
						'name' => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
					),
					'datePublished' => get_the_date( 'c', $post_id ),
					'dateModified' => get_the_modified_date( 'c', $post_id ),
				);
				break;
				
			default:
				// Generate WebPage schema
				$schema = array(
					'@context' => 'https://schema.org',
					'@type' => 'WebPage',
					'name' => get_the_title( $post_id ),
					'description' => get_the_excerpt( $post_id ),
				);
				break;
		}
		
		// Save schema
		$this->seo_meta->save_schema( $post_id, $schema );
		
		return true;
	}

	/**
	 * Update SEO data for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $seo_data   The SEO data.
	 * @return   bool                  True on success, false on failure.
	 */
	private function update_post_seo( $post_id, $seo_data ) {
		// Update each field
		if ( isset( $seo_data['title'] ) ) {
			$this->seo_meta->save_title( $post_id, sanitize_text_field( $seo_data['title'] ) );
		}
		
		if ( isset( $seo_data['description'] ) ) {
			$this->seo_meta->save_description( $post_id, sanitize_textarea_field( $seo_data['description'] ) );
		}
		
		if ( isset( $seo_data['focus_keyword'] ) ) {
			$this->seo_meta->save_focus_keyword( $post_id, sanitize_text_field( $seo_data['focus_keyword'] ) );
		}
		
		if ( isset( $seo_data['noindex'] ) ) {
			$this->seo_meta->save_noindex( $post_id, (bool) $seo_data['noindex'] );
		}
		
		if ( isset( $seo_data['nofollow'] ) ) {
			$this->seo_meta->save_nofollow( $post_id, (bool) $seo_data['nofollow'] );
		}
		
		return true;
	}

	/**
	 * Update a single SEO field for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $field      The field name.
	 * @param    string    $value      The field value.
	 * @return   bool                  True on success, false on failure.
	 */
	private function update_post_seo_field( $post_id, $field, $value ) {
		switch ( $field ) {
			case 'title':
				$this->seo_meta->save_title( $post_id, $value );
				break;
				
			case 'description':
				$this->seo_meta->save_description( $post_id, $value );
				break;
				
			case 'focus_keyword':
				$this->seo_meta->save_focus_keyword( $post_id, $value );
				break;
				
			default:
				return false;
		}
		
		return true;
	}

	/**
	 * Calculate product SEO score.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   int                   The SEO score.
	 */
	private function calculate_product_seo_score( $post_id ) {
		$score = 0;
		$max_score = 100;
		
		// Check SEO title
		$title = get_post_meta( $post_id, '_seo_simple_product_title', true );
		if ( ! $title ) {
			$title = $this->seo_meta->get_title( $post_id );
		}
		
		if ( $title ) {
			$length = strlen( $title );
			if ( $length >= 50 && $length <= 60 ) {
				$score += 25;
			} elseif ( $length > 0 ) {
				$score += 15;
			}
		}
		
		// Check SEO description
		$description = get_post_meta( $post_id, '_seo_simple_product_description', true );
		if ( ! $description ) {
			$description = $this->seo_meta->get_description( $post_id );
		}
		
		if ( $description ) {
			$length = strlen( $description );
			if ( $length >= 150 && $length <= 160 ) {
				$score += 25;
			} elseif ( $length > 0 ) {
				$score += 15;
			}
		}
		
		// Check focus keyword
		$keyword = get_post_meta( $post_id, '_seo_simple_product_focus_keyword', true );
		if ( ! $keyword ) {
			$keyword = $this->seo_meta->get_focus_keyword( $post_id );
		}
		
		if ( $keyword ) {
			$score += 25;
			
			// Check if keyword is in title
			if ( stripos( $title, $keyword ) !== false ) {
				$score += 12.5;
			}
			
			// Check if keyword is in description
			if ( stripos( $description, $keyword ) !== false ) {
				$score += 12.5;
			}
		}
		
		return min( $score, $max_score );
	}

	/**
	 * Export SEO data with advanced options.
	 *
	 * @since    1.0.0
	 * @param    array     $post_types          The post types to export.
	 * @param    string    $post_status         The post status filter.
	 * @param    array     $export_fields       The fields to export.
	 * @param    bool      $export_only_missing Export only posts with missing data.
	 * @param    bool      $include_backup      Include backup data.
	 * @return   array                          The SEO data.
	 */
	private function export_seo_data_advanced( $post_types, $post_status, $export_fields, $export_only_missing, $include_backup ) {
		$args = array(
			'post_type' => $post_types,
			'post_status' => $post_status === 'all' ? 'any' : $post_status,
			'posts_per_page' => -1,
		);
		
		$posts = get_posts( $args );
		$data = array();
		
		// Default fields if none specified
		if ( empty( $export_fields ) ) {
			$export_fields = array( 'post_id', 'post_title', 'post_url', 'seo_title', 'seo_description', 'focus_keyword' );
		}
		
		foreach ( $posts as $post ) {
			$row = array();
			$has_missing_data = false;
			
			// Always include post ID
			$row['ID'] = $post->ID;
			
			// Add requested fields
			if ( in_array( 'post_title', $export_fields ) ) {
				$row['post_title'] = $post->post_title;
			}
			
			if ( in_array( 'post_url', $export_fields ) ) {
				$row['post_url'] = get_permalink( $post->ID );
			}
			
			if ( in_array( 'seo_title', $export_fields ) ) {
				$title = $this->seo_meta->get_title( $post->ID );
				$row['seo_title'] = $title ? $title : '';
				if ( empty( $title ) ) $has_missing_data = true;
			}
			
			if ( in_array( 'seo_description', $export_fields ) ) {
				$description = $this->seo_meta->get_description( $post->ID );
				$row['seo_description'] = $description ? $description : '';
				if ( empty( $description ) ) $has_missing_data = true;
			}
			
			if ( in_array( 'focus_keyword', $export_fields ) ) {
				$keyword = $this->seo_meta->get_focus_keyword( $post->ID );
				$row['focus_keyword'] = $keyword ? $keyword : '';
				if ( empty( $keyword ) ) $has_missing_data = true;
			}
			
			if ( in_array( 'seo_score', $export_fields ) ) {
				$score = $this->seo_meta->get_overall_seo_score( $post->ID );
				$row['seo_score'] = $score;
			}
			
			if ( in_array( 'meta_robots', $export_fields ) ) {
				$noindex = $this->seo_meta->get_noindex( $post->ID );
				$nofollow = $this->seo_meta->get_nofollow( $post->ID );
				$robots = array();
				if ( $noindex ) $robots[] = 'noindex';
				if ( $nofollow ) $robots[] = 'nofollow';
				$row['meta_robots'] = implode( ', ', $robots );
			}
			
			if ( in_array( 'canonical_url', $export_fields ) ) {
				$canonical = $this->seo_meta->get_canonical_url( $post->ID );
				$row['canonical_url'] = $canonical ? $canonical : '';
			}
			
			if ( in_array( 'schema_type', $export_fields ) ) {
				$schema = $this->seo_meta->get_schema( $post->ID );
				$schema_type = isset( $schema['@type'] ) ? $schema['@type'] : '';
				$row['schema_type'] = $schema_type;
			}
			
			// Add WooCommerce fields if applicable
			if ( in_array( 'product_title', $export_fields ) && get_post_type( $post->ID ) === 'product' ) {
				$row['product_title'] = get_post_meta( $post->ID, '_seo_simple_product_title', true );
			}
			
			if ( in_array( 'product_description', $export_fields ) && get_post_type( $post->ID ) === 'product' ) {
				$row['product_description'] = get_post_meta( $post->ID, '_seo_simple_product_description', true );
			}
			
			if ( in_array( 'product_focus_keyword', $export_fields ) && get_post_type( $post->ID ) === 'product' ) {
				$row['product_focus_keyword'] = get_post_meta( $post->ID, '_seo_simple_product_focus_keyword', true );
			}
			
			if ( in_array( 'product_brand', $export_fields ) && get_post_type( $post->ID ) === 'product' ) {
				$row['product_brand'] = get_post_meta( $post->ID, '_seo_simple_product_brand', true );
			}
			
			if ( in_array( 'product_schema_type', $export_fields ) && get_post_type( $post->ID ) === 'product' ) {
				$row['product_schema_type'] = get_post_meta( $post->ID, '_seo_simple_product_schema_type', true );
			}
			
			// Add backup data if requested
			if ( $include_backup ) {
				$row['backup_data'] = json_encode( array(
					'title' => $this->seo_meta->get_title( $post->ID ),
					'description' => $this->seo_meta->get_description( $post->ID ),
					'focus_keyword' => $this->seo_meta->get_focus_keyword( $post->ID ),
					'noindex' => $this->seo_meta->get_noindex( $post->ID ),
					'nofollow' => $this->seo_meta->get_nofollow( $post->ID ),
				) );
			}
			
			// Skip if only exporting missing data and this post has all data
			if ( $export_only_missing && ! $has_missing_data ) {
				continue;
			}
			
			$data[] = $row;
		}
		
		return $data;
	}

	/**
	 * Save recent export.
	 *
	 * @since    1.0.0
	 * @param    string    $file_url     The file URL.
	 * @param    array     $post_types   The post types.
	 * @param    int       $record_count The number of records.
	 */
	private function save_recent_export( $file_url, $post_types, $record_count ) {
		$exports = get_option( 'seo_simple_recent_exports', array() );
		
		// Parse file URL to get file path
		$upload_dir = wp_upload_dir();
		$file_path = str_replace( $upload_dir['url'], $upload_dir['path'], $file_url );
		
		$new_export = array(
			'date' => current_time( 'mysql' ),
			'file' => $file_path,
			'download_url' => $file_url,
			'post_types' => $post_types,
			'records' => $record_count,
		);
		
		// Add to beginning of array
		array_unshift( $exports, $new_export );
		
		// Keep only last 20 exports
		$exports = array_slice( $exports, 0, 20 );
		
		update_option( 'seo_simple_recent_exports', $exports );
	}

	/**
	 * Save recent import.
	 *
	 * @since    1.0.0
	 * @param    string    $file_url     The file URL.
	 * @param    int       $record_count The number of records.
	 * @param    string    $status       The import status.
	 * @param    string    $backup_file  The backup file path.
	 */
	private function save_recent_import( $file_url, $record_count, $status, $backup_file = '' ) {
		$imports = get_option( 'seo_simple_recent_imports', array() );
		
		// Parse file URL to get file path
		$upload_dir = wp_upload_dir();
		$file_path = str_replace( $upload_dir['url'], $upload_dir['path'], $file_url );
		
		$new_import = array(
			'date' => current_time( 'mysql' ),
			'file' => $file_path,
			'records' => $record_count,
			'status' => $status,
		);
		
		if ( $backup_file ) {
			$backup_url = str_replace( $upload_dir['path'], $upload_dir['url'], $backup_file );
			$new_import['backup_file'] = $backup_file;
			$new_import['backup_download_url'] = $backup_url;
		}
		
		// Add to beginning of array
		array_unshift( $imports, $new_import );
		
		// Keep only last 20 imports
		$imports = array_slice( $imports, 0, 20 );
		
		update_option( 'seo_simple_recent_imports', $imports );
	}

	/**
	 * Export SEO data.
	 *
	 * @since    1.0.0
	 * @param    string    $post_type    The post type.
	 * @return   array                   The SEO data.
	 */
	private function export_seo_data( $post_type ) {
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);
		
		$posts = get_posts( $args );
		$data = array();
		
		foreach ( $posts as $post ) {
			$row = array(
				'ID' => $post->ID,
				'title' => $post->post_title,
				'seo_title' => $this->seo_meta->get_title( $post->ID ),
				'seo_description' => $this->seo_meta->get_description( $post->ID ),
				'focus_keyword' => $this->seo_meta->get_focus_keyword( $post->ID ),
				'noindex' => $this->seo_meta->get_noindex( $post->ID ) ? '1' : '0',
				'nofollow' => $this->seo_meta->get_nofollow( $post->ID ) ? '1' : '0',
			);
			
			// Add WooCommerce fields if applicable
			if ( $post_type === 'product' ) {
				$row['product_title'] = get_post_meta( $post->ID, '_seo_simple_product_title', true );
				$row['product_description'] = get_post_meta( $post->ID, '_seo_simple_product_description', true );
				$row['product_focus_keyword'] = get_post_meta( $post->ID, '_seo_simple_product_focus_keyword', true );
				$row['product_brand'] = get_post_meta( $post->ID, '_seo_simple_product_brand', true );
				$row['product_schema_type'] = get_post_meta( $post->ID, '_seo_simple_product_schema_type', true );
			}
			
			$data[] = $row;
		}
		
		return $data;
	}

	/**
	 * Generate export file.
	 *
	 * @since    1.0.0
	 * @param    array     $data       The data to export.
	 * @param    string    $format     The file format.
	 * @return   string|false          The file URL or false on failure.
	 */
	private function generate_export_file( $data, $format ) {
		if ( empty( $data ) ) {
			return false;
		}
		
		$upload_dir = wp_upload_dir();
		$filename = 'seo-export-' . date( 'Y-m-d-H-i-s' ) . '.' . $format;
		$filepath = $upload_dir['path'] . '/' . $filename;
		
		// Create file based on format
		if ( $format === 'csv' ) {
			$handle = fopen( $filepath, 'w' );
			
			// Add header row
			fputcsv( $handle, array_keys( $data[0] ) );
			
			// Add data rows
			foreach ( $data as $row ) {
				fputcsv( $handle, $row );
			}
			
			fclose( $handle );
		} elseif ( $format === 'json' ) {
			file_put_contents( $filepath, json_encode( $data, JSON_PRETTY_PRINT ) );
		} else {
			return false;
		}
		
		// Return file URL
		return $upload_dir['url'] . '/' . $filename;
	}

	/**
	 * AJAX handler for previewing import data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_preview_import() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_import_seo' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check if file was uploaded
		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'File upload failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Preview the file
		$result = $this->preview_import_file( $_FILES['import_file'] );
		
		if ( $result ) {
			wp_send_json_success( array(
				'columns' => $result['columns'],
				'preview' => $result['preview'],
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to preview file.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for processing import with mappings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_process_import() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_import_seo' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check if file was uploaded
		if ( ! isset( $_FILES['import_file'] ) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => __( 'File upload failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Get mappings and settings
		$mappings = isset( $_POST['mappings'] ) ? json_decode( stripslashes( $_POST['mappings'] ), true ) : array();
		$import_mode = isset( $_POST['import_mode'] ) ? sanitize_text_field( $_POST['import_mode'] ) : 'update';
		$create_backup = isset( $_POST['create_backup'] ) && $_POST['create_backup'] === '1';
		$dry_run = isset( $_POST['dry_run'] ) && $_POST['dry_run'] === '1';
		
		// Process the file
		$result = $this->process_import_file_with_mappings( $_FILES['import_file'], $mappings, $import_mode, $create_backup, $dry_run );
		
		if ( $result ) {
			wp_send_json_success( array(
				'message' => sprintf(
					__( 'Import completed. %d records processed, %d records updated, %d errors.', 'seo-simple' ),
					$result['processed'],
					$result['updated'],
					$result['errors']
				),
				'result' => $result,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Import failed.', 'seo-simple' ) ) );
		}
	}

	/**
	 * Preview import file.
	 *
	 * @since    1.0.0
	 * @param    array     $file       The uploaded file.
	 * @return   array|false           The preview data or false on failure.
	 */
	private function preview_import_file( $file ) {
		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
		
		if ( $extension !== 'csv' && $extension !== 'json' ) {
			return false;
		}
		
		$columns = array();
		$preview = array();
		
		// Read file based on format
		if ( $extension === 'csv' ) {
			$handle = fopen( $file['tmp_name'], 'r' );
			
			if ( ! $handle ) {
				return false;
			}
			
			// Get header row
			$header = fgetcsv( $handle );
			if ( ! $header ) {
				fclose( $handle );
				return false;
			}
			
			$columns = $header;
			
			// Get preview rows (max 5)
			$row_count = 0;
			while ( ( $row = fgetcsv( $handle ) ) !== false && $row_count < 5 ) {
				$preview[] = $row;
				$row_count++;
			}
			
			fclose( $handle );
		} elseif ( $extension === 'json' ) {
			$json_data = file_get_contents( $file['tmp_name'] );
			$data = json_decode( $json_data, true );
			
			if ( ! $data || ! is_array( $data ) || empty( $data ) ) {
				return false;
			}
			
			// Get columns from first row
			$columns = array_keys( $data[0] );
			
			// Get preview rows (max 5)
			$row_count = 0;
			foreach ( $data as $row ) {
				if ( $row_count >= 5 ) {
					break;
				}
				$preview[] = array_values( $row );
				$row_count++;
			}
		}
		
		return array(
			'columns' => $columns,
			'preview' => $preview,
		);
	}

	/**
	 * Process import file with field mappings.
	 *
	 * @since    1.0.0
	 * @param    array     $file          The uploaded file.
	 * @param    array     $mappings      The field mappings.
	 * @param    string    $import_mode   The import mode.
	 * @param    bool      $create_backup Whether to create backup.
	 * @param    bool      $dry_run       Whether this is a dry run.
	 * @return   array|false              The import result or false on failure.
	 */
	private function process_import_file_with_mappings( $file, $mappings, $import_mode, $create_backup, $dry_run ) {
		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
		
		if ( $extension !== 'csv' && $extension !== 'json' ) {
			return false;
		}
		
		$data = array();
		
		// Read file based on format
		if ( $extension === 'csv' ) {
			$handle = fopen( $file['tmp_name'], 'r' );
			
			if ( ! $handle ) {
				return false;
			}
			
			// Get header row
			$header = fgetcsv( $handle );
			if ( ! $header ) {
				fclose( $handle );
				return false;
			}
			
			// Map column indexes to field names
			$column_map = array();
			foreach ( $header as $index => $column ) {
				if ( isset( $mappings[$column] ) ) {
					$column_map[$index] = $mappings[$column];
				}
			}
			
			// Get data rows
			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				$mapped_row = array();
				foreach ( $column_map as $index => $field ) {
					if ( isset( $row[$index] ) ) {
						$mapped_row[$field] = $row[$index];
					}
				}
				if ( ! empty( $mapped_row ) ) {
					$data[] = $mapped_row;
				}
			}
			
			fclose( $handle );
		} elseif ( $extension === 'json' ) {
			$json_data = file_get_contents( $file['tmp_name'] );
			$raw_data = json_decode( $json_data, true );
			
			if ( ! $raw_data || ! is_array( $raw_data ) ) {
				return false;
			}
			
			// Map fields according to mappings
			foreach ( $raw_data as $row ) {
				$mapped_row = array();
				foreach ( $mappings as $column => $field ) {
					if ( isset( $row[$column] ) ) {
						$mapped_row[$field] = $row[$column];
					}
				}
				if ( ! empty( $mapped_row ) ) {
					$data[] = $mapped_row;
				}
			}
		}
		
		// Process data
		$processed = 0;
		$updated = 0;
		$errors = 0;
		
		foreach ( $data as $row ) {
			$processed++;
			
			$post_id = isset( $row['post_id'] ) ? intval( $row['post_id'] ) : 0;
			
			if ( ! $post_id || ! get_post( $post_id ) ) {
				$errors++;
				continue;
			}
			
			// Skip existing posts if mode is skip_existing
			if ( $import_mode === 'skip_existing' ) {
				$existing_title = $this->seo_meta->get_title( $post_id );
				$existing_description = $this->seo_meta->get_description( $post_id );
				if ( $existing_title || $existing_description ) {
					continue;
				}
			}
			
			// Create backup if needed
			if ( $create_backup && ! $dry_run ) {
				$this->create_import_backup( $post_id );
			}
			
			// Update SEO data
			$seo_data = array();
			
			if ( isset( $row['seo_title'] ) ) {
				$seo_data['title'] = $row['seo_title'];
			}
			
			if ( isset( $row['seo_description'] ) ) {
				$seo_data['description'] = $row['seo_description'];
			}
			
			if ( isset( $row['focus_keyword'] ) ) {
				$seo_data['focus_keyword'] = $row['focus_keyword'];
			}
			
			if ( isset( $row['noindex'] ) ) {
				$seo_data['noindex'] = $row['noindex'] === '1' || $row['noindex'] === 'true';
			}
			
			if ( isset( $row['nofollow'] ) ) {
				$seo_data['nofollow'] = $row['nofollow'] === '1' || $row['nofollow'] === 'true';
			}
			
			// Apply import mode
			if ( $import_mode === 'overwrite' ) {
				// Overwrite all data
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			} elseif ( $import_mode === 'merge' ) {
				// Merge with existing data
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			} else {
				// Update mode (default)
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			}
		}
		
		return array(
			'processed' => $processed,
			'updated' => $updated,
			'errors' => $errors,
		);
	}

	/**
	 * Create backup before import.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function create_import_backup( $post_id ) {
		$backup_data = array(
			'title' => $this->seo_meta->get_title( $post_id ),
			'description' => $this->seo_meta->get_description( $post_id ),
			'focus_keyword' => $this->seo_meta->get_focus_keyword( $post_id ),
			'noindex' => $this->seo_meta->get_noindex( $post_id ),
			'nofollow' => $this->seo_meta->get_nofollow( $post_id ),
		);
		
		// Save backup as post meta
		update_post_meta( $post_id, '_seo_simple_import_backup', $backup_data );
		
		return true;
	}

	/**
	 * Process import file.
	 *
	 * @since    1.0.0
	 * @param    array     $file          The uploaded file.
	 * @param    string    $import_mode   The import mode.
	 * @param    bool      $create_backup Whether to create backup.
	 * @return   array|false              The import result or false on failure.
	 */
	private function process_import_file( $file, $import_mode = 'update', $create_backup = false ) {
		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
		
		if ( $extension !== 'csv' && $extension !== 'json' ) {
			return false;
		}
		
		$data = array();
		
		// Read file based on format
		if ( $extension === 'csv' ) {
			$handle = fopen( $file['tmp_name'], 'r' );
			
			// Get header row
			$header = fgetcsv( $handle );
			
			// Get data rows
			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				$data[] = array_combine( $header, $row );
			}
			
			fclose( $handle );
		} elseif ( $extension === 'json' ) {
			$json_data = file_get_contents( $file['tmp_name'] );
			$data = json_decode( $json_data, true );
			
			if ( ! $data ) {
				return false;
			}
		}
		
		// Process data
		$processed = 0;
		$updated = 0;
		$errors = 0;
		
		foreach ( $data as $row ) {
			$processed++;
			
			$post_id = isset( $row['ID'] ) ? intval( $row['ID'] ) : 0;
			
			if ( ! $post_id || ! get_post( $post_id ) ) {
				$errors++;
				continue;
			}
			
			// Skip existing posts if mode is skip_existing
			if ( $import_mode === 'skip_existing' ) {
				$existing_title = $this->seo_meta->get_title( $post_id );
				$existing_description = $this->seo_meta->get_description( $post_id );
				if ( $existing_title || $existing_description ) {
					continue;
				}
			}
			
			// Create backup if needed
			if ( $create_backup ) {
				$this->create_import_backup( $post_id );
			}
			
			// Update SEO data
			$seo_data = array();
			
			if ( isset( $row['seo_title'] ) ) {
				$seo_data['title'] = $row['seo_title'];
			}
			
			if ( isset( $row['seo_description'] ) ) {
				$seo_data['description'] = $row['seo_description'];
			}
			
			if ( isset( $row['focus_keyword'] ) ) {
				$seo_data['focus_keyword'] = $row['focus_keyword'];
			}
			
			if ( isset( $row['noindex'] ) ) {
				$seo_data['noindex'] = $row['noindex'] === '1' || $row['noindex'] === 'true';
			}
			
			if ( isset( $row['nofollow'] ) ) {
				$seo_data['nofollow'] = $row['nofollow'] === '1' || $row['nofollow'] === 'true';
			}
			
			// Apply import mode
			if ( $import_mode === 'overwrite' ) {
				// Overwrite all data
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			} elseif ( $import_mode === 'merge' ) {
				// Merge with existing data
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			} else {
				// Update mode (default)
				if ( ! empty( $seo_data ) ) {
					$this->update_post_seo( $post_id, $seo_data );
					$updated++;
				}
			}
			
			// Update WooCommerce fields if applicable
			$post_type = get_post_type( $post_id );
			if ( $post_type === 'product' ) {
				if ( isset( $row['product_title'] ) ) {
					update_post_meta( $post_id, '_seo_simple_product_title', $row['product_title'] );
				}
				
				if ( isset( $row['product_description'] ) ) {
					update_post_meta( $post_id, '_seo_simple_product_description', $row['product_description'] );
				}
				
				if ( isset( $row['product_focus_keyword'] ) ) {
					update_post_meta( $post_id, '_seo_simple_product_focus_keyword', $row['product_focus_keyword'] );
				}
				
				if ( isset( $row['product_brand'] ) ) {
					update_post_meta( $post_id, '_seo_simple_product_brand', $row['product_brand'] );
				}
				
				if ( isset( $row['product_schema_type'] ) ) {
					update_post_meta( $post_id, '_seo_simple_product_schema_type', $row['product_schema_type'] );
				}
			}
		}
		
		return array(
			'processed' => $processed,
			'updated' => $updated,
			'errors' => $errors,
		);
	}
}
