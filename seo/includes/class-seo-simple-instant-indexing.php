<?php

/**
 * Handles instant indexing for search engines.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles instant indexing for search engines.
 *
 * This class is responsible for submitting URLs to search engines
 * for instant indexing, including Google, Bing, and Yandex.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Instant_Indexing {

	/**
	 * The meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * The security handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security    $security    The security handler instance.
	 */
	private $security;

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache_Manager    $cache_manager    The cache handler instance.
	 */
	private $cache_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta               $seo_meta       The SEO meta handler instance.
	 * @param    Seo_Simple_Security           $security       The security handler instance.
	 * @param    Seo_Simple_Cache_Manager      $cache_manager  The cache handler instance.
	 */
	public function __construct( $seo_meta, $security = null, $cache_manager = null ) {
		$this->seo_meta = $seo_meta;
		$this->security = $security;
		$this->cache_manager = $cache_manager;
	}

	/**
	 * Register hooks for instant indexing.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add admin menu items
		add_action( 'admin_menu', array( $this, 'add_admin_menu_items' ) );
		
		// Add meta box to post editor
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_submit_indexing', array( $this, 'ajax_submit_indexing' ) );
		add_action( 'wp_ajax_seo_simple_get_indexing_status', array( $this, 'ajax_get_indexing_status' ) );
		add_action( 'wp_ajax_seo_simple_save_indexing_settings', array( $this, 'ajax_save_indexing_settings' ) );
		
		// Handle post submission for automatic indexing
		add_action( 'transition_post_status', array( $this, 'handle_post_status_change' ), 10, 3 );
		
		// Add settings tab
		add_filter( 'seo_simple_settings_tabs', array( $this, 'add_settings_tab' ) );
		
		// Add settings fields
		add_action( 'seo_simple_settings_tab_content_indexing', array( $this, 'render_settings_tab' ) );
	}

	/**
	 * Add admin menu items.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu_items() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Instant Indexing', 'seo-simple' ),
			__( 'Instant Indexing', 'seo-simple' ),
			'manage_options',
			'seo-simple-instant-indexing',
			array( $this, 'render_indexing_page' )
		);
	}

	/**
	 * Add meta box to post editor.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );
		
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'seo-simple-instant-indexing',
				__( 'Instant Indexing', 'seo-simple' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    The post object.
	 */
	public function render_meta_box( $post ) {
		// Get indexing status
		$indexing_status = $this->get_indexing_status( $post->ID );
		$last_submitted = get_post_meta( $post->ID, '_seo_simple_last_indexed', true );
		
		// Check if post is published
		$is_published = 'publish' === $post->post_status;
		
		include_once dirname( __FILE__ ) . '/../admin/partials/meta-box-instant-indexing.php';
	}

	/**
	 * Add settings tab.
	 *
	 * @since    1.0.0
	 * @param    array     $tabs    The existing tabs.
	 * @return   array              The modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['indexing'] = array(
			'label' => __( 'Instant Indexing', 'seo-simple' ),
			'icon' => 'dashicons-search',
		);
		
		return $tabs;
	}

	/**
	 * Render settings tab.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_tab() {
		$settings = $this->seo_meta->get_global_settings();
		
		// Set default values
		$google_enabled = isset( $settings['google_indexing_enabled'] ) ? $settings['google_indexing_enabled'] : false;
		$google_api_key = isset( $settings['google_indexing_api_key'] ) ? $settings['google_indexing_api_key'] : '';
		$bing_enabled = isset( $settings['bing_indexing_enabled'] ) ? $settings['bing_indexing_enabled'] : false;
		$bing_api_key = isset( $settings['bing_indexing_api_key'] ) ? $settings['bing_indexing_api_key'] : '';
		$yandex_enabled = isset( $settings['yandex_indexing_enabled'] ) ? $settings['yandex_indexing_enabled'] : false;
		$yandex_api_key = isset( $settings['yandex_indexing_api_key'] ) ? $settings['yandex_indexing_api_key'] : '';
		$yandex_user_id = isset( $settings['yandex_indexing_user_id'] ) ? $settings['yandex_indexing_user_id'] : '';
		$yandex_host_id = isset( $settings['yandex_indexing_host_id'] ) ? $settings['yandex_indexing_host_id'] : '';
		$auto_index = isset( $settings['auto_indexing_enabled'] ) ? $settings['auto_indexing_enabled'] : false;
		$indexing_limit = isset( $settings['indexing_daily_limit'] ) ? $settings['indexing_daily_limit'] : 100;
		
		include_once dirname( __FILE__ ) . '/../admin/partials/indexing-settings-page.php';
	}

	/**
	 * Render indexing page.
	 *
	 * @since    1.0.0
	 */
	public function render_indexing_page() {
		$settings = $this->seo_meta->get_global_settings();
		$api_keys_configured = $this->check_api_keys_configured();
		
		include_once dirname( __FILE__ ) . '/../admin/partials/instant-indexing-page.php';
	}

	/**
	 * Check if API keys are configured.
	 *
	 * @since    1.0.0
	 * @return   array     Array of configured search engines.
	 */
	private function check_api_keys_configured() {
		$settings = $this->seo_meta->get_global_settings();
		$configured = array();
		
		if ( ! empty( $settings['google_indexing_api_key'] ) ) {
			$configured[] = 'google';
		}
		
		if ( ! empty( $settings['bing_indexing_api_key'] ) ) {
			$configured[] = 'bing';
		}
		
		if ( ! empty( $settings['yandex_indexing_api_key'] ) && 
		     ! empty( $settings['yandex_indexing_user_id'] ) && 
		     ! empty( $settings['yandex_indexing_host_id'] ) ) {
			$configured[] = 'yandex';
		}
		
		return $configured;
	}

	/**
	 * AJAX handler for submitting URLs for indexing.
	 *
	 * @since    1.0.0
	 */
	public function ajax_submit_indexing() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_submit_indexing' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to submit URLs for indexing.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';
		$engines = isset( $_POST['engines'] ) ? array_map( 'sanitize_text_field', $_POST['engines'] ) : array();
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		
		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid URL.', 'seo-simple' ) ) );
		}
		
		if ( empty( $engines ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select at least one search engine.', 'seo-simple' ) ) );
		}
		
		// Check daily limit
		if ( ! $this->check_daily_limit( count( $engines ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Daily indexing limit reached.', 'seo-simple' ) ) );
		}
		
		$results = array();
		$success_count = 0;
		
		foreach ( $engines as $engine ) {
			$result = $this->submit_to_search_engine( $url, $engine );
			$results[$engine] = $result;
			
			if ( $result['success'] ) {
				$success_count++;
			}
		}
		
		// Update submission count
		$this->update_daily_submission_count( $success_count );
		
		// Update post meta if post ID is provided
		if ( $post_id > 0 ) {
			update_post_meta( $post_id, '_seo_simple_last_indexed', current_time( 'mysql' ) );
			
			// Save indexing status
			$current_status = get_post_meta( $post_id, '_seo_simple_indexing_status', true );
			if ( ! is_array( $current_status ) ) {
				$current_status = array();
			}
			
			foreach ( $engines as $engine ) {
				$current_status[$engine] = array(
					'submitted' => current_time( 'mysql' ),
					'success' => $results[$engine]['success'],
					'message' => $results[$engine]['message']
				);
			}
			
			update_post_meta( $post_id, '_seo_simple_indexing_status', $current_status );
		}
		
		wp_send_json_success( array(
			'message' => sprintf( __( 'Successfully submitted to %d search engine(s).', 'seo-simple' ), $success_count ),
			'results' => $results
		) );
	}

	/**
	 * AJAX handler for getting indexing status.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_indexing_status() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_get_indexing_status' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to access indexing status.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		
		if ( $post_id > 0 ) {
			$status = $this->get_indexing_status( $post_id );
			wp_send_json_success( $status );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'seo-simple' ) ) );
		}
	}

	/**
	 * AJAX handler for saving indexing settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_indexing_settings() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_save_indexing_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to save these settings.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize and save settings
		$settings = $this->seo_meta->get_global_settings();
		
		// Google settings
		$settings['google_indexing_enabled'] = isset( $_POST['google_indexing_enabled'] ) ? (bool) $_POST['google_indexing_enabled'] : false;
		$settings['google_indexing_api_key'] = isset( $_POST['google_indexing_api_key'] ) ? sanitize_text_field( $_POST['google_indexing_api_key'] ) : '';
		
		// Bing settings
		$settings['bing_indexing_enabled'] = isset( $_POST['bing_indexing_enabled'] ) ? (bool) $_POST['bing_indexing_enabled'] : false;
		$settings['bing_indexing_api_key'] = isset( $_POST['bing_indexing_api_key'] ) ? sanitize_text_field( $_POST['bing_indexing_api_key'] ) : '';
		
		// Yandex settings
		$settings['yandex_indexing_enabled'] = isset( $_POST['yandex_indexing_enabled'] ) ? (bool) $_POST['yandex_indexing_enabled'] : false;
		$settings['yandex_indexing_api_key'] = isset( $_POST['yandex_indexing_api_key'] ) ? sanitize_text_field( $_POST['yandex_indexing_api_key'] ) : '';
		$settings['yandex_indexing_user_id'] = isset( $_POST['yandex_indexing_user_id'] ) ? sanitize_text_field( $_POST['yandex_indexing_user_id'] ) : '';
		$settings['yandex_indexing_host_id'] = isset( $_POST['yandex_indexing_host_id'] ) ? sanitize_text_field( $_POST['yandex_indexing_host_id'] ) : '';
		
		// General settings
		$settings['auto_indexing_enabled'] = isset( $_POST['auto_indexing_enabled'] ) ? (bool) $_POST['auto_indexing_enabled'] : false;
		$settings['indexing_daily_limit'] = isset( $_POST['indexing_daily_limit'] ) ? intval( $_POST['indexing_daily_limit'] ) : 100;
		
		// Update settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'seo-simple' ) ) );
	}

	/**
	 * Get indexing status for a post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   array                 The indexing status.
	 */
	private function get_indexing_status( $post_id ) {
		$status = get_post_meta( $post_id, '_seo_simple_indexing_status', true );
		$last_submitted = get_post_meta( $post_id, '_seo_simple_last_indexed', true );
		
		if ( ! is_array( $status ) ) {
			$status = array();
		}
		
		return array(
			'status' => $status,
			'last_submitted' => $last_submitted,
			'url' => get_permalink( $post_id )
		);
	}

	/**
	 * Submit URL to search engine.
	 *
	 * @since    1.0.0
	 * @param    string    $url     The URL to submit.
	 * @param    string    $engine  The search engine.
	 * @return   array              The result of the submission.
	 */
	private function submit_to_search_engine( $url, $engine ) {
		$settings = $this->seo_meta->get_global_settings();
		
		switch ( $engine ) {
			case 'google':
				return $this->submit_to_google( $url, $settings );
			case 'bing':
				return $this->submit_to_bing( $url, $settings );
			case 'yandex':
				return $this->submit_to_yandex( $url, $settings );
			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown search engine.', 'seo-simple' )
				);
		}
	}

	/**
	 * Submit URL to Google.
	 *
	 * @since    1.0.0
	 * @param    string    $url       The URL to submit.
	 * @param    array     $settings  The plugin settings.
	 * @return   array                The result of the submission.
	 */
	private function submit_to_google( $url, $settings ) {
		if ( empty( $settings['google_indexing_api_key'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Google API key not configured.', 'seo-simple' )
			);
		}
		
		$api_key = $settings['google_indexing_api_key'];
		$endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
		
		$body = json_encode( array(
			'url' => $url,
			'type' => 'URL_UPDATED'
		) );
		
		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $api_key
		);
		
		$response = wp_remote_post( $endpoint, array(
			'headers' => $headers,
			'body' => $body,
			'timeout' => 30,
			'sslverify' => true
		) );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message()
			);
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $response_code === 200 ) {
			return array(
				'success' => true,
				'message' => __( 'URL submitted to Google successfully.', 'seo-simple' )
			);
		} else {
			$message = isset( $response_body['error']['message'] ) ? $response_body['error']['message'] : __( 'Unknown error.', 'seo-simple' );
			return array(
				'success' => false,
				'message' => sprintf( __( 'Google API error: %s', 'seo-simple' ), $message )
			);
		}
	}

	/**
	 * Submit URL to Bing.
	 *
	 * @since    1.0.0
	 * @param    string    $url       The URL to submit.
	 * @param    array     $settings  The plugin settings.
	 * @return   array                The result of the submission.
	 */
	private function submit_to_bing( $url, $settings ) {
		if ( empty( $settings['bing_indexing_api_key'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Bing API key not configured.', 'seo-simple' )
			);
		}
		
		$api_key = $settings['bing_indexing_api_key'];
		$endpoint = 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrl';
		
		$body = json_encode( array(
			'siteUrl' => home_url(),
			'url' => $url
		) );
		
		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Bearer ' . $api_key
		);
		
		$response = wp_remote_post( $endpoint, array(
			'headers' => $headers,
			'body' => $body,
			'timeout' => 30,
			'sslverify' => true
		) );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message()
			);
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $response_code === 200 ) {
			return array(
				'success' => true,
				'message' => __( 'URL submitted to Bing successfully.', 'seo-simple' )
			);
		} else {
			$message = isset( $response_body['error']['message'] ) ? $response_body['error']['message'] : __( 'Unknown error.', 'seo-simple' );
			return array(
				'success' => false,
				'message' => sprintf( __( 'Bing API error: %s', 'seo-simple' ), $message )
			);
		}
	}

	/**
	 * Submit URL to Yandex.
	 *
	 * @since    1.0.0
	 * @param    string    $url       The URL to submit.
	 * @param    array     $settings  The plugin settings.
	 * @return   array                The result of the submission.
	 */
	private function submit_to_yandex( $url, $settings ) {
		if ( empty( $settings['yandex_indexing_api_key'] ) || 
		     empty( $settings['yandex_indexing_user_id'] ) || 
		     empty( $settings['yandex_indexing_host_id'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Yandex API credentials not configured.', 'seo-simple' )
			);
		}
		
		$api_key = $settings['yandex_indexing_api_key'];
		$user_id = $settings['yandex_indexing_user_id'];
		$host_id = $settings['yandex_indexing_host_id'];
		$endpoint = "https://api.webmaster.yandex.net/v4/user/{$user_id}/hosts/{$host_id}/recrawl/queue";
		
		$body = json_encode( array(
			'url' => $url
		) );
		
		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'OAuth ' . $api_key
		);
		
		$response = wp_remote_post( $endpoint, array(
			'headers' => $headers,
			'body' => $body,
			'timeout' => 30,
			'sslverify' => true
		) );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message()
			);
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $response_code === 202 ) {
			return array(
				'success' => true,
				'message' => __( 'URL submitted to Yandex successfully.', 'seo-simple' )
			);
		} else {
			$message = isset( $response_body['error_message'] ) ? $response_body['error_message'] : __( 'Unknown error.', 'seo-simple' );
			return array(
				'success' => false,
				'message' => sprintf( __( 'Yandex API error: %s', 'seo-simple' ), $message )
			);
		}
	}

	/**
	 * Check daily limit.
	 *
	 * @since    1.0.0
	 * @param    int       $count    The number of submissions to check.
	 * @return   bool                True if within limit.
	 */
	private function check_daily_limit( $count ) {
		$settings = $this->seo_meta->get_global_settings();
		$daily_limit = isset( $settings['indexing_daily_limit'] ) ? intval( $settings['indexing_daily_limit'] ) : 100;
		
		$today = date( 'Y-m-d' );
		$submission_count = get_option( 'seo_simple_indexing_count_' . $today, 0 );
		
		return ( $submission_count + $count ) <= $daily_limit;
	}

	/**
	 * Update daily submission count.
	 *
	 * @since    1.0.0
	 * @param    int       $count    The number of submissions to add.
	 */
	private function update_daily_submission_count( $count ) {
		$today = date( 'Y-m-d' );
		$submission_count = get_option( 'seo_simple_indexing_count_' . $today, 0 );
		$submission_count += $count;
		update_option( 'seo_simple_indexing_count_' . $today, $submission_count );
	}

	/**
	 * Handle post status change for automatic indexing.
	 *
	 * @since    1.0.0
	 * @param    string    $new_status    The new post status.
	 * @param    string    $old_status    The old post status.
	 * @param    WP_Post   $post          The post object.
	 */
	public function handle_post_status_change( $new_status, $old_status, $post ) {
		// Only handle transition to publish
		if ( $new_status !== 'publish' || $old_status === 'publish' ) {
			return;
		}
		
		$settings = $this->seo_meta->get_global_settings();
		
		// Check if auto indexing is enabled
		if ( empty( $settings['auto_indexing_enabled'] ) ) {
			return;
		}
		
		// Get enabled search engines
		$engines = array();
		if ( ! empty( $settings['google_indexing_enabled'] ) && ! empty( $settings['google_indexing_api_key'] ) ) {
			$engines[] = 'google';
		}
		if ( ! empty( $settings['bing_indexing_enabled'] ) && ! empty( $settings['bing_indexing_api_key'] ) ) {
			$engines[] = 'bing';
		}
		if ( ! empty( $settings['yandex_indexing_enabled'] ) && 
		     ! empty( $settings['yandex_indexing_api_key'] ) && 
		     ! empty( $settings['yandex_indexing_user_id'] ) && 
		     ! empty( $settings['yandex_indexing_host_id'] ) ) {
			$engines[] = 'yandex';
		}
		
		if ( empty( $engines ) ) {
			return;
		}
		
		// Check daily limit
		if ( ! $this->check_daily_limit( count( $engines ) ) ) {
			return;
		}
		
		$url = get_permalink( $post );
		$results = array();
		$success_count = 0;
		
		foreach ( $engines as $engine ) {
			$result = $this->submit_to_search_engine( $url, $engine );
			$results[$engine] = $result;
			
			if ( $result['success'] ) {
				$success_count++;
			}
		}
		
		// Update submission count
		$this->update_daily_submission_count( $success_count );
		
		// Update post meta
		update_post_meta( $post->ID, '_seo_simple_last_indexed', current_time( 'mysql' ) );
		
		// Save indexing status
		$current_status = get_post_meta( $post->ID, '_seo_simple_indexing_status', true );
		if ( ! is_array( $current_status ) ) {
			$current_status = array();
		}
		
		foreach ( $engines as $engine ) {
			$current_status[$engine] = array(
				'submitted' => current_time( 'mysql' ),
				'success' => $results[$engine]['success'],
				'message' => $results[$engine]['message']
			);
		}
		
		update_post_meta( $post->ID, '_seo_simple_indexing_status', $current_status );
	}
}