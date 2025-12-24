<?php
/**
 * HMS Payment Gateway Framework
 *
 * This file defines an abstract class for payment gateways.
 * Specific gateway providers (e.g., Razorpay, PayU, Stripe) will extend this class.
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract HMS_Payment_Gateway Class
 *
 * @since 1.0.0
 */
abstract class HMS_Payment_Gateway {

    /**
     * Gateway ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Gateway title.
     *
     * @var string
     */
    protected $title;

    /**
     * Gateway description.
     *
     * @var string
     */
    protected $description;

    /**
     * API credentials/settings.
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Constructor.
     *
     * @param array $settings API credentials and settings for the gateway.
     */
    public function __construct($settings = array()) {
        $this->settings = $settings;
    }

    /**
     * Get gateway ID.
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get gateway title.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Get gateway description.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Process a payment.
     * This method must be implemented by child classes.
     *
     * @param array $payment_data {
     *     Array of payment data.
     *
     *     @type float  $amount       The payment amount.
     *     @type string $currency     The currency code (e.g., INR, USD).
     *     @type string $description  A description of the payment.
     *     @type string $order_id     A unique identifier for the order/bill in HMS.
     *     @type array  $customer_data {
     *         Optional customer data.
     *
     *         @type string $name  Customer name.
     *         @type string $email Customer email.
     *         @type string $phone Customer phone number.
     *     }
     *     @type string $return_url   URL to redirect to after payment completion.
     *     @type string $cancel_url   URL to redirect to if payment is cancelled.
     * }
     * @return array|WP_Error  Array with 'success' (bool), 'transaction_id' (string),
     *                         'status' (string), and 'redirect_url' (string) on success,
     *                         or WP_Error object on failure.
     */
    abstract public function process_payment($payment_data);

    /**
     * Verify a payment status.
     * This method must be implemented by child classes.
     * Used for webhooks or server-to-server verification.
     *
     * @param array $verification_data {
     *     Array of data needed for verification, varies by gateway.
     *
     *     @type string $transaction_id The transaction ID to verify.
     *     @type array  $gateway_data   Any additional data from the gateway (e.g., webhook payload).
     * }
     * @return array|WP_Error  Array with 'success' (bool), 'status' (string - e.g., 'completed', 'failed', 'pending'),
     *                         and 'message' (string) on success, or WP_Error object on failure.
     */
    abstract public function verify_payment($verification_data);

    /**
     * Refund a payment.
     * This method can be implemented by child classes if the gateway supports refunds.
     *
     * @param array $refund_data {
     *     Array of refund data.
     *
     *     @type string $transaction_id The original transaction ID.
     *     @type float  $amount         The amount to refund.
     *     @type string $reason         Reason for the refund.
     * }
     * @return array|WP_Error  Array with 'success' (bool), 'refund_id' (string),
     *                         and 'message' (string) on success, or WP_Error object on failure.
     */
    public function refund_payment($refund_data) {
        // Default implementation if not overridden.
        return new WP_Error('refund_not_supported', esc_html__('Refunds are not supported by this payment gateway.', 'hospital-management-system'));
    }

    /**
     * Validate payment data.
     * Basic validation, can be extended by child classes.
     *
     * @param array $payment_data The payment data to validate.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    protected function validate_payment_data($payment_data) {
        if (empty($payment_data['amount']) || !is_numeric($payment_data['amount']) || $payment_data['amount'] <= 0) {
            return new WP_Error('invalid_amount', esc_html__('Invalid payment amount.', 'hospital-management-system'));
        }
        if (empty($payment_data['currency'])) {
            return new WP_Error('invalid_currency', esc_html__('Currency is required.', 'hospital-management-system'));
        }
        if (empty($payment_data['order_id'])) {
            return new WP_Error('invalid_order_id', esc_html__('Order ID is required.', 'hospital-management-system'));
        }
        return true;
    }

    /**
     * Log a message for debugging purposes.
     *
     * @param string $message The message to log.
     */
    protected function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HMS Payment Gateway (' . $this->id . '): ' . $message);
        }
    }
}

/**
 * Example: Razorpay Payment Gateway Implementation (Placeholder)
 *
 * To use this, you would create a new file, e.g., class-hms-payment-gateway-razorpay.php,
 * and define a class HMS_Payment_Gateway_Razorpay that extends HMS_Payment_Gateway.
 *
 * class HMS_Payment_Gateway_Razorpay extends HMS_Payment_Gateway {
 *     protected $id = 'razorpay';
 *     protected $title = 'Razorpay';
 *     protected $description = 'Process payments via Razorpay.';
 *
 *     public function process_payment($payment_data) {
 *         $validation = $this->validate_payment_data($payment_data);
 *         if (is_wp_error($validation)) {
 *             return $validation;
 *         }
 *
 *         // Implementation using Razorpay PHP SDK
 *         // $key_id = $this->settings['key_id'];
 *         // $key_secret = $this->settings['key_secret'];
 *         // try {
 *         //     $api = new Razorpay\Api\Api($key_id, $key_secret);
 *         //     $orderData = [
 *         //         'receipt'         => $payment_data['order_id'],
 *         //         'amount'          => $payment_data['amount'] * 100, // Amount in paise
 *         //         'currency'        => $payment_data['currency'],
 *         //         'payment_capture' => 1 // Auto capture
 *         //     ];
 *         //     $razorpayOrder = $api->order->create($orderData);
 *         //     return array(
 *         //         'success' => true,
 *         //         'transaction_id' => $razorpayOrder->id, // Razorpay Order ID
 *         //         'status' => 'pending',
 *         //         'redirect_url' => $payment_data['return_url'] . '?order_id=' . $razorpayOrder->id // Redirect to payment page
 *         //     );
 *         // } catch (Exception $e) {
 *         //     $this->log('Error processing payment: ' . $e->getMessage());
 *         //     return new WP_Error('payment_process_error', $e->getMessage());
 *         // }
 *     }
 *
 *     public function verify_payment($verification_data) {
 *         // Implementation for verifying Razorpay webhook or payment signature
 *         // $order_id = $verification_data['razorpay_order_id'];
 *         // $payment_id = $verification_data['razorpay_payment_id'];
 *         // $signature = $verification_data['razorpay_signature'];
 *         // try {
 *         //     $api = new Razorpay\Api\Api($this->settings['key_id'], $this->settings['key_secret']);
 *         //     $attributes = array('razorpay_order_id' => $order_id, 'razorpay_payment_id' => $payment_id, 'razorpay_signature' => $signature);
 *         //     $api->utility->verifyPaymentSignature($attributes);
 *         //     // If verification is successful
 *         //     return array('success' => true, 'status' => 'completed', 'message' => 'Payment verified successfully.');
 *         // } catch (Exception $e) {
 *         //     $this->log('Error verifying payment: ' . $e->getMessage());
 *         //     return new WP_Error('payment_verify_error', $e->getMessage());
 *         // }
 *     }
 * }
 */
