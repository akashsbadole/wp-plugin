<?php
class CLR_Conditional {
    public function __construct() {
        // No specific hooks needed here, functionality is called from main redirect class
    }

    public function get_redirect($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        // Get all active conditional rules, ordered by priority
        $rules = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active' ORDER BY priority DESC");
        
        if (empty($rules)) {
            return false;
        }
        
        // Get user info
        $user_info = get_userdata($user_id);
        $user_role = isset($user_info->roles[0]) ? $user_info->roles[0] : 'subscriber';
        
        // Get device info
        $device_info = $this->get_device_info();
        
        // Get referral info
        $referral_info = $this->get_referral_info();
        
        // Get time info
        $time_info = $this->get_time_info();
        
        // Get geolocation info if enabled
        $geo_info = array();
        if (get_option('clr_geo_enabled')) {
            $geo = new CLR_Geolocation();
            $geo_info = $geo->get_user_location($user_id);
        }
        
        // Check each rule
        foreach ($rules as $rule) {
            $conditions = json_decode($rule->conditions, true);
            
            if ($this->evaluate_conditions($conditions, $user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info)) {
                return $rule->redirect_url;
            }
        }
        
        return false;
    }
    
    private function evaluate_conditions($conditions, $user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info) {
        if (empty($conditions)) {
            return false;
        }
        
        // Get the logical operator (AND/OR)
        $operator = isset($conditions['operator']) ? $conditions['operator'] : 'AND';
        
        // Evaluate each condition group
        $results = array();
        
        if (isset($conditions['groups']) && is_array($conditions['groups'])) {
            foreach ($conditions['groups'] as $group) {
                $group_operator = isset($group['operator']) ? $group['operator'] : 'AND';
                $group_results = array();
                
                if (isset($group['conditions']) && is_array($group['conditions'])) {
                    foreach ($group['conditions'] as $condition) {
                        $result = $this->evaluate_single_condition($condition, $user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info);
                        $group_results[] = $result;
                        
                        // Short-circuit evaluation
                        if ($group_operator === 'AND' && !$result) {
                            break; // One false is enough for AND
                        } elseif ($group_operator === 'OR' && $result) {
                            break; // One true is enough for OR
                        }
                    }
                }
                
                // Evaluate group results based on group operator
                if ($group_operator === 'AND') {
                    $group_result = !in_array(false, $group_results);
                } else { // OR
                    $group_result = in_array(true, $group_results);
                }
                
                $results[] = $group_result;
                
                // Short-circuit evaluation
                if ($operator === 'AND' && !$group_result) {
                    break; // One false is enough for AND
                } elseif ($operator === 'OR' && $group_result) {
                    break; // One true is enough for OR
                }
            }
        }
        
        // Evaluate final results based on operator
        if ($operator === 'AND') {
            return !in_array(false, $results);
        } else { // OR
            return in_array(true, $results);
        }
    }
    
    private function evaluate_single_condition($condition, $user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info) {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        
        switch ($field) {
            // User fields
            case 'user_role':
                return $this->compare_values($user_role, $operator, $value);
                
            case 'user_id':
                return $this->compare_values($user_id, $operator, intval($value));
                
            case 'user_registered':
                $user_data = get_userdata($user_id);
                $registered_date = strtotime($user_data->user_registered);
                $days_ago = (time() - $registered_date) / (60 * 60 * 24);
                return $this->compare_values($days_ago, $operator, intval($value));
                
            case 'user_post_count':
                $post_count = count_user_posts($user_id);
                return $this->compare_values($post_count, $operator, intval($value));
                
            // Device fields
            case 'device_type':
                return $this->compare_values($device_info['device_type'], $operator, $value);
                
            case 'device_os':
                return $this->compare_values($device_info['device_os'], $operator, $value);
                
            case 'browser':
                return $this->compare_values($device_info['browser'], $operator, $value);
                
            // Referral fields
            case 'referral_source':
                return $this->compare_values($referral_info['source'], $operator, $value);
                
            case 'referral_medium':
                return $this->compare_values($referral_info['medium'], $operator, $value);
                
            case 'referral_campaign':
                return $this->compare_values($referral_info['campaign'], $operator, $value);
                
            // Time fields
            case 'time_hour':
                return $this->compare_values($time_info['hour'], $operator, intval($value));
                
            case 'time_day':
                return $this->compare_values($time_info['day_of_week'], $operator, $value);
                
            case 'time_month':
                return $this->compare_values($time_info['month'], $operator, intval($value));
                
            // Geolocation fields
            case 'geo_country':
                if (empty($geo_info)) return false;
                return $this->compare_values($geo_info['country'], $operator, $value);
                
            case 'geo_region':
                if (empty($geo_info)) return false;
                return $this->compare_values($geo_info['region'], $operator, $value);
                
            case 'geo_city':
                if (empty($geo_info)) return false;
                return $this->compare_values($geo_info['city'], $operator, $value);
                
            // Custom fields
            case 'custom_field':
                $custom_value = get_user_meta($user_id, $value['field'], true);
                return $this->compare_values($custom_value, $operator, $value['value']);
                
            default:
                return false;
        }
    }
    
