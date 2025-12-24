<?php
/**
 * Google Analytics Page Template
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

<div class="wrap seo-simple-analytics-page">
    <h1><?php _e( 'Google Analytics', 'seo-simple' ); ?></h1>
    
    <div class="seo-simple-date-range-selector">
        <h3><?php _e( 'Date Range', 'seo-simple' ); ?></h3>
        <div class="date-fields">
            <div class="date-field">
                <label for="analytics-start-date"><?php _e( 'Start Date', 'seo-simple' ); ?></label>
                <input type="text" id="analytics-start-date" class="seo-simple-date-picker" value="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ); ?>">
            </div>
            <div class="date-field">
                <label for="analytics-end-date"><?php _e( 'End Date', 'seo-simple' ); ?></label>
                <input type="text" id="analytics-end-date" class="seo-simple-date-picker" value="<?php echo date( 'Y-m-d' ); ?>">
            </div>
        </div>
        <div class="date-presets">
            <button type="button" class="button seo-simple-date-preset" data-preset="7days"><?php _e( 'Last 7 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="30days"><?php _e( 'Last 30 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="90days"><?php _e( 'Last 90 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="1year"><?php _e( 'Last Year', 'seo-simple' ); ?></button>
        </div>
    </div>
    
    <div class="seo-simple-analytics-filters">
        <h3><?php _e( 'Metrics & Dimensions', 'seo-simple' ); ?></h3>
        
        <div class="filter-section">
            <h4><?php _e( 'Metrics', 'seo-simple' ); ?></h4>
            <div class="filter-options" id="analytics-metrics">
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="metrics[]" value="sessions" checked>
                        <?php _e( 'Sessions', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="metrics[]" value="users" checked>
                        <?php _e( 'Users', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="metrics[]" value="pageviews" checked>
                        <?php _e( 'Pageviews', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="metrics[]" value="bounce_rate">
                        <?php _e( 'Bounce Rate', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="metrics[]" value="avg_session_duration">
                        <?php _e( 'Avg. Session Duration', 'seo-simple' ); ?>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <h4><?php _e( 'Dimensions', 'seo-simple' ); ?></h4>
            <div class="filter-options" id="analytics-dimensions">
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="page" checked>
                        <?php _e( 'Page', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="source">
                        <?php _e( 'Source', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="medium">
                        <?php _e( 'Medium', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="campaign">
                        <?php _e( 'Campaign', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="country">
                        <?php _e( 'Country', 'seo-simple' ); ?>
                    </label>
                </div>
                <div class="filter-option">
                    <label>
                        <input type="checkbox" name="dimensions[]" value="device">
                        <?php _e( 'Device', 'seo-simple' ); ?>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <button type="button" class="button button-primary" id="seo-simple-fetch-analytics-data">
                <?php _e( 'Fetch Data', 'seo-simple' ); ?>
            </button>
        </div>
    </div>
    
    <div id="seo-simple-analytics-data-container" class="seo-simple-data-container" style="display: none;">
        <div class="seo-simple-loading">
            <div class="seo-simple-loading-spinner"></div>
            <?php _e( 'Loading data...', 'seo-simple' ); ?>
        </div>
    </div>
</div>