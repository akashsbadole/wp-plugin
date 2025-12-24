<?php

/**
 * Handles auto configuration of SEO settings based on site analysis.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles auto configuration of SEO settings based on site analysis.
 *
 * This class analyzes the site and automatically configures optimal SEO settings.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Auto_Config {

	/**
	 * The site analysis results.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $site_analysis    The site analysis results.
	 */
	private $site_analysis;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Defer analysis until needed to avoid issues during early initialization
		$this->site_analysis = null;
	}

	/**
	 * Analyze the site to determine optimal SEO settings.
	 *
	 * @since    1.0.0
	 * @return   array    The site analysis results.
	 */
	private function analyze_site() {
		$analysis = array();

		// Check if it's a multisite installation
		$analysis['is_multisite'] = is_multisite();

		// Get site type based on active plugins and content
		$analysis['site_type'] = $this->determine_site_type();

		// Count posts by type
		$analysis['post_types'] = $this->get_post_type_counts();

		// Check for WooCommerce
		$analysis['has_woocommerce'] = class_exists( 'WooCommerce' );

		// Check for page builders
		$analysis['page_builders'] = $this->get_active_page_builders();

		// Analyze content structure
		$analysis['content_analysis'] = $this->analyze_content_structure();

		// Get site performance metrics
		$analysis['performance'] = $this->analyze_performance();

		// Check current theme
		$analysis['theme'] = wp_get_theme()->get( 'Name' );

		// Get site size
		$analysis['site_size'] = $this->estimate_site_size();

		return $analysis;
	}

	/**
	 * Determine the site type based on plugins and content.
	 *
	 * @since    1.0.0
	 * @return   string    The site type.
	 */
	private function determine_site_type() {
		// Make sure plugin functions are available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		// Check for common site types
		if ( class_exists( 'WooCommerce' ) ) {
			return 'ecommerce';
		}

		if ( is_plugin_active( 'wp-real-estate/wp-real-estate.php' ) ||
		     is_plugin_active( 'easy-property-listings/easy-property-listings.php' ) ) {
			return 'realestate';
		}

		if ( is_plugin_active( 'wp-job-manager/wp-job-manager.php' ) ) {
			return 'jobboard';
		}

		if ( is_plugin_active( 'learnpress/learnpress.php' ) ||
		     is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
			return 'lms';
		}

		// Default to blog if no specific type detected
		return 'blog';
	}

	/**
	 * Get post type counts.
	 *
	 * @since    1.0.0
	 * @return   array    Post type counts.
	 */
	private function get_post_type_counts() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$counts = array();

		foreach ( $post_types as $post_type ) {
			$counts[ $post_type->name ] = array(
				'name' => $post_type->labels->singular_name,
				'count' => wp_count_posts( $post_type->name )->publish,
				'has_archive' => $post_type->has_archive,
			);
		}

		return $counts;
	}

	/**
	 * Get active page builders.
	 *
	 * @since    1.0.0
	 * @return   array    Active page builders.
	 */
	private function get_active_page_builders() {
		$builders = array();

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$builders[] = 'elementor';
		}

		if ( defined( 'ET_BUILDER_VERSION' ) ) {
			$builders[] = 'divi';
		}

		if ( class_exists( 'FLBuilder' ) ) {
			$builders[] = 'beaver_builder';
		}

		if ( class_exists( 'Vc_Manager' ) ) {
			$builders[] = 'wpbakery';
		}

		return $builders;
	}

	/**
	 * Analyze content structure.
	 *
	 * @since    1.0.0
	 * @return   array    Content analysis results.
	 */
	private function analyze_content_structure() {
		$analysis = array();

		// Get recent posts for analysis
		$recent_posts = get_posts( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'numberposts' => 10,
		) );

		$total_content_length = 0;
		$posts_with_images = 0;
		$posts_with_headings = 0;

		foreach ( $recent_posts as $post ) {
			$content = $post->post_content;
			$total_content_length += strlen( $content );

			if ( has_block( 'image', $content ) || preg_match( '/<img[^>]+>/i', $content ) ) {
				$posts_with_images++;
			}

			if ( preg_match( '/<h[1-6][^>]*>/i', $content ) ) {
				$posts_with_headings++;
			}
		}

		$analysis['avg_content_length'] = count( $recent_posts ) > 0 ? $total_content_length / count( $recent_posts ) : 0;
		$analysis['image_usage_ratio'] = count( $recent_posts ) > 0 ? $posts_with_images / count( $recent_posts ) : 0;
		$analysis['heading_usage_ratio'] = count( $recent_posts ) > 0 ? $posts_with_headings / count( $recent_posts ) : 0;

		return $analysis;
	}

	/**
	 * Analyze site performance.
	 *
	 * @since    1.0.0
	 * @return   array    Performance analysis results.
	 */
	private function analyze_performance() {
		$analysis = array();

		// Make sure plugin functions are available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check for caching plugins
		$analysis['has_caching'] = (
			is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
			is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
			is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ||
			is_plugin_active( 'litespeed-cache/litespeed-cache.php' )
		);

		// Check for image optimization plugins
		$analysis['has_image_optimization'] = (
			is_plugin_active( 'smush/smush.php' ) ||
			is_plugin_active( 'imagify/imagify.php' ) ||
			is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' )
		);

		// Check for CDN
		$analysis['has_cdn'] = (
			defined( 'W3TC_CDN_ROOT' ) ||
			is_plugin_active( 'cloudflare/cloudflare.php' ) ||
			get_option( 'jetpack_sync_non_public_post_stati' ) // Jetpack CDN
		);

		return $analysis;
	}

	/**
	 * Estimate site size.
	 *
	 * @since    1.0.0
	 * @return   string    Site size category.
	 */
	private function estimate_site_size() {
		$total_posts = 0;
		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $post_types as $post_type ) {
			$total_posts += wp_count_posts( $post_type )->publish;
		}

		if ( $total_posts < 50 ) {
			return 'small';
		} elseif ( $total_posts < 500 ) {
			return 'medium';
		} elseif ( $total_posts < 5000 ) {
			return 'large';
		} else {
			return 'enterprise';
		}
	}

	/**
	 * Generate optimal SEO settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @return   array    Recommended SEO settings.
	 */
	public function generate_optimal_settings() {
		$settings = array();
		$analysis = $this->get_site_analysis();

		// General settings
		$settings['general'] = $this->get_general_settings( $analysis );

		// Title and meta settings
		$settings['titles_meta'] = $this->get_titles_meta_settings( $analysis );

		// Social settings
		$settings['social'] = $this->get_social_settings( $analysis );

		// Sitemap settings
		$settings['sitemap'] = $this->get_sitemap_settings( $analysis );

		// Advanced settings
		$settings['advanced'] = $this->get_advanced_settings( $analysis );

		return $settings;
	}

	/**
	 * Get general settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @param    array    $analysis    Site analysis results.
	 * @return   array    General settings.
	 */
	private function get_general_settings( $analysis ) {
		$settings = array();

		// Enable tracking based on site type
		$settings['enable_tracking'] = true;

		// Set post types to include
		$settings['post_types'] = array();
		foreach ( $analysis['post_types'] as $post_type => $data ) {
			if ( $data['count'] > 0 ) {
				$settings['post_types'][] = $post_type;
			}
		}

		// Enable/disable features based on site size
		if ( $analysis['site_size'] === 'small' ) {
			$settings['enable_advanced_analysis'] = false;
			$settings['cache_interval'] = 'daily';
		} elseif ( $analysis['site_size'] === 'medium' ) {
			$settings['enable_advanced_analysis'] = true;
			$settings['cache_interval'] = 'twicedaily';
		} else {
			$settings['enable_advanced_analysis'] = true;
			$settings['cache_interval'] = 'hourly';
		}

		return $settings;
	}

	/**
	 * Get titles and meta settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @param    array    $analysis    Site analysis results.
	 * @return   array    Titles and meta settings.
	 */
	private function get_titles_meta_settings( $analysis ) {
		$settings = array();

		// Set title templates based on site type
		switch ( $analysis['site_type'] ) {
			case 'ecommerce':
				$settings['title_template'] = '%product_title% | %product_category% | %site_name%';
				$settings['description_template'] = '%product_description% - Buy now at %site_name%';
				break;
			case 'blog':
				$settings['title_template'] = '%post_title% | %category% | %site_name%';
				$settings['description_template'] = '%post_excerpt%';
				break;
			default:
				$settings['title_template'] = '%post_title% | %site_name%';
				$settings['description_template'] = '%post_excerpt%';
		}

		// Set meta description length based on content analysis
		if ( $analysis['content_analysis']['avg_content_length'] > 2000 ) {
			$settings['meta_description_length'] = 160;
		} else {
			$settings['meta_description_length'] = 150;
		}

		// Enable social meta based on image usage
		$settings['enable_social_meta'] = $analysis['content_analysis']['image_usage_ratio'] > 0.5;

		return $settings;
	}

	/**
	 * Get social settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @param    array    $analysis    Site analysis results.
	 * @return   array    Social settings.
	 */
	private function get_social_settings( $analysis ) {
		$settings = array();

		// Enable Open Graph
		$settings['enable_og'] = true;

		// Enable Twitter Cards
		$settings['enable_twitter'] = true;

		// Set default image based on site type
		if ( $analysis['site_type'] === 'ecommerce' ) {
			$settings['default_image'] = '';
			$settings['use_product_image'] = true;
		} else {
			$settings['default_image'] = '';
			$settings['use_featured_image'] = true;
		}

		return $settings;
	}

	/**
	 * Get sitemap settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @param    array    $analysis    Site analysis results.
	 * @return   array    Sitemap settings.
	 */
	private function get_sitemap_settings( $analysis ) {
		$settings = array();

		// Enable sitemap
		$settings['enable_sitemap'] = true;

		// Set sitemap update frequency based on site activity
		switch ( $analysis['site_size'] ) {
			case 'small':
				$settings['update_frequency'] = 'daily';
				break;
			case 'medium':
				$settings['update_frequency'] = 'twicedaily';
				break;
			default:
				$settings['update_frequency'] = 'hourly';
		}

		// Include post types based on count
		$settings['include_post_types'] = array();
		foreach ( $analysis['post_types'] as $post_type => $data ) {
			if ( $data['count'] > 0 ) {
				$settings['include_post_types'][] = $post_type;
			}
		}

		// Include taxonomies
		$settings['include_taxonomies'] = array( 'category', 'post_tag' );
		if ( $analysis['has_woocommerce'] ) {
			$settings['include_taxonomies'][] = 'product_cat';
			$settings['include_taxonomies'][] = 'product_tag';
		}

		return $settings;
	}

	/**
	 * Get advanced settings based on site analysis.
	 *
	 * @since    1.0.0
	 * @param    array    $analysis    Site analysis results.
	 * @return   array    Advanced settings.
	 */
	private function get_advanced_settings( $analysis ) {
		$settings = array();

		// Enable/disable features based on performance analysis
		$settings['enable_caching'] = ! $analysis['performance']['has_caching'];
		$settings['enable_image_optimization'] = ! $analysis['performance']['has_image_optimization'];

		// Set performance features based on site size
		if ( $analysis['site_size'] === 'large' || $analysis['site_size'] === 'enterprise' ) {
			$settings['enable_minification'] = true;
			$settings['enable_lazy_loading'] = true;
			$settings['enable_gzip'] = true;
		} else {
			$settings['enable_minification'] = false;
			$settings['enable_lazy_loading'] = true;
			$settings['enable_gzip'] = false;
		}

		// Enable page builder integration if detected
		$settings['enable_page_builder_integration'] = ! empty( $analysis['page_builders'] );

		// Set security features based on site type
		if ( $analysis['site_type'] === 'ecommerce' ) {
			$settings['enable_advanced_security'] = true;
		} else {
			$settings['enable_advanced_security'] = $analysis['site_size'] !== 'small';
		}

		return $settings;
	}

	/**
	 * Apply the auto configuration settings.
	 *
	 * @since    1.0.0
	 * @return   bool    True on success, false on failure.
	 */
	public function apply_auto_configuration() {
		// Ensure analysis is performed before applying configuration
		$analysis = $this->get_site_analysis();
		$settings = $this->generate_optimal_settings();

		// Get existing settings
		$existing_settings = get_option( 'seo_simple_settings', array() );

		// Merge with new settings
		$merged_settings = array_merge_recursive( $existing_settings, $settings );

		// Save settings
		$result = update_option( 'seo_simple_settings', $merged_settings );

		// Log the configuration
		error_log( '[SEO Simple] Auto configuration applied: ' . print_r( $settings, true ) );

		return $result;
	}

	/**
	 * Get the site analysis results.
	 *
	 * @since    1.0.0
	 * @return   array    The site analysis results.
	 */
	public function get_site_analysis() {
		// Perform analysis if not already done
		if ( $this->site_analysis === null ) {
			$this->site_analysis = $this->analyze_site();
		}
		return $this->site_analysis;
	}
}