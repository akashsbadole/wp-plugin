<div class="wrap clr-wrap">
    <h1><?php _e('Premium Analytics', 'clr'); ?></h1>
    
    <?php if (!clr_is_premium()): ?>
    <div class="notice notice-warning">
        <p><?php _e('Premium features require a valid license key. Please activate your license to unlock these features.', 'clr'); ?></p>
        <p><a href="<?php echo admin_url('admin.php?page=clr-settings'); ?>" class="button button-primary"><?php _e('Activate License', 'clr'); ?></a></p>
    </div>
    <?php else: ?>
    
    <div class="clr-premium-analytics-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Real-time Analytics Dashboard', 'clr'); ?></h2>
            
            <div class="clr-real-time-dashboard">
                <div class="clr-metric-box">
                    <h3><?php _e('Active Sessions', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-active-sessions">-</div>
                    <div class="clr-metric-subtitle"><?php _e('Last 5 minutes', 'clr'); ?></div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('Events Today', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-events-today">-</div>
                    <div class="clr-metric-subtitle" id="clr-events-today-change">-</div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('Page Views', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-page-views">-</div>
                    <div class="clr-metric-subtitle" id="clr-page-views-change">-</div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('User Interactions', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-user-interactions">-</div>
                    <div class="clr-metric-subtitle" id="clr-user-interactions-change">-</div>
                </div>
            </div>
            
            <div class="clr-real-time-events">
                <h3><?php _e('Live Event Stream', 'clr'); ?></h3>
                <div class="clr-event-stream" id="clr-event-stream">
                    <div class="clr-event-stream-placeholder"><?php _e('Waiting for events...', 'clr'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('User Journey Analysis', 'clr'); ?></h2>
            
            <div class="clr-user-journey-container">
                <div class="clr-user-journey-filter">
                    <label for="clr-journey-user"><?php _e('Select User:', 'clr'); ?></label>
                    <select id="clr-journey-user" class="regular-text">
                        <option value=""><?php _e('Select a user', 'clr'); ?></option>
                    </select>
                    
                    <label for="clr-journey-days"><?php _e('Time Period:', 'clr'); ?></label>
                    <select id="clr-journey-days">
                        <option value="7"><?php _e('Last 7 days', 'clr'); ?></option>
                        <option value="30" selected><?php _e('Last 30 days', 'clr'); ?></option>
                        <option value="90"><?php _e('Last 90 days', 'clr'); ?></option>
                    </select>
                    
                    <button type="button" class="button" id="clr-load-user-journey"><?php _e('Load Journey', 'clr'); ?></button>
                </div>
                
                <div class="clr-user-journey-results" id="clr-user-journey-results">
                    <p class="description"><?php _e('Select a user to view their journey through your site.', 'clr'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Conversion Funnel', 'clr'); ?></h2>
            
            <div class="clr-conversion-funnel-container">
                <div class="clr-funnel-filter">
                    <label for="clr-funnel-days"><?php _e('Time Period:', 'clr'); ?></label>
                    <select id="clr-funnel-days">
                        <option value="7"><?php _e('Last 7 days', 'clr'); ?></option>
                        <option value="30" selected><?php _e('Last 30 days', 'clr'); ?></option>
                        <option value="90"><?php _e('Last 90 days', 'clr'); ?></option>
                    </select>
                    
                    <button type="button" class="button" id="clr-refresh-funnel"><?php _e('Refresh', 'clr'); ?></button>
                </div>
                
                <div class="clr-conversion-funnel" id="clr-conversion-funnel">
                    <div class="clr-funnel-placeholder"><?php _e('Loading conversion funnel...', 'clr'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Interaction Heatmap', 'clr'); ?></h2>
            
            <div class="clr-heatmap-container">
                <div class="clr-heatmap-filter">
                    <label for="clr-heatmap-post"><?php _e('Select Page/Post:', 'clr'); ?></label>
                    <select id="clr-heatmap-post" class="regular-text">
                        <option value=""><?php _e('Select a page or post', 'clr'); ?></option>
                        <?php
                        // Get recent posts and pages
                        $recent_posts = get_posts(array(
                            'numberposts' => 20,
                            'post_type' => array('post', 'page'),
                            'post_status' => 'publish'
                        ));
                        
                        foreach ($recent_posts as $post) {
                            echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <label for="clr-heatmap-days"><?php _e('Time Period:', 'clr'); ?></label>
                    <select id="clr-heatmap-days">
                        <option value="7"><?php _e('Last 7 days', 'clr'); ?></option>
                        <option value="30" selected><?php _e('Last 30 days', 'clr'); ?></option>
                        <option value="90"><?php _e('Last 90 days', 'clr'); ?></option>
                    </select>
                    
                    <button type="button" class="button" id="clr-load-heatmap"><?php _e('Load Heatmap', 'clr'); ?></button>
                </div>
                
                <div class="clr-heatmap-results" id="clr-heatmap-results">
                    <p class="description"><?php _e('Select a page or post to view interaction heatmap.', 'clr'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Custom Reports', 'clr'); ?></h2>
            
            <div class="clr-custom-reports-container">
                <div class="clr-reports-list">
                    <h3><?php _e('Saved Reports', 'clr'); ?></h3>
                    
                    <div class="clr-reports-actions">
                        <button type="button" class="button button-primary" id="clr-create-new-report"><?php _e('Create New Report', 'clr'); ?></button>
                    </div>
                    
                    <div class="clr-reports-table-container">
                        <table class="wp-list-table widefat fixed striped clr-reports-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Name', 'clr'); ?></th>
                                    <th><?php _e('Description', 'clr'); ?></th>
                                    <th><?php _e('Created', 'clr'); ?></th>
                                    <th><?php _e('Actions', 'clr'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="clr-reports-list">
                                <tr>
                                    <td colspan="4"><?php _e('No reports found. Create your first report to get started.', 'clr'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Third-party Integrations', 'clr'); ?></h2>
            
            <div class="clr-integrations-container">
                <div class="clr-integration-box">
                    <h3><?php _e('Google Analytics', 'clr'); ?></h3>
                    <p><?php _e('Connect your Google Analytics account to import data and enhance your reports.', 'clr'); ?></p>
                    
                    <div class="clr-integration-status">
                        <?php $ga_connected = get_option('clr_ga_connected', false); ?>
                        <span class="clr-status <?php echo $ga_connected ? 'active' : 'inactive'; ?>">
                            <?php echo $ga_connected ? __('Connected', 'clr') : __('Not Connected', 'clr'); ?>
                        </span>
                    </div>
                    
                    <div class="clr-integration-actions">
                        <?php if ($ga_connected): ?>
                            <button type="button" class="button" id="clr-disconnect-ga"><?php _e('Disconnect', 'clr'); ?></button>
                        <?php else: ?>
                            <button type="button" class="button button-primary" id="clr-connect-ga"><?php _e('Connect', 'clr'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="clr-integration-box">
                    <h3><?php _e('Mailchimp', 'clr'); ?></h3>
                    <p><?php _e('Connect to Mailchimp to track email campaign conversions and user engagement.', 'clr'); ?></p>
                    
                    <div class="clr-integration-status">
                        <?php $mailchimp_connected = get_option('clr_mailchimp_connected', false); ?>
                        <span class="clr-status <?php echo $mailchimp_connected ? 'active' : 'inactive'; ?>">
                            <?php echo $mailchimp_connected ? __('Connected', 'clr') : __('Not Connected', 'clr'); ?>
                        </span>
                    </div>
                    
                    <div class="clr-integration-actions">
                        <?php if ($mailchimp_connected): ?>
                            <button type="button" class="button" id="clr-disconnect-mailchimp"><?php _e('Disconnect', 'clr'); ?></button>
                        <?php else: ?>
                            <button type="button" class="button button-primary" id="clr-connect-mailchimp"><?php _e('Connect', 'clr'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="clr-integration-box">
                    <h3><?php _e('Slack Notifications', 'clr'); ?></h3>
                    <p><?php _e('Get real-time notifications in Slack when important events occur.', 'clr'); ?></p>
                    
                    <div class="clr-integration-status">
                        <?php $slack_connected = get_option('clr_slack_connected', false); ?>
                        <span class="clr-status <?php echo $slack_connected ? 'active' : 'inactive'; ?>">
                            <?php echo $slack_connected ? __('Connected', 'clr') : __('Not Connected', 'clr'); ?>
                        </span>
                    </div>
                    
                    <div class="clr-integration-actions">
                        <?php if ($slack_connected): ?>
                            <button type="button" class="button" id="clr-disconnect-slack"><?php _e('Disconnect', 'clr'); ?></button>
                        <?php else: ?>
                            <button type="button" class="button button-primary" id="clr-connect-slack"><?php _e('Connect', 'clr'); ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Journey Modal -->
    <div id="clr-user-journey-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content clr-modal-wide">
            <h2 id="clr-user-journey-title"><?php _e('User Journey', 'clr'); ?></h2>
            
            <div id="clr-user-journey-content">
                <!-- User journey will be loaded here -->
            </div>
            
            <div class="clr-modal-actions">
                <button type="button" class="button" id="clr-close-user-journey">
                    <?php _e('Close', 'clr'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Custom Report Modal -->
    <div id="clr-custom-report-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-custom-report-title"><?php _e('Create Custom Report', 'clr'); ?></h2>
            
            <form id="clr-custom-report-form">
                <div class="clr-form-field">
                    <label for="clr-report-name"><?php _e('Report Name:', 'clr'); ?></label>
                    <input type="text" id="clr-report-name" class="regular-text" required>
                </div>
                
                <div class="clr-form-field">
                    <label for="clr-report-description"><?php _e('Description:', 'clr'); ?></label>
                    <textarea id="clr-report-description" class="large-text" rows="3"></textarea>
                </div>
                
                <div class="clr-form-field">
                    <label for="clr-report-type"><?php _e('Report Type:', 'clr'); ?></label>
                    <select id="clr-report-type" required>
                        <option value=""><?php _e('Select a type', 'clr'); ?></option>
                        <option value="user_activity"><?php _e('User Activity', 'clr'); ?></option>
                        <option value="event_frequency"><?php _e('Event Frequency', 'clr'); ?></option>
                        <option value="time_series"><?php _e('Time Series', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="clr-form-field">
                    <label for="clr-report-days"><?php _e('Time Period:', 'clr'); ?></label>
                    <select id="clr-report-days" required>
                        <option value="7"><?php _e('Last 7 days', 'clr'); ?></option>
                        <option value="30" selected><?php _e('Last 30 days', 'clr'); ?></option>
                        <option value="90"><?php _e('Last 90 days', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="clr-form-field" id="clr-report-limit-field">
                    <label for="clr-report-limit"><?php _e('Limit Results:', 'clr'); ?></label>
                    <input type="number" id="clr-report-limit" min="1" max="100" value="10">
                </div>
                
                <div class="clr-form-actions">
                    <button type="submit" class="button button-primary"><?php _e('Save Report', 'clr'); ?></button>
                    <button type="button" class="button" id="clr-cancel-report"><?php _e('Cancel', 'clr'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Integration Modal -->
    <div id="clr-integration-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-integration-title"><?php _e('Connect Integration', 'clr'); ?></h2>
            
            <div id="clr-integration-content">
                <!-- Integration form will be loaded here -->
            </div>
            
            <div class="clr-modal-actions">
                <button type="button" class="button" id="clr-close-integration">
                    <?php _e('Close', 'clr'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>