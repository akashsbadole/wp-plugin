<?php
/**
 * Template for the Speed Optimization settings page.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'seo_simple_settings', array() );
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="seo-simple-speed-wrapper">
        <!-- Header -->
        <div class="seo-simple-speed-header">
            <h2><?php _e( 'Speed Optimization Settings', 'seo-simple' ); ?></h2>
            <p><?php _e( 'Configure speed optimization settings to improve your website performance and user experience.', 'seo-simple' ); ?></p>
        </div>

        <!-- Settings Form -->
        <form id="seo-simple-speed-settings-form" class="seo-simple-speed-settings-form">
            <?php wp_nonce_field( 'seo_simple_save_speed_settings', 'seo_simple_speed_settings_nonce' ); ?>
            
            <!-- Page Caching -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-archive"></span>
                    <?php _e( 'Page Caching', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_page_caching"><?php _e( 'Enable Page Caching', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Creates static HTML files for fast delivery to visitors.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_page_caching" name="seo_simple_settings[speed_page_caching]" value="1" <?php checked( isset( $settings['speed_page_caching'] ) && $settings['speed_page_caching'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Browser Caching -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e( 'Browser Caching', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_browser_caching"><?php _e( 'Enable Browser Caching', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Allows browsers to store static files locally to reduce loading times on repeat visits.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_browser_caching" name="seo_simple_settings[speed_browser_caching]" value="1" <?php checked( isset( $settings['speed_browser_caching'] ) && $settings['speed_browser_caching'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- GZIP Compression -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-compress"></span>
                    <?php _e( 'GZIP Compression', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_gzip_compression"><?php _e( 'Enable GZIP Compression', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Compresses files to reduce their size and improve loading times.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_gzip_compression" name="seo_simple_settings[speed_gzip_compression]" value="1" <?php checked( isset( $settings['speed_gzip_compression'] ) && $settings['speed_gzip_compression'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- File Minification -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php _e( 'File Minification', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_minify_css"><?php _e( 'Minify CSS', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Removes unnecessary characters from CSS files to reduce their size.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_minify_css" name="seo_simple_settings[speed_minify_css]" value="1" <?php checked( isset( $settings['speed_minify_css'] ) && $settings['speed_minify_css'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_minify_js"><?php _e( 'Minify JavaScript', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Removes unnecessary characters from JavaScript files to reduce their size.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_minify_js" name="seo_simple_settings[speed_minify_js]" value="1" <?php checked( isset( $settings['speed_minify_js'] ) && $settings['speed_minify_js'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_minify_html"><?php _e( 'Minify HTML', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Removes unnecessary characters from HTML output to reduce page size.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_minify_html" name="seo_simple_settings[speed_minify_html]" value="1" <?php checked( isset( $settings['speed_minify_html'] ) && $settings['speed_minify_html'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- File Combination -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-media-default"></span>
                    <?php _e( 'File Combination', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_combine_css"><?php _e( 'Combine CSS Files', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Combines multiple CSS files into a single file to reduce HTTP requests.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_combine_css" name="seo_simple_settings[speed_combine_css]" value="1" <?php checked( isset( $settings['speed_combine_css'] ) && $settings['speed_combine_css'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_combine_js"><?php _e( 'Combine JavaScript Files', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Combines multiple JavaScript files into a single file to reduce HTTP requests.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_combine_js" name="seo_simple_settings[speed_combine_js]" value="1" <?php checked( isset( $settings['speed_combine_js'] ) && $settings['speed_combine_js'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Loading Optimization -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-clock"></span>
                    <?php _e( 'Loading Optimization', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_lazy_loading"><?php _e( 'Lazy Loading', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Delays loading of images and videos until they are visible on screen.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_lazy_loading" name="seo_simple_settings[speed_lazy_loading]" value="1" <?php checked( isset( $settings['speed_lazy_loading'] ) && $settings['speed_lazy_loading'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_defer_js"><?php _e( 'Defer JavaScript', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Delays loading of JavaScript files to improve perceived performance.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_defer_js" name="seo_simple_settings[speed_defer_js]" value="1" <?php checked( isset( $settings['speed_defer_js'] ) && $settings['speed_defer_js'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_async_css"><?php _e( 'Async CSS', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Loads CSS asynchronously to prevent render-blocking.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_async_css" name="seo_simple_settings[speed_async_css]" value="1" <?php checked( isset( $settings['speed_async_css'] ) && $settings['speed_async_css'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Image Optimization -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-format-image"></span>
                    <?php _e( 'Image Optimization', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_optimize_images"><?php _e( 'Optimize Images', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Automatically optimizes images on upload to reduce file size.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_optimize_images" name="seo_simple_settings[speed_optimize_images]" value="1" <?php checked( isset( $settings['speed_optimize_images'] ) && $settings['speed_optimize_images'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_webp_support"><?php _e( 'WebP Support', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Generates and serves WebP images for supported browsers.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_webp_support" name="seo_simple_settings[speed_webp_support]" value="1" <?php checked( isset( $settings['speed_webp_support'] ) && $settings['speed_webp_support'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Font Optimization -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-editor-textcolor"></span>
                    <?php _e( 'Font Optimization', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_local_google_fonts"><?php _e( 'Local Google Fonts', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Downloads Google Fonts locally to improve loading speed.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_local_google_fonts" name="seo_simple_settings[speed_local_google_fonts]" value="1" <?php checked( isset( $settings['speed_local_google_fonts'] ) && $settings['speed_local_google_fonts'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Database Optimization -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-database"></span>
                    <?php _e( 'Database Optimization', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_database_cleanup"><?php _e( 'Database Cleanup', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Automatically cleans up database to remove unnecessary data.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_database_cleanup" name="seo_simple_settings[speed_database_cleanup]" value="1" <?php checked( isset( $settings['speed_database_cleanup'] ) && $settings['speed_database_cleanup'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_cleanup_interval"><?php _e( 'Cleanup Interval', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'How often to run database cleanup.', 'seo-simple' ); ?></span>
                    <select id="speed_cleanup_interval" name="seo_simple_settings[speed_cleanup_interval]" class="seo-simple-speed-select">
                        <option value="daily" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'daily' ); ?>><?php _e( 'Daily', 'seo-simple' ); ?></option>
                        <option value="weekly" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'weekly' ); ?>><?php _e( 'Weekly', 'seo-simple' ); ?></option>
                        <option value="monthly" <?php selected( isset( $settings['speed_cleanup_interval'] ) && $settings['speed_cleanup_interval'] === 'monthly' ); ?>><?php _e( 'Monthly', 'seo-simple' ); ?></option>
                    </select>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="seo-simple-speed-section">
                <h3>
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e( 'Advanced Settings', 'seo-simple' ); ?>
                </h3>
                
                <div class="seo-simple-speed-field">
                    <label for="speed_remove_query_strings"><?php _e( 'Remove Query Strings', 'seo-simple' ); ?></label>
                    <span class="description"><?php _e( 'Removes query strings from static resources to improve caching.', 'seo-simple' ); ?></span>
                    <label class="seo-simple-speed-toggle">
                        <input type="checkbox" id="speed_remove_query_strings" name="seo_simple_settings[speed_remove_query_strings]" value="1" <?php checked( isset( $settings['speed_remove_query_strings'] ) && $settings['speed_remove_query_strings'] ); ?>>
                        <span class="seo-simple-speed-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="seo-simple-speed-section">
                <p class="submit">
                    <button type="submit" class="seo-simple-speed-button">
                        <?php _e( 'Save Settings', 'seo-simple' ); ?>
                    </button>
                </p>
            </div>
        </form>
    </div>
</div>