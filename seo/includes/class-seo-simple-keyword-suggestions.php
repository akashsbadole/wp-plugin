<?php

/**
 * Handles Google keyword suggestions functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles Google keyword suggestions functionality.
 *
 * This class handles all operations related to Google keyword suggestions including:
 * - Fetching keyword suggestions from Google Autocomplete API
 * - Caching keyword suggestions for performance
 * - Displaying keyword suggestions in the admin interface
 * - Analyzing keyword competition and search volume
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Keyword_Suggestions {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Keyword_Suggestions    $instance    The single instance of the class.
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
	 * @return   Seo_Simple_Keyword_Suggestions    The single instance of the class.
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
	 * Get keyword suggestions from Google Autocomplete API.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $keyword    The keyword to get suggestions for.
	 * @param    string    $language   The language code (optional).
	 * @param    string    $country    The country code (optional).
	 * @return   array                 The keyword suggestions.
	 */
	public function get_keyword_suggestions( $keyword, $language = 'en', $country = 'us' ) {
		if ( empty( $keyword ) ) {
			return array();
		}

		// Create cache key
		$cache_key = 'seo_simple_keyword_suggestions_' . md5( $keyword . $language . $country );
		
		// Try to get from cache first
		$cached_suggestions = $this->cache_manager->get( $cache_key );
		if ( false !== $cached_suggestions ) {
			return $cached_suggestions;
		}

		// Fetch from Google Autocomplete API
		$suggestions = $this->fetch_from_google_autocomplete( $keyword, $language, $country );
		
		// Cache the results for 24 hours
		$this->cache_manager->set( $cache_key, $suggestions, 86400 );
		
		return $suggestions;
	}

	/**
	 * Fetch keyword suggestions from Google Autocomplete API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $keyword    The keyword to get suggestions for.
	 * @param    string    $language   The language code.
	 * @param    string    $country    The country code.
	 * @return   array                 The keyword suggestions.
	 */
	private function fetch_from_google_autocomplete( $keyword, $language, $country ) {
		$suggestions = array();
		
		// Build the API URL
		$url = 'http://suggestqueries.google.com/complete/search';
		$params = array(
			'client' => 'firefox',
			'q' => $keyword,
			'hl' => $language,
			'gl' => $country,
		);
		
		$url = add_query_arg( $params, $url );
		
		// Make the request
		$response = wp_remote_get( $url, array(
			'timeout' => 10,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
		) );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return $suggestions;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		// Parse the response
		if ( ! empty( $body ) ) {
			// Remove the outer array and quotes
			$body = str_replace( array( 'window.google.ac.h(', ')', '"' ), '', $body );
			$data = json_decode( $body, true );
			
			if ( isset( $data[1] ) && is_array( $data[1] ) ) {
				foreach ( $data[1] as $item ) {
					if ( isset( $item[0] ) ) {
						$suggestion = $item[0];
						// Clean up the suggestion
						$suggestion = html_entity_decode( $suggestion, ENT_QUOTES, 'UTF-8' );
						$suggestion = trim( $suggestion );
						
						if ( ! empty( $suggestion ) && ! in_array( $suggestion, $suggestions ) ) {
							$suggestions[] = $suggestion;
						}
					}
				}
			}
		}
		
		return $suggestions;
	}

	/**
	 * Get related keywords based on a seed keyword.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $keyword    The seed keyword.
	 * @param    int       $limit      The maximum number of suggestions to return.
	 * @return   array                 The related keywords.
	 */
	public function get_related_keywords( $keyword, $limit = 10 ) {
		$related_keywords = array();
		
		// Get suggestions with different modifiers
		$modifiers = array(
			'', // No modifier
			'how to ', // How to
			'what is ', // What is
			'best ', // Best
			'top ', // Top
			'cheap ', // Cheap
			'free ', // Free
			'online ', // Online
			'near me ', // Near me
			'review ', // Review
			'vs ', // Versus
		);
		
		$suggestions = array();
		foreach ( $modifiers as $modifier ) {
			$modified_keyword = $modifier . $keyword;
			$modifier_suggestions = $this->get_keyword_suggestions( $modified_keyword );
			$suggestions = array_merge( $suggestions, $modifier_suggestions );
		}
		
		// Remove duplicates and limit results
		$suggestions = array_unique( $suggestions );
		$suggestions = array_slice( $suggestions, 0, $limit );
		
		// Analyze each suggestion
		foreach ( $suggestions as $suggestion ) {
			$related_keywords[] = array(
				'keyword' => $suggestion,
				'relevance' => $this->calculate_relevance( $keyword, $suggestion ),
				'difficulty' => $this->estimate_difficulty( $suggestion ),
				'volume' => $this->estimate_volume( $suggestion ),
			);
		}
		
		// Sort by relevance
		usort( $related_keywords, function( $a, $b ) {
			return $b['relevance'] <=> $a['relevance'];
		} );
		
		return $related_keywords;
	}

	/**
	 * Calculate relevance score between two keywords.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $keyword1    The first keyword.
	 * @param    string    $keyword2    The second keyword.
	 * @return   float                  The relevance score (0-1).
	 */
	private function calculate_relevance( $keyword1, $keyword2 ) {
		$keyword1 = strtolower( $keyword1 );
		$keyword2 = strtolower( $keyword2 );
		
		// Exact match
		if ( $keyword1 === $keyword2 ) {
			return 1.0;
		}
		
		// Contains the original keyword
		if ( strpos( $keyword2, $keyword1 ) !== false ) {
			return 0.9;
		}
		
		// Calculate word similarity
		$words1 = explode( ' ', $keyword1 );
		$words2 = explode( ' ', $keyword2 );
		
		$common_words = array_intersect( $words1, $words2 );
		$total_words = array_unique( array_merge( $words1, $words2 ) );
		
		if ( empty( $total_words ) ) {
			return 0.0;
		}
		
		return count( $common_words ) / count( $total_words );
	}

	/**
	 * Estimate keyword difficulty based on various factors.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $keyword    The keyword to analyze.
	 * @return   int                   The difficulty score (0-100).
	 */
	private function estimate_difficulty( $keyword ) {
		$difficulty = 50; // Default difficulty
		
		// Increase difficulty for longer keywords
		$word_count = str_word_count( $keyword );
		if ( $word_count >= 4 ) {
			$difficulty += 20;
		} elseif ( $word_count >= 3 ) {
			$difficulty += 10;
		}
		
		// Increase difficulty for commercial intent
		$commercial_terms = array( 'buy', 'price', 'cost', 'cheap', 'discount', 'deal', 'sale', 'offer' );
		foreach ( $commercial_terms as $term ) {
			if ( strpos( strtolower( $keyword ), $term ) !== false ) {
				$difficulty += 15;
				break;
			}
		}
		
		// Decrease difficulty for long-tail keywords
		if ( $word_count >= 5 ) {
			$difficulty -= 15;
		}
		
		// Decrease difficulty for question keywords
		$question_terms = array( 'how', 'what', 'when', 'where', 'why', 'who' );
		foreach ( $question_terms as $term ) {
			if ( strpos( strtolower( $keyword ), $term ) !== false ) {
				$difficulty -= 10;
				break;
			}
		}
		
		// Ensure difficulty is within bounds
		return max( 0, min( 100, $difficulty ) );
	}

	/**
	 * Estimate search volume based on various factors.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $keyword    The keyword to analyze.
	 * @return   int                   The estimated monthly search volume.
	 */
	private function estimate_volume( $keyword ) {
		$volume = 100; // Default volume
		
		// Increase volume for shorter keywords
		$word_count = str_word_count( $keyword );
		if ( $word_count === 1 ) {
			$volume = 1000;
		} elseif ( $word_count === 2 ) {
			$volume = 500;
		} elseif ( $word_count === 3 ) {
			$volume = 200;
		} else {
			$volume = 50;
		}
		
		// Increase volume for commercial intent
		$commercial_terms = array( 'buy', 'price', 'cost', 'cheap', 'discount', 'deal', 'sale', 'offer' );
		foreach ( $commercial_terms as $term ) {
			if ( strpos( strtolower( $keyword ), $term ) !== false ) {
				$volume *= 1.5;
				break;
			}
		}
		
		// Decrease volume for long-tail keywords
		if ( $word_count >= 5 ) {
			$volume *= 0.5;
		}
		
		// Decrease volume for question keywords
		$question_terms = array( 'how', 'what', 'when', 'where', 'why', 'who' );
		foreach ( $question_terms as $term ) {
			if ( strpos( strtolower( $keyword ), $term ) !== false ) {
				$volume *= 0.7;
				break;
			}
		}
		
		// Round to nearest 10
		return round( $volume / 10 ) * 10;
	}

	/**
	 * Get keyword suggestions for a post.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $post_id    The post ID.
	 * @param    int       $limit      The maximum number of suggestions to return.
	 * @return   array                 The keyword suggestions.
	 */
	public function get_post_keyword_suggestions( $post_id, $limit = 10 ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}
		
		// Get the focus keyword
		$focus_keyword = get_post_meta( $post_id, '_seo_simple_focus_keyword', true );
		
		// If no focus keyword, use the post title
		if ( empty( $focus_keyword ) ) {
			$focus_keyword = $post->post_title;
		}
		
		// Get related keywords
		$suggestions = $this->get_related_keywords( $focus_keyword, $limit );
		
		// Filter out keywords that are already used in the post
		$post_content = strtolower( $post->post_content . ' ' . $post->post_title );
		$filtered_suggestions = array();
		
		foreach ( $suggestions as $suggestion ) {
			$keyword = strtolower( $suggestion['keyword'] );
			
			// Skip if the keyword is already in the post content
			if ( strpos( $post_content, $keyword ) !== false ) {
				continue;
			}
			
			$filtered_suggestions[] = $suggestion;
		}
		
		return $filtered_suggestions;
	}

	/**
	 * Get trending keywords based on current content.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $limit      The maximum number of keywords to return.
	 * @return   array                 The trending keywords.
	 */
	public function get_trending_keywords( $limit = 10 ) {
		// Get recent posts
		$recent_posts = get_posts( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'numberposts' => 50,
			'orderby' => 'date',
			'order' => 'DESC',
		) );
		
		if ( empty( $recent_posts ) ) {
			return array();
		}
		
		// Extract keywords from recent posts
		$keywords = array();
		foreach ( $recent_posts as $post ) {
			$focus_keyword = get_post_meta( $post->ID, '_seo_simple_focus_keyword', true );
			
			if ( ! empty( $focus_keyword ) ) {
				$keywords[] = $focus_keyword;
			}
		}
		
		// Get suggestions for each keyword
		$trending_keywords = array();
		foreach ( $keywords as $keyword ) {
			$suggestions = $this->get_related_keywords( $keyword, 3 );
			$trending_keywords = array_merge( $trending_keywords, $suggestions );
		}
		
		// Remove duplicates and sort by volume
		$trending_keywords = array_unique( $trending_keywords, SORT_REGULAR );
		usort( $trending_keywords, function( $a, $b ) {
			return $b['volume'] <=> $a['volume'];
		} );
		
		// Return the top keywords
		return array_slice( $trending_keywords, 0, $limit );
	}

	/**
	 * Clear keyword suggestion cache.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    string    $keyword    The keyword to clear cache for (optional).
	 */
	public function clear_cache( $keyword = '' ) {
		if ( empty( $keyword ) ) {
			// Clear all keyword suggestion cache
			$this->cache_manager->clear_group( 'seo_simple_keyword_suggestions_' );
		} else {
			// Clear cache for specific keyword
			$cache_key = 'seo_simple_keyword_suggestions_' . md5( $keyword );
			$this->cache_manager->delete( $cache_key );
		}
	}

	/**
	 * Get keyword difficulty label.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $difficulty    The difficulty score (0-100).
	 * @return   string                   The difficulty label.
	 */
	public function get_difficulty_label( $difficulty ) {
		if ( $difficulty < 30 ) {
			return __( 'Easy', 'seo-simple' );
		} elseif ( $difficulty < 60 ) {
			return __( 'Medium', 'seo-simple' );
		} else {
			return __( 'Hard', 'seo-simple' );
		}
	}

	/**
	 * Get search volume label.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @param    int       $volume    The estimated monthly search volume.
	 * @return   string              The volume label.
	 */
	public function get_volume_label( $volume ) {
		if ( $volume < 100 ) {
			return __( 'Very Low', 'seo-simple' );
		} elseif ( $volume < 500 ) {
			return __( 'Low', 'seo-simple' );
		} elseif ( $volume < 1000 ) {
			return __( 'Medium', 'seo-simple' );
		} elseif ( $volume < 5000 ) {
			return __( 'High', 'seo-simple' );
		} else {
			return __( 'Very High', 'seo-simple' );
		}
	}
}