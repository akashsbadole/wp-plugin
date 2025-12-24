<?php
class CLR_AI {
    public function __construct() {
        add_action('wp_login', array($this, 'track_user_behavior'), 10, 2);
    }

    public function track_user_behavior($user_login, $user) {
        // Track user behavior for AI analysis
        $user_id = $user->ID;
        
        // Get user's last 5 login locations
        $behavior_data = $this->get_user_behavior_data($user_id);
        
        // Send to AI service (placeholder)
        $this->send_to_ai_service($user_id, $behavior_data);
    }

    private function get_user_behavior_data($user_id) {
        global $wpdb;
        
        // Get page visits
        $visits = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, COUNT(*) as visit_count 
            FROM {$wpdb->prefix}clr_page_visits 
            WHERE user_id = %d 
            GROUP BY post_id 
            ORDER BY visit_count DESC 
            LIMIT 5",
            $user_id
        ));
        
        // Get purchase history (if WooCommerce exists)
        $purchases = array();
        if (class_exists('WooCommerce')) {
            $orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'limit' => 5,
                'status' => 'completed'
            ));
            
            foreach ($orders as $order) {
                $purchases[] = array(
                    'total' => $order->get_total(),
                    'items' => count($order->get_items())
                );
            }
        }
        
        return array(
            'visits' => $visits,
            'purchases' => $purchases
        );
    }

    private function send_to_ai_service($user_id, $data) {
        // In a real implementation, this would send data to an AI service
        // For now, we'll simulate with a simple algorithm
        
        $suggested_url = home_url();
        
        // If user has visited shop page frequently, redirect to shop
        if (!empty($data['visits'])) {
            foreach ($data['visits'] as $visit) {
                $post = get_post($visit->post_id);
                if ($post && $post->post_type === 'product') {
                    $suggested_url = get_permalink(wc_get_page_id('shop'));
                    break;
                }
            }
        }
        
        // Save suggestion
        update_user_meta($user_id, 'clr_ai_suggestion', $suggested_url);
    }

    public function get_suggestion($user_id) {
        return get_user_meta($user_id, 'clr_ai_suggestion', true);
    }
}