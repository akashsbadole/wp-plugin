/**
 * JavaScript for Google integration admin functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize tabs
    $('.seo-simple-google-tabs .tab-button').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Update button states
        $('.seo-simple-google-tabs .tab-button').removeClass('active');
        $(this).addClass('active');
        
        // Update tab content
        $('.seo-simple-google-tab').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Initialize date pickers
    $('.seo-simple-date-picker').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: 0
        });
    });

    // Initialize date range presets
    $('.seo-simple-date-preset').on('click', function(e) {
        e.preventDefault();
        
        var preset = $(this).data('preset');
        var startDate = $('#start-date');
        var endDate = $('#end-date');
        
        switch (preset) {
            case '7days':
                startDate.datepicker('setDate', -7);
                endDate.datepicker('setDate', 0);
                break;
            case '30days':
                startDate.datepicker('setDate', -30);
                endDate.datepicker('setDate', 0);
                break;
            case '90days':
                startDate.datepicker('setDate', -90);
                endDate.datepicker('setDate', 0);
                break;
            case '1year':
                startDate.datepicker('setDate', -365);
                endDate.datepicker('setDate', 0);
                break;
        }
    });

    // Handle settings form submission
    $('#seo-simple-google-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        
        // Show loading state
        submitButton.prop('disabled', true).text('Saving...');
        
        // Collect form data
        var formData = {
            action: 'seo_simple_google_save_settings',
            nonce: seo_simple_google_admin.nonce,
            google_analytics_enabled: form.find('#google-analytics-enabled').is(':checked'),
            google_analytics_id: form.find('#google-analytics-id').val(),
            google_analytics_version: form.find('#google-analytics-version').val(),
            google_analytics_anonymize_ip: form.find('#google-analytics-anonymize-ip').is(':checked'),
            google_analytics_track_outbound: form.find('#google-analytics-track-outbound').is(':checked'),
            google_analytics_ecommerce: form.find('#google-analytics-ecommerce').is(':checked'),
            google_adsense_enabled: form.find('#google-adsense-enabled').is(':checked'),
            google_adsense_id: form.find('#google-adsense-id').val(),
            google_site_verification: form.find('#google-site-verification').val(),
            google_search_console_enabled: form.find('#google-search-console-enabled').is(':checked')
        };
        
        // Collect excluded roles
        formData.google_analytics_exclude_roles = [];
        form.find('#google-analytics-exclude-roles input:checked').each(function() {
            formData.google_analytics_exclude_roles.push($(this).val());
        });
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while saving settings.', 'error');
            },
            complete: function() {
                // Restore button state
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle Google Search Console authentication
    $('#seo-simple-google-authenticate').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var site = $('#google-search-console-site').val();
        
        if (!site) {
            showNotice('Please enter your site URL.', 'error');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text(seo_simple_google_admin.strings.authenticating);
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_google_authenticate',
                nonce: seo_simple_google_admin.nonce,
                site: site
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Reload the page to update the UI
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred during authentication.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle Google Search Console revoke access
    $('#seo-simple-google-revoke-access').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(seo_simple_google_admin.strings.confirm_revoke)) {
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Revoking...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_google_revoke_access',
                nonce: seo_simple_google_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Reload the page to update the UI
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while revoking access.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle connection tests
    $('.seo-simple-connection-test').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var service = button.data('service');
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Testing...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_google_test_connection',
                nonce: seo_simple_google_admin.nonce,
                service: service
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred during connection test.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle Analytics data fetching
    $('#seo-simple-fetch-analytics-data').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var startDate = $('#analytics-start-date').val();
        var endDate = $('#analytics-end-date').val();
        var metrics = [];
        var dimensions = [];
        
        // Collect selected metrics
        $('#analytics-metrics input:checked').each(function() {
            metrics.push($(this).val());
        });
        
        // Collect selected dimensions
        $('#analytics-dimensions input:checked').each(function() {
            dimensions.push($(this).val());
        });
        
        if (!startDate || !endDate) {
            showNotice('Please select a date range.', 'error');
            return;
        }
        
        if (metrics.length === 0) {
            showNotice('Please select at least one metric.', 'error');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('Loading...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_google_analytics_data',
                nonce: seo_simple_google_admin.nonce,
                start_date: startDate,
                end_date: endDate,
                metrics: metrics,
                dimensions: dimensions
            },
            success: function(response) {
                if (response.success) {
                    displayAnalyticsData(response.data);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while fetching Analytics data.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle Search Console data fetching
    $('#seo-simple-fetch-search-console-data').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var startDate = $('#search-console-start-date').val();
        var endDate = $('#search-console-end-date').val();
        var dimensions = [];
        
        // Collect selected dimensions
        $('#search-console-dimensions input:checked').each(function() {
            dimensions.push($(this).val());
        });
        
        if (!startDate || !endDate) {
            showNotice('Please select a date range.', 'error');
            return;
        }
        
        if (dimensions.length === 0) {
            showNotice('Please select at least one dimension.', 'error');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('Loading...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_google_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_google_search_console_data',
                nonce: seo_simple_google_admin.nonce,
                start_date: startDate,
                end_date: endDate,
                dimensions: dimensions
            },
            success: function(response) {
                if (response.success) {
                    displaySearchConsoleData(response.data);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while fetching Search Console data.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Display Analytics data
    function displayAnalyticsData(data) {
        var container = $('#seo-simple-analytics-data-container');
        
        // Clear existing content
        container.empty();
        
        // Create summary stats
        var summaryHtml = '<div class="seo-simple-stats-grid">';
        
        if (data.total_sessions !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + number_format(data.total_sessions) + '</div><div class="seo-simple-stat-label">Sessions</div></div>';
        }
        
        if (data.total_users !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + number_format(data.total_users) + '</div><div class="seo-simple-stat-label">Users</div></div>';
        }
        
        if (data.total_pageviews !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + number_format(data.total_pageviews) + '</div><div class="seo-simple-stat-label">Pageviews</div></div>';
        }
        
        if (data.bounce_rate !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + data.bounce_rate.toFixed(2) + '%</div><div class="seo-simple-stat-label">Bounce Rate</div></div>';
        }
        
        if (data.avg_session_duration !== undefined) {
            var minutes = Math.floor(data.avg_session_duration / 60);
            var seconds = Math.floor(data.avg_session_duration % 60);
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds + '</div><div class="seo-simple-stat-label">Avg. Session Duration</div></div>';
        }
        
        summaryHtml += '</div>';
        
        // Add summary to container
        container.append(summaryHtml);
        
        // Add top pages if available
        if (data.top_pages && data.top_pages.length > 0) {
            var pagesHtml = '<h3>Top Pages</h3><table class="wp-list-table widefat fixed striped"><thead><tr><th>Page</th><th>Pageviews</th><th>Sessions</th></tr></thead><tbody>';
            
            for (var i = 0; i < data.top_pages.length; i++) {
                var page = data.top_pages[i];
                pagesHtml += '<tr><td>' + page.page + '</td><td>' + number_format(page.pageviews) + '</td><td>' + number_format(page.sessions) + '</td></tr>';
            }
            
            pagesHtml += '</tbody></table>';
            container.append(pagesHtml);
        }
        
        // Show the container
        container.show();
    }

    // Display Search Console data
    function displaySearchConsoleData(data) {
        var container = $('#seo-simple-search-console-data-container');
        
        // Clear existing content
        container.empty();
        
        // Create summary stats
        var summaryHtml = '<div class="seo-simple-stats-grid">';
        
        if (data.total_clicks !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + number_format(data.total_clicks) + '</div><div class="seo-simple-stat-label">Total Clicks</div></div>';
        }
        
        if (data.total_impressions !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + number_format(data.total_impressions) + '</div><div class="seo-simple-stat-label">Total Impressions</div></div>';
        }
        
        if (data.avg_ctr !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + data.avg_ctr.toFixed(2) + '%</div><div class="seo-simple-stat-label">Avg. CTR</div></div>';
        }
        
        if (data.avg_position !== undefined) {
            summaryHtml += '<div class="seo-simple-stat"><div class="seo-simple-stat-number">' + data.avg_position.toFixed(1) + '</div><div class="seo-simple-stat-label">Avg. Position</div></div>';
        }
        
        summaryHtml += '</div>';
        
        // Add summary to container
        container.append(summaryHtml);
        
        // Add top queries if available
        if (data.top_queries && data.top_queries.length > 0) {
            var queriesHtml = '<h3>Top Queries</h3><table class="wp-list-table widefat fixed striped"><thead><tr><th>Query</th><th>Clicks</th><th>Impressions</th><th>CTR</th><th>Position</th></tr></thead><tbody>';
            
            for (var i = 0; i < data.top_queries.length; i++) {
                var query = data.top_queries[i];
                queriesHtml += '<tr><td>' + query.query + '</td><td>' + number_format(query.clicks) + '</td><td>' + number_format(query.impressions) + '</td><td>' + query.ctr.toFixed(2) + '%</td><td>' + query.position.toFixed(1) + '</td></tr>';
            }
            
            queriesHtml += '</tbody></table>';
            container.append(queriesHtml);
        }
        
        // Add top pages if available
        if (data.top_pages && data.top_pages.length > 0) {
            var pagesHtml = '<h3>Top Pages</h3><table class="wp-list-table widefat fixed striped"><thead><tr><th>Page</th><th>Clicks</th><th>Impressions</th><th>CTR</th><th>Position</th></tr></thead><tbody>';
            
            for (var i = 0; i < data.top_pages.length; i++) {
                var page = data.top_pages[i];
                pagesHtml += '<tr><td>' + page.page + '</td><td>' + number_format(page.clicks) + '</td><td>' + number_format(page.impressions) + '</td><td>' + page.ctr.toFixed(2) + '%</td><td>' + page.position.toFixed(1) + '</td></tr>';
            }
            
            pagesHtml += '</tbody></table>';
            container.append(pagesHtml);
        }
        
        // Show the container
        container.show();
    }

    // Helper function to show notices
    function showNotice(message, type) {
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Add to top of page
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Handle dismiss button
        notice.on('click', '.notice-dismiss', function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }

    // Helper function for number formatting
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});