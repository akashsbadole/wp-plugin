/**
 * JavaScript for Divi compatibility with SEO Simple.
 *
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Divi Compatibility object.
     */
    const SeoSimpleDivi = {
        
        /**
         * Initialize the Divi compatibility.
         */
        init: function() {
            this.bindEvents();
            this.addSeoSection();
            this.setupAutoAnalysis();
        },
        
        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Only run if Divi is active
            if (typeof et_pb_builder === 'undefined') {
                return;
            }
            
            // Add SEO section when Divi builder is ready
            $(document).on('et_pb_ready', this.addSeoSection.bind(this));
            
            // Analyze content when Divi saves
            $(document).on('et_pb_app_save', this.analyzeContent.bind(this));
            
            // Update SEO preview when content changes
            $(document).on('et_pb_app_changed', this.updateSeoPreview.bind(this));
            
            // Handle SEO form submission
            $(document).on('click', '#seo-simple-divi-save', this.saveSeoData.bind(this));
            
            // Handle analyze button click
            $(document).on('click', '#seo-simple-divi-analyze', this.analyzeContent.bind(this));
        },
        
        /**
         * Add SEO section to Divi.
         */
        addSeoSection: function() {
            // Check if Divi builder is active
            if ($('#et_pb_main_area').length === 0) {
                return;
            }
            
            // Add SEO section to Divi settings
            if ($('#et_pb_seo_simple_section').length === 0) {
                this.createSeoSection();
            }
        },
        
        /**
         * Create SEO section.
         */
        createSeoSection: function() {
            const seoSection = $(`
                <div id="et_pb_seo_simple_section" class="et_pb_settings_tab et_pb_settings_tab_active">
                    <div class="et-pb-option">
                        <label for="seo-simple-divi-title"><?php _e('SEO Title', 'seo-simple'); ?></label>
                        <input type="text" id="seo-simple-divi-title" class="regular-text" />
                    </div>
                    <div class="et-pb-option">
                        <label for="seo-simple-divi-description"><?php _e('SEO Description', 'seo-simple'); ?></label>
                        <textarea id="seo-simple-divi-description" rows="3" class="large-text"></textarea>
                    </div>
                    <div class="et-pb-option">
                        <label for="seo-simple-divi-focus-keyword"><?php _e('Focus Keyword', 'seo-simple'); ?></label>
                        <input type="text" id="seo-simple-divi-focus-keyword" class="regular-text" />
                    </div>
                    <div class="et-pb-option">
                        <button type="button" id="seo-simple-divi-analyze" class="button button-secondary"><?php _e('Analyze Content', 'seo-simple'); ?></button>
                        <button type="button" id="seo-simple-divi-save" class="button button-primary"><?php _e('Save SEO Data', 'seo-simple'); ?></button>
                    </div>
                    <div class="et-pb-option">
                        <div id="seo-simple-divi-loading" style="display: none;">
                            <p><?php _e('Analyzing content...', 'seo-simple'); ?></p>
                        </div>
                        <div id="seo-simple-divi-results">
                            <div class="seo-simple-divi-scores">
                                <div class="seo-simple-divi-score">
                                    <label><?php _e('SEO Score:', 'seo-simple'); ?></label>
                                    <span id="seo-simple-divi-seo-score">0</span>
                                </div>
                                <div class="seo-simple-divi-score">
                                    <label><?php _e('Readability Score:', 'seo-simple'); ?></label>
                                    <span id="seo-simple-divi-readability-score">0</span>
                                </div>
                                <div class="seo-simple-divi-score">
                                    <label><?php _e('Word Count:', 'seo-simple'); ?></label>
                                    <span id="seo-simple-divi-word-count">0</span>
                                </div>
                            </div>
                            <div class="seo-simple-divi-recommendations" id="seo-simple-divi-recommendations" style="display: none;">
                                <h4><?php _e('Recommendations', 'seo-simple'); ?></h4>
                                <div id="seo-simple-divi-recommendations-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Add to Divi settings tabs
            $('.et_pb_settings_tabs').append(`
                <li class="et_pb_settings_tab et_pb_active" data-tab="seo_simple">
                    <span><?php _e('SEO', 'seo-simple'); ?></span>
                </li>
            `);
            
            // Add to settings container
            $('.et_pb_settings_container').append(seoSection);
            
            // Bind tab switching
            $('.et_pb_settings_tab').on('click', function() {
                $('.et_pb_settings_tab').removeClass('et_pb_active');
                $(this).addClass('et_pb_active');
                
                const tab = $(this).data('tab');
                $('.et_pb_settings_tab').hide();
                $(`#et_pb_${tab}_section`).show();
            });
            
            // Load existing SEO data
            this.loadSeoData();
        },
        
        /**
         * Setup automatic analysis.
         */
        setupAutoAnalysis: function() {
            // Analyze content after a delay when editing stops
            let analyzeTimeout;
            $(document).on('et_pb_app_changed', function() {
                clearTimeout(analyzeTimeout);
                analyzeTimeout = setTimeout(function() {
                    SeoSimpleDivi.analyzeContent(false);
                }, 2000);
            });
        },
        
        /**
         * Analyze content.
         */
        analyzeContent: function(showLoading) {
            const postId = $('#post_ID').val();
            
            if (!postId) {
                return;
            }
            
            if (showLoading !== false) {
                this.showLoading();
            }
            
            // Make AJAX request to analyze content
            $.ajax({
                url: seoSimpleDivi.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_page_builder_analyze',
                    nonce: seoSimpleDivi.nonce,
                    post_id: postId,
                    page_builder: 'divi'
                },
                success: this.handleAnalysisSuccess.bind(this),
                error: this.handleAnalysisError.bind(this),
                complete: this.hideLoading.bind(this)
            });
        },
        
        /**
         * Handle analysis success.
         */
        handleAnalysisSuccess: function(response) {
            if (response.success) {
                this.updateAnalysisDisplay(response.data);
            } else {
                this.showError(response.data.message || 'Analysis failed');
            }
        },
        
        /**
         * Handle analysis error.
         */
        handleAnalysisError: function(xhr, status, error) {
            this.showError('Analysis request failed: ' + error);
        },
        
        /**
         * Update analysis display.
         */
        updateAnalysisDisplay: function(data) {
            // Update SEO score
            $('#seo-simple-divi-seo-score').text(data.analysis.scores.seo || 0);
            
            // Update readability score
            $('#seo-simple-divi-readability-score').text(data.analysis.scores.readability || 0);
            
            // Update word count
            $('#seo-simple-divi-word-count').text(data.word_count || 0);
            
            // Update recommendations
            if (data.analysis.recommendations && data.analysis.recommendations.length > 0) {
                let recommendationsHtml = '';
                data.analysis.recommendations.forEach(function(rec) {
                    recommendationsHtml += '<div class="seo-simple-recommendation">';
                    recommendationsHtml += '<h4>' + rec.title + '</h4>';
                    recommendationsHtml += '<p>' + rec.message + '</p>';
                    recommendationsHtml += '</div>';
                });
                $('#seo-simple-divi-recommendations-list').html(recommendationsHtml);
                $('#seo-simple-divi-recommendations').show();
            } else {
                $('#seo-simple-divi-recommendations').hide();
            }
        },
        
        /**
         * Save SEO data.
         */
        saveSeoData: function(e) {
            e.preventDefault();
            
            const postId = $('#post_ID').val();
            const seoTitle = $('#seo-simple-divi-title').val();
            const seoDescription = $('#seo-simple-divi-description').val();
            const focusKeyword = $('#seo-simple-divi-focus-keyword').val();
            
            if (!postId) {
                return;
            }
            
            // Show loading state
            this.showLoading();
            
            // Make AJAX request to save SEO data
            $.ajax({
                url: seoSimpleDivi.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_page_builder_save_seo',
                    nonce: seoSimpleDivi.nonce,
                    post_id: postId,
                    seo_title: seoTitle,
                    seo_description: seoDescription,
                    focus_keyword: focusKeyword
                },
                success: function(response) {
                    if (response.success) {
                        SeoSimpleDivi.showSuccess(response.data.message);
                    } else {
                        SeoSimpleDivi.showError(response.data.message || 'Save failed');
                    }
                },
                error: function() {
                    SeoSimpleDivi.showError('Save request failed');
                },
                complete: this.hideLoading.bind(this)
            });
        },
        
        /**
         * Update SEO preview.
         */
        updateSeoPreview: function() {
            // Get post title
            const title = $('#title').val() || '';
            
            // Get Divi content
            const content = this.extractDiviContent();
            
            // Update preview
            $('#seo-simple-divi-preview-title').text(title);
            $('#seo-simple-divi-preview-description').text(this.generateDescription(content));
        },
        
        /**
         * Extract content from Divi.
         */
        extractDiviContent: function() {
            let content = '';
            
            // Get content from the visual editor
            if (typeof et_pb_builder !== 'undefined') {
                // Extract content from Divi modules
                $('.et_pb_module').each(function() {
                    content += SeoSimpleDivi.extractModuleContent($(this));
                });
            }
            
            // Fallback to post content
            if (!content && $('#content').length) {
                content = $('#content').val();
            }
            
            return content;
        },
        
        /**
         * Extract content from Divi module.
         */
        extractModuleContent: function($module) {
            let content = '';
            
            // Get module type
            const moduleType = $module.data('module_type') || '';
            
            switch (moduleType) {
                case 'et_pb_text':
                    $module.find('.et_pb_text_inner').each(function() {
                        content += $(this).text() + ' ';
                    });
                    break;
                    
                case 'et_pb_heading':
                    $module.find('.et_pb_module_header').each(function() {
                        content += $(this).text() + ' ';
                    });
                    break;
                    
                case 'et_pb_blurb':
                    $module.find('.et_pb_blurb_content').each(function() {
                        content += $(this).text() + ' ';
                    });
                    break;
                    
                case 'et_pb_image':
                    $module.find('img').each(function() {
                        const alt = $(this).attr('alt');
                        if (alt) {
                            content += alt + ' ';
                        }
                    });
                    break;
                    
                case 'et_pb_button':
                    $module.find('.et_pb_button').each(function() {
                        content += $(this).text() + ' ';
                    });
                    break;
                    
                case 'et_pb_accordion':
                    $module.find('.et_pb_accordion_item').each(function() {
                        $(this).find('.et_pb_accordion_title').each(function() {
                            content += $(this).text() + ' ';
                        });
                        $(this).find('.et_pb_accordion_content').each(function() {
                            content += $(this).text() + ' ';
                        });
                    });
                    break;
                    
                case 'et_pb_tabs':
                    $module.find('.et_pb_tabs_controls li').each(function() {
                        content += $(this).text() + ' ';
                    });
                    $module.find('.et_pb_all_tabs .et_pb_tab').each(function() {
                        content += $(this).text() + ' ';
                    });
                    break;
            }
            
            return content;
        },
        
        /**
         * Generate description from content.
         */
        generateDescription: function(content) {
            // Strip HTML tags
            content = content.replace(/<[^>]*>/g, '');
            
            // Limit to 160 characters
            if (content.length > 160) {
                content = content.substring(0, 160);
                // Find the last space to avoid cutting words
                const lastSpace = content.lastIndexOf(' ');
                if (lastSpace > 0) {
                    content = content.substring(0, lastSpace);
                }
                content += '...';
            }
            
            return content;
        },
        
        /**
         * Load SEO data.
         */
        loadSeoData: function() {
            const postId = $('#post_ID').val();
            
            if (!postId) {
                return;
            }
            
            // Make AJAX request to get SEO data
            $.ajax({
                url: seoSimpleDivi.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_page_builder_get_meta',
                    nonce: seoSimpleDivi.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        $('#seo-simple-divi-title').val(response.data.seo_title || '');
                        $('#seo-simple-divi-description').val(response.data.seo_description || '');
                        $('#seo-simple-divi-focus-keyword').val(response.data.focus_keyword || '');
                    }
                }
            });
        },
        
        /**
         * Show loading state.
         */
        showLoading: function() {
            $('#seo-simple-divi-loading').show();
            $('#seo-simple-divi-results').hide();
        },
        
        /**
         * Hide loading state.
         */
        hideLoading: function() {
            $('#seo-simple-divi-loading').hide();
            $('#seo-simple-divi-results').show();
        },
        
        /**
         * Show success message.
         */
        showSuccess: function(message) {
            const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('#et_pb_seo_simple_section').prepend($notice);
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 3000);
        },
        
        /**
         * Show error message.
         */
        showError: function(message) {
            const $notice = $('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>');
            $('#et_pb_seo_simple_section').prepend($notice);
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        }
    };
    
    /**
     * Initialize when Divi is ready.
     */
    $(document).ready(function() {
        // Check if Divi builder is active
        if ($('#et_pb_main_area').length > 0 || typeof et_pb_builder !== 'undefined') {
            SeoSimpleDivi.init();
        }
        
        // Also initialize when Divi builder is loaded
        $(document).on('et_pb_ready', function() {
            SeoSimpleDivi.init();
        });
    });
    
    // Export for global access
    window.SeoSimpleDivi = SeoSimpleDivi;
    
})(jQuery);