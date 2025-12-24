jQuery(document).ready(function($) {
    // Load redirect rules
    function loadRules() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_rules',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-rules-container').html(response);
            }
        });
    }
    
    // Add new rule
    $(document).on('click', '#clr-add-rule', function() {
        var ruleHtml = `
            <div class="clr-rule-item">
                <div class="clr-rule-header">
                    <h3><?php _e('New Rule', 'clr'); ?></h3>
                    <div class="clr-rule-actions">
                        <button class="button button-secondary clr-save-rule"><?php _e('Save', 'clr'); ?></button>
                        <button class="button button-link clr-delete-rule"><?php _e('Delete', 'clr'); ?></button>
                    </div>
                </div>
                <div class="clr-rule-form">
                    <!-- Form fields here -->
                </div>
            </div>
        `;
        
        $('#clr-rules-container').append(ruleHtml);
    });
    
    // Save rule
    $(document).on('click', '.clr-save-rule', function() {
        var $rule = $(this).closest('.clr-rule-item');
        var formData = $rule.find('form').serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_rule&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    loadRules();
                }
            }
        });
    });
    
    // A/B Testing Functionality
    
    // Load A/B testing variants
    function loadVariants() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_ab_variants',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-variants-list').html(response);
            }
        });
    }
    
    // Load A/B testing results
    function loadResults() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_ab_results',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-results-list').html(response);
            }
        });
    }
    
    // Add new variant
    $(document).on('click', '#clr-add-variant', function() {
        $('#clr-variant-id').val('');
        $('#clr-variant-role').val('');
        $('#clr-variant-url').val('');
        $('#clr-modal-title').text('Add New Variant');
        $('#clr-variant-modal').show();
    });
    
    // Edit variant
    $(document).on('click', '.clr-edit-variant', function() {
        var variantId = $(this).data('id');
        var role = $(this).data('role');
        var url = $(this).data('url');
        
        $('#clr-variant-id').val(variantId);
        $('#clr-variant-role').val(role);
        $('#clr-variant-url').val(url);
        $('#clr-modal-title').text('Edit Variant');
        $('#clr-variant-modal').show();
    });
    
    // Delete variant
    $(document).on('click', '.clr-delete-variant', function() {
        if (confirm('Are you sure you want to delete this variant?')) {
            var variantId = $(this).data('id');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_ab_variant',
                    variant_id: variantId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if(response.success) {
                        loadVariants();
                        loadResults();
                    }
                }
            });
        }
    });
    
    // Save variant
    $('#clr-variant-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_ab_variant&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    $('#clr-variant-modal').hide();
                    loadVariants();
                    loadResults();
                }
            }
        });
    });
    
    // Cancel variant form
    $('#clr-cancel-variant').on('click', function() {
        $('#clr-variant-modal').hide();
    });
    
    // Close modal when clicking outside
    $('.clr-modal-backdrop').on('click', function() {
        $('#clr-variant-modal').hide();
    });
    
    // Initialize
    loadRules();
    
    // Initialize A/B testing functionality if on A/B testing page
    if ($('#clr-variants-list').length) {
        loadVariants();
        loadResults();
    }
    
    // Time-Based Redirects Functionality
    
    // Load time-based rules
    function loadTimeRules() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_time_rules',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-time-rules-list').html(response);
                updateCurrentTimeStatus();
            }
        });
    }
    
    // Update current time status
    function updateCurrentTimeStatus() {
        var now = new Date();
        var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var currentDay = days[now.getDay()];
        var currentHour = now.getHours();
        
        $('#clr-current-time-display').text(now.toLocaleTimeString());
        $('#clr-current-day-display').text(currentDay);
        
        // Find active rule
        var activeRule = 'None';
        $('#clr-time-rules-list tr').each(function() {
            var $row = $(this);
            var ruleDay = $row.find('td:eq(1)').text();
            var timeRange = $row.find('td:eq(2)').text();
            var timeParts = timeRange.split(' - ');
            var startHour = parseInt(timeParts[0].split(':')[0]);
            var endHour = parseInt(timeParts[1].split(':')[0]);
            
            if ((ruleDay === 'Any Day' || ruleDay === currentDay) &&
                (currentHour >= startHour && currentHour < endHour)) {
                activeRule = $row.find('td:eq(3)').text();
                return false; // Break the loop
            }
        });
        
        $('#clr-active-rule-display').text(activeRule);
    }
    
    // Add new time rule
    $(document).on('click', '#clr-add-time-rule', function() {
        $('#clr-time-rule-id').val('');
        $('#clr-time-rule-day').val('any');
        $('#clr-time-rule-start').val('9');
        $('#clr-time-rule-end').val('17');
        $('#clr-time-rule-url').val('');
        $('#clr-time-rule-priority').val('10');
        $('#clr-time-modal-title').text('Add New Time Rule');
        $('#clr-time-rule-modal').show();
    });
    
    // Edit time rule
    $(document).on('click', '.clr-edit-time-rule', function() {
        var ruleId = $(this).data('id');
        var day = $(this).data('day');
        var start = $(this).data('start');
        var end = $(this).data('end');
        var url = $(this).data('url');
        var priority = $(this).data('priority');
        
        $('#clr-time-rule-id').val(ruleId);
        $('#clr-time-rule-day').val(day);
        $('#clr-time-rule-start').val(start);
        $('#clr-time-rule-end').val(end);
        $('#clr-time-rule-url').val(url);
        $('#clr-time-rule-priority').val(priority);
        $('#clr-time-modal-title').text('Edit Time Rule');
        $('#clr-time-rule-modal').show();
    });
    
    // Delete time rule
    $(document).on('click', '.clr-delete-time-rule', function() {
        if (confirm('Are you sure you want to delete this rule?')) {
            var ruleId = $(this).data('id');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_time_rule',
                    rule_id: ruleId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if(response.success) {
                        loadTimeRules();
                    }
                }
            });
        }
    });
    
    // Save time rule
    $('#clr-time-rule-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_time_rule&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    $('#clr-time-rule-modal').hide();
                    loadTimeRules();
                }
            }
        });
    });
    
    // Cancel time rule form
    $('#clr-cancel-time-rule').on('click', function() {
        $('#clr-time-rule-modal').hide();
    });
    
    // Close modal when clicking outside
    $(document).on('click', '.clr-modal-backdrop', function() {
        $('#clr-time-rule-modal').hide();
    });
    
    // Initialize time-based functionality if on time-based page
    if ($('#clr-time-rules-list').length) {
        loadTimeRules();
        
        // Update time status every minute
        setInterval(updateCurrentTimeStatus, 60000);
    }
    
    // Device-Based Redirects Functionality
    
    // Load device-based rules
    function loadDeviceRules() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_device_rules',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-device-rules-list').html(response);
                updateCurrentDeviceStatus();
            }
        });
    }
    
    // Update current device status
    function updateCurrentDeviceStatus() {
        // Simple device detection
        var userAgent = navigator.userAgent;
        var deviceType = 'desktop';
        var deviceOS = 'unknown';
        var browser = 'unknown';
        
        // Detect device type
        if (/tablet|ipad|playbook|silk|(android(?!.*mobi))/i.test(userAgent)) {
            deviceType = 'tablet';
        } else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(userAgent)) {
            deviceType = 'mobile';
        }
        
        // Detect OS
        if (/windows/i.test(userAgent)) {
            deviceOS = 'windows';
        } else if (/macintosh|mac os x/i.test(userAgent)) {
            deviceOS = 'mac';
        } else if (/linux/i.test(userAgent)) {
            deviceOS = 'linux';
        } else if (/android/i.test(userAgent)) {
            deviceOS = 'android';
        } else if (/iphone|ipad|ipod/i.test(userAgent)) {
            deviceOS = 'ios';
        }
        
        // Detect browser
        if (/msie|trident/i.test(userAgent)) {
            browser = 'ie';
        } else if (/firefox/i.test(userAgent)) {
            browser = 'firefox';
        } else if (/chrome/i.test(userAgent)) {
            browser = 'chrome';
        } else if (/safari/i.test(userAgent) && !/chrome/i.test(userAgent)) {
            browser = 'safari';
        } else if (/opera/i.test(userAgent)) {
            browser = 'opera';
        } else if (/edge/i.test(userAgent)) {
            browser = 'edge';
        }
        
        // Display device info
        $('#clr-current-device-type').text(deviceType.charAt(0).toUpperCase() + deviceType.slice(1));
        $('#clr-current-device-os').text(deviceOS.charAt(0).toUpperCase() + deviceOS.slice(1));
        $('#clr-current-browser').text(browser.charAt(0).toUpperCase() + browser.slice(1));
        
        // Find active rule
        var activeRule = 'None';
        $('#clr-device-rules-list tr').each(function() {
            var $row = $(this);
            var ruleDeviceType = $row.find('td:eq(1)').text().toLowerCase();
            var ruleOS = $row.find('td:eq(2)').text().toLowerCase();
            var ruleBrowser = $row.find('td:eq(3)').text().toLowerCase();
            
            if ((ruleDeviceType === 'any device' || ruleDeviceType === deviceType.charAt(0).toUpperCase() + deviceType.slice(1)) &&
                (ruleOS === 'any os' || ruleOS === deviceOS.charAt(0).toUpperCase() + deviceOS.slice(1)) &&
                (ruleBrowser === 'any browser' || ruleBrowser === browser.charAt(0).toUpperCase() + browser.slice(1))) {
                activeRule = $row.find('td:eq(4)').text();
                return false; // Break the loop
            }
        });
        
        $('#clr-active-device-rule').text(activeRule);
    }
    
    // Add new device rule
    $(document).on('click', '#clr-add-device-rule', function() {
        $('#clr-device-rule-id').val('');
        $('#clr-device-rule-type').val('any');
        $('#clr-device-rule-os').val('any');
        $('#clr-device-rule-browser').val('any');
        $('#clr-device-rule-url').val('');
        $('#clr-device-rule-priority').val('10');
        $('#clr-device-modal-title').text('Add New Device Rule');
        $('#clr-device-rule-modal').show();
    });
    
    // Edit device rule
    $(document).on('click', '.clr-edit-device-rule', function() {
        var ruleId = $(this).data('id');
        var type = $(this).data('type');
        var os = $(this).data('os');
        var browser = $(this).data('browser');
        var url = $(this).data('url');
        var priority = $(this).data('priority');
        
        $('#clr-device-rule-id').val(ruleId);
        $('#clr-device-rule-type').val(type);
        $('#clr-device-rule-os').val(os);
        $('#clr-device-rule-browser').val(browser);
        $('#clr-device-rule-url').val(url);
        $('#clr-device-rule-priority').val(priority);
        $('#clr-device-modal-title').text('Edit Device Rule');
        $('#clr-device-rule-modal').show();
    });
    
    // Delete device rule
    $(document).on('click', '.clr-delete-device-rule', function() {
        if (confirm('Are you sure you want to delete this rule?')) {
            var ruleId = $(this).data('id');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_device_rule',
                    rule_id: ruleId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if(response.success) {
                        loadDeviceRules();
                    }
                }
            });
        }
    });
    
    // Save device rule
    $('#clr-device-rule-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_device_rule&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    $('#clr-device-rule-modal').hide();
                    loadDeviceRules();
                }
            }
        });
    });
    
    // Cancel device rule form
    $('#clr-cancel-device-rule').on('click', function() {
        $('#clr-device-rule-modal').hide();
    });
    
    // Initialize device-based functionality if on device-based page
    if ($('#clr-device-rules-list').length) {
        loadDeviceRules();
    }
    
    // Referral-Based Redirects Functionality
    
    // Load referral-based rules
    function loadReferralRules() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_referral_rules',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-referral-rules-list').html(response);
                updateCurrentReferralStatus();
            }
        });
    }
    
    // Update current referral status
    function updateCurrentReferralStatus() {
        // Get referral information from document referrer
        var referrer = document.referrer;
        var source = 'direct';
        var medium = 'none';
        var campaign = 'none';
        var domain = '';
        
        if (referrer) {
            // Parse the referrer URL
            var referrerUrl = new URL(referrer);
            domain = referrerUrl.hostname;
            
            // Identify common search engines
            var searchEngines = {
                'google.com': 'google',
                'bing.com': 'bing',
                'yahoo.com': 'yahoo',
                'duckduckgo.com': 'duckduckgo',
                'baidu.com': 'baidu',
                'yandex.com': 'yandex'
            };
            
            for (var domainName in searchEngines) {
                if (domain.indexOf(domainName) !== -1) {
                    source = searchEngines[domainName];
                    medium = 'organic';
                    break;
                }
            }
            
            // Identify common social media platforms
            var socialPlatforms = {
                'facebook.com': 'facebook',
                'twitter.com': 'twitter',
                'instagram.com': 'instagram',
                'linkedin.com': 'linkedin',
                'pinterest.com': 'pinterest',
                'youtube.com': 'youtube'
            };
            
            for (var domainName in socialPlatforms) {
                if (domain.indexOf(domainName) !== -1) {
                    source = socialPlatforms[domainName];
                    medium = 'social';
                    break;
                }
            }
            
            // Extract UTM parameters if present
            var utmSource = referrerUrl.searchParams.get('utm_source');
            var utmMedium = referrerUrl.searchParams.get('utm_medium');
            var utmCampaign = referrerUrl.searchParams.get('utm_campaign');
            
            if (utmSource) source = utmSource;
            if (utmMedium) medium = utmMedium;
            if (utmCampaign) campaign = utmCampaign;
        }
        
        // Display referral info
        $('#clr-current-referral-source').text(source.charAt(0).toUpperCase() + source.slice(1));
        $('#clr-current-referral-medium').text(medium.charAt(0).toUpperCase() + medium.slice(1));
        $('#clr-current-referral-campaign').text(campaign.charAt(0).toUpperCase() + campaign.slice(1));
        $('#clr-current-referral-domain').text(domain || 'None');
        
        // Find active rule
        var activeRule = 'None';
        $('#clr-referral-rules-list tr').each(function() {
            var $row = $(this);
            var ruleSource = $row.find('td:eq(1)').text().toLowerCase();
            var ruleMedium = $row.find('td:eq(2)').text().toLowerCase();
            var ruleCampaign = $row.find('td:eq(3)').text().toLowerCase();
            
            if ((ruleSource === source || ruleSource === 'any') &&
                (ruleMedium === medium || ruleMedium === 'any medium') &&
                (ruleCampaign === campaign || ruleCampaign === 'none')) {
                activeRule = $row.find('td:eq(4)').text();
                return false; // Break the loop
            }
        });
        
        $('#clr-active-referral-rule').text(activeRule);
    }
    
    // Add new referral rule
    $(document).on('click', '#clr-add-referral-rule', function() {
        $('#clr-referral-rule-id').val('');
        $('#clr-referral-rule-source').val('');
        $('#clr-referral-rule-medium').val('any');
        $('#clr-referral-rule-campaign').val('none');
        $('#clr-referral-rule-url').val('');
        $('#clr-referral-rule-priority').val('10');
        $('#clr-referral-modal-title').text('Add New Referral Rule');
        $('#clr-referral-rule-modal').show();
    });
    
    // Edit referral rule
    $(document).on('click', '.clr-edit-referral-rule', function() {
        var ruleId = $(this).data('id');
        var source = $(this).data('source');
        var medium = $(this).data('medium');
        var campaign = $(this).data('campaign');
        var url = $(this).data('url');
        var priority = $(this).data('priority');
        
        $('#clr-referral-rule-id').val(ruleId);
        $('#clr-referral-rule-source').val(source);
        $('#clr-referral-rule-medium').val(medium);
        $('#clr-referral-rule-campaign').val(campaign);
        $('#clr-referral-rule-url').val(url);
        $('#clr-referral-rule-priority').val(priority);
        $('#clr-referral-modal-title').text('Edit Referral Rule');
        $('#clr-referral-rule-modal').show();
    });
    
    // Delete referral rule
    $(document).on('click', '.clr-delete-referral-rule', function() {
        if (confirm('Are you sure you want to delete this rule?')) {
            var ruleId = $(this).data('id');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_referral_rule',
                    rule_id: ruleId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if(response.success) {
                        loadReferralRules();
                    }
                }
            });
        }
    });
    
    // Save referral rule
    $('#clr-referral-rule-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_referral_rule&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    $('#clr-referral-rule-modal').hide();
                    loadReferralRules();
                }
            }
        });
    });
    
    // Cancel referral rule form
    $('#clr-cancel-referral-rule').on('click', function() {
        $('#clr-referral-rule-modal').hide();
    });
    
    // Initialize referral-based functionality if on referral-based page
    if ($('#clr-referral-rules-list').length) {
        loadReferralRules();
    }
    
    // Analytics Functionality
    
    // Load analytics data
    function loadAnalytics() {
        var days = $('#clr-analytics-period').val();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_analytics',
                days: days,
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateAnalyticsDashboard(response.data);
                }
            }
        });
    }
    
    // Update analytics dashboard
    function updateAnalyticsDashboard(data) {
        // Update overview metrics
        $('#clr-total-sessions').text(data.total_redirects);
        $('#clr-total-page-views').text('-'); // This would need additional calculation
        $('#clr-total-redirects').text(data.total_redirects);
        $('#clr-avg-session-duration').text('-'); // This would need additional calculation
        
        // Update charts
        updateRedirectsTypeChart(data.redirects_by_type);
        updateRedirectsRoleChart(data.redirects_by_user_role);
        updateTopRedirectsChart(data.redirects_by_url);
        updateConversionRatesChart(data.conversions_by_redirect);
    }
    
    // Update redirects by type chart
    function updateRedirectsTypeChart(data) {
        var labels = [];
        var values = [];
        
        $.each(data, function(key, value) {
            labels.push(key.charAt(0).toUpperCase() + key.slice(1));
            values.push(value);
        });
        
        var chartData = {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#4285F4', // Google Blue
                    '#EA4335', // Google Red
                    '#FBBC05', // Google Yellow
                    '#34A853', // Google Green
                    '#9C27B0', // Purple
                    '#FF9800'  // Orange
                ]
            }]
        };
        
        // This would use a charting library like Chart.js
        // For now, just display a simple table
        var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Type</th><th>Count</th></tr></thead><tbody>';
        
        $.each(data, function(key, value) {
            html += '<tr><td>' + key.charAt(0).toUpperCase() + key.slice(1) + '</td><td>' + value + '</td></tr>';
        });
        
        html += '</tbody></table>';
        
        $('#clr-redirects-type-chart').html(html);
    }
    
    // Update redirects by user role chart
    function updateRedirectsRoleChart(data) {
        var labels = [];
        var values = [];
        
        $.each(data, function(key, value) {
            labels.push(key.charAt(0).toUpperCase() + key.slice(1));
            values.push(value);
        });
        
        // This would use a charting library like Chart.js
        // For now, just display a simple table
        var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Role</th><th>Count</th></tr></thead><tbody>';
        
        $.each(data, function(key, value) {
            html += '<tr><td>' + key.charAt(0).toUpperCase() + key.slice(1) + '</td><td>' + value + '</td></tr>';
        });
        
        html += '</tbody></table>';
        
        $('#clr-redirects-role-chart').html(html);
    }
    
    // Update top redirects chart
    function updateTopRedirectsChart(data) {
        var labels = [];
        var values = [];
        
        // Get top 5 redirects
        var count = 0;
        $.each(data, function(key, value) {
            if (count < 5) {
                labels.push(key.length > 30 ? key.substring(0, 30) + '...' : key);
                values.push(value);
                count++;
            }
        });
        
        // This would use a charting library like Chart.js
        // For now, just display a simple table
        var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>URL</th><th>Count</th></tr></thead><tbody>';
        
        count = 0;
        $.each(data, function(key, value) {
            if (count < 5) {
                html += '<tr><td>' + key + '</td><td>' + value + '</td></tr>';
                count++;
            }
        });
        
        html += '</tbody></table>';
        
        $('#clr-top-redirects-chart').html(html);
    }
    
    // Update conversion rates chart
    function updateConversionRatesChart(data) {
        var labels = [];
        var values = [];
        
        // Get top 5 redirects by conversion rate
        var count = 0;
        $.each(data, function(key, value) {
            if (count < 5) {
                labels.push(key.length > 30 ? key.substring(0, 30) + '...' : key);
                values.push(value.conversion_rate);
                count++;
            }
        });
        
        // This would use a charting library like Chart.js
        // For now, just display a simple table
        var html = '<table class="wp-list-table widefat fixed striped"><thead><tr><th>URL</th><th>Redirects</th><th>Conversions</th><th>Rate</th></tr></thead><tbody>';
        
        count = 0;
        $.each(data, function(key, value) {
            if (count < 5) {
                html += '<tr><td>' + key + '</td><td>' + value.redirects + '</td><td>' + value.conversions + '</td><td>' + value.conversion_rate.toFixed(2) + '%</td></tr>';
                count++;
            }
        });
        
        html += '</tbody></table>';
        
        $('#clr-conversion-rates-chart').html(html);
    }
    
    // Refresh analytics
    $('#clr-refresh-analytics').on('click', function() {
        loadAnalytics();
    });
    
    // Change analytics period
    $('#clr-analytics-period').on('change', function() {
        loadAnalytics();
    });
    
    // Search user for analytics
    $('#clr-search-user').on('click', function() {
        var userSearch = $('#clr-user-search').val();
        
        if (!userSearch) {
            alert('Please enter a username or email.');
            return;
        }
        
        var days = $('#clr-analytics-period').val();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_user_analytics',
                user_search: userSearch,
                days: days,
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showUserAnalytics(response.data);
                } else {
                    alert(response.data);
                }
            }
        });
    });
    
    // Show user analytics in modal
    function showUserAnalytics(data) {
        var userInfo = data.user_info;
        var analytics = data;
        
        var html = '<h3>' + userInfo.display_name + ' (' + userInfo.username + ')</h3>';
        html += '<p><strong>Email:</strong> ' + userInfo.email + '</p>';
        html += '<p><strong>Roles:</strong> ' + userInfo.roles.join(', ') + '</p>';
        
        html += '<h4>User Analytics</h4>';
        html += '<div class="clr-user-analytics-metrics">';
        html += '<div class="clr-metric-box"><h5>Total Sessions</h5><div>' + analytics.total_sessions + '</div></div>';
        html += '<div class="clr-metric-box"><h5>Total Page Views</h5><div>' + analytics.total_page_views + '</div></div>';
        html += '<div class="clr-metric-box"><h5>Avg. Session Duration</h5><div>' + formatDuration(analytics.average_session_duration) + '</div></div>';
        html += '</div>';
        
        // Most visited pages
        if (analytics.most_visited_pages && Object.keys(analytics.most_visited_pages).length > 0) {
            html += '<h4>Most Visited Pages</h4>';
            html += '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Page</th><th>Views</th></tr></thead><tbody>';
            
            $.each(analytics.most_visited_pages, function(page, views) {
                html += '<tr><td>' + page + '</td><td>' + views + '</td></tr>';
            });
            
            html += '</tbody></table>';
        }
        
        // Device types
        if (analytics.device_types && Object.keys(analytics.device_types).length > 0) {
            html += '<h4>Device Types</h4>';
            html += '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Device</th><th>Sessions</th></tr></thead><tbody>';
            
            $.each(analytics.device_types, function(device, sessions) {
                html += '<tr><td>' + device.charAt(0).toUpperCase() + device.slice(1) + '</td><td>' + sessions + '</td></tr>';
            });
            
            html += '</tbody></table>';
        }
        
        $('#clr-user-analytics-content').html(html);
        $('#clr-user-analytics-modal').show();
    }
    
    // Format duration in seconds to readable format
    function formatDuration(seconds) {
        if (seconds < 60) {
            return seconds + 's';
        } else if (seconds < 3600) {
            return Math.floor(seconds / 60) + 'm ' + (seconds % 60) + 's';
        } else {
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            return hours + 'h ' + minutes + 'm';
        }
    }
    
    // Close user analytics modal
    $('#clr-close-user-analytics').on('click', function() {
        $('#clr-user-analytics-modal').hide();
    });
    
    // Initialize analytics functionality if on analytics page
    if ($('#clr-analytics-period').length) {
        loadAnalytics();
    }
    
    // Conditional Redirects Functionality
    
    // Load conditional rules
    function loadConditionalRules() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_conditional_rules',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-conditional-rules-list').html(response);
            }
        });
    }
    
    // Add new conditional rule
    $(document).on('click', '#clr-add-conditional-rule', function() {
        $('#clr-conditional-rule-id').val('');
        $('#clr-conditional-rule-name').val('');
        $('#clr-conditional-rule-description').val('');
        $('#clr-conditional-rule-url').val('');
        $('#clr-conditional-rule-priority').val('10');
        $('#clr-conditional-rule-status').val('active');
        $('#clr-conditions-operator').val('AND');
        $('#clr-conditions-groups').empty();
        
        // Add one empty condition group
        addConditionGroup();
        
        $('#clr-conditional-modal-title').text('Add New Conditional Rule');
        $('#clr-conditional-rule-modal').show();
    });
    
    // Edit conditional rule
    $(document).on('click', '.clr-edit-conditional-rule', function() {
        var ruleId = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var conditions = JSON.parse($(this).data('conditions'));
        var url = $(this).data('url');
        var priority = $(this).data('priority');
        var status = $(this).data('status');
        
        $('#clr-conditional-rule-id').val(ruleId);
        $('#clr-conditional-rule-name').val(name);
        $('#clr-conditional-rule-description').val(description);
        $('#clr-conditional-rule-url').val(url);
        $('#clr-conditional-rule-priority').val(priority);
        $('#clr-conditional-rule-status').val(status);
        $('#clr-conditions-operator').val(conditions.operator || 'AND');
        $('#clr-conditions-groups').empty();
        
        // Add condition groups
        if (conditions.groups && conditions.groups.length > 0) {
            $.each(conditions.groups, function(index, group) {
                var groupElement = addConditionGroup();
                
                // Set group operator
                $(groupElement).find('.clr-group-operator').val(group.operator || 'AND');
                
                // Add conditions to group
                if (group.conditions && group.conditions.length > 0) {
                    $.each(group.conditions, function(index, condition) {
                        var conditionElement = addCondition(groupElement);
                        
                        // Set condition values
                        $(conditionElement).find('.clr-condition-field').val(condition.field);
                        $(conditionElement).find('.clr-condition-operator').val(condition.operator);
                        $(conditionElement).find('.clr-condition-value').val(condition.value);
                        
                        // Handle custom field
                        if (condition.field === 'custom_field' && condition.value && condition.value.field) {
                            $(conditionElement).find('.clr-condition-custom-field').show();
                            $(conditionElement).find('.clr-condition-custom-field-name').val(condition.value.field);
                            $(conditionElement).find('.clr-condition-value').val(condition.value.value);
                        }
                        
                        // Trigger field change to show/hide appropriate inputs
                        $(conditionElement).find('.clr-condition-field').trigger('change');
                    });
                }
            });
        } else {
            // Add one empty condition group
            addConditionGroup();
        }
        
        $('#clr-conditional-modal-title').text('Edit Conditional Rule');
        $('#clr-conditional-rule-modal').show();
    });
    
    // Toggle conditional rule status
    $(document).on('click', '.clr-toggle-conditional-rule', function() {
        var ruleId = $(this).data('id');
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_toggle_conditional_rule',
                rule_id: ruleId,
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                if(response.success) {
                    loadConditionalRules();
                }
            }
        });
    });
    
    // Delete conditional rule
    $(document).on('click', '.clr-delete-conditional-rule', function() {
        if (confirm('Are you sure you want to delete this rule?')) {
            var ruleId = $(this).data('id');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_conditional_rule',
                    rule_id: ruleId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if(response.success) {
                        loadConditionalRules();
                    }
                }
            });
        }
    });
    
    // Save conditional rule
    $('#clr-conditional-rule-form').on('submit', function(e) {
        e.preventDefault();
        
        // Build conditions object
        var conditions = {
            operator: $('#clr-conditions-operator').val(),
            groups: []
        };
        
        // Collect condition groups
        $('#clr-conditions-groups .clr-condition-group').each(function() {
            var group = {
                operator: $(this).find('.clr-group-operator').val(),
                conditions: []
            };
            
            // Collect conditions in group
            $(this).find('.clr-condition').each(function() {
                var field = $(this).find('.clr-condition-field').val();
                var operator = $(this).find('.clr-condition-operator').val();
                var value = $(this).find('.clr-condition-value').val();
                
                if (field && operator && value) {
                    var condition = {
                        field: field,
                        operator: operator,
                        value: value
                    };
                    
                    // Handle custom field
                    if (field === 'custom_field') {
                        var customFieldName = $(this).find('.clr-condition-custom-field-name').val();
                        if (customFieldName) {
                            condition.value = {
                                field: customFieldName,
                                value: value
                            };
                        }
                    }
                    
                    group.conditions.push(condition);
                }
            });
            
            if (group.conditions.length > 0) {
                conditions.groups.push(group);
            }
        });
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData + '&action=clr_save_conditional_rule&conditions=' + encodeURIComponent(JSON.stringify(conditions)) + '&nonce=' + clrAdmin.nonce,
            success: function(response) {
                if(response.success) {
                    $('#clr-conditional-rule-modal').hide();
                    loadConditionalRules();
                }
            }
        });
    });
    
    // Cancel conditional rule form
    $('#clr-cancel-conditional-rule').on('click', function() {
        $('#clr-conditional-rule-modal').hide();
    });
    
    // Add condition group
    $('#clr-add-condition-group').on('click', function() {
        addConditionGroup();
    });
    
    // Add condition group function
    function addConditionGroup() {
        var template = $('#clr-condition-group-template').html();
        var groupElement = $(template).appendTo('#clr-conditions-groups');
        
        // Add one empty condition to the group
        addCondition(groupElement);
        
        return groupElement;
    }
    
    // Add condition
    $(document).on('click', '.clr-add-condition', function() {
        var groupElement = $(this).closest('.clr-condition-group');
        addCondition(groupElement);
    });
    
    // Add condition function
    function addCondition(groupElement) {
        var template = $('#clr-condition-template').html();
        var conditionElement = $(template).appendTo($(groupElement).find('.clr-condition-group-conditions'));
        
        return conditionElement;
    }
    
    // Remove condition group
    $(document).on('click', '.clr-remove-condition-group', function() {
        if ($('#clr-conditions-groups .clr-condition-group').length > 1) {
            $(this).closest('.clr-condition-group').remove();
        } else {
            alert('You must have at least one condition group.');
        }
    });
    
    // Remove condition
    $(document).on('click', '.clr-remove-condition', function() {
        var groupElement = $(this).closest('.clr-condition-group');
        if ($(groupElement).find('.clr-condition').length > 1) {
            $(this).closest('.clr-condition').remove();
        } else {
            alert('Each condition group must have at least one condition.');
        }
    });
    
    // Handle condition field change
    $(document).on('change', '.clr-condition-field', function() {
        var field = $(this).val();
        var conditionElement = $(this).closest('.clr-condition');
        
        // Show/hide custom field input
        if (field === 'custom_field') {
            $(conditionElement).find('.clr-condition-custom-field').show();
        } else {
            $(conditionElement).find('.clr-condition-custom-field').hide();
        }
    });
    
    // Test conditional rules
    $('#clr-test-rule').on('click', function() {
        var userRole = $('#clr-test-user-role').val();
        var deviceType = $('#clr-test-device-type').val();
        var timeHour = $('#clr-test-time-hour').val();
        var referralSource = $('#clr-test-referral-source').val();
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_test_conditional_rules',
                user_role: userRole,
                device_type: deviceType,
                time_hour: timeHour,
                referral_source: referralSource,
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                if(response.success) {
                    var results = $('#clr-test-results');
                    
                    if (response.data.matched) {
                        results.html('<div class="notice notice-success"><p><strong>Rule Matched:</strong> ' + response.data.rule_name + '</p><p><strong>Redirect URL:</strong> ' + response.data.redirect_url + '</p></div>');
                    } else {
                        results.html('<div class="notice notice-warning"><p>' + response.data.message + '</p></div>');
                    }
                }
            }
        });
    });
    
    // Initialize conditional functionality if on conditional page
    if ($('#clr-conditional-rules-list').length) {
        loadConditionalRules();
    }
    
    // Import/Export Functionality
    
    // Export rules
    $('#clr-export-rules').on('click', function() {
        var format = $('input[name="clr_export_format"]:checked').val();
        
        // Create a form to submit the export request
        var form = $('<form method="post" action="' + clrAdmin.ajaxurl + '"></form>');
        form.append('<input type="hidden" name="action" value="clr_export_rules">');
        form.append('<input type="hidden" name="format" value="' + format + '">');
        form.append('<input type="hidden" name="nonce" value="' + clrAdmin.nonce + '">');
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
    
    // Handle file selection for import
    $('#clr-import-file').on('change', function() {
        if (this.files.length > 0) {
            $('#clr-import-rules').prop('disabled', false);
        } else {
            $('#clr-import-rules').prop('disabled', true);
        }
    });
    
    // Import rules
    $('#clr-import-rules').on('click', function() {
        var fileInput = $('#clr-import-file')[0];
        
        if (fileInput.files.length === 0) {
            alert('Please select a file to import.');
            return;
        }
        
        var overwrite = $('#clr-import-overwrite').is(':checked') ? 1 : 0;
        
        // Create FormData for file upload
        var formData = new FormData();
        formData.append('action', 'clr_import_rules');
        formData.append('nonce', clrAdmin.nonce);
        formData.append('overwrite', overwrite);
        formData.append('import_file', fileInput.files[0]);
        
        // Show loading indicator
        $('#clr-import-rules').prop('disabled', true).text('Importing...');
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Reset button
                $('#clr-import-rules').prop('disabled', false).text('Import Rules');
                
                // Show results
                $('#clr-import-results').show();
                
                if (response.success) {
                    var resultsHtml = '<div class="notice notice-success"><p>' + response.data.message + '</p></div>';
                    
                    if (response.data.imported) {
                        resultsHtml += '<h4>' + 'Import Summary' + '</h4>';
                        resultsHtml += '<ul>';
                        
                        if (response.data.imported.basic > 0) {
                            resultsHtml += '<li>' + response.data.imported.basic + ' ' + 'basic rules imported' + '</li>';
                        }
                        
                        if (response.data.imported.time_based > 0) {
                            resultsHtml += '<li>' + response.data.imported.time_based + ' ' + 'time-based rules imported' + '</li>';
                        }
                        
                        if (response.data.imported.device_based > 0) {
                            resultsHtml += '<li>' + response.data.imported.device_based + ' ' + 'device-based rules imported' + '</li>';
                        }
                        
                        if (response.data.imported.referral_based > 0) {
                            resultsHtml += '<li>' + response.data.imported.referral_based + ' ' + 'referral-based rules imported' + '</li>';
                        }
                        
                        if (response.data.imported.conditional > 0) {
                            resultsHtml += '<li>' + response.data.imported.conditional + ' ' + 'conditional rules imported' + '</li>';
                        }
                        
                        if (response.data.imported.ab_testing > 0) {
                            resultsHtml += '<li>' + response.data.imported.ab_testing + ' ' + 'A/B testing variants imported' + '</li>';
                        }
                        
                        resultsHtml += '</ul>';
                    }
                    
                    if (response.data.errors && response.data.errors.length > 0) {
                        resultsHtml += '<h4>' + 'Errors' + '</h4>';
                        resultsHtml += '<ul>';
                        
                        $.each(response.data.errors, function(index, error) {
                            resultsHtml += '<li>' + error + '</li>';
                        });
                        
                        resultsHtml += '</ul>';
                    }
                } else {
                    var resultsHtml = '<div class="notice notice-error"><p>' + response.data.message + '</p></div>';
                }
                
                $('#clr-import-results .clr-import-results-content').html(resultsHtml);
                
                // Reset file input
                $('#clr-import-file').val('');
                $('#clr-import-rules').prop('disabled', true);
            },
            error: function() {
                // Reset button
                $('#clr-import-rules').prop('disabled', false).text('Import Rules');
                
                // Show error message
                $('#clr-import-results').show();
                $('#clr-import-results .clr-import-results-content').html('<div class="notice notice-error"><p>' + 'An error occurred during import. Please try again.' + '</p></div>');
            }
        });
    });
    
    // Email Notifications Functionality
    
    // Test email notification
    $('#clr-test-email').on('click', function() {
        var $button = $(this);
        var $result = $('#clr-test-email-result');
        
        // Show loading state
        $button.prop('disabled', true).text('Sending...');
        $result.show().html('<div class="notice notice-info"><p>Sending test email...</p></div>');
        
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_test_email',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                // Reset button
                $button.prop('disabled', false).text('Send Test Email');
                
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    $result.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                // Reset button
                $button.prop('disabled', false).text('Send Test Email');
                
                // Show error message
                $result.html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
            }
        });
        
        // License Activation Functionality
        
        // Activate license
        $(document).on('click', '#clr_activate_license', function() {
            var $button = $(this);
            var licenseKey = $('input[name="clr_license_key"]').val();
            var $status = $('#clr_license_status');
            
            if (!licenseKey) {
                $status.html('<span class="clr-license-error">' + 'Please enter a license key.' + '</span>');
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Activating...');
            $status.html('<span class="clr-license-loading">' + 'Activating license...' + '</span>');
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_activate_license',
                    license_key: licenseKey,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    // Reset button
                    $button.prop('disabled', false).text('Activate License');
                    
                    if (response.success) {
                        $status.html('<span class="clr-license-success">' + response.data + '</span>');
                        // Reload the page to update the premium status
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $status.html('<span class="clr-license-error">' + response.data + '</span>');
                    }
                },
                error: function() {
                    // Reset button
                    $button.prop('disabled', false).text('Activate License');
                    
                    // Show error message
                    $status.html('<span class="clr-license-error">' + 'An error occurred. Please try again.' + '</span>');
                }
            });
        });
        
        // Premium Analytics Functionality
        
        // Initialize premium analytics if on premium analytics page
        function initPremiumAnalytics() {
            // Load real-time dashboard
            loadRealTimeDashboard();
            
            // Set up real-time updates
            setInterval(checkRealTimeData, 5000); // Check every 5 seconds
            
            // Load user journey data
            $('#clr-load-user-journey').on('click', loadUserJourney);
            
            // Load conversion funnel
            $('#clr-refresh-funnel').on('click', loadConversionFunnel);
            $('#clr-funnel-days').on('change', loadConversionFunnel);
            
            // Load heatmap data
            $('#clr-load-heatmap').on('click', loadHeatmap);
            
            // Custom reports functionality
            loadCustomReports();
            $('#clr-create-new-report').on('click', showCreateReportModal);
            $('#clr-save-report').on('click', saveCustomReport);
            $('#clr-cancel-report').on('click', hideCreateReportModal);
            
            // Integration functionality
            $('#clr-connect-ga').on('click', showGAConnectionModal);
            $('#clr-disconnect-ga').on('click', disconnectGA);
            $('#clr-connect-mailchimp').on('click', showMailchimpConnectionModal);
            $('#clr-disconnect-mailchimp').on('click', disconnectMailchimp);
            $('#clr-connect-slack').on('click', showSlackConnectionModal);
            $('#clr-disconnect-slack').on('click', disconnectSlack);
            
            // Close modals
            $('#clr-close-user-journey').on('click', hideUserJourneyModal);
            $('#clr-close-integration').on('click', hideIntegrationModal);
            $('.clr-modal-backdrop').on('click', function() {
                hideUserJourneyModal();
                hideCreateReportModal();
                hideIntegrationModal();
            });
        }
        
        // Load real-time dashboard data
        function loadRealTimeDashboard() {
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_check_real_time_data',
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateRealTimeDashboard(response.data);
                    }
                }
            });
        }
        
        // Update real-time dashboard
        function updateRealTimeDashboard(data) {
            $('#clr-active-sessions').text(data.active_sessions);
            
            // Calculate events today
            var eventsToday = 0;
            var eventsYesterday = 0;
            var eventsChange = 0;
            
            // This would be calculated from the data
            // For now, just use placeholder values
            $('#clr-events-today').text(eventsToday);
            $('#clr-events-today-change').text(eventsChange >= 0 ? '+' + eventsChange + '%' : eventsChange + '%');
            
            // Calculate page views
            var pageViews = 0;
            var pageViewsChange = 0;
            $('#clr-page-views').text(pageViews);
            $('#clr-page-views-change').text(pageViewsChange >= 0 ? '+' + pageViewsChange + '%' : pageViewsChange + '%');
            
            // Calculate user interactions
            var userInteractions = 0;
            var userInteractionsChange = 0;
            $('#clr-user-interactions').text(userInteractions);
            $('#clr-user-interactions-change').text(userInteractionsChange >= 0 ? '+' + userInteractionsChange + '%' : userInteractionsChange + '%');
            
            // Update event stream
            updateEventStream(data.events);
        }
        
        // Update event stream
        function updateEventStream(events) {
            var $stream = $('#clr-event-stream');
            
            if (events && events.length > 0) {
                // Remove placeholder if it exists
                $stream.find('.clr-event-stream-placeholder').remove();
                
                // Add new events
                $.each(events, function(index, event) {
                    var eventHtml = '<div class="clr-event-stream-item">';
                    eventHtml += '<span class="clr-event-type">' + event.event_type + '</span>';
                    eventHtml += '<span class="clr-event-count">' + event.count + '</span>';
                    eventHtml += '<span class="clr-event-time">' + new Date().toLocaleTimeString() + '</span>';
                    eventHtml += '</div>';
                    
                    $stream.prepend(eventHtml);
                });
                
                // Keep only the last 20 events
                $stream.find('.clr-event-stream-item:gt(19)').remove();
            }
        }
        
        // Check for real-time data updates
        function checkRealTimeData() {
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_check_real_time_data',
                    last_check: Math.floor(Date.now() / 1000),
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateRealTimeDashboard(response.data);
                    }
                }
            });
        }
        
        // Load user journey
        function loadUserJourney() {
            var userId = $('#clr-journey-user').val();
            var days = $('#clr-journey-days').val();
            
            if (!userId) {
                alert('Please select a user.');
                return;
            }
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_load_user_journey',
                    user_id: userId,
                    days: days,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showUserJourney(response.data);
                    }
                }
            });
        }
        
        // Show user journey in modal
        function showUserJourney(journey) {
            var html = '';
            
            if (journey && journey.length > 0) {
                $.each(journey, function(index, session) {
                    html += '<div class="clr-user-journey-session">';
                    html += '<h4>Session: ' + session.session_id + '</h4>';
                    html += '<div class="clr-user-journey-events">';
                    
                    $.each(session.events, function(index, event) {
                        var eventClass = 'clr-event-' + event.type.replace('_', '-');
                        var eventIcon = getEventIcon(event.type);
                        var eventTitle = getEventTitle(event.type);
                        
                        html += '<div class="clr-user-journey-event ' + eventClass + '">';
                        html += '<span class="clr-event-icon">' + eventIcon + '</span>';
                        html += '<div class="clr-event-details">';
                        html += '<h5>' + eventTitle + '</h5>';
                        html += '<p class="clr-event-time">' + event.timestamp + '</p>';
                        
                        // Add event-specific details
                        if (event.type === 'login') {
                            html += '<p>User logged in</p>';
                        } else if (event.type === 'page_view') {
                            html += '<p>Viewed page: ' + (event.data.url || 'Unknown') + '</p>';
                        } else if (event.type === 'interaction_click') {
                            html += '<p>Clicked on: ' + (event.data.text || 'Unknown element') + '</p>';
                        } else if (event.type === 'interaction_form_submit') {
                            html += '<p>Submitted form: ' + (event.data.form_id || 'Unknown form') + '</p>';
                        } else if (event.type === 'comment') {
                            html += '<p>Commented on post: ' + (event.data.post_id || 'Unknown post') + '</p>';
                        } else if (event.type === 'post_published') {
                            html += '<p>Published post: ' + (event.data.post_title || 'Unknown post') + '</p>';
                        }
                        
                        html += '</div>';
                        html += '</div>';
                    });
                    
                    html += '</div>';
                    html += '</div>';
                });
            } else {
                html = '<p>No user journey data found for the selected time period.</p>';
            }
            
            $('#clr-user-journey-content').html(html);
            $('#clr-user-journey-modal').show();
        }
        
        // Get event icon based on event type
        function getEventIcon(eventType) {
            var icons = {
                'login': '🔑',
                'page_view': '👁️',
                'interaction_click': '👆',
                'interaction_form_submit': '📝',
                'comment': '💬',
                'post_published': '📝'
            };
            
            return icons[eventType] || '📌';
        }
        
        // Get event title based on event type
        function getEventTitle(eventType) {
            var titles = {
                'login': 'User Login',
                'page_view': 'Page View',
                'interaction_click': 'Click Interaction',
                'interaction_form_submit': 'Form Submission',
                'comment': 'Comment',
                'post_published': 'Post Published'
            };
            
            return titles[eventType] || 'Event';
        }
        
        // Hide user journey modal
        function hideUserJourneyModal() {
            $('#clr-user-journey-modal').hide();
        }
        
        // Load conversion funnel
        function loadConversionFunnel() {
            var days = $('#clr-funnel-days').val();
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_load_conversion_funnel',
                    days: days,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showConversionFunnel(response.data);
                    }
                }
            });
        }
        
        // Show conversion funnel
        function showConversionFunnel(data) {
            var html = '<div class="clr-conversion-funnel-steps">';
            
            var previousCount = null;
            
            $.each(data, function(stepName, stepData) {
                var stepTitle = stepName.charAt(0).toUpperCase() + stepName.slice(1).replace('_', ' ');
                var stepCount = stepData.count;
                var stepRate = stepData.conversion_rate;
                var stepUsers = stepData.unique_users;
                
                html += '<div class="clr-funnel-step">';
                html += '<div class="clr-funnel-step-header">';
                html += '<h4>' + stepTitle + '</h4>';
                html += '<div class="clr-funnel-step-metrics">';
                html += '<span class="clr-funnel-step-count">' + stepCount + '</span>';
                html += '<span class="clr-funnel-step-users">' + stepUsers + ' users</span>';
                html += '</div>';
                html += '</div>';
                
                // Add funnel bar
                var barWidth = previousCount !== null ? (stepCount / previousCount) * 100 : 100;
                html += '<div class="clr-funnel-step-bar-container">';
                html += '<div class="clr-funnel-step-bar" style="width: ' + barWidth + '%;"></div>';
                html += '</div>';
                
                // Add conversion rate
                if (stepRate !== undefined) {
                    html += '<div class="clr-funnel-step-rate">';
                    html += '<span class="clr-funnel-step-rate-value">' + stepRate.toFixed(2) + '%</span>';
                    html += '<span class="clr-funnel-step-rate-label">conversion rate</span>';
                    html += '</div>';
                }
                
                html += '</div>';
                
                previousCount = stepCount;
            });
            
            html += '</div>';
            
            $('#clr-conversion-funnel').html(html);
        }
        
        // Load heatmap data
        function loadHeatmap() {
            var postId = $('#clr-heatmap-post').val();
            var days = $('#clr-heatmap-days').val();
            
            if (!postId) {
                alert('Please select a page or post.');
                return;
            }
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_load_heatmap_data',
                    post_id: postId,
                    days: days,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showHeatmap(response.data);
                    }
                }
            });
        }
        
        // Show heatmap
        function showHeatmap(data) {
            var html = '<div class="clr-heatmap-container">';
            html += '<div class="clr-heatmap-preview">';
            html += '<h4>Click Heatmap Preview</h4>';
            html += '<div class="clr-heatmap-preview-area">';
            
            if (data && data.length > 0) {
                // This would be replaced with an actual heatmap visualization
                // For now, just show a list of clicks
                html += '<div class="clr-heatmap-clicks">';
                html += '<h5>Click Data (' + data.length + ' clicks)</h5>';
                html += '<ul>';
                
                $.each(data, function(index, click) {
                    html += '<li>';
                    html += '<strong>' + click.element + ':</strong> ' + click.text;
                    html += ' <span class="clr-heatmap-coords">(' + click.x + ', ' + click.y + ')</span>';
                    html += '</li>';
                });
                
                html += '</ul>';
                html += '</div>';
            } else {
                html += '<p>No click data found for the selected page and time period.</p>';
            }
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            $('#clr-heatmap-results').html(html);
        }
        
        // Load custom reports
        function loadCustomReports() {
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_load_custom_reports',
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateCustomReportsList(response.data);
                    }
                }
            });
        }
        
        // Update custom reports list
        function updateCustomReportsList(reports) {
            var html = '';
            
            if (reports && reports.length > 0) {
                $.each(reports, function(index, report) {
                    html += '<tr>';
                    html += '<td>' + report.name + '</td>';
                    html += '<td>' + report.description + '</td>';
                    html += '<td>' + report.created_at + '</td>';
                    html += '<td>';
                    html += '<button type="button" class="button button-small clr-view-report" data-id="' + report.id + '">View</button> ';
                    html += '<button type="button" class="button button-small clr-export-report" data-id="' + report.id + '">Export</button> ';
                    html += '<button type="button" class="button button-small clr-delete-report" data-id="' + report.id + '">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="4">No reports found. Create your first report to get started.</td></tr>';
            }
            
            $('#clr-reports-list').html(html);
            
            // Add event handlers for report actions
            $('.clr-view-report').on('click', function() {
                var reportId = $(this).data('id');
                viewCustomReport(reportId);
            });
            
            $('.clr-export-report').on('click', function() {
                var reportId = $(this).data('id');
                exportCustomReport(reportId);
            });
            
            $('.clr-delete-report').on('click', function() {
                var reportId = $(this).data('id');
                
                if (confirm('Are you sure you want to delete this report?')) {
                    deleteCustomReport(reportId);
                }
            });
        }
        
        // Show create report modal
        function showCreateReportModal() {
            $('#clr-report-id').val('');
            $('#clr-report-name').val('');
            $('#clr-report-description').val('');
            $('#clr-report-type').val('');
            $('#clr-report-days').val('30');
            $('#clr-report-limit').val('10');
            
            $('#clr-custom-report-title').text('Create Custom Report');
            $('#clr-custom-report-modal').show();
        }
        
        // Hide create report modal
        function hideCreateReportModal() {
            $('#clr-custom-report-modal').hide();
        }
        
        // Save custom report
        function saveCustomReport() {
            var reportId = $('#clr-report-id').val();
            var name = $('#clr-report-name').val();
            var description = $('#clr-report-description').val();
            var type = $('#clr-report-type').val();
            var days = $('#clr-report-days').val();
            var limit = $('#clr-report-limit').val();
            
            if (!name || !type) {
                alert('Please fill in all required fields.');
                return;
            }
            
            var config = {
                type: type,
                days: days,
                limit: limit
            };
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_save_custom_report',
                    report_id: reportId,
                    name: name,
                    description: description,
                    config: JSON.stringify(config),
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        hideCreateReportModal();
                        loadCustomReports();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // View custom report
        function viewCustomReport(reportId) {
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_load_custom_report',
                    report_id: reportId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showCustomReportResults(response.data);
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // Show custom report results
        function showCustomReportResults(data) {
            var report = data.report;
            var reportData = data.data;
            
            var html = '<div class="clr-custom-report-results">';
            html += '<h3>' + report.name + '</h3>';
            html += '<p>' + report.description + '</p>';
            
            html += '<div class="clr-report-data">';
            html += '<table class="wp-list-table widefat fixed striped">';
            
            // Add header row
            if (reportData.length > 0) {
                html += '<thead><tr>';
                $.each(Object.keys(reportData[0]), function(index, key) {
                    html += '<th>' + key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' ') + '</th>';
                });
                html += '</tr></thead>';
            }
            
            // Add data rows
            html += '<tbody>';
            $.each(reportData, function(index, row) {
                html += '<tr>';
                $.each(row, function(key, value) {
                    html += '<td>' + value + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody>';
            
            html += '</table>';
            html += '</div>';
            
            html += '<div class="clr-report-actions">';
            html += '<button type="button" class="button" id="clr-close-report-results">Close</button>';
            html += '<button type="button" class="button button-primary" id="clr-export-current-report" data-id="' + report.id + '">Export Report</button>';
            html += '</div>';
            
            html += '</div>';
            
            // Show results in modal
            $('#clr-custom-report-content').html(html);
            $('#clr-custom-report-modal').show();
            
            // Add event handlers
            $('#clr-close-report-results').on('click', function() {
                $('#clr-custom-report-modal').hide();
            });
            
            $('#clr-export-current-report').on('click', function() {
                var reportId = $(this).data('id');
                exportCustomReport(reportId);
            });
        }
        
        // Export custom report
        function exportCustomReport(reportId) {
            var format = prompt('Export format (csv or json):', 'csv');
            
            if (!format || (format !== 'csv' && format !== 'json')) {
                alert('Please enter a valid format (csv or json).');
                return;
            }
            
            // Create a form to submit the export request
            var form = $('<form method="post" action="' + clrAdmin.ajaxurl + '"></form>');
            form.append('<input type="hidden" name="action" value="clr_export_report">');
            form.append('<input type="hidden" name="report_id" value="' + reportId + '">');
            form.append('<input type="hidden" name="format" value="' + format + '">');
            form.append('<input type="hidden" name="nonce" value="' + clrAdmin.nonce + '">');
            
            $('body').append(form);
            form.submit();
            form.remove();
        }
        
        // Delete custom report
        function deleteCustomReport(reportId) {
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_delete_custom_report',
                    report_id: reportId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        loadCustomReports();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // Show Google Analytics connection modal
        function showGAConnectionModal() {
            var trackingId = prompt('Enter your Google Analytics Tracking ID:');
            
            if (!trackingId) {
                return;
            }
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_connect_ga',
                    tracking_id: trackingId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // Disconnect Google Analytics
        function disconnectGA() {
            if (confirm('Are you sure you want to disconnect Google Analytics?')) {
                $.ajax({
                    url: clrAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clr_disconnect_ga',
                        nonce: clrAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data);
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    }
                });
            }
        }
        
        // Show Mailchimp connection modal
        function showMailchimpConnectionModal() {
            var apiKey = prompt('Enter your Mailchimp API Key:');
            
            if (!apiKey) {
                return;
            }
            
            var listId = prompt('Enter your Mailchimp List ID:');
            
            if (!listId) {
                return;
            }
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_connect_mailchimp',
                    api_key: apiKey,
                    list_id: listId,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // Disconnect Mailchimp
        function disconnectMailchimp() {
            if (confirm('Are you sure you want to disconnect Mailchimp?')) {
                $.ajax({
                    url: clrAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clr_disconnect_mailchimp',
                        nonce: clrAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data);
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    }
                });
            }
        }
        
        // Show Slack connection modal
        function showSlackConnectionModal() {
            var webhookUrl = prompt('Enter your Slack Webhook URL:');
            
            if (!webhookUrl) {
                return;
            }
            
            $.ajax({
                url: clrAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'clr_connect_slack',
                    webhook_url: webhookUrl,
                    nonce: clrAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
        
        // Disconnect Slack
        function disconnectSlack() {
            if (confirm('Are you sure you want to disconnect Slack?')) {
                $.ajax({
                    url: clrAdmin.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clr_disconnect_slack',
                        nonce: clrAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data);
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    }
                });
            }
        }
        
        // Hide integration modal
        function hideIntegrationModal() {
            $('#clr-integration-modal').hide();
        }
    });
    
    // Load notification history
    function loadNotificationHistory() {
        $.ajax({
            url: clrAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'clr_load_notification_history',
                nonce: clrAdmin.nonce
            },
            success: function(response) {
                $('#clr-notification-history-list').html(response);
            }
        });
    }
    
    // Initialize email notifications functionality if on email notifications page
    if ($('#clr-test-email').length) {
        loadNotificationHistory();
    }
    
    // Download template
    $('.clr-template-option button').on('click', function() {
        var template = $(this).data('template');
        
        // Create a form to submit the template request
        var form = $('<form method="post" action="' + clrAdmin.ajaxurl + '"></form>');
        form.append('<input type="hidden" name="action" value="clr_download_template">');
        form.append('<input type="hidden" name="template" value="' + template + '">');
        form.append('<input type="hidden" name="nonce" value="' + clrAdmin.nonce + '">');
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
});