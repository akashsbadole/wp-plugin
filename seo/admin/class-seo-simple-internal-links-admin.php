<?php

/**
 * Handles the admin interface for internal linking.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the admin interface for internal linking.
 *
 * This class handles the admin interface for internal linking functionality
 * including displaying suggestions and managing link relationships.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Internal_Links_Admin {

	/**
	 * The internal links handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Internal_Links    $internal_links    The internal links handler instance.
	 */
	private $internal_links;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Initialize internal links handler
		$this->internal_links = new Seo_Simple_Internal_Links();
		
		// Register AJAX handlers
		add_action( 'wp_ajax_seo_simple_get_internal_link_suggestions', array( $this, 'ajax_get_internal_link_suggestions' ) );
		add_action( 'wp_ajax_seo_simple_search_posts_by_keyword', array( $this, 'ajax_search_posts_by_keyword' ) );
		add_action( 'wp_ajax_seo_simple_get_orphaned_posts', array( $this, 'ajax_get_orphaned_posts' ) );
	}

	/**
	 * AJAX handler for getting internal link suggestions.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_internal_link_suggestions() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_internal_links_nonce' ) ) {
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
		
		// Get internal link suggestions
		$suggestions = $this->internal_links->get_internal_link_suggestions( $post_id );
		
		// Render suggestions
		ob_start();
		$this->render_internal_link_suggestions( $suggestions );
		$html = ob_get_clean();
		
		wp_send_json_success( array(
			'html' => $html,
			'count' => count( $suggestions ),
		) );
	}

	/**
	 * AJAX handler for searching posts by keyword.
	 *
	 * @since    1.0.0
	 */
	public function ajax_search_posts_by_keyword() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_internal_links_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 10;
		
		if ( empty( $keyword ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a keyword to search.', 'seo-simple' ) ) );
		}
		
		// Get posts by keyword
		$posts = $this->internal_links->get_posts_by_keyword( $keyword, $limit );
		
		// Render posts
		ob_start();
		$this->render_posts_list( $posts );
		$html = ob_get_clean();
		
		wp_send_json_success( array(
			'html' => $html,
			'count' => count( $posts ),
		) );
	}

	/**
	 * AJAX handler for getting orphaned posts.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_orphaned_posts() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_internal_links_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize input data
		$limit = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 10;
		
		// Get orphaned posts
		$posts = $this->internal_links->get_orphaned_posts( $limit );
		
		// Render posts
		ob_start();
		$this->render_orphaned_posts_list( $posts );
		$html = ob_get_clean();
		
		wp_send_json_success( array(
			'html' => $html,
			'count' => count( $posts ),
		) );
	}

	/**
	 * Render internal link suggestions.
	 *
	 * @since    1.0.0
	 * @param    array    $suggestions    The internal link suggestions.
	 */
	private function render_internal_link_suggestions( $suggestions ) {
		if ( empty( $suggestions ) ) {
			echo '<p>' . __( 'No internal link suggestions found. Try adding more content to your post.', 'seo-simple' ) . '</p>';
			return;
		}
		
		echo '<div class="seo-simple-internal-links-suggestions">';
		
		foreach ( $suggestions as $suggestion ) {
			echo '<div class="seo-simple-internal-link-suggestion">';
			echo '<h4><a href="' . esc_url( $suggestion['url'] ) . '" target="_blank">' . esc_html( $suggestion['title'] ) . '</a></h4>';
			echo '<p>' . __( 'Similarity:', 'seo-simple' ) . ' ' . $suggestion['similarity'] . '%</p>';
			
			if ( ! empty( $suggestion['common_keywords'] ) && is_array( $suggestion['common_keywords'] ) ) {
				$keywords = array_filter( $suggestion['common_keywords'], 'is_string' );
				if ( ! empty( $keywords ) && is_array( $keywords ) ) {
					echo '<p>' . __( 'Common keywords:', 'seo-simple' ) . ' ' . implode( ', ', array_map( 'esc_html', $keywords ) ) . '</p>';
				}
			}
			
			if ( ! empty( $suggestion['anchor_texts'] ) ) {
				echo '<p>' . __( 'Suggested anchor text:', 'seo-simple' ) . '</p>';
				echo '<ul class="seo-simple-anchor-text-suggestions">';
				foreach ( $suggestion['anchor_texts'] as $anchor_text ) {
					echo '<li><code>' . esc_html( $anchor_text ) . '</code> ';
					echo '<button class="button button-small seo-simple-insert-link" data-url="' . esc_url( $suggestion['url'] ) . '" data-title="' . esc_attr( $suggestion['title'] ) . '" data-anchor="' . esc_attr( $anchor_text ) . '">' . __( 'Insert Link', 'seo-simple' ) . '</button></li>';
				}
				echo '</ul>';
			}
			
			echo '<button class="button seo-simple-insert-link" data-url="' . esc_url( $suggestion['url'] ) . '" data-title="' . esc_attr( $suggestion['title'] ) . '">' . __( 'Insert Link', 'seo-simple' ) . '</button>';
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * Render a list of posts.
	 *
	 * @since    1.0.0
	 * @param    array    $posts    The posts to render.
	 */
	private function render_posts_list( $posts ) {
		if ( empty( $posts ) ) {
			echo '<p>' . __( 'No posts found for this keyword.', 'seo-simple' ) . '</p>';
			return;
		}
		
		echo '<div class="seo-simple-posts-list">';
		
		foreach ( $posts as $post ) {
			echo '<div class="seo-simple-post-item">';
			echo '<h4><a href="' . esc_url( $post['url'] ) . '" target="_blank">' . esc_html( $post['title'] ) . '</a></h4>';
			echo '<p>' . __( 'Relevance:', 'seo-simple' ) . ' ' . $post['relevance'] . '</p>';
			echo '<button class="button seo-simple-insert-link" data-url="' . esc_url( $post['url'] ) . '" data-title="' . esc_attr( $post['title'] ) . '">' . __( 'Insert Link', 'seo-simple' ) . '</button>';
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * Render a list of orphaned posts.
	 *
	 * @since    1.0.0
	 * @param    array    $posts    The orphaned posts to render.
	 */
	private function render_orphaned_posts_list( $posts ) {
		if ( empty( $posts ) ) {
			echo '<p>' . __( 'No orphaned posts found. Great job!', 'seo-simple' ) . '</p>';
			return;
		}
		
		echo '<div class="seo-simple-orphaned-posts-list">';
		
		foreach ( $posts as $post ) {
			echo '<div class="seo-simple-orphaned-post-item">';
			echo '<h4><a href="' . esc_url( $post['url'] ) . '" target="_blank">' . esc_html( $post['title'] ) . '</a></h4>';
			echo '<p>' . __( 'Internal links pointing to this post:', 'seo-simple' ) . ' ' . $post['link_count'] . '</p>';
			echo '<a href="' . get_edit_post_link( $post['post_id'] ) . '" class="button">' . __( 'Edit Post', 'seo-simple' ) . '</a>';
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * Render the internal links interface for the meta box.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 */
	public function render_meta_box_interface( $post_id ) {
		// Get internal link suggestions
		$suggestions = $this->internal_links->get_internal_link_suggestions( $post_id );
		
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/internal-links-meta-box.php';
	}

	/**
	 * Render the internal links dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function render_dashboard_widget() {
		// Get orphaned posts
		$orphaned_posts = $this->internal_links->get_orphaned_posts( 5 );
		
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/internal-links-dashboard-widget.php';
	}

	/**
	 * Render the internal links page.
	 *
	 * @since    1.0.0
	 */
	public function render_internal_links_page() {
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/internal-links-settings.php';
	}

	/**
	 * Render the internal links settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		// Include the template
		include plugin_dir_path( __FILE__ ) . 'partials/internal-links-settings.php';
	}

	/**
	 * Register the internal links admin menu.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'seo-simple',
			__( 'Internal Links', 'seo-simple' ),
			__( 'Internal Links', 'seo-simple' ),
			'manage_options',
			'seo-simple-internal-links',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the internal links dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {
		wp_add_dashboard_widget(
			'seo_simple_internal_links_dashboard',
			__( 'Internal Links', 'seo-simple' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'seo-simple-internal-links-admin',
			plugin_dir_url( __FILE__ ) . 'js/seo-simple-internal-links.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
		
		wp_enqueue_style(
			'seo-simple-internal-links-admin',
			plugin_dir_url( __FILE__ ) . 'css/seo-simple-internal-links.css',
			array(),
			SEO_SIMPLE_VERSION
		);
		
		// Localize script
		wp_localize_script(
			'seo-simple-internal-links-admin',
			'seo_simple_internal_links',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'seo_simple_internal_links_nonce' ),
				'inserting_text' => __( 'Inserting link...', 'seo-simple' ),
				'link_inserted_text' => __( 'Link inserted', 'seo-simple' ),
				'error_text' => __( 'Error occurred', 'seo-simple' ),
			)
		);
	}

	/**
	 * Handle AJAX requests for internal links admin.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_requests() {
		// AJAX handlers are already registered in the constructor
		// This method is added for consistency with other admin classes
	}
}
