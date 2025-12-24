<div class="wrap clr-wrap">
    <h1><?php _e('A/B Testing for Login Redirects', 'clr'); ?></h1>
    
    <div class="clr-ab-testing-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('A/B Test Variants', 'clr'); ?></h2>
            <p><?php _e('Create and manage different redirect variants to test which ones perform better.', 'clr'); ?></p>
            
            <div class="clr-variants-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('ID', 'clr'); ?></th>
                            <th><?php _e('User Role', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Actions', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-variants-list">
                        <!-- Variants will be dynamically loaded here -->
                    </tbody>
                </table>
                
                <button type="button" class="button button-primary" id="clr-add-variant">
                    <?php _e('Add New Variant', 'clr'); ?>
                </button>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('A/B Test Results', 'clr'); ?></h2>
            <p><?php _e('View the performance of your A/B test variants.', 'clr'); ?></p>
            
            <div class="clr-results-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Variant ID', 'clr'); ?></th>
                            <th><?php _e('User Role', 'clr'); ?></th>
                            <th><?php _e('Redirect URL', 'clr'); ?></th>
                            <th><?php _e('Total Tests', 'clr'); ?></th>
                            <th><?php _e('Conversions', 'clr'); ?></th>
                            <th><?php _e('Conversion Rate', 'clr'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="clr-results-list">
                        <!-- Results will be dynamically loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Variant Modal -->
    <div id="clr-variant-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content">
            <h2 id="clr-modal-title"><?php _e('Add New Variant', 'clr'); ?></h2>
            
            <form id="clr-variant-form">
                <input type="hidden" id="clr-variant-id" name="variant_id" value="">
                
                <div class="form-row">
                    <label for="clr-variant-role"><?php _e('User Role', 'clr'); ?></label>
                    <select id="clr-variant-role" name="role" required>
                        <option value=""><?php _e('Select a role', 'clr'); ?></option>
                        <option value="administrator"><?php _e('Administrator', 'clr'); ?></option>
                        <option value="editor"><?php _e('Editor', 'clr'); ?></option>
                        <option value="author"><?php _e('Author', 'clr'); ?></option>
                        <option value="contributor"><?php _e('Contributor', 'clr'); ?></option>
                        <option value="subscriber"><?php _e('Subscriber', 'clr'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="clr-variant-url"><?php _e('Redirect URL', 'clr'); ?></label>
                    <input type="url" id="clr-variant-url" name="redirect_url" required 
                           placeholder="<?php esc_attr_e('https://example.com/welcome-page', 'clr'); ?>">
                </div>
                
                <div class="clr-modal-actions">
                    <button type="button" class="button" id="clr-cancel-variant">
                        <?php _e('Cancel', 'clr'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Save Variant', 'clr'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>