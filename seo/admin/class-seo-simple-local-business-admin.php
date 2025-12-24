<?php

/**
 * Local Business Admin AJAX handlers
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Local Business Admin class
 *
 * This class handles AJAX requests for the local business functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Local_Business_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Handle AJAX requests
		add_action( 'wp_ajax_seo_simple_get_location_preview', array( $this, 'ajax_get_location_preview' ) );
		add_action( 'wp_ajax_seo_simple_get_coordinates', array( $this, 'ajax_get_coordinates' ) );
	}

	/**
	 * Handle AJAX request for location preview.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_location_preview() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_location_preview', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$location_id = isset( $_POST['location_id'] ) ? intval( $_POST['location_id'] ) : 0;
		
		if ( empty( $location_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid location ID.', 'seo-simple' ) ) );
		}
		
		$location = get_post( $location_id );
		if ( ! $location || $location->post_type !== 'seo_location' ) {
			wp_send_json_error( array( 'message' => __( 'Location not found.', 'seo-simple' ) ) );
		}
		
		// Get location data
		$address = get_post_meta( $location_id, '_seo_address', true );
		$phone = get_post_meta( $location_id, '_seo_phone', true );
		$email = get_post_meta( $location_id, '_seo_email', true );
		$business_type = get_post_meta( $location_id, '_seo_business_type', true );
		$business_hours = get_post_meta( $location_id, '_seo_business_hours', true );
		
		// Build preview HTML
		$html = '<p><strong>' . esc_html( $location->post_title ) . '</strong></p>';
		
		if ( $business_type ) {
			$html .= '<p>' . esc_html__( 'Type:', 'seo-simple' ) . ' ' . esc_html( $business_type ) . '</p>';
		}
		
		if ( $address ) {
			$html .= '<p>' . esc_html( $address ) . '</p>';
		}
		
		if ( $phone ) {
			$html .= '<p>' . esc_html__( 'Phone:', 'seo-simple' ) . ' ' . esc_html( $phone ) . '</p>';
		}
		
		if ( $email ) {
			$html .= '<p>' . esc_html__( 'Email:', 'seo-simple' ) . ' <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
		}
		
		if ( ! empty( $business_hours ) && is_array( $business_hours ) ) {
			$html .= '<h4>' . esc_html__( 'Business Hours', 'seo-simple' ) . '</h4><ul>';
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
					$html .= '<li>' . $days_map[$day] . ': ' . __( 'Closed', 'seo-simple' ) . '</li>';
				} elseif ( ! empty( $hours['open'] ) && ! empty( $hours['close'] ) ) {
					$html .= '<li>' . $days_map[$day] . ': ' . $hours['open'] . ' - ' . $hours['close'] . '</li>';
				}
			}
			$html .= '</ul>';
		}
		
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Handle AJAX request for getting coordinates from address.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_coordinates() {
		// Verify nonce for security
		if ( ! check_ajax_referer( 'seo_simple_get_coordinates', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}
		
		$address = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';
		
		if ( empty( $address ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter an address.', 'seo-simple' ) ) );
		}
		
		// Use Nominatim API for geocoding
		$response = wp_remote_get( 'https://nominatim.openstreetmap.org/search?' . http_build_query( array(
			'q' => $address,
			'format' => 'json',
			'addressdetails' => 1,
			'limit' => 1
		) ) );
		
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'Error retrieving coordinates.', 'seo-simple' ) ) );
		}
		
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		if ( empty( $data ) || ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not find coordinates for the provided address.', 'seo-simple' ) ) );
		}
		
		$latitude = isset( $data[0]['lat'] ) ? $data[0]['lat'] : '';
		$longitude = isset( $data[0]['lon'] ) ? $data[0]['lon'] : '';
		
		if ( empty( $latitude ) || empty( $longitude ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid coordinates received.', 'seo-simple' ) ) );
		}
		
		wp_send_json_success( array(
			'latitude' => $latitude,
			'longitude' => $longitude
		) );
	}
}