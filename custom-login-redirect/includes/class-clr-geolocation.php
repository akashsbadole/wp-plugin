<?php
class CLR_Geolocation {
    public function __construct() {
        add_action('wp_login', array($this, 'capture_location'), 10, 2);
    }

    public function capture_location($user_login, $user) {
        $user_id = $user->ID;
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Get location data (using a free service as example)
        $location_data = $this->get_location_from_ip($ip);
        
        if ($location_data) {
            update_user_meta($user_id, 'clr_user_location', $location_data);
        }
    }

    private function get_location_from_ip($ip) {
        // Get the API token from settings
        $token = get_option('clr_geo_api_token', '');
        
        // If no token is set, return false
        if (empty($token)) {
            return false;
        }
        
        $url = "https://ipinfo.io/{$ip}/json?token={$token}";
        
        $response = wp_remote_get($url);
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['city'], $data['region'], $data['country'])) {
                return array(
                    'city' => $data['city'],
                    'region' => $data['region'],
                    'country' => $data['country'],
                    'loc' => $data['loc'] ?? null
                );
            }
        }
        
        return false;
    }

    public function get_redirect($user_id) {
        $location = get_user_meta($user_id, 'clr_user_location', true);
        
        if (!$location) {
            return false;
        }
        
        // Check for location-specific redirect rules
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE conditions LIKE %s 
            ORDER BY id DESC LIMIT 1",
            '%' . $location['city'] . '%'
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Default location-based redirects
        if ($location['country'] === 'IN') {
            // India-specific redirects
            if (in_array($location['city'], ['Mumbai', 'Delhi', 'Bangalore'])) {
                return home_url('/metro-offers');
            } else {
                return home_url('/regional-offers');
            }
        }
        
        return false;
    }
    
    /**
     * Get user location data
     *
     * @param int $user_id The user ID
     * @return array|false Location data or false if not available
     */
    public function get_user_location($user_id) {
        $location = get_user_meta($user_id, 'clr_user_location', true);
        
        if (empty($location)) {
            // If no location data exists, try to capture it
            $ip = $this->get_user_ip();
            $location_data = $this->get_location_from_ip($ip);
            
            if ($location_data) {
                update_user_meta($user_id, 'clr_user_location', $location_data);
                return $location_data;
            }
            
            return false;
        }
        
        return $location;
    }
    
    /**
     * Get user IP address
     *
     * @return string The user's IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}