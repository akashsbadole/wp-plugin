<?php

/**
 * Bulk Editor functionality for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Bulk Editor functionality for SEO Simple plugin.
 *
 * This class handles all bulk editing functionality including creating the bulk editor page,
 * retrieving and filtering posts, handling bulk updates, and search/filter functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Bulk_Editor {

	/**
	 * The SEO meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	public function __construct( $seo_meta ) {
		$this->seo_meta = $seo_meta;
	}

	/**
	 * Register the bulk editor page.
	 *
	 * @since    1.0.0
	 */
	public function register_bulk_editor_page() {
		add_submenu_page(
			'seo-simple',
			__( 'SEO Bulk Editor', 'seo-simple' ),
			__( 'Bulk Editor', 'seo-simple' ),
			'edit_posts',
			'seo-simple-bulk-editor',
			array( $this, 'render_bulk_editor_page' )
		);
	}

	/**
	 * Render the bulk editor page.
	 *
	 * @since    1.0.0
	 */
	public function render_bulk_editor_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/bulk-editor-page.php';
	}

	/**
	 * Get posts for bulk editing.
	 *
	 * @since    1.0.0
	 * @param    array   $filters    Optional filters.
	 * @param    int     $limit      Number of posts to return.
	 * @param    int     $offset     Offset for pagination.
	 * @return   array               Posts for bulk editing.
	 */
	public function get_posts_for_bulk_edit( $filters = array(), $limit = 20, $offset = 0 ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		// Create cache key based on filters and pagination
		$cache_key = 'seo_simple_bulk_edit_' . md5( serialize( $filters ) . '_' . $limit . '_' . $offset );
		
		return $cache_manager->remember( $cache_key, function() use ( $filters, $limit, $offset ) {
			// Use a more efficient query with proper indexing
			$posts = array();
			$where_clauses = array();
			$join_clauses = array();

			global $wpdb;

			// Build base query with optimized JOIN strategy
			$query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT p.ID, p.post_title, p.post_type, p.post_status, p.post_modified
				FROM {$wpdb->posts} p
				WHERE p.post_status = 'publish' AND p.post_type IN ('post', 'page')
				AND p.post_password = ''"; // Exclude password protected posts

			// Apply filters
			if ( ! empty( $filters['missing_title'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_title ON p.ID = pm_title.post_id AND pm_title.meta_key = '_seo_simple_title'";
				$where_clauses[] = "(pm_title.meta_key IS NULL OR pm_title.meta_value = '')";
			}

			if ( ! empty( $filters['missing_description'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_desc ON p.ID = pm_desc.post_id AND pm_desc.meta_key = '_seo_simple_description'";
				$where_clauses[] = "(pm_desc.meta_key IS NULL OR pm_desc.meta_value = '')";
			}

			if ( ! empty( $filters['missing_focus_keyword'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_keyword ON p.ID = pm_keyword.post_id AND pm_keyword.meta_key = '_seo_simple_focus_keyword'";
				$where_clauses[] = "(pm_keyword.meta_key IS NULL OR pm_keyword.meta_value = '')";
			}

			if ( ! empty( $filters['post_type'] ) ) {
				$where_clauses[] = $wpdb->prepare( "p.post_type = %s", $filters['post_type'] );
			}

			if ( ! empty( $filters['search'] ) ) {
				$where_clauses[] = $wpdb->prepare( "p.post_title LIKE %s", '%' . $wpdb->esc_like( $filters['search'] ) . '%' );
			}

			// If no specific filters are selected, get all posts
			if ( empty( $where_clauses ) ) {
				// Default to getting all posts ordered by last modified
				$query .= " ORDER BY p.post_modified DESC";
			} else {
				// Add join clauses
				if ( ! empty( $join_clauses ) ) {
					$query .= ' ' . implode( ' ', array_unique( $join_clauses ) );
				}

				// Add where clauses
				$query .= ' AND (' . implode( ' OR ', $where_clauses ) . ')';

				// Add order
				$query .= " ORDER BY p.post_modified DESC";
			}

			// Add limit and offset
			$query .= " LIMIT %d OFFSET %d";

			$results = $wpdb->get_results( $wpdb->prepare( $query, $limit, $offset ) );
			
			// Store total count for pagination without additional query
			$total_count = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			wp_cache_set( 'seo_simple_bulk_edit_total_' . md5( serialize( $filters ) ), $total_count, '', 1800 );
			
			// Collect post IDs for bulk meta retrieval
			$post_ids = array();
			foreach ( $results as $result ) {
				$post_ids[] = $result->ID;
			}
			
			// Get meta data in bulk for efficiency
			$meta_data = array();
			if ( ! empty( $post_ids ) ) {
				$meta_fields = array( 'title', 'description', 'focus_keyword' );
				foreach ( $meta_fields as $field ) {
					$field_data = $this->seo_meta->get_meta_for_posts( $post_ids, $field );
					foreach ( $field_data as $post_id => $value ) {
						if ( ! isset( $meta_data[$post_id] ) ) {
							$meta_data[$post_id] = array();
						}
						$meta_data[$post_id][$field] = $value;
					}
				}
				
				// Get noindex/nofollow data in bulk
				$noindex_data = $this->seo_meta->get_meta_for_posts( $post_ids, 'noindex' );
				$nofollow_data = $this->seo_meta->get_meta_for_posts( $post_ids, 'nofollow' );
				$canonical_data = $this->seo_meta->get_meta_for_posts( $post_ids, 'canonical' );
				
				foreach ( $post_ids as $post_id ) {
					if ( ! isset( $meta_data[$post_id] ) ) {
						$meta_data[$post_id] = array();
					}
					$meta_data[$post_id]['noindex'] = isset( $noindex_data[$post_id] ) ? $noindex_data[$post_id] : '';
					$meta_data[$post_id]['nofollow'] = isset( $nofollow_data[$post_id] ) ? $nofollow_data[$post_id] : '';
					$meta_data[$post_id]['canonical'] = isset( $canonical_data[$post_id] ) ? $canonical_data[$post_id] : '';
				}
			}

			foreach ( $results as $result ) {
				$post_id = $result->ID;
				$post_meta = isset( $meta_data[$post_id] ) ? $meta_data[$post_id] : array();
				
				$posts[] = array(
					'ID' => $result->ID,
					'title' => $result->post_title,
					'type' => $result->post_type,
					'status' => $result->post_status,
					'modified' => $result->post_modified,
					'edit_link' => get_edit_post_link( $result->ID ),
					'view_link' => get_permalink( $result->ID ),
					'seo_title' => isset( $post_meta['title'] ) ? $post_meta['title'] : '',
					'seo_description' => isset( $post_meta['description'] ) ? $post_meta['description'] : '',
					'focus_keyword' => isset( $post_meta['focus_keyword'] ) ? $post_meta['focus_keyword'] : '',
					'noindex' => ! empty( $post_meta['noindex'] ),
					'nofollow' => ! empty( $post_meta['nofollow'] ),
					'canonical' => isset( $post_meta['canonical'] ) ? $post_meta['canonical'] : '',
				);
			}
			
			return $posts;
		}, 1800 ); // Cache for 30 minutes
	}

	/**
	 * Count posts for bulk editing.
	 *
	 * @since    1.0.0
	 * @param    array   $filters    Optional filters.
	 * @return   int                 Total count.
	 */
	public function count_posts_for_bulk_edit( $filters = array() ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		// Create cache key based on filters
		$cache_key = 'seo_simple_bulk_edit_count_' . md5( serialize( $filters ) );
		
		// Try to get cached total count from previous query
		$cached_total = wp_cache_get( 'seo_simple_bulk_edit_total_' . md5( serialize( $filters ) ) );
		if ( $cached_total !== false ) {
			return $cached_total;
		}
		
		return $cache_manager->remember( $cache_key, function() use ( $filters ) {
			$where_clauses = array();
			$join_clauses = array();

			global $wpdb;

			// Build base query
			$query = "SELECT COUNT(DISTINCT p.ID)
				FROM {$wpdb->posts} p
				WHERE p.post_status = 'publish' AND p.post_type IN ('post', 'page')";

			// Apply filters
			if ( ! empty( $filters['missing_title'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_title ON p.ID = pm_title.post_id AND pm_title.meta_key = '_seo_simple_title'";
				$where_clauses[] = "(pm_title.meta_key IS NULL OR pm_title.meta_value = '')";
			}

			if ( ! empty( $filters['missing_description'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_desc ON p.ID = pm_desc.post_id AND pm_desc.meta_key = '_seo_simple_description'";
				$where_clauses[] = "(pm_desc.meta_key IS NULL OR pm_desc.meta_value = '')";
			}

			if ( ! empty( $filters['missing_focus_keyword'] ) ) {
				$join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_keyword ON p.ID = pm_keyword.post_id AND pm_keyword.meta_key = '_seo_simple_focus_keyword'";
				$where_clauses[] = "(pm_keyword.meta_key IS NULL OR pm_keyword.meta_value = '')";
			}

			if ( ! empty( $filters['post_type'] ) ) {
				$where_clauses[] = $wpdb->prepare( "p.post_type = %s", $filters['post_type'] );
			}

			if ( ! empty( $filters['search'] ) ) {
				$where_clauses[] = $wpdb->prepare( "p.post_title LIKE %s", '%' . $wpdb->esc_like( $filters['search'] ) . '%' );
			}

			// Add join clauses
			if ( ! empty( $join_clauses ) ) {
				$query .= ' ' . implode( ' ', array_unique( $join_clauses ) );
			}

			// Add where clauses
			if ( ! empty( $where_clauses ) ) {
				$query .= ' AND (' . implode( ' OR ', $where_clauses ) . ')';
			}

			$count = (int) $wpdb->get_var( $query );
			
			return $count;
		}, 1800 ); // Cache for 30 minutes
	}

	/**
	 * Get available post types for filtering.
	 *
	 * @since    1.0.0
	 * @return   array    Available post types.
	 */
	public function get_available_post_types() {
		$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
		$available_types = array();

		foreach ( $post_types as $post_type ) {
			if ( $post_type->name !== 'attachment' ) {
				$available_types[$post_type->name] = $post_type->labels->singular_name;
			}
		}

		return $available_types;
	}

	/**
	 * Save bulk SEO data.
	 *
	 * @since    1.0.0
	 * @param    array   $data    SEO data to save.
	 * @return   array             Result of the operation.
	 */
	public function save_bulk_seo_data( $data ) {
		$results = array(
			'success' => 0,
			'failed' => 0,
			'errors' => array(),
		);

		if ( empty( $data ) || ! is_array( $data ) ) {
			$results['failed'] = 1;
			$results['errors'][] = __( 'No data provided.', 'seo-simple' );
			return $results;
		}

		// Clear bulk editor cache
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_manager->clear_cache_group( 'seo_simple' );

		foreach ( $data as $post_id => $seo_data ) {
			$post_id = intval( $post_id );

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				$results['failed']++;
				$results['errors'][] = sprintf( 
					__( 'You do not have permission to edit post %d.', 'seo-simple' ), 
					$post_id 
				);
				continue;
			}

			// Verify nonce
			if ( ! isset( $seo_data['nonce'] ) || ! wp_verify_nonce( $seo_data['nonce'], 'seo_simple_bulk_edit_' . $post_id ) ) {
				$results['failed']++;
				$results['errors'][] = sprintf( 
					__( 'Security verification failed for post %d.', 'seo-simple' ), 
					$post_id 
				);
				continue;
			}

			// Process and save each field
			$fields_to_save = array(
				'title' => 'sanitize_text_field',
				'description' => 'sanitize_textarea_field',
				'focus_keyword' => 'sanitize_text_field',
				'canonical' => 'esc_url_raw',
			);

			foreach ( $fields_to_save as $field_name => $sanitize_callback ) {
				if ( isset( $seo_data[$field_name] ) ) {
					$value = call_user_func( $sanitize_callback, $seo_data[$field_name] );
					update_post_meta( $post_id, '_seo_simple_' . $field_name, $value );
				}
			}

			// Handle checkbox fields
			if ( isset( $seo_data['noindex'] ) ) {
				update_post_meta( $post_id, '_seo_simple_noindex', 1 );
			} else {
				delete_post_meta( $post_id, '_seo_simple_noindex' );
			}

			if ( isset( $seo_data['nofollow'] ) ) {
				update_post_meta( $post_id, '_seo_simple_nofollow', 1 );
			} else {
				delete_post_meta( $post_id, '_seo_simple_nofollow' );
			}

			// Clear SEO analysis cache
			$this->seo_meta->clear_seo_analysis( $post_id );

			$results['success']++;
		}

		return $results;
	}

	/**
	 * Handle AJAX requests for bulk editor.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		add_action( 'wp_ajax_seo_simple_bulk_save', array( $this, 'ajax_bulk_save' ) );
		add_action( 'wp_ajax_seo_simple_bulk_filter', array( $this, 'ajax_bulk_filter' ) );
		add_action( 'wp_ajax_seo_simple_generate_auto_title', array( $this, 'ajax_generate_auto_title' ) );
		add_action( 'wp_ajax_seo_simple_generate_auto_description', array( $this, 'ajax_generate_auto_description' ) );
	}

	/**
	 * AJAX handler for bulk saving.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_save() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No data provided.', 'seo-simple' ) ) );
		}
		
		$data = json_decode( stripslashes( $_POST['data'] ), true );
		$results = $this->save_bulk_seo_data( $data );
		
		if ( $results['failed'] > 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Some posts could not be updated.', 'seo-simple' ),
				'results' => $results
			) );
		} else {
			wp_send_json_success( array(
				'message' => sprintf( __( '%d posts updated successfully.', 'seo-simple' ), $results['success'] ),
				'results' => $results
			) );
		}
	}

	/**
	 * AJAX handler for bulk filtering.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_filter() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		$filters = array();
		
		if ( isset( $_POST['missing_title'] ) ) {
			$filters['missing_title'] = true;
		}
		
		if ( isset( $_POST['missing_description'] ) ) {
			$filters['missing_description'] = true;
		}
		
		if ( isset( $_POST['missing_focus_keyword'] ) ) {
			$filters['missing_focus_keyword'] = true;
		}
		
		if ( isset( $_POST['post_type'] ) && ! empty( $_POST['post_type'] ) ) {
			$filters['post_type'] = sanitize_text_field( $_POST['post_type'] );
		}
		
		if ( isset( $_POST['search'] ) && ! empty( $_POST['search'] ) ) {
			$filters['search'] = sanitize_text_field( $_POST['search'] );
		}
		
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 20;
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		
		$posts = $this->get_posts_for_bulk_edit( $filters, $limit, $offset );
		$total = $this->count_posts_for_bulk_edit( $filters );
		
		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/bulk-editor-table.php';
		$html = ob_get_clean();
		
		wp_send_json_success( array(
			'html' => $html,
			'total' => $total,
			'limit' => $limit,
			'offset' => $offset,
		) );
	}

	/**
	 * AJAX handler for generating auto title.
	 *
	 * @since    1.0.0
	 */
	public function ajax_generate_auto_title() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Post ID not provided.', 'seo-simple' ) ) );
		}
		
		$post_id = intval( $_POST['post_id'] );
		$title = $this->seo_meta->get_title( $post_id );
		
		wp_send_json_success( array( 'title' => $title ) );
	}

	/**
	 * AJAX handler for generating auto description.
	 *
	 * @since    1.0.0
	 */
	public function ajax_generate_auto_description() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Post ID not provided.', 'seo-simple' ) ) );
		}
		
		$post_id = intval( $_POST['post_id'] );
		$description = $this->seo_meta->get_description( $post_id );
		
		wp_send_json_success( array( 'description' => $description ) );
	}
}