<?php

/**
 * Dashboard functionality for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Dashboard functionality for SEO Simple plugin.
 *
 * This class handles all dashboard functionality including adding widgets,
 * retrieving statistics, and identifying posts needing improvement.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Dashboard {

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
	 * Register the dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget(
				'seo_simple_dashboard_widget',
				__( 'SEO Simple Overview', 'seo-simple' ),
				array( $this, 'render_dashboard_widget' )
			);
		}
	}

	/**
	 * Render the dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/dashboard-widget.php';
	}

	/**
	 * Get SEO statistics for the dashboard.
	 *
	 * @since    1.0.0
	 * @return   array    SEO statistics.
	 */
	public function get_seo_statistics() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		return $cache_manager->remember( 'seo_simple_dashboard_stats', function() {
			$stats = array(
				'total_posts' => 0,
				'indexed_pages' => 0,
				'missing_title' => 0,
				'missing_description' => 0,
				'posts_needing_improvement' => 0,
				'good_seo_score' => 0,
			);

			// Get public post types with caching
			$post_types = wp_cache_get( 'seo_simple_public_post_types', 'seo_simple' );
			if ( $post_types === false ) {
				$post_types = get_post_types( array( 'public' => true ), 'names' );
				unset( $post_types['attachment'] ); // Exclude attachments
				wp_cache_set( 'seo_simple_public_post_types', $post_types, 'seo_simple', 3600 );
			}

			// Process post types in batches to reduce memory usage
			foreach ( $post_types as $post_type ) {
				// Use more efficient query with pagination
				$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => 100, // Process in batches
					'fields' => 'ids',
					'no_found_rows' => true, // More efficient
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				);

				$offset = 0;
				do {
					$args['offset'] = $offset;
					$post_ids = get_posts( $args );
					$stats['total_posts'] += count( $post_ids );
				
				// Skip if no posts for this type
				if ( empty( $post_ids ) ) {
					continue;
				}
				
				// Get noindex posts in bulk
				$noindex_posts = $this->seo_meta->get_meta_for_posts( $post_ids, 'noindex' );
				$stats['indexed_pages'] += count( $post_ids ) - count( $noindex_posts );
				
				// Get missing titles in bulk
				$title_posts = $this->seo_meta->get_meta_for_posts( $post_ids, 'title' );
				$stats['missing_title'] += count( $post_ids ) - count( array_filter( $title_posts ) );
				
				// Get missing descriptions in bulk
				$description_posts = $this->seo_meta->get_meta_for_posts( $post_ids, 'description' );
				$stats['missing_description'] += count( $post_ids ) - count( array_filter( $description_posts ) );
				
				// Get SEO scores in batches to avoid memory issues
				$batch_size = 100;
				$post_batches = array_chunk( $post_ids, $batch_size );
				
				foreach ( $post_batches as $batch ) {
					foreach ( $batch as $post_id ) {
						// Check SEO score
						$seo_score = $this->seo_meta->get_overall_seo_score( $post_id );
						if ( $seo_score['score'] >= 70 ) {
							$stats['good_seo_score']++;
						} elseif ( $seo_score['score'] < 50 ) {
							$stats['posts_needing_improvement']++;
						}
					}
					}
					
					$offset += 100;
				} while ( ! empty( $post_ids ) );
			}
			
			return $stats;
		}, 1800 ); // Cache for 30 minutes
	}

	/**
	 * Get posts needing SEO improvement.
	 *
	 * @since    1.0.0
	 * @param    int     $limit    Number of posts to return.
	 * @return   array             Posts needing improvement.
	 */
	public function get_posts_needing_improvement( $limit = 5 ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_posts_needing_improvement_' . $limit;
		
		return $cache_manager->remember( $cache_key, function() use ( $limit ) {
			$posts = array();
			$posts_with_scores = array();

			// Get public post types
			$post_types = get_post_types( array( 'public' => true ), 'names' );
			unset( $post_types['attachment'] ); // Exclude attachments

			foreach ( $post_types as $post_type ) {
				$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids', // Only get IDs to reduce memory usage
				);

				$post_ids = get_posts( $args );
				
				// Skip if no posts for this type
				if ( empty( $post_ids ) ) {
					continue;
				}
				
				// Get noindex posts in bulk
				$noindex_posts = $this->seo_meta->get_meta_for_posts( $post_ids, 'noindex' );
				$indexed_posts = array_diff( $post_ids, array_keys( $noindex_posts ) );
				
				// Skip if no indexed posts
				if ( empty( $indexed_posts ) ) {
					continue;
				}
				
				// Get meta data in bulk
				$title_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'title' );
				$description_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'description' );
				$keyword_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'focus_keyword' );
				
				// Get basic post data in bulk
				$post_data = array();
				foreach ( $indexed_posts as $post_id ) {
					$post_data[$post_id] = array(
						'title' => get_the_title( $post_id ),
						'edit_link' => get_edit_post_link( $post_id ),
					);
				}
				
				// Process posts in batches to avoid memory issues
				$batch_size = 50;
				$post_batches = array_chunk( $indexed_posts, $batch_size );
				
				foreach ( $post_batches as $batch ) {
					foreach ( $batch as $post_id ) {
						// Calculate issues
						$issues = 0;
						$title = isset( $title_posts[$post_id] ) ? $title_posts[$post_id] : '';
						$description = isset( $description_posts[$post_id] ) ? $description_posts[$post_id] : '';
						$focus_keyword = isset( $keyword_posts[$post_id] ) ? $keyword_posts[$post_id] : '';
						$seo_score = $this->seo_meta->get_overall_seo_score( $post_id );

						if ( empty( $title ) ) $issues++;
						if ( empty( $description ) ) $issues++;
						if ( empty( $focus_keyword ) ) $issues++;
						if ( $seo_score['score'] < 50 ) $issues++;

						if ( $issues > 0 ) {
							$posts_with_scores[] = array(
								'ID' => $post_id,
								'title' => $post_data[$post_id]['title'],
								'edit_link' => $post_data[$post_id]['edit_link'],
								'issues' => $issues,
								'seo_score' => $seo_score['score'],
							);
						}
					}
				}
			}

			// Sort by issues (descending) and SEO score (ascending)
			usort( $posts_with_scores, function( $a, $b ) {
				if ( $a['issues'] == $b['issues'] ) {
					return $a['seo_score'] - $b['seo_score'];
				}
				return $b['issues'] - $a['issues'];
			});

			// Return top posts
			$result = array_slice( $posts_with_scores, 0, $limit );
			
			return $result;
		}, 1800 ); // Cache for 30 minutes
	}

	/**
	 * Get total indexed pages count.
	 *
	 * @since    1.0.0
	 * @return   int     Number of indexed pages.
	 */
	public function get_indexed_pages_count() {
		$count = 0;

		// Get public post types
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		unset( $post_types['attachment'] ); // Exclude attachments

		foreach ( $post_types as $post_type ) {
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids',
			);

			$post_ids = get_posts( $args );

			foreach ( $post_ids as $post_id ) {
				if ( ! $this->seo_meta->is_noindex( $post_id ) ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Get missing meta count.
	 *
	 * @since    1.0.0
	 * @return   array    Missing meta counts.
	 */
	public function get_missing_meta_count() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		return $cache_manager->remember( 'seo_simple_missing_meta_count', function() {
			$counts = array(
				'title' => 0,
				'description' => 0,
				'focus_keyword' => 0,
			);

			// Get public post types
			$post_types = get_post_types( array( 'public' => true ), 'names' );
			unset( $post_types['attachment'] ); // Exclude attachments

			foreach ( $post_types as $post_type ) {
				$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
				);

				$post_ids = get_posts( $args );
				
				// Skip if no posts for this type
				if ( empty( $post_ids ) ) {
					continue;
				}
				
				// Get noindex posts in bulk
				$noindex_posts = $this->seo_meta->get_meta_for_posts( $post_ids, 'noindex' );
				$indexed_posts = array_diff( $post_ids, array_keys( $noindex_posts ) );
				
				// Skip if no indexed posts
				if ( empty( $indexed_posts ) ) {
					continue;
				}
				
				// Get meta data in bulk
				$title_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'title' );
				$description_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'description' );
				$keyword_posts = $this->seo_meta->get_meta_for_posts( $indexed_posts, 'focus_keyword' );
				
				// Count missing meta
				$counts['title'] += count( $indexed_posts ) - count( array_filter( $title_posts ) );
				$counts['description'] += count( $indexed_posts ) - count( array_filter( $description_posts ) );
				$counts['focus_keyword'] += count( $indexed_posts ) - count( array_filter( $keyword_posts ) );
			}
			
			return $counts;
		}, 1800 ); // Cache for 30 minutes
	}

	/**
	 * Get posts with missing SEO fields.
	 *
	 * @since    1.0.0
	 * @param    array   $filters    Optional filters.
	 * @param    int     $limit      Number of posts to return.
	 * @param    int     $offset     Offset for pagination.
	 * @return   array               Posts with missing SEO fields.
	 */
	public function get_posts_missing_seo_fields( $filters = array(), $limit = 20, $offset = 0 ) {
		$posts = array();
		$where_clauses = array();
		$join_clauses = array();

		global $wpdb;

		// Build base query
		$query = "SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_status
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

		// Add order and limit
		$query .= " ORDER BY p.post_modified DESC LIMIT %d OFFSET %d";

		$results = $wpdb->get_results( $wpdb->prepare( $query, $limit, $offset ) );

		foreach ( $results as $result ) {
			$posts[] = array(
				'ID' => $result->ID,
				'title' => $result->post_title,
				'type' => $result->post_type,
				'edit_link' => get_edit_post_link( $result->ID ),
				'seo_title' => $this->seo_meta->get_meta( $result->ID, 'title' ),
				'seo_description' => $this->seo_meta->get_meta( $result->ID, 'description' ),
				'focus_keyword' => $this->seo_meta->get_focus_keyword( $result->ID ),
			);
		}

		return $posts;
	}

	/**
	 * Count posts with missing SEO fields.
	 *
	 * @since    1.0.0
	 * @param    array   $filters    Optional filters.
	 * @return   int                 Total count.
	 */
	public function count_posts_missing_seo_fields( $filters = array() ) {
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

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Clear dashboard cache.
	 *
	 * @since    1.0.0
	 * @return   bool    True on success, false on failure.
	 */
	public function clear_dashboard_cache() {
		// Clear all dashboard related caches
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_manager->delete( 'seo_simple_dashboard_stats' );
		$cache_manager->delete( 'seo_simple_missing_meta_count' );
		
		// Clear posts needing improvement caches for different limits
		for ( $i = 5; $i <= 50; $i += 5 ) {
			$cache_manager->delete( 'seo_simple_posts_needing_improvement_' . $i );
		}
		
		return true;
	}

	/**
	 * Handle AJAX requests for dashboard data.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		add_action( 'wp_ajax_seo_simple_get_dashboard_stats', array( $this, 'ajax_get_dashboard_stats' ) );
		add_action( 'wp_ajax_seo_simple_refresh_dashboard_widget', array( $this, 'ajax_refresh_dashboard_widget' ) );
	}

	/**
	 * AJAX handler for getting dashboard statistics.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_dashboard_stats() {
		check_ajax_referer( 'seo_simple_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		$stats = $this->get_seo_statistics();
		$posts_needing_improvement = $this->get_posts_needing_improvement( 5 );
		
		wp_send_json_success( array(
			'stats' => $stats,
			'posts_needing_improvement' => $posts_needing_improvement,
		) );
	}

	/**
	 * AJAX handler for refreshing dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function ajax_refresh_dashboard_widget() {
		check_ajax_referer( 'seo_simple_admin_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		ob_start();
		$this->render_dashboard_widget();
		$html = ob_get_clean();
		
		wp_send_json_success( array( 'html' => $html ) );
	}
}