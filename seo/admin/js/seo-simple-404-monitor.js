/**
 * 404 Monitor Admin JavaScript
 *
 * @since 1.0.0
 * @package Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize the 404 monitor
        init404Monitor();
    });

    /**
     * Initialize the 404 monitor functionality
     */
    function init404Monitor() {
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
            if (confirm(seo_simple_404_monitor.confirm_clear_all)) {
                clearAll404s();
            }
        });
        
        // Export 404 errors
        $('#export-404s').on('click', function() {
            export404s();
        });
        
        // Handle ignore URL clicks
        $(document).on('click', '.ignore-url', function(e) {
            e.preventDefault();
            var $this = $(this);
            var id = $this.data('id');
            var isIgnored = $this.text().includes(seo_simple_404_monitor.text_unignore);
            
            ignoreUrl(id, isIgnored);
        });
        
        // Handle create redirect clicks
        $(document).on('click', '.create-redirect', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var redirectUrl = prompt(seo_simple_404_monitor.prompt_redirect, '/');
            
            if (redirectUrl) {
                // Get the log ID from the row
                var id = $(this).closest('tr').find('input[name="log[]"]').val();
                createRedirect(id, redirectUrl);
            }
        });
        
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
        
        // Handle bulk actions
        $('#doaction').on('click', function(e) {
            e.preventDefault();
            handleBulkAction();
        });
        
        // Settings form submission
        $('#seo-simple-404-settings-form').on('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });
    }

    /**
     * Load statistics data via AJAX
     */
    function loadStats() {
        showLoading('#seo-simple-404-stats');
        
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_get_stats',
            nonce: seo_simple_404_monitor.nonce
        }, function(response) {
            if (response.success) {
                $('#stat-total-404s').text(response.data.total_404s || 0);
                $('#stat-last-24h').text(response.data.last_24h || 0);
                $('#stat-last-7days').text(response.data.last_7days || 0);
                $('#stat-top-referrer').text(response.data.top_referrer || '-');
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        }).always(function() {
            hideLoading('#seo-simple-404-stats');
        });
    }

    /**
     * Load logs data via AJAX
     */
    function loadLogs(page) {
        page = page || 1;
        
        showLoading('.seo-simple-404-table-container');
        
        var formData = $('.seo-simple-404-filters form').serializeArray();
        formData.push({name: 'action', value: 'seo_simple_404_monitor_get_logs'});
        formData.push({name: 'nonce', value: seo_simple_404_monitor.nonce});
        formData.push({name: 'page', value: page});
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                $('.seo-simple-404-table tbody').html(response.data.html);
                $('.seo-simple-404-pagination').html(response.data.pagination);
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        }).always(function() {
            hideLoading('.seo-simple-404-table-container');
        });
    }

    /**
     * Clear all 404 errors
     */
    function clearAll404s() {
        $.post(ajaxurl, {
            action: 'seo_simple_clear_all_404s',
            nonce: seo_simple_404_monitor.clear_nonce
        }, function(response) {
            if (response.success) {
                showAlert(response.data.message || seo_simple_404_monitor.success_cleared, 'success');
                loadStats();
                loadLogs();
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Export 404 errors
     */
    function export404s() {
        window.location.href = ajaxurl + '?action=seo_simple_export_404s&nonce=' + seo_simple_404_monitor.export_nonce;
    }

    /**
     * Ignore or unignore a URL
     */
    function ignoreUrl(id, isIgnored) {
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_ignore_url',
            nonce: seo_simple_404_monitor.nonce,
            id: id
        }, function(response) {
            if (response.success) {
                showAlert(response.data.message || (isIgnored ? seo_simple_404_monitor.success_unignored : seo_simple_404_monitor.success_ignored), 'success');
                loadLogs(); // Reload logs
                loadStats(); // Reload stats
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Create a redirect for a 404 URL
     */
    function createRedirect(id, redirectUrl) {
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_create_redirect',
            nonce: seo_simple_404_monitor.nonce,
            id: id,
            redirect_to: redirectUrl
        }, function(response) {
            if (response.success) {
                showAlert(response.data.message || seo_simple_404_monitor.success_redirect, 'success');
                loadLogs(); // Reload logs
                loadStats(); // Reload stats
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction() {
        var action = $('#bulk-action-selector-top').val();
        if (action === '-1') {
            return;
        }
        
        var selectedIds = [];
        $('input[name="log[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length === 0) {
            showAlert(seo_simple_404_monitor.error_no_selection, 'warning');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm(seo_simple_404_monitor.confirm_bulk_delete)) {
                return;
            }
            bulkAction('delete', selectedIds);
        } else if (action === 'redirect') {
            var redirectUrl = prompt(seo_simple_404_monitor.prompt_bulk_redirect, '/');
            if (redirectUrl) {
                bulkActionRedirect(selectedIds, redirectUrl);
            }
        }
    }

    /**
     * Perform bulk action
     */
    function bulkAction(action, ids) {
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_bulk_action',
            nonce: seo_simple_404_monitor.nonce,
            bulk_action: action,
            ids: ids
        }, function(response) {
            if (response.success) {
                showAlert(response.data.message || seo_simple_404_monitor.success_bulk_action, 'success');
                loadLogs();
                loadStats();
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Create bulk redirects
     */
    function bulkActionRedirect(ids, redirectUrl) {
        $.post(ajaxurl, {
            action: 'seo_simple_404_monitor_bulk_redirect',
            nonce: seo_simple_404_monitor.nonce,
            ids: ids,
            redirect_to: redirectUrl
        }, function(response) {
            if (response.success) {
                showAlert(response.data.message || seo_simple_404_monitor.success_bulk_redirect, 'success');
                loadLogs();
                loadStats();
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Save settings
     */
    function saveSettings() {
        var formData = $('#seo-simple-404-settings-form').serialize();
        
        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                showAlert(response.data.message || seo_simple_404_monitor.success_settings, 'success');
            } else {
                showAlert(response.data.message || seo_simple_404_monitor.error_general, 'error');
            }
        }).fail(function() {
            showAlert(seo_simple_404_monitor.error_connection, 'error');
        });
    }

    /**
     * Show loading indicator
     */
    function showLoading(container) {
        $(container).append('<div class="seo-simple-loading-overlay"></div>');
    }

    /**
     * Hide loading indicator
     */
    function hideLoading(container) {
        $(container).find('.seo-simple-loading-overlay').remove();
    }

    /**
     * Show alert message
     */
    function showAlert(message, type) {
        type = type || 'info';
        
        var alertClass = 'seo-simple-alert seo-simple-alert-' + type;
        var alertHtml = '<div class="' + alertClass + '">' + message + '</div>';
        
        // Remove any existing alerts
        $('.seo-simple-alert').remove();
        
        // Add the new alert at the top of the wrapper
        $('.seo-simple-404-wrapper').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.seo-simple-alert').fadeOut();
        }, 5000);
    }

})(jQuery);