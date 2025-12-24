<?php
/**
 * HMS Notifications Management Class
 *
 * Handles email, SMS, and WhatsApp notifications for the Hospital Management System.
 *
 * @package Hospital_Management_System
 * @subpackage Includes
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class HMS_Notifications {

    /**
     * Initialize the notifications class
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize hooks and filters
     */
    public function init() {
        // Hook into WordPress email system
        add_filter('wp_mail_from', array($this, 'custom_mail_from'));
        add_filter('wp_mail_from_name', array($this, 'custom_mail_from_name'));
        
        // Add custom email headers
        add_filter('wp_mail', array($this, 'custom_mail_headers'));
    }

    /**
     * Send email notification
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message
     * @param array $args Additional arguments
     * @return bool True if email was sent successfully
     */
    public function send_email($to, $subject, $message, $args = array()) {
        // Check if email notifications are enabled
        if (get_option('hms_enable_email', 'on') !== 'on') {
            return false;
        }

        // Validate email address
        if (!is_email($to)) {
            error_log('HMS Notifications: Invalid email address - ' . $to);
            return false;
        }

        // Prepare email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Add custom headers if provided
        if (isset($args['headers']) && is_array($args['headers'])) {
            $headers = array_merge($headers, $args['headers']);
        }

        // Get email template
        $template = isset($args['template']) ? $args['template'] : 'default';
        $formatted_message = $this->get_email_template($template, $message, $args);

        // Send email
        $sent = wp_mail($to, $subject, $formatted_message, $headers);

        // Log email activity
        $this->log_notification_activity('email', $to, $subject, $sent);

        return $sent;
    }

    /**
     * Send SMS notification
     *
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @param array $args Additional arguments
     * @return bool True if SMS was sent successfully
     */
    public function send_sms($to, $message, $args = array()) {
        // Check if SMS notifications are enabled
        if (get_option('hms_enable_sms', 'off') !== 'on') {
            return false;
        }

        // Validate phone number
        $to = $this->format_phone_number($to);
        if (!$to) {
            error_log('HMS Notifications: Invalid phone number');
            return false;
        }

        // Get SMS gateway settings
        $gateway = get_option('hms_sms_gateway', 'twilio');
        $api_key = get_option('hms_sms_api_key', '');
        $sender_id = get_option('hms_sms_sender_id', '');

        if (empty($api_key)) {
            error_log('HMS Notifications: SMS API key not configured');
            return false;
        }

        // Send SMS based on gateway
        $sent = false;
        switch ($gateway) {
            case 'twilio':
                $sent = $this->send_twilio_sms($to, $message, $api_key, $sender_id);
                break;
            case 'plivo':
                $sent = $this->send_plivo_sms($to, $message, $api_key, $sender_id);
                break;
            case 'msg91':
                $sent = $this->send_msg91_sms($to, $message, $api_key, $sender_id);
                break;
            case 'textlocal':
                $sent = $this->send_textlocal_sms($to, $message, $api_key, $sender_id);
                break;
            default:
                $sent = $this->send_custom_sms($to, $message, $api_key, $sender_id, $gateway);
                break;
        }

        // Log SMS activity
        $this->log_notification_activity('sms', $to, $message, $sent);

        return $sent;
    }

    /**
     * Send WhatsApp notification
     *
     * @param string $to Recipient phone number
     * @param string $message WhatsApp message
     * @param array $args Additional arguments
     * @return bool True if WhatsApp message was sent successfully
     */
    public function send_whatsapp($to, $message, $args = array()) {
        // Check if WhatsApp notifications are enabled
        if (get_option('hms_enable_whatsapp', 'off') !== 'on') {
            return false;
        }

        // Validate phone number
        $to = $this->format_phone_number($to);
        if (!$to) {
            error_log('HMS Notifications: Invalid phone number for WhatsApp');
            return false;
        }

        // Get WhatsApp API settings
        $api_key = get_option('hms_whatsapp_api_key', '');

        if (empty($api_key)) {
            error_log('HMS Notifications: WhatsApp API key not configured');
            return false;
        }

        // Send WhatsApp message (implement based on your WhatsApp API provider)
        $sent = $this->send_whatsapp_message($to, $message, $api_key);

        // Log WhatsApp activity
        $this->log_notification_activity('whatsapp', $to, $message, $sent);

        return $sent;
    }

    /**
     * Send appointment reminder notifications
     *
     * @param int $appointment_id Appointment ID
     * @param string $type Reminder type (24h, 2h, etc.)
     * @return bool True if notifications were sent successfully
     */
    public function send_appointment_reminder($appointment_id, $type = '24h') {
        global $wpdb;

        // Get appointment details
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, p.first_name, p.last_name, p.email, p.phone, 
                    d.first_name as doctor_first_name, d.last_name as doctor_last_name,
                    dept.name as department_name
             FROM {$wpdb->prefix}hms_appointments a
             LEFT JOIN {$wpdb->prefix}hms_patients p ON a.patient_id = p.id
             LEFT JOIN {$wpdb->prefix}hms_staff d ON a.doctor_id = d.id
             LEFT JOIN {$wpdb->prefix}hms_departments dept ON a.department_id = dept.id
             WHERE a.id = %d AND a.status = 'scheduled'",
            $appointment_id
        ));

        if (!$appointment) {
            return false;
        }

        // Prepare notification content
        $patient_name = $appointment->first_name . ' ' . $appointment->last_name;
        $doctor_name = $appointment->doctor_first_name . ' ' . $appointment->doctor_last_name;
        $appointment_date = date('F j, Y', strtotime($appointment->appointment_date));
        $appointment_time = date('g:i A', strtotime($appointment->appointment_time));

        $subject = sprintf(__('Appointment Reminder - %s', 'hospital-management-system'), $appointment_date);
        
        $message_args = array(
            'patient_name' => $patient_name,
            'doctor_name' => $doctor_name,
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time,
            'department' => $appointment->department_name,
            'hospital_name' => get_option('hms_hospital_name', get_bloginfo('name'))
        );

        $email_message = $this->get_appointment_reminder_template('email', $type, $message_args);
        $sms_message = $this->get_appointment_reminder_template('sms', $type, $message_args);

        $results = array();

        // Send email reminder
        if (!empty($appointment->email)) {
            $results['email'] = $this->send_email($appointment->email, $subject, $email_message, array(
                'template' => 'appointment_reminder'
            ));
        }

        // Send SMS reminder
        if (!empty($appointment->phone)) {
            $results['sms'] = $this->send_sms($appointment->phone, $sms_message);
        }

        return !empty($results) && !in_array(false, $results, true);
    }

    /**
     * Send appointment confirmation notification
     *
     * @param int $appointment_id Appointment ID
     * @return bool True if notifications were sent successfully
     */
    public function send_appointment_confirmation($appointment_id) {
        // Similar to reminder but with confirmation template
        return $this->send_appointment_notification($appointment_id, 'confirmation');
    }

    /**
     * Send appointment cancellation notification
     *
     * @param int $appointment_id Appointment ID
     * @return bool True if notifications were sent successfully
     */
    public function send_appointment_cancellation($appointment_id) {
        // Similar to reminder but with cancellation template
        return $this->send_appointment_notification($appointment_id, 'cancellation');
    }

    /**
     * Custom mail from address
     */
    public function custom_mail_from($email) {
        $custom_email = get_option('hms_email_from_address', '');
        return !empty($custom_email) ? $custom_email : $email;
    }

    /**
     * Custom mail from name
     */
    public function custom_mail_from_name($name) {
        $custom_name = get_option('hms_email_from_name', '');
        return !empty($custom_name) ? $custom_name : $name;
    }

    /**
     * Add custom email headers
     */
    public function custom_mail_headers($args) {
        // Add hospital branding to email headers if needed
        return $args;
    }

    /**
     * Get email template
     *
     * @param string $template Template name
     * @param string $message Message content
     * @param array $args Template arguments
     * @return string Formatted email content
     */
    private function get_email_template($template, $message, $args = array()) {
        $hospital_name = get_option('hms_hospital_name', get_bloginfo('name'));
        $hospital_address = get_option('hms_hospital_address', '');
        $hospital_phone = get_option('hms_hospital_phone', '');
        $primary_color = get_option('hms_primary_color', '#2271b1');

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($hospital_name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: ' . esc_attr($primary_color) . '; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 10px 20px; background-color: ' . esc_attr($primary_color) . '; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . esc_html($hospital_name) . '</h1>
        </div>
        <div class="content">
            ' . wp_kses_post($message) . '
        </div>
        <div class="footer">
            <p>' . esc_html($hospital_name) . '</p>';
            
        if (!empty($hospital_address)) {
            $html .= '<p>' . esc_html($hospital_address) . '</p>';
        }
        
        if (!empty($hospital_phone)) {
            $html .= '<p>Phone: ' . esc_html($hospital_phone) . '</p>';
        }
        
        $html .= '</div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Get appointment reminder template
     *
     * @param string $type Notification type (email, sms)
     * @param string $reminder_type Reminder type (24h, 2h, etc.)
     * @param array $args Template arguments
     * @return string Formatted message
     */
    private function get_appointment_reminder_template($type, $reminder_type, $args) {
        $templates = array(
            'email' => array(
                '24h' => '<h2>Appointment Reminder</h2>
                         <p>Dear {patient_name},</p>
                         <p>This is a reminder that you have an appointment scheduled for tomorrow:</p>
                         <ul>
                             <li><strong>Date:</strong> {appointment_date}</li>
                             <li><strong>Time:</strong> {appointment_time}</li>
                             <li><strong>Doctor:</strong> Dr. {doctor_name}</li>
                             <li><strong>Department:</strong> {department}</li>
                         </ul>
                         <p>Please arrive 15 minutes before your scheduled time.</p>
                         <p>If you need to reschedule or cancel, please contact us as soon as possible.</p>
                         <p>Thank you,<br>{hospital_name}</p>',
                '2h' => '<h2>Appointment Today</h2>
                        <p>Dear {patient_name},</p>
                        <p>Your appointment is in 2 hours:</p>
                        <ul>
                            <li><strong>Time:</strong> {appointment_time}</li>
                            <li><strong>Doctor:</strong> Dr. {doctor_name}</li>
                            <li><strong>Department:</strong> {department}</li>
                        </ul>
                        <p>Please arrive 15 minutes early.</p>
                        <p>Thank you,<br>{hospital_name}</p>'
            ),
            'sms' => array(
                '24h' => 'Reminder: You have an appointment tomorrow at {appointment_time} with Dr. {doctor_name} at {hospital_name}. Please arrive 15 minutes early.',
                '2h' => 'Reminder: Your appointment with Dr. {doctor_name} is in 2 hours at {appointment_time}. Please arrive 15 minutes early.'
            )
        );

        $template = isset($templates[$type][$reminder_type]) ? $templates[$type][$reminder_type] : '';
        
        // Replace placeholders
        foreach ($args as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    /**
     * Send appointment notification (generic method)
     */
    private function send_appointment_notification($appointment_id, $notification_type) {
        // Implementation similar to send_appointment_reminder but with different templates
        return true; // Placeholder
    }

    /**
     * Format phone number for SMS/WhatsApp
     *
     * @param string $phone Raw phone number
     * @return string|false Formatted phone number or false if invalid
     */
    private function format_phone_number($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Basic validation - should have at least 10 digits
        if (strlen($phone) < 10) {
            return false;
        }

        // Add country code if not present (assuming India +91 as default)
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Send SMS via Twilio
     */
    private function send_twilio_sms($to, $message, $api_key, $sender_id) {
        // Implement Twilio SMS sending
        // This is a placeholder - implement actual Twilio API integration
        return true;
    }

    /**
     * Send SMS via Plivo
     */
    private function send_plivo_sms($to, $message, $api_key, $sender_id) {
        // Implement Plivo SMS sending
        return true;
    }

    /**
     * Send SMS via MSG91
     */
    private function send_msg91_sms($to, $message, $api_key, $sender_id) {
        // Implement MSG91 SMS sending
        return true;
    }

    /**
     * Send SMS via TextLocal
     */
    private function send_textlocal_sms($to, $message, $api_key, $sender_id) {
        // Implement TextLocal SMS sending
        return true;
    }

    /**
     * Send SMS via custom gateway
     */
    private function send_custom_sms($to, $message, $api_key, $sender_id, $gateway) {
        // Allow custom SMS gateway implementation
        return apply_filters('hms_send_custom_sms', false, $to, $message, $api_key, $sender_id, $gateway);
    }

    /**
     * Send WhatsApp message
     */
    private function send_whatsapp_message($to, $message, $api_key) {
        // Implement WhatsApp Business API integration
        return true;
    }

    /**
     * Log notification activity
     *
     * @param string $type Notification type (email, sms, whatsapp)
     * @param string $recipient Recipient address/number
     * @param string $message Message content
     * @param bool $success Whether the notification was sent successfully
     */
    private function log_notification_activity($type, $recipient, $message, $success) {
        if (get_option('hms_enable_debug_mode', 'off') === 'on') {
            $log_message = sprintf(
                'HMS Notification [%s]: %s to %s - %s',
                strtoupper($type),
                $success ? 'SUCCESS' : 'FAILED',
                $recipient,
                substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '')
            );
            error_log($log_message);
        }

        // Store in database for reporting (optional)
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'hms_notification_log',
            array(
                'type' => $type,
                'recipient' => $recipient,
                'message' => $message,
                'status' => $success ? 'sent' : 'failed',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get notification statistics
     *
     * @param array $filters Optional filters
     * @return array Notification statistics
     */
    public function get_notification_stats($filters = array()) {
        global $wpdb;

        $where_clause = '1=1';
        $where_values = array();

        if (!empty($filters['type'])) {
            $where_clause .= ' AND type = %s';
            $where_values[] = $filters['type'];
        }

        if (!empty($filters['date_from'])) {
            $where_clause .= ' AND DATE(created_at) >= %s';
            $where_values[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where_clause .= ' AND DATE(created_at) <= %s';
            $where_values[] = $filters['date_to'];
        }

        $query = "SELECT 
                    type,
                    status,
                    COUNT(*) as count
                  FROM {$wpdb->prefix}hms_notification_log 
                  WHERE {$where_clause}
                  GROUP BY type, status";

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $results = $wpdb->get_results($query);

        $stats = array();
        foreach ($results as $result) {
            $stats[$result->type][$result->status] = $result->count;
        }

        return $stats;
    }
}