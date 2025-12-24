<?php
/**
 * WooCommerce product SEO meta box template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the current values with defaults
$product_title = ! empty( $product_title ) ? $product_title : '';
$product_description = ! empty( $product_description ) ? $product_description : '';
$product_focus_keyword = ! empty( $product_focus_keyword ) ? $product_focus_keyword : '';
$product_schema_type = ! empty( $product_schema_type ) ? $product_schema_type : 'Product';
$product_brand = ! empty( $product_brand ) ? $product_brand : '';
$product_gtin = ! empty( $product_gtin ) ? $product_gtin : '';
$product_mpn = ! empty( $product_mpn ) ? $product_mpn : '';
$product_condition = ! empty( $product_condition ) ? $product_condition : 'NewCondition';
$product_availability = ! empty( $product_availability ) ? $product_availability : 'InStock';
?>

<div class="seo-simple-woocommerce-product">
	<div class="seo-simple-tabs">
		<ul class="seo-simple-tab-nav">
			<li class="active"><a href="#seo-tab-basic" data-tab="basic"><?php _e( 'Basic SEO', 'seo-simple' ); ?></a></li>
			<li><a href="#seo-tab-schema" data-tab="schema"><?php _e( 'Schema', 'seo-simple' ); ?></a></li>
			<li><a href="#seo-tab-product" data-tab="product"><?php _e( 'Product Data', 'seo-simple' ); ?></a></li>
		</ul>
		
		<div class="seo-simple-tab-content">
			<!-- Basic SEO Tab -->
			<div id="seo-tab-basic" class="seo-simple-tab-pane active">
				<div class="seo-simple-field">
					<label for="seo-simple-product-title"><?php _e( 'SEO Title', 'seo-simple' ); ?></label>
					<input type="text" id="seo-simple-product-title" name="_seo_simple_product_title" value="<?php echo esc_attr( $product_title ); ?>" class="widefat" />
					<div class="seo-simple-char-counter">
						<span class="seo-simple-count"><?php echo strlen( $product_title ); ?></span> / 60
					</div>
					<p class="description"><?php _e( 'Optimal length: 50-60 characters. Leave empty to use the product title.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-description"><?php _e( 'SEO Description', 'seo-simple' ); ?></label>
					<textarea id="seo-simple-product-description" name="_seo_simple_product_description" rows="4" class="widefat"><?php echo esc_textarea( $product_description ); ?></textarea>
					<div class="seo-simple-char-counter">
						<span class="seo-simple-count"><?php echo strlen( $product_description ); ?></span> / 160
					</div>
					<p class="description"><?php _e( 'Optimal length: 150-160 characters. Leave empty to use the product description.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-focus-keyword"><?php _e( 'Focus Keyword', 'seo-simple' ); ?></label>
					<input type="text" id="seo-simple-product-focus-keyword" name="_seo_simple_product_focus_keyword" value="<?php echo esc_attr( $product_focus_keyword ); ?>" class="widefat" />
					<p class="description"><?php _e( 'The main keyword or phrase you want this product to rank for.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-seo-preview">
					<h4><?php _e( 'Google Preview', 'seo-simple' ); ?></h4>
					<div class="seo-simple-preview-container">
						<div class="seo-simple-preview-title" id="seo-simple-preview-title"><?php echo ! empty( $product_title ) ? esc_html( $product_title ) : esc_html( get_the_title() ); ?></div>
						<div class="seo-simple-preview-url"><?php echo esc_url( get_permalink() ); ?></div>
						<div class="seo-simple-preview-description" id="seo-simple-preview-description"><?php echo ! empty( $product_description ) ? esc_html( $product_description ) : esc_html( wp_trim_words( get_the_content(), 30 ) ); ?></div>
					</div>
				</div>
			</div>
			
			<!-- Schema Tab -->
			<div id="seo-tab-schema" class="seo-simple-tab-pane">
				<div class="seo-simple-field">
					<label for="seo-simple-product-schema-type"><?php _e( 'Schema Type', 'seo-simple' ); ?></label>
					<select id="seo-simple-product-schema-type" name="_seo_simple_product_schema_type" class="widefat">
						<option value="Product" <?php selected( $product_schema_type, 'Product' ); ?>><?php _e( 'Product', 'seo-simple' ); ?></option>
						<option value="Vehicle" <?php selected( $product_schema_type, 'Vehicle' ); ?>><?php _e( 'Vehicle', 'seo-simple' ); ?></option>
						<option value="Book" <?php selected( $product_schema_type, 'Book' ); ?>><?php _e( 'Book', 'seo-simple' ); ?></option>
						<option value="SoftwareApplication" <?php selected( $product_schema_type, 'SoftwareApplication' ); ?>><?php _e( 'Software Application', 'seo-simple' ); ?></option>
						<option value="Event" <?php selected( $product_schema_type, 'Event' ); ?>><?php _e( 'Event', 'seo-simple' ); ?></option>
						<option value="TouristTrip" <?php selected( $product_schema_type, 'TouristTrip' ); ?>><?php _e( 'Tourist Trip', 'seo-simple' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Select the most appropriate schema type for this product.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-brand"><?php _e( 'Brand', 'seo-simple' ); ?></label>
					<input type="text" id="seo-simple-product-brand" name="_seo_simple_product_brand" value="<?php echo esc_attr( $product_brand ); ?>" class="widefat" />
					<p class="description"><?php _e( 'The brand name of the product.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-gtin"><?php _e( 'GTIN (Global Trade Item Number)', 'seo-simple' ); ?></label>
					<input type="text" id="seo-simple-product-gtin" name="_seo_simple_product_gtin" value="<?php echo esc_attr( $product_gtin ); ?>" class="widefat" />
					<p class="description"><?php _e( 'Includes UPC, EAN, ISBN, etc.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-mpn"><?php _e( 'MPN (Manufacturer Part Number)', 'seo-simple' ); ?></label>
					<input type="text" id="seo-simple-product-mpn" name="_seo_simple_product_mpn" value="<?php echo esc_attr( $product_mpn ); ?>" class="widefat" />
					<p class="description"><?php _e( 'The manufacturer part number.', 'seo-simple' ); ?></p>
				</div>
			</div>
			
			<!-- Product Data Tab -->
			<div id="seo-tab-product" class="seo-simple-tab-pane">
				<div class="seo-simple-field">
					<label for="seo-simple-product-condition"><?php _e( 'Condition', 'seo-simple' ); ?></label>
					<select id="seo-simple-product-condition" name="_seo_simple_product_condition" class="widefat">
						<option value="NewCondition" <?php selected( $product_condition, 'NewCondition' ); ?>><?php _e( 'New', 'seo-simple' ); ?></option>
						<option value="RefurbishedCondition" <?php selected( $product_condition, 'RefurbishedCondition' ); ?>><?php _e( 'Refurbished', 'seo-simple' ); ?></option>
						<option value="UsedCondition" <?php selected( $product_condition, 'UsedCondition' ); ?>><?php _e( 'Used', 'seo-simple' ); ?></option>
						<option value="DamagedCondition" <?php selected( $product_condition, 'DamagedCondition' ); ?>><?php _e( 'Damaged', 'seo-simple' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The condition of the product.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label for="seo-simple-product-availability"><?php _e( 'Availability', 'seo-simple' ); ?></label>
					<select id="seo-simple-product-availability" name="_seo_simple_product_availability" class="widefat">
						<option value="InStock" <?php selected( $product_availability, 'InStock' ); ?>><?php _e( 'In Stock', 'seo-simple' ); ?></option>
						<option value="OutOfStock" <?php selected( $product_availability, 'OutOfStock' ); ?>><?php _e( 'Out of Stock', 'seo-simple' ); ?></option>
						<option value="PreOrder" <?php selected( $product_availability, 'PreOrder' ); ?>><?php _e( 'Pre-order', 'seo-simple' ); ?></option>
						<option value="Discontinued" <?php selected( $product_availability, 'Discontinued' ); ?>><?php _e( 'Discontinued', 'seo-simple' ); ?></option>
						<option value="InStoreOnly" <?php selected( $product_availability, 'InStoreOnly' ); ?>><?php _e( 'In Store Only', 'seo-simple' ); ?></option>
						<option value="LimitedAvailability" <?php selected( $product_availability, 'LimitedAvailability' ); ?>><?php _e( 'Limited Availability', 'seo-simple' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The availability status of the product.', 'seo-simple' ); ?></p>
				</div>
				
				<div class="seo-simple-field">
					<label><?php _e( 'Product Images SEO', 'seo-simple' ); ?></label>
					<div class="seo-simple-product-images">
						<?php
						$product = wc_get_product( $post->ID );
						if ( $product ) {
							$attachment_ids = $product->get_gallery_image_ids();
							if ( $attachment_ids ) {
								foreach ( $attachment_ids as $attachment_id ) {
									$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
									$has_alt = ! empty( $alt_text );
									?>
									<div class="seo-simple-image-item">
										<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail' ); ?>
										<div class="seo-simple-image-status <?php echo $has_alt ? 'has-alt' : 'missing-alt'; ?>">
											<?php if ( $has_alt ) : ?>
												<span class="dashicons dashicons-yes-alt"></span>
												<span><?php _e( 'Has alt text', 'seo-simple' ); ?></span>
											<?php else : ?>
												<span class="dashicons dashicons-no-alt"></span>
												<span><?php _e( 'Missing alt text', 'seo-simple' ); ?></span>
											<?php endif; ?>
										</div>
									</div>
									<?php
								}
							} else {
								echo '<p>' . __( 'No product images found.', 'seo-simple' ) . '</p>';
							}
						}
						?>
					</div>
					<p class="description"><?php _e( 'Product images should have descriptive alt text for better SEO.', 'seo-simple' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.seo-simple-woocommerce-product {
	margin: 10px 0;
}

.seo-simple-tabs {
	border: 1px solid #ddd;
	background: #fff;
}

.seo-simple-tab-nav {
	margin: 0;
	padding: 0;
	list-style: none;
	border-bottom: 1px solid #ddd;
	background: #f9f9f9;
}

.seo-simple-tab-nav li {
	display: inline-block;
	margin: 0;
	padding: 0;
}

.seo-simple-tab-nav li a {
	display: block;
	padding: 10px 15px;
	text-decoration: none;
	color: #555;
	border-right: 1px solid #ddd;
}

.seo-simple-tab-nav li a:hover {
	background: #f1f1f1;
}

.seo-simple-tab-nav li.active a {
	background: #fff;
	color: #23282d;
	border-bottom: 1px solid #fff;
	margin-bottom: -1px;
}

.seo-simple-tab-content {
	padding: 15px;
}

.seo-simple-tab-pane {
	display: none;
}

.seo-simple-tab-pane.active {
	display: block;
}

.seo-simple-field {
	margin-bottom: 15px;
}

.seo-simple-field label {
	display: block;
	font-weight: bold;
	margin-bottom: 5px;
}

.seo-simple-char-counter {
	text-align: right;
	font-size: 12px;
	color: #666;
	margin-top: 5px;
}

.seo-simple-seo-preview {
	margin-top: 20px;
	padding: 15px;
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 3px;
}

.seo-simple-preview-container {
	background: #fff;
	border: 1px solid #ddd;
	padding: 10px;
	margin-top: 10px;
}

.seo-simple-preview-title {
	color: #1a0dab;
	font-size: 18px;
	font-weight: normal;
	margin-bottom: 3px;
	cursor: pointer;
}

.seo-simple-preview-url {
	color: #006621;
	font-size: 14px;
	margin-bottom: 3px;
}

.seo-simple-preview-description {
	color: #545454;
	font-size: 13px;
	line-height: 1.4;
}

.seo-simple-product-images {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 10px;
}

.seo-simple-image-item {
	width: 80px;
	text-align: center;
}

.seo-simple-image-item img {
	max-width: 100%;
	height: auto;
	border: 1px solid #ddd;
}

.seo-simple-image-status {
	font-size: 11px;
	margin-top: 5px;
}

.seo-simple-image-status.has-alt {
	color: #46b450;
}

.seo-simple-image-status.missing-alt {
	color: #dc3232;
}

.seo-simple-image-status .dashicons {
	font-size: 14px;
	width: 14px;
	height: 14px;
	vertical-align: middle;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab functionality
	$('.seo-simple-tab-nav a').on('click', function(e) {
		e.preventDefault();
		
		var tab = $(this).data('tab');
		
		// Update nav
		$('.seo-simple-tab-nav li').removeClass('active');
		$(this).parent().addClass('active');
		
		// Update content
		$('.seo-simple-tab-pane').removeClass('active');
		$('#seo-tab-' + tab).addClass('active');
	});
	
	// Character counters
	$('#seo-simple-product-title').on('input', function() {
		var length = $(this).val().length;
		$(this).siblings('.seo-simple-char-counter').find('.seo-simple-count').text(length);
		
		// Update preview
		$('#seo-simple-preview-title').text($(this).val() || $('title').text());
	});
	
	$('#seo-simple-product-description').on('input', function() {
		var length = $(this).val().length;
		$(this).siblings('.seo-simple-char-counter').find('.seo-simple-count').text(length);
		
		// Update preview
		$('#seo-simple-preview-description').text($(this).val() || $('#seo-simple-preview-description').data('default'));
	});
	
	// Focus keyword analysis
	$('#seo-simple-product-focus-keyword').on('input', function() {
		var keyword = $(this).val().toLowerCase();
		var title = $('#seo-simple-product-title').val().toLowerCase();
		var description = $('#seo-simple-product-description').val().toLowerCase();
		
		// Check if keyword is in title
		var inTitle = title.includes(keyword);
		// Check if keyword is in description
		var inDescription = description.includes(keyword);
		
		// Update indicators (could be enhanced with more checks)
		// This is a simplified version - a full implementation would check more factors
	});
});
</script>