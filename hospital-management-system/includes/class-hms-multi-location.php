<?php

/**
 * HMS Multi-Location Management
 *
 * This class handles the management of multiple hospital branches or locations,
 * potentially using WordPress Multisite or custom taxonomies.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Multi_Location {

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
     * Register the hooks for the multi-location features.
     */
    private function hooks() {
        // If using Multisite, add network admin menu items
        if ( is_multisite() && is_main_site() ) {
            add_action( 'network_admin_menu', array( $this, 'add_network_admin_menu' ) );
            add_action( 'network_admin_edit_hms_multi_location_settings', array( $this, 'save_network_settings' ) );
        }

        // If using taxonomies, register them
        add_action( 'init', array( $this, 'register_location_taxonomy' ) );

        // Add location selector to user profiles
        add_action( 'show_user_profile', array( $this, 'add_user_location_field' ) );
        add_action( 'edit_user_profile', array( $this, 'add_user_location_field' ) );
        add_action( 'personal_options_update', array( $this, 'save_user_location_field' ) );
        add_action( 'edit_user_profile_update', array( $this, 'save_user_location_field' ) );

        // Add location selector to post types (Patients, Appointments, etc.)
        add_action( 'add_meta_boxes', array( $this, 'add_location_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_post_location_meta' ) );

        // Filter content based on location
        add_action( 'pre_get_posts', array( $this, 'filter_content_by_location' ) );
        // Add similar filters for other queries as needed (e.g., users, reports)
    }

    /**
     * Add menu item for network admin if Multisite is enabled.
     */
    public function add_network_admin_menu() {
        add_submenu_page(
            'settings.php', // Parent slug
            esc_html__( 'HMS Locations', 'hospital-management-system' ), // Page title
            esc_html__( 'Locations', 'hospital-management-system' ), // Menu title
            'manage_network_options', // Capability
            'hms-multi-location-settings', // Menu slug
            array( $this, 'render_network_settings_page' ) // Callback function
        );
    }

    /**
     * Render the network settings page for managing locations.
     */
    public function render_network_settings_page() {
        if ( ! current_user_can( 'manage_network_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="edit.php?action=hms_multi_location_settings" method="post">
                <?php
                wp_nonce_field( 'hms_multi_location_settings_nonce', 'hms_multi_location_settings_nonce' );
                // Example: Get list of sites in the network
                $sites = get_sites();
                if ( ! empty( $sites ) ) {
                    echo '<h2>' . esc_html__( 'Network Sites (Locations)', 'hospital-management-system' ) . '</h2>';
                    echo '<table class="form-table">';
                    foreach ( $sites as $site ) {
                        $site_id = $site->blog_id;
                        $site_details = get_blog_details( $site_id );
                        $site_name = $site_details->blogname;
                        $is_active_location = get_site_option( 'hms_active_location_' . $site_id, 0 );
                        echo '<tr>';
                        echo '<th scope="row">' . esc_html( $site_name ) . ' (ID: ' . esc_html( $site_id ) . ')</th>';
                        echo '<td><label><input type="checkbox" name="hms_active_locations[]" value="' . esc_attr( $site_id ) . '" ' . checked( $is_active_location, 1, false ) . '> ' . esc_html__( 'Enable as HMS Location', 'hospital-management-system' ) . '</label></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save network settings for active locations.
     */
    public function save_network_settings() {
        if ( ! isset( $_POST['hms_multi_location_settings_nonce'] ) || ! wp_verify_nonce( $_POST['hms_multi_location_settings_nonce'], 'hms_multi_location_settings_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'hospital-management-system' ) );
        }

        if ( ! current_user_can( 'manage_network_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to save these settings.', 'hospital-management-system' ) );
        }

        // First, reset all locations to inactive
        $sites = get_sites();
        foreach ( $sites as $site ) {
            update_site_option( 'hms_active_location_' . $site->blog_id, 0 );
        }

        // Then, activate selected locations
        if ( isset( $_POST['hms_active_locations'] ) && is_array( $_POST['hms_active_locations'] ) ) {
            foreach ( $_POST['hms_active_locations'] as $site_id ) {
                update_site_option( 'hms_active_location_' . absint( $site_id ), 1 );
            }
        }

        wp_redirect( add_query_arg( array( 'page' => 'hms-multi-location-settings', 'updated' => 'true' ), network_admin_url( 'settings.php' ) ) );
        exit;
    }

    /**
     * Register a custom taxonomy for 'Location' if not using Multisite.
     */
    public function register_location_taxonomy() {
        // Only register if not using Multisite for locations
        if ( is_multisite() && get_site_option( 'hms_use_multisite_for_locations', false ) ) {
            return;
        }

        $labels = array(
            'name'              => _x( 'Locations', 'taxonomy general name', 'hospital-management-system' ),
            'singular_name'     => _x( 'Location', 'taxonomy singular name', 'hospital-management-system' ),
            'search_items'      => esc_html__( 'Search Locations', 'hospital-management-system' ),
            'all_items'         => esc_html__( 'All Locations', 'hospital-management-system' ),
            'parent_item'       => esc_html__( 'Parent Location', 'hospital-management-system' ),
            'parent_item_colon' => esc_html__( 'Parent Location:', 'hospital-management-system' ),
            'edit_item'         => esc_html__( 'Edit Location', 'hospital-management-system' ),
            'update_item'       => esc_html__( 'Update Location', 'hospital-management-system' ),
            'add_new_item'      => esc_html__( 'Add New Location', 'hospital-management-system' ),
            'new_item_name'     => esc_html__( 'New Location Name', 'hospital-management-system' ),
            'menu_name'         => esc_html__( 'Locations', 'hospital-management-system' ),
        );

        $args = array(
            'hierarchical'      => true, // Allows for parent/child locations (e.g., City > Hospital Branch)
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'hms-location' ),
            'show_in_rest'      => false, // Set to true if needed for block editor/Gutenberg
            'capabilities'      => array(
                'manage_terms' => 'manage_hms',
                'edit_terms'   => 'manage_hms',
                'delete_terms' => 'manage_hms',
                'assign_terms' => 'manage_hms', // Or a more specific capability
            ),
        );

        register_taxonomy( 'hms_location', array( 'hms_patient', 'hms_appointment', 'hms_staff' ), $args );
    }

    /**
     * Add a location selector field to user profiles.
     *
     * @param WP_User $user The user object.
     */
    public function add_user_location_field( $user ) {
        if ( ! current_user_can( 'manage_hms' ) && ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }

        $user_location_id = $this->get_user_location_id( $user->ID );
        $locations       = $this->get_all_locations();

        if ( empty( $locations ) ) {
            return;
        }
        ?>
        <h3><?php esc_html_e( 'Hospital Management System', 'hospital-management-system' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="hms_user_location"><?php esc_html_e( 'Assigned Location', 'hospital-management-system' ); ?></label></th>
                <td>
                    <select name="hms_user_location" id="hms_user_location" class="regular-text">
                        <option value=""><?php esc_html_e( 'Select a location...', 'hospital-management-system' ); ?></option>
                        <?php foreach ( $locations as $location ) : ?>
                            <option value="<?php echo esc_attr( $location->term_id ); ?>" <?php selected( $user_location_id, $location->term_id ); ?>>
                                <?php echo esc_html( $location->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Assign the user to a specific hospital location.', 'hospital-management-system' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save the user location field.
     *
     * @param int $user_id The ID of the user being saved.
     */
    public function save_user_location_field( $user_id ) {
        if ( ! current_user_can( 'manage_hms' ) && ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        if ( isset( $_POST['hms_user_location'] ) ) {
            $location_id = absint( $_POST['hms_user_location'] );
            update_user_meta( $user_id, '_hms_user_location_id', $location_id );
        }
    }

    /**
     * Add a location meta box to relevant post types.
     *
     * @param string $post_type The current post type.
     */
    public function add_location_meta_box( $post_type ) {
        $post_types_to_add = array( 'hms_patient', 'hms_appointment', 'hms_staff', 'hms_bill' ); // Add more as needed

        if ( in_array( $post_type, $post_types_to_add ) ) {
            add_meta_box(
                'hms_location_details',
                esc_html__( 'Location Details', 'hospital-management-system' ),
                array( $this, 'render_location_meta_box' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the location meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_location_meta_box( $post ) {
        wp_nonce_field( 'hms_save_location_meta_box_data', 'hms_location_meta_box_nonce' );

        $post_location_id = get_post_meta( $post->ID, '_hms_post_location_id', true );
        $locations       = $this->get_all_locations();

        if ( empty( $locations ) ) {
            esc_html_e( 'No locations available.', 'hospital-management-system' );
            return;
        }
        ?>
        <label for="hms_post_location"><?php esc_html_e( 'Assign to Location:', 'hospital-management-system' ); ?></label><br>
        <select name="hms_post_location" id="hms_post_location" style="width: 100%;">
            <option value=""><?php esc_html_e( 'Select a location...', 'hospital-management-system' ); ?></option>
            <?php foreach ( $locations as $location ) : ?>
                <option value="<?php echo esc_attr( $location->term_id ); ?>" <?php selected( $post_location_id, $location->term_id ); ?>>
                    <?php echo esc_html( $location->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Save the location meta box data.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_post_location_meta( $post_id ) {
        if ( ! isset( $_POST['hms_location_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['hms_location_meta_box_nonce'], 'hms_save_location_meta_box_data' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        $post_type = get_post_type( $post_id );
        if ( 'hms_patient' === $post_type && ! current_user_can( 'edit_hms_patient', $post_id ) ) {
            return;
        }
        // Add checks for other post types as well

        if ( isset( $_POST['hms_post_location'] ) ) {
            $location_id = absint( $_POST['hms_post_location'] );
            update_post_meta( $post_id, '_hms_post_location_id', $location_id );
        }
    }

    /**
     * Filter post queries to show content based on the user's assigned location.
     *
     * @param WP_Query $query The current WP_Query object.
     */
    public function filter_content_by_location( $query ) {
        // Only apply to main query and in the admin area for specific post types
        if ( ! $query->is_main_query() || ! is_admin() ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        $post_types_to_filter = array( 'hms_patient', 'hms_appointment', 'hms_staff' );
        if ( ! in_array( $screen->post_type, $post_types_to_filter ) ) {
            return;
        }

        // Super admins can see all locations
        if ( current_user_can( 'manage_network' ) ) {
            return;
        }

        $user_id = get_current_user_id();
        $user_location_id = $this->get_user_location_id( $user_id );

        if ( $user_location_id ) {
            $tax_query = array(
                array(
                    'taxonomy' => 'hms_location',
                    'field'    => 'term_id',
                    'terms'    => $user_location_id,
                ),
            );
            $query->set( 'tax_query', $tax_query );
        } else {
            // If user has no assigned location, show no posts for these types
            // Or, alternatively, you could choose to show all posts if that's the desired behavior
            $query->set( 'post__in', array(0) ); // This will result in no posts being shown
        }
    }

    // --- Helper Methods ---

    /**
     * Get the assigned location ID for a user.
     *
     * @param int $user_id The user ID.
     * @return int|false The location term ID or false if not found.
     */
    private function get_user_location_id( $user_id ) {
        return get_user_meta( $user_id, '_hms_user_location_id', true );
    }

    /**
     * Get all available locations.
     * This method abstracts whether locations are stored as a taxonomy or as network sites.
     *
     * @return array An array of location objects (WP_Term or site objects).
     */
    private function get_all_locations() {
        $locations = array();

        if ( is_multisite() && get_site_option( 'hms_use_multisite_for_locations', false ) ) {
            // Get active sites from network options
            $sites = get_sites();
            foreach ( $sites as $site ) {
                if ( get_site_option( 'hms_active_location_' . $site->blog_id, 0 ) ) {
                    $site_details = get_blog_details( $site );
                    // Create a simple object to mimic WP_Term for consistency in select dropdowns
                    $location_obj         = new stdClass();
                    $location_obj->term_id = $site->blog_id;
                    $location_obj->name    = $site_details->blogname;
                    $locations[]         = $location_obj;
                }
            }
        } else {
            // Get terms from the hms_location taxonomy
            $terms = get_terms( array(
                'taxonomy'   => 'hms_location',
                'hide_empty' => false,
            ) );
            if ( ! is_wp_error( $terms ) ) {
                $locations = $terms;
            }
        }

        return $locations;
    }
}
