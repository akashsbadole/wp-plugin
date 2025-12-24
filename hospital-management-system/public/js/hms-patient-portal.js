/**
 * Hospital Management System - Patient Portal Scripts
 *
 * Handles frontend JavaScript interactions for the patient portal,
 * including AJAX calls for forms and dynamic content loading.
 *
 * @package Hospital_Management_System
 * @subpackage Public/JS
 */

(function($) {
    'use strict';

    // Ensure hms_ajax_obj is available
    if (typeof hms_ajax_obj === 'undefined') {
        console.error('hms_ajax_obj is not defined. Patient portal AJAX functionality may not work.');
        return;
    }

    // Document Ready
    $(document).ready(function() {

        // --- General AJAX Error Handling ---
        $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
            console.error('AJAX Error:', thrownError, jqXHR);
            // Optionally, display a user-friendly error message in a common element
            // For example, show a notification at the top of the page.
        });

        // --- Patient Registration ---
        $('body').on('submit', '#hms-patient-registration-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var loader = form.find('.hms-loader');
            var messageDiv = form.find('.hms-message');

            submitBtn.prop('disabled', true);
            loader.show();
            messageDiv.hide().removeClass('hms-success hms-error').empty();

            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=hms_patient_register&nonce=' + hms_ajax_obj.nonce,
                success: function(response) {
                    if (response.success) {
                        messageDiv.addClass('hms-success').html(response.data.message).show();
                        form[0].reset();
                        // Redirect to login or dashboard after successful registration
                        if (response.data.redirect_url) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect_url;
                            }, 2000);
                        }
                    } else {
                        messageDiv.addClass('hms-error').html(response.data.message).show();
                    }
                },
                error: function() {
                    messageDiv.addClass('hms-error').html(hms_ajax_obj.generic_error_message || '<?php _e('An unexpected error occurred. Please try again.', 'hospital-management-system'); ?>').show();
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    loader.hide();
                }
            });
        });

        // --- Patient Login (Custom, if needed) ---
        // Note: Standard login is often handled by WordPress core.
        // This is a placeholder for a custom AJAX login if implemented.
        $('body').on('submit', '#hms-patient-login-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var loader = form.find('.hms-loader');
            var messageDiv = form.find('.hms-message');

            submitBtn.prop('disabled', true);
            loader.show();
            messageDiv.hide().removeClass('hms-success hms-error').empty();

            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=hms_patient_login&nonce=' + hms_ajax_obj.nonce,
                success: function(response) {
                    if (response.success) {
                        messageDiv.addClass('hms-success').html(response.data.message).show();
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    } else {
                        messageDiv.addClass('hms-error').html(response.data.message).show();
                    }
                },
                error: function() {
                    messageDiv.addClass('hms-error').html(hms_ajax_obj.generic_error_message || '<?php _e('Login failed. Please check your credentials.', 'hospital-management-system'); ?>').show();
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    loader.hide();
                }
            });
        });

        // --- Forgot Password ---
        $('body').on('submit', '#hms-forgot-password-form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var loader = form.find('.hms-loader');
            var messageDiv = form.find('.hms-message');

            submitBtn.prop('disabled', true);
            loader.show();
            messageDiv.hide().removeClass('hms-success hms-error').empty();

            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=hms_forgot_password&nonce=' + hms_ajax_obj.nonce,
                success: function(response) {
                    if (response.success) {
                        messageDiv.addClass('hms-success').html(response.data.message).show();
                        form[0].reset();
                    } else {
                        messageDiv.addClass('hms-error').html(response.data.message).show();
                    }
                },
                error: function() {
                    messageDiv.addClass('hms-error').html(hms_ajax_obj.generic_error_message || '<?php _e('An error occurred. Please try again.', 'hospital-management-system'); ?>').show();
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    loader.hide();
                }
            });
        });

        // --- Get Doctors by Department (for Appointment Booking) ---
        // This is already handled in online-appointment-booking.php template,
        // but can be centralized here if preferred.
        // For now, it remains in the template for simplicity.

        // --- Patient Dashboard AJAX Loaders ---
        // These functions are called from patient-portal-dashboard.php
        // They are defined here to keep the template cleaner.
        window.hmsLoadUpcomingAppointments = function(patient_id) {
            var container = $('#hms-upcoming-appointments-list');
            container.html('<p class="hms-loading-text"><?php _e('Loading appointments...', 'hospital-management-system'); ?></p>');
            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: { action: 'hms_patient_get_upcoming_appointments', patient_id: patient_id, nonce: hms_ajax_obj.nonce },
                success: function(response) {
                    if (response.success) {
                        container.html(response.data);
                    } else {
                        container.html('<p class="hms-error-message">' + response.data + '</p>');
                    }
                },
                error: function() {
                    container.html('<p class="hms-error-message"><?php _e('Failed to load appointments.', 'hospital-management-system'); ?></p>');
                }
            });
        };

        window.hmsLoadRecentBills = function(patient_id) {
            var container = $('#hms-recent-bills-list');
            container.html('<p class="hms-loading-text"><?php _e('Loading bills...', 'hospital-management-system'); ?></p>');
            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: { action: 'hms_patient_get_recent_bills', patient_id: patient_id, nonce: hms_ajax_obj.nonce },
                success: function(response) {
                    if (response.success) {
                        container.html(response.data);
                    } else {
                        container.html('<p class="hms-error-message">' + response.data + '</p>');
                    }
                },
                error: function() {
                    container.html('<p class="hms-error-message"><?php _e('Failed to load bills.', 'hospital-management-system'); ?></p>');
                }
            });
        };

        window.hmsLoadMedicalRecordsSummary = function(patient_id) {
            var container = $('#hms-medical-records-list');
            container.html('<p class="hms-loading-text"><?php _e('Loading records...', 'hospital-management-system'); ?></p>');
            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: { action: 'hms_patient_get_medical_records_summary', patient_id: patient_id, nonce: hms_ajax_obj.nonce },
                success: function(response) {
                    if (response.success) {
                        container.html(response.data);
                    } else {
                        container.html('<p class="hms-error-message">' + response.data + '</p>');
                    }
                },
                error: function() {
                    container.html('<p class="hms-error-message"><?php _e('Failed to load records.', 'hospital-management-system'); ?></p>');
                }
            });
        };

        // --- Modal Content Loading (for Dashboard) ---
        window.hmsLoadAjaxContentInModal = function(action, title, data) {
            var modalBody = $('#hms-modal-body');
            var modal = $('#hms-dashboard-modal');
            modalBody.html('<p class="hms-loading-text"><?php _e('Loading...', 'hospital-management-system'); ?></p>');
            modal.show();

            $.ajax({
                url: hms_ajax_obj.ajax_url,
                type: 'POST',
                data: $.extend({ action: action, nonce: hms_ajax_obj.nonce }, data),
                success: function(response) {
                    if (response.success) {
                        modalBody.html('<h2>' + title + '</h2>' + response.data);
                        // Re-initialize any event handlers or scripts within the modal content if necessary
                    } else {
                        modalBody.html('<p class="hms-error-message">' + response.data + '</p>');
                    }
                },
                error: function() {
                    modalBody.html('<p class="hms-error-message"><?php _e('An error occurred. Please try again.', 'hospital-management-system'); ?></p>');
                }
            });
        };

        // --- Profile Update Form (within Modal) ---
        // The submission is handled by a delegated event in patient-portal-dashboard.php
        // This function can be used to load the form into the modal.
        window.hmsLoadProfileUpdateForm = function(patient_id) {
            window.hmsLoadAjaxContentInModal('hms_patient_update_profile_form', '<?php _e('Edit Profile', 'hospital-management-system'); ?>', { patient_id: patient_id });
        };
        
        // --- Change Password Form (within Modal) ---
        window.hmsLoadChangePasswordForm = function() {
            window.hmsLoadAjaxContentInModal('hms_patient_change_password_form', '<?php _e('Change Password', 'hospital-management-system'); ?>');
        };

    }); // End Document Ready

})(jQuery); // End IIFE
