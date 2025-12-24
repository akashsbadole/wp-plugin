<?php

/**
 * Handles SEO analysis functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles SEO analysis functionality.
 *
 * This class handles all operations related to SEO analysis including:
 * - Analyzing content for SEO optimization
 * - Checking readability scores
 * - Validating meta tags and structured data
 * - Providing actionable SEO recommendations
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Analyzer {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Analyzer    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * The cache manager instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache_Manager    $cache_manager    The cache manager instance.
	 */
	private $cache_manager;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   Seo_Simple_Analyzer    The single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->cache_manager = Seo_Simple_Cache_Manager::get_instance();
	}

	/**
	 * Analyze content for SEO optimization.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $post_id    The post ID.
	 * @param    string    $content    The content to analyze.
	 * @return   array                 The analysis results.
	 */
	public function analyze_content( $post_id, $content = '' ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// If content is provided, use it, otherwise use post content
		if ( empty( $content ) ) {
			$content = $post->post_content;
		}

		// Create cache key
		$cache_key = 'seo_simple_analysis_' . $post_id . '_' . md5( $content );
		
		// Try to get from cache first
		$cached_analysis = $this->cache_manager->get( $cache_key );
		if ( false !== $cached_analysis ) {
			return $cached_analysis;
		}

		// Get SEO meta data
		$meta = get_post_meta( $post_id, '_seo_simple_meta', true );
		$meta = wp_parse_args( $meta, array(
			'title' => '',
			'description' => '',
			'focus_keyword' => '',
			'noindex' => 0,
			'nofollow' => 0,
		) );

		// Create a temporary post object with the provided content
		$temp_post = clone $post;
		$temp_post->post_content = $content;

		// Perform analysis
		$analysis = array(
			'overall_score' => 0,
			'seo_score' => 0,
			'readability_score' => 0,
			'checks' => array(),
			'recommendations' => array(),
			'focus_keyword' => $meta['focus_keyword'],
		);

		// SEO checks
		$seo_checks = $this->perform_seo_checks( $temp_post, $meta );
		$analysis['checks'] = array_merge( $analysis['checks'], $seo_checks );

		// Readability checks
		$readability_checks = $this->perform_readability_checks( $temp_post );
		$analysis['checks'] = array_merge( $analysis['checks'], $readability_checks );

		// Calculate scores
		$analysis['seo_score'] = $this->calculate_seo_score( $seo_checks );
		$analysis['readability_score'] = $this->calculate_readability_score( $readability_checks );
		$analysis['overall_score'] = round( ( $analysis['seo_score'] + $analysis['readability_score'] ) / 2 );

		// Generate recommendations
		$analysis['recommendations'] = $this->generate_recommendations( $analysis['checks'] );

		// Cache the results for 1 hour
		$this->cache_manager->set( $cache_key, $analysis, 3600 );
		
		return $analysis;
	}

	/**
	 * Analyze a post for SEO optimization.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int      $post_id    The post ID to analyze.
	 * @return   array                The analysis results.
	 */
	public function analyze_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Create cache key
		$cache_key = 'seo_simple_analysis_' . $post_id . '_' . md5( $post->post_modified );
		
		// Try to get from cache first
		$cached_analysis = $this->cache_manager->get( $cache_key );
		if ( false !== $cached_analysis ) {
			return $cached_analysis;
		}

		// Get SEO meta data
		$meta = get_post_meta( $post_id, '_seo_simple_meta', true );
		$meta = wp_parse_args( $meta, array(
			'title' => '',
			'description' => '',
			'focus_keyword' => '',
			'noindex' => 0,
			'nofollow' => 0,
		) );

		// Perform analysis
		$analysis = array(
			'overall_score' => 0,
			'seo_score' => 0,
			'readability_score' => 0,
			'checks' => array(),
			'recommendations' => array(),
			'focus_keyword' => $meta['focus_keyword'],
		);

		// SEO checks
		$seo_checks = $this->perform_seo_checks( $post, $meta );
		$analysis['checks'] = array_merge( $analysis['checks'], $seo_checks );

		// Readability checks
		$readability_checks = $this->perform_readability_checks( $post );
		$analysis['checks'] = array_merge( $analysis['checks'], $readability_checks );

		// Calculate scores
		$analysis['seo_score'] = $this->calculate_seo_score( $seo_checks );
		$analysis['readability_score'] = $this->calculate_readability_score( $readability_checks );
		$analysis['overall_score'] = round( ( $analysis['seo_score'] + $analysis['readability_score'] ) / 2 );

		// Generate recommendations
		$analysis['recommendations'] = $this->generate_recommendations( $analysis['checks'] );

		// Cache the results for 1 hour
		$this->cache_manager->set( $cache_key, $analysis, 3600 );
		
		return $analysis;
	}

	/**
	 * Perform SEO checks on a post.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    WP_Post    $post    The post object.
	 * @param    array      $meta    The SEO meta data.
	 * @return   array               The SEO checks results.
	 */
	private function perform_seo_checks( $post, $meta ) {
		$checks = array();
		$content = $post->post_content;
		$focus_keyword = ! empty( $meta['focus_keyword'] ) ? strtolower( $meta['focus_keyword'] ) : '';

		// Check if focus keyword is set
		$checks['focus_keyword_set'] = array(
			'title' => __( 'Focus Keyword Set', 'seo-simple' ),
			'status' => ! empty( $focus_keyword ) ? 'good' : 'warning',
			'message' => ! empty( $focus_keyword ) 
				? __( 'Focus keyword is set.', 'seo-simple' ) 
				: __( 'No focus keyword set.', 'seo-simple' ),
		);

		if ( ! empty( $focus_keyword ) ) {
			// Check if focus keyword is in title
			$title = strtolower( $post->post_title );
			$checks['keyword_in_title'] = array(
				'title' => __( 'Keyword in Title', 'seo-simple' ),
				'status' => strpos( $title, $focus_keyword ) !== false ? 'good' : 'warning',
				'message' => strpos( $title, $focus_keyword ) !== false 
					? __( 'Focus keyword found in title.', 'seo-simple' ) 
					: __( 'Focus keyword not found in title.', 'seo-simple' ),
			);

			// Check if focus keyword is in content
			$content_lower = strtolower( $content );
			$checks['keyword_in_content'] = array(
				'title' => __( 'Keyword in Content', 'seo-simple' ),
				'status' => strpos( $content_lower, $focus_keyword ) !== false ? 'good' : 'warning',
				'message' => strpos( $content_lower, $focus_keyword ) !== false 
					? __( 'Focus keyword found in content.', 'seo-simple' ) 
					: __( 'Focus keyword not found in content.', 'seo-simple' ),
			);

			// Check keyword density
			$word_count = str_word_count( strip_tags( $content ) );
			$keyword_count = substr_count( $content_lower, $focus_keyword );
			$density = $word_count > 0 ? round( ( $keyword_count / $word_count ) * 100, 2 ) : 0;
			
			$density_status = 'good';
			if ( $density < 0.5 || $density > 3 ) {
				$density_status = 'warning';
			}
			
			$checks['keyword_density'] = array(
				'title' => __( 'Keyword Density', 'seo-simple' ),
				'status' => $density_status,
				'message' => sprintf(
					__( 'Keyword density is %s%%. Recommended range is 0.5%% - 3%%.', 'seo-simple' ),
					$density
				),
				'value' => $density,
			);

			// Check if focus keyword is in first paragraph
			$first_paragraph = $this->get_first_paragraph( $content );
			$checks['keyword_in_first_paragraph'] = array(
				'title' => __( 'Keyword in First Paragraph', 'seo-simple' ),
				'status' => strpos( strtolower( $first_paragraph ), $focus_keyword ) !== false ? 'good' : 'warning',
				'message' => strpos( strtolower( $first_paragraph ), $focus_keyword ) !== false 
					? __( 'Focus keyword found in first paragraph.', 'seo-simple' ) 
					: __( 'Focus keyword not found in first paragraph.', 'seo-simple' ),
			);

			// Check if focus keyword is in headings
			$headings = $this->extract_headings( $content );
			$found_in_heading = false;
			foreach ( $headings as $heading ) {
				if ( strpos( strtolower( $heading ), $focus_keyword ) !== false ) {
					$found_in_heading = true;
					break;
				}
			}
			
			$checks['keyword_in_headings'] = array(
				'title' => __( 'Keyword in Headings', 'seo-simple' ),
				'status' => $found_in_heading ? 'good' : 'warning',
				'message' => $found_in_heading 
					? __( 'Focus keyword found in headings.', 'seo-simple' ) 
					: __( 'Focus keyword not found in headings.', 'seo-simple' ),
			);
		}

		// Check meta title
		$meta_title = ! empty( $meta['title'] ) ? $meta['title'] : $post->post_title;
		$title_length = strlen( $meta_title );
		$title_status = 'good';
		if ( $title_length < 30 || $title_length > 60 ) {
			$title_status = 'warning';
		}
		
		$checks['meta_title_length'] = array(
			'title' => __( 'Meta Title Length', 'seo-simple' ),
			'status' => $title_status,
			'message' => sprintf(
				__( 'Meta title is %d characters. Recommended length is 30-60 characters.', 'seo-simple' ),
				$title_length
			),
			'value' => $title_length,
		);

		// Check meta description
		$meta_description = ! empty( $meta['description'] ) ? $meta['description'] : $this->generate_excerpt( $content );
		$description_length = strlen( $meta_description );
		$description_status = 'good';
		if ( $description_length < 120 || $description_length > 160 ) {
			$description_status = 'warning';
		}
		
		$checks['meta_description_length'] = array(
			'title' => __( 'Meta Description Length', 'seo-simple' ),
			'status' => $description_status,
			'message' => sprintf(
				__( 'Meta description is %d characters. Recommended length is 120-160 characters.', 'seo-simple' ),
				$description_length
			),
			'value' => $description_length,
		);

		// Check content length
		$content_length = strlen( strip_tags( $content ) );
		$content_status = 'good';
		if ( $content_length < 300 ) {
			$content_status = 'warning';
		}
		
		$checks['content_length'] = array(
			'title' => __( 'Content Length', 'seo-simple' ),
			'status' => $content_status,
			'message' => sprintf(
				__( 'Content is %d characters. Recommended minimum is 300 characters.', 'seo-simple' ),
				$content_length
			),
			'value' => $content_length,
		);

		// Check images with alt text
		$images = $this->extract_images( $content );
		$images_with_alt = 0;
		foreach ( $images as $image ) {
			if ( ! empty( $image['alt'] ) ) {
				$images_with_alt++;
			}
		}
		
		$image_status = 'good';
		if ( count( $images ) > 0 && $images_with_alt < count( $images ) ) {
			$image_status = 'warning';
		}
		
		$checks['image_alt_text'] = array(
			'title' => __( 'Image Alt Text', 'seo-simple' ),
			'status' => $image_status,
			'message' => sprintf(
				__( '%d of %d images have alt text.', 'seo-simple' ),
				$images_with_alt,
				count( $images )
			),
		);

		// Check internal links
		$internal_links = $this->count_internal_links( $content );
		$links_status = 'good';
		if ( $internal_links < 1 ) {
			$links_status = 'warning';
		}
		
		$checks['internal_links'] = array(
			'title' => __( 'Internal Links', 'seo-simple' ),
			'status' => $links_status,
			'message' => sprintf(
				_n( 'Content has %d internal link.', 'Content has %d internal links.', $internal_links, 'seo-simple' ),
				$internal_links
			),
			'value' => $internal_links,
		);

		// Check external links
		$external_links = $this->count_external_links( $content );
		$checks['external_links'] = array(
			'title' => __( 'External Links', 'seo-simple' ),
			'status' => 'good',
			'message' => sprintf(
				_n( 'Content has %d external link.', 'Content has %d external links.', $external_links, 'seo-simple' ),
				$external_links
			),
			'value' => $external_links,
		);

		return $checks;
	}

	/**
	 * Perform readability checks on a post.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    WP_Post    $post    The post object.
	 * @return   array               The readability checks results.
	 */
	private function perform_readability_checks( $post ) {
		$checks = array();
		$content = $post->post_content;
		$text_content = strip_tags( $content );
		$sentences = $this->split_into_sentences( $text_content );
		$paragraphs = $this->split_into_paragraphs( $text_content );
		$headings = $this->extract_headings( $content );

		// Check sentence length
		$sentence_lengths = array_map( 'str_word_count', $sentences );
		$avg_sentence_length = count( $sentence_lengths ) > 0 
			? round( array_sum( $sentence_lengths ) / count( $sentence_lengths ), 1 ) 
			: 0;
		
		$sentence_status = 'good';
		if ( $avg_sentence_length > 20 ) {
			$sentence_status = 'warning';
		}
		
		$checks['sentence_length'] = array(
			'title' => __( 'Sentence Length', 'seo-simple' ),
			'status' => $sentence_status,
			'message' => sprintf(
				__( 'Average sentence length is %s words. Recommended maximum is 20 words.', 'seo-simple' ),
				$avg_sentence_length
			),
			'value' => $avg_sentence_length,
		);

		// Check paragraph length
		$paragraph_lengths = array_map( 'str_word_count', $paragraphs );
		$avg_paragraph_length = count( $paragraph_lengths ) > 0 
			? round( array_sum( $paragraph_lengths ) / count( $paragraph_lengths ), 1 ) 
			: 0;
		
		$paragraph_status = 'good';
		if ( $avg_paragraph_length > 150 ) {
			$paragraph_status = 'warning';
		}
		
		$checks['paragraph_length'] = array(
			'title' => __( 'Paragraph Length', 'seo-simple' ),
			'status' => $paragraph_status,
			'message' => sprintf(
				__( 'Average paragraph length is %s words. Recommended maximum is 150 words.', 'seo-simple' ),
				$avg_paragraph_length
			),
			'value' => $avg_paragraph_length,
		);

		// Check subheading distribution
		$heading_count = count( $headings );
		$paragraph_count = count( $paragraphs );
		$heading_ratio = $paragraph_count > 0 ? round( $heading_count / $paragraph_count, 2 ) : 0;
		
		$heading_status = 'good';
		if ( $heading_ratio < 0.1 ) {
			$heading_status = 'warning';
		}
		
		$checks['subheading_distribution'] = array(
			'title' => __( 'Subheading Distribution', 'seo-simple' ),
			'status' => $heading_status,
			'message' => sprintf(
				__( 'Content has %d headings for %d paragraphs. Recommended ratio is at least 1 heading per 10 paragraphs.', 'seo-simple' ),
				$heading_count,
				$paragraph_count
			),
		);

		// Check for passive voice
		$passive_voice_percentage = $this->calculate_passive_voice_percentage( $text_content );
		$passive_status = 'good';
		if ( $passive_voice_percentage > 15 ) {
			$passive_status = 'warning';
		}
		
		$checks['passive_voice'] = array(
			'title' => __( 'Passive Voice', 'seo-simple' ),
			'status' => $passive_status,
			'message' => sprintf(
				__( '%s%% of sentences use passive voice. Recommended maximum is 15%%.', 'seo-simple' ),
				$passive_voice_percentage
			),
			'value' => $passive_voice_percentage,
		);

		// Check transition words
		$transition_words_percentage = $this->calculate_transition_words_percentage( $text_content );
		$transition_status = 'good';
		if ( $transition_words_percentage < 25 ) {
			$transition_status = 'warning';
		}
		
		$checks['transition_words'] = array(
			'title' => __( 'Transition Words', 'seo-simple' ),
			'status' => $transition_status,
			'message' => sprintf(
				__( '%s%% of sentences contain transition words. Recommended minimum is 25%%.', 'seo-simple' ),
				$transition_words_percentage
			),
			'value' => $transition_words_percentage,
		);

		// Calculate Flesch Reading Ease score
		$flesch_score = $this->calculate_flesch_reading_ease( $text_content );
		$flesch_status = 'good';
		if ( $flesch_score < 30 ) {
			$flesch_status = 'warning';
		} elseif ( $flesch_score < 50 ) {
			$flesch_status = 'warning';
		}
		
		$flesch_label = $this->get_flesch_reading_label( $flesch_score );
		$checks['flesch_reading_ease'] = array(
			'title' => __( 'Flesch Reading Ease', 'seo-simple' ),
			'status' => $flesch_status,
			'message' => sprintf(
				__( 'Reading ease score is %d (%s). Higher scores indicate easier readability.', 'seo-simple' ),
				$flesch_score,
				$flesch_label
			),
			'value' => $flesch_score,
		);

		return $checks;
	}

	/**
	 * Calculate SEO score based on checks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $checks    The SEO checks.
	 * @return   int                  The SEO score (0-100).
	 */
	private function calculate_seo_score( $checks ) {
		$total_checks = count( $checks );
		$good_checks = 0;

		foreach ( $checks as $check ) {
			if ( $check['status'] === 'good' ) {
				$good_checks++;
			}
		}

		return $total_checks > 0 ? round( ( $good_checks / $total_checks ) * 100 ) : 0;
	}

	/**
	 * Calculate readability score based on checks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $checks    The readability checks.
	 * @return   int                  The readability score (0-100).
	 */
	private function calculate_readability_score( $checks ) {
		$total_checks = count( $checks );
		$good_checks = 0;

		foreach ( $checks as $check ) {
			if ( $check['status'] === 'good' ) {
				$good_checks++;
			}
		}

		return $total_checks > 0 ? round( ( $good_checks / $total_checks ) * 100 ) : 0;
	}

	/**
	 * Generate recommendations based on checks.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $checks    All checks.
	 * @return   array                The recommendations.
	 */
	private function generate_recommendations( $checks ) {
		$recommendations = array();

		foreach ( $checks as $key => $check ) {
			if ( $check['status'] === 'warning' ) {
				$recommendations[] = array(
					'check' => $key,
					'title' => $check['title'],
					'message' => $check['message'],
					'priority' => $this->get_recommendation_priority( $key ),
				);
			}
		}

		// Sort by priority
		usort( $recommendations, function( $a, $b ) {
			return $b['priority'] - $a['priority'];
		} );

		return $recommendations;
	}

	/**
	 * Get recommendation priority for a check.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $check    The check key.
	 * @return   int                 The priority score.
	 */
	private function get_recommendation_priority( $check ) {
		$priorities = array(
			'focus_keyword_set' => 10,
			'keyword_in_title' => 9,
			'keyword_in_content' => 8,
			'keyword_in_first_paragraph' => 7,
			'keyword_density' => 6,
			'meta_title_length' => 9,
			'meta_description_length' => 8,
			'content_length' => 5,
			'image_alt_text' => 4,
			'internal_links' => 3,
			'sentence_length' => 2,
			'paragraph_length' => 2,
			'subheading_distribution' => 3,
			'passive_voice' => 1,
			'transition_words' => 1,
			'flesch_reading_ease' => 2,
		);

		return isset( $priorities[$check] ) ? $priorities[$check] : 0;
	}

	/**
	 * Get the first paragraph from content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   string                The first paragraph.
	 */
	private function get_first_paragraph( $content ) {
		$content = strip_tags( $content );
		$paragraphs = explode( "\n\n", $content );
		return ! empty( $paragraphs[0] ) ? trim( $paragraphs[0] ) : '';
	}

	/**
	 * Extract headings from content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   array                The headings.
	 */
	private function extract_headings( $content ) {
		$headings = array();
		
		if ( preg_match_all( '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $heading ) {
				$headings[] = strip_tags( $heading );
			}
		}
		
		return $headings;
	}

	/**
	 * Extract images from content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   array                The images.
	 */
	private function extract_images( $content ) {
		$images = array();
		
		if ( preg_match_all( '/<img[^>]+>/i', $content, $matches ) ) {
			foreach ( $matches[0] as $img_tag ) {
				$image = array( 'alt' => '' );
				
				if ( preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match ) ) {
					$image['alt'] = $alt_match[1];
				}
				
				$images[] = $image;
			}
		}
		
		return $images;
	}

	/**
	 * Count internal links in content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   int                   The number of internal links.
	 */
	private function count_internal_links( $content ) {
		$count = 0;
		$home_url = home_url();
		
		if ( preg_match_all( '/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $url ) {
				if ( strpos( $url, $home_url ) === 0 ) {
					$count++;
				}
			}
		}
		
		return $count;
	}

	/**
	 * Count external links in content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   int                   The number of external links.
	 */
	private function count_external_links( $content ) {
		$count = 0;
		$home_url = home_url();
		
		if ( preg_match_all( '/<a[^>]+href=["\']([^"\']*)["\'][^>]*>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $url ) {
				if ( strpos( $url, 'http' ) === 0 && strpos( $url, $home_url ) !== 0 ) {
					$count++;
				}
			}
		}
		
		return $count;
	}

	/**
	 * Split text into sentences.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   array              The sentences.
	 */
	private function split_into_sentences( $text ) {
		$sentences = preg_split( '/(?<=[.?!])\s+(?=[A-Z])/', $text );
		return array_filter( array_map( 'trim', $sentences ) );
	}

	/**
	 * Split text into paragraphs.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   array              The paragraphs.
	 */
	private function split_into_paragraphs( $text ) {
		$paragraphs = explode( "\n\n", $text );
		return array_filter( array_map( 'trim', $paragraphs ) );
	}

	/**
	 * Calculate passive voice percentage.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   float              The passive voice percentage.
	 */
	private function calculate_passive_voice_percentage( $text ) {
		$sentences = $this->split_into_sentences( $text );
		$total_sentences = count( $sentences );
		
		if ( $total_sentences === 0 ) {
			return 0;
		}
		
		$passive_indicators = array(
			'\bwas\b', '\bwere\b', '\bbe\b', '\bbeen\b', '\bbeing\b',
			'\bis\b', '\bare\b', '\bam\b', '\bhas\b', '\bhave\b', '\bhad\b'
		);
		
		$passive_count = 0;
		foreach ( $sentences as $sentence ) {
			foreach ( $passive_indicators as $indicator ) {
				if ( preg_match( '/' . $indicator . '/i', $sentence ) ) {
					$passive_count++;
					break;
				}
			}
		}
		
		return round( ( $passive_count / $total_sentences ) * 100, 1 );
	}

	/**
	 * Calculate transition words percentage.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   float              The transition words percentage.
	 */
	private function calculate_transition_words_percentage( $text ) {
		$sentences = $this->split_into_sentences( $text );
		$total_sentences = count( $sentences );
		
		if ( $total_sentences === 0 ) {
			return 0;
		}
		
		$transition_words = array(
			'also', 'although', 'and', 'as', 'because', 'but', 'consequently',
			'for', 'however', 'in addition', 'in fact', 'in contrast', 'indeed',
			'instead', 'likewise', 'meanwhile', 'moreover', 'nevertheless',
			'nonetheless', 'of course', 'on the other hand', 'or', 'otherwise',
			'similarly', 'so', 'still', 'then', 'therefore', 'though', 'thus',
			'yet', 'above all', 'after all', 'anyway', 'as a result', 'as a rule',
			'for example', 'for instance', 'in any case', 'in any event',
			'in conclusion', 'in short', 'in the first place', 'on the contrary',
			'to sum up', 'to summarize'
		);
		
		$transition_count = 0;
		foreach ( $sentences as $sentence ) {
			$sentence_lower = strtolower( $sentence );
			foreach ( $transition_words as $word ) {
				if ( strpos( $sentence_lower, ' ' . $word . ' ' ) !== false ) {
					$transition_count++;
					break;
				}
			}
		}
		
		return round( ( $transition_count / $total_sentences ) * 100, 1 );
	}

	/**
	 * Calculate Flesch Reading Ease score.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   float              The Flesch Reading Ease score.
	 */
	private function calculate_flesch_reading_ease( $text ) {
		$words = str_word_count( $text );
		$sentences = count( $this->split_into_sentences( $text ) );
		$syllables = $this->count_syllables( $text );
		
		if ( $sentences === 0 || $words === 0 ) {
			return 0;
		}
		
		// Flesch Reading Ease formula: 206.835 - 1.015 * (words/sentences) - 84.6 * (syllables/words)
		$score = 206.835 - (1.015 * ($words / $sentences)) - (84.6 * ($syllables / $words));
		
		return round( $score, 1 );
	}

	/**
	 * Count syllables in text.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The text.
	 * @return   int                The number of syllables.
	 */
	private function count_syllables( $text ) {
		$words = str_word_count( $text, 1 );
		$syllable_count = 0;
		
		foreach ( $words as $word ) {
			$word = strtolower( $word );
			$word = preg_replace( '/[^a-z]/', '', $word );
			
			if ( empty( $word ) ) {
				continue;
			}
			
			// Basic syllable counting algorithm
			$syllables = preg_match_all( '/[aeiouy]+/', $word, $matches );
			$syllable_count += $syllables;
			
			// Adjust for silent e
			if ( substr( $word, -1 ) === 'e' && $syllables > 1 ) {
				$syllable_count--;
			}
			
			// Ensure at least 1 syllable
			if ( $syllables < 1 ) {
				$syllable_count++;
			}
		}
		
		return $syllable_count;
	}

	/**
	 * Get Flesch Reading Ease label.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    float     $score    The Flesch Reading Ease score.
	 * @return   string              The label.
	 */
	private function get_flesch_reading_label( $score ) {
		if ( $score >= 90 ) {
			return __( 'Very Easy', 'seo-simple' );
		} elseif ( $score >= 80 ) {
			return __( 'Easy', 'seo-simple' );
		} elseif ( $score >= 70 ) {
			return __( 'Fairly Easy', 'seo-simple' );
		} elseif ( $score >= 60 ) {
			return __( 'Standard', 'seo-simple' );
		} elseif ( $score >= 50 ) {
			return __( 'Fairly Difficult', 'seo-simple' );
		} elseif ( $score >= 30 ) {
			return __( 'Difficult', 'seo-simple' );
		} else {
			return __( 'Very Difficult', 'seo-simple' );
		}
	}

	/**
	 * Generate excerpt from content.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content    The content.
	 * @return   string                The excerpt.
	 */
	private function generate_excerpt( $content ) {
		$excerpt = strip_tags( $content );
		$excerpt = trim( $excerpt );
		
		if ( is_string( $excerpt ) && strlen( $excerpt ) > 160 ) {
			$excerpt = substr( $excerpt, 0, 157 ) . '...';
		}
		
		return $excerpt;
	}

	/**
	 * Get cached analysis for a post.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $post_id    The post ID.
	 * @return   array|false           The cached analysis or false if not found.
	 */
	public function get_cached_analysis( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		// Create cache key
		$cache_key = 'seo_simple_analysis_' . $post_id . '_' . md5( $post->post_modified );
		
		// Try to get from cache first
		$cached_analysis = $this->cache_manager->get( $cache_key );
		if ( false !== $cached_analysis ) {
			// Add analyzed_at timestamp for display
			$cached_analysis['analyzed_at'] = time();
			
			// Restructure the data to match expected format
			$restructured = array(
				'scores' => array(
					'overall' => $cached_analysis['overall_score'],
					'seo' => $cached_analysis['seo_score'],
					'readability' => $cached_analysis['readability_score'],
				),
				'seo_checks' => array(),
				'readability_checks' => array(),
				'recommendations' => $cached_analysis['recommendations'],
				'analyzed_at' => $cached_analysis['analyzed_at'],
			);
			
			// Separate checks into SEO and readability
			foreach ( $cached_analysis['checks'] as $key => $check ) {
				if ( in_array( $key, array(
					'sentence_length', 'paragraph_length', 'subheading_distribution',
					'passive_voice', 'transition_words', 'flesch_reading_ease'
				) ) ) {
					$restructured['readability_checks'][] = $check;
				} else {
					$restructured['seo_checks'][] = $check;
				}
			}
			
			return $restructured;
		}
		
		return false;
	}

	/**
	 * Clear analysis cache.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $post_id    The post ID (optional).
	 */
	public function clear_cache( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			// Clear all analysis cache
			$this->cache_manager->clear_group( 'seo_simple_analysis_' );
		} else {
			// Clear cache for specific post
			$post = get_post( $post_id );
			if ( $post ) {
				$cache_key = 'seo_simple_analysis_' . $post_id . '_' . md5( $post->post_modified );
				$this->cache_manager->delete( $cache_key );
			}
		}
	}

	/**
	 * Get score color class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $score    The score (0-100).
	 * @return   string              The color class.
	 */
	public function get_score_class( $score ) {
		if ( $score >= 80 ) {
			return 'score-good';
		} elseif ( $score >= 60 ) {
			return 'score-okay';
		} elseif ( $score >= 40 ) {
			return 'score-warning';
		} else {
			return 'score-bad';
		}
	}

	/**
	 * Get status color class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $status    The status (good, warning, error).
	 * @return   string              The color class.
	 */
	public function get_status_class( $status ) {
		switch ( $status ) {
			case 'good':
				return 'status-good';
			case 'warning':
				return 'status-warning';
			case 'error':
				return 'status-error';
			default:
				return '';
		}
	}

	/**
	 * Get status icon HTML.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $status    The status (good, warning, error).
	 * @return   string              The status icon HTML.
	 */
	public function get_status_icon( $status ) {
		switch ( $status ) {
			case 'good':
				return '<span class="dashicons dashicons-yes-alt status-good"></span>';
			case 'warning':
				return '<span class="dashicons dashicons-warning status-warning"></span>';
			case 'error':
				return '<span class="dashicons dashicons-no-alt status-error"></span>';
			default:
				return '';
		}
	}

	/**
	 * Get priority color class.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $priority    The priority score.
	 * @return   string                 The color class.
	 */
	public function get_priority_class( $priority ) {
		if ( $priority >= 8 ) {
			return 'priority-high';
		} elseif ( $priority >= 5 ) {
			return 'priority-medium';
		} else {
			return 'priority-low';
		}
	}

	/**
	 * Get posts that need SEO improvement.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $limit    The maximum number of posts to return.
	 * @return   array               The posts needing improvement.
	 */
	public function get_posts_needing_improvement( $limit = 5 ) {
		$posts = get_posts( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'numberposts' => $limit * 2, // Get more to filter
			'orderby' => 'date',
			'order' => 'DESC',
		) );

		$posts_needing_improvement = array();

		foreach ( $posts as $post ) {
			$analysis = $this->analyze_post( $post->ID );
			
			if ( $analysis['overall_score'] < 70 ) {
				$post_data = $post;
				$post_data->seo_score = $analysis['seo_score'];
				$post_data->readability_score = $analysis['readability_score'];
				$post_data->recommendations_count = count( $analysis['recommendations'] );
				$posts_needing_improvement[] = $post_data;
			}
		}

		// Sort by score (lowest first)
		usort( $posts_needing_improvement, function( $a, $b ) {
			return $a->seo_score - $b->seo_score;
		} );

		return array_slice( $posts_needing_improvement, 0, $limit );
	}

	/**
	 * Get posts missing SEO data.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    The posts missing SEO data.
	 */
	public function get_posts_missing_seo_data() {
		$posts = get_posts( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'numberposts' => 100,
			'orderby' => 'date',
			'order' => 'DESC',
		) );

		$posts_missing_data = array();

		foreach ( $posts as $post ) {
			$meta = get_post_meta( $post->ID, '_seo_simple_meta', true );
			$meta = wp_parse_args( $meta, array(
				'title' => '',
				'description' => '',
				'focus_keyword' => '',
			) );

			// Check if essential SEO data is missing
			if ( empty( $meta['title'] ) || empty( $meta['description'] ) || empty( $meta['focus_keyword'] ) ) {
				$posts_missing_data[] = $post;
			}
		}

		return $posts_missing_data;
	}
}