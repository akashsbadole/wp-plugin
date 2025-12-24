<?php

/**
 * SEO Simple Elementor HowTo Widget
 *
 * Custom Elementor widget for displaying how-to guides with schema markup.
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
 * SEO Simple Elementor HowTo Widget Class
 *
 * Creates a how-to widget for Elementor with HowTo schema markup.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_HowTo_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since    1.0.0
	 * @return   string    Widget name.
	 */
	public function get_name() {
		return 'seo-simple-howto';
	}

	/**
	 * Get widget title.
	 *
	 * @since    1.0.0
	 * @return   string    Widget title.
	 */
	public function get_title() {
		return __( 'SEO How-To', 'seo-simple' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since    1.0.0
	 * @return   string    Widget icon.
	 */
	public function get_icon() {
		return 'eicon-list-ol';
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
			'title',
			[
				'label' => __( 'How-To Title', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter how-to title', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'description',
			[
				'label' => __( 'Description', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => __( 'Enter how-to description', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'image',
			[
				'label' => __( 'Main Image', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'estimated_time',
			[
				'label' => __( 'Estimated Time', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'e.g., PT30M', 'seo-simple' ),
				'description' => __( 'Enter in ISO 8601 duration format (e.g., PT30M for 30 minutes)', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'supply_heading',
			[
				'label' => __( 'Supplies', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'supplies',
			[
				'label' => __( 'Supply List', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'name',
						'label' => __( 'Supply Name', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => __( 'Enter supply name', 'seo-simple' ),
					],
					[
						'name' => 'image',
						'label' => __( 'Supply Image', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::MEDIA,
					],
				],
				'title_field' => '{{{ name }}}',
			]
		);

		$this->add_control(
			'tool_heading',
			[
				'label' => __( 'Tools', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tools',
			[
				'label' => __( 'Tool List', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'name',
						'label' => __( 'Tool Name', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => __( 'Enter tool name', 'seo-simple' ),
					],
					[
						'name' => 'image',
						'label' => __( 'Tool Image', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::MEDIA,
					],
				],
				'title_field' => '{{{ name }}}',
			]
		);

		$this->add_control(
			'steps_heading',
			[
				'label' => __( 'Steps', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'steps',
			[
				'label' => __( 'Step List', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'name',
						'label' => __( 'Step Title', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => __( 'Enter step title', 'seo-simple' ),
					],
					[
						'name' => 'text',
						'label' => __( 'Step Description', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::WYSIWYG,
						'label_block' => true,
						'placeholder' => __( 'Enter step description', 'seo-simple' ),
					],
					[
						'name' => 'image',
						'label' => __( 'Step Image', 'seo-simple' ),
						'type' => \Elementor\Controls_Manager::MEDIA,
					],
				],
				'title_field' => '{{{ name }}}',
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
				'description' => __( 'Enable HowTo schema markup for better SEO.', 'seo-simple' ),
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
			'title_color',
			[
				'label' => __( 'Title Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-howto-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-howto-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .seo-simple-howto-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .seo-simple-howto-content',
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
					'{{WRAPPER}} .seo-simple-howto-container' => 'text-align: {{VALUE}}',
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
		
		$title = $settings['title'];
		$description = $settings['description'];
		$image = $settings['image']['url'];
		$estimated_time = $settings['estimated_time'];
		$supplies = $settings['supplies'];
		$tools = $settings['tools'];
		$steps = $settings['steps'];
		$schema_enabled = $settings['schema_enabled'] === 'yes';

		if ( empty( $title ) || empty( $steps ) ) {
			echo '<p>' . __( 'Please provide a title and at least one step.', 'seo-simple' ) . '</p>';
			return;
		}

		$this->add_render_attribute( 'howto-container', 'class', 'seo-simple-howto-container' );
		
		if ( $schema_enabled ) {
			$this->add_render_attribute( 'howto-container', 'itemscope', '' );
			$this->add_render_attribute( 'howto-container', 'itemtype', 'https://schema.org/HowTo' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'howto-container' ) . '>';
		
		// Main image
		if ( ! empty( $image ) ) {
			echo '<div class="seo-simple-howto-image-wrapper">';
			echo '<img class="seo-simple-howto-image" src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '">';
			
			if ( $schema_enabled ) {
				echo '<link itemprop="image" href="' . esc_url( $image ) . '" />';
			}
			
			echo '</div>';
		}
		
		// Title
		echo '<h2 class="seo-simple-howto-title">';
		if ( $schema_enabled ) {
			echo '<span itemprop="name">' . esc_html( $title ) . '</span>';
		} else {
			echo esc_html( $title );
		}
		echo '</h2>';
		
		// Description
		if ( ! empty( $description ) ) {
			echo '<div class="seo-simple-howto-description">';
			if ( $schema_enabled ) {
				echo '<span itemprop="description">' . esc_html( $description ) . '</span>';
			} else {
				echo esc_html( $description );
			}
			echo '</div>';
		}
		
		// Estimated time
		if ( ! empty( $estimated_time ) ) {
			echo '<div class="seo-simple-howto-time">';
			echo '<i class="fas fa-clock"></i> ';
			if ( $schema_enabled ) {
				echo '<time itemprop="totalTime" datetime="' . esc_attr( $estimated_time ) . '">' . $this->format_duration( $estimated_time ) . '</time>';
			} else {
				echo $this->format_duration( $estimated_time );
			}
			echo '</div>';
		}
		
		// Supplies
		if ( ! empty( $supplies ) ) {
			echo '<div class="seo-simple-howto-section">';
			echo '<h3>' . __( 'Supplies', 'seo-simple' ) . '</h3>';
			echo '<ul class="seo-simple-howto-supplies">';
			
			foreach ( $supplies as $index => $supply ) {
				echo '<li class="seo-simple-howto-supply">';
				
				if ( $schema_enabled ) {
					echo '<span itemprop="supply" itemscope itemtype="https://schema.org/HowToSupply">';
					echo '<span itemprop="name">' . esc_html( $supply['name'] ) . '</span>';
					echo '</span>';
				} else {
					echo esc_html( $supply['name'] );
				}
				
				if ( ! empty( $supply['image']['url'] ) ) {
					echo '<img class="seo-simple-howto-supply-image" src="' . esc_url( $supply['image']['url'] ) . '" alt="' . esc_attr( $supply['name'] ) . '">';
				}
				
				echo '</li>';
			}
			
			echo '</ul>';
			echo '</div>';
		}
		
		// Tools
		if ( ! empty( $tools ) ) {
			echo '<div class="seo-simple-howto-section">';
			echo '<h3>' . __( 'Tools', 'seo-simple' ) . '</h3>';
			echo '<ul class="seo-simple-howto-tools">';
			
			foreach ( $tools as $index => $tool ) {
				echo '<li class="seo-simple-howto-tool">';
				
				if ( $schema_enabled ) {
					echo '<span itemprop="tool" itemscope itemtype="https://schema.org/HowToTool">';
					echo '<span itemprop="name">' . esc_html( $tool['name'] ) . '</span>';
					echo '</span>';
				} else {
					echo esc_html( $tool['name'] );
				}
				
				if ( ! empty( $tool['image']['url'] ) ) {
					echo '<img class="seo-simple-howto-tool-image" src="' . esc_url( $tool['image']['url'] ) . '" alt="' . esc_attr( $tool['name'] ) . '">';
				}
				
				echo '</li>';
			}
			
			echo '</ul>';
			echo '</div>';
		}
		
		// Steps
		if ( ! empty( $steps ) ) {
			echo '<div class="seo-simple-howto-section">';
			echo '<h3>' . __( 'Steps', 'seo-simple' ) . '</h3>';
			echo '<ol class="seo-simple-howto-steps">';
			
			foreach ( $steps as $index => $step ) {
				$step_number = $index + 1;
				
				echo '<li class="seo-simple-howto-step">';
				
				if ( $schema_enabled ) {
					echo '<div itemprop="step" itemscope itemtype="https://schema.org/HowToStep">';
					echo '<meta itemprop="position" content="' . esc_attr( $step_number ) . '" />';
				}
				
				echo '<div class="seo-simple-howto-step-content">';
				
				if ( ! empty( $step['name'] ) ) {
					echo '<h4 class="seo-simple-howto-step-title">';
					if ( $schema_enabled ) {
						echo '<span itemprop="name">' . esc_html( $step['name'] ) . '</span>';
					} else {
						echo esc_html( $step['name'] );
					}
					echo '</h4>';
				}
				
				if ( ! empty( $step['text'] ) ) {
					echo '<div class="seo-simple-howto-step-text">';
					if ( $schema_enabled ) {
						echo '<span itemprop="text">' . $this->parse_text_editor( $step['text'] ) . '</span>';
					} else {
						echo $this->parse_text_editor( $step['text'] );
					}
					echo '</div>';
				}
				
				if ( ! empty( $step['image']['url'] ) ) {
					echo '<img class="seo-simple-howto-step-image" src="' . esc_url( $step['image']['url'] ) . '" alt="' . esc_attr( $step['name'] ) . '">';
					
					if ( $schema_enabled ) {
						echo '<meta itemprop="image" content="' . esc_url( $step['image']['url'] ) . '" />';
					}
				}
				
				echo '</div>';
				
				if ( $schema_enabled ) {
					echo '</div>';
				}
				
				echo '</li>';
			}
			
			echo '</ol>';
			echo '</div>';
		}
		
		echo '</div>';
	}

	/**
	 * Format duration for display.
	 *
	 * @since    1.0.0
	 * @param    string    $duration    The duration in ISO 8601 format.
	 * @return   string                 The formatted duration.
	 */
	private function format_duration( $duration ) {
		// Simple parsing for common formats
		if ( preg_match( '/PT(\d+H)?(\d+M)?(\d+S)?/', $duration, $matches ) ) {
			$hours = isset( $matches[1] ) ? intval( str_replace( 'H', '', $matches[1] ) ) : 0;
			$minutes = isset( $matches[2] ) ? intval( str_replace( 'M', '', $matches[2] ) ) : 0;
			$seconds = isset( $matches[3] ) ? intval( str_replace( 'S', '', $matches[3] ) ) : 0;
			
			$formatted = '';
			
			if ( $hours > 0 ) {
				$formatted .= $hours . ' hour' . ( $hours > 1 ? 's' : '' );
			}
			
			if ( $minutes > 0 ) {
				if ( ! empty( $formatted ) ) {
					$formatted .= ', ';
				}
				$formatted .= $minutes . ' minute' . ( $minutes > 1 ? 's' : '' );
			}
			
			if ( $seconds > 0 && empty( $formatted ) ) {
				$formatted .= $seconds . ' second' . ( $seconds > 1 ? 's' : '' );
			}
			
			return $formatted;
		}
		
		return $duration;
	}
}