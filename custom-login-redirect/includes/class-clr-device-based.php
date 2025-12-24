<?php
class CLR_Device_Based {
    public function __construct() {
        add_action('wp_login', array($this, 'capture_device_info'), 10, 2);
    }

    public function capture_device_info($user_login, $user) {
        $user_id = $user->ID;
        
        // Get device information
        $device_info = $this->get_device_info();
        
        // Store device information for device-based redirects
        update_user_meta($user_id, 'clr_last_device_info', $device_info);
    }

    private function get_device_info() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Default to desktop
        $device_type = 'desktop';
        $device_os = 'unknown';
        $browser = 'unknown';
        
        // Detect device type
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent)) {
            $device_type = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent)) {
            $device_type = 'mobile';
        }
        
        // Detect OS
        if (preg_match('/windows/i', $user_agent)) {
            $device_os = 'windows';
        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $device_os = 'mac';
        } elseif (preg_match('/linux/i', $user_agent)) {
            $device_os = 'linux';
        } elseif (preg_match('/android/i', $user_agent)) {
            $device_os = 'android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $user_agent)) {
            $device_os = 'ios';
        }
        
        // Detect browser
        if (preg_match('/msie/i', $user_agent) && !preg_match('/opera/i', $user_agent)) {
            $browser = 'ie';
        } elseif (preg_match('/firefox/i', $user_agent)) {
            $browser = 'firefox';
        } elseif (preg_match('/chrome/i', $user_agent)) {
            $browser = 'chrome';
        } elseif (preg_match('/safari/i', $user_agent)) {
            $browser = 'safari';
        } elseif (preg_match('/opera/i', $user_agent)) {
            $browser = 'opera';
        } elseif (preg_match('/edge/i', $user_agent)) {
            $browser = 'edge';
        }
        
        return array(
            'device_type' => $device_type,
            'device_os' => $device_os,
            'browser' => $browser,
            'user_agent' => $user_agent
        );
    }

    public function get_redirect($user_id) {
        $device_info = get_user_meta($user_id, 'clr_last_device_info', true);
        
        if (!$device_info) {
            // If no stored device info, get current device info
            $device_info = $this->get_device_info();
        }
        
        // Check for device-specific redirect rules
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        // First check for device type + OS + browser specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE device_type = %s 
            AND device_os = %s 
            AND browser = %s
            ORDER BY priority DESC LIMIT 1",
            $device_info['device_type'],
            $device_info['device_os'],
            $device_info['browser']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Then check for device type + OS specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE device_type = %s 
            AND device_os = %s 
            AND browser = 'any'
            ORDER BY priority DESC LIMIT 1",
            $device_info['device_type'],
            $device_info['device_os']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Then check for device type specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE device_type = %s 
            AND device_os = 'any' 
            AND browser = 'any'
            ORDER BY priority DESC LIMIT 1",
            $device_info['device_type']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Default device-based redirects
        if ($device_info['device_type'] === 'mobile') {
            return home_url('/mobile-home');
        } elseif ($device_info['device_type'] === 'tablet') {
            return home_url('/tablet-home');
        } else {
            return home_url('/desktop-home');
        }
    }
    
    public function get_all_rules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, device_type, device_os, browser");
    }
    
    public function add_rule($device_type, $device_os, $browser, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'device_type' => $device_type,
                'device_os' => $device_os,
                'browser' => $browser,
                'redirect_url' => $redirect_url,
                'priority' => $priority,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    public function update_rule($rule_id, $device_type, $device_os, $browser, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        return $wpdb->update(
            $table_name,
            array(
                'device_type' => $device_type,
                'device_os' => $device_os,
                'browser' => $browser,
                'redirect_url' => $redirect_url,
                'priority' => $priority
            ),
            array('id' => $rule_id)
        );
    }
    
    public function delete_rule($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        return $wpdb->delete($table_name, array('id' => $rule_id));
    }
    
    /**
     * Get rule details for the applied rule
     */
    public function get_rule_details($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        $device_info = get_user_meta($user_id, 'clr_last_device_info', true);
        
        if (!$device_info) {
            // If no stored device info, get current device info
            $device_info = $this->get_device_info();
        }
        
        // First check for device type + OS + browser specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name
            WHERE device_type = %s
            AND device_os = %s
            AND browser = %s
            ORDER BY priority DESC LIMIT 1",
            $device_info['device_type'],
            $device_info['device_os'],
            $device_info['browser']
        ));
        
        if (!$rule) {
            // Then check for device type + OS specific rules
            $rule = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE device_type = %s
                AND device_os = %s
                AND browser = 'any'
                ORDER BY priority DESC LIMIT 1",
                $device_info['device_type'],
                $device_info['device_os']
            ));
        }
        
        if (!$rule) {
            // Then check for device type specific rules
            $rule = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE device_type = %s
                AND device_os = 'any'
                AND browser = 'any'
                ORDER BY priority DESC LIMIT 1",
                $device_info['device_type']
            ));
        }
        
        if ($rule) {
            return array(
                'device_type' => $rule->device_type,
                'device_os' => $rule->device_os,
                'browser' => $rule->browser
            );
        }
        
        // If no rule found in database, return default rule details
        return array(
            'device_type' => $device_info['device_type'],
            'device_os' => 'any',
            'browser' => 'any'
        );
    }
}