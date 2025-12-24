<?php

/**
 * Handles SEO meta data operations.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles SEO meta data operations.
 *
 * This class handles all operations related to SEO meta data including
 * saving, retrieving, and formatting meta information for posts.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Meta {

	/**
	 * The meta key prefix for all SEO meta fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $meta_key_prefix    The prefix for all SEO meta keys.
	 */
	private $meta_key_prefix = '_seo_simple_';

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Hook into save_post action
		add_action( 'save_post', array( $this, 'save_seo_meta' ), 10, 2 );
	}

	/**
	 * Save SEO meta data when a post is saved.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The ID of the post being saved.
	 * @param    WP_Post $post       The post object.
	 */
	public function save_seo_meta( $post_id, $post ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['seo_simple_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['seo_simple_meta_box_nonce'], 'seo_simple_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( 'page' === $post->post_type ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		// Initialize security handler if available
		$security = null;
		if ( class_exists( 'Seo_Simple_Security' ) ) {
			$security = new Seo_Simple_Security();
		}

		// Sanitize and save the meta fields
		$meta_fields = array(
			'title'       => 'sanitize_text_field',
			'description' => 'sanitize_textarea_field',
			'keywords'    => 'sanitize_text_field',
			'focus_keyword' => 'sanitize_text_field',
			'noindex'     => 'intval',
			'nofollow'    => 'intval',
			'canonical'   => 'esc_url_raw',
			'social_title' => 'sanitize_text_field',
			'social_description' => 'sanitize_textarea_field',
			'social_image' => 'esc_url_raw',
			'social_image_id' => 'intval',
			'use_seo_data' => 'intval',
			'schema_type' => 'sanitize_text_field',
			'custom_schema' => 'wp_kses_post',
		);

		foreach ( $meta_fields as $field_name => $sanitize_callback ) {
			if ( isset( $_POST[ $this->meta_key_prefix . $field_name ] ) ) {
				$value = $_POST[ $this->meta_key_prefix . $field_name ];
				
				// Use security class if available for specific fields
				if ( $security ) {
					switch ( $field_name ) {
						case 'title':
							$value = $security->sanitize_title( $value );
							break;
						case 'description':
						case 'social_description':
							$value = $security->sanitize_description( $value );
							break;
						case 'focus_keyword':
							$value = $security->sanitize_focus_keyword( $value );
							break;
						case 'canonical':
						case 'social_image':
							$value = $security->sanitize_canonical_url( $value );
							break;
						case 'custom_schema':
							$value = $security->sanitize_schema_json( $value );
							$value = wp_json_encode( $value );
							break;
						default:
							$value = call_user_func( $sanitize_callback, $value );
							break;
					}
				} else {
					// Fallback to default sanitization
					$value = call_user_func( $sanitize_callback, $value );
				}
				
				update_post_meta( $post_id, $this->meta_key_prefix . $field_name, $value );
			}
		}
		
		// Clear cache for this post
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_manager->delete_group( "seo_simple_post_{$post_id}" );
	}

	/**
	 * Get SEO meta value for a specific field.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id     The post ID.
	 * @param    string  $field_name  The meta field name.
	 * @param    string  $default     Default value if meta doesn't exist.
	 * @return   string               The meta value.
	 */
	public function get_meta( $post_id, $field_name, $default = '' ) {
		$value = get_post_meta( $post_id, $this->meta_key_prefix . $field_name, true );
		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Get all SEO meta data for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               All SEO meta data.
	 */
	public function get_all_meta( $post_id ) {
		$meta_fields = array( 'title', 'description', 'keywords', 'focus_keyword', 'noindex', 'nofollow', 'canonical', 'social_title', 'social_description', 'social_image', 'social_image_id', 'use_seo_data', 'schema_type', 'custom_schema' );
		$meta_data = array();

		foreach ( $meta_fields as $field ) {
			$meta_data[ $field ] = $this->get_meta( $post_id, $field );
		}

		return $meta_data;
	}

	/**
	 * Generate the SEO title for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The generated SEO title.
	 */
	public function get_title( $post_id ) {
		$custom_title = $this->get_meta( $post_id, 'title' );
		
		if ( ! empty( $custom_title ) ) {
			return $custom_title;
		}

		// Get global settings
		$settings = $this->get_global_settings();
		$post = get_post( $post_id );
		
		// Get content type specific template
		$content_type = $post->post_type;
		$title_format = isset( $settings[ 'seo_' . $content_type . '_title_format' ] ) ?
			$settings[ 'seo_' . $content_type . '_title_format' ] :
			$settings['seo_title_format'];
		
		$separator = $settings['seo_title_separator'];
		
		// Prepare replacements
		$replacements = array(
			'%post_title%' => get_the_title( $post ),
			'%site_name%' => get_bloginfo( 'name' ),
			'%separator%' => $separator,
		);
		
		// Add category if it's a post
		if ( $content_type === 'post' ) {
			$categories = get_the_category( $post_id );
			if ( ! empty( $categories ) ) {
				$replacements['%category%'] = $categories[0]->name;
			}
		}
		
		// Add author if it's a post
		if ( $content_type === 'post' ) {
			$author_id = $post->post_author;
			$author = get_the_author_meta( 'display_name', $author_id );
			$replacements['%author%'] = $author;
		}
		
		// Replace placeholders
		$title = str_replace( array_keys( $replacements ), array_values( $replacements ), $title_format );
		
		// Apply length limit
		$max_length = intval( $settings['seo_title_length'] );
		if ( is_string( $title ) && strlen( $title ) > $max_length ) {
			$title = substr( $title, 0, $max_length - 3 ) . '...';
		}

		return $title;
	}

	/**
	 * Generate the SEO description for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The generated SEO description.
	 */
	public function get_description( $post_id ) {
		$custom_description = $this->get_meta( $post_id, 'description' );
		
		if ( ! empty( $custom_description ) ) {
			return $custom_description;
		}

		// Get global settings
		$settings = $this->get_global_settings();
		$post = get_post( $post_id );
		
		// Get content type specific template
		$content_type = $post->post_type;
		$description_template = isset( $settings[ 'seo_' . $content_type . '_description_format' ] ) ?
			$settings[ 'seo_' . $content_type . '_description_format' ] :
			$settings['seo_description_template'];
		
		// If no template is set, use default behavior
		if ( empty( $description_template ) ) {
			$description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
			$description = strip_tags( $description );
			$description = strip_shortcodes( $description );
			$description = wp_trim_words( $description, 30, '...' );
		} else {
			// Process template
			$content = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
			$content = strip_tags( $content );
			$content = strip_shortcodes( $content );
			
			$replacements = array(
				'%post_content%' => $content,
				'%post_excerpt%' => ! empty( $post->post_excerpt ) ? $post->post_excerpt : $content,
				'%post_title%' => get_the_title( $post ),
			);
			
			$description = str_replace( array_keys( $replacements ), array_values( $replacements ), $description_template );
		}
		
		// Apply length limit
		$max_length = intval( $settings['seo_description_length'] );
		if ( is_string( $description ) && strlen( $description ) > $max_length ) {
			$description = substr( $description, 0, $max_length - 3 ) . '...';
		}
		
		return $description;
	}

	/**
	 * Get the SEO keywords for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The SEO keywords.
	 */
	public function get_keywords( $post_id ) {
		$custom_keywords = $this->get_meta( $post_id, 'keywords' );
		
		if ( ! empty( $custom_keywords ) ) {
			return $custom_keywords;
		}

		// Fallback to post tags
		$tags = wp_get_post_tags( $post_id );
		if ( ! empty( $tags ) ) {
			$keywords = array();
			foreach ( $tags as $tag ) {
				$keywords[] = $tag->name;
			}
			return implode( ', ', $keywords );
		}

		return '';
	}

	/**
	 * Get the focus keyword for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The focus keyword.
	 */
	public function get_focus_keyword( $post_id ) {
		$keyword = $this->get_meta( $post_id, 'focus_keyword' );
		
		// Handle case where keyword is stored as array
		if ( is_array( $keyword ) ) {
			return implode( ', ', $keyword );
		}
		
		return $keyword;
	}

	/**
	 * Save focus keyword for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id        The post ID.
	 * @param    string  $focus_keyword  The focus keyword.
	 * @return   bool                    True on success, false on failure.
	 */
	public function save_focus_keyword( $post_id, $focus_keyword ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$focus_keyword = sanitize_text_field( $focus_keyword );
		return update_post_meta( $post_id, $this->meta_key_prefix . 'focus_keyword', $focus_keyword );
	}

	/**
	 * Check if noindex is set for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if noindex is set.
	 */
	public function is_noindex( $post_id ) {
		return (bool) $this->get_meta( $post_id, 'noindex' );
	}
	
	/**
	 * Save readability scores for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    array   $scores     The readability scores.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_readability_scores( $post_id, $scores ) {
		if ( empty( $post_id ) || empty( $scores ) || ! is_array( $scores ) ) {
			return false;
		}
		
		// Sanitize scores
		$sanitized_scores = array();
		foreach ( $scores as $metric => $data ) {
			if ( is_array( $data ) ) {
				$sanitized_scores[ $metric ] = array();
				foreach ( $data as $key => $value ) {
					if ( is_numeric( $value ) ) {
						$sanitized_scores[ $metric ][ $key ] = floatval( $value );
					} elseif ( is_string( $value ) ) {
						$sanitized_scores[ $metric ][ $key ] = sanitize_text_field( $value );
					}
				}
			}
		}
		
		// Save to post meta
		return update_post_meta( $post_id, $this->meta_key_prefix . 'readability_scores', wp_json_encode( $sanitized_scores ) );
	}
	
	/**
	 * Get readability scores for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $cached     Whether to use cached results.
	 * @return   array               The readability scores.
	 */
	public function get_readability_scores( $post_id, $cached = true ) {
		if ( empty( $post_id ) ) {
			return array();
		}
		
		// Get from post meta
		$scores = get_post_meta( $post_id, $this->meta_key_prefix . 'readability_scores', true );
		
		if ( empty( $scores ) ) {
			return array();
		}
		
		// Decode JSON
		$scores = json_decode( $scores, true );
		
		if ( ! is_array( $scores ) ) {
			return array();
		}
		
		// If not using cached results, check if content has changed
		if ( ! $cached ) {
			$post = get_post( $post_id );
			$content_hash = md5( $post->post_content );
			$stored_hash = get_post_meta( $post_id, $this->meta_key_prefix . 'content_hash', true );
			
			if ( $content_hash !== $stored_hash ) {
				// Content has changed, return empty array to trigger new analysis
				return array();
			}
		}
		
		return $scores;
	}

	/**
	 * Save SEO analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    array   $analysis   The SEO analysis results.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_seo_analysis( $post_id, $analysis ) {
		if ( empty( $post_id ) || empty( $analysis ) || ! is_array( $analysis ) ) {
			return false;
		}
		
		// Sanitize analysis
		$sanitized_analysis = array();
		foreach ( $analysis as $metric => $data ) {
			if ( is_array( $data ) ) {
				$sanitized_analysis[ $metric ] = array();
				foreach ( $data as $key => $value ) {
					if ( is_numeric( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = floatval( $value );
					} elseif ( is_string( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = sanitize_text_field( $value );
					} elseif ( is_bool( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = $value;
					}
				}
			}
		}
		
		// Save to post meta
		return update_post_meta( $post_id, $this->meta_key_prefix . 'seo_analysis', wp_json_encode( $sanitized_analysis ) );
	}

	/**
	 * Get SEO analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $cached     Whether to use cached results.
	 * @return   array               The SEO analysis results.
	 */
	public function get_seo_analysis( $post_id, $cached = true ) {
		if ( empty( $post_id ) ) {
			return array();
		}
		
		// Get from post meta
		$analysis = get_post_meta( $post_id, $this->meta_key_prefix . 'seo_analysis', true );
		
		if ( empty( $analysis ) ) {
			return array();
		}
		
		// Decode JSON
		$analysis = json_decode( $analysis, true );
		
		if ( ! is_array( $analysis ) ) {
			return array();
		}
		
		// If not using cached results, check if content has changed
		if ( ! $cached ) {
			$post = get_post( $post_id );
			$content_hash = md5( $post->post_content );
			$stored_hash = get_post_meta( $post_id, $this->meta_key_prefix . 'seo_content_hash', true );
			
			if ( $content_hash !== $stored_hash ) {
				// Content has changed, return empty array to trigger new analysis
				return array();
			}
		}
		
		return $analysis;
	}
	
	/**
	 * Cache analysis results by storing content hash.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function cache_analysis_results( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$post = get_post( $post_id );
		$content_hash = md5( $post->post_content );
		
		return update_post_meta( $post_id, $this->meta_key_prefix . 'content_hash', $content_hash );
	}

	/**
	 * Cache SEO analysis results by storing content hash.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function cache_seo_analysis_results( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$post = get_post( $post_id );
		$content_hash = md5( $post->post_content );
		
		return update_post_meta( $post_id, $this->meta_key_prefix . 'seo_content_hash', $content_hash );
	}
	
	/**
	 * Get the readability score for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   int                 The readability score.
	 */
	public function get_readability_score( $post_id ) {
		$overall_score = $this->get_overall_readability_score( $post_id );
		return isset( $overall_score['score'] ) ? intval( $overall_score['score'] ) : 0;
	}
	
	/**
	 * Get the overall readability score for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The overall score data.
	 */
	public function get_overall_readability_score( $post_id ) {
		$scores = $this->get_readability_scores( $post_id );
		
		if ( empty( $scores ) || ! isset( $scores['overall'] ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No readability analysis available.', 'seo-simple' )
			);
		}
		
		return $scores['overall'];
	}

	/**
	 * Get the SEO score for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   int                 The SEO score.
	 */
	public function get_seo_score( $post_id ) {
		$overall_score = $this->get_overall_seo_score( $post_id );
		return isset( $overall_score['score'] ) ? intval( $overall_score['score'] ) : 0;
	}
	
	/**
	 * Get the overall SEO score for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The overall score data.
	 */
	public function get_overall_seo_score( $post_id ) {
		$analysis = $this->get_seo_analysis( $post_id );
		
		if ( empty( $analysis ) || ! isset( $analysis['overall'] ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No SEO analysis available.', 'seo-simple' )
			);
		}
		
		return $analysis['overall'];
	}
	
	/**
	 * Clear readability scores for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function clear_readability_scores( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		// Delete scores and content hash
		$result1 = delete_post_meta( $post_id, $this->meta_key_prefix . 'readability_scores' );
		$result2 = delete_post_meta( $post_id, $this->meta_key_prefix . 'content_hash' );
		
		return $result1 || $result2;
	}

	/**
	 * Clear SEO analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function clear_seo_analysis( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		// Delete analysis and content hash
		$result1 = delete_post_meta( $post_id, $this->meta_key_prefix . 'seo_analysis' );
		$result2 = delete_post_meta( $post_id, $this->meta_key_prefix . 'seo_content_hash' );
		
		return $result1 || $result2;
	}
	
	/**
	 * Get the social title for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social title.
	 */
	public function get_social_title( $post_id ) {
		$social_title = $this->get_meta( $post_id, 'social_title' );
		
		if ( ! empty( $social_title ) ) {
			return $social_title;
		}
		
		// Fallback to SEO title if use_seo_data is checked
		if ( $this->get_use_seo_data( $post_id ) ) {
			return $this->get_title( $post_id );
		}
		
		return '';
	}
	
	/**
	 * Get the social description for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social description.
	 */
	public function get_social_description( $post_id ) {
		$social_description = $this->get_meta( $post_id, 'social_description' );
		
		if ( ! empty( $social_description ) ) {
			return $social_description;
		}
		
		// Fallback to SEO description if use_seo_data is checked
		if ( $this->get_use_seo_data( $post_id ) ) {
			return $this->get_description( $post_id );
		}
		
		return '';
	}
	
	/**
	 * Get the social image for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The social image URL.
	 */
	public function get_social_image( $post_id ) {
		$social_image = $this->get_meta( $post_id, 'social_image' );
		
		if ( ! empty( $social_image ) ) {
			return $social_image;
		}
		
		// Try to get image from social_image_id
		$social_image_id = $this->get_social_image_id( $post_id );
		if ( $social_image_id ) {
			$image_src = wp_get_attachment_image_src( $social_image_id, 'full' );
			if ( $image_src ) {
				return $image_src[0];
			}
		}
		
		// Fallback to featured image
		if ( has_post_thumbnail( $post_id ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$image_src = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			if ( $image_src ) {
				return $image_src[0];
			}
		}
		
		return '';
	}
	
	/**
	 * Get the social image ID for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   int                 The social image ID.
	 */
	public function get_social_image_id( $post_id ) {
		return intval( $this->get_meta( $post_id, 'social_image_id' ) );
	}
	
	/**
	 * Check if use SEO data is enabled for social media.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if use SEO data is enabled.
	 */
	public function get_use_seo_data( $post_id ) {
		return (bool) $this->get_meta( $post_id, 'use_seo_data' );
	}
	
	/**
	 * Check if nofollow is set for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if nofollow is set.
	 */
	public function is_nofollow( $post_id ) {
		return (bool) $this->get_meta( $post_id, 'nofollow' );
	}
	
	/**
	 * Get the canonical URL for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The canonical URL.
	 */
	public function get_canonical_url( $post_id ) {
		$canonical = $this->get_meta( $post_id, 'canonical' );
		
		// If custom canonical URL is set, return it
		if ( ! empty( $canonical ) ) {
			return $canonical;
		}
		
		// Otherwise, return empty to use default permalink
		return '';
	}
	
	/**
	 * Save nofollow setting for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $nofollow   Whether to set nofollow.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_nofollow( $post_id, $nofollow ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$nofollow = $nofollow ? 1 : 0;
		return update_post_meta( $post_id, $this->meta_key_prefix . 'nofollow', $nofollow );
	}
	
	/**
	 * Save canonical URL for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id     The post ID.
	 * @param    string  $canonical   The canonical URL.
	 * @return   bool                  True on success, false on failure.
	 */
	public function save_canonical_url( $post_id, $canonical ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$canonical = esc_url_raw( $canonical );
		return update_post_meta( $post_id, $this->meta_key_prefix . 'canonical', $canonical );
	}
	
	/**
	 * Get the robots meta tag value for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   string              The robots meta value.
	 */
	public function get_robots_meta( $post_id ) {
		$robots = array();
		
		// Default to index,follow
		$robots[] = 'index';
		$robots[] = 'follow';
		
		// Check if noindex is set
		if ( $this->is_noindex( $post_id ) ) {
			$robots = array();
			$robots[] = 'noindex';
		}
		
		// Check if nofollow is set
		if ( $this->is_nofollow( $post_id ) ) {
			$robots = array_diff( $robots, array( 'follow' ) );
			$robots[] = 'nofollow';
		}
		
		return implode( ',', $robots );
	}
	
	/**
	 * Get all technical SEO meta data for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               All technical SEO meta data.
	 */
	public function get_technical_seo_meta( $post_id ) {
		return array(
			'noindex'   => $this->is_noindex( $post_id ),
			'nofollow'  => $this->is_nofollow( $post_id ),
			'canonical' => $this->get_canonical_url( $post_id ),
			'robots'    => $this->get_robots_meta( $post_id ),
		);
	}
	
	/**
	 * Get global SEO settings with caching.
	 *
	 * @since    1.0.0
	 * @return   array               The global SEO settings.
	 */
	public function get_global_settings() {
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		return $cache_manager->remember( 'seo_simple_global_settings', array( $this, 'get_settings_from_db' ), 3600 );
	}

	/**
	 * Get global SEO settings from database.
	 *
	 * @since    1.0.0
	 * @return   array               The global SEO settings.
	 */
	public function get_settings_from_db() {
		// Try to get settings from the new settings system first
		if ( class_exists( 'Seo_Simple_Settings' ) ) {
			$settings = get_option( 'seo_simple_settings', array() );
			if ( ! empty( $settings ) ) {
				return $settings;
			}
		}
		
		// Fallback to individual options
		return array(
			// General Settings
			'seo_enable_posts' => get_option( 'seo_enable_posts', 1 ),
			'seo_enable_pages' => get_option( 'seo_enable_pages', 1 ),
			'seo_enable_categories' => get_option( 'seo_enable_categories', 1 ),
			'seo_enable_tags' => get_option( 'seo_enable_tags', 1 ),

			// Titles & Meta
			'seo_title_separator' => get_option( 'seo_title_separator', '-' ),
			'seo_title_format' => get_option( 'seo_title_format', '%post_title% | %site_name%' ),
			'seo_description_template' => get_option( 'seo_description_template', '' ),
			'seo_home_title' => get_option( 'seo_home_title', '' ),
			'seo_home_description' => get_option( 'seo_home_description', '' ),
			'seo_title_length' => get_option( 'seo_title_length', 60 ),
			'seo_description_length' => get_option( 'seo_description_length', 160 ),

			// Social Settings
			'seo_enable_og' => get_option( 'seo_enable_og', 1 ),
			'seo_enable_twitter' => get_option( 'seo_enable_twitter', 1 ),
			'seo_enable_json_ld' => get_option( 'seo_enable_json_ld', 1 ),
			'seo_og_image' => get_option( 'seo_og_image', '' ),

			// Sitemap Settings
			'seo_enable_sitemap' => get_option( 'seo_enable_sitemap', 1 ),
			'seo_sitemap_include_posts' => get_option( 'seo_sitemap_include_posts', 1 ),
			'seo_sitemap_include_pages' => get_option( 'seo_sitemap_include_pages', 1 ),
			'seo_sitemap_include_categories' => get_option( 'seo_sitemap_include_categories', 1 ),
			'seo_sitemap_include_tags' => get_option( 'seo_sitemap_include_tags', 1 ),

			// Advanced Settings
			'seo_show_metabox' => get_option( 'seo_show_metabox', 1 ),
			'seo_enable_analysis' => get_option( 'seo_enable_analysis', 1 ),
			'seo_enable_readability' => get_option( 'seo_enable_readability', 1 ),
			'seo_enable_robots' => get_option( 'seo_enable_robots', 1 ),
		);
	}

	/**
	 * Clear global settings cache.
	 *
	 * @since    1.0.0
	 * @return   bool      True on success, false on failure.
	 */
	public function clear_settings_cache() {
		$result = true;
		
		// Clear WordPress object cache
		wp_cache_delete( 'seo_simple_global_settings', 'seo_simple' );
		
		// Clear plugin cache
		if ( $this->cache ) {
			$result = $this->cache->delete_settings();
		}
		
		return $result;
	}

	/**
	 * Get multiple post meta values efficiently.
	 *
	 * @since    1.0.0
	 * @param    array     $post_ids    Array of post IDs.
	 * @param    string    $field_name  The meta field name.
	 * @return   array                 Array of meta values indexed by post ID.
	 */
	public function get_meta_for_posts( $post_ids, $field_name ) {
		if ( empty( $post_ids ) || empty( $field_name ) ) {
			return array();
		}

		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		$cache_key = 'seo_simple_meta_' . $field_name . '_' . md5( implode( ',', $post_ids ) );
		
		return $cache_manager->remember( $cache_key, function() use ( $post_ids, $field_name ) {
			global $wpdb;
			$meta_key = $this->meta_key_prefix . $field_name;
			
			// Process in batches to avoid memory issues with large post sets
			$batch_size = 500;
			$post_batches = array_chunk( $post_ids, $batch_size );
			$meta_values = array();
			
			foreach ( $post_batches as $batch ) {
				// Prepare placeholders for IN clause
				$placeholders = implode( ',', array_fill( 0, count( $batch ), '%d' ) );
				
				$sql = $wpdb->prepare(
					"SELECT post_id, meta_value FROM $wpdb->postmeta WHERE post_id IN ($placeholders) AND meta_key = %s",
					array_merge( $batch, array( $meta_key ) )
				);
				
				$results = $wpdb->get_results( $sql );
				
				foreach ( $results as $result ) {
					$meta_values[$result->post_id] = $result->meta_value;
				}
			}
			
			return $meta_values;
		}, 3600 );
	}

	/**
	 * Save multiple post meta values efficiently.
	 *
	 * @since    1.0.0
	 * @param    array     $meta_data   Array of meta data indexed by post ID.
	 * @param    string    $field_name  The meta field name.
	 * @return   bool                  True on success, false on failure.
	 */
	public function save_meta_for_posts( $meta_data, $field_name ) {
		if ( empty( $meta_data ) || empty( $field_name ) ) {
			return false;
		}

		$meta_key = $this->meta_key_prefix . $field_name;
		$post_ids = array_keys( $meta_data );
		$cache_manager = Seo_Simple_Cache_Manager::get_instance();
		
		// Clear cache before updating
		$cache_key = 'seo_simple_meta_' . $field_name . '_' . md5( implode( ',', $post_ids ) );
		$cache_manager->delete( $cache_key );
		
		// Batch update for better performance
		if ( count( $meta_data ) > 100 ) {
			// Use direct database query for large updates
			global $wpdb;
			$values = array();
			$placeholders = array();
			
			foreach ( $meta_data as $post_id => $value ) {
				$values[] = $post_id;
				$values[] = $meta_key;
				$values[] = $value;
				$placeholders[] = '(%d, %s, %s)';
			}
			
			// Insert or update using ON DUPLICATE KEY UPDATE
			$sql = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES " .
			       implode( ', ', $placeholders ) .
			       " ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
			
			$wpdb->query( $wpdb->prepare( $sql, $values ) );
		} else {
			// Use standard WordPress functions for smaller updates
			foreach ( $meta_data as $post_id => $value ) {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
		
		// Clear analysis cache for all affected posts
		foreach ( $post_ids as $post_id ) {
			$cache_manager->delete_group( "seo_simple_post_{$post_id}" );
		}
		
		return true;
	}
	
	/**
	 * Get the SEO title for a taxonomy term.
	 *
	 * @since    1.0.0
	 * @param    WP_Term $term        The term object.
	 * @return   string               The generated SEO title.
	 */
	public function get_term_title( $term ) {
		// Check for custom title meta
		$custom_title = get_term_meta( $term->term_id, '_seo_simple_title', true );
		
		if ( ! empty( $custom_title ) ) {
			return $custom_title;
		}
		
		// Get global settings
		$settings = $this->get_global_settings();
		
		// Get taxonomy specific template
		$taxonomy = $term->taxonomy;
		$title_format = isset( $settings[ 'seo_' . $taxonomy . '_title_format' ] ) ?
			$settings[ 'seo_' . $taxonomy . '_title_format' ] :
			'%term_title% | %site_name%';
		
		$separator = $settings['seo_title_separator'];
		
		// Prepare replacements
		$replacements = array(
			'%term_title%' => $term->name,
			'%site_name%' => get_bloginfo( 'name' ),
			'%separator%' => $separator,
		);
		
		// Replace placeholders
		$title = str_replace( array_keys( $replacements ), array_values( $replacements ), $title_format );
		
		// Apply length limit
		$max_length = intval( $settings['seo_title_length'] );
		if ( is_string( $title ) && strlen( $title ) > $max_length ) {
			$title = substr( $title, 0, $max_length - 3 ) . '...';
		}
		
		return $title;
	}
	
	/**
	 * Get the SEO description for a taxonomy term.
	 *
	 * @since    1.0.0
	 * @param    WP_Term $term        The term object.
	 * @return   string               The generated SEO description.
	 */
	public function get_term_description( $term ) {
		// Check for custom description meta
		$custom_description = get_term_meta( $term->term_id, '_seo_simple_description', true );
		
		if ( ! empty( $custom_description ) ) {
			return $custom_description;
		}
		
		// Get global settings
		$settings = $this->get_global_settings();
		
		// Get taxonomy specific template
		$taxonomy = $term->taxonomy;
		$description_template = isset( $settings[ 'seo_' . $taxonomy . '_description_format' ] ) ?
			$settings[ 'seo_' . $taxonomy . '_description_format' ] :
			'';
		
		// If no template is set, use term description
		if ( empty( $description_template ) ) {
			$description = $term->description;
		} else {
			// Process template
			$term_description = ! empty( $term->description ) ? $term->description : '';
			
			$replacements = array(
				'%term_description%' => $term_description,
				'%term_title%' => $term->name,
			);
			
			$description = str_replace( array_keys( $replacements ), array_values( $replacements ), $description_template );
		}
		
		// Apply length limit
		$max_length = intval( $settings['seo_description_length'] );
		if ( is_string( $description ) && strlen( $description ) > $max_length ) {
			$description = substr( $description, 0, $max_length - 3 ) . '...';
		}
		
		return $description;
	}
	
	/**
	 * Get the homepage title.
	 *
	 * @since    1.0.0
	 * @return   string               The homepage title.
	 */
	public function get_homepage_title() {
		$settings = $this->get_global_settings();
		$home_title = $settings['seo_home_title'];
		
		if ( ! empty( $home_title ) ) {
			return $home_title;
		}
		
		// Fallback to site title with tagline
		$site_name = get_bloginfo( 'name' );
		$site_description = get_bloginfo( 'description' );
		
		if ( ! empty( $site_description ) ) {
			return $site_name . ' ' . $settings['seo_title_separator'] . ' ' . $site_description;
		}
		
		return $site_name;
	}
	
	/**
	 * Get the homepage description.
	 *
	 * @since    1.0.0
	 * @return   string               The homepage description.
	 */
	public function get_homepage_description() {
		$settings = $this->get_global_settings();
		$home_description = $settings['seo_home_description'];
		
		if ( ! empty( $home_description ) ) {
			return $home_description;
		}
		
		// Fallback to site description
		return get_bloginfo( 'description' );
	}
	
	/**
	 * Get the default Open Graph image.
	 *
	 * @since    1.0.0
	 * @return   string               The default OG image URL.
	 */
	public function get_default_og_image() {
		$settings = $this->get_global_settings();
		return $settings['seo_og_image'];
	}
	
	/**
	 * Save deep content analysis results for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    array   $analysis   The deep analysis results.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_deep_analysis( $post_id, $analysis ) {
		if ( empty( $post_id ) || empty( $analysis ) || ! is_array( $analysis ) ) {
			return false;
		}
		
		// Sanitize analysis
		$sanitized_analysis = array();
		foreach ( $analysis as $metric => $data ) {
			if ( is_array( $data ) ) {
				$sanitized_analysis[ $metric ] = array();
				foreach ( $data as $key => $value ) {
					if ( is_numeric( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = floatval( $value );
					} elseif ( is_string( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = sanitize_text_field( $value );
					} elseif ( is_bool( $value ) ) {
						$sanitized_analysis[ $metric ][ $key ] = $value;
					}
				}
			}
		}
		
		// Save to post meta
		return update_post_meta( $post_id, $this->meta_key_prefix . 'deep_analysis', wp_json_encode( $sanitized_analysis ) );
	}
	
	/**
	 * Get deep content analysis results for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $cached     Whether to use cached results.
	 * @return   array               The deep analysis results.
	 */
	public function get_deep_analysis( $post_id, $cached = true ) {
		if ( empty( $post_id ) ) {
			return array();
		}
		
		// Get from post meta
		$analysis = get_post_meta( $post_id, $this->meta_key_prefix . 'deep_analysis', true );
		
		if ( empty( $analysis ) ) {
			return array();
		}
		
		// Decode JSON
		$analysis = json_decode( $analysis, true );
		
		if ( ! is_array( $analysis ) ) {
			return array();
		}
		
		// If not using cached results, check if content has changed
		if ( ! $cached ) {
			$post = get_post( $post_id );
			$content_hash = md5( $post->post_content );
			$stored_hash = get_post_meta( $post_id, $this->meta_key_prefix . 'deep_content_hash', true );
			
			if ( $content_hash !== $stored_hash ) {
				// Content has changed, return empty array to trigger new analysis
				return array();
			}
		}
		
		return $analysis;
	}
	
	/**
	 * Cache deep analysis results by storing content hash.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function cache_deep_analysis_results( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$post = get_post( $post_id );
		$content_hash = md5( $post->post_content );
		
		return update_post_meta( $post_id, $this->meta_key_prefix . 'deep_content_hash', $content_hash );
	}
	
	/**
	 * Clear deep analysis for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function clear_deep_analysis( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		// Delete analysis and content hash
		$result1 = delete_post_meta( $post_id, $this->meta_key_prefix . 'deep_analysis' );
		$result2 = delete_post_meta( $post_id, $this->meta_key_prefix . 'deep_content_hash' );
		
		return $result1 || $result2;
	}
	
	/**
	 * Save SEO title for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    string  $title      The SEO title.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_title( $post_id, $title ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$title = sanitize_text_field( $title );
		return update_post_meta( $post_id, $this->meta_key_prefix . 'title', $title );
	}
	
	/**
	 * Save SEO description for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id        The post ID.
	 * @param    string  $description    The SEO description.
	 * @return   bool                    True on success, false on failure.
	 */
	public function save_description( $post_id, $description ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$description = sanitize_textarea_field( $description );
		return update_post_meta( $post_id, $this->meta_key_prefix . 'description', $description );
	}
	
	/**
	 * Save noindex setting for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    bool    $noindex    Whether to set noindex.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_noindex( $post_id, $noindex ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$noindex = $noindex ? 1 : 0;
		return update_post_meta( $post_id, $this->meta_key_prefix . 'noindex', $noindex );
	}
	
	/**
	 * Get noindex setting for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if noindex is set.
	 */
	public function get_noindex( $post_id ) {
		return (bool) $this->get_meta( $post_id, 'noindex' );
	}
	
	/**
	 * Get nofollow setting for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True if nofollow is set.
	 */
	public function get_nofollow( $post_id ) {
		return (bool) $this->get_meta( $post_id, 'nofollow' );
	}
	
	/**
	 * Save schema data for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    array   $schema     The schema data.
	 * @return   bool                True on success, false on failure.
	 */
	public function save_schema( $post_id, $schema ) {
		if ( empty( $post_id ) || empty( $schema ) || ! is_array( $schema ) ) {
			return false;
		}
		
		// Sanitize schema
		$sanitized_schema = array();
		foreach ( $schema as $key => $value ) {
			if ( is_string( $value ) ) {
				$sanitized_schema[ $key ] = sanitize_text_field( $value );
			} elseif ( is_array( $value ) ) {
				$sanitized_schema[ $key ] = $this->sanitize_schema_array( $value );
			} else {
				$sanitized_schema[ $key ] = $value;
			}
		}
		
		// Save to post meta
		return update_post_meta( $post_id, $this->meta_key_prefix . 'schema', wp_json_encode( $sanitized_schema ) );
	}
	
	/**
	 * Get schema data for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The schema data.
	 */
	public function get_schema( $post_id ) {
		if ( empty( $post_id ) ) {
			return array();
		}
		
		// Get from post meta
		$schema = get_post_meta( $post_id, $this->meta_key_prefix . 'schema', true );
		
		if ( empty( $schema ) ) {
			return array();
		}
		
		// Decode JSON
		$schema = json_decode( $schema, true );
		
		if ( ! is_array( $schema ) ) {
			return array();
		}
		
		return $schema;
	}
	
	/**
	 * Delete all SEO meta for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function delete_all_meta( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}
		
		$meta_fields = array( 'title', 'description', 'keywords', 'focus_keyword', 'noindex', 'nofollow', 'canonical', 'social_title', 'social_description', 'social_image', 'social_image_id', 'use_seo_data', 'schema_type', 'custom_schema', 'schema', 'seo_analysis', 'readability_scores', 'deep_analysis' );
		
		$result = false;
		foreach ( $meta_fields as $field ) {
			$deleted = delete_post_meta( $post_id, $this->meta_key_prefix . $field );
			if ( $deleted ) {
				$result = true;
			}
		}
		
		return $result;
	}
	
	/**
	 * Sanitize schema array recursively.
	 *
	 * @since    1.0.0
	 * @param    array   $schema    The schema array.
	 * @return   array              The sanitized schema array.
	 */
	private function sanitize_schema_array( $schema ) {
		$sanitized = array();
		
		foreach ( $schema as $key => $value ) {
			if ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} elseif ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_schema_array( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}
		
		return $sanitized;
	}
}
