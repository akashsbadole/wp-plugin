<?php

/**
 * Handles internal linking functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles internal linking functionality.
 *
 * This class handles all operations related to internal linking including
 * finding related content, suggesting internal links, and managing link relationships.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Internal_Links {

	/**
	 * The meta key prefix for internal link data.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $meta_key_prefix    The prefix for internal link meta keys.
	 */
	private $meta_key_prefix = '_seo_simple_internal_links_';

	/**
	 * The minimum similarity score for suggesting internal links.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int       $similarity_threshold    The minimum similarity score (0-100).
	 */
	private $similarity_threshold = 60;

	/**
	 * The maximum number of internal links to suggest.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int       $max_suggestions    The maximum number of suggestions.
	 */
	private $max_suggestions = 5;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Hook into save_post to update internal link data
		add_action( 'save_post', array( $this, 'update_internal_link_data' ), 10, 2 );
		
		// Hook into content analysis to provide internal link suggestions
		add_filter( 'seo_simple_content_analysis', array( $this, 'add_internal_link_analysis' ), 10, 3 );
	}

	/**
	 * Update internal link data when a post is saved.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    WP_Post $post       The post object.
	 */
	public function update_internal_link_data( $post_id, $post ) {
		// Skip if this is an autosave or revision
		if ( wp_is_post_revision( $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Skip if post is not published
		if ( $post->post_status !== 'publish' ) {
			return;
		}
		
		// Extract keywords from the post
		$keywords = $this->extract_keywords( $post );
		
		// Save keywords to post meta
		update_post_meta( $post_id, $this->meta_key_prefix . 'keywords', $keywords );
		
		// Update link relationships
		$this->update_link_relationships( $post_id, $keywords );
	}

	/**
	 * Extract keywords from a post.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post    The post object.
	 * @return   array             The extracted keywords.
	 */
	private function extract_keywords( $post ) {
		// Get post content
		$content = $post->post_content . ' ' . $post->post_title;
		
		// Strip HTML tags and shortcodes
		$content = strip_tags( strip_shortcodes( $content ) );
		
		// Convert to lowercase
		$content = strtolower( $content );
		
		// Remove special characters except spaces
		$content = preg_replace( '/[^a-z0-9\s]/', ' ', $content );
		
		// Split into words
		$words = explode( ' ', $content );
		
		// Filter out common words (stop words)
		$stop_words = $this->get_stop_words();
		$words = array_diff( $words, $stop_words );
		
		// Count word frequency
		$word_count = array_count_values( $words );
		
		// Filter out words that are too short or too infrequent
		$keywords = array();
		foreach ( $word_count as $word => $count ) {
			if ( strlen( $word ) >= 3 && $count >= 2 ) {
				$keywords[$word] = $count;
			}
		}
		
		// Sort by frequency
		arsort( $keywords );
		
		// Return top keywords
		return array_slice( $keywords, 0, 20, true );
	}

	/**
	 * Get a list of common stop words.
	 *
	 * @since    1.0.0
	 * @return   array    The list of stop words.
	 */
	private function get_stop_words() {
		return array(
			'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at', 'be', 'because', 'been',
			'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can', 'did', 'do', 'does', 'doing', 'down', 'during', 'each',
			'few', 'for', 'from', 'further', 'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself',
			'his', 'how', 'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself', 'just', 'me', 'more', 'most', 'my', 'myself', 'no',
			'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own',
			'same', 'she', 'should', 'so', 'some', 'such', 'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then',
			'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'we', 'were',
			'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'you', 'your', 'yours', 'yourself', 'yourselves'
		);
	}

	/**
	 * Update link relationships between posts.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @param    array   $keywords   The post keywords.
	 */
	private function update_link_relationships( $post_id, $keywords ) {
		// Get all published posts
		$posts = get_posts( array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'exclude' => array( $post_id ),
		) );
		
		$related_posts = array();
		
		foreach ( $posts as $post ) {
			// Get keywords for the other post
			$other_keywords = get_post_meta( $post->ID, $this->meta_key_prefix . 'keywords', true );
			
			if ( ! empty( $other_keywords ) && is_array( $other_keywords ) ) {
				// Calculate similarity
				$similarity = $this->calculate_similarity( $keywords, $other_keywords );
				
				if ( $similarity >= $this->similarity_threshold ) {
					$related_posts[$post->ID] = $similarity;
				}
			}
		}
		
		// Sort by similarity
		arsort( $related_posts );
		
		// Save related posts
		update_post_meta( $post_id, $this->meta_key_prefix . 'related_posts', $related_posts );
	}

	/**
	 * Calculate similarity between two keyword arrays.
	 *
	 * @since    1.0.0
	 * @param    array   $keywords1    The first keyword array.
	 * @param    array   $keywords2    The second keyword array.
	 * @return   float                  The similarity score (0-100).
	 */
	private function calculate_similarity( $keywords1, $keywords2 ) {
		// Get common keywords
		$common_keywords = array_intersect_key( $keywords1, $keywords2 );
		
		if ( empty( $common_keywords ) ) {
			return 0;
		}
		
		// Calculate total frequency of common keywords
		$total_frequency = 0;
		foreach ( $common_keywords as $keyword => $frequency ) {
			$total_frequency += min( $keywords1[$keyword], $keywords2[$keyword] );
		}
		
		// Calculate total frequency of all keywords
		$total_keywords1 = array_sum( $keywords1 );
		$total_keywords2 = array_sum( $keywords2 );
		
		// Calculate Jaccard similarity
		$similarity = ( 2 * $total_frequency ) / ( $total_keywords1 + $total_keywords2 );
		
		// Convert to percentage
		return round( $similarity * 100, 2 );
	}

	/**
	 * Get internal link suggestions for a post.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The internal link suggestions.
	 */
	public function get_internal_link_suggestions( $post_id ) {
		// Get related posts
		$related_posts = get_post_meta( $post_id, $this->meta_key_prefix . 'related_posts', true );
		
		if ( empty( $related_posts ) || ! is_array( $related_posts ) ) {
			return array();
		}
		
		// Get the current post
		$current_post = get_post( $post_id );
		if ( ! $current_post ) {
			return array();
		}
		
		// Get current post keywords
		$current_keywords = get_post_meta( $post_id, $this->meta_key_prefix . 'keywords', true );
		if ( empty( $current_keywords ) || ! is_array( $current_keywords ) ) {
			$current_keywords = $this->extract_keywords( $current_post );
		}
		
		$suggestions = array();
		$count = 0;
		
		foreach ( $related_posts as $related_post_id => $similarity ) {
			if ( $count >= $this->max_suggestions ) {
				break;
			}
			
			$related_post = get_post( $related_post_id );
			if ( ! $related_post ) {
				continue;
			}
			
			// Get related post keywords
			$related_keywords = get_post_meta( $related_post_id, $this->meta_key_prefix . 'keywords', true );
			if ( empty( $related_keywords ) || ! is_array( $related_keywords ) ) {
				$related_keywords = $this->extract_keywords( $related_post );
			}
			
			// Find common keywords
			$common_keywords = array_intersect_key( $current_keywords, $related_keywords );
			
			// Find anchor text suggestions
			$anchor_texts = $this->find_anchor_text_suggestions( $current_post, $common_keywords );
			
			$suggestions[] = array(
				'post_id' => $related_post_id,
				'title' => get_the_title( $related_post ),
				'url' => get_permalink( $related_post_id ),
				'similarity' => $similarity,
				'common_keywords' => array_keys( $common_keywords ),
				'anchor_texts' => $anchor_texts,
			);
			
			$count++;
		}
		
		return $suggestions;
	}

	/**
	 * Find anchor text suggestions in the current post.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post              The current post.
	 * @param    array   $common_keywords   The common keywords.
	 * @return   array                     The anchor text suggestions.
	 */
	private function find_anchor_text_suggestions( $post, $common_keywords ) {
		$content = $post->post_content;
		$anchor_texts = array();
		
		// Look for exact keyword matches
		foreach ( $common_keywords as $keyword => $frequency ) {
			if ( stripos( $content, $keyword ) !== false ) {
				// Find the context around the keyword
				$pattern = '/.{0,50}\b' . preg_quote( $keyword, '/' ) . '\b.{0,50}/i';
				preg_match_all( $pattern, $content, $matches );
				
				if ( ! empty( $matches[0] ) ) {
					foreach ( $matches[0] as $match ) {
						// Clean up the match
						$match = trim( strip_tags( $match ) );
						if ( strlen( $match ) > 10 ) {
							$anchor_texts[] = $match;
						}
					}
				}
			}
		}
		
		// Remove duplicates and limit to 5 suggestions
		$anchor_texts = array_unique( $anchor_texts );
		return array_slice( $anchor_texts, 0, 5 );
	}

	/**
	 * Add internal link analysis to content analysis.
	 *
	 * @since    1.0.0
	 * @param    array   $analysis    The current analysis.
	 * @param    int     $post_id     The post ID.
	 * @param    WP_Post $post        The post object.
	 * @return   array                The updated analysis.
	 */
	public function add_internal_link_analysis( $analysis, $post_id, $post ) {
		// Get internal link suggestions
		$suggestions = $this->get_internal_link_suggestions( $post_id );
		
		// Count existing internal links
		$content = $post->post_content;
		$home_url = home_url();
		$internal_link_count = 0;
		
		// Simple regex to find internal links
		$pattern = '/<a[^>]+href=["\'](' . preg_quote( $home_url, '/' ) . '[^"\']*)["\'][^>]*>/i';
		if ( preg_match_all( $pattern, $content, $matches ) ) {
			$internal_link_count = count( $matches[0] );
		}
		
		// Determine status based on link count
		if ( $internal_link_count >= 5 ) {
			$status = 'good';
			$message = __( 'Good number of internal links', 'seo-simple' );
		} elseif ( $internal_link_count >= 3 ) {
			$status = 'ok';
			$message = __( 'Consider adding more internal links', 'seo-simple' );
		} else {
			$status = 'bad';
			$message = __( 'Add more internal links to improve SEO', 'seo-simple' );
		}
		
		// Add internal link analysis
		$analysis['internal_links'] = array(
			'count' => $internal_link_count,
			'status' => $status,
			'message' => $message,
			'suggestions' => $suggestions,
		);
		
		return $analysis;
	}

	/**
	 * Get posts that can be linked to based on keywords.
	 *
	 * @since    1.0.0
	 * @param    string  $keyword    The keyword to search for.
	 * @param    int     $limit      The maximum number of results.
	 * @return   array               The matching posts.
	 */
	public function get_posts_by_keyword( $keyword, $limit = 10 ) {
		// Get all published posts
		$posts = get_posts( array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		) );
		
		$matching_posts = array();
		
		foreach ( $posts as $post ) {
			// Get keywords for the post
			$keywords = get_post_meta( $post->ID, $this->meta_key_prefix . 'keywords', true );
			
			if ( ! empty( $keywords ) && is_array( $keywords ) && isset( $keywords[$keyword] ) ) {
				$matching_posts[] = array(
					'post_id' => $post->ID,
					'title' => get_the_title( $post ),
					'url' => get_permalink( $post->ID ),
					'relevance' => $keywords[$keyword],
				);
			}
		}
		
		// Sort by relevance
		usort( $matching_posts, function( $a, $b ) {
			return $b['relevance'] - $a['relevance'];
		} );
		
		// Limit results
		return array_slice( $matching_posts, 0, $limit );
	}

	/**
	 * Get orphaned posts (posts with few or no internal links pointing to them).
	 *
	 * @since    1.0.0
	 * @param    int     $limit      The maximum number of results.
	 * @return   array               The orphaned posts.
	 */
	public function get_orphaned_posts( $limit = 10 ) {
		global $wpdb;
		
		// Get all published posts
		$posts = get_posts( array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		) );
		
		$orphaned_posts = array();
		
		foreach ( $posts as $post ) {
			// Count internal links to this post
			$link_count = 0;
			
			// Search for links to this post in all other posts
			$post_url = get_permalink( $post->ID );
			
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT COUNT(*) as count FROM {$wpdb->posts} 
				 WHERE post_content LIKE %s AND post_status = 'publish' AND ID != %d",
				'%' . $post_url . '%',
				$post->ID
			) );
			
			if ( ! empty( $results ) ) {
				$link_count = intval( $results[0]->count );
			}
			
			// Consider posts with fewer than 2 internal links as orphaned
			if ( $link_count < 2 ) {
				$orphaned_posts[] = array(
					'post_id' => $post->ID,
					'title' => get_the_title( $post ),
					'url' => $post_url,
					'link_count' => $link_count,
				);
			}
		}
		
		// Sort by link count (fewest links first)
		usort( $orphaned_posts, function( $a, $b ) {
			return $a['link_count'] - $b['link_count'];
		} );
		
		// Limit results
		return array_slice( $orphaned_posts, 0, $limit );
	}
}