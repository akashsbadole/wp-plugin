/**
 * HMS UI Framework - JavaScript Components
 * 
 * Provides consistent interactive components and behaviors
 * across all HMS admin pages.
 * 
 * @package Hospital_Management_System
 * @subpackage Admin/JS
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Namespace for HMS UI components
    window.HMS_UI = window.HMS_UI || {};

    /**
     * Initialize HMS UI Framework
     */
    HMS_UI.init = function() {
        HMS_UI.initNotices();
        HMS_UI.initModals();
        HMS_UI.initTables();
        HMS_UI.initForms();
        HMS_UI.initTooltips();
        HMS_UI.initBreadcrumbs();
        HMS_UI.initConfirmActions();
    };

    /**
     * Notice Management
     */
    HMS_UI.initNotices = function() {
        // Auto-dismiss notices after 5 seconds
        $('.hms-notice:not(.hms-notice-persistent)').each(function() {
            var $notice = $(this);
            setTimeout(function() {
                HMS_UI.dismissNotice($notice);
            }, 5000);
        });

        // Handle manual dismiss
        $(document).on('click', '.hms-notice-dismiss', function(e) {
            e.preventDefault();
            HMS_UI.dismissNotice($(this).closest('.hms-notice'));
        });
    };

    HMS_UI.dismissNotice = function($notice) {
        $notice.fadeOut(300, function() {
            $(this).remove();
        });
    };

    HMS_UI.showNotice = function(message, type, persistent) {
        type = type || 'info';
        persistent = persistent || false;
        
        var dismissClass = persistent ? 'hms-notice-persistent' : '';
        var dismissButton = persistent ? '' : '<button type="button" class="hms-notice-dismiss">&times;</button>';
        
        var $notice = $('<div class="hms-notice hms-notice-' + type + ' hms-notice-dismissible ' + dismissClass + '">' +
            '<p>' + message + '</p>' + dismissButton + '</div>');
        
        $('.hms-wrap').prepend($notice);
        
        if (!persistent) {
            setTimeout(function() {
                HMS_UI.dismissNotice($notice);
            }, 5000);
        }
        
        return $notice;
    };

    /**
     * Modal Management
     */
    HMS_UI.initModals = function() {
        // Close modal on backdrop click
        $(document).on('click', '.hms-modal', function(e) {
            if (e.target === this) {
                HMS_UI.closeModal($(this));
            }
        });

        // Close modal on close button click
        $(document).on('click', '.hms-modal-close, [data-hms-dismiss="modal"]', function(e) {
            e.preventDefault();
            HMS_UI.closeModal($(this).closest('.hms-modal'));
        });

        // Close modal on ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                $('.hms-modal.show').each(function() {
                    HMS_UI.closeModal($(this));
                });
            }
        });
    };

    HMS_UI.openModal = function(modalId) {
        var $modal = $('#' + modalId);
        if ($modal.length) {
            $modal.addClass('show');
            $('body').addClass('hms-modal-open');
            
            // Focus first input
            setTimeout(function() {
                $modal.find('input, select, textarea').first().focus();
            }, 100);
            
            // Trigger custom event
            $modal.trigger('hms:modal:opened');
        }
    };

    HMS_UI.closeModal = function($modal) {
        $modal.removeClass('show');
        $('body').removeClass('hms-modal-open');
        
        // Trigger custom event
        $modal.trigger('hms:modal:closed');
    };

    /**
     * Table Enhancements
     */
    HMS_UI.initTables = function() {
        // Add responsive wrapper to tables
        $('.hms-table:not(.hms-table-responsive *)').each(function() {
            if (!$(this).parent().hasClass('hms-table-responsive')) {
                $(this).wrap('<div class="hms-table-responsive"></div>');
            }
        });

        // Initialize DataTables if available
        if ($.fn.DataTable) {
            $('.hms-data-table').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    HMS_UI.initDataTable($(this));
                }
            });
        }
    };

    HMS_UI.initDataTable = function($table) {
        var options = {
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries available",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"hms-table-controls"<"hms-table-length"l><"hms-table-search"f>>rtip',
            drawCallback: function() {
                // Reinitialize tooltips after table redraw
                HMS_UI.initTooltips();
            }
        };

        // Merge with custom options if provided
        var customOptions = $table.data('table-options');
        if (customOptions) {
            options = $.extend(true, options, customOptions);
        }

        return $table.DataTable(options);
    };

    /**
     * Form Enhancements
     */
    HMS_UI.initForms = function() {
        // Form validation
        $('.hms-form').each(function() {
            HMS_UI.initFormValidation($(this));
        });

        // Auto-resize textareas
        $(document).on('input', '.hms-textarea[data-auto-resize]', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Character counter
        $(document).on('input', '[data-max-length]', function() {
            var $input = $(this);
            var maxLength = parseInt($input.data('max-length'));
            var currentLength = $input.val().length;
            var $counter = $input.siblings('.hms-char-counter');
            
            if (!$counter.length) {
                $counter = $('<div class="hms-char-counter"></div>');
                $input.after($counter);
            }
            
            $counter.text(currentLength + '/' + maxLength);
            
            if (currentLength > maxLength) {
                $counter.addClass('hms-text-danger');
            } else {
                $counter.removeClass('hms-text-danger');
            }
        });
    };

    HMS_UI.initFormValidation = function($form) {
        $form.on('submit', function(e) {
            var isValid = true;
            
            // Clear previous errors
            $form.find('.hms-form-group').removeClass('has-error');
            $form.find('.hms-error-message').remove();
            
            // Validate required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.hms-form-group');
                
                if (!$field.val().trim()) {
                    isValid = false;
                    $group.addClass('has-error');
                    $group.append('<span class="hms-error-message">This field is required.</span>');
                }
            });
            
            // Validate email fields
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.hms-form-group');
                var email = $field.val().trim();
                
                if (email && !HMS_UI.isValidEmail(email)) {
                    isValid = false;
                    $group.addClass('has-error');
                    $group.append('<span class="hms-error-message">Please enter a valid email address.</span>');
                }
            });
            
            // Validate phone fields
            $form.find('input[type="tel"], input[data-type="phone"]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.hms-form-group');
                var phone = $field.val().trim();
                
                if (phone && !HMS_UI.isValidPhone(phone)) {
                    isValid = false;
                    $group.addClass('has-error');
                    $group.append('<span class="hms-error-message">Please enter a valid phone number.</span>');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Scroll to first error
                var $firstError = $form.find('.has-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 300);
                }
                
                HMS_UI.showNotice('Please correct the errors below.', 'error');
            }
        });
    };

    HMS_UI.isValidEmail = function(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    };

    HMS_UI.isValidPhone = function(phone) {
        var regex = /^[\+]?[1-9][\d]{0,15}$/;
        return regex.test(phone.replace(/[\s\-\(\)]/g, ''));
    };

    /**
     * Tooltip Management
     */
    HMS_UI.initTooltips = function() {
        // Simple tooltip implementation
        $('[data-tooltip]').each(function() {
            var $element = $(this);
            var tooltipText = $element.data('tooltip');
            
            if (!$element.attr('title')) {
                $element.attr('title', tooltipText);
            }
        });
    };

    /**
     * Breadcrumb Management
     */
    HMS_UI.initBreadcrumbs = function() {
        // Auto-generate breadcrumbs if container exists but is empty
        var $breadcrumb = $('.hms-breadcrumb');
        if ($breadcrumb.length && $breadcrumb.children().length === 0) {
            HMS_UI.generateBreadcrumbs($breadcrumb);
        }
    };

    HMS_UI.generateBreadcrumbs = function($container) {
        var breadcrumbs = [];
        
        // Add Dashboard
        breadcrumbs.push({
            text: 'Dashboard',
            url: adminUrl + 'admin.php?page=hms_dashboard'
        });
        
        // Get current page info
        var currentPage = HMS_UI.getCurrentPageInfo();
        if (currentPage) {
            if (currentPage.parent) {
                breadcrumbs.push({
                    text: currentPage.parent.text,
                    url: currentPage.parent.url
                });
            }
            
            breadcrumbs.push({
                text: currentPage.text,
                url: null // Current page
            });
        }
        
        // Render breadcrumbs
        var html = '';
        breadcrumbs.forEach(function(crumb, index) {
            var isLast = index === breadcrumbs.length - 1;
            var itemClass = isLast ? 'hms-breadcrumb-item active' : 'hms-breadcrumb-item';
            
            if (isLast || !crumb.url) {
                html += '<li class="' + itemClass + '">' + crumb.text + '</li>';
            } else {
                html += '<li class="' + itemClass + '"><a href="' + crumb.url + '">' + crumb.text + '</a></li>';
            }
        });
        
        $container.html(html);
    };

    HMS_UI.getCurrentPageInfo = function() {
        var urlParams = new URLSearchParams(window.location.search);
        var page = urlParams.get('page');
        
        var pageMap = {
            'hms_patients': { text: 'Patients', parent: null },
            'hms_patient_registration': { text: 'Patient Registration', parent: { text: 'Patients', url: adminUrl + 'admin.php?page=hms_patients' } },
            'hms_appointments': { text: 'Appointments', parent: null },
            'hms_appointment_details': { text: 'Appointment Details', parent: { text: 'Appointments', url: adminUrl + 'admin.php?page=hms_appointments' } },
            'hms_prescriptions': { text: 'Prescriptions', parent: null },
            'hms_prescription_details': { text: 'Prescription Details', parent: { text: 'Prescriptions', url: adminUrl + 'admin.php?page=hms_prescriptions' } },
            'hms_billing': { text: 'Billing', parent: null },
            'hms_bill_details': { text: 'Bill Details', parent: { text: 'Billing', url: adminUrl + 'admin.php?page=hms_billing' } },
            'hms_pharmacy': { text: 'Pharmacy', parent: null },
            'hms_lab_tests': { text: 'Lab Tests', parent: null },
            'hms_reports': { text: 'Reports', parent: null },
            'hms_settings': { text: 'Settings', parent: null }
        };
        
        return pageMap[page] || null;
    };

    /**
     * Confirmation Actions
     */
    HMS_UI.initConfirmActions = function() {
        $(document).on('click', '[data-confirm]', function(e) {
            var message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    };

    /**
     * Loading States
     */
    HMS_UI.showLoading = function($element, text) {
        text = text || 'Loading...';
        
        if ($element.is('button')) {
            $element.data('original-text', $element.text());
            $element.prop('disabled', true).text(text);
        } else {
            $element.addClass('hms-loading');
            if (!$element.find('.hms-loading-spinner').length) {
                $element.append('<div class="hms-loading-spinner"></div>');
            }
        }
    };

    HMS_UI.hideLoading = function($element) {
        if ($element.is('button')) {
            var originalText = $element.data('original-text');
            $element.prop('disabled', false).text(originalText || 'Submit');
        } else {
            $element.removeClass('hms-loading');
            $element.find('.hms-loading-spinner').remove();
        }
    };

    /**
     * AJAX Helper
     */
    HMS_UI.ajax = function(options) {
        var defaults = {
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            beforeSend: function() {
                if (options.loadingElement) {
                    HMS_UI.showLoading(options.loadingElement, options.loadingText);
                }
            },
            complete: function() {
                if (options.loadingElement) {
                    HMS_UI.hideLoading(options.loadingElement);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                HMS_UI.showNotice('An error occurred. Please try again.', 'error');
            }
        };
        
        return $.ajax($.extend(defaults, options));
    };

    /**
     * Utility Functions
     */
    HMS_UI.formatCurrency = function(amount, currency) {
        currency = currency || '₹';
        return currency + parseFloat(amount).toFixed(2);
    };

    HMS_UI.formatDate = function(date, format) {
        format = format || 'Y-m-d';
        var d = new Date(date);
        
        var formats = {
            'Y-m-d': d.getFullYear() + '-' + 
                     String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                     String(d.getDate()).padStart(2, '0'),
            'd/m/Y': String(d.getDate()).padStart(2, '0') + '/' + 
                     String(d.getMonth() + 1).padStart(2, '0') + '/' + 
                     d.getFullYear(),
            'M d, Y': d.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            })
        };
        
        return formats[format] || d.toLocaleDateString();
    };

    HMS_UI.debounce = function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HMS_UI.init();
    });

    // Re-initialize on AJAX complete (for dynamic content)
    $(document).ajaxComplete(function() {
        HMS_UI.initTooltips();
    });

})(jQuery);