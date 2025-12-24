<div class="wrap clr-wrap">
    <h1><?php _e('Email Notifications', 'clr'); ?></h1>
    
    <div class="clr-email-notifications-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Notification Settings', 'clr'); ?></h2>
            <p><?php _e('Configure email notifications for redirect events.', 'clr'); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields('clr_email_notifications'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Email Notifications', 'clr'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="clr_email_notifications_enabled" value="1" <?php checked(get_option('clr_email_notifications_enabled'), 1); ?>>
                                <?php _e('Enable email notifications', 'clr'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Notification Events', 'clr'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="clr_notification_events[]" value="redirect" <?php checked(in_array('redirect', get_option('clr_notification_events', array())), true); ?>>
                                <?php _e('User redirected', 'clr'); ?>
                            </label><br>
                            
                            <label>
                                <input type="checkbox" name="clr_notification_events[]" value="login" <?php checked(in_array('login', get_option('clr_notification_events', array())), true); ?>>
                                <?php _e('User login', 'clr'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Recipient Email', 'clr'); ?></th>
                        <td>
                            <input type="email" name="clr_notification_email" value="<?php echo esc_attr(get_option('clr_notification_email')); ?>" class="regular-text">
                            <p class="description"><?php _e('Leave empty to use the admin email address.', 'clr'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Email Template', 'clr'); ?></th>
                        <td>
                            <select name="clr_email_template">
                                <option value="default" <?php selected(get_option('clr_email_template'), 'default'); ?>><?php _e('Default Template', 'clr'); ?></option>
                                <option value="custom" <?php selected(get_option('clr_email_template'), 'custom'); ?>><?php _e('Custom Template', 'clr'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Custom Email Template', 'clr'); ?></th>
                        <td>
                            <textarea name="clr_email_custom_template" rows="10" class="large-text"><?php echo esc_textarea(get_option('clr_email_custom_template')); ?></textarea>
                            <p class="description"><?php _e('Available placeholders: {site_name}, {site_url}, {admin_url}, {user_id}, {user_login}, {user_email}, {user_roles}, {current_time}, {ip_address}, {user_agent}, {redirect_url}, {rule_type}, {rule_info}', 'clr'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Test Email Notification', 'clr'); ?></h2>
            <p><?php _e('Send a test email to verify that email notifications are working correctly.', 'clr'); ?></p>
            
            <div class="clr-test-email-section">
                <button type="button" class="button button-primary" id="clr-test-email">
                    <?php _e('Send Test Email', 'clr'); ?>
                </button>
                
                <div id="clr-test-email-result" style="display:none; margin-top: 15px;">
                    <!-- Test result will be displayed here -->
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Notification History', 'clr'); ?></h2>
            <p><?php _e('View recent email notifications that have been sent.', 'clr'); ?></p>
            
            <div class="clr-notification-history-section">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('Time', 'clr'); ?></th>
                            <th><?php _e('Event', 'clr'); ?></th>
                            <th><?php _e('User', 'clr'); ?></th>
                            <th><?php _e('Recipient', 'clr'); ?></th>
                            <th><?php _e('Status', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-notification-history-list">
                        <tr>
                            <td colspan="6"><?php _e('Loading notification history...', 'clr'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="clr-pagination">
                    <!-- Pagination will be added here if needed -->
                </div>
            </div>
        </div>
    </div>
</div>