<?php

/**
 * HMS Basic Telemedicine
 *
 * This class handles the basic telemedicine features, including
 * session management and placeholder for video integration.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Telemedicine {

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
     * Register the hooks for the telemedicine features.
     */
    private function hooks() {
        // Register a custom post type for telemedicine sessions
        add_action( 'init', array( $this, 'register_telemedicine_session_cpt' ) );

        // Add meta boxes for session details
        add_action( 'add_meta_boxes', array( $this, 'add_session_meta_boxes' ) );
        add_action( 'save_post_hms_telemed_session', array( $this, 'save_session_meta_data' ) );

        // Handle AJAX for creating/joining sessions
        add_action( 'wp_ajax_hms_create_telemedicine_session', array( $this, 'ajax_create_session' ) );
        add_action( 'wp_ajax_hms_join_telemedicine_session', array( $this, 'ajax_join_session' ) );
        add_action( 'wp_ajax_hms_end_telemedicine_session', array( $this, 'ajax_end_session' ) );

        // Enqueue scripts for telemedicine pages
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_telemedicine_assets' ) );
    }

    /**
     * Register the 'hms_telemed_session' custom post type.
     */
    public function register_telemedicine_session_cpt() {
        $labels = array(
            'name'                  => _x( 'Telemedicine Sessions', 'Post Type General Name', 'hospital-management-system' ),
            'singular_name'         => _x( 'Telemedicine Session', 'Post Type Singular Name', 'hospital-management-system' ),
            'menu_name'             => esc_html__( 'Telemedicine', 'hospital-management-system' ),
            'name_admin_bar'        => esc_html__( 'Telemedicine Session', 'hospital-management-system' ),
            'archives'              => esc_html__( 'Session Archives', 'hospital-management-system' ),
            'attributes'            => esc_html__( 'Session Attributes', 'hospital-management-system' ),
            'parent_item_colon'     => esc_html__( 'Parent Session:', 'hospital-management-system' ),
            'all_items'             => esc_html__( 'All Sessions', 'hospital-management-system' ),
            'add_new_item'          => esc_html__( 'Add New Session', 'hospital-management-system' ),
            'add_new'               => esc_html__( 'Add New', 'hospital-management-system' ),
            'new_item'              => esc_html__( 'New Session', 'hospital-management-system' ),
            'edit_item'             => esc_html__( 'Edit Session', 'hospital-management-system' ),
            'update_item'           => esc_html__( 'Update Session', 'hospital-management-system' ),
            'view_item'             => esc_html__( 'View Session', 'hospital-management-system' ),
            'view_items'            => esc_html__( 'View Sessions', 'hospital-management-system' ),
            'search_items'          => esc_html__( 'Search Session', 'hospital-management-system' ),
            'not_found'             => esc_html__( 'Not found', 'hospital-management-system' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'hospital-management-system' ),
            'featured_image'        => esc_html__( 'Featured Image', 'hospital-management-system' ),
            'set_featured_image'    => esc_html__( 'Set featured image', 'hospital-management-system' ),
            'remove_featured_image' => esc_html__( 'Remove featured image', 'hospital-management-system' ),
            'use_featured_image'    => esc_html__( 'Use as featured image', 'hospital-management-system' ),
            'insert_into_item'      => esc_html__( 'Insert into session', 'hospital-management-system' ),
            'uploaded_to_this_item' => esc_html__( 'Uploaded to this session', 'hospital-management-system' ),
            'items_list'            => esc_html__( 'Sessions list', 'hospital-management-system' ),
            'items_list_navigation' => esc_html__( 'Sessions list navigation', 'hospital-management-system' ),
            'filter_items_list'     => esc_html__( 'Filter sessions list', 'hospital-management-system' ),
        );
        $args = array(
            'label'                 => esc_html__( 'Telemedicine Session', 'hospital-management-system' ),
            'description'           => esc_html__( 'Holds telemedicine sessions', 'hospital-management-system' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author' ),
            'hierarchical'          => false,
            'public'                => false, // Not publicly queryable on the front end
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25, // After Patients, Appointments, etc.
            'menu_icon'             => 'dashicons-video-alt2',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => false, // Set to true if needed for block editor
        );
        register_post_type( 'hms_telemed_session', $args );
    }

    /**
     * Add meta boxes for the telemedicine session CPT.
     *
     * @param string $post_type The current post type.
     */
    public function add_session_meta_boxes( $post_type ) {
        if ( 'hms_telemed_session' === $post_type ) {
            add_meta_box(
                'hms_session_details',
                esc_html__( 'Session Details', 'hospital-management-system' ),
                array( $this, 'render_session_details_meta_box' ),
                'hms_telemed_session',
                'normal',
                'default'
            );
            add_meta_box(
                'hms_session_participants',
                esc_html__( 'Participants', 'hospital-management-system' ),
                array( $this, 'render_session_participants_meta_box' ),
                'hms_telemed_session',
                'side',
                'default'
            );
        }
    }

    /**
     * Render the session details meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_session_details_meta_box( $post ) {
        wp_nonce_field( 'hms_save_session_details_meta_box', 'hms_session_details_nonce' );

        $scheduled_time = get_post_meta( $post->ID, '_hms_session_scheduled_time', true );
        $duration      = get_post_meta( $post->ID, '_hms_session_duration', true );
        $status        = get_post_meta( $post->ID, '_hms_session_status', true );
        $video_link    = get_post_meta( $post->ID, '_hms_session_video_link', true );
        $notes         = get_post_meta( $post->ID, '_hms_session_notes', true );

        $statuses = array( 'scheduled', 'in-progress', 'completed', 'cancelled' );
        ?>
        <p>
            <label for="hms_session_scheduled_time"><?php esc_html_e( 'Scheduled Time:', 'hospital-management-system' ); ?></label><br>
            <input type="datetime-local" id="hms_session_scheduled_time" name="hms_session_scheduled_time" value="<?php echo esc_attr( $scheduled_time ); ?>" class="regular-text">
        </p>
        <p>
            <label for="hms_session_duration"><?php esc_html_e( 'Duration (minutes):', 'hospital-management-system' ); ?></label><br>
            <input type="number" id="hms_session_duration" name="hms_session_duration" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text">
        </p>
        <p>
            <label for="hms_session_status"><?php esc_html_e( 'Status:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_session_status" name="hms_session_status">
                <?php foreach ( $statuses as $stat ) : ?>
                    <option value="<?php echo esc_attr( $stat ); ?>" <?php selected( $status, $stat ); ?>><?php echo esc_html( ucfirst( $stat ) ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_session_video_link"><?php esc_html_e( 'Video Conference Link (e.g., Zoom, Jitsi):', 'hospital-management-system' ); ?></label><br>
            <input type="url" id="hms_session_video_link" name="hms_session_video_link" value="<?php echo esc_url( $video_link ); ?>" class="regular-text">
            <small><?php esc_html_e( 'This will be generated or entered by the provider.', 'hospital-management-system' ); ?></small>
        </p>
        <p>
            <label for="hms_session_notes"><?php esc_html_e( 'Internal Notes:', 'hospital-management-system' ); ?></label><br>
            <textarea id="hms_session_notes" name="hms_session_notes" rows="4" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea>
        </p>
        <?php
    }

    /**
     * Render the session participants meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_session_participants_meta_box( $post ) {
        $patient_id = get_post_meta( $post->ID, '_hms_session_patient_id', true );
        $doctor_id  = get_post_meta( $post->ID, '_hms_session_doctor_id', true );

        // Fetch patients and doctors for dropdowns
        $patients = get_posts( array( 'post_type' => 'hms_patient', 'numberposts' => -1, 'post_status' => 'publish' ) );
        $doctors  = get_posts( array( 'post_type' => 'hms_staff', 'numberposts' => -1, 'post_status' => 'publish', 'meta_query' => array( array( 'key' => '_hms_staff_role', 'value' => 'Doctor' ) ) ) );
        ?>
        <p>
            <label for="hms_session_patient_id"><?php esc_html_e( 'Patient:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_session_patient_id" name="hms_session_patient_id" class="regular-text">
                <option value=""><?php esc_html_e( 'Select Patient', 'hospital-management-system' ); ?></option>
                <?php foreach ( $patients as $patient ) : ?>
                    <option value="<?php echo esc_attr( $patient->ID ); ?>" <?php selected( $patient_id, $patient->ID ); ?>>
                        <?php echo esc_html( $patient->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="hms_session_doctor_id"><?php esc_html_e( 'Doctor:', 'hospital-management-system' ); ?></label><br>
            <select id="hms_session_doctor_id" name="hms_session_doctor_id" class="regular-text">
                <option value=""><?php esc_html_e( 'Select Doctor', 'hospital-management-system' ); ?></option>
                <?php foreach ( $doctors as $doctor ) : ?>
                    <option value="<?php echo esc_attr( $doctor->ID ); ?>" <?php selected( $doctor_id, $doctor->ID ); ?>>
                        <?php echo esc_html( $doctor->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save the meta data for the telemedicine session.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_session_meta_data( $post_id ) {
        if ( ! isset( $_POST['hms_session_details_nonce'] ) || ! wp_verify_nonce( $_POST['hms_session_details_nonce'], 'hms_save_session_details_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $meta_keys = array(
            'hms_session_scheduled_time' => 'datetime',
            'hms_session_duration'      => 'int',
            'hms_session_status'        => 'text',
            'hms_session_video_link'    => 'url',
            'hms_session_notes'         => 'textarea',
            'hms_session_patient_id'    => 'int',
            'hms_session_doctor_id'     => 'int',
        );

        foreach ( $meta_keys as $key => $type ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = $_POST[ $key ];
                switch ( $type ) {
                    case 'int':
                        $value = absint( $value );
                        break;
                    case 'url':
                        $value = esc_url_raw( $value );
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field( $value );
                        break;
                    case 'datetime':
                        // datetime-local input format is 'Y-m-dTH:i'
                        // Convert to MySQL datetime format 'Y-m-d H:i:s'
                        $date_obj = DateTime::createFromFormat( 'Y-m-d\TH:i', $value );
                        $value = $date_obj ? $date_obj->format( 'Y-m-d H:i:s' ) : '';
                        break;
                    default: // 'text'
                        $value = sanitize_text_field( $value );
                        break;
                }
                update_post_meta( $post_id, '_' . $key, $value );
            }
        }
    }

    // --- AJAX Handlers ---

    /**
     * AJAX handler to create a new telemedicine session.
     */
    public function ajax_create_session() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' ); // Using a common nonce for now

        if ( ! current_user_can( 'edit_hms_telemed_sessions' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $patient_id = absint( $_POST['patient_id'] );
        $doctor_id  = absint( $_POST['doctor_id'] );
        $scheduled_time = sanitize_text_field( $_POST['scheduled_time'] );
        $duration   = absint( $_POST['duration'] );

        if ( ! $patient_id || ! $doctor_id || ! $scheduled_time || ! $duration ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Missing required fields.', 'hospital-management-system' ) ) );
        }

        $session_id = wp_insert_post( array(
            /* translators: 1: Patient name, 2: Doctor name */
            'post_title'  => sprintf( esc_html__( 'Session: %1$s - %2$s', 'hospital-management-system' ), get_the_title( $patient_id ), get_the_title( $doctor_id ) ),
            'post_type'   => 'hms_telemed_session',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'meta_input'  => array(
                '_hms_session_patient_id'    => $patient_id,
                '_hms_session_doctor_id'     => $doctor_id,
                '_hms_session_scheduled_time' => $scheduled_time,
                '_hms_session_duration'      => $duration,
                '_hms_session_status'        => 'scheduled',
            ),
        ) );

        if ( is_wp_error( $session_id ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Could not create session.', 'hospital-management-system' ) ) );
        }

        // Placeholder: Generate video link using a third-party API
        // $video_link = $this->generate_video_link($session_id);
        // update_post_meta($session_id, '_hms_session_video_link', $video_link);

        wp_send_json_success( array(
            'message'     => esc_html__( 'Session created successfully.', 'hospital-management-system' ),
            'session_id' => $session_id,
        ) );
    }

    /**
     * AJAX handler to join a telemedicine session.
     */
    public function ajax_join_session() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        $session_id = absint( $_POST['session_id'] );
        $session    = get_post( $session_id );

        if ( ! $session || 'hms_telemed_session' !== $session->post_type ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid session.', 'hospital-management-system' ) ) );
        }

        $user_id      = get_current_user_id();
        $patient_id   = get_post_meta( $session_id, '_hms_session_patient_id', true );
        $doctor_id    = get_post_meta( $session_id, '_hms_session_doctor_id', true );
        $video_link   = get_post_meta( $session_id, '_hms_session_video_link', true );
        $status       = get_post_meta( $session_id, '_hms_session_status', true );

        // Check if user is patient or doctor for this session
        $is_participant = false;
        if ( $this->get_patient_post_by_user_id( $user_id ) == $patient_id ) {
            $is_participant = true;
        } elseif ( $this->get_staff_post_by_user_id( $user_id ) == $doctor_id ) {
            $is_participant = true;
        }

        if ( ! $is_participant && ! current_user_can( 'edit_hms_telemed_sessions' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You are not a participant in this session.', 'hospital-management-system' ) ) );
        }

        if ( 'scheduled' !== $status && 'in-progress' !== $status ) {
            wp_send_json_error( array( 'message' => esc_html__( 'This session is not active.', 'hospital-management-system' ) ) );
        }

        if ( empty( $video_link ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Video link is not available yet.', 'hospital-management-system' ) ) );
        }

        // If status is 'scheduled', and a doctor is joining, change to 'in-progress'
        if ( 'scheduled' === $status && $this->get_staff_post_by_user_id( $user_id ) == $doctor_id ) {
            update_post_meta( $session_id, '_hms_session_status', 'in-progress' );
        }

        wp_send_json_success( array(
            'message'    => esc_html__( 'Joining session...', 'hospital-management-system' ),
            'video_link' => $video_link,
        ) );
    }

    /**
     * AJAX handler to end a telemedicine session.
     */
    public function ajax_end_session() {
        check_ajax_referer( 'hms_patient_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_hms_telemed_sessions' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'hospital-management-system' ) ) );
        }

        $session_id = absint( $_POST['session_id'] );
        $session    = get_post( $session_id );

        if ( ! $session || 'hms_telemed_session' !== $session->post_type ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Invalid session.', 'hospital-management-system' ) ) );
        }

        update_post_meta( $session_id, '_hms_session_status', 'completed' );

        // Placeholder: Notify participants that session has ended.

        wp_send_json_success( array( 'message' => esc_html__( 'Session ended successfully.', 'hospital-management-system' ) ) );
    }

    /**
     * Enqueue scripts and styles for telemedicine pages.
     */
    public function enqueue_telemedicine_assets() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'hms_telemedicine' ) ) {
            // Enqueue CSS
            wp_enqueue_style(
                'hms-telemedicine',
                HMS_PLUGIN_URL . 'public/css/hms-telemedicine.css',
                array(),
                $this->version
            );

            // Enqueue JS
            wp_enqueue_script(
                'hms-telemedicine-js',
                HMS_PLUGIN_URL . 'public/js/hms-telemedicine.js',
                array( 'jquery' ),
                $this->version,
                true
            );

            wp_localize_script( 'hms-telemedicine-js', 'hms_telemedicine_vars', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'hms_patient_nonce' ),
            ) );
        }
    }

    // --- Helper Methods ---

    /**
     * Get the patient post object associated with a user ID.
     * (Duplicate from HMS_Patient_Portal_Mobile, consider moving to a helper class)
     *
     * @param int $user_id The WordPress user ID.
     * @return WP_Post|false The patient post object or false if not found.
     */
    private function get_patient_post_by_user_id( $user_id ) {
        $patients = get_posts( array(
            'post_type'      => 'hms_patient',
            'posts_per_page' => 1,
            'meta_key'       => '_hms_patient_user_id',
            'meta_value'     => $user_id,
            'post_status'    => 'publish',
        ) );
        return ! empty( $patients ) ? $patients[0] : false;
    }

    /**
     * Get the staff post object associated with a user ID.
     *
     * @param int $user_id The WordPress user ID.
     * @return WP_Post|false The staff post object or false if not found.
     */
    private function get_staff_post_by_user_id( $user_id ) {
        $staff = get_posts( array(
            'post_type'      => 'hms_staff',
            'posts_per_page' => 1,
            'meta_key'       => '_hms_staff_user_id',
            'meta_value'     => $user_id,
            'post_status'    => 'publish',
        ) );
        return ! empty( $staff ) ? $staff[0] : false;
    }

    // --- Placeholder for Video Integration ---
    private function generate_video_link( $session_id ) {
        // This is where you would integrate with a video service API (Zoom, Jitsi, etc.)
        // For now, return a dummy link.
        return 'https://meet.jit.si/HMSSession' . $session_id;
    }
}
