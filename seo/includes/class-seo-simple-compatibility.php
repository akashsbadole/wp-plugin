<?php

/**
 * Handles compatibility with various WordPress plugins and features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles compatibility with various WordPress plugins and features.
 *
 * This class is responsible for ensuring the SEO plugin works correctly
 * with different editors, plugins, and WordPress configurations.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Compatibility {

	/**
	 * The meta handler instance.
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
	public function __construct( $seo_meta = null ) {
		$this->seo_meta = $seo_meta;
		
		// Initialize compatibility hooks
		$this->init_hooks();
	}

	/**
	 * Initialize compatibility hooks.
	 *
	 * @since    1.0.0
	 */
	private function init_hooks() {
		// WooCommerce integration
		if ( $this->is_woocommerce_active() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_woocommerce_seo_fields' ), 20 );
			add_filter( 'seo_plugin_save_meta', array( $this, 'save_woocommerce_seo_meta' ), 10, 3 );
			add_filter( 'seo_plugin_calculate_score', array( $this, 'woocommerce_score_calculation' ), 10, 3 );
			add_filter( 'seo_plugin_add_schema', array( $this, 'add_woocommerce_schema' ), 10, 3 );
		}
		
		// Gutenberg integration
		if ( $this->is_gutenberg_active() ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ) );
			add_filter( 'register_post_type_args', array( $this, 'add_gutenberg_support' ), 10, 2 );
		}
		
		// Classic Editor integration
		if ( $this->is_classic_editor_active() ) {
			add_filter( 'plugin_action_links_' . plugin_basename( SEO_SIMPLE_PLUGIN_FILE ), array( $this, 'add_classic_editor_settings' ) );
		}
		
		// Multisite compatibility
		if ( is_multisite() ) {
			add_filter( 'option_seo_simple_settings', array( $this, 'multisite_settings_filter' ), 10, 2 );
			add_action( 'wp_initialize_site', array( $this, 'new_site_setup' ), 10, 2 );
			add_action( 'wpmu_delete_blog', array( $this, 'delete_site_data' ), 10, 2 );
		}
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if WooCommerce is active.
	 */
	public function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check if Gutenberg is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if Gutenberg is active.
	 */
	public function is_gutenberg_active() {
		// Gutenberg plugin is active
		if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
			return true;
		}
		
		// WordPress 5.0+ with block editor
		if ( function_exists( 'register_block_type' ) ) {
			$post = get_post();
			if ( $post && function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $post->post_type ) ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if Classic Editor is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if Classic Editor is active.
	 */
	public function is_classic_editor_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		return is_plugin_active( 'classic-editor/classic-editor.php' );
	}

	/**
	 * Check which editor is currently active for a post type.
	 *
	 * @since    1.0.0
	 * @param    string    $post_type    The post type to check.
	 * @return   string                  'gutenberg', 'classic', or 'both'.
	 */
	public function get_active_editor( $post_type = 'post' ) {
		// Check if Gutenberg is disabled for this post type
		if ( ! $this->is_gutenberg_active() ) {
			return 'classic';
		}
		
		// Check if Classic Editor is forcing the classic editor
		if ( $this->is_classic_editor_active() ) {
			$classic_editor_settings = get_option( 'classic-editor-replace', 'block' );
			$classic_editor_allow_users = get_option( 'classic-editor-allow-users', 'allow' );
			
			if ( $classic_editor_settings === 'replace' ) {
				return 'classic';
			}
			
			if ( $classic_editor_settings === 'allow' && $classic_editor_allow_users === 'disallow' ) {
				return 'gutenberg';
			}
			
			// If users can choose, check their preference
			if ( $classic_editor_settings === 'allow' && $classic_editor_allow_users === 'allow' ) {
				$user_preference = get_user_meta( get_current_user_id(), 'classic-editor-settings', true );
				if ( $user_preference === 'classic-editor' ) {
					return 'classic';
				}
			}
		}
		
		// Check if the post type supports Gutenberg
		if ( function_exists( 'use_block_editor_for_post_type' ) && ! use_block_editor_for_post_type( $post_type ) ) {
			return 'classic';
		}
		
		return 'gutenberg';
	}

	/**
	 * Add SEO fields to WooCommerce products.
	 *
	 * @since    1.0.0
	 */
	public function add_woocommerce_seo_fields() {
		add_meta_box(
			'seo-simple-woocommerce-meta-box',
			__( 'Product SEO', 'seo-simple' ),
			array( $this, 'render_woocommerce_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render WooCommerce product SEO meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_woocommerce_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_simple_woocommerce_meta_box', 'seo_simple_woocommerce_meta_box_nonce' );
		
		// Get current meta values
		$meta_data = $this->seo_meta->get_all_meta( $post->ID );
		
		// Get product-specific meta
		$product_seo_title = get_post_meta( $post->ID, '_seo_simple_product_title', true );
		$product_seo_description = get_post_meta( $post->ID, '_seo_simple_product_description', true );
		
		// Include the WooCommerce meta box template
		include plugin_dir_path( __FILE__ ) . '../admin/partials/meta-box-woocommerce.php';
	}

	/**
	 * Save WooCommerce SEO meta data.
	 *
	 * @since    1.0.0
	 * @param    bool      $result    Whether to continue saving.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to save.
	 * @return   bool                 Whether to continue saving.
	 */
	public function save_woocommerce_seo_meta( $result, $post_id, $data ) {
		// Check if this is a product
		if ( get_post_type( $post_id ) !== 'product' ) {
			return $result;
		}
		
		// Check nonce
		if ( ! isset( $_POST['seo_simple_woocommerce_meta_box_nonce'] ) || 
		     ! wp_verify_nonce( $_POST['seo_simple_woocommerce_meta_box_nonce'], 'seo_simple_woocommerce_meta_box' ) ) {
			return $result;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $result;
		}
		
		// Save product-specific SEO fields
		if ( isset( $data['product_title'] ) ) {
			update_post_meta( $post_id, '_seo_simple_product_title', sanitize_text_field( $data['product_title'] ) );
		}
		
		if ( isset( $data['product_description'] ) ) {
			update_post_meta( $post_id, '_seo_simple_product_description', sanitize_textarea_field( $data['product_description'] ) );
		}
		
		return $result;
	}

	/**
	 * Custom score calculation for WooCommerce products.
	 *
	 * @since    1.0.0
	 * @param    array     $score     The current score data.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to calculate score from.
	 * @return   array                The modified score data.
	 */
	public function woocommerce_score_calculation( $score, $post_id, $data ) {
		// Only modify for products
		if ( get_post_type( $post_id ) !== 'product' ) {
			return $score;
		}
		
		// Get product
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return $score;
		}
		
		// Check for product-specific SEO factors
		$checks = array();
		
		// Check if product has a price
		if ( $product->get_price() > 0 ) {
			$checks['has_price'] = array(
				'status' => 'good',
				'message' => __( 'Product has a price set', 'seo-simple' )
			);
		} else {
			$checks['has_price'] = array(
				'status' => 'bad',
				'message' => __( 'Product price is not set', 'seo-simple' )
			);
		}
		
		// Check if product has categories
		$categories = get_the_terms( $post_id, 'product_cat' );
		if ( $categories && ! is_wp_error( $categories ) && count( $categories ) > 0 ) {
			$checks['has_categories'] = array(
				'status' => 'good',
				'message' => __( 'Product is assigned to categories', 'seo-simple' )
			);
		} else {
			$checks['has_categories'] = array(
				'status' => 'bad',
				'message' => __( 'Product is not assigned to any categories', 'seo-simple' )
			);
		}
		
		// Check if product has images
		if ( has_post_thumbnail( $post_id ) ) {
			$checks['has_image'] = array(
				'status' => 'good',
				'message' => __( 'Product has a featured image', 'seo-simple' )
			);
		} else {
			$checks['has_image'] = array(
				'status' => 'bad',
				'message' => __( 'Product does not have a featured image', 'seo-simple' )
			);
		}
		
		// Check if product has a description
		$product_description = $product->get_description();
		if ( ! empty( $product_description ) ) {
			$checks['has_description'] = array(
				'status' => 'good',
				'message' => __( 'Product has a description', 'seo-simple' )
			);
		} else {
			$checks['has_description'] = array(
				'status' => 'bad',
				'message' => __( 'Product does not have a description', 'seo-simple' )
			);
		}
		
		// Calculate score based on checks
		$total_checks = count( $checks );
		$good_checks = 0;
		
		foreach ( $checks as $check ) {
			if ( $check['status'] === 'good' ) {
				$good_checks++;
			}
		}
		
		$product_score = round( ( $good_checks / $total_checks ) * 100 );
		
		// Determine status
		if ( $product_score >= 80 ) {
			$status = 'good';
		} elseif ( $product_score >= 50 ) {
			$status = 'ok';
		} else {
			$status = 'bad';
		}
		
		// Add product-specific data to score
		$score['woocommerce'] = array(
			'score' => $product_score,
			'status' => $status,
			'checks' => $checks
		);
		
		return $score;
	}

	/**
	 * Add WooCommerce schema data.
	 *
	 * @since    1.0.0
	 * @param    array     $schema    The current schema data.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to build schema from.
	 * @return   array                The modified schema data.
	 */
	public function add_woocommerce_schema( $schema, $post_id, $data ) {
		// Only add for products
		if ( get_post_type( $post_id ) !== 'product' ) {
			return $schema;
		}
		
		// Get product
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return $schema;
		}
		
		// Build product schema
		$product_schema = array(
			'@context' => 'https://schema.org/',
			'@type' => 'Product',
			'name' => get_the_title( $post_id ),
			'description' => ! empty( $data['description'] ) ? $data['description'] : $product->get_description(),
			'url' => get_permalink( $post_id ),
		);
		
		// Add image if available
		if ( has_post_thumbnail( $post_id ) ) {
			$image_id = get_post_thumbnail_id( $post_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$product_schema['image'] = array( $image_url );
			}
		}
		
		// Add price if available
		if ( $product->get_price() > 0 ) {
			$product_schema['offers'] = array(
				'@type' => 'Offer',
				'price' => $product->get_price(),
				'priceCurrency' => get_woocommerce_currency(),
				'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url' => get_permalink( $post_id ),
			);
		}
		
		// Add ratings if available
		$average_rating = $product->get_average_rating();
		if ( $average_rating > 0 ) {
			$product_schema['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => $average_rating,
				'reviewCount' => $product->get_review_count(),
			);
		}
		
		// Add categories
		$categories = get_the_terms( $post_id, 'product_cat' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$category_names = array();
			foreach ( $categories as $category ) {
				$category_names[] = $category->name;
			}
			$product_schema['category'] = $category_names;
		}
		
		// Add to schema array
		if ( ! isset( $schema['@graph'] ) ) {
			$schema['@graph'] = array();
		}
		
		$schema['@graph'][] = $product_schema;
		
		return $schema;
	}

	/**
	 * Enqueue Gutenberg-specific assets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_gutenberg_assets() {
		// Only enqueue if not already enqueued by the main meta box class
		if ( ! wp_script_is( 'seo-simple-gutenberg', 'registered' ) ) {
			wp_enqueue_script(
				'seo-simple-gutenberg',
				plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/seo-simple-gutenberg.js',
				array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-compose' ),
				SEO_SIMPLE_VERSION,
				true
			);
			
			wp_enqueue_style(
				'seo-simple-gutenberg',
				plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/seo-simple-gutenberg.css',
				array( 'wp-edit-blocks' ),
				SEO_SIMPLE_VERSION
			);
		}
	}

	/**
	 * Add Gutenberg support to custom post types.
	 *
	 * @since    1.0.0
	 * @param    array     $args      The post type arguments.
	 * @param    string    $post_type The post type name.
	 * @return   array                The modified post type arguments.
	 */
	public function add_gutenberg_support( $args, $post_type ) {
		// Only modify for public post types that don't already support the editor
		if ( $args['public'] && ! isset( $args['show_in_rest'] ) ) {
			$args['show_in_rest'] = true;
		}
		
		return $args;
	}

	/**
	 * Add Classic Editor settings link.
	 *
	 * @since    1.0.0
	 * @param    array     $links     The existing plugin action links.
	 * @return   array                The modified plugin action links.
	 */
	public function add_classic_editor_settings( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=seo-simple-settings' ) . '">' . __( 'Settings', 'seo-simple' ) . '</a>';
		array_unshift( $links, $settings_link );
		
		return $links;
	}

	/**
	 * Filter settings for multisite compatibility.
	 *
	 * @since    1.0.0
	 * @param    mixed     $value     The option value.
	 * @param    string    $option    The option name.
	 * @return   mixed                The filtered option value.
	 */
	public function multisite_settings_filter( $value, $option ) {
		if ( $option !== 'seo_simple_settings' ) {
			return $value;
		}
		
		// Get network-wide settings
		$network_settings = get_site_option( 'seo_simple_network_settings', array() );
		
		// If network settings exist and are not empty, use them as defaults
		if ( ! empty( $network_settings ) ) {
			// Merge with site-specific settings, with site-specific taking precedence
			if ( is_array( $value ) ) {
				$value = array_merge( $network_settings, $value );
			} else {
				$value = $network_settings;
			}
		}
		
		return $value;
	}

	/**
	 * Set up SEO data for a new site in a multisite network.
	 *
	 * @since    1.0.0
	 * @param    WP_Site   $site      The new site object.
	 * @param    array     $args      The arguments passed to wp_initialize_site().
	 */
	public function new_site_setup( $site, $args ) {
		switch_to_blog( $site->blog_id );
		
		// Set default settings for the new site
		$default_settings = array(
			// General Settings
			'seo_enable_posts' => 1,
			'seo_enable_pages' => 1,
			'seo_enable_categories' => 1,
			'seo_enable_tags' => 1,

			// Titles & Meta
			'seo_title_separator' => '-',
			'seo_title_format' => '%post_title% | %site_name%',
			'seo_description_template' => '',
			'seo_home_title' => '',
			'seo_home_description' => '',
			'seo_title_length' => 60,
			'seo_description_length' => 160,

			// Social Settings
			'seo_enable_og' => 1,
			'seo_enable_twitter' => 1,
			'seo_enable_json_ld' => 1,
			'seo_og_image' => '',

			// Sitemap Settings
			'seo_enable_sitemap' => 1,
			'seo_sitemap_include_posts' => 1,
			'seo_sitemap_include_pages' => 1,
			'seo_sitemap_include_categories' => 1,
			'seo_sitemap_include_tags' => 1,

			// Advanced Settings
			'seo_show_metabox' => 1,
			'seo_enable_analysis' => 1,
			'seo_enable_readability' => 1,
			'seo_enable_robots' => 1,
		);
		
		update_option( 'seo_simple_settings', $default_settings );
		
		restore_current_blog();
	}

	/**
	 * Clean up SEO data when a site is deleted in a multisite network.
	 *
	 * @since    1.0.0
	 * @param    int       $site_id   The site ID.
	 * @param    bool      $drop      Whether to drop the site's tables.
	 */
	public function delete_site_data( $site_id, $drop ) {
		// This is handled by WordPress automatically when $drop is true
		// Tables with the site's prefix will be dropped
		// If we need to clean up additional data, we can do it here
		
		// Example: Clean up any site options related to our plugin
		delete_site_option( 'seo_simple_site_' . $site_id . '_settings' );
	}

	/**
	 * Check if third-party plugins are compatible.
	 *
	 * @since    1.0.0
	 * @return   array    Array of compatible/incompatible plugins.
	 */
	public function check_plugin_compatibility() {
		$plugins = array();
		
		// Check common SEO plugins
		$common_seo_plugins = array(
			'yoast-seo' => 'Yoast SEO',
			'all-in-one-seo-pack' => 'All in One SEO Pack',
			'rank-math' => 'Rank Math',
			'seopress' => 'SEOPress',
			'the-seo-framework' => 'The SEO Framework',
		);
		
		foreach ( $common_seo_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( $plugin_slug . '/' . $plugin_slug . '.php' ) || 
			     is_plugin_active( $plugin_slug . '-premium/' . $plugin_slug . '.php' ) ) {
				$plugins[$plugin_slug] = array(
					'name' => $plugin_name,
					'status' => 'warning',
					'message' => sprintf( __( 'Another SEO plugin (%s) is active. This may cause conflicts.', 'seo-simple' ), $plugin_name ),
				);
			}
		}
		
		// Check page builders
		$page_builders = array(
			'elementor' => 'Elementor',
			'beaver-builder-lite-version' => 'Beaver Builder',
			'brizy' => 'Brizy',
			'divi' => 'Divi',
			'vc' => 'WPBakery Page Builder',
		);
		
		foreach ( $page_builders as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( $plugin_slug . '/' . $plugin_slug . '.php' ) || 
			     is_plugin_active( $plugin_slug . '-pro/' . $plugin_slug . '.php' ) ) {
				$plugins[$plugin_slug] = array(
					'name' => $plugin_name,
					'status' => 'info',
					'message' => sprintf( __( '%s is active. SEO Simple is compatible with this page builder.', 'seo-simple' ), $plugin_name ),
				);
			}
		}
		
		return $plugins;
	}

	/**
	 * Get compatibility information for the current site.
	 *
	 * @since    1.0.0
	 * @return   array    Array of compatibility information.
	 */
	public function get_compatibility_info() {
		$info = array();
		
		// Editor information
		$info['editor'] = array(
			'gutenberg' => $this->is_gutenberg_active(),
			'classic' => $this->is_classic_editor_active(),
			'active_editor' => $this->get_active_editor(),
		);
		
		// Plugin information
		$info['plugins'] = $this->check_plugin_compatibility();
		
		// WooCommerce information
		$info['woocommerce'] = array(
			'active' => $this->is_woocommerce_active(),
			'version' => $this->is_woocommerce_active() ? WC()->version : 'N/A',
		);
		
		// Multisite information
		$info['multisite'] = array(
			'enabled' => is_multisite(),
			'network' => is_network_admin(),
		);
		
		return $info;
	}
}
