<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		global $post;
		
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Simple_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Simple_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Only load styles when FAQ content is present on the page
		$should_load_faq_styles = false;
		
		// Check if FAQ shortcode is used
		if ($post && has_shortcode($post->post_content, 'seo_simple_faq')) {
			$should_load_faq_styles = true;
		}
		
		// Check if FAQ block is present
		if (function_exists('has_block') && $post && has_block('seo-simple/faq', $post)) {
			$should_load_faq_styles = true;
		}
		
		// Check if post has FAQs in meta
		if (!$should_load_faq_styles && $post) {
			$faqs = get_post_meta($post->ID, '_seo_simple_faqs', true);
			if (!empty($faqs) && is_array($faqs)) {
				$should_load_faq_styles = true;
			}
		}
		
		// Load FAQ styles if needed
		if ($should_load_faq_styles) {
			wp_enqueue_style( $this->plugin_name . '-faq', plugin_dir_url( __FILE__ ) . 'css/seo-simple-faq.css', array(), $this->version, 'all' );
		}
		
		// Enqueue breadcrumbs styles
		$should_load_breadcrumbs = false;
		
		// Check if breadcrumbs shortcode is used
		if ($post && has_shortcode($post->post_content, 'seo_breadcrumbs')) {
			$should_load_breadcrumbs = true;
		}
		
		// Check if breadcrumbs are enabled in settings
		$settings = get_option('seo_simple_settings', array());
		if (isset($settings['seo_enable_breadcrumbs']) && $settings['seo_enable_breadcrumbs']) {
			$should_load_breadcrumbs = true;
		}
		
		// Load breadcrumbs styles if needed
		if ($should_load_breadcrumbs) {
			wp_enqueue_style( 'seo-simple-breadcrumbs', plugin_dir_url( __FILE__ ) . 'css/seo-simple-breadcrumbs.css', array(), $this->version, 'all' );
		}
		
		// Enqueue local business styles
		$should_load_local_business = false;
		
		// Check if local business shortcode is used
		if ($post && has_shortcode($post->post_content, 'local_business_info')) {
			$should_load_local_business = true;
		}
		
		// Check if this is a location post type
		if ($post && get_post_type($post) === 'seo_location') {
			$should_load_local_business = true;
		}
		
		// Check if local business schema is enabled
		if ($post && get_post_meta($post->ID, '_seo_enable_local_business_schema', true)) {
			$should_load_local_business = true;
		}
		
		// Load local business styles if needed
		if ($should_load_local_business) {
			wp_enqueue_style( 'seo-simple-local-business', plugin_dir_url( __FILE__ ) . 'css/seo-simple-local-business.css', array(), $this->version, 'all' );
		}
		
		// Allow other plugins/themes to force load styles
		if (apply_filters('seo_simple_force_load_public_styles', false)) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/seo-simple-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $post;
		
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Seo_Simple_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Seo_Simple_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Only load scripts when FAQ content is present on the page
		$should_load_faq_scripts = false;
		
		// Check if FAQ shortcode is used
		if ($post && has_shortcode($post->post_content, 'seo_simple_faq')) {
			$should_load_faq_scripts = true;
		}
		
		// Check if FAQ block is present
		if (function_exists('has_block') && $post && has_block('seo-simple/faq', $post)) {
			$should_load_faq_scripts = true;
		}
		
		// Check if post has FAQs in meta
		if (!$should_load_faq_scripts && $post) {
			$faqs = get_post_meta($post->ID, '_seo_simple_faqs', true);
			if (!empty($faqs) && is_array($faqs)) {
				$should_load_faq_scripts = true;
			}
		}
		
		// Load FAQ scripts if needed
		if ($should_load_faq_scripts) {
			wp_enqueue_script( $this->plugin_name . '-faq', plugin_dir_url( __FILE__ ) . 'js/seo-simple-faq.js', array( 'jquery' ), $this->version, true );
			
			// Pass configuration data to FAQ script
			wp_localize_script( $this->plugin_name . '-faq', 'seo_simple_faq_vars', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_faq_nonce' ),
				'schema' => true, // Enable schema.org microdata by default
				'analytics' => false // Analytics tracking disabled by default
			) );
		}
		
		// Enqueue local business scripts if needed
		$should_load_local_business_scripts = false;
		
		// Check if local business shortcode is used
		if ($post && has_shortcode($post->post_content, 'local_business_info')) {
			$should_load_local_business_scripts = true;
		}
		
		// Check if this is a location post type
		if ($post && get_post_type($post) === 'seo_location') {
			$should_load_local_business_scripts = true;
		}
		
		// Check if local business schema is enabled
		if ($post && get_post_meta($post->ID, '_seo_enable_local_business_schema', true)) {
			$should_load_local_business_scripts = true;
		}
		
		if ($should_load_local_business_scripts) {
			wp_enqueue_script( 'seo-simple-local-business', plugin_dir_url( __FILE__ ) . 'js/seo-simple-local-business.js', array( 'jquery' ), $this->version, true );
		}
		
		// Allow other plugins/themes to force load scripts
		if (apply_filters('seo_simple_force_load_public_scripts', false)) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/seo-simple-public.js', array( 'jquery' ), $this->version, false );
		}

	}

}
