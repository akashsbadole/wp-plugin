/**
 * Enhanced SEO Meta Box JavaScript
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab functionality
        $('.seo-simple-tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            
            // Update active tab button
            $('.seo-simple-tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Show corresponding tab content
            $('.seo-simple-tab-content').removeClass('active');
            $(`#seo-simple-tab-${tabId}`).addClass('active');
        });
        
        // Live SERP preview
        function updateSerpPreview() {
            const title = $('#seo-simple-title').val() || $('title').text();
            const description = $('#seo-simple-description').val() || $('meta[name="description"]').attr('content') || '';
            const siteName = $('#seo-simple-site-name').data('site-name');
            
            // Update title preview
            $('#seo-simple-title-preview').text(title);
            
            // Update description preview
            $('#seo-simple-description-preview').text(description);
            
            // Update character counts
            $('.seo-simple-title-count').text(title.length);
            $('.seo-simple-description-count').text(description.length);
            
            // Update title color based on length
            if (title.length > 60) {
                $('.seo-simple-title-count').css('color', '#dc3232');
            } else if (title.length > 50) {
                $('.seo-simple-title-count').css('color', '#ffb900');
            } else {
                $('.seo-simple-title-count').css('color', '#46b450');
            }
            
            // Update description color based on length
            if (description.length > 160) {
                $('.seo-simple-description-count').css('color', '#dc3232');
            } else if (description.length > 150) {
                $('.seo-simple-description-count').css('color', '#ffb900');
            } else {
                $('.seo-simple-description-count').css('color', '#46b450');
            }
        }
        
        // Update preview on input
        $('#seo-simple-title, #seo-simple-description').on('input', updateSerpPreview);
        
        // Initial preview update
        updateSerpPreview();
        
        // SEO Analysis
        $('#seo-simple-analyze-seo').on('click', function() {
            const $button = $(this);
            const $spinner = $('#seo-simple-seo-spinner');
            
            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();
            
            // Get form data
            const postId = $('#post_ID').val();
            const title = $('#seo-simple-title').val();
            const description = $('#seo-simple-description').val();
            const focusKeyword = $('#seo-simple-focus-keyword').val();
            const content = $('#content').val() || tinyMCE.get('content')?.getContent() || '';
            
            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_simple_analyze_seo',
                    post_id: postId,
                    title: title,
                    description: description,
                    content: content,
                    focus_keyword: focusKeyword,
                    nonce: seoSimple.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateSeoAnalysis(response.data);
                    } else {
                        alert(seoSimple.messages.error);
                    }
                },
                error: function() {
                    alert(seoSimple.messages.error);
                },
                complete: function() {
                    // Hide loading state
                    $button.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });
        
        // Deep Analysis
        $('#seo-simple-analyze-deep').on('click', function() {
            const $button = $(this);
            const $spinner = $('#seo-simple-deep-spinner');
            
            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();
            
            // Get form data
            const postId = $('#post_ID').val();
            const title = $('#seo-simple-title').val();
            const description = $('#seo-simple-description').val();
            const focusKeyword = $('#seo-simple-focus-keyword').val();
            const content = $('#content').val() || tinyMCE.get('content')?.getContent() || '';
            
            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_simple_analyze_deep',
                    post_id: postId,
                    title: title,
                    description: description,
                    content: content,
                    focus_keyword: focusKeyword,
                    nonce: seoSimple.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateDeepAnalysis(response.data);
                    } else {
                        alert(seoSimple.messages.error);
                    }
                },
                error: function() {
                    alert(seoSimple.messages.error);
                },
                complete: function() {
                    // Hide loading state
                    $button.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });
        
        // Update SEO Analysis UI
        function updateSeoAnalysis(data) {
            // Update overall score
            if (data.overall) {
                $('#seo-simple-overall-score-fill').css('width', data.overall.score + '%');
                $('#seo-simple-overall-score-fill').css('background-color', getStatusColor(data.overall.status));
                $('.seo-simple-score-number').text(data.overall.score);
                $('.seo-simple-score-status').removeClass('seo-simple-status-good seo-simple-status-ok seo-simple-status-bad')
                    .addClass('seo-simple-status-' + data.overall.status)
                    .text(data.overall.recommendation);
            }
            
            // Update individual checks
            updateCheck('keyword-title', data.keyword_in_title);
            updateCheck('keyword-description', data.keyword_in_description);
            updateCheck('keyword-url', data.keyword_in_url);
            updateCheck('keyword-first-paragraph', data.keyword_in_first_paragraph);
            updateCheck('keyword-headings', data.keyword_in_headings);
            updateCheck('word-count', data.word_count);
            updateCheck('keyword-density', data.keyword_density);
            updateCheck('links', data.links);
            updateCheck('image-alt', data.image_alt_text);
        }
        
        // Update Deep Analysis UI
        function updateDeepAnalysis(data) {
            // Update overall score
            if (data.overall) {
                $('#seo-simple-deep-score-fill').css('width', data.overall.score + '%');
                $('#seo-simple-deep-score-fill').css('background-color', getStatusColor(data.overall.status));
                $('#seo-simple-deep-score').text(data.overall.score);
                $('#seo-simple-deep-score-fill').parent().next('.seo-simple-score-status')
                    .removeClass('seo-simple-status-good seo-simple-status-ok seo-simple-status-bad')
                    .addClass('seo-simple-status-' + data.overall.status)
                    .text(data.overall.recommendation);
            }
            
            // Update individual checks
            updateCheck('sentiment', data.sentiment);
            updateCheck('complexity', data.complexity);
            updateCheck('keyword-variations', data.keyword_variations);
            updateCheck('structure', data.content_structure);
            updateCheck('freshness', data.content_freshness);
        }
        
        // Update individual check UI
        function updateCheck(checkId, data) {
            const $indicator = $(`#seo-simple-check-${checkId} .seo-simple-indicator-dot`);
            const $details = $(`#seo-simple-check-${checkId}`).parent().find('.seo-simple-check-details');
            
            if (data) {
                // Update indicator
                $indicator.removeClass('seo-simple-status-good seo-simple-status-ok seo-simple-status-bad')
                    .addClass('seo-simple-status-' + data.status);
                
                // Update details
                if (checkId === 'word-count' && data.word_count !== undefined) {
                    $details.html(`Word count: ${data.word_count}. ${data.recommendation}`);
                } else if (checkId === 'keyword-density' && data.density !== undefined) {
                    $details.html(`Density: ${data.density}%. ${data.recommendation}`);
                } else if (checkId === 'links' && data.internal_links !== undefined) {
                    $details.html(`Internal links: ${data.internal_links}. ${data.recommendation}`);
                } else if (checkId === 'image-alt' && data.total_images !== undefined && data.total_images > 0) {
                    $details.html(`Images with alt text: ${data.images_with_alt}/${data.total_images} (${data.percentage_with_alt}%). ${data.recommendation}`);
                } else if (checkId === 'sentiment' && data.sentiment !== undefined) {
                    $details.html(`Sentiment: ${data.sentiment.charAt(0).toUpperCase() + data.sentiment.slice(1)} (${data.score}). ${data.recommendation}`);
                } else if (checkId === 'complexity' && data.complexity !== undefined) {
                    $details.html(`Complexity: ${data.complexity.charAt(0).toUpperCase() + data.complexity.slice(1)}. ${data.recommendation}`);
                } else if (checkId === 'keyword-variations' && data.total_related_terms !== undefined) {
                    $details.html(`Related terms found: ${data.total_related_terms}. ${data.recommendation}`);
                } else if (checkId === 'structure' && data.h1_count !== undefined) {
                    $details.html(`Structure: H1:${data.h1_count}, H2:${data.h2_count}, H3:${data.h3_count}. ${data.recommendation}`);
                } else if (checkId === 'freshness' && data.days_since_update !== undefined) {
                    $details.html(`Last updated: ${data.days_since_update} days ago. ${data.recommendation}`);
                } else {
                    $details.text(data.recommendation);
                }
            }
        }
        
        // Get status color
        function getStatusColor(status) {
            switch (status) {
                case 'good':
                    return '#46b450';
                case 'ok':
                    return '#ffb900';
                case 'bad':
                default:
                    return '#dc3232';
            }
        }
        
        // Schema type change handler
        $('#seo-simple-schema_type').on('change', function() {
            const schemaType = $(this).val();
            
            // Hide all schema fields
            $('#seo-simple-custom-schema-field, #seo-simple-faq-schema-field').hide();
            
            // Show relevant field based on selection
            if (schemaType === 'custom') {
                $('#seo-simple-custom-schema-field').show();
            } else if (schemaType === 'faq') {
                $('#seo-simple-faq-schema-field').show();
            }
        });
        
        // Validate schema
        $('#seo-simple-validate-schema').on('click', function() {
            const $button = $(this);
            const $spinner = $('#seo-simple-schema-spinner');
            const $result = $('#seo-simple-schema-validation-result');
            
            // Show loading state
            $button.prop('disabled', true);
            $spinner.show();
            $result.empty();
            
            // Get schema data
            const schema = $('#seo-simple-custom_schema').val();
            
            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'seo_simple_validate_schema',
                    schema: schema,
                    nonce: seoSimple.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    } else {
                        $result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $result.html('<div class="notice notice-error inline"><p>' + seoSimple.messages.error + '</p></div>');
                },
                complete: function() {
                    // Hide loading state
                    $button.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });
        
        // Add FAQ shortcode
        $('#seo-simple-add-faq').on('click', function() {
            const shortcode = '[seo_simple_faq question="Your Question"]Your Answer[/seo_simple_faq]';
            
            // Insert into content editor
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                tinyMCE.get('content').execCommand('mceInsertContent', false, shortcode);
            } else {
                $('#content').val($('#content').val() + shortcode);
            }
            
            // Switch to content tab
            $('#content-html').click();
        });
        
        // Initialize schema field visibility
        $('#seo-simple-schema_type').trigger('change');
    });

})(jQuery);