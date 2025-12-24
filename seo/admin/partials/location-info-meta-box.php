<?php
/**
 * Location information meta box template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Business types for local business schema
$business_types = array(
	'' => __( 'Select Business Type', 'seo-simple' ),
	'LocalBusiness' => __( 'Local Business', 'seo-simple' ),
	'Restaurant' => __( 'Restaurant', 'seo-simple' ),
	'ProfessionalService' => __( 'Professional Service', 'seo-simple' ),
	'Store' => __( 'Store', 'seo-simple' ),
	'HomeAndConstructionBusiness' => __( 'Home & Construction', 'seo-simple' ),
	'AutomotiveBusiness' => __( 'Automotive Business', 'seo-simple' ),
	'HealthAndBeautyBusiness' => __( 'Health & Beauty', 'seo-simple' ),
	'FinancialService' => __( 'Financial Service', 'seo-simple' ),
	'LegalService' => __( 'Legal Service', 'seo-simple' ),
	'LodgingBusiness' => __( 'Lodging Business', 'seo-simple' ),
	'EntertainmentBusiness' => __( 'Entertainment Business', 'seo-simple' ),
);
?>

<div class="seo-location-info">
	<div class="form-field">
		<label for="seo_business_type"><?php esc_html_e( 'Business Type', 'seo-simple' ); ?></label>
		<select id="seo_business_type" name="seo_business_type" class="widefat">
			<?php foreach ( $business_types as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $business_type, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Select the type of business for schema markup.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_address"><?php esc_html_e( 'Address', 'seo-simple' ); ?></label>
		<textarea id="seo_address" name="seo_address" class="widefat" rows="3"><?php echo esc_textarea( $address ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Enter the full business address. This will be used in the schema markup.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_latitude"><?php esc_html_e( 'Latitude', 'seo-simple' ); ?></label>
		<input type="text" id="seo_latitude" name="seo_latitude" value="<?php echo esc_attr( $latitude ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the latitude coordinates for the business location.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_longitude"><?php esc_html_e( 'Longitude', 'seo-simple' ); ?></label>
		<input type="text" id="seo_longitude" name="seo_longitude" value="<?php echo esc_attr( $longitude ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the longitude coordinates for the business location.', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<label for="seo_price_range"><?php esc_html_e( 'Price Range', 'seo-simple' ); ?></label>
		<input type="text" id="seo_price_range" name="seo_price_range" value="<?php echo esc_attr( $price_range ); ?>" class="widefat" />
		<p class="description"><?php esc_html_e( 'Enter the price range (e.g., $, $$, $$$).', 'seo-simple' ); ?></p>
	</div>

	<div class="form-field">
		<button type="button" id="seo_get_coordinates" class="button"><?php esc_html_e( 'Get Coordinates from Address', 'seo-simple' ); ?></button>
		<span class="spinner"></span>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#seo_get_coordinates').on('click', function() {
		var address = $('#seo_address').val();
		if (!address) {
			alert('<?php esc_html_e( 'Please enter an address first.', 'seo-simple' ); ?>');
			return;
		}
		
		$(this).next('.spinner').addClass('is-active');
		
		// Use Nominatim API for geocoding
		$.ajax({
			url: 'https://nominatim.openstreetmap.org/search',
			method: 'GET',
			data: {
				q: address,
				format: 'json',
				addressdetails: 1,
				limit: 1
			},
			success: function(data) {
				if (data && data.length > 0) {
					$('#seo_latitude').val(data[0].lat);
					$('#seo_longitude').val(data[0].lon);
				} else {
					alert('<?php esc_html_e( 'Could not find coordinates for the provided address.', 'seo-simple' ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( 'Error retrieving coordinates. Please try again.', 'seo-simple' ); ?>');
			},
			complete: function() {
				$('#seo_get_coordinates').next('.spinner').removeClass('is-active');
			}
		});
	});
});
</script>