<div class="wrap clr-wrap">
    <h1><?php _e('Referral-Based Redirects', 'clr'); ?></h1>
    
    <div class="clr-referral-based-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Referral-Based Rules', 'clr'); ?></h2>
            <p><?php _e('Create rules to redirect users based on their referral source, medium, or campaign.', 'clr'); ?></p>
            
            <div class="clr-referral-rules-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('Source', 'clr'); ?></th>
                            <th><?php _e('Medium', 'clr'); ?></th>
                            <th><?php _e('Campaign', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Priority', 'clr'); ?></th>
                            <th><?php _e('Actions', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-referral-rules-list">
                        <!-- Rules will be dynamically loaded here -->
                    </tbody>
                </table>
                
                <button type="button" class="button button-primary" id="clr-add-referral-rule">
                    <?php _e('Add New Rule', 'clr'); ?>
                </button>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Current Referral Detection', 'clr'); ?></h2>
            <div class="clr-current-referral">
                <p>
                    <strong><?php _e('Referral Source:', 'clr'); ?></strong> 
                    <span id="clr-current-referral-source"><?php _e('Direct', 'clr'); ?></span>
                </p>
                <p>
                    <strong><?php _e('Referral Medium:', 'clr'); ?></strong> 
                    <span id="clr-current-referral-medium"><?php _e('None', 'clr'); ?></span>
                </p>
                <p>
                    <strong><?php _e('Referral Campaign:', 'clr'); ?></strong> 
                    <span id="clr-current-referral-campaign"><?php _e('None', 'clr'); ?></span>
                </p>
                <p>
                    <strong><?php _e('Referral Domain:', 'clr'); ?></strong> 
                    <span id="clr-current-referral-domain"><?php _e('None', 'clr'); ?></span>
                </p>
                <p>
                    <strong><?php _e('Active Rule:', 'clr'); ?></strong> 
                    <span id="clr-active-referral-rule"><?php _e('None', 'clr'); ?></span>
                </p>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Common Referral Sources', 'clr'); ?></h2>
            <div class="clr-common-sources">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Source', 'clr'); ?></th>
                            <th><?php _e('Description', 'clr'); ?></th>
                            <th><?php _e('Example URL', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>google</td>
                            <td><?php _e('Google Search', 'clr'); ?></td>
                            <td>https://www.google.com/search?q=wordpress</td>
                        </tr>
                        <tr>
                            <td>facebook</td>
                            <td><?php _e('Facebook', 'clr'); ?></td>
                            <td>https://www.facebook.com/</td>
                        </tr>
                        <tr>
                            <td>twitter</td>
                            <td><?php _e('Twitter', 'clr'); ?></td>
                            <td>https://twitter.com/</td>
                        </tr>
                        <tr>
                            <td>linkedin</td>
                            <td><?php _e('LinkedIn', 'clr'); ?></td>
                            <td>https://www.linkedin.com/</td>
                        </tr>
                        <tr>
                            <td>direct</td>
                            <td><?php _e('Direct Traffic', 'clr'); ?></td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Referral Rule Modal -->
    <div id="clr-referral-rule-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-referral-modal-title"><?php _e('Add New Referral Rule', 'clr'); ?></h2>
            
            <form id="clr-referral-rule-form">
                <input type="hidden" id="clr-referral-rule-id" name="rule_id" value="">
                
                <div class="form-row">
                    <label for="clr-referral-rule-source"><?php _e('Source', 'clr'); ?></label>
                    <input type="text" id="clr-referral-rule-source" name="source" required 
                           placeholder="<?php esc_attr_e('e.g., google, facebook, twitter', 'clr'); ?>">
                    <p class="description"><?php _e('The referral source (e.g., search engine, social media platform).', 'clr'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="clr-referral-rule-medium"><?php _e('Medium', 'clr'); ?></label>
                    <select id="clr-referral-rule-medium" name="medium" required>
                        <option value="any"><?php _e('Any Medium', 'clr'); ?></option>
                        <option value="organic"><?php _e('Organic Search', 'clr'); ?></option>
                        <option value="social"><?php _e('Social Media', 'clr'); ?></option>
                        <option value="email"><?php _e('Email', 'clr'); ?></option>
                        <option value="cpc"><?php _e('Paid Search', 'clr'); ?></option>
                        <option value="referral"><?php _e('Referral', 'clr'); ?></option>
                        <option value="none"><?php _e('None', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-referral-rule-campaign"><?php _e('Campaign', 'clr'); ?></label>
                    <input type="text" id="clr-referral-rule-campaign" name="campaign" required 
                           placeholder="<?php esc_attr_e('e.g., summer_sale, product_launch', 'clr'); ?>">
                    <p class="description"><?php _e('The marketing campaign or promotion name.', 'clr'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="clr-referral-rule-url"><?php _e('Redirect URL', 'clr'); ?></label>
                    <input type="url" id="clr-referral-rule-url" name="redirect_url" required 
                           placeholder="<?php esc_attr_e('https://example.com/referral-specific-page', 'clr'); ?>">
                </div>
                
                <div class="form-row">
                    <label for="clr-referral-rule-priority"><?php _e('Priority', 'clr'); ?></label>
                    <input type="number" id="clr-referral-rule-priority" name="priority" min="1" max="100" value="10">
                    <p class="description"><?php _e('Higher numbers mean higher priority. Rules with higher priority will be checked first.', 'clr'); ?></p>
                </div>
                
                <div class="clr-modal-actions">
                    <button type="button" class="button" id="clr-cancel-referral-rule">
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