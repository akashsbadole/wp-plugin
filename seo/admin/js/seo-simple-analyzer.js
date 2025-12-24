/**
 * JavaScript for SEO Simple Analyzer.
 *
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * SEO Analyzer object.
     */
    const SeoSimpleAnalyzer = {
        
        /**
         * Initialize the analyzer.
         */
        init: function() {
            this.bindEvents();
            this.loadInitialAnalysis();
        },
        
        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            // Analyze button click
            $(document).on('click', '.seo-simple-analyze-btn', this.handleAnalyzeClick.bind(this));
            
            // Clear cache button click
            $(document).on('click', '.seo-simple-clear-cache-btn', this.handleClearCacheClick.bind(this));
            
            // Focus keyword change
            $(document).on('input', '.seo-simple-focus-keyword', this.handleKeywordChange.bind(this));
            
            // Tab navigation
            $(document).on('click', '.seo-simple-tab-nav button', this.handleTabClick.bind(this));
            
            // Content change (auto-analyze)
            $(document).on('input', '#title, #content, #excerpt', this.debounce(this.handleContentChange.bind(this), 1000));
            
            // Title and meta changes
            $(document).on('input', '.seo-simple-meta-title, .seo-simple-meta-description', this.debounce(this.handleMetaChange.bind(this), 1000));
        },
        
        /**
         * Load initial analysis when page loads.
         */
        loadInitialAnalysis: function() {
            const postId = $('#post_ID').val();
            if (postId) {
                this.analyzeContent(false);
            }
        },
        
        /**
         * Handle analyze button click.
         */
        handleAnalyzeClick: function(e) {
            e.preventDefault();
            this.analyzeContent(true);
        },
        
        /**
         * Handle clear cache button click.
         */
        handleClearCacheClick: function(e) {
            e.preventDefault();
            this.clearCache();
        },
        
        /**
         * Handle focus keyword change.
         */
        handleKeywordChange: function() {
            const keyword = $('.seo-simple-focus-keyword').val();
            if (keyword.length > 2) {
                this.analyzeContent(false);
            }
        },
        
        /**
         * Handle tab click.
         */
        handleTabClick: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const tabId = $button.data('tab');
            
            // Update active tab
            $('.seo-simple-tab-nav button').removeClass('active');
            $button.addClass('active');
            
            // Show corresponding content
            $('.seo-simple-tab-content').removeClass('active');
            $(`#${tabId}-content`).addClass('active');
        },
        
        /**
         * Handle content change.
         */
        handleContentChange: function() {
            this.analyzeContent(false);
        },
        
        /**
         * Handle meta change.
         */
        handleMetaChange: function() {
            this.analyzeContent(false);
        },
        
        /**
         * Analyze content.
         */
        analyzeContent: function(showLoading) {
            const postId = $('#post_ID').val();
            const title = $('#title').val();
            const content = $('#content').val();
            const excerpt = $('#excerpt').val();
            const metaTitle = $('.seo-simple-meta-title').val();
            const metaDescription = $('.seo-simple-meta-description').val();
            const focusKeyword = $('.seo-simple-focus-keyword').val();
            
            // Don't analyze if we don't have enough content
            if (!title && !content) {
                return;
            }
            
            // Show loading state
            if (showLoading) {
                this.showLoading();
            }
            
            // Make AJAX request
            $.ajax({
                url: seoSimpleAnalyzer.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_analyze_content',
                    nonce: seoSimpleAnalyzer.nonce,
                    post_id: postId,
                    title: title,
                    content: content,
                    excerpt: excerpt,
                    meta_title: metaTitle,
                    meta_description: metaDescription,
                    focus_keyword: focusKeyword
                },
                success: this.handleAnalysisSuccess.bind(this),
                error: this.handleAnalysisError.bind(this),
                complete: this.handleAnalysisComplete.bind(this)
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
         * Handle analysis complete.
         */
        handleAnalysisComplete: function() {
            this.hideLoading();
        },
        
        /**
         * Update analysis display.
         */
        updateAnalysisDisplay: function(data) {
            // Update scores
            this.updateScores(data.scores);
            
            // Update SEO checks
            this.updateChecks('seo', data.seo_checks);
            
            // Update readability checks
            this.updateChecks('readability', data.readability_checks);
            
            // Update recommendations
            this.updateRecommendations(data.recommendations);
            
            // Update last analyzed time
            this.updateLastAnalyzedTime(data.analyzed_at);
        },
        
        /**
         * Update scores display.
         */
        updateScores: function(scores) {
            // Overall score
            $('.seo-simple-score-overall .seo-simple-score-number').text(scores.overall);
            $('.seo-simple-score-overall').removeClass('score-good score-okay score-warning score-bad')
                .addClass(this.getScoreClass(scores.overall));
            
            // SEO score
            $('.seo-simple-score-seo .seo-simple-score-value').text(scores.seo);
            $('.seo-simple-score-seo').removeClass('score-good score-okay score-warning score-bad')
                .addClass(this.getScoreClass(scores.seo));
            
            // Readability score
            $('.seo-simple-score-readability .seo-simple-score-value').text(scores.readability);
            $('.seo-simple-score-readability').removeClass('score-good score-okay score-warning score-bad')
                .addClass(this.getScoreClass(scores.readability));
        },
        
        /**
         * Update checks display.
         */
        updateChecks: function(type, checks) {
            const $container = $(`.seo-simple-${type}-checks`);
            $container.empty();
            
            if (checks.length === 0) {
                $container.html('<div class="seo-simple-empty-state">No checks to display.</div>');
                return;
            }
            
            checks.forEach(check => {
                const $checkItem = this.createCheckItem(check);
                $container.append($checkItem);
            });
        },
        
        /**
         * Create check item element.
         */
        createCheckItem: function(check) {
            const statusClass = this.getStatusClass(check.status);
            const statusIcon = this.getStatusIcon(check.status);
            
            return $(`
                <div class="seo-simple-check-item">
                    <div class="seo-simple-check-icon ${statusClass}">${statusIcon}</div>
                    <div class="seo-simple-check-details">
                        <div class="seo-simple-check-title">${check.title}</div>
                        <div class="seo-simple-check-message">${check.message}</div>
                    </div>
                    <div class="seo-simple-check-value">${check.value}</div>
                </div>
            `);
        },
        
        /**
         * Update recommendations display.
         */
        updateRecommendations: function(recommendations) {
            const $container = $('.seo-simple-recommendations');
            $container.empty();
            
            if (recommendations.length === 0) {
                $container.html('<div class="seo-simple-recommendations-title">Great job! No recommendations at this time.</div>');
                return;
            }
            
            $container.append('<div class="seo-simple-recommendations-title">Recommendations</div>');
            
            recommendations.forEach(rec => {
                const $recItem = this.createRecommendationItem(rec);
                $container.append($recItem);
            });
        },
        
        /**
         * Create recommendation item element.
         */
        createRecommendationItem: function(recommendation) {
            const priorityClass = this.getPriorityClass(recommendation.priority);
            
            return $(`
                <div class="seo-simple-recommendation-item ${priorityClass}">
                    <div class="seo-simple-recommendation-title">${recommendation.title}</div>
                    <div class="seo-simple-recommendation-message">${recommendation.message}</div>
                </div>
            `);
        },
        
        /**
         * Update last analyzed time.
         */
        updateLastAnalyzedTime: function(timestamp) {
            if (timestamp) {
                const date = new Date(timestamp * 1000);
                const timeString = date.toLocaleTimeString();
                $('.seo-simple-last-analyzed').text('Last analyzed: ' + timeString);
            }
        },
        
        /**
         * Clear cache.
         */
        clearCache: function() {
            const postId = $('#post_ID').val();
            
            $.ajax({
                url: seoSimpleAnalyzer.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_clear_cache',
                    nonce: seoSimpleAnalyzer.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Re-analyze after clearing cache
                        SeoSimpleAnalyzer.analyzeContent(true);
                    } else {
                        SeoSimpleAnalyzer.showError('Failed to clear cache: ' + response.data.message);
                    }
                },
                error: function() {
                    SeoSimpleAnalyzer.showError('Cache clear request failed');
                }
            });
        },
        
        /**
         * Show loading state.
         */
        showLoading: function() {
            $('.seo-simple-analyzer-content').addClass('loading');
            $('.seo-simple-analyze-btn').prop('disabled', true);
        },
        
        /**
         * Hide loading state.
         */
        hideLoading: function() {
            $('.seo-simple-analyzer-content').removeClass('loading');
            $('.seo-simple-analyze-btn').prop('disabled', false);
        },
        
        /**
         * Show error message.
         */
        showError: function(message) {
            $('.seo-simple-analyzer-content').html(
                '<div class="seo-simple-error-state">' + message + '</div>'
            );
        },
        
        /**
         * Get score class based on value.
         */
        getScoreClass: function(score) {
            if (score >= 80) return 'score-good';
            if (score >= 60) return 'score-okay';
            if (score >= 40) return 'score-warning';
            return 'score-bad';
        },
        
        /**
         * Get status class based on status.
         */
        getStatusClass: function(status) {
            if (status === 'good') return 'status-good';
            if (status === 'warning') return 'status-warning';
            return 'status-error';
        },
        
        /**
         * Get status icon based on status.
         */
        getStatusIcon: function(status) {
            if (status === 'good') return '✓';
            if (status === 'warning') return '!';
            return '✗';
        },
        
        /**
         * Get priority class based on priority.
         */
        getPriorityClass: function(priority) {
            if (priority === 'high') return 'high-priority';
            if (priority === 'medium') return 'medium-priority';
            return 'low-priority';
        },
        
        /**
         * Debounce function to limit API calls.
         */
        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(context, args);
                }, wait);
            };
        }
    };
    
    /**
     * Initialize when document is ready.
     */
    $(document).ready(function() {
        // Only initialize if analyzer elements exist
        if ($('.seo-simple-analyzer').length || $('#seo_simple_analyzer').length) {
            SeoSimpleAnalyzer.init();
        }
    });
    
    // Export for global access
    window.SeoSimpleAnalyzer = SeoSimpleAnalyzer;
    
})(jQuery);