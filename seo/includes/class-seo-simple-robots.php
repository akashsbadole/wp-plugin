<?php

/**
 * Handles robots.txt management for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles robots.txt management.
 *
 * This class handles all operations related to robots.txt including
 * getting, updating, and validating robots.txt content.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Robots {

	/**
	 * The option name for storing robots.txt content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_name    The option name.
	 */
	private $option_name = 'seo_simple_robots_txt';

	/**
	 * Default robots.txt content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $default_content    The default robots.txt content.
	 */
	private $default_content;

	/**
	 * The security handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Security    $security    The security handler instance.
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Initialize security handler if available
		if ( class_exists( 'Seo_Simple_Security' ) ) {
			$this->security = new Seo_Simple_Security();
		}
		
		// Initialize default content
		$this->default_content = $this->get_default_robots_content();
		
		// Hook into robots_txt filter
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 10, 2 );
		
		// Register admin settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get the default robots.txt content.
	 *
	 * @since    1.0.0
	 * @return   string    The default robots.txt content.
	 */
	private function get_default_robots_content() {
		$site_url = get_site_url();
		$content = "User-agent: *\n";
		$content .= "Disallow: /wp-admin/\n";
		$content .= "Allow: /wp-admin/admin-ajax.php\n";
		$content .= "Disallow: /wp-includes/\n";
		$content .= "Disallow: /wp-content/plugins/\n";
		$content .= "Disallow: /wp-content/themes/\n";
		$content .= "Disallow: /readme.html\n";
		$content .= "Disallow: /license.txt\n";
		$content .= "Disallow: /?s=\n";
		$content .= "Disallow: /search\n";
		$content .= "Disallow: /author/\n";
		$content .= "Disallow: */trackback/\n";
		$content .= "Disallow: */comments/\n";
		$content .= "Disallow: */attachment/\n";
		$content .= "Disallow: */feed/\n";
		
		// Allow common bots
		$content .= "\nUser-agent: Googlebot\n";
		$content .= "Allow: /\n";
		$content .= "\nUser-agent: Googlebot-Image\n";
		$content .= "Allow: /\n";
		$content .= "\nUser-agent: Bingbot\n";
		$content .= "Allow: /\n";
		
		// Add sitemap reference
		$content .= "\n# XML Sitemap\n";
		$content .= "Sitemap: " . $site_url . "/sitemap_index.xml\n";
		
		return $content;
	}

	/**
	 * Filter robots.txt content.
	 *
	 * @since    1.0.0
	 * @param    string    $output    The current robots.txt output.
	 * @param    bool      $public    Whether the site is public.
	 * @return   string               The modified robots.txt output.
	 */
	public function filter_robots_txt( $output, $public ) {
		// If site is not public, disallow all
		if ( ! $public ) {
			return "User-agent: *\nDisallow: /\n";
		}
		
		// Get custom robots.txt content
		$custom_content = $this->get_robots_content();
		
		// If custom content exists, use it
		if ( ! empty( $custom_content ) ) {
			return $custom_content;
		}
		
		// Otherwise, return default content
		return $this->default_content;
	}

	/**
	 * Get the current robots.txt content.
	 *
	 * @since    1.0.0
	 * @return   string    The current robots.txt content.
	 */
	public function get_robots_content() {
		return get_option( $this->option_name, '' );
	}

	/**
	 * Update robots.txt content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The new robots.txt content.
	 * @return   bool                  True on success, false on failure.
	 */
	public function update_robots_content( $content ) {
		// Validate content
		if ( ! $this->validate_robots_content( $content ) ) {
			return false;
		}
		
		// Sanitize content using security class if available
		if ( $this->security ) {
			$content = $this->security->sanitize_robots_content( $content );
		} else {
			// Fallback to default sanitization
			$content = $this->sanitize_robots_content( $content );
		}
		
		return update_option( $this->option_name, $content );
	}

	/**
	 * Reset robots.txt to default content.
	 *
	 * @since    1.0.0
	 * @return   bool    True on success, false on failure.
	 */
	public function reset_robots_content() {
		return delete_option( $this->option_name );
	}

	/**
	 * Validate robots.txt content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The robots.txt content to validate.
	 * @return   bool                  True if valid, false otherwise.
	 */
	public function validate_robots_content( $content ) {
		// Check if content is a string
		if ( ! is_string( $content ) ) {
			return false;
		}
		
		// Check content length (not too long)
		if ( strlen( $content ) > 50000 ) {
			return false;
		}
		
		// Basic pattern validation
		$lines = explode( "\n", $content );
		
		foreach ( $lines as $line ) {
			$line = trim( $line );
			
			// Skip empty lines and comments
			if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
				continue;
			}
			
			// Check for valid directives
			$valid_directives = array(
				'User-agent:',
				'Disallow:',
				'Allow:',
				'Crawl-delay:',
				'Sitemap:',
				'Host:',
			);
			
			$is_valid = false;
			foreach ( $valid_directives as $directive ) {
				if ( stripos( $line, $directive ) === 0 ) {
					$is_valid = true;
					break;
				}
			}
			
			if ( ! $is_valid ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Sanitize robots.txt content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The robots.txt content to sanitize.
	 * @return   string                The sanitized robots.txt content.
	 */
	private function sanitize_robots_content( $content ) {
		// Convert line endings
		$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
		
		// Remove extra whitespace at the end of lines
		$lines = explode( "\n", $content );
		$sanitized_lines = array();
		
		foreach ( $lines as $line ) {
			$sanitized_lines[] = rtrim( $line );
		}
		
		// Remove extra empty lines at the end
		while ( ! empty( $sanitized_lines ) && empty( end( $sanitized_lines ) ) ) {
			array_pop( $sanitized_lines );
		}
		
		return implode( "\n", $sanitized_lines );
	}

	/**
	 * Register admin settings for robots.txt.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			'seo_simple_robots_settings',
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_robots_content_for_settings' ),
				'default' => ''
			)
		);
	}

	/**
	 * Sanitize robots.txt content for settings.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The robots.txt content to sanitize.
	 * @return   string                The sanitized robots.txt content.
	 */
	public function sanitize_robots_content_for_settings( $content ) {
		if ( ! $this->validate_robots_content( $content ) ) {
			add_settings_error(
				$this->option_name,
				'invalid_robots_content',
				__( 'Invalid robots.txt content. Please check your syntax.', 'seo-simple' ),
				'error'
			);
			
			return $this->get_robots_content();
		}
		
		// Use security class if available
		if ( $this->security ) {
			return $this->security->sanitize_robots_content( $content );
		} else {
			// Fallback to default sanitization
			return $this->sanitize_robots_content( $content );
		}
	}

	/**
	 * Check if custom robots.txt is enabled.
	 *
	 * @since    1.0.0
	 * @return   bool    True if custom robots.txt is enabled.
	 */
	public function is_custom_robots_enabled() {
		$content = $this->get_robots_content();
		return ! empty( $content );
	}

	/**
	 * Get robots.txt URL.
	 *
	 * @since    1.0.0
	 * @return   string    The robots.txt URL.
	 */
	public function get_robots_url() {
		return get_site_url() . '/robots.txt';
	}

	/**
	 * Add sitemap to robots.txt if not present.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The robots.txt content.
	 * @return   string                The modified content.
	 */
	public function ensure_sitemap_in_robots( $content ) {
		$sitemap_url = get_site_url() . '/sitemap_index.xml';
		
		// Check if sitemap is already present
		if ( strpos( $content, 'Sitemap:' ) !== false ) {
			return $content;
		}
		
		// Add sitemap at the end
		$content .= "\n\n# XML Sitemap\n";
		$content .= "Sitemap: " . $sitemap_url . "\n";
		
		return $content;
	}

	/**
	 * Get robots.txt preview.
	 *
	 * @since    1.0.0
	 * @return   string    The robots.txt preview.
	 */
	public function get_robots_preview() {
		$custom_content = $this->get_robots_content();
		
		if ( ! empty( $custom_content ) ) {
			return $custom_content;
		}
		
		return $this->default_content;
	}

	/**
	 * Check if robots.txt is writable.
	 *
	 * @since    1.0.0
	 * @return   bool    True if robots.txt is writable.
	 */
	public function is_robots_writable() {
		// Since we're using WordPress virtual robots.txt, this is always true
		return true;
	}

	/**
	 * Get common robot user agents.
	 *
	 * @since    1.0.0
	 * @return   array    Array of common robot user agents.
	 */
	public function get_common_user_agents() {
		return array(
			'Googlebot' => 'Google',
			'Googlebot-Image' => 'Google Image Search',
			'Googlebot-News' => 'Google News',
			'Bingbot' => 'Bing',
			'Slurp' => 'Yahoo',
			'DuckDuckBot' => 'DuckDuckGo',
			'Baiduspider' => 'Baidu',
			'YandexBot' => 'Yandex',
			'facebookexternalhit' => 'Facebook',
			'Twitterbot' => 'Twitter',
			'LinkedInBot' => 'LinkedIn',
			'WhatsApp' => 'WhatsApp',
			'Applebot' => 'Apple',
			'ia_archiver' => 'Alexa',
			'msnbot' => 'MSN',
		);
	}

	/**
	 * Get common disallow patterns.
	 *
	 * @since    1.0.0
	 * @return   array    Array of common disallow patterns with descriptions.
	 */
	public function get_common_disallow_patterns() {
		return array(
			'/wp-admin/' => 'WordPress admin area',
			'/wp-includes/' => 'WordPress includes files',
			'/wp-content/plugins/' => 'WordPress plugins',
			'/wp-content/themes/' => 'WordPress themes',
			'/readme.html' => 'WordPress readme file',
			'/license.txt' => 'WordPress license file',
			'/?s=' => 'Search results',
			'/search' => 'Search results',
			'/author/' => 'Author pages',
			'*/trackback/' => 'Trackbacks',
			'*/comments/' => 'Comments',
			'*/attachment/' => 'Attachment pages',
			'*/feed/' => 'RSS feeds',
			'/cgi-bin/' => 'CGI scripts',
			'/tmp/' => 'Temporary files',
		);
	}
}