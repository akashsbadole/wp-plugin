<?php
class CLR_Analytics {
    public function __construct() {
        add_action('wp_login', array($this, 'track_login'), 10, 2);
        add_action('wp_footer', array($this, 'track_page_view'));
        add_action('template_redirect', array($this, 'track_redirect'));
        
        // Check if analytics table exists, create if missing
        add_action('init', array($this, 'check_and_create_table'));
    }
    
    /**
     * Check if analytics table exists and create it if missing
     */
    public function check_and_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if ($table_exists != $table_name) {
            // Include install functions
            require_once(CLR_PLUGIN_DIR . 'includes/install.php');
            
            // Try to create the missing table
            clr_create_table_if_not_exists('clr_analytics_events');
        }
    }

    public function track_login($user_login, $user) {
        $user_id = $user->ID;
        
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
        
        // Create session ID
        $session_id = $this->generate_session_id();
        
        // Store session info
        update_user_meta($user_id, 'clr_current_session', $session_id);
        
        // Track login event
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists before trying to insert
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists == $table_name) {
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'session_id' => $session_id,
                    'event_type' => 'login',
                    'event_data' => json_encode(array(
                        'user_role' => $user_role,
                        'device' => $device_info,
                        'referral' => $referral_info,
                        'time' => $time_info,
                        'geo' => $geo_info
                    )),
                    'timestamp' => current_time('mysql'),
                    'ip_address' => $this->get_user_ip()
                )
            );
        }
    }
    
    public function track_redirect() {
        // Check if this is a redirect from our plugin
        if (isset($_GET['clr_redirect']) && $_GET['clr_redirect'] == '1' && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $session_id = get_user_meta($user_id, 'clr_current_session', true);
            
            if ($session_id) {
                $redirect_url = $_SERVER['REQUEST_URI'];
                $redirect_type = isset($_GET['clr_type']) ? sanitize_text_field($_GET['clr_type']) : 'default';
                
                // Track redirect event
                global $wpdb;
                $table_name = $wpdb->prefix . 'clr_analytics_events';
                
                // Check if table exists before trying to insert
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                if ($table_exists == $table_name) {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'user_id' => $user_id,
                            'session_id' => $session_id,
                            'event_type' => 'redirect',
                            'event_data' => json_encode(array(
                                'redirect_url' => $redirect_url,
                                'redirect_type' => $redirect_type
                            )),
                            'timestamp' => current_time('mysql'),
                            'ip_address' => $this->get_user_ip()
                        )
                    );
                }
            }
        }
    }
    
    public function track_page_view() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $user_id = get_current_user_id();
        $session_id = get_user_meta($user_id, 'clr_current_session', true);
        
        if ($session_id) {
            $page_url = $_SERVER['REQUEST_URI'];
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            
            // Track page view event
            global $wpdb;
            $table_name = $wpdb->prefix . 'clr_analytics_events';
            
            // Check if table exists before trying to insert
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
            if ($table_exists == $table_name) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'user_id' => $user_id,
                        'session_id' => $session_id,
                        'event_type' => 'page_view',
                        'event_data' => json_encode(array(
                            'page_url' => $page_url,
                            'post_id' => $post_id,
                            'post_type' => $post_type
                        )),
                        'timestamp' => current_time('mysql'),
                        'ip_address' => $this->get_user_ip()
                    )
                );
            }
        }
    }
    
    public function get_user_analytics($user_id, $days = 30) {
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists before trying to query
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$events_table'");
        if ($table_exists != $events_table) {
            // Return empty analytics if table doesn't exist
            return array(
                'total_sessions' => 0,
                'total_page_views' => 0,
                'total_redirects' => 0,
                'average_session_duration' => 0,
                'most_visited_pages' => array(),
                'device_types' => array(),
                'referral_sources' => array(),
                'login_times' => array(),
                'geo_locations' => array()
            );
        }
        
        // Get events for the specified time period
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $events_table
            WHERE user_id = %d
            AND timestamp >= %s
            ORDER BY timestamp ASC",
            $user_id, $start_date
        ));
        
        // Process events to generate analytics
        $analytics = array(
            'total_sessions' => 0,
            'total_page_views' => 0,
            'total_redirects' => 0,
            'average_session_duration' => 0,
            'most_visited_pages' => array(),
            'device_types' => array(),
            'referral_sources' => array(),
            'login_times' => array(),
            'geo_locations' => array()
        );
        
        $sessions = array();
        $page_views = array();
        $redirects = array();
        $session_start_times = array();
        
        foreach ($events as $event) {
            $event_data = json_decode($event->event_data, true);
            
            switch ($event->event_type) {
                case 'login':
                    $sessions[$event->session_id] = $event;
                    $session_start_times[$event->session_id] = strtotime($event->timestamp);
                    
                    // Extract device info
                    if (isset($event_data['device']['device_type'])) {
                        $device_type = $event_data['device']['device_type'];
                        if (!isset($analytics['device_types'][$device_type])) {
                            $analytics['device_types'][$device_type] = 0;
                        }
                        $analytics['device_types'][$device_type]++;
                    }
                    
                    // Extract referral info
                    if (isset($event_data['referral']['source'])) {
                        $source = $event_data['referral']['source'];
                        if (!isset($analytics['referral_sources'][$source])) {
                            $analytics['referral_sources'][$source] = 0;
                        }
                        $analytics['referral_sources'][$source]++;
                    }
                    
                    // Extract time info
                    if (isset($event_data['time']['hour'])) {
                        $hour = $event_data['time']['hour'];
                        if (!isset($analytics['login_times'][$hour])) {
                            $analytics['login_times'][$hour] = 0;
                        }
                        $analytics['login_times'][$hour]++;
                    }
                    
                    // Extract geo info
                    if (isset($event_data['geo']['country'])) {
                        $country = $event_data['geo']['country'];
                        if (!isset($analytics['geo_locations'][$country])) {
                            $analytics['geo_locations'][$country] = 0;
                        }
                        $analytics['geo_locations'][$country]++;
                    }
                    break;
                    
                case 'page_view':
                    if (isset($sessions[$event->session_id])) {
                        $page_views[] = $event;
                        
                        // Track most visited pages
                        if (isset($event_data['page_url'])) {
                            $page_url = $event_data['page_url'];
                            if (!isset($analytics['most_visited_pages'][$page_url])) {
                                $analytics['most_visited_pages'][$page_url] = 0;
                            }
                            $analytics['most_visited_pages'][$page_url]++;
                        }
                    }
                    break;
                    
                case 'redirect':
                    if (isset($sessions[$event->session_id])) {
                        $redirects[] = $event;
                    }
                    break;
            }
        }
        
        // Calculate analytics metrics
        $analytics['total_sessions'] = count($sessions);
        $analytics['total_page_views'] = count($page_views);
        $analytics['total_redirects'] = count($redirects);
        
        // Calculate average session duration
        $total_duration = 0;
        $valid_sessions = 0;
        
        foreach ($sessions as $session_id => $login_event) {
            $login_time = strtotime($login_event->timestamp);
            $last_event_time = $login_time;
            
            // Find the last event for this session
            foreach ($events as $event) {
                if ($event->session_id === $session_id) {
                    $event_time = strtotime($event->timestamp);
                    if ($event_time > $last_event_time) {
                        $last_event_time = $event_time;
                    }
                }
            }
            
            $duration = $last_event_time - $login_time;
            if ($duration > 0 && $duration < 86400) { // Less than 24 hours
                $total_duration += $duration;
                $valid_sessions++;
            }
        }
        
        if ($valid_sessions > 0) {
            $analytics['average_session_duration'] = $total_duration / $valid_sessions;
        }
        
        // Sort arrays by count (descending)
        arsort($analytics['most_visited_pages']);
        arsort($analytics['device_types']);
        arsort($analytics['referral_sources']);
        arsort($analytics['login_times']);
        arsort($analytics['geo_locations']);
        
        return $analytics;
    }
    
    public function get_redirect_analytics($days = 30) {
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists before trying to query
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$events_table'");
        if ($table_exists != $events_table) {
            // Return empty analytics if table doesn't exist
            return array(
                'total_redirects' => 0,
                'redirects_by_type' => array(),
                'redirects_by_url' => array(),
                'redirects_by_user_role' => array(),
                'conversions_by_redirect' => array()
            );
        }
        
        // Get redirect events for the specified time period
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $redirect_events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $events_table
            WHERE event_type = 'redirect'
            AND timestamp >= %s
            ORDER BY timestamp ASC",
            $start_date
        ));
        
        // Process events to generate analytics
        $analytics = array(
            'total_redirects' => count($redirect_events),
            'redirects_by_type' => array(),
            'redirects_by_url' => array(),
            'redirects_by_user_role' => array(),
            'conversions_by_redirect' => array()
        );
        
        foreach ($redirect_events as $event) {
            $event_data = json_decode($event->event_data, true);
            
            // Track redirects by type
            if (isset($event_data['redirect_type'])) {
                $type = $event_data['redirect_type'];
                if (!isset($analytics['redirects_by_type'][$type])) {
                    $analytics['redirects_by_type'][$type] = 0;
                }
                $analytics['redirects_by_type'][$type]++;
            }
            
            // Track redirects by URL
            if (isset($event_data['redirect_url'])) {
                $url = $event_data['redirect_url'];
                if (!isset($analytics['redirects_by_url'][$url])) {
                    $analytics['redirects_by_url'][$url] = 0;
                }
                $analytics['redirects_by_url'][$url]++;
            }
            
            // Get user role for this redirect
            $user_info = get_userdata($event->user_id);
            if ($user_info && !empty($user_info->roles)) {
                $role = $user_info->roles[0];
                if (!isset($analytics['redirects_by_user_role'][$role])) {
                    $analytics['redirects_by_user_role'][$role] = 0;
                }
                $analytics['redirects_by_user_role'][$role]++;
            }
        }
        
        // Calculate conversion rates for each redirect URL
        foreach ($analytics['redirects_by_url'] as $url => $count) {
            // Get the number of sessions that visited this URL after redirect
            $conversions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT session_id) FROM $events_table 
                WHERE event_type = 'page_view' 
                AND event_data LIKE %s
                AND timestamp >= %s",
                '%' . $wpdb->esc_like($url) . '%',
                $start_date
            ));
            
            $conversion_rate = $count > 0 ? ($conversions / $count) * 100 : 0;
            $analytics['conversions_by_redirect'][$url] = array(
                'redirects' => $count,
                'conversions' => $conversions,
                'conversion_rate' => $conversion_rate
            );
        }
        
        // Sort arrays by count (descending)
        arsort($analytics['redirects_by_type']);
        arsort($analytics['redirects_by_url']);
        arsort($analytics['redirects_by_user_role']);
        
        // Sort conversions by conversion rate (descending)
        uasort($analytics['conversions_by_redirect'], function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });
        
        return $analytics;
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
    
    private function generate_session_id() {
        return md5(uniqid(rand(), true));
    }
    
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