<div class="wrap clr-wrap">
    <h1><?php _e('User Behavior Analytics', 'clr'); ?></h1>
    
    <div class="clr-analytics-container">
        <div class="clr-section">
            <h2 class="title"><?php _e('Analytics Dashboard', 'clr'); ?></h2>
            
            <div class="clr-date-filter">
                <label for="clr-analytics-period"><?php _e('Time Period:', 'clr'); ?></label>
                <select id="clr-analytics-period">
                    <option value="7"><?php _e('Last 7 days', 'clr'); ?></option>
                    <option value="30" selected><?php _e('Last 30 days', 'clr'); ?></option>
                    <option value="90"><?php _e('Last 90 days', 'clr'); ?></option>
                    <option value="365"><?php _e('Last year', 'clr'); ?></option>
                </select>
                <button type="button" class="button" id="clr-refresh-analytics"><?php _e('Refresh', 'clr'); ?></button>
            </div>
            
            <div class="clr-analytics-overview">
                <div class="clr-metric-box">
                    <h3><?php _e('Total Sessions', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-total-sessions">-</div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('Total Page Views', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-total-page-views">-</div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('Total Redirects', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-total-redirects">-</div>
                </div>
                
                <div class="clr-metric-box">
                    <h3><?php _e('Avg. Session Duration', 'clr'); ?></h3>
                    <div class="clr-metric-value" id="clr-avg-session-duration">-</div>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('Redirect Analytics', 'clr'); ?></h2>
            
            <div class="clr-charts-container">
                <div class="clr-chart-box">
                    <h3><?php _e('Redirects by Type', 'clr'); ?></h3>
                    <div id="clr-redirects-type-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
                
                <div class="clr-chart-box">
                    <h3><?php _e('Redirects by User Role', 'clr'); ?></h3>
                    <div id="clr-redirects-role-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="clr-chart-box">
                <h3><?php _e('Top Redirect URLs', 'clr'); ?></h3>
                <div id="clr-top-redirects-chart" class="clr-chart">
                    <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                </div>
            </div>
            
            <div class="clr-chart-box">
                <h3><?php _e('Conversion Rates by Redirect', 'clr'); ?></h3>
                <div id="clr-conversion-rates-chart" class="clr-chart">
                    <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                </div>
            </div>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('User Behavior Analytics', 'clr'); ?></h2>
            
            <div class="clr-charts-container">
                <div class="clr-chart-box">
                    <h3><?php _e('Device Types', 'clr'); ?></h3>
                    <div id="clr-device-types-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
                
                <div class="clr-chart-box">
                    <h3><?php _e('Referral Sources', 'clr'); ?></h3>
                    <div id="clr-referral-sources-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="clr-charts-container">
                <div class="clr-chart-box">
                    <h3><?php _e('Login Times by Hour', 'clr'); ?></h3>
                    <div id="clr-login-times-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
                
                <div class="clr-chart-box">
                    <h3><?php _e('Top Visited Pages', 'clr'); ?></h3>
                    <div id="clr-top-pages-chart" class="clr-chart">
                        <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (get_option('clr_geo_enabled')): ?>
            <div class="clr-chart-box">
                <h3><?php _e('Geographic Distribution', 'clr'); ?></h3>
                <div id="clr-geo-chart" class="clr-chart">
                    <div class="clr-chart-placeholder"><?php _e('Loading chart...', 'clr'); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="clr-section">
            <h2 class="title"><?php _e('User Analytics', 'clr'); ?></h2>
            
            <div class="clr-user-analytics-filter">
                <label for="clr-user-search"><?php _e('Search User:', 'clr'); ?></label>
                <input type="text" id="clr-user-search" class="regular-text" placeholder="<?php esc_attr_e('Enter username or email', 'clr'); ?>">
                <button type="button" class="button" id="clr-search-user"><?php _e('Search', 'clr'); ?></button>
            </div>
            
            <div id="clr-user-analytics-results">
                <p class="description"><?php _e('Search for a user to view their individual analytics.', 'clr'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- User Analytics Modal -->
    <div id="clr-user-analytics-modal" style="display:none;">
        <div class="clr-modal-backdrop"></div>
        <div class="clr-modal-content clr-modal-wide">
            <h2 id="clr-user-modal-title"><?php _e('User Analytics', 'clr'); ?></h2>
            
            <div id="clr-user-analytics-content">
                <!-- User analytics will be loaded here -->
            </div>
            
            <div class="clr-modal-actions">
                <button type="button" class="button" id="clr-close-user-analytics">
                    <?php _e('Close', 'clr'); ?>
                </button>
            </div>
        </div>
    </div>
</div>