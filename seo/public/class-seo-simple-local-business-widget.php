<?php

/**
 * Local Business Widget
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local Business Widget class
 *
 * This class handles the local business widget functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Local_Business_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		// Use a placeholder for now, actual translations will be loaded in form()
		parent::__construct(
			'seo_simple_local_business_widget', // Base ID
			'Local Business Info', // Name - will be translated in form()
			array( 'description' => 'Display business location information in a widget.' ) // Args - will be translated in form()
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		$location_id = isset( $instance['location_id'] ) ? intval( $instance['location_id'] ) : 0;
		$show_address = isset( $instance['show_address'] ) ? (bool) $instance['show_address'] : true;
		$show_phone = isset( $instance['show_phone'] ) ? (bool) $instance['show_phone'] : true;
		$show_email = isset( $instance['show_email'] ) ? (bool) $instance['show_email'] : true;
		$show_hours = isset( $instance['show_hours'] ) ? (bool) $instance['show_hours'] : true;
		$show_map = isset( $instance['show_map'] ) ? (bool) $instance['show_map'] : false;
		
		if ( empty( $location_id ) ) {
			echo '<p>' . __( 'Please select a location in the widget settings.', 'seo-simple' ) . '</p>';
			echo $args['after_widget'];
			return;
		}
		
		$location = get_post( $location_id );
		if ( ! $location || $location->post_type !== 'seo_location' ) {
			echo '<p>' . __( 'Selected location not found.', 'seo-simple' ) . '</p>';
			echo $args['after_widget'];
			return;
		}
		
		// Get location data
		$address = get_post_meta( $location_id, '_seo_address', true );
		$phone = get_post_meta( $location_id, '_seo_phone', true );
		$email = get_post_meta( $location_id, '_seo_email', true );
		$latitude = get_post_meta( $location_id, '_seo_latitude', true );
		$longitude = get_post_meta( $location_id, '_seo_longitude', true );
		$business_hours = get_post_meta( $location_id, '_seo_business_hours', true );
		
		echo '<div class="seo-local-business-widget">';
		
		// Business name
		echo '<h3 class="business-name">' . get_the_title( $location_id ) . '</h3>';
		
		// Address
		if ( $show_address && ! empty( $address ) ) {
			echo '<div class="business-address">' . esc_html( $address ) . '</div>';
		}
		
		// Phone
		if ( $show_phone && ! empty( $phone ) ) {
			echo '<div class="business-phone"><a href="tel:' . esc_attr( preg_replace( '/[^0-9]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a></div>';
		}
		
		// Email
		if ( $show_email && ! empty( $email ) ) {
			echo '<div class="business-email"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></div>';
		}
		
		// Business hours
		if ( $show_hours && ! empty( $business_hours ) && is_array( $business_hours ) ) {
			echo '<div class="business-hours"><h4>' . __( 'Business Hours', 'seo-simple' ) . '</h4><ul>';
			$days_map = array(
				'monday' => __( 'Monday', 'seo-simple' ),
				'tuesday' => __( 'Tuesday', 'seo-simple' ),
				'wednesday' => __( 'Wednesday', 'seo-simple' ),
				'thursday' => __( 'Thursday', 'seo-simple' ),
				'friday' => __( 'Friday', 'seo-simple' ),
				'saturday' => __( 'Saturday', 'seo-simple' ),
				'sunday' => __( 'Sunday', 'seo-simple' ),
			);
			
			foreach ( $business_hours as $day => $hours ) {
				if ( $hours['closed'] ) {
					echo '<li>' . $days_map[$day] . ': ' . __( 'Closed', 'seo-simple' ) . '</li>';
				} elseif ( ! empty( $hours['open'] ) && ! empty( $hours['close'] ) ) {
					echo '<li>' . $days_map[$day] . ': ' . $hours['open'] . ' - ' . $hours['close'] . '</li>';
				}
			}
			echo '</ul></div>';
		}
		
		// Map
		if ( $show_map && ! empty( $latitude ) && ! empty( $longitude ) ) {
			echo '<div class="business-map">';
			echo '<iframe 
				width="100%" 
				height="200" 
				frameborder="0" 
				style="border:0" 
				src="https://www.openstreetmap.org/export/embed.html?bbox=' . 
				(floatval($longitude) - 0.005) . ',' . 
				(floatval($latitude) - 0.005) . ',' . 
				(floatval($longitude) + 0.005) . ',' . 
				(floatval($latitude) + 0.005) . 
				'&layer=mapnik&marker=' . $latitude . ',' . $longitude . '" 
				allowfullscreen>
			</iframe>';
			echo '</div>';
		}
		
		echo '</div>';
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Our Location', 'seo-simple' );
		$location_id = isset( $instance['location_id'] ) ? intval( $instance['location_id'] ) : 0;
		$show_address = isset( $instance['show_address'] ) ? (bool) $instance['show_address'] : true;
		$show_phone = isset( $instance['show_phone'] ) ? (bool) $instance['show_phone'] : true;
		$show_email = isset( $instance['show_email'] ) ? (bool) $instance['show_email'] : true;
		$show_hours = isset( $instance['show_hours'] ) ? (bool) $instance['show_hours'] : true;
		$show_map = isset( $instance['show_map'] ) ? (bool) $instance['show_map'] : false;
		
		// Get all locations
		$locations = get_posts( array(
			'post_type' => 'seo_location',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		) );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'seo-simple' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'location_id' ) ); ?>"><?php esc_attr_e( 'Location:', 'seo-simple' ); ?></label> 
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'location_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'location_id' ) ); ?>">
				<option value="0"><?php esc_html_e( 'Select a location', 'seo-simple' ); ?></option>
				<?php foreach ( $locations as $location ) : ?>
					<option value="<?php echo esc_attr( $location->ID ); ?>" <?php selected( $location_id, $location->ID ); ?>>
						<?php echo esc_html( $location->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_address ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_address' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_address' ) ); ?>"><?php esc_attr_e( 'Show address', 'seo-simple' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_phone ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_phone' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_phone' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_phone' ) ); ?>"><?php esc_attr_e( 'Show phone', 'seo-simple' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_email ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_email' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_email' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_email' ) ); ?>"><?php esc_attr_e( 'Show email', 'seo-simple' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_hours ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_hours' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_hours' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_hours' ) ); ?>"><?php esc_attr_e( 'Show business hours', 'seo-simple' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_map ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_map' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_map' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_map' ) ); ?>"><?php esc_attr_e( 'Show map', 'seo-simple' ); ?></label>
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['location_id'] = ( ! empty( $new_instance['location_id'] ) ) ? intval( $new_instance['location_id'] ) : 0;
		$instance['show_address'] = isset( $new_instance['show_address'] ) ? (bool) $new_instance['show_address'] : false;
		$instance['show_phone'] = isset( $new_instance['show_phone'] ) ? (bool) $new_instance['show_phone'] : false;
		$instance['show_email'] = isset( $new_instance['show_email'] ) ? (bool) $new_instance['show_email'] : false;
		$instance['show_hours'] = isset( $new_instance['show_hours'] ) ? (bool) $new_instance['show_hours'] : false;
		$instance['show_map'] = isset( $new_instance['show_map'] ) ? (bool) $new_instance['show_map'] : false;

		return $instance;
	}
}