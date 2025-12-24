<?php
/**
 * WooCommerce product SEO meta box template.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
?>

<div class="seo-simple-woocommerce-meta">
	<div class="seo-simple-form-row">
		<label for="_seo_simple_product_title" class="seo-simple-label">
			<?php _e( 'Product SEO Title', 'seo-simple' ); ?>
		</label>
		<input type="text" id="_seo_simple_product_title" name="_seo_simple_product_title" 
		       value="<?php echo esc_attr( $product_seo_title ); ?>" 
		       placeholder="<?php echo esc_attr( $meta_data['title_preview'] ); ?>" 
		       class="seo-simple-input widefat" />
		<p class="seo-simple-description">
			<?php _e( 'Custom SEO title for the product. Leave empty to use the default SEO title.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label for="_seo_simple_product_description" class="seo-simple-label">
			<?php _e( 'Product SEO Description', 'seo-simple' ); ?>
		</label>
		<textarea id="_seo_simple_product_description" name="_seo_simple_product_description" 
		          rows="3" class="seo-simple-input widefat" 
		          placeholder="<?php echo esc_attr( $meta_data['description_preview'] ); ?>"><?php echo esc_textarea( $product_seo_description ); ?></textarea>
		<p class="seo-simple-description">
			<?php _e( 'Custom SEO description for the product. Leave empty to use the default SEO description.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<hr />
	
	<div class="seo-simple-form-row">
		<h3><?php _e( 'Product Schema Information', 'seo-simple' ); ?></h3>
		<p class="seo-simple-description">
			<?php _e( 'This information will be used to generate rich schema for search engines.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label for="seo-simple-brand" class="seo-simple-label">
			<?php _e( 'Product Brand', 'seo-simple' ); ?>
		</label>
		<input type="text" id="seo-simple-brand" name="seo-simple-brand" 
		       value="<?php echo esc_attr( $product->get_attribute( 'brand' ) ); ?>" 
		       class="seo-simple-input widefat" />
		<p class="seo-simple-description">
			<?php _e( 'The brand or manufacturer of the product.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label for="seo-simple-mpn" class="seo-simple-label">
			<?php _e( 'Product MPN', 'seo-simple' ); ?>
		</label>
		<input type="text" id="seo-simple-mpn" name="seo-simple-mpn" 
		       value="<?php echo esc_attr( get_post_meta( $post->ID, '_seo_simple_mpn', true ) ); ?>" 
		       class="seo-simple-input widefat" />
		<p class="seo-simple-description">
			<?php _e( 'The Manufacturer Part Number (MPN) of the product.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label for="seo-simple-gtin" class="seo-simple-label">
			<?php _e( 'Product GTIN', 'seo-simple' ); ?>
		</label>
		<input type="text" id="seo-simple-gtin" name="seo-simple-gtin" 
		       value="<?php echo esc_attr( get_post_meta( $post->ID, '_seo_simple_gtin', true ) ); ?>" 
		       class="seo-simple-input widefat" />
		<p class="seo-simple-description">
			<?php _e( 'The Global Trade Item Number (GTIN, UPC, EAN, etc.) of the product.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label for="seo-simple-condition" class="seo-simple-label">
			<?php _e( 'Product Condition', 'seo-simple' ); ?>
		</label>
		<select id="seo-simple-condition" name="seo-simple-condition" class="seo-simple-input widefat">
			<option value="NewCondition" <?php selected( get_post_meta( $post->ID, '_seo_simple_condition', true ), 'NewCondition' ); ?>>
				<?php _e( 'New', 'seo-simple' ); ?>
			</option>
			<option value="RefurbishedCondition" <?php selected( get_post_meta( $post->ID, '_seo_simple_condition', true ), 'RefurbishedCondition' ); ?>>
				<?php _e( 'Refurbished', 'seo-simple' ); ?>
			</option>
			<option value="UsedCondition" <?php selected( get_post_meta( $post->ID, '_seo_simple_condition', true ), 'UsedCondition' ); ?>>
				<?php _e( 'Used', 'seo-simple' ); ?>
			</option>
			<option value="DamagedCondition" <?php selected( get_post_meta( $post->ID, '_seo_simple_condition', true ), 'DamagedCondition' ); ?>>
				<?php _e( 'Damaged', 'seo-simple' ); ?>
			</option>
		</select>
		<p class="seo-simple-description">
			<?php _e( 'The condition of the product.', 'seo-simple' ); ?>
		</p>
	</div>
	
	<div class="seo-simple-form-row">
		<label class="seo-simple-label">
			<?php _e( 'Schema Preview', 'seo-simple' ); ?>
		</label>
		<div class="seo-simple-schema-preview">
			<button type="button" class="button seo-simple-generate-schema" id="seo-simple-generate-product-schema">
				<?php _e( 'Generate Schema Preview', 'seo-simple' ); ?>
			</button>
			<div class="seo-simple-schema-output" id="seo-simple-product-schema-output"></div>
		</div>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		// Generate schema preview
		$('#seo-simple-generate-product-schema').on('click', function(e) {
			e.preventDefault();
			
			var button = $(this);
			var output = $('#seo-simple-product-schema-output');
			
			button.prop('disabled', true);
			output.empty();
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'seo_simple_generate_product_schema',
					post_id: '<?php echo $post->ID; ?>',
					nonce: '<?php echo wp_create_nonce( 'seo_simple_generate_schema' ); ?>'
				},
				success: function(response) {
					if (response.success) {
						output.html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
					} else {
						output.html('<div class="error">' + response.data + '</div>');
					}
				},
				error: function() {
					output.html('<div class="error"><?php _e( 'Error generating schema.', 'seo-simple' ); ?></div>');
				},
				complete: function() {
					button.prop('disabled', false);
				}
			});
		});
	});
	</script>
	
	<style>
	.seo-simple-woocommerce-meta .seo-simple-form-row {
		margin-bottom: 15px;
	}
	
	.seo-simple-label {
		display: block;
		font-weight: bold;
		margin-bottom: 5px;
	}
	
	.seo-simple-input {
		width: 100%;
	}
	
	.seo-simple-description {
		font-size: 12px;
		color: #666;
		margin: 5px 0 0 0;
	}
	
	.seo-simple-schema-preview {
		margin-top: 10px;
	}
	
	.seo-simple-schema-output {
		margin-top: 10px;
		max-height: 300px;
		overflow: auto;
		background: #f9f9f9;
		border: 1px solid #ddd;
		padding: 10px;
	}
	
	.seo-simple-schema-output pre {
		margin: 0;
	}
	</style>
</div>