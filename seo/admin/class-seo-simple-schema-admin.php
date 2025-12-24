<?php

/**
 * Admin class for enhanced schema generator.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Admin class for enhanced schema generator.
 *
 * This class handles the admin interface for the enhanced schema generator,
 * including meta boxes, settings, and schema validation.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Schema_Admin {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The enhanced schema handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema_Enhanced    $schema_enhanced    The enhanced schema handler instance.
	 */
	private $schema_enhanced;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta               $seo_meta         The SEO meta handler instance.
	 * @param    Seo_Simple_Schema_Enhanced    $schema_enhanced  The enhanced schema handler instance.
	 */
	public function __construct( $seo_meta, $schema_enhanced ) {
		$this->seo_meta = $seo_meta;
		$this->schema_enhanced = $schema_enhanced;
	}

	/**
	 * Register hooks for the admin interface.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_schema_meta_boxes' ) );
		
		// Save post meta
		add_action( 'save_post', array( $this, 'save_schema_meta' ) );
		
		// Enqueue scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_seo_simple_validate_schema', array( $this, 'ajax_validate_schema' ) );
		add_action( 'wp_ajax_seo_simple_preview_schema', array( $this, 'ajax_preview_schema' ) );
		add_action( 'wp_ajax_seo_simple_get_schema_fields', array( $this, 'ajax_get_schema_fields' ) );
	}

	/**
	 * Add schema meta boxes to the post editor.
	 *
	 * @since    1.0.0
	 */
	public function add_schema_meta_boxes() {
		$post_types = get_post_types( array( 'public' => true ) );
		
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'seo-simple-schema-enhanced',
				__( 'Enhanced Schema', 'seo-simple' ),
				array( $this, 'render_schema_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the schema meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_schema_meta_box( $post ) {
		// Include the meta box template
		include_once plugin_dir_path( __FILE__ ) . 'partials/meta-box-schema-enhanced.php';
	}

	/**
	 * Save schema meta data.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    The post ID.
	 */
	public function save_schema_meta( $post_id ) {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_schema_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_schema_nonce'], 'seo_simple_schema_save' ) ) {
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Save schema type
		if ( isset( $_POST['seo_simple_schema_type'] ) ) {
			update_post_meta( $post_id, '_seo_simple_schema_type', sanitize_text_field( $_POST['seo_simple_schema_type'] ) );
		}

		// Save custom schema
		if ( isset( $_POST['seo_simple_custom_schema'] ) ) {
			$custom_schema = wp_kses_post( $_POST['seo_simple_custom_schema'] );
			update_post_meta( $post_id, '_seo_simple_custom_schema', $custom_schema );
		}

		// Save schema-specific fields
		$schema_type = isset( $_POST['seo_simple_schema_type'] ) ? sanitize_text_field( $_POST['seo_simple_schema_type'] ) : '';
		if ( ! empty( $schema_type ) ) {
			$this->save_schema_specific_fields( $post_id, $schema_type );
		}
	}

	/**
	 * Save schema-specific fields.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id      The post ID.
	 * @param    string    $schema_type  The schema type.
	 */
	private function save_schema_specific_fields( $post_id, $schema_type ) {
		// Define schema field mappings
		$field_mappings = $this->get_schema_field_mappings();

		// Check if schema type has field mappings
		if ( isset( $field_mappings[$schema_type] ) ) {
			$fields = $field_mappings[$schema_type];
			
			foreach ( $fields as $field ) {
				$meta_key = '_seo_simple_' . $field;
				
				if ( isset( $_POST[$meta_key] ) ) {
					$value = wp_kses_post( $_POST[$meta_key] );
					update_post_meta( $post_id, $meta_key, $value );
				}
			}
		}
	}

	/**
	 * Get schema field mappings.
	 *
	 * @since    1.0.0
	 * @return   array    Schema field mappings.
	 */
	private function get_schema_field_mappings() {
		return array(
			'organization' => array(
				'organization_name',
				'organization_description',
				'organization_url',
				'organization_logo',
				'organization_phone',
				'organization_email',
				'organization_address',
				'organization_city',
				'organization_state',
				'organization_postal',
				'organization_country',
				'organization_facebook',
				'organization_twitter',
				'organization_instagram',
				'organization_linkedin',
				'organization_youtube',
				'organization_pinterest',
			),
			'person' => array(
				'person_name',
				'person_title',
				'person_job_title',
				'person_email',
				'person_phone',
				'person_url',
				'person_address',
				'person_birth_date',
				'person_image',
				'person_facebook',
				'person_twitter',
				'person_instagram',
				'person_linkedin',
			),
			'localbusiness' => array(
				'business_name',
				'business_description',
				'business_phone',
				'business_email',
				'business_address',
				'business_city',
				'business_state',
				'business_postal',
				'business_country',
				'business_hours',
				'business_price_range',
				'business_payment_accepted',
				'business_image',
			),
			'restaurant' => array(
				'business_name',
				'business_description',
				'business_phone',
				'business_email',
				'business_address',
				'business_city',
				'business_state',
				'business_postal',
				'business_country',
				'business_hours',
				'business_price_range',
				'business_payment_accepted',
				'business_image',
				'restaurant_cuisine',
				'restaurant_menu_url',
				'restaurant_reservations_url',
				'restaurant_accepts_reservations',
			),
			'event' => array(
				'event_name',
				'event_description',
				'event_start_date',
				'event_end_date',
				'event_location_name',
				'event_location_address',
				'event_url',
				'event_price',
				'event_currency',
				'event_availability',
				'event_image',
			),
			'recipe' => array(
				'recipe_name',
				'recipe_description',
				'recipe_image',
				'recipe_prep_time',
				'recipe_cook_time',
				'recipe_total_time',
				'recipe_yield',
				'recipe_category',
				'recipe_cuisine',
				'recipe_keywords',
				'recipe_ingredients',
				'recipe_instructions',
				'recipe_calories',
				'recipe_fat',
			),
			'howto' => array(
				'howto_name',
				'howto_description',
				'howto_image',
				'howto_total_time',
				'howto_cost',
				'howto_currency',
				'howto_supplies',
				'howto_tools',
				'howto_steps',
			),
			'jobposting' => array(
				'job_title',
				'job_description',
				'job_date_posted',
				'job_valid_through',
				'job_employment_type',
				'job_hiring_organization',
				'job_location',
				'job_salary',
				'job_currency',
				'job_salary_unit',
			),
			'course' => array(
				'course_name',
				'course_description',
				'course_provider',
				'course_duration',
				'course_price',
				'course_currency',
				'course_availability',
				'course_image',
			),
			'book' => array(
				'book_name',
				'book_description',
				'book_author',
				'book_isbn',
				'book_publisher',
				'book_date_published',
				'book_language',
				'book_genre',
				'book_pages',
				'book_image',
			),
			'movie' => array(
				'movie_name',
				'movie_description',
				'movie_director',
				'movie_actor',
				'movie_producer',
				'movie_date_published',
				'movie_duration',
				'movie_genre',
				'movie_language',
				'movie_image',
				'movie_trailer',
			),
			'musicrecording' => array(
				'music_name',
				'music_description',
				'music_artist',
				'music_album',
				'music_genre',
				'music_date_published',
				'music_duration',
				'music_image',
				'music_preview',
			),
			'softwareapplication' => array(
				'software_name',
				'software_description',
				'software_application_category',
				'software_operating_system',
				'software_version',
				'software_price',
				'software_currency',
				'software_download_url',
				'software_image',
				'software_screenshot',
			),
			'review' => array(
				'review_item_name',
				'review_item_type',
				'review_rating',
				'review_best_rating',
				'review_worst_rating',
				'review_author',
				'review_date_published',
				'review_image',
			),
			'aggregaterating' => array(
				'rating_item_name',
				'rating_item_type',
				'rating_value',
				'rating_best',
				'rating_worst',
				'rating_count',
			),
			'videoobject' => array(
				'video_name',
				'video_description',
				'video_url',
				'video_thumbnail_url',
				'video_duration',
				'video_date_uploaded',
				'video_content_url',
				'video_embed_url',
			),
			'imageobject' => array(
				'image_url',
				'image_caption',
				'image_width',
				'image_height',
				'image_author',
				'image_date_published',
			),
			'quotation' => array(
				'quotation_text',
				'quotation_author',
				'quotation_source',
				'quotation_date_published',
			),
			'service' => array(
				'service_name',
				'service_description',
				'service_provider',
				'service_type',
				'service_area',
				'service_price',
				'service_currency',
				'service_image',
			),
			'itemlist' => array(
				'item_list_name',
				'item_list_description',
				'item_list_type',
				'item_list_items',
			),
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only enqueue on post editing pages
		if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
			return;
		}

		// Enqueue styles
		wp_enqueue_style(
			'seo-simple-schema-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-schema-admin.css',
			array(),
			SEO_SIMPLE_VERSION
		);

		// Enqueue scripts
		wp_enqueue_script(
			'seo-simple-schema-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-schema-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'seo-simple-schema-admin',
			'seoSimpleSchemaAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_schema_admin_nonce' ),
				'schemaTypes' => $this->schema_enhanced->get_schema_types(),
			)
		);
	}

	/**
	 * AJAX handler for validating schema.
	 *
	 * @since    1.0.0
	 */
	public function ajax_validate_schema() {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_schema_admin_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Get schema data
		$schema_json = isset( $_POST['schema'] ) ? stripslashes( $_POST['schema'] ) : '';
		
		if ( empty( $schema_json ) ) {
			wp_send_json_error( array( 'message' => __( 'Schema data is empty.', 'seo-simple' ) ) );
		}

		// Decode JSON
		$schema = json_decode( $schema_json, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON format.', 'seo-simple' ) ) );
		}

		// Validate schema
		$is_valid = $this->schema_enhanced->validate_schema( $schema );
		
		if ( $is_valid ) {
			wp_send_json_success( array( 'message' => __( 'Schema is valid!', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Schema validation failed.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for previewing schema.
	 *
	 * @since    1.0.0
	 */
	public function ajax_preview_schema() {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_schema_admin_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Get post ID
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		
		if ( $post_id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}

		// Generate schema
		$schema = $this->schema_enhanced->generate_schema_by_type( $_POST['schema_type'], $post_id );
		
		if ( empty( $schema ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to generate schema.', 'seo-simple' ) ) );
		}

		// Format JSON for display
		$json = json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		
		wp_send_json_success( array( 'schema' => $json ) );
	}

	/**
	 * AJAX handler for getting schema fields.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_schema_fields() {
		// Check nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_schema_admin_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}

		// Get schema type
		$schema_type = isset( $_POST['schema_type'] ) ? sanitize_text_field( $_POST['schema_type'] ) : '';
		
		if ( empty( $schema_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Schema type is required.', 'seo-simple' ) ) );
		}

		// Get post ID
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

		// Render schema fields
		ob_start();
		$this->render_schema_fields( $schema_type, $post_id );
		$fields_html = ob_get_clean();

		wp_send_json_success( array( 'html' => $fields_html ) );
	}

	/**
	 * Render schema fields.
	 *
	 * @since    1.0.0
	 * @param    string    $schema_type    The schema type.
	 * @param    int       $post_id        The post ID.
	 */
	private function render_schema_fields( $schema_type, $post_id ) {
		// Include the appropriate template based on schema type
		$template_path = plugin_dir_path( __FILE__ ) . 'partials/schema-fields/';
		$template_file = $template_path . 'schema-' . strtolower( $schema_type ) . '.php';
		
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<p>' . __( 'No additional fields required for this schema type.', 'seo-simple' ) . '</p>';
		}
	}
}