<?php

/**
 * Handles shortcodes for the SEO plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Handles shortcodes for the SEO plugin.
 *
 * This class handles all shortcodes provided by the plugin, including
 * the FAQ shortcode for generating FAQ schema.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@gmail.com>
 */
class Seo_Simple_Shortcodes {

	/**
	 * The schema handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Schema    $schema    The schema handler instance.
	 */
	private $schema;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    Seo_Simple_Schema    $schema    The schema handler instance.
	 */
	public function __construct( $schema = null ) {
		$this->schema = $schema;
		
		// Register shortcodes
		add_shortcode( 'seo_simple_faq', array( $this, 'faq_shortcode' ) );
		
		// Register Gutenberg blocks if available
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'init', array( $this, 'register_gutenberg_blocks' ) );
		}
		
		// Add FAQ schema to head
		add_action( 'wp_head', array( $this, 'output_faq_schema' ), 30 );
	}

	/**
	 * FAQ shortcode handler.
	 *
	 * @since    1.0.0
	 * @param    array   $atts       Shortcode attributes.
	 * @param    string  $content    Shortcode content.
	 * @return   string              The FAQ HTML.
	 */
	public function faq_shortcode( $atts, $content = '' ) {
		// Default attributes
		$atts = shortcode_atts(
			array(
				'question' => '',
				'answer'   => '',
				'class'    => 'seo-simple-faq-item',
			),
			$atts,
			'seo_simple_faq'
		);

		// If answer is provided as attribute, use it
		if ( ! empty( $atts['answer'] ) ) {
			$content = $atts['answer'];
		}

		// If content is empty, return empty string
		if ( empty( $atts['question'] ) || empty( $content ) ) {
			return '';
		}

		// Sanitize inputs
		$question = sanitize_text_field( $atts['question'] );
		$answer = wp_kses_post( $content );
		$class = sanitize_html_class( $atts['class'] );

		// Generate unique ID for this FAQ item
		$faq_id = 'seo-simple-faq-' . uniqid();

		// Generate FAQ HTML
		$html = '<div class="' . esc_attr( $class ) . '" data-faq-id="' . esc_attr( $faq_id ) . '">';
		$html .= '<div class="seo-simple-faq-question" data-toggle="faq" data-target="' . esc_attr( $faq_id ) . '">';
		$html .= '<h4>' . esc_html( $question ) . '</h4>';
		$html .= '<span class="seo-simple-faq-toggle">+</span>';
		$html .= '</div>';
		$html .= '<div class="seo-simple-faq-answer" id="' . esc_attr( $faq_id ) . '">';
		$html .= '<div class="seo-simple-faq-answer-content">';
		$html .= $answer;
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		// Add schema data to post meta for later use
		$this->add_faq_to_post_meta( $question, $answer );

		return $html;
	}

	/**
	 * Register Gutenberg blocks.
	 *
	 * @since    1.0.0
	 */
	public function register_gutenberg_blocks() {
		// Register FAQ block
		register_block_type(
			'seo-simple/faq',
			array(
				'editor_script'   => 'seo-simple-faq-block',
				'editor_style'    => 'seo-simple-faq-block-editor',
				'style'           => 'seo-simple-faq-block',
				'render_callback' => array( $this, 'render_faq_block' ),
				'attributes'      => array(
					'questions' => array(
						'type'    => 'array',
						'default' => array(),
					),
				),
			)
		);

		// Register block assets
		wp_register_script(
			'seo-simple-faq-block',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/faq-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ),
			SEO_SIMPLE_VERSION,
			true
		);

		wp_register_style(
			'seo-simple-faq-block-editor',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/faq-block-editor.css',
			array( 'wp-edit-blocks' ),
			SEO_SIMPLE_VERSION
		);

		wp_register_style(
			'seo-simple-faq-block',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/faq-block.css',
			array(),
			SEO_SIMPLE_VERSION
		);
	}

	/**
	 * Render FAQ block.
	 *
	 * @since    1.0.0
	 * @param    array   $attributes    Block attributes.
	 * @return   string                  The FAQ HTML.
	 */
	public function render_faq_block( $attributes ) {
		$questions = isset( $attributes['questions'] ) ? $attributes['questions'] : array();

		if ( empty( $questions ) ) {
			return '';
		}

		$html = '<div class="seo-simple-faq-container">';

		foreach ( $questions as $question ) {
			if ( empty( $question['question'] ) || empty( $question['answer'] ) ) {
				continue;
			}

			// Sanitize inputs
			$question_text = sanitize_text_field( $question['question'] );
			$answer_text = wp_kses_post( $question['answer'] );

			// Generate unique ID for this FAQ item
			$faq_id = 'seo-simple-faq-' . uniqid();

			// Generate FAQ HTML
			$html .= '<div class="seo-simple-faq-item" data-faq-id="' . esc_attr( $faq_id ) . '">';
			$html .= '<div class="seo-simple-faq-question" data-toggle="faq" data-target="' . esc_attr( $faq_id ) . '">';
			$html .= '<h4>' . esc_html( $question_text ) . '</h4>';
			$html .= '<span class="seo-simple-faq-toggle">+</span>';
			$html .= '</div>';
			$html .= '<div class="seo-simple-faq-answer" id="' . esc_attr( $faq_id ) . '">';
			$html .= '<div class="seo-simple-faq-answer-content">';
			$html .= $answer_text;
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			// Add schema data to post meta for later use
			$this->add_faq_to_post_meta( $question_text, $answer_text );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Add FAQ data to post meta.
	 *
	 * @since    1.0.0
	 * @param    string  $question    The FAQ question.
	 * @param    string  $answer      The FAQ answer.
	 */
	private function add_faq_to_post_meta( $question, $answer ) {
		// Get current post ID
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		// Get existing FAQs
		$faqs = get_post_meta( $post_id, '_seo_simple_faqs', true );
		if ( ! is_array( $faqs ) ) {
			$faqs = array();
		}

		// Add new FAQ
		$faqs[] = array(
			'question' => $question,
			'answer'   => $answer,
		);

		// Update post meta
		update_post_meta( $post_id, '_seo_simple_faqs', $faqs );
	}

	/**
	 * Get FAQs from post meta.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The FAQs array.
	 */
	public function get_faqs( $post_id ) {
		$faqs = get_post_meta( $post_id, '_seo_simple_faqs', true );
		return is_array( $faqs ) ? $faqs : array();
	}

	/**
	 * Clear FAQs from post meta.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   bool                True on success, false on failure.
	 */
	public function clear_faqs( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		return delete_post_meta( $post_id, '_seo_simple_faqs' );
	}

	/**
	 * Validate FAQ content.
	 *
	 * @since    1.0.0
	 * @param    string  $question    The FAQ question.
	 * @param    string  $answer      The FAQ answer.
	 * @return   bool                True if valid, false otherwise.
	 */
	public function validate_faq( $question, $answer ) {
		// Check if question is empty
		if ( empty( $question ) ) {
			return false;
		}

		// Check if answer is empty
		if ( empty( $answer ) ) {
			return false;
		}

		// Check if question is too long
		if ( strlen( $question ) > 200 ) {
			return false;
		}

		// Check if answer is too short
		if ( strlen( $answer ) < 20 ) {
			return false;
		}

		return true;
	}

	/**
	 * Generate FAQ schema from post FAQs.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id    The post ID.
	 * @return   array               The FAQ schema data.
	 */
	public function generate_faq_schema_from_post_faqs( $post_id ) {
		$faqs = $this->get_faqs( $post_id );

		if ( empty( $faqs ) ) {
			return array();
		}

		$schema_faqs = array();

		foreach ( $faqs as $faq ) {
			if ( $this->validate_faq( $faq['question'], $faq['answer'] ) ) {
				$schema_faqs[] = array(
					'@type'          => 'Question',
					'name'           => $faq['question'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text' => $faq['answer'],
					),
				);
			}
		}

		if ( empty( $schema_faqs ) ) {
			return array();
		}

		return array(
			'@type'      => 'FAQPage',
			'mainEntity' => $schema_faqs,
		);
	}
	
	
	/**
		* Output FAQ schema to head.
		*
		* @since    1.0.0
		*/
	public function output_faq_schema() {
		// Only output on singular posts/pages
		if ( ! is_singular() ) {
			return;
		}
		
		$post_id = get_the_ID();
		$faq_schema = $this->generate_faq_schema_from_post_faqs( $post_id );
		
		if ( empty( $faq_schema ) ) {
			return;
		}
		
		// Output schema
		echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}