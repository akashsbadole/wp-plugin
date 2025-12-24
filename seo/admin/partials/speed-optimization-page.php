<?php

/**
 * Template for the Speed Optimization page.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct file access
if (! defined('ABSPATH')) {
    exit;
}

$settings = get_option('seo_simple_settings', array());
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="seo-simple-speed-wrapper">
        <!-- Header -->
        <div class="seo-simple-speed-header">
            <h2><?php _e('Speed Optimization', 'seo-simple'); ?></h2>
            <p><?php _e('Optimize your website speed and improve performance with our comprehensive speed optimization tools.', 'seo-simple'); ?></p>
        </div>

        <!-- Speed Score Dashboard -->
        <div class="seo-simple-speed-section">
            <h3>
                <span class="dashicons dashicons-dashboard"></span>
                <?php _e('Speed Score', 'seo-simple'); ?>
            </h3>
            <div class="seo-simple-speed-dashboard">
                <div class="seo-simple-speed-score">
                    <div class="seo-simple-speed-score-value">
                        <span class="seo-simple-speed-score-number">85</span>
                        <span class="seo-simple-speed-score-label"><?php _e('Good', 'seo-simple'); ?></span>
                    </div>
                    <div class="seo-simple-speed-score-progress">
                        <div class="seo-simple-speed-score-bar" style="width: 85%;"></div>
                    </div>
                </div>
                <div class="seo-simple-speed-metrics">
                    <div class="seo-simple-speed-metric">
                        <span class="seo-simple-speed-metric-label"><?php _e('Page Load Time', 'seo-simple'); ?></span>
                        <span class="seo-simple-speed-metric-value">2.3s</span>
                    </div>
                    <div class="seo-simple-speed-metric">
                        <span class="seo-simple-speed-metric-label"><?php _e('First Contentful Paint', 'seo-simple'); ?></span>
                        <span class="seo-simple-speed-metric-value">1.8s</span>
                    </div>
                    <div class="seo-simple-speed-metric">
                        <span class="seo-simple-speed-metric-label"><?php _e('Time to Interactive', 'seo-simple'); ?></span>
                        <span class="seo-simple-speed-metric-value">3.1s</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optimization Tools -->
        <div class="seo-simple-speed-section">
            <h3>
                <span class="dashicons dashicons-hammer"></span>
                <?php _e('Optimization Tools', 'seo-simple'); ?>
            </h3>
            <div class="seo-simple-speed-tools">
                <div class="seo-simple-speed-tool">
                    <h4><?php _e('Clear Cache', 'seo-simple'); ?></h4>
                    <p><?php _e('Clear all cached files to ensure visitors see the latest version of your site.', 'seo-simple'); ?></p>
                    <button type="button" class="seo-simple-speed-button seo-simple-speed-clear-cache">
                        <?php _e('Clear Cache', 'seo-simple'); ?>
                    </button>
                </div>
                <div class="seo-simple-speed-tool">
                    <h4><?php _e('Optimize Images', 'seo-simple'); ?></h4>
                    <p><?php _e('Compress and optimize images to reduce file size without sacrificing quality.', 'seo-simple'); ?></p>
                    <button type="button" class="seo-simple-speed-button seo-simple-speed-optimize-images">
                        <?php _e('Optimize Images', 'seo-simple'); ?>
                    </button>
                </div>
                <div class="seo-simple-speed-tool">
                    <h4><?php _e('Minify Files', 'seo-simple'); ?></h4>
                    <p><?php _e('Minify CSS, JavaScript, and HTML files to reduce their size.', 'seo-simple'); ?></p>
                    <button type="button" class="seo-simple-speed-button seo-simple-speed-minify-files">
                        <?php _e('Minify Files', 'seo-simple'); ?>
                    </button>
                </div>
                <div class="seo-simple-speed-tool">
                    <h4><?php _e('Optimize Database', 'seo-simple'); ?></h4>
                    <p><?php _e('Clean up and optimize your database to improve performance.', 'seo-simple'); ?></p>
                    <button type="button" class="seo-simple-speed-button seo-simple-speed-optimize-database">
                        <?php _e('Optimize Database', 'seo-simple'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Performance Recommendations -->
        <div class="seo-simple-speed-section">
            <h3>
                <span class="dashicons dashicons-lightbulb"></span>
                <?php _e('Performance Recommendations', 'seo-simple'); ?>
            </h3>
            <div class="seo-simple-speed-recommendations">
                <div class="seo-simple-speed-recommendation">
                    <div class="seo-simple-speed-recommendation-status warning">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="seo-simple-speed-recommendation-content">
                        <h4><?php _e('Optimize Images', 'seo-simple'); ?></h4>
                        <p><?php _e('Some images on your site are not optimized. Compressing them can reduce page load time.', 'seo-simple'); ?></p>
                    </div>
                </div>
                <div class="seo-simple-speed-recommendation">
                    <div class="seo-simple-speed-recommendation-status success">
                        <span class="dashicons dashicons-yes"></span>
                    </div>
                    <div class="seo-simple-speed-recommendation-content">
                        <h4><?php _e('Browser Caching Enabled', 'seo-simple'); ?></h4>
                        <p><?php _e('Browser caching is properly configured to improve repeat visit performance.', 'seo-simple'); ?></p>
                    </div>
                </div>
                <div class="seo-simple-speed-recommendation">
                    <div class="seo-simple-speed-recommendation-status info">
                        <span class="dashicons dashicons-info"></span>
                    </div>
                    <div class="seo-simple-speed-recommendation-content">
                        <h4><?php _e('Consider CDN', 'seo-simple'); ?></h4>
                        <p><?php _e('Using a Content Delivery Network can significantly improve page load times for global visitors.', 'seo-simple'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Speed Test -->
        <div class="seo-simple-speed-section">
            <h3>
                <span class="dashicons dashicons-performance"></span>
                <?php _e('Speed Test', 'seo-simple'); ?>
            </h3>
            <div class="seo-simple-speed-test">
                <p><?php _e('Run a comprehensive speed test to analyze your website performance.', 'seo-simple'); ?></p>
                <button type="button" class="seo-simple-speed-button seo-simple-speed-run-test">
                    <?php _e('Run Speed Test', 'seo-simple'); ?>
                </button>
                <div class="seo-simple-speed-test-results" style="display: none;">
                    <!-- Test results will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>