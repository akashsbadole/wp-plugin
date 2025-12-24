<?php

/**
 * Handles WooCommerce SEO functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles WooCommerce SEO functionality.
 *
 * This class is responsible for optimizing WooCommerce stores for search engines,
 * including product SEO, category SEO, and schema markup for WooCommerce content.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_WooCommerce {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The schema handler instance.
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
	public function __construct( $seo_meta, $schema_enhanced = null ) {
		$this->seo_meta = $seo_meta;
		$this->schema_enhanced = $schema_enhanced;
	}

	/**
	 * Register hooks for WooCommerce SEO.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Only run if WooCommerce is active
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		// Product SEO
		add_action( 'add_meta_boxes', array( $this, 'add_product_seo_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_product_seo_meta' ) );
		add_filter( 'woocommerce_structured_data_product', array( $this, 'enhance_product_structured_data' ), 10, 2 );
		
		// Category SEO
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_seo_fields' ) );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_seo_fields' ) );
		add_action( 'created_product_cat', array( $this, 'save_category_seo_meta' ) );
		add_action( 'edited_product_cat', array( $this, 'save_category_seo_meta' ) );
		
		// Tag SEO
		add_action( 'product_tag_add_form_fields', array( $this, 'add_tag_seo_fields' ) );
		add_action( 'product_tag_edit_form_fields', array( $this, 'edit_tag_seo_fields' ) );
		add_action( 'created_product_tag', array( $this, 'save_tag_seo_meta' ) );
		add_action( 'edited_product_tag', array( $this, 'save_tag_seo_meta' ) );
		
		// Schema markup
		add_action( 'wp_head', array( $this, 'output_woocommerce_schema' ), 20 );
		
		// Breadcrumbs
		add_filter( 'woocommerce_breadcrumb_defaults', array( $this, 'customize_breadcrumbs' ) );
		
		// Pagination
		add_action( 'wp_head', array( $this, 'add_pagination_rel_links' ) );
		
		// Remove default WooCommerce structured data if we're using our own
		add_action( 'init', array( $this, 'maybe_remove_default_structured_data' ) );
		
		// Add SEO data to REST API
		add_action( 'rest_api_init', array( $this, 'register_rest_fields' ) );
	}

	/**
	 * Add SEO meta box to products.
	 *
	 * @since    1.0.0
	 */
	public function add_product_seo_meta_box() {
		add_meta_box(
			'seo-simple-woocommerce-product',
			__( 'Product SEO', 'seo-simple' ),
			array( $this, 'render_product_seo_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render the product SEO meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_product_seo_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_simple_woocommerce_product', 'seo_simple_woocommerce_product_nonce' );
		
		// Get current meta values
		$product_title = get_post_meta( $post->ID, '_seo_simple_product_title', true );
		$product_description = get_post_meta( $post->ID, '_seo_simple_product_description', true );
		$product_focus_keyword = get_post_meta( $post->ID, '_seo_simple_product_focus_keyword', true );
		$product_schema_type = get_post_meta( $post->ID, '_seo_simple_product_schema_type', true );
		$product_brand = get_post_meta( $post->ID, '_seo_simple_product_brand', true );
		$product_gtin = get_post_meta( $post->ID, '_seo_simple_product_gtin', true );
		$product_mpn = get_post_meta( $post->ID, '_seo_simple_product_mpn', true );
		$product_condition = get_post_meta( $post->ID, '_seo_simple_product_condition', true );
		$product_availability = get_post_meta( $post->ID, '_seo_simple_product_availability', true );
		
		// Include the meta box template
		include plugin_dir_path( __FILE__ ) . '../admin/partials/meta-box-woocommerce-product.php';
	}

	/**
	 * Save product SEO meta data.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 */
	public function save_product_seo_meta( $post_id ) {
		// Check if this is a product
		if ( get_post_type( $post_id ) !== 'product' ) {
			return;
		}
		
		// Check nonce
		if ( ! isset( $_POST['seo_simple_woocommerce_product_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['seo_simple_woocommerce_product_nonce'], 'seo_simple_woocommerce_product' ) ) {
			return;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Save product SEO fields
		$fields = array(
			'_seo_simple_product_title',
			'_seo_simple_product_description',
			'_seo_simple_product_focus_keyword',
			'_seo_simple_product_schema_type',
			'_seo_simple_product_brand',
			'_seo_simple_product_gtin',
			'_seo_simple_product_mpn',
			'_seo_simple_product_condition',
			'_seo_simple_product_availability',
		);
		
		foreach ( $fields as $field ) {
			if ( isset( $_POST[$field] ) ) {
				$value = sanitize_text_field( $_POST[$field] );
				update_post_meta( $post_id, $field, $value );
			}
		}
	}

	/**
	 * Add SEO fields to product category add form.
	 *
	 * @since    1.0.0
	 */
	public function add_category_seo_fields() {
		?>
		<div class="form-field term-seo-title-wrap">
			<label for="seo-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label>
			<input name="seo_title" id="seo-title" type="text" value="" size="40" />
			<p class="description"><?php _e( 'Custom SEO title for this category.', 'seo-simple' ); ?></p>
		</div>
		<div class="form-field term-seo-description-wrap">
			<label for="seo-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label>
			<textarea name="seo_description" id="seo-description" rows="5" cols="40"></textarea>
			<p class="description"><?php _e( 'Custom SEO description for this category.', 'seo-simple' ); ?></p>
		</div>
		<div class="form-field term-seo-focus-keyword-wrap">
			<label for="seo-focus-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label>
			<input name="seo_focus_keyword" id="seo-focus-keyword" type="text" value="" size="40" />
			<p class="description"><?php _e( 'Primary keyword for this category.', 'seo-simple' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add SEO fields to product category edit form.
	 *
	 * @since    1.0.0
	 * @param    WP_Term    $term    The term object.
	 */
	public function edit_category_seo_fields( $term ) {
		$seo_title = get_term_meta( $term->term_id, 'seo_title', true );
		$seo_description = get_term_meta( $term->term_id, 'seo_description', true );
		$seo_focus_keyword = get_term_meta( $term->term_id, 'seo_focus_keyword', true );
		?>
		<tr class="form-field term-seo-title-wrap">
			<th scope="row" valign="top"><label for="seo-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label></th>
			<td>
				<input name="seo_title" id="seo-title" type="text" value="<?php echo esc_attr( $seo_title ); ?>" size="40" />
				<p class="description"><?php _e( 'Custom SEO title for this category.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-seo-description-wrap">
			<th scope="row" valign="top"><label for="seo-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label></th>
			<td>
				<textarea name="seo_description" id="seo-description" rows="5" cols="40"><?php echo esc_textarea( $seo_description ); ?></textarea>
				<p class="description"><?php _e( 'Custom SEO description for this category.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-seo-focus-keyword-wrap">
			<th scope="row" valign="top"><label for="seo-focus-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label></th>
			<td>
				<input name="seo_focus_keyword" id="seo-focus-keyword" type="text" value="<?php echo esc_attr( $seo_focus_keyword ); ?>" size="40" />
				<p class="description"><?php _e( 'Primary keyword for this category.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save category SEO meta data.
	 *
	 * @since    1.0.0
	 * @param    int       $term_id    The term ID.
	 */
	public function save_category_seo_meta( $term_id ) {
		if ( isset( $_POST['seo_title'] ) ) {
			update_term_meta( $term_id, 'seo_title', sanitize_text_field( $_POST['seo_title'] ) );
		}
		if ( isset( $_POST['seo_description'] ) ) {
			update_term_meta( $term_id, 'seo_description', sanitize_textarea_field( $_POST['seo_description'] ) );
		}
		if ( isset( $_POST['seo_focus_keyword'] ) ) {
			update_term_meta( $term_id, 'seo_focus_keyword', sanitize_text_field( $_POST['seo_focus_keyword'] ) );
		}
	}

	/**
	 * Add SEO fields to product tag add form.
	 *
	 * @since    1.0.0
	 */
	public function add_tag_seo_fields() {
		?>
		<div class="form-field term-seo-title-wrap">
			<label for="seo-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label>
			<input name="seo_title" id="seo-title" type="text" value="" size="40" />
			<p class="description"><?php _e( 'Custom SEO title for this tag.', 'seo-simple' ); ?></p>
		</div>
		<div class="form-field term-seo-description-wrap">
			<label for="seo-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label>
			<textarea name="seo_description" id="seo-description" rows="5" cols="40"></textarea>
			<p class="description"><?php _e( 'Custom SEO description for this tag.', 'seo-simple' ); ?></p>
		</div>
		<div class="form-field term-seo-focus-keyword-wrap">
			<label for="seo-focus-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label>
			<input name="seo_focus_keyword" id="seo-focus-keyword" type="text" value="" size="40" />
			<p class="description"><?php _e( 'Primary keyword for this tag.', 'seo-simple' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add SEO fields to product tag edit form.
	 *
	 * @since    1.0.0
	 * @param    WP_Term    $term    The term object.
	 */
	public function edit_tag_seo_fields( $term ) {
		$seo_title = get_term_meta( $term->term_id, 'seo_title', true );
		$seo_description = get_term_meta( $term->term_id, 'seo_description', true );
		$seo_focus_keyword = get_term_meta( $term->term_id, 'seo_focus_keyword', true );
		?>
		<tr class="form-field term-seo-title-wrap">
			<th scope="row" valign="top"><label for="seo-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label></th>
			<td>
				<input name="seo_title" id="seo-title" type="text" value="<?php echo esc_attr( $seo_title ); ?>" size="40" />
				<p class="description"><?php _e( 'Custom SEO title for this tag.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-seo-description-wrap">
			<th scope="row" valign="top"><label for="seo-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label></th>
			<td>
				<textarea name="seo_description" id="seo-description" rows="5" cols="40"><?php echo esc_textarea( $seo_description ); ?></textarea>
				<p class="description"><?php _e( 'Custom SEO description for this tag.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-seo-focus-keyword-wrap">
			<th scope="row" valign="top"><label for="seo-focus-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label></th>
			<td>
				<input name="seo_focus_keyword" id="seo-focus-keyword" type="text" value="<?php echo esc_attr( $seo_focus_keyword ); ?>" size="40" />
				<p class="description"><?php _e( 'Primary keyword for this tag.', 'seo-simple' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save tag SEO meta data.
	 *
	 * @since    1.0.0
	 * @param    int       $term_id    The term ID.
	 */
	public function save_tag_seo_meta( $term_id ) {
		if ( isset( $_POST['seo_title'] ) ) {
			update_term_meta( $term_id, 'seo_title', sanitize_text_field( $_POST['seo_title'] ) );
		}
		if ( isset( $_POST['seo_description'] ) ) {
			update_term_meta( $term_id, 'seo_description', sanitize_textarea_field( $_POST['seo_description'] ) );
		}
		if ( isset( $_POST['seo_focus_keyword'] ) ) {
			update_term_meta( $term_id, 'seo_focus_keyword', sanitize_text_field( $_POST['seo_focus_keyword'] ) );
		}
	}

	/**
	 * Enhance WooCommerce product structured data.
	 *
	 * @since    1.0.0
	 * @param    array       $data    The structured data.
	 * @param    WC_Product  $product The product object.
	 * @return   array                The enhanced structured data.
	 */
	public function enhance_product_structured_data( $data, $product ) {
		if ( ! $product || ! is_array( $data ) ) {
			return $data;
		}

		$product_id = $product->get_id();
		
		// Add custom SEO title if available
		$custom_title = get_post_meta( $product_id, '_seo_simple_product_title', true );
		if ( ! empty( $custom_title ) ) {
			$data['name'] = $custom_title;
		}
		
		// Add custom SEO description if available
		$custom_description = get_post_meta( $product_id, '_seo_simple_product_description', true );
		if ( ! empty( $custom_description ) ) {
			$data['description'] = $custom_description;
		}
		
		// Add brand if available
		$brand = get_post_meta( $product_id, '_seo_simple_product_brand', true );
		if ( ! empty( $brand ) ) {
			$data['brand'] = array(
				'@type' => 'Brand',
				'name'  => $brand,
			);
		}
		
		// Add GTIN if available
		$gtin = get_post_meta( $product_id, '_seo_simple_product_gtin', true );
		if ( ! empty( $gtin ) ) {
			$data['gtin'] = $gtin;
		}
		
		// Add MPN if available
		$mpn = get_post_meta( $product_id, '_seo_simple_product_mpn', true );
		if ( ! empty( $mpn ) ) {
			$data['mpn'] = $mpn;
		}
		
		// Add condition if available
		$condition = get_post_meta( $product_id, '_seo_simple_product_condition', true );
		if ( ! empty( $condition ) ) {
			$data['itemCondition'] = 'https://schema.org/' . ucfirst( $condition );
		}
		
		// Add availability if available
		$availability = get_post_meta( $product_id, '_seo_simple_product_availability', true );
		if ( ! empty( $availability ) ) {
			$data['availability'] = 'https://schema.org/' . ucfirst( $availability );
		}
		
		return $data;
	}

	/**
	 * Output WooCommerce schema markup.
	 *
	 * @since    1.0.0
	 */
	public function output_woocommerce_schema() {
		// Only output on WooCommerce pages
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
			return;
		}
		
		// Output shop page schema
		if ( is_shop() || is_product_category() || is_product_tag() ) {
			$this->output_shop_schema();
		}
		
		// Output product schema
		if ( is_product() ) {
			$this->output_product_schema();
		}
	}

	/**
	 * Output shop page schema.
	 *
	 * @since    1.0.0
	 */
	private function output_shop_schema() {
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'WebPage',
			'name' => get_the_title( wc_get_page_id( 'shop' ) ),
			'url' => get_permalink( wc_get_page_id( 'shop' ) ),
			'description' => get_bloginfo( 'description' ),
		);
		
		// Add breadcrumb if available
		if ( function_exists( 'woocommerce_breadcrumb' ) ) {
			$breadcrumbs = array();
			$breadcrumb = woocommerce_breadcrumb( array(
				'delimiter' => ' > ',
				'wrap_before' => '',
				'wrap_after' => '',
				'before' => '',
				'after' => '',
				'home' => false,
			) );
			
			if ( $breadcrumb ) {
				$breadcrumb_items = explode( ' > ', $breadcrumb );
				foreach ( $breadcrumb_items as $index => $item ) {
					$breadcrumbs[] = array(
						'@type' => 'ListItem',
						'position' => $index + 1,
						'name' => strip_tags( $item ),
					);
				}
				
				if ( ! empty( $breadcrumbs ) ) {
					$schema['breadcrumb'] = array(
						'@type' => 'BreadcrumbList',
						'itemListElement' => $breadcrumbs,
					);
				}
			}
		}
		
		echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
	}

	/**
	 * Output product schema.
	 *
	 * @since    1.0.0
	 */
	private function output_product_schema() {
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		// Use enhanced schema if available
		if ( $this->schema_enhanced ) {
			$schema = $this->schema_enhanced->generate_product_schema( $product );
		} else {
			$schema = $this->generate_basic_product_schema( $product );
		}
		
		if ( ! empty( $schema ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>';
		}
	}

	/**
	 * Generate basic product schema.
	 *
	 * @since    1.0.0
	 * @param    WC_Product  $product    The product object.
	 * @return   array                   The product schema.
	 */
	private function generate_basic_product_schema( $product ) {
		$product_id = $product->get_id();
		$title = get_post_meta( $product_id, '_seo_simple_product_title', true ) ?: get_the_title( $product_id );
		$description = get_post_meta( $product_id, '_seo_simple_product_description', true ) ?: $product->get_description();
		
		$schema = array(
			'@context' => 'https://schema.org',
			'@type' => 'Product',
			'name' => $title,
			'description' => $description,
			'url' => get_permalink( $product_id ),
			'sku' => $product->get_sku(),
		);
		
		// Add brand if available
		$brand = get_post_meta( $product_id, '_seo_simple_product_brand', true );
		if ( ! empty( $brand ) ) {
			$schema['brand'] = array(
				'@type' => 'Brand',
				'name' => $brand,
			);
		}
		
		// Add images
		if ( has_post_thumbnail( $product_id ) ) {
			$image_id = get_post_thumbnail_id( $product_id );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$schema['image'] = array( $image_url );
			}
		}
		
		// Add price information
		if ( $product->get_price() > 0 ) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'price' => $product->get_price(),
				'priceCurrency' => get_woocommerce_currency(),
				'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url' => get_permalink( $product_id ),
				'seller' => array(
					'@type' => 'Organization',
					'name' => get_bloginfo( 'name' ),
				),
			);
		}
		
		// Add ratings if available
		$average_rating = $product->get_average_rating();
		if ( $average_rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => $average_rating,
				'reviewCount' => $product->get_review_count(),
				'bestRating' => '5',
				'worstRating' => '1',
			);
		}
		
		return $schema;
	}

	/**
	 * Customize WooCommerce breadcrumbs.
	 *
	 * @since    1.0.0
	 * @param    array    $defaults    The default breadcrumb arguments.
	 * @return   array                The modified breadcrumb arguments.
	 */
	public function customize_breadcrumbs( $defaults ) {
		$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb seo-simple-breadcrumb" itemprop="breadcrumb">';
		$defaults['wrap_after'] = '</nav>';
		$defaults['before'] = '';
		$defaults['after'] = '';
		$defaults['home'] = false;
		
		return $defaults;
	}

	/**
	 * Add pagination rel links for WooCommerce archives.
	 *
	 * @since    1.0.0
	 */
	public function add_pagination_rel_links() {
		if ( ! is_woocommerce() ) {
			return;
		}
		
		global $wp_query;
		
		if ( $wp_query->max_num_pages > 1 ) {
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			
			// Add prev link
			if ( $paged > 1 ) {
				$prev_page = $paged - 1;
				echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $prev_page ) ) . '" />' . "\n";
			}
			
			// Add next link
			if ( $paged < $wp_query->max_num_pages ) {
				$next_page = $paged + 1;
				echo '<link rel="next" href="' . esc_url( get_pagenum_link( $next_page ) ) . '" />' . "\n";
			}
		}
	}

	/**
	 * Remove default WooCommerce structured data if we're using our own.
	 *
	 * @since    1.0.0
	 */
	public function maybe_remove_default_structured_data() {
		// Check if we should use our own structured data
		$settings = $this->seo_meta->get_global_settings();
		
		if ( isset( $settings['seo_woocommerce_use_custom_schema'] ) && $settings['seo_woocommerce_use_custom_schema'] ) {
			remove_action( 'wp_footer', array( WC()->structured_data, 'output_structured_data' ), 10 );
		}
	}

	/**
	 * Register REST API fields for WooCommerce SEO.
	 *
	 * @since    1.0.0
	 */
	public function register_rest_fields() {
		// Register product SEO fields
		register_rest_field(
			'product',
			'seo_simple_product',
			array(
				'get_callback' => array( $this, 'get_product_seo_rest' ),
				'update_callback' => array( $this, 'update_product_seo_rest' ),
				'schema' => array(
					'description' => __( 'Product SEO data.', 'seo-simple' ),
					'type' => 'object',
				),
			)
		);
		
		// Register product category SEO fields
		register_rest_field(
			'product_cat',
			'seo_simple_category',
			array(
				'get_callback' => array( $this, 'get_category_seo_rest' ),
				'update_callback' => array( $this, 'update_category_seo_rest' ),
				'schema' => array(
					'description' => __( 'Product category SEO data.', 'seo-simple' ),
					'type' => 'object',
				),
			)
		);
	}

	/**
	 * Get product SEO data for REST API.
	 *
	 * @since    1.0.0
	 * @param    array           $object     The post object.
	 * @param    string          $field_name The field name.
	 * @param    WP_REST_Request $request    The request object.
	 * @return   array                       The SEO data.
	 */
	public function get_product_seo_rest( $object, $field_name, $request ) {
		$product_id = $object['id'];
		
		return array(
			'title' => get_post_meta( $product_id, '_seo_simple_product_title', true ),
			'description' => get_post_meta( $product_id, '_seo_simple_product_description', true ),
			'focus_keyword' => get_post_meta( $product_id, '_seo_simple_product_focus_keyword', true ),
			'brand' => get_post_meta( $product_id, '_seo_simple_product_brand', true ),
			'gtin' => get_post_meta( $product_id, '_seo_simple_product_gtin', true ),
			'mpn' => get_post_meta( $product_id, '_seo_simple_product_mpn', true ),
			'condition' => get_post_meta( $product_id, '_seo_simple_product_condition', true ),
			'availability' => get_post_meta( $product_id, '_seo_simple_product_availability', true ),
		);
	}

	/**
	 * Update product SEO data for REST API.
	 *
	 * @since    1.0.0
	 * @param    mixed           $value      The value to update.
	 * @param    WP_Post         $object     The post object.
	 * @param    string          $field_name The field name.
	 * @return   bool|WP_Error               True on success, WP_Error on failure.
	 */
	public function update_product_seo_rest( $value, $object, $field_name ) {
		if ( ! current_user_can( 'edit_post', $object->ID ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to edit this product.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		// Update each field
		if ( isset( $value['title'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_title', sanitize_text_field( $value['title'] ) );
		}
		if ( isset( $value['description'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_description', sanitize_textarea_field( $value['description'] ) );
		}
		if ( isset( $value['focus_keyword'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_focus_keyword', sanitize_text_field( $value['focus_keyword'] ) );
		}
		if ( isset( $value['brand'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_brand', sanitize_text_field( $value['brand'] ) );
		}
		if ( isset( $value['gtin'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_gtin', sanitize_text_field( $value['gtin'] ) );
		}
		if ( isset( $value['mpn'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_mpn', sanitize_text_field( $value['mpn'] ) );
		}
		if ( isset( $value['condition'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_condition', sanitize_text_field( $value['condition'] ) );
		}
		if ( isset( $value['availability'] ) ) {
			update_post_meta( $object->ID, '_seo_simple_product_availability', sanitize_text_field( $value['availability'] ) );
		}
		
		return true;
	}

	/**
	 * Get category SEO data for REST API.
	 *
	 * @since    1.0.0
	 * @param    array           $object     The term object.
	 * @param    string          $field_name The field name.
	 * @param    WP_REST_Request $request    The request object.
	 * @return   array                       The SEO data.
	 */
	public function get_category_seo_rest( $object, $field_name, $request ) {
		$term_id = $object['id'];
		
		return array(
			'title' => get_term_meta( $term_id, 'seo_title', true ),
			'description' => get_term_meta( $term_id, 'seo_description', true ),
			'focus_keyword' => get_term_meta( $term_id, 'seo_focus_keyword', true ),
		);
	}

	/**
	 * Update category SEO data for REST API.
	 *
	 * @since    1.0.0
	 * @param    mixed           $value      The value to update.
	 * @param    WP_Term         $object     The term object.
	 * @param    string          $field_name The field name.
	 * @return   bool|WP_Error               True on success, WP_Error on failure.
	 */
	public function update_category_seo_rest( $value, $object, $field_name ) {
		if ( ! current_user_can( 'edit_term', $object->term_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to edit this category.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		// Update each field
		if ( isset( $value['title'] ) ) {
			update_term_meta( $object->term_id, 'seo_title', sanitize_text_field( $value['title'] ) );
		}
		if ( isset( $value['description'] ) ) {
			update_term_meta( $object->term_id, 'seo_description', sanitize_textarea_field( $value['description'] ) );
		}
		if ( isset( $value['focus_keyword'] ) ) {
			update_term_meta( $object->term_id, 'seo_focus_keyword', sanitize_text_field( $value['focus_keyword'] ) );
		}
		
		return true;
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