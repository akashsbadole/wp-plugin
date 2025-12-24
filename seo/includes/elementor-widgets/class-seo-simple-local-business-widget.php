<?php

/**
 * SEO Simple Elementor Local Business Widget
 *
 * Custom Elementor widget for displaying local business information with schema markup.
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
 * SEO Simple Elementor Local Business Widget Class
 *
 * Creates a local business widget for Elementor with LocalBusiness schema markup.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes/elementor-widgets
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_Local_Business_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since    1.0.0
	 * @return   string    Widget name.
	 */
	public function get_name() {
		return 'seo-simple-local-business';
	}

	/**
	 * Get widget title.
	 *
	 * @since    1.0.0
	 * @return   string    Widget title.
	 */
	public function get_title() {
		return __( 'SEO Local Business', 'seo-simple' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since    1.0.0
	 * @return   string    Widget icon.
	 */
	public function get_icon() {
		return 'eicon-map-pin';
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
			'business_type',
			[
				'label' => __( 'Business Type', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'LocalBusiness',
				'options' => [
					'LocalBusiness' => __( 'Local Business', 'seo-simple' ),
					'Restaurant' => __( 'Restaurant', 'seo-simple' ),
					'Store' => __( 'Store', 'seo-simple' ),
					'ProfessionalService' => __( 'Professional Service', 'seo-simple' ),
					'HealthAndBeautyBusiness' => __( 'Health & Beauty', 'seo-simple' ),
					'AutomotiveBusiness' => __( 'Automotive', 'seo-simple' ),
					'HomeAndConstructionBusiness' => __( 'Home & Construction', 'seo-simple' ),
					'FinancialService' => __( 'Financial Service', 'seo-simple' ),
					'LegalService' => __( 'Legal Service', 'seo-simple' ),
				],
			]
		);

		$this->add_control(
			'business_name',
			[
				'label' => __( 'Business Name', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter business name', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'description',
			[
				'label' => __( 'Description', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => __( 'Enter business description', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'image',
			[
				'label' => __( 'Business Image', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_control(
			'address',
			[
				'label' => __( 'Address', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter business address', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'phone',
			[
				'label' => __( 'Phone', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter phone number', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'email',
			[
				'label' => __( 'Email', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Enter email address', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'website',
			[
				'label' => __( 'Website', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'https://example.com', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'hours',
			[
				'label' => __( 'Business Hours', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => __( 'e.g., Mon-Fri: 9am-5pm, Sat: 10am-2pm', 'seo-simple' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'price_range',
			[
				'label' => __( 'Price Range', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'e.g., $$, $$$', 'seo-simple' ),
				'label_block' => true,
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
				'description' => __( 'Enable LocalBusiness schema markup for better SEO.', 'seo-simple' ),
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
					'{{WRAPPER}} .seo-simple-local-business-title' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} .seo-simple-local-business-content' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .seo-simple-local-business-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .seo-simple-local-business-content',
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
					'{{WRAPPER}} .seo-simple-local-business-container' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'image_size',
			[
				'label' => __( 'Image Size', 'seo-simple' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 500,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 150,
				],
				'selectors' => [
					'{{WRAPPER}} .seo-simple-local-business-image' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
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
		
		$business_type = $settings['business_type'];
		$business_name = $settings['business_name'];
		$description = $settings['description'];
		$image = $settings['image']['url'];
		$address = $settings['address'];
		$phone = $settings['phone'];
		$email = $settings['email'];
		$website = $settings['website']['url'];
		$hours = $settings['hours'];
		$price_range = $settings['price_range'];
		$schema_enabled = $settings['schema_enabled'] === 'yes';

		if ( empty( $business_name ) ) {
			echo '<p>' . __( 'Please enter a business name.', 'seo-simple' ) . '</p>';
			return;
		}

		$this->add_render_attribute( 'business-container', 'class', 'seo-simple-local-business-container' );
		
		if ( $schema_enabled ) {
			$this->add_render_attribute( 'business-container', 'itemscope', '' );
			$this->add_render_attribute( 'business-container', 'itemtype', 'https://schema.org/' . esc_attr( $business_type ) );
		}

		echo '<div ' . $this->get_render_attribute_string( 'business-container' ) . '>';
		
		// Business image
		if ( ! empty( $image ) ) {
			echo '<div class="seo-simple-local-business-image-wrapper">';
			echo '<img class="seo-simple-local-business-image" src="' . esc_url( $image ) . '" alt="' . esc_attr( $business_name ) . '">';
			
			if ( $schema_enabled ) {
				echo '<link itemprop="image" href="' . esc_url( $image ) . '" />';
			}
			
			echo '</div>';
		}
		
		// Business name
		echo '<h2 class="seo-simple-local-business-title">';
		if ( $schema_enabled ) {
			echo '<span itemprop="name">' . esc_html( $business_name ) . '</span>';
		} else {
			echo esc_html( $business_name );
		}
		echo '</h2>';
		
		// Description
		if ( ! empty( $description ) ) {
			echo '<div class="seo-simple-local-business-description">';
			if ( $schema_enabled ) {
				echo '<span itemprop="description">' . esc_html( $description ) . '</span>';
			} else {
				echo esc_html( $description );
			}
			echo '</div>';
		}
		
		// Address
		if ( ! empty( $address ) ) {
			echo '<div class="seo-simple-local-business-address">';
			echo '<i class="fas fa-map-marker-alt"></i> ';
			if ( $schema_enabled ) {
				echo '<span itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
				echo '<span itemprop="streetAddress">' . esc_html( $address ) . '</span>';
				echo '</span>';
			} else {
				echo esc_html( $address );
			}
			echo '</div>';
		}
		
		// Phone
		if ( ! empty( $phone ) ) {
			echo '<div class="seo-simple-local-business-phone">';
			echo '<i class="fas fa-phone"></i> ';
			if ( $schema_enabled ) {
				echo '<span itemprop="telephone">' . esc_html( $phone ) . '</span>';
			} else {
				echo '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>';
			}
			echo '</div>';
		}
		
		// Email
		if ( ! empty( $email ) ) {
			echo '<div class="seo-simple-local-business-email">';
			echo '<i class="fas fa-envelope"></i> ';
			if ( $schema_enabled ) {
				echo '<span itemprop="email">' . esc_html( $email ) . '</span>';
			} else {
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			}
			echo '</div>';
		}
		
		// Website
		if ( ! empty( $website ) ) {
			echo '<div class="seo-simple-local-business-website">';
			echo '<i class="fas fa-globe"></i> ';
			if ( $schema_enabled ) {
				echo '<a href="' . esc_url( $website ) . '" itemprop="url" target="_blank">' . esc_html( $website ) . '</a>';
			} else {
				echo '<a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a>';
			}
			echo '</div>';
		}
		
		// Business hours
		if ( ! empty( $hours ) ) {
			echo '<div class="seo-simple-local-business-hours">';
			echo '<i class="fas fa-clock"></i> ';
			if ( $schema_enabled ) {
				echo '<span itemprop="openingHoursSpecification" itemscope itemtype="https://schema.org/OpeningHoursSpecification">';
				echo '<span itemprop="description">' . esc_html( $hours ) . '</span>';
				echo '</span>';
			} else {
				echo esc_html( $hours );
			}
			echo '</div>';
		}
		
		// Price range
		if ( ! empty( $price_range ) ) {
			echo '<div class="seo-simple-local-business-price">';
			echo '<i class="fas fa-dollar-sign"></i> ';
			if ( $schema_enabled ) {
				echo '<span itemprop="priceRange">' . esc_html( $price_range ) . '</span>';
			} else {
				echo esc_html( $price_range );
			}
			echo '</div>';
		}
		
		echo '</div>';
	}
}