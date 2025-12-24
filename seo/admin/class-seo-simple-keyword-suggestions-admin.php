<?php

/**
 * Admin class for Google keyword suggestions.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Admin class for Google keyword suggestions.
 *
 * This class handles all admin-related operations for keyword suggestions including:
 * - Displaying keyword suggestions in the meta box
 * - Handling AJAX requests for keyword suggestions
 * - Enqueuing admin scripts and styles
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Keyword_Suggestions_Admin {

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
	 * @var      string    $version    The current version of the plugin.
	 */
	private $version;

	/**
	 * The keyword suggestions instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Keyword_Suggestions    $keyword_suggestions    The keyword suggestions instance.
	 */
	private $keyword_suggestions;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->keyword_suggestions = Seo_Simple_Keyword_Suggestions::get_instance();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-keyword-suggestions',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-keyword-suggestions.css',
			array(),
			$this->version,
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
			$this->plugin_name . '-keyword-suggestions',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-keyword-suggestions.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name . '-keyword-suggestions',
			'seo_simple_keyword_suggestions',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_keyword_suggestions_nonce' ),
				'getting_suggestions' => __( 'Getting suggestions...', 'seo-simple' ),
				'no_suggestions' => __( 'No suggestions found.', 'seo-simple' ),
				'error' => __( 'Error getting suggestions.', 'seo-simple' ),
			)
		);
	}

	/**
	 * Handle AJAX requests for keyword suggestions.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// Get keyword suggestions
		add_action( 'wp_ajax_seo_simple_get_keyword_suggestions', array( $this, 'ajax_get_keyword_suggestions' ) );
		
		// Get trending keywords
		add_action( 'wp_ajax_seo_simple_get_trending_keywords', array( $this, 'ajax_get_trending_keywords' ) );
		
		// Clear keyword cache
		add_action( 'wp_ajax_seo_simple_clear_keyword_cache', array( $this, 'ajax_clear_keyword_cache' ) );
	}

	/**
	 * AJAX handler for getting keyword suggestions.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_keyword_suggestions() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_keyword_suggestions_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 10;

		if ( empty( $keyword ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a keyword.', 'seo-simple' ) ) );
		}

		// Get suggestions
		if ( $post_id > 0 ) {
			$suggestions = $this->keyword_suggestions->get_post_keyword_suggestions( $post_id, $limit );
		} else {
			$suggestions = $this->keyword_suggestions->get_related_keywords( $keyword, $limit );
		}

		wp_send_json_success( array( 'suggestions' => $suggestions ) );
	}

	/**
	 * AJAX handler for getting trending keywords.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_trending_keywords() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_keyword_suggestions_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}

		$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 10;

		// Get trending keywords
		$trending = $this->keyword_suggestions->get_trending_keywords( $limit );

		wp_send_json_success( array( 'trending' => $trending ) );
	}

	/**
	 * AJAX handler for clearing keyword cache.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_keyword_cache() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_keyword_suggestions_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

		// Clear cache
		$this->keyword_suggestions->clear_cache( $keyword );

		wp_send_json_success( array( 'message' => __( 'Cache cleared successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Render keyword suggestions section in the meta box.
	 *
	 * @since    1.0.0
	 * @param    int      $post_id    The post ID.
	 */
	public function render_keyword_suggestions_section( $post_id ) {
		// Get the focus keyword
		$focus_keyword = get_post_meta( $post_id, '_seo_simple_focus_keyword', true );
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/keyword-suggestions-section.php';
	}

	/**
	 * Render keyword suggestions dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		$trending_keywords = $this->keyword_suggestions->get_trending_keywords( 5 );
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/keyword-suggestions-dashboard-widget.php';
	}

	/**
	 * Register dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo_simple_keyword_suggestions',
			__( 'SEO Keyword Suggestions', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Add keyword suggestions tab to the meta box.
	 *
	 * @since    1.0.0
	 * @param    array    $tabs    The existing tabs.
	 * @return   array             The modified tabs.
	 */
	public function add_meta_box_tab( $tabs ) {
		$tabs['keyword_suggestions'] = array(
			'label' => __( 'Keywords', 'seo-simple' ),
			'icon' => 'dashicons-search',
			'content' => array( $this, 'render_keyword_suggestions_section' ),
		);
		
		return $tabs;
	}

	/**
	 * Add keyword suggestions settings to the settings page.
	 *
	 * @since    1.0.0
	 * @param    array    $settings    The existing settings.
	 * @return   array                 The modified settings.
	 */
	public function add_settings( $settings ) {
		$settings['keyword_suggestions'] = array(
			'title' => __( 'Keyword Suggestions', 'seo-simple' ),
			'fields' => array(
				'enable_keyword_suggestions' => array(
					'label' => __( 'Enable Keyword Suggestions', 'seo-simple' ),
					'type' => 'checkbox',
					'description' => __( 'Enable Google keyword suggestions in the meta box.', 'seo-simple' ),
					'default' => 1,
				),
				'keyword_suggestions_language' => array(
					'label' => __( 'Language', 'seo-simple' ),
					'type' => 'select',
					'options' => $this->get_language_options(),
					'description' => __( 'Select the language for keyword suggestions.', 'seo-simple' ),
					'default' => 'en',
				),
				'keyword_suggestions_country' => array(
					'label' => __( 'Country', 'seo-simple' ),
					'type' => 'select',
					'options' => $this->get_country_options(),
					'description' => __( 'Select the country for keyword suggestions.', 'seo-simple' ),
					'default' => 'us',
				),
				'keyword_suggestions_limit' => array(
					'label' => __( 'Suggestions Limit', 'seo-simple' ),
					'type' => 'number',
					'description' => __( 'Maximum number of suggestions to display.', 'seo-simple' ),
					'default' => 10,
					'min' => 1,
					'max' => 50,
				),
			),
		);
		
		return $settings;
	}

	/**
	 * Get language options for the settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array    The language options.
	 */
	private function get_language_options() {
		return array(
			'en' => __( 'English', 'seo-simple' ),
			'es' => __( 'Spanish', 'seo-simple' ),
			'fr' => __( 'French', 'seo-simple' ),
			'de' => __( 'German', 'seo-simple' ),
			'it' => __( 'Italian', 'seo-simple' ),
			'pt' => __( 'Portuguese', 'seo-simple' ),
			'ru' => __( 'Russian', 'seo-simple' ),
			'ja' => __( 'Japanese', 'seo-simple' ),
			'zh' => __( 'Chinese', 'seo-simple' ),
			'ko' => __( 'Korean', 'seo-simple' ),
			'ar' => __( 'Arabic', 'seo-simple' ),
			'hi' => __( 'Hindi', 'seo-simple' ),
		);
	}

	/**
	 * Get country options for the settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array    The country options.
	 */
	private function get_country_options() {
		return array(
			'us' => __( 'United States', 'seo-simple' ),
			'uk' => __( 'United Kingdom', 'seo-simple' ),
			'ca' => __( 'Canada', 'seo-simple' ),
			'au' => __( 'Australia', 'seo-simple' ),
			'de' => __( 'Germany', 'seo-simple' ),
			'fr' => __( 'France', 'seo-simple' ),
			'es' => __( 'Spain', 'seo-simple' ),
			'it' => __( 'Italy', 'seo-simple' ),
			'nl' => __( 'Netherlands', 'seo-simple' ),
			'br' => __( 'Brazil', 'seo-simple' ),
			'mx' => __( 'Mexico', 'seo-simple' ),
			'in' => __( 'India', 'seo-simple' ),
			'jp' => __( 'Japan', 'seo-simple' ),
			'cn' => __( 'China', 'seo-simple' ),
			'kr' => __( 'South Korea', 'seo-simple' ),
			'ru' => __( 'Russia', 'seo-simple' ),
		);
	}
}