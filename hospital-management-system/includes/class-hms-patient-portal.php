<?php
/**
 * HMS Patient Portal Class
 *
 * Handles Patient Engagement Enhancements, including the patient portal,
 * online appointment booking, and telemedicine framework.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HMS_Patient_Portal {

    /**
     * Constructor.
     * Hooks into WordPress actions for shortcodes and AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Register shortcodes
        add_shortcode('hms_patient_portal_login', array($this, 'render_patient_portal_login_shortcode'));
        add_shortcode('hms_patient_portal_dashboard', array($this, 'render_patient_portal_dashboard_shortcode'));
        add_shortcode('hms_online_appointment_booking', array($this, 'render_online_appointment_booking_shortcode'));
        add_shortcode('hms_join_telemedicine_session', array($this, 'render_join_telemedicine_session_shortcode'));

        // AJAX hooks for patient portal (authenticated)
        add_action('wp_ajax_hms_patient_book_appointment', array($this, 'ajax_patient_book_appointment'));
        add_action('wp_ajax_hms_patient_cancel_appointment', array($this, 'ajax_patient_cancel_appointment'));
        add_action('wp_ajax_hms_patient_view_bills', array($this, 'ajax_patient_view_bills'));
        add_action('wp_ajax_hms_patient_make_payment', array($this, 'ajax_patient_make_payment'));
        add_action('wp_ajax_hms_patient_view_records', array($this, 'ajax_patient_view_records'));
        add_action('wp_ajax_hms_patient_update_profile', array($this, 'ajax_patient_update_profile'));
        add_action('wp_ajax_hms_patient_change_password', array($this, 'ajax_patient_change_password'));

        // AJAX hooks for public (non-authenticated) actions like registration
        add_action('wp_ajax_nopriv_hms_patient_register', array($this, 'ajax_patient_register'));
        add_action('wp_ajax_nopriv_hms_patient_login', array($this, 'ajax_patient_login'));
        add_action('wp_ajax_nopriv_hms_forgot_password', array($this, 'ajax_forgot_password'));

        // Enqueue public styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }

    /**
     * Enqueue public styles and scripts.
     *
     * @since    1.0.0
     */
    public function enqueue_public_assets() {
        // Only load on pages with our shortcodes
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'hms_patient_portal_login') ||
            has_shortcode($post->post_content, 'hms_patient_portal_dashboard') ||
            has_shortcode($post->post_content, 'hms_online_appointment_booking') ||
            has_shortcode($post->post_content, 'hms_join_telemedicine_session')) {

            wp_enqueue_style('hms-patient-portal', plugin_dir_url(dirname(__FILE__)) . 'public/css/hms-patient-portal.css', array(), HMS_VERSION);
            wp_enqueue_script('hms-patient-portal', plugin_dir_url(dirname(__FILE__)) . 'public/js/hms-patient-portal.js', array('jquery'), HMS_VERSION, true);
            
            wp_localize_script('hms-patient-portal', 'hms_ajax_obj', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('hms_patient_portal_nonce'),
                'is_user_logged_in' => is_user_logged_in(),
            ));
        }
    }

    // --- Shortcode Renderers ---

    /**
     * Render the Patient Portal Login shortcode.
     *
     * @since    1.0.0
     * @param    array     $atts               Shortcode attributes.
     * @return   string                         The HTML output.
     */
    public function render_patient_portal_login_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<p>' . esc_html__('You are already logged in.', 'hospital-management-system') . '</p>';
        }
        ob_start();
        // This will load a template file from public/templates/
        load_template(plugin_dir_path(dirname(__FILE__)) . 'public/templates/patient-portal-login.php', false);
        return ob_get_clean();
    }

    /**
     * Render the Patient Portal Dashboard shortcode.
     *
     * @since    1.0.0
     * @param    array     $atts               Shortcode attributes.
     * @return   string                         The HTML output.
     */
    public function render_patient_portal_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to access the dashboard.', 'hospital-management-system') . ' <a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Login', 'hospital-management-system') . '</a></p>';
        }
        // Check if the logged-in user is a patient
        if (!$this->is_current_user_patient()) {
            return '<p>' . esc_html__('You do not have permission to access this page.', 'hospital-management-system') . '</p>';
        }
        ob_start();
        load_template(plugin_dir_path(dirname(__FILE__)) . 'public/templates/patient-portal-dashboard.php', false);
        return ob_get_clean();
    }

    /**
     * Render the Online Appointment Booking shortcode.
     *
     * @since    1.0.0
     * @param    array     $atts               Shortcode attributes.
     * @return   string                         The HTML output.
     */
    public function render_online_appointment_booking_shortcode($atts) {
        // Can be accessed by logged-in patients or guests
        ob_start();
        load_template(plugin_dir_path(dirname(__FILE__)) . 'public/templates/online-appointment-booking.php', false);
        return ob_get_clean();
    }

    /**
     * Render the Join Telemedicine Session shortcode.
     *
     * @since    1.0.0
     * @param    array     $atts               Shortcode attributes.
     * @return   string                         The HTML output.
     */
    public function render_join_telemedicine_session_shortcode($atts) {
        // Attributes: session_id (required)
        $atts = shortcode_atts(array(
            'session_id' => 0,
        ), $atts, 'hms_join_telemedicine_session');

        if (empty($atts['session_id'])) {
            return '<p>' . esc_html__('Error: Telemedicine session ID is missing.', 'hospital-management-system') . '</p>';
        }

        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please log in to join the session.', 'hospital-management-system') . ' <a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Login', 'hospital-management-system') . '</a></p>';
        }

        if (!$this->is_current_user_patient()) {
             return '<p>' . esc_html__('You do not have permission to access this page.', 'hospital-management-system') . '</p>';
        }

        $session = $this->get_telemedicine_session(intval($atts['session_id']));
        if (!$session || $session->status !== 'scheduled') {
            return '<p>' . esc_html__('Invalid or unavailable telemedicine session.', 'hospital-management-system') . '</p>';
        }
        
        // Check if the current patient is the one assigned to the session
        $current_patient_id = $this->get_current_patient_id();
        if ($session->patient_id != $current_patient_id) {
            return '<p>' . esc_html__('You are not authorized to join this session.', 'hospital-management-system') . '</p>';
        }

        ob_start();
        // Pass session data to the template
        set_query_var('telemedicine_session', $session);
        load_template(plugin_dir_path(dirname(__FILE__)) . 'public/templates/join-telemedicine-session.php', false);
        return ob_get_clean();
    }


    // --- Telemedicine Framework ---

    /**
     * Schedule a new telemedicine appointment.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of appointment data.
     * @return   int|false|WP_Error           The new appointment's database ID, or false/WP_Error on failure.
     */
    public function schedule_telemedicine_appointment(array $data) {
        // This will interact with the main HMS_Appointments class or a similar mechanism.
        // For now, we assume a separate table `wp_hms_telemed_sessions`.
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_telemed_sessions';

        if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return new WP_Error('missing_data', esc_html__('Required data for scheduling is missing.', 'hospital-management-system'));
        }

        $session_data = array(
            'patient_id'        => intval($data['patient_id']),
            'doctor_id'         => intval($data['doctor_id']),
            'appointment_date'  => sanitize_text_field($data['appointment_date']),
            'appointment_time'  => sanitize_text_field($data['appointment_time']),
            'status'            => 'scheduled', // scheduled, in_progress, completed, cancelled
            'meeting_link'      => '', // To be generated by video conferencing API
            'meeting_id'        => '', // To be generated by video conferencing API
            'notes'             => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
            'created_by'        => get_current_user_id(),
            'created_at'        => current_time('mysql'),
        );

        $format = array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s');
        
        $session_id = $wpdb->insert($table_name, $session_data, $format);

        if ($session_id) {
            $session_id = $wpdb->insert_id;
            // Trigger video conferencing API to generate meeting link and ID
            $api_response = $this->generate_video_conferencing_details($session_id);
            if (!is_wp_error($api_response)) {
                $wpdb->update($table_name, array('meeting_link' => $api_response['link'], 'meeting_id' => $api_response['id']), array('id' => $session_id), array('%s', '%s'), array('%d'));
            }
            HMS_Helper::log_action('Telemedicine Session Scheduled', 'Telemedicine', $session_id, json_encode($session_data));
            return $session_id;
        }
        return false;
    }

    /**
     * Get a single telemedicine session by ID.
     *
     * @since    1.0.0
     * @param    int       $session_id         The session's database ID.
     * @return   object|null                   The session data object, or null if not found.
     */
    public function get_telemedicine_session(int $session_id): ?object {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_telemed_sessions';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $session_id));
    }

    /**
     * Get telemedicine sessions for a specific patient.
     *
     * @since    1.0.0
     * @param    int       $patient_id         The patient's ID.
     * @return   array                          An array of session data objects.
     */
    public function get_patient_telemedicine_sessions(int $patient_id): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_telemed_sessions';
        $staff_table = $wpdb->prefix . 'hms_staff';
        $query = $wpdb->prepare(
            "SELECT ts.*, s.first_name, s.last_name, s.staff_id as doctor_staff_id FROM {$table_name} ts
             JOIN {$staff_table} s ON ts.doctor_id = s.id
             WHERE ts.patient_id = %d ORDER BY ts.appointment_date DESC, ts.appointment_time DESC",
            $patient_id
        );
        return $wpdb->get_results($query);
    }

    /**
     * Generate video conferencing details (placeholder).
     * This would integrate with an external API like Zoom, Google Meet, etc.
     *
     * @since    1.0.0
     * @param    int       $session_id         The session ID for which to generate details.
     * @return   array|WP_Error                An array with 'link' and 'id', or WP_Error.
     */
    private function generate_video_conferencing_details(int $session_id) {
        // Placeholder: Simulate API call to Zoom/Google Meet
        // In a real scenario, you would use their SDKs to create a meeting.
        // For now, generate a dummy link and ID.
        $dummy_link = 'https://meet.jit.si/HMSSession' . $session_id . '_' . uniqid();
        $dummy_id = 'hms_meet_' . $session_id . '_' . uniqid();
        
        // Log this as an integration point that needs to be filled.
        HMS_Helper::log_action('Video Conferencing API Called (Placeholder)', 'Telemedicine', $session_id, "Link: {$dummy_link}, ID: {$dummy_id}");
        
        return array(
            'link' => $dummy_link,
            'id'   => $dummy_id,
        );
    }

    /**
     * Update the status of a telemedicine session.
     *
     * @since    1.0.0
     * @param    int       $session_id         The session's database ID.
     * @param    string    $status             The new status.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_telemedicine_session_status(int $session_id, string $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_telemed_sessions';
        $valid_statuses = array('scheduled', 'in_progress', 'completed', 'cancelled');
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', esc_html__('Invalid status provided.', 'hospital-management-system'));
        }
        $updated = $wpdb->update($table_name, array('status' => $status), array('id' => $session_id), array('%s'), array('%d'));
        if ($updated !== false) {
            HMS_Helper::log_action('Telemedicine Session Status Updated', 'Telemedicine', $session_id, "New Status: {$status}");
            return true;
        }
        return false;
    }


    // --- Patient Portal Helper Functions ---

    /**
     * Check if the currently logged-in user is a patient.
     *
     * @since    1.0.0
     * @return   bool                           True if user is a patient, false otherwise.
     */
    private function is_current_user_patient(): bool {
        if (!is_user_logged_in()) {
            return false;
        }
        $user_id = get_current_user_id();
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        return in_array('hms_patient', $user_roles); // Assuming 'hms_patient' role exists
    }

    /**
     * Get the patient ID associated with the currently logged-in user.
     * This assumes a one-to-one mapping between WordPress user and patient record.
     *
     * @since    1.0.0
     * @return   int|false                     The patient ID, or false if not found.
     */
    private function get_current_patient_id() {
        if (!$this->is_current_user_patient()) {
            return false;
        }
        $user_id = get_current_user_id();
        // Assuming patient record has a 'user_id' field that maps to WP user ID.
        $patient = HMS_Helper::hms_get_row('hms_patients', array('user_id' => $user_id), array('%d'));
        return $patient ? $patient->id : false;
    }


    // --- AJAX Handlers for Patient Portal ---

    public function ajax_patient_register() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        // Implementation for patient registration
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_login() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        // Implementation for patient login (usually handled by WordPress core, but can be customized)
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_forgot_password() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        // Implementation for forgot password
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_book_appointment() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient to book an appointment.', 'hospital-management-system'), 403);
        }
        // Implementation for booking an appointment (can be regular or telemedicine)
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_cancel_appointment() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient.', 'hospital-management-system'), 403);
        }
        // Implementation for cancelling an appointment
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_view_bills() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient.', 'hospital-management-system'), 403);
        }
        $patient_id = $this->get_current_patient_id();
        if (!$patient_id) {
            wp_send_json_error(esc_html__('Patient record not found.', 'hospital-management-system'));
        }
        // Fetch bills for the patient
        // $bills = $this->get_patient_bills($patient_id); // This method would be in HMS_Billing or here
        // wp_send_json_success($bills);
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_make_payment() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient.', 'hospital-management-system'), 403);
        }
        // Implementation for making a payment (will use HMS_Billing methods)
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_view_records() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient.', 'hospital-management-system'), 403);
        }
        $patient_id = $this->get_current_patient_id();
        if (!$patient_id) {
            wp_send_json_error(esc_html__('Patient record not found.', 'hospital-management-system'));
        }
        // Fetch medical records for the patient
        // $records = $this->get_patient_medical_records($patient_id); // This method would be in HMS_Clinical or here
        // wp_send_json_success($records);
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_update_profile() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!$this->is_current_user_patient()) {
            wp_send_json_error(esc_html__('You must be logged in as a patient.', 'hospital-management-system'), 403);
        }
        // Implementation for updating patient profile
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

    public function ajax_patient_change_password() {
        check_ajax_referer('hms_patient_portal_nonce', 'nonce');
        if (!is_user_logged_in()) { // General user check, not just patient role
            wp_send_json_error(esc_html__('You must be logged in.', 'hospital-management-system'), 403);
        }
        // Implementation for changing password
        wp_send_json_error(esc_html__('Not implemented yet.', 'hospital-management-system'));
    }

} // End of class HMS_Patient_Portal
