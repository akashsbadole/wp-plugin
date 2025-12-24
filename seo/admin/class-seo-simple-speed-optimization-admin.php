<?php

/**
 * Handles the admin side of the speed optimization features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin side of the speed optimization features.
 *
 * This class defines all the code necessary for the speed optimization
 * admin area functionality.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Speed_Optimization_Admin {

	/**
	 * The speed optimization handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Speed_Optimization    $speed_optimization    The speed optimization handler instance.
	 */
	private $speed_optimization;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Speed_Optimization    $speed_optimization    The speed optimization handler instance.
	 */
	public function __construct( $speed_optimization ) {
		$this->speed_optimization = $speed_optimization;
	}

	/**
	 * Register hooks for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Register admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu_items' ) );
		
		// Register meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		
		// Register dashboard widget
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		
		// Register admin bar menu
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
		
		// Register settings
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		
		// Register menu items
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Register scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_test_page_speed', array( $this, 'ajax_test_page_speed' ) );
		add_action( 'wp_ajax_seo_simple_update_speed_data', array( $this, 'ajax_update_speed_data' ) );
		add_action( 'wp_ajax_seo_simple_save_single_speed_setting', array( $this, 'ajax_save_single_setting' ) );
		add_action( 'wp_ajax_seo_simple_test_speed_feature', array( $this, 'ajax_test_feature' ) );
		add_action( 'wp_ajax_seo_simple_export_speed_settings', array( $this, 'ajax_export_settings' ) );
		add_action( 'wp_ajax_seo_simple_import_speed_settings', array( $this, 'ajax_import_settings' ) );
		add_action( 'wp_ajax_seo_simple_reset_speed_settings', array( $this, 'ajax_reset_settings' ) );
		add_action( 'wp_ajax_seo_simple_get_cache_details', array( $this, 'ajax_get_cache_details' ) );
		add_action( 'wp_ajax_seo_simple_delete_cache_file', array( $this, 'ajax_delete_cache_file' ) );
		add_action( 'wp_ajax_seo_simple_optimize_images', array( $this, 'ajax_optimize_images' ) );
		add_action( 'wp_ajax_seo_simple_generate_webp', array( $this, 'ajax_generate_webp' ) );
		add_action( 'wp_ajax_seo_simple_download_google_fonts', array( $this, 'ajax_download_fonts' ) );
		add_action( 'wp_ajax_seo_simple_get_cache_stats', array( $this, 'ajax_get_cache_stats' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'seo-simple-speed-optimization-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-speed-optimization-admin.css',
			array(),
			SEO_SIMPLE_VERSION,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'seo-simple-speed-optimization-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-speed-optimization-admin.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			false
		);

		wp_localize_script(
			'seo-simple-speed-optimization-admin',
			'seo_simple_speed_optimization',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_speed_optimization_nonce' ),
				'strings' => array(
					'confirm_clear_cache' => __( 'Are you sure you want to clear all caches? This action cannot be undone.', 'seo-simple' ),
					'cache_cleared' => __( 'Cache cleared successfully!', 'seo-simple' ),
					'error_occurred' => __( 'An error occurred. Please try again.', 'seo-simple' ),
					'settings_saved' => __( 'Settings saved successfully!', 'seo-simple' ),
					'test_running' => __( 'Running speed test... Please wait.', 'seo-simple' ),
					'test_completed' => __( 'Speed test completed!', 'seo-simple' ),
					'saving' => __( 'Saving...', 'seo-simple' ),
				),
			)
		);
	}

	/**
	 * Add meta box for speed optimization.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_box() {
		add_meta_box(
			'seo-simple-speed-optimization',
			__( 'Speed Optimization', 'seo-simple' ),
			array( $this, 'render_meta_box' ),
			'post',
			'normal',
			'default'
		);

		add_meta_box(
			'seo-simple-speed-optimization',
			__( 'Speed Optimization', 'seo-simple' ),
			array( $this, 'render_meta_box' ),
			'page',
			'normal',
			'default'
		);

		// Add to WooCommerce products if active
		if ( class_exists( 'WooCommerce' ) ) {
			add_meta_box(
				'seo-simple-speed-optimization',
				__( 'Speed Optimization', 'seo-simple' ),
				array( $this, 'render_meta_box' ),
				'product',
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render meta box content.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_simple_speed_optimization_meta_box', 'seo_simple_speed_optimization_nonce' );

		// Get post meta
		$disable_optimization = get_post_meta( $post->ID, '_seo_simple_disable_speed_optimization', true );
		$custom_critical_css = get_post_meta( $post->ID, '_seo_simple_custom_critical_css', true );

		include_once plugin_dir_path( __FILE__ ) . 'partials/meta-box-speed-optimization.php';
	}

	/**
	 * Save meta box data.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 */
	public function save_meta_box( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['seo_simple_speed_optimization_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['seo_simple_speed_optimization_nonce'], 'seo_simple_speed_optimization_meta_box' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		// Sanitize and save the data
		$disable_optimization = isset( $_POST['seo_simple_disable_speed_optimization'] ) ? '1' : '0';
		update_post_meta( $post_id, '_seo_simple_disable_speed_optimization', $disable_optimization );

		$custom_critical_css = isset( $_POST['seo_simple_custom_critical_css'] ) ? sanitize_textarea_field( $_POST['seo_simple_custom_critical_css'] ) : '';
		update_post_meta( $post_id, '_seo_simple_custom_critical_css', $custom_critical_css );
	}

	/**
	 * Add dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo-simple-speed-dashboard',
			__( 'Speed Optimization Dashboard', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		// Get speed test results
		$speed_test_results = get_option( 'seo_simple_speed_test_results', array() );
		
		// Get cache statistics
		$cache_stats = $this->speed_optimization->get_cache_statistics();
		
		// Get settings
		$settings = get_option( 'seo_simple_settings', array() );

		include_once plugin_dir_path( __FILE__ ) . 'partials/dashboard-speed-widget.php';
	}

	/**
	 * Add admin bar menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_bar_menu() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'seo-simple-speed-optimization',
				'title' => __( 'Speed Test', 'seo-simple' ),
				'href'  => admin_url( 'admin.php?page=seo-simple-speed' ),
				'meta'  => array(
					'title' => __( 'Run Speed Test', 'seo-simple' ),
				),
			)
		);
	}

	/**
	 * Add settings to the settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings() {
		// Register the settings group
		register_setting(
			'seo_simple_speed_settings',
			'seo_simple_speed_settings',
			array( $this, 'sanitize_settings' )
		);
		
		// Also register with the main settings group for compatibility
		register_setting(
			'seo_simple_settings',
			'seo_simple_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'seo_simple_speed_settings_section',
			__( 'Speed Optimization Settings', 'seo-simple' ),
			array( $this, 'render_settings_section' ),
			'seo-simple-speed'
		);

		add_settings_field(
			'speed_page_caching',
			__( 'Page Caching', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_page_caching',
				'description' => __( 'Enable page caching to create static HTML files for fast delivery.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_browser_caching',
			__( 'Browser Caching', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_browser_caching',
				'description' => __( 'Enable browser caching to reduce loading times on repeat visits.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_gzip_compression',
			__( 'GZIP Compression', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_gzip_compression',
				'description' => __( 'Enable GZIP compression to reduce file sizes.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_minify_css',
			__( 'Minify CSS', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_minify_css',
				'description' => __( 'Minify CSS files to reduce their size.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_minify_js',
			__( 'Minify JavaScript', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_minify_js',
				'description' => __( 'Minify JavaScript files to reduce their size.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_minify_html',
			__( 'Minify HTML', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_minify_html',
				'description' => __( 'Minify HTML output to reduce page size.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_lazy_loading',
			__( 'Lazy Loading', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_lazy_loading',
				'description' => __( 'Enable lazy loading for images and videos.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_database_cleanup',
			__( 'Database Cleanup', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_database_cleanup',
				'description' => __( 'Automatically clean up database to remove unnecessary data.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_cleanup_interval',
			__( 'Cleanup Interval', 'seo-simple' ),
			array( $this, 'render_select_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_cleanup_interval',
				'options' => array(
					'daily' => __( 'Daily', 'seo-simple' ),
					'weekly' => __( 'Weekly', 'seo-simple' ),
					'monthly' => __( 'Monthly', 'seo-simple' ),
				),
				'description' => __( 'How often to run database cleanup.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_combine_css',
			__( 'Combine CSS Files', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_combine_css',
				'description' => __( 'Combine multiple CSS files into a single file to reduce HTTP requests.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_combine_js',
			__( 'Combine JavaScript Files', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_combine_js',
				'description' => __( 'Combine multiple JavaScript files into a single file to reduce HTTP requests.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_defer_js',
			__( 'Defer JavaScript', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_defer_js',
				'description' => __( 'Defer loading of JavaScript files to improve perceived performance.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_async_css',
			__( 'Async CSS', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_async_css',
				'description' => __( 'Load CSS asynchronously to prevent render-blocking.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_remove_query_strings',
			__( 'Remove Query Strings', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_remove_query_strings',
				'description' => __( 'Remove query strings from static resources to improve caching.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_optimize_images',
			__( 'Optimize Images', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_optimize_images',
				'description' => __( 'Automatically optimize images on upload.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_webp_support',
			__( 'WebP Support', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_webp_support',
				'description' => __( 'Generate and serve WebP images for supported browsers.', 'seo-simple' ),
			)
		);

		add_settings_field(
			'speed_local_google_fonts',
			__( 'Local Google Fonts', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			'seo-simple-speed-optimization',
			'seo_simple_speed_settings_section',
			array(
				'name' => 'speed_local_google_fonts',
				'description' => __( 'Download Google Fonts locally to improve loading speed.', 'seo-simple' ),
			)
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @since    1.0.0
	 * @param    array    $input    The input data.
	 * @return   array              The sanitized data.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize checkbox fields
		$checkbox_fields = array(
			'speed_page_caching',
			'speed_browser_caching',
			'speed_gzip_compression',
			'speed_minify_css',
			'speed_minify_js',
			'speed_minify_html',
			'speed_lazy_loading',
			'speed_database_cleanup',
			'speed_combine_css',
			'speed_combine_js',
			'speed_defer_js',
			'speed_async_css',
			'speed_remove_query_strings',
			'speed_optimize_images',
			'speed_webp_support',
			'speed_local_google_fonts',
		);

		foreach ( $checkbox_fields as $field ) {
			$sanitized[$field] = isset( $input[$field] ) ? (bool) $input[$field] : false;
		}

		// Sanitize select fields
		if ( isset( $input['speed_cleanup_interval'] ) ) {
			$sanitized['speed_cleanup_interval'] = sanitize_text_field( $input['speed_cleanup_interval'] );
		}

		return $sanitized;
	}

	/**
	 * Render settings section.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_section() {
		echo '<p>' . __( 'Configure speed optimization settings to improve your website performance.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render checkbox field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$settings = get_option( 'seo_simple_settings', array() );
		$name = $args['name'];
		$checked = isset( $settings[$name] ) && $settings[$name] ? 'checked="checked"' : '';
		$description = isset( $args['description'] ) ? $args['description'] : '';

		echo '<input type="checkbox" id="' . esc_attr( $name ) . '" name="seo_simple_settings[' . esc_attr( $name ) . ']" value="1" ' . $checked . ' />';
		echo '<label for="' . esc_attr( $name ) . '">' . esc_html( $description ) . '</label>';
	}

	/**
	 * Render select field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_select_field( $args ) {
		$settings = get_option( 'seo_simple_settings', array() );
		$name = $args['name'];
		$options = isset( $args['options'] ) ? $args['options'] : array();
		$description = isset( $args['description'] ) ? $args['description'] : '';
		$current_value = isset( $settings[$name] ) ? $settings[$name] : '';

		echo '<select id="' . esc_attr( $name ) . '" name="seo_simple_settings[' . esc_attr( $name ) . ']">';
		
		foreach ( $options as $value => $label ) {
			$selected = $current_value === $value ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		
		echo '</select>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}

	/**
	 * Add settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Speed Optimization', 'seo-simple' ),
			__( 'Speed', 'seo-simple' ),
			'manage_options',
			'seo-simple-speed-optimization',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		// Get settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Get speed test results
		$speed_test_results = get_option( 'seo_simple_speed_test_results', array() );
		
		// Get cache statistics
		$cache_stats = $this->speed_optimization->get_cache_statistics();

		include_once plugin_dir_path( __FILE__ ) . 'partials/speed-optimization-page.php';
	}

	/**
	 * Add speed optimization tab to the main settings page.
	 *
	 * @since    1.0.0
	 * @param    array    $tabs    The existing tabs.
	 * @return   array             The modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['speed'] = array(
			'label' => __( 'Speed', 'seo-simple' ),
			'icon' => 'dashicons-performance',
			'callback' => array( $this, 'render_settings_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render settings tab.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_tab() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/speed-settings-page.php';
	}

	/**
	 * Add speed optimization to the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'seo-simple',
			__( 'Speed Optimization', 'seo-simple' ),
			__( 'Speed', 'seo-simple' ),
			'manage_options',
			'seo-simple-speed-optimization',
			array( $this, 'render_speed_page' )
		);
	}

	/**
	 * Render speed page.
	 *
	 * @since    1.0.0
	 */
	public function render_speed_page() {
		// Get settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Get speed test results
		$speed_test_results = get_option( 'seo_simple_speed_test_results', array() );
		
		// Get cache statistics
		$cache_stats = $this->speed_optimization->get_cache_statistics();

		include_once plugin_dir_path( __FILE__ ) . 'partials/speed-page.php';
	}

	/**
	 * Add speed optimization to the admin bar.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_bar_items() {
		global $wp_admin_bar;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add main menu item
		$wp_admin_bar->add_menu(
			array(
				'id'    => 'seo-simple-speed-optimization',
				'title' => '<span class="ab-icon dashicons-performance"></span>' . __( 'Speed Test', 'seo-simple' ),
				'href'  => admin_url( 'admin.php?page=seo-simple-speed' ),
				'meta'  => array(
					'title' => __( 'Speed Optimization', 'seo-simple' ),
				),
			)
		);

		// Add clear cache item
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'seo-simple-speed-optimization',
				'id'     => 'seo-simple-speed-clear-cache',
				'title'  => __( 'Clear Cache', 'seo-simple' ),
				'href'   => '#',
				'meta'   => array(
					'title' => __( 'Clear all speed optimization caches', 'seo-simple' ),
					'onclick' => 'seoSimpleSpeedOptimization.clearCache(event);',
				),
			)
		);

		// Add run test item
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'seo-simple-speed-optimization',
				'id'     => 'seo-simple-speed-run-test',
				'title'  => __( 'Run Speed Test', 'seo-simple' ),
				'href'   => '#',
				'meta'   => array(
					'title' => __( 'Run a speed test on your website', 'seo-simple' ),
					'onclick' => 'seoSimpleSpeedOptimization.runSpeedTest(event);',
				),
			)
		);
	}

	/**
	 * Add speed optimization to the admin footer.
	 *
	 * @since    1.0.0
	 * @param    string    $footer    The footer text.
	 * @return   string               The modified footer text.
	 */
	public function add_admin_footer( $footer ) {
		// Only show on speed optimization pages
		if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'seo-simple-speed' || $_GET['page'] === 'seo-simple-settings' ) ) {
			$settings = get_option( 'seo_simple_settings', array() );
			
			// Count enabled features
			$enabled_features = 0;
			$speed_features = array(
				'speed_page_caching',
				'speed_browser_caching',
				'speed_gzip_compression',
				'speed_minify_css',
				'speed_minify_js',
				'speed_minify_html',
				'speed_lazy_loading',
				'speed_database_cleanup',
				'speed_combine_css',
				'speed_combine_js',
				'speed_defer_js',
				'speed_async_css',
				'speed_remove_query_strings',
				'speed_optimize_images',
				'speed_webp_support',
				'speed_local_google_fonts',
			);
			
			foreach ( $speed_features as $feature ) {
				if ( isset( $settings[$feature] ) && $settings[$feature] ) {
					$enabled_features++;
				}
			}
			
			$footer .= ' | ' . sprintf(
				__( 'Speed Optimization: %d/%d features enabled', 'seo-simple' ),
				$enabled_features,
				count( $speed_features )
			);
		}
		
		return $footer;
	}

	/**
	* AJAX handler for testing page speed.
	*
	* @since    1.0.0
	*/
	public function ajax_test_page_speed() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_post', $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to test this page.', 'seo-simple' ) ) );
		}
		
		$post_id = intval( $_POST['post_id'] );
		
		// Run speed test
		$score = $this->speed_optimization->run_speed_test();
		
		// Save score
		update_post_meta( $post_id, '_seo_simple_speed_score', $score['page_load_time'] );
		update_post_meta( $post_id, '_seo_simple_speed_last_updated', current_time( 'mysql' ) );
		
		wp_send_json_success( array( 'score' => $score['page_load_time'] ) );
	}

	/**
	* AJAX handler for updating speed data.
	*
	* @since    1.0.0
	*/
	public function ajax_update_speed_data() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_post', $_POST['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to update this page.', 'seo-simple' ) ) );
		}
		
		$post_id = intval( $_POST['post_id'] );
		
		// Get speed test results
		$results = $this->speed_optimization->run_speed_test();
		
		// Save data
		update_post_meta( $post_id, '_seo_simple_page_size', $results['page_size'] );
		update_post_meta( $post_id, '_seo_simple_load_time', $results['page_load_time'] );
		update_post_meta( $post_id, '_seo_simple_requests', $results['requests'] );
		update_post_meta( $post_id, '_seo_simple_speed_last_updated', current_time( 'mysql' ) );
		
		wp_send_json_success( array(
			'page_size' => size_format( $results['page_size'] ),
			'load_time' => $results['page_load_time'],
			'requests' => $results['requests'],
			'last_updated' => date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
		) );
	}

	/**
		* AJAX handler for saving a single setting.
		*
		* @since    1.0.0
		*/
	public function ajax_save_single_setting() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to save settings.', 'seo-simple' ) ) );
		}
		
		$setting = sanitize_text_field( $_POST['setting'] );
		$value = $_POST['value'] === '1' ? true : false;
		
		// Get current settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Update setting
		$settings[$setting] = $value;
		
		// Save settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Setting saved successfully.', 'seo-simple' ) ) );
	}

	/**
		* AJAX handler for testing a feature.
		*
		* @since    1.0.0
		*/
	public function ajax_test_feature() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to test features.', 'seo-simple' ) ) );
		}
		
		$feature = sanitize_text_field( $_POST['feature'] );
		
		// Test feature
		$result = $this->speed_optimization->test_feature( $feature );
		
		wp_send_json_success( array(
			'message' => $result['message'],
			'result' => $result,
		) );
	}

	/**
		* AJAX handler for exporting settings.
		*
		* @since    1.0.0
		*/
	public function ajax_export_settings() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to export settings.', 'seo-simple' ) ) );
		}
		
		// Get settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Filter speed settings
		$speed_settings = array();
		$speed_features = array(
			'speed_page_caching',
			'speed_browser_caching',
			'speed_gzip_compression',
			'speed_minify_css',
			'speed_minify_js',
			'speed_minify_html',
			'speed_lazy_loading',
			'speed_database_cleanup',
			'speed_cleanup_interval',
			'speed_combine_css',
			'speed_combine_js',
			'speed_defer_js',
			'speed_async_css',
			'speed_remove_query_strings',
			'speed_optimize_images',
			'speed_webp_support',
			'speed_local_google_fonts',
		);
		
		foreach ( $speed_features as $feature ) {
			if ( isset( $settings[$feature] ) ) {
				$speed_settings[$feature] = $settings[$feature];
			}
		}
		
		wp_send_json_success( array( 'settings' => $speed_settings ) );
	}

	/**
		* AJAX handler for importing settings.
		*
		* @since    1.0.0
		*/
	public function ajax_import_settings() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to import settings.', 'seo-simple' ) ) );
		}
		
		// Get settings
		$settings = json_decode( stripslashes( $_POST['settings'] ), true );
		
		if ( ! is_array( $settings ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid settings format.', 'seo-simple' ) ) );
		}
		
		// Get current settings
		$current_settings = get_option( 'seo_simple_settings', array() );
		
		// Merge settings
		$merged_settings = array_merge( $current_settings, $settings );
		
		// Save settings
		update_option( 'seo_simple_settings', $merged_settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings imported successfully.', 'seo-simple' ) ) );
	}

	/**
		* AJAX handler for resetting settings.
		*
		* @since    1.0.0
		*/
	public function ajax_reset_settings() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to reset settings.', 'seo-simple' ) ) );
		}
		
		// Get current settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Remove speed settings
		$speed_features = array(
			'speed_page_caching',
			'speed_browser_caching',
			'speed_gzip_compression',
			'speed_minify_css',
			'speed_minify_js',
			'speed_minify_html',
			'speed_lazy_loading',
			'speed_database_cleanup',
			'speed_cleanup_interval',
			'speed_combine_css',
			'speed_combine_js',
			'speed_defer_js',
			'speed_async_css',
			'speed_remove_query_strings',
			'speed_optimize_images',
			'speed_webp_support',
			'speed_local_google_fonts',
		);
		
		foreach ( $speed_features as $feature ) {
			if ( isset( $settings[$feature] ) ) {
				unset( $settings[$feature] );
			}
		}
		
		// Save settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings reset successfully.', 'seo-simple' ) ) );
	}

	/**
		* AJAX handler for getting cache details.
		*
		* @since    1.0.0
		*/
	public function ajax_get_cache_details() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to view cache details.', 'seo-simple' ) ) );
		}
		
		$cache_type = sanitize_text_field( $_POST['cache_type'] );
		
		// Get cache details
		$details = $this->speed_optimization->get_cache_details( $cache_type );
		
		wp_send_json_success( array( 'details' => $details ) );
	}

	/**
		* AJAX handler for deleting cache file.
		*
		* @since    1.0.0
		*/
	public function ajax_delete_cache_file() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to delete cache files.', 'seo-simple' ) ) );
		}
		
		$file = sanitize_text_field( $_POST['file'] );
		
		// Delete cache file
		$result = $this->speed_optimization->delete_cache_file( $file );
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Cache file deleted successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete cache file.', 'seo-simple' ) ) );
		}
	}

	/**
		* AJAX handler for optimizing images.
		*
		* @since    1.0.0
		*/
	public function ajax_optimize_images() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to optimize images.', 'seo-simple' ) ) );
		}
		
		// Optimize images
		$result = $this->speed_optimization->optimize_all_images();
		
		wp_send_json_success( array(
			'message' => __( 'Image optimization completed.', 'seo-simple' ),
			'progress' => $result,
		) );
	}

	/**
		* AJAX handler for generating WebP images.
		*
		* @since    1.0.0
		*/
	public function ajax_generate_webp() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to generate WebP images.', 'seo-simple' ) ) );
		}
		
		// Generate WebP images
		$result = $this->speed_optimization->generate_all_webp_images();
		
		wp_send_json_success( array(
			'message' => __( 'WebP generation completed.', 'seo-simple' ),
			'progress' => $result,
		) );
	}

	/**
		* AJAX handler for downloading Google Fonts.
		*
		* @since    1.0.0
		*/
	public function ajax_download_fonts() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to download Google Fonts.', 'seo-simple' ) ) );
		}
		
		// Download Google Fonts
		$result = $this->speed_optimization->download_all_google_fonts();
		
		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Google Fonts downloaded successfully.', 'seo-simple' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to download Google Fonts.', 'seo-simple' ) ) );
		}
	}

	/**
		* AJAX handler for getting cache statistics.
		*
		* @since    1.0.0
		*/
	public function ajax_get_cache_stats() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to view cache statistics.', 'seo-simple' ) ) );
		}
		
		// Get cache statistics
		$stats = $this->speed_optimization->get_cache_statistics();
		
		wp_send_json_success( array( 'stats' => $stats ) );
	}
}
