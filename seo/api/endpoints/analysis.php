<?php

/**
 * Handles analysis-specific REST API endpoints for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api/endpoints
 */

/**
 * Handles analysis-specific REST API endpoints for the SEO plugin.
 *
 * This class is responsible for handling analysis-specific REST API endpoints
 * for readability and SEO analysis.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/api/endpoints
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_REST_Analysis_Controller extends WP_REST_Controller {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The analysis handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Analysis    $analysis    The analysis handler instance.
	 */
	private $analysis;

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta         $seo_meta    The SEO meta handler instance.
	 * @param    Seo_Simple_Analysis    $analysis    The analysis handler instance.
	 */
	public function __construct( $seo_meta = null, $analysis = null ) {
		$this->seo_meta = $seo_meta;
		$this->analysis = $analysis;
		$this->namespace = 'seo-simple/v1';
		$this->rest_base = 'analysis';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		// GET /seo-simple/v1/analysis/{id}
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
						'type' => array(
							'description' => __( 'Type of analysis to retrieve.', 'seo-simple' ),
							'type'        => 'string',
							'enum'        => array( 'readability', 'seo', 'all' ),
							'default'     => 'all',
						),
						'refresh' => array(
							'description' => __( 'Whether to refresh the analysis.', 'seo-simple' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// POST /seo-simple/v1/analysis/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'type' => array(
							'description' => __( 'Type of analysis to perform.', 'seo-simple' ),
							'type'        => 'string',
							'enum'        => array( 'readability', 'seo', 'all' ),
							'required'    => true,
						),
						'title' => array(
							'description' => __( 'Post title for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'description' => array(
							'description' => __( 'Post description for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'content' => array(
							'description' => __( 'Post content for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'focus_keyword' => array(
							'description' => __( 'Focus keyword for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// GET /seo-simple/v1/analysis/readability/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/readability/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_readability_analysis' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'refresh' => array(
							'description' => __( 'Whether to refresh the analysis.', 'seo-simple' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
				'schema' => array( $this, 'get_readability_item_schema' ),
			)
		);

		// GET /seo-simple/v1/analysis/seo/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/seo/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_seo_analysis' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'refresh' => array(
							'description' => __( 'Whether to refresh the analysis.', 'seo-simple' ),
							'type'        => 'boolean',
							'default'     => false,
						),
					),
				),
				'schema' => array( $this, 'get_seo_item_schema' ),
			)
		);

		// POST /seo-simple/v1/analysis/readability/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/readability/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_readability_analysis' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'content' => array(
							'description' => __( 'Post content for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
					),
				),
				'schema' => array( $this, 'get_readability_item_schema' ),
			)
		);

		// POST /seo-simple/v1/analysis/seo/{id}
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/seo/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_seo_analysis' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'id' => array(
							'description' => __( 'Unique identifier for the post.', 'seo-simple' ),
							'type'        => 'integer',
							'required'    => true,
						),
						'title' => array(
							'description' => __( 'Post title for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'description' => array(
							'description' => __( 'Post description for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'content' => array(
							'description' => __( 'Post content for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
						'focus_keyword' => array(
							'description' => __( 'Focus keyword for analysis.', 'seo-simple' ),
							'type'        => 'string',
						),
					),
				),
				'schema' => array( $this, 'get_seo_item_schema' ),
			)
		);

		// DELETE /seo-simple/v1/analysis/{id}
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
					'args'                => array(
						'type' => array(
							'description' => __( 'Type of analysis to delete.', 'seo-simple' ),
							'type'        => 'string',
							'enum'        => array( 'readability', 'seo', 'all' ),
							'default'     => 'all',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// POST /seo-simple/v1/analysis/batch
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'batch_analyze_items' ),
					'permission_callback' => array( $this, 'batch_analyze_items_permissions_check' ),
					'args'                => array(
						'items' => array(
							'description' => __( 'Array of items to analyze.', 'seo-simple' ),
							'type'        => 'array',
							'required'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id' => array(
										'type'     => 'integer',
										'required' => true,
									),
									'type' => array(
										'type'     => 'string',
										'enum'     => array( 'readability', 'seo', 'all' ),
										'required' => true,
									),
								),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to read analysis data.
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
				__( 'Sorry, you are not allowed to view analysis data for this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to create analysis data.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has create access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
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
				__( 'Sorry, you are not allowed to analyze this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to delete analysis data.
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
		
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete analysis data for this post.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Check if a given request has access to batch analyze items.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   bool|WP_Error                   True if the request has batch analyze access, WP_Error object otherwise.
	 */
	public function batch_analyze_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to batch analyze posts.', 'seo-simple' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		return true;
	}

	/**
	 * Get analysis data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		$type = $request->get_param( 'type' );
		$refresh = $request->get_param( 'refresh' );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$data = array();
		
		// Get readability analysis
		if ( $type === 'readability' || $type === 'all' ) {
			$use_cache = ! $refresh;
			$data['readability'] = $this->seo_meta->get_readability_scores( $post_id, $use_cache );
			$data['readability']['overall'] = $this->seo_meta->get_overall_readability_score( $post_id );
		}
		
		// Get SEO analysis
		if ( $type === 'seo' || $type === 'all' ) {
			$use_cache = ! $refresh;
			$data['seo'] = $this->seo_meta->get_seo_analysis( $post_id, $use_cache );
			$data['seo']['overall'] = $this->seo_meta->get_overall_seo_score( $post_id );
		}
		
		// Prepare response
		$response = rest_ensure_response( $data );
		
		// Add links
		$response->add_links( $this->prepare_links( $post_id ) );
		
		return $response;
	}

	/**
	 * Create analysis data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
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
		if ( $type === 'readability' || $type === 'all' ) {
			$content = $request->get_param( 'content' );
			if ( empty( $content ) ) {
				$post = get_post( $post_id );
				$content = $post->post_content;
			}
			
			$results['readability'] = $analysis->analyze_content( $content );
			
			// Save results
			if ( $this->seo_meta ) {
				$this->seo_meta->save_readability_scores( $post_id, $results['readability'] );
				$this->seo_meta->cache_analysis_results( $post_id );
			}
		}
		
		// Run SEO analysis
		if ( $type === 'seo' || $type === 'all' ) {
			$title = $request->get_param( 'title' );
			$description = $request->get_param( 'description' );
			$content = $request->get_param( 'content' );
			$focus_keyword = $request->get_param( 'focus_keyword' );
			
			if ( empty( $content ) ) {
				$post = get_post( $post_id );
				$content = $post->post_content;
			}
			
			$results['seo'] = $analysis->analyze_seo( $post_id, $title, $description, $content, $focus_keyword );
			
			// Save results
			if ( $this->seo_meta ) {
				$this->seo_meta->save_seo_analysis( $post_id, $results['seo'] );
				$this->seo_meta->cache_seo_analysis_results( $post_id );
				$this->seo_meta->save_focus_keyword( $post_id, $focus_keyword );
			}
		}
		
		// Apply developer hook
		$results = apply_filters( 'seo_plugin_calculate_score', $results, $post_id, $request->get_params() );
		
		// Prepare response
		$response = rest_ensure_response( $results );
		
		// Add links
		$response->add_links( $this->prepare_links( $post_id ) );
		
		return $response;
	}

	/**
	 * Get readability analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function get_readability_analysis( $request ) {
		$request->set_param( 'type', 'readability' );
		return $this->get_item( $request );
	}

	/**
	 * Get SEO analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function get_seo_analysis( $request ) {
		$request->set_param( 'type', 'seo' );
		return $this->get_item( $request );
	}

	/**
	 * Create readability analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function create_readability_analysis( $request ) {
		$request->set_param( 'type', 'readability' );
		return $this->create_item( $request );
	}

	/**
	 * Create SEO analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function create_seo_analysis( $request ) {
		$request->set_param( 'type', 'seo' );
		return $this->create_item( $request );
	}

	/**
	 * Delete analysis data for a post.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$post_id = intval( $request->get_param( 'id' ) );
		$type = $request->get_param( 'type' );
		
		if ( ! $this->seo_meta ) {
			return new WP_Error(
				'rest_meta_handler_missing',
				__( 'SEO meta handler is not available.', 'seo-simple' ),
				array( 'status' => 500 )
			);
		}
		
		$deleted = array();
		
		// Delete readability analysis
		if ( $type === 'readability' || $type === 'all' ) {
			$result = $this->seo_meta->clear_readability_scores( $post_id );
			
			if ( $result ) {
				$deleted[] = 'readability';
			}
		}
		
		// Delete SEO analysis
		if ( $type === 'seo' || $type === 'all' ) {
			$result = $this->seo_meta->clear_seo_analysis( $post_id );
			
			if ( $result ) {
				$deleted[] = 'seo';
			}
		}
		
		$response = array(
			'deleted' => true,
			'types'   => $deleted,
		);
		
		return rest_ensure_response( $response );
	}

	/**
	 * Batch analyze multiple items.
	 *
	 * @since    1.0.0
	 * @param    WP_REST_Request    $request    Full data about the request.
	 * @return   WP_REST_Response|WP_Error      Response object on success, or WP_Error object on failure.
	 */
	public function batch_analyze_items( $request ) {
		$items = $request->get_param( 'items' );
		$results = array(
			'success' => array(),
			'failed'  => array(),
		);
		
		foreach ( $items as $item ) {
			$post_id = isset( $item['id'] ) ? intval( $item['id'] ) : 0;
			$type = isset( $item['type'] ) ? $item['type'] : 'all';
			
			if ( empty( $post_id ) ) {
				$results['failed'][] = array(
					'item'  => $item,
					'reason' => __( 'Invalid post ID', 'seo-simple' ),
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
			
			// Create a mock request for create_item
			$mock_request = new WP_REST_Request( 'POST', '/' );
			$mock_request->set_param( 'id', $post_id );
			$mock_request->set_param( 'type', $type );
			
			// Try to analyze the item
			try {
				$result = $this->create_item( $mock_request );
				
				if ( ! is_wp_error( $result ) ) {
					$results['success'][] = array(
						'id'    => $post_id,
						'type'  => $type,
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
	 * Prepares links for the request.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    Post ID.
	 * @return   array                 Links for the given post.
	 */
	protected function prepare_links( $post_id ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $post_id ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			),
			'about' => array(
				'href' => rest_url( sprintf( 'wp/v2/posts/%d', $post_id ) ),
			),
			'meta' => array(
				'href' => rest_url( sprintf( 'seo-simple/v1/meta/%d', $post_id ) ),
			),
		);
		
		return $links;
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
			'title'      => 'seo-analysis',
			'type'       => 'object',
			'properties' => array(
				'readability' => array(
					'description' => __( 'Readability analysis results.', 'seo-simple' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'overall' => array(
							'description' => __( 'Overall readability score.', 'seo-simple' ),
							'type'        => 'object',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'score' => array(
									'description' => __( 'Readability score.', 'seo-simple' ),
									'type'        => 'number',
								),
								'status' => array(
									'description' => __( 'Readability status.', 'seo-simple' ),
									'type'        => 'string',
									'enum'        => array( 'good', 'ok', 'bad' ),
								),
								'recommendation' => array(
									'description' => __( 'Readability recommendation.', 'seo-simple' ),
									'type'        => 'string',
								),
							),
						),
					),
				),
				'seo' => array(
					'description' => __( 'SEO analysis results.', 'seo-simple' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'overall' => array(
							'description' => __( 'Overall SEO score.', 'seo-simple' ),
							'type'        => 'object',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'score' => array(
									'description' => __( 'SEO score.', 'seo-simple' ),
									'type'        => 'number',
								),
								'status' => array(
									'description' => __( 'SEO status.', 'seo-simple' ),
									'type'        => 'string',
									'enum'        => array( 'good', 'ok', 'bad' ),
								),
								'recommendation' => array(
									'description' => __( 'SEO recommendation.', 'seo-simple' ),
									'type'        => 'string',
								),
							),
						),
					),
				),
			),
		);
		
		$this->schema = $schema;
		
		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Retrieves the readability item's schema, conforming to JSON Schema.
	 *
	 * @since    1.0.0
	 * @return   array    Item schema data.
	 */
	public function get_readability_item_schema() {
		$schema = $this->get_item_schema();
		
		// Only include readability properties
		$properties = array();
		if ( isset( $schema['properties']['readability'] ) ) {
			$properties = $schema['properties']['readability']['properties'];
		}
		
		$schema['properties'] = $properties;
		
		return $schema;
	}

	/**
	 * Retrieves the SEO item's schema, conforming to JSON Schema.
	 *
	 * @since    1.0.0
	 * @return   array    Item schema data.
	 */
	public function get_seo_item_schema() {
		$schema = $this->get_item_schema();
		
		// Only include SEO properties
		$properties = array();
		if ( isset( $schema['properties']['seo'] ) ) {
			$properties = $schema['properties']['seo']['properties'];
		}
		
		$schema['properties'] = $properties;
		
		return $schema;
	}
}