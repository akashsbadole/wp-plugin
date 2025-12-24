<?php
class CLR_Referral_Based {
    public function __construct() {
        add_action('wp_login', array($this, 'capture_referral_info'), 10, 2);
        add_action('init', array($this, 'track_referral'), 1);
    }

    public function track_referral() {
        // Store referral information in session when user first visits
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['clr_referral_info']) && isset($_SERVER['HTTP_REFERER'])) {
            $referral_info = $this->get_referral_info($_SERVER['HTTP_REFERER']);
            $_SESSION['clr_referral_info'] = $referral_info;
        }
    }

    public function capture_referral_info($user_login, $user) {
        $user_id = $user->ID;
        
        // Get referral information
        if (isset($_SESSION['clr_referral_info'])) {
            $referral_info = $_SESSION['clr_referral_info'];
        } else {
            $referral_info = $this->get_referral_info($_SERVER['HTTP_REFERER'] ?? '');
        }
        
        // Store referral information for referral-based redirects
        update_user_meta($user_id, 'clr_last_referral_info', $referral_info);
    }

    private function get_referral_info($referer) {
        $referral_data = array(
            'source' => 'direct',
            'medium' => 'none',
            'campaign' => 'none',
            'domain' => '',
            'query_params' => array()
        );
        
        if (empty($referer)) {
            return $referral_data;
        }
        
        // Parse the referer URL
        $referer_url = parse_url($referer);
        
        if (isset($referer_url['host'])) {
            $referral_data['domain'] = $referer_url['host'];
            
            // Identify common search engines
            $search_engines = array(
                'google.com' => 'google',
                'bing.com' => 'bing',
                'yahoo.com' => 'yahoo',
                'duckduckgo.com' => 'duckduckgo',
                'baidu.com' => 'baidu',
                'yandex.com' => 'yandex'
            );
            
            foreach ($search_engines as $domain => $engine) {
                if (strpos($referral_data['domain'], $domain) !== false) {
                    $referral_data['source'] = $engine;
                    $referral_data['medium'] = 'organic';
                    break;
                }
            }
            
            // Identify common social media platforms
            $social_platforms = array(
                'facebook.com' => 'facebook',
                'twitter.com' => 'twitter',
                'instagram.com' => 'instagram',
                'linkedin.com' => 'linkedin',
                'pinterest.com' => 'pinterest',
                'youtube.com' => 'youtube'
            );
            
            foreach ($social_platforms as $domain => $platform) {
                if (strpos($referral_data['domain'], $domain) !== false) {
                    $referral_data['source'] = $platform;
                    $referral_data['medium'] = 'social';
                    break;
                }
            }
            
            // Extract UTM parameters if present
            if (isset($referer_url['query'])) {
                parse_str($referer_url['query'], $query_params);
                $referral_data['query_params'] = $query_params;
                
                if (isset($query_params['utm_source'])) {
                    $referral_data['source'] = $query_params['utm_source'];
                }
                
                if (isset($query_params['utm_medium'])) {
                    $referral_data['medium'] = $query_params['utm_medium'];
                }
                
                if (isset($query_params['utm_campaign'])) {
                    $referral_data['campaign'] = $query_params['utm_campaign'];
                }
            }
        }
        
        return $referral_data;
    }

    public function get_redirect($user_id) {
        $referral_info = get_user_meta($user_id, 'clr_last_referral_info', true);
        
        if (!$referral_info) {
            return false;
        }
        
        // Check for referral-specific redirect rules
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        // First check for source + medium + campaign specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE source = %s 
            AND medium = %s 
            AND campaign = %s
            ORDER BY priority DESC LIMIT 1",
            $referral_info['source'],
            $referral_info['medium'],
            $referral_info['campaign']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Then check for source + medium specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE source = %s 
            AND medium = %s 
            AND campaign = 'any'
            ORDER BY priority DESC LIMIT 1",
            $referral_info['source'],
            $referral_info['medium']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Then check for source specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name 
            WHERE source = %s 
            AND medium = 'any' 
            AND campaign = 'any'
            ORDER BY priority DESC LIMIT 1",
            $referral_info['source']
        ));
        
        if ($rule) {
            return $rule->redirect_url;
        }
        
        // Default referral-based redirects
        if ($referral_info['medium'] === 'organic') {
            return home_url('/welcome-search-visitor');
        } elseif ($referral_info['medium'] === 'social') {
            return home_url('/welcome-social-visitor');
        } elseif (in_array($referral_info['source'], array('google', 'bing', 'yahoo'))) {
            return home_url('/welcome-search-engine');
        } elseif (in_array($referral_info['source'], array('facebook', 'twitter', 'instagram'))) {
            return home_url('/welcome-social-media');
        }
        
        return false;
    }
    
    public function get_all_rules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, source, medium, campaign");
    }
    
    public function add_rule($source, $medium, $campaign, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'source' => $source,
                'medium' => $medium,
                'campaign' => $campaign,
                'redirect_url' => $redirect_url,
                'priority' => $priority,
                'created_at' => current_time('mysql')
            )
        );
    }
    
    public function update_rule($rule_id, $source, $medium, $campaign, $redirect_url, $priority = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        return $wpdb->update(
            $table_name,
            array(
                'source' => $source,
                'medium' => $medium,
                'campaign' => $campaign,
                'redirect_url' => $redirect_url,
                'priority' => $priority
            ),
            array('id' => $rule_id)
        );
    }
    
    public function delete_rule($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        return $wpdb->delete($table_name, array('id' => $rule_id));
    }
    
    /**
     * Get rule details for the applied rule
     */
    public function get_rule_details($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        $referral_info = get_user_meta($user_id, 'clr_last_referral_info', true);
        
        if (!$referral_info) {
            return array(
                'source' => 'direct',
                'medium' => 'none',
                'campaign' => 'none'
            );
        }
        
        // First check for source + medium + campaign specific rules
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name
            WHERE source = %s
            AND medium = %s
            AND campaign = %s
            ORDER BY priority DESC LIMIT 1",
            $referral_info['source'],
            $referral_info['medium'],
            $referral_info['campaign']
        ));
        
        if (!$rule) {
            // Then check for source + medium specific rules
            $rule = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE source = %s
                AND medium = %s
                AND campaign = 'any'
                ORDER BY priority DESC LIMIT 1",
                $referral_info['source'],
                $referral_info['medium']
            ));
        }
        
        if (!$rule) {
            // Then check for source specific rules
            $rule = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name
                WHERE source = %s
                AND medium = 'any'
                AND campaign = 'any'
                ORDER BY priority DESC LIMIT 1",
                $referral_info['source']
            ));
        }
        
        if ($rule) {
            return array(
                'source' => $rule->source,
                'medium' => $rule->medium,
                'campaign' => $rule->campaign
            );
        }
        
        // If no rule found in database, return default rule details
        return array(
            'source' => $referral_info['source'],
            'medium' => $referral_info['medium'],
            'campaign' => $referral_info['campaign']
        );
    }
}