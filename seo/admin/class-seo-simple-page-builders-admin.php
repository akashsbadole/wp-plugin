<?php

/**
 * Page Builder Integration Admin Class
 *
 * Handles admin interface for page builder integrations.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Page Builder Integration Admin Class
 *
 * This class handles the admin interface for page builder integrations,
 * including settings pages and compatibility features.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Page_Builders_Admin {

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
	 * The page builder instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Page_Builders    $page_builders    The page builder instance.
	 */
	private $page_builders;

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
		
		// Initialize page builders
		$this->page_builders = Seo_Simple_Page_Builders::get_instance();
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_page_builder_analyze', array( $this, 'ajax_analyze_page_builder' ) );
		add_action( 'wp_ajax_seo_simple_page_builder_save_seo', array( $this, 'ajax_save_page_builder_seo' ) );
		add_action( 'wp_ajax_seo_simple_page_builder_get_meta', array( $this, 'ajax_get_page_builder_meta' ) );
		
		// Add settings
		add_action( 'admin_init', array( $this, 'register_page_builder_settings' ) );
		
		// Add compatibility notices
		add_action( 'admin_notices', array( $this, 'display_page_builder_notices' ) );
	}

	/**
	 * AJAX handler for analyzing page builder content.
	 *
	 * @since    1.0.0
	 */
	public function ajax_analyze_page_builder() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_page_builder_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$page_builder = isset( $_POST['page_builder'] ) ? sanitize_text_field( $_POST['page_builder'] ) : '';
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Get content based on page builder
		$content = '';
		switch ( $page_builder ) {
			case 'elementor':
				$content = $this->page_builders->get_elementor_content( $post_id );
				break;
			case 'divi':
				$content = $this->page_builders->get_divi_content( $post_id );
				break;
			case 'beaver':
				$content = $this->page_builders->get_beaver_builder_content( $post_id );
				break;
			case 'vc':
				$content = $this->page_builders->get_visual_composer_content( $post_id );
				break;
			case 'fusion':
				$content = $this->page_builders->get_fusion_builder_content( $post_id );
				break;
		}
		
		if ( empty( $content ) ) {
			wp_send_json_error( array( 'message' => __( 'No content found for analysis.', 'seo-simple' ) ) );
		}
		
		// Perform SEO analysis
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analysis = $analyzer->analyze_content( $post_id, $content );
			
			wp_send_json_success( array(
				'analysis' => $analysis,
				'content_length' => strlen( $content ),
				'word_count' => str_word_count( strip_tags( $content ) )
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'SEO Analyzer not available.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for saving page builder SEO data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_page_builder_seo() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_page_builder_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$seo_title = isset( $_POST['seo_title'] ) ? sanitize_text_field( $_POST['seo_title'] ) : '';
		$seo_description = isset( $_POST['seo_description'] ) ? sanitize_textarea_field( $_POST['seo_description'] ) : '';
		$focus_keyword = isset( $_POST['focus_keyword'] ) ? sanitize_text_field( $_POST['focus_keyword'] ) : '';
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Save SEO data
		update_post_meta( $post_id, '_seo_simple_page_builder_title', $seo_title );
		update_post_meta( $post_id, '_seo_simple_page_builder_description', $seo_description );
		update_post_meta( $post_id, '_seo_simple_focus_keyword', $focus_keyword );
		
		// Trigger SEO analysis
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
		
		wp_send_json_success( array( 'message' => __( 'SEO data saved successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for getting page builder meta data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_page_builder_meta() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_page_builder_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		
		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
		
		// Get SEO meta data
		$seo_title = get_post_meta( $post_id, '_seo_simple_page_builder_title', true );
		$seo_description = get_post_meta( $post_id, '_seo_simple_page_builder_description', true );
		$focus_keyword = get_post_meta( $post_id, '_seo_simple_focus_keyword', true );
		
		wp_send_json_success( array(
			'seo_title' => $seo_title,
			'seo_description' => $seo_description,
			'focus_keyword' => $focus_keyword
		) );
	}

	/**
	 * Add page builder menu.
	 *
	 * @since    1.0.0
	 */
	public function add_page_builder_menu() {
		add_submenu_page(
			'seo-simple',
			__( 'Page Builder SEO', 'seo-simple' ),
			__( 'Page Builder SEO', 'seo-simple' ),
			'manage_options',
			'seo-simple-page-builders',
			array( $this, 'render_page_builder_page' )
		);
	}

	/**
	 * Register page builder settings.
	 *
	 * @since    1.0.0
	 */
	public function register_page_builder_settings() {
		register_setting(
			'seo_simple_page_builders',
			'seo_simple_page_builder_settings'
		);
		
		// Elementor settings
		add_settings_section(
			'seo_simple_elementor_settings',
			__( 'Elementor Settings', 'seo-simple' ),
			array( $this, 'elementor_settings_callback' ),
			'seo_simple_page_builders'
		);
		
		add_settings_field(
			'seo_simple_elementor_enabled',
			__( 'Enable Elementor Integration', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_elementor_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[elementor_enabled]',
				'description' => __( 'Enable SEO integration with Elementor page builder.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'elementor_enabled', true )
			)
		);
		
		add_settings_field(
			'seo_simple_elementor_analyze_on_save',
			__( 'Analyze on Save', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_elementor_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[elementor_analyze_on_save]',
				'description' => __( 'Automatically analyze content when saving in Elementor.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'elementor_analyze_on_save', true )
			)
		);
		
		// Divi settings
		add_settings_section(
			'seo_simple_divi_settings',
			__( 'Divi Settings', 'seo-simple' ),
			array( $this, 'divi_settings_callback' ),
			'seo_simple_page_builders'
		);
		
		add_settings_field(
			'seo_simple_divi_enabled',
			__( 'Enable Divi Integration', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_divi_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[divi_enabled]',
				'description' => __( 'Enable SEO integration with Divi page builder.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'divi_enabled', true )
			)
		);
		
		add_settings_field(
			'seo_simple_divi_analyze_on_save',
			__( 'Analyze on Save', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_divi_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[divi_analyze_on_save]',
				'description' => __( 'Automatically analyze content when saving in Divi.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'divi_analyze_on_save', true )
			)
		);
		
		// Other page builders settings
		add_settings_section(
			'seo_simple_other_builders_settings',
			__( 'Other Page Builders', 'seo-simple' ),
			array( $this, 'other_builders_settings_callback' ),
			'seo_simple_page_builders'
		);
		
		add_settings_field(
			'seo_simple_beaver_enabled',
			__( 'Enable Beaver Builder Integration', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_other_builders_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[beaver_enabled]',
				'description' => __( 'Enable SEO integration with Beaver Builder.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'beaver_enabled', true )
			)
		);
		
		add_settings_field(
			'seo_simple_vc_enabled',
			__( 'Enable Visual Composer Integration', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_other_builders_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[vc_enabled]',
				'description' => __( 'Enable SEO integration with Visual Composer.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'vc_enabled', true )
			)
		);
		
		add_settings_field(
			'seo_simple_fusion_enabled',
			__( 'Enable Fusion Builder Integration', 'seo-simple' ),
			array( $this, 'checkbox_field_callback' ),
			'seo_simple_page_builders',
			'seo_simple_other_builders_settings',
			array(
				'name' => 'seo_simple_page_builder_settings[fusion_enabled]',
				'description' => __( 'Enable SEO integration with Fusion Builder.', 'seo-simple' ),
				'value' => $this->get_page_builder_setting( 'fusion_enabled', true )
			)
		);
	}

	/**
	 * Get page builder setting.
	 *
	 * @since    1.0.0
	 * @param    string    $key        The setting key.
	 * @param    mixed     $default    The default value.
	 * @return   mixed                 The setting value.
	 */
	private function get_page_builder_setting( $key, $default = null ) {
		$settings = get_option( 'seo_simple_page_builder_settings', array() );
		return isset( $settings[$key] ) ? $settings[$key] : $default;
	}

	/**
	 * Elementor settings callback.
	 *
	 * @since    1.0.0
	 */
	public function elementor_settings_callback() {
		echo '<p>' . __( 'Configure SEO integration settings for Elementor page builder.', 'seo-simple' ) . '</p>';
		
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			echo '<div class="notice notice-warning"><p>' . __( 'Elementor is not installed or activated. Install Elementor to use these features.', 'seo-simple' ) . '</p></div>';
		}
	}

	/**
	 * Divi settings callback.
	 *
	 * @since    1.0.0
	 */
	public function divi_settings_callback() {
		echo '<p>' . __( 'Configure SEO integration settings for Divi page builder.', 'seo-simple' ) . '</p>';
		
		if ( ! class_exists( 'ET_Builder_Plugin' ) ) {
			echo '<div class="notice notice-warning"><p>' . __( 'Divi is not installed or activated. Install Divi to use these features.', 'seo-simple' ) . '</p></div>';
		}
	}

	/**
	 * Other builders settings callback.
	 *
	 * @since    1.0.0
	 */
	public function other_builders_settings_callback() {
		echo '<p>' . __( 'Configure SEO integration settings for other page builders.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Checkbox field callback.
	 *
	 * @since    1.0.0
	 * @param    array     $args    The field arguments.
	 */
	public function checkbox_field_callback( $args ) {
		$name = $args['name'];
		$value = $args['value'];
		$description = $args['description'];
		
		$checked = $value ? 'checked="checked"' : '';
		
		echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . $checked . ' />';
		echo '<label for="' . esc_attr( $name ) . '"> ' . $description . '</label>';
	}

	/**
	 * Render page builder page.
	 *
	 * @since    1.0.0
	 */
	public function render_page_builder_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Page Builder SEO', 'seo-simple' ); ?></h1>
			
			<div class="seo-simple-page-builders-wrapper">
				<div class="seo-simple-page-builders-overview">
					<h2><?php _e( 'Overview', 'seo-simple' ); ?></h2>
					<p><?php _e( 'The SEO Simple plugin integrates with popular page builders to ensure your SEO settings work correctly with their content. Below you can configure the integration settings for each supported page builder.', 'seo-simple' ); ?></p>
					
					<div class="seo-simple-page-builders-status">
						<h3><?php _e( 'Page Builder Status', 'seo-simple' ); ?></h3>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php _e( 'Page Builder', 'seo-simple' ); ?></th>
									<th><?php _e( 'Status', 'seo-simple' ); ?></th>
									<th><?php _e( 'Integration', 'seo-simple' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php _e( 'Elementor', 'seo-simple' ); ?></td>
									<td>
										<?php if ( class_exists( '\Elementor\Plugin' ) ) : ?>
											<span class="status-active"><?php _e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="status-inactive"><?php _e( 'Not Active', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $this->get_page_builder_setting( 'elementor_enabled', true ) ) : ?>
											<span class="integration-enabled"><?php _e( 'Enabled', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="integration-disabled"><?php _e( 'Disabled', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Divi', 'seo-simple' ); ?></td>
									<td>
										<?php if ( class_exists( 'ET_Builder_Plugin' ) ) : ?>
											<span class="status-active"><?php _e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="status-inactive"><?php _e( 'Not Active', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $this->get_page_builder_setting( 'divi_enabled', true ) ) : ?>
											<span class="integration-enabled"><?php _e( 'Enabled', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="integration-disabled"><?php _e( 'Disabled', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Beaver Builder', 'seo-simple' ); ?></td>
									<td>
										<?php if ( class_exists( 'FLBuilder' ) ) : ?>
											<span class="status-active"><?php _e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="status-inactive"><?php _e( 'Not Active', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $this->get_page_builder_setting( 'beaver_enabled', true ) ) : ?>
											<span class="integration-enabled"><?php _e( 'Enabled', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="integration-disabled"><?php _e( 'Disabled', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Visual Composer', 'seo-simple' ); ?></td>
									<td>
										<?php if ( class_exists( 'Vc_Manager' ) ) : ?>
											<span class="status-active"><?php _e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="status-inactive"><?php _e( 'Not Active', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $this->get_page_builder_setting( 'vc_enabled', true ) ) : ?>
											<span class="integration-enabled"><?php _e( 'Enabled', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="integration-disabled"><?php _e( 'Disabled', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php _e( 'Fusion Builder', 'seo-simple' ); ?></td>
									<td>
										<?php if ( class_exists( 'FusionBuilder' ) ) : ?>
											<span class="status-active"><?php _e( 'Active', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="status-inactive"><?php _e( 'Not Active', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $this->get_page_builder_setting( 'fusion_enabled', true ) ) : ?>
											<span class="integration-enabled"><?php _e( 'Enabled', 'seo-simple' ); ?></span>
										<?php else : ?>
											<span class="integration-disabled"><?php _e( 'Disabled', 'seo-simple' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="seo-simple-page-builders-settings">
					<form method="post" action="options.php">
						<?php
						settings_fields( 'seo_simple_page_builders' );
						do_settings_sections( 'seo_simple_page_builders' );
						submit_button();
						?>
					</form>
				</div>
			</div>
		</div>
		
		<style>
		.seo-simple-page-builders-wrapper {
			margin-top: 20px;
		}
		
		.seo-simple-page-builders-overview {
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
			margin-bottom: 20px;
		}
		
		.seo-simple-page-builders-settings {
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		
		.status-active {
			color: #46b450;
			font-weight: bold;
		}
		
		.status-inactive {
			color: #dc3232;
			font-weight: bold;
		}
		
		.integration-enabled {
			color: #46b450;
			font-weight: bold;
		}
		
		.integration-disabled {
			color: #ffb900;
			font-weight: bold;
		}
		
		.seo-simple-page-builders-status table {
			margin-top: 10px;
		}
		
		.seo-simple-page-builders-status th {
			font-weight: bold;
		}
		</style>
		<?php
	}

	/**
	 * Display page builder notices.
	 *
	 * @since    1.0.0
	 */
	public function display_page_builder_notices() {
		$screen = get_current_screen();
		
		// Only show notices on post edit screens
		if ( ! $screen || ( $screen->base !== 'post' && $screen->base !== 'post-new' ) ) {
			return;
		}
		
		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
		
		if ( ! $post_id ) {
			return;
		}
		
		// Check if Elementor is being used
		if ( class_exists( '\Elementor\Plugin' ) && get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder' ) {
			if ( ! $this->get_page_builder_setting( 'elementor_enabled', true ) ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php _e( 'SEO Simple: Elementor integration is disabled. Enable it in SEO Simple > Page Builder SEO to ensure SEO features work correctly with Elementor content.', 'seo-simple' ); ?>
					</p>
				</div>
				<?php
			}
		}
		
		// Check if Divi is being used
		if ( class_exists( 'ET_Builder_Plugin' ) && get_post_meta( $post_id, '_et_pb_use_builder', true ) ) {
			if ( ! $this->get_page_builder_setting( 'divi_enabled', true ) ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php _e( 'SEO Simple: Divi integration is disabled. Enable it in SEO Simple > Page Builder SEO to ensure SEO features work correctly with Divi content.', 'seo-simple' ); ?>
					</p>
				</div>
				<?php
			}
		}
		
		// Check if Beaver Builder is being used
		if ( class_exists( 'FLBuilder' ) && FLBuilderModel::is_builder_enabled( $post_id ) ) {
			if ( ! $this->get_page_builder_setting( 'beaver_enabled', true ) ) {
				?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<?php _e( 'SEO Simple: Beaver Builder integration is disabled. Enable it in SEO Simple > Page Builder SEO to ensure SEO features work correctly with Beaver Builder content.', 'seo-simple' ); ?>
					</p>
				</div>
				<?php
			}
		}
	}
}
