<div class="wrap clr-wrap">
    <h1><?php _e('Device-Based Redirects', 'clr'); ?></h1>
    
    <div class="clr-device-based-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Device-Based Rules', 'clr'); ?></h2>
            <p><?php _e('Create rules to redirect users based on their device type, operating system, or browser.', 'clr'); ?></p>
            
            <div class="clr-device-rules-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('Device Type', 'clr'); ?></th>
                            <th><?php _e('Operating System', 'clr'); ?></th>
                            <th><?php _e('Browser', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Priority', 'clr'); ?></th>
                            <th><?php _e('Actions', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-device-rules-list">
                        <!-- Rules will be dynamically loaded here -->
                    </tbody>
                </table>
                
                <button type="button" class="button button-primary" id="clr-add-device-rule">
                    <?php _e('Add New Rule', 'clr'); ?>
                </button>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Current Device Detection', 'clr'); ?></h2>
            <div class="clr-current-device">
                <p>
                    <strong><?php _e('Your Device Type:', 'clr'); ?></strong> 
                    <span id="clr-current-device-type"></span>
                </p>
                <p>
                    <strong><?php _e('Your Operating System:', 'clr'); ?></strong> 
                    <span id="clr-current-device-os"></span>
                </p>
                <p>
                    <strong><?php _e('Your Browser:', 'clr'); ?></strong> 
                    <span id="clr-current-browser"></span>
                </p>
                <p>
                    <strong><?php _e('Active Rule:', 'clr'); ?></strong> 
                    <span id="clr-active-device-rule"><?php _e('None', 'clr'); ?></span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Device Rule Modal -->
    <div id="clr-device-rule-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-device-modal-title"><?php _e('Add New Device Rule', 'clr'); ?></h2>
            
            <form id="clr-device-rule-form">
                <input type="hidden" id="clr-device-rule-id" name="rule_id" value="">
                
                <div class="form-row">
                    <label for="clr-device-rule-type"><?php _e('Device Type', 'clr'); ?></label>
                    <select id="clr-device-rule-type" name="device_type" required>
                        <option value="any"><?php _e('Any Device', 'clr'); ?></option>
                        <option value="desktop"><?php _e('Desktop', 'clr'); ?></option>
                        <option value="mobile"><?php _e('Mobile', 'clr'); ?></option>
                        <option value="tablet"><?php _e('Tablet', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-device-rule-os"><?php _e('Operating System', 'clr'); ?></label>
                    <select id="clr-device-rule-os" name="device_os" required>
                        <option value="any"><?php _e('Any OS', 'clr'); ?></option>
                        <option value="windows"><?php _e('Windows', 'clr'); ?></option>
                        <option value="mac"><?php _e('Mac', 'clr'); ?></option>
                        <option value="linux"><?php _e('Linux', 'clr'); ?></option>
                        <option value="android"><?php _e('Android', 'clr'); ?></option>
                        <option value="ios"><?php _e('iOS', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-device-rule-browser"><?php _e('Browser', 'clr'); ?></label>
                    <select id="clr-device-rule-browser" name="browser" required>
                        <option value="any"><?php _e('Any Browser', 'clr'); ?></option>
                        <option value="chrome"><?php _e('Chrome', 'clr'); ?></option>
                        <option value="firefox"><?php _e('Firefox', 'clr'); ?></option>
                        <option value="safari"><?php _e('Safari', 'clr'); ?></option>
                        <option value="edge"><?php _e('Edge', 'clr'); ?></option>
                        <option value="opera"><?php _e('Opera', 'clr'); ?></option>
                        <option value="ie"><?php _e('Internet Explorer', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-device-rule-url"><?php _e('Redirect URL', 'clr'); ?></label>
                    <input type="url" id="clr-device-rule-url" name="redirect_url" required 
                           placeholder="<?php esc_attr_e('https://example.com/device-specific-page', 'clr'); ?>">
                </div>
                
                <div class="form-row">
                    <label for="clr-device-rule-priority"><?php _e('Priority', 'clr'); ?></label>
                    <input type="number" id="clr-device-rule-priority" name="priority" min="1" max="100" value="10">
                    <p class="description"><?php _e('Higher numbers mean higher priority. Rules with higher priority will be checked first.', 'clr'); ?></p>
                </div>
                
                <div class="clr-modal-actions">
                    <button type="button" class="button" id="clr-cancel-device-rule">
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