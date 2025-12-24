<?php
/**
 * Google Search Console Page Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = $this->seo_meta->get_global_settings();
$google_search_console_site = isset( $settings['google_search_console_site'] ) ? $settings['google_search_console_site'] : '';
?>

<div class="wrap seo-simple-search-console-page">
    <h1><?php _e( 'Google Search Console', 'seo-simple' ); ?></h1>
    
    <div class="seo-simple-date-range-selector">
        <h3><?php _e( 'Date Range', 'seo-simple' ); ?></h3>
        <div class="date-fields">
            <div class="date-field">
                <label for="search-console-start-date"><?php _e( 'Start Date', 'seo-simple' ); ?></label>
                <input type="text" id="search-console-start-date" class="seo-simple-date-picker" value="<?php echo date( 'Y-m-d', strtotime( '-30 days' ) ); ?>">
            </div>
            <div class="date-field">
                <label for="search-console-end-date"><?php _e( 'End Date', 'seo-simple' ); ?></label>
                <input type="text" id="search-console-end-date" class="seo-simple-date-picker" value="<?php echo date( 'Y-m-d' ); ?>">
            </div>
        </div>
        <div class="date-presets">
            <button type="button" class="button seo-simple-date-preset" data-preset="7days"><?php _e( 'Last 7 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="30days"><?php _e( 'Last 30 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="90days"><?php _e( 'Last 90 Days', 'seo-simple' ); ?></button>
            <button type="button" class="button seo-simple-date-preset" data-preset="1year"><?php _e( 'Last Year', 'seo-simple' ); ?></button>
        </div>
    </div>
    
    <div class="seo-simple-search-console-data">
        <h3><?php _e( 'Search Performance Data', 'seo-simple' ); ?></h3>
        
        <div class="data-filters">
            <div class="filter-group">
                <label><?php _e( 'Dimensions', 'seo-simple' ); ?></label>
                <div id="search-console-dimensions">
                    <div class="filter-option">
                        <label>
                            <input type="checkbox" name="dimensions[]" value="page" checked>
                            <?php _e( 'Page', 'seo-simple' ); ?>
                        </label>
                    </div>
                    <div class="filter-option">
                        <label>
                            <input type="checkbox" name="dimensions[]" value="query" checked>
                            <?php _e( 'Query', 'seo-simple' ); ?>
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
            
            <div class="filter-group">
                <label><?php _e( 'Filters', 'seo-simple' ); ?></label>
                <div id="search-console-filters">
                    <div class="filter-option">
                        <label>
                            <input type="checkbox" name="filters[]" value="web">
                            <?php _e( 'Web', 'seo-simple' ); ?>
                        </label>
                    </div>
                    <div class="filter-option">
                        <label>
                            <input type="checkbox" name="filters[]" value="image">
                            <?php _e( 'Images', 'seo-simple' ); ?>
                        </label>
                    </div>
                    <div class="filter-option">
                        <label>
                            <input type="checkbox" name="filters[]" value="video">
                            <?php _e( 'Videos', 'seo-simple' ); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="filter-section">
            <button type="button" class="button button-primary" id="seo-simple-fetch-search-console-data">
                <?php _e( 'Fetch Data', 'seo-simple' ); ?>
            </button>
        </div>
    </div>
    
    <div id="seo-simple-search-console-data-container" class="seo-simple-data-container" style="display: none;">
        <div class="seo-simple-loading">
            <div class="seo-simple-loading-spinner"></div>
            <?php _e( 'Loading data...', 'seo-simple' ); ?>
        </div>
    </div>
    
    <?php if ( ! empty( $google_search_console_site ) ) : ?>
    <div class="seo-simple-search-console-info">
        <h3><?php _e( 'Site Information', 'seo-simple' ); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th><?php _e( 'Site URL', 'seo-simple' ); ?></th>
                <td><code><?php echo esc_html( $google_search_console_site ); ?></code></td>
            </tr>
            <tr>
                <th><?php _e( 'Last Data Fetch', 'seo-simple' ); ?></th>
                <td><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_option( 'seo_simple_search_console_last_fetch', time() ) ); ?></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
</div>