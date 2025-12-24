<?php

/**
 * Handles REST API functionality for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api
 */

/**
 * Handles REST API functionality for the SEO plugin.
 *
 * This class is responsible for registering REST API endpoints and handling
 * requests for SEO data via the WordPress REST API.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_REST_API {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The namespace for the REST API endpoints.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $namespace    The REST API namespace.
	 */
	private $namespace = 'seo-simple/v1';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	public function __construct( $seo_meta = null ) {
		$this->seo_meta = $seo_meta;
		
		// Register REST API hooks
		$this->init_hooks();
	}

	/**
	 * Initialize REST API hooks.
	 *
	 * @since    1.0.0
	 */
	private function init_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since    1.0.0
	 */
	public function register_rest_routes() {
		// Register meta endpoints
		register_rest_route(
			$this->namespace,
			'/meta/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_meta_data' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/meta/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_meta_data' ),
				'permission_callback' => array( $this, 'check_edit_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'description' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'keywords' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'focus_keyword' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'noindex' => array(
						'sanitize_callback' => function( $param ) {
							return (bool) $param;
						},
					),
					'nofollow' => array(
						'sanitize_callback' => function( $param ) {
							return (bool) $param;
						},
					),
					'canonical' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
					'social_title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'social_description' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'social_image' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
					'use_seo_data' => array(
						'sanitize_callback' => function( $param ) {
							return (bool) $param;
						},
					),
				),
			)
		);

		// Register analysis endpoints
		register_rest_route(
			$this->namespace,
			'/analysis/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_analysis_data' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'type' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/analysis/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'run_analysis' ),
				'permission_callback' => array( $this, 'check_edit_permissions' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
					'type' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'description' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'content' => array(
						'sanitize_callback' => 'wp_kses_post',
					),
					'focus_keyword' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Register batch operations endpoint
		register_rest_route(
			$this->namespace,
			'/batch',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'batch_operations' ),
				'permission_callback' => array( $this, 'check_edit_permissions' ),
				'args'                => array(
					'operation' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'post_ids' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_array( $param ) && ! empty( $param );
						},
					),
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
					'meta_data' => array(
						'sanitize_callback' => function( $param ) {
							return is_array( $param ) ? $param : array();
						},
					),
				),
			)
		);

		// Register settings endpoint
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_settings_permissions' ),
				'args'                => array(
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_settings_permissions' ),
				'args'                => array(
					'nonce' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_string( $param );
						},
					),
					'settings' => array(
						'required'          => true,
						'sanitize_callback' => function( $param ) {
							return is_array( $param ) ? $param : array();
						},
					),
				),
			)
		);
	}

	/**
	 * Check if the current user has permission to read SEO data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   bool|WP_Error                   True if permission is granted, WP_Error otherwise.
	 */
	public function check_permissions( $request ) {
		// Verify nonce for security
		$nonce = $request->get_param( 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'rest_nonce_failed',
				__( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
			);
		}
		
		if ( ! current_user_can( 'read_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to read SEO data for this post.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		return true;
	}

	/**
	 * Check if the current user has permission to edit SEO data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   bool|WP_Error                   True if permission is granted, WP_Error otherwise.
	 */
	public function check_edit_permissions( $request ) {
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
			);
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to edit SEO data for this post.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		return true;
	}

	/**
	 * Check if the current user has permission to manage settings.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   bool|WP_Error                   True if permission is granted, WP_Error otherwise.
	 */
	public function check_settings_permissions( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to manage SEO settings.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		return true;
	}

	/**
	 * Get SEO meta data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function get_meta_data( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$meta_data = $this->seo_meta->get_all_meta( $post_id );
		
		// Add computed values
		$meta_data['title_preview'] = $this->seo_meta->get_title( $post_id );
		$meta_data['description_preview'] = $this->seo_meta->get_description( $post_id );
		$meta_data['social_title_preview'] = $this->seo_meta->get_social_title( $post_id );
		$meta_data['social_description_preview'] = $this->seo_meta->get_social_description( $post_id );
		$meta_data['social_image_preview'] = $this->seo_meta->get_social_image( $post_id );
		$meta_data['permalink'] = get_permalink( $post_id );
		
		// Add WooCommerce specific data if applicable
		if ( get_post_type( $post_id ) === 'product' && class_exists( 'WooCommerce' ) ) {
			$meta_data['product_title'] = get_post_meta( $post_id, '_seo_simple_product_title', true );
			$meta_data['product_description'] = get_post_meta( $post_id, '_seo_simple_product_description', true );
		}
		
		return rest_ensure_response( $meta_data );
	}

	/**
	 * Update SEO meta data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function update_meta_data( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		// Get all meta fields from the request
		$meta_fields = array(
			'title', 'description', 'keywords', 'focus_keyword',
			'noindex', 'nofollow', 'canonical',
			'social_title', 'social_description', 'social_image', 'use_seo_data'
		);
		
		$updated_fields = array();
		
		foreach ( $meta_fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$value = $request->get_param( $field );
				
				// Update the meta field
				$result = update_post_meta( $post_id, '_seo_simple_' . $field, $value );
				
				if ( $result ) {
					$updated_fields[] = $field;
				}
			}
		}
		
		// Handle social_image_id separately if social_image is provided
		if ( $request->has_param( 'social_image' ) ) {
			$social_image = $request->get_param( 'social_image' );
			$image_id = attachment_url_to_postid( $social_image );
			
			if ( $image_id ) {
				update_post_meta( $post_id, '_seo_simple_social_image_id', $image_id );
				$updated_fields[] = 'social_image_id';
			}
		}
		
		// Handle WooCommerce specific fields
		if ( get_post_type( $post_id ) === 'product' && class_exists( 'WooCommerce' ) ) {
			if ( $request->has_param( 'product_title' ) ) {
				$value = $request->get_param( 'product_title' );
				$result = update_post_meta( $post_id, '_seo_simple_product_title', $value );
				
				if ( $result ) {
					$updated_fields[] = 'product_title';
				}
			}
			
			if ( $request->has_param( 'product_description' ) ) {
				$value = $request->get_param( 'product_description' );
				$result = update_post_meta( $post_id, '_seo_simple_product_description', $value );
				
				if ( $result ) {
					$updated_fields[] = 'product_description';
				}
			}
		}
		
		// Apply developer hook
		do_action( 'seo_plugin_save_meta', $post_id, $request->get_params() );
		
		// Return updated meta data
		return $this->get_meta_data( $request );
	}

	/**
	 * Get analysis data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function get_analysis_data( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		$type = $request->get_param( 'type' );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$data = array();
		
		// Get readability analysis
		if ( ! $type || $type === 'readability' ) {
			$data['readability'] = $this->seo_meta->get_readability_scores( $post_id );
			$data['readability']['overall'] = $this->seo_meta->get_overall_readability_score( $post_id );
		}
		
		// Get SEO analysis
		if ( ! $type || $type === 'seo' ) {
			$data['seo'] = $this->seo_meta->get_seo_analysis( $post_id );
			$data['seo']['overall'] = $this->seo_meta->get_overall_seo_score( $post_id );
		}
		
		return rest_ensure_response( $data );
	}

	/**
	 * Run analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function run_analysis( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		$type = $request->get_param( 'type' );
		
		if ( ! class_exists( 'Seo_Simple_Analysis' ) ) {
			return new WP_Error(
				'rest_analysis_class_missing',
				__( 'Analysis class is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$analysis = new Seo_Simple_Analysis();
		$results = array();
		
		// Run readability analysis
		if ( $type === 'readability' ) {
			$content = $request->get_param( 'content' );
			if ( empty( $content ) ) {
				$post = get_post( $post_id );
				$content = $post->post_content;
			}
			
			$results = $analysis->analyze_content( $content );
			
			// Save results
			if ( $this->seo_meta ) {
				$this->seo_meta->save_readability_scores( $post_id, $results );
				$this->seo_meta->cache_analysis_results( $post_id );
			}
		}
		
		// Run SEO analysis
		elseif ( $type === 'seo' ) {
			$title = $request->get_param( 'title' );
			$description = $request->get_param( 'description' );
			$content = $request->get_param( 'content' );
			$focus_keyword = $request->get_param( 'focus_keyword' );
			
			if ( empty( $content ) ) {
				$post = get_post( $post_id );
				$content = $post->post_content;
			}
			
			$results = $analysis->analyze_seo( $post_id, $title, $description, $content, $focus_keyword );
			
			// Save results
			if ( $this->seo_meta ) {
				$this->seo_meta->save_seo_analysis( $post_id, $results );
				$this->seo_meta->cache_seo_analysis_results( $post_id );
				$this->seo_meta->save_focus_keyword( $post_id, $focus_keyword );
			}
		}
		
		// Apply developer hook
		$results = apply_filters( 'seo_plugin_calculate_score', $results, $post_id, $request->get_params() );
		
		return rest_ensure_response( $results );
	}

	/**
	 * Perform batch operations on multiple posts.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function batch_operations( $request ) {
		$operation = $request->get_param( 'operation' );
		$post_ids = $request->get_param( 'post_ids' );
		$meta_data = $request->get_param( 'meta_data' );
		
		$results = array(
			'success' => array(),
			'failed' => array(),
		);
		
		foreach ( $post_ids as $post_id ) {
			$post_id = intval( $post_id );
			
			// Check permissions
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				$results['failed'][] = array(
					'post_id' => $post_id,
					'reason' => __( 'Permission denied', 'seo-simple' ),
				);
				continue;
			}
			
			// Perform operation
			$success = false;
			
			switch ( $operation ) {
				case 'update_meta':
					$success = $this->batch_update_meta( $post_id, $meta_data );
					break;
				
				case 'clear_analysis':
					$success = $this->batch_clear_analysis( $post_id );
					break;
				
				case 'calculate_score':
					$success = $this->batch_calculate_score( $post_id );
					break;
				
				default:
					$results['failed'][] = array(
						'post_id' => $post_id,
						'reason' => __( 'Invalid operation', 'seo-simple' ),
					);
					continue 2;
			}
			
			if ( $success ) {
				$results['success'][] = $post_id;
			} else {
				$results['failed'][] = array(
					'post_id' => $post_id,
					'reason' => __( 'Operation failed', 'seo-simple' ),
				);
			}
		}
		
		return rest_ensure_response( $results );
	}

	/**
	 * Batch update meta data for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $meta_data  The meta data to update.
	 * @return   bool                  True on success, false on failure.
	 */
	private function batch_update_meta( $post_id, $meta_data ) {
		if ( empty( $meta_data ) || ! is_array( $meta_data ) ) {
			return false;
		}
		
		$success = true;
		
		foreach ( $meta_data as $key => $value ) {
			$result = update_post_meta( $post_id, '_seo_simple_' . $key, $value );
			
			if ( ! $result ) {
				$success = false;
			}
		}
		
		return $success;
	}

	/**
	 * Batch clear analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function batch_clear_analysis( $post_id ) {
		if ( ! $this->seo_meta ) {
			return false;
		}
		
		$readability_result = $this->seo_meta->clear_readability_scores( $post_id );
		$seo_result = $this->seo_meta->clear_seo_analysis( $post_id );
		
		return $readability_result || $seo_result;
	}

	/**
	 * Batch calculate score for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	private function batch_calculate_score( $post_id ) {
		if ( ! class_exists( 'Seo_Simple_Analysis' ) || ! $this->seo_meta ) {
			return false;
		}
		
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
		
		$analysis = new Seo_Simple_Analysis();
		
		// Run readability analysis
		$readability_results = $analysis->analyze_content( $post->post_content );
		$this->seo_meta->save_readability_scores( $post_id, $readability_results );
		
		// Run SEO analysis
		$title = $this->seo_meta->get_title( $post_id );
		$description = $this->seo_meta->get_description( $post_id );
		$focus_keyword = $this->seo_meta->get_focus_keyword( $post_id );
		
		$seo_results = $analysis->analyze_seo( $post_id, $title, $description, $post->post_content, $focus_keyword );
		$this->seo_meta->save_seo_analysis( $post_id, $seo_results );
		
		// Cache results
		$this->seo_meta->cache_analysis_results( $post_id );
		$this->seo_meta->cache_seo_analysis_results( $post_id );
		
		return true;
	}

	/**
	 * Get SEO settings.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function get_settings( $request ) {
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$settings = $this->seo_meta->get_global_settings();
		
		return rest_ensure_response( $settings );
	}

	/**
	 * Update SEO settings.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    The REST request object.
	 * @return   WP_REST_Response|WP_Error      The REST response object or WP_Error.
	 */
	public function update_settings( $request ) {
		$settings = $request->get_param( 'settings' );
		
		if ( empty( $settings ) || ! is_array( $settings ) ) {
			return new WP_Error(
				'rest_invalid_settings',
				__( 'Invalid settings data.', 'seo-simple' ),
				array( 'status' => 400 )
			);
		}
		
		// Get existing settings
		$existing_settings = get_option( 'seo_simple_settings', array() );
		
		// Merge with new settings
		$updated_settings = array_merge( $existing_settings, $settings );
		
		// Update settings
		$result = update_option( 'seo_simple_settings', $updated_settings );
		
		if ( ! $result ) {
			return new WP_Error(
				'rest_settings_update_failed',
				__( 'Failed to update settings.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		return rest_ensure_response( $updated_settings );
	}
}