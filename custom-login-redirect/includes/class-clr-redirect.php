<?php
class CLR_Redirect {
    public function __construct() {
        add_filter('login_redirect', array($this, 'handle_redirect'), 10, 3);
        add_action('wp_login', array($this, 'track_user_login'), 10, 2);
    }

    public function handle_redirect($redirect_to, $request, $user) {
        if (!isset($user->roles) || empty($user->roles)) {
            return $redirect_to;
        }

        $role = $user->roles[0];
        $user_id = $user->ID;
        $redirect_type = 'basic';
        $rule_details = array('role' => $role);
        
        // Get base redirect rule
        $redirect_url = $this->get_role_redirect($role);
        
        // Apply AI suggestions if enabled
        if (get_option('clr_ai_enabled')) {
            $ai = new CLR_AI();
            $ai_suggestion = $ai->get_suggestion($user_id);
            if ($ai_suggestion) {
                $redirect_url = $ai_suggestion;
                $redirect_type = 'ai';
                $rule_details = array('type' => 'ai');
            }
        }
        
        // Apply geolocation if enabled
        if (get_option('clr_geo_enabled')) {
            $geo = new CLR_Geolocation();
            $geo_redirect = $geo->get_redirect($user_id);
            if ($geo_redirect) {
                $redirect_url = $geo_redirect;
                $redirect_type = 'geo';
                $rule_details = array('type' => 'geo');
            }
        }
        
        // Apply A/B testing if enabled
        if (get_option('clr_ab_enabled')) {
            $ab_test = new CLR_AB_Testing();
            $ab_redirect = $ab_test->get_redirect($role);
            if ($ab_redirect) {
                $redirect_url = $ab_redirect;
                $redirect_type = 'ab_testing';
                $rule_details = array('role' => $role);
            }
        }
        
        // Apply Time-Based redirects if enabled
        if (get_option('clr_time_based_enabled')) {
            $time_based = new CLR_Time_Based();
            $time_redirect = $time_based->get_redirect($user_id);
            if ($time_redirect) {
                $redirect_url = $time_redirect;
                $redirect_type = 'time_based';
                $rule_details = $time_based->get_rule_details($user_id);
            }
        }
        
        // Apply Device-Based redirects if enabled
        if (get_option('clr_device_based_enabled')) {
            $device_based = new CLR_Device_Based();
            $device_redirect = $device_based->get_redirect($user_id);
            if ($device_redirect) {
                $redirect_url = $device_redirect;
                $redirect_type = 'device_based';
                $rule_details = $device_based->get_rule_details($user_id);
            }
        }
        
        // Apply Referral-Based redirects if enabled
        if (get_option('clr_referral_based_enabled')) {
            $referral_based = new CLR_Referral_Based();
            $referral_redirect = $referral_based->get_redirect($user_id);
            if ($referral_redirect) {
                $redirect_url = $referral_redirect;
                $redirect_type = 'referral_based';
                $rule_details = $referral_based->get_rule_details($user_id);
            }
        }
        
        // Apply Conditional redirects if enabled
        if (get_option('clr_conditional_enabled')) {
            $conditional = new CLR_Conditional();
            $conditional_redirect = $conditional->get_redirect($user_id);
            if ($conditional_redirect) {
                $redirect_url = $conditional_redirect;
                $redirect_type = 'conditional';
                $rule_details = $conditional->get_rule_details($user_id);
            }
        }
        
        // Add tracking parameter to redirect URL if analytics is enabled
        if (get_option('clr_analytics_enabled') && $redirect_url) {
            $redirect_url = add_query_arg(array('clr_redirect' => '1', 'clr_type' => $redirect_type), $redirect_url);
        }
        
        // Trigger action for email notifications if a redirect is applied
        if ($redirect_url && $redirect_url !== $redirect_to) {
            do_action('clr_user_redirected', $user_id, $redirect_url, $redirect_type, $rule_details);
        }
        
        return $redirect_url ? $redirect_url : $redirect_to;
    }

    private function get_role_redirect($role) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name WHERE role = %s ORDER BY id DESC LIMIT 1",
            $role
        ));
        
        return $rule ? $rule->redirect_url : false;
    }

    public function track_user_login($user_login, $user) {
        // Track login for analytics
        $data = array(
            'user_id' => $user->ID,
            'role' => $user->roles[0],
            'timestamp' => current_time('mysql'),
            'ip' => $_SERVER['REMOTE_ADDR']
        );
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_user_logins';
        $wpdb->insert($table_name, $data);
    }
}