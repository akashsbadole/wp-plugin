<div class="wrap clr-wrap">
    <h1><?php _e('Conditional Redirects', 'clr'); ?></h1>
    
    <div class="clr-conditional-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Conditional Rules', 'clr'); ?></h2>
            <p><?php _e('Create complex redirect rules using the visual rule builder. Rules are evaluated in order of priority.', 'clr'); ?></p>
            
            <div class="clr-conditional-rules-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('Name', 'clr'); ?></th>
                            <th><?php _e('Description', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Priority', 'clr'); ?></th>
                            <th><?php _e('Status', 'clr'); ?></th>
                            <th><?php _e('Actions', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-conditional-rules-list">
                        <!-- Rules will be dynamically loaded here -->
                    </tbody>
                </table>
                
                <button type="button" class="button button-primary" id="clr-add-conditional-rule">
                    <?php _e('Add New Rule', 'clr'); ?>
                </button>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Rule Testing', 'clr'); ?></h2>
            <p><?php _e('Test your conditional rules by simulating different user scenarios.', 'clr'); ?></p>
            
            <div class="clr-rule-tester">
                <div class="clr-tester-section">
                    <h3><?php _e('Test Scenario', 'clr'); ?></h3>
                    
                    <div class="form-row">
                        <label for="clr-test-user-role"><?php _e('User Role', 'clr'); ?></label>
                        <select id="clr-test-user-role">
                            <option value="subscriber"><?php _e('Subscriber', 'clr'); ?></option>
                            <option value="customer"><?php _e('Customer', 'clr'); ?></option>
                            <option value="contributor"><?php _e('Contributor', 'clr'); ?></option>
                            <option value="author"><?php _e('Author', 'clr'); ?></option>
                            <option value="editor"><?php _e('Editor', 'clr'); ?></option>
                            <option value="administrator"><?php _e('Administrator', 'clr'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="clr-test-device-type"><?php _e('Device Type', 'clr'); ?></label>
                        <select id="clr-test-device-type">
                            <option value="desktop"><?php _e('Desktop', 'clr'); ?></option>
                            <option value="mobile"><?php _e('Mobile', 'clr'); ?></option>
                            <option value="tablet"><?php _e('Tablet', 'clr'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="clr-test-time-hour"><?php _e('Time (Hour)', 'clr'); ?></label>
                        <select id="clr-test-time-hour">
                            <?php for ($i = 0; $i < 24; $i++) { ?>
                                <option value="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="clr-test-referral-source"><?php _e('Referral Source', 'clr'); ?></label>
                        <select id="clr-test-referral-source">
                            <option value="direct"><?php _e('Direct', 'clr'); ?></option>
                            <option value="google"><?php _e('Google', 'clr'); ?></option>
                            <option value="facebook"><?php _e('Facebook', 'clr'); ?></option>
                            <option value="twitter"><?php _e('Twitter', 'clr'); ?></option>
                            <option value="linkedin"><?php _e('LinkedIn', 'clr'); ?></option>
                        </select>
                    </div>
                    
                    <button type="button" class="button" id="clr-test-rule">
                        <?php _e('Test Rules', 'clr'); ?>
                    </button>
                </div>
                
                <div class="clr-tester-section">
                    <h3><?php _e('Test Results', 'clr'); ?></h3>
                    <div id="clr-test-results">
                        <p><?php _e('Run a test to see which rule would be applied.', 'clr'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Conditional Rule Modal -->
    <div id="clr-conditional-rule-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content clr-modal-wide">
            <h2 id="clr-conditional-modal-title"><?php _e('Add New Conditional Rule', 'clr'); ?></h2>
            
            <form id="clr-conditional-rule-form">
                <input type="hidden" id="clr-conditional-rule-id" name="rule_id" value="">
                
                <div class="form-row">
                    <label for="clr-conditional-rule-name"><?php _e('Rule Name', 'clr'); ?></label>
                    <input type="text" id="clr-conditional-rule-name" name="name" required>
                </div>
                
                <div class="form-row">
                    <label for="clr-conditional-rule-description"><?php _e('Description', 'clr'); ?></label>
                    <textarea id="clr-conditional-rule-description" name="description" rows="2"></textarea>
                </div>
                
                <div class="form-row">
                    <label for="clr-conditional-rule-url"><?php _e('Redirect URL', 'clr'); ?></label>
                    <input type="url" id="clr-conditional-rule-url" name="redirect_url" required>
                </div>
                
                <div class="form-row">
                    <label for="clr-conditional-rule-priority"><?php _e('Priority', 'clr'); ?></label>
                    <input type="number" id="clr-conditional-rule-priority" name="priority" min="1" max="100" value="10">
                    <p class="description"><?php _e('Higher numbers mean higher priority. Rules with higher priority will be checked first.', 'clr'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="clr-conditional-rule-status"><?php _e('Status', 'clr'); ?></label>
                    <select id="clr-conditional-rule-status" name="status">
                        <option value="active"><?php _e('Active', 'clr'); ?></option>
                        <option value="inactive"><?php _e('Inactive', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="clr-conditions-builder">
                    <h3><?php _e('Conditions', 'clr'); ?></h3>
                    
                    <div class="clr-conditions-operator">
                        <label><?php _e('Match conditions with:', 'clr'); ?></label>
                        <select id="clr-conditions-operator" name="operator">
                            <option value="AND"><?php _e('AND (All conditions must match)', 'clr'); ?></option>
                            <option value="OR"><?php _e('OR (Any condition can match)', 'clr'); ?></option>
                        </select>
                    </div>
                    
                    <div id="clr-conditions-groups">
                        <!-- Condition groups will be dynamically added here -->
                    </div>
                    
                    <div class="clr-conditions-actions">
                        <button type="button" class="button" id="clr-add-condition-group">
                            <?php _e('Add Condition Group', 'clr'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="clr-modal-actions">
                    <button type="button" class="button" id="clr-cancel-conditional-rule">
                        <?php _e('Cancel', 'clr'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Save Rule', 'clr'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Condition Group Template -->
    <script type="text/html" id="clr-condition-group-template">
        <div class="clr-condition-group">
            <div class="clr-condition-group-header">
                <h4><?php _e('Condition Group', 'clr'); ?></h4>
                <div class="clr-condition-group-operator">
                    <select class="clr-group-operator" name="group_operator[]">
                        <option value="AND"><?php _e('AND', 'clr'); ?></option>
                        <option value="OR"><?php _e('OR', 'clr'); ?></option>
                    </select>
                </div>
                <button type="button" class="button button-link clr-remove-condition-group">
                    <?php _e('Remove Group', 'clr'); ?>
                </button>
            </div>
            
            <div class="clr-condition-group-conditions">
                <!-- Conditions will be dynamically added here -->
            </div>
            
            <div class="clr-condition-group-actions">
                <button type="button" class="button button-small clr-add-condition">
                    <?php _e('Add Condition', 'clr'); ?>
                </button>
            </div>
        </div>
    </script>
    
    <!-- Condition Template -->
    <script type="text/html" id="clr-condition-template">
        <div class="clr-condition">
            <select class="clr-condition-field">
                <option value=""><?php _e('Select field...', 'clr'); ?></option>
                <optgroup label="<?php _e('User Fields', 'clr'); ?>">
                    <option value="user_role"><?php _e('User Role', 'clr'); ?></option>
                    <option value="user_id"><?php _e('User ID', 'clr'); ?></option>
                    <option value="user_registered"><?php _e('Days Since Registration', 'clr'); ?></option>
                    <option value="user_post_count"><?php _e('User Post Count', 'clr'); ?></option>
                </optgroup>
                <optgroup label="<?php _e('Device Fields', 'clr'); ?>">
                    <option value="device_type"><?php _e('Device Type', 'clr'); ?></option>
                    <option value="device_os"><?php _e('Operating System', 'clr'); ?></option>
                    <option value="browser"><?php _e('Browser', 'clr'); ?></option>
                </optgroup>
                <optgroup label="<?php _e('Referral Fields', 'clr'); ?>">
                    <option value="referral_source"><?php _e('Referral Source', 'clr'); ?></option>
                    <option value="referral_medium"><?php _e('Referral Medium', 'clr'); ?></option>
                    <option value="referral_campaign"><?php _e('Referral Campaign', 'clr'); ?></option>
                </optgroup>
                <optgroup label="<?php _e('Time Fields', 'clr'); ?>">
                    <option value="time_hour"><?php _e('Hour of Day', 'clr'); ?></option>
                    <option value="time_day"><?php _e('Day of Week', 'clr'); ?></option>
                    <option value="time_month"><?php _e('Month', 'clr'); ?></option>
                </optgroup>
                <?php if (get_option('clr_geo_enabled')): ?>
                <optgroup label="<?php _e('Geolocation Fields', 'clr'); ?>">
                    <option value="geo_country"><?php _e('Country', 'clr'); ?></option>
                    <option value="geo_region"><?php _e('Region', 'clr'); ?></option>
                    <option value="geo_city"><?php _e('City', 'clr'); ?></option>
                </optgroup>
                <?php endif; ?>
                <optgroup label="<?php _e('Custom Fields', 'clr'); ?>">
                    <option value="custom_field"><?php _e('Custom User Field', 'clr'); ?></option>
                </optgroup>
            </select>
            
            <select class="clr-condition-operator">
                <option value="equals"><?php _e('equals', 'clr'); ?></option>
                <option value="not_equals"><?php _e('does not equal', 'clr'); ?></option>
                <option value="contains"><?php _e('contains', 'clr'); ?></option>
                <option value="not_contains"><?php _e('does not contain', 'clr'); ?></option>
                <option value="starts_with"><?php _e('starts with', 'clr'); ?></option>
                <option value="ends_with"><?php _e('ends with', 'clr'); ?></option>
                <option value="greater_than"><?php _e('greater than', 'clr'); ?></option>
                <option value="less_than"><?php _e('less than', 'clr'); ?></option>
                <option value="greater_than_or_equal"><?php _e('greater than or equal', 'clr'); ?></option>
                <option value="less_than_or_equal"><?php _e('less than or equal', 'clr'); ?></option>
                <option value="in"><?php _e('in', 'clr'); ?></option>
                <option value="not_in"><?php _e('not in', 'clr'); ?></option>
                <option value="matches_regex"><?php _e('matches regex', 'clr'); ?></option>
            </select>
            
            <input type="text" class="clr-condition-value" placeholder="<?php esc_attr_e('Value', 'clr'); ?>">
            
            <div class="clr-condition-custom-field" style="display:none;">
                <input type="text" class="clr-condition-custom-field-name" placeholder="<?php esc_attr_e('Field Name', 'clr'); ?>">
            </div>
            
            <button type="button" class="button button-link clr-remove-condition">
                <?php _e('Remove', 'clr'); ?>
            </button>
        </div>
    </script>
</div>