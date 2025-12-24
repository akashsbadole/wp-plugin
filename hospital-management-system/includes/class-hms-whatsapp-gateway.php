<?php
/**
 * HMS WhatsApp Gateway Framework
 *
 * This file defines an abstract class for WhatsApp gateways.
 * Specific API providers (e.g., Twilio WhatsApp, Facebook Graph API) will extend this class.
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract HMS_WhatsApp_Gateway Class
 *
 * @since 1.0.0
 */
abstract class HMS_WhatsApp_Gateway {

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
     * Send a WhatsApp message.
     * This method must be implemented by child classes.
     *
     * @param string $to      Recipient phone number in international format (e.g., +1234567890).
     * @param string $message The message content.
     * @param array  $options {
     *     Optional. Additional options for sending the message.
     *
     *     @type string $media_url URL of a media file (image, pdf, etc.) to send.
     *     @type string $media_type MIME type of the media file (e.g., image/jpeg, application/pdf).
     *     @type string $template_name Name of a pre-approved template to use.
     *     @type array  $template_vars Variables to populate in the template.
     * }
     * @return array|WP_Error  Array with 'success' (bool) and 'message_id' (string) on success,
     *                         or WP_Error object on failure.
     */
    abstract public function send_message($to, $message, $options = array());

    /**
     * Get the status of a sent message.
     * This method can be implemented by child classes if the API supports it.
     *
     * @param string $message_id The unique ID of the message from the gateway.
     * @return array|WP_Error  Array with 'success' (bool), 'status' (string - e.g., 'sent', 'delivered', 'read', 'failed'),
     *                         and 'timestamp' (string) on success, or WP_Error object on failure.
     */
    public function get_message_status($message_id) {
        // Default implementation if not overridden.
        return new WP_Error('status_check_not_supported', esc_html__('Message status check is not supported by this WhatsApp gateway.', 'hospital-management-system'));
    }

    /**
     * Validate a phone number for WhatsApp.
     * Basic validation, can be overridden by child classes for specific formats.
     * WhatsApp numbers typically need to be in E.164 format.
     *
     * @param string $phone_number The phone number to validate.
     * @return bool
     */
    protected function validate_phone_number($phone_number) {
        // Basic check for international format, starts with '+' and contains only digits.
        // Note: WhatsApp has specific requirements, e.g., the number must be registered on WhatsApp.
        // This is a basic format check.
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone_number) === 1;
    }

    /**
     * Log a message for debugging purposes.
     *
     * @param string $message The message to log.
     */
    protected function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('HMS WhatsApp Gateway (' . $this->id . '): ' . $message);
        }
    }
}

/**
 * Example: Twilio WhatsApp Gateway Implementation (Placeholder)
 *
 * To use this, you would create a new file, e.g., class-hms-whatsapp-gateway-twilio.php,
 * and define a class HMS_WhatsApp_Gateway_Twilio that extends HMS_WhatsApp_Gateway.
 *
 * class HMS_WhatsApp_Gateway_Twilio extends HMS_WhatsApp_Gateway {
 *     protected $id = 'twilio_whatsapp';
 *     protected $title = 'Twilio API for WhatsApp';
 *     protected $description = 'Send WhatsApp messages via Twilio API.';
 *
 *     public function send_message($to, $message, $options = array()) {
 *         if (!$this->validate_phone_number($to)) {
 *             return new WP_Error('invalid_phone_number', esc_html__('Invalid phone number format.', 'hospital-management-system'));
 *         }
 *
 *         // Implementation using Twilio PHP SDK
 *         // $sid = $this->settings['account_sid'];
 *         // $token = $this->settings['auth_token'];
 *         // $from_number = $this->settings['whatsapp_from_number']; // Must be a Twilio WhatsApp-enabled number
 *
 *         // try {
 *         //     $client = new Twilio\Rest\Client($sid, $token);
 *         //     $message_options = array(
 *         //         'from' => 'whatsapp:' . $from_number,
 *         //         'body' => $message,
 *         //         'to' => 'whatsapp:' . $to
 *         //     );
 *
 *         //     if (!empty($options['media_url'])) {
 *         //         $message_options['mediaUrl'] = $options['media_url'];
 *         //     }
 *
 *         //     $twilio_message = $client->messages->create($message_options);
 *
 *         //     return array(
 *         //         'success' => true,
 *         //         'message_id' => $twilio_message->sid
 *         //     );
 *         // } catch (Exception $e) {
 *         //     $this->log('Error sending WhatsApp message: ' . $e->getMessage());
 *         //     return new WP_Error('whatsapp_send_error', $e->getMessage());
 *         // }
 *     }
 * }
 */

/**
 * Example: Facebook Graph API (WhatsApp Business Platform) Implementation (Placeholder)
 *
 * class HMS_WhatsApp_Gateway_Facebook extends HMS_WhatsApp_Gateway {
 *     protected $id = 'facebook_whatsapp';
 *     protected $title = 'Facebook Graph API for WhatsApp';
 *     protected $description = 'Send WhatsApp messages via Facebook Graph API.';
 *
 *     public function send_message($to, $message, $options = array()) {
 *         if (!$this->validate_phone_number($to)) {
 *             return new WP_Error('invalid_phone_number', esc_html__('Invalid phone number format.', 'hospital-management-system'));
 *         }
 *
 *         // Implementation using Facebook Graph API
 *         // $access_token = $this->settings['permanent_access_token'];
 *         // $phone_number_id = $this->settings['phone_number_id'];
 *         // $api_version = 'v16.0'; // Use a recent API version
 *
 *         // $url = "https://graph.facebook.com/{$api_version}/{$phone_number_id}/messages";
 *
 *         // $headers = array(
 *         //     'Authorization: Bearer ' . $access_token,
 *         //     'Content-Type: application/json'
 *         // );
 *
 *         // $payload = array(
 *         //     'messaging_product' => 'whatsapp',
 *         //     'to' => $to,
 *         //     'text' => array('body' => $message)
 *         // );
 *
 *         // if (!empty($options['template_name'])) {
 *         //     // Logic for sending template messages
 *         //     $payload['template'] = array(
 *         //         'name' => $options['template_name'],
 *         //         'language' => array('code' => 'en_US'), // Or get from options
 *         //         'components' => array() // Add components based on $options['template_vars']
 *         //     );
 *         //     unset($payload['text']);
 *         // }
 *
 *         // $response = wp_remote_post($url, array(
 *         //     'headers' => $headers,
 *         //     'body' => json_encode($payload),
 *         //     'timeout' => 15,
 *         // ));
 *
 *         // if (is_wp_error($response)) {
 *         //     $this->log('Error sending WhatsApp message via Facebook: ' . $response->get_error_message());
 *         //     return new WP_Error('whatsapp_send_error', $response->get_error_message());
 *         // }
 *
 *         // $body = json_decode(wp_remote_retrieve_body($response), true);
 *
 *         // if (isset($body['error'])) {
 *         //     $this->log('Facebook API Error: ' . $body['error']['message']);
 *         //     return new WP_Error('whatsapp_api_error', $body['error']['message']);
 *         // }
 *
 *         // if (isset($body['messages'][0]['id'])) {
 *         //     return array(
 *         //         'success' => true,
 *         //         'message_id' => $body['messages'][0]['id']
 *         //     );
 *         // }
 *
 *         // return new WP_Error('whatsapp_unknown_error', esc_html__('An unknown error occurred.', 'hospital-management-system'));
 *     }
 * }
 */