    private function compare_values($actual, $operator, $expected) {
        switch ($operator) {
            case 'equals':
                return $actual == $expected;
                
            case 'not_equals':
                return $actual != $expected;
                
            case 'contains':
                return strpos($actual, $expected) !== false;
                
            case 'not_contains':
                return strpos($actual, $expected) === false;
                
            case 'starts_with':
                return strpos($actual, $expected) === 0;
                
            case 'ends_with':
                return substr($actual, -strlen($expected)) === $expected;
                
            case 'greater_than':
                return $actual > $expected;
                
            case 'less_than':
                return $actual < $expected;
                
            case 'greater_than_or_equal':
                return $actual >= $expected;
                
            case 'less_than_or_equal':
                return $actual <= $expected;
                
            case 'in':
                return is_array($expected) ? in_array($actual, $expected) : false;
                
            case 'not_in':
                return is_array($expected) ? !in_array($actual, $expected) : true;
                
            case 'matches_regex':
                return preg_match('/' . str_replace('/', '\/', $expected) . '/', $actual) === 1;
                
            default:
                return false;
        }
    }
    
    public function get_all_rules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, name");
    }
    
    public function get_rule($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rule_id));
    }
    
    public function add_rule($name, $description, $conditions, $redirect_url, $priority = 10, $status = 'active') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'description' => $description,
                'conditions' => json_encode($conditions),
                'redirect_url' => $redirect_url,
                'priority' => $priority,
                'status' => $status,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            )
        );
    }
    
    public function update_rule($rule_id, $name, $description, $conditions, $redirect_url, $priority = 10, $status = 'active') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->update(
            $table_name,
            array(
                'name' => $name,
                'description' => $description,
                'conditions' => json_encode($conditions),
                'redirect_url' => $redirect_url,
                'priority' => $priority,
                'status' => $status,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $rule_id)
        );
    }
    
    public function delete_rule($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->delete($table_name, array('id' => $rule_id));
    }
    
    public function toggle_rule_status($rule_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        $rule = $this->get_rule($rule_id);
        if (!$rule) {
            return false;
        }
        
        $new_status = $rule->status === 'active' ? 'inactive' : 'active';
        
        return $wpdb->update(
            $table_name,
            array(
                'status' => $new_status,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $rule_id)
        );
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
    
    private function get_referral_info() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
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
    
    private function get_time_info() {
        $current_time = current_time('timestamp');
        
        return array(
            'hour' => (int)date('H', $current_time),
            'day_of_week' => strtolower(date('l', $current_time)),
            'day_of_month' => (int)date('j', $current_time),
            'month' => (int)date('n', $current_time),
            'year' => (int)date('Y', $current_time)
        );
    }
    
    /**
     * Get rule details for the applied rule
     */
    public function get_rule_details($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        // Get all active conditional rules, ordered by priority
        $rules = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active' ORDER BY priority DESC");
        
        if (empty($rules)) {
            return array();
        }
        
        // Get user info
        $user_info = get_userdata($user_id);
        $user_role = isset($user_info->roles[0]) ? $user_info->roles[0] : 'subscriber';
        
        // Get device info
        $device_info = $this->get_device_info();
        
        // Get referral info
        $referral_info = $this->get_referral_info();
        
        // Get time info
        $time_info = $this->get_time_info();
        
        // Get geolocation info if enabled
        $geo_info = array();
        if (get_option('clr_geo_enabled')) {
            $geo = new CLR_Geolocation();
            $geo_info = $geo->get_user_location($user_id);
        }
        
        // Check each rule
        foreach ($rules as $rule) {
            $conditions = json_decode($rule->conditions, true);
            
            if ($this->evaluate_conditions($conditions, $user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info)) {
                return array(
                    'name' => $rule->name,
                    'description' => $rule->description
                );
            }
        }
        
        return array();
    }
}