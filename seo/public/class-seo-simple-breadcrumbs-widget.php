<?php

/**
 * Breadcrumbs Widget
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumbs Widget class
 *
 * This class handles the breadcrumbs widget functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Breadcrumbs_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		// Use a placeholder for now, actual translations will be loaded in form()
		parent::__construct(
			'seo_simple_breadcrumbs_widget', // Base ID
			'SEO Breadcrumbs', // Name - will be translated in form()
			array( 'description' => 'Display SEO optimized breadcrumbs in a widget.' ) // Args - will be translated in form()
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
		// Don't display breadcrumbs on homepage if disabled
		if ( is_front_page() && isset( $instance['show_on_home'] ) && ! $instance['show_on_home'] ) {
			return;
		}
		
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		// Get breadcrumbs
		$breadcrumbs = Seo_Simple_Breadcrumbs::get_instance();
		$breadcrumb_args = array();
		
		// Override settings with widget settings
		if ( isset( $instance['separator'] ) && ! empty( $instance['separator'] ) ) {
			$breadcrumb_args['separator'] = $instance['separator'];
		}
		
		if ( isset( $instance['home_text'] ) && ! empty( $instance['home_text'] ) ) {
			$breadcrumb_args['home_text'] = $instance['home_text'];
		}
		
		if ( isset( $instance['show_current'] ) ) {
			$breadcrumb_args['show_current'] = (bool) $instance['show_current'];
		}
		
		echo $breadcrumbs->get_breadcrumbs( $breadcrumb_args );
		
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$separator = ! empty( $instance['separator'] ) ? $instance['separator'] : '';
		$home_text = ! empty( $instance['home_text'] ) ? $instance['home_text'] : '';
		$show_current = isset( $instance['show_current'] ) ? (bool) $instance['show_current'] : true;
		$show_on_home = isset( $instance['show_on_home'] ) ? (bool) $instance['show_on_home'] : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'seo-simple' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'separator' ) ); ?>"><?php esc_attr_e( 'Separator:', 'seo-simple' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'separator' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'separator' ) ); ?>" type="text" value="<?php echo esc_attr( $separator ); ?>">
			<small><?php esc_html_e( 'Leave empty to use default separator.', 'seo-simple' ); ?></small>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'home_text' ) ); ?>"><?php esc_attr_e( 'Home Text:', 'seo-simple' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'home_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'home_text' ) ); ?>" type="text" value="<?php echo esc_attr( $home_text ); ?>">
			<small><?php esc_html_e( 'Leave empty to use default text.', 'seo-simple' ); ?></small>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_current ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_current' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_current' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_current' ) ); ?>"><?php esc_attr_e( 'Show current page', 'seo-simple' ); ?></label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_on_home ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_on_home' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_on_home' ) ); ?>">
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_on_home' ) ); ?>"><?php esc_attr_e( 'Show on homepage', 'seo-simple' ); ?></label>
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
		$instance['separator'] = ( ! empty( $new_instance['separator'] ) ) ? sanitize_text_field( $new_instance['separator'] ) : '';
		$instance['home_text'] = ( ! empty( $new_instance['home_text'] ) ) ? sanitize_text_field( $new_instance['home_text'] ) : '';
		$instance['show_current'] = isset( $new_instance['show_current'] ) ? (bool) $new_instance['show_current'] : false;
		$instance['show_on_home'] = isset( $new_instance['show_on_home'] ) ? (bool) $new_instance['show_on_home'] : false;

		return $instance;
	}
}