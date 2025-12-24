<div class="wrap clr-wrap">
    <h1><?php _e('Time-Based Redirects', 'clr'); ?></h1>
    
    <div class="clr-time-based-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Time-Based Rules', 'clr'); ?></h2>
            <p><?php _e('Create rules to redirect users based on the time of day or day of the week.', 'clr'); ?></p>
            
            <div class="clr-time-rules-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('Day of Week', 'clr'); ?></th>
                            <th><?php _e('Time Range', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Priority', 'clr'); ?></th>
                            <th><?php _e('Actions', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-time-rules-list">
                        <!-- Rules will be dynamically loaded here -->
                    </tbody>
                </table>
                
                <button type="button" class="button button-primary" id="clr-add-time-rule">
                    <?php _e('Add New Rule', 'clr'); ?>
                </button>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Current Time Status', 'clr'); ?></h2>
            <div class="clr-current-time">
                <p>
                    <strong><?php _e('Current Time:', 'clr'); ?></strong> 
                    <span id="clr-current-time-display"></span>
                </p>
                <p>
                    <strong><?php _e('Current Day:', 'clr'); ?></strong> 
                    <span id="clr-current-day-display"></span>
                </p>
                <p>
                    <strong><?php _e('Active Rule:', 'clr'); ?></strong> 
                    <span id="clr-active-rule-display"><?php _e('None', 'clr'); ?></span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Time Rule Modal -->
    <div id="clr-time-rule-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-time-modal-title"><?php _e('Add New Time Rule', 'clr'); ?></h2>
            
            <form id="clr-time-rule-form">
                <input type="hidden" id="clr-time-rule-id" name="rule_id" value="">
                
                <div class="form-row">
                    <label for="clr-time-rule-day"><?php _e('Day of Week', 'clr'); ?></label>
                    <select id="clr-time-rule-day" name="day_of_week" required>
                        <option value="any"><?php _e('Any Day', 'clr'); ?></option>
                        <option value="monday"><?php _e('Monday', 'clr'); ?></option>
                        <option value="tuesday"><?php _e('Tuesday', 'clr'); ?></option>
                        <option value="wednesday"><?php _e('Wednesday', 'clr'); ?></option>
                        <option value="thursday"><?php _e('Thursday', 'clr'); ?></option>
                        <option value="friday"><?php _e('Friday', 'clr'); ?></option>
                        <option value="saturday"><?php _e('Saturday', 'clr'); ?></option>
                        <option value="sunday"><?php _e('Sunday', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-time-rule-start"><?php _e('Start Hour (0-23)', 'clr'); ?></label>
                    <input type="number" id="clr-time-rule-start" name="start_hour" min="0" max="23" required>
                </div>
                
                <div class="form-row">
                    <label for="clr-time-rule-end"><?php _e('End Hour (0-23)', 'clr'); ?></label>
                    <input type="number" id="clr-time-rule-end" name="end_hour" min="0" max="23" required>
                </div>
                
                <div class="form-row">
                    <label for="clr-time-rule-url"><?php _e('Redirect URL', 'clr'); ?></label>
                    <input type="url" id="clr-time-rule-url" name="redirect_url" required 
                           placeholder="<?php esc_attr_e('https://example.com/time-specific-page', 'clr'); ?>">
                </div>
                
                <div class="form-row">
                    <label for="clr-time-rule-priority"><?php _e('Priority', 'clr'); ?></label>
                    <input type="number" id="clr-time-rule-priority" name="priority" min="1" max="100" value="10">
                    <p class="description"><?php _e('Higher numbers mean higher priority. Rules with higher priority will be checked first.', 'clr'); ?></p>
                </div>
                
                <div class="clr-modal-actions">
                    <button type="button" class="button" id="clr-cancel-time-rule">
                        <?php _e('Cancel', 'clr'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Save Rule', 'clr'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>