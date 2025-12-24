<?php

/**
 * Handles the admin interface for role management.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin interface for role management.
 *
 * This class handles the admin interface for role management functionality
 * including displaying role settings and managing capabilities.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Roles_Admin {

	/**
	 * The role manager instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Roles    $roles    The role manager instance.
	 */
	private $roles;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Initialize role manager
		$this->roles = new Seo_Simple_Roles();
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_reset_roles', array( $this, 'ajax_reset_roles' ) );
		add_action( 'wp_ajax_seo_simple_add_custom_role', array( $this, 'ajax_add_custom_role' ) );
		add_action( 'wp_ajax_seo_simple_remove_custom_role', array( $this, 'ajax_remove_custom_role' ) );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'seo-simple',
			__( 'Role Manager', 'seo-simple' ),
			__( 'Role Manager', 'seo-simple' ),
			'manage_options',
			'seo-simple-roles',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		// Handle form submission
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'seo_simple_role_settings' ) ) {
			$this->handle_form_submission();
		}
		
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/roles-settings.php';
	}

	/**
	 * Handle form submission.
	 *
	 * @since    1.0.0
	 */
	private function handle_form_submission() {
		// Get settings from form
		$settings = array();
		$capabilities = $this->roles->get_seo_capabilities();
		$wordpress_roles = $this->roles->get_wordpress_roles();
		
		foreach ( $capabilities as $capability ) {
			$settings[$capability] = array();
			
			foreach ( $wordpress_roles as $role_name => $display_name ) {
				$settings[$capability][$role_name] = isset( $_POST[$capability][$role_name] ) ? 1 : 0;
			}
		}
		
		// Update settings
		$this->roles->update_settings( $settings );
		
		// Re-initialize roles
		$this->roles->init_roles();
		
		// Show success message
		add_settings_error(
			'seo_simple_role_settings',
			'settings_updated',
			__( 'Role settings saved successfully.', 'seo-simple' ),
			'updated'
		);
		
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		
		// Redirect to avoid form resubmission
		wp_safe_redirect(
			add_query_arg(
				array( 'settings-updated' => 'true' ),
				wp_get_referer()
			)
		);
		exit;
	}

	/**
	 * AJAX handler for resetting roles.
	 *
	 * @since    1.0.0
	 */
	public function ajax_reset_roles() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_reset_roles_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Reset capabilities
		$this->roles->reset_capabilities();
		
		wp_send_json_success( array( 'message' => __( 'Role settings reset to defaults successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for adding custom role.
	 *
	 * @since    1.0.0
	 */
	public function ajax_add_custom_role() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_add_role_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$role_name = isset( $_POST['role_name'] ) ? sanitize_text_field( $_POST['role_name'] ) : '';
		$display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( $_POST['display_name'] ) : '';
		$capabilities = isset( $_POST['capabilities'] ) ? array_map( 'sanitize_text_field', $_POST['capabilities'] ) : array();
		
		if ( empty( $role_name ) || empty( $display_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Role name and display name are required.', 'seo-simple' ) ) );
		}
		
		// Validate role name
		if ( ! preg_match( '/^[a-z0-9_]+$/', $role_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Role name can only contain lowercase letters, numbers, and underscores.', 'seo-simple' ) ) );
		}
		
		// Check if role already exists
		if ( get_role( $role_name ) ) {
			wp_send_json_error( array( 'message' => __( 'A role with this name already exists.', 'seo-simple' ) ) );
		}
		
		// Add custom role
		$success = $this->roles->add_custom_role( $role_name, $display_name, $capabilities );
		
		if ( $success ) {
			wp_send_json_success( array( 'message' => __( 'Custom role added successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add custom role.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for removing custom role.
	 *
	 * @since    1.0.0
	 */
	public function ajax_remove_custom_role() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_remove_role_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$role_name = isset( $_POST['role_name'] ) ? sanitize_text_field( $_POST['role_name'] ) : '';
		
		if ( empty( $role_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Role name is required.', 'seo-simple' ) ) );
		}
		
		// Remove custom role
		$success = $this->roles->remove_custom_role( $role_name );
		
		if ( $success ) {
			wp_send_json_success( array( 'message' => __( 'Custom role removed successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to remove custom role. It may be a default WordPress role that cannot be removed.', 'seo-simple' ) ) );
		}
	}

	/**
	 * Get role statistics.
	 *
	 * @since    1.0.0
	 * @return   array    The role statistics.
	 */
	public function get_role_statistics() {
		$statistics = array();
		$capabilities = $this->roles->get_seo_capabilities();
		$wordpress_roles = $this->roles->get_wordpress_roles();
		
		foreach ( $wordpress_roles as $role_name => $display_name ) {
			$role = get_role( $role_name );
			
			if ( $role ) {
				$statistics[$role_name] = array(
					'name' => $display_name,
					'capabilities' => array(),
					'user_count' => count( get_users( array( 'role' => $role_name ) ) ),
				);
				
				foreach ( $capabilities as $capability ) {
					$statistics[$role_name]['capabilities'][$capability] = $role->has_cap( $capability );
				}
			}
		}
		
		return $statistics;
	}

	/**
	 * Get custom roles.
	 *
	 * @since    1.0.0
	 * @return   array    The custom roles.
	 */
	public function get_custom_roles() {
		global $wp_roles;
		
		$custom_roles = array();
		$default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		
		foreach ( $wp_roles->roles as $role_name => $role ) {
			if ( ! in_array( $role_name, $default_roles ) ) {
				$custom_roles[$role_name] = $role['name'];
			}
		}
		
		return $custom_roles;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'seo-simple-roles-admin',
			plugin_dir_url( __FILE__ ) . '../js/seo-simple-roles.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_enqueue_style(
			'seo-simple-roles-admin',
			plugin_dir_url( __FILE__ ) . '../css/seo-simple-roles.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		// Localize script
		wp_localize_script(
			'seo-simple-roles-admin',
			'seo_simple_roles',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'reset_nonce' => wp_create_nonce( 'seo_simple_reset_roles_nonce' ),
				'add_role_nonce' => wp_create_nonce( 'seo_simple_add_role_nonce' ),
				'remove_role_nonce' => wp_create_nonce( 'seo_simple_remove_role_nonce' ),
				'reset_confirm_text' => __( 'Are you sure you want to reset all role settings to defaults? This action cannot be undone.', 'seo-simple' ),
				'remove_role_confirm_text' => __( 'Are you sure you want to remove this role? Users with this role will lose their permissions.', 'seo-simple' ),
				'success_text' => __( 'Success', 'seo-simple' ),
				'error_text' => __( 'Error occurred', 'seo-simple' ),
			)
		);
	}

	/**
	 * Handle AJAX requests for roles admin.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// AJAX handlers are already registered in the constructor
		// This method is added for consistency with other admin classes
	}
}
