<?php
/**
 * Meta Box Google Tab Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get post ID from global post object
$post_id = isset( $post ) ? $post->ID : get_the_ID();

// Get Google Analytics settings from post meta if not already set
if ( ! isset( $google_analytics_track ) ) {
    $google_analytics_track = get_post_meta( $post_id, '_seo_simple_google_analytics_track', true );
}
if ( ! isset( $google_analytics_custom_event ) ) {
    $google_analytics_custom_event = get_post_meta( $post_id, '_seo_simple_google_analytics_custom_event', true );
}
if ( ! isset( $google_analytics_event_category ) ) {
    $google_analytics_event_category = get_post_meta( $post_id, '_seo_simple_google_analytics_event_category', true );
}
if ( ! isset( $google_analytics_event_action ) ) {
    $google_analytics_event_action = get_post_meta( $post_id, '_seo_simple_google_analytics_event_action', true );
}
if ( ! isset( $google_analytics_event_label ) ) {
    $google_analytics_event_label = get_post_meta( $post_id, '_seo_simple_google_analytics_event_label', true );
}
?>

<div class="seo-simple-meta-google-section">
    <h4><?php _e( 'Google Analytics Tracking', 'seo-simple' ); ?></h4>
    
    <div class="seo-simple-meta-google-field">
        <label for="seo-simple-google-analytics-track">
            <input type="checkbox" id="seo-simple-google-analytics-track" name="seo_simple_google_analytics_track" value="1" <?php checked( $google_analytics_track ); ?>>
            <?php _e( 'Track this page specifically', 'seo-simple' ); ?>
        </label>
        <p class="description"><?php _e( 'Enable to send custom tracking events for this page.', 'seo-simple' ); ?></p>
    </div>
    
    <div class="seo-simple-meta-google-conditional" id="seo-simple-google-analytics-options" style="<?php echo $google_analytics_track ? '' : 'display: none;'; ?>">
        <div class="seo-simple-meta-google-field">
            <label for="seo-simple-google-analytics-custom-event">
                <input type="checkbox" id="seo-simple-google-analytics-custom-event" name="seo_simple_google_analytics_custom_event" value="1" <?php checked( $google_analytics_custom_event ); ?>>
                <?php _e( 'Send custom event', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Send a custom event when this page is viewed.', 'seo-simple' ); ?></p>
        </div>
        
        <div id="seo-simple-google-analytics-event-fields" style="<?php echo $google_analytics_custom_event ? '' : 'display: none;'; ?>">
            <div class="seo-simple-meta-google-field">
                <label for="seo-simple-google-analytics-event-category"><?php _e( 'Event Category', 'seo-simple' ); ?></label>
                <input type="text" id="seo-simple-google-analytics-event-category" name="seo_simple_google_analytics_event_category" value="<?php echo esc_attr( $google_analytics_event_category ); ?>" placeholder="<?php esc_attr_e( 'e.g., Page View', 'seo-simple' ); ?>">
            </div>
            
            <div class="seo-simple-meta-google-field">
                <label for="seo-simple-google-analytics-event-action"><?php _e( 'Event Action', 'seo-simple' ); ?></label>
                <input type="text" id="seo-simple-google-analytics-event-action" name="seo_simple_google_analytics_event_action" value="<?php echo esc_attr( $google_analytics_event_action ); ?>" placeholder="<?php esc_attr_e( 'e.g., Homepage', 'seo-simple' ); ?>">
            </div>
            
            <div class="seo-simple-meta-google-field">
                <label for="seo-simple-google-analytics-event-label"><?php _e( 'Event Label', 'seo-simple' ); ?></label>
                <input type="text" id="seo-simple-google-analytics-event-label" name="seo_simple_google_analytics_event_label" value="<?php echo esc_attr( $google_analytics_event_label ); ?>" placeholder="<?php esc_attr_e( 'e.g., Organic Traffic', 'seo-simple' ); ?>">
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Toggle analytics options
    $('#seo-simple-google-analytics-track').on('change', function() {
        $('#seo-simple-google-analytics-options').toggle($(this).is(':checked'));
    });
    
    // Toggle event fields
    $('#seo-simple-google-analytics-custom-event').on('change', function() {
        $('#seo-simple-google-analytics-event-fields').toggle($(this).is(':checked'));
    });
});
</script>