<?php

/**
 * Handles WooCommerce SEO admin functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles WooCommerce SEO admin functionality.
 *
 * This class handles the admin interface for WooCommerce SEO features.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_WooCommerce_Admin {

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
	public function __construct( $seo_meta ) {
		$this->seo_meta = $seo_meta;
	}

	/**
	 * Register hooks for WooCommerce SEO admin.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Only run if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		// Add WooCommerce settings tab to SEO settings
		add_filter( 'seo_simple_settings_tabs', array( $this, 'add_woocommerce_settings_tab' ) );
		add_action( 'seo_simple_settings_tab_woocommerce', array( $this, 'render_woocommerce_settings' ) );
		
		// Add WooCommerce SEO dashboard widget
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		
		// Add WooCommerce SEO columns to product list
		add_filter( 'manage_edit-product_columns', array( $this, 'add_product_columns' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'display_product_columns' ), 10, 2 );
		
		// Add WooCommerce SEO columns to category list
		add_filter( 'manage_edit-product_cat_columns', array( $this, 'add_category_columns' ) );
		add_action( 'manage_product_cat_custom_column', array( $this, 'display_category_columns' ), 10, 3 );
		
		// Add WooCommerce SEO columns to tag list
		add_filter( 'manage_edit-product_tag_columns', array( $this, 'add_tag_columns' ) );
		add_action( 'manage_product_tag_custom_column', array( $this, 'display_tag_columns' ), 10, 3 );
		
		// AJAX handlers
		add_action( 'wp_ajax_seo_simple_woocommerce_analyze_product', array( $this, 'ajax_analyze_product' ) );
		add_action( 'wp_ajax_seo_simple_woocommerce_bulk_optimize', array( $this, 'ajax_bulk_optimize' ) );
		
		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add WooCommerce settings tab.
	 *
	 * @since    1.0.0
	 * @param    array    $tabs    The existing tabs.
	 * @return   array             The modified tabs.
	 */
	public function add_woocommerce_settings_tab( $tabs ) {
		$tabs['woocommerce'] = __( 'WooCommerce', 'seo-simple' );
		return $tabs;
	}

	/**
	 * Render WooCommerce settings.
	 *
	 * @since    1.0.0
	 */
	public function render_woocommerce_settings() {
		// Get current settings
		$settings = $this->seo_meta->get_global_settings();
		
		// Set defaults
		$defaults = array(
			'seo_woocommerce_enable' => '1',
			'seo_woocommerce_product_schema' => '1',
			'seo_woocommerce_category_schema' => '1',
			'seo_woocommerce_use_custom_schema' => '1',
			'seo_woocommerce_product_title_template' => '%product_title% - %site_name%',
			'seo_woocommerce_product_description_template' => '%product_description%',
			'seo_woocommerce_category_title_template' => '%category_name% - %site_name%',
			'seo_woocommerce_category_description_template' => '%category_description%',
			'seo_woocommerce_enable_breadcrumbs' => '1',
			'seo_woocommerce_enable_rel_next_prev' => '1',
			'seo_woocommerce_remove_generator' => '1',
			'seo_woocommerce_optimize_product_images' => '1',
			'seo_woocommerce_enable_product_schema' => '1',
			'seo_woocommerce_enable_breadcrumb_schema' => '1',
			'seo_woocommerce_enable_organization_schema' => '1',
		);
		
		// Merge with defaults
		$settings = wp_parse_args( $settings, $defaults );
		
		// Save settings if form submitted
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'seo_simple_woocommerce_settings' ) ) {
			// Update settings
			foreach ( array_keys( $defaults ) as $key ) {
				if ( isset( $_POST[$key] ) ) {
					$settings[$key] = sanitize_text_field( $_POST[$key] );
				} else {
					$settings[$key] = '0';
				}
			}
			
			// Save settings
			update_option( 'seo_simple_global_settings', $settings );
			
			// Show success message
			echo '<div class="notice notice-success is-dismissible"><p>' . __( 'WooCommerce SEO settings saved successfully!', 'seo-simple' ) . '</p></div>';
		}
		
		// Include the settings template
		include plugin_dir_path( __FILE__ ) . 'partials/woocommerce-settings-page.php';
	}

	/**
	 * Add WooCommerce SEO dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo_simple_woocommerce_dashboard',
			__( 'WooCommerce SEO Status', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render WooCommerce SEO dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		// Get statistics
		$total_products = wp_count_posts( 'product' )->publish;
		$products_with_seo = $this->count_products_with_seo();
		$products_missing_seo = $total_products - $products_with_seo;
		
		$total_categories = wp_count_terms( 'product_cat', array( 'hide_empty' => false ) );
		$categories_with_seo = $this->count_categories_with_seo();
		$categories_missing_seo = $total_categories - $categories_with_seo;
		
		// Include the dashboard widget template
		include plugin_dir_path( __FILE__ ) . 'partials/woocommerce-dashboard-widget.php';
	}

	/**
	 * Add SEO columns to products list.
	 *
	 * @since    1.0.0
	 * @param    array    $columns    The existing columns.
	 * @return   array                The modified columns.
	 */
	public function add_product_columns( $columns ) {
		$columns['seo_title'] = __( 'SEO Title', 'seo-simple' );
		$columns['seo_description'] = __( 'SEO Description', 'seo-simple' );
		$columns['seo_score'] = __( 'SEO Score', 'seo-simple' );
		return $columns;
	}

	/**
	 * Display SEO columns in products list.
	 *
	 * @since    1.0.0
	 * @param    string    $column    The column name.
	 * @param    int       $post_id   The post ID.
	 */
	public function display_product_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'seo_title':
				$title = get_post_meta( $post_id, '_seo_simple_product_title', true );
				echo $title ? esc_html( $title ) : '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				break;
				
			case 'seo_description':
				$description = get_post_meta( $post_id, '_seo_simple_product_description', true );
				if ( $description && is_string( $description ) ) {
					echo esc_html( substr( $description, 0, 50 ) . '...' );
				} else {
					echo '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				break;
				
			case 'seo_score':
				$score = $this->calculate_product_seo_score( $post_id );
				$color = $score >= 80 ? 'green' : ( $score >= 50 ? 'orange' : 'red' );
				echo '<span class="seo-simple-score seo-simple-score-' . $color . '">' . $score . '</span>';
				break;
		}
	}

	/**
	 * Add SEO columns to product categories list.
	 *
	 * @since    1.0.0
	 * @param    array    $columns    The existing columns.
	 * @return   array                The modified columns.
	 */
	public function add_category_columns( $columns ) {
		$columns['seo_title'] = __( 'SEO Title', 'seo-simple' );
		$columns['seo_description'] = __( 'SEO Description', 'seo-simple' );
		return $columns;
	}

	/**
	 * Display SEO columns in product categories list.
	 *
	 * @since    1.0.0
	 * @param    string    $string     The column content.
	 * @param    string    $column     The column name.
	 * @param    int       $term_id    The term ID.
	 * @return   string                The modified column content.
	 */
	public function display_category_columns( $string, $column, $term_id ) {
		switch ( $column ) {
			case 'seo_title':
				$title = get_term_meta( $term_id, 'seo_title', true );
				return $title ? esc_html( $title ) : '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				
			case 'seo_description':
				$description = get_term_meta( $term_id, 'seo_description', true );
				if ( $description && is_string( $description ) ) {
					return esc_html( substr( $description, 0, 50 ) . '...' );
				} else {
					return '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				
			default:
				return $string;
		}
	}

	/**
	 * Add SEO columns to product tags list.
	 *
	 * @since    1.0.0
	 * @param    array    $columns    The existing columns.
	 * @return   array                The modified columns.
	 */
	public function add_tag_columns( $columns ) {
		$columns['seo_title'] = __( 'SEO Title', 'seo-simple' );
		$columns['seo_description'] = __( 'SEO Description', 'seo-simple' );
		return $columns;
	}

	/**
	 * Display SEO columns in product tags list.
	 *
	 * @since    1.0.0
	 * @param    string    $string     The column content.
	 * @param    string    $column     The column name.
	 * @param    int       $term_id    The term ID.
	 * @return   string                The modified column content.
	 */
	public function display_tag_columns( $string, $column, $term_id ) {
		switch ( $column ) {
			case 'seo_title':
				$title = get_term_meta( $term_id, 'seo_title', true );
				return $title ? esc_html( $title ) : '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				
			case 'seo_description':
				$description = get_term_meta( $term_id, 'seo_description', true );
				if ( $description && is_string( $description ) ) {
					return esc_html( substr( $description, 0, 50 ) . '...' );
				} else {
					return '<span class="seo-simple-missing">' . __( 'Default', 'seo-simple' ) . '</span>';
				}
				
			default:
				return $string;
		}
	}

	/**
	 * AJAX handler for analyzing product SEO.
	 *
	 * @since    1.0.0
	 */
	public function ajax_analyze_product() {
		// Check nonce
		if ( ! check_ajax_referer( 'seo_simple_woocommerce_analyze_product', 'nonce', false ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have permission to perform this action.', 'seo-simple' ) );
		}
		
		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		
		if ( ! $product_id ) {
			wp_die( __( 'Invalid product ID.', 'seo-simple' ) );
		}
		
		// Analyze product SEO
		$analysis = $this->analyze_product_seo( $product_id );
		
		// Return results
		wp_send_json_success( $analysis );
	}

	/**
	 * AJAX handler for bulk optimizing products.
	 *
	 * @since    1.0.0
	 */
	public function ajax_bulk_optimize() {
		// Check nonce
		if ( ! check_ajax_referer( 'seo_simple_woocommerce_bulk_optimize', 'nonce', false ) ) {
			wp_die( __( 'Security check failed.', 'seo-simple' ) );
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have permission to perform this action.', 'seo-simple' ) );
		}
		
		$product_ids = isset( $_POST['product_ids'] ) ? array_map( 'intval', $_POST['product_ids'] ) : array();
		
		if ( empty( $product_ids ) ) {
			wp_die( __( 'No products selected.', 'seo-simple' ) );
		}
		
		$results = array();
		
		foreach ( $product_ids as $product_id ) {
			$results[$product_id] = $this->auto_optimize_product( $product_id );
		}
		
		// Return results
		wp_send_json_success( $results );
	}

	/**
	 * Analyze product SEO.
	 *
	 * @since    1.0.0
	 * @param    int       $product_id    The product ID.
	 * @return   array                    The analysis results.
	 */
	private function analyze_product_seo( $product_id ) {
		$product = wc_get_product( $product_id );
		
		if ( ! $product ) {
			return array( 'error' => __( 'Product not found.', 'seo-simple' ) );
		}
		
		$analysis = array();
		$score = 0;
		$max_score = 100;
		
		// Check SEO title
		$seo_title = get_post_meta( $product_id, '_seo_simple_product_title', true );
		$title_length = strlen( $seo_title );
		
		if ( $title_length >= 50 && $title_length <= 60 ) {
			$analysis['title'] = array(
				'status' => 'good',
				'message' => __( 'SEO title length is optimal.', 'seo-simple' ),
				'score' => 15,
			);
			$score += 15;
		} elseif ( $title_length > 0 ) {
			$analysis['title'] = array(
				'status' => 'warning',
				'message' => sprintf( __( 'SEO title length is %d characters. Optimal: 50-60.', 'seo-simple' ), $title_length ),
				'score' => 10,
			);
			$score += 10;
		} else {
			$analysis['title'] = array(
				'status' => 'error',
				'message' => __( 'SEO title is missing.', 'seo-simple' ),
				'score' => 0,
			);
		}
		
		// Check SEO description
		$seo_description = get_post_meta( $product_id, '_seo_simple_product_description', true );
		$description_length = strlen( $seo_description );
		
		if ( $description_length >= 150 && $description_length <= 160 ) {
			$analysis['description'] = array(
				'status' => 'good',
				'message' => __( 'SEO description length is optimal.', 'seo-simple' ),
				'score' => 15,
			);
			$score += 15;
		} elseif ( $description_length > 0 ) {
			$analysis['description'] = array(
				'status' => 'warning',
				'message' => sprintf( __( 'SEO description length is %d characters. Optimal: 150-160.', 'seo-simple' ), $description_length ),
				'score' => 10,
			);
			$score += 10;
		} else {
			$analysis['description'] = array(
				'status' => 'error',
				'message' => __( 'SEO description is missing.', 'seo-simple' ),
				'score' => 0,
			);
		}
		
		// Check focus keyword
		$focus_keyword = get_post_meta( $product_id, '_seo_simple_product_focus_keyword', true );
		
		if ( ! empty( $focus_keyword ) ) {
			$analysis['focus_keyword'] = array(
				'status' => 'good',
				'message' => __( 'Focus keyword is set.', 'seo-simple' ),
				'score' => 10,
			);
			$score += 10;
			
			// Check if keyword is in title
			if ( stripos( $seo_title, $focus_keyword ) !== false ) {
				$analysis['keyword_in_title'] = array(
					'status' => 'good',
					'message' => __( 'Focus keyword is in SEO title.', 'seo-simple' ),
					'score' => 10,
				);
				$score += 10;
			} else {
				$analysis['keyword_in_title'] = array(
					'status' => 'warning',
					'message' => __( 'Focus keyword is not in SEO title.', 'seo-simple' ),
					'score' => 0,
				);
			}
			
			// Check if keyword is in description
			if ( stripos( $seo_description, $focus_keyword ) !== false ) {
				$analysis['keyword_in_description'] = array(
					'status' => 'good',
					'message' => __( 'Focus keyword is in SEO description.', 'seo-simple' ),
					'score' => 10,
				);
				$score += 10;
			} else {
				$analysis['keyword_in_description'] = array(
					'status' => 'warning',
					'message' => __( 'Focus keyword is not in SEO description.', 'seo-simple' ),
					'score' => 0,
				);
			}
		} else {
			$analysis['focus_keyword'] = array(
				'status' => 'error',
				'message' => __( 'Focus keyword is missing.', 'seo-simple' ),
				'score' => 0,
			);
		}
		
		// Check product images
		$attachment_ids = $product->get_gallery_image_ids();
		$attachment_ids[] = $product->get_image_id();
		
		$images_with_alt = 0;
		$total_images = 0;
		
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id ) {
				$total_images++;
				$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				if ( ! empty( $alt_text ) ) {
					$images_with_alt++;
				}
			}
		}
		
		if ( $total_images > 0 ) {
			$alt_percentage = ( $images_with_alt / $total_images ) * 100;
			
			if ( $alt_percentage == 100 ) {
				$analysis['images_alt'] = array(
					'status' => 'good',
					'message' => __( 'All product images have alt text.', 'seo-simple' ),
					'score' => 15,
				);
				$score += 15;
			} elseif ( $alt_percentage >= 50 ) {
				$analysis['images_alt'] = array(
					'status' => 'warning',
					'message' => sprintf( __( '%d%% of product images have alt text.', 'seo-simple' ), round( $alt_percentage ) ),
					'score' => 8,
				);
				$score += 8;
			} else {
				$analysis['images_alt'] = array(
					'status' => 'error',
					'message' => sprintf( __( 'Only %d%% of product images have alt text.', 'seo-simple' ), round( $alt_percentage ) ),
					'score' => 0,
				);
			}
		} else {
			$analysis['images_alt'] = array(
				'status' => 'warning',
				'message' => __( 'No product images found.', 'seo-simple' ),
				'score' => 5,
			);
			$score += 5;
		}
		
		// Check schema data
		$brand = get_post_meta( $product_id, '_seo_simple_product_brand', true );
		$gtin = get_post_meta( $product_id, '_seo_simple_product_gtin', true );
		
		if ( ! empty( $brand ) ) {
			$analysis['brand'] = array(
				'status' => 'good',
				'message' => __( 'Product brand is set.', 'seo-simple' ),
				'score' => 5,
			);
			$score += 5;
		} else {
			$analysis['brand'] = array(
				'status' => 'warning',
				'message' => __( 'Product brand is not set.', 'seo-simple' ),
				'score' => 0,
			);
		}
		
		if ( ! empty( $gtin ) ) {
			$analysis['gtin'] = array(
				'status' => 'good',
				'message' => __( 'Product GTIN is set.', 'seo-simple' ),
				'score' => 5,
			);
			$score += 5;
		} else {
			$analysis['gtin'] = array(
				'status' => 'info',
				'message' => __( 'Product GTIN is not set (optional).', 'seo-simple' ),
				'score' => 0,
			);
		}
		
		// Calculate final score
		$analysis['score'] = array(
			'value' => $score,
			'max' => $max_score,
			'grade' => $this->get_score_grade( $score ),
		);
		
		return $analysis;
	}

	/**
	 * Auto-optimize a product.
	 *
	 * @since    1.0.0
	 * @param    int       $product_id    The product ID.
	 * @return   array                    The optimization results.
	 */
	private function auto_optimize_product( $product_id ) {
		$product = wc_get_product( $product_id );
		
		if ( ! $product ) {
			return array( 'error' => __( 'Product not found.', 'seo-simple' ) );
		}
		
		$results = array();
		
		// Auto-generate SEO title if missing
		$seo_title = get_post_meta( $product_id, '_seo_simple_product_title', true );
		if ( empty( $seo_title ) ) {
			$settings = $this->seo_meta->get_global_settings();
			$template = isset( $settings['seo_woocommerce_product_title_template'] ) ? 
				$settings['seo_woocommerce_product_title_template'] : 
				'%product_title% - %site_name%';
			
			$generated_title = str_replace(
				array( '%product_title%', '%site_name%' ),
				array( get_the_title( $product_id ), get_bloginfo( 'name' ) ),
				$template
			);
			
			update_post_meta( $product_id, '_seo_simple_product_title', $generated_title );
			$results['title_generated'] = $generated_title;
		}
		
		// Auto-generate SEO description if missing
		$seo_description = get_post_meta( $product_id, '_seo_simple_product_description', true );
		if ( empty( $seo_description ) ) {
			$settings = $this->seo_meta->get_global_settings();
			$template = isset( $settings['seo_woocommerce_product_description_template'] ) ? 
				$settings['seo_woocommerce_product_description_template'] : 
				'%product_description%';
			
			$generated_description = str_replace(
				array( '%product_description%', '%product_short_description%' ),
				array( $product->get_description(), $product->get_short_description() ),
				$template
			);
			
			// Trim to optimal length
			if ( is_string( $generated_description ) && strlen( $generated_description ) > 160 ) {
				$generated_description = substr( $generated_description, 0, 157 ) . '...';
			}
			
			update_post_meta( $product_id, '_seo_simple_product_description', $generated_description );
			$results['description_generated'] = $generated_description;
		}
		
		// Auto-generate focus keyword if missing
		$focus_keyword = get_post_meta( $product_id, '_seo_simple_product_focus_keyword', true );
		if ( empty( $focus_keyword ) ) {
			// Use product title as focus keyword
			$generated_keyword = get_the_title( $product_id );
			update_post_meta( $product_id, '_seo_simple_product_focus_keyword', $generated_keyword );
			$results['keyword_generated'] = $generated_keyword;
		}
		
		// Auto-generate alt text for images if missing
		$attachment_ids = $product->get_gallery_image_ids();
		$attachment_ids[] = $product->get_image_id();
		
		$images_updated = 0;
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id ) {
				$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				if ( empty( $alt_text ) ) {
					// Generate alt text from product title
					$generated_alt = get_the_title( $product_id );
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', $generated_alt );
					$images_updated++;
				}
			}
		}
		
		if ( $images_updated > 0 ) {
			$results['images_updated'] = $images_updated;
		}
		
		return $results;
	}

	/**
	 * Calculate product SEO score.
	 *
	 * @since    1.0.0
	 * @param    int       $product_id    The product ID.
	 * @return   int                      The SEO score.
	 */
	private function calculate_product_seo_score( $product_id ) {
		$analysis = $this->analyze_product_seo( $product_id );
		
		if ( isset( $analysis['score']['value'] ) ) {
			return $analysis['score']['value'];
		}
		
		return 0;
	}

	/**
	 * Get score grade.
	 *
	 * @since    1.0.0
	 * @param    int       $score    The score.
	 * @return   string              The grade.
	 */
	private function get_score_grade( $score ) {
		if ( $score >= 80 ) {
			return 'A';
		} elseif ( $score >= 60 ) {
			return 'B';
		} elseif ( $score >= 40 ) {
			return 'C';
		} elseif ( $score >= 20 ) {
			return 'D';
		} else {
			return 'F';
		}
	}

	/**
	 * Count products with SEO data.
	 *
	 * @since    1.0.0
	 * @return   int    The count.
	 */
	private function count_products_with_seo() {
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_seo_simple_product_title',
					'value' => '',
					'compare' => '!=',
				),
				array(
					'key' => '_seo_simple_product_description',
					'value' => '',
					'compare' => '!=',
				),
			),
		);
		
		$query = new WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Count categories with SEO data.
	 *
	 * @since    1.0.0
	 * @return   int    The count.
	 */
	private function count_categories_with_seo() {
		$args = array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'seo_title',
					'value' => '',
					'compare' => '!=',
				),
				array(
					'key' => 'seo_description',
					'value' => '',
					'compare' => '!=',
				),
			),
		);
		
		$terms = get_terms( $args );
		return count( $terms );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since    1.0.0
	 * @param    string    $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only enqueue on WooCommerce pages
		if ( $hook === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' ) {
			wp_enqueue_script(
				'seo-simple-woocommerce-admin',
				plugin_dir_url( __FILE__ ) . 'js/seo-simple-woocommerce-admin.js',
				array( 'jquery' ),
				SEO_SIMPLE_VERSION,
				true
			);
			
			wp_localize_script(
				'seo-simple-woocommerce-admin',
				'seo_simple_woocommerce',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce_analyze' => wp_create_nonce( 'seo_simple_woocommerce_analyze_product' ),
					'nonce_bulk' => wp_create_nonce( 'seo_simple_woocommerce_bulk_optimize' ),
					'text_confirm_bulk' => __( 'Are you sure you want to optimize the selected products?', 'seo-simple' ),
				)
			);
		}
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}
}