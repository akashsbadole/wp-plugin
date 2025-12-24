/**
 * JavaScript for the Speed Optimization admin interface.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for you within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practice to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    // The main Speed Optimization object
    window.seoSimpleSpeedOptimization = {
        
        /**
         * Initialize the Speed Optimization functionality.
         */
        init: function() {
            this.bindEvents();
            this.initCharts();
            this.initTooltips();
        },

        /**
         * Bind events to DOM elements.
         */
        bindEvents: function() {
            // Settings form submission
            $(document).on('submit', '#seo-simple-speed-settings-form', this.handleSettingsSubmit.bind(this));
            
            // Clear cache button
            $(document).on('click', '#seo-simple-clear-cache-btn', this.handleClearCache.bind(this));
            
            // Run speed test button
            $(document).on('click', '#seo-simple-run-test-btn', this.handleRunSpeedTest.bind(this));
            
            // Tab navigation
            $(document).on('click', '.seo-simple-speed-tab', this.handleTabClick.bind(this));
            
            // Toggle switches
            $(document).on('change', '.seo-simple-speed-toggle', this.handleToggleChange.bind(this));
            
            // Test individual features
            $(document).on('click', '.seo-simple-test-feature', this.handleTestFeature.bind(this));
            
            // Export settings
            $(document).on('click', '#seo-simple-export-settings', this.handleExportSettings.bind(this));
            
            // Import settings
            $(document).on('click', '#seo-simple-import-settings', this.handleImportSettings.bind(this));
            
            // Reset settings
            $(document).on('click', '#seo-simple-reset-settings', this.handleResetSettings.bind(this));
            
            // View cache details
            $(document).on('click', '.seo-simple-view-cache-details', this.handleViewCacheDetails.bind(this));
            
            // Delete cache file
            $(document).on('click', '.seo-simple-delete-cache-file', this.handleDeleteCacheFile.bind(this));
            
            // Optimize images
            $(document).on('click', '#seo-simple-optimize-images-btn', this.handleOptimizeImages.bind(this));
            
            // Generate WebP images
            $(document).on('click', '#seo-simple-generate-webp-btn', this.handleGenerateWebp.bind(this));
            
            // Download Google Fonts
            $(document).on('click', '#seo-simple-download-fonts-btn', this.handleDownloadFonts.bind(this));
        },

        /**
         * Initialize charts.
         */
        initCharts: function() {
            // Speed test results chart
            const speedTestCanvas = document.getElementById('seo-simple-speed-test-chart');
            if (speedTestCanvas) {
                this.renderSpeedTestChart(speedTestCanvas);
            }

            // Cache statistics chart
            const cacheStatsCanvas = document.getElementById('seo-simple-cache-stats-chart');
            if (cacheStatsCanvas) {
                this.renderCacheStatsChart(cacheStatsCanvas);
            }
        },

        /**
         * Initialize tooltips.
         */
        initTooltips: function() {
            $('.seo-simple-tooltip').each(function() {
                $(this).tooltipster({
                    theme: 'tooltipster-shadow',
                    contentAsHTML: true,
                    interactive: true,
                    maxWidth: 300
                });
            });
        },

        /**
         * Handle settings form submission.
         *
         * @param {Event} event The form submission event.
         */
        handleSettingsSubmit: function(event) {
            event.preventDefault();
            
            const $form = $(event.target);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Show loading state
            $submitBtn.prop('disabled', true).text(seo_simple_speed_optimization.strings.saving || 'Saving...');
            
            // Submit form via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=seo_simple_save_speed_settings&nonce=' + seo_simple_speed_optimization.nonce,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle clear cache button click.
         *
         * @param {Event} event The click event.
         */
        handleClearCache: function(event) {
            event.preventDefault();
            
            if (!confirm(seo_simple_speed_optimization.strings.confirm_clear_cache)) {
                return;
            }
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            // Show loading state
            $button.prop('disabled', true).text('Clearing...');
            
            // Clear cache via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_clear_speed_cache',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        // Refresh cache statistics
                        this.refreshCacheStats();
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle run speed test button click.
         *
         * @param {Event} event The click event.
         */
        handleRunSpeedTest: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            // Show loading state
            $button.prop('disabled', true).text(seo_simple_speed_optimization.strings.test_running || 'Running speed test...');
            
            // Show progress indicator
            this.showProgressIndicator('Running speed test...');
            
            // Run speed test via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_run_speed_test',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(seo_simple_speed_optimization.strings.test_completed || 'Speed test completed!', 'success');
                        this.updateSpeedTestResults(response.data.results);
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                    this.hideProgressIndicator();
                }.bind(this)
            });
        },

        /**
         * Handle tab click.
         *
         * @param {Event} event The click event.
         */
        handleTabClick: function(event) {
            event.preventDefault();
            
            const $tab = $(event.target);
            const tabId = $tab.data('tab');
            
            // Update active tab
            $('.seo-simple-speed-tab').removeClass('active');
            $tab.addClass('active');
            
            // Show corresponding content
            $('.seo-simple-speed-tab-content').hide();
            $('#' + tabId).show();
        },

        /**
         * Handle toggle change.
         *
         * @param {Event} event The change event.
         */
        handleToggleChange: function(event) {
            const $toggle = $(event.target);
            const feature = $toggle.data('feature');
            const isEnabled = $toggle.is(':checked');
            
            // Show confirmation for certain features
            if (feature === 'speed_page_caching' && isEnabled) {
                if (!confirm('Enabling page caching will significantly improve your site speed. However, you may need to clear the cache manually to see changes. Continue?')) {
                    $toggle.prop('checked', false);
                    return;
                }
            }
            
            // Auto-save the setting
            this.saveSingleSetting(feature, isEnabled);
        },

        /**
         * Handle test feature click.
         *
         * @param {Event} event The click event.
         */
        handleTestFeature: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const feature = $button.data('feature');
            
            // Show loading state
            $button.prop('disabled', true).text('Testing...');
            
            // Test feature via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_test_speed_feature',
                    feature: feature,
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        this.updateFeatureTestResult(feature, response.data.result);
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text('Test');
                }
            });
        },

        /**
         * Handle export settings click.
         *
         * @param {Event} event The click event.
         */
        handleExportSettings: function(event) {
            event.preventDefault();
            
            // Export settings via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_export_speed_settings',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Create and download file
                        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data.settings, null, 2));
                        const downloadAnchorNode = document.createElement('a');
                        downloadAnchorNode.setAttribute("href", dataStr);
                        downloadAnchorNode.setAttribute("download", "seo-simple-speed-settings.json");
                        document.body.appendChild(downloadAnchorNode);
                        downloadAnchorNode.click();
                        downloadAnchorNode.remove();
                        
                        this.showNotice('Settings exported successfully!', 'success');
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this)
            });
        },

        /**
         * Handle import settings click.
         *
         * @param {Event} event The click event.
         */
        handleImportSettings: function(event) {
            event.preventDefault();
            
            // Create file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.json';
            
            fileInput.onchange = function(e) {
                const file = e.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const settings = JSON.parse(e.target.result);
                        
                        // Import settings via AJAX
                        $.ajax({
                            url: seo_simple_speed_optimization.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'seo_simple_import_speed_settings',
                                settings: settings,
                                nonce: seo_simple_speed_optimization.nonce
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    this.showNotice(response.data.message, 'success');
                                    // Reload page to show new settings
                                    location.reload();
                                } else {
                                    this.showNotice(response.data.message, 'error');
                                }
                            }.bind(this),
                            error: function() {
                                this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                            }.bind(this)
                        });
                    } catch (error) {
                        this.showNotice('Invalid settings file. Please upload a valid JSON file.', 'error');
                    }
                }.bind(this);
                
                reader.readAsText(file);
            }.bind(this);
            
            fileInput.click();
        },

        /**
         * Handle reset settings click.
         *
         * @param {Event} event The click event.
         */
        handleResetSettings: function(event) {
            event.preventDefault();
            
            if (!confirm('Are you sure you want to reset all speed optimization settings to their defaults? This action cannot be undone.')) {
                return;
            }
            
            // Reset settings via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_reset_speed_settings',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        // Reload page to show default settings
                        location.reload();
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this)
            });
        },

        /**
         * Handle view cache details click.
         *
         * @param {Event} event The click event.
         */
        handleViewCacheDetails: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const cacheType = $button.data('cache-type');
            
            // Show loading state
            $button.prop('disabled', true).text('Loading...');
            
            // Get cache details via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_get_cache_details',
                    cache_type: cacheType,
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showCacheDetailsModal(response.data.details);
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text('View Details');
                }
            });
        },

        /**
         * Handle delete cache file click.
         *
         * @param {Event} event The click event.
         */
        handleDeleteCacheFile: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const file = $button.data('file');
            
            if (!confirm('Are you sure you want to delete this cache file?')) {
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Deleting...');
            
            // Delete cache file via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_delete_cache_file',
                    file: file,
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        // Remove file from list
                        $button.closest('tr').remove();
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text('Delete');
                }
            });
        },

        /**
         * Handle optimize images click.
         *
         * @param {Event} event The click event.
         */
        handleOptimizeImages: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            if (!confirm('This will optimize all images on your site. For large sites, this may take a long time. Continue?')) {
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Optimizing...');
            this.showProgressIndicator('Optimizing images...');
            
            // Optimize images via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_optimize_images',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        this.updateOptimizationProgress(response.data.progress);
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                    this.hideProgressIndicator();
                }.bind(this)
            });
        },

        /**
         * Handle generate WebP images click.
         *
         * @param {Event} event The click event.
         */
        handleGenerateWebp: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            if (!confirm('This will generate WebP versions of all images on your site. For large sites, this may take a long time. Continue?')) {
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Generating...');
            this.showProgressIndicator('Generating WebP images...');
            
            // Generate WebP images via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_generate_webp',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        this.updateOptimizationProgress(response.data.progress);
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                    this.hideProgressIndicator();
                }.bind(this)
            });
        },

        /**
         * Handle download Google Fonts click.
         *
         * @param {Event} event The click event.
         */
        handleDownloadFonts: function(event) {
            event.preventDefault();
            
            const $button = $(event.target);
            const originalText = $button.text();
            
            // Show loading state
            $button.prop('disabled', true).text('Downloading...');
            this.showProgressIndicator('Downloading Google Fonts...');
            
            // Download fonts via AJAX
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_download_google_fonts',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                    this.hideProgressIndicator();
                }.bind(this)
            });
        },

        /**
         * Save a single setting.
         *
         * @param {string} setting The setting name.
         * @param {boolean} value  The setting value.
         */
        saveSingleSetting: function(setting, value) {
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_save_single_speed_setting',
                    setting: setting,
                    value: value ? '1' : '0',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                    } else {
                        this.showNotice(response.data.message, 'error');
                    }
                }.bind(this),
                error: function() {
                    this.showNotice(seo_simple_speed_optimization.strings.error_occurred || 'An error occurred. Please try again.', 'error');
                }.bind(this)
            });
        },

        /**
         * Show a notice.
         *
         * @param {string} message The notice message.
         * @param {string} type    The notice type (success, error, warning, info).
         */
        showNotice: function(message, type) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('h1').after($notice);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
            
            // Make dismissible
            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            });
        },

        /**
         * Show progress indicator.
         *
         * @param {string} message The progress message.
         */
        showProgressIndicator: function(message) {
            if ($('#seo-simple-progress-overlay').length === 0) {
                $('body').append('<div id="seo-simple-progress-overlay" class="seo-simple-progress-overlay">' +
                    '<div class="seo-simple-progress-content">' +
                    '<div class="seo-simple-progress-spinner"></div>' +
                    '<div class="seo-simple-progress-message">' + message + '</div>' +
                    '</div>' +
                    '</div>');
            }
        },

        /**
         * Hide progress indicator.
         */
        hideProgressIndicator: function() {
            $('#seo-simple-progress-overlay').remove();
        },

        /**
         * Update speed test results.
         *
         * @param {Object} results The speed test results.
         */
        updateSpeedTestResults: function(results) {
            // Update page load time
            if (results.page_load_time) {
                $('#seo-simple-page-load-time').text(results.page_load_time + 's');
            }
            
            // Update page size
            if (results.page_size_formatted) {
                $('#seo-simple-page-size').text(results.page_size_formatted);
            }
            
            // Update requests
            if (results.requests) {
                $('#seo-simple-requests').text(results.requests);
            }
            
            // Update cache status
            if (results.cache_enabled) {
                $('#seo-simple-cache-status').text(results.cache_enabled);
            }
            
            // Update GZIP status
            if (results.gzip_enabled) {
                $('#seo-simple-gzip-status').text(results.gzip_enabled);
            }
            
            // Update optimized images count
            if (results.images_optimized) {
                $('#seo-simple-images-optimized').text(results.images_optimized);
            }
            
            // Update chart
            this.updateSpeedTestChart(results);
        },

        /**
         * Update feature test result.
         *
         * @param {string} feature The feature name.
         * @param {Object} result  The test result.
         */
        updateFeatureTestResult: function(feature, result) {
            const $resultContainer = $('#seo-simple-' + feature + '-result');
            
            if ($resultContainer.length) {
                $resultContainer.removeClass('success error warning');
                $resultContainer.addClass(result.status);
                $resultContainer.text(result.message);
            }
        },

        /**
         * Update optimization progress.
         *
         * @param {Object} progress The progress data.
         */
        updateOptimizationProgress: function(progress) {
            if (progress.percentage) {
                $('#seo-simple-optimization-progress').text(progress.percentage + '%');
                $('#seo-simple-optimization-progress-bar').css('width', progress.percentage + '%');
            }
            
            if (progress.current && progress.total) {
                $('#seo-simple-optimization-status').text(progress.current + ' / ' + progress.total);
            }
            
            if (progress.message) {
                $('#seo-simple-optimization-message').text(progress.message);
            }
        },

        /**
         * Refresh cache statistics.
         */
        refreshCacheStats: function() {
            $.ajax({
                url: seo_simple_speed_optimization.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_get_cache_stats',
                    nonce: seo_simple_speed_optimization.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        this.updateCacheStats(response.data.stats);
                    }
                }.bind(this)
            });
        },

        /**
         * Update cache statistics.
         *
         * @param {Object} stats The cache statistics.
         */
        updateCacheStats: function(stats) {
            // Update combined files count
            if (stats.combined_files !== undefined) {
                $('#seo-simple-combined-files').text(stats.combined_files);
            }
            
            // Update minified files count
            if (stats.minified_files !== undefined) {
                $('#seo-simple-minified-files').text(stats.minified_files);
            }
            
            // Update chart
            this.updateCacheStatsChart(stats);
        },

        /**
         * Show cache details modal.
         *
         * @param {Object} details The cache details.
         */
        showCacheDetailsModal: function(details) {
            // Create modal HTML
            const modalHtml = '<div id="seo-simple-cache-details-modal" class="seo-simple-modal">' +
                '<div class="seo-simple-modal-content">' +
                '<div class="seo-simple-modal-header">' +
                '<h3>Cache Details</h3>' +
                '<button type="button" class="seo-simple-modal-close">&times;</button>' +
                '</div>' +
                '<div class="seo-simple-modal-body">' +
                '<table class="widefat">' +
                '<thead><tr><th>File</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>' +
                '<tbody>';
            
            // Add cache files
            if (details.files && details.files.length > 0) {
                details.files.forEach(function(file) {
                    modalHtml += '<tr>' +
                        '<td>' + file.name + '</td>' +
                        '<td>' + file.size + '</td>' +
                        '<td>' + file.created + '</td>' +
                        '<td><button class="button button-small seo-simple-delete-cache-file" data-file="' + file.path + '">Delete</button></td>' +
                        '</tr>';
                });
            } else {
                modalHtml += '<tr><td colspan="4">No cache files found.</td></tr>';
            }
            
            modalHtml += '</tbody></table>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Add modal to page
            $('body').append(modalHtml);
            
            // Show modal
            $('#seo-simple-cache-details-modal').fadeIn();
            
            // Handle close button
            $('.seo-simple-modal-close').on('click', function() {
                $('#seo-simple-cache-details-modal').fadeOut(function() {
                    $(this).remove();
                });
            });
            
            // Handle click outside modal
            $(document).on('click', '#seo-simple-cache-details-modal', function(e) {
                if (e.target === this) {
                    $(this).fadeOut(function() {
                        $(this).remove();
                    });
                }
            });
        },

        /**
         * Render speed test chart.
         *
         * @param {HTMLElement} canvas The chart canvas element.
         */
        renderSpeedTestChart: function(canvas) {
            const ctx = canvas.getContext('2d');
            
            // Get historical data
            const historicalData = this.getHistoricalSpeedData();
            
            // Create chart
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: historicalData.labels,
                    datasets: [{
                        label: 'Page Load Time (s)',
                        data: historicalData.loadTimes,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Load Time (s)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Render cache statistics chart.
         *
         * @param {HTMLElement} canvas The chart canvas element.
         */
        renderCacheStatsChart: function(canvas) {
            const ctx = canvas.getContext('2d');
            
            // Get cache statistics
            const stats = this.getCurrentCacheStats();
            
            // Create chart
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Combined Files', 'Minified Files', 'Other'],
                    datasets: [{
                        data: [stats.combined_files, stats.minified_files, stats.other],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Cache Statistics'
                        }
                    }
                }
            });
        },

        /**
         * Update speed test chart.
         *
         * @param {Object} results The speed test results.
         */
        updateSpeedTestChart: function(results) {
            // This would update the chart with new data
            // Implementation depends on the charting library used
        },

        /**
         * Update cache statistics chart.
         *
         * @param {Object} stats The cache statistics.
         */
        updateCacheStatsChart: function(stats) {
            // This would update the chart with new data
            // Implementation depends on the charting library used
        },

        /**
         * Get historical speed data.
         *
         * @return {Object} The historical speed data.
         */
        getHistoricalSpeedData: function() {
            // This would fetch historical speed data
            // For now, return dummy data
            return {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                loadTimes: [2.5, 2.3, 2.4, 2.2, 2.1, 2.3, 2.0]
            };
        },

        /**
         * Get current cache statistics.
         *
         * @return {Object} The current cache statistics.
         */
        getCurrentCacheStats: function() {
            // This would fetch current cache statistics
            // For now, return dummy data
            return {
                combined_files: 10,
                minified_files: 25,
                other: 5
            };
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        seoSimpleSpeedOptimization.init();
    });

})(jQuery);