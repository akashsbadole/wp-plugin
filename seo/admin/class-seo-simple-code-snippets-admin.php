<?php

/**
 * Admin functionality for code snippets.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Admin functionality for code snippets.
 *
 * This class handles the admin interface for managing code snippets.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Code_Snippets_Admin {

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
	 * The code snippets handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Code_Snippets    $code_snippets    The code snippets handler instance.
	 */
	private $code_snippets;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->code_snippets = new Seo_Simple_Code_Snippets();
	}

	/**
	 * Register the admin menu for code snippets.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		// Code snippets are now integrated into settings
		// No need to create a separate menu item
		// This method is kept for backward compatibility
	}

	/**
	 * Display the admin page for code snippets.
	 *
	 * @since    1.0.0
	 */
	public function display_admin_page() {
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
		$preset = isset( $_GET['preset'] ) ? sanitize_text_field( $_GET['preset'] ) : '';
		$error = isset( $_GET['error'] ) ? sanitize_text_field( $_GET['error'] ) : '';
		
		switch ( $action ) {
			case 'add':
				$this->display_add_edit_form( null, $preset, $error );
				break;
			case 'edit':
				$snippet = $this->code_snippets->get_snippet( $id );
				$this->display_add_edit_form( $snippet, '', $error );
				break;
			case 'delete':
				$this->handle_delete();
				break;
			default:
				$this->display_snippets_list();
				break;
		}
	}

	/**
	 * Display the list of code snippets.
	 *
	 * @since    1.0.0
	 */
	private function display_snippets_list() {
		$snippets = $this->code_snippets->get_all_snippets();
		$message = '';
		
		if ( isset( $_GET['message'] ) ) {
			switch ( $_GET['message'] ) {
				case 'added':
					$message = __( 'Code snippet added successfully!', 'seo-simple' );
					break;
				case 'updated':
					$message = __( 'Code snippet updated successfully!', 'seo-simple' );
					break;
				case 'deleted':
					$message = __( 'Code snippet deleted successfully!', 'seo-simple' );
					break;
			}
		}
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/code-snippets-list.php';
	}

	/**
	 * Display the add/edit form for code snippets.
	 *
	 * @since    1.0.0
	 * @param    object    $snippet    The snippet object (for edit mode).
	 * @param    string    $preset     The preset to use (for add mode).
	 * @param    string    $error      The error message (if any).
	 */
	private function display_add_edit_form( $snippet = null, $preset = '', $error = '' ) {
		$is_edit = $snippet !== null;
		
		// Check for form errors
		$form_error = null;
		$submited_data = null;
		
		if ( ! empty( $error ) && $error === 'validation_failed' ) {
			$error_data = get_transient( 'seo_simple_snippet_form_error' );
			if ( $error_data ) {
				$form_error = $error_data['error'];
				$submited_data = $error_data['submitted_data'];
				delete_transient( 'seo_simple_snippet_form_error' );
			}
		}
		
		// Get preset data if specified
		$preset_data = array();
		if ( ! empty( $preset ) && ! $is_edit ) {
			switch ( $preset ) {
				case 'google-analytics':
					$preset_data = array(
						'name' => 'Google Analytics',
						'code' => '<!-- Global site tag (gtag.js) - Google Analytics -->' . "\n" .
								  '<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>' . "\n" .
								  '<script>' . "\n" .
								  '  window.dataLayer = window.dataLayer || [];' . "\n" .
								  '  function gtag(){dataLayer.push(arguments);}' . "\n" .
								  '  gtag(\'js\', new Date());' . "\n" .
								  '  gtag(\'config\', \'GA_TRACKING_ID\');' . "\n" .
								  '</script>',
						'location' => 'header'
					);
					break;
				case 'facebook-pixel':
					$preset_data = array(
						'name' => 'Facebook Pixel',
						'code' => '<!-- Facebook Pixel Code -->' . "\n" .
								  '<script>' . "\n" .
								  '!function(f,b,e,v,n,t,s)' . "\n" .
								  '{if(f.fbq)return;n=f.fbq=function(){n.callMethod?' . "\n" .
								  'n.callMethod.apply(n,arguments):n.queue.push(arguments);}' . "\n" .
								  'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';' . "\n" .
								  'n.queue=[];t=b.createElement(e);t.async=!0;' . "\n" .
								  't.src=v;s=b.getElementsByTagName(e)[0];' . "\n" .
								  's.parentNode.insertBefore(t,s)}(window, document,\'script\',' . "\n" .
								  '\'https://connect.facebook.net/en_US/fbevents.js\');' . "\n" .
								  'fbq(\'init\', \'YOUR_PIXEL_ID\');' . "\n" .
								  'fbq(\'track\', \'PageView\');' . "\n" .
								  '</script>' . "\n" .
								  '<noscript><img height="1" width="1" style="display:none"' . "\n" .
								  'src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"' . "\n" .
								  '/></noscript>',
						'location' => 'header'
					);
					break;
			}
		}
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/code-snippets-form.php';
	}

	/**
	 * Handle form submission for adding/editing snippets.
	 *
	 * @since    1.0.0
	 */
	public function handle_form_submission() {
		// Verify nonce for security
		if ( ! isset( $_POST['seo_simple_code_snippet_nonce'] ) ||
			 ! wp_verify_nonce( $_POST['seo_simple_code_snippet_nonce'], 'seo_simple_code_snippet_action' ) ) {
			wp_die( __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
		}
		
		$id = isset( $_POST['snippet_id'] ) ? intval( $_POST['snippet_id'] ) : 0;
		$name = sanitize_text_field( $_POST['snippet_name'] );
		$code = wp_kses_post( $_POST['snippet_code'] );
		$location = sanitize_text_field( $_POST['snippet_location'] );
		$status = sanitize_text_field( $_POST['snippet_status'] );
		$priority = intval( $_POST['snippet_priority'] );
		
		// Validate the code using the new validation method
		$validation = $this->code_snippets->validate_snippet_code( $code );
		if ( ! $validation['valid'] ) {
			// Store the error message and submitted data in a transient
			$transient_data = array(
				'error' => $validation['message'],
				'submitted_data' => array(
					'name' => $name,
					'code' => $code,
					'location' => $location,
					'status' => $status,
					'priority' => $priority
				)
			);
			set_transient( 'seo_simple_snippet_form_error', $transient_data, 60 );
			
			// Redirect back to the form with error
			$redirect_url = add_query_arg(
				array(
					'action' => $id > 0 ? 'edit' : 'add',
					'id' => $id > 0 ? $id : 0,
					'error' => 'validation_failed'
				),
				admin_url( 'options-general.php?page=seo-simple-code-snippets' )
			);
			wp_redirect( $redirect_url );
			exit;
		}
		
		$data = array(
			'name' => $name,
			'code' => $code,
			'location' => $location,
			'status' => $status,
			'priority' => $priority
		);
		
		if ( $id > 0 ) {
			$result = $this->code_snippets->update_snippet( $id, $data );
			if ( $result ) {
				wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=updated' ) );
				exit;
			}
		} else {
			$result = $this->code_snippets->add_snippet( $data );
			if ( $result ) {
				wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=added' ) );
				exit;
			}
		}
	}

	/**
	 * Handle deletion of snippets.
	 *
	 * @since    1.0.0
	 */
	private function handle_delete() {
		// Verify nonce for security
		if ( ! isset( $_GET['_wpnonce'] ) ||
			 ! wp_verify_nonce( $_GET['_wpnonce'], 'seo_simple_delete_snippet_' . $_GET['id'] ) ) {
			wp_die( __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) );
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
		}
		
		$id = intval( $_GET['id'] );
		$result = $this->code_snippets->delete_snippet( $id );
		
		if ( $result ) {
			wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=deleted' ) );
			exit;
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		
		// Only load on our admin page
		if ( $screen && strpos( $screen->id, 'seo-simple-code-snippets' ) !== false ) {
			wp_enqueue_style(
				$this->plugin_name . '-code-snippets',
				plugin_dir_url( __FILE__ ) . 'css/seo-simple-code-snippets.css',
				array(),
				$this->version,
				'all'
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		
		// Only load on our admin page
		if ( $screen && strpos( $screen->id, 'seo-simple-code-snippets' ) !== false ) {
			wp_enqueue_script(
				$this->plugin_name . '-code-snippets',
				plugin_dir_url( __FILE__ ) . 'js/seo-simple-code-snippets.js',
				array( 'jquery' ),
				$this->version,
				false
			);
			
			// Localize script
			wp_localize_script(
				$this->plugin_name . '-code-snippets',
				'seo_simple_admin',
				array(
					'delete_confirm' => __( 'Are you sure you want to delete this snippet?', 'seo-simple' ),
					'name_required' => __( 'Please enter a name for the snippet.', 'seo-simple' ),
					'code_required' => __( 'Please enter the code for the snippet.', 'seo-simple' ),
					'preset_confirm' => __( 'This will replace the current content. Do you want to continue?', 'seo-simple' ),
					'validation_error' => __( 'Code validation failed. Please check your code and try again.', 'seo-simple' ),
				)
			);
		}
	}
}