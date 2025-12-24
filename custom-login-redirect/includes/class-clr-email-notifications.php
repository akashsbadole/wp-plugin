<?php
class CLR_Email_Notifications {
    public function __construct() {
        add_action('clr_user_redirected', array($this, 'send_redirect_notification'), 10, 4);
        add_action('wp_login', array($this, 'send_login_notification'), 10, 2);
    }

    /**
     * Send email notification when a user is redirected
     */
    public function send_redirect_notification($user_id, $redirect_url, $rule_type, $rule_details) {
        // Check if email notifications are enabled
        if (!get_option('clr_email_notifications_enabled')) {
            return;
        }

        // Get user data
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return;
        }

        // Get notification settings
        $notification_events = get_option('clr_notification_events', array());
        if (!in_array('redirect', $notification_events)) {
            return;
        }

        // Get admin email
        $admin_email = get_option('admin_email');
        $custom_email = get_option('clr_notification_email');
        $to_email = !empty($custom_email) ? $custom_email : $admin_email;

        // Prepare email subject
        $subject = sprintf(
            '[%s] User Redirected: %s',
            get_bloginfo('name'),
            $user->user_login
        );

        // Prepare email body
        $message = $this->get_redirect_email_template($user, $redirect_url, $rule_type, $rule_details);

