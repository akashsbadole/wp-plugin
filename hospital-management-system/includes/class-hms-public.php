<?php
/**
 * HMS Public Class
 *
 * Handles public-facing functionality of the plugin.
 */

class HMS_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, HMS_PLUGIN_URL . 'public/css/hms-patient-portal.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, HMS_PLUGIN_URL . 'public/js/hms-patient-portal.js', array('jquery'), $this->version, true);
        
        wp_localize_script($this->plugin_name, 'hms_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hms_patient_portal_nonce'),
        ));
    }

    public function register_shortcodes() {
        add_shortcode('hms_patient_portal', array($this, 'patient_portal_shortcode'));
        add_shortcode('hms_appointment_booking', array($this, 'appointment_booking_shortcode'));
        add_shortcode('hms_bill_payment', array($this, 'bill_payment_shortcode'));
    }

    public function patient_portal_shortcode() {
        if (!is_user_logged_in()) {
            return $this->patient_login_form();
        } else {
            return $this->patient_dashboard();
        }
    }

    private function patient_login_form() {
        ob_start();
        include HMS_PLUGIN_DIR . 'public/templates/patient-login.php';
        return ob_get_clean();
    }

    private function patient_dashboard() {
        $patient_id = $this->get_current_patient_id();
        
        if (!$patient_id) {
            return esc_html__('Patient record not found', 'hospital-management-system');
        }
        
        $patient = get_post($patient_id);
        $appointments = $this->get_patient_appointments($patient_id);
        $bills = $this->get_patient_bills($patient_id);
        $prescriptions = $this->get_patient_prescriptions($patient_id);
        
        ob_start();
        include HMS_PLUGIN_DIR . 'public/templates/patient-dashboard.php';
        return ob_get_clean();
    }

    public function appointment_booking_shortcode() {
        if (!is_user_logged_in()) {
            return esc_html__('Please login to book an appointment', 'hospital-management-system');
        }
        
        $patient_id = $this->get_current_patient_id();
        
        if (!$patient_id) {
            return esc_html__('Patient record not found', 'hospital-management-system');
        }
        
        $departments = $this->get_departments();
        $doctors = $this->get_doctors();
        
        ob_start();
        include HMS_PLUGIN_DIR . 'public/templates/appointment-booking.php';
        return ob_get_clean();
    }

    public function bill_payment_shortcode() {
        if (!is_user_logged_in()) {
            return esc_html__('Please login to view your bills', 'hospital-management-system');
        }
        
        $patient_id = $this->get_current_patient_id();
        
        if (!$patient_id) {
            return esc_html__('Patient record not found', 'hospital-management-system');
        }
        
        $bills = $this->get_patient_bills($patient_id);
        
        ob_start();
        include HMS_PLUGIN_DIR . 'public/templates/bill-payment.php';
        return ob_get_clean();
    }

    private function get_current_patient_id() {
        $user_id = get_current_user_id();
        $patient_id = get_user_meta($user_id, '_patient_id', true);
        
        if ($patient_id) {
            return $patient_id;
        }
        
        // Try to find patient by user email
        $user = get_user_by('ID', $user_id);
        $args = array(
            'post_type' => 'hms_patient',
            'meta_query' => array(
                array(
                    'key' => '_email',
                    'value' => $user->user_email,
                ),
            ),
            'posts_per_page' => 1,
        );
        
        $patients = get_posts($args);
        
        if ($patients) {
            $patient = $patients[0];
            update_user_meta($user_id, '_patient_id', $patient->ID);
            return $patient->ID;
        }
        
        return false;
    }

    private function get_patient_appointments($patient_id) {
        $args = array(
            'post_type' => 'hms_appointment',
            'meta_query' => array(
                array(
                    'key' => '_patient_id',
                    'value' => $patient_id,
                ),
            ),
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => '_appointment_date',
            'order' => 'DESC',
        );
        
        return get_posts($args);
    }

    private function get_patient_bills($patient_id) {
        $args = array(
            'post_type' => 'hms_bill',
            'meta_query' => array(
                array(
                    'key' => '_patient_id',
                    'value' => $patient_id,
                ),
            ),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        return get_posts($args);
    }

    private function get_patient_prescriptions($patient_id) {
        $prescriptions = array();
        
        $appointments = $this->get_patient_appointments($patient_id);
        
        foreach ($appointments as $appointment) {
            $prescription = get_post_meta($appointment->ID, '_prescription', true);
            
            if (!empty($prescription)) {
                $prescriptions[] = array(
                    'appointment_id' => $appointment->ID,
                    'appointment_date' => get_post_meta($appointment->ID, '_appointment_date', true),
                    'doctor_id' => get_post_meta($appointment->ID, '_doctor_id', true),
                    'prescription' => $prescription,
                );
            }
        }
        
        return $prescriptions;
    }

    private function get_departments() {
        $args = array(
            'post_type' => 'hms_department',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        return get_posts($args);
    }

    private function get_doctors() {
        $args = array(
            'role' => 'hms_doctor',
            'posts_per_page' => -1,
            'orderby' => 'display_name',
            'order' => 'ASC',
        );
        
        return get_users($args);
    }
}