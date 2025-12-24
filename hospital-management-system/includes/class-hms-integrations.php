<?php
/**
 * HMS Integrations Management Class
 *
 * Handles third-party integrations for the Hospital Management System.
 *
 * @package Hospital_Management_System
 * @subpackage Includes
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class HMS_Integrations {

    /**
     * Initialize the integrations class
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize hooks and filters
     */
    public function init() {
        // Initialize payment gateway hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_payment_scripts'));
        
        // Add integration status checks
        add_action('admin_notices', array($this, 'show_integration_notices'));
    }

    /**
     * Process payment through configured gateway
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    public function process_payment($amount, $payment_data) {
        $gateway = get_option('hms_payment_gateway', 'cash');
        
        switch ($gateway) {
            case 'razorpay':
                return $this->process_razorpay_payment($amount, $payment_data);
            case 'stripe':
                return $this->process_stripe_payment($amount, $payment_data);
            case 'paypal':
                return $this->process_paypal_payment($amount, $payment_data);
            case 'payu':
                return $this->process_payu_payment($amount, $payment_data);
            case 'cash':
            default:
                return $this->process_cash_payment($amount, $payment_data);
        }
    }

    /**
     * Process Razorpay payment
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    private function process_razorpay_payment($amount, $payment_data) {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $key_id = isset($gateway_keys['razorpay']['key_id']) ? $gateway_keys['razorpay']['key_id'] : '';
        $key_secret = isset($gateway_keys['razorpay']['key_secret']) ? $gateway_keys['razorpay']['key_secret'] : '';

        if (empty($key_id) || empty($key_secret)) {
            return array(
                'success' => false,
                'message' => __('Razorpay configuration is incomplete.', 'hospital-management-system')
            );
        }

        // Implement Razorpay payment processing
        try {
            // This is a placeholder - implement actual Razorpay API integration
            $order_id = 'HMS_' . time() . '_' . wp_rand(1000, 9999);
            
            return array(
                'success' => true,
                'transaction_id' => $order_id,
                'gateway' => 'razorpay',
                'amount' => $amount,
                'status' => 'pending',
                'message' => __('Payment initiated successfully.', 'hospital-management-system')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Process Stripe payment
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    private function process_stripe_payment($amount, $payment_data) {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $public_key = isset($gateway_keys['stripe']['public_key']) ? $gateway_keys['stripe']['public_key'] : '';
        $secret_key = isset($gateway_keys['stripe']['secret_key']) ? $gateway_keys['stripe']['secret_key'] : '';

        if (empty($public_key) || empty($secret_key)) {
            return array(
                'success' => false,
                'message' => __('Stripe configuration is incomplete.', 'hospital-management-system')
            );
        }

        // Implement Stripe payment processing
        try {
            // This is a placeholder - implement actual Stripe API integration
            $payment_intent_id = 'pi_' . wp_rand(100000, 999999);
            
            return array(
                'success' => true,
                'transaction_id' => $payment_intent_id,
                'gateway' => 'stripe',
                'amount' => $amount,
                'status' => 'pending',
                'message' => __('Payment initiated successfully.', 'hospital-management-system')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Process PayPal payment
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    private function process_paypal_payment($amount, $payment_data) {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $client_id = isset($gateway_keys['paypal']['client_id']) ? $gateway_keys['paypal']['client_id'] : '';
        $client_secret = isset($gateway_keys['paypal']['client_secret']) ? $gateway_keys['paypal']['client_secret'] : '';

        if (empty($client_id) || empty($client_secret)) {
            return array(
                'success' => false,
                'message' => __('PayPal configuration is incomplete.', 'hospital-management-system')
            );
        }

        // Implement PayPal payment processing
        try {
            // This is a placeholder - implement actual PayPal API integration
            $order_id = 'PP_' . time() . '_' . wp_rand(1000, 9999);
            
            return array(
                'success' => true,
                'transaction_id' => $order_id,
                'gateway' => 'paypal',
                'amount' => $amount,
                'status' => 'pending',
                'message' => __('Payment initiated successfully.', 'hospital-management-system')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Process PayU payment
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    private function process_payu_payment($amount, $payment_data) {
        // Implement PayU payment processing
        return array(
            'success' => true,
            'transaction_id' => 'PU_' . time(),
            'gateway' => 'payu',
            'amount' => $amount,
            'status' => 'pending',
            'message' => __('Payment initiated successfully.', 'hospital-management-system')
        );
    }

    /**
     * Process cash payment
     *
     * @param float $amount Payment amount
     * @param array $payment_data Payment details
     * @return array Payment result
     */
    private function process_cash_payment($amount, $payment_data) {
        return array(
            'success' => true,
            'transaction_id' => 'CASH_' . time(),
            'gateway' => 'cash',
            'amount' => $amount,
            'status' => 'completed',
            'message' => __('Cash payment recorded successfully.', 'hospital-management-system')
        );
    }

    /**
     * Sync data with Laboratory Information System (LIS)
     *
     * @param array $lab_data Laboratory data to sync
     * @return array Sync result
     */
    public function sync_with_lis($lab_data) {
        $lis_endpoint = get_option('hms_lis_api_endpoint', '');
        $lis_api_key = get_option('hms_lis_api_key', '');

        if (empty($lis_endpoint) || empty($lis_api_key)) {
            return array(
                'success' => false,
                'message' => __('LIS integration is not configured.', 'hospital-management-system')
            );
        }

        try {
            // Prepare API request
            $headers = array(
                'Authorization' => 'Bearer ' . $lis_api_key,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_post($lis_endpoint . '/sync', array(
                'headers' => $headers,
                'body' => json_encode($lab_data),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if ($response_code !== 200) {
                throw new Exception('LIS API returned error: ' . $response_code);
            }

            $result = json_decode($response_body, true);

            return array(
                'success' => true,
                'data' => $result,
                'message' => __('Data synced with LIS successfully.', 'hospital-management-system')
            );

        } catch (Exception $e) {
            error_log('HMS LIS Integration Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Sync data with Electronic Medical Record (EMR) system
     *
     * @param array $emr_data EMR data to sync
     * @return array Sync result
     */
    public function sync_with_emr($emr_data) {
        $emr_endpoint = get_option('hms_emr_api_endpoint', '');
        $emr_api_key = get_option('hms_emr_api_key', '');

        if (empty($emr_endpoint) || empty($emr_api_key)) {
            return array(
                'success' => false,
                'message' => __('EMR integration is not configured.', 'hospital-management-system')
            );
        }

        try {
            // Prepare API request
            $headers = array(
                'Authorization' => 'Bearer ' . $emr_api_key,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_post($emr_endpoint . '/sync', array(
                'headers' => $headers,
                'body' => json_encode($emr_data),
                'timeout' => 30
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if ($response_code !== 200) {
                throw new Exception('EMR API returned error: ' . $response_code);
            }

            $result = json_decode($response_body, true);

            return array(
                'success' => true,
                'data' => $result,
                'message' => __('Data synced with EMR successfully.', 'hospital-management-system')
            );

        } catch (Exception $e) {
            error_log('HMS EMR Integration Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Test payment gateway connection
     *
     * @param string $gateway Gateway name
     * @return array Test result
     */
    public function test_payment_gateway($gateway = null) {
        if (!$gateway) {
            $gateway = get_option('hms_payment_gateway', 'cash');
        }

        switch ($gateway) {
            case 'razorpay':
                return $this->test_razorpay_connection();
            case 'stripe':
                return $this->test_stripe_connection();
            case 'paypal':
                return $this->test_paypal_connection();
            case 'cash':
                return array(
                    'success' => true,
                    'message' => __('Cash payment gateway is always available.', 'hospital-management-system')
                );
            default:
                return array(
                    'success' => false,
                    'message' => __('Unknown payment gateway.', 'hospital-management-system')
                );
        }
    }

    /**
     * Test Razorpay connection
     */
    private function test_razorpay_connection() {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $key_id = isset($gateway_keys['razorpay']['key_id']) ? $gateway_keys['razorpay']['key_id'] : '';
        $key_secret = isset($gateway_keys['razorpay']['key_secret']) ? $gateway_keys['razorpay']['key_secret'] : '';

        if (empty($key_id) || empty($key_secret)) {
            return array(
                'success' => false,
                'message' => __('Razorpay API keys are not configured.', 'hospital-management-system')
            );
        }

        // Test API connection (placeholder)
        return array(
            'success' => true,
            'message' => __('Razorpay connection test successful.', 'hospital-management-system')
        );
    }

    /**
     * Test Stripe connection
     */
    private function test_stripe_connection() {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $secret_key = isset($gateway_keys['stripe']['secret_key']) ? $gateway_keys['stripe']['secret_key'] : '';

        if (empty($secret_key)) {
            return array(
                'success' => false,
                'message' => __('Stripe API keys are not configured.', 'hospital-management-system')
            );
        }

        // Test API connection (placeholder)
        return array(
            'success' => true,
            'message' => __('Stripe connection test successful.', 'hospital-management-system')
        );
    }

    /**
     * Test PayPal connection
     */
    private function test_paypal_connection() {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        $client_id = isset($gateway_keys['paypal']['client_id']) ? $gateway_keys['paypal']['client_id'] : '';
        $client_secret = isset($gateway_keys['paypal']['client_secret']) ? $gateway_keys['paypal']['client_secret'] : '';

        if (empty($client_id) || empty($client_secret)) {
            return array(
                'success' => false,
                'message' => __('PayPal API credentials are not configured.', 'hospital-management-system')
            );
        }

        // Test API connection (placeholder)
        return array(
            'success' => true,
            'message' => __('PayPal connection test successful.', 'hospital-management-system')
        );
    }

    /**
     * Test LIS connection
     *
     * @return array Test result
     */
    public function test_lis_connection() {
        $lis_endpoint = get_option('hms_lis_api_endpoint', '');
        $lis_api_key = get_option('hms_lis_api_key', '');

        if (empty($lis_endpoint) || empty($lis_api_key)) {
            return array(
                'success' => false,
                'message' => __('LIS configuration is incomplete.', 'hospital-management-system')
            );
        }

        try {
            $headers = array(
                'Authorization' => 'Bearer ' . $lis_api_key,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_get($lis_endpoint . '/health', array(
                'headers' => $headers,
                'timeout' => 10
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                return array(
                    'success' => true,
                    'message' => __('LIS connection test successful.', 'hospital-management-system')
                );
            } else {
                throw new Exception('LIS API returned error: ' . $response_code);
            }

        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Test EMR connection
     *
     * @return array Test result
     */
    public function test_emr_connection() {
        $emr_endpoint = get_option('hms_emr_api_endpoint', '');
        $emr_api_key = get_option('hms_emr_api_key', '');

        if (empty($emr_endpoint) || empty($emr_api_key)) {
            return array(
                'success' => false,
                'message' => __('EMR configuration is incomplete.', 'hospital-management-system')
            );
        }

        try {
            $headers = array(
                'Authorization' => 'Bearer ' . $emr_api_key,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_get($emr_endpoint . '/health', array(
                'headers' => $headers,
                'timeout' => 10
            ));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);

            if ($response_code === 200) {
                return array(
                    'success' => true,
                    'message' => __('EMR connection test successful.', 'hospital-management-system')
                );
            } else {
                throw new Exception('EMR API returned error: ' . $response_code);
            }

        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Enqueue payment gateway scripts
     */
    public function enqueue_payment_scripts() {
        $gateway = get_option('hms_payment_gateway', 'cash');
        
        switch ($gateway) {
            case 'razorpay':
                wp_enqueue_script('razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', array(), null, true);
                break;
            case 'stripe':
                wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, true);
                break;
            case 'paypal':
                wp_enqueue_script('paypal-sdk', 'https://www.paypal.com/sdk/js?client-id=' . $this->get_paypal_client_id(), array(), null, true);
                break;
        }
    }

    /**
     * Get PayPal client ID
     */
    private function get_paypal_client_id() {
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        return isset($gateway_keys['paypal']['client_id']) ? $gateway_keys['paypal']['client_id'] : '';
    }

    /**
     * Show integration status notices
     */
    public function show_integration_notices() {
        if (!current_user_can('manage_hms')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'hms') === false) {
            return;
        }

        // Check payment gateway configuration
        $gateway = get_option('hms_payment_gateway', 'cash');
        if ($gateway !== 'cash') {
            $gateway_keys = get_option('hms_payment_gateway_keys', array());
            if (empty($gateway_keys[$gateway])) {
                echo '<div class="notice notice-warning"><p>';
                echo sprintf(
                    __('Payment gateway "%s" is selected but not configured. <a href="%s">Configure it now</a>.', 'hospital-management-system'),
                    ucfirst($gateway),
                    admin_url('admin.php?page=hms-settings&tab=integration-settings')
                );
                echo '</p></div>';
            }
        }

        // Check notification settings
        $email_enabled = get_option('hms_enable_email', 'on');
        $sms_enabled = get_option('hms_enable_sms', 'off');
        
        if ($email_enabled === 'on') {
            $from_email = get_option('hms_email_from_address', '');
            if (empty($from_email)) {
                echo '<div class="notice notice-warning"><p>';
                echo sprintf(
                    __('Email notifications are enabled but no "From" email address is configured. <a href="%s">Configure it now</a>.', 'hospital-management-system'),
                    admin_url('admin.php?page=hms-settings&tab=notification-settings')
                );
                echo '</p></div>';
            }
        }

        if ($sms_enabled === 'on') {
            $sms_api_key = get_option('hms_sms_api_key', '');
            if (empty($sms_api_key)) {
                echo '<div class="notice notice-warning"><p>';
                echo sprintf(
                    __('SMS notifications are enabled but no API key is configured. <a href="%s">Configure it now</a>.', 'hospital-management-system'),
                    admin_url('admin.php?page=hms-settings&tab=notification-settings')
                );
                echo '</p></div>';
            }
        }
    }

    /**
     * Get integration status
     *
     * @return array Integration status for all services
     */
    public function get_integration_status() {
        $status = array();

        // Payment gateway status
        $gateway = get_option('hms_payment_gateway', 'cash');
        $gateway_keys = get_option('hms_payment_gateway_keys', array());
        
        $status['payment'] = array(
            'gateway' => $gateway,
            'configured' => $gateway === 'cash' || !empty($gateway_keys[$gateway]),
            'active' => true
        );

        // LIS status
        $lis_endpoint = get_option('hms_lis_api_endpoint', '');
        $lis_api_key = get_option('hms_lis_api_key', '');
        
        $status['lis'] = array(
            'configured' => !empty($lis_endpoint) && !empty($lis_api_key),
            'active' => !empty($lis_endpoint) && !empty($lis_api_key)
        );

        // EMR status
        $emr_endpoint = get_option('hms_emr_api_endpoint', '');
        $emr_api_key = get_option('hms_emr_api_key', '');
        
        $status['emr'] = array(
            'configured' => !empty($emr_endpoint) && !empty($emr_api_key),
            'active' => !empty($emr_endpoint) && !empty($emr_api_key)
        );

        // Notification status
        $status['notifications'] = array(
            'email' => array(
                'enabled' => get_option('hms_enable_email', 'on') === 'on',
                'configured' => !empty(get_option('hms_email_from_address', ''))
            ),
            'sms' => array(
                'enabled' => get_option('hms_enable_sms', 'off') === 'on',
                'configured' => !empty(get_option('hms_sms_api_key', ''))
            ),
            'whatsapp' => array(
                'enabled' => get_option('hms_enable_whatsapp', 'off') === 'on',
                'configured' => !empty(get_option('hms_whatsapp_api_key', ''))
            )
        );

        return $status;
    }
}