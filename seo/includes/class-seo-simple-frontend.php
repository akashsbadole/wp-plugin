<?php

/**
 * Handles frontend SEO functionality including social media meta tags.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles frontend SEO functionality including social media meta tags.
 *
 * This class handles all operations related to frontend SEO including
 * outputting meta tags for search engines and social media platforms.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Frontend {

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
	 * The redirect handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Redirects    $redirects    The redirect handler instance.
	 */
	private $redirects;

	/**
	 * The schema handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema    $schema    The schema handler instance.
	 */
	private $schema;

	/**
	 * The multisite handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Multisite    $multisite    The multisite handler instance.
	 */
	private $multisite;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Meta       $seo_meta    The SEO meta handler instance.
	 * @param    Seo_Simple_Schema      $schema      The schema handler instance.
	 * @param    Seo_Simple_Multisite   $multisite   The multisite handler instance.
	 */
	public function __construct( $seo_meta, $schema = null, $multisite = null ) {
		$this->seo_meta = $seo_meta;
		$this->schema = $schema;
		$this->multisite = $multisite;
		
		// Initialize security handler if available
		if ( class_exists( 'Seo_Simple_Security' ) ) {
			$this->security = new Seo_Simple_Security();
		}
		
		// Initialize redirect handler if available
		if ( class_exists( 'Seo_Simple_Redirects' ) ) {
			$this->redirects = new Seo_Simple_Redirects();
		}
		
		// Note: Hooks are now registered through the loader in define_public_hooks()
		// This ensures proper hook registration timing
		
		// Add WooCommerce-specific hooks
		if ( $this->is_woocommerce_active() ) {
			add_filter( 'seo_plugin_add_schema', array( $this, 'add_woocommerce_schema_data' ), 10, 3 );
		}
	}

	/**
		* Get cached global settings.
		*
		* @since    1.0.0
		* @access   private
		* @return   array    The global settings.
		*/
	private function get_cached_settings() {
		// Try cache first, but fall back to direct database access if cache fails
		try {
			$cache_manager = Seo_Simple_Cache_Manager::get_instance();
			$settings = $cache_manager->remember( 'seo_simple_global_settings', array( $this->seo_meta, 'get_global_settings' ), 3600 );
			
			// If cache returned empty array, try direct access
			if (empty($settings)) {
				$settings = $this->seo_meta->get_settings_from_db();
			}
			
			return $settings;
		} catch (Exception $e) {
			// If cache manager fails, fall back to direct database access
			return $this->seo_meta->get_settings_from_db();
		}
	}

	/**
		* Get cached post meta data.
		*
		* @since    1.0.0
		* @access   private
		* @param    int       $post_id    The post ID.
		* @param    string    $field_name The meta field name.
		* @return   mixed                 The meta value.
		*/
	private function get_cached_post_meta( $post_id, $field_name ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = "seo_simple_post_meta_{$post_id}_{$field_name}";
		return $cache_manager->remember( $cache_key, function() use ( $post_id, $field_name ) {
			return $this->seo_meta->get_meta( $post_id, $field_name );
		}, 3600 );
	}

	/**
		* Get multiple post meta values in a single query.
		*
		* @since    1.0.0
		* @access   private
		* @param    int       $post_id    The post ID.
		* @return   array                 All meta values for the post.
		*/
	private function get_post_meta_batch( $post_id ) {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = "seo_simple_post_meta_batch_{$post_id}";
		
		return $cache_manager->remember( $cache_key, function() use ( $post_id ) {
			$meta_fields = array( 'title', 'description', 'keywords', 'focus_keyword', 'noindex', 'nofollow', 'canonical', 'social_title', 'social_description', 'social_image', 'social_image_id', 'use_seo_data' );
			$meta_data = array();
			
			foreach ( $meta_fields as $field ) {
				$meta_data[$field] = $this->seo_meta->get_meta( $post_id, $field );
			}
			
			return $meta_data;
		}, 3600 );
	}

	/**
	 * Output basic SEO meta tags.
	 *
	 * @since    1.0.0
	 */
	public function output_meta_tags() {
		$title = '';
		$description = '';
		$keywords = '';
		
		// Ensure settings are loaded with multiple fallbacks
		try {
			$settings = $this->get_cached_settings();
		} catch (Exception $e) {
			// If all else fails, use default settings
			$settings = array(
				'seo_title_separator' => '-',
				'seo_title_format' => '%post_title% | %site_name%',
				'seo_title_length' => 60,
				'seo_description_length' => 160,
			);
		}
		
		// Handle different page types
		if ( is_home() || is_front_page() ) {
			// Homepage
			try {
				$title = $this->seo_meta->get_homepage_title();
				$description = $this->seo_meta->get_homepage_description();
			} catch (Exception $e) {
				// Fallback if methods fail
				$title = '';
				$description = '';
			}
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = get_bloginfo( 'name' );
				$site_description = get_bloginfo( 'description' );
				if ( ! empty( $site_description ) ) {
					$title .= ' | ' . $site_description;
				}
			}
			if ( empty( $description ) ) {
				$description = get_bloginfo( 'description' );
			}
		} elseif ( is_singular() ) {
			// Singular posts/pages - use batch meta retrieval for efficiency
			global $post;
			if ( $post ) {
				try {
					$meta_data = $this->get_post_meta_batch( $post->ID );
					$title = $this->seo_meta->get_title( $post->ID );
					$description = $this->seo_meta->get_description( $post->ID );
					$keywords = $this->seo_meta->get_keywords( $post->ID );
				} catch (Exception $e) {
					// Fallback if methods fail
					$title = '';
					$description = '';
					$keywords = '';
				}
				
				// Fallback if empty
				if ( empty( $title ) ) {
					$title = get_the_title( $post ) . ' | ' . get_bloginfo( 'name' );
				}
				if ( empty( $description ) ) {
					$description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
					$description = strip_tags( $description );
					$description = strip_shortcodes( $description );
					$description = wp_trim_words( $description, 30, '...' );
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			// Taxonomy archives
			$term = get_queried_object();
			if ( $term ) {
				try {
					$title = $this->seo_meta->get_term_title( $term );
					$description = $this->seo_meta->get_term_description( $term );
				} catch (Exception $e) {
					// Fallback if methods fail
					$title = '';
					$description = '';
				}
				
				// Fallback if empty
				if ( empty( $title ) ) {
					$title = single_term_title( '', false ) . ' | ' . get_bloginfo( 'name' );
				}
				if ( empty( $description ) && ! empty( $term->description ) ) {
					$description = $term->description;
				}
			}
		} elseif ( is_author() ) {
			// Author archives
			$author = get_queried_object();
			$author_name = isset( $author->display_name ) ? esc_html( $author->display_name ) : '';
			$title = sprintf( __( 'Author: %s | %s', 'seo-simple' ), $author_name, get_bloginfo( 'name' ) );
			$description = sprintf( __( 'Posts by %s', 'seo-simple' ), $author_name );
		} elseif ( is_search() ) {
			// Search results
			$search_query = get_search_query();
			$search_query = $search_query ? esc_html( $search_query ) : '';
			$title = sprintf( __( 'Search Results for: %s | %s', 'seo-simple' ), $search_query, get_bloginfo( 'name' ) );
			$description = sprintf( __( 'Search results for: %s', 'seo-simple' ), $search_query );
		} elseif ( is_404() ) {
			// 404 page
			$title = __( 'Page Not Found | %s', 'seo-simple' );
			$title = sprintf( $title, get_bloginfo( 'name' ) );
			$description = __( 'The page you are looking for could not be found.', 'seo-simple' );
		} else {
			// Default for other archives
			$title = get_the_archive_title() . ' | ' . get_bloginfo( 'name' );
			$description = get_the_archive_description();
		}
		
		// Sanitize output using security class if available
		if ( $this->security ) {
			$title = $this->security->sanitize_title( $title );
			$description = $this->security->sanitize_description( $description );
			$keywords = $this->security->sanitize_keywords( $keywords );
		} else {
			// Fallback to default sanitization
			$title = esc_html( $title );
			$description = esc_attr( $description );
			$keywords = esc_attr( $keywords );
		}
		
		// Validate and truncate content length to prevent issues
		$title = $this->validate_meta_content_length( $title, 60 ); // Google typically truncates at 60 chars
		$description = $this->validate_meta_content_length( $description, 160 ); // Google typically truncates at 160 chars
		$keywords = $this->validate_meta_content_length( $keywords, 255 ); // Reasonable limit for keywords
		
		// Output meta tags with proper escaping
		if ( ! empty( $title ) ) {
			echo '<title>' . esc_html( $title ) . '</title>' . "\n";
		}
		
		if ( ! empty( $description ) ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		}
		
		if ( ! empty( $keywords ) ) {
			echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
		}
	}

	/**
	 * Output Open Graph meta tags for Facebook and other social platforms.
	 *
	 * @since    1.0.0
	 */
	public function output_open_graph_tags() {
		// Check if Open Graph is enabled
		$settings = $this->get_cached_settings();
		
		// Default to enabled if setting is not found
		if ( ! isset( $settings['seo_enable_og'] ) || ! $settings['seo_enable_og'] ) {
			return;
		}
		
		$title = '';
		$description = '';
		$image = '';
		$url = '';
		$type = 'website';
		
		// Handle different page types
		if ( is_home() || is_front_page() ) {
			// Homepage
			$title = $this->seo_meta->get_homepage_title();
			$description = $this->seo_meta->get_homepage_description();
			$url = home_url( '/' );
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = get_bloginfo( 'name' );
				$site_description = get_bloginfo( 'description' );
				if ( ! empty( $site_description ) ) {
					$title .= ' | ' . $site_description;
				}
			}
			if ( empty( $description ) ) {
				$description = get_bloginfo( 'description' );
			}
		} elseif ( is_singular() ) {
			// Singular posts/pages - use batch meta retrieval for efficiency
			global $post;
			$meta_data = $this->get_post_meta_batch( $post->ID );
			$title = $this->seo_meta->get_social_title( $post->ID );
			$description = $this->seo_meta->get_social_description( $post->ID );
			$image = $this->seo_meta->get_social_image( $post->ID );
			$url = get_permalink( $post->ID );
			$type = is_page() ? 'website' : 'article';
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = $this->seo_meta->get_title( $post->ID );
				if ( empty( $title ) ) {
					$title = get_the_title( $post ) . ' | ' . get_bloginfo( 'name' );
				}
			}
			if ( empty( $description ) ) {
				$description = $this->seo_meta->get_description( $post->ID );
				if ( empty( $description ) ) {
					$description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
					$description = strip_tags( $description );
					$description = strip_shortcodes( $description );
					$description = wp_trim_words( $description, 30, '...' );
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			// Taxonomy archives
			$term = get_queried_object();
			$title = $this->seo_meta->get_term_title( $term );
			$description = $this->seo_meta->get_term_description( $term );
			$url = get_term_link( $term );
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = single_term_title( '', false ) . ' | ' . get_bloginfo( 'name' );
			}
			if ( empty( $description ) && ! empty( $term->description ) ) {
				$description = $term->description;
			}
		} else {
			// Other pages - skip OG tags
			return;
		}
		
		// If no title, don't output OG tags
		if ( empty( $title ) ) {
			return;
		}
		
		$site_name = get_bloginfo( 'name' );
		
		// Sanitize output using security class if available
		if ( $this->security ) {
			$title = $this->security->sanitize_title( $title );
			$description = $this->security->sanitize_description( $description );
			$site_name = $this->security->sanitize_title( $site_name );
			$url = $this->security->sanitize_canonical_url( $url );
			$image = $this->security->sanitize_canonical_url( $image );
		} else {
			// Fallback to default sanitization
			$title = esc_attr( $title );
			$description = esc_attr( $description );
			$site_name = esc_attr( $site_name );
			$url = esc_url( $url );
			$image = esc_url( $image );
		}
		
		// Validate and truncate content length
		$title = $this->validate_meta_content_length( $title, 100 ); // OG title recommended max 100 chars
		$description = $this->validate_meta_content_length( $description, 300 ); // OG description recommended max 300 chars
		
		// Output Open Graph namespace
		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
		
		// Output basic Open Graph tags with proper escaping
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		
		if ( ! empty( $description ) ) {
			echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
		}
		
		if ( ! empty( $url ) ) {
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		}
		
		echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
		echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
		
		// Output image if available or use default
		if ( empty( $image ) ) {
			$image = $this->seo_meta->get_default_og_image();
			if ( $this->security ) {
				$image = $this->security->sanitize_canonical_url( $image );
			} else {
				$image = esc_url( $image );
			}
		}
		
		if ( ! empty( $image ) ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
			
			// Try to get image dimensions if it's a local attachment
			if ( is_singular() ) {
				global $post;
				$image_id = $this->seo_meta->get_social_image_id( $post->ID );
				if ( $image_id ) {
					$image_meta = wp_get_attachment_metadata( $image_id );
					if ( $image_meta ) {
						if ( isset( $image_meta['width'] ) && isset( $image_meta['height'] ) ) {
							echo '<meta property="og:image:width" content="' . esc_attr( $image_meta['width'] ) . '">' . "\n";
							echo '<meta property="og:image:height" content="' . esc_attr( $image_meta['height'] ) . '">' . "\n";
						}
					}
					
					// Get image mime type
					$image_mime = get_post_mime_type( $image_id );
					if ( $image_mime ) {
						echo '<meta property="og:image:type" content="' . esc_attr( $image_mime ) . '">' . "\n";
					}
				}
			}
		}
		
		// Additional article tags for posts
		if ( is_single() && $type === 'article' ) {
			// Publish time
			if ( $post->post_date ) {
				echo '<meta property="article:published_time" content="' . esc_attr( date( 'c', strtotime( $post->post_date ) ) ) . '">' . "\n";
			}
			
			// Modified time
			if ( $post->post_modified ) {
				echo '<meta property="article:modified_time" content="' . esc_attr( date( 'c', strtotime( $post->post_modified ) ) ) . '">' . "\n";
			}
			
			// Author
			$author_id = $post->post_author;
			if ( $author_id ) {
				$author = get_the_author_meta( 'display_name', $author_id );
				if ( $author ) {
					if ( $this->security ) {
						$author = $this->security->sanitize_title( $author );
					} else {
						$author = esc_attr( $author );
					}
					echo '<meta property="article:author" content="' . esc_attr( $author ) . '">' . "\n";
				}
			}
			
			// Categories
			$categories = get_the_category( $post->ID );
			if ( $categories && ! is_wp_error( $categories ) ) {
				foreach ( $categories as $category ) {
					if ( isset( $category->name ) ) {
						$category_name = $this->security ?
							$this->security->sanitize_title( $category->name ) :
							esc_attr( $category->name );
						echo '<meta property="article:section" content="' . esc_attr( $category_name ) . '">' . "\n";
					}
				}
			}
			
			// Tags
			$tags = get_the_tags( $post->ID );
			if ( $tags && ! is_wp_error( $tags ) ) {
				foreach ( $tags as $tag ) {
					if ( isset( $tag->name ) ) {
						$tag_name = $this->security ?
							$this->security->sanitize_title( $tag->name ) :
							esc_attr( $tag->name );
						echo '<meta property="article:tag" content="' . esc_attr( $tag_name ) . '">' . "\n";
					}
				}
			}
		}
	}

	/**
	 * Output Twitter Card meta tags.
	 *
	 * @since    1.0.0
	 */
	public function output_twitter_card_tags() {
		// Check if Twitter Cards are enabled
		$settings = $this->get_cached_settings();
		
		// Default to enabled if setting is not found
		if ( ! isset( $settings['seo_enable_twitter'] ) || ! $settings['seo_enable_twitter'] ) {
			return;
		}
		
		$title = '';
		$description = '';
		$image = '';
		$url = '';
		$author_id = 0;
		
		// Handle different page types
		if ( is_home() || is_front_page() ) {
			// Homepage
			$title = $this->seo_meta->get_homepage_title();
			$description = $this->seo_meta->get_homepage_description();
			$url = home_url( '/' );
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = get_bloginfo( 'name' );
				$site_description = get_bloginfo( 'description' );
				if ( ! empty( $site_description ) ) {
					$title .= ' | ' . $site_description;
				}
			}
			if ( empty( $description ) ) {
				$description = get_bloginfo( 'description' );
			}
		} elseif ( is_singular() ) {
			// Singular posts/pages - use batch meta retrieval for efficiency
			global $post;
			$meta_data = $this->get_post_meta_batch( $post->ID );
			$title = $this->seo_meta->get_social_title( $post->ID );
			$description = $this->seo_meta->get_social_description( $post->ID );
			$image = $this->seo_meta->get_social_image( $post->ID );
			$url = get_permalink( $post->ID );
			$author_id = $post->post_author;
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = $this->seo_meta->get_title( $post->ID );
				if ( empty( $title ) ) {
					$title = get_the_title( $post ) . ' | ' . get_bloginfo( 'name' );
				}
			}
			if ( empty( $description ) ) {
				$description = $this->seo_meta->get_description( $post->ID );
				if ( empty( $description ) ) {
					$description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
					$description = strip_tags( $description );
					$description = strip_shortcodes( $description );
					$description = wp_trim_words( $description, 30, '...' );
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			// Taxonomy archives
			$term = get_queried_object();
			$title = $this->seo_meta->get_term_title( $term );
			$description = $this->seo_meta->get_term_description( $term );
			$url = get_term_link( $term );
			
			// Fallback if empty
			if ( empty( $title ) ) {
				$title = single_term_title( '', false ) . ' | ' . get_bloginfo( 'name' );
			}
			if ( empty( $description ) && ! empty( $term->description ) ) {
				$description = $term->description;
			}
		} else {
			// Other pages - skip Twitter Card tags
			return;
		}
		
		// If no title, don't output Twitter Card tags
		if ( empty( $title ) ) {
			return;
		}
		
		// Output image if available or use default
		if ( empty( $image ) ) {
			$image = $this->seo_meta->get_default_og_image();
		}
		
		// Sanitize output using security class if available
		if ( $this->security ) {
			$title = $this->security->sanitize_title( $title );
			$description = $this->security->sanitize_description( $description );
			$url = $this->security->sanitize_canonical_url( $url );
			$image = $this->security->sanitize_canonical_url( $image );
		} else {
			// Fallback to default sanitization
			$title = esc_attr( $title );
			$description = esc_attr( $description );
			$url = esc_url( $url );
			$image = esc_url( $image );
		}
		
		// Validate and truncate content length
		$title = $this->validate_meta_content_length( $title, 70 ); // Twitter title recommended max 70 chars
		$description = $this->validate_meta_content_length( $description, 200 ); // Twitter description recommended max 200 chars
		
		// Determine card type based on image availability
		$card_type = ! empty( $image ) ? 'summary_large_image' : 'summary';
		
		// Output Twitter Card meta tags with proper escaping
		echo '<meta name="twitter:card" content="' . esc_attr( $card_type ) . '">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		
		if ( ! empty( $description ) ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
		}
		
		if ( ! empty( $url ) ) {
			echo '<meta name="twitter:url" content="' . esc_url( $url ) . '">' . "\n";
		}
		
		if ( ! empty( $image ) ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
		}
		
		// Twitter site handle
		$twitter_username = isset( $settings['seo_twitter_username'] ) ? $settings['seo_twitter_username'] : '';
		if ( ! empty( $twitter_username ) ) {
			// Remove @ if present
			$twitter_username = ltrim( $twitter_username, '@' );
			$twitter_username = sanitize_text_field( $twitter_username );
			echo '<meta name="twitter:site" content="@' . esc_attr( $twitter_username ) . '">' . "\n";
		}
		
		// Twitter creator handle (can be filtered)
		$twitter_creator = apply_filters( 'seo_simple_twitter_creator', '' );
		if ( ! empty( $twitter_creator ) ) {
			$twitter_creator = sanitize_text_field( $twitter_creator );
			echo '<meta name="twitter:creator" content="' . esc_attr( $twitter_creator ) . '">' . "\n";
		}
		
		// Alternative: use author's Twitter handle if available
		if ( $author_id && empty( $twitter_creator ) ) {
			$author_twitter = get_the_author_meta( 'twitter', $author_id );
			if ( $author_twitter && ! empty( $author_twitter ) ) {
				// Remove @ if present
				$author_twitter = ltrim( $author_twitter, '@' );
				$author_twitter = sanitize_text_field( $author_twitter );
				echo '<meta name="twitter:creator" content="@' . esc_attr( $author_twitter ) . '">' . "\n";
			}
		}
	}

	/**
	 * Output canonical URL meta tag.
	 *
	 * @since    1.0.0
	 */
	public function output_canonical_url() {
		$canonical_url = '';
		
		if ( is_singular() ) {
			global $post;
			
			// Use batch meta retrieval for efficiency
			$meta_data = $this->get_post_meta_batch( $post->ID );
			
			// Get custom canonical URL
			$custom_canonical = $this->seo_meta->get_canonical_url( $post->ID );
			
			if ( ! empty( $custom_canonical ) ) {
				$canonical_url = $custom_canonical;
			} else {
				// Use permalink as canonical URL
				$canonical_url = get_permalink( $post->ID );
			}
		} elseif ( is_home() || is_front_page() ) {
			$canonical_url = home_url( '/' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term && ! is_wp_error( $term ) ) {
				$canonical_url = get_term_link( $term );
			}
		} elseif ( is_search() ) {
			$canonical_url = get_search_link();
		}
		
		// Sanitize URL using security class if available
		if ( $this->security ) {
			$canonical_url = $this->security->sanitize_canonical_url( $canonical_url );
		} else {
			$canonical_url = esc_url_raw( $canonical_url );
		}
		
		// Output canonical URL if valid
		if ( ! empty( $canonical_url ) && filter_var( $canonical_url, FILTER_VALIDATE_URL ) ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical_url ) . '">' . "\n";
		}
	}

	/**
	 * Output robots meta tag.
	 *
	 * @since    1.0.0
	 */
	public function output_robots_meta() {
		$robots = array();
		
		// Default to index,follow
		$robots[] = 'index';
		$robots[] = 'follow';
		
		if ( is_singular() ) {
			global $post;
			
			// Use batch meta retrieval for efficiency
			$meta_data = $this->get_post_meta_batch( $post->ID );
			
			// Check if noindex is set
			if ( $this->seo_meta->is_noindex( $post->ID ) ) {
				$robots = array();
				$robots[] = 'noindex';
			}
			
			// Check if nofollow is set
			if ( $this->seo_meta->is_nofollow( $post->ID ) ) {
				$robots = array_diff( $robots, array( 'follow' ) );
				$robots[] = 'nofollow';
			}
		}
		
		// Apply filters
		$robots = apply_filters( 'seo_simple_robots_meta', $robots );
		
		// Sanitize robots values
		$sanitized_robots = array();
		$allowed_robots = array( 'index', 'noindex', 'follow', 'nofollow', 'noarchive', 'nosnippet', 'notranslate', 'noimageindex', 'unavailable_after' );
		
		foreach ( $robots as $robot ) {
			if ( in_array( $robot, $allowed_robots, true ) ) {
				$sanitized_robots[] = $robot;
			}
		}
		
		// Output robots meta tag
		if ( ! empty( $sanitized_robots ) ) {
			echo '<meta name="robots" content="' . esc_attr( implode( ',', $sanitized_robots ) ) . '">' . "\n";
		}
	}

	/**
	 * Check for redirects before handling the request.
	 *
	 * @since    1.0.0
	 */
	public function check_redirects() {
		if ( ! $this->redirects ) {
			return;
		}
		
		// Don't redirect admin, login, or register pages
		if ( is_admin() || is_login() || $GLOBALS['pagenow'] === 'wp-register.php' ) {
			return;
		}
		
		// Get current URL
		$current_url = $this->get_current_url();
		
		// Sanitize URL using security class if available
		if ( $this->security ) {
			$current_url = $this->security->sanitize_canonical_url( $current_url );
		} else {
			$current_url = esc_url_raw( $current_url );
		}
		
		// Check for redirect
		$redirect = $this->redirects->get_redirect( $current_url );
		
		if ( $redirect ) {
			// Update hit count
			$this->redirects->update_hit_count( $redirect['id'] );
			
			// Perform redirect
			wp_redirect( $redirect['to_url'], $redirect['redirect_type'] );
			exit;
		}
	}

	/**
	 * Get current URL.
	 *
	 * @since    1.0.0
	 * @return   string    The current URL.
	 */
	private function get_current_url() {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : '';
		$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		
		return $protocol . $host . $path;
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
	 * Output WooCommerce-specific schema data.
	 *
	 * @since    1.0.0
	 */
	public function output_woocommerce_schema() {
		// Only output on product pages
		if ( ! is_product() ) {
			return;
		}
		
		global $product;
		
		if ( ! $product ) {
			return;
		}
		
		// Get product schema data
		$schema = $this->get_woocommerce_product_schema( $product );
		
		if ( ! empty( $schema ) ) {
			// Sanitize schema using security class if available
			if ( $this->security ) {
				$schema = $this->security->sanitize_schema_json( $schema );
			}
			
			$json_schema = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			if ( $json_schema ) {
				echo '<script type="application/ld+json">' . $json_schema . '</script>' . "\n";
			}
		}
	}
	
	/**
	 * Get WooCommerce product schema data.
	 *
	 * @since    1.0.0
	 * @param    WC_Product    $product    The product object.
	 * @return   array                      The schema data.
	 */
	public function get_woocommerce_product_schema( $product ) {
		global $post;
		
		// Use batch meta retrieval for efficiency
		$meta_data = $this->get_post_meta_batch( $post->ID );
		
		// Get product-specific SEO meta
		$product_title = get_post_meta( $post->ID, '_seo_simple_product_title', true );
		$product_description = get_post_meta( $post->ID, '_seo_simple_product_description', true );
		
		// Use product-specific meta or fall back to regular SEO meta
		$title = ! empty( $product_title ) ? $product_title : $this->seo_meta->get_title( $post->ID );
		$description = ! empty( $product_description ) ? $product_description : $this->seo_meta->get_description( $post->ID );
		
		// Sanitize output using security class if available
		if ( $this->security ) {
			$title = $this->security->sanitize_title( $title );
			$description = $this->security->sanitize_description( $description );
		} else {
			$title = esc_html( $title );
			$description = esc_html( $description );
		}
		
		// Validate content length
		$title = $this->validate_meta_content_length( $title, 200 );
		$description = $this->validate_meta_content_length( $description, 5000 );
		
		// Build product schema
		$schema = array(
			'@context' => 'https://schema.org/',
			'@type' => 'Product',
			'name' => $title,
			'description' => $description,
			'url' => esc_url( get_permalink( $post->ID ) ),
			'sku' => $product->get_sku() ? sanitize_text_field( $product->get_sku() ) : '',
		);
		
		// Add brand if available
		$brand = $product->get_attribute( 'brand' );
		if ( $brand ) {
			$brand_name = $this->security ?
				$this->security->sanitize_title( $brand ) :
				esc_html( $brand );
			$schema['brand'] = array(
				'@type' => 'Brand',
				'name' => $brand_name,
			);
		}
		
		// Add image if available
		if ( has_post_thumbnail( $post->ID ) ) {
			$image_id = get_post_thumbnail_id( $post->ID );
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $image_url ) {
				$image_url = $this->security ?
					$this->security->sanitize_canonical_url( $image_url ) :
					esc_url( $image_url );
				$schema['image'] = array( $image_url );
			}
		}
		
		// Add gallery images
		$attachment_ids = $product->get_gallery_image_ids();
		if ( ! empty( $attachment_ids ) ) {
			$gallery_images = array();
			
			// Add featured image again if it exists
			if ( has_post_thumbnail( $post->ID ) ) {
				$image_id = get_post_thumbnail_id( $post->ID );
				$image_url = wp_get_attachment_image_url( $image_id, 'full' );
				if ( $image_url ) {
					$image_url = $this->security ?
						$this->security->sanitize_canonical_url( $image_url ) :
						esc_url( $image_url );
					$gallery_images[] = $image_url;
				}
			}
			
			// Add gallery images
			foreach ( $attachment_ids as $attachment_id ) {
				$image_url = wp_get_attachment_image_url( $attachment_id, 'full' );
				if ( $image_url ) {
					$image_url = $this->security ?
						$this->security->sanitize_canonical_url( $image_url ) :
						esc_url( $image_url );
					$gallery_images[] = $image_url;
				}
			}
			
			if ( ! empty( $gallery_images ) ) {
				$schema['image'] = $gallery_images;
			}
		}
		
		// Add price if available
		if ( $product->get_price() > 0 ) {
			$schema['offers'] = array(
				'@type' => 'Offer',
				'price' => (float) $product->get_price(),
				'priceCurrency' => sanitize_text_field( get_woocommerce_currency() ),
				'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url' => esc_url( get_permalink( $post->ID ) ),
				'seller' => array(
					'@type' => 'Organization',
					'name' => esc_html( get_bloginfo( 'name' ) ),
				),
			);
			
			// Add sale price if available
			if ( $product->is_on_sale() && $product->get_sale_price() > 0 ) {
				$schema['offers']['priceSpecification'] = array(
					'@type' => 'PriceSpecification',
					'price' => (float) $product->get_sale_price(),
					'priceCurrency' => sanitize_text_field( get_woocommerce_currency() ),
				);
			}
		}
		
		// Add ratings if available
		$average_rating = $product->get_average_rating();
		if ( $average_rating > 0 ) {
			$schema['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => (float) $average_rating,
				'reviewCount' => (int) $product->get_review_count(),
				'bestRating' => '5',
				'worstRating' => '1',
			);
		}
		
		// Add reviews if available
		$args = array(
			'post_id' => $post->ID,
			'status' => 'approve',
			'type' => 'review',
		);
		
		$comments = get_comments( $args );
		
		if ( ! empty( $comments ) ) {
			$schema['review'] = array();
			
			foreach ( $comments as $comment ) {
				$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
				
				$author_name = $this->security ?
					$this->security->sanitize_title( $comment->comment_author ) :
					esc_html( $comment->comment_author );
				
				$review_body = $this->security ?
					$this->security->sanitize_description( $comment->comment_content ) :
					esc_html( $comment->comment_content );
				
				$review = array(
					'@type' => 'Review',
					'author' => array(
						'@type' => 'Person',
						'name' => $author_name,
					),
					'datePublished' => date( 'c', strtotime( $comment->comment_date ) ),
					'reviewBody' => $review_body,
				);
				
				if ( $rating > 0 ) {
					$review['reviewRating'] = array(
						'@type' => 'Rating',
						'ratingValue' => (float) $rating,
						'bestRating' => '5',
						'worstRating' => '1',
					);
				}
				
				$schema['review'][] = $review;
			}
		}
		
		// Add categories
		$categories = get_the_terms( $post->ID, 'product_cat' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$category_names = array();
			foreach ( $categories as $category ) {
				if ( isset( $category->name ) ) {
					$category_name = $this->security ?
						$this->security->sanitize_title( $category->name ) :
						esc_html( $category->name );
					$category_names[] = $category_name;
				}
			}
			if ( ! empty( $category_names ) ) {
				$schema['category'] = $category_names;
			}
		}
		
		// Add tags
		$tags = get_the_terms( $post->ID, 'product_tag' );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$tag_names = array();
			foreach ( $tags as $tag ) {
				if ( isset( $tag->name ) ) {
					$tag_name = $this->security ?
						$this->security->sanitize_title( $tag->name ) :
						esc_html( $tag->name );
					$tag_names[] = $tag_name;
				}
			}
			if ( ! empty( $tag_names ) && is_array( $tag_names ) ) {
				// Ensure all elements are strings before implode
				$string_tag_names = array_filter( $tag_names, 'is_string' );
				if ( ! empty( $string_tag_names ) ) {
					$schema['keywords'] = sanitize_text_field( implode( ', ', $string_tag_names ) );
				}
			}
		}
		
		// Add product attributes
		$attributes = $product->get_attributes();
		if ( ! empty( $attributes ) ) {
			$schema['additionalProperty'] = array();
			
			foreach ( $attributes as $attribute ) {
				if ( $attribute->get_visible() ) {
					$values = $product->get_attribute( $attribute->get_name() );
					if ( $values ) {
						$attribute_name = $this->security ?
							$this->security->sanitize_title( wc_attribute_label( $attribute->get_name() ) ) :
							esc_html( wc_attribute_label( $attribute->get_name() ) );
						
						$attribute_values = $this->security ?
							$this->security->sanitize_description( $values ) :
							esc_html( $values );
						
						$schema['additionalProperty'][] = array(
							'@type' => 'PropertyValue',
							'name' => $attribute_name,
							'value' => $attribute_values,
						);
					}
				}
			}
		}
		
		// Apply developer filter
		$schema = apply_filters( 'seo_plugin_add_schema', $schema, $post->ID, array() );
		
		return $schema;
	}
	
	/**
	 * Add WooCommerce schema data to the schema filter.
	 *
	 * @since    1.0.0
	 * @param    array     $schema    The current schema data.
	 * @param    int       $post_id   The post ID.
	 * @param    array     $data      The data to build schema from.
	 * @return   array                The modified schema data.
	 */
	public function add_woocommerce_schema_data( $schema, $post_id, $data ) {
		// Only add for products
		if ( get_post_type( $post_id ) !== 'product' ) {
			return $schema;
		}
		
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return $schema;
		}
		
		// Get product schema data
		$product_schema = $this->get_woocommerce_product_schema( $product );
		
		// Merge with existing schema
		if ( ! empty( $product_schema ) ) {
			if ( isset( $schema['@graph'] ) ) {
				// If schema already has a @graph property, add to it
				$schema['@graph'][] = $product_schema;
			} else {
				// If schema doesn't have a @graph property, create one
				$schema = array(
					'@context' => 'https://schema.org/',
					'@graph' => array( $schema, $product_schema ),
				);
			}
		}
		
		return $schema;
	}

	/**
	 * Output network analytics and verification codes.
	 *
	 * @since    1.0.0
	 */
	public function output_network_codes() {
		// Skip if multisite is not enabled or not available
		if ( ! $this->multisite || ! is_multisite() ) {
			return;
		}
		
		// Skip if network features are not enabled
		if ( ! $this->multisite->is_network_enabled() ) {
			return;
		}
		
		// Output Google Analytics code
		$analytics_code = $this->multisite->get_network_analytics();
		if ( ! empty( $analytics_code ) ) {
			// Sanitize analytics code
			$analytics_code = sanitize_text_field( $analytics_code );
			
			// Output Google Analytics script
			echo "<!-- Google Analytics (Network) -->\n";
			echo "<script async src=\"https://www.googletagmanager.com/gtag/js?id=" . esc_attr( $analytics_code ) . "\"></script>\n";
			echo "<script>\n";
			echo "window.dataLayer = window.dataLayer || [];\n";
			echo "function gtag(){dataLayer.push(arguments);}\n";
			echo "gtag('js', new Date());\n";
			echo "gtag('config', '" . esc_attr( $analytics_code ) . "');\n";
			echo "</script>\n";
			echo "<!-- End Google Analytics -->\n\n";
		}
		
		// Output Google Site Verification
		$google_verification = $this->multisite->get_network_verification( 'google' );
		if ( ! empty( $google_verification ) ) {
			$google_verification = sanitize_text_field( $google_verification );
			echo '<meta name="google-site-verification" content="' . esc_attr( $google_verification ) . '">' . "\n";
		}
		
		// Output Bing Site Verification
		$bing_verification = $this->multisite->get_network_verification( 'bing' );
		if ( ! empty( $bing_verification ) ) {
			$bing_verification = sanitize_text_field( $bing_verification );
			echo '<meta name="msvalidate.01" content="' . esc_attr( $bing_verification ) . '">' . "\n";
		}
	}

	/**
	 * Output structured data schema.
	 *
	 * @since    1.0.0
	 */
	public function output_schema() {
		// Check if schema is enabled
		$settings = $this->get_cached_settings();
		
		// Default to enabled if setting is not found
		if ( ! isset( $settings['seo_enable_json_ld'] ) || ! $settings['seo_enable_json_ld'] ) {
			return;
		}
		
		// Skip if schema handler is not available
		if ( ! $this->schema ) {
			return;
		}
		
		// Generate schema
		$schema = $this->schema->generate_schema();
		
		if ( empty( $schema ) ) {
			return;
		}
		
		// Validate schema
		if ( ! $this->schema->validate_schema( $schema ) ) {
			return;
		}
		
		// Sanitize schema using security class if available
		if ( $this->security ) {
			$schema = $this->security->sanitize_schema_json( $schema );
		} else {
			$schema = $this->schema->sanitize_schema( $schema );
		}
		
		// Output schema with proper escaping
		$json_schema = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( $json_schema ) {
			echo '<script type="application/ld+json">' . $json_schema . '</script>' . "\n";
		}
	}
	
	/**
	 * Validate and truncate meta content length to prevent issues.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to validate.
	 * @param    int       $max_length The maximum allowed length.
	 * @return   string                The validated and possibly truncated content.
	 */
	private function validate_meta_content_length( $content, $max_length ) {
		if ( empty( $content ) ) {
			return $content;
		}
		
		// Ensure content is a string
		$content = (string) $content;
		
		// Truncate if too long
		if ( is_string( $content ) && strlen( $content ) > $max_length ) {
			$content = substr( $content, 0, $max_length );
		}
		
		return $content;
	}
}