        // Send email
        $this->send_email($to_email, $subject, $message, 'redirect', $user_id);
    }

    /**
     * Send email notification when a user logs in
     */
    public function send_login_notification($user_login, $user) {
        // Check if email notifications are enabled
        if (!get_option('clr_email_notifications_enabled')) {
            return;
        }

        // Get notification settings
        $notification_events = get_option('clr_notification_events', array());
        if (!in_array('login', $notification_events)) {
            return;
        }

        // Get admin email
        $admin_email = get_option('admin_email');
        $custom_email = get_option('clr_notification_email');
        $to_email = !empty($custom_email) ? $custom_email : $admin_email;

        // Prepare email subject
        $subject = sprintf(
            '[%s] User Login: %s',
            get_bloginfo('name'),
            $user_login
        );

        // Prepare email body
        $message = $this->get_login_email_template($user);

        // Send email
        $this->send_email($to_email, $subject, $message, 'login', $user->ID);
    }

    /**
     * Get redirect email template
     */
    private function get_redirect_email_template($user, $redirect_url, $rule_type, $rule_details) {
        $template = get_option('clr_email_template', 'default');
        
        // Get site information
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        $admin_url = admin_url();
        
        // Get user information
        $user_id = $user->ID;
        $user_login = $user->user_login;
        $user_email = $user->user_email;
        $user_roles = implode(', ', $user->roles);
        
        // Get current time
        $current_time = current_time('mysql');
        
        // Get IP address
        $ip_address = $this->get_user_ip();
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Build rule details string based on rule type
        $rule_info = '';
        
        switch ($rule_type) {
            case 'basic':
                $rule_info = sprintf(
                    'Role: %s',
                    $rule_details['role']
                );
                break;
                
            case 'time_based':
                $days = array(
                    'any' => 'Any Day',
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday'
                );
                
                $rule_info = sprintf(
                    'Time Rule: %s, %s:00 - %s:00',
                    $days[$rule_details['day_of_week']],
                    $rule_details['start_hour'],
                    $rule_details['end_hour']
                );
                break;
                
            case 'device_based':
                $device_types = array(
                    'any' => 'Any Device',
                    'desktop' => 'Desktop',
                    'mobile' => 'Mobile',
                    'tablet' => 'Tablet'
                );
                
                $device_os = array(
                    'any' => 'Any OS',
                    'windows' => 'Windows',
                    'mac' => 'Mac',
                    'linux' => 'Linux',
                    'android' => 'Android',
                    'ios' => 'iOS'
                );
                
                $browsers = array(
                    'any' => 'Any Browser',
                    'chrome' => 'Chrome',
                    'firefox' => 'Firefox',
                    'safari' => 'Safari',
                    'edge' => 'Edge',
                    'opera' => 'Opera',
                    'ie' => 'Internet Explorer'
                );
                
                $rule_info = sprintf(
                    'Device Rule: %s, %s, %s',
                    $device_types[$rule_details['device_type']],
                    $device_os[$rule_details['device_os']],
                    $browsers[$rule_details['browser']]
                );
                break;
                
            case 'referral_based':
                $rule_info = sprintf(
                    'Referral Rule: Source: %s, Medium: %s, Campaign: %s',
                    $rule_details['source'],
                    $rule_details['medium'],
                    $rule_details['campaign']
                );
                break;
                
            case 'conditional':
                $rule_info = sprintf(
                    'Conditional Rule: %s',
                    $rule_details['name']
                );
                break;
        }
        
        // Use custom template if set
        if ($template === 'custom' && ($custom_template = get_option('clr_email_custom_template'))) {
            // Replace placeholders in custom template
            $message = str_replace(
                array(
                    '{site_name}',
                    '{site_url}',
                    '{admin_url}',
                    '{user_id}',
                    '{user_login}',
                    '{user_email}',
                    '{user_roles}',
                    '{current_time}',
                    '{ip_address}',
                    '{user_agent}',
                    '{redirect_url}',
                    '{rule_type}',
                    '{rule_info}'
                ),
                array(
                    $site_name,
                    $site_url,
                    $admin_url,
                    $user_id,
                    $user_login,
                    $user_email,
                    $user_roles,
                    $current_time,
                    $ip_address,
                    $user_agent,
                    $redirect_url,
                    $rule_type,
                    $rule_info
                ),
                $custom_template
            );
            
            return $message;
        }
        
        // Default template
        $message = "A user has been redirected on your site {$site_name}.\n\n";
        $message .= "User Information:\n";
        $message .= "  - User ID: {$user_id}\n";
        $message .= "  - Username: {$user_login}\n";
        $message .= "  - Email: {$user_email}\n";
        $message .= "  - Roles: {$user_roles}\n\n";
        $message .= "Redirect Details:\n";
        $message .= "  - Time: {$current_time}\n";
        $message .= "  - IP Address: {$ip_address}\n";
        $message .= "  - User Agent: {$user_agent}\n";
        $message .= "  - Redirect URL: {$redirect_url}\n";
        $message .= "  - Rule Type: {$rule_type}\n";
        $message .= "  - Rule Details: {$rule_info}\n\n";
        $message .= "You can manage redirect rules here: {$admin_url}admin.php?page=clr-settings\n\n";
        $message .= "This notification was sent by the Custom Login Redirect plugin.";
        
        return $message;
    }

    /**
     * Get login email template
     */
    private function get_login_email_template($user) {
        $template = get_option('clr_email_template', 'default');
        
        // Get site information
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');
        $admin_url = admin_url();
        
        // Get user information
        $user_id = $user->ID;
        $user_login = $user->user_login;
        $user_email = $user->user_email;
        $user_roles = implode(', ', $user->roles);
        
        // Get current time
        $current_time = current_time('mysql');
        
        // Get IP address
        $ip_address = $this->get_user_ip();
        
        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Use custom template if set
        if ($template === 'custom' && ($custom_template = get_option('clr_email_custom_template'))) {
            // Replace placeholders in custom template
            $message = str_replace(
                array(
                    '{site_name}',
                    '{site_url}',
                    '{admin_url}',
                    '{user_id}',
                    '{user_login}',
                    '{user_email}',
                    '{user_roles}',
                    '{current_time}',
                    '{ip_address}',
                    '{user_agent}'
                ),
                array(
                    $site_name,
                    $site_url,
                    $admin_url,
                    $user_id,
                    $user_login,
                    $user_email,
                    $user_roles,
                    $current_time,
                    $ip_address,
                    $user_agent
                ),
                $custom_template
            );
            
            return $message;
        }
        
        // Default template
        $message = "A user has logged in to your site {$site_name}.\n\n";
        $message .= "User Information:\n";
        $message .= "  - User ID: {$user_id}\n";
        $message .= "  - Username: {$user_login}\n";
        $message .= "  - Email: {$user_email}\n";
        $message .= "  - Roles: {$user_roles}\n\n";
        $message .= "Login Details:\n";
        $message .= "  - Time: {$current_time}\n";
        $message .= "  - IP Address: {$ip_address}\n";
        $message .= "  - User Agent: {$user_agent}\n\n";
        $message .= "You can manage redirect rules here: {$admin_url}admin.php?page=clr-settings\n\n";
        $message .= "This notification was sent by the Custom Login Redirect plugin.";
        
        return $message;
    }

    /**
     * Send email
     */
    private function send_email($to, $subject, $message, $event_type = 'redirect', $user_id = 0) {
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        $result = wp_mail($to, $subject, $message, $headers);
        
        // Log the email notification
        $this->log_notification($to, $subject, $message, $result ? 'sent' : 'failed', $event_type, $user_id);
        
        return $result;
    }

    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_address = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip_address;
    }
    
    /**
     * Log notification to database
     */
    private function log_notification($recipient, $subject, $message, $status, $event_type = 'redirect', $user_id = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_notification_history';
        
        // Get current user if user_id not provided
        if ($user_id === 0) {
            $current_user = wp_get_current_user();
            if ($current_user && $current_user->exists()) {
                $user_id = $current_user->ID;
            }
        }
        
        // Get user login
        $user_login = '';
        if ($user_id > 0) {
            $user = get_user_by('ID', $user_id);
            if ($user) {
                $user_login = $user->user_login;
            }
        }
        
        // Insert notification record
        $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'user_id' => $user_id,
                'user_login' => $user_login,
                'recipient' => $recipient,
                'subject' => $subject,
                'message' => $message,
                'status' => $status,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Test email notification
     */
    public function test_email_notification() {
        // Get admin email
        $admin_email = get_option('admin_email');
        $custom_email = get_option('clr_notification_email');
        $to_email = !empty($custom_email) ? $custom_email : $admin_email;

        // Prepare email subject
        $subject = sprintf(
            '[%s] Test Email Notification',
            get_bloginfo('name')
        );

        // Prepare email body
        $message = "This is a test email notification from the Custom Login Redirect plugin.\n\n";
        $message .= "If you received this email, the email notification feature is working correctly.\n\n";
        $message .= "Site: " . get_bloginfo('name') . " (" . get_bloginfo('url') . ")\n";
        $message .= "Time: " . current_time('mysql') . "\n\n";
        $message .= "This notification was sent by the Custom Login Redirect plugin.";

        // Send email
        return $this->send_email($to_email, $subject, $message, 'test');
    }
}