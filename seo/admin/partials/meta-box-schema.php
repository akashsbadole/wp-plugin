<?php
/**
 * Schema Meta Box Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$meta_prefix = $this->get_meta_key_prefix();
$schema_type = get_post_meta( $post->ID, '_seo_simple_schema_type', true );
$custom_schema = get_post_meta( $post->ID, '_seo_simple_custom_schema', true );
?>

<div class="seo-simple-meta-box schema-tab" id="seo-simple-schema-tab">
	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>schema_type"><?php _e( 'Schema Type', 'seo-simple' ); ?></label>
		<select 
			id="<?php echo esc_attr( $meta_prefix ); ?>schema_type" 
			name="<?php echo esc_attr( $meta_prefix ); ?>schema_type" 
			class="widefat seo-simple-schema-type">
			<option value=""><?php _e( 'Auto-detect', 'seo-simple' ); ?></option>
			<option value="article" <?php selected( $schema_type, 'article' ); ?>><?php _e( 'Article', 'seo-simple' ); ?></option>
			<option value="webpage" <?php selected( $schema_type, 'webpage' ); ?>><?php _e( 'WebPage', 'seo-simple' ); ?></option>
			<?php if ( class_exists( 'WooCommerce' ) && get_post_type( $post->ID ) === 'product' ) : ?>
				<option value="product" <?php selected( $schema_type, 'product' ); ?>><?php _e( 'Product', 'seo-simple' ); ?></option>
			<?php endif; ?>
			<option value="faq" <?php selected( $schema_type, 'faq' ); ?>><?php _e( 'FAQ', 'seo-simple' ); ?></option>
			<option value="custom" <?php selected( $schema_type, 'custom' ); ?>><?php _e( 'Custom JSON-LD', 'seo-simple' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Select the type of structured data to generate for this content.', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field seo-simple-custom-schema-field" <?php echo $schema_type === 'custom' ? '' : 'style="display: none;"'; ?>>
		<label for="<?php echo esc_attr( $meta_prefix ); ?>custom_schema"><?php _e( 'Custom JSON-LD Schema', 'seo-simple' ); ?></label>
		<textarea 
			id="<?php echo esc_attr( $meta_prefix ); ?>custom_schema" 
			name="<?php echo esc_attr( $meta_prefix ); ?>custom_schema" 
			class="widefat seo-simple-custom-schema" 
			rows="15"><?php echo esc_textarea( $custom_schema ); ?></textarea>
		<p class="description"><?php _e( 'Enter your custom JSON-LD schema. Must be valid JSON format.', 'seo-simple' ); ?></p>
		
		<div class="seo-simple-schema-actions">
			<button type="button" class="button seo-simple-validate-schema-button">
				<?php _e( 'Validate Schema', 'seo-simple' ); ?>
			</button>
			<span class="spinner seo-simple-schema-spinner"></span>
		</div>
		
		<div class="seo-simple-schema-validation-result"></div>
	</div>

	<div class="seo-simple-field" <?php echo $schema_type === 'article' ? '' : 'style="display: none;"'; ?>>
		<h4><?php _e( 'Article Schema Settings', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'Article schema will be generated automatically with the following information:', 'seo-simple' ); ?></p>
		<ul>
			<li><?php _e( 'Article title and description', 'seo-simple' ); ?></li>
			<li><?php _e( 'Author information', 'seo-simple' ); ?></li>
			<li><?php _e( 'Publish and modification dates', 'seo-simple' ); ?></li>
			<li><?php _e( 'Featured image if available', 'seo-simple' ); ?></li>
			<li><?php _e( 'Categories and tags', 'seo-simple' ); ?></li>
			<li><?php _e( 'Word count', 'seo-simple' ); ?></li>
		</ul>
	</div>

	<div class="seo-simple-field" <?php echo $schema_type === 'webpage' ? '' : 'style="display: none;"'; ?>>
		<h4><?php _e( 'WebPage Schema Settings', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'WebPage schema will be generated automatically with the following information:', 'seo-simple' ); ?></p>
		<ul>
			<li><?php _e( 'Page title and description', 'seo-simple' ); ?></li>
			<li><?php _e( 'Last modified date', 'seo-simple' ); ?></li>
			<li><?php _e( 'Breadcrumb navigation', 'seo-simple' ); ?></li>
			<li><?php _e( 'Featured image if available', 'seo-simple' ); ?></li>
			<?php if ( is_front_page() ) : ?>
				<li><?php _e( 'Search action for homepage', 'seo-simple' ); ?></li>
			<?php endif; ?>
		</ul>
	</div>

	<div class="seo-simple-field" <?php echo ( $schema_type === 'product' && class_exists( 'WooCommerce' ) ) ? '' : 'style="display: none;"'; ?>>
		<h4><?php _e( 'Product Schema Settings', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'Product schema will be generated automatically with the following information:', 'seo-simple' ); ?></p>
		<ul>
			<li><?php _e( 'Product title and description', 'seo-simple' ); ?></li>
			<li><?php _e( 'Product SKU and brand', 'seo-simple' ); ?></li>
			<li><?php _e( 'Product images', 'seo-simple' ); ?></li>
			<li><?php _e( 'Price and availability', 'seo-simple' ); ?></li>
			<li><?php _e( 'Ratings and reviews', 'seo-simple' ); ?></li>
			<li><?php _e( 'Product categories and tags', 'seo-simple' ); ?></li>
			<li><?php _e( 'Product attributes', 'seo-simple' ); ?></li>
		</ul>
	</div>

	<div class="seo-simple-field seo-simple-faq-schema-field" <?php echo $schema_type === 'faq' ? '' : 'style="display: none;"'; ?>>
		<h4><?php _e( 'FAQ Schema Settings', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'Add FAQ content to generate FAQ schema. You can use the following methods:', 'seo-simple' ); ?></p>
		
		<div class="seo-simple-faq-methods">
			<h5><?php _e( 'Method 1: FAQ Shortcode', 'seo-simple' ); ?></h5>
			<p><?php _e( 'Add the following shortcode to your content:', 'seo-simple' ); ?></p>
			<code>[seo_simple_faq question="Your Question"]Your Answer[/seo_simple_faq]</code>
			<p><?php _e( 'You can add multiple FAQ items by using the shortcode multiple times.', 'seo-simple' ); ?></p>
			
			<h5><?php _e( 'Method 2: FAQ Block', 'seo-simple' ); ?></h5>
			<p><?php _e( 'Use the FAQ block in the Gutenberg editor to add FAQ content.', 'seo-simple' ); ?></p>
			
			<h5><?php _e( 'Method 3: Auto-detection', 'seo-simple' ); ?></h5>
			<p><?php _e( 'The plugin will automatically detect FAQ content in your post and generate schema for it.', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-faq-examples">
			<h5><?php _e( 'Example FAQ Content:', 'seo-simple' ); ?></h5>
			<div class="seo-simple-faq-example">
				<h6>What is your return policy?</h6>
				<p>We offer a 30-day return policy for all unused items in their original packaging.</p>
			</div>
			<div class="seo-simple-faq-example">
				<h6>How long does shipping take?</h6>
				<p>Standard shipping typically takes 5-7 business days, while express shipping takes 2-3 business days.</p>
			</div>
		</div>
	</div>

	<div class="seo-simple-field">
		<h4><?php _e( 'Schema Preview', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'Preview the structured data that will be generated for this page:', 'seo-simple' ); ?></p>
		
		<button type="button" class="button seo-simple-preview-schema-button">
			<?php _e( 'Preview Schema', 'seo-simple' ); ?>
		</button>
		<span class="spinner seo-simple-preview-spinner"></span>
		
		<div class="seo-simple-schema-preview"></div>
	</div>
</div>

<style>
.seo-simple-meta-box .seo-simple-field {
	margin-bottom: 15px;
}
.seo-simple-meta-box label {
	display: block;
	font-weight: bold;
	margin-bottom: 5px;
}
.seo-simple-meta-box .description {
	font-size: 12px;
	color: #666;
	margin: 5px 0 0 0;
}
.seo-simple-schema-actions {
	margin-top: 10px;
}
.seo-simple-schema-validation-result {
	margin-top: 10px;
	padding: 10px;
	border-radius: 4px;
	display: none;
}
.seo-simple-schema-validation-result.success {
	background-color: #f7f9f7;
	border: 1px solid #46b450;
	color: #46b450;
}
.seo-simple-schema-validation-result.error {
	background-color: #f7f6f6;
	border: 1px solid #dc3232;
	color: #dc3232;
}
.seo-simple-faq-methods {
	background-color: #f9f9f9;
	padding: 15px;
	border-radius: 4px;
	margin-bottom: 15px;
}
.seo-simple-faq-methods h5 {
	margin-top: 0;
	margin-bottom: 10px;
}
.seo-simple-faq-examples {
	background-color: #f9f9f9;
	padding: 15px;
	border-radius: 4px;
}
.seo-simple-faq-example {
	margin-bottom: 10px;
}
.seo-simple-faq-example h6 {
	margin: 0 0 5px 0;
	font-weight: bold;
}
.seo-simple-faq-example p {
	margin: 0;
}
.seo-simple-schema-preview {
	margin-top: 15px;
	padding: 15px;
	background-color: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 4px;
	display: none;
}
.seo-simple-schema-preview pre {
	background-color: #fff;
	padding: 10px;
	border: 1px solid #ddd;
	border-radius: 4px;
	overflow: auto;
	max-height: 300px;
	font-size: 12px;
}
</style>
