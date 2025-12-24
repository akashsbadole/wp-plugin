<?php

/**
 * Handles meta-specific REST API endpoints for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api/endpoints
 */

/**
 * Handles meta-specific REST API endpoints for the SEO plugin.
 *
 * This class is responsible for handling meta-specific REST API endpoints
 * for reading and writing SEO meta data.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api/endpoints
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_REST_Meta_Controller extends WP_REST_Controller {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	public function __construct( $seo_meta = null ) {
		$this->seo_meta = $seo_meta;
		$this->namespace = 'seo-simple/v1';
		$this->rest_base = 'meta';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		// GET /seo-simple/v1/meta/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'context' => array(
							'description' => __( 'Scope under which the request is made; determines fields present in response.', 'seo-simple' ),
							'type'        => 'string',
							'enum'        => array( 'view', 'edit' ),
							'default'     => 'view',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// GET /seo-simple/v1/meta
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// POST /seo-simple/v1/meta/batch
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'batch_update_items' ),
					'permission_callback' => array( $this, 'batch_update_items_permissions_check' ),
					'args'                => array(
						'items' => array(
							'description' => __( 'Array of meta items to update.', 'seo-simple' ),
							'type'        => 'array',
							'required'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id' => array(
										'type'     => 'integer',
										'required' => true,
									),
									'meta' => array(
										'type'       => 'object',
										'required'   => true,
									),
								),
							),
						),
					),
				),
			)
		);

		// DELETE /seo-simple/v1/meta/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				'args' => array(
					'id' => array(
						'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check if a given request has access to read meta data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view SEO meta data.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to read a specific meta data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has read access, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
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
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view SEO meta data for this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to update a specific meta data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has update access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
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
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit SEO meta data for this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to delete a specific meta data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has delete access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
			);
		}
		
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete SEO meta data for this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to batch update meta data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has update access, WP_Error object otherwise.
	 */
	public function batch_update_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to batch update SEO meta data.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Get a collection of meta items.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$args = array();
		
		// Get query parameters
		$post_type = $request->get_param( 'post_type' );
		$post_status = $request->get_param( 'post_status' );
		$per_page = $request->get_param( 'per_page' );
		$page = $request->get_param( 'page' );
		$search = $request->get_param( 'search' );
		
		// Build query args
		if ( ! empty( $post_type ) ) {
			$args['post_type'] = $post_type;
		}
		
		if ( ! empty( $post_status ) ) {
			$args['post_status'] = $post_status;
		}
		
		if ( ! empty( $per_page ) ) {
			$args['posts_per_page'] = $per_page;
		}
		
		if ( ! empty( $page ) ) {
			$args['paged'] = $page;
		}
		
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}
		
		// Get posts
		$posts_query = new WP_Query( $args );
		$posts = $posts_query->posts;
		
		// Prepare response data
		$data = array();
		
		foreach ( $posts as $post ) {
			$item = $this->prepare_item_for_response( $post, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}
		
		// Get total posts count
		$total_posts = $posts_query->found_posts;
		$max_pages = ceil( $total_posts / (int) $per_page );
		
		// Prepare response
		$response = rest_ensure_response( $data );
		
		// Add pagination headers
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );
		
		// Calculate base URL for pagination
		$base = add_query_arg( $request->get_query_params(), rest_url( $this->namespace . '/' . $this->rest_base ) );
		
		// Add pagination links
		if ( $page > 1 ) {
			$prev_page = $page - 1;
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		
		if ( $page < $max_pages ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}
		
		return $response;
	}

	/**
	 * Get one meta item from the collection.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
			);
		}
		
		$data = $this->prepare_item_for_response( $post, $request );
		return rest_ensure_response( $data );
	}

	/**
	 * Update one meta item from the collection.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
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
				
				// Sanitize based on field type
				switch ( $field ) {
					case 'title':
					case 'keywords':
					case 'focus_keyword':
					case 'social_title':
						$value = sanitize_text_field( $value );
						break;
					
					case 'description':
					case 'social_description':
						$value = sanitize_textarea_field( $value );
						break;
					
					case 'canonical':
					case 'social_image':
						$value = esc_url_raw( $value );
						break;
					
					case 'noindex':
					case 'nofollow':
					case 'use_seo_data':
						$value = (bool) $value;
						break;
				}
				
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
				$value = sanitize_text_field( $request->get_param( 'product_title' ) );
				$result = update_post_meta( $post_id, '_seo_simple_product_title', $value );
				
				if ( $result ) {
					$updated_fields[] = 'product_title';
				}
			}
			
			if ( $request->has_param( 'product_description' ) ) {
				$value = sanitize_textarea_field( $request->get_param( 'product_description' ) );
				$result = update_post_meta( $post_id, '_seo_simple_product_description', $value );
				
				if ( $result ) {
					$updated_fields[] = 'product_description';
				}
			}
		}
		
		// Apply developer hook
		do_action( 'seo_plugin_save_meta', $post_id, $request->get_params() );
		
		// Return updated meta data
		return $this->get_item( $request );
	}

	/**
	 * Delete one meta item from the collection.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$post_id = $request->get_param( 'id' );
		$post = get_post( $post_id );
		
		if ( ! $post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'seo-simple' ),
				array( 'status' => 404 )
			);
		}
		
		// Get all SEO meta fields
		$meta_fields = array(
			'title', 'description', 'keywords', 'focus_keyword',
			'noindex', 'nofollow', 'canonical',
			'social_title', 'social_description', 'social_image', 'social_image_id', 'use_seo_data',
			'readability_scores', 'seo_analysis', 'content_hash', 'seo_content_hash'
		);
		
		$deleted_fields = array();
		
		foreach ( $meta_fields as $field ) {
			$result = delete_post_meta( $post_id, '_seo_simple_' . $field );
			
			if ( $result ) {
				$deleted_fields[] = $field;
			}
		}
		
		// Handle WooCommerce specific fields
		if ( get_post_type( $post_id ) === 'product' && class_exists( 'WooCommerce' ) ) {
			$result = delete_post_meta( $post_id, '_seo_simple_product_title' );
			if ( $result ) {
				$deleted_fields[] = 'product_title';
			}
			
			$result = delete_post_meta( $post_id, '_seo_simple_product_description' );
			if ( $result ) {
				$deleted_fields[] = 'product_description';
			}
		}
		
		$response = array(
			'deleted' => true,
			'fields'  => $deleted_fields,
		);
		
		return rest_ensure_response( $response );
	}

	/**
	 * Batch update multiple meta items.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function batch_update_items( $request ) {
		$items = $request->get_param( 'items' );
		$results = array(
			'success' => array(),
			'failed'  => array(),
		);
		
		foreach ( $items as $item ) {
			$post_id = isset( $item['id'] ) ? intval( $item['id'] ) : 0;
			$meta_data = isset( $item['meta'] ) ? $item['meta'] : array();
			
			if ( empty( $post_id ) || empty( $meta_data ) ) {
				$results['failed'][] = array(
					'item'  => $item,
					'reason' => __( 'Invalid item data', 'seo-simple' ),
				);
				continue;
			}
			
			// Check permissions
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				$results['failed'][] = array(
					'item'  => $item,
					'reason' => __( 'Permission denied', 'seo-simple' ),
				);
				continue;
			}
			
			// Create a mock request for update_item
			$mock_request = new WP_REST_Request( 'POST', '/' );
			$mock_request->set_param( 'id', $post_id );
			
			foreach ( $meta_data as $key => $value ) {
				$mock_request->set_param( $key, $value );
			}
			
			// Try to update the item
			try {
				$result = $this->update_item( $mock_request );
				
				if ( ! is_wp_error( $result ) ) {
					$results['success'][] = array(
						'id'    => $post_id,
						'data'  => $result->get_data(),
					);
				} else {
					$results['failed'][] = array(
						'item'  => $item,
						'reason' => $result->get_error_message(),
					);
				}
			} catch ( Exception $e ) {
				$results['failed'][] = array(
					'item'  => $item,
					'reason' => $e->getMessage(),
				);
			}
		}
		
		return rest_ensure_response( $results );
	}

	/**
	 * Prepares a single post output for response.
	 *
	 * @since    1.0.0
	 * @param    WP_Post           $post        Post object.
	 * @param    WP_REST_Request   $request     Request object.
	 * @return   WP_REST_Response               Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
		$context = $request->get_param( 'context' );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		// Get meta data
		$meta_data = $this->seo_meta->get_all_meta( $post->ID );
		
		// Add computed values
		$meta_data['title_preview'] = $this->seo_meta->get_title( $post->ID );
		$meta_data['description_preview'] = $this->seo_meta->get_description( $post->ID );
		$meta_data['social_title_preview'] = $this->seo_meta->get_social_title( $post->ID );
		$meta_data['social_description_preview'] = $this->seo_meta->get_social_description( $post->ID );
		$meta_data['social_image_preview'] = $this->seo_meta->get_social_image( $post->ID );
		$meta_data['permalink'] = get_permalink( $post->ID );
		
		// Add post info
		$meta_data['post_info'] = array(
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'slug'       => $post->post_name,
			'status'     => $post->post_status,
			'type'       => $post->post_type,
			'modified'   => $post->post_modified_gmt,
		);
		
		// Add WooCommerce specific data if applicable
		if ( $post->post_type === 'product' && class_exists( 'WooCommerce' ) ) {
			$meta_data['product_title'] = get_post_meta( $post->ID, '_seo_simple_product_title', true );
			$meta_data['product_description'] = get_post_meta( $post->ID, '_seo_simple_product_description', true );
		}
		
		// Add sensitive data only if user has edit permissions
		if ( current_user_can( 'edit_post', $post->ID ) && $context === 'edit' ) {
			$meta_data['technical_seo'] = $this->seo_meta->get_technical_seo_meta( $post->ID );
		}
		
		// Wrap the data in a response object
		$response = rest_ensure_response( $meta_data );
		
		// Add links
		$response->add_links( $this->prepare_links( $post ) );
		
		/**
		 * Filter the meta data for a response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'seo_simple_rest_prepare_meta', $response, $post, $request );
	}

	/**
	 * Prepares links for the request.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    Post object.
	 * @return   array               Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $post->ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			),
			'about' => array(
				'href' => rest_url( sprintf( 'wp/v2/posts/%d', $post->ID ) ),
			),
		);
		
		return $links;
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @since    1.0.0
	 * @return   array    Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param(),
			'post_type' => array(
				'description' => __( 'Limit results to posts assigned to specific post types.', 'seo-simple' ),
				'type'        => 'string',
				'default'     => 'post',
			),
			'post_status' => array(
				'description' => __( 'Limit results to posts with specific status.', 'seo-simple' ),
				'type'        => 'string',
				'default'     => 'publish',
			),
			'per_page' => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'seo-simple' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'page' => array(
				'description' => __( 'Current page of the collection.', 'seo-simple' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'search' => array(
				'description' => __( 'Limit results to those matching a string.', 'seo-simple' ),
				'type'        => 'string',
			),
		);
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since    1.0.0
	 * @return   array    Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}
		
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'seo-meta',
			'type'       => 'object',
			'properties' => array(
				'title' => array(
					'description' => __( 'SEO title for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'description' => array(
					'description' => __( 'SEO description for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'keywords' => array(
					'description' => __( 'SEO keywords for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'focus_keyword' => array(
					'description' => __( 'Focus keyword for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'noindex' => array(
					'description' => __( 'Whether to prevent search engines from indexing this post.', 'seo-simple' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'nofollow' => array(
					'description' => __( 'Whether to prevent search engines from following links on this post.', 'seo-simple' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'canonical' => array(
					'description' => __( 'Canonical URL for the post.', 'seo-simple' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
				),
				'social_title' => array(
					'description' => __( 'Social media title for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'social_description' => array(
					'description' => __( 'Social media description for the post.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'social_image' => array(
					'description' => __( 'Social media image URL for the post.', 'seo-simple' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
				),
				'use_seo_data' => array(
					'description' => __( 'Whether to use SEO data for social media.', 'seo-simple' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'title_preview' => array(
					'description' => __( 'Generated SEO title preview.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description_preview' => array(
					'description' => __( 'Generated SEO description preview.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'social_title_preview' => array(
					'description' => __( 'Generated social title preview.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'social_description_preview' => array(
					'description' => __( 'Generated social description preview.', 'seo-simple' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'social_image_preview' => array(
					'description' => __( 'Generated social image preview.', 'seo-simple' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'permalink' => array(
					'description' => __( 'Post permalink.', 'seo-simple' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'post_info' => array(
					'description' => __( 'Basic post information.', 'seo-simple' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'id' => array(
							'description' => __( 'Post ID.', 'seo-simple' ),
							'type'        => 'integer',
						),
						'title' => array(
							'description' => __( 'Post title.', 'seo-simple' ),
							'type'        => 'string',
						),
						'slug' => array(
							'description' => __( 'Post slug.', 'seo-simple' ),
							'type'        => 'string',
						),
						'status' => array(
							'description' => __( 'Post status.', 'seo-simple' ),
							'type'        => 'string',
						),
						'type' => array(
							'description' => __( 'Post type.', 'seo-simple' ),
							'type'        => 'string',
						),
						'modified' => array(
							'description' => __( 'Post last modified date.', 'seo-simple' ),
							'type'        => 'string',
							'format'      => 'date-time',
						),
					),
				),
			),
		);
		
		$this->schema = $schema;
		
		return $this->add_additional_fields_schema( $this->schema );
	}
}