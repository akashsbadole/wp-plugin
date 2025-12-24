/**
 * HMS Accessibility Enhancements
 * 
 * Provides JavaScript-based accessibility improvements
 * including keyboard navigation, ARIA attributes, and screen reader support.
 * 
 * @package Hospital_Management_System
 * @subpackage Admin/JS
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Namespace for HMS Accessibility
    window.HMS_A11Y = window.HMS_A11Y || {};

    /**
     * Initialize accessibility enhancements
     */
    HMS_A11Y.init = function() {
        HMS_A11Y.addSkipLinks();
        HMS_A11Y.enhanceKeyboardNavigation();
        HMS_A11Y.improveFormAccessibility();
        HMS_A11Y.enhanceTableAccessibility();
        HMS_A11Y.improveModalAccessibility();
        HMS_A11Y.addAriaLabels();
        HMS_A11Y.manageFocus();
        HMS_A11Y.announceChanges();
    };

    /**
     * Add skip links for keyboard navigation
     */
    HMS_A11Y.addSkipLinks = function() {
        if ($('.hms-skip-link').length === 0) {
            var skipLinks = $('<div class="hms-skip-links">' +
                '<a href="#hms-main-content" class="hms-skip-link">' + 
                hmsA11Y.strings.skip_to_content + '</a>' +
                '<a href="#hms-main-navigation" class="hms-skip-link">' + 
                hmsA11Y.strings.skip_to_navigation + '</a>' +
                '</div>');
            
            $('body').prepend(skipLinks);
        }
        
        // Add main content landmark if it doesn't exist
        if ($('#hms-main-content').length === 0) {
            $('.hms-wrap').attr('id', 'hms-main-content').attr('role', 'main');
        }
    };

    /**
     * Enhance keyboard navigation
     */
    HMS_A11Y.enhanceKeyboardNavigation = function() {
        // Arrow key navigation for button groups
        $('.hms-btn-group').on('keydown', '.hms-btn', function(e) {
            var $buttons = $(this).parent().find('.hms-btn');
            var currentIndex = $buttons.index(this);
            var $target;

            switch(e.which) {
                case 37: // Left arrow
                case 38: // Up arrow
                    e.preventDefault();
                    $target = $buttons.eq(currentIndex - 1);
                    if ($target.length === 0) {
                        $target = $buttons.last();
                    }
                    $target.focus();
                    break;
                case 39: // Right arrow
                case 40: // Down arrow
                    e.preventDefault();
                    $target = $buttons.eq(currentIndex + 1);
                    if ($target.length === 0) {
                        $target = $buttons.first();
                    }
                    $target.focus();
                    break;
            }
        });

        // Escape key to close modals
        $(document).on('keydown', function(e) {
            if (e.which === 27) { // Escape key
                $('.hms-modal.show').each(function() {
                    HMS_A11Y.closeModal($(this));
                });
            }
        });

        // Enter and Space for button-like elements
        $(document).on('keydown', '[role="button"]:not(button):not(input)', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                e.preventDefault();
                $(this).click();
            }
        });

        // Tab trapping in modals
        $(document).on('keydown', '.hms-modal.show', function(e) {
            if (e.which === 9) { // Tab key
                HMS_A11Y.trapFocus($(this), e);
            }
        });
    };

    /**
     * Improve form accessibility
     */
    HMS_A11Y.improveFormAccessibility = function() {
        // Add required indicators
        $('.hms-form-group').each(function() {
            var $group = $(this);
            var $input = $group.find('.hms-input, .hms-select, .hms-textarea');
            var $label = $group.find('.hms-label');
            
            if ($input.attr('required') && $label.length) {
                if (!$label.find('.hms-required-indicator').length) {
                    $label.append(' <span class="hms-required-indicator" aria-label="' + 
                        hmsA11Y.strings.required_field + '">*</span>');
                }
                
                // Add aria-required
                $input.attr('aria-required', 'true');
            }
            
            // Link labels to inputs
            if ($input.length && $label.length) {
                var inputId = $input.attr('id');
                if (!inputId) {
                    inputId = 'hms-field-' + Math.random().toString(36).substr(2, 9);
                    $input.attr('id', inputId);
                }
                $label.attr('for', inputId);
            }
        });

        // Add error announcements
        $('.hms-form').on('submit', function(e) {
            var $form = $(this);
            var $errors = $form.find('.has-error');
            
            if ($errors.length > 0) {
                var errorCount = $errors.length;
                var message = errorCount === 1 ? 
                    hmsA11Y.strings.form_error_single : 
                    hmsA11Y.strings.form_error_multiple.replace('%d', errorCount);
                
                HMS_A11Y.announce(message, 'assertive');
                
                // Focus first error field
                setTimeout(function() {
                    $errors.first().find('.hms-input, .hms-select, .hms-textarea').focus();
                }, 100);
            }
        });

        // Real-time validation feedback
        $('.hms-input, .hms-select, .hms-textarea').on('blur', function() {
            var $input = $(this);
            var $group = $input.closest('.hms-form-group');
            
            if ($input.attr('required') && !$input.val().trim()) {
                $group.addClass('has-error');
                $input.attr('aria-invalid', 'true');
                
                if (!$group.find('.hms-error-message').length) {
                    $group.append('<span class="hms-error-message" role="alert">' + 
                        hmsA11Y.strings.required_field + '</span>');
                }
            } else {
                $group.removeClass('has-error');
                $input.attr('aria-invalid', 'false');
                $group.find('.hms-error-message').remove();
            }
        });
    };

    /**
     * Enhance table accessibility
     */
    HMS_A11Y.enhanceTableAccessibility = function() {
        $('.hms-table').each(function() {
            var $table = $(this);
            
            // Add table caption if missing
            if (!$table.find('caption').length && $table.closest('.hms-card').find('.hms-card-header h2, .hms-card-header h3').length) {
                var title = $table.closest('.hms-card').find('.hms-card-header h2, .hms-card-header h3').first().text();
                $table.prepend('<caption class="hms-sr-only">' + title + '</caption>');
            }
            
            // Add scope attributes to headers
            $table.find('th').each(function() {
                if (!$(this).attr('scope')) {
                    $(this).attr('scope', 'col');
                }
            });
            
            // Add row headers if first column contains headers
            $table.find('tbody tr').each(function() {
                var $firstCell = $(this).find('td:first-child');
                if ($firstCell.length && $firstCell.text().trim() && !$firstCell.attr('scope')) {
                    // Check if this looks like a row header (contains text, not numbers)
                    if (isNaN($firstCell.text().trim())) {
                        $firstCell.attr('scope', 'row');
                    }
                }
            });
            
            // Add sortable indicators
            $table.find('th[data-sortable]').each(function() {
                var $th = $(this);
                $th.attr('role', 'columnheader')
                   .attr('aria-sort', 'none')
                   .attr('tabindex', '0')
                   .addClass('hms-sortable');
                
                if (!$th.find('.hms-sort-indicator').length) {
                    $th.append('<span class="hms-sort-indicator hms-sr-only">' + 
                        hmsA11Y.strings.sortable_column + '</span>');
                }
            });
        });

        // Handle sortable column clicks
        $(document).on('click keydown', '.hms-sortable', function(e) {
            if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) {
                return;
            }
            
            e.preventDefault();
            var $th = $(this);
            var currentSort = $th.attr('aria-sort');
            var newSort = currentSort === 'ascending' ? 'descending' : 'ascending';
            
            // Reset other columns
            $th.siblings().attr('aria-sort', 'none');
            $th.attr('aria-sort', newSort);
            
            // Announce sort change
            var columnName = $th.text().trim();
            var message = hmsA11Y.strings.column_sorted
                .replace('%s', columnName)
                .replace('%s', newSort);
            HMS_A11Y.announce(message);
        });
    };

    /**
     * Improve modal accessibility
     */
    HMS_A11Y.improveModalAccessibility = function() {
        $('.hms-modal').each(function() {
            var $modal = $(this);
            
            // Add ARIA attributes
            $modal.attr('role', 'dialog')
                  .attr('aria-modal', 'true')
                  .attr('aria-hidden', 'true');
            
            // Link to title
            var $title = $modal.find('.hms-modal-title');
            if ($title.length) {
                var titleId = $title.attr('id') || 'hms-modal-title-' + Math.random().toString(36).substr(2, 9);
                $title.attr('id', titleId);
                $modal.attr('aria-labelledby', titleId);
            }
            
            // Add close button if missing
            if (!$modal.find('.hms-modal-close').length) {
                $modal.find('.hms-modal-header').append(
                    '<button type="button" class="hms-modal-close" aria-label="' + 
                    hmsA11Y.strings.close_modal + '">&times;</button>'
                );
            }
        });
    };

    /**
     * Add ARIA labels and descriptions
     */
    HMS_A11Y.addAriaLabels = function() {
        // Add labels to buttons without text
        $('.hms-btn').each(function() {
            var $btn = $(this);
            if (!$btn.text().trim() && !$btn.attr('aria-label')) {
                var title = $btn.attr('title');
                if (title) {
                    $btn.attr('aria-label', title);
                }
            }
        });

        // Add descriptions to form fields
        $('.hms-help-text').each(function() {
            var $help = $(this);
            var $input = $help.siblings('.hms-input, .hms-select, .hms-textarea');
            
            if ($input.length) {
                var helpId = 'hms-help-' + Math.random().toString(36).substr(2, 9);
                $help.attr('id', helpId);
                $input.attr('aria-describedby', helpId);
            }
        });

        // Add live regions for dynamic content
        if (!$('#hms-live-region').length) {
            $('body').append('<div id="hms-live-region" aria-live="polite" aria-atomic="true" class="hms-sr-only"></div>');
        }
        
        if (!$('#hms-live-region-assertive').length) {
            $('body').append('<div id="hms-live-region-assertive" aria-live="assertive" aria-atomic="true" class="hms-sr-only"></div>');
        }
    };

    /**
     * Manage focus for better keyboard navigation
     */
    HMS_A11Y.manageFocus = function() {
        var lastFocusedElement;

        // Store last focused element before modal opens
        $(document).on('hms:modal:opened', '.hms-modal', function() {
            lastFocusedElement = document.activeElement;
            
            // Focus first focusable element in modal
            var $modal = $(this);
            setTimeout(function() {
                var $focusable = $modal.find('input, select, textarea, button, [tabindex]:not([tabindex="-1"])').first();
                if ($focusable.length) {
                    $focusable.focus();
                } else {
                    $modal.find('.hms-modal-content').attr('tabindex', '-1').focus();
                }
            }, 100);
        });

        // Restore focus when modal closes
        $(document).on('hms:modal:closed', '.hms-modal', function() {
            if (lastFocusedElement) {
                $(lastFocusedElement).focus();
                lastFocusedElement = null;
            }
        });

        // Focus management for dynamic content
        $(document).on('click', '[data-hms-focus-target]', function() {
            var target = $(this).data('hms-focus-target');
            setTimeout(function() {
                $(target).focus();
            }, 100);
        });
    };

    /**
     * Announce changes to screen readers
     */
    HMS_A11Y.announceChanges = function() {
        // Announce form submissions
        $('.hms-form').on('submit', function() {
            HMS_A11Y.announce(hmsA11Y.strings.form_submitting);
        });

        // Announce AJAX loading states
        $(document).ajaxStart(function() {
            HMS_A11Y.announce(hmsA11Y.strings.loading);
        });

        $(document).ajaxComplete(function() {
            HMS_A11Y.announce(hmsA11Y.strings.content_updated);
        });

        // Announce table updates
        $(document).on('hms:table:updated', function(e, message) {
            HMS_A11Y.announce(message || hmsA11Y.strings.table_updated);
        });
    };

    /**
     * Trap focus within an element
     */
    HMS_A11Y.trapFocus = function($container, event) {
        var $focusable = $container.find('input, select, textarea, button, [href], [tabindex]:not([tabindex="-1"])');
        var $first = $focusable.first();
        var $last = $focusable.last();

        if (event.shiftKey) {
            if (document.activeElement === $first[0]) {
                event.preventDefault();
                $last.focus();
            }
        } else {
            if (document.activeElement === $last[0]) {
                event.preventDefault();
                $first.focus();
            }
        }
    };

    /**
     * Announce message to screen readers
     */
    HMS_A11Y.announce = function(message, priority) {
        priority = priority || 'polite';
        var $liveRegion = priority === 'assertive' ? 
            $('#hms-live-region-assertive') : 
            $('#hms-live-region');
        
        $liveRegion.text(message);
        
        // Clear after announcement
        setTimeout(function() {
            $liveRegion.empty();
        }, 1000);
    };

    /**
     * Close modal with proper focus management
     */
    HMS_A11Y.closeModal = function($modal) {
        $modal.removeClass('show').attr('aria-hidden', 'true');
        $modal.trigger('hms:modal:closed');
    };

    /**
     * Check if user prefers reduced motion
     */
    HMS_A11Y.prefersReducedMotion = function() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    };

    /**
     * Check if user is using keyboard navigation
     */
    HMS_A11Y.isKeyboardUser = function() {
        var keyboardUser = false;
        
        $(document).on('keydown', function() {
            keyboardUser = true;
            $('body').addClass('hms-keyboard-user');
        });
        
        $(document).on('mousedown', function() {
            keyboardUser = false;
            $('body').removeClass('hms-keyboard-user');
        });
        
        return keyboardUser;
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HMS_A11Y.init();
        HMS_A11Y.isKeyboardUser(); // Start keyboard detection
    });

    // Re-initialize on AJAX complete
    $(document).ajaxComplete(function() {
        HMS_A11Y.addAriaLabels();
        HMS_A11Y.enhanceTableAccessibility();
    });

})(jQuery);