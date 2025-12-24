<?php
/**
 * Third-party Integrations for Custom Login Redirect
 *
 * This class handles integrations with third-party services like
 * Google Analytics, Mailchimp, Slack, etc.
 */

class CLR_Integrations {
    public function __construct() {
        // Initialize integration features
        add_action('init', array($this, 'init_integration_features'));
        
        // Add AJAX handlers for integration features
        add_action('wp_ajax_clr_connect_ga', array($this, 'ajax_connect_ga'));
        add_action('wp_ajax_clr_disconnect_ga', array($this, 'ajax_disconnect_ga'));
        add_action('wp_ajax_clr_connect_mailchimp', array($this, 'ajax_connect_mailchimp'));
        add_action('wp_ajax_clr_disconnect_mailchimp', array($this, 'ajax_disconnect_mailchimp'));
        add_action('wp_ajax_clr_connect_slack', array($this, 'ajax_connect_slack'));
        add_action('wp_ajax_clr_disconnect_slack', array($this, 'ajax_disconnect_slack'));
        
        // Add hooks for data synchronization
        add_action('clr_user_redirected', array($this, 'track_redirect_in_ga'), 10, 4);
        add_action('wp_login', array($this, 'sync_user_to_mailchimp'), 10, 2);
        add_action('clr_user_redirected', array($this, 'send_slack_notification'), 10, 4);
    }
    
    /**
     * Initialize integration features if license is valid
     */
    public function init_integration_features() {
        if (clr_is_premium()) {
            // Add integration scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_integration_scripts'));
        }
    }
    
    /**
     * Enqueue integration scripts
     */
    public function enqueue_integration_scripts() {
        // Google Analytics tracking
        if (get_option('clr_ga_connected') && get_option('clr_ga_tracking_id')) {
            wp_enqueue_script('clr-ga-tracking', CLR_PLUGIN_URL . 'assets/js/ga-tracking.js', array(), CLR_VERSION, true);
            
            $tracking_id = get_option('clr_ga_tracking_id');
            wp_localize_script('clr-ga-tracking', 'clrGA', array(
                'trackingId' => $tracking_id
            ));
        }
    }
    
    /**
     * AJAX handler to connect Google Analytics
     */
    public function ajax_connect_ga() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $tracking_id = sanitize_text_field($_POST['tracking_id']);
        
        if (empty($tracking_id)) {
            wp_send_json_error(__('Please enter a valid tracking ID.', 'clr'));
        }
        
        // Validate tracking ID format (UA-XXXXXXXX-X or G-XXXXXXXXXX)
        if (!preg_match('/^(UA-\d{4,10}-\d{1,4}|G-\w{10})$/', $tracking_id)) {
            wp_send_json_error(__('Invalid tracking ID format.', 'clr'));
        }
        
        // Save options
        update_option('clr_ga_connected', 1);
        update_option('clr_ga_tracking_id', $tracking_id);
        
        wp_send_json_success(__('Google Analytics connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Google Analytics
     */
    public function ajax_disconnect_ga() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_ga_connected', 0);
        delete_option('clr_ga_tracking_id');
        
        wp_send_json_success(__('Google Analytics disconnected successfully.', 'clr'));
    }
    
    /**
     * Track redirect in Google Analytics
     */
    public function track_redirect_in_ga($user_id, $redirect_url, $redirect_type, $rule_details) {
        if (!get_option('clr_ga_connected') || !get_option('clr_ga_tracking_id')) {
            return;
        }
        
        // Get user info
        $user = get_userdata($user_id);
        $user_role = isset($user->roles[0]) ? $user->roles[0] : 'subscriber';
        
        // Track event
        $event_data = array(
            'event' => 'login_redirect',
            'event_category' => 'user_redirect',
            'event_action' => $redirect_type,
            'event_label' => $user_role,
            'redirect_url' => $redirect_url
        );
        
        // Store event data for tracking
        set_transient('clr_ga_event_' . $user_id, $event_data, 60);
    }
    
    /**
     * AJAX handler to connect Mailchimp
     */
    public function ajax_connect_mailchimp() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $list_id = sanitize_text_field($_POST['list_id']);
        
        if (empty($api_key) || empty($list_id)) {
            wp_send_json_error(__('Please enter both API key and list ID.', 'clr'));
        }
        
        // Validate API key by making a test request
        $response = $this->test_mailchimp_connection($api_key);
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Invalid API key. Please check and try again.', 'clr'));
        }
        
        // Save options
        update_option('clr_mailchimp_connected', 1);
        update_option('clr_mailchimp_api_key', $api_key);
        update_option('clr_mailchimp_list_id', $list_id);
        
        wp_send_json_success(__('Mailchimp connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Mailchimp
     */
    public function ajax_disconnect_mailchimp() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_mailchimp_connected', 0);
        delete_option('clr_mailchimp_api_key');
        delete_option('clr_mailchimp_list_id');
        
        wp_send_json_success(__('Mailchimp disconnected successfully.', 'clr'));
    }
    
    /**
     * Test Mailchimp connection
     */
    private function test_mailchimp_connection($api_key) {
        $data_center = substr($api_key, strpos($api_key, '-') + 1);
        $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/ping';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
            )
        ));
        
        return $response;
    }
    
    /**
     * Sync user to Mailchimp
     */
    public function sync_user_to_mailchimp($user_login, $user) {
        if (!get_option('clr_mailchimp_connected') || !get_option('clr_mailchimp_api_key')) {
            return;
        }
        
        $api_key = get_option('clr_mailchimp_api_key');
        $list_id = get_option('clr_mailchimp_list_id');
        
        // Get user data
        $user_data = array(
            'email_address' => $user->user_email,
            'status' => 'subscribed',
            'merge_fields' => array(
                'FNAME' => $user->first_name,
                'LNAME' => $user->last_name,
                'USERNAME' => $user->user_login
            )
        );
        
        // Get or create subscriber hash
        $subscriber_hash = md5(strtolower($user->user_email));
        
        // Prepare API request
        $data_center = substr($api_key, strpos($api_key, '-') + 1);
        $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $subscriber_hash;
        
        $response = wp_remote_put($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($user_data)
        ));
        
        // Log result
        if (is_wp_error($response)) {
            error_log('CLR Mailchimp sync error: ' . $response->get_error_message());
        }
    }
    
    /**
     * AJAX handler to connect Slack
     */
    public function ajax_connect_slack() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $webhook_url = esc_url_raw($_POST['webhook_url']);
        
        if (empty($webhook_url)) {
            wp_send_json_error(__('Please enter a valid webhook URL.', 'clr'));
        }
        
        // Validate webhook URL
        if (!filter_var($webhook_url, FILTER_VALIDATE_URL) || strpos($webhook_url, 'hooks.slack.com') === false) {
            wp_send_json_error(__('Invalid webhook URL.', 'clr'));
        }
        
        // Test webhook by sending a test message
        $test_message = array(
            'text' => 'Custom Login Redirect plugin connected successfully!',
            'attachments' => array(
                array(
                    'text' => 'You will now receive notifications for important user events.',
                    'color' => '#36a64f'
                )
            )
        );
        
        $response = wp_remote_post($webhook_url, array(
            'body' => json_encode($test_message),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(__('Failed to connect to Slack. Please check your webhook URL.', 'clr'));
        }
        
        // Save options
        update_option('clr_slack_connected', 1);
        update_option('clr_slack_webhook_url', $webhook_url);
        
        wp_send_json_success(__('Slack connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Slack
     */
    public function ajax_disconnect_slack() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_slack_connected', 0);
        delete_option('clr_slack_webhook_url');
        
        wp_send_json_success(__('Slack disconnected successfully.', 'clr'));
    }
    
    /**
     * Send Slack notification
     */
    public function send_slack_notification($user_id, $redirect_url, $redirect_type, $rule_details) {
        if (!get_option('clr_slack_connected') || !get_option('clr_slack_webhook_url')) {
            return;
        }
        
        // Get user info
        $user = get_userdata($user_id);
        $user_role = isset($user->roles[0]) ? $user->roles[0] : 'subscriber';
        
        // Prepare notification message
        $message = array(
            'text' => 'User Redirected',
            'attachments' => array(
                array(
                    'fallback' => 'User ' . $user->user_login . ' was redirected to ' . $redirect_url,
                    'color' => '#36a64f',
                    'author_name' => $user->display_name,
                    'author_icon' => get_avatar_url($user_id, array('size' => 32)),
                    'fields' => array(
                        array(
                            'title' => 'User',
                            'value' => $user->user_login,
                            'short' => true
                        ),
                        array(
                            'title' => 'Role',
                            'value' => $user_role,
                            'short' => true
                        ),
                        array(
                            'title' => 'Redirect Type',
                            'value' => ucwords(str_replace('_', ' ', $redirect_type)),
                            'short' => true
                        ),
                        array(
                            'title' => 'Destination',
                            'value' => $redirect_url,
                            'short' => false
                        )
                    ),
                    'footer' => 'Custom Login Redirect',
                    'ts' => time()
                )
            )
        );
        
        // Send notification
        $webhook_url = get_option('clr_slack_webhook_url');
        
        wp_remote_post($webhook_url, array(
            'body' => json_encode($message),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'blocking' => false // Send asynchronously
        ));
    }
    
    /**
     * Get integration status
     */
    public function get_integration_status($integration) {
        switch ($integration) {
            case 'ga':
                return get_option('clr_ga_connected', false);
            case 'mailchimp':
                return get_option('clr_mailchimp_connected', false);
            case 'slack':
                return get_option('clr_slack_connected', false);
            default:
                return false;
        }
    }
    
    /**
     * Get integration settings
     */
    public function get_integration_settings($integration) {
        switch ($integration) {
            case 'ga':
                return array(
                    'tracking_id' => get_option('clr_ga_tracking_id', '')
                );
            case 'mailchimp':
                return array(
                    'api_key' => get_option('clr_mailchimp_api_key', ''),
                    'list_id' => get_option('clr_mailchimp_list_id', '')
                );
            case 'slack':
                return array(
                    'webhook_url' => get_option('clr_slack_webhook_url', '')
                );
            default:
                return array();
        }
    }
}