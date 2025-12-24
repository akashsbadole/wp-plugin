/**
 * Internal Links Admin JavaScript
 *
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize internal links functionality
    initInternalLinks();

    /**
     * Initialize internal links functionality.
     */
    function initInternalLinks() {
        // Get suggestions button
        $('.seo-simple-get-internal-link-suggestions').on('click', function(e) {
            e.preventDefault();
            getInternalLinkSuggestions($(this));
        });

        // Insert link buttons
        $(document).on('click', '.seo-simple-insert-link', function(e) {
            e.preventDefault();
            insertLink($(this));
        });

        // Search posts by keyword
        $('.seo-simple-search-posts-button').on('click', function(e) {
            e.preventDefault();
            searchPostsByKeyword($(this));
        });

        // Search on enter key
        $('.seo-simple-keyword-search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('.seo-simple-search-posts-button').click();
            }
        });

        // Get orphaned posts
        $('.seo-simple-get-orphaned-posts').on('click', function(e) {
            e.preventDefault();
            getOrphanedPosts($(this));
        });

        // Auto-refresh suggestions when content changes
        if (typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {
            // Gutenberg editor
            wp.data.subscribe(function() {
                const content = wp.data.select('core/editor').getEditedPostContent();
                const title = wp.data.select('core/editor').getEditedPostAttribute('title');
                const postId = wp.data.select('core/editor').getCurrentPostId();
                
                // Debounce the refresh
                clearTimeout(window.seoSimpleRefreshTimeout);
                window.seoSimpleRefreshTimeout = setTimeout(function() {
                    if (content && postId) {
                        refreshInternalLinkSuggestions(postId);
                    }
                }, 2000);
            });
        } else {
            // Classic editor
            $('#content, #title').on('input', function() {
                const postId = $('#post_ID').val();
                
                // Debounce the refresh
                clearTimeout(window.seoSimpleRefreshTimeout);
                window.seoSimpleRefreshTimeout = setTimeout(function() {
                    if (postId) {
                        refreshInternalLinkSuggestions(postId);
                    }
                }, 2000);
            });
        }
    }

    /**
     * Get internal link suggestions.
     *
     * @param {jQuery} button The button element.
     */
    function getInternalLinkSuggestions(button) {
        const postId = button.data('post-id');
        const container = button.closest('.seo-simple-internal-links-container');
        const suggestionsContainer = container.find('.seo-simple-internal-links-suggestions-container');
        const loadingIndicator = container.find('.seo-simple-loading');

        if (!postId) {
            showError(suggestionsContainer, 'Post ID not found.');
            return;
        }

        // Show loading
        button.prop('disabled', true);
        loadingIndicator.show();
        suggestionsContainer.empty();

        // Make AJAX request
        $.ajax({
            url: seo_simple_internal_links.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_get_internal_link_suggestions',
                post_id: postId,
                nonce: seo_simple_internal_links.nonce
            },
            success: function(response) {
                if (response.success) {
                    suggestionsContainer.html(response.data.html);
                    
                    // Update count
                    container.find('.seo-simple-suggestions-count').text(response.data.count);
                } else {
                    showError(suggestionsContainer, response.data.message || 'Error occurred.');
                }
            },
            error: function() {
                showError(suggestionsContainer, seo_simple_internal_links.error_text);
            },
            complete: function() {
                button.prop('disabled', false);
                loadingIndicator.hide();
            }
        });
    }

    /**
     * Refresh internal link suggestions.
     *
     * @param {int} postId The post ID.
     */
    function refreshInternalLinkSuggestions(postId) {
        const container = $('.seo-simple-internal-links-container');
        const suggestionsContainer = container.find('.seo-simple-internal-links-suggestions-container');

        // Don't refresh if container is not visible or user is actively interacting
        if (!container.is(':visible') || container.hasClass('seo-simple-user-interacting')) {
            return;
        }

        // Make AJAX request
        $.ajax({
            url: seo_simple_internal_links.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_get_internal_link_suggestions',
                post_id: postId,
                nonce: seo_simple_internal_links.nonce
            },
            success: function(response) {
                if (response.success) {
                    suggestionsContainer.html(response.data.html);
                    
                    // Update count
                    container.find('.seo-simple-suggestions-count').text(response.data.count);
                }
            }
        });
    }

    /**
     * Insert a link into the editor.
     *
     * @param {jQuery} button The button element.
     */
    function insertLink(button) {
        const url = button.data('url');
        const title = button.data('title');
        const anchorText = button.data('anchor') || title;
        
        if (!url) {
            alert('URL not found.');
            return;
        }

        // Mark container as user interacting
        button.closest('.seo-simple-internal-links-container').addClass('seo-simple-user-interacting');
        
        // Remove user interacting class after 5 seconds
        setTimeout(function() {
            $('.seo-simple-internal-links-container').removeClass('seo-simple-user-interacting');
        }, 5000);

        // Insert link based on editor type
        if (typeof wp !== 'undefined' && typeof wp.data !== 'undefined') {
            // Gutenberg editor
            insertLinkGutenberg(url, title, anchorText);
        } else {
            // Classic editor
            insertLinkClassic(url, title, anchorText);
        }

        // Show success message
        const originalText = button.text();
        button.text(seo_simple_internal_links.link_inserted_text);
        button.prop('disabled', true);

        setTimeout(function() {
            button.text(originalText);
            button.prop('disabled', false);
        }, 2000);
    }

    /**
     * Insert link in Gutenberg editor.
     *
     * @param {string} url The URL.
     * @param {string} title The title.
     * @param {string} anchorText The anchor text.
     */
    function insertLinkGutenberg(url, title, anchorText) {
        // Create link object
        const link = {
            url: url,
            opensInNewTab: false,
            title: title
        };

        // Apply format to selected text or insert new text
        wp.data.dispatch('core/block-editor').formatBlock(
            wp.data.select('core/block-editor').getSelectedBlockClientId(),
            { type: 'core/link', attributes: link },
            anchorText
        );
    }

    /**
     * Insert link in Classic editor.
     *
     * @param {string} url The URL.
     * @param {string} title The title.
     * @param {string} anchorText The anchor text.
     */
    function insertLinkClassic(url, title, anchorText) {
        // Check if we're in the visual editor
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
            // Visual editor
            const editor = tinyMCE.activeEditor;
            
            // Insert link
            editor.selection.setContent(
                '<a href="' + url + '" title="' + title + '">' + anchorText + '</a>'
            );
        } else {
            // Text editor
            const textarea = $('#content');
            const start = textarea[0].selectionStart;
            const end = textarea[0].selectionEnd;
            const text = textarea.val();
            
            // Insert link
            const before = text.substring(0, start);
            const after = text.substring(end);
            textarea.val(before + '<a href="' + url + '" title="' + title + '">' + anchorText + '</a>' + after);
        }
    }

    /**
     * Search posts by keyword.
     *
     * @param {jQuery} button The button element.
     */
    function searchPostsByKeyword(button) {
        const container = button.closest('.seo-simple-search-posts-container');
        const keywordInput = container.find('.seo-simple-keyword-search');
        const resultsContainer = container.find('.seo-simple-search-results');
        const loadingIndicator = container.find('.seo-simple-loading');
        
        const keyword = keywordInput.val().trim();
        
        if (!keyword) {
            showError(resultsContainer, 'Please enter a keyword to search.');
            return;
        }

        // Show loading
        button.prop('disabled', true);
        loadingIndicator.show();
        resultsContainer.empty();

        // Make AJAX request
        $.ajax({
            url: seo_simple_internal_links.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_search_posts_by_keyword',
                keyword: keyword,
                limit: 10,
                nonce: seo_simple_internal_links.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data.html);
                } else {
                    showError(resultsContainer, response.data.message || 'Error occurred.');
                }
            },
            error: function() {
                showError(resultsContainer, seo_simple_internal_links.error_text);
            },
            complete: function() {
                button.prop('disabled', false);
                loadingIndicator.hide();
            }
        });
    }

    /**
     * Get orphaned posts.
     *
     * @param {jQuery} button The button element.
     */
    function getOrphanedPosts(button) {
        const container = button.closest('.seo-simple-orphaned-posts-container');
        const resultsContainer = container.find('.seo-simple-orphaned-posts-results');
        const loadingIndicator = container.find('.seo-simple-loading');

        // Show loading
        button.prop('disabled', true);
        loadingIndicator.show();
        resultsContainer.empty();

        // Make AJAX request
        $.ajax({
            url: seo_simple_internal_links.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_get_orphaned_posts',
                limit: 10,
                nonce: seo_simple_internal_links.nonce
            },
            success: function(response) {
                if (response.success) {
                    resultsContainer.html(response.data.html);
                } else {
                    showError(resultsContainer, response.data.message || 'Error occurred.');
                }
            },
            error: function() {
                showError(resultsContainer, seo_simple_internal_links.error_text);
            },
            complete: function() {
                button.prop('disabled', false);
                loadingIndicator.hide();
            }
        });
    }

    /**
     * Show error message.
     *
     * @param {jQuery} container The container element.
     * @param {string} message The error message.
     */
    function showError(container, message) {
        container.html('<div class="notice notice-error"><p>' + message + '</p></div>');
    }
});