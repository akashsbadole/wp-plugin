<?php

/**
 * HMS Basic Clinical Features
 *
 * This class handles the core clinical functionalities like file management,
 * custom templates for prescriptions/records, and user role management.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Clinical_Features {

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
     * Get patient summary data for clinical dashboard
     *
     * @param int $patient_id Patient ID
     * @return array Patient summary data
     */
    public function get_patient_summary($patient_id) {
        global $wpdb;
        
        if (!$patient_id) {
            return array();
        }
        
        // Get patient basic info
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hms_patients WHERE id = %d",
            $patient_id
        ));
        
        if (!$patient) {
            return array();
        }
        
        // Get recent appointments
        $recent_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, s.first_name as doctor_first_name, s.last_name as doctor_last_name 
             FROM {$wpdb->prefix}hms_appointments a 
             LEFT JOIN {$wpdb->prefix}hms_staff s ON a.doctor_id = s.id 
             WHERE a.patient_id = %d 
             ORDER BY a.appointment_date DESC 
             LIMIT 5",
            $patient_id
        ));
        
        // Get recent prescriptions
        $recent_prescriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, s.first_name as doctor_first_name, s.last_name as doctor_last_name 
             FROM {$wpdb->prefix}hms_prescriptions p 
             LEFT JOIN {$wpdb->prefix}hms_staff s ON p.doctor_id = s.id 
             WHERE p.patient_id = %d 
             ORDER BY p.created_at DESC 
             LIMIT 5",
            $patient_id
        ));
        
        // Get recent medical records
        $recent_records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hms_medical_records 
             WHERE patient_id = %d 
             ORDER BY created_at DESC 
             LIMIT 5",
            $patient_id
        ));
        
        // Get recent lab tests
        $recent_lab_tests = $wpdb->get_results($wpdb->prepare(
            "SELECT lto.*, lt.test_name
             FROM {$wpdb->prefix}hms_lab_test_orders lto
             LEFT JOIN {$wpdb->prefix}hms_lab_tests lt ON lto.test_id = lt.id
             WHERE lto.patient_id = %d
             ORDER BY lto.created_at DESC
             LIMIT 5",
            $patient_id
        ));
        
        return array(
            'patient' => $patient,
            'recent_appointments' => $recent_appointments,
            'recent_prescriptions' => $recent_prescriptions,
            'recent_records' => $recent_records,
            'recent_lab_tests' => $recent_lab_tests
        );
    }

    /**
     * Get recent clinical activities across all patients
     *
     * @param array $filters Optional filters
     * @return array Recent activities
     */
    public function get_recent_activities($filters = array()) {
        global $wpdb;
        
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 20;
        $date_from = isset($filters['date_from']) ? sanitize_text_field($filters['date_from']) : date('Y-m-d', strtotime('-7 days'));
        
        $activities = array();
        
        // Get recent appointments
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT 'appointment' as type, a.id, a.appointment_date as activity_date, 
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    CONCAT(s.first_name, ' ', s.last_name) as staff_name,
                    a.status, a.created_at
             FROM {$wpdb->prefix}hms_appointments a 
             LEFT JOIN {$wpdb->prefix}hms_patients p ON a.patient_id = p.id 
             LEFT JOIN {$wpdb->prefix}hms_staff s ON a.doctor_id = s.id 
             WHERE a.appointment_date >= %s 
             ORDER BY a.created_at DESC 
             LIMIT %d",
            $date_from, $limit
        ));
        
        // Get recent prescriptions
        $prescriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT 'prescription' as type, p.id, p.created_at as activity_date,
                    CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                    CONCAT(s.first_name, ' ', s.last_name) as staff_name,
                    p.status, p.created_at
             FROM {$wpdb->prefix}hms_prescriptions p 
             LEFT JOIN {$wpdb->prefix}hms_patients pt ON p.patient_id = pt.id 
             LEFT JOIN {$wpdb->prefix}hms_staff s ON p.doctor_id = s.id 
             WHERE p.created_at >= %s 
             ORDER BY p.created_at DESC 
             LIMIT %d",
            $date_from, $limit
        ));
        
        // Merge and sort activities
        $activities = array_merge($appointments, $prescriptions);
        usort($activities, function($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });
        
        return array_slice($activities, 0, $limit);
    }

    /**
     * Get pending clinical tasks
     *
     * @return array Pending tasks
     */
    public function get_pending_tasks() {
        global $wpdb;
        
        $tasks = array();
        
        // Pending prescriptions
        $pending_prescriptions = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_prescriptions WHERE status = 'pending'"
        );
        
        // Today's appointments
        $todays_appointments = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_appointments 
             WHERE DATE(appointment_date) = %s AND status = 'scheduled'",
            current_time('Y-m-d')
        ));
        
        // Pending lab results
        $pending_lab_results = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_lab_test_orders WHERE status = 'pending'"
        );
        
        // Patient queue
        $queue_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hms_queue 
             WHERE queue_date = %s AND status = 'waiting'",
            current_time('Y-m-d')
        ));
        
        return array(
            'pending_prescriptions' => $pending_prescriptions,
            'todays_appointments' => $todays_appointments,
            'pending_lab_results' => $pending_lab_results,
            'queue_count' => $queue_count
        );
    }

    /**
     * Get clinical alerts and notifications
     *
     * @return array Clinical alerts
     */
    public function get_clinical_alerts() {
        global $wpdb;
        
        $alerts = array();
        
        // Critical lab results
        $critical_labs = $wpdb->get_results(
            "SELECT lto.*, lt.test_name, CONCAT(p.first_name, ' ', p.last_name) as patient_name
             FROM {$wpdb->prefix}hms_lab_test_orders lto
             LEFT JOIN {$wpdb->prefix}hms_lab_tests lt ON lto.test_id = lt.id
             LEFT JOIN {$wpdb->prefix}hms_patients p ON lto.patient_id = p.id
             WHERE lto.status = 'completed' AND lto.is_critical = 1 AND lto.notified = 0
             ORDER BY lto.completed_at DESC"
        );
        
        foreach ($critical_labs as $lab) {
            $alerts[] = array(
                'type' => 'critical_lab',
                'priority' => 'high',
                'message' => sprintf(__('Critical lab result for %s', 'hospital-management-system'), $lab->patient_name),
                'data' => $lab
            );
        }
        
        // Overdue appointments
        $overdue_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name 
             FROM {$wpdb->prefix}hms_appointments a 
             LEFT JOIN {$wpdb->prefix}hms_patients p ON a.patient_id = p.id 
             WHERE a.appointment_date < %s AND a.status = 'scheduled'",
            current_time('Y-m-d H:i:s')
        ));
        
        foreach ($overdue_appointments as $appointment) {
            $alerts[] = array(
                'type' => 'overdue_appointment',
                'priority' => 'medium',
                'message' => sprintf(__('Overdue appointment for %s', 'hospital-management-system'), $appointment->patient_name),
                'data' => $appointment
            );
        }
        
        return $alerts;
    }

    /**
     * Generate patient timeline for clinical view
     *
     * @param int $patient_id Patient ID
     * @return array Patient timeline events
     */
    public function generate_patient_timeline($patient_id) {
        global $wpdb;
        
        if (!$patient_id) {
            return array();
        }
        
        $timeline = array();
        
        // Get appointments
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT 'appointment' as event_type, id, appointment_date as event_date, 
                    'Appointment' as event_title, status, created_at
             FROM {$wpdb->prefix}hms_appointments 
             WHERE patient_id = %d",
            $patient_id
        ));
        
        // Get prescriptions
        $prescriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT 'prescription' as event_type, id, created_at as event_date,
                    'Prescription' as event_title, status, created_at
             FROM {$wpdb->prefix}hms_prescriptions 
             WHERE patient_id = %d",
            $patient_id
        ));
        
        // Get medical records
        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT 'medical_record' as event_type, id, created_at as event_date,
                    diagnosis as event_title, 'completed' as status, created_at
             FROM {$wpdb->prefix}hms_medical_records 
             WHERE patient_id = %d",
            $patient_id
        ));
        
        // Get lab tests
        $lab_tests = $wpdb->get_results($wpdb->prepare(
            "SELECT 'lab_test' as event_type, lto.id, lto.created_at as event_date,
                    lt.test_name as event_title, lto.status, lto.created_at
             FROM {$wpdb->prefix}hms_lab_test_orders lto
             LEFT JOIN {$wpdb->prefix}hms_lab_tests lt ON lto.test_id = lt.id
             WHERE lto.patient_id = %d",
            $patient_id
        ));
        
        // Merge all events
        $timeline = array_merge($appointments, $prescriptions, $records, $lab_tests);
        
        // Sort by event date
        usort($timeline, function($a, $b) {
            return strtotime($b->event_date) - strtotime($a->event_date);
        });
        
        return $timeline;
    }

    /**
     * Register the hooks for the clinical features.
     */
    private function hooks() {
        add_action( 'init', array( $this, 'register_clinical_post_types' ) );
        add_action( 'admin_init', array( $this, 'add_role_capabilities' ) );
        add_filter( 'wp_handle_upload', array( $this, 'handle_clinical_file_upload' ), 10, 2 );
        add_action( 'add_meta_boxes', array( $this, 'add_clinical_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_clinical_meta_box_data' ) );
        
        // AJAX hooks for clinical notes
        add_action( 'wp_ajax_hms_add_clinical_note', array( $this, 'ajax_add_clinical_note' ) );
        add_action( 'wp_ajax_hms_update_clinical_note', array( $this, 'ajax_update_clinical_note' ) );
        add_action( 'wp_ajax_hms_get_clinical_notes', array( $this, 'ajax_get_clinical_notes' ) );
        add_action( 'wp_ajax_hms_delete_clinical_note', array( $this, 'ajax_delete_clinical_note' ) );
        
        // AJAX hooks for clinical alerts
        add_action( 'wp_ajax_hms_create_clinical_alert', array( $this, 'ajax_create_clinical_alert' ) );
        add_action( 'wp_ajax_hms_dismiss_clinical_alert', array( $this, 'ajax_dismiss_clinical_alert' ) );
        add_action( 'wp_ajax_hms_get_clinical_alerts', array( $this, 'ajax_get_clinical_alerts' ) );
        
        // Admin notices for clinical alerts
        add_action( 'admin_notices', array( $this, 'display_clinical_alert_notices' ) );
        
        // Add filters for custom template rendering if needed
        // add_filter( 'single_template', array( $this, 'load_custom_clinical_template' ) );
    }

    /**
     * Register custom post types for clinical features.
     */
    public function register_clinical_post_types() {
        // Prescription Post Type
        $labels = array(
            'name'                  => _x( 'Prescriptions', 'Post Type General Name', 'hospital-management-system' ),
            'singular_name'         => _x( 'Prescription', 'Post Type Singular Name', 'hospital-management-system' ),
            'menu_name'             => esc_html__( 'Prescriptions', 'hospital-management-system' ),
            'name_admin_bar'        => esc_html__( 'Prescription', 'hospital-management-system' ),
            'archives'              => esc_html__( 'Prescription Archives', 'hospital-management-system' ),
            'attributes'            => esc_html__( 'Prescription Attributes', 'hospital-management-system' ),
            'parent_item_colon'     => esc_html__( 'Parent Prescription:', 'hospital-management-system' ),
            'all_items'             => esc_html__( 'All Prescriptions', 'hospital-management-system' ),
            'add_new_item'          => esc_html__( 'Add New Prescription', 'hospital-management-system' ),
            'add_new'               => esc_html__( 'Add New', 'hospital-management-system' ),
            'new_item'              => esc_html__( 'New Prescription', 'hospital-management-system' ),
            'edit_item'             => esc_html__( 'Edit Prescription', 'hospital-management-system' ),
            'update_item'           => esc_html__( 'Update Prescription', 'hospital-management-system' ),
            'view_item'             => esc_html__( 'View Prescription', 'hospital-management-system' ),
            'view_items'            => esc_html__( 'View Prescriptions', 'hospital-management-system' ),
            'search_items'          => esc_html__( 'Search Prescription', 'hospital-management-system' ),
            'not_found'             => esc_html__( 'Not found', 'hospital-management-system' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'hospital-management-system' ),
            'featured_image'        => esc_html__( 'Featured Image', 'hospital-management-system' ),
            'set_featured_image'    => esc_html__( 'Set featured image', 'hospital-management-system' ),
            'remove_featured_image' => esc_html__( 'Remove featured image', 'hospital-management-system' ),
            'use_featured_image'    => esc_html__( 'Use as featured image', 'hospital-management-system' ),
            'insert_into_item'      => esc_html__( 'Insert into prescription', 'hospital-management-system' ),
            'uploaded_to_this_item' => esc_html__( 'Uploaded to this prescription', 'hospital-management-system' ),
            'items_list'            => esc_html__( 'Prescriptions list', 'hospital-management-system' ),
            'items_list_navigation' => esc_html__( 'Prescriptions list navigation', 'hospital-management-system' ),
            'filter_items_list'     => esc_html__( 'Filter prescriptions list', 'hospital-management-system' ),
        );
        $args = array(
            'label'                 => esc_html__( 'Prescription', 'hospital-management-system' ),
            'description'           => esc_html__( 'Holds patient prescriptions', 'hospital-management-system' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions' ),
            'hierarchical'          => false,
            'public'                => false, // Not publicly queryable on the front end
            'show_ui'               => true,
            'show_in_menu'          => 'hms_dashboard', // Show under HMS Dashboard
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-prescription-bottle',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => false, // Disable Gutenberg for now
        );
        register_post_type( 'hms_prescription', $args );

        // Medical Record Post Type
        $labels = array(
            'name'                  => _x( 'Medical Records', 'Post Type General Name', 'hospital-management-system' ),
            'singular_name'         => _x( 'Medical Record', 'Post Type Singular Name', 'hospital-management-system' ),
            'menu_name'             => esc_html__( 'Medical Records', 'hospital-management-system' ),
            'name_admin_bar'        => esc_html__( 'Medical Record', 'hospital-management-system' ),
            'archives'              => esc_html__( 'Medical Record Archives', 'hospital-management-system' ),
            'attributes'            => esc_html__( 'Medical Record Attributes', 'hospital-management-system' ),
            'parent_item_colon'     => esc_html__( 'Parent Medical Record:', 'hospital-management-system' ),
            'all_items'             => esc_html__( 'All Medical Records', 'hospital-management-system' ),
            'add_new_item'          => esc_html__( 'Add New Medical Record', 'hospital-management-system' ),
            'add_new'               => esc_html__( 'Add New', 'hospital-management-system' ),
            'new_item'              => esc_html__( 'New Medical Record', 'hospital-management-system' ),
            'edit_item'             => esc_html__( 'Edit Medical Record', 'hospital-management-system' ),
            'update_item'           => esc_html__( 'Update Medical Record', 'hospital-management-system' ),
            'view_item'             => esc_html__( 'View Medical Record', 'hospital-management-system' ),
            'view_items'            => esc_html__( 'View Medical Records', 'hospital-management-system' ),
            'search_items'          => esc_html__( 'Search Medical Record', 'hospital-management-system' ),
            'not_found'             => esc_html__( 'Not found', 'hospital-management-system' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'hospital-management-system' ),
            'featured_image'        => esc_html__( 'Featured Image', 'hospital-management-system' ),
            'set_featured_image'    => esc_html__( 'Set featured image', 'hospital-management-system' ),
            'remove_featured_image' => esc_html__( 'Remove featured image', 'hospital-management-system' ),
            'use_featured_image'    => esc_html__( 'Use as featured image', 'hospital-management-system' ),
            'insert_into_item'      => esc_html__( 'Insert into medical record', 'hospital-management-system' ),
            'uploaded_to_this_item' => esc_html__( 'Uploaded to this medical record', 'hospital-management-system' ),
            'items_list'            => esc_html__( 'Medical Records list', 'hospital-management-system' ),
            'items_list_navigation' => esc_html__( 'Medical Records list navigation', 'hospital-management-system' ),
            'filter_items_list'     => esc_html__( 'Filter medical records list', 'hospital-management-system' ),
        );
        $args = array(
            'label'                 => esc_html__( 'Medical Record', 'hospital-management-system' ),
            'description'           => esc_html__( 'Holds patient medical records and history', 'hospital-management-system' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions', 'comments' ), // Comments for internal notes
            'hierarchical'          => false,
            'public'                => false, // Not publicly queryable
            'show_ui'               => true,
            'show_in_menu'          => 'hms_dashboard', // Show under HMS Dashboard
            'menu_position'         => 26,
            'menu_icon'             => 'dashicons-clipboard',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => false, // Disable Gutenberg
        );
        register_post_type( 'hms_medical_record', $args );

        // Flush rewrite rules to avoid 404 errors for new CPTs
        flush_rewrite_rules();
    }

    /**
     * Add custom capabilities to user roles.
     */
    public function add_role_capabilities() {
        // Define capabilities for clinical post types
        $clinical_caps = array(
            'edit_hms_prescription',
            'read_hms_prescription',
            'delete_hms_prescription',
            'edit_hms_prescriptions',
            'edit_others_hms_prescriptions',
            'publish_hms_prescriptions',
            'read_private_hms_prescriptions',
            'delete_hms_prescriptions',
            'delete_private_hms_prescriptions',
            'delete_published_hms_prescriptions',
            'delete_others_hms_prescriptions',
            'edit_private_hms_prescriptions',
            'edit_published_hms_prescriptions',

            'edit_hms_medical_record',
            'read_hms_medical_record',
            'delete_hms_medical_record',
            'edit_hms_medical_records',
            'edit_others_hms_medical_records',
            'publish_hms_medical_records',
            'read_private_hms_medical_records',
            'delete_hms_medical_records',
            'delete_private_hms_medical_records',
            'delete_published_hms_medical_records',
            'delete_others_hms_medical_records',
            'edit_private_hms_medical_records',
            'edit_published_hms_medical_records',
        );

        // Add capabilities to Administrator role
        $role = get_role( 'administrator' );
        if ( $role ) {
            foreach ( $clinical_caps as $cap ) {
                $role->add_cap( $cap );
            }
        }

        // Add capabilities to Doctor role (if it exists, otherwise create it)
        $doctor_role = get_role( 'hms_doctor' );
        if ( ! $doctor_role ) {
            add_role( 'hms_doctor', esc_html__( 'Doctor', 'hospital-management-system' ), array( 'read' => true ) );
            $doctor_role = get_role( 'hms_doctor' );
        }
        if ( $doctor_role ) {
            // Doctors can manage their own prescriptions and medical records
            $doctor_role->add_cap( 'edit_hms_prescriptions' );
            $doctor_role->add_cap( 'read_hms_prescription' );
            $doctor_role->add_cap( 'delete_hms_prescriptions' );
            $doctor_role->add_cap( 'publish_hms_prescriptions' );
            $doctor_role->add_cap( 'read_private_hms_prescriptions' );
            $doctor_role->add_cap( 'edit_hms_medical_records' );
            $doctor_role->add_cap( 'read_hms_medical_record' );
            $doctor_role->add_cap( 'delete_hms_medical_records' );
            $doctor_role->add_cap( 'publish_hms_medical_records' );
            $doctor_role->add_cap( 'read_private_hms_medical_records' );
            // Add core HMS capabilities
            $doctor_role->add_cap( 'manage_patients' );
            $doctor_role->add_cap( 'manage_clinical' );
            $doctor_role->add_cap( 'view_sensitive_data' );
        }

        // Add capabilities to Nurse role (if it exists, otherwise create it)
        $nurse_role = get_role( 'hms_nurse' );
        if ( ! $nurse_role ) {
            add_role( 'hms_nurse', esc_html__( 'Nurse', 'hospital-management-system' ), array( 'read' => true ) );
            $nurse_role = get_role( 'hms_nurse' );
        }
        if ( $nurse_role ) {
            // Nurses can read and maybe edit (depending on hospital policy) medical records
            $nurse_role->add_cap( 'read_hms_prescription' );
            $nurse_role->add_cap( 'read_hms_medical_record' );
            $nurse_role->add_cap( 'read_private_hms_medical_records' );
            // Add core HMS capabilities
            $nurse_role->add_cap( 'manage_patients' );
            $nurse_role->add_cap( 'manage_clinical' );
            $nurse_role->add_cap( 'view_sensitive_data' );
        }
    }

    /**
     * Handle file uploads for clinical documents.
     * This hook runs after a file is uploaded to the WordPress media library.
     *
     * @param array $upload Array of upload data.
     * @param array $context Context of the upload (e.g., 'hms_clinical_upload').
     * @return array Modified upload data.
     */
    public function handle_clinical_file_upload( $upload, $context ) {
        // Only process uploads from our custom context
        if ( 'hms_clinical_upload' !== $context ) {
            return $upload;
        }

        // Check for errors
        if ( isset( $upload['error'] ) && $upload['error'] ) {
            return $upload;
        }

        // Add additional security or processing here if needed
        // For example, scan files for malware, restrict file types further, etc.

        // Log the upload action
        if ( class_exists( 'HMS_Helper' ) ) {
            HMS_Helper::log_action( 'Clinical File Uploaded', 'Media', $upload['attachment_id'], json_encode( $upload ) );
        }

        return $upload;
    }

    /**
     * Add meta boxes for clinical post types.
     */
    public function add_clinical_meta_boxes() {
        // Meta box for Prescriptions
        add_meta_box(
            'hms_prescription_details',
            esc_html__( 'Prescription Details', 'hospital-management-system' ),
            array( $this, 'render_prescription_details_meta_box' ),
            'hms_prescription',
            'normal',
            'default'
        );

        // Meta box for Medical Records
        add_meta_box(
            'hms_medical_record_details',
            esc_html__( 'Medical Record Details', 'hospital-management-system' ),
            array( $this, 'render_medical_record_details_meta_box' ),
            'hms_medical_record',
            'normal',
            'default'
        );
    }

    /**
     * Render the prescription details meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_prescription_details_meta_box( $post ) {
        wp_nonce_field( 'hms_save_prescription_details_meta_box_data', 'hms_prescription_details_meta_box_nonce' );

        // Get existing values
        $patient_id = get_post_meta( $post->ID, '_hms_patient_id', true );
        $appointment_id = get_post_meta( $post->ID, '_hms_appointment_id', true );
        $doctor_id = get_post_meta( $post->ID, '_hms_doctor_id', true );
        $medication_details = get_post_meta( $post->ID, '_hms_medication_details', true );
        $notes = get_post_meta( $post->ID, '_hms_notes', true );

        // Fetch patients and doctors for dropdowns
        $patients = get_posts( array( 'post_type' => 'hms_patient', 'numberposts' => -1, 'post_status' => 'publish' ) );
        $doctors = get_users( array( 'role' => 'hms_doctor' ) );
        $appointments = get_posts( array( 'post_type' => 'hms_appointment', 'numberposts' => -1, 'post_status' => 'publish' ) );

        ?>
        <p>
            <label for="hms_patient_id"><?php esc_html_e( 'Patient:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_patient_id" name="hms_patient_id" style="width: 100%;">
                <option value=""><?php esc_html_e( 'Select Patient', 'hospital-management-system' ); ?></option>
                <?php foreach ( $patients as $patient ) : ?>
                    <option value="<?php echo esc_attr( $patient->ID ); ?>" <?php selected( $patient_id, $patient->ID ); ?>>
                        <?php echo esc_html( get_the_title( $patient->ID ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_appointment_id"><?php esc_html_e( 'Associated Appointment:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_appointment_id" name="hms_appointment_id" style="width: 100%;">
                <option value=""><?php esc_html_e( 'Select Appointment', 'hospital-management-system' ); ?></option>
                <?php foreach ( $appointments as $appointment ) : ?>
                    <option value="<?php echo esc_attr( $appointment->ID ); ?>" <?php selected( $appointment_id, $appointment->ID ); ?>>
                        <?php echo esc_html( get_the_title( $appointment->ID ) . ' - ' . get_post_meta($appointment->ID, '_appointment_date', true) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_doctor_id"><?php esc_html_e( 'Prescribing Doctor:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_doctor_id" name="hms_doctor_id" style="width: 100%;">
                <option value=""><?php esc_html_e( 'Select Doctor', 'hospital-management-system' ); ?></option>
                <?php foreach ( $doctors as $doctor ) : ?>
                    <option value="<?php echo esc_attr( $doctor->ID ); ?>" <?php selected( $doctor_id, $doctor->ID ); ?>>
                        <?php echo esc_html( $doctor->display_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_medication_details"><?php esc_html_e( 'Medication Details:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_medication_details" name="hms_medication_details" rows="5" style="width: 100%;"><?php echo esc_textarea( $medication_details ); ?></textarea>
            <span class="description"><?php esc_html_e( 'List medications, dosage, frequency, etc.', 'hospital-management-system' ); ?></span>
        </p>
        <p>
            <label for="hms_notes"><?php esc_html_e( 'Additional Notes:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_notes" name="hms_notes" rows="3" style="width: 100%;"><?php echo esc_textarea( $notes ); ?></textarea>
        </p>
        <?php
    }

    /**
     * Render the medical record details meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_medical_record_details_meta_box( $post ) {
        wp_nonce_field( 'hms_save_medical_record_details_meta_box_data', 'hms_medical_record_details_meta_box_nonce' );

        // Get existing values
        $patient_id = get_post_meta( $post->ID, '_hms_patient_id', true );
        $doctor_id = get_post_meta( $post->ID, '_hms_doctor_id', true );
        $record_type = get_post_meta( $post->ID, '_hms_record_type', true );
        $diagnosis = get_post_meta( $post->ID, '_hms_diagnosis', true );
        $symptoms = get_post_meta( $post->ID, '_hms_symptoms', true );
        $treatment_plan = get_post_meta( $post->ID, '_hms_treatment_plan', true );
        $follow_up_date = get_post_meta( $post->ID, '_hms_follow_up_date', true );

        $patients = get_posts( array( 'post_type' => 'hms_patient', 'numberposts' => -1, 'post_status' => 'publish' ) );
        $doctors = get_users( array( 'role' => 'hms_doctor' ) );
        $record_types = array(
            'consultation' => esc_html__( 'Consultation', 'hospital-management-system' ),
            'lab_report'    => esc_html__( 'Lab Report', 'hospital-management-system' ),
            'imaging_scan'  => esc_html__( 'Imaging Scan', 'hospital-management-system' ),
            'surgery_note'  => esc_html__( 'Surgery Note', 'hospital-management-system' ),
            'discharge_summary' => esc_html__( 'Discharge Summary', 'hospital-management-system' ),
        );
        ?>
        <p>
            <label for="hms_patient_id_mr"><?php esc_html_e( 'Patient:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_patient_id_mr" name="hms_patient_id_mr" style="width: 100%;">
                <option value=""><?php esc_html_e( 'Select Patient', 'hospital-management-system' ); ?></option>
                <?php foreach ( $patients as $patient ) : ?>
                    <option value="<?php echo esc_attr( $patient->ID ); ?>" <?php selected( $patient_id, $patient->ID ); ?>>
                        <?php echo esc_html( get_the_title( $patient->ID ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_doctor_id_mr"><?php esc_html_e( 'Attending Doctor:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_doctor_id_mr" name="hms_doctor_id_mr" style="width: 100%;">
                <option value=""><?php esc_html_e( 'Select Doctor', 'hospital-management-system' ); ?></option>
                <?php foreach ( $doctors as $doctor ) : ?>
                    <option value="<?php echo esc_attr( $doctor->ID ); ?>" <?php selected( $doctor_id, $doctor->ID ); ?>>
                        <?php echo esc_html( $doctor->display_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_record_type"><?php esc_html_e( 'Record Type:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_record_type" name="hms_record_type" style="width: 100%;">
                <?php foreach ( $record_types as $key => $label ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $record_type, $key ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_diagnosis"><?php esc_html_e( 'Diagnosis:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_diagnosis" name="hms_diagnosis" rows="3" style="width: 100%;"><?php echo esc_textarea( $diagnosis ); ?></textarea>
        </p>
        <p>
            <label for="hms_symptoms"><?php esc_html_e( 'Symptoms:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_symptoms" name="hms_symptoms" rows="3" style="width: 100%;"><?php echo esc_textarea( $symptoms ); ?></textarea>
        </p>
        <p>
            <label for="hms_treatment_plan"><?php esc_html_e( 'Treatment Plan:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_treatment_plan" name="hms_treatment_plan" rows="5" style="width: 100%;"><?php echo esc_textarea( $treatment_plan ); ?></textarea>
        </p>
         <p>
            <label for="hms_follow_up_date"><?php esc_html_e( 'Follow-up Date:', 'hospital-management-system' ); ?></label><br>
            <input type="date" id="hms_follow_up_date" name="hms_follow_up_date" value="<?php echo esc_attr( $follow_up_date ); ?>" class="regular-text">
        </p>
        <?php
    }

    /**
     * Save meta box data for clinical post types.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_clinical_meta_box_data( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['hms_prescription_details_meta_box_nonce'] ) && ! isset( $_POST['hms_medical_record_details_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( isset( $_POST['hms_prescription_details_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['hms_prescription_details_meta_box_nonce'], 'hms_save_prescription_details_meta_box_data' ) ) {
            return;
        }
        if ( isset( $_POST['hms_medical_record_details_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['hms_medical_record_details_meta_box_nonce'], 'hms_save_medical_record_details_meta_box_data' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'hms_prescription' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        } elseif ( isset( $_POST['post_type'] ) && 'hms_medical_record' === $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        } else {
            return;
        }

        // Sanitize and save the data for prescriptions
        if ( isset( $_POST['hms_patient_id'] ) ) {
            update_post_meta( $post_id, '_hms_patient_id', absint( $_POST['hms_patient_id'] ) );
        }
        if ( isset( $_POST['hms_appointment_id'] ) ) {
            update_post_meta( $post_id, '_hms_appointment_id', absint( $_POST['hms_appointment_id'] ) );
        }
        if ( isset( $_POST['hms_doctor_id'] ) ) {
            update_post_meta( $post_id, '_hms_doctor_id', absint( $_POST['hms_doctor_id'] ) );
        }
        if ( isset( $_POST['hms_medication_details'] ) ) {
            update_post_meta( $post_id, '_hms_medication_details', sanitize_textarea_field( $_POST['hms_medication_details'] ) );
        }
        if ( isset( $_POST['hms_notes'] ) ) {
            update_post_meta( $post_id, '_hms_notes', sanitize_textarea_field( $_POST['hms_notes'] ) );
        }

        // Sanitize and save the data for medical records
        if ( isset( $_POST['hms_patient_id_mr'] ) ) {
            update_post_meta( $post_id, '_hms_patient_id', absint( $_POST['hms_patient_id_mr'] ) );
        }
        if ( isset( $_POST['hms_doctor_id_mr'] ) ) {
            update_post_meta( $post_id, '_hms_doctor_id', absint( $_POST['hms_doctor_id_mr'] ) );
        }
        if ( isset( $_POST['hms_record_type'] ) ) {
            update_post_meta( $post_id, '_hms_record_type', sanitize_key( $_POST['hms_record_type'] ) );
        }
        if ( isset( $_POST['hms_diagnosis'] ) ) {
            update_post_meta( $post_id, '_hms_diagnosis', sanitize_textarea_field( $_POST['hms_diagnosis'] ) );
        }
        if ( isset( $_POST['hms_symptoms'] ) ) {
            update_post_meta( $post_id, '_hms_symptoms', sanitize_textarea_field( $_POST['hms_symptoms'] ) );
        }
        if ( isset( $_POST['hms_treatment_plan'] ) ) {
            update_post_meta( $post_id, '_hms_treatment_plan', sanitize_textarea_field( $_POST['hms_treatment_plan'] ) );
        }
        if ( isset( $_POST['hms_follow_up_date'] ) ) {
            update_post_meta( $post_id, '_hms_follow_up_date', sanitize_text_field( $_POST['hms_follow_up_date'] ) );
        }
    }

    /**
     * Load a custom template for clinical post types if it exists in the theme.
     *
     * @param string $template Path to the original template.
     * @return string Path to the custom template or original.
     */
    public function load_custom_clinical_template( $template ) {
        global $post;

        if ( 'hms_prescription' === $post->post_type ) {
            $custom_template = locate_template( array( 'single-hms_prescription.php' ) );
            if ( $custom_template ) {
                return $custom_template;
            }
        }
        if ( 'hms_medical_record' === $post->post_type ) {
            $custom_template = locate_template( array( 'single-hms_medical_record.php' ) );
            if ( $custom_template ) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Add clinical note for a patient
     *
     * @param array $data Clinical note data
     * @return int|false Note ID on success, false on failure
     */
    public function add_clinical_note($data) {
        global $wpdb;
        
        if (empty($data['patient_id']) || empty($data['note_content'])) {
            return false;
        }
        
        $note_data = array(
            'patient_id' => intval($data['patient_id']),
            'note_type' => isset($data['note_type']) ? sanitize_text_field($data['note_type']) : 'general',
            'note_title' => isset($data['note_title']) ? sanitize_text_field($data['note_title']) : '',
            'note_content' => sanitize_textarea_field($data['note_content']),
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'is_private' => isset($data['is_private']) ? intval($data['is_private']) : 0,
            'priority' => isset($data['priority']) ? sanitize_text_field($data['priority']) : 'normal'
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'hms_clinical_notes',
            $note_data,
            array('%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s')
        );
        
        if ($result !== false) {
            $note_id = $wpdb->insert_id;
            
            // Log the action
            if (class_exists('HMS_Helper')) {
                HMS_Helper::log_action('Clinical Note Added', 'Clinical Note', $note_id, json_encode($note_data));
            }
            
            return $note_id;
        }
        
        return false;
    }

    /**
     * Get clinical notes for a patient
     *
     * @param int $patient_id Patient ID
     * @param array $filters Optional filters
     * @return array Clinical notes
     */
    public function get_patient_clinical_notes($patient_id, $filters = array()) {
        global $wpdb;
        
        if (!$patient_id) {
            return array();
        }
        
        $where_clauses = array('cn.patient_id = %d');
        $where_values = array($patient_id);
        
        // Add filters
        if (isset($filters['note_type']) && !empty($filters['note_type'])) {
            $where_clauses[] = 'cn.note_type = %s';
            $where_values[] = $filters['note_type'];
        }
        
        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $where_clauses[] = 'cn.priority = %s';
            $where_values[] = $filters['priority'];
        }
        
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $where_clauses[] = 'DATE(cn.created_at) >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $where_clauses[] = 'DATE(cn.created_at) <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_clauses);
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        
        $query = "SELECT cn.*, u.display_name as created_by_name 
                  FROM {$wpdb->prefix}hms_clinical_notes cn 
                  LEFT JOIN {$wpdb->users} u ON cn.created_by = u.ID 
                  {$where_clause} 
                  ORDER BY cn.created_at DESC 
                  LIMIT {$limit}";
        
        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Update clinical note
     *
     * @param int $note_id Note ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function update_clinical_note($note_id, $data) {
        global $wpdb;
        
        if (!$note_id) {
            return false;
        }
        
        $update_data = array();
        $format = array();
        
        $allowed_fields = array('note_type', 'note_title', 'note_content', 'is_private', 'priority');
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'note_content') {
                    $update_data[$field] = sanitize_textarea_field($data[$field]);
                } elseif ($field === 'is_private') {
                    $update_data[$field] = intval($data[$field]);
                    $format[] = '%d';
                    continue;
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
                $format[] = '%s';
            }
        }
        
        if (empty($update_data)) {
            return true;
        }
        
        $update_data['updated_at'] = current_time('mysql');
        $format[] = '%s';
        
        $result = $wpdb->update(
            $wpdb->prefix . 'hms_clinical_notes',
            $update_data,
            array('id' => $note_id),
            $format,
            array('%d')
        );
        
        if ($result !== false) {
            // Log the action
            if (class_exists('HMS_Helper')) {
                HMS_Helper::log_action('Clinical Note Updated', 'Clinical Note', $note_id, json_encode($update_data));
            }
            return true;
        }
        
        return false;
    }

    /**
     * Create clinical alert
     *
     * @param array $data Alert data
     * @return int|false Alert ID on success, false on failure
     */
    public function create_clinical_alert($data) {
        global $wpdb;
        
        if (empty($data['alert_type']) || empty($data['message'])) {
            return false;
        }
        
        $alert_data = array(
            'patient_id' => isset($data['patient_id']) ? intval($data['patient_id']) : null,
            'alert_type' => sanitize_text_field($data['alert_type']),
            'priority' => isset($data['priority']) ? sanitize_text_field($data['priority']) : 'medium',
            'message' => sanitize_textarea_field($data['message']),
            'details' => isset($data['details']) ? sanitize_textarea_field($data['details']) : '',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'status' => 'active',
            'expires_at' => isset($data['expires_at']) ? sanitize_text_field($data['expires_at']) : null
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'hms_clinical_alerts',
            $alert_data,
            array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        if ($result !== false) {
            $alert_id = $wpdb->insert_id;
            
            // Log the action
            if (class_exists('HMS_Helper')) {
                HMS_Helper::log_action('Clinical Alert Created', 'Clinical Alert', $alert_id, json_encode($alert_data));
            }
            
            // Send notifications if needed
            $this->send_alert_notifications($alert_id, $alert_data);
            
            return $alert_id;
        }
        
        return false;
    }

    /**
     * Get active clinical alerts
     *
     * @param array $filters Optional filters
     * @return array Active alerts
     */
    public function get_active_clinical_alerts($filters = array()) {
        global $wpdb;
        
        $where_clauses = array("ca.status = 'active'");
        $where_values = array();
        
        // Add expiry check
        $where_clauses[] = "(ca.expires_at IS NULL OR ca.expires_at > %s)";
        $where_values[] = current_time('mysql');
        
        // Add filters
        if (isset($filters['patient_id']) && !empty($filters['patient_id'])) {
            $where_clauses[] = 'ca.patient_id = %d';
            $where_values[] = intval($filters['patient_id']);
        }
        
        if (isset($filters['alert_type']) && !empty($filters['alert_type'])) {
            $where_clauses[] = 'ca.alert_type = %s';
            $where_values[] = $filters['alert_type'];
        }
        
        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $where_clauses[] = 'ca.priority = %s';
            $where_values[] = $filters['priority'];
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_clauses);
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 20;
        
        $query = "SELECT ca.*, 
                         CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                         u.display_name as created_by_name 
                  FROM {$wpdb->prefix}hms_clinical_alerts ca 
                  LEFT JOIN {$wpdb->prefix}hms_patients p ON ca.patient_id = p.id 
                  LEFT JOIN {$wpdb->users} u ON ca.created_by = u.ID 
                  {$where_clause} 
                  ORDER BY ca.priority DESC, ca.created_at DESC 
                  LIMIT {$limit}";
        
        return $wpdb->get_results($wpdb->prepare($query, $where_values));
    }

    /**
     * Dismiss clinical alert
     *
     * @param int $alert_id Alert ID
     * @return bool Success status
     */
    public function dismiss_clinical_alert($alert_id) {
        global $wpdb;
        
        if (!$alert_id) {
            return false;
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'hms_clinical_alerts',
            array(
                'status' => 'dismissed',
                'dismissed_by' => get_current_user_id(),
                'dismissed_at' => current_time('mysql')
            ),
            array('id' => $alert_id),
            array('%s', '%d', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Log the action
            if (class_exists('HMS_Helper')) {
                HMS_Helper::log_action('Clinical Alert Dismissed', 'Clinical Alert', $alert_id, '');
            }
            return true;
        }
        
        return false;
    }

    /**
     * Send alert notifications
     *
     * @param int $alert_id Alert ID
     * @param array $alert_data Alert data
     */
    private function send_alert_notifications($alert_id, $alert_data) {
        // This method can be extended to send email, SMS, or push notifications
        // For now, we'll just create a WordPress admin notice
        
        if ($alert_data['priority'] === 'high' || $alert_data['priority'] === 'critical') {
            // Store in transient for admin notice
            $notices = get_transient('hms_clinical_alerts_notices') ?: array();
            $notices[] = array(
                'id' => $alert_id,
                'message' => $alert_data['message'],
                'priority' => $alert_data['priority'],
                'patient_id' => $alert_data['patient_id']
            );
            set_transient('hms_clinical_alerts_notices', $notices, 3600); // 1 hour
        }
    }

    /**
     * Get integrated clinical data for a patient
     *
     * @param int $patient_id Patient ID
     * @return array Integrated clinical data
     */
    public function get_integrated_clinical_data($patient_id) {
        if (!$patient_id) {
            return array();
        }
        
        $data = array();
        
        // Get patient summary
        $data['patient_summary'] = $this->get_patient_summary($patient_id);
        
        // Get clinical notes
        $data['clinical_notes'] = $this->get_patient_clinical_notes($patient_id, array('limit' => 10));
        
        // Get active alerts for this patient
        $data['active_alerts'] = $this->get_active_clinical_alerts(array('patient_id' => $patient_id));
        
        // Get patient timeline
        $data['timeline'] = $this->generate_patient_timeline($patient_id);
        
        return $data;
    }

    /**
     * AJAX handler for adding clinical note
     */
    public function ajax_add_clinical_note() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'note_type' => sanitize_text_field($_POST['note_type']),
            'note_title' => sanitize_text_field($_POST['note_title']),
            'note_content' => sanitize_textarea_field($_POST['note_content']),
            'priority' => sanitize_text_field($_POST['priority']),
            'is_private' => isset($_POST['is_private']) ? 1 : 0
        );
        
        $note_id = $this->add_clinical_note($data);
        
        if ($note_id) {
            wp_send_json_success(array(
                'message' => __('Clinical note added successfully.', 'hospital-management-system'),
                'note_id' => $note_id
            ));
        } else {
            wp_send_json_error(__('Failed to add clinical note.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for updating clinical note
     */
    public function ajax_update_clinical_note() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $note_id = intval($_POST['note_id']);
        $data = array(
            'note_type' => sanitize_text_field($_POST['note_type']),
            'note_title' => sanitize_text_field($_POST['note_title']),
            'note_content' => sanitize_textarea_field($_POST['note_content']),
            'priority' => sanitize_text_field($_POST['priority']),
            'is_private' => isset($_POST['is_private']) ? 1 : 0
        );
        
        $success = $this->update_clinical_note($note_id, $data);
        
        if ($success) {
            wp_send_json_success(array(
                'message' => __('Clinical note updated successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to update clinical note.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for getting clinical notes
     */
    public function ajax_get_clinical_notes() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $patient_id = intval($_POST['patient_id']);
        $filters = array();
        
        if (isset($_POST['note_type']) && !empty($_POST['note_type'])) {
            $filters['note_type'] = sanitize_text_field($_POST['note_type']);
        }
        
        if (isset($_POST['limit'])) {
            $filters['limit'] = intval($_POST['limit']);
        }
        
        $notes = $this->get_patient_clinical_notes($patient_id, $filters);
        
        wp_send_json_success(array(
            'notes' => $notes
        ));
    }

    /**
     * AJAX handler for creating clinical alert
     */
    public function ajax_create_clinical_alert() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $data = array(
            'patient_id' => isset($_POST['patient_id']) ? intval($_POST['patient_id']) : null,
            'alert_type' => sanitize_text_field($_POST['alert_type']),
            'priority' => sanitize_text_field($_POST['priority']),
            'message' => sanitize_textarea_field($_POST['message']),
            'details' => sanitize_textarea_field($_POST['details']),
            'expires_at' => isset($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : null
        );
        
        $alert_id = $this->create_clinical_alert($data);
        
        if ($alert_id) {
            wp_send_json_success(array(
                'message' => __('Clinical alert created successfully.', 'hospital-management-system'),
                'alert_id' => $alert_id
            ));
        } else {
            wp_send_json_error(__('Failed to create clinical alert.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for dismissing clinical alert
     */
    public function ajax_dismiss_clinical_alert() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $alert_id = intval($_POST['alert_id']);
        $success = $this->dismiss_clinical_alert($alert_id);
        
        if ($success) {
            wp_send_json_success(array(
                'message' => __('Clinical alert dismissed successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to dismiss clinical alert.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler for getting clinical alerts
     */
    public function ajax_get_clinical_alerts() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_clinical')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        
        $filters = array();
        
        if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
            $filters['patient_id'] = intval($_POST['patient_id']);
        }
        
        if (isset($_POST['alert_type']) && !empty($_POST['alert_type'])) {
            $filters['alert_type'] = sanitize_text_field($_POST['alert_type']);
        }
        
        if (isset($_POST['priority']) && !empty($_POST['priority'])) {
            $filters['priority'] = sanitize_text_field($_POST['priority']);
        }
        
        $alerts = $this->get_active_clinical_alerts($filters);
        
        wp_send_json_success(array(
            'alerts' => $alerts
        ));
    }

    /**
     * Display clinical alert notices in admin
     */
    public function display_clinical_alert_notices() {
        $notices = get_transient('hms_clinical_alerts_notices');
        
        if (!empty($notices)) {
            foreach ($notices as $notice) {
                $class = $notice['priority'] === 'critical' ? 'notice-error' : 'notice-warning';
                echo '<div class="notice ' . esc_attr($class) . ' is-dismissible">';
                echo '<p><strong>' . esc_html($notice['message']) . '</strong></p>';
                if ($notice['patient_id']) {
                    echo '<p><a href="' . admin_url('admin.php?page=hms_clinical&patient_id=' . $notice['patient_id']) . '">' . __('View Patient', 'hospital-management-system') . '</a></p>';
                }
                echo '</div>';
            }
            
            // Clear the notices after displaying
            delete_transient('hms_clinical_alerts_notices');
        }
    }
}
