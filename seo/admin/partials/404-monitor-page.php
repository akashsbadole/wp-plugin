<?php
/**
 * Template for the 404 Monitor page.
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

    <div class="seo-simple-404-wrapper">
        <!-- Header -->
        <div class="seo-simple-404-header">
            <h2><?php _e( '404 Error Monitor', 'seo-simple' ); ?></h2>
            <p><?php _e( 'Monitor and manage 404 errors on your website to improve user experience and SEO.', 'seo-simple' ); ?></p>
        </div>

        <!-- Statistics -->
        <div class="seo-simple-404-stats">
            <div class="seo-simple-404-stat">
                <h3><?php _e( 'Total 404 Errors', 'seo-simple' ); ?></h3>
                <div class="seo-simple-404-stat-value" id="stat-total-404s">0</div>
            </div>
            <div class="seo-simple-404-stat">
                <h3><?php _e( 'Last 24 Hours', 'seo-simple' ); ?></h3>
                <div class="seo-simple-404-stat-value" id="stat-last-24h">0</div>
            </div>
            <div class="seo-simple-404-stat">
                <h3><?php _e( 'Last 7 Days', 'seo-simple' ); ?></h3>
                <div class="seo-simple-404-stat-value" id="stat-last-7days">0</div>
            </div>
            <div class="seo-simple-404-stat">
                <h3><?php _e( 'Top Referrer', 'seo-simple' ); ?></h3>
                <div class="seo-simple-404-stat-value" id="stat-top-referrer">-</div>
            </div>
        </div>

        <!-- Filter Options -->
        <div class="seo-simple-404-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="seo-simple-404-monitor" />
                
                <div class="seo-simple-404-filter">
                    <label for="filter-date"><?php _e( 'Date Range:', 'seo-simple' ); ?></label>
                    <select id="filter-date" name="date_range">
                        <option value="all"><?php _e( 'All Time', 'seo-simple' ); ?></option>
                        <option value="today"><?php _e( 'Today', 'seo-simple' ); ?></option>
                        <option value="week"><?php _e( 'Last 7 Days', 'seo-simple' ); ?></option>
                        <option value="month"><?php _e( 'Last 30 Days', 'seo-simple' ); ?></option>
                    </select>
                </div>
                
                <div class="seo-simple-404-filter">
                    <label for="filter-referrer"><?php _e( 'Referrer:', 'seo-simple' ); ?></label>
                    <select id="filter-referrer" name="referrer">
                        <option value="all"><?php _e( 'All', 'seo-simple' ); ?></option>
                        <option value="external"><?php _e( 'External', 'seo-simple' ); ?></option>
                        <option value="internal"><?php _e( 'Internal', 'seo-simple' ); ?></option>
                        <option value="direct"><?php _e( 'Direct', 'seo-simple' ); ?></option>
                    </select>
                </div>
                
                <div class="seo-simple-404-filter">
                    <label for="filter-ip"><?php _e( 'IP Address:', 'seo-simple' ); ?></label>
                    <input type="text" id="filter-ip" name="ip" placeholder="<?php esc_attr_e( 'Enter IP address', 'seo-simple' ); ?>" />
                </div>
                
                <div class="seo-simple-404-filter">
                    <input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'seo-simple' ); ?>" />
                    <input type="button" class="button" id="clear-filters" value="<?php esc_attr_e( 'Clear', 'seo-simple' ); ?>" />
                </div>
            </form>
        </div>

        <!-- 404 Errors Table -->
        <div class="seo-simple-404-table-container">
            <table class="wp-list-table widefat fixed striped seo-simple-404-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-url"><?php _e( 'URL', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-count"><?php _e( 'Count', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-referrer"><?php _e( 'Referrer', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-ip"><?php _e( 'IP Address', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-user-agent"><?php _e( 'User Agent', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-date"><?php _e( 'Last Accessed', 'seo-simple' ); ?></th>
                        <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'seo-simple' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="seo-simple-404-no-data"><?php _e( 'No 404 errors found.', 'seo-simple' ); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="seo-simple-404-pagination">
            <span class="displaying-num"><?php _e( 'No items found.', 'seo-simple' ); ?></span>
        </div>

        <!-- Actions -->
        <div class="seo-simple-404-actions">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select bulk action', 'seo-simple' ); ?></label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1"><?php _e( 'Bulk Actions', 'seo-simple' ); ?></option>
                    <option value="delete"><?php _e( 'Delete', 'seo-simple' ); ?></option>
                    <option value="redirect"><?php _e( 'Create Redirect', 'seo-simple' ); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e( 'Apply', 'seo-simple' ); ?>" />
            </div>
            
            <div class="alignright">
                <button type="button" class="button" id="clear-all-404s"><?php _e( 'Clear All 404 Errors', 'seo-simple' ); ?></button>
                <button type="button" class="button button-primary" id="export-404s"><?php _e( 'Export CSV', 'seo-simple' ); ?></button>
            </div>
        </div>

        <!-- Settings -->
        <div class="seo-simple-404-settings">
            <h3><?php _e( '404 Monitor Settings', 'seo-simple' ); ?></h3>
            <form id="seo-simple-404-settings-form" method="post" action="options.php">
                <?php settings_fields( 'seo_simple_404_settings' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_404_monitor"><?php _e( 'Enable 404 Monitoring', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_404_monitor" name="seo_simple_settings[enable_404_monitor]" value="1" <?php checked( isset( $settings['enable_404_monitor'] ) && $settings['enable_404_monitor'] ); ?> />
                            <p class="description"><?php _e( 'Enable to log 404 errors for monitoring.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="exclude_bots"><?php _e( 'Exclude Bots', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="exclude_bots" name="seo_simple_settings[exclude_bots]" value="1" <?php checked( isset( $settings['exclude_bots'] ) && $settings['exclude_bots'] ); ?> />
                            <p class="description"><?php _e( 'Exclude search engine bots from 404 monitoring.', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="retention_days"><?php _e( 'Data Retention', 'seo-simple' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="retention_days" name="seo_simple_settings[404_retention_days]" value="<?php echo isset( $settings['404_retention_days'] ) ? esc_attr( $settings['404_retention_days'] ) : '30'; ?>" min="1" max="365" />
                            <span class="description"><?php _e( 'days', 'seo-simple' ); ?></span>
                            <p class="description"><?php _e( 'How long to keep 404 error logs (1-365 days).', 'seo-simple' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Load statistics on page load
    loadStats();
    
    // Load logs on page load
    loadLogs();
    
    // Clear filters button
    $('#clear-filters').on('click', function() {
        window.location.href = '?page=seo-simple-404-monitor';
    });
    
    // Clear all 404 errors
    $('#clear-all-404s').on('click', function() {
        if (confirm('<?php _e( 'Are you sure you want to clear all 404 errors? This action cannot be undone.', 'seo-simple' ); ?>')) {
            // AJAX call to clear all 404 errors
            $.post(ajaxurl, {
                action: 'seo_simple_clear_all_404s',
                nonce: '<?php echo wp_create_nonce( 'seo_simple_clear_404s' ); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e( 'An error occurred.', 'seo-simple' ); ?>');
                }
            });
        }
    });
    
    // Export 404 errors
    $('#export-404s').on('click', function() {
        window.location.href = ajaxurl + '?action=seo_simple_export_404s&nonce=<?php echo wp_create_nonce( 'seo_simple_export_404s' ); ?>';
    });
    
    // Handle ignore URL clicks
    $(document).on('click', '.ignore-url', function(e) {
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('id');
        var isIgnored = $this.text().includes('<?php _e( 'Unignore', 'seo-simple' ); ?>');
        
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_ignore_url',
            nonce: seo_simple_404_monitor.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                loadLogs(); // Reload logs
                loadStats(); // Reload stats
            } else {
                alert(response.data.message || '<?php _e( 'An error occurred.', 'seo-simple' ); ?>');
            }
        });
    });
    
    // Handle create redirect clicks
    $(document).on('click', '.create-redirect', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var redirectUrl = prompt('<?php _e( 'Enter the URL to redirect to:', 'seo-simple' ); ?>', '/');
        
        if (redirectUrl) {
            // Get the log ID from the row
            var id = $(this).closest('tr').find('input[name="log[]"]').val();
            
            $.post(ajaxurl, {
                action: 'seo_simple_404_monitor_create_redirect',
                nonce: seo_simple_404_monitor.nonce,
                id: id,
                redirect_to: redirectUrl
            }, function(response) {
                if (response.success) {
                    loadLogs(); // Reload logs
                    loadStats(); // Reload stats
                } else {
                    alert(response.data.message || '<?php _e( 'An error occurred.', 'seo-simple' ); ?>');
                }
            });
        }
    });
    
    // Function to load statistics
    function loadStats() {
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_get_stats',
            nonce: seo_simple_404_monitor.nonce
        }, function(response) {
            if (response.success) {
                $('#stat-total-404s').text(response.data.total_404s || 0);
                $('#stat-last-24h').text(response.data.last_24h || 0);
                $('#stat-last-7days').text(response.data.last_7days || 0);
                $('#stat-top-referrer').text(response.data.top_referrer || '-');
            }
        });
    }
    
    // Function to load logs
    function loadLogs(page) {
        page = page || 1;
        
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_get_logs',
            nonce: seo_simple_404_monitor.nonce,
            page: page
        }, function(response) {
            if (response.success) {
                $('.seo-simple-404-table tbody').html(response.data.html);
                $('.seo-simple-404-pagination').html(response.data.pagination);
            }
        });
    }
    
    // Handle pagination clicks
    $(document).on('click', '.pagination-links a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page) {
            loadLogs(page);
        }
    });
    
    // Handle form submissions for filtering
    $('.seo-simple-404-filters form').on('submit', function(e) {
        e.preventDefault();
        loadLogs();
    });
});
</script>
