<?php
/**
 * Local business schema meta box template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="seo-local-business-schema">
	<div class="form-field">
		<label>
			<input type="checkbox" id="seo_enable_local_business_schema" name="seo_enable_local_business_schema" value="1" <?php checked( $enable_schema ); ?> />
			<?php esc_html_e( 'Enable Local Business Schema', 'seo-simple' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'Check this box to add local business schema markup to this page.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field" id="seo_business_location_field">
		<label for="seo_business_location"><?php esc_html_e( 'Business Location', 'seo-simple' ); ?></label>
		<select id="seo_business_location" name="seo_business_location" class="widefat">
			<option value=""><?php esc_html_e( 'Select a location', 'seo-simple' ); ?></option>
			<?php foreach ( $locations as $location ) : ?>
				<option value="<?php echo esc_attr( $location->ID ); ?>" <?php selected( $business_location, $location->ID ); ?>>
					<?php echo esc_html( $location->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the business location to use for schema markup.', 'seo-simple' ); ?>
			<?php if ( empty( $locations ) ) : ?>
				<br>
				<strong><?php esc_html_e( 'No locations found. Please create a location first.', 'seo-simple' ); ?></strong>
			<?php endif; ?>
		</p>
	</div>

	<?php if ( ! empty( $business_location ) ) : ?>
		<div class="seo-location-preview">
			<h4><?php esc_html_e( 'Location Preview', 'seo-simple' ); ?></h4>
			<div id="seo-location-preview-content">
				<?php
				$location = get_post( $business_location );
				if ( $location ) :
					$address = get_post_meta( $location->ID, '_seo_address', true );
					$phone = get_post_meta( $location->ID, '_seo_phone', true );
					$business_type = get_post_meta( $location->ID, '_seo_business_type', true );
				?>
					<p><strong><?php echo esc_html( $location->post_title ); ?></strong></p>
					<?php if ( $business_type ) : ?>
						<p><?php esc_html_e( 'Type:', 'seo-simple' ); ?> <?php echo esc_html( $business_type ); ?></p>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<p><?php echo esc_html( $address ); ?></p>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<p><?php esc_html_e( 'Phone:', 'seo-simple' ); ?> <?php echo esc_html( $phone ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
	// Toggle business location field based on schema checkbox
	$('#seo_enable_local_business_schema').on('change', function() {
		if ($(this).is(':checked')) {
			$('#seo_business_location_field').show();
		} else {
			$('#seo_business_location_field').hide();
		}
	}).trigger('change');
	
	// Update location preview when selection changes
	$('#seo_business_location').on('change', function() {
		var locationId = $(this).val();
		if (!locationId) {
			$('#seo-location-preview-content').html('');
			return;
		}
		
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'seo_simple_get_location_preview',
				location_id: locationId,
				nonce: '<?php echo wp_create_nonce( 'seo_simple_location_preview' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$('#seo-location-preview-content').html(response.data.html);
				} else {
					$('#seo-location-preview-content').html('<p><?php esc_html_e( 'Error loading location preview.', 'seo-simple' ); ?></p>');
				}
			},
			error: function() {
				$('#seo-location-preview-content').html('<p><?php esc_html_e( 'Error loading location preview.', 'seo-simple' ); ?></p>');
			}
		});
	});
});
</script>