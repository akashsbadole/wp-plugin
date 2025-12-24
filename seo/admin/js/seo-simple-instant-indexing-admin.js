/**
 * JavaScript for Instant Indexing admin functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize tabs
    $('.seo-simple-indexing-tabs .tab-button').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Update button states
        $('.seo-simple-indexing-tabs .tab-button').removeClass('active');
        $(this).addClass('active');
        
        // Update tab content
        $('.seo-simple-indexing-tab').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Handle settings form submission
    $('#seo-simple-indexing-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        
        // Show loading state
        submitButton.prop('disabled', true).text('Saving...');
        
        // Collect form data
        var formData = {
            action: 'seo_simple_save_indexing_settings',
            nonce: seo_simple_instant_indexing_admin.nonce,
            google_indexing_enabled: form.find('#google-indexing-enabled').is(':checked'),
            google_indexing_api_key: form.find('#google-indexing-api-key').val(),
            bing_indexing_enabled: form.find('#bing-indexing-enabled').is(':checked'),
            bing_indexing_api_key: form.find('#bing-indexing-api-key').val(),
            yandex_indexing_enabled: form.find('#yandex-indexing-enabled').is(':checked'),
            yandex_indexing_api_key: form.find('#yandex-indexing-api-key').val(),
            yandex_indexing_user_id: form.find('#yandex-indexing-user-id').val(),
            yandex_indexing_host_id: form.find('#yandex-indexing-host-id').val(),
            auto_indexing_enabled: form.find('#auto-indexing-enabled').is(':checked'),
            indexing_daily_limit: form.find('#indexing-daily-limit').val()
        };
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_instant_indexing_admin.ajax_url,
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

    // Handle single URL submission
    $('#seo-simple-submit-url').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var url = $('#seo-simple-url').val();
        var engines = [];
        
        // Collect selected engines
        $('#seo-simple-engines input:checked').each(function() {
            engines.push($(this).val());
        });
        
        if (!url) {
            showNotice('Please enter a URL.', 'error');
            return;
        }
        
        if (engines.length === 0) {
            showNotice('Please select at least one search engine.', 'error');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('Submitting...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_instant_indexing_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_submit_indexing',
                nonce: seo_simple_instant_indexing_admin.nonce,
                url: url,
                engines: engines
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Display results
                    displaySubmissionResults(response.data.results);
                    
                    // Refresh stats
                    refreshStats();
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while submitting URL.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle bulk submission
    $('#seo-simple-bulk-submit').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var postIds = [];
        var engines = [];
        
        // Collect selected posts
        $('#seo-simple-posts-table input:checked').each(function() {
            postIds.push($(this).val());
        });
        
        // Collect selected engines
        $('#seo-simple-bulk-engines input:checked').each(function() {
            engines.push($(this).val());
        });
        
        if (postIds.length === 0) {
            showNotice('Please select at least one post.', 'error');
            return;
        }
        
        if (engines.length === 0) {
            showNotice('Please select at least one search engine.', 'error');
            return;
        }
        
        if (!confirm(seo_simple_instant_indexing_admin.strings.confirm_bulk_indexing)) {
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text(seo_simple_instant_indexing_admin.strings.submitting);
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_instant_indexing_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_bulk_indexing',
                nonce: seo_simple_instant_indexing_admin.nonce,
                post_ids: postIds,
                engines: engines
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Display results
                    displayBulkResults(response.data.results);
                    
                    // Refresh stats and recent submissions
                    refreshStats();
                    refreshRecentSubmissions();
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred during bulk submission.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle post selection toggle
    $('#seo-simple-select-all').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('#seo-simple-posts-table input[type="checkbox"]').prop('checked', isChecked);
    });

    // Handle history pagination
    $(document).on('click', '.seo-simple-history-pagination a', function(e) {
        e.preventDefault();
        
        var page = $(this).data('page');
        
        if (page) {
            loadHistoryPage(page);
        }
    });

    // Handle clear history
    $('#seo-simple-clear-history').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(seo_simple_instant_indexing_admin.strings.confirm_clear_history)) {
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Clearing...');
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_instant_indexing_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_clear_indexing_history',
                nonce: seo_simple_instant_indexing_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Clear history table
                    $('#seo-simple-history-table tbody').empty();
                    $('#seo-simple-history-pagination').empty();
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while clearing history.', 'error');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Display submission results
    function displaySubmissionResults(results) {
        var container = $('#seo-simple-submission-results');
        
        // Clear existing content
        container.empty();
        
        // Create results table
        var table = $('<table class="wp-list-table widefat fixed striped"><thead><tr><th>Search Engine</th><th>Status</th><th>Message</th></tr></thead><tbody></tbody></table>');
        var tbody = table.find('tbody');
        
        for (var engine in results) {
            var result = results[engine];
            var statusClass = result.success ? 'success' : 'error';
            var statusText = result.success ? 'Success' : 'Failed';
            
            var row = $('<tr></tr>');
            row.append('<td>' + engine.charAt(0).toUpperCase() + engine.slice(1) + '</td>');
            row.append('<td class="' + statusClass + '">' + statusText + '</td>');
            row.append('<td>' + result.message + '</td>');
            
            tbody.append(row);
        }
        
        container.append(table);
        container.show();
    }

    // Display bulk results
    function displayBulkResults(results) {
        var container = $('#seo-simple-bulk-results');
        
        // Clear existing content
        container.empty();
        
        // Create summary
        var successCount = 0;
        var failureCount = 0;
        
        for (var postId in results) {
            if (results[postId].success) {
                successCount++;
            } else {
                failureCount++;
            }
        }
        
        var summary = $('<div class="seo-simple-bulk-summary"></div>');
        summary.append('<p><strong>Summary:</strong> ' + successCount + ' successful, ' + failureCount + ' failed</p>');
        container.append(summary);
        
        // Create results table
        var table = $('<table class="wp-list-table widefat fixed striped"><thead><tr><th>Post</th><th>URL</th><th>Results</th></tr></thead><tbody></tbody></table>');
        var tbody = table.find('tbody');
        
        for (var postId in results) {
            var result = results[postId];
            var statusClass = result.success ? 'success' : 'error';
            
            var row = $('<tr></tr>');
            row.append('<td><a href="' + result.url + '" target="_blank">' + result.title + '</a></td>');
            row.append('<td>' + result.url + '</td>');
            
            var resultsCell = $('<td></td>');
            for (var engine in result.results) {
                var engineResult = result.results[engine];
                var engineStatusClass = engineResult.success ? 'success' : 'error';
                resultsCell.append('<div class="engine-result ' + engineStatusClass + '">' + engine + ': ' + engineResult.message + '</div>');
            }
            row.append(resultsCell);
            
            tbody.append(row);
        }
        
        container.append(table);
        container.show();
    }

    // Load history page
    function loadHistoryPage(page) {
        var perPage = 20;
        
        // Send AJAX request
        $.ajax({
            url: seo_simple_instant_indexing_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_get_indexing_history',
                nonce: seo_simple_instant_indexing_admin.nonce,
                page: page,
                per_page: perPage
            },
            success: function(response) {
                if (response.success) {
                    displayHistory(response.data);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while loading history.', 'error');
            }
        });
    }

    // Display history
    function displayHistory(data) {
        var tbody = $('#seo-simple-history-table tbody');
        var pagination = $('#seo-simple-history-pagination');
        
        // Clear existing content
        tbody.empty();
        pagination.empty();
        
        // Add history rows
        for (var i = 0; i < data.history.length; i++) {
            var entry = data.history[i];
            var row = $('<tr></tr>');
            
            row.append('<td>' + entry.timestamp + '</td>');
            row.append('<td>' + entry.user + '</td>');
            row.append('<td>' + entry.post_count + '</td>');
            row.append('<td>' + entry.engines + '</td>');
            row.append('<td>' + entry.success_count + ' / ' + entry.total_count + ' (' + entry.success_rate + '%)</td>');
            
            tbody.append(row);
        }
        
        // Add pagination
        if (data.total_pages > 1) {
            var paginationHtml = '<div class="tablenav"><div class="tablenav-pages">';
            
            if (data.page > 1) {
                paginationHtml += '<a class="prev-page" data-page="' + (data.page - 1) + '">&laquo;</a>';
            }
            
            for (var i = 1; i <= data.total_pages; i++) {
                var activeClass = i === data.page ? 'current' : '';
                paginationHtml += '<a class="' + activeClass + '" data-page="' + i + '">' + i + '</a>';
            }
            
            if (data.page < data.total_pages) {
                paginationHtml += '<a class="next-page" data-page="' + (data.page + 1) + '">&raquo;</a>';
            }
            
            paginationHtml += '</div></div>';
            pagination.html(paginationHtml);
        }
    }

    // Refresh stats
    function refreshStats() {
        // This would typically be done via AJAX, but for simplicity we'll reload the page
        window.location.reload();
    }

    // Refresh recent submissions
    function refreshRecentSubmissions() {
        // This would typically be done via AJAX, but for simplicity we'll reload the page
        window.location.reload();
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
});