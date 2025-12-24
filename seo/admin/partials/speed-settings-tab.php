<?php
/**
 * Speed Optimization Settings Tab Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="seo-simple-speed-settings-tab">
    <h3><?php _e( 'Speed Optimization Settings', 'seo-simple' ); ?></h3>
    <p><?php _e( 'Configure speed optimization settings to improve your website performance.', 'seo-simple' ); ?></p>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Caching Settings', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_page_caching]" value="1" <?php checked( isset( $settings['speed_page_caching'] ) && $settings['speed_page_caching'] ); ?>>
                <?php _e( 'Enable Page Caching', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Create static HTML files for faster page delivery.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_browser_caching]" value="1" <?php checked( isset( $settings['speed_browser_caching'] ) && $settings['speed_browser_caching'] ); ?>>
                <?php _e( 'Enable Browser Caching', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Reduce loading times on repeat visits.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'File Optimization', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_gzip_compression]" value="1" <?php checked( isset( $settings['speed_gzip_compression'] ) && $settings['speed_gzip_compression'] ); ?>>
                <?php _e( 'Enable GZIP Compression', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Compress files to reduce their size.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_minify_css]" value="1" <?php checked( isset( $settings['speed_minify_css'] ) && $settings['speed_minify_css'] ); ?>>
                <?php _e( 'Minify CSS', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Remove unnecessary characters from CSS files.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_minify_js]" value="1" <?php checked( isset( $settings['speed_minify_js'] ) && $settings['speed_minify_js'] ); ?>>
                <?php _e( 'Minify JavaScript', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Remove unnecessary characters from JavaScript files.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_minify_html]" value="1" <?php checked( isset( $settings['speed_minify_html'] ) && $settings['speed_minify_html'] ); ?>>
                <?php _e( 'Minify HTML', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Remove unnecessary characters from HTML output.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Loading Optimization', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_lazy_loading]" value="1" <?php checked( isset( $settings['speed_lazy_loading'] ) && $settings['speed_lazy_loading'] ); ?>>
                <?php _e( 'Enable Lazy Loading', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Load images and videos only when they come into view.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_defer_js]" value="1" <?php checked( isset( $settings['speed_defer_js'] ) && $settings['speed_defer_js'] ); ?>>
                <?php _e( 'Defer JavaScript Loading', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Load JavaScript files after the main content has loaded.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_async_css]" value="1" <?php checked( isset( $settings['speed_async_css'] ) && $settings['speed_async_css'] ); ?>>
                <?php _e( 'Load CSS Asynchronously', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Prevent CSS from blocking page rendering.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Image Optimization', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_optimize_images]" value="1" <?php checked( isset( $settings['speed_optimize_images'] ) && $settings['speed_optimize_images'] ); ?>>
                <?php _e( 'Optimize Images on Upload', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Automatically optimize images when they are uploaded.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_webp_support]" value="1" <?php checked( isset( $settings['speed_webp_support'] ) && $settings['speed_webp_support'] ); ?>>
                <?php _e( 'Generate WebP Images', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Create WebP versions of images for supported browsers.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Font Optimization', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_local_google_fonts]" value="1" <?php checked( isset( $settings['speed_local_google_fonts'] ) && $settings['speed_local_google_fonts'] ); ?>>
                <?php _e( 'Download Google Fonts Locally', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Host Google Fonts on your own server for faster loading.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Database Optimization', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_database_cleanup]" value="1" <?php checked( isset( $settings['speed_database_cleanup'] ) && $settings['speed_database_cleanup'] ); ?>>
                <?php _e( 'Enable Database Cleanup', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Automatically clean up unnecessary database data.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label for="seo_simple_speed_cleanup_interval"><?php _e( 'Cleanup Interval', 'seo-simple' ); ?></label>
            <select id="seo_simple_speed_cleanup_interval" name="seo_simple_settings[speed_cleanup_interval]">
                <option value="daily" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'daily' ); ?>><?php _e( 'Daily', 'seo-simple' ); ?></option>
                <option value="weekly" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'weekly' ); ?>><?php _e( 'Weekly', 'seo-simple' ); ?></option>
                <option value="monthly" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'monthly' ); ?>><?php _e( 'Monthly', 'seo-simple' ); ?></option>
            </select>
            <p class="description"><?php _e( 'How often to run database cleanup.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-section">
        <h4><?php _e( 'Advanced Settings', 'seo-simple' ); ?></h4>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_combine_css]" value="1" <?php checked( isset( $settings['speed_combine_css'] ) && $settings['speed_combine_css'] ); ?>>
                <?php _e( 'Combine CSS Files', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Combine multiple CSS files into a single file.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_combine_js]" value="1" <?php checked( isset( $settings['speed_combine_js'] ) && $settings['speed_combine_js'] ); ?>>
                <?php _e( 'Combine JavaScript Files', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Combine multiple JavaScript files into a single file.', 'seo-simple' ); ?></p>
        </div>
        
        <div class="seo-simple-setting-field">
            <label>
                <input type="checkbox" name="seo_simple_settings[speed_remove_query_strings]" value="1" <?php checked( isset( $settings['speed_remove_query_strings'] ) && $settings['speed_remove_query_strings'] ); ?>>
                <?php _e( 'Remove Query Strings', 'seo-simple' ); ?>
            </label>
            <p class="description"><?php _e( 'Remove query strings from static resources to improve caching.', 'seo-simple' ); ?></p>
        </div>
    </div>
    
    <div class="seo-simple-settings-actions">
        <button type="button" class="button button-secondary" id="seo-simple-clear-cache"><?php _e( 'Clear All Caches', 'seo-simple' ); ?></button>
        <button type="button" class="button button-secondary" id="seo-simple-test-speed"><?php _e( 'Test Page Speed', 'seo-simple' ); ?></button>
        <span class="spinner" id="seo-simple-speed-spinner"></span>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#seo-simple-clear-cache').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(seo_simple_speed_optimization.strings.confirm_clear_cache)) {
            return;
        }
        
        var $button = $(this);
        var $spinner = $('#seo-simple-speed-spinner');
        
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_simple_clear_cache',
                nonce: seo_simple_speed_optimization.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(seo_simple_speed_optimization.strings.cache_cleared);
                } else {
                    alert(response.data.message || seo_simple_speed_optimization.strings.error_occurred);
                }
            },
            error: function() {
                alert(seo_simple_speed_optimization.strings.error_occurred);
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
    
    $('#seo-simple-test-speed').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $spinner = $('#seo-simple-speed-spinner');
        
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        
        alert(seo_simple_speed_optimization.strings.test_running);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_simple_test_site_speed',
                nonce: seo_simple_speed_optimization.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(seo_simple_speed_optimization.strings.test_completed + '\n' + 
                          'Page Load Time: ' + response.data.load_time + 's\n' +
                          'Page Size: ' + response.data.page_size + '\n' +
                          'Requests: ' + response.data.requests);
                } else {
                    alert(response.data.message || seo_simple_speed_optimization.strings.error_occurred);
                }
            },
            error: function() {
                alert(seo_simple_speed_optimization.strings.error_occurred);
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
</script>