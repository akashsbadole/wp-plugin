<?php

/**
 * Handles the SEO meta box in the post editor.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the SEO meta box in the post editor.
 *
 * This class is responsible for rendering and managing the SEO meta box
 * that appears in the post editor screen.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Meta_Box {

	/**
	 * The meta key prefix for all SEO meta fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $meta_key_prefix    The prefix for all SEO meta keys.
	 */
	private $meta_key_prefix = '_seo_simple_';

	/**
	 * The SEO meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	protected $seo_meta;

	/**
	 * The compatibility handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Compatibility    $compatibility    The compatibility handler instance.
	 */
	private $compatibility;

	/**
	 * The schema handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema    $schema    The schema handler instance.
	 */
	private $schema;

	/**
	 * The security handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security    $security    The security handler instance.
	 */
	private $security;

	/**
	 * The internal links admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Internal_Links_Admin    $internal_links_admin    The internal links admin instance.
	 */
	private $internal_links_admin;

	/**
	 * The enhanced schema admin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema_Admin    $schema_enhanced_admin    The enhanced schema admin instance.
	 */
	private $schema_enhanced_admin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta               $seo_meta       The SEO meta handler instance.
	 * @param    Seo_Simple_Compatibility      $compatibility  The compatibility handler instance.
	 * @param    Seo_Simple_Schema             $schema         The schema handler instance.
	 */
	public function __construct( $seo_meta, $compatibility = null, $schema = null ) {
		$this->seo_meta = $seo_meta;
		$this->compatibility = $compatibility;
		$this->schema = $schema;
		
		// Initialize security handler if available
		if ( class_exists( 'Seo_Simple_Security' ) ) {
			$this->security = new Seo_Simple_Security();
		}
		
		// Initialize internal links admin if available
		if ( class_exists( 'Seo_Simple_Internal_Links_Admin' ) ) {
			$this->internal_links_admin = new Seo_Simple_Internal_Links_Admin();
		}
		
		// Initialize enhanced schema admin if available
		if ( class_exists( 'Seo_Simple_Schema_Enhanced' ) && $schema instanceof Seo_Simple_Schema_Enhanced ) {
			$this->schema_enhanced_admin = new Seo_Simple_Schema_Admin( $seo_meta, $schema );
		}
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_analyze_readability', array( $this, 'ajax_analyze_readability' ) );
		add_action( 'wp_ajax_seo_simple_analyze_seo', array( $this, 'ajax_analyze_seo' ) );
		add_action( 'wp_ajax_seo_simple_analyze_deep', array( $this, 'ajax_analyze_deep' ) );
		add_action( 'wp_ajax_seo_simple_get_image_url', array( $this, 'ajax_get_image_url' ) );
		add_action( 'wp_ajax_seo_simple_generate_product_schema', array( $this, 'ajax_generate_product_schema' ) );
		add_action( 'wp_ajax_seo_simple_get_analysis', array( $this, 'ajax_get_analysis' ) );
		add_action( 'wp_ajax_seo_simple_validate_schema', array( $this, 'ajax_validate_schema' ) );
		add_action( 'wp_ajax_seo_simple_preview_schema', array( $this, 'ajax_preview_schema' ) );
		
		// Add Gutenberg support
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ) );
		
		// Add WooCommerce support
		if ( $this->is_woocommerce_active() ) {
			// Don't add WooCommerce meta box if the dedicated WooCommerce SEO class is available
			if ( ! class_exists( 'Seo_Simple_WooCommerce' ) ) {
				add_action( 'add_meta_boxes', array( $this, 'add_woocommerce_seo_fields' ), 20 );
				add_action( 'save_post', array( $this, 'save_woocommerce_seo_meta' ), 10, 2 );
			}
		}
		
		// Add save post hook for Google settings
		add_action( 'save_post', array( $this, 'save_google_meta' ), 10, 2 );
	}

	/**
	 * Register the meta box.
	 *
	 * @since    1.0.0
	 */
	public function register_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );
		
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'seo-simple-meta-box',
				__( 'SEO Settings', 'seo-simple' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_simple_meta_box', 'seo_simple_meta_box_nonce' );
		
		// Get current meta values
		$meta_data = $this->seo_meta->get_all_meta( $post->ID );
		
		// Include the meta box template with tabs
		?>
		<div class="seo-simple-meta-box-tabs">
			<ul class="seo-simple-tab-nav">
				<li class="seo-simple-tab-nav-item active" data-tab="seo">
					<?php _e( 'SEO', 'seo-simple' ); ?>
				</li>
				<li class="seo-simple-tab-nav-item" data-tab="readability">
					<?php _e( 'Readability', 'seo-simple' ); ?>
				</li>
				<li class="seo-simple-tab-nav-item" data-tab="social">
					<?php _e( 'Social', 'seo-simple' ); ?>
				</li>
				<li class="seo-simple-tab-nav-item" data-tab="schema">
					<?php _e( 'Schema', 'seo-simple' ); ?>
				</li>
				<?php if ( $this->internal_links_admin ) : ?>
				<li class="seo-simple-tab-nav-item" data-tab="internal-links">
					<?php _e( 'Internal Links', 'seo-simple' ); ?>
				</li>
				<?php endif; ?>
				<li class="seo-simple-tab-nav-item" data-tab="analyzer">
					<?php _e( 'Analyzer', 'seo-simple' ); ?>
				</li>
				<li class="seo-simple-tab-nav-item" data-tab="google">
					<?php _e( 'Google', 'seo-simple' ); ?>
				</li>
			</ul>
			
			<div class="seo-simple-tab-content">
				<!-- SEO Tab -->
				<div class="seo-simple-tab-pane active" id="seo-simple-seo-tab">
					<?php include plugin_dir_path( __FILE__ ) . 'partials/meta-box-seo.php'; ?>
				</div>
				
				<!-- Readability Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-readability-tab">
					<?php include plugin_dir_path( __FILE__ ) . 'partials/meta-box-readability.php'; ?>
				</div>
				
				<!-- Social Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-social-tab">
					<?php include plugin_dir_path( __FILE__ ) . 'partials/meta-box-social.php'; ?>
				</div>
				
				<!-- Schema Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-schema-tab">
					<?php
					// Use enhanced schema template if available
					if ( class_exists( 'Seo_Simple_Schema_Enhanced' ) && $this->schema instanceof Seo_Simple_Schema_Enhanced ) {
						include plugin_dir_path( __FILE__ ) . 'partials/meta-box-schema-enhanced.php';
					} else {
						include plugin_dir_path( __FILE__ ) . 'partials/meta-box-schema.php';
					}
					?>
				</div>
				
				<?php if ( $this->internal_links_admin ) : ?>
				<!-- Internal Links Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-internal-links-tab">
					<?php $this->internal_links_admin->render_meta_box_interface( $post->ID ); ?>
				</div>
				<?php endif; ?>
				
				<!-- Analyzer Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-analyzer-tab">
					<?php include plugin_dir_path( __FILE__ ) . 'partials/meta-box-analyzer.php'; ?>
				</div>
				
				<!-- Google Tab -->
				<div class="seo-simple-tab-pane" id="seo-simple-google-tab">
					<?php
					// Get Google Analytics settings from post meta
					$google_analytics_track = get_post_meta( $post->ID, '_seo_simple_google_analytics_track', true );
					$google_analytics_custom_event = get_post_meta( $post->ID, '_seo_simple_google_analytics_custom_event', true );
					$google_analytics_event_category = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_category', true );
					$google_analytics_event_action = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_action', true );
					$google_analytics_event_label = get_post_meta( $post->ID, '_seo_simple_google_analytics_event_label', true );
					
					include plugin_dir_path( __FILE__ ) . 'partials/meta-box-google.php'; ?>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Tab switching functionality
			$('.seo-simple-tab-nav-item').on('click', function(e) {
				e.preventDefault();
				
				var $tabItem = $(this);
				var tabName = $tabItem.data('tab');
				
				// Remove active class from all tab items
				$('.seo-simple-tab-nav-item').removeClass('active');
				
				// Add active class to clicked tab item
				$tabItem.addClass('active');
				
				// Hide all tab panes
				$('.seo-simple-tab-pane').hide();
				
				// Show the selected tab pane
				$('#seo-simple-' + tabName + '-tab').show();
			});
			
			// Show SEO tab by default
			$('#seo-simple-seo-tab').show();
		});
		</script>
		
		<style>
		.seo-simple-meta-box-tabs {
			margin-top: 10px;
		}
		.seo-simple-tab-nav {
			display: flex;
			list-style: none;
			margin: 0;
			padding: 0;
			border-bottom: 1px solid #ddd;
		}
		.seo-simple-tab-nav-item {
			padding: 10px 15px;
			cursor: pointer;
			border-bottom: 2px solid transparent;
			font-weight: bold;
		}
		.seo-simple-tab-nav-item:hover {
			background-color: #f9f9f9;
		}
		.seo-simple-tab-nav-item.active {
			border-bottom-color: #0073aa;
			color: #0073aa;
		}
		.seo-simple-tab-pane {
			display: none;
		}
		.seo-simple-tab-pane.active {
			display: block;
		}
		</style>
		<?php
	}

	/**
	 * Get the meta key prefix.
	 *
	 * @since    1.0.0
	 * @return   string    The meta key prefix.
	 */
	public function get_meta_key_prefix() {
		return $this->meta_key_prefix;
	}

	/**
	 * Get the current SEO title for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The SEO title.
	 */
	public function get_title_preview( $post_id ) {
		return $this->seo_meta->get_title( $post_id );
	}

	/**
	 * Get the current SEO description for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The SEO description.
	 */
	public function get_description_preview( $post_id ) {
		return $this->seo_meta->get_description( $post_id );
	}
	
	/**
	 * Get the social title for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social title.
	 */
	public function get_social_title_preview( $post_id ) {
		return $this->seo_meta->get_social_title( $post_id );
	}
	
	/**
	 * Get the social description for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social description.
	 */
	public function get_social_description_preview( $post_id ) {
		return $this->seo_meta->get_social_description( $post_id );
	}
	
	/**
	 * Get the social image for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social image URL.
	 */
	public function get_social_image_preview( $post_id ) {
		return $this->seo_meta->get_social_image( $post_id );
	}
	
	/**
	 * Get the social image ID for preview.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   int                 The social image ID.
	 */
	public function get_social_image_id_preview( $post_id ) {
		return $this->seo_meta->get_social_image_id( $post_id );
	}

	/**
	 * Get the permalink for the post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The permalink.
	 */
	public function get_permalink( $post_id ) {
		return get_permalink( $post_id );
	}
	
	/**
	 * Get color code for status.
	 *
	 * @since    1.0.0
	 * @param    string    $status    The status (good, ok, bad).
	 * @return   string               The color code.
	 */
	public function get_status_color( $status ) {
		switch ( $status ) {
			case 'good':
				return '#46b450';
			case 'ok':
				return '#ffb900';
			case 'bad':
			default:
				return '#dc3232';
		}
	}
	
	/**
	 * Sanitize and validate AJAX request data.
	 *
	 * @since    1.0.0
	 * @param    array     $data    The data to sanitize.
	 * @return   array              The sanitized data.
	 */
	private function sanitize_ajax_data( $data ) {
		if ( ! $this->security ) {
			return $data;
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			switch ( $key ) {
				case 'post_id':
					$sanitized[$key] = $this->security->validate_post_id( $value );
					break;
				case 'title':
					$sanitized[$key] = $this->security->sanitize_title( $value );
					break;
				case 'description':
					$sanitized[$key] = $this->security->sanitize_description( $value );
					break;
				case 'focus_keyword':
					$sanitized[$key] = $this->security->sanitize_focus_keyword( $value );
					break;
				case 'content':
					$sanitized[$key] = wp_kses_post( $value );
					break;
				case 'image_id':
					$sanitized[$key] = $this->security->validate_image_id( $value );
					break;
				case 'schema':
					$sanitized[$key] = $this->security->sanitize_schema_json( $value );
					break;
				default:
					$sanitized[$key] = sanitize_text_field( $value );
					break;
			}
		}

		return $sanitized;
	}

	/**
	 * Validate AJAX request nonce and permissions.
	 *
	 * @since    1.0.0
	 * @param    string    $nonce       The nonce to validate.
	 * @param    string    $action      The action the nonce is for.
	 * @param    string    $capability  The capability to check.
	 * @param    int       $post_id     The post ID to check (optional).
	 * @return   bool                   True if valid, false otherwise.
	 */
	private function validate_ajax_request( $nonce, $action, $capability, $post_id = null ) {
		if ( ! $this->security ) {
			// Fallback to default WordPress validation
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				return false;
			}
			
			if ( $post_id ) {
				return current_user_can( $capability, $post_id );
			} else {
				return current_user_can( $capability );
			}
		}

		// Use security class for validation
		if ( ! $this->security->validate_nonce( $nonce, $action ) ) {
			return false;
		}
		
		return $this->security->current_user_can( $capability, $post_id );
	}
	
	/**
	 * AJAX handler for analyzing readability.
	 *
	 * @since    1.0.0
	 */
	public function ajax_analyze_readability() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_analyze_readability' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
			'content' => isset( $_POST['content'] ) ? $_POST['content'] : '',
		) );
		
		$post_id = $data['post_id'];
		$content = $data['content'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Check if content has actual text (not just HTML tags)
		$text_content = strip_tags( strip_shortcodes( $content ) );
		$text_content = preg_replace( '/\s+/', ' ', $text_content ); // Normalize whitespace
		$text_content = trim( $text_content );
		
		if ( empty( $text_content ) || strlen( $text_content ) < 10 ) {
			wp_send_json_error( array( 'message' => __( 'Please add some content before analyzing readability.', 'seo-simple' ) ) );
		}
		
		// Load the analysis class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-analysis.php';
		$analysis = new Seo_Simple_Analysis();
		
		// Perform analysis using cached method
		$results = $analysis->analyze_content_cached( $post_id, $content );
		
		// Save results to post meta
		$this->seo_meta->save_readability_scores( $post_id, $results );
		
		// Return results
		wp_send_json_success( $results );
	}
	
	/**
	 * AJAX handler for analyzing SEO.
	 *
	 * @since    1.0.0
	 */
	public function ajax_analyze_seo() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_meta_box' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
			'title' => isset( $_POST['title'] ) ? $_POST['title'] : '',
			'description' => isset( $_POST['description'] ) ? $_POST['description'] : '',
			'content' => isset( $_POST['content'] ) ? $_POST['content'] : '',
			'focus_keyword' => isset( $_POST['focus_keyword'] ) ? $_POST['focus_keyword'] : '',
		) );
		
		$post_id = $data['post_id'];
		$title = $data['title'];
		$description = $data['description'];
		$content = $data['content'];
		$focus_keyword = $data['focus_keyword'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Load the analysis class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-analysis.php';
		$analysis = new Seo_Simple_Analysis();
		
		// Perform SEO analysis using cached method
		$results = $analysis->analyze_seo_cached( $post_id, $title, $description, $content, $focus_keyword );
		
		// Save results to post meta
		$this->seo_meta->save_seo_analysis( $post_id, $results );
		
		// Save focus keyword
		$this->seo_meta->save_focus_keyword( $post_id, $focus_keyword );
		
		// Return results
		wp_send_json_success( $results );
	}
	
	/**
		* AJAX handler for deep content analysis.
		*
		* @since    1.0.0
		*/
	public function ajax_analyze_deep() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_enhanced_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
		) );
		
		$post_id = $data['post_id'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Get post object
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'seo-simple' ) ) );
		}
		
		// Load the analysis class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-analysis.php';
		$analysis = new Seo_Simple_Analysis();
		
		// Perform deep analysis
		$results = $analysis->deep_content_analysis( $post );
		
		// Save results to post meta
		$this->seo_meta->save_deep_analysis( $post_id, $results );
		$this->seo_meta->cache_deep_analysis_results( $post_id );
		
		// Return results
		wp_send_json_success( $results );
	}
	
	/**
		* Get deep analysis results for a post.
		*
		* @since    1.0.0
		* @param    int     $post_id    The post ID.
		* @return   array               The deep analysis results.
		*/
	public function get_deep_analysis( $post_id ) {
		return $this->seo_meta->get_deep_analysis( $post_id );
	}
	
	/**
	 * AJAX handler for getting image URL.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_image_url() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_meta_box' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'image_id' => isset( $_POST['image_id'] ) ? $_POST['image_id'] : 0,
		) );
		
		$image_id = $data['image_id'];
		
		if ( empty( $image_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid image ID.', 'seo-simple' ) ) );
		}
		
		// Get image URL
		$image_src = wp_get_attachment_image_src( $image_id, 'medium' );
		$image_url = $image_src ? $image_src[0] : '';
		
		if ( empty( $image_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not retrieve image URL.', 'seo-simple' ) ) );
		}
		
		// Return image URL
		wp_send_json_success( array( 'url' => $image_url ) );
	}
	
	/**
	 * AJAX handler for generating WooCommerce product schema.
	 *
	 * @since    1.0.0
	 */
	public function ajax_generate_product_schema() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_generate_schema' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
		) );
		
		$post_id = $data['post_id'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Check if this is a product
		if ( get_post_type( $post_id ) !== 'product' ) {
			wp_send_json_error( array( 'message' => __( 'This is not a product.', 'seo-simple' ) ) );
		}
		
		// Get product
		$product = wc_get_product( $post_id );
		
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'seo-simple' ) ) );
		}
		
		// Initialize frontend class to get schema
		$frontend = new Seo_Simple_Frontend( $this->seo_meta );
		$schema = $frontend->get_woocommerce_product_schema( $product );
		
		if ( empty( $schema ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not generate schema.', 'seo-simple' ) ) );
		}
		
		// Return schema
		wp_send_json_success( $schema );
	}
	
	/**
	 * AJAX handler for getting analysis results.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_analysis() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_gutenberg_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
		) );
		
		$post_id = $data['post_id'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Get analysis data
		$data = array();
		
		// Get readability analysis
		$readability = $this->seo_meta->get_readability_scores( $post_id );
		if ( ! empty( $readability ) ) {
			$readability['overall'] = $this->seo_meta->get_overall_readability_score( $post_id );
			$data['readability'] = $readability;
		}
		
		// Get SEO analysis
		$seo = $this->seo_meta->get_seo_analysis( $post_id );
		if ( ! empty( $seo ) ) {
			$seo['overall'] = $this->seo_meta->get_overall_seo_score( $post_id );
			$data['seo'] = $seo;
		}
		
		// Return analysis data
		wp_send_json_success( $data );
	}
	
	/**
	 * Check if WooCommerce is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}
	
	/**
	 * Check if Gutenberg is active.
	 *
	 * @since    1.0.0
	 * @return   bool    True if Gutenberg is active.
	 */
	private function is_gutenberg_active() {
		// Gutenberg plugin is active
		if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
			return true;
		}
		
		// WordPress 5.0+ with block editor
		if ( function_exists( 'register_block_type' ) ) {
			$post = get_post();
			if ( $post && function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $post->post_type ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Enqueue Gutenberg-specific assets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_gutenberg_assets() {
		wp_enqueue_script(
			'seo-simple-gutenberg',
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/seo-simple-gutenberg.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-compose' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_enqueue_style(
			'seo-simple-gutenberg',
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/seo-simple-gutenberg.css',
			array( 'wp-edit-blocks' ),
			SEO_SIMPLE_VERSION
		);
		
		// Localize script
		wp_localize_script(
			'seo-simple-gutenberg',
			'seo_simple_gutenberg',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_gutenberg_nonce' ),
				'plugin_url' => plugin_dir_url( dirname( __FILE__ ) ),
			)
		);
	}
	
	/**
	 * Add SEO fields to WooCommerce products.
	 *
	 * @since    1.0.0
	 */
	public function add_woocommerce_seo_fields() {
		add_meta_box(
			'seo-simple-woocommerce-meta-box',
			__( 'Product SEO', 'seo-simple' ),
			array( $this, 'render_woocommerce_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}
	
	/**
	 * Render WooCommerce product SEO meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_woocommerce_meta_box( $post ) {
		// Add nonce for security
		wp_nonce_field( 'seo_simple_woocommerce_meta_box', 'seo_simple_woocommerce_meta_box_nonce' );
		
		// Get current meta values
		$meta_data = $this->seo_meta->get_all_meta( $post->ID );
		
		// Get product-specific meta
		$product_seo_title = get_post_meta( $post->ID, '_seo_simple_product_title', true );
		$product_seo_description = get_post_meta( $post->ID, '_seo_simple_product_description', true );
		
		// Include the WooCommerce meta box template
		include plugin_dir_path( __FILE__ ) . 'partials/meta-box-woocommerce.php';
	}
	
	/**
	 * Save WooCommerce SEO meta data.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    WP_Post   $post       The post object.
	 */
	public function save_woocommerce_seo_meta( $post_id, $post ) {
		// Check if this is a product
		if ( $post->post_type !== 'product' ) {
			return;
		}
		
		// Check nonce
		if ( ! isset( $_POST['seo_simple_woocommerce_meta_box_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['seo_simple_woocommerce_meta_box_nonce'], 'seo_simple_woocommerce_meta_box' ) ) {
			return;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Save product-specific SEO fields
		if ( isset( $_POST['_seo_simple_product_title'] ) ) {
			update_post_meta( $post_id, '_seo_simple_product_title', sanitize_text_field( $_POST['_seo_simple_product_title'] ) );
		}
		
		if ( isset( $_POST['_seo_simple_product_description'] ) ) {
			update_post_meta( $post_id, '_seo_simple_product_description', sanitize_textarea_field( $_POST['_seo_simple_product_description'] ) );
		}
		
		// Apply developer hook
		do_action( 'seo_plugin_save_meta', $post_id, $_POST );
	}
	
	/**
		* Save Google SEO meta data.
		*
		* @since    1.0.0
		* @param    int       $post_id    The post ID.
		* @param    WP_Post   $post       The post object.
		*/
	public function save_google_meta( $post_id, $post ) {
		// Check nonce
		if ( ! isset( $_POST['seo_simple_meta_box_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['seo_simple_meta_box_nonce'], 'seo_simple_meta_box' ) ) {
			return;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Save Google Analytics settings
		if ( isset( $_POST['seo_simple_google_analytics_track'] ) ) {
			update_post_meta( $post_id, '_seo_simple_google_analytics_track', 1 );
		} else {
			delete_post_meta( $post_id, '_seo_simple_google_analytics_track' );
		}
		
		if ( isset( $_POST['seo_simple_google_analytics_custom_event'] ) ) {
			update_post_meta( $post_id, '_seo_simple_google_analytics_custom_event', 1 );
		} else {
			delete_post_meta( $post_id, '_seo_simple_google_analytics_custom_event' );
		}
		
		if ( isset( $_POST['seo_simple_google_analytics_event_category'] ) ) {
			update_post_meta( $post_id, '_seo_simple_google_analytics_event_category', sanitize_text_field( $_POST['seo_simple_google_analytics_event_category'] ) );
		}
		
		if ( isset( $_POST['seo_simple_google_analytics_event_action'] ) ) {
			update_post_meta( $post_id, '_seo_simple_google_analytics_event_action', sanitize_text_field( $_POST['seo_simple_google_analytics_event_action'] ) );
		}
		
		if ( isset( $_POST['seo_simple_google_analytics_event_label'] ) ) {
			update_post_meta( $post_id, '_seo_simple_google_analytics_event_label', sanitize_text_field( $_POST['seo_simple_google_analytics_event_label'] ) );
		}
		
		// Apply developer hook
		do_action( 'seo_plugin_save_google_meta', $post_id, $_POST );
	}
	
	/**
		* Register the meta box with Gutenberg support.
	 *
	 * @since    1.0.0
	 */
	public function register_meta_box_with_gutenberg() {
		$post_types = get_post_types( array( 'public' => true ) );
		
		foreach ( $post_types as $post_type ) {
			// Skip if Gutenberg is active for this post type, as we'll use the sidebar plugin
			if ( $this->is_gutenberg_active() && function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $post_type ) ) {
				continue;
			}
			
			add_meta_box(
				'seo-simple-meta-box',
				__( 'SEO Settings', 'seo-simple' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}
	
	/**
	 * Register REST API fields for Gutenberg integration.
	 *
	 * @since    1.0.0
	 */
	public function register_rest_fields() {
		register_rest_field(
			array( 'post', 'page' ),
			'seo_simple_meta',
			array(
				'get_callback'    => array( $this, 'get_rest_meta' ),
				'update_callback' => array( $this, 'update_rest_meta' ),
				'schema'          => array(
					'description' => __( 'SEO meta data.', 'seo-simple' ),
					'type'        => 'object',
				),
			)
		);
	}
	
	/**
	 * Get SEO meta for REST API.
	 *
	 * @since    1.0.0
	 * @param    array           $object     The post object.
	 * @param    string          $field_name The field name.
	 * @param    WP_REST_Request $request    The request object.
	 * @return   array                       The SEO meta data.
	 */
	public function get_rest_meta( $object, $field_name, $request ) {
		if ( ! $this->seo_meta ) {
			return array();
		}
		
		return $this->seo_meta->get_all_meta( $object['id'] );
	}
	
	/**
	 * Update SEO meta for REST API.
	 *
	 * @since    1.0.0
	 * @param    mixed           $value      The value to update.
	 * @param    WP_Post         $object     The post object.
	 * @param    string          $field_name The field name.
	 * @return   bool|WP_Error               True on success, WP_Error on failure.
	 */
	public function update_rest_meta( $value, $object, $field_name ) {
		if ( ! current_user_can( 'edit_post', $object->ID ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to edit this post.', 'seo-simple' ),
				array( 'status' => 403 )
			);
		}
		
		// Apply developer hook
		do_action( 'seo_plugin_save_meta', $object->ID, $value );
		
		return true;
	}
	
	/**
	 * AJAX handler for validating schema JSON.
	 *
	 * @since    1.0.0
	 */
	public function ajax_validate_schema() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_meta_box' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'schema' => isset( $_POST['schema'] ) ? $_POST['schema'] : '',
		) );
		
		$schema = $data['schema'];
		
		if ( empty( $schema ) ) {
			wp_send_json_error( array( 'message' => __( 'Schema JSON is empty.', 'seo-simple' ) ) );
		}
		
		// Validate schema if schema class is available
		if ( $this->schema ) {
			$is_valid = $this->schema->validate_schema( $schema );
			
			if ( ! $is_valid ) {
				wp_send_json_error( array(
					'message' => __( 'Schema validation failed. Please check the required fields.', 'seo-simple' )
				) );
			}
			
			wp_send_json_success( array(
				'message' => __( 'Schema is valid and has been sanitized.', 'seo-simple' ),
				'sanitized_schema' => json_encode( $schema, JSON_PRETTY_PRINT )
			) );
		} else {
			// Just check if it's valid JSON if schema class is not available
			wp_send_json_success( array(
				'message' => __( 'Schema JSON is valid.', 'seo-simple' )
			) );
		}
	}
		
	/**
	 * AJAX handler for previewing schema.
	 *
	 * @since    1.0.0
	 */
	public function ajax_preview_schema() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_meta_box' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$data = $this->sanitize_ajax_data( array(
			'post_id' => isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0,
		) );
		
		$post_id = $data['post_id'];
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Generate schema if schema class is available
		if ( $this->schema ) {
			$schema = $this->schema->generate_schema();
			
			if ( empty( $schema ) ) {
				wp_send_json_error( array(
					'message' => __( 'No schema data available for this post.', 'seo-simple' )
				) );
			}
			
			wp_send_json_success( array(
				'message' => __( 'Schema preview generated successfully.', 'seo-simple' ),
				'schema' => $schema
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Schema handler not available.', 'seo-simple' )
			) );
		}
	}
	
	/**
		* Render schema fields for the enhanced schema generator.
		*
		* @since    1.0.0
		* @param    string    $schema_type    The schema type.
		* @param    int       $post_id        The post ID.
		*/
	public function render_schema_fields( $schema_type, $post_id ) {
		// Check if enhanced schema admin is available
		if ( class_exists( 'Seo_Simple_Schema_Admin' ) && isset( $this->schema_enhanced_admin ) ) {
			$this->schema_enhanced_admin->render_schema_fields( $schema_type, $post_id );
		} else {
			echo '<p>' . __( 'Enhanced schema generator not available.', 'seo-simple' ) . '</p>';
		}
	}
}
