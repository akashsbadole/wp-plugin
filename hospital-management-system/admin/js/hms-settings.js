/**
 * HMS Settings Page JavaScript
 * 
 * @package Hospital_Management_System
 * @subpackage Admin/JS
 */

(function($) {
    'use strict';

    /**
     * Settings Page Handler
     */
    var HMSSettings = {
        
        /**
         * Initialize the settings page
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initMediaUpload();
            this.initColorPickers();
            this.initPaymentGatewayFields();
            this.initNotificationToggles();
            this.initFormValidation();
            this.initAutoSave();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Tab navigation
            $(document).on('click', '.hms-settings-tabs .nav-tab', this.handleTabClick);
            
            // Form submission
            $(document).on('submit', '#hms-settings-form', this.handleFormSubmit);
            
            // Payment gateway change
            $(document).on('change', '#hms_payment_gateway', this.handlePaymentGatewayChange);
            
            // Notification toggles
            $(document).on('change', '.hms-notification-toggle input', this.handleNotificationToggle);
            
            // Role capability changes
            $(document).on('change', '.hms-capabilities-table input[type="checkbox"]', this.handleCapabilityChange);
            
            // Reset to defaults
            $(document).on('click', '.hms-reset-defaults', this.handleResetDefaults);
            
            // Test connection buttons
            $(document).on('click', '.hms-test-connection', this.handleTestConnection);
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            var activeTab = this.getActiveTab();
            this.showTab(activeTab);
        },

        /**
         * Get active tab from URL or default
         */
        getActiveTab: function() {
            var urlParams = new URLSearchParams(window.location.search);
            var tab = urlParams.get('tab');
            return tab || 'general-settings';
        },

        /**
         * Show specific tab
         */
        showTab: function(tabId) {
            // Hide all sections
            $('.hms-settings-section').removeClass('active');
            $('.nav-tab').removeClass('nav-tab-active');
            
            // Show selected section
            $('#' + tabId).addClass('active');
            $('.nav-tab[href*="tab=' + tabId + '"]').addClass('nav-tab-active');
            
            // Update URL without reload
            var url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({}, '', url);
        },

        /**
         * Handle tab click
         */
        handleTabClick: function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var tabId = href.split('tab=')[1];
            HMSSettings.showTab(tabId);
        },

        /**
         * Initialize media upload functionality
         */
        initMediaUpload: function() {
            var mediaUploader;
            
            // Upload logo button
            $(document).on('click', '#hms-upload-logo-button', function(e) {
                e.preventDefault();
                
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                mediaUploader = wp.media({
                    title: hms_settings_vars.strings.select_logo,
                    button: {
                        text: hms_settings_vars.strings.use_this_logo
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#hms_hospital_logo_id').val(attachment.id);
                    $('#hms-logo-preview').html('<img src="' + attachment.url + '" alt="Hospital Logo" style="max-width: 200px;">');
                    $('#hms-remove-logo-button').show();
                });
                
                mediaUploader.open();
            });
            
            // Remove logo button
            $(document).on('click', '#hms-remove-logo-button', function(e) {
                e.preventDefault();
                $('#hms_hospital_logo_id').val('');
                $('#hms-logo-preview').html('<div class="hms-media-preview empty">' + hms_settings_vars.strings.no_logo_selected + '</div>');
                $(this).hide();
            });
            
            // Upload favicon button
            $(document).on('click', '#hms-upload-favicon-button', function(e) {
                e.preventDefault();
                
                var faviconUploader = wp.media({
                    title: hms_settings_vars.strings.select_favicon,
                    button: {
                        text: hms_settings_vars.strings.use_this_favicon
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                faviconUploader.on('select', function() {
                    var attachment = faviconUploader.state().get('selection').first().toJSON();
                    $('#hms_hospital_favicon_id').val(attachment.id);
                    $('#hms-favicon-preview').html('<img src="' + attachment.url + '" alt="Hospital Favicon" style="max-width: 32px;">');
                    $('#hms-remove-favicon-button').show();
                });
                
                faviconUploader.open();
            });
            
            // Remove favicon button
            $(document).on('click', '#hms-remove-favicon-button', function(e) {
                e.preventDefault();
                $('#hms_hospital_favicon_id').val('');
                $('#hms-favicon-preview').html('<div class="hms-media-preview empty">' + hms_settings_vars.strings.no_favicon_selected + '</div>');
                $(this).hide();
            });
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.hms-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        var color = ui.color.toString();
                        $(this).siblings('.hms-color-value').text(color);
                    }
                });
            }
        },

        /**
         * Initialize payment gateway fields
         */
        initPaymentGatewayFields: function() {
            var selectedGateway = $('#hms_payment_gateway').val();
            this.showPaymentGatewayFields(selectedGateway);
        },

        /**
         * Handle payment gateway change
         */
        handlePaymentGatewayChange: function() {
            var selectedGateway = $(this).val();
            HMSSettings.showPaymentGatewayFields(selectedGateway);
        },

        /**
         * Show payment gateway specific fields
         */
        showPaymentGatewayFields: function(gateway) {
            $('.hms-gateway-keys').removeClass('active');
            $('#hms-' + gateway + '-keys').addClass('active');
        },

        /**
         * Initialize notification toggles
         */
        initNotificationToggles: function() {
            $('.hms-notification-toggle input').each(function() {
                var isChecked = $(this).is(':checked');
                var section = $(this).closest('.hms-notification-section');
                var fields = section.find('.hms-notification-fields');
                
                if (isChecked) {
                    fields.show();
                } else {
                    fields.hide();
                }
            });
        },

        /**
         * Handle notification toggle
         */
        handleNotificationToggle: function() {
            var isChecked = $(this).is(':checked');
            var section = $(this).closest('.hms-notification-section');
            var fields = section.find('.hms-notification-fields');
            
            if (isChecked) {
                fields.slideDown();
            } else {
                fields.slideUp();
            }
        },

        /**
         * Handle capability change
         */
        handleCapabilityChange: function() {
            var $checkbox = $(this);
            var role = $checkbox.data('role');
            var capability = $checkbox.data('capability');
            var isChecked = $checkbox.is(':checked');
            
            // Show loading state
            $checkbox.prop('disabled', true);
            
            // Send AJAX request to update capability
            $.ajax({
                url: hms_settings_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'hms_update_role_capability',
                    nonce: hms_settings_vars.nonce,
                    role: role,
                    capability: capability,
                    enabled: isChecked ? 1 : 0
                },
                success: function(response) {
                    if (response.success) {
                        HMSSettings.showMessage(response.data.message, 'success');
                    } else {
                        HMSSettings.showMessage(response.data.message, 'error');
                        // Revert checkbox state
                        $checkbox.prop('checked', !isChecked);
                    }
                },
                error: function() {
                    HMSSettings.showMessage(hms_settings_vars.strings.error_updating_capability, 'error');
                    // Revert checkbox state
                    $checkbox.prop('checked', !isChecked);
                },
                complete: function() {
                    $checkbox.prop('disabled', false);
                }
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            $('#hms-settings-form').on('submit', function() {
                return HMSSettings.validateForm();
            });
        },

        /**
         * Validate form before submission
         */
        validateForm: function() {
            var isValid = true;
            var errors = [];
            
            // Validate required fields
            $('.hms-settings-section.active input[required], .hms-settings-section.active select[required]').each(function() {
                if (!$(this).val().trim()) {
                    isValid = false;
                    var label = $(this).closest('tr').find('th label').text();
                    errors.push(label + ' ' + hms_settings_vars.strings.is_required);
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });
            
            // Validate email fields
            $('.hms-settings-section.active input[type="email"]').each(function() {
                var email = $(this).val().trim();
                if (email && !HMSSettings.isValidEmail(email)) {
                    isValid = false;
                    errors.push(hms_settings_vars.strings.invalid_email);
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });
            
            // Validate URL fields
            $('.hms-settings-section.active input[type="url"]').each(function() {
                var url = $(this).val().trim();
                if (url && !HMSSettings.isValidUrl(url)) {
                    isValid = false;
                    errors.push(hms_settings_vars.strings.invalid_url);
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });
            
            if (!isValid) {
                HMSSettings.showMessage(errors.join('<br>'), 'error');
            }
            
            return isValid;
        },

        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Validate URL format
         */
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch (e) {
                return false;
            }
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            var $form = $(this);
            var $submitButton = $form.find('input[type="submit"]');
            
            // Show loading state
            $submitButton.prop('disabled', true);
            $submitButton.val(hms_settings_vars.strings.saving);
            $form.addClass('hms-settings-loading');
            
            // Add spinner
            if (!$submitButton.siblings('.hms-settings-spinner').length) {
                $submitButton.after('<span class="hms-settings-spinner"></span>');
            }
        },

        /**
         * Initialize auto-save functionality
         */
        initAutoSave: function() {
            var autoSaveTimeout;
            
            $('.hms-settings-section input, .hms-settings-section select, .hms-settings-section textarea').on('change', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(function() {
                    HMSSettings.autoSave();
                }, 2000);
            });
        },

        /**
         * Auto-save settings
         */
        autoSave: function() {
            var $form = $('#hms-settings-form');
            var formData = $form.serialize();
            
            $.ajax({
                url: hms_settings_vars.ajax_url,
                type: 'POST',
                data: formData + '&action=hms_auto_save_settings&nonce=' + hms_settings_vars.nonce,
                success: function(response) {
                    if (response.success) {
                        HMSSettings.showMessage(hms_settings_vars.strings.settings_auto_saved, 'success', 2000);
                    }
                }
            });
        },

        /**
         * Handle reset to defaults
         */
        handleResetDefaults: function(e) {
            e.preventDefault();
            
            if (!confirm(hms_settings_vars.strings.confirm_reset_defaults)) {
                return;
            }
            
            var section = $(this).data('section');
            
            $.ajax({
                url: hms_settings_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'hms_reset_settings_defaults',
                    nonce: hms_settings_vars.nonce,
                    section: section
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        HMSSettings.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    HMSSettings.showMessage(hms_settings_vars.strings.error_resetting_defaults, 'error');
                }
            });
        },

        /**
         * Handle test connection
         */
        handleTestConnection: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var service = $button.data('service');
            var $section = $button.closest('.hms-integration-section, .hms-notification-section');
            
            // Get relevant settings
            var settings = {};
            $section.find('input, select').each(function() {
                settings[$(this).attr('name')] = $(this).val();
            });
            
            // Show loading state
            $button.prop('disabled', true);
            $button.text(hms_settings_vars.strings.testing_connection);
            
            $.ajax({
                url: hms_settings_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'hms_test_service_connection',
                    nonce: hms_settings_vars.nonce,
                    service: service,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        HMSSettings.showMessage(response.data.message, 'success');
                    } else {
                        HMSSettings.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    HMSSettings.showMessage(hms_settings_vars.strings.connection_test_failed, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $button.text(hms_settings_vars.strings.test_connection);
                }
            });
        },

        /**
         * Show message to user
         */
        showMessage: function(message, type, duration) {
            type = type || 'info';
            duration = duration || 5000;
            
            var $message = $('<div class="hms-settings-message ' + type + '">' + message + '</div>');
            
            // Remove existing messages
            $('.hms-settings-message').remove();
            
            // Add new message
            $('.hms-settings-page h1').after($message);
            
            // Auto-hide after duration
            if (duration > 0) {
                setTimeout(function() {
                    $message.fadeOut(function() {
                        $(this).remove();
                    });
                }, duration);
            }
            
            // Scroll to top to show message
            $('html, body').animate({
                scrollTop: $('.hms-settings-page').offset().top - 50
            }, 300);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        if ($('.hms-settings-page').length) {
            HMSSettings.init();
        }
    });

})(jQuery);