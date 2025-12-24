/**
 * JavaScript for Elementor compatibility with SEO Simple.
 *
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Elementor Compatibility object.
     */
    const SeoSimpleElementor = {
        
        /**
         * Initialize the Elementor compatibility.
         */
        init: function() {
            this.bindEvents();
            this.addSeoPanel();
            this.setupAutoAnalysis();
        },
        
        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Only run if Elementor is active
            if (typeof elementor === 'undefined') {
                return;
            }
            
            // Add SEO panel when Elementor editor is ready
            elementor.on('panel:init', this.addSeoPanel.bind(this));
            
            // Analyze content when Elementor saves
            elementor.on('document:save', this.analyzeContent.bind(this));
            
            // Update SEO preview when content changes
            elementor.on('document:edited', this.updateSeoPreview.bind(this));
            
            // Handle SEO form submission
            $(document).on('click', '#seo-simple-elementor-save', this.saveSeoData.bind(this));
            
            // Handle analyze button click
            $(document).on('click', '#seo-simple-elementor-analyze', this.analyzeContent.bind(this));
        },
        
        /**
         * Add SEO panel to Elementor.
         */
        addSeoPanel: function() {
            if (typeof elementor === 'undefined') {
                return;
            }
            
            // Add SEO tab to Elementor panel
            if (typeof SeoSimpleElementorSeoPanel.render === 'function') {
                // Non-Marionette version - panel is already rendered in the render function
                return;
            } else {
                // Marionette version
                elementor.getPanelView().addTab('seo-simple', {
                    title: 'SEO',
                    icon: 'eicon-search',
                    view: SeoSimpleElementorSeoPanel
                });
            }
        },
        
        /**
         * Setup automatic analysis.
         */
        setupAutoAnalysis: function() {
            if (typeof elementor === 'undefined') {
                return;
            }
            
            // Analyze content after a delay when editing stops
            let analyzeTimeout;
            elementor.on('document:edited', function() {
                clearTimeout(analyzeTimeout);
                analyzeTimeout = setTimeout(function() {
                    SeoSimpleElementor.analyzeContent(false);
                }, 2000);
            });
        },
        
        /**
         * Analyze content.
         */
        analyzeContent: function(showLoading) {
            if (typeof elementor === 'undefined') {
                return;
            }
            
            const postId = elementor.config.document.id;
            
            if (showLoading !== false) {
                this.showLoading();
            }
            
            // Make AJAX request to analyze content
            $.ajax({
                url: seoSimpleElementor.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_page_builder_analyze',
                    nonce: seoSimpleElementor.nonce,
                    post_id: postId,
                    page_builder: 'elementor'
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
            $('#seo-simple-elementor-seo-score').text(data.analysis.scores.seo || 0);
            
            // Update readability score
            $('#seo-simple-elementor-readability-score').text(data.analysis.scores.readability || 0);
            
            // Update word count
            $('#seo-simple-elementor-word-count').text(data.word_count || 0);
            
            // Update content length
            $('#seo-simple-elementor-content-length').text(data.content_length || 0);
            
            // Update recommendations
            if (data.analysis.recommendations && data.analysis.recommendations.length > 0) {
                let recommendationsHtml = '';
                data.analysis.recommendations.forEach(function(rec) {
                    recommendationsHtml += '<div class="seo-simple-recommendation">';
                    recommendationsHtml += '<h4>' + rec.title + '</h4>';
                    recommendationsHtml += '<p>' + rec.message + '</p>';
                    recommendationsHtml += '</div>';
                });
                $('#seo-simple-elementor-recommendations').html(recommendationsHtml).show();
            } else {
                $('#seo-simple-elementor-recommendations').hide();
            }
            
            // Update SEO checks
            if (data.analysis.seo_checks && data.analysis.seo_checks.length > 0) {
                let checksHtml = '';
                data.analysis.seo_checks.forEach(function(check) {
                    const statusClass = check.status === 'good' ? 'good' : (check.status === 'warning' ? 'warning' : 'bad');
                    const statusIcon = check.status === 'good' ? '✓' : (check.status === 'warning' ? '!' : '✗');
                    
                    checksHtml += '<div class="seo-simple-check ' + statusClass + '">';
                    checksHtml += '<span class="icon">' + statusIcon + '</span>';
                    checksHtml += '<span class="title">' + check.title + '</span>';
                    checksHtml += '<span class="message">' + check.message + '</span>';
                    checksHtml += '</div>';
                });
                $('#seo-simple-elementor-seo-checks').html(checksHtml);
            }
            
            // Update readability checks
            if (data.analysis.readability_checks && data.analysis.readability_checks.length > 0) {
                let checksHtml = '';
                data.analysis.readability_checks.forEach(function(check) {
                    const statusClass = check.status === 'good' ? 'good' : (check.status === 'warning' ? 'warning' : 'bad');
                    const statusIcon = check.status === 'good' ? '✓' : (check.status === 'warning' ? '!' : '✗');
                    
                    checksHtml += '<div class="seo-simple-check ' + statusClass + '">';
                    checksHtml += '<span class="icon">' + statusIcon + '</span>';
                    checksHtml += '<span class="title">' + check.title + '</span>';
                    checksHtml += '<span class="message">' + check.message + '</span>';
                    checksHtml += '</div>';
                });
                $('#seo-simple-elementor-readability-checks').html(checksHtml);
            }
        },
        
        /**
         * Save SEO data.
         */
        saveSeoData: function(e) {
            e.preventDefault();
            
            const postId = elementor.config.document.id;
            const seoTitle = $('#seo-simple-elementor-title').val();
            const seoDescription = $('#seo-simple-elementor-description').val();
            const focusKeyword = $('#seo-simple-elementor-focus-keyword').val();
            
            // Show loading state
            this.showLoading();
            
            // Make AJAX request to save SEO data
            $.ajax({
                url: seoSimpleElementor.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_page_builder_save_seo',
                    nonce: seoSimpleElementor.nonce,
                    post_id: postId,
                    seo_title: seoTitle,
                    seo_description: seoDescription,
                    focus_keyword: focusKeyword
                },
                success: function(response) {
                    if (response.success) {
                        SeoSimpleElementor.showSuccess(response.data.message);
                    } else {
                        SeoSimpleElementor.showError(response.data.message || 'Save failed');
                    }
                },
                error: function() {
                    SeoSimpleElementor.showError('Save request failed');
                },
                complete: this.hideLoading.bind(this)
            });
        },
        
        /**
         * Update SEO preview.
         */
        updateSeoPreview: function() {
            if (typeof elementor === 'undefined') {
                return;
            }
            
            const postId = elementor.config.document.id;
            const document = elementor.documents.getCurrent();
            
            // Get document title
            const title = document.config.settings.page_title || '';
            
            // Get document content
            const content = this.extractElementorContent();
            
            // Update preview
            $('#seo-simple-elementor-preview-title').text(title);
            $('#seo-simple-elementor-preview-description').text(this.generateDescription(content));
        },
        
        /**
         * Extract content from Elementor.
         */
        extractElementorContent: function() {
            if (typeof elementor === 'undefined') {
                return '';
            }
            
            const document = elementor.documents.getCurrent();
            let content = '';
            
            // Extract content from each widget
            document.elements.each(function(element) {
                if (element.model) {
                    content += SeoSimpleElementor.extractWidgetContent(element.model);
                }
            });
            
            return content;
        },
        
        /**
         * Extract content from Elementor widget.
         */
        extractWidgetContent: function(widget) {
            let content = '';
            
            if (!widget || !widget.attributes) {
                return content;
            }
            
            const widgetType = widget.attributes.widgetType;
            const settings = widget.attributes.settings;
            
            switch (widgetType) {
                case 'heading':
                    if (settings.title) {
                        content += settings.title + ' ';
                    }
                    break;
                    
                case 'text-editor':
                    if (settings.editor) {
                        content += settings.editor + ' ';
                    }
                    break;
                    
                case 'image':
                    if (settings.alt) {
                        content += settings.alt + ' ';
                    }
                    break;
                    
                case 'button':
                    if (settings.text) {
                        content += settings.text + ' ';
                    }
                    break;
            }
            
            // Extract content from child elements
            if (widget.attributes.elements) {
                widget.attributes.elements.each(function(child) {
                    content += SeoSimpleElementor.extractWidgetContent(child);
                });
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
         * Show loading state.
         */
        showLoading: function() {
            $('#seo-simple-elementor-loading').show();
            $('#seo-simple-elementor-results').hide();
        },
        
        /**
         * Hide loading state.
         */
        hideLoading: function() {
            $('#seo-simple-elementor-loading').hide();
            $('#seo-simple-elementor-results').show();
        },
        
        /**
         * Show success message.
         */
        showSuccess: function(message) {
            const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('.seo-simple-elementor-panel').prepend($notice);
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
            $('.seo-simple-elementor-panel').prepend($notice);
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 5000);
        }
    };
    
    /**
     * SEO Panel View for Elementor.
     * Only use Marionette if available, otherwise create a simple panel
     */
    let SeoSimpleElementorSeoPanel;
    
    if (typeof Marionette !== 'undefined' && Marionette.ItemView) {
        // Use Marionette if available
        SeoSimpleElementorSeoPanel = Marionette.ItemView.extend({
            template: '#tmpl-seo-simple-elementor-panel',
            id: 'seo-simple-elementor-panel',
            className: 'seo-simple-elementor-panel elementor-panel',
            
            ui: {
                title: '#seo-simple-elementor-title',
                description: '#seo-simple-elementor-description',
                focusKeyword: '#seo-simple-elementor-focus-keyword',
                saveBtn: '#seo-simple-elementor-save',
                analyzeBtn: '#seo-simple-elementor-analyze'
            },
            
            events: {
                'click @ui.saveBtn': 'saveSeoData',
                'click @ui.analyzeBtn': 'analyzeContent'
            },
            
            initialize: function() {
                this.loadSeoData();
            },
            
            loadSeoData: function() {
                const postId = elementor.config.document.id;
                
                // Make AJAX request to get SEO data
                $.ajax({
                    url: seoSimpleElementor.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'seo_simple_page_builder_get_meta',
                        nonce: seoSimpleElementor.nonce,
                        post_id: postId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#seo-simple-elementor-title').val(response.data.seo_title || '');
                            $('#seo-simple-elementor-description').val(response.data.seo_description || '');
                            $('#seo-simple-elementor-focus-keyword').val(response.data.focus_keyword || '');
                        }
                    }
                });
            },
            
            saveSeoData: function(e) {
                e.preventDefault();
                SeoSimpleElementor.saveSeoData(e);
            },
            
            analyzeContent: function(e) {
                e.preventDefault();
                SeoSimpleElementor.analyzeContent(true);
            }
        });
    } else {
        // Fallback simple panel without Marionette
        SeoSimpleElementorSeoPanel = {
            render: function() {
                // Create a simple panel without Marionette
                const panelHtml = `
                    <div id="seo-simple-elementor-panel" class="seo-simple-elementor-panel elementor-panel">
                        <div class="elementor-panel-header">
                            <h3>SEO Settings</h3>
                        </div>
                        <div class="elementor-panel-content">
                            <div class="elementor-control-field">
                                <label class="elementor-control-title">SEO Title</label>
                                <div class="elementor-control-input">
                                    <input type="text" id="seo-simple-elementor-title" class="elementor-control-tag-input">
                                </div>
                            </div>
                            <div class="elementor-control-field">
                                <label class="elementor-control-title">Meta Description</label>
                                <div class="elementor-control-input">
                                    <textarea id="seo-simple-elementor-description" class="elementor-control-tag-input" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="elementor-control-field">
                                <label class="elementor-control-title">Focus Keyword</label>
                                <div class="elementor-control-input">
                                    <input type="text" id="seo-simple-elementor-focus-keyword" class="elementor-control-tag-input">
                                </div>
                            </div>
                            <div class="elementor-control-field">
                                <div class="elementor-control-input">
                                    <button id="seo-simple-elementor-save" class="elementor-button elementor-button-success">Save SEO</button>
                                    <button id="seo-simple-elementor-analyze" class="elementor-button">Analyze</button>
                                </div>
                            </div>
                            <div id="seo-simple-elementor-loading" style="display:none;">Analyzing...</div>
                            <div id="seo-simple-elementor-results">
                                <div class="elementor-control-field">
                                    <label class="elementor-control-title">SEO Score</label>
                                    <div class="elementor-control-input">
                                        <span id="seo-simple-elementor-seo-score">0</span>
                                    </div>
                                </div>
                                <div class="elementor-control-field">
                                    <label class="elementor-control-title">Readability Score</label>
                                    <div class="elementor-control-input">
                                        <span id="seo-simple-elementor-readability-score">0</span>
                                    </div>
                                </div>
                                <div id="seo-simple-elementor-recommendations"></div>
                                <div id="seo-simple-elementor-seo-checks"></div>
                                <div id="seo-simple-elementor-readability-checks"></div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add the panel to the Elementor editor
                if (elementor && elementor.getPanelView) {
                    elementor.getPanelView().addTab('seo-simple', {
                        title: 'SEO',
                        icon: 'eicon-search',
                        content: panelHtml
                    });
                }
                
                // Load initial data
                this.loadSeoData();
            },
            
            loadSeoData: function() {
                if (typeof elementor === 'undefined') return;
                
                const postId = elementor.config.document.id;
                
                // Make AJAX request to get SEO data
                $.ajax({
                    url: seoSimpleElementor.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'seo_simple_page_builder_get_meta',
                        nonce: seoSimpleElementor.nonce,
                        post_id: postId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#seo-simple-elementor-title').val(response.data.seo_title || '');
                            $('#seo-simple-elementor-description').val(response.data.seo_description || '');
                            $('#seo-simple-elementor-focus-keyword').val(response.data.focus_keyword || '');
                        }
                    }
                });
            }
        };
    }
    
    /**
     * Initialize when Elementor is ready.
     */
    $(document).ready(function() {
        if (typeof elementor !== 'undefined') {
            elementor.on('frontend:init', function() {
                SeoSimpleElementor.init();
            });
            
            // Initialize the panel based on whether Marionette is available
            if (typeof SeoSimpleElementorSeoPanel.render === 'function') {
                // Non-Marionette version
                SeoSimpleElementorSeoPanel.render();
            }
        }
    });
    
    // Export for global access
    window.SeoSimpleElementor = SeoSimpleElementor;
    
})(jQuery);