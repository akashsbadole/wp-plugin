<?php
/**
 * Business hours meta box template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$days = array(
	'monday' => __( 'Monday', 'seo-simple' ),
	'tuesday' => __( 'Tuesday', 'seo-simple' ),
	'wednesday' => __( 'Wednesday', 'seo-simple' ),
	'thursday' => __( 'Thursday', 'seo-simple' ),
	'friday' => __( 'Friday', 'seo-simple' ),
	'saturday' => __( 'Saturday', 'seo-simple' ),
	'sunday' => __( 'Sunday', 'seo-simple' ),
);
?>

<div class="seo-business-hours">
	<p class="description"><?php esc_html_e( 'Set the business hours for each day of the week.', 'seo-simple' ); ?></p>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Day', 'seo-simple' ); ?></th>
				<th><?php esc_html_e( 'Open', 'seo-simple' ); ?></th>
				<th><?php esc_html_e( 'Close', 'seo-simple' ); ?></th>
				<th><?php esc_html_e( 'Closed', 'seo-simple' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $days as $day_key => $day_label ) : ?>
				<tr>
					<td>
						<label for="seo_business_hours_<?php echo esc_attr( $day_key ); ?>_label">
							<?php echo esc_html( $day_label ); ?>
						</label>
					</td>
					<td>
						<input type="time" 
							   id="seo_business_hours_<?php echo esc_attr( $day_key ); ?>_open" 
							   name="seo_business_hours[<?php echo esc_attr( $day_key ); ?>][open]" 
							   value="<?php echo esc_attr( isset( $business_hours[$day_key]['open'] ) ? $business_hours[$day_key]['open'] : '' ); ?>" 
							   class="regular-text" />
					</td>
					<td>
						<input type="time" 
							   id="seo_business_hours_<?php echo esc_attr( $day_key ); ?>_close" 
							   name="seo_business_hours[<?php echo esc_attr( $day_key ); ?>][close]" 
							   value="<?php echo esc_attr( isset( $business_hours[$day_key]['close'] ) ? $business_hours[$day_key]['close'] : '' ); ?>" 
							   class="regular-text" />
					</td>
					<td>
						<input type="checkbox" 
							   id="seo_business_hours_<?php echo esc_attr( $day_key ); ?>_closed" 
							   name="seo_business_hours[<?php echo esc_attr( $day_key ); ?>][closed]" 
							   value="1" 
							   <?php checked( isset( $business_hours[$day_key]['closed'] ) && $business_hours[$day_key]['closed'] ); ?> />
						<label for="seo_business_hours_<?php echo esc_attr( $day_key ); ?>_closed">
							<?php esc_html_e( 'Closed all day', 'seo-simple' ); ?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<div class="seo-business-hours-actions">
		<button type="button" id="seo_set_weekday_hours" class="button"><?php esc_html_e( 'Set Weekday Hours', 'seo-simple' ); ?></button>
		<button type="button" id="seo_set_weekend_hours" class="button"><?php esc_html_e( 'Set Weekend Hours', 'seo-simple' ); ?></button>
		<button type="button" id="seo_set_all_days" class="button"><?php esc_html_e( 'Set All Days', 'seo-simple' ); ?></button>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Toggle time inputs based on closed checkbox
	$('input[name$="[closed]"]').on('change', function() {
		var $row = $(this).closest('tr');
		var $timeInputs = $row.find('input[type="time"]');
		
		if ($(this).is(':checked')) {
			$timeInputs.prop('disabled', true);
		} else {
			$timeInputs.prop('disabled', false);
		}
	}).trigger('change');
	
	// Set weekday hours
	$('#seo_set_weekday_hours').on('click', function() {
		var openTime = prompt('<?php esc_html_e( 'Enter opening time (e.g., 09:00):', 'seo-simple' ); ?>');
		if (openTime === null) return;
		
		var closeTime = prompt('<?php esc_html_e( 'Enter closing time (e.g., 17:00):', 'seo-simple' ); ?>');
		if (closeTime === null) return;
		
		var weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
		weekdays.forEach(function(day) {
			$('input[name="seo_business_hours[' + day + '][open]"]').val(openTime);
			$('input[name="seo_business_hours[' + day + '][close]"]').val(closeTime);
			$('input[name="seo_business_hours[' + day + '][closed]"]').prop('checked', false).trigger('change');
		});
	});
	
	// Set weekend hours
	$('#seo_set_weekend_hours').on('click', function() {
		var openTime = prompt('<?php esc_html_e( 'Enter opening time (e.g., 10:00):', 'seo-simple' ); ?>');
		if (openTime === null) return;
		
		var closeTime = prompt('<?php esc_html_e( 'Enter closing time (e.g., 15:00):', 'seo-simple' ); ?>');
		if (closeTime === null) return;
		
		var weekends = ['saturday', 'sunday'];
		weekends.forEach(function(day) {
			$('input[name="seo_business_hours[' + day + '][open]"]').val(openTime);
			$('input[name="seo_business_hours[' + day + '][close]"]').val(closeTime);
			$('input[name="seo_business_hours[' + day + '][closed]"]').prop('checked', false).trigger('change');
		});
	});
	
	// Set all days
	$('#seo_set_all_days').on('click', function() {
		var openTime = prompt('<?php esc_html_e( 'Enter opening time (e.g., 09:00):', 'seo-simple' ); ?>');
		if (openTime === null) return;
		
		var closeTime = prompt('<?php esc_html_e( 'Enter closing time (e.g., 17:00):', 'seo-simple' ); ?>');
		if (closeTime === null) return;
		
		$('input[name$="[open]"]').val(openTime);
		$('input[name$="[close]"]').val(closeTime);
		$('input[name$="[closed]"]').prop('checked', false).trigger('change');
	});
});
</script>