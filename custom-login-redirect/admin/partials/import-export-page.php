<div class="wrap clr-wrap">
    <h1><?php _e('Bulk Import/Export', 'clr'); ?></h1>
    
    <div class="clr-import-export-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Export Redirect Rules', 'clr'); ?></h2>
            <p><?php _e('Export all your redirect rules to a file for backup or migration purposes.', 'clr'); ?></p>
            
            <div class="clr-export-section">
                <h3><?php _e('Select Export Format', 'clr'); ?></h3>
                
                <div class="form-row">
                    <label>
                        <input type="radio" name="clr_export_format" value="json" checked>
                        <?php _e('JSON (Recommended)', 'clr'); ?>
                        <span class="description"><?php _e('Human-readable format that preserves all data.', 'clr'); ?></span>
                    </label>
                </div>
                
                <div class="form-row">
                    <label>
                        <input type="radio" name="clr_export_format" value="csv">
                        <?php _e('CSV', 'clr'); ?>
                        <span class="description"><?php _e('Spreadsheet-compatible format for easy editing.', 'clr'); ?></span>
                    </label>
                </div>
                
                <div class="form-row">
                    <button type="button" class="button button-primary" id="clr-export-rules">
                        <?php _e('Export Rules', 'clr'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Import Redirect Rules', 'clr'); ?></h2>
            <p><?php _e('Import redirect rules from a previously exported file.', 'clr'); ?></p>
            
            <div class="clr-import-section">
                <h3><?php _e('Select Import File', 'clr'); ?></h3>
                
                <div class="form-row">
                    <input type="file" id="clr-import-file" accept=".json,.csv">
                </div>
                
                <div class="form-row">
                    <label>
                        <input type="checkbox" id="clr-import-overwrite" value="1">
                        <?php _e('Overwrite existing rules', 'clr'); ?>
                        <span class="description"><?php _e('Warning: This will replace all existing rules of the same type.', 'clr'); ?></span>
                    </label>
                </div>
                
                <div class="form-row">
                    <button type="button" class="button button-primary" id="clr-import-rules" disabled>
                        <?php _e('Import Rules', 'clr'); ?>
                    </button>
                </div>
            </div>
            
            <div id="clr-import-results" style="display:none;">
                <h3><?php _e('Import Results', 'clr'); ?></h3>
                <div class="clr-import-results-content">
                    <!-- Results will be displayed here -->
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Import/Export Templates', 'clr'); ?></h2>
            <p><?php _e('Download templates to help you create import files in the correct format.', 'clr'); ?></p>
            
            <div class="clr-templates-section">
                <h3><?php _e('Available Templates', 'clr'); ?></h3>
                
                <div class="clr-template-options">
                    <div class="clr-template-option">
                        <h4><?php _e('Basic Redirect Rules Template', 'clr'); ?></h4>
                        <p><?php _e('CSV template for basic role-based redirects.', 'clr'); ?></p>
                        <button type="button" class="button" data-template="basic"><?php _e('Download Template', 'clr'); ?></button>
                    </div>
                    
                    <div class="clr-template-option">
                        <h4><?php _e('Time-Based Redirect Rules Template', 'clr'); ?></h4>
                        <p><?php _e('CSV template for time-based redirects.', 'clr'); ?></p>
                        <button type="button" class="button" data-template="time_based"><?php _e('Download Template', 'clr'); ?></button>
                    </div>
                    
                    <div class="clr-template-option">
                        <h4><?php _e('Device-Based Redirect Rules Template', 'clr'); ?></h4>
                        <p><?php _e('CSV template for device-based redirects.', 'clr'); ?></p>
                        <button type="button" class="button" data-template="device_based"><?php _e('Download Template', 'clr'); ?></button>
                    </div>
                    
                    <div class="clr-template-option">
                        <h4><?php _e('Referral-Based Redirect Rules Template', 'clr'); ?></h4>
                        <p><?php _e('CSV template for referral-based redirects.', 'clr'); ?></p>
                        <button type="button" class="button" data-template="referral_based"><?php _e('Download Template', 'clr'); ?></button>
                    </div>
                    
                    <div class="clr-template-option">
                        <h4><?php _e('Complete Rules Template', 'clr'); ?></h4>
                        <p><?php _e('CSV template with all rule types.', 'clr'); ?></p>
                        <button type="button" class="button" data-template="complete"><?php _e('Download Template', 'clr'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>