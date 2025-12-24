<?php
class CLR_Time_Based {
    public function __construct() {
        add_action('wp_login', array($this, 'capture_login_time'), 10, 2);
    }

    public function capture_login_time($user_login, $user) {
        $user_id = $user->ID;
        $current_time = current_time('timestamp');
        
        // Store login time for time-based redirects
        update_user_meta($user_id, 'clr_last_login_time', $current_time);
    }

    public function get_redirect($user_id) {
        $login_time = get_user_meta($user_id, 'clr_last_login_time', true);
        
        if (!$login_time) {
            return false;
        }
        
        // Get current time in WordPress timezone
        $current_time = current_time('timestamp');
        $current_hour = (int)date('H', $current_time);
        $current_day = strtolower(date('l', $current_time));
        
        // Check for time-based redirect rules
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        // First check for day-specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE day_of_week = %s 
            AND start_hour <= %d 
            AND end_hour > %d
            ORDER BY priority DESC LIMIT 1",
            $current_day,
            $current_hour,
            $current_hour
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Then check for general time-based rules (any day)
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE day_of_week = 'any' 
            AND start_hour <= %d 
            AND end_hour > %d
            ORDER BY priority DESC LIMIT 1",
            $current_hour,
            $current_hour
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Default time-based redirects
        if ($current_hour >= 9 && $current_hour < 17) {
            // Business hours
            return home_url('/business-hours');
        } elseif ($current_hour >= 17 && $current_hour < 22) {
            // Evening
            return home_url('/evening-content');
        } else {
            // Night/Early morning
            return home_url('/after-hours');
        }
    }
    
    public function get_all_rules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, day_of_week, start_hour");
    }
    
    public function add_rule($day_of_week, $start_hour, $end_hour, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'day_of_week' => $day_of_week,
                'start_hour' => $start_hour,
                'end_hour' => $end_hour,
                'redirect_url' => $redirect_url,
                'priority' => $priority,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    public function update_rule($rule_id, $day_of_week, $start_hour, $end_hour, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        return $wpdb->update(
            $table_name,
            array(
                'day_of_week' => $day_of_week,
                'start_hour' => $start_hour,
                'end_hour' => $end_hour,
                'redirect_url' => $redirect_url,
                'priority' => $priority
            ),
            array('id' => $rule_id)
        );
    }
    
    public function delete_rule($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        return $wpdb->delete($table_name, array('id' => $rule_id));
    }
    
    /**
     * Get rule details for the applied rule
     */
    public function get_rule_details($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        // Get current time details
        $current_time = current_time('timestamp');
        $current_hour = (int)date('H', $current_time);
        $current_day = strtolower(date('l', $current_time));
        
        // First check for day-specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name
            WHERE day_of_week = %s
            AND start_hour <= %d
            AND end_hour > %d
            ORDER BY priority DESC LIMIT 1",
            $current_day,
            $current_hour,
            $current_hour
        ));
        
        if (!$rule) {
            // Then check for general time-based rules (any day)
            $rule = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE day_of_week = 'any'
                AND start_hour <= %d
                AND end_hour > %d
                ORDER BY priority DESC LIMIT 1",
                $current_hour,
                $current_hour
            ));
        }
        
        if ($rule) {
            return array(
                'day_of_week' => $rule->day_of_week,
                'start_hour' => $rule->start_hour,
                'end_hour' => $rule->end_hour
            );
        }
        
        // If no rule found in database, return default rule details
        return array(
            'day_of_week' => 'any',
            'start_hour' => 0,
            'end_hour' => 24
        );
    }
}