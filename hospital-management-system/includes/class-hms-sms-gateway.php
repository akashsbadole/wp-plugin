<?php
/**
 * HMS SMS Gateway Framework
 *
 * This file defines an abstract class for SMS gateways.
 * Specific gateway providers (e.g., Twilio, Plivo) will extend this class.
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract HMS_SMS_Gateway Class
 *
 * @since 1.0.0
 */
abstract class HMS_SMS_Gateway {

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
     * Send an SMS message.
     * This method must be implemented by child classes.
     *
     * @param string $to      Recipient phone number in international format (e.g., +1234567890).
     * @param string $message The message content.
     * @return array|WP_Error  Array with 'success' (bool) and 'message' (string) on success,
     *                         or WP_Error object on failure.
     */
    abstract public function send_sms($to, $message);

    /**
     * Validate a phone number.
     * Basic validation, can be overridden by child classes for specific formats.
     *
     * @param string $phone_number The phone number to validate.
     * @return bool
     */
    protected function validate_phone_number($phone_number) {
        // Basic check for international format, starts with '+' and contains only digits.
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone_number) === 1;
    }

    /**
     * Log a message for debugging purposes.
     *
     * @param string $message The message to log.
     */
    protected function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HMS SMS Gateway (' . $this->id . '): ' . $message);
        }
    }
}

/**
 * Example: Twilio SMS Gateway Implementation (Placeholder)
 *
 * To use this, you would create a new file, e.g., class-hms-sms-gateway-twilio.php,
 * and define a class HMS_SMS_Gateway_Twilio that extends HMS_SMS_Gateway.
 *
 * class HMS_SMS_Gateway_Twilio extends HMS_SMS_Gateway {
 *     protected $id = 'twilio';
 *     protected $title = 'Twilio';
 *     protected $description = 'Send SMS via Twilio API.';
 *
 *     public function send_sms($to, $message) {
 *         // Implementation using Twilio PHP SDK
 *         // $sid = $this->settings['account_sid'];
 *         // $token = $this->settings['auth_token'];
 *         // $from_number = $this->settings['from_number'];
 *         // try {
 *         //     $client = new Twilio\Rest\Client($sid, $token);
 *         //     $client->messages->create(
 *         //         $to,
 *         //         array(
 *         //             'from' => $from_number,
 *         //             'body' => $message
 *         //         )
 *         //     );
 *         //     return array('success' => true, 'message' => 'SMS sent successfully.');
 *         // } catch (Exception $e) {
 *         //     $this->log('Error sending SMS: ' . $e->getMessage());
 *         //     return new WP_Error('sms_send_error', $e->getMessage());
 *         // }
 *     }
 * }
 */
