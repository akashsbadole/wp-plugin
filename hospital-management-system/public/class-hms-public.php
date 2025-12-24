<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * Also registers shortcodes for the patient portal.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/public
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Public {

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
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register public-facing Custom Post Types
        add_action('init', array($this, 'register_public_cpts'));

        // Register shortcodes
        add_shortcode('hms_patient_login', array($this, 'render_patient_login_shortcode'));
        add_shortcode('hms_appointment_booking', array($this, 'render_appointment_booking_shortcode'));
        add_shortcode('hms_patient_dashboard', array($this, 'render_patient_dashboard_shortcode'));

        // Register AJAX actions for patient portal
        add_action('wp_ajax_hms_patient_get_appointments', array($this, 'ajax_patient_get_appointments'));
        add_action('wp_ajax_hms_patient_get_bills', array($this, 'ajax_patient_get_bills'));
        add_action('wp_ajax_hms_patient_get_prescriptions', array($this, 'ajax_patient_get_prescriptions'));
        add_action('wp_ajax_hms_patient_book_appointment', array($this, 'ajax_patient_book_appointment'));
        // Note: The login action is registered in the main loader file because it's public (no_priv)
        // add_action('wp_ajax_nopriv_hms_patient_login', array($this, 'ajax_patient_login'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/hms-patient-portal.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/hms-patient-portal.js', array('jquery'), $this->version, true);
        
        // Localize script for AJAX
        wp_localize_script($this->plugin_name, 'hms_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('hms_public_nonce'),
            'strings'  => array(
                'logging_in'        => esc_html__('Logging in...', 'hospital-management-system'),
                'unexpected_error'  => esc_html__('An unexpected error occurred. Please try again.', 'hospital-management-system'),
                'booking_placeholder' => esc_html__('Appointment booking functionality will be implemented next.', 'hospital-management-system'),
                'appointment_booked' => esc_html__('Appointment booked successfully.', 'hospital-management-system'),
            )
        ));
    }

    /**
     * Register public-facing Custom Post Types.
     * This is the "original code" that was reportedly deleted.
     *
     * @since    1.0.0
     */
    public function register_public_cpts() {
        // Example: Register a 'Testimonial' CPT
        $labels = array(
            'name'                  => _x('Testimonials', 'Post Type General Name', 'hospital-management-system'),
            'singular_name'         => _x('Testimonial', 'Post Type Singular Name', 'hospital-management-system'),
            'menu_name'             => esc_html__('Testimonials', 'hospital-management-system'),
            'name_admin_bar'        => esc_html__('Testimonial', 'hospital-management-system'),
            'archives'              => esc_html__('Testimonial Archives', 'hospital-management-system'),
            'attributes'            => esc_html__('Testimonial Attributes', 'hospital-management-system'),
            'parent_item_colon'     => esc_html__('Parent Testimonial:', 'hospital-management-system'),
            'all_items'             => esc_html__('All Testimonials', 'hospital-management-system'),
            'add_new_item'          => esc_html__('Add New Testimonial', 'hospital-management-system'),
            'add_new'               => esc_html__('Add New', 'hospital-management-system'),
            'new_item'              => esc_html__('New Testimonial', 'hospital-management-system'),
            'edit_item'             => esc_html__('Edit Testimonial', 'hospital-management-system'),
            'update_item'           => esc_html__('Update Testimonial', 'hospital-management-system'),
            'view_item'             => esc_html__('View Testimonial', 'hospital-management-system'),
            'view_items'            => esc_html__('View Testimonials', 'hospital-management-system'),
            'search_items'          => esc_html__('Search Testimonial', 'hospital-management-system'),
            'not_found'             => esc_html__('Not found', 'hospital-management-system'),
            'not_found_in_trash'    => esc_html__('Not found in Trash', 'hospital-management-system'),
            'featured_image'        => esc_html__('Featured Image', 'hospital-management-system'),
            'set_featured_image'    => esc_html__('Set featured image', 'hospital-management-system'),
            'remove_featured_image' => esc_html__('Remove featured image', 'hospital-management-system'),
            'use_featured_image'    => esc_html__('Use as featured image', 'hospital-management-system'),
            'insert_into_item'      => esc_html__('Insert into testimonial', 'hospital-management-system'),
            'uploaded_to_this_item' => esc_html__('Uploaded to this testimonial', 'hospital-management-system'),
            'items_list'            => esc_html__('Testimonials list', 'hospital-management-system'),
            'items_list_navigation' => esc_html__('Testimonials list navigation', 'hospital-management-system'),
            'filter_items_list'     => esc_html__('Filter testimonials list', 'hospital-management-system'),
        );
        $args = array(
            'label'                 => esc_html__('Testimonial', 'hospital-management-system'),
            'description'           => esc_html__('Holds patient testimonials', 'hospital-management-system'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-format-quote',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type('hms_testimonial', $args);
    }

    /**
     * Render the Patient Login form shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string             The HTML output for the login form.
     */
    public function render_patient_login_shortcode($atts) {
        // Check if user is already logged in
        if (is_user_logged_in() && current_user_can('hms_patient')) {
            $current_user = wp_get_current_user();
            $patient_dashboard_url = get_permalink(get_option('hms_patient_dashboard_page_id'));
            $output = '<div class="hms-alert hms-alert-info">';
            /* translators: 1: User display name, 2: Dashboard URL */
            $output .= sprintf(esc_html__('You are already logged in as %1$s. <a href="%2$s">Go to Dashboard</a>', 'hospital-management-system'), esc_html($current_user->display_name), esc_url($patient_dashboard_url));
            $output .= '</div>';
            return $output;
        }

        // Start output buffering
        ob_start();
        ?>
        <div class="hms-patient-portal-container">
            <div class="hms-card hms-login-card">
                <div class="hms-card-header">
                    <h4 class="hms-mb-0"><?php esc_html_e('Patient Login', 'hospital-management-system'); ?></h4>
                </div>
                <div class="hms-card-body">
                    <form id="hms-patient-login-form">
                        <div class="hms-form-group">
                            <label for="hms-patient-username"><?php esc_html_e('Patient ID or Email', 'hospital-management-system'); ?></label>
                            <input type="text" name="username" id="hms-patient-username" class="hms-form-control" required>
                        </div>
                        <div class="hms-form-group">
                            <label for="hms-patient-password"><?php esc_html_e('Password', 'hospital-management-system'); ?></label>
                            <input type="password" name="password" id="hms-patient-password" class="hms-form-control" required>
                        </div>
                        <div class="hms-form-group">
                            <input type="checkbox" name="remember" id="hms-patient-remember">
                            <label for="hms-patient-remember"><?php esc_html_e('Remember Me', 'hospital-management-system'); ?></label>
                        </div>
                        <div id="hms-login-feedback" class="hms-feedback" style="display: none;"></div>
                        <button type="submit" class="hms-btn hms-btn-primary hms-btn-block"><?php esc_html_e('Login', 'hospital-management-system'); ?></button>
                    </form>
                    <div class="hms-mt-3 hms-text-center">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Forgot Password?', 'hospital-management-system'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the Appointment Booking form shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string             The HTML output for the booking form.
     */
    public function render_appointment_booking_shortcode($atts) {
        // Check if user is logged in and has patient capability
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            $login_url = get_permalink(get_option('hms_patient_login_page_id'));
            /* translators: %s: Login URL */
            return '<div class="hms-alert hms-alert-warning">' . sprintf(esc_html__('You must be <a href="%s">logged in as a patient</a> to book an appointment.', 'hospital-management-system'), esc_url($login_url)) . '</div>';
        }
        
        // Start output buffering
        ob_start();
        // Include the template file
        include_once HMS_PLUGIN_DIR . 'public/templates/appointment-booking.php';
        return ob_get_clean();
    }

    /**
     * Render the Patient Dashboard shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string             The HTML output for the dashboard.
     */
    public function render_patient_dashboard_shortcode($atts) {
        // Check if user is logged in and has patient capability
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            $login_url = get_permalink(get_option('hms_patient_login_page_id'));
            /* translators: %s: Login URL */
            return '<div class="hms-alert hms-alert-warning">' . sprintf(esc_html__('You must be <a href="%s">logged in as a patient</a> to view the dashboard.', 'hospital-management-system'), esc_url($login_url)) . '</div>';
        }

        // Get patient data associated with the logged-in user
        $current_user = wp_get_current_user();
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient_by_user_id($current_user->ID);

        if (!$patient) {
            return '<div class="hms-alert hms-alert-danger">' . esc_htmlesc_html__('Patient profile not found. Please contact the administrator.', 'hospital-management-system') . '</div>';
        }
        
        // Start output buffering
        ob_start();
        // Include the template file
        include_once HMS_PLUGIN_DIR . 'public/templates/patient-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX login for patients.
     * Note: This action should be registered as 'wp_ajax_nopriv_hms_patient_login' in the main loader.
     */
    public function ajax_patient_login() {
        check_ajax_referer('hms_public_nonce', 'nonce');

        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        // Attempt to log the user in
        $user = wp_signon(array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ));

        if (is_wp_error($user)) {
            wp_send_json_error($user->get_error_message());
        }

        // Check if the logged-in user has the 'hms_patient' role
        if (!in_array('hms_patient', (array) $user->roles)) {
            wp_logout(); // Log out the user if they don't have the correct role
            wp_send_json_error(esc_html__('You do not have sufficient privileges to access the patient portal.', 'hospital-management-system'));
        }

        wp_send_json_success(array(
            'message' => esc_html__('Login successful. Redirecting...', 'hospital-management-system'),
            'redirect_url' => get_permalink(get_option('hms_patient_dashboard_page_id'))
        ));
    }

    // --- Patient Dashboard AJAX Handlers ---

    /**
     * Handle AJAX request to get patient's appointments.
     */
    public function ajax_patient_get_appointments() {
        check_ajax_referer('hms_public_nonce', 'nonce');
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            wp_send_json_error(esc_html__('Unauthorized request.', 'hospital-management-system'), 403);
        }

        $current_user = wp_get_current_user();
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient_by_user_id($current_user->ID);

        if (!$patient) {
            wp_send_json_error(esc_html__('Patient profile not found.', 'hospital-management-system'));
        }

        $appointment_obj = new HMS_Appointment(); // Assuming HMS_Appointment class exists
        $appointments = $appointment_obj->get_appointments_by_patient($patient->id);

        wp_send_json_success($appointments);
    }

    /**
     * Handle AJAX request to get patient's bills.
     */
    public function ajax_patient_get_bills() {
        check_ajax_referer('hms_public_nonce', 'nonce');
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            wp_send_json_error(esc_html__('Unauthorized request.', 'hospital-management-system'), 403);
        }

        $current_user = wp_get_current_user();
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient_by_user_id($current_user->ID);

        if (!$patient) {
            wp_send_json_error(esc_html__('Patient profile not found.', 'hospital-management-system'));
        }
        
        $billing_obj = new HMS_Billing(); // Assuming HMS_Billing class exists
        $bills = $billing_obj->get_bills_by_patient($patient->id);

        wp_send_json_success($bills);
    }

    /**
     * Handle AJAX request to get patient's prescriptions.
     */
    public function ajax_patient_get_prescriptions() {
        check_ajax_referer('hms_public_nonce', 'nonce');
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            wp_send_json_error(esc_html__('Unauthorized request.', 'hospital-management-system'), 403);
        }

        $current_user = wp_get_current_user();
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient_by_user_id($current_user->ID);

        if (!$patient) {
            wp_send_json_error(esc_html__('Patient profile not found.', 'hospital-management-system'));
        }

        $prescription_obj = new HMS_Prescription(); // Assuming HMS_Prescription class exists
        $prescriptions = $prescription_obj->get_prescriptions_by_patient($patient->id);

        wp_send_json_success($prescriptions);
    }

    /**
     * Handle AJAX request to book a new appointment.
     */
    public function ajax_patient_book_appointment() {
        check_ajax_referer('hms_public_nonce', 'nonce');
        if (!is_user_logged_in() || !current_user_can('hms_patient')) {
            wp_send_json_error(esc_html__('Unauthorized request.', 'hospital-management-system'), 403);
        }

        $current_user = wp_get_current_user();
        $patient_obj = new HMS_Patient();
        $patient = $patient_obj->get_patient_by_user_id($current_user->ID);

        if (!$patient) {
            wp_send_json_error(esc_html__('Patient profile not found.', 'hospital-management-system'));
        }

        $appointment_data = array(
            'patient_id'   => $patient->id,
            'doctor_id'    => intval($_POST['doctor_id']),
            'department_id'=> intval($_POST['department_id']),
            'date_time'    => sanitize_text_field($_POST['date_time']),
            'reason'       => sanitize_textarea_field($_POST['reason']),
            'status'       => 'pending', // Default status for new appointments
        );

        $appointment_obj = new HMS_Appointment(); // Assuming HMS_Appointment class exists
        $new_appointment_id = $appointment_obj->create_appointment($appointment_data);

        if (is_wp_error($new_appointment_id)) {
            wp_send_json_error($new_appointment_id->get_error_message());
        } elseif ($new_appointment_id) {
            wp_send_json_success(array(
                'message' => hms_ajax_obj->strings['appointment_booked'],
                'appointment_id' => $new_appointment_id
            ));
        } else {
            wp_send_json_error(esc_html__('Failed to book appointment.', 'hospital-management-system'));
        }
    }
}
