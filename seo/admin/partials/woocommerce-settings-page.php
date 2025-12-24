<?php
/**
 * WooCommerce SEO settings page template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<form method="post" action="">
		<?php wp_nonce_field( 'seo_simple_woocommerce_settings' ); ?>
		
		<div class="seo-simple-settings-section">
			<h2><?php _e( 'WooCommerce SEO Settings', 'seo-simple' ); ?></h2>
			<p><?php _e( 'Configure SEO settings for your WooCommerce store.', 'seo-simple' ); ?></p>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Enable WooCommerce SEO', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable" id="seo_woocommerce_enable" value="1" <?php checked( $settings['seo_woocommerce_enable'], '1' ); ?> />
							<label for="seo_woocommerce_enable"><?php _e( 'Enable SEO features for WooCommerce products and categories', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Product Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_product_schema" id="seo_woocommerce_product_schema" value="1" <?php checked( $settings['seo_woocommerce_product_schema'], '1' ); ?> />
							<label for="seo_woocommerce_product_schema"><?php _e( 'Enable Product schema markup', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Category Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_category_schema" id="seo_woocommerce_category_schema" value="1" <?php checked( $settings['seo_woocommerce_category_schema'], '1' ); ?> />
							<label for="seo_woocommerce_category_schema"><?php _e( 'Enable CollectionPage schema for product categories', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Custom Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_use_custom_schema" id="seo_woocommerce_use_custom_schema" value="1" <?php checked( $settings['seo_woocommerce_use_custom_schema'], '1' ); ?> />
							<label for="seo_woocommerce_use_custom_schema"><?php _e( 'Use custom schema markup instead of WooCommerce default', 'seo-simple' ); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="seo-simple-settings-section">
			<h3><?php _e( 'Title & Description Templates', 'seo-simple' ); ?></h3>
			<p><?php _e( 'Configure templates for automatically generating SEO titles and descriptions.', 'seo-simple' ); ?></p>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="seo_woocommerce_product_title_template"><?php _e( 'Product Title Template', 'seo-simple' ); ?></label>
						</th>
						<td>
							<input type="text" name="seo_woocommerce_product_title_template" id="seo_woocommerce_product_title_template" value="<?php echo esc_attr( $settings['seo_woocommerce_product_title_template'] ); ?>" class="regular-text" />
							<p class="description">
								<?php _e( 'Available placeholders:', 'seo-simple' ); ?> %product_title%, %site_name%, %product_category%, %product_brand%
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="seo_woocommerce_product_description_template"><?php _e( 'Product Description Template', 'seo-simple' ); ?></label>
						</th>
						<td>
							<textarea name="seo_woocommerce_product_description_template" id="seo_woocommerce_product_description_template" rows="3" class="large-text"><?php echo esc_textarea( $settings['seo_woocommerce_product_description_template'] ); ?></textarea>
							<p class="description">
								<?php _e( 'Available placeholders:', 'seo-simple' ); ?> %product_description%, %product_short_description%, %product_price%, %product_currency%
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="seo_woocommerce_category_title_template"><?php _e( 'Category Title Template', 'seo-simple' ); ?></label>
						</th>
						<td>
							<input type="text" name="seo_woocommerce_category_title_template" id="seo_woocommerce_category_title_template" value="<?php echo esc_attr( $settings['seo_woocommerce_category_title_template'] ); ?>" class="regular-text" />
							<p class="description">
								<?php _e( 'Available placeholders:', 'seo-simple' ); ?> %category_name%, %site_name%, %category_description%
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="seo_woocommerce_category_description_template"><?php _e( 'Category Description Template', 'seo-simple' ); ?></label>
						</th>
						<td>
							<textarea name="seo_woocommerce_category_description_template" id="seo_woocommerce_category_description_template" rows="3" class="large-text"><?php echo esc_textarea( $settings['seo_woocommerce_category_description_template'] ); ?></textarea>
							<p class="description">
								<?php _e( 'Available placeholders:', 'seo-simple' ); ?> %category_description%, %site_name%, %category_name%
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="seo-simple-settings-section">
			<h3><?php _e( 'Additional SEO Features', 'seo-simple' ); ?></h3>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Breadcrumbs', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable_breadcrumbs" id="seo_woocommerce_enable_breadcrumbs" value="1" <?php checked( $settings['seo_woocommerce_enable_breadcrumbs'], '1' ); ?> />
							<label for="seo_woocommerce_enable_breadcrumbs"><?php _e( 'Enable SEO-optimized breadcrumbs', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Pagination Links', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable_rel_next_prev" id="seo_woocommerce_enable_rel_next_prev" value="1" <?php checked( $settings['seo_woocommerce_enable_rel_next_prev'], '1' ); ?> />
							<label for="seo_woocommerce_enable_rel_next_prev"><?php _e( 'Add rel="next" and rel="prev" links for paginated archives', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Remove Generator Tag', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_remove_generator" id="seo_woocommerce_remove_generator" value="1" <?php checked( $settings['seo_woocommerce_remove_generator'], '1' ); ?> />
							<label for="seo_woocommerce_remove_generator"><?php _e( 'Remove WooCommerce generator meta tag', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Optimize Product Images', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_optimize_product_images" id="seo_woocommerce_optimize_product_images" value="1" <?php checked( $settings['seo_woocommerce_optimize_product_images'], '1' ); ?> />
							<label for="seo_woocommerce_optimize_product_images"><?php _e( 'Automatically add alt text to product images', 'seo-simple' ); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="seo-simple-settings-section">
			<h3><?php _e( 'Schema Markup Settings', 'seo-simple' ); ?></h3>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Product Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable_product_schema" id="seo_woocommerce_enable_product_schema" value="1" <?php checked( $settings['seo_woocommerce_enable_product_schema'], '1' ); ?> />
							<label for="seo_woocommerce_enable_product_schema"><?php _e( 'Enable Product schema markup', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Breadcrumb Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable_breadcrumb_schema" id="seo_woocommerce_enable_breadcrumb_schema" value="1" <?php checked( $settings['seo_woocommerce_enable_breadcrumb_schema'], '1' ); ?> />
							<label for="seo_woocommerce_enable_breadcrumb_schema"><?php _e( 'Enable BreadcrumbList schema markup', 'seo-simple' ); ?></label>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Organization Schema', 'seo-simple' ); ?></th>
						<td>
							<input type="checkbox" name="seo_woocommerce_enable_organization_schema" id="seo_woocommerce_enable_organization_schema" value="1" <?php checked( $settings['seo_woocommerce_enable_organization_schema'], '1' ); ?> />
							<label for="seo_woocommerce_enable_organization_schema"><?php _e( 'Enable Organization schema markup', 'seo-simple' ); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="seo-simple-settings-section">
			<h3><?php _e( 'Bulk Actions', 'seo-simple' ); ?></h3>
			<p><?php _e( 'Perform bulk SEO operations on your WooCommerce products.', 'seo-simple' ); ?></p>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Generate SEO Data', 'seo-simple' ); ?></th>
						<td>
							<p>
								<button type="button" id="seo-simple-generate-seo-data" class="button button-secondary">
									<?php _e( 'Generate SEO Data for All Products', 'seo-simple' ); ?>
								</button>
								<span class="spinner"></span>
							</p>
							<p class="description">
								<?php _e( 'Generate SEO titles, descriptions, and focus keywords for all products that are missing this data.', 'seo-simple' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Optimize Images', 'seo-simple' ); ?></th>
						<td>
							<p>
								<button type="button" id="seo-simple-optimize-images" class="button button-secondary">
									<?php _e( 'Add Alt Text to Product Images', 'seo-simple' ); ?>
								</button>
								<span class="spinner"></span>
							</p>
							<p class="description">
								<?php _e( 'Add alt text to all product images that are missing it.', 'seo-simple' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<?php submit_button( __( 'Save Changes', 'seo-simple' ), 'primary', 'submit' ); ?>
	</form>
</div>

<style>
.seo-simple-settings-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	margin: 20px 0;
	padding: 15px;
}

.seo-simple-settings-section h2 {
	margin-top: 0;
	font-size: 1.3em;
}

.seo-simple-settings-section h3 {
	margin-top: 20px;
	font-size: 1.1em;
}

.seo-simple-settings-section:first-child h3 {
	margin-top: 0;
}

.seo-simple-bulk-progress {
	margin: 10px 0;
	padding: 10px;
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 3px;
}

.seo-simple-bulk-progress-bar {
	height: 20px;
	background: #e7e7e7;
	border-radius: 10px;
	overflow: hidden;
	margin: 5px 0;
}

.seo-simple-bulk-progress-bar div {
	height: 100%;
	background: #0073aa;
	width: 0%;
	transition: width 0.3s;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Generate SEO data for all products
	$('#seo-simple-generate-seo-data').on('click', function() {
		var $button = $(this);
		var $spinner = $button.siblings('.spinner');
		
		if (!confirm('<?php echo esc_js( __( 'Are you sure you want to generate SEO data for all products? This may take a while.', 'seo-simple' ) ); ?>')) {
			return;
		}
		
		$button.prop('disabled', true);
		$spinner.addClass('is-active');
		
		// Create progress indicator
		var $progress = $('<div class="seo-simple-bulk-progress"><div class="seo-simple-bulk-progress-bar"><div></div></div><div class="seo-simple-bulk-status">Starting...</div></div>');
		$button.after($progress);
		var $progressBar = $progress.find('.seo-simple-bulk-progress-bar div');
		var $status = $progress.find('.seo-simple-bulk-status');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_woocommerce_bulk_generate_seo',
				nonce: '<?php echo wp_create_nonce( 'seo_simple_woocommerce_bulk_generate_seo' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$status.text(response.data.message);
					$progressBar.css('width', '100%');
					
					// Show success message
					$progress.after('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
				} else {
					$status.text(response.data.message || '<?php echo esc_js( __( 'An error occurred.', 'seo-simple' ) ); ?>');
					
					// Show error message
					$progress.after('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || '<?php echo esc_js( __( 'An error occurred.', 'seo-simple' ) ); ?>') + '</p></div>');
				}
			},
			error: function() {
				$status.text('<?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?>');
				
				// Show error message
				$progress.after('<div class="notice notice-error is-dismissible"><p><?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?></p></div>');
			},
			complete: function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
				
				// Remove progress after a delay
				setTimeout(function() {
					$progress.fadeOut(function() {
						$progress.remove();
					});
				}, 3000);
			}
		});
	});
	
	// Optimize product images
	$('#seo-simple-optimize-images').on('click', function() {
		var $button = $(this);
		var $spinner = $button.siblings('.spinner');
		
		if (!confirm('<?php echo esc_js( __( 'Are you sure you want to add alt text to all product images? This may take a while.', 'seo-simple' ) ); ?>')) {
			return;
		}
		
		$button.prop('disabled', true);
		$spinner.addClass('is-active');
		
		// Create progress indicator
		var $progress = $('<div class="seo-simple-bulk-progress"><div class="seo-simple-bulk-progress-bar"><div></div></div><div class="seo-simple-bulk-status">Starting...</div></div>');
		$button.after($progress);
		var $progressBar = $progress.find('.seo-simple-bulk-progress-bar div');
		var $status = $progress.find('.seo-simple-bulk-status');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_woocommerce_bulk_optimize_images',
				nonce: '<?php echo wp_create_nonce( 'seo_simple_woocommerce_bulk_optimize_images' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$status.text(response.data.message);
					$progressBar.css('width', '100%');
					
					// Show success message
					$progress.after('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
				} else {
					$status.text(response.data.message || '<?php echo esc_js( __( 'An error occurred.', 'seo-simple' ) ); ?>');
					
					// Show error message
					$progress.after('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || '<?php echo esc_js( __( 'An error occurred.', 'seo-simple' ) ); ?>') + '</p></div>');
				}
			},
			error: function() {
				$status.text('<?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?>');
				
				// Show error message
				$progress.after('<div class="notice notice-error is-dismissible"><p><?php echo esc_js( __( 'Request failed.', 'seo-simple' ) ); ?></p></div>');
			},
			complete: function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
				
				// Remove progress after a delay
				setTimeout(function() {
					$progress.fadeOut(function() {
						$progress.remove();
					});
				}, 3000);
			}
		});
	});
});
</script>