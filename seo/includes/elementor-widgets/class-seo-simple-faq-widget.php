<?php

/**
 * SEO Simple Elementor FAQ Widget
 *
 * Custom Elementor widget for displaying SEO-friendly FAQ with schema markup.
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
 * SEO Simple Elementor FAQ Widget Class
 *
 * Creates an FAQ widget for Elementor with FAQPage schema markup.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_FAQ_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since    1.0.0
	 * @return   string    Widget name.
	 */
	public function get_name() {
		return 'seo-simple-faq';
	}

	/**
	 * Get widget title.
	 *
	 * @since    1.0.0
	 * @return   string    Widget title.
	 */
	public function get_title() {
		return __( 'SEO FAQ', 'seo-simple' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since    1.0.0
	 * @return   string    Widget icon.
	 */
	public function get_icon() {
		return 'eicon-help';
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
			'schema_enabled',
			[
				'label' => __( 'Enable Schema Markup', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Enable', 'seo-simple' ),
				'label_off' => __( 'Disable', 'seo-simple' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Enable FAQPage schema markup for better SEO.', 'seo-simple' ),
			]
		);

		$this->add_control(
			'expand_first',
			[
				'label' => __( 'Expand First Item', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'seo-simple' ),
				'label_off' => __( 'No', 'seo-simple' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'collapsible',
			[
				'label' => __( 'Collapsible', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'seo-simple' ),
				'label_off' => __( 'No', 'seo-simple' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'faq_items',
			[
				'label' => __( 'FAQ Items', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'question',
						'label' => __( 'Question', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => __( 'Enter your question', 'seo-simple' ),
					],
					[
						'name' => 'answer',
						'label' => __( 'Answer', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::WYSIWYG,
						'label_block' => true,
						'placeholder' => __( 'Enter your answer', 'seo-simple' ),
					],
				],
				'default' => [
					[
						'question' => __( 'What is SEO?', 'seo-simple' ),
						'answer' => __( 'SEO stands for Search Engine Optimization. It is the process of improving your website to increase its visibility when people search for products or services related to your business in Google, Bing, and other search engines.', 'seo-simple' ),
					],
					[
						'question' => __( 'Why is SEO important?', 'seo-simple' ),
						'answer' => __( 'SEO is important because it helps your website rank higher in search engine results pages, which leads to more traffic and potential customers for your business.', 'seo-simple' ),
					],
				],
				'title_field' => '{{{ question }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'question_style_section',
			[
				'label' => __( 'Question', 'seo-simple' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'question_color',
			[
				'label' => __( 'Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-question' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'question_background',
			[
				'label' => __( 'Background Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#f5f5f5',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-question' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'question_typography',
				'selector' => '{{WRAPPER}} .seo-simple-faq-question',
			]
		);

		$this->add_responsive_control(
			'question_padding',
			[
				'label' => __( 'Padding', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-question' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'question_border_radius',
			[
				'label' => __( 'Border Radius', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-question' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'answer_style_section',
			[
				'label' => __( 'Answer', 'seo-simple' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'answer_color',
			[
				'label' => __( 'Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-answer' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'answer_background',
			[
				'label' => __( 'Background Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-answer' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'answer_typography',
				'selector' => '{{WRAPPER}} .seo-simple-faq-answer',
			]
		);

		$this->add_responsive_control(
			'answer_padding',
			[
				'label' => __( 'Padding', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-answer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'answer_border_radius',
			[
				'label' => __( 'Border Radius', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-answer' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'container_style_section',
			[
				'label' => __( 'Container', 'seo-simple' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label' => __( 'Item Spacing', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Border Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#e0e0e0',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-item' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'border_width',
			[
				'label' => __( 'Border Width', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-item' => 'border-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => __( 'Border Radius', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-faq-item' => 'border-radius: {{SIZE}}{{UNIT}};',
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
		$faq_items = $settings['faq_items'];
		$schema_enabled = $settings['schema_enabled'] === 'yes';
		$expand_first = $settings['expand_first'] === 'yes';
		$collapsible = $settings['collapsible'] === 'yes';

		if ( empty( $faq_items ) ) {
			return;
		}

		$this->add_render_attribute( 'faq-container', 'class', 'seo-simple-faq-container' );
		
		if ( $schema_enabled ) {
			$this->add_render_attribute( 'faq-container', 'itemscope', '' );
			$this->add_render_attribute( 'faq-container', 'itemtype', 'https://schema.org/FAQPage' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'faq-container' ) . '>';

		foreach ( $faq_items as $index => $item ) {
			$is_first = $index === 0;
			$is_expanded = $expand_first && $is_first;
			
			$this->render_faq_item( $item, $index, $is_expanded, $collapsible, $schema_enabled );
		}

		echo '</div>';

		if ( $collapsible ) {
			$this->add_script();
		}
	}

	/**
	 * Render FAQ item.
	 *
	 * @since    1.0.0
	 * @param    array     $item          FAQ item data.
	 * @param    int       $index         Item index.
	 * @param    bool      $is_expanded   Whether item is expanded.
	 * @param    bool      $collapsible   Whether items are collapsible.
	 * @param    bool      $schema_enabled Whether schema is enabled.
	 */
	private function render_faq_item( $item, $index, $is_expanded, $collapsible, $schema_enabled ) {
		$question_id = 'seo-simple-faq-' . $this->get_id() . '-' . $index;
		$answer_id = 'seo-simple-faq-answer-' . $this->get_id() . '-' . $index;

		$this->add_render_attribute( 'faq-item', 'class', 'seo-simple-faq-item' );
		
		$this->add_render_attribute( 'faq-question', 'class', 'seo-simple-faq-question' );
		
		if ( $collapsible ) {
			$this->add_render_attribute( 'faq-question', 'role', 'button' );
			$this->add_render_attribute( 'faq-question', 'tabindex', '0' );
			$this->add_render_attribute( 'faq-question', 'aria-expanded', $is_expanded ? 'true' : 'false' );
			$this->add_render_attribute( 'faq-question', 'aria-controls', $answer_id );
		}

		if ( $schema_enabled ) {
			$this->add_render_attribute( 'faq-item', 'itemprop', 'mainEntity', 'itemscope', '', 'itemtype', 'https://schema.org/Question' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'faq-item' ) . '>';
		
		// Question
		echo '<div ' . $this->get_render_attribute_string( 'faq-question' ) . '>';
		
		if ( $schema_enabled ) {
			echo '<span itemprop="name">' . $this->parse_text_editor( $item['question'] ) . '</span>';
		} else {
			echo $this->parse_text_editor( $item['question'] );
		}
		
		echo '</div>';
		
		// Answer
		$this->add_render_attribute( 'faq-answer', 'class', 'seo-simple-faq-answer' );
		$this->add_render_attribute( 'faq-answer', 'id', $answer_id );
		
		if ( $collapsible ) {
			$this->add_render_attribute( 'faq-answer', 'role', 'region' );
			$this->add_render_attribute( 'faq-answer', 'aria-labelledby', $question_id );
			if ( ! $is_expanded ) {
				$this->add_render_attribute( 'faq-answer', 'hidden', '' );
			}
		}

		echo '<div ' . $this->get_render_attribute_string( 'faq-answer' ) . '>';
		
		if ( $schema_enabled ) {
			echo '<div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">';
			echo '<div itemprop="text">' . $this->parse_text_editor( $item['answer'] ) . '</div>';
			echo '</div>';
		} else {
			echo $this->parse_text_editor( $item['answer'] );
		}
		
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Add script for collapsible functionality.
	 *
	 * @since    1.0.0
	 */
	private function add_script() {
		$script = '
			jQuery(document).ready(function($) {
				$(".seo-simple-faq-question").on("click", function() {
					var $this = $(this);
					var $answer = $this.next(".seo-simple-faq-answer");
					var isExpanded = $this.attr("aria-expanded") === "true";
					
					// Toggle current item
					$this.attr("aria-expanded", !isExpanded);
					if (isExpanded) {
						$answer.attr("hidden", "");
					} else {
						$answer.removeAttr("hidden");
					}
				});
				
				// Keyboard navigation
				$(".seo-simple-faq-question").on("keydown", function(e) {
					if (e.key === "Enter" || e.key === " ") {
						e.preventDefault();
						$(this).click();
					}
				});
			});
		';
		
		wp_add_inline_script( 'elementor-frontend', $script );
	}
}