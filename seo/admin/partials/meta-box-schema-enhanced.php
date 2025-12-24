<?php
/**
 * Enhanced Schema Meta Box Template
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
$schema_types = $this->schema->get_schema_types();
?>

<div class="seo-simple-meta-box schema-enhanced-tab" id="seo-simple-schema-enhanced-tab">
	<div class="seo-simple-field">
		<label for="<?php echo esc_attr( $meta_prefix ); ?>schema_type"><?php _e( 'Schema Type', 'seo-simple' ); ?></label>
		<select 
			id="<?php echo esc_attr( $meta_prefix ); ?>schema_type" 
			name="<?php echo esc_attr( $meta_prefix ); ?>schema_type" 
			class="widefat seo-simple-schema-type-enhanced">
			<option value=""><?php _e( 'Auto-detect', 'seo-simple' ); ?></option>
			<?php foreach ( $schema_types as $type => $info ) : ?>
				<?php 
				// Skip product type if WooCommerce is not active
				if ( isset( $info['requires'] ) && $info['requires'] === 'woocommerce' && ! class_exists( 'WooCommerce' ) ) {
					continue;
				}
				?>
				<option value="<?php echo esc_attr( strtolower( $type ) ); ?>" <?php selected( $schema_type, strtolower( $type ) ); ?>>
					<?php echo esc_html( $info['name'] ); ?>
				</option>
			<?php endforeach; ?>
			<option value="custom" <?php selected( $schema_type, 'custom' ); ?>><?php _e( 'Custom JSON-LD', 'seo-simple' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Select the type of structured data to generate for this content.', 'seo-simple' ); ?></p>
	</div>

	<div class="seo-simple-field seo-simple-schema-fields-container">
		<?php if ( ! empty( $schema_type ) && $schema_type !== 'custom' ) : ?>
			<?php $this->render_schema_fields( $schema_type, $post->ID ); ?>
		<?php endif; ?>
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

	<div class="seo-simple-field">
		<h4><?php _e( 'Schema Preview', 'seo-simple' ); ?></h4>
		<p class="description"><?php _e( 'Preview the structured data that will be generated for this page:', 'seo-simple' ); ?></p>
		
		<button type="button" class="button seo-simple-preview-schema-button" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
			<?php _e( 'Preview Schema', 'seo-simple' ); ?>
		</button>
		<span class="spinner seo-simple-preview-spinner"></span>
		
		<div class="seo-simple-schema-preview"></div>
	</div>

	<?php wp_nonce_field( 'seo_simple_schema_save', 'seo_simple_schema_nonce' ); ?>
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
.seo-simple-schema-fields-container {
	background-color: #f9f9f9;
	padding: 15px;
	border-radius: 4px;
	margin-bottom: 15px;
}
.seo-simple-schema-fields-container .seo-simple-field {
	margin-bottom: 10px;
}
.seo-simple-schema-fields-container .seo-simple-field:last-child {
	margin-bottom: 0;
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
.seo-simple-schema-enhanced-tab .seo-simple-field-group {
	border: 1px solid #ddd;
	border-radius: 4px;
	padding: 15px;
	margin-bottom: 15px;
	background-color: #fff;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-group h4 {
	margin-top: 0;
	margin-bottom: 15px;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-group .seo-simple-field {
	margin-bottom: 10px;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-group .seo-simple-field:last-child {
	margin-bottom: 0;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-help {
	font-size: 12px;
	color: #666;
	font-style: italic;
	margin-top: 5px;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-row {
	display: flex;
	gap: 15px;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-row .seo-simple-field {
	flex: 1;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-half {
	width: 50%;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-third {
	width: 33.33%;
}
.seo-simple-schema-enhanced-tab .seo-simple-field-quarter {
	width: 25%;
}
@media screen and (max-width: 782px) {
	.seo-simple-schema-enhanced-tab .seo-simple-field-row {
		flex-direction: column;
		gap: 0;
	}
	.seo-simple-schema-enhanced-tab .seo-simple-field-half,
	.seo-simple-schema-enhanced-tab .seo-simple-field-third,
	.seo-simple-schema-enhanced-tab .seo-simple-field-quarter {
		width: 100%;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Schema type change handler
	$('#seo_simple_schema_type').on('change', function() {
		var schemaType = $(this).val();
		var postId = $('#post_ID').val();
		
		// Hide all schema-specific fields
		$('.seo-simple-custom-schema-field').hide();
		$('.seo-simple-schema-fields-container').hide();
		
		if (schemaType === 'custom') {
			$('.seo-simple-custom-schema-field').show();
		} else if (schemaType !== '') {
			// Show loading spinner
			$('.seo-simple-schema-fields-container').html('<div class="spinner is-active"></div>');
			$('.seo-simple-schema-fields-container').show();
			
			// AJAX request to get schema fields
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'seo_simple_get_schema_fields',
					schema_type: schemaType,
					post_id: postId,
					nonce: seoSimpleSchemaAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						$('.seo-simple-schema-fields-container').html(response.data.html);
					} else {
						$('.seo-simple-schema-fields-container').html('<p>' + response.data.message + '</p>');
					}
				},
				error: function() {
					$('.seo-simple-schema-fields-container').html('<p><?php echo esc_js( __( 'Error loading schema fields.', 'seo-simple' ) ); ?></p>');
				}
			});
		}
	});
	
	// Validate schema button handler
	$('.seo-simple-validate-schema-button').on('click', function() {
		var schema = $('#seo_simple_custom_schema').val();
		
		if (!schema) {
			$('.seo-simple-schema-validation-result')
				.removeClass('success')
				.addClass('error')
				.text('<?php echo esc_js( __( 'Please enter schema data.', 'seo-simple' ) ); ?>')
				.show();
			return;
		}
		
		// Show spinner
		$('.seo-simple-schema-spinner').addClass('is-active');
		
		// AJAX request to validate schema
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_validate_schema',
				schema: schema,
				nonce: seoSimpleSchemaAdmin.nonce
			},
			success: function(response) {
				$('.seo-simple-schema-spinner').removeClass('is-active');
				
				if (response.success) {
					$('.seo-simple-schema-validation-result')
						.removeClass('error')
						.addClass('success')
						.text(response.data.message)
						.show();
				} else {
					$('.seo-simple-schema-validation-result')
						.removeClass('success')
						.addClass('error')
						.text(response.data.message)
						.show();
				}
			},
			error: function() {
				$('.seo-simple-schema-spinner').removeClass('is-active');
				$('.seo-simple-schema-validation-result')
					.removeClass('success')
					.addClass('error')
					.text('<?php echo esc_js( __( 'Error validating schema.', 'seo-simple' ) ); ?>')
					.show();
			}
		});
	});
	
	// Preview schema button handler
	$('.seo-simple-preview-schema-button').on('click', function() {
		var postId = $(this).data('post-id');
		var schemaType = $('#seo_simple_schema_type').val();
		
		if (!schemaType) {
			$('.seo-simple-schema-preview')
				.html('<p><?php echo esc_js( __( 'Please select a schema type.', 'seo-simple' ) ); ?></p>')
				.show();
			return;
		}
		
		// Show spinner
		$('.seo-simple-preview-spinner').addClass('is-active');
		
		// AJAX request to preview schema
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_preview_schema',
				schema_type: schemaType,
				post_id: postId,
				nonce: seoSimpleSchemaAdmin.nonce
			},
			success: function(response) {
				$('.seo-simple-preview-spinner').removeClass('is-active');
				
				if (response.success) {
					$('.seo-simple-schema-preview')
						.html('<pre>' + response.data.schema + '</pre>')
						.show();
				} else {
					$('.seo-simple-schema-preview')
						.html('<p>' + response.data.message + '</p>')
						.show();
				}
			},
			error: function() {
				$('.seo-simple-preview-spinner').removeClass('is-active');
				$('.seo-simple-schema-preview')
					.html('<p><?php echo esc_js( __( 'Error generating schema preview.', 'seo-simple' ) ); ?></p>')
					.show();
			}
		});
	});
});
</script>
