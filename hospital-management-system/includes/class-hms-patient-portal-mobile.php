<?php

/**
 * HMS Patient Portal & Mobile Experience
 *
 * This class handles the patient portal and mobile-specific features,
 * including responsive design and PWA capabilities.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Patient_Portal_Mobile {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->hooks();
    }

    /**
     * Register the hooks for the patient portal and mobile features.
     */
    private function hooks() {
        // Enqueue public styles and scripts for patient portal pages
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

        // Add PWA manifest and service worker
        add_action( 'wp_head', array( $this, 'add_pwa_manifest' ), 10, 0 );
        add_action( 'wp_head', array( $this, 'add_service_worker_script' ), 10, 0 );

        // Add theme color meta tag for mobile browsers
        add_action( 'wp_head', array( $this, 'add_theme_color_meta' ), 10, 0 );

        // Add viewport meta tag for responsive design
        add_action( 'wp_head', array( $this, 'add_viewport_meta' ), 1, 0 );

        // Add mobile-friendly body class
        add_filter( 'body_class', array( $this, 'add_mobile_body_class' ) );

        // Handle AJAX requests for patient portal actions
        add_action( 'wp_ajax_hms_patient_login', array( $this, 'ajax_patient_login' ) );
        add_action( 'wp_ajax_nopriv_hms_patient_login', array( $this, 'ajax_patient_login' ) );
        add_action( 'wp_ajax_hms_patient_logout', array( $this, 'ajax_patient_logout' ) );
        add_action( 'wp_ajax_hms_patient_get_appointments', array( $this, 'ajax_patient_get_appointments' ) );
        add_action( 'wp_ajax_hms_patient_get_medical_records', array( $this, 'ajax_patient_get_medical_records' ) );
        add_action( 'wp_ajax_hms_patient_get_bills', array( $this, 'ajax_patient_get_bills' ) );
        add_action( 'wp_ajax_hms_patient_book_appointment', array( $this, 'ajax_patient_book_appointment' ) );
    }

    /**
     * Enqueue public styles and scripts for patient portal.
     */
    public function enqueue_public_assets() {
        // Only load on pages containing our shortcodes
        global $post;
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'hms_patient_portal' ) || has_shortcode( $post->post_content, 'hms_appointment_booking' ) || has_shortcode( $post->post_content, 'hms_bill_payment' ) ) ) {

            // Enqueue patient portal specific CSS
            wp_enqueue_style(
                'hms-patient-portal-mobile',
                HMS_PLUGIN_URL . 'public/css/hms-patient-portal-mobile.css',
                array(),
                $this->version
            );

            // Enqueue patient portal specific JS
            wp_enqueue_script(
                'hms-patient-portal-mobile-js',
                HMS_PLUGIN_URL . 'public/js/hms-patient-portal-mobile.js',
                array( 'jquery' ),
                $this->version,
                true
            );

            // Localize script with AJAX URL and nonce
            wp_localize_script( 'hms-patient-portal-mobile-js', 'hms_patient_vars', array(
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'hms_patient_nonce' ),
                'login_url'   => wp_login_url(),
                'is_user_logged_in' => is_user_logged_in(),
            ) );
        }
    }

    /**
     * Add PWA manifest link tag to the head.
     */
    public function add_pwa_manifest() {
        echo '<link rel="manifest" href="' . esc_url( HMS_PLUGIN_URL . 'public/manifest.json' ) . '">' . "\n";
    }

    /**
     * Add service worker registration script to the head.
     */
    public function add_service_worker_script() {
        if ( ! is_admin() ) {
            echo '<script>
                if (\'serviceWorker\' in navigator) {
                    window.addEventListener(\'load\', function() {
                        navigator.serviceWorker.register(\'' . esc_url( HMS_PLUGIN_URL . 'public/service-worker.js' ) . '\')
                            .then(function(registration) {
                                console.log(\'Service Worker registration successful with scope: \', registration.scope);
                            }).catch(function(error) {
                                console.log(\'Service Worker registration failed: \', error);
                            });
                    });
                }
            </script>' . "\n";
        }
    }

    /**
     * Add theme color meta tag for mobile browsers.
     */
    public function add_theme_color_meta() {
        // Get primary color from white-label settings or use a default
        $primary_color = get_option( 'hms_wl_primary_color', '#007bff' );
        echo '<meta name="theme-color" content="' . esc_attr( $primary_color ) . '">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
    }

    /**
     * Add viewport meta tag for responsive design.
     */
    public function add_viewport_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' . "\n";
    }

    /**
     * Add a mobile-friendly body class.
     *
     * @param array $classes The existing body classes.
     * @return array The modified body classes.
     */
    public function add_mobile_body_class( $classes ) {
        // Add a class to indicate mobile support
        $classes[] = 'hms-mobile-friendly';
        // You could add more specific classes based on device detection if needed
        // For example: $classes[] = 'hms-is-mobile';
        return $classes;
    }

    // --- AJAX Handlers ---

    /**
     * AJAX handler for patient login.
     */
    public function ajax_patient_login() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        $username = sanitize_user( $_POST['username'] );
        $password = trim( $_POST['password'] );
        $remember = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';

        $user = wp_signon( array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ) );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => $user->get_error_message() ) );
        }

        // Check if the user has the 'hms_patient' role or a similar capability
        if ( ! in_array( 'hms_patient', (array) $user->roles ) ) {
            wp_logout(); // Log out the user if they don't have the correct role
            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access the patient portal.', 'hospital-management-system' ) ) );
        }

        wp_send_json_success( array(
            'message' => esc_html__( 'Login successful. Redirecting...', 'hospital-management-system' ),
            'redirect_url' => home_url( '/patient-portal/' ), // Adjust as needed
        ) );
    }

    /**
     * AJAX handler for patient logout.
     */
    public function ajax_patient_logout() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        wp_logout();

        wp_send_json_success( array(
            'message' => esc_html__( 'Logout successful. Redirecting...', 'hospital-management-system' ),
            'redirect_url' => home_url( '/patient-portal/' ), // Adjust as needed
        ) );
    }

    /**
     * AJAX handler to get patient appointments.
     */
    public function ajax_patient_get_appointments() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! in_array( 'hms_patient', (array) wp_get_current_user()->roles ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $user_id = get_current_user_id();
        $patient_post = $this->get_patient_post_by_user_id( $user_id );

        if ( ! $patient_post ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Patient profile not found.', 'hospital-management-system' ) ) );
        }

        $appointments = get_posts( array(
            'post_type'      => 'hms_appointment',
            'posts_per_page' => -1,
            'meta_key'       => '_hms_appointment_patient_id', // Assuming this meta key stores patient post ID
            'meta_value'     => $patient_post->ID,
            'post_status'    => 'publish', // Or any relevant status
            'orderby'        => 'meta_value',
            'meta_key'       => '_hms_appointment_date',
            'order'          => 'DESC',
        ) );

        $appointment_data = array();
        foreach ( $appointments as $appointment ) {
            $doctor_id = get_post_meta( $appointment->ID, '_hms_appointment_doctor_id', true );
            $doctor_post = get_post( $doctor_id );
            $appointment_date = get_post_meta( $appointment->ID, '_hms_appointment_date', true );

            $appointment_data[] = array(
                'id'          => $appointment->ID,
                'date'        => $appointment_date,
                'status'      => get_post_meta( $appointment->ID, '_hms_appointment_status', true ),
                'reason'      => get_the_title( $appointment ), // Assuming title is the reason
                'doctor_name' => $doctor_post ? $doctor_post->post_title : esc_html__( 'N/A', 'hospital-management-system' ),
            );
        }

        wp_send_json_success( $appointment_data );
    }

    /**
     * AJAX handler to get patient medical records.
     */
    public function ajax_patient_get_medical_records() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! in_array( 'hms_patient', (array) wp_get_current_user()->roles ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $user_id = get_current_user_id();
        $patient_post = $this->get_patient_post_by_user_id( $user_id );

        if ( ! $patient_post ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Patient profile not found.', 'hospital-management-system' ) ) );
        }
        
        // This is a simplified example. In a real scenario, you'd fetch from a custom table
        // or a CPT linked to the patient.
        $records = get_posts( array(
            'post_type'      => 'hms_medical_record', // Assuming a CPT for medical records
            'posts_per_page' => -1,
            'meta_key'       => '_hms_record_patient_id', // Meta key linking to patient post ID
            'meta_value'     => $patient_post->ID,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $record_data = array();
        foreach ($records as $record) {
            $doctor_id = get_post_meta($record->ID, '_hms_record_doctor_id', true);
            $doctor_post = get_post($doctor_id);
            $record_data[] = array(
                'id' => $record->ID,
                'date' => $record->post_date,
                'diagnosis' => get_post_meta($record->ID, '_hms_record_diagnosis', true),
                'doctor_name' => $doctor_post ? $doctor_post->post_title : esc_html__('N/A', 'hospital-management-system'),
                'notes' => get_the_content(null, false, $record) // Get content without applying filters
            );
        }

        wp_send_json_success($record_data);
    }

    /**
     * AJAX handler to get patient bills.
     */
    public function ajax_patient_get_bills() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! in_array( 'hms_patient', (array) wp_get_current_user()->roles ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $user_id = get_current_user_id();
        $patient_post = $this->get_patient_post_by_user_id( $user_id );

        if ( ! $patient_post ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Patient profile not found.', 'hospital-management-system' ) ) );
        }

        $bills = get_posts( array(
            'post_type'      => 'hms_bill',
            'posts_per_page' => -1,
            'meta_key'       => '_hms_bill_patient_id', // Meta key linking to patient post ID
            'meta_value'     => $patient_post->ID,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $bill_data = array();
        foreach ($bills as $bill) {
            $bill_data[] = array(
                'id' => $bill->ID,
                'bill_number' => get_the_title($bill),
                'date' => $bill->post_date,
                'total_amount' => get_post_meta($bill->ID, '_hms_bill_total_amount', true),
                'amount_paid' => get_post_meta($bill->ID, '_hms_bill_amount_paid', true),
                'status' => get_post_meta($bill->ID, '_hms_bill_payment_status', true),
            );
        }

        wp_send_json_success($bill_data);
    }

    /**
     * AJAX handler to book an appointment.
     */
    public function ajax_patient_book_appointment() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! in_array( 'hms_patient', (array) wp_get_current_user()->roles ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $user_id = get_current_user_id();
        $patient_post = $this->get_patient_post_by_user_id( $user_id );

        if ( ! $patient_post ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Patient profile not found.', 'hospital-management-system' ) ) );
        }

        $doctor_id = absint( $_POST['doctor_id'] );
        $appointment_date = sanitize_text_field( $_POST['appointment_date'] );
        $reason = sanitize_textarea_field( $_POST['reason'] );

        if ( ! $doctor_id || ! $appointment_date || ! $reason ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Please fill in all required fields.', 'hospital-management-system' ) ) );
        }

        // Create the appointment post
        $appointment_id = wp_insert_post( array(
            'post_title'  => $reason,
            'post_type'   => 'hms_appointment',
            'post_status' => 'publish',
            'post_author' => $user_id, // Or a default admin user
            'meta_input'  => array(
                '_hms_appointment_patient_id' => $patient_post->ID,
                '_hms_appointment_doctor_id' => $doctor_id,
                '_hms_appointment_date' => $appointment_date,
                '_hms_appointment_status' => 'scheduled',
            ),
        ) );

        if ( is_wp_error( $appointment_id ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Could not book appointment. Please try again.', 'hospital-management-system' ) ) );
        }

        // Optionally, send a notification to the doctor or admin
        // $this->send_appointment_notification($appointment_id);

        wp_send_json_success( array(
            'message' => esc_html__( 'Appointment booked successfully.', 'hospital-management-system' ),
            'appointment_id' => $appointment_id,
        ) );
    }

    // --- Helper Methods ---

    /**
     * Get the patient post object associated with a user ID.
     *
     * @param int $user_id The WordPress user ID.
     * @return WP_Post|false The patient post object or false if not found.
     */
    private function get_patient_post_by_user_id( $user_id ) {
        $patients = get_posts( array(
            'post_type'      => 'hms_patient',
            'posts_per_page' => 1,
            'meta_key'       => '_hms_patient_user_id', // Assuming this meta key stores the WP user ID
            'meta_value'     => $user_id,
            'post_status'    => 'publish',
        ) );

        return ! empty( $patients ) ? $patients[0] : false;
    }

    // --- Placeholder for PWA file creation ---
    // You would need to manually create or generate these files:
    // public/manifest.json
    // public/service-worker.js
    // public/css/hms-patient-portal-mobile.css
    // public/js/hms-patient-portal-mobile.js
}
