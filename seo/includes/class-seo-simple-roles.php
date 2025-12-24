<?php

/**
 * Handles role management for SEO functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles role management for SEO functionality.
 *
 * This class handles all operations related to role management including
 * creating custom roles, managing capabilities, and restricting access.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Roles {

	/**
	 * The option key for role settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_key    The option key for role settings.
	 */
	private $option_key = 'seo_simple_role_settings';

	/**
	 * The default SEO capabilities.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array     $default_capabilities    The default SEO capabilities.
	 */
	private $default_capabilities = array(
		'seo_simple_manage_settings' => array(
			'administrator' => true,
			'editor' => false,
			'author' => false,
			'contributor' => false,
			'subscriber' => false,
		),
		'seo_simple_analyze_content' => array(
			'administrator' => true,
			'editor' => true,
			'author' => true,
			'contributor' => false,
			'subscriber' => false,
		),
		'seo_simple_edit_meta' => array(
			'administrator' => true,
			'editor' => true,
			'author' => true,
			'contributor' => false,
			'subscriber' => false,
		),
		'seo_simple_view_dashboard' => array(
			'administrator' => true,
			'editor' => true,
			'author' => false,
			'contributor' => false,
			'subscriber' => false,
		),
		'seo_simple_manage_redirects' => array(
			'administrator' => true,
			'editor' => false,
			'author' => false,
			'contributor' => false,
			'subscriber' => false,
		),
		'seo_simple_manage_sitemap' => array(
			'administrator' => true,
			'editor' => false,
			'author' => false,
			'contributor' => false,
			'subscriber' => false,
		),
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Register activation and deactivation hooks
		register_activation_hook( SEO_SIMPLE_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( SEO_SIMPLE_PLUGIN_FILE, array( $this, 'deactivate' ) );
		
		// Initialize roles
		add_action( 'init', array( $this, 'init_roles' ) );
		
		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Activate the role manager.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		// Set default capabilities
		$this->set_default_capabilities();
		
		// Create SEO Manager role if it doesn't exist
		$this->create_seo_manager_role();
	}

	/**
	 * Deactivate the role manager.
	 *
	 * @since    1.0.0
	 */
	public function deactivate() {
		// Remove SEO Manager role
		if ( get_role( 'seo_manager' ) ) {
			remove_role( 'seo_manager' );
		}
	}

	/**
	 * Initialize roles.
	 *
	 * @since    1.0.0
	 */
	public function init_roles() {
		// Get current settings
		$settings = $this->get_settings();
		
		// Apply capabilities to roles
		foreach ( $settings as $capability => $roles ) {
			foreach ( $roles as $role_name => $has_capability ) {
				$role = get_role( $role_name );
				
				if ( $role ) {
					if ( $has_capability ) {
						$role->add_cap( $capability );
					} else {
						$role->remove_cap( $capability );
					}
				}
			}
		}
	}

	/**
	 * Set default capabilities.
	 *
	 * @since    1.0.0
	 */
	private function set_default_capabilities() {
		// Check if settings already exist
		$settings = get_option( $this->option_key );
		
		if ( $settings === false ) {
			// Set default settings
			update_option( $this->option_key, $this->default_capabilities );
		}
	}

	/**
	 * Create SEO Manager role.
	 *
	 * @since    1.0.0
	 */
	private function create_seo_manager_role() {
		// Check if role already exists
		if ( get_role( 'seo_manager' ) ) {
			return;
		}
		
		// Get editor role as base
		$editor = get_role( 'editor' );
		
		if ( $editor ) {
			// Create SEO Manager role with editor capabilities
			add_role( 'seo_manager', __( 'SEO Manager', 'seo-simple' ), $editor->capabilities );
			
			// Add SEO-specific capabilities
			$seo_manager = get_role( 'seo_manager' );
			
			if ( $seo_manager ) {
				$seo_manager->add_cap( 'seo_simple_manage_settings' );
				$seo_manager->add_cap( 'seo_simple_analyze_content' );
				$seo_manager->add_cap( 'seo_simple_edit_meta' );
				$seo_manager->add_cap( 'seo_simple_view_dashboard' );
				$seo_manager->add_cap( 'seo_simple_manage_redirects' );
				$seo_manager->add_cap( 'seo_simple_manage_sitemap' );
			}
		}
	}

	/**
	 * Register settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			'seo_simple_role_settings',
			$this->option_key,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default' => $this->default_capabilities,
			)
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @since    1.0.0
	 * @param    array     $settings    The settings to sanitize.
	 * @return   array                  The sanitized settings.
	 */
	public function sanitize_settings( $settings ) {
		$sanitized = array();
		
		foreach ( $this->default_capabilities as $capability => $default_roles ) {
			if ( isset( $settings[$capability] ) && is_array( $settings[$capability] ) ) {
				$sanitized[$capability] = array();
				
				foreach ( $default_roles as $role_name => $default_value ) {
					$sanitized[$capability][$role_name] = isset( $settings[$capability][$role_name] ) ? (bool) $settings[$capability][$role_name] : $default_value;
				}
			} else {
				$sanitized[$capability] = $default_roles;
			}
		}
		
		return $sanitized;
	}

	/**
	 * Get role settings.
	 *
	 * @since    1.0.0
	 * @return   array    The role settings.
	 */
	public function get_settings() {
		return get_option( $this->option_key, $this->default_capabilities );
	}

	/**
	 * Update role settings.
	 *
	 * @since    1.0.0
	 * @param    array     $settings    The new settings.
	 * @return   bool                  True on success, false on failure.
	 */
	public function update_settings( $settings ) {
		return update_option( $this->option_key, $settings );
	}

	/**
	 * Check if a user has a specific SEO capability.
	 *
	 * @since    1.0.0
	 * @param    string    $capability    The capability to check.
	 * @param    int       $user_id       The user ID (optional, defaults to current user).
	 * @return   bool                     True if user has capability, false otherwise.
	 */
	public function current_user_can( $capability, $user_id = null ) {
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			return $user && $user->has_cap( $capability );
		} else {
			return current_user_can( $capability );
		}
	}

	/**
	 * Get all SEO capabilities.
	 *
	 * @since    1.0.0
	 * @return   array    The SEO capabilities.
	 */
	public function get_seo_capabilities() {
		return array_keys( $this->default_capabilities );
	}

	/**
	 * Get capability label.
	 *
	 * @since    1.0.0
	 * @param    string    $capability    The capability.
	 * @return   string                   The capability label.
	 */
	public function get_capability_label( $capability ) {
		$labels = array(
			'seo_simple_manage_settings' => __( 'Manage SEO Settings', 'seo-simple' ),
			'seo_simple_analyze_content' => __( 'Analyze Content', 'seo-simple' ),
			'seo_simple_edit_meta' => __( 'Edit SEO Meta', 'seo-simple' ),
			'seo_simple_view_dashboard' => __( 'View SEO Dashboard', 'seo-simple' ),
			'seo_simple_manage_redirects' => __( 'Manage Redirects', 'seo-simple' ),
			'seo_simple_manage_sitemap' => __( 'Manage Sitemap', 'seo-simple' ),
		);
		
		return isset( $labels[$capability] ) ? $labels[$capability] : $capability;
	}

	/**
	 * Get capability description.
	 *
	 * @since    1.0.0
	 * @param    string    $capability    The capability.
	 * @return   string                   The capability description.
	 */
	public function get_capability_description( $capability ) {
		$descriptions = array(
			'seo_simple_manage_settings' => __( 'Can access and modify SEO settings in the WordPress admin.', 'seo-simple' ),
			'seo_simple_analyze_content' => __( 'Can view SEO analysis for posts and pages.', 'seo-simple' ),
			'seo_simple_edit_meta' => __( 'Can edit SEO meta fields like title, description, and keywords.', 'seo-simple' ),
			'seo_simple_view_dashboard' => __( 'Can view the SEO dashboard with statistics and reports.', 'seo-simple' ),
			'seo_simple_manage_redirects' => __( 'Can create, edit, and delete URL redirects.', 'seo-simple' ),
			'seo_simple_manage_sitemap' => __( 'Can regenerate and configure XML sitemaps.', 'seo-simple' ),
		);
		
		return isset( $descriptions[$capability] ) ? $descriptions[$capability] : '';
	}

	/**
	 * Get all WordPress roles.
	 *
	 * @since    1.0.0
	 * @return   array    The WordPress roles.
	 */
	public function get_wordpress_roles() {
		global $wp_roles;
		
		$roles = array();
		
		foreach ( $wp_roles->roles as $role_name => $role ) {
			$roles[$role_name] = $role['name'];
		}
		
		return $roles;
	}

	/**
	 * Reset capabilities to defaults.
	 *
	 * @since    1.0.0
	 */
	public function reset_capabilities() {
		// Update settings to defaults
		$this->update_settings( $this->default_capabilities );
		
		// Re-initialize roles
		$this->init_roles();
	}

	/**
	 * Get users with a specific SEO capability.
	 *
	 * @since    1.0.0
	 * @param    string    $capability    The capability.
	 * @return   array                    The users with the capability.
	 */
	public function get_users_with_capability( $capability ) {
		$users = get_users( array(
			'capability' => $capability,
		) );
		
		return $users;
	}

	/**
	 * Add custom role.
	 *
	 * @since    1.0.0
	 * @param    string    $role_name     The role name.
	 * @param    string    $display_name  The display name.
	 * @param    array     $capabilities  The capabilities.
	 * @return   bool                     True on success, false on failure.
	 */
	public function add_custom_role( $role_name, $display_name, $capabilities = array() ) {
		// Check if role already exists
		if ( get_role( $role_name ) ) {
			return false;
		}
		
		// Add role
		$role_added = add_role( $role_name, $display_name, $capabilities );
		
		if ( $role_added ) {
			// Add SEO capabilities based on settings
			$settings = $this->get_settings();
			
			foreach ( $settings as $capability => $roles ) {
				if ( in_array( $role_name, array_keys( $roles ) ) && $roles[$role_name] ) {
					$role_added->add_cap( $capability );
				}
			}
			
			return true;
		}
		
		return false;
	}

	/**
	 * Remove custom role.
	 *
	 * @since    1.0.0
	 * @param    string    $role_name     The role name.
	 * @return   bool                     True on success, false on failure.
	 */
	public function remove_custom_role( $role_name ) {
		// Don't allow removing default WordPress roles
		$wp_default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		
		if ( in_array( $role_name, $wp_default_roles ) ) {
			return false;
		}
		
		// Remove role
		return remove_role( $role_name );
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
	 * Get role statistics.
	 *
	 * @since    1.0.0
	 * @return   array    The role statistics.
	 */
	public function get_role_statistics() {
		$statistics = array();
		$capabilities = $this->get_seo_capabilities();
		$wordpress_roles = $this->get_wordpress_roles();
		
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
}
