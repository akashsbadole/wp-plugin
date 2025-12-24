<?php

/**
 * SEO Simple Elementor Widgets
 *
 * Custom Elementor widgets for SEO features.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Simple Elementor Widgets Class
 *
 * Registers and manages custom Elementor widgets for SEO features.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Elementor_Widgets {

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register widgets.
	 *
	 * @since    1.0.0
	 */
	public function register_widgets() {
		// Include widget files
		require_once __DIR__ . '/elementor-widgets/class-seo-simple-breadcrumbs-widget.php';
		require_once __DIR__ . '/elementor-widgets/class-seo-simple-faq-widget.php';
		require_once __DIR__ . '/elementor-widgets/class-seo-simple-review-widget.php';
		require_once __DIR__ . '/elementor-widgets/class-seo-simple-local-business-widget.php';
		require_once __DIR__ . '/elementor-widgets/class-seo-simple-howto-widget.php';
		
		// Register widgets
		\Elementor\Plugin::instance()->widgets_manager->register( new \Seo_Simple_Elementor_Breadcrumbs_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register( new \Seo_Simple_Elementor_FAQ_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register( new \Seo_Simple_Elementor_Review_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register( new \Seo_Simple_Elementor_Local_Business_Widget() );
		\Elementor\Plugin::instance()->widgets_manager->register( new \Seo_Simple_Elementor_HowTo_Widget() );
	}
}

// Initialize the widgets
new Seo_Simple_Elementor_Widgets();