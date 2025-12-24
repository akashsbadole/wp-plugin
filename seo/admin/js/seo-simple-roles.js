/**
 * Roles Admin JavaScript
 *
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize roles functionality
    initRoles();

    /**
     * Initialize roles functionality.
     */
    function initRoles() {
        // Reset roles button
        $('.seo-simple-reset-roles').on('click', function(e) {
            e.preventDefault();
            resetRoles($(this));
        });

        // Add custom role button
        $('.seo-simple-add-role').on('click', function(e) {
            e.preventDefault();
            showAddRoleModal();
        });

        // Remove custom role buttons
        $(document).on('click', '.seo-simple-remove-role', function(e) {
            e.preventDefault();
            removeCustomRole($(this));
        });

        // Add role form submission
        $(document).on('submit', '#seo-simple-add-role-form', function(e) {
            e.preventDefault();
            submitAddRoleForm($(this));
        });

        // Close modal buttons
        $(document).on('click', '.seo-simple-modal-close, .seo-simple-modal-overlay', function(e) {
            e.preventDefault();
            closeModal();
        });

        // Prevent closing when clicking inside modal content
        $(document).on('click', '.seo-simple-modal-content', function(e) {
            e.stopPropagation();
        });

        // Toggle all checkboxes
        $('.seo-simple-toggle-all').on('change', function() {
            const $table = $(this).closest('table');
            const isChecked = $(this).prop('checked');
            
            $table.find('input[type="checkbox"]').not('.seo-simple-toggle-all').prop('checked', isChecked);
        });

        // Update toggle all checkbox state
        $('.seo-simple-capabilities-table input[type="checkbox"]').not('.seo-simple-toggle-all').on('change', function() {
            const $table = $(this).closest('table');
            const $toggleAll = $table.find('.seo-simple-toggle-all');
            const $checkboxes = $table.find('input[type="checkbox"]').not('.seo-simple-toggle-all');
            
            $toggleAll.prop('checked', $checkboxes.length === $checkboxes.filter(':checked').length);
        });
    }

    /**
     * Reset roles.
     *
     * @param {jQuery} button The button element.
     */
    function resetRoles(button) {
        if (!confirm(seo_simple_roles.reset_confirm_text)) {
            return;
        }

        // Show loading
        button.prop('disabled', true);
        const originalText = button.text();
        button.text('Resetting...');

        // Make AJAX request
        $.ajax({
            url: seo_simple_roles.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_reset_roles',
                nonce: seo_simple_roles.reset_nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(response.data.message || 'Error occurred.', 'error');
                }
            },
            error: function() {
                showNotification(seo_simple_roles.error_text, 'error');
            },
            complete: function() {
                button.prop('disabled', false);
                button.text(originalText);
            }
        });
    }

    /**
     * Show add role modal.
     */
    function showAddRoleModal() {
        // Create modal HTML
        const modalHtml = `
            <div class="seo-simple-modal-overlay">
                <div class="seo-simple-modal-content">
                    <div class="seo-simple-modal-header">
                        <h3><?php _e( 'Add Custom Role', 'seo-simple' ); ?></h3>
                        <button type="button" class="seo-simple-modal-close">&times;</button>
                    </div>
                    <div class="seo-simple-modal-body">
                        <form id="seo-simple-add-role-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="role_name"><?php _e( 'Role Name', 'seo-simple' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="role_name" name="role_name" class="regular-text" required>
                                        <p class="description"><?php _e( 'Lowercase letters, numbers, and underscores only.', 'seo-simple' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="display_name"><?php _e( 'Display Name', 'seo-simple' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="display_name" name="display_name" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <?php _e( 'Copy Capabilities From', 'seo-simple' ); ?>
                                    </th>
                                    <td>
                                        <select name="copy_from" class="regular-text">
                                            <option value=""><?php _e( 'None', 'seo-simple' ); ?></option>
                                            <option value="administrator"><?php _e( 'Administrator', 'seo-simple' ); ?></option>
                                            <option value="editor"><?php _e( 'Editor', 'seo-simple' ); ?></option>
                                            <option value="author"><?php _e( 'Author', 'seo-simple' ); ?></option>
                                            <option value="contributor"><?php _e( 'Contributor', 'seo-simple' ); ?></option>
                                            <option value="subscriber"><?php _e( 'Subscriber', 'seo-simple' ); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">
                                <button type="submit" class="button button-primary"><?php _e( 'Add Role', 'seo-simple' ); ?></button>
                                <button type="button" class="button seo-simple-modal-close"><?php _e( 'Cancel', 'seo-simple' ); ?></button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page
        $('body').append(modalHtml);

        // Show modal
        $('.seo-simple-modal-overlay').fadeIn(200);
    }

    /**
     * Close modal.
     */
    function closeModal() {
        $('.seo-simple-modal-overlay').fadeOut(200, function() {
            $(this).remove();
        });
    }

    /**
     * Submit add role form.
     *
     * @param {jQuery} form The form element.
     */
    function submitAddRoleForm(form) {
        // Get form data
        const formData = {
            action: 'seo_simple_add_custom_role',
            nonce: seo_simple_roles.add_role_nonce,
            role_name: form.find('#role_name').val(),
            display_name: form.find('#display_name').val(),
            capabilities: []
        };

        // Get capabilities from selected role
        const copyFrom = form.find('select[name="copy_from"]').val();
        if (copyFrom) {
            // Get capabilities from the selected role
            form.closest('.seo-simple-modal-content').find('.seo-simple-capabilities-table input[type="checkbox"]').each(function() {
                const capability = $(this).data('capability');
                if ($(this).prop('checked')) {
                    formData.capabilities.push(capability);
                }
            });
        }

        // Show loading
        form.find('button[type="submit"]').prop('disabled', true).text('Adding...');

        // Make AJAX request
        $.ajax({
            url: seo_simple_roles.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    closeModal();
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(response.data.message || 'Error occurred.', 'error');
                }
            },
            error: function() {
                showNotification(seo_simple_roles.error_text, 'error');
            },
            complete: function() {
                form.find('button[type="submit"]').prop('disabled', false).text('<?php _e( 'Add Role', 'seo-simple' ); ?>');
            }
        });
    }

    /**
     * Remove custom role.
     *
     * @param {jQuery} button The button element.
     */
    function removeCustomRole(button) {
        const roleName = button.data('role-name');
        const displayName = button.data('display-name');

        if (!confirm(seo_simple_roles.remove_role_confirm_text.replace('{role}', displayName))) {
            return;
        }

        // Show loading
        button.prop('disabled', true);
        const originalText = button.text();
        button.text('Removing...');

        // Make AJAX request
        $.ajax({
            url: seo_simple_roles.ajax_url,
            type: 'POST',
            data: {
                action: 'seo_simple_remove_custom_role',
                nonce: seo_simple_roles.remove_role_nonce,
                role_name: roleName
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    
                    // Remove role row from table
                    button.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    showNotification(response.data.message || 'Error occurred.', 'error');
                }
            },
            error: function() {
                showNotification(seo_simple_roles.error_text, 'error');
            },
            complete: function() {
                button.prop('disabled', false);
                button.text(originalText);
            }
        });
    }

    /**
     * Show notification.
     *
     * @param {string} message The notification message.
     * @param {string} type    The notification type (success, error).
     */
    function showNotification(message, type) {
        // Create notification HTML
        const notificationHtml = `
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'seo-simple' ); ?></span>
                </button>
            </div>
        `;

        // Add notification to top of page
        $('.wrap h1').after(notificationHtml);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

        // Handle dismiss button
        $(document).on('click', '.notice-dismiss', function() {
            $(this).closest('.notice').fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
});