<?php

/**
 * Local Business SEO functionality
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Local Business SEO class
 *
 * This class handles all local business SEO functionality including schema markup,
 * business information management, and local SEO features.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Local_Business {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @static
	 * @var      Seo_Simple_Local_Business    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @static
	 * @return   Seo_Simple_Local_Business    The single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function __construct() {
		// Register hooks - ensure post types are registered after textdomain is loaded
		add_action( 'init', array( $this, 'register_post_types' ), 0 ); // Priority 0, after textdomain (-5) but before most other hooks
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ) );
		add_action( 'wp_head', array( $this, 'output_local_business_schema' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'seo_simple_schema_types', array( $this, 'add_local_business_schema_type' ) );
		add_filter( 'seo_simple_schema_data', array( $this, 'add_local_business_schema_data' ), 10, 2 );
		
		// Add shortcode for business information
		add_shortcode( 'local_business_info', array( $this, 'business_info_shortcode' ) );
		
		// Add widget for business information after textdomain is loaded
		add_action( 'init', array( $this, 'register_widgets' ), 20 );
	}

	/**
	 * Register custom post types for local business.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_post_types() {
		// Register Locations post type
		register_post_type( 'seo_location', array(
			'labels' => array(
				'name' => __( 'Business Locations', 'seo-simple' ),
				'singular_name' => __( 'Location', 'seo-simple' ),
				'menu_name' => __( 'Locations', 'seo-simple' ),
				'name_admin_bar' => __( 'Location', 'seo-simple' ),
				'add_new' => __( 'Add New', 'seo-simple' ),
				'add_new_item' => __( 'Add New Location', 'seo-simple' ),
				'new_item' => __( 'New Location', 'seo-simple' ),
				'edit_item' => __( 'Edit Location', 'seo-simple' ),
				'view_item' => __( 'View Location', 'seo-simple' ),
				'all_items' => __( 'All Locations', 'seo-simple' ),
				'search_items' => __( 'Search Locations', 'seo-simple' ),
				'parent_item_colon' => __( 'Parent Locations:', 'seo-simple' ),
				'not_found' => __( 'No locations found.', 'seo-simple' ),
				'not_found_in_trash' => __( 'No locations found in Trash.', 'seo-simple' ),
				'featured_image' => __( 'Location Image', 'seo-simple' ),
				'set_featured_image' => __( 'Set location image', 'seo-simple' ),
				'remove_featured_image' => __( 'Remove location image', 'seo-simple' ),
				'use_featured_image' => __( 'Use as location image', 'seo-simple' ),
				'archives' => __( 'Location archives', 'seo-simple' ),
				'insert_into_item' => __( 'Insert into location', 'seo-simple' ),
				'uploaded_to_this_item' => __( 'Uploaded to this location', 'seo-simple' ),
				'filter_items_list' => __( 'Filter locations list', 'seo-simple' ),
				'items_list_navigation' => __( 'Locations list navigation', 'seo-simple' ),
				'items_list' => __( 'Locations list', 'seo-simple' ),
			),
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'locations' ),
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 25,
			'menu_icon' => 'dashicons-location',
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		) );

		// Register Location Categories taxonomy
		register_taxonomy( 'seo_location_category', array( 'seo_location' ), array(
			'labels' => array(
				'name' => __( 'Location Categories', 'seo-simple' ),
				'singular_name' => __( 'Location Category', 'seo-simple' ),
				'menu_name' => __( 'Categories', 'seo-simple' ),
				'all_items' => __( 'All Categories', 'seo-simple' ),
				'edit_item' => __( 'Edit Category', 'seo-simple' ),
				'view_item' => __( 'View Category', 'seo-simple' ),
				'update_item' => __( 'Update Category', 'seo-simple' ),
				'add_new_item' => __( 'Add New Category', 'seo-simple' ),
				'new_item_name' => __( 'New Category Name', 'seo-simple' ),
				'parent_item' => __( 'Parent Category', 'seo-simple' ),
				'parent_item_colon' => __( 'Parent Category:', 'seo-simple' ),
				'search_items' => __( 'Search Categories', 'seo-simple' ),
				'popular_items' => __( 'Popular Categories', 'seo-simple' ),
				'separate_items_with_commas' => __( 'Separate categories with commas', 'seo-simple' ),
				'add_or_remove_items' => __( 'Add or remove categories', 'seo-simple' ),
				'choose_from_most_used' => __( 'Choose from the most used', 'seo-simple' ),
				'not_found' => __( 'No categories found.', 'seo-simple' ),
				'no_terms' => __( 'No categories', 'seo-simple' ),
				'items_list_navigation' => __( 'Categories list navigation', 'seo-simple' ),
				'items_list' => __( 'Categories list', 'seo-simple' ),
			),
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'location-categories' ),
		) );
	}

	/**
	 * Add meta boxes for local business information.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_meta_boxes() {
		// Add meta box for locations
		add_meta_box(
			'seo_location_info',
			__( 'Location Information', 'seo-simple' ),
			array( $this, 'render_location_meta_box' ),
			'seo_location',
			'normal',
			'high'
		);
		
		// Add meta box for business hours
		add_meta_box(
			'seo_business_hours',
			__( 'Business Hours', 'seo-simple' ),
			array( $this, 'render_business_hours_meta_box' ),
			'seo_location',
			'normal',
			'high'
		);
		
		// Add meta box for contact information
		add_meta_box(
			'seo_contact_info',
			__( 'Contact Information', 'seo-simple' ),
			array( $this, 'render_contact_info_meta_box' ),
			'seo_location',
			'normal',
			'high'
		);
		
		// Add meta box for posts/pages to enable local business schema
		add_meta_box(
			'seo_local_business_schema',
			__( 'Local Business Schema', 'seo-simple' ),
			array( $this, 'render_local_business_schema_meta_box' ),
			array( 'post', 'page' ),
			'side',
			'default'
		);
	}

	/**
	 * Render location information meta box.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_Post    $post    The current post object.
	 */
	public function render_location_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_location_info_nonce', 'seo_location_info_nonce' );
		
		// Get current values
		$business_type = get_post_meta( $post->ID, '_seo_business_type', true );
		$address = get_post_meta( $post->ID, '_seo_address', true );
		$latitude = get_post_meta( $post->ID, '_seo_latitude', true );
		$longitude = get_post_meta( $post->ID, '_seo_longitude', true );
		$price_range = get_post_meta( $post->ID, '_seo_price_range', true );
		
		// Include meta box template
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/location-info-meta-box.php';
	}

	/**
	 * Render business hours meta box.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_Post    $post    The current post object.
	 */
	public function render_business_hours_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_business_hours_nonce', 'seo_business_hours_nonce' );
		
		// Get current values
		$business_hours = get_post_meta( $post->ID, '_seo_business_hours', true );
		if ( empty( $business_hours ) ) {
			$business_hours = array(
				'monday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'tuesday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'wednesday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'thursday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'friday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'saturday' => array( 'open' => '', 'close' => '', 'closed' => false ),
				'sunday' => array( 'open' => '', 'close' => '', 'closed' => false ),
			);
		}
		
		// Include meta box template
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/business-hours-meta-box.php';
	}

	/**
	 * Render contact information meta box.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_Post    $post    The current post object.
	 */
	public function render_contact_info_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_contact_info_nonce', 'seo_contact_info_nonce' );
		
		// Get current values
		$phone = get_post_meta( $post->ID, '_seo_phone', true );
		$email = get_post_meta( $post->ID, '_seo_email', true );
		$website = get_post_meta( $post->ID, '_seo_website', true );
		$fax = get_post_meta( $post->ID, '_seo_fax', true );
		
		// Include meta box template
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/contact-info-meta-box.php';
	}

	/**
	 * Render local business schema meta box for posts/pages.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_Post    $post    The current post object.
	 */
	public function render_local_business_schema_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_local_business_schema_nonce', 'seo_local_business_schema_nonce' );
		
		// Get current values
		$enable_schema = get_post_meta( $post->ID, '_seo_enable_local_business_schema', true );
		$business_location = get_post_meta( $post->ID, '_seo_business_location', true );
		
		// Get all locations
		$locations = get_posts( array(
			'post_type' => 'seo_location',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		) );
		
		// Include meta box template
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/local-business-schema-meta-box.php';
	}

	/**
	 * Save meta data for locations.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $post_id    The post ID.
	 */
	public function save_meta_data( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['seo_location_info_nonce'] ) || 
		     ! isset( $_POST['seo_business_hours_nonce'] ) || 
		     ! isset( $_POST['seo_contact_info_nonce'] ) ) {
			return;
		}
		
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['seo_location_info_nonce'], 'seo_location_info_nonce' ) ||
		     ! wp_verify_nonce( $_POST['seo_business_hours_nonce'], 'seo_business_hours_nonce' ) ||
		     ! wp_verify_nonce( $_POST['seo_contact_info_nonce'], 'seo_contact_info_nonce' ) ) {
			return;
		}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'seo_location' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
		
		// Save location info
		if ( isset( $_POST['seo_business_type'] ) ) {
			update_post_meta( $post_id, '_seo_business_type', sanitize_text_field( $_POST['seo_business_type'] ) );
		}
		
		if ( isset( $_POST['seo_address'] ) ) {
			update_post_meta( $post_id, '_seo_address', sanitize_textarea_field( $_POST['seo_address'] ) );
		}
		
		if ( isset( $_POST['seo_latitude'] ) ) {
			update_post_meta( $post_id, '_seo_latitude', sanitize_text_field( $_POST['seo_latitude'] ) );
		}
		
		if ( isset( $_POST['seo_longitude'] ) ) {
			update_post_meta( $post_id, '_seo_longitude', sanitize_text_field( $_POST['seo_longitude'] ) );
		}
		
		if ( isset( $_POST['seo_price_range'] ) ) {
			update_post_meta( $post_id, '_seo_price_range', sanitize_text_field( $_POST['seo_price_range'] ) );
		}
		
		// Save business hours
		if ( isset( $_POST['seo_business_hours'] ) ) {
			$business_hours = array();
			$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
			
			foreach ( $days as $day ) {
				$business_hours[$day] = array(
					'open' => isset( $_POST['seo_business_hours'][$day]['open'] ) ? sanitize_text_field( $_POST['seo_business_hours'][$day]['open'] ) : '',
					'close' => isset( $_POST['seo_business_hours'][$day]['close'] ) ? sanitize_text_field( $_POST['seo_business_hours'][$day]['close'] ) : '',
					'closed' => isset( $_POST['seo_business_hours'][$day]['closed'] ) ? (bool) $_POST['seo_business_hours'][$day]['closed'] : false,
				);
			}
			
			update_post_meta( $post_id, '_seo_business_hours', $business_hours );
		}
		
		// Save contact info
		if ( isset( $_POST['seo_phone'] ) ) {
			update_post_meta( $post_id, '_seo_phone', sanitize_text_field( $_POST['seo_phone'] ) );
		}
		
		if ( isset( $_POST['seo_email'] ) ) {
			update_post_meta( $post_id, '_seo_email', sanitize_email( $_POST['seo_email'] ) );
		}
		
		if ( isset( $_POST['seo_website'] ) ) {
			update_post_meta( $post_id, '_seo_website', esc_url_raw( $_POST['seo_website'] ) );
		}
		
		if ( isset( $_POST['seo_fax'] ) ) {
			update_post_meta( $post_id, '_seo_fax', sanitize_text_field( $_POST['seo_fax'] ) );
		}
		
		// Save local business schema for posts/pages
		if ( isset( $_POST['seo_local_business_schema_nonce'] ) && 
		     wp_verify_nonce( $_POST['seo_local_business_schema_nonce'], 'seo_local_business_schema_nonce' ) ) {
			
			if ( isset( $_POST['seo_enable_local_business_schema'] ) ) {
				update_post_meta( $post_id, '_seo_enable_local_business_schema', '1' );
			} else {
				delete_post_meta( $post_id, '_seo_enable_local_business_schema' );
			}
			
			if ( isset( $_POST['seo_business_location'] ) ) {
				update_post_meta( $post_id, '_seo_business_location', intval( $_POST['seo_business_location'] ) );
			}
		}
	}

	/**
	 * Output local business schema markup.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function output_local_business_schema() {
		global $post;
		
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}
		
		// Check if local business schema is enabled for this post
		$enable_schema = get_post_meta( $post->ID, '_seo_enable_local_business_schema', true );
		$business_location_id = get_post_meta( $post->ID, '_seo_business_location', true );
		
		if ( ! $enable_schema || empty( $business_location_id ) ) {
			return;
		}
		
		$location = get_post( $business_location_id );
		if ( ! $location || $location->post_type !== 'seo_location' ) {
			return;
		}
		
		$schema = $this->get_local_business_schema( $location );
		if ( $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
		}
	}

	/**
	 * Get local business schema data.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    WP_Post    $location    The location post object.
	 * @return   array                   The schema data.
	 */
	public function get_local_business_schema( $location ) {
		$business_type = get_post_meta( $location->ID, '_seo_business_type', true );
		$address = get_post_meta( $location->ID, '_seo_address', true );
		$latitude = get_post_meta( $location->ID, '_seo_latitude', true );
		$longitude = get_post_meta( $location->ID, '_seo_longitude', true );
		$price_range = get_post_meta( $location->ID, '_seo_price_range', true );
		$phone = get_post_meta( $location->ID, '_seo_phone', true );
		$email = get_post_meta( $location->ID, '_seo_email', true );
		$website = get_post_meta( $location->ID, '_seo_website', true );
		$business_hours = get_post_meta( $location->ID, '_seo_business_hours', true );
		
		if ( empty( $business_type ) || empty( $address ) ) {
			return false;
		}
		
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => $business_type,
			'name' => get_the_title( $location->ID ),
			'description' => get_the_excerpt( $location->ID ),
			'url' => get_permalink( $location->ID ),
			'address' => $address,
		);
		
		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {
			$schema['geo'] = array(
				'@type' => 'GeoCoordinates',
				'latitude' => floatval( $latitude ),
				'longitude' => floatval( $longitude ),
			);
		}
		
		if ( ! empty( $price_range ) ) {
			$schema['priceRange'] = $price_range;
		}
		
		if ( ! empty( $phone ) ) {
			$schema['telephone'] = $phone;
		}
		
		if ( ! empty( $email ) ) {
			$schema['email'] = $email;
		}
		
		if ( ! empty( $website ) ) {
			$schema['sameAs'] = $website;
		}
		
		if ( ! empty( $business_hours ) && is_array( $business_hours ) ) {
			$opening_hours = array();
			$days_map = array(
				'monday' => 'Monday',
				'tuesday' => 'Tuesday',
				'wednesday' => 'Wednesday',
				'thursday' => 'Thursday',
				'friday' => 'Friday',
				'saturday' => 'Saturday',
				'sunday' => 'Sunday',
			);
			
			foreach ( $business_hours as $day => $hours ) {
				if ( ! $hours['closed'] && ! empty( $hours['open'] ) && ! empty( $hours['close'] ) ) {
					$opening_hours[] = $days_map[$day] . ' ' . $hours['open'] . '-' . $hours['close'];
				}
			}
			
			if ( ! empty( $opening_hours ) ) {
				$schema['openingHours'] = $opening_hours;
			}
		}
		
		if ( has_post_thumbnail( $location->ID ) ) {
			$image_id = get_post_thumbnail_id( $location->ID );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = $image_url;
			}
		}
		
		return apply_filters( 'seo_simple_local_business_schema', $schema, $location );
	}

	/**
	 * Add local business schema type to schema types filter.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $schema_types    The current schema types.
	 * @return   array                      The modified schema types.
	 */
	public function add_local_business_schema_type( $schema_types ) {
		$schema_types['LocalBusiness'] = __( 'Local Business', 'seo-simple' );
		return $schema_types;
	}

	/**
	 * Add local business schema data to schema data filter.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $schema_data    The current schema data.
	 * @param    int       $post_id        The post ID.
	 * @return   array                     The modified schema data.
	 */
	public function add_local_business_schema_data( $schema_data, $post_id ) {
		$enable_schema = get_post_meta( $post_id, '_seo_enable_local_business_schema', true );
		$business_location_id = get_post_meta( $post_id, '_seo_business_location', true );
		
		if ( $enable_schema && ! empty( $business_location_id ) ) {
			$location = get_post( $business_location_id );
			if ( $location && $location->post_type === 'seo_location' ) {
				$local_business_schema = $this->get_local_business_schema( $location );
				if ( $local_business_schema ) {
					$schema_data[] = $local_business_schema;
				}
			}
		}
		
		return $schema_data;
	}

	/**
	 * Enqueue scripts for local business functionality.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'seo-simple-local-business',
			plugin_dir_url( __FILE__ ) . '../public/css/seo-simple-local-business.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		wp_enqueue_script(
			'seo-simple-local-business',
			plugin_dir_url( __FILE__ ) . '../public/js/seo-simple-local-business.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}

	/**
	 * Business info shortcode.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array     $atts    Shortcode attributes.
	 * @return   string             The shortcode output.
	 */
	public function business_info_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,
			'show' => 'all', // all, name, address, phone, email, hours
			'schema' => 'false',
		), $atts, 'local_business_info' );
		
		$location_id = intval( $atts['id'] );
		if ( empty( $location_id ) ) {
			return '';
		}
		
		$location = get_post( $location_id );
		if ( ! $location || $location->post_type !== 'seo_location' ) {
			return '';
		}
		
		$output = '';
		
		if ( $atts['show'] === 'all' || $atts['show'] === 'name' ) {
			$output .= '<h3 class="business-name">' . get_the_title( $location_id ) . '</h3>';
		}
		
		if ( $atts['show'] === 'all' || $atts['show'] === 'address' ) {
			$address = get_post_meta( $location_id, '_seo_address', true );
			if ( ! empty( $address ) ) {
				$output .= '<div class="business-address">' . esc_html( $address ) . '</div>';
			}
		}
		
		if ( $atts['show'] === 'all' || $atts['show'] === 'phone' ) {
			$phone = get_post_meta( $location_id, '_seo_phone', true );
			if ( ! empty( $phone ) ) {
				$output .= '<div class="business-phone">' . esc_html( $phone ) . '</div>';
			}
		}
		
		if ( $atts['show'] === 'all' || $atts['show'] === 'email' ) {
			$email = get_post_meta( $location_id, '_seo_email', true );
			if ( ! empty( $email ) ) {
				$output .= '<div class="business-email"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></div>';
			}
		}
		
		if ( $atts['show'] === 'all' || $atts['show'] === 'hours' ) {
			$business_hours = get_post_meta( $location_id, '_seo_business_hours', true );
			if ( ! empty( $business_hours ) && is_array( $business_hours ) ) {
				$output .= '<div class="business-hours"><h4>' . __( 'Business Hours', 'seo-simple' ) . '</h4><ul>';
				$days_map = array(
					'monday' => __( 'Monday', 'seo-simple' ),
					'tuesday' => __( 'Tuesday', 'seo-simple' ),
					'wednesday' => __( 'Wednesday', 'seo-simple' ),
					'thursday' => __( 'Thursday', 'seo-simple' ),
					'friday' => __( 'Friday', 'seo-simple' ),
					'saturday' => __( 'Saturday', 'seo-simple' ),
					'sunday' => __( 'Sunday', 'seo-simple' ),
				);
				
				foreach ( $business_hours as $day => $hours ) {
					if ( $hours['closed'] ) {
						$output .= '<li>' . $days_map[$day] . ': ' . __( 'Closed', 'seo-simple' ) . '</li>';
					} elseif ( ! empty( $hours['open'] ) && ! empty( $hours['close'] ) ) {
						$output .= '<li>' . $days_map[$day] . ': ' . $hours['open'] . ' - ' . $hours['close'] . '</li>';
					}
				}
				$output .= '</ul></div>';
			}
		}
		
		if ( $atts['schema'] === 'true' ) {
			$schema = $this->get_local_business_schema( $location );
			if ( $schema ) {
				$output .= '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
			}
		}
		
		return '<div class="local-business-info">' . $output . '</div>';
	}

	/**
	 * Register widgets for local business information.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function register_widgets() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-seo-simple-local-business-widget.php';
		register_widget( 'Seo_Simple_Local_Business_Widget' );
	}
}