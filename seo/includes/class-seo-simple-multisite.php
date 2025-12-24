<?php

/**
 * Handles multisite functionality for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles multisite functionality for the SEO plugin.
 *
 * This class handles all multisite-specific functionality including:
 * - Network-wide settings management
 * - Site-specific settings with network defaults
 * - Network activation and deactivation
 * - Cross-site data sharing
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Multisite {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Multisite    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   Seo_Simple_Multisite    The single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the multisite functionality.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function init() {
		if ( ! is_multisite() ) {
			return;
		}

		// Add network admin menu
		add_action( 'network_admin_menu', array( $this, 'add_network_admin_menu' ) );

		// Handle network settings
		add_action( 'admin_init', array( $this, 'handle_network_settings' ) );

		// Add network settings to plugin row
		add_filter( 'plugin_row_meta', array( $this, 'add_network_settings_link' ), 10, 2 );

		// Handle site activation/deactivation
		add_action( 'activate_blog', array( $this, 'on_site_activation' ) );
		add_action( 'deactivate_blog', array( $this, 'on_site_deactivation' ) );

		// Filter settings to use network defaults
		add_filter( 'option_seo_simple_settings', array( $this, 'filter_site_settings' ), 10, 2 );

		// Add network admin notices
		add_action( 'network_admin_notices', array( $this, 'show_network_notices' ) );

		// Handle network-wide actions
		add_action( 'wp_ajax_seo_simple_network_action', array( $this, 'handle_network_ajax' ) );
		
		// Handle network sitemap generation
		add_action( 'wp_ajax_seo_simple_generate_network_sitemap', array( $this, 'ajax_generate_network_sitemap' ) );
	}

	/**
	 * Add network admin menu.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function add_network_admin_menu() {
		add_submenu_page(
			'settings.php',
			__( 'SEO Simple Network Settings', 'seo-simple' ),
			__( 'SEO Simple', 'seo-simple' ),
			'manage_network_options',
			'seo-simple-network',
			array( $this, 'render_network_settings_page' )
		);
	}

	/**
	 * Handle network settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function handle_network_settings() {
		if ( ! is_network_admin() ) {
			return;
		}

		if ( isset( $_POST['seo_simple_network_settings'] ) && 
			 current_user_can( 'manage_network_options' ) && 
			 check_admin_referer( 'seo_simple_network_settings' ) ) {
			
			$settings = $this->sanitize_network_settings( $_POST );
			update_site_option( 'seo_simple_network_settings', $settings );
			
			// Redirect to avoid form resubmission
			wp_redirect( add_query_arg( array( 'updated' => 'true' ), network_admin_url( 'settings.php?page=seo-simple-network' ) ) );
			exit;
		}
	}

	/**
	 * Sanitize network settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array    $settings    The settings to sanitize.
	 * @return   array                 The sanitized settings.
	 */
	public function sanitize_network_settings( $settings ) {
		$sanitized = array();
		
		// General network settings
		if ( isset( $settings['network_enable'] ) ) {
			$sanitized['network_enable'] = absint( $settings['network_enable'] );
		}
		
		if ( isset( $settings['inherit_network_settings'] ) ) {
			$sanitized['inherit_network_settings'] = absint( $settings['inherit_network_settings'] );
		}
		
		if ( isset( $settings['allow_site_override'] ) ) {
			$sanitized['allow_site_override'] = absint( $settings['allow_site_override'] );
		}
		
		// Network-wide SEO settings
		if ( isset( $settings['network_title_separator'] ) ) {
			$sanitized['network_title_separator'] = sanitize_text_field( $settings['network_title_separator'] );
		}
		
		if ( isset( $settings['network_title_format'] ) ) {
			$sanitized['network_title_format'] = sanitize_text_field( $settings['network_title_format'] );
		}
		
		if ( isset( $settings['network_description_template'] ) ) {
			$sanitized['network_description_template'] = sanitize_textarea_field( $settings['network_description_template'] );
		}
		
		if ( isset( $settings['network_enable_og'] ) ) {
			$sanitized['network_enable_og'] = absint( $settings['network_enable_og'] );
		}
		
		if ( isset( $settings['network_enable_twitter'] ) ) {
			$sanitized['network_enable_twitter'] = absint( $settings['network_enable_twitter'] );
		}
		
		if ( isset( $settings['network_enable_json_ld'] ) ) {
			$sanitized['network_enable_json_ld'] = absint( $settings['network_enable_json_ld'] );
		}
		
		// Network sitemap settings
		if ( isset( $settings['network_enable_sitemap'] ) ) {
			$sanitized['network_enable_sitemap'] = absint( $settings['network_enable_sitemap'] );
		}
		
		if ( isset( $settings['network_sitemap_include_sites'] ) && is_array( $settings['network_sitemap_include_sites'] ) ) {
			$sanitized['network_sitemap_include_sites'] = array_map( 'absint', $settings['network_sitemap_include_sites'] );
		}
		
		// Network analytics settings
		if ( isset( $settings['network_google_analytics'] ) ) {
			$sanitized['network_google_analytics'] = sanitize_text_field( $settings['network_google_analytics'] );
		}
		
		if ( isset( $settings['network_google_site_verification'] ) ) {
			$sanitized['network_google_site_verification'] = sanitize_text_field( $settings['network_google_site_verification'] );
		}
		
		if ( isset( $settings['network_bing_site_verification'] ) ) {
			$sanitized['network_bing_site_verification'] = sanitize_text_field( $settings['network_bing_site_verification'] );
		}
		
		return $sanitized;
	}

	/**
	 * Render the network settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function render_network_settings_page() {
		$network_settings = $this->get_network_settings();
		$sites = $this->get_network_sites();
		$updated = isset( $_GET['updated'] ) && $_GET['updated'] === 'true';
		
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/network-settings-page.php';
	}

	/**
	 * Get network settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    The network settings.
	 */
	public function get_network_settings() {
		$defaults = array(
			'network_enable' => 1,
			'inherit_network_settings' => 0,
			'allow_site_override' => 1,
			'network_title_separator' => '-',
			'network_title_format' => '%post_title% | %site_name%',
			'network_description_template' => '',
			'network_enable_og' => 1,
			'network_enable_twitter' => 1,
			'network_enable_json_ld' => 1,
			'network_enable_sitemap' => 1,
			'network_sitemap_include_sites' => array(),
			'network_google_analytics' => '',
			'network_google_site_verification' => '',
			'network_bing_site_verification' => '',
		);
		
		return wp_parse_args( get_site_option( 'seo_simple_network_settings', array() ), $defaults );
	}

	/**
	 * Get network sites.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    The network sites.
	 */
	public function get_network_sites() {
		$sites = get_sites( array( 'number' => 1000 ) );
		$site_list = array();
		
		foreach ( $sites as $site ) {
			$site_list[] = array(
				'blog_id' => $site->blog_id,
				'domain' => $site->domain,
				'path' => $site->path,
				'site_name' => get_blog_option( $site->blog_id, 'blogname' ),
				'active' => $this->is_site_active( $site->blog_id ),
			);
		}
		
		return $site_list;
	}

	/**
	 * Check if a site has the SEO plugin active.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int      $blog_id    The blog ID.
	 * @return   bool                 Whether the plugin is active on the site.
	 */
	public function is_site_active( $blog_id ) {
		if ( ! is_multisite() ) {
			return false;
		}
		
		$active_plugins = get_blog_option( $blog_id, 'active_plugins' );
		return in_array( 'seo-simple/seo-simple.php', $active_plugins );
	}

	/**
	 * Filter site settings to use network defaults.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    mixed    $value      The option value.
	 * @param    string   $option     The option name.
	 * @return   mixed                The filtered option value.
	 */
	public function filter_site_settings( $value, $option ) {
		if ( $option !== 'seo_simple_settings' || ! is_multisite() ) {
			return $value;
		}
		
		$network_settings = $this->get_network_settings();
		
		// If network settings are disabled, return site settings
		if ( empty( $network_settings['network_enable'] ) ) {
			return $value;
		}
		
		// If site settings exist and overrides are allowed, return site settings
		if ( ! empty( $value ) && ! empty( $network_settings['allow_site_override'] ) ) {
			return $value;
		}
		
		// If inherit network settings is enabled, merge network defaults
		if ( ! empty( $network_settings['inherit_network_settings'] ) ) {
			$defaults = $this->get_default_site_settings_from_network();
			return wp_parse_args( $value, $defaults );
		}
		
		return $value;
	}

	/**
	 * Get default site settings from network settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    The default site settings.
	 */
	public function get_default_site_settings_from_network() {
		$network_settings = $this->get_network_settings();
		
		return array(
			'seo_title_separator' => $network_settings['network_title_separator'],
			'seo_title_format' => $network_settings['network_title_format'],
			'seo_description_template' => $network_settings['network_description_template'],
			'seo_enable_og' => $network_settings['network_enable_og'],
			'seo_enable_twitter' => $network_settings['network_enable_twitter'],
			'seo_enable_json_ld' => $network_settings['network_enable_json_ld'],
			'seo_enable_sitemap' => $network_settings['network_enable_sitemap'],
		);
	}

	/**
	 * Handle site activation.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int      $blog_id    The blog ID.
	 */
	public function on_site_activation( $blog_id ) {
		if ( ! is_multisite() ) {
			return;
		}
		
		$network_settings = $this->get_network_settings();
		
		// If inherit network settings is enabled, apply network defaults
		if ( ! empty( $network_settings['inherit_network_settings'] ) ) {
			$defaults = $this->get_default_site_settings_from_network();
			update_blog_option( $blog_id, 'seo_simple_settings', $defaults );
		}
	}

	/**
	 * Handle site deactivation.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int      $blog_id    The blog ID.
	 */
	public function on_site_deactivation( $blog_id ) {
		// Clean up site-specific data if needed
		delete_blog_option( $blog_id, 'seo_simple_settings' );
	}

	/**
	 * Add network settings link to plugin row.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    array    $links      The existing links.
	 * @param    string   $file       The plugin file.
	 * @return   array                The modified links.
	 */
	public function add_network_settings_link( $links, $file ) {
		if ( $file !== 'seo-simple/seo-simple.php' || ! is_multisite() || ! is_network_admin() ) {
			return $links;
		}
		
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			network_admin_url( 'settings.php?page=seo-simple-network' ),
			__( 'Network Settings', 'seo-simple' )
		);
		
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Show network admin notices.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function show_network_notices() {
		if ( isset( $_GET['updated'] ) && $_GET['updated'] === 'true' ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'Network settings saved successfully.', 'seo-simple' ); ?></p>
			</div>
			<?php
		}
		
		// Show notice if network settings are not configured
		$network_settings = $this->get_network_settings();
		if ( empty( $network_settings['network_enable'] ) ) {
			?>
			<div class="notice notice-warning">
				<p><?php _e( 'SEO Simple network features are disabled. Enable them in the <a href="settings.php?page=seo-simple-network">Network Settings</a>.', 'seo-simple' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Handle network AJAX requests.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function handle_network_ajax() {
		if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_network_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
		}
		
		check_ajax_referer( 'seo_simple_network_nonce', 'nonce' );
		
		$action = isset( $_POST['network_action'] ) ? sanitize_text_field( $_POST['network_action'] ) : '';
		
		switch ( $action ) {
			case 'activate_on_sites':
				$this->activate_on_sites();
				break;
				
			case 'deactivate_on_sites':
				$this->deactivate_on_sites();
				break;
				
			case 'sync_settings':
				$this->sync_settings_to_sites();
				break;
				
			default:
				wp_send_json_error( array( 'message' => __( 'Invalid action.', 'seo-simple' ) ) );
		}
	}
	
	/**
	 * AJAX handler for generating network sitemap.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function ajax_generate_network_sitemap() {
		if ( ! is_multisite() || ! is_network_admin() || ! current_user_can( 'manage_network_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
		}
		
		check_ajax_referer( 'seo_simple_network_nonce', 'nonce' );
		
		if ( $this->generate_network_sitemap() ) {
			wp_send_json_success( array( 'message' => __( 'Network sitemap generated successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Error generating network sitemap.', 'seo-simple' ) ) );
		}
	}

	/**
	 * Activate plugin on selected sites.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function activate_on_sites() {
		$sites = isset( $_POST['sites'] ) ? array_map( 'absint', $_POST['sites'] ) : array();
		
		if ( empty( $sites ) ) {
			wp_send_json_error( array( 'message' => __( 'No sites selected.', 'seo-simple' ) ) );
		}
		
		$activated = 0;
		foreach ( $sites as $site_id ) {
			if ( ! $this->is_site_active( $site_id ) ) {
				$active_plugins = get_blog_option( $site_id, 'active_plugins' );
				$active_plugins[] = 'seo-simple/seo-simple.php';
				update_blog_option( $site_id, 'active_plugins', $active_plugins );
				$activated++;
			}
		}
		
		wp_send_json_success( array( 
			'message' => sprintf( __( 'Plugin activated on %d sites.', 'seo-simple' ), $activated )
		) );
	}

	/**
	 * Deactivate plugin on selected sites.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function deactivate_on_sites() {
		$sites = isset( $_POST['sites'] ) ? array_map( 'absint', $_POST['sites'] ) : array();
		
		if ( empty( $sites ) ) {
			wp_send_json_error( array( 'message' => __( 'No sites selected.', 'seo-simple' ) ) );
		}
		
		$deactivated = 0;
		foreach ( $sites as $site_id ) {
			if ( $this->is_site_active( $site_id ) ) {
				$active_plugins = get_blog_option( $site_id, 'active_plugins' );
				$active_plugins = array_diff( $active_plugins, array( 'seo-simple/seo-simple.php' ) );
				update_blog_option( $site_id, 'active_plugins', $active_plugins );
				$deactivated++;
			}
		}
		
		wp_send_json_success( array( 
			'message' => sprintf( __( 'Plugin deactivated on %d sites.', 'seo-simple' ), $deactivated )
		) );
	}

	/**
	 * Sync settings to selected sites.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function sync_settings_to_sites() {
		$sites = isset( $_POST['sites'] ) ? array_map( 'absint', $_POST['sites'] ) : array();
		
		if ( empty( $sites ) ) {
			wp_send_json_error( array( 'message' => __( 'No sites selected.', 'seo-simple' ) ) );
		}
		
		$network_settings = $this->get_network_settings();
		$defaults = $this->get_default_site_settings_from_network();
		
		$synced = 0;
		foreach ( $sites as $site_id ) {
			if ( $this->is_site_active( $site_id ) ) {
				update_blog_option( $site_id, 'seo_simple_settings', $defaults );
				$synced++;
			}
		}
		
		wp_send_json_success( array( 
			'message' => sprintf( __( 'Settings synced to %d sites.', 'seo-simple' ), $synced )
		) );
	}

	/**
	 * Generate network sitemap.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   bool    Whether the sitemap was generated successfully.
	 */
	public function generate_network_sitemap() {
		if ( ! is_multisite() ) {
			return false;
		}
		
		$network_settings = $this->get_network_settings();
		
		if ( empty( $network_settings['network_enable_sitemap'] ) ) {
			return false;
		}
		
		$sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$sitemap_content .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		$include_sites = ! empty( $network_settings['network_sitemap_include_sites'] ) 
			? $network_settings['network_sitemap_include_sites'] 
			: array();
		
		$sites = get_sites( array( 'number' => 1000 ) );
		
		foreach ( $sites as $site ) {
			// Skip if site is not in include list (if list is not empty)
			if ( ! empty( $include_sites ) && ! in_array( $site->blog_id, $include_sites ) ) {
				continue;
			}
			
			// Skip if plugin is not active on site
			if ( ! $this->is_site_active( $site->blog_id ) ) {
				continue;
			}
			
			switch_to_blog( $site->blog_id );
			
			// Check if site has sitemap enabled
			$site_settings = get_option( 'seo_simple_settings', array() );
			if ( ! empty( $site_settings['seo_enable_sitemap'] ) ) {
				$sitemap_url = get_home_url( null, 'sitemap.xml' );
				$lastmod = get_option( 'seo_simple_sitemap_last_generated', 0 );
				
				if ( $lastmod ) {
					$lastmod = date( 'Y-m-d\TH:i:s\Z', $lastmod );
				} else {
					$lastmod = date( 'Y-m-d\TH:i:s\Z' );
				}
				
				$sitemap_content .= "\t" . '<sitemap>' . "\n";
				$sitemap_content .= "\t\t" . '<loc>' . esc_url( $sitemap_url ) . '</loc>' . "\n";
				$sitemap_content .= "\t\t" . '<lastmod>' . $lastmod . '</lastmod>' . "\n";
				$sitemap_content .= "\t" . '</sitemap>' . "\n";
			}
			
			restore_current_blog();
		}
		
		$sitemap_content .= '</sitemapindex>';
		
		// Save network sitemap
		$upload_dir = wp_upload_dir();
		$sitemap_path = $upload_dir['basedir'] . '/network-sitemap.xml';
		
		if ( file_put_contents( $sitemap_path, $sitemap_content ) ) {
			update_site_option( 'seo_simple_network_sitemap_last_generated', current_time( 'timestamp' ) );
			return true;
		}
		
		return false;
	}

	/**
	 * Check if current site can override network settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   bool    Whether the site can override network settings.
	 */
	public function can_site_override() {
		if ( ! is_multisite() ) {
			return true;
		}
		
		$network_settings = $this->get_network_settings();
		return ! empty( $network_settings['allow_site_override'] );
	}

	/**
	 * Check if network settings are enabled.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   bool    Whether network settings are enabled.
	 */
	public function is_network_enabled() {
		if ( ! is_multisite() ) {
			return false;
		}
		
		$network_settings = $this->get_network_settings();
		return ! empty( $network_settings['network_enable'] );
	}

	/**
	 * Get network analytics code.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   string    The network analytics code.
	 */
	public function get_network_analytics() {
		if ( ! $this->is_network_enabled() ) {
			return '';
		}
		
		$network_settings = $this->get_network_settings();
		return ! empty( $network_settings['network_google_analytics'] ) 
			? $network_settings['network_google_analytics'] 
			: '';
	}

	/**
	 * Get network site verification codes.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string   $engine     The search engine (google, bing).
	 * @return   string               The verification code.
	 */
	public function get_network_verification( $engine ) {
		if ( ! $this->is_network_enabled() ) {
			return '';
		}
		
		$network_settings = $this->get_network_settings();
		$setting_key = 'network_' . $engine . '_site_verification';
		
		return ! empty( $network_settings[$setting_key] ) 
			? $network_settings[$setting_key] 
			: '';
	}
}