<div class="wrap clr-wrap">
    <h1><?php _e('Custom Login Redirect Settings', 'clr'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('clr_settings'); ?>
        
        <h2 class="title"><?php _e('General Settings', 'clr'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable AI-Powered Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_ai_enabled" value="1" <?php checked(get_option('clr_ai_enabled'), 1); ?>>
                        <?php _e('Use AI to suggest optimal redirect pages based on user behavior', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Geolocation Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_geo_enabled" value="1" <?php checked(get_option('clr_geo_enabled'), 1); ?>>
                        <?php _e('Redirect users to region-specific pages based on their location', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('IP Geolocation API Token', 'clr'); ?> <span class="clr-premium-badge"><?php _e('PREMIUM', 'clr'); ?></span></th>
                <td>
                    <input type="text" name="clr_geo_api_token" value="<?php echo esc_attr(get_option('clr_geo_api_token', '')); ?>" class="regular-text" disabled>
                    <p class="description"><?php _e('Enter your IPInfo.io API token for geolocation services. Get your token at https://ipinfo.io/', 'clr'); ?></p>
                    <div class="clr-premium-notice">
                        <p><?php _e('This is a premium feature. Upgrade to premium to unlock IP geolocation functionality.', 'clr'); ?></p>
                        <a href="#" class="button button-primary"><?php _e('Upgrade to Premium', 'clr'); ?></a>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable A/B Testing', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_ab_enabled" value="1" <?php checked(get_option('clr_ab_enabled'), 1); ?>>
                        <?php _e('Test different redirect URLs to optimize conversions', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Time-Based Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_time_based_enabled" value="1" <?php checked(get_option('clr_time_based_enabled'), 1); ?>>
                        <?php _e('Redirect users based on time of day or day of week', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Device-Based Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_device_based_enabled" value="1" <?php checked(get_option('clr_device_based_enabled'), 1); ?>>
                        <?php _e('Redirect users based on device type, operating system, or browser', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Referral-Based Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_referral_based_enabled" value="1" <?php checked(get_option('clr_referral_based_enabled'), 1); ?>>
                        <?php _e('Redirect users based on referral source, medium, or campaign', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable User Behavior Analytics', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_analytics_enabled" value="1" <?php checked(get_option('clr_analytics_enabled'), 1); ?>>
                        <?php _e('Track user behavior after login to optimize redirect strategies', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Conditional Redirects', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_conditional_enabled" value="1" <?php checked(get_option('clr_conditional_enabled'), 1); ?>>
                        <?php _e('Create complex redirect rules using the visual rule builder', 'clr'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Enable Page Builder Integration', 'clr'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="clr_page_builder_enabled" value="1" <?php checked(get_option('clr_page_builder_enabled'), 1); ?>>
                        <?php _e('Add drag-and-drop redirect rules for Elementor/Divi', 'clr'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <h2 class="title"><?php _e('Premium Features', 'clr'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('License Key', 'clr'); ?></th>
                <td>
                    <input type="text" name="clr_license_key" value="<?php echo esc_attr(get_option('clr_license_key', '')); ?>" class="regular-text">
                    <p class="description"><?php _e('Enter your premium license key to unlock all premium features.', 'clr'); ?></p>
                    <button type="button" id="clr_activate_license" class="button button-secondary"><?php _e('Activate License', 'clr'); ?></button>
                    <span id="clr_license_status" class="clr-license-status"></span>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('IP Geolocation API Token', 'clr'); ?> <span class="clr-premium-badge"><?php _e('PREMIUM', 'clr'); ?></span></th>
                <td>
                    <?php $is_premium = clr_is_premium(); ?>
                    <input type="text" name="clr_geo_api_token" value="<?php echo esc_attr(get_option('clr_geo_api_token', '')); ?>" class="regular-text" <?php echo $is_premium ? '' : 'disabled'; ?>>
                    <p class="description"><?php _e('Enter your IPInfo.io API token for geolocation services. Get your token at https://ipinfo.io/', 'clr'); ?></p>
                    <?php if (!$is_premium): ?>
                    <div class="clr-premium-notice">
                        <p><?php _e('This is a premium feature. Upgrade to premium to unlock IP geolocation functionality.', 'clr'); ?></p>
                        <a href="#" class="button button-primary"><?php _e('Upgrade to Premium', 'clr'); ?></a>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        <h2 class="title"><?php _e('Custom Code', 'clr'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Header Code', 'clr'); ?></th>
                <td>
                    <textarea name="clr_header_code" class="large-text" rows="5"><?php echo esc_textarea(get_option('clr_header_code', '')); ?></textarea>
                    <p class="description"><?php _e('Add custom code to be inserted in the <head> section of your site. Useful for Google Analytics, Meta Pixel, etc.', 'clr'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Footer Code', 'clr'); ?></th>
                <td>
                    <textarea name="clr_footer_code" class="large-text" rows="5"><?php echo esc_textarea(get_option('clr_footer_code', '')); ?></textarea>
                    <p class="description"><?php _e('Add custom code to be inserted before the closing </body> tag. Useful for tracking scripts, etc.', 'clr'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2 class="title"><?php _e('Redirect Rules', 'clr'); ?></h2>
        <div id="clr-rules-container">
            <!-- Rules will be dynamically loaded here -->
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>