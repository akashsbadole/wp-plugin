<?php

/**
 * Handles speed optimization features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles speed optimization features.
 *
 * This class is responsible for implementing various speed optimization
 * techniques such as caching, minification, lazy loading, and more.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Speed_Optimization {

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
	 * @param    Seo_Simple_Security           $security       The security handler instance.
	 * @param    Seo_Simple_Cache_Manager      $cache_manager  The cache handler instance.
	 */
	public function __construct( $security = null, $cache_manager = null ) {
		$this->security = $security;
		$this->cache_manager = $cache_manager;
	}

	/**
	 * Register hooks for speed optimization.
	 *
	 * @since    1.0.0
	 */
	public function register_hooks() {
		// Add settings tab
		add_filter( 'seo_simple_settings_tabs', array( $this, 'add_settings_tab' ) );
		
		// Add settings fields
		add_action( 'seo_simple_settings_tab_content_speed', array( $this, 'render_settings_tab' ) );
		
		// Add AJAX handlers
		add_action( 'wp_ajax_seo_simple_save_speed_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_seo_simple_clear_speed_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_seo_simple_run_speed_test', array( $this, 'ajax_run_speed_test' ) );
		
		// Add admin menu items
		add_action( 'admin_menu', array( $this, 'add_admin_menu_items' ) );
		
		// Add speed optimization features
		add_action( 'init', array( $this, 'init_speed_optimization' ) );
		
		// Add lazy loading
		add_filter( 'the_content', array( $this, 'add_lazy_loading' ) );
		
		// Add minification
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_minified_assets' ), 999 );
		
		// Add GZIP compression
		add_action( 'init', array( $this, 'enable_gzip_compression' ) );
		
		// Add browser caching
		add_action( 'send_headers', array( $this, 'add_browser_caching_headers' ) );
		
		// Add database cleanup
		add_action( 'wp', array( $this, 'schedule_database_cleanup' ) );
		add_action( 'seo_simple_database_cleanup', array( $this, 'perform_database_cleanup' ) );
		
		// Add preconnect to external resources
		add_action( 'wp_head', array( $this, 'add_preconnect_headers' ) );
		
		// Add critical CSS
		add_action( 'wp_head', array( $this, 'add_critical_css' ) );
		
		// Add DNS prefetch
		add_action( 'wp_head', array( $this, 'add_dns_prefetch' ) );
	}

	/**
	 * Add settings tab.
	 *
	 * @since    1.0.0
	 * @param    array     $tabs    The existing tabs.
	 * @return   array              The modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['speed'] = array(
			'label' => __( 'Speed', 'seo-simple' ),
			'icon' => 'dashicons-performance',
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
		
		// Set default values
		$page_caching = isset( $settings['speed_page_caching'] ) ? $settings['speed_page_caching'] : false;
		$browser_caching = isset( $settings['speed_browser_caching'] ) ? $settings['speed_browser_caching'] : true;
		$gzip_compression = isset( $settings['speed_gzip_compression'] ) ? $settings['speed_gzip_compression'] : true;
		$minify_css = isset( $settings['speed_minify_css'] ) ? $settings['speed_minify_css'] : false;
		$minify_js = isset( $settings['speed_minify_js'] ) ? $settings['speed_minify_js'] : false;
		$minify_html = isset( $settings['speed_minify_html'] ) ? $settings['speed_minify_html'] : false;
		$lazy_loading = isset( $settings['speed_lazy_loading'] ) ? $settings['speed_lazy_loading'] : true;
		$database_cleanup = isset( $settings['speed_database_cleanup'] ) ? $settings['speed_database_cleanup'] : true;
		$cleanup_interval = isset( $settings['speed_cleanup_interval'] ) ? $settings['speed_cleanup_interval'] : 'weekly';
		$combine_css = isset( $settings['speed_combine_css'] ) ? $settings['speed_combine_css'] : false;
		$combine_js = isset( $settings['speed_combine_js'] ) ? $settings['speed_combine_js'] : false;
		$defer_js = isset( $settings['speed_defer_js'] ) ? $settings['speed_defer_js'] : false;
		$async_css = isset( $settings['speed_async_css'] ) ? $settings['speed_async_css'] : false;
		$remove_query_strings = isset( $settings['speed_remove_query_strings'] ) ? $settings['speed_remove_query_strings'] : true;
		$optimize_images = isset( $settings['speed_optimize_images'] ) ? $settings['speed_optimize_images'] : true;
		$webp_support = isset( $settings['speed_webp_support'] ) ? $settings['speed_webp_support'] : false;
		$local_google_fonts = isset( $settings['speed_local_google_fonts'] ) ? $settings['speed_local_google_fonts'] : false;
		
		include_once dirname( __FILE__ ) . '/../admin/partials/speed-settings-page.php';
	}

	/**
	 * Add admin menu items.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu_items() {
		add_submenu_page(
			'seo-simple-settings',
			__( 'Speed Optimization', 'seo-simple' ),
			__( 'Speed', 'seo-simple' ),
			'manage_options',
			'seo-simple-speed',
			array( $this, 'render_speed_page' )
		);
	}

	/**
	 * Render speed optimization page.
	 *
	 * @since    1.0.0
	 */
	public function render_speed_page() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Get speed test results
		$speed_test_results = get_option( 'seo_simple_speed_test_results', array() );
		
		// Get cache statistics
		$cache_stats = $this->get_cache_statistics();
		
		include_once dirname( __FILE__ ) . '/../admin/partials/speed-optimization-page.php';
	}

	/**
	 * Initialize speed optimization features.
	 *
	 * @since    1.0.0
	 */
	public function init_speed_optimization() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Remove query strings from static resources
		if ( isset( $settings['speed_remove_query_strings'] ) && $settings['speed_remove_query_strings'] ) {
			add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ) );
			add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ) );
		}
		
		// Optimize images
		if ( isset( $settings['speed_optimize_images'] ) && $settings['speed_optimize_images'] ) {
			add_filter( 'wp_generate_attachment_metadata', array( $this, 'optimize_images' ), 10, 2 );
		}
		
		// Add WebP support
		if ( isset( $settings['speed_webp_support'] ) && $settings['speed_webp_support'] ) {
			add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_webp_images' ), 10, 2 );
			add_filter( 'wp_get_attachment_url', array( $this, 'serve_webp_images' ), 10, 2 );
		}
		
		// Local Google Fonts
		if ( isset( $settings['speed_local_google_fonts'] ) && $settings['speed_local_google_fonts'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'localize_google_fonts' ), 999 );
		}
	}

	/**
	 * Add lazy loading to images.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The post content.
	 * @return   string                The modified content.
	 */
	public function add_lazy_loading( $content ) {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['speed_lazy_loading'] ) || ! $settings['speed_lazy_loading'] ) {
			return $content;
		}
		
		// Add loading="lazy" to images
		$content = preg_replace( '/<img([^>]+)src=([\'"])([^\'">]+)\2([^>]*)>/i', '<img$1src=$2$3$2 loading="lazy"$4>', $content );
		
		return $content;
	}

	/**
	 * Enqueue minified assets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_minified_assets() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Minify CSS
		if ( isset( $settings['speed_minify_css'] ) && $settings['speed_minify_css'] ) {
			add_filter( 'style_loader_tag', array( $this, 'minify_css_url' ), 10, 4 );
		}
		
		// Minify JS
		if ( isset( $settings['speed_minify_js'] ) && $settings['speed_minify_js'] ) {
			add_filter( 'script_loader_tag', array( $this, 'minify_js_url' ), 10, 3 );
		}
		
		// Combine CSS
		if ( isset( $settings['speed_combine_css'] ) && $settings['speed_combine_css'] ) {
			add_action( 'wp_print_styles', array( $this, 'combine_css_files' ) );
		}
		
		// Combine JS
		if ( isset( $settings['speed_combine_js'] ) && $settings['speed_combine_js'] ) {
			add_action( 'wp_print_scripts', array( $this, 'combine_js_files' ) );
		}
		
		// Defer JS
		if ( isset( $settings['speed_defer_js'] ) && $settings['speed_defer_js'] ) {
			add_filter( 'script_loader_tag', array( $this, 'defer_js' ), 10, 3 );
		}
		
		// Async CSS
		if ( isset( $settings['speed_async_css'] ) && $settings['speed_async_css'] ) {
			add_filter( 'style_loader_tag', array( $this, 'async_css' ), 10, 4 );
		}
	}

	/**
	 * Enable GZIP compression.
	 *
	 * @since    1.0.0
	 */
	public function enable_gzip_compression() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['speed_gzip_compression'] ) || ! $settings['speed_gzip_compression'] ) {
			return;
		}
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Check if GZIP is already enabled
		if ( ini_get( 'zlib.output_compression' ) ) {
			return;
		}
		
		// Enable GZIP compression if not already enabled
		if ( ! ob_get_level() ) {
			ob_start( 'ob_gzhandler' );
		}
	}

	/**
	 * Add browser caching headers.
	 *
	 * @since    1.0.0
	 */
	public function add_browser_caching_headers() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['speed_browser_caching'] ) || ! $settings['speed_browser_caching'] ) {
			return;
		}
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Set cache headers for static files
		$expires = 60 * 60 * 24 * 30; // 30 days
		
		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=' . $expires . ', public' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires ) . ' GMT' );
	}

	/**
	 * Schedule database cleanup.
	 *
	 * @since    1.0.0
	 */
	public function schedule_database_cleanup() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		if ( ! isset( $settings['speed_database_cleanup'] ) || ! $settings['speed_database_cleanup'] ) {
			return;
		}
		
		$interval = isset( $settings['speed_cleanup_interval'] ) ? $settings['speed_cleanup_interval'] : 'weekly';
		
		// Schedule the event if not already scheduled
		if ( ! wp_next_scheduled( 'seo_simple_database_cleanup' ) ) {
			wp_schedule_event( time(), $interval, 'seo_simple_database_cleanup' );
		}
	}

	/**
	 * Perform database cleanup.
	 *
	 * @since    1.0.0
	 */
	public function perform_database_cleanup() {
		global $wpdb;
		
		// Clean up post revisions
		$wpdb->query(
			"DELETE FROM {$wpdb->posts} WHERE post_type = 'revision' AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);
		
		// Clean up spam comments
		$wpdb->query(
			"DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam' AND comment_date < DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);
		
		// Clean up trashed posts
		$wpdb->query(
			"DELETE FROM {$wpdb->posts} WHERE post_status = 'trash' AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);
		
		// Clean up expired transients
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_value < DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);
		
		// Optimize tables
		$tables = array( $wpdb->posts, $wpdb->comments, $wpdb->options, $wpdb->postmeta, $wpdb->commentmeta );
		
		foreach ( $tables as $table ) {
			$wpdb->query( "OPTIMIZE TABLE $table" );
		}
	}

	/**
	 * Add preconnect headers.
	 *
	 * @since    1.0.0
	 */
	public function add_preconnect_headers() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Preconnect to Google Fonts
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		
		// Preconnect to common CDNs
		echo '<link rel="preconnect" href="https://ajax.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://www.google-analytics.com">' . "\n";
		echo '<link rel="preconnect" href="https://www.googletagmanager.com">' . "\n";
	}

	/**
	 * Add critical CSS.
	 *
	 * @since    1.0.0
	 */
	public function add_critical_css() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Get critical CSS from cache or generate it
		$critical_css = $this->get_critical_css();
		
		if ( $critical_css ) {
			echo '<style id="seo-simple-critical-css">' . $critical_css . '</style>' . "\n";
			echo '<script>document.addEventListener("DOMContentLoaded",function(){var a=document.getElementById("seo-simple-critical-css");a&&a.parentNode.removeChild(a)});</script>' . "\n";
		}
	}

	/**
	 * Add DNS prefetch.
	 *
	 * @since    1.0.0
	 */
	public function add_dns_prefetch() {
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// DNS prefetch for external domains
		echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
		echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
		echo '<link rel="dns-prefetch" href="//connect.facebook.net">' . "\n";
		echo '<link rel="dns-prefetch" href="//platform.twitter.com">' . "\n";
	}

	/**
	 * Get critical CSS.
	 *
	 * @since    1.0.0
	 * @return   string    The critical CSS.
	 */
	private function get_critical_css() {
		// Check if we have cached critical CSS
		$cached_css = get_transient( 'seo_simple_critical_css' );
		
		if ( $cached_css ) {
			return $cached_css;
		}
		
		// Generate critical CSS based on current page
		$critical_css = $this->generate_critical_css();
		
		// Cache for 24 hours
		set_transient( 'seo_simple_critical_css', $critical_css, 24 * HOUR_IN_SECONDS );
		
		return $critical_css;
	}

	/**
	 * Generate critical CSS.
	 *
	 * @since    1.0.0
	 * @return   string    The generated critical CSS.
	 */
	private function generate_critical_css() {
		// This is a simplified implementation
		// In a real implementation, you would use a service like Critical CSS generator
		// or a library to extract critical CSS from the page
		
		$critical_css = '
		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			max-width: 100%;
			margin: 0;
			padding: 0;
		}
		
		#header, #nav, #content {
			opacity: 1;
		}
		
		img {
			max-width: 100%;
			height: auto;
		}
		';
		
		return $critical_css;
	}

	/**
	 * Remove query strings from static resources.
	 *
	 * @since    1.0.0
	 * @param    string    $src    The resource URL.
	 * @return   string            The modified URL.
	 */
	public function remove_query_strings( $src ) {
		if ( strpos( $src, '?ver=' ) !== false ) {
			$src = remove_query_arg( 'ver', $src );
		}
		
		return $src;
	}

	/**
	 * Optimize images.
	 *
	 * @since    1.0.0
	 * @param    array     $metadata      The attachment metadata.
	 * @param    int       $attachment_id The attachment ID.
	 * @return   array                    The modified metadata.
	 */
	public function optimize_images( $metadata, $attachment_id ) {
		// Get image path
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
		
		// Optimize main image
		$this->optimize_image_file( $file_path );
		
		// Optimize thumbnails
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$size_path = dirname( $file_path ) . '/' . $size_data['file'];
				if ( file_exists( $size_path ) ) {
					$this->optimize_image_file( $size_path );
				}
			}
		}
		
		return $metadata;
	}

	/**
	 * Optimize an image file.
	 *
	 * @since    1.0.0
	 * @param    string    $file_path    The file path.
	 */
	private function optimize_image_file( $file_path ) {
		// Check if image optimizer is available
		if ( ! function_exists( 'imagepng' ) ) {
			return;
		}
		
		// Get image info
		$image_info = getimagesize( $file_path );
		
		if ( ! $image_info ) {
			return;
		}
		
		$mime_type = $image_info['mime'];
		
		// Optimize based on mime type
		switch ( $mime_type ) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg( $file_path );
				imagejpeg( $image, $file_path, 85 );
				imagedestroy( $image );
				break;
				
			case 'image/png':
				$image = imagecreatefrompng( $file_path );
				imagepng( $image, $file_path, 9 );
				imagedestroy( $image );
				break;
		}
	}

	/**
	 * Generate WebP images.
	 *
	 * @since    1.0.0
	 * @param    array     $metadata      The attachment metadata.
	 * @param    int       $attachment_id The attachment ID.
	 * @return   array                    The modified metadata.
	 */
	public function generate_webp_images( $metadata, $attachment_id ) {
		// Check if GD supports WebP
		if ( ! function_exists( 'imagewebp' ) ) {
			return $metadata;
		}
		
		// Get image path
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
		
		// Generate WebP for main image
		$this->generate_webp_file( $file_path );
		
		// Generate WebP for thumbnails
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$size_path = dirname( $file_path ) . '/' . $size_data['file'];
				if ( file_exists( $size_path ) ) {
					$this->generate_webp_file( $size_path );
				}
			}
		}
		
		return $metadata;
	}

	/**
	 * Generate a WebP file.
	 *
	 * @since    1.0.0
	 * @param    string    $file_path    The file path.
	 */
	private function generate_webp_file( $file_path ) {
		// Get image info
		$image_info = getimagesize( $file_path );
		
		if ( ! $image_info ) {
			return;
		}
		
		$mime_type = $image_info['mime'];
		$webp_path = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file_path );
		
		// Generate WebP based on mime type
		switch ( $mime_type ) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg( $file_path );
				imagewebp( $image, $webp_path, 85 );
				imagedestroy( $image );
				break;
				
			case 'image/png':
				$image = imagecreatefrompng( $file_path );
				imagewebp( $image, $webp_path, 9 );
				imagedestroy( $image );
				break;
		}
	}

	/**
	 * Serve WebP images.
	 *
	 * @since    1.0.0
	 * @param    string    $url          The image URL.
	 * @param    int       $attachment_id The attachment ID.
	 * @return   string                  The modified URL.
	 */
	public function serve_webp_images( $url, $attachment_id ) {
		// Check if browser supports WebP
		if ( ! isset( $_SERVER['HTTP_ACCEPT'] ) || strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) === false ) {
			return $url;
		}
		
		// Get file path
		$upload_dir = wp_upload_dir();
		$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
		$webp_path = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file_path );
		
		// Check if WebP file exists
		if ( file_exists( $webp_path ) ) {
			$webp_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $webp_path );
			return $webp_url;
		}
		
		return $url;
	}

	/**
	 * Localize Google Fonts.
	 *
	 * @since    1.0.0
	 */
	public function localize_google_fonts() {
		global $wp_styles;
		
		// Find Google Fonts
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[$handle];
			
			if ( $style && strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				// Download and localize the font
				$this->download_google_font( $style->src, $handle );
				
				// Update the src to local file
				$local_src = $this->get_local_google_font_url( $style->src, $handle );
				if ( $local_src ) {
					$wp_styles->registered[$handle]->src = $local_src;
				}
			}
		}
	}

	/**
	 * Download Google Font.
	 *
	 * @since    1.0.0
	 * @param    string    $url     The font URL.
	 * @param    string    $handle  The font handle.
	 */
	private function download_google_font( $url, $handle ) {
		$upload_dir = wp_upload_dir();
		$font_dir = $upload_dir['basedir'] . '/seo-simple-fonts/';
		
		// Create directory if it doesn't exist
		if ( ! file_exists( $font_dir ) ) {
			wp_mkdir_p( $font_dir );
		}
		
		// Get font file name
		$font_file = $handle . '.css';
		$font_path = $font_dir . $font_file;
		
		// Check if font already exists
		if ( file_exists( $font_path ) && time() - filemtime( $font_path ) < WEEK_IN_SECONDS ) {
			return;
		}
		
		// Download font
		$response = wp_remote_get( $url );
		
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$css = wp_remote_retrieve_body( $response );
			
			// Process CSS to download font files
			$css = $this->process_google_font_css( $css, $font_dir );
			
			// Save CSS file
			file_put_contents( $font_path, $css );
		}
	}

	/**
	 * Process Google Font CSS.
	 *
	 * @since    1.0.0
	 * @param    string    $css       The CSS content.
	 * @param    string    $font_dir  The font directory.
	 * @return   string               The processed CSS.
	 */
	private function process_google_font_css( $css, $font_dir ) {
		// Find font URLs
		preg_match_all( '/url\(([^)]+)\)/', $css, $matches );
		
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $font_url ) {
				// Download font file
				$response = wp_remote_get( $font_url );
				
				if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
					$font_content = wp_remote_retrieve_body( $response );
					$font_file = basename( $font_url );
					$font_path = $font_dir . $font_file;
					
					// Save font file
					file_put_contents( $font_path, $font_content );
					
					// Update CSS to use local font
					$css = str_replace( $font_url, $font_file, $css );
				}
			}
		}
		
		return $css;
	}

	/**
	 * Get local Google Font URL.
	 *
	 * @since    1.0.0
	 * @param    string    $url     The original font URL.
	 * @param    string    $handle  The font handle.
	 * @return   string|false       The local URL or false if not found.
	 */
	private function get_local_google_font_url( $url, $handle ) {
		$upload_dir = wp_upload_dir();
		$font_file = 'seo-simple-fonts/' . $handle . '.css';
		$font_path = $upload_dir['basedir'] . '/' . $font_file;
		
		if ( file_exists( $font_path ) ) {
			return $upload_dir['baseurl'] . '/' . $font_file;
		}
		
		return false;
	}

	/**
	 * Minify CSS URL.
	 *
	 * @since    1.0.0
	 * @param    string    $html    The HTML link tag.
	 * @param    string    $handle  The style handle.
	 * @param    string    $href    The stylesheet URL.
	 * @param    string    $media   The media attribute.
	 * @return   string             The modified HTML.
	 */
	public function minify_css_url( $html, $handle, $href, $media ) {
		// Check if file exists
		$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $href );
		
		if ( file_exists( $file_path ) ) {
			$minified_path = preg_replace( '/\.css$/', '.min.css', $file_path );
			
			// Create minified file if it doesn't exist
			if ( ! file_exists( $minified_path ) ) {
				$css = file_get_contents( $file_path );
				$minified_css = $this->minify_css( $css );
				file_put_contents( $minified_path, $minified_css );
			}
			
			// Update href to minified file
			$minified_url = str_replace( '.css', '.min.css', $href );
			$html = str_replace( $href, $minified_url, $html );
		}
		
		return $html;
	}

	/**
	 * Minify JS URL.
	 *
	 * @since    1.0.0
	 * @param    string    $tag     The script tag.
	 * @param    string    $handle  The script handle.
	 * @param    string    $src     The script URL.
	 * @return   string             The modified tag.
	 */
	public function minify_js_url( $tag, $handle, $src ) {
		// Check if file exists
		$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $src );
		
		if ( file_exists( $file_path ) ) {
			$minified_path = preg_replace( '/\.js$/', '.min.js', $file_path );
			
			// Create minified file if it doesn't exist
			if ( ! file_exists( $minified_path ) ) {
				$js = file_get_contents( $file_path );
				$minified_js = $this->minify_js( $js );
				file_put_contents( $minified_path, $minified_js );
			}
			
			// Update src to minified file
			$minified_url = str_replace( '.js', '.min.js', $src );
			$tag = str_replace( $src, $minified_url, $tag );
		}
		
		return $tag;
	}

	/**
	 * Minify CSS.
	 *
	 * @since    1.0.0
	 * @param    string    $css    The CSS content.
	 * @return   string            The minified CSS.
	 */
	private function minify_css( $css ) {
		// Remove comments
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		
		// Remove whitespace
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );
		
		return $css;
	}

	/**
	 * Minify JS.
	 *
	 * @since    1.0.0
	 * @param    string    $js    The JavaScript content.
	 * @return   string            The minified JavaScript.
	 */
	private function minify_js( $js ) {
		// Remove comments
		$js = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js );
		
		// Remove whitespace
		$js = preg_replace( '/\s+/', ' ', $js );
		
		return $js;
	}

	/**
	 * Combine CSS files.
	 *
	 * @since    1.0.0
	 */
	public function combine_css_files() {
		global $wp_styles;
		
		// Get all enqueued styles
		$styles = array();
		
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[$handle];
			
			if ( $style && ! empty( $style->src ) ) {
				$styles[$handle] = $style;
			}
		}
		
		if ( empty( $styles ) ) {
			return;
		}
		
		// Generate combined filename
		$hash = md5( serialize( array_keys( $styles ) ) );
		$combined_filename = 'seo-simple-combined-' . $hash . '.css';
		
		$upload_dir = wp_upload_dir();
		$combined_path = $upload_dir['basedir'] . '/seo-simple-cache/' . $combined_filename;
		$combined_url = $upload_dir['baseurl'] . '/seo-simple-cache/' . $combined_filename;
		
		// Create cache directory if it doesn't exist
		if ( ! file_exists( dirname( $combined_path ) ) ) {
			wp_mkdir_p( dirname( $combined_path ) );
		}
		
		// Check if combined file exists
		if ( ! file_exists( $combined_path ) ) {
			$combined_css = '';
			
			// Combine all CSS
			foreach ( $styles as $handle => $style ) {
				$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $style->src );
				
				if ( file_exists( $file_path ) ) {
					$css = file_get_contents( $file_path );
					$combined_css .= "/* $handle */\n" . $css . "\n\n";
				}
			}
			
			// Save combined file
			file_put_contents( $combined_path, $combined_css );
		}
		
		// Dequeue all styles and enqueue combined
		foreach ( array_keys( $styles ) as $handle ) {
			wp_dequeue_style( $handle );
		}
		
		wp_enqueue_style( 'seo-simple-combined-css', $combined_url, array(), null );
	}

	/**
	 * Combine JS files.
	 *
	 * @since    1.0.0
	 */
	public function combine_js_files() {
		global $wp_scripts;
		
		// Get all enqueued scripts
		$scripts = array();
		
		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[$handle];
			
			if ( $script && ! empty( $script->src ) ) {
				$scripts[$handle] = $script;
			}
		}
		
		if ( empty( $scripts ) ) {
			return;
		}
		
		// Generate combined filename
		$hash = md5( serialize( array_keys( $scripts ) ) );
		$combined_filename = 'seo-simple-combined-' . $hash . '.js';
		
		$upload_dir = wp_upload_dir();
		$combined_path = $upload_dir['basedir'] . '/seo-simple-cache/' . $combined_filename;
		$combined_url = $upload_dir['baseurl'] . '/seo-simple-cache/' . $combined_filename;
		
		// Create cache directory if it doesn't exist
		if ( ! file_exists( dirname( $combined_path ) ) ) {
			wp_mkdir_p( dirname( $combined_path ) );
		}
		
		// Check if combined file exists
		if ( ! file_exists( $combined_path ) ) {
			$combined_js = '';
			
			// Combine all JS
			foreach ( $scripts as $handle => $script ) {
				$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $script->src );
				
				if ( file_exists( $file_path ) ) {
					$js = file_get_contents( $file_path );
					$combined_js .= "/* $handle */\n" . $js . "\n\n";
				}
			}
			
			// Save combined file
			file_put_contents( $combined_path, $combined_js );
		}
		
		// Dequeue all scripts and enqueue combined
		foreach ( array_keys( $scripts ) as $handle ) {
			wp_dequeue_script( $handle );
		}
		
		wp_enqueue_script( 'seo-simple-combined-js', $combined_url, array(), null, true );
	}

	/**
	 * Defer JavaScript.
	 *
	 * @since    1.0.0
	 * @param    string    $tag     The script tag.
	 * @param    string    $handle  The script handle.
	 * @param    string    $src     The script URL.
	 * @return   string             The modified tag.
	 */
	public function defer_js( $tag, $handle, $src ) {
		// Skip jQuery and other critical scripts
		if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ) ) ) {
			return $tag;
		}
		
		// Add defer attribute
		if ( strpos( $tag, 'defer' ) === false ) {
			$tag = str_replace( '<script ', '<script defer ', $tag );
		}
		
		return $tag;
	}

	/**
	 * Async CSS.
	 *
	 * @since    1.0.0
	 * @param    string    $html    The HTML link tag.
	 * @param    string    $handle  The style handle.
	 * @param    string    $href    The stylesheet URL.
	 * @param    string    $media   The media attribute.
	 * @return   string             The modified HTML.
	 */
	public function async_css( $html, $handle, $href, $media ) {
		// Add media="print" and onload to make CSS non-blocking
		if ( strpos( $html, 'media=' ) === false ) {
			$html = str_replace( '<link', '<link media="print" onload="this.media=\'all\'"', $html );
		}
		
		return $html;
	}

	/**
	 * AJAX handler for saving settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_settings() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_save_speed_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to save these settings.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Sanitize and save settings
		$settings = get_option( 'seo_simple_settings', array() );
		
		// Page caching
		$settings['speed_page_caching'] = isset( $_POST['speed_page_caching'] ) ? (bool) $_POST['speed_page_caching'] : false;
		
		// Browser caching
		$settings['speed_browser_caching'] = isset( $_POST['speed_browser_caching'] ) ? (bool) $_POST['speed_browser_caching'] : false;
		
		// GZIP compression
		$settings['speed_gzip_compression'] = isset( $_POST['speed_gzip_compression'] ) ? (bool) $_POST['speed_gzip_compression'] : false;
		
		// Minification
		$settings['speed_minify_css'] = isset( $_POST['speed_minify_css'] ) ? (bool) $_POST['speed_minify_css'] : false;
		$settings['speed_minify_js'] = isset( $_POST['speed_minify_js'] ) ? (bool) $_POST['speed_minify_js'] : false;
		$settings['speed_minify_html'] = isset( $_POST['speed_minify_html'] ) ? (bool) $_POST['speed_minify_html'] : false;
		
		// Lazy loading
		$settings['speed_lazy_loading'] = isset( $_POST['speed_lazy_loading'] ) ? (bool) $_POST['speed_lazy_loading'] : false;
		
		// Database cleanup
		$settings['speed_database_cleanup'] = isset( $_POST['speed_database_cleanup'] ) ? (bool) $_POST['speed_database_cleanup'] : false;
		$settings['speed_cleanup_interval'] = isset( $_POST['speed_cleanup_interval'] ) ? sanitize_text_field( $_POST['speed_cleanup_interval'] ) : 'weekly';
		
		// Combine files
		$settings['speed_combine_css'] = isset( $_POST['speed_combine_css'] ) ? (bool) $_POST['speed_combine_css'] : false;
		$settings['speed_combine_js'] = isset( $_POST['speed_combine_js'] ) ? (bool) $_POST['speed_combine_js'] : false;
		
		// Defer/Async
		$settings['speed_defer_js'] = isset( $_POST['speed_defer_js'] ) ? (bool) $_POST['speed_defer_js'] : false;
		$settings['speed_async_css'] = isset( $_POST['speed_async_css'] ) ? (bool) $_POST['speed_async_css'] : false;
		
		// Remove query strings
		$settings['speed_remove_query_strings'] = isset( $_POST['speed_remove_query_strings'] ) ? (bool) $_POST['speed_remove_query_strings'] : false;
		
		// Image optimization
		$settings['speed_optimize_images'] = isset( $_POST['speed_optimize_images'] ) ? (bool) $_POST['speed_optimize_images'] : false;
		$settings['speed_webp_support'] = isset( $_POST['speed_webp_support'] ) ? (bool) $_POST['speed_webp_support'] : false;
		
		// Google Fonts
		$settings['speed_local_google_fonts'] = isset( $_POST['speed_local_google_fonts'] ) ? (bool) $_POST['speed_local_google_fonts'] : false;
		
		// Update settings
		update_option( 'seo_simple_settings', $settings );
		
		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for clearing cache.
	 *
	 * @since    1.0.0
	 */
	public function ajax_clear_cache() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to clear cache.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Clear various caches
		$this->clear_all_caches();
		
		wp_send_json_success( array( 'message' => __( 'Cache cleared successfully.', 'seo-simple' ) ) );
	}

	/**
	 * AJAX handler for running speed test.
	 *
	 * @since    1.0.0
	 */
	public function ajax_run_speed_test() {
		// Verify nonce for security
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'seo_simple_speed_optimization_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security verification failed.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to run speed test.', 'seo-simple' ) ) );
			wp_die();
		}
		
		// Run speed test
		$results = $this->run_speed_test();
		
		// Save results
		update_option( 'seo_simple_speed_test_results', $results );
		
		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Clear all caches.
	 *
	 * @since    1.0.0
	 */
	private function clear_all_caches() {
		// Clear WordPress cache
		wp_cache_flush();
		
		// Clear our transients
		delete_transient( 'seo_simple_critical_css' );
		
		// Clear combined files
		$upload_dir = wp_upload_dir();
		$cache_dir = $upload_dir['basedir'] . '/seo-simple-cache/';
		
		if ( file_exists( $cache_dir ) ) {
			$files = glob( $cache_dir . '*' );
			
			if ( $files ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						unlink( $file );
					}
				}
			}
		}
		
		// Clear minified files
		$files = glob( WP_CONTENT_DIR . '/*.min.css' );
		
		if ( $files ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}
		
		$files = glob( WP_CONTENT_DIR . '/*.min.js' );
		
		if ( $files ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	 * Get cache statistics.
	 *
	 * @since    1.0.0
	 * @return   array     The cache statistics.
	 */
	public function get_cache_statistics() {
		$stats = array();
		
		// Get WordPress cache stats
		if ( function_exists( 'wp_cache_get_stats' ) ) {
			$stats['wp_cache'] = wp_cache_get_stats();
		}
		
		// Get combined files count
		$upload_dir = wp_upload_dir();
		$cache_dir = $upload_dir['basedir'] . '/seo-simple-cache/';
		
		if ( file_exists( $cache_dir ) ) {
			$files = glob( $cache_dir . '*' );
			$stats['combined_files'] = count( $files );
		} else {
			$stats['combined_files'] = 0;
		}
		
		// Get minified files count
		$css_files = glob( WP_CONTENT_DIR . '/*.min.css' );
		$js_files = glob( WP_CONTENT_DIR . '/*.min.js' );
		$stats['minified_files'] = count( $css_files ) + count( $js_files );
		
		return $stats;
	}

	/**
	 * Run speed test.
	 *
	 * @since    1.0.0
	 * @return   array     The speed test results.
	 */
	public function run_speed_test() {
		$results = array();
		
		// Test page load time
		$start_time = microtime( true );
		
		// Make a request to the homepage
		$response = wp_remote_get( home_url() );
		
		$end_time = microtime( true );
		$load_time = $end_time - $start_time;
		
		$results['page_load_time'] = round( $load_time, 3 );
		
		// Test page size
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$results['page_size'] = strlen( $body );
			$results['page_size_formatted'] = size_format( strlen( $body ) );
		}
		
		// Test number of requests
		$results['requests'] = 1; // Simplified
		
		// Test cache status
		$results['cache_enabled'] = wp_using_ext_object_cache() ? 'Yes' : 'No';
		
		// Test GZIP compression
		$results['gzip_enabled'] = ini_get( 'zlib.output_compression' ) ? 'Yes' : 'No';
		
		// Test image optimization
		$results['images_optimized'] = $this->count_optimized_images();
		
		return $results;
	}

	/**
	 * Count optimized images.
	 *
	 * @since    1.0.0
	 * @return   int       The number of optimized images.
	 */
	private function count_optimized_images() {
		global $wpdb;
		
		// Count images with WebP versions
		$upload_dir = wp_upload_dir();
		$count = 0;
		
		$images = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%.jpg' OR meta_value LIKE '%.jpeg' OR meta_value LIKE '%.png'" );
		
		foreach ( $images as $image ) {
			$image_path = $upload_dir['basedir'] . '/' . $image;
			$webp_path = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $image_path );
			
			if ( file_exists( $webp_path ) ) {
				$count++;
			}
		}
		
		return $count;
	}

	/**
		* Test speed optimization features.
		*
		* @since    1.0.0
		* @return   array     Test results.
		*/
	public function test_features() {
		$results = array();
		
		// Test page caching
		$results['page_caching'] = array(
			'enabled' => get_option( 'seo_simple_page_cache_enabled', false ),
			'status' => get_option( 'seo_simple_page_cache_enabled', false ) ? 'Active' : 'Inactive',
			'description' => 'Page caching creates static HTML files for faster loading.'
		);
		
		// Test browser caching
		$results['browser_caching'] = array(
			'enabled' => get_option( 'seo_simple_browser_cache_enabled', true ),
			'status' => get_option( 'seo_simple_browser_cache_enabled', true ) ? 'Active' : 'Inactive',
			'description' => 'Browser caching stores resources on visitors\' devices for faster repeat visits.'
		);
		
		// Test GZIP compression
		$results['gzip_compression'] = array(
			'enabled' => ini_get( 'zlib.output_compression' ) || get_option( 'seo_simple_gzip_enabled', true ),
			'status' => ( ini_get( 'zlib.output_compression' ) || get_option( 'seo_simple_gzip_enabled', true ) ) ? 'Active' : 'Inactive',
			'description' => 'GZIP compression reduces file sizes for faster downloads.'
		);
		
		// Test minification
		$minify_css = get_option( 'seo_simple_minify_css', false );
		$minify_js = get_option( 'seo_simple_minify_js', false );
		$minify_html = get_option( 'seo_simple_minify_html', false );
		
		$results['minification'] = array(
			'enabled' => $minify_css || $minify_js || $minify_html,
			'status' => ( $minify_css || $minify_js || $minify_html ) ? 'Active' : 'Inactive',
			'description' => 'Minification removes unnecessary characters from code to reduce file sizes.',
			'details' => array(
				'css' => $minify_css ? 'Enabled' : 'Disabled',
				'js' => $minify_js ? 'Enabled' : 'Disabled',
				'html' => $minify_html ? 'Enabled' : 'Disabled'
			)
		);
		
		// Test lazy loading
		$results['lazy_loading'] = array(
			'enabled' => get_option( 'seo_simple_lazy_loading', true ),
			'status' => get_option( 'seo_simple_lazy_loading', true ) ? 'Active' : 'Inactive',
			'description' => 'Lazy loading delays loading of images until they are visible.'
		);
		
		// Test image optimization
		$results['image_optimization'] = array(
			'enabled' => get_option( 'seo_simple_optimize_images', true ),
			'status' => get_option( 'seo_simple_optimize_images', true ) ? 'Active' : 'Inactive',
			'description' => 'Image optimization reduces file sizes without compromising quality.',
			'optimized_count' => $this->count_optimized_images()
		);
		
		// Test WebP support
		$results['webp_support'] = array(
			'enabled' => get_option( 'seo_simple_webp_support', false ) && function_exists( 'imagewebp' ),
			'status' => ( get_option( 'seo_simple_webp_support', false ) && function_exists( 'imagewebp' ) ) ? 'Active' : 'Inactive',
			'description' => 'WebP is a modern image format that provides superior compression.',
			'webp_count' => $this->count_webp_images()
		);
		
		// Test local Google Fonts
		$results['local_google_fonts'] = array(
			'enabled' => get_option( 'seo_simple_local_fonts', false ),
			'status' => get_option( 'seo_simple_local_fonts', false ) ? 'Active' : 'Inactive',
			'description' => 'Local Google Fonts reduces external requests by hosting fonts locally.'
		);
		
		return $results;
	}

	/**
	 * Count WebP images.
	 *
	 * @since    1.0.0
	 * @return   int       The number of WebP images.
	 */
	private function count_webp_images() {
		$upload_dir = wp_upload_dir();
		$webp_dir = $upload_dir['basedir'];
		
		if ( ! file_exists( $webp_dir ) ) {
			return 0;
		}
		
		$files = glob( $webp_dir . '/**/*.webp', GLOB_NOSORT );
		return $files ? count( $files ) : 0;
	}

	/**
	 * Get cache details.
	 *
	 * @since    1.0.0
	 * @return   array     Cache details.
	 */
	public function get_cache_details() {
		$details = array();
		
		// Get page cache details
		$details['page_cache'] = array(
			'enabled' => get_option( 'seo_simple_page_cache_enabled', false ),
			'path' => WP_CONTENT_DIR . '/cache/seo-simple/',
			'size' => $this->get_directory_size( WP_CONTENT_DIR . '/cache/seo-simple/' ),
			'files' => $this->count_files_in_directory( WP_CONTENT_DIR . '/cache/seo-simple/' )
		);
		
		// Get minified files details
		$details['minified_files'] = array(
			'css_count' => count( glob( WP_CONTENT_DIR . '/*.min.css' ) ),
			'js_count' => count( glob( WP_CONTENT_DIR . '/*.min.js' ) ),
			'size' => $this->get_minified_files_size()
		);
		
		// Get WebP images details
		$details['webp_images'] = array(
			'count' => $this->count_webp_images(),
			'size' => $this->get_webp_files_size()
		);
		
		// Get local fonts details
		$details['local_fonts'] = array(
			'enabled' => get_option( 'seo_simple_local_fonts', false ),
			'path' => wp_upload_dir()['basedir'] . '/seo-simple-fonts/',
			'size' => $this->get_directory_size( wp_upload_dir()['basedir'] . '/seo-simple-fonts/' ),
			'files' => $this->count_files_in_directory( wp_upload_dir()['basedir'] . '/seo-simple-fonts/' )
		);
		
		return $details;
	}

	/**
	 * Get directory size.
	 *
	 * @since    1.0.0
	 * @param    string    $dir    The directory path.
	 * @return   string            The formatted size.
	 */
	private function get_directory_size( $dir ) {
		if ( ! file_exists( $dir ) ) {
			return '0 B';
		}
		
		$size = 0;
		$files = glob( $dir . '**/*', GLOB_NOSORT );
		
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$size += filesize( $file );
			}
		}
		
		return size_format( $size );
	}

	/**
	 * Count files in directory.
	 *
	 * @since    1.0.0
	 * @param    string    $dir    The directory path.
	 * @return   int               The file count.
	 */
	private function count_files_in_directory( $dir ) {
		if ( ! file_exists( $dir ) ) {
			return 0;
		}
		
		$files = glob( $dir . '**/*', GLOB_NOSORT );
		$count = 0;
		
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$count++;
			}
		}
		
		return $count;
	}

	/**
	 * Get minified files size.
	 *
	 * @since    1.0.0
	 * @return   string    The formatted size.
	 */
	private function get_minified_files_size() {
		$size = 0;
		
		$css_files = glob( WP_CONTENT_DIR . '/*.min.css' );
		$js_files = glob( WP_CONTENT_DIR . '/*.min.js' );
		
		foreach ( $css_files as $file ) {
			if ( is_file( $file ) ) {
				$size += filesize( $file );
			}
		}
		
		foreach ( $js_files as $file ) {
			if ( is_file( $file ) ) {
				$size += filesize( $file );
			}
		}
		
		return size_format( $size );
	}

	/**
	 * Get WebP files size.
	 *
	 * @since    1.0.0
	 * @return   string    The formatted size.
	 */
	private function get_webp_files_size() {
		$upload_dir = wp_upload_dir();
		$webp_dir = $upload_dir['basedir'];
		
		if ( ! file_exists( $webp_dir ) ) {
			return '0 B';
		}
		
		$size = 0;
		$files = glob( $webp_dir . '/**/*.webp', GLOB_NOSORT );
		
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$size += filesize( $file );
			}
		}
		
		return size_format( $size );
	}

	/**
	 * Delete cache files.
	 *
	 * @since    1.0.0
	 * @param    string    $type    The cache type.
	 * @return   bool               True on success, false on failure.
	 */
	public function delete_cache_files( $type = 'all' ) {
		$success = true;
		
		if ( $type === 'all' || $type === 'page' ) {
			// Delete page cache
			$cache_dir = WP_CONTENT_DIR . '/cache/seo-simple/';
			if ( file_exists( $cache_dir ) ) {
				$files = glob( $cache_dir . '*', GLOB_NOSORT );
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						$success = $success && unlink( $file );
					}
				}
			}
		}
		
		if ( $type === 'all' || $type === 'minified' ) {
			// Delete minified files
			$css_files = glob( WP_CONTENT_DIR . '/*.min.css' );
			foreach ( $css_files as $file ) {
				if ( is_file( $file ) ) {
					$success = $success && unlink( $file );
				}
			}
			
			$js_files = glob( WP_CONTENT_DIR . '/*.min.js' );
			foreach ( $js_files as $file ) {
				if ( is_file( $file ) ) {
					$success = $success && unlink( $file );
				}
			}
		}
		
		if ( $type === 'all' || $type === 'webp' ) {
			// Delete WebP files
			$upload_dir = wp_upload_dir();
			$webp_files = glob( $upload_dir['basedir'] . '/**/*.webp', GLOB_NOSORT );
			foreach ( $webp_files as $file ) {
				if ( is_file( $file ) ) {
					$success = $success && unlink( $file );
				}
			}
		}
		
		if ( $type === 'all' || $type === 'fonts' ) {
			// Delete local fonts
			$font_dir = wp_upload_dir()['basedir'] . '/seo-simple-fonts/';
			if ( file_exists( $font_dir ) ) {
				$files = glob( $font_dir . '*', GLOB_NOSORT );
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						$success = $success && unlink( $file );
					}
				}
			}
		}
		
		// Clear transients
		delete_transient( 'seo_simple_critical_css' );
		
		return $success;
	}

	/**
	 * Optimize all images.
	 *
	 * @since    1.0.0
	 * @return   array     Optimization results.
	 */
	public function optimize_all_images() {
		$results = array(
			'optimized' => 0,
			'errors' => 0,
			'skipped' => 0
		);
		
		// Get all images
		$attachments = get_posts( array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'post_status' => 'inherit'
		) );
		
		foreach ( $attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			
			if ( ! $metadata ) {
				$results['errors']++;
				continue;
			}
			
			// Optimize main image
			$upload_dir = wp_upload_dir();
			$file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
			
			if ( file_exists( $file_path ) ) {
				$this->optimize_image_file( $file_path );
				$results['optimized']++;
			} else {
				$results['errors']++;
			}
			
			// Optimize thumbnails
			if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size => $size_data ) {
					$size_path = dirname( $file_path ) . '/' . $size_data['file'];
					if ( file_exists( $size_path ) ) {
						$this->optimize_image_file( $size_path );
						$results['optimized']++;
					} else {
						$results['errors']++;
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 * Generate WebP images for all images.
	 *
	 * @since    1.0.0
	 * @return   array     Generation results.
	 */
	public function generate_all_webp_images() {
		$results = array(
			'generated' => 0,
			'errors' => 0,
			'skipped' => 0
		);
		
		// Check if GD supports WebP
		if ( ! function_exists( 'imagewebp' ) ) {
			$results['errors'] = 'WebP not supported';
			return $results;
		}
		
		// Get all images
		$attachments = get_posts( array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'post_status' => 'inherit'
		) );
		
		foreach ( $attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			
			if ( ! $metadata ) {
				$results['errors']++;
				continue;
			}
			
			// Generate WebP for main image
			$upload_dir = wp_upload_dir();
			$file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
			
			if ( file_exists( $file_path ) ) {
				$this->generate_webp_file( $file_path );
				$results['generated']++;
			} else {
				$results['errors']++;
			}
			
			// Generate WebP for thumbnails
			if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
				foreach ( $metadata['sizes'] as $size => $size_data ) {
					$size_path = dirname( $file_path ) . '/' . $size_data['file'];
					if ( file_exists( $size_path ) ) {
						$this->generate_webp_file( $size_path );
						$results['generated']++;
					} else {
						$results['errors']++;
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 * Download Google Fonts.
	 *
	 * @since    1.0.0
	 * @return   array     Download results.
	 */
	public function download_google_fonts() {
		$results = array(
			'downloaded' => 0,
			'errors' => 0,
			'skipped' => 0
		);
		
		// Get all enqueued Google Fonts
		global $wp_styles;
		
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[$handle];
			
			if ( $style && strpos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				// Download and localize the font
				$this->download_google_font( $style->src, $handle );
				$results['downloaded']++;
			}
		}
		
		return $results;
	}
}