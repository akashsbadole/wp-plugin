<?php

/**
 * SEO Simple Elementor Review Widget
 *
 * Custom Elementor widget for displaying SEO-friendly reviews with schema markup.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Simple Elementor Review Widget Class
 *
 * Creates a review widget for Elementor with Review schema markup.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_Review_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since    1.0.0
	 * @return   string    Widget name.
	 */
	public function get_name() {
		return 'seo-simple-review';
	}

	/**
	 * Get widget title.
	 *
	 * @since    1.0.0
	 * @return   string    Widget title.
	 */
	public function get_title() {
		return __( 'SEO Review', 'seo-simple' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since    1.0.0
	 * @return   string    Widget icon.
	 */
	public function get_icon() {
		return 'eicon-star';
	}

	/**
	 * Get widget categories.
	 *
	 * @since    1.0.0
	 * @return   array    Widget categories.
	 */
	public function get_categories() {
		return [ 'seo-simple' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since    1.0.0
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'seo-simple' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'item_type',
			[
				'label' => __( 'Item Type', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'Product',
				'options' => [
					'Product' => __( 'Product', 'seo-simple' ),
					'Book' => __( 'Book', 'seo-simple' ),
					'Movie' => __( 'Movie', 'seo-simple' ),
					'Course' => __( 'Course', 'seo-simple' ),
					'Recipe' => __( 'Recipe', 'seo-simple' ),
					'SoftwareApplication' => __( 'Software Application', 'seo-simple' ),
					'LocalBusiness' => __( 'Local Business', 'seo-simple' ),
					'Event' => __( 'Event', 'seo-simple' ),
				],
			]
		);

		$this->add_control(
			'item_name',
			[
				'label' => __( 'Item Name', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter the name of the item being reviewed', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'item_image',
			[
				'label' => __( 'Item Image', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'reviewer_name',
			[
				'label' => __( 'Reviewer Name', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter the reviewer name', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'rating',
			[
				'label' => __( 'Rating', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 5,
				'step' => 0.1,
				'default' => 4.5,
			]
		);

		$this->add_control(
			'review_title',
			[
				'label' => __( 'Review Title', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter the review title', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'review_content',
			[
				'label' => __( 'Review Content', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => __( 'Enter your review', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'review_date',
			[
				'label' => __( 'Review Date', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
				'default' => '',
				'description' => __( 'Leave empty to use current date', 'seo-simple' ),
			]
		);

		$this->add_control(
			'schema_enabled',
			[
				'label' => __( 'Enable Schema Markup', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Enable', 'seo-simple' ),
				'label_off' => __( 'Disable', 'seo-simple' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Enable Review schema markup for better SEO.', 'seo-simple' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_section',
			[
				'label' => __( 'Style', 'seo-simple' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'star_color',
			[
				'label' => __( 'Star Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#FFC107',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-review-stars' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Title Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-review-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => __( 'Content Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-review-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .seo-simple-review-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .seo-simple-review-content',
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => __( 'Alignment', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'seo-simple' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'seo-simple' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'seo-simple' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .seo-simple-review-container' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since    1.0.0
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$item_type = $settings['item_type'];
		$item_name = $settings['item_name'];
		$item_image = $settings['item_image']['url'];
		$reviewer_name = $settings['reviewer_name'];
		$rating = floatval( $settings['rating'] );
		$review_title = $settings['review_title'];
		$review_content = $settings['review_content'];
		$review_date = $settings['review_date'];
		$schema_enabled = $settings['schema_enabled'] === 'yes';

		if ( empty( $item_name ) || empty( $reviewer_name ) || empty( $review_content ) ) {
			echo '<p>' . __( 'Please fill in all required fields for the review.', 'seo-simple' ) . '</p>';
			return;
		}

		$this->add_render_attribute( 'review-container', 'class', 'seo-simple-review-container' );
		
		if ( $schema_enabled ) {
			$this->add_render_attribute( 'review-container', 'itemscope', '' );
			$this->add_render_attribute( 'review-container', 'itemtype', 'https://schema.org/Review' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'review-container' ) . '>';
		
		// Item being reviewed
		if ( $schema_enabled ) {
			echo '<div itemprop="itemReviewed" itemscope itemtype="https://schema.org/' . esc_attr( $item_type ) . '">';
			
			if ( ! empty( $item_image ) ) {
				echo '<link itemprop="image" href="' . esc_url( $item_image ) . '" />';
			}
			
			echo '<meta itemprop="name" content="' . esc_attr( $item_name ) . '" />';
			echo '</div>';
		}
		
		// Rating
		echo '<div class="seo-simple-review-rating">';
		echo $this->render_stars( $rating );
		
		if ( $schema_enabled ) {
			echo '<div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">';
			echo '<meta itemprop="ratingValue" content="' . esc_attr( $rating ) . '" />';
			echo '<meta itemprop="bestRating" content="5" />';
			echo '<meta itemprop="worstRating" content="1" />';
			echo '</div>';
		}
		
		echo '</div>';
		
		// Review title
		if ( ! empty( $review_title ) ) {
			echo '<h3 class="seo-simple-review-title">';
			if ( $schema_enabled ) {
				echo '<span itemprop="name">' . esc_html( $review_title ) . '</span>';
			} else {
				echo esc_html( $review_title );
			}
			echo '</h3>';
		}
		
		// Review content
		echo '<div class="seo-simple-review-content">';
		if ( $schema_enabled ) {
			echo '<span itemprop="reviewBody">' . $this->parse_text_editor( $review_content ) . '</span>';
		} else {
			echo $this->parse_text_editor( $review_content );
		}
		echo '</div>';
		
		// Review author and date
		echo '<div class="seo-simple-review-meta">';
		
		if ( $schema_enabled ) {
			echo '<div itemprop="author" itemscope itemtype="https://schema.org/Person">';
			echo '<meta itemprop="name" content="' . esc_attr( $reviewer_name ) . '" />';
			echo '</div>';
			
			$review_date_formatted = ! empty( $review_date ) ? date( 'Y-m-d', strtotime( $review_date ) ) : date( 'Y-m-d' );
			echo '<meta itemprop="datePublished" content="' . esc_attr( $review_date_formatted ) . '" />';
		}
		
		echo '<span class="seo-simple-reviewer">' . esc_html( $reviewer_name ) . '</span>';
		
		if ( ! empty( $review_date ) ) {
			echo ' - <span class="seo-simple-review-date">' . esc_html( date( get_option( 'date_format' ), strtotime( $review_date ) ) ) . '</span>';
		} else {
			echo ' - <span class="seo-simple-review-date">' . esc_html( date( get_option( 'date_format' ) ) ) . '</span>';
		}
		
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render star rating.
	 *
	 * @since    1.0.0
	 * @param    float     $rating    The rating value.
	 * @return   string               The star rating HTML.
	 */
	private function render_stars( $rating ) {
		$full_stars = floor( $rating );
		$has_half_star = ( $rating - $full_stars ) >= 0.5;
		$empty_stars = 5 - $full_stars - ( $has_half_star ? 1 : 0 );
		
		$html = '<div class="seo-simple-review-stars">';
		
		// Full stars
		for ( $i = 0; $i < $full_stars; $i++ ) {
			$html .= '<i class="fas fa-star"></i>';
		}
		
		// Half star
		if ( $has_half_star ) {
			$html .= '<i class="fas fa-star-half-alt"></i>';
		}
		
		// Empty stars
		for ( $i = 0; $i < $empty_stars; $i++ ) {
			$html .= '<i class="far fa-star"></i>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
}