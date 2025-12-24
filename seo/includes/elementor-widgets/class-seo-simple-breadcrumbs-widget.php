<?php

/**
 * SEO Simple Elementor Breadcrumbs Widget
 *
 * Custom Elementor widget for displaying SEO-friendly breadcrumbs.
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
 * SEO Simple Elementor Breadcrumbs Widget Class
 *
 * Creates a breadcrumbs widget for Elementor with SEO benefits.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_Breadcrumbs_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since    1.0.0
	 * @return   string    Widget name.
	 */
	public function get_name() {
		return 'seo-simple-breadcrumbs';
	}

	/**
	 * Get widget title.
	 *
	 * @since    1.0.0
	 * @return   string    Widget title.
	 */
	public function get_title() {
		return __( 'SEO Breadcrumbs', 'seo-simple' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since    1.0.0
	 * @return   string    Widget icon.
	 */
	public function get_icon() {
		return 'eicon-navigation-horizontal';
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
			'separator',
			[
				'label' => __( 'Separator', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '>',
				'placeholder' => __( 'Enter separator character', 'seo-simple' ),
			]
		);

		$this->add_control(
			'home_text',
			[
				'label' => __( 'Home Text', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Home', 'seo-simple' ),
				'placeholder' => __( 'Enter home text', 'seo-simple' ),
			]
		);

		$this->add_control(
			'show_current',
			[
				'label' => __( 'Show Current Page', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', 'seo-simple' ),
				'label_off' => __( 'Hide', 'seo-simple' ),
				'return_value' => 'yes',
				'default' => 'yes',
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
				'description' => __( 'Enable BreadcrumbList schema markup for better SEO.', 'seo-simple' ),
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
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Text Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-breadcrumbs a, {{WRAPPER}} .seo-simple-breadcrumbs span' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => __( 'Link Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#0073aa',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-breadcrumbs a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => __( 'Link Hover Color', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#005a87',
				'selectors' => [
					'{{WRAPPER}} .seo-simple-breadcrumbs a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .seo-simple-breadcrumbs',
			]
		);

		$this->add_responsive_control(
			'separator_spacing',
			[
				'label' => __( 'Separator Spacing', 'seo-simple' ),
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
					'{{WRAPPER}} .seo-simple-breadcrumb-separator' => 'margin: 0 {{SIZE}}{{UNIT}};',
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

		$separator = ! empty( $settings['separator'] ) ? $settings['separator'] : '>';
		$home_text = ! empty( $settings['home_text'] ) ? $settings['home_text'] : __( 'Home', 'seo-simple' );
		$show_current = $settings['show_current'] === 'yes';
		$schema_enabled = $settings['schema_enabled'] === 'yes';
		$alignment = ! empty( $settings['alignment'] ) ? $settings['alignment'] : 'left';

		// Generate breadcrumbs
		$breadcrumbs = $this->generate_breadcrumbs( $separator, $home_text, $show_current );

		if ( empty( $breadcrumbs ) ) {
			return;
		}

		// Add alignment class
		$this->add_render_attribute( 'wrapper', 'class', 'seo-simple-breadcrumbs elementor-align-' . $alignment );

		// Add schema markup if enabled
		if ( $schema_enabled ) {
			$this->add_render_attribute( 'wrapper', 'itemscope', '' );
			$this->add_render_attribute( 'wrapper', 'itemtype', 'https://schema.org/BreadcrumbList' );
		}

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
		echo $breadcrumbs;
		echo '</div>';
	}

	/**
	 * Generate breadcrumbs HTML.
	 *
	 * @since    1.0.0
	 * @param    string    $separator    The separator character.
	 * @param    string    $home_text    The home text.
	 * @param    bool      $show_current Whether to show current page.
	 * @return   string                  The breadcrumbs HTML.
	 */
	private function generate_breadcrumbs( $separator, $home_text, $show_current ) {
		$schema_enabled = $this->get_settings_for_display( 'schema_enabled' ) === 'yes';
		$html = '';
		$position = 1;

		// Home link
		$html .= '<a href="' . esc_url( home_url( '/' ) ) . '"';
		if ( $schema_enabled ) {
			$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
		}
		$html .= '>';
		if ( $schema_enabled ) {
			$html .= '<span itemprop="name">' . esc_html( $home_text ) . '</span>';
		} else {
			$html .= esc_html( $home_text );
		}
		if ( $schema_enabled ) {
			$html .= '<meta itemprop="position" content="' . $position . '" />';
		}
		$html .= '</a>';

		if ( is_front_page() ) {
			return $html;
		}

		$html .= '<span class="seo-simple-breadcrumb-separator">' . esc_html( $separator ) . '</span>';
		$position++;

		// Blog page
		if ( is_home() ) {
			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . single_post_title( '', false ) . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= single_post_title( '', false );
				}
				$html .= '</span>';
			}
			return $html;
		}

		// Categories, tags, etc.
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			if ( $term->parent ) {
				$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
				$ancestors = array_reverse( $ancestors );
				foreach ( $ancestors as $ancestor ) {
					$ancestor_term = get_term( $ancestor, $term->taxonomy );
					$html .= '<a href="' . esc_url( get_term_link( $ancestor_term ) ) . '"';
					if ( $schema_enabled ) {
						$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
					}
					$html .= '>';
					if ( $schema_enabled ) {
						$html .= '<span itemprop="name">' . esc_html( $ancestor_term->name ) . '</span>';
					} else {
						$html .= esc_html( $ancestor_term->name );
					}
					if ( $schema_enabled ) {
						$html .= '<meta itemprop="position" content="' . $position . '" />';
					}
					$html .= '</a>';
					$html .= '<span class="seo-simple-breadcrumb-separator">' . esc_html( $separator ) . '</span>';
					$position++;
				}
			}

			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . single_term_title( '', false ) . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= single_term_title( '', false );
				}
				$html .= '</span>';
			}
			return $html;
		}

		// Single post
		if ( is_single() && ! is_attachment() ) {
			$post_type = get_post_type();
			
			// Add post type archive if it exists
			if ( get_post_type_archive_link( $post_type ) ) {
				$post_type_object = get_post_type_object( $post_type );
				$html .= '<a href="' . esc_url( get_post_type_archive_link( $post_type ) ) . '"';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . esc_html( $post_type_object->labels->name ) . '</span>';
				} else {
					$html .= esc_html( $post_type_object->labels->name );
				}
				if ( $schema_enabled ) {
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				}
				$html .= '</a>';
				$html .= '<span class="seo-simple-breadcrumb-separator">' . esc_html( $separator ) . '</span>';
				$position++;
			}

			// Add categories
			$categories = get_the_category();
			if ( $categories && ! is_wp_error( $categories ) ) {
				$category = $categories[0];
				$html .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '"';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . esc_html( $category->name ) . '</span>';
				} else {
					$html .= esc_html( $category->name );
				}
				if ( $schema_enabled ) {
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				}
				$html .= '</a>';
				$html .= '<span class="seo-simple-breadcrumb-separator">' . esc_html( $separator ) . '</span>';
				$position++;
			}

			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . get_the_title() . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= get_the_title();
				}
				$html .= '</span>';
			}
			return $html;
		}

		// Pages
		if ( is_page() ) {
			$post = get_post();
			if ( $post->post_parent ) {
				$ancestors = get_post_ancestors( $post );
				$ancestors = array_reverse( $ancestors );
				foreach ( $ancestors as $ancestor ) {
					$html .= '<a href="' . esc_url( get_permalink( $ancestor ) ) . '"';
					if ( $schema_enabled ) {
						$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
					}
					$html .= '>';
					if ( $schema_enabled ) {
						$html .= '<span itemprop="name">' . get_the_title( $ancestor ) . '</span>';
					} else {
						$html .= get_the_title( $ancestor );
					}
					if ( $schema_enabled ) {
						$html .= '<meta itemprop="position" content="' . $position . '" />';
					}
					$html .= '</a>';
					$html .= '<span class="seo-simple-breadcrumb-separator">' . esc_html( $separator ) . '</span>';
					$position++;
				}
			}

			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . get_the_title() . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= get_the_title();
				}
				$html .= '</span>';
			}
			return $html;
		}

		// Search results
		if ( is_search() ) {
			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . __( 'Search Results for: ', 'seo-simple' ) . get_search_query() . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= __( 'Search Results for: ', 'seo-simple' ) . get_search_query();
				}
				$html .= '</span>';
			}
			return $html;
		}

		// 404 page
		if ( is_404() ) {
			if ( $show_current ) {
				$html .= '<span';
				if ( $schema_enabled ) {
					$html .= ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
				}
				$html .= '>';
				if ( $schema_enabled ) {
					$html .= '<span itemprop="name">' . __( '404 Not Found', 'seo-simple' ) . '</span>';
					$html .= '<meta itemprop="position" content="' . $position . '" />';
				} else {
					$html .= __( '404 Not Found', 'seo-simple' );
				}
				$html .= '</span>';
			}
			return $html;
		}

		return $html;
	}
}