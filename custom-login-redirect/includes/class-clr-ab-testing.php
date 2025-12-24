<?php
class CLR_AB_Testing {
    public function __construct() {
        add_action('wp_login', array($this, 'record_ab_test'), 10, 2);
        add_action('template_redirect', array($this, 'track_conversion'));
    }

    public function record_ab_test($user_login, $user) {
        $role = $user->roles[0];
        $user_id = $user->ID;
        
        // Get A/B test variants for this role
        $variants = $this->get_variants($role);
        
        if (!empty($variants)) {
            // Select random variant
            $selected = $variants[array_rand($variants)];
            
            // Record selection
            global $wpdb;
            $table_name = $wpdb->prefix . 'clr_ab_tests';
            
            $wpdb->insert($table_name, array(
                'user_id' => $user_id,
                'role' => $role,
                'variant_id' => $selected['id'],
                'redirect_url' => $selected['redirect_url'],
                'converted' => 0,
                'created_at' => current_time('mysql')
            ));
            
            // Store in user session
            $_SESSION['clr_ab_test_id'] = $wpdb->insert_id;
        }
    }

    private function get_variants($role) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_ab_variants';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE role = %s",
            $role
        ), ARRAY_A);
    }

    public function get_redirect($role) {
        if (!isset($_SESSION['clr_ab_test_id'])) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_ab_tests';
        
        $test = $wpdb->get_row($wpdb->prepare(
            "SELECT redirect_url FROM $table_name WHERE id = %d",
            $_SESSION['clr_ab_test_id']
        ));
        
        return $test ? $test->redirect_url : false;
    }

    public function track_conversion() {
        if (!isset($_SESSION['clr_ab_test_id'])) {
            return;
        }
        
        // Simple conversion tracking (e.g., if user visits a specific page)
        if (is_page('thank-you') || is_page('order-received')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'clr_ab_tests';
            
            $wpdb->update(
                $table_name,
                array('converted' => 1),
                array('id' => $_SESSION['clr_ab_test_id'])
            );
            
            unset($_SESSION['clr_ab_test_id']);
        }
    }
}