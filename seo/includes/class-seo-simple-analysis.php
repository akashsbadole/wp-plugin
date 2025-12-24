<?php

/**
 * Handles content analysis for readability and SEO.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles content analysis for readability and SEO.
 *
 * This class provides methods to analyze content for various readability metrics
 * including sentence length, paragraph length, subheading distribution, passive voice,
 * transition words, and Flesch Reading Ease Score. It also provides SEO analysis
 * methods for focus keyword optimization.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Analysis {

	/**
	 * The cache handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Cache    $cache    The cache handler instance.
	 */
	private $cache;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Initialize cache handler if available
		if ( class_exists( 'Seo_Simple_Cache' ) ) {
			$this->cache = new Seo_Simple_Cache();
		}
	}

	/**
	 * Analyze sentence length in the content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with average, status, and recommendation.
	 */
	public function analyze_sentence_length( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'average' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Split into sentences (basic approach)
		$sentences = preg_split( '/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		
		$total_words = 0;
		foreach ( $sentences as $sentence ) {
			$words = preg_split( '/\s+/', trim( $sentence ), -1, PREG_SPLIT_NO_EMPTY );
			$total_words += count( $words );
		}
		
		$average = $total_words / count( $sentences );
		
		// Determine status based on average words per sentence
		if ( $average <= 15 ) {
			$status = 'good';
			$score = 100;
			$recommendation = __( 'Great! Your sentences are easy to read.', 'seo-simple' );
		} elseif ( $average <= 20 ) {
			$status = 'ok';
			$score = 70;
			$recommendation = __( 'Consider shortening some sentences for better readability.', 'seo-simple' );
		} else {
			$status = 'bad';
			$score = 40;
			$recommendation = __( 'Your sentences are too long. Break them into shorter ones.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'average' => round( $average, 1 ),
			'status' => $status,
			'recommendation' => $recommendation,
			'total_sentences' => count( $sentences )
		);
	}

	/**
	 * Analyze paragraph length in the content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with status and recommendation.
	 */
	public function analyze_paragraph_length( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Split into paragraphs
		$paragraphs = preg_split( '/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY );
		
		$long_paragraphs = 0;
		$total_paragraphs = count( $paragraphs );
		
		foreach ( $paragraphs as $paragraph ) {
			$words = preg_split( '/\s+/', trim( $paragraph ), -1, PREG_SPLIT_NO_EMPTY );
			if ( count( $words ) > 150 ) {
				$long_paragraphs++;
			}
		}
		
		$percentage_long = ( $long_paragraphs / $total_paragraphs ) * 100;
		
		// Determine status based on percentage of long paragraphs
		if ( $percentage_long <= 25 ) {
			$status = 'good';
			$score = 100;
			$recommendation = __( 'Great! Your paragraphs are well-structured.', 'seo-simple' );
		} elseif ( $percentage_long <= 50 ) {
			$status = 'ok';
			$score = 70;
			$recommendation = __( 'Some paragraphs are too long. Consider breaking them up.', 'seo-simple' );
		} else {
			$status = 'bad';
			$score = 40;
			$recommendation = __( 'Many paragraphs are too long. Break them into smaller ones.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'total_paragraphs' => $total_paragraphs,
			'long_paragraphs' => $long_paragraphs
		);
	}

	/**
	 * Analyze subheading distribution in the content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with status and recommendation.
	 */
	public function analyze_subheading_distribution( $content ) {
		// Count H2 and H3 headings
		$h2_count = substr_count( $content, '<h2' );
		$h3_count = substr_count( $content, '<h3' );
		
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' ),
				'h2_count' => $h2_count,
				'h3_count' => $h3_count
			);
		}
		
		// Split into paragraphs
		$paragraphs = preg_split( '/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY );
		$paragraph_count = count( $paragraphs );
		
		if ( $paragraph_count <= 1 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add more content to analyze subheading distribution.', 'seo-simple' ),
				'h2_count' => $h2_count,
				'h3_count' => $h3_count
			);
		}
		
		$total_headings = $h2_count + $h3_count;
		
		// Calculate paragraphs per heading
		$paragraphs_per_heading = $total_headings > 0 ? $paragraph_count / $total_headings : $paragraph_count;
		
		// Determine status
		if ( $total_headings >= 3 && $paragraphs_per_heading <= 5 ) {
			$status = 'good';
			$score = 100;
			$recommendation = __( 'Great! Your content is well-structured with appropriate headings.', 'seo-simple' );
		} elseif ( $total_headings >= 1 && $paragraphs_per_heading <= 8 ) {
			$status = 'ok';
			$score = 70;
			$recommendation = __( 'Consider adding more subheadings to improve content structure.', 'seo-simple' );
		} else {
			$status = 'bad';
			$score = 40;
			$recommendation = __( 'Add more subheadings to break up your content and improve readability.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'h2_count' => $h2_count,
			'h3_count' => $h3_count,
			'paragraphs_per_heading' => round( $paragraphs_per_heading, 1 )
		);
	}

	/**
	 * Detect passive voice usage in the content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with status and recommendation.
	 */
	public function detect_passive_voice( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Basic passive voice patterns
		$passive_patterns = array(
			'/\b(is|are|was|were|be|been|being)\s+(\w+ed\s+by|ing\s+by)\b/i',
			'/\b(is|are|was|were|be|been|being)\s+(\w+ed\b)(?!.*by)/i',
			'/\b(has|have|had)\s+been\s+(\w+ed)\b/i'
		);
		
		$passive_count = 0;
		foreach ( $passive_patterns as $pattern ) {
			$matches = array();
			preg_match_all( $pattern, $text, $matches );
			$passive_count += count( $matches[0] );
		}
		
		// Count sentences
		$sentences = preg_split( '/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		$sentence_count = count( $sentences );
		
		if ( $sentence_count == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		$percentage_passive = ( $passive_count / $sentence_count ) * 100;
		
		// Determine status
		if ( $percentage_passive <= 10 ) {
			$status = 'good';
			$score = 100;
			$recommendation = __( 'Great! Minimal passive voice usage.', 'seo-simple' );
		} elseif ( $percentage_passive <= 20 ) {
			$status = 'ok';
			$score = 70;
			$recommendation = __( 'Consider reducing passive voice for more engaging content.', 'seo-simple' );
		} else {
			$status = 'bad';
			$score = 40;
			$recommendation = __( 'High passive voice usage. Use active voice for clearer writing.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'passive_count' => $passive_count,
			'percentage_passive' => round( $percentage_passive, 1 )
		);
	}

	/**
	 * Calculate transition words usage in the content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with status and recommendation.
	 */
	public function calculate_transition_words( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		$text = strtolower( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Common transition words
		$transition_words = array(
			'also', 'although', 'and', 'because', 'but', 'consequently', 'finally',
			'for example', 'for instance', 'furthermore', 'however', 'in addition',
			'in conclusion', 'in fact', 'in summary', 'indeed', 'instead', 'likewise',
			'meanwhile', 'moreover', 'nevertheless', 'nonetheless', 'of course',
			'on the other hand', 'otherwise', 'similarly', 'still', 'therefore',
			'thus', 'yet', 'above all', 'after all', 'as a result', 'at the same time',
			'even so', 'for this reason', 'in other words', 'in the same way'
		);
		
		$total_words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$total_word_count = count( $total_words );
		
		if ( $total_word_count == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		$transition_count = 0;
		foreach ( $transition_words as $word ) {
			$transition_count += substr_count( $text, ' ' . $word . ' ' );
			$transition_count += substr_count( $text, ' ' . $word . '.' );
			$transition_count += substr_count( $text, ' ' . $word . ',' );
		}
		
		$percentage_transition = ( $transition_count / $total_word_count ) * 100;
		
		// Determine status
		if ( $percentage_transition >= 3 ) {
			$status = 'good';
			$score = 100;
			$recommendation = __( 'Great! Good use of transition words.', 'seo-simple' );
		} elseif ( $percentage_transition >= 1.5 ) {
			$status = 'ok';
			$score = 70;
			$recommendation = __( 'Consider adding more transition words to improve flow.', 'seo-simple' );
		} else {
			$status = 'bad';
			$score = 40;
			$recommendation = __( 'Add more transition words to improve content flow.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'transition_count' => $transition_count,
			'percentage_transition' => round( $percentage_transition, 1 )
		);
	}

	/**
	 * Calculate Flesch Reading Ease Score.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with score, status, and recommendation.
	 */
	public function calculate_flesch_reading_ease( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Count sentences
		$sentences = preg_split( '/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		$sentence_count = count( $sentences );
		
		// Count words
		$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$word_count = count( $words );
		
		if ( $sentence_count == 0 || $word_count == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Please add some content before analyzing readability.', 'seo-simple' )
			);
		}
		
		// Count syllables (simplified approach)
		$syllable_count = 0;
		foreach ( $words as $word ) {
			$syllable_count += $this->count_syllables( $word );
		}
		
		// Calculate Flesch Reading Ease Score
		$avg_sentence_length = $word_count / $sentence_count;
		$avg_syllables_per_word = $syllable_count / $word_count;
		$flesch_score = 206.835 - (1.015 * $avg_sentence_length) - (84.6 * $avg_syllables_per_word);
		
		// Ensure score is within bounds
		$flesch_score = max( 0, min( 100, $flesch_score ) );
		
		// Determine status
		if ( $flesch_score >= 60 ) {
			$status = 'good';
			$recommendation = __( 'Great! Your content is easy to read.', 'seo-simple' );
		} elseif ( $flesch_score >= 30 ) {
			$status = 'ok';
			$recommendation = __( 'Your content is moderately difficult to read. Consider simplifying.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Your content is difficult to read. Simplify sentences and words.', 'seo-simple' );
		}
		
		return array(
			'score' => round( $flesch_score, 1 ),
			'status' => $status,
			'recommendation' => $recommendation
		);
	}

	/**
	 * Count syllables in a word (simplified approach).
	 *
	 * @since    1.0.0
	 * @param    string    $word    The word to analyze.
	 * @return   int                The number of syllables.
	 */
	private function count_syllables( $word ) {
		$word = strtolower( $word );
		$word = preg_replace( '/[^a-z]/', '', $word );
		
		if ( strlen( $word ) <= 3 ) {
			return 1;
		}
		
		// Basic syllable counting rules
		$word = preg_replace( '/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word );
		$word = preg_replace( '/^y/', '', $word );
		$matches = preg_match_all( '/[aeiouy]{1,2}/', $word, $matches );
		
		return max( 1, $matches );
	}

	/**
	 * Calculate overall readability score.
	 *
	 * @since    1.0.0
	 * @param    array    $analyses    Array of individual analysis results.
	 * @return   array                Overall score with status and recommendation.
	 */
	public function calculate_overall_score( $analyses ) {
		if ( empty( $analyses ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No content analysis available.', 'seo-simple' )
			);
		}
		
		$total_score = 0;
		$count = 0;
		
		foreach ( $analyses as $analysis ) {
			if ( isset( $analysis['score'] ) ) {
				$total_score += $analysis['score'];
				$count++;
			}
		}
		
		if ( $count == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No valid analysis results.', 'seo-simple' )
			);
		}
		
		$overall_score = $total_score / $count;
		
		// Determine status
		if ( $overall_score >= 80 ) {
			$status = 'good';
			$recommendation = __( 'Excellent! Your content is highly readable.', 'seo-simple' );
		} elseif ( $overall_score >= 60 ) {
			$status = 'ok';
			$recommendation = __( 'Good! Your content is fairly readable with room for improvement.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Your content needs improvement for better readability.', 'seo-simple' );
		}
		
		return array(
			'score' => round( $overall_score, 1 ),
			'status' => $status,
			'recommendation' => $recommendation
		);
	}

	/**
	 * Analyze focus keyword usage in title.
	 *
	 * @since    1.0.0
	 * @param    string    $title          The title to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with status and recommendation.
	 */
	public function analyze_keyword_in_title( $title, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		if ( empty( $title ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add a title to your content.', 'seo-simple' ),
				'found' => false
			);
		}
		
		$found = false;
		$score = 0;
		$status = 'bad';
		
		// Check if keyword is in title (case-insensitive)
		if ( stripos( $title, $focus_keyword ) !== false ) {
			$found = true;
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your focus keyword is in the title.', 'seo-simple' );
		} else {
			$recommendation = __( 'Add your focus keyword to the title.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'found' => $found
		);
	}

	/**
	 * Analyze focus keyword usage in meta description.
	 *
	 * @since    1.0.0
	 * @param    string    $description    The meta description to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with status and recommendation.
	 */
	public function analyze_keyword_in_description( $description, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		if ( empty( $description ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add a meta description to your content.', 'seo-simple' ),
				'found' => false
			);
		}
		
		$found = false;
		$score = 0;
		$status = 'bad';
		
		// Check if keyword is in description (case-insensitive)
		if ( stripos( $description, $focus_keyword ) !== false ) {
			$found = true;
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your focus keyword is in the meta description.', 'seo-simple' );
		} else {
			$recommendation = __( 'Add your focus keyword to the meta description.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'found' => $found
		);
	}

	/**
	 * Analyze focus keyword usage in URL slug.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id        The post ID.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with status and recommendation.
	 */
	public function analyze_keyword_in_url( $post_id, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Invalid post ID.', 'seo-simple' ),
				'found' => false
			);
		}
		
		$permalink = get_permalink( $post_id );
		$url_parts = wp_parse_url( $permalink );
		$path = isset( $url_parts['path'] ) ? $url_parts['path'] : '';
		
		$found = false;
		$score = 0;
		$status = 'bad';
		
		// Check if keyword is in URL (case-insensitive)
		if ( stripos( $path, $focus_keyword ) !== false ) {
			$found = true;
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your focus keyword is in the URL.', 'seo-simple' );
		} else {
			$recommendation = __( 'Include your focus keyword in the URL.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'found' => $found,
			'url' => $permalink
		);
	}

	/**
	 * Analyze focus keyword usage in first paragraph.
	 *
	 * @since    1.0.0
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with status and recommendation.
	 */
	public function analyze_keyword_in_first_paragraph( $content, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		// Strip HTML tags and get first paragraph
		$text = strip_tags( strip_shortcodes( $content ) );
		$paragraphs = preg_split( '/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY );
		
		if ( empty( $paragraphs ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content with paragraphs to analyze.', 'seo-simple' ),
				'found' => false
			);
		}
		
		$first_paragraph = trim( $paragraphs[0] );
		$found = false;
		$score = 0;
		$status = 'bad';
		
		// Check if keyword is in first paragraph (case-insensitive)
		if ( stripos( $first_paragraph, $focus_keyword ) !== false ) {
			$found = true;
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your focus keyword is in the first paragraph.', 'seo-simple' );
		} else {
			$recommendation = __( 'Add your focus keyword to the first paragraph.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'found' => $found
		);
	}

	/**
	 * Analyze focus keyword usage in headings.
	 *
	 * @since    1.0.0
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with status and recommendation.
	 */
	public function analyze_keyword_in_headings( $content, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'h1_found' => false,
				'h2_found' => false,
				'h3_found' => false
			);
		}
		
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze.', 'seo-simple' ),
				'h1_found' => false,
				'h2_found' => false,
				'h3_found' => false
			);
		}
		
		// Extract headings
		$h1_found = false;
		$h2_found = false;
		$h3_found = false;
		
		// Use DOMDocument to parse HTML and extract headings
		$dom = new DOMDocument();
		
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		
		if ( $dom->loadHTML( '<?xml encoding="utf-8">' . $content ) ) {
			$xpath = new DOMXPath( $dom );
			
			// Check H1 headings
			$h1_nodes = $xpath->query( '//h1' );
			foreach ( $h1_nodes as $node ) {
				$h1_text = $node->textContent;
				if ( stripos( $h1_text, $focus_keyword ) !== false ) {
					$h1_found = true;
					break;
				}
			}
			
			// Check H2 headings
			$h2_nodes = $xpath->query( '//h2' );
			foreach ( $h2_nodes as $node ) {
				$h2_text = $node->textContent;
				if ( stripos( $h2_text, $focus_keyword ) !== false ) {
					$h2_found = true;
					break;
				}
			}
			
			// Check H3 headings
			$h3_nodes = $xpath->query( '//h3' );
			foreach ( $h3_nodes as $node ) {
				$h3_text = $node->textContent;
				if ( stripos( $h3_text, $focus_keyword ) !== false ) {
					$h3_found = true;
					break;
				}
			}
		}
		
		libxml_clear_errors();
		
		// Calculate score and status
		$found_headings = 0;
		if ( $h1_found ) $found_headings++;
		if ( $h2_found ) $found_headings++;
		if ( $h3_found ) $found_headings++;
		
		if ( $found_headings >= 2 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your focus keyword is used in multiple headings.', 'seo-simple' );
		} elseif ( $found_headings == 1 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Good! Your focus keyword is in a heading. Consider using it in more headings.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Use your focus keyword in headings (H1, H2, or H3).', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'h1_found' => $h1_found,
			'h2_found' => $h2_found,
			'h3_found' => $h3_found
		);
	}

	/**
	 * Calculate word count of content.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with word count and status.
	 */
	public function calculate_word_count( $content ) {
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze word count.', 'seo-simple' ),
				'word_count' => 0
			);
		}
		
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		
		// Count words
		$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$word_count = count( $words );
		
		// Determine status based on word count
		if ( $word_count >= 300 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your content has sufficient word count.', 'seo-simple' );
		} elseif ( $word_count >= 200 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Your content is a bit short. Consider adding more details.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Your content is too short. Add more valuable content.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'word_count' => $word_count
		);
	}

	/**
	 * Calculate keyword density.
	 *
	 * @since    1.0.0
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with keyword density and status.
	 */
	public function calculate_keyword_density( $content, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze.', 'seo-simple' ),
				'density' => 0,
				'keyword_count' => 0
			);
		}
		
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze keyword density.', 'seo-simple' ),
				'density' => 0,
				'keyword_count' => 0
			);
		}
		
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = strtolower( $text );
		$keyword = strtolower( $focus_keyword );
		
		// Count words
		$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$total_words = count( $words );
		
		if ( $total_words == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content with words to analyze keyword density.', 'seo-simple' ),
				'density' => 0,
				'keyword_count' => 0
			);
		}
		
		// Count keyword occurrences
		$keyword_count = 0;
		foreach ( $words as $word ) {
			if ( $word === $keyword ) {
				$keyword_count++;
			}
		}
		
		// Calculate density (percentage)
		$density = ( $keyword_count / $total_words ) * 100;
		
		// Determine status based on density
		if ( $density >= 1 && $density <= 3 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your keyword density is optimal.', 'seo-simple' );
		} elseif ( $density >= 0.5 && $density < 1 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Consider using your keyword more frequently.', 'seo-simple' );
		} elseif ( $density > 3 ) {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Your keyword density is too high. This may look like keyword stuffing.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Use your focus keyword more in your content.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'density' => round( $density, 2 ),
			'keyword_count' => $keyword_count,
			'total_words' => $total_words
		);
	}

	/**
	 * Count internal and external links.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with link counts and status.
	 */
	public function count_links( $content ) {
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze links.', 'seo-simple' ),
				'internal_links' => 0,
				'external_links' => 0,
				'total_links' => 0
			);
		}
		
		$dom = new DOMDocument();
		
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		
		$internal_links = 0;
		$external_links = 0;
		
		if ( $dom->loadHTML( '<?xml encoding="utf-8">' . $content ) ) {
			$xpath = new DOMXPath( $dom );
			$links = $xpath->query( '//a[@href]' );
			
			$site_url = site_url();
			$site_host = wp_parse_url( $site_url, PHP_URL_HOST );
			
			foreach ( $links as $link ) {
				$href = $link->getAttribute( 'href' );
				
				// Skip empty links, anchors, and javascript
				if ( empty( $href ) || $href[0] === '#' || strpos( $href, 'javascript:' ) === 0 ) {
					continue;
				}
				
				$link_host = wp_parse_url( $href, PHP_URL_HOST );
				
				// Check if it's an internal or external link
				if ( empty( $link_host ) || $link_host === $site_host ) {
					$internal_links++;
				} else {
					$external_links++;
				}
			}
		}
		
		libxml_clear_errors();
		
		$total_links = $internal_links + $external_links;
		
		// Determine status based on link counts
		if ( $internal_links >= 2 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Your content has internal links.', 'seo-simple' );
		} elseif ( $internal_links >= 1 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Consider adding more internal links to related content.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Add internal links to related content on your site.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'internal_links' => $internal_links,
			'external_links' => $external_links,
			'total_links' => $total_links
		);
	}

	/**
	 * Check image alt text completeness.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with image alt text status.
	 */
	public function check_image_alt_text( $content ) {
		if ( empty( $content ) ) {
			return array(
				'score' => 100,
				'status' => 'good',
				'recommendation' => __( 'No images to analyze.', 'seo-simple' ),
				'total_images' => 0,
				'images_with_alt' => 0,
				'images_without_alt' => 0
			);
		}
		
		$dom = new DOMDocument();
		
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		
		$total_images = 0;
		$images_with_alt = 0;
		$images_without_alt = 0;
		
		if ( $dom->loadHTML( '<?xml encoding="utf-8">' . $content ) ) {
			$xpath = new DOMXPath( $dom );
			$images = $xpath->query( '//img' );
			
			$total_images = $images->length;
			
			foreach ( $images as $image ) {
				$alt = $image->getAttribute( 'alt' );
				if ( ! empty( $alt ) ) {
					$images_with_alt++;
				} else {
					$images_without_alt++;
				}
			}
		}
		
		libxml_clear_errors();
		
		// Calculate percentage of images with alt text
		if ( $total_images == 0 ) {
			return array(
				'score' => 100,
				'status' => 'good',
				'recommendation' => __( 'No images to analyze.', 'seo-simple' ),
				'total_images' => 0,
				'images_with_alt' => 0,
				'images_without_alt' => 0
			);
		}
		
		$percentage_with_alt = ( $images_with_alt / $total_images ) * 100;
		
		// Determine status based on alt text completeness
		if ( $percentage_with_alt >= 80 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! Most images have alt text.', 'seo-simple' );
		} elseif ( $percentage_with_alt >= 50 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Some images are missing alt text. Add alt text to improve accessibility.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Many images are missing alt text. Add alt text to all images for better SEO and accessibility.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'total_images' => $total_images,
			'images_with_alt' => $images_with_alt,
			'images_without_alt' => $images_without_alt,
			'percentage_with_alt' => round( $percentage_with_alt, 1 )
		);
	}

	/**
	 * Calculate overall SEO score based on all analyses.
	 *
	 * @since    1.0.0
	 * @param    array    $analyses    Array of individual analysis results.
	 * @return   array                Overall score with status and recommendation.
	 */
	public function calculate_overall_seo_score( $analyses ) {
		if ( empty( $analyses ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No SEO analysis available.', 'seo-simple' )
			);
		}
		
		// Define weights for different checks
		$weights = array(
			'keyword_in_title' => 0.2,
			'keyword_in_description' => 0.15,
			'keyword_in_url' => 0.1,
			'keyword_in_first_paragraph' => 0.15,
			'keyword_in_headings' => 0.1,
			'word_count' => 0.1,
			'keyword_density' => 0.1,
			'links' => 0.05,
			'image_alt_text' => 0.05
		);
		
		$weighted_score = 0;
		$total_weight = 0;
		
		foreach ( $analyses as $analysis_type => $analysis ) {
			if ( isset( $analysis['score'] ) && isset( $weights[$analysis_type] ) ) {
				$weighted_score += $analysis['score'] * $weights[$analysis_type];
				$total_weight += $weights[$analysis_type];
			}
		}
		
		if ( $total_weight == 0 ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'No valid SEO analysis results.', 'seo-simple' )
			);
		}
		
		// Normalize score to 0-100 range
		$overall_score = $weighted_score / $total_weight;
		
		// Determine status
		if ( $overall_score >= 80 ) {
			$status = 'good';
			$recommendation = __( 'Excellent! Your content is well-optimized for search engines.', 'seo-simple' );
		} elseif ( $overall_score >= 60 ) {
			$status = 'ok';
			$recommendation = __( 'Good! Your content is fairly optimized with room for improvement.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Your content needs optimization for better search engine visibility.', 'seo-simple' );
		}
		
		return array(
			'score' => round( $overall_score, 1 ),
			'status' => $status,
			'recommendation' => $recommendation
		);
	}

	/**
	 * Perform complete SEO analysis with focus keyword.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id        The post ID.
	 * @param    string    $title          The SEO title.
	 * @param    string    $description    The meta description.
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Complete SEO analysis results.
	 */
	public function analyze_seo( $post_id, $title, $description, $content, $focus_keyword ) {
		$results = array();
		
		// Perform individual SEO analyses
		$results['keyword_in_title'] = $this->analyze_keyword_in_title( $title, $focus_keyword );
		$results['keyword_in_description'] = $this->analyze_keyword_in_description( $description, $focus_keyword );
		$results['keyword_in_url'] = $this->analyze_keyword_in_url( $post_id, $focus_keyword );
		$results['keyword_in_first_paragraph'] = $this->analyze_keyword_in_first_paragraph( $content, $focus_keyword );
		$results['keyword_in_headings'] = $this->analyze_keyword_in_headings( $content, $focus_keyword );
		$results['word_count'] = $this->calculate_word_count( $content );
		$results['keyword_density'] = $this->calculate_keyword_density( $content, $focus_keyword );
		$results['links'] = $this->count_links( $content );
		$results['image_alt_text'] = $this->check_image_alt_text( $content );
		
		// Calculate overall SEO score
		$results['overall'] = $this->calculate_overall_seo_score( $results );
		
		return $results;
	}

	/**
	 * Perform complete readability analysis.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Complete analysis results.
	 */
	public function analyze_content( $content ) {
		$results = array();
		
		// Perform individual analyses
		$results['sentence_length'] = $this->analyze_sentence_length( $content );
		$results['paragraph_length'] = $this->analyze_paragraph_length( $content );
		$results['subheading_distribution'] = $this->analyze_subheading_distribution( $content );
		$results['passive_voice'] = $this->detect_passive_voice( $content );
		$results['transition_words'] = $this->calculate_transition_words( $content );
		$results['flesch_reading_ease'] = $this->calculate_flesch_reading_ease( $content );
		
		// Calculate overall score
		$results['overall'] = $this->calculate_overall_score( $results );
		
		return $results;
	}

	/**
	 * Perform cached readability analysis.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $content    The content to analyze.
	 * @return   array                 The cached or new analysis results.
	 */
	public function analyze_content_cached( $post_id, $content ) {
		// Check if cache is available
		if ( ! $this->cache ) {
			return $this->analyze_content( $content );
		}

		// Check if content has changed
		if ( $this->cache->has_content_changed( $post_id, $content ) ) {
			// Content has changed, clear old cache
			$this->cache->delete_analysis( $post_id, 'readability' );
		} else {
			// Try to get cached results
			$cached_results = $this->cache->get_analysis( $post_id, 'readability' );
			if ( ! empty( $cached_results ) ) {
				return $cached_results;
			}
		}

		// Perform analysis
		$results = $this->analyze_content( $content );

		// Cache results
		$this->cache->set_analysis( $post_id, 'readability', $results );
		$this->cache->set_content_hash( $post_id, $content );

		return $results;
	}

	/**
	 * Perform cached SEO analysis.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id        The post ID.
	 * @param    string    $title          The SEO title.
	 * @param    string    $description    The meta description.
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     The cached or new analysis results.
	 */
	public function analyze_seo_cached( $post_id, $title, $description, $content, $focus_keyword ) {
		// Check if cache is available
		if ( ! $this->cache ) {
			return $this->analyze_seo( $post_id, $title, $description, $content, $focus_keyword );
		}

		// Create a unique key for SEO analysis (includes title, description, and keyword)
		$seo_data = array(
			'title' => $title,
			'description' => $description,
			'focus_keyword' => $focus_keyword,
		);
		$seo_hash = md5( serialize( $seo_data ) );

		// Check if content or SEO data has changed
		if ( $this->cache->has_content_changed( $post_id, $content ) ) {
			// Content has changed, clear old cache
			$this->cache->delete_analysis( $post_id, 'seo' );
		} else {
			// Try to get cached results
			$cached_results = $this->cache->get_analysis( $post_id, 'seo' );
			if ( ! empty( $cached_results ) && isset( $cached_results['seo_hash'] ) && $cached_results['seo_hash'] === $seo_hash ) {
				return $cached_results;
			}
		}

		// Perform analysis
		$results = $this->analyze_seo( $post_id, $title, $description, $content, $focus_keyword );
		
		// Add SEO hash to results for validation
		$results['seo_hash'] = $seo_hash;

		// Cache results
		$this->cache->set_analysis( $post_id, 'seo', $results );
		$this->cache->set_content_hash( $post_id, $content );

		return $results;
	}

	/**
	 * Clear cache for a specific post.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   bool                  True on success, false on failure.
	 */
	public function clear_post_cache( $post_id ) {
		if ( ! $this->cache ) {
			return false;
		}

		return $this->cache->delete_analysis( $post_id );
	}

	/**
	 * Batch analyze multiple posts with caching.
	 *
	 * @since    1.0.0
	 * @param    array     $posts      Array of post objects.
	 * @param    string    $type       Analysis type (readability, seo).
	 * @return   array                 Array of analysis results indexed by post ID.
	 */
	public function batch_analyze( $posts, $type = 'readability' ) {
		$results = array();

		foreach ( $posts as $post ) {
			$post_id = $post->ID;
			$content = $post->post_content;

			if ( $type === 'readability' ) {
				$results[$post_id] = $this->analyze_content_cached( $post_id, $content );
			} elseif ( $type === 'seo' ) {
				$title = get_post_meta( $post_id, '_seo_simple_title', true );
				$description = get_post_meta( $post_id, '_seo_simple_description', true );
				$focus_keyword = get_post_meta( $post_id, '_seo_simple_focus_keyword', true );
				
				$results[$post_id] = $this->analyze_seo_cached( $post_id, $title, $description, $content, $focus_keyword );
			}
		}

		return $results;
	}

	/**
	 * Optimize text processing for better performance.
	 *
	 * @since    1.0.0
	 * @param    string    $text       The text to process.
	 * @return   string                The processed text.
	 */
	private function optimize_text_processing( $text ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $text ) );
		
		// Normalize whitespace
		$text = preg_replace( '/\s+/', ' ', $text );
		
		// Trim
		$text = trim( $text );
		
		return $text;
	}

	/**
	 * Analyze content sentiment using a basic approach.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with sentiment score and classification.
	 */
	public function analyze_sentiment( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'sentiment' => 'neutral',
				'recommendation' => __( 'Please add some content before analyzing sentiment.', 'seo-simple' )
			);
		}
		
		// Simple word-based sentiment analysis
		$positive_words = array(
			'good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'awesome', 'perfect',
			'love', 'like', 'enjoy', 'happy', 'pleased', 'satisfied', 'delighted', 'thrilled',
			'best', 'better', 'outstanding', 'superb', 'brilliant', 'exceptional', 'remarkable',
			'success', 'achieve', 'benefit', 'advantage', 'positive', 'effective', 'valuable'
		);
		
		$negative_words = array(
			'bad', 'terrible', 'awful', 'horrible', 'disgusting', 'worst', 'hate', 'dislike',
			'sad', 'angry', 'frustrated', 'disappointed', 'upset', 'annoyed', 'worried', 'concerned',
			'fail', 'failure', 'problem', 'issue', 'difficult', 'hard', 'impossible', 'wrong',
			'negative', 'poor', 'inadequate', 'insufficient', 'useless', 'worthless', 'harmful'
		);
		
		$words = preg_split( '/\s+/', strtolower( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$positive_count = 0;
		$negative_count = 0;
		
		foreach ( $words as $word ) {
			if ( in_array( $word, $positive_words ) ) {
				$positive_count++;
			} elseif ( in_array( $word, $negative_words ) ) {
				$negative_count++;
			}
		}
		
		$total_sentiment_words = $positive_count + $negative_count;
		
		if ( $total_sentiment_words == 0 ) {
			return array(
				'score' => 0,
				'sentiment' => 'neutral',
				'recommendation' => __( 'Your content has a neutral tone. Consider adding more emotional language to engage readers.', 'seo-simple' ),
				'positive_words' => 0,
				'negative_words' => 0
			);
		}
		
		// Calculate sentiment score (-100 to 100)
		$sentiment_score = ( ( $positive_count - $negative_count ) / $total_sentiment_words ) * 100;
		
		// Determine sentiment classification
		if ( $sentiment_score > 20 ) {
			$sentiment = 'positive';
			$recommendation = __( 'Your content has a positive tone, which is great for engaging readers.', 'seo-simple' );
		} elseif ( $sentiment_score < -20 ) {
			$sentiment = 'negative';
			$recommendation = __( 'Your content has a negative tone. Consider balancing with more positive language.', 'seo-simple' );
		} else {
			$sentiment = 'neutral';
			$recommendation = __( 'Your content has a neutral tone. Consider adding more emotional language to engage readers.', 'seo-simple' );
		}
		
		return array(
			'score' => round( $sentiment_score, 1 ),
			'sentiment' => $sentiment,
			'recommendation' => $recommendation,
			'positive_words' => $positive_count,
			'negative_words' => $negative_count
		);
	}

	/**
	 * Analyze content complexity based on sentence structure and vocabulary.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with complexity score and recommendations.
	 */
	public function analyze_content_complexity( $content ) {
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = preg_replace( '/\s+/', ' ', $text ); // Normalize whitespace
		$text = trim( $text );
		
		// Check if there's actual content to analyze
		if ( empty( $text ) || strlen( $text ) < 10 ) {
			return array(
				'score' => 0,
				'complexity' => 'unknown',
				'recommendation' => __( 'Please add some content before analyzing complexity.', 'seo-simple' )
			);
		}
		
		// Split into sentences
		$sentences = preg_split( '/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		
		if ( empty( $sentences ) ) {
			return array(
				'score' => 0,
				'complexity' => 'unknown',
				'recommendation' => __( 'Please add complete sentences to analyze complexity.', 'seo-simple' )
			);
		}
		
		// Calculate average sentence length
		$total_words = 0;
		$max_sentence_length = 0;
		$complex_words = 0;
		
		foreach ( $sentences as $sentence ) {
			$words = preg_split( '/\s+/', trim( $sentence ), -1, PREG_SPLIT_NO_EMPTY );
			$word_count = count( $words );
			$total_words += $word_count;
			$max_sentence_length = max( $max_sentence_length, $word_count );
			
			// Count complex words (3+ syllables)
			foreach ( $words as $word ) {
				if ( $this->count_syllables( $word ) >= 3 ) {
					$complex_words++;
				}
			}
		}
		
		$avg_sentence_length = $total_words / count( $sentences );
		$total_words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
		$total_word_count = count( $total_words );
		
		// Calculate complexity metrics
		$complex_word_percentage = $total_word_count > 0 ? ( $complex_words / $total_word_count ) * 100 : 0;
		
		// Calculate complexity score (0-100)
		$complexity_score = 0;
		
		// Factor 1: Average sentence length (higher = more complex)
		if ( $avg_sentence_length > 20 ) {
			$complexity_score += 30;
		} elseif ( $avg_sentence_length > 15 ) {
			$complexity_score += 20;
		} elseif ( $avg_sentence_length > 10 ) {
			$complexity_score += 10;
		}
		
		// Factor 2: Complex word percentage (higher = more complex)
		if ( $complex_word_percentage > 20 ) {
			$complexity_score += 40;
		} elseif ( $complex_word_percentage > 15 ) {
			$complexity_score += 30;
		} elseif ( $complex_word_percentage > 10 ) {
			$complexity_score += 20;
		}
		
		// Factor 3: Maximum sentence length (higher = more complex)
		if ( $max_sentence_length > 30 ) {
			$complexity_score += 30;
		} elseif ( $max_sentence_length > 25 ) {
			$complexity_score += 20;
		} elseif ( $max_sentence_length > 20 ) {
			$complexity_score += 10;
		}
		
		// Determine complexity level
		if ( $complexity_score >= 70 ) {
			$complexity = 'high';
			$recommendation = __( 'Your content is highly complex. Simplify sentences and use simpler words to improve readability.', 'seo-simple' );
		} elseif ( $complexity_score >= 40 ) {
			$complexity = 'medium';
			$recommendation = __( 'Your content has moderate complexity. Consider simplifying some sentences for better readability.', 'seo-simple' );
		} else {
			$complexity = 'low';
			$recommendation = __( 'Your content is simple and easy to understand. Good for general audiences.', 'seo-simple' );
		}
		
		return array(
			'score' => min( 100, $complexity_score ),
			'complexity' => $complexity,
			'recommendation' => $recommendation,
			'avg_sentence_length' => round( $avg_sentence_length, 1 ),
			'complex_word_percentage' => round( $complex_word_percentage, 1 ),
			'max_sentence_length' => $max_sentence_length
		);
	}

	/**
	 * Analyze keyword variations and related terms usage.
	 *
	 * @since    1.0.0
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Analysis results with keyword variations and recommendations.
	 */
	public function analyze_keyword_variations( $content, $focus_keyword ) {
		if ( empty( $focus_keyword ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Set a focus keyword to analyze variations.', 'seo-simple' ),
				'variations_found' => 0,
				'lsi_terms_found' => 0
			);
		}
		
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze keyword variations.', 'seo-simple' ),
				'variations_found' => 0,
				'lsi_terms_found' => 0
			);
		}
		
		// Strip HTML tags and shortcodes
		$text = strip_tags( strip_shortcodes( $content ) );
		$text = strtolower( $text );
		$keyword = strtolower( $focus_keyword );
		
		// Generate keyword variations
		$variations = array();
		$variations[] = $keyword;
		
		// Add plural form
		if ( substr( $keyword, -1 ) !== 's' ) {
			$variations[] = $keyword . 's';
		}
		
		// Add common variations
		$variations[] = str_replace( ' ', '-', $keyword ); // Hyphenated
		$variations[] = str_replace( ' ', '', $keyword );  // No spaces
		
		// Count variations in content
		$variation_count = 0;
		foreach ( $variations as $variation ) {
			$variation_count += substr_count( $text, $variation );
		}
		
		// Simple LSI (Latent Semantic Indexing) terms based on keyword
		$lsi_terms = array();
		$keyword_parts = explode( ' ', $keyword );
		
		foreach ( $keyword_parts as $part ) {
			if ( strlen( $part ) > 3 ) {
				$lsi_terms[] = $part;
			}
		}
		
		// Count LSI terms in content
		$lsi_count = 0;
		foreach ( $lsi_terms as $term ) {
			$lsi_count += substr_count( $text, $term );
		}
		
		// Calculate score
		$total_variations = $variation_count + $lsi_count;
		
		if ( $total_variations >= 5 ) {
			$score = 100;
			$status = 'good';
			$recommendation = __( 'Great! You\'re using keyword variations and related terms effectively.', 'seo-simple' );
		} elseif ( $total_variations >= 3 ) {
			$score = 70;
			$status = 'ok';
			$recommendation = __( 'Good! Consider adding more keyword variations and related terms.', 'seo-simple' );
		} else {
			$score = 40;
			$status = 'bad';
			$recommendation = __( 'Add more keyword variations and related terms to improve topical relevance.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'variations_found' => $variation_count,
			'lsi_terms_found' => $lsi_count,
			'total_related_terms' => $total_variations
		);
	}

	/**
	 * Analyze content structure and organization.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with structure metrics and recommendations.
	 */
	public function analyze_content_structure( $content ) {
		if ( empty( $content ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Add content to analyze structure.', 'seo-simple' )
			);
		}
		
		// Use DOMDocument to parse HTML and analyze structure
		$dom = new DOMDocument();
		
		// Suppress warnings for malformed HTML
		libxml_use_internal_errors( true );
		
		$h1_count = 0;
		$h2_count = 0;
		$h3_count = 0;
		$h4_count = 0;
		$list_count = 0;
		$blockquote_count = 0;
		$table_count = 0;
		
		if ( $dom->loadHTML( '<?xml encoding="utf-8">' . $content ) ) {
			$xpath = new DOMXPath( $dom );
			
			// Count headings
			$h1_count = $xpath->query( '//h1' )->length;
			$h2_count = $xpath->query( '//h2' )->length;
			$h3_count = $xpath->query( '//h3' )->length;
			$h4_count = $xpath->query( '//h4' )->length;
			
			// Count lists
			$list_count = $xpath->query( '//ul|//ol' )->length;
			
			// Count blockquotes
			$blockquote_count = $xpath->query( '//blockquote' )->length;
			
			// Count tables
			$table_count = $xpath->query( '//table' )->length;
		}
		
		libxml_clear_errors();
		
		// Calculate structure score
		$score = 0;
		
		// Check for proper heading hierarchy
		if ( $h1_count === 1 ) {
			$score += 20; // Perfect H1 usage
		} elseif ( $h1_count === 0 ) {
			$score -= 10; // Missing H1
		} else {
			$score -= 5; // Multiple H1s
		}
		
		// Check for H2 usage
		if ( $h2_count >= 2 ) {
			$score += 20;
		} elseif ( $h2_count >= 1 ) {
			$score += 10;
		}
		
		// Check for H3 usage
		if ( $h3_count >= 1 ) {
			$score += 15;
		}
		
		// Check for lists
		if ( $list_count >= 1 ) {
			$score += 15;
		}
		
		// Check for blockquotes
		if ( $blockquote_count >= 1 ) {
			$score += 10;
		}
		
		// Check for tables
		if ( $table_count >= 1 ) {
			$score += 10;
		}
		
		// Check heading hierarchy (H3s should not exceed H2s)
		if ( $h3_count > $h2_count && $h2_count > 0 ) {
			$score -= 10;
		}
		
		// Normalize score to 0-100
		$score = max( 0, min( 100, $score + 50 ) );
		
		// Determine status
		if ( $score >= 80 ) {
			$status = 'good';
			$recommendation = __( 'Excellent! Your content is well-structured with proper headings and elements.', 'seo-simple' );
		} elseif ( $score >= 60 ) {
			$status = 'ok';
			$recommendation = __( 'Good structure! Consider adding more headings or lists to improve organization.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Improve content structure with proper headings, lists, and other elements.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'h1_count' => $h1_count,
			'h2_count' => $h2_count,
			'h3_count' => $h3_count,
			'h4_count' => $h4_count,
			'list_count' => $list_count,
			'blockquote_count' => $blockquote_count,
			'table_count' => $table_count
		);
	}

	/**
	 * Analyze content freshness and timeliness.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    string    $content    The content to analyze.
	 * @return   array                 Analysis results with freshness metrics and recommendations.
	 */
	public function analyze_content_freshness( $post_id, $content ) {
		if ( empty( $post_id ) ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Invalid post ID.', 'seo-simple' )
			);
		}
		
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array(
				'score' => 0,
				'status' => 'bad',
				'recommendation' => __( 'Post not found.', 'seo-simple' )
			);
		}
		
		$current_time = current_time( 'timestamp' );
		$post_date = get_the_time( 'U', $post );
		$modified_date = get_the_modified_time( 'U', $post );
		
		// Use the most recent date
		$last_updated = max( $post_date, $modified_date );
		$days_since_update = ( $current_time - $last_updated ) / DAY_IN_SECONDS;
		
		// Check for time-sensitive indicators in content
		$text = strtolower( strip_tags( strip_shortcodes( $content ) ) );
		
		$freshness_indicators = array(
			'2023', '2024', '2025', 'recent', 'latest', 'new', 'updated', 'current',
			'today', 'this week', 'this month', 'this year', 'recently'
		);
		
		$outdated_indicators = array(
			'2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019',
			'last year', 'previous', 'old', 'outdated', 'past', 'former'
		);
		
		$freshness_count = 0;
		$outdated_count = 0;
		
		foreach ( $freshness_indicators as $indicator ) {
			$freshness_count += substr_count( $text, $indicator );
		}
		
		foreach ( $outdated_indicators as $indicator ) {
			$outdated_count += substr_count( $text, $indicator );
		}
		
		// Calculate freshness score
		$score = 100;
		
		// Deduct points based on age
		if ( $days_since_update > 365 ) {
			$score -= 40; // More than a year old
		} elseif ( $days_since_update > 180 ) {
			$score -= 25; // More than 6 months old
		} elseif ( $days_since_update > 90 ) {
			$score -= 10; // More than 3 months old
		}
		
		// Adjust based on content indicators
		$score += min( 20, $freshness_count * 5 ); // Add points for freshness indicators
		$score -= min( 20, $outdated_count * 5 );  // Deduct points for outdated indicators
		
		// Normalize score
		$score = max( 0, min( 100, $score ) );
		
		// Determine status
		if ( $days_since_update <= 90 && $score >= 80 ) {
			$status = 'good';
			$recommendation = __( 'Your content is fresh and up-to-date. Great for SEO!', 'seo-simple' );
		} elseif ( $days_since_update <= 180 && $score >= 60 ) {
			$status = 'ok';
			$recommendation = __( 'Your content is relatively fresh. Consider updating it periodically.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Your content may be outdated. Consider updating it with recent information.', 'seo-simple' );
		}
		
		return array(
			'score' => $score,
			'status' => $status,
			'recommendation' => $recommendation,
			'days_since_update' => round( $days_since_update ),
			'last_updated' => date( 'Y-m-d', $last_updated ),
			'freshness_indicators' => $freshness_count,
			'outdated_indicators' => $outdated_count
		);
	}

	/**
	 * Perform comprehensive deep content analysis.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id        The post ID.
	 * @param    string    $title          The SEO title.
	 * @param    string    $description    The meta description.
	 * @param    string    $content        The content to analyze.
	 * @param    string    $focus_keyword  The focus keyword.
	 * @return   array                     Comprehensive analysis results.
	 */
	public function deep_content_analysis( $post_id, $title, $description, $content, $focus_keyword ) {
		// Get basic SEO analysis
		$seo_analysis = $this->analyze_seo( $post_id, $title, $description, $content, $focus_keyword );
		
		// Get basic readability analysis
		$readability_analysis = $this->analyze_content( $content );
		
		// Get advanced analysis
		$sentiment_analysis = $this->analyze_sentiment( $content );
		$complexity_analysis = $this->analyze_content_complexity( $content );
		$keyword_variations = $this->analyze_keyword_variations( $content, $focus_keyword );
		$content_structure = $this->analyze_content_structure( $content );
		$content_freshness = $this->analyze_content_freshness( $post_id, $content );
		
		// Calculate overall deep analysis score
		$all_scores = array(
			$seo_analysis['overall']['score'] * 0.3,  // 30% weight
			$readability_analysis['overall']['score'] * 0.2,  // 20% weight
			$sentiment_analysis['score'] * 0.1,  // 10% weight
			$complexity_analysis['score'] * 0.1,  // 10% weight
			$keyword_variations['score'] * 0.1,  // 10% weight
			$content_structure['score'] * 0.1,  // 10% weight
			$content_freshness['score'] * 0.1   // 10% weight
		);
		
		$overall_score = array_sum( $all_scores );
		
		// Determine overall status
		if ( $overall_score >= 80 ) {
			$status = 'good';
			$recommendation = __( 'Excellent! Your content is highly optimized and well-structured.', 'seo-simple' );
		} elseif ( $overall_score >= 60 ) {
			$status = 'ok';
			$recommendation = __( 'Good! Your content has solid optimization with room for improvement.', 'seo-simple' );
		} else {
			$status = 'bad';
			$recommendation = __( 'Your content needs significant optimization for better performance.', 'seo-simple' );
		}
		
		return array(
			'overall' => array(
				'score' => round( $overall_score, 1 ),
				'status' => $status,
				'recommendation' => $recommendation
			),
			'seo' => $seo_analysis,
			'readability' => $readability_analysis,
			'sentiment' => $sentiment_analysis,
			'complexity' => $complexity_analysis,
			'keyword_variations' => $keyword_variations,
			'content_structure' => $content_structure,
			'content_freshness' => $content_freshness
		);
	}
}