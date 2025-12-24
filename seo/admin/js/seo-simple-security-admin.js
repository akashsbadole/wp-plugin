/**
 * JavaScript for the Security admin interface.
 *
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    // Run security scan
    $('#seo-simple-run-scan').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $results = $('#seo-simple-scan-results');
        
        // Show loading state
        $button.prop('disabled', true).text(seo_simple_security_admin.strings.scan_running);
        $results.empty().append('<div class="spinner is-active"></div><p>' + seo_simple_security_admin.strings.scan_running + '</p>');
        
        // Run scan
        $.ajax({
            url: seo_simple_security_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_run_security_scan',
                nonce: seo_simple_security_admin.nonce_scan
            },
            success: function(response) {
                if (response.success) {
                    $results.empty().html(renderScanResults(response.data.results));
                    $button.text(seo_simple_security_admin.strings.scan_complete);
                    
                    // Show notification
                    showNotification(seo_simple_security_admin.strings.scan_complete, 'success');
                } else {
                    $results.empty().html('<p class="error">' + response.data.message + '</p>');
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                $results.empty().html('<p class="error">' + seo_simple_security_admin.strings.error + '</p>');
                showNotification(seo_simple_security_admin.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Block IP
    $(document).on('click', '.seo-simple-block-ip', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var ip = $button.data('ip');
        
        if (!confirm(seo_simple_security_admin.strings.confirm_block)) {
            return;
        }
        
        // Block IP
        $.ajax({
            url: seo_simple_security_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_block_ip',
                ip: ip,
                nonce: seo_simple_security_admin.nonce_block
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    $button.closest('tr').remove();
                    
                    // Update blocked IPs count
                    var $count = $('.seo-simple-blocked-ips-count');
                    $count.text(parseInt($count.text()) + 1);
                    
                    showNotification(seo_simple_security_admin.strings.ip_blocked, 'success');
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification(seo_simple_security_admin.strings.error, 'error');
            }
        });
    });

    // Unblock IP
    $(document).on('click', '.seo-simple-unblock-ip', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var ip = $button.data('ip');
        
        if (!confirm(seo_simple_security_admin.strings.confirm_unblock)) {
            return;
        }
        
        // Unblock IP
        $.ajax({
            url: seo_simple_security_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_unblock_ip',
                ip: ip,
                nonce: seo_simple_security_admin.nonce_unblock
            },
            success: function(response) {
                if (response.success) {
                    // Update UI
                    $button.closest('tr').remove();
                    
                    // Update blocked IPs count
                    var $count = $('.seo-simple-blocked-ips-count');
                    $count.text(Math.max(0, parseInt($count.text()) - 1));
                    
                    showNotification(seo_simple_security_admin.strings.ip_unblocked, 'success');
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification(seo_simple_security_admin.strings.error, 'error');
            }
        });
    });

    // Load security logs
    $('#seo-simple-load-logs').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $logs = $('#seo-simple-security-logs');
        var limit = $button.data('limit') || 50;
        
        // Show loading state
        $button.prop('disabled', true);
        $logs.empty().append('<div class="spinner is-active"></div>');
        
        // Load logs
        $.ajax({
            url: seo_simple_security_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_get_security_logs',
                limit: limit,
                nonce: seo_simple_security_admin.nonce_logs
            },
            success: function(response) {
                if (response.success) {
                    $logs.empty().html(renderSecurityLogs(response.data.logs));
                } else {
                    $logs.empty().html('<p class="error">' + response.data.message + '</p>');
                }
            },
            error: function() {
                $logs.empty().html('<p class="error">' + seo_simple_security_admin.strings.error + '</p>');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Update threat feed
    $('#update-threat-feed').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $status = $('#threat-feed-update-status');
        
        // Show loading state
        $button.prop('disabled', true).text(seo_simple_security_admin.strings.updating);
        $status.empty().append('<div class="spinner is-active inline"></div>');
        
        // Update threat feed
        $.ajax({
            url: seo_simple_security_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_update_threat_feed',
                nonce: seo_simple_security_admin.nonce_threat_feed
            },
            success: function(response) {
                if (response.success) {
                    $status.empty().text(response.data.message);
                    
                    // Update counts
                    if (response.data.rules_updated !== undefined) {
                        $('.seo-simple-threat-rules-count').text(response.data.rules_updated);
                    }
                    if (response.data.ips_updated !== undefined) {
                        $('.seo-simple-threat-ips-count').text(response.data.ips_updated);
                    }
                    
                    // Show notification
                    showNotification(response.data.message, 'success');
                    
                    // Reload page after a short delay to show updated status
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    $status.empty().text(response.data.message);
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                $status.empty().text(seo_simple_security_admin.strings.error);
                showNotification(seo_simple_security_admin.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(seo_simple_security_admin.strings.update_now);
            }
        });
    });

    // Tab navigation
    $('.seo-simple-tabs').on('click', '.seo-simple-tab', function(e) {
        e.preventDefault();
        
        var $tab = $(this);
        var $tabs = $tab.closest('.seo-simple-tabs');
        var $content = $tabs.next('.seo-simple-tab-content');
        var target = $tab.data('tab');
        
        // Update active tab
        $tabs.find('.seo-simple-tab').removeClass('active');
        $tab.addClass('active');
        
        // Update active content
        $content.find('.seo-simple-tab-pane').removeClass('active');
        $content.find('#' + target).addClass('active');
    });

    // Render scan results
    function renderScanResults(results) {
        var html = '<div class="seo-simple-scan-results">';
        
        // Summary
        html += '<div class="seo-simple-scan-summary">';
        html += '<h3>' + seo_simple_security_admin.strings.scan_complete + '</h3>';
        html += '<p>' + seo_simple_security_admin.strings.scanned_files + ': ' + results.scanned_files + '</p>';
        html += '<p>' + seo_simple_security_admin.strings.issues_found + ': ' + results.issues.length + '</p>';
        html += '</div>';
        
        // Issues
        if (results.issues.length > 0) {
            html += '<div class="seo-simple-scan-issues">';
            html += '<h4>' + seo_simple_security_admin.strings.issues_found + '</h4>';
            html += '<table class="wp-list-table widefat fixed striped">';
            html += '<thead><tr><th>' + seo_simple_security_admin.strings.type + '</th><th>' + seo_simple_security_admin.strings.file + '</th><th>' + seo_simple_security_admin.strings.message + '</th></tr></thead>';
            html += '<tbody>';
            
            for (var i = 0; i < results.issues.length; i++) {
                var issue = results.issues[i];
                html += '<tr>';
                html += '<td>' + issue.type + '</td>';
                html += '<td>' + issue.file + '</td>';
                html += '<td>' + issue.message + '</td>';
                html += '</tr>';
            }
            
            html += '</tbody></table>';
            html += '</div>';
        } else {
            html += '<div class="seo-simple-scan-no-issues">';
            html += '<p>' + seo_simple_security_admin.strings.no_issues + '</p>';
            html += '</div>';
        }
        
        html += '</div>';
        
        return html;
    }

    // Render security logs
    function renderSecurityLogs(logs) {
        var html = '';
        
        if (logs.length === 0) {
            html = '<p>' + seo_simple_security_admin.strings.no_logs + '</p>';
        } else {
            html = '<table class="wp-list-table widefat fixed striped">';
            html += '<thead><tr><th>' + seo_simple_security_admin.strings.timestamp + '</th><th>' + seo_simple_security_admin.strings.event + '</th><th>' + seo_simple_security_admin.strings.details + '</th></tr></thead>';
            html += '<tbody>';
            
            for (var i = 0; i < logs.length; i++) {
                var log = logs[i];
                html += '<tr>';
                html += '<td>' + log.timestamp + '</td>';
                html += '<td>' + log.event + '</td>';
                html += '<td>' + JSON.stringify(log.data) + '</td>';
                html += '</tr>';
            }
            
            html += '</tbody></table>';
        }
        
        return html;
    }

    // Show notification
    function showNotification(message, type) {
        var $notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('body').append($notification);
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $notification.fadeOut(function() {
                $notification.remove();
            });
        }, 3000);
        
        // Dismissible
        $notification.on('click', '.notice-dismiss', function() {
            $notification.fadeOut(function() {
                $notification.remove();
            });
        });
    }
});