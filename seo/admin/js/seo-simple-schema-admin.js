/**
 * Enhanced Schema Admin JavaScript
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
    'use strict';

    /**
     * Initialize the enhanced schema admin.
     */
    function initEnhancedSchemaAdmin() {
        // Schema type change handler
        $('#seo_simple_schema_type').on('change', handleSchemaTypeChange);
        
        // Validate schema button handler
        $('.seo-simple-validate-schema-button').on('click', handleValidateSchema);
        
        // Preview schema button handler
        $('.seo-simple-preview-schema-button').on('click', handlePreviewSchema);
        
        // Initialize date pickers
        initDatePickers();
        
        // Initialize media uploaders
        initMediaUploaders();
    }

    /**
     * Handle schema type change.
     */
    function handleSchemaTypeChange() {
        var schemaType = $(this).val();
        var postId = $('#post_ID').val();
        
        // Hide all schema-specific fields
        $('.seo-simple-custom-schema-field').hide();
        $('.seo-simple-schema-fields-container').hide();
        
        if (schemaType === 'custom') {
            $('.seo-simple-custom-schema-field').show();
        } else if (schemaType !== '') {
            // Show loading spinner
            $('.seo-simple-schema-fields-container').html('<div class="spinner is-active"></div>');
            $('.seo-simple-schema-fields-container').show();
            
            // AJAX request to get schema fields
            $.ajax({
                url: seoSimpleSchemaAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'seo_simple_get_schema_fields',
                    schema_type: schemaType,
                    post_id: postId,
                    nonce: seoSimpleSchemaAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.seo-simple-schema-fields-container').html(response.data.html);
                        
                        // Re-initialize date pickers and media uploaders
                        initDatePickers();
                        initMediaUploaders();
                    } else {
                        $('.seo-simple-schema-fields-container').html('<p>' + response.data.message + '</p>');
                    }
                },
                error: function() {
                    $('.seo-simple-schema-fields-container').html('<p>' + seoSimpleSchemaAdmin.i18n.errorLoadingFields + '</p>');
                }
            });
        }
    }

    /**
     * Handle validate schema button click.
     */
    function handleValidateSchema() {
        var schema = $('#seo_simple_custom_schema').val();
        
        if (!schema) {
            showValidationResult(seoSimpleSchemaAdmin.i18n.enterSchemaData, 'error');
            return;
        }
        
        // Show spinner
        $('.seo-simple-schema-spinner').addClass('is-active');
        
        // AJAX request to validate schema
        $.ajax({
            url: seoSimpleSchemaAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_simple_validate_schema',
                schema: schema,
                nonce: seoSimpleSchemaAdmin.nonce
            },
            success: function(response) {
                $('.seo-simple-schema-spinner').removeClass('is-active');
                
                if (response.success) {
                    showValidationResult(response.data.message, 'success');
                } else {
                    showValidationResult(response.data.message, 'error');
                }
            },
            error: function() {
                $('.seo-simple-schema-spinner').removeClass('is-active');
                showValidationResult(seoSimpleSchemaAdmin.i18n.errorValidatingSchema, 'error');
            }
        });
    }

    /**
     * Handle preview schema button click.
     */
    function handlePreviewSchema() {
        var postId = $(this).data('post-id');
        var schemaType = $('#seo_simple_schema_type').val();
        
        if (!schemaType) {
            showSchemaPreview('<p>' + seoSimpleSchemaAdmin.i18n.selectSchemaType + '</p>');
            return;
        }
        
        // Show spinner
        $('.seo-simple-preview-spinner').addClass('is-active');
        
        // AJAX request to preview schema
        $.ajax({
            url: seoSimpleSchemaAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seo_simple_preview_schema',
                schema_type: schemaType,
                post_id: postId,
                nonce: seoSimpleSchemaAdmin.nonce
            },
            success: function(response) {
                $('.seo-simple-preview-spinner').removeClass('is-active');
                
                if (response.success) {
                    showSchemaPreview('<pre>' + escapeHtml(response.data.schema) + '</pre>');
                } else {
                    showSchemaPreview('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $('.seo-simple-preview-spinner').removeClass('is-active');
                showSchemaPreview('<p>' + seoSimpleSchemaAdmin.i18n.errorGeneratingPreview + '</p>');
            }
        });
    }

    /**
     * Show validation result.
     *
     * @param {string} message The message to show.
     * @param {string} type The type of message (success or error).
     */
    function showValidationResult(message, type) {
        $('.seo-simple-schema-validation-result')
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .show();
    }

    /**
     * Show schema preview.
     *
     * @param {string} html The HTML to show.
     */
    function showSchemaPreview(html) {
        $('.seo-simple-schema-preview')
            .html(html)
            .show();
    }

    /**
     * Initialize date pickers.
     */
    function initDatePickers() {
        $('.seo-simple-date-picker').each(function() {
            if (!$(this).hasClass('hasDatepicker')) {
                $(this).datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true
                });
            }
        });
    }

    /**
     * Initialize media uploaders.
     */
    function initMediaUploaders() {
        $('.seo-simple-media-uploader').each(function() {
            var uploader = $(this);
            var target = $('#' + uploader.data('target'));
            
            uploader.off('click').on('click', function(e) {
                e.preventDefault();
                
                var frame = wp.media({
                    title: uploader.data('title') || seoSimpleSchemaAdmin.i18n.selectImage,
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    target.val(attachment.url);
                    
                    // Show preview if available
                    var preview = $('#' + target.attr('id') + '-preview');
                    if (preview.length) {
                        preview.attr('src', attachment.url).show();
                    }
                });
                
                frame.open();
            });
        });
    }

    /**
     * Escape HTML.
     *
     * @param {string} text The text to escape.
     * @return {string} The escaped text.
     */
    function escapeHtml(text) {
        var map = {
            '&': '&',
            '<': '<',
            '>': '>',
            '"': '"',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        initEnhancedSchemaAdmin();
    });

})(jQuery);