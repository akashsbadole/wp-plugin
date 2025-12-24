<?php

/**
 * Admin class for SEO analyzer.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Admin class for SEO analyzer.
 *
 * This class handles all admin-related operations for the SEO analyzer including:
 * - Displaying analysis results in the meta box
 * - Handling AJAX requests for content analysis
 * - Enqueuing admin scripts and styles
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Analyzer_Admin {

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
	 * The analyzer instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Analyzer    $analyzer    The analyzer instance.
	 */
	private $analyzer;

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
		$this->analyzer = Seo_Simple_Analyzer::get_instance();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name . '-analyzer',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-analyzer.css',
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
			$this->plugin_name . '-analyzer',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-analyzer.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name . '-analyzer',
			'seo_simple_analyzer',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_analyzer_nonce' ),
				'analyzing' => __( 'Analyzing content...', 'seo-simple' ),
				'error' => __( 'Error analyzing content.', 'seo-simple' ),
			)
		);
	}

	/**
	 * Handle AJAX requests for the analyzer.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// Analyze content
		add_action( 'wp_ajax_seo_simple_analyze_content', array( $this, 'ajax_analyze_content' ) );
		
		// Clear analysis cache
		add_action( 'wp_ajax_seo_simple_clear_analysis_cache', array( $this, 'ajax_clear_analysis_cache' ) );
	}

	/**
	 * AJAX handler for analyzing content.
	 *
	 * @since    1.0.0
	 */
	public function ajax_analyze_content() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_analyzer_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}

		// Perform analysis
		$analysis = $this->analyzer->analyze_post( $post_id );

		wp_send_json_success( array( 'analysis' => $analysis ) );
	}

	/**
	 * AJAX handler for clearing analysis cache.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_analysis_cache() {
		// Verify nonce
		if ( ! check_ajax_referer( 'seo_simple_analyzer_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions.', 'seo-simple' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		// Clear cache
		$this->analyzer->clear_cache( $post_id );

		wp_send_json_success( array( 'message' => __( 'Analysis cache cleared successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Render analyzer section in the meta box.
	 *
	 * @since    1.0.0
	 * @param    int      $post_id    The post ID.
	 */
	public function render_analyzer_section( $post_id ) {
		$analysis = $this->analyzer->analyze_post( $post_id );
		$focus_keyword = get_post_meta( $post_id, '_seo_simple_focus_keyword', true );
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/analyzer-section.php';
	}

	/**
	 * Render analyzer dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		// Get posts that need SEO improvement
		$posts_needing_improvement = $this->get_posts_needing_improvement();
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/analyzer-dashboard-widget.php';
	}

	/**
	 * Register dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo_simple_analyzer',
			__( 'SEO Analyzer', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Add analyzer tab to the meta box.
	 *
	 * @since    1.0.0
	 * @param    array    $tabs    The existing tabs.
	 * @return   array             The modified tabs.
	 */
	public function add_meta_box_tab( $tabs ) {
		$tabs['analyzer'] = array(
			'label' => __( 'Analysis', 'seo-simple' ),
			'icon' => 'dashicons-chart-bar',
			'content' => array( $this, 'render_analyzer_section' ),
		);
		
		return $tabs;
	}

	/**
	 * Get posts that need SEO improvement.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array    The posts needing improvement.
	 */
	private function get_posts_needing_improvement() {
		$posts = get_posts( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'numberposts' => 20,
			'orderby' => 'date',
			'order' => 'DESC',
		) );

		$posts_needing_improvement = array();

		foreach ( $posts as $post ) {
			$analysis = $this->analyzer->analyze_post( $post->ID );
			
			if ( $analysis['overall_score'] < 70 ) {
				$posts_needing_improvement[] = array(
					'post' => $post,
					'score' => $analysis['overall_score'],
					'seo_score' => $analysis['seo_score'],
					'readability_score' => $analysis['readability_score'],
					'recommendations_count' => count( $analysis['recommendations'] ),
				);
			}
		}

		// Sort by score (lowest first)
		usort( $posts_needing_improvement, function( $a, $b ) {
			return $a['score'] - $b['score'];
		} );

		return array_slice( $posts_needing_improvement, 0, 5 );
	}

	/**
	 * Add analyzer settings to the settings page.
	 *
	 * @since    1.0.0
	 * @param    array    $settings    The existing settings.
	 * @return   array                 The modified settings.
	 */
	public function add_settings( $settings ) {
		$settings['analyzer'] = array(
			'title' => __( 'SEO Analyzer', 'seo-simple' ),
			'fields' => array(
				'enable_analyzer' => array(
					'label' => __( 'Enable SEO Analyzer', 'seo-simple' ),
					'type' => 'checkbox',
					'description' => __( 'Enable SEO content analysis in the meta box.', 'seo-simple' ),
					'default' => 1,
				),
				'analyzer_cache_duration' => array(
					'label' => __( 'Cache Duration', 'seo-simple' ),
					'type' => 'number',
					'description' => __( 'How long to cache analysis results (in hours).', 'seo-simple' ),
					'default' => 1,
					'min' => 0,
					'max' => 24,
				),
				'analyzer_auto_analyze' => array(
					'label' => __( 'Auto Analyze', 'seo-simple' ),
					'type' => 'checkbox',
					'description' => __( 'Automatically analyze content when focus keyword is set.', 'seo-simple' ),
					'default' => 1,
				),
			),
		);
		
		return $settings;
	}

	/**
	 * Get score color class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int      $score    The score (0-100).
	 * @return   string             The color class.
	 */
	public function get_score_class( $score ) {
		if ( $score >= 80 ) {
			return 'score-good';
		} elseif ( $score >= 60 ) {
			return 'score-okay';
		} elseif ( $score >= 40 ) {
			return 'score-warning';
		} else {
			return 'score-bad';
		}
	}

	/**
	 * Get status icon HTML.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $status    The status (good, warning, error).
	 * @return   string              The status icon HTML.
	 */
	public function get_status_icon( $status ) {
		switch ( $status ) {
			case 'good':
				return '<span class="dashicons dashicons-yes-alt status-good"></span>';
			case 'warning':
				return '<span class="dashicons dashicons-warning status-warning"></span>';
			case 'error':
				return '<span class="dashicons dashicons-no-alt status-error"></span>';
			default:
				return '';
		}
	}
}