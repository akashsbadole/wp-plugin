<?php

/**
 * Page Builder Integration Class
 *
 * Handles integration with popular page builders like Elementor and Divi.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 */

/**
 * Page Builder Integration Class
 *
 * This class handles integration with popular page builders to ensure
 * SEO features work correctly with their content.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/includes
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Page_Builders {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Page_Builders    $instance    The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * The SEO meta handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Meta    $seo_meta    The SEO meta handler instance.
	 */
	private $seo_meta;

	/**
	 * Get the single instance of the class.
	 *
	 * @since    1.0.0
	 * @static
	 * @return   Seo_Simple_Page_Builders    The single instance of the class.
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
	 * @access   private
	 */
	private function __construct() {
		// Initialize SEO meta handler
		$this->seo_meta = new Seo_Simple_Meta();
		
		// Initialize page builder integrations
		$this->init_elementor_integration();
		$this->init_divi_integration();
		$this->init_beaver_builder_integration();
		$this->init_visual_composer_integration();
		$this->init_fusion_builder_integration();
		
		// Add filters for content extraction
		add_filter( 'seo_simple_get_content', array( $this, 'extract_page_builder_content' ), 10, 2 );
		add_filter( 'seo_simple_get_title', array( $this, 'extract_page_builder_title' ), 10, 2 );
		add_filter( 'seo_simple_get_description', array( $this, 'extract_page_builder_description' ), 10, 2 );
		
		// Add filters for meta box compatibility
		add_filter( 'seo_simple_meta_box_supported', array( $this, 'is_page_builder_supported' ), 10, 2 );
		
		// Add action for saving page builder data
		add_action( 'save_post', array( $this, 'save_page_builder_seo_data' ), 10, 2 );
		
		// Add scripts for page builder compatibility
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_page_builder_scripts' ) );
	}

	/**
	 * Initialize Elementor integration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_elementor_integration() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		
		// Add Elementor data to SEO analysis
		add_action( 'elementor/editor/after_save', array( $this, 'elementor_after_save' ), 10, 2 );
		
		// Add Elementor widgets for SEO
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_elementor_widgets' ) );
		
		// Add Elementor panel tabs for SEO
		add_action( 'elementor/element/before_section_start', array( $this, 'add_elementor_seo_section' ), 10, 2 );
		
		// Add Elementor frontend scripts
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_elementor_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_elementor_scripts' ) );
	}

	/**
	 * Initialize Divi integration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_divi_integration() {
		if ( ! class_exists( 'ET_Builder_Plugin' ) ) {
			return;
		}
		
		// Add Divi data to SEO analysis
		add_action( 'et_builder_save_layout_settings', array( $this, 'divi_after_save' ), 10, 2 );
		
		// Add Divi modules for SEO
		add_action( 'et_builder_ready', array( $this, 'register_divi_modules' ) );
		
		// Add Divi field for SEO
		add_filter( 'et_builder_module_fields', array( $this, 'add_divi_seo_fields' ), 10, 2 );
		
		// Add Divi frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_divi_scripts' ) );
	}

	/**
	 * Initialize Beaver Builder integration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_beaver_builder_integration() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return;
		}
		
		// Add Beaver Builder data to SEO analysis
		add_action( 'fl_builder_after_save_layout', array( $this, 'beaver_builder_after_save' ), 10, 2 );
		
		// Add Beaver Builder modules for SEO
		add_action( 'fl_builder_register_modules', array( $this, 'register_beaver_builder_modules' ) );
		
		// Add Beaver Builder frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_beaver_builder_scripts' ) );
	}

	/**
	 * Initialize Visual Composer integration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_visual_composer_integration() {
		if ( ! class_exists( 'Vc_Manager' ) ) {
			return;
		}
		
		// Add Visual Composer data to SEO analysis
		add_action( 'vc_after_save', array( $this, 'visual_composer_after_save' ), 10, 2 );
		
		// Add Visual Composer elements for SEO
		add_action( 'vc_before_init', array( $this, 'register_visual_composer_elements' ) );
		
		// Add Visual Composer frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_visual_composer_scripts' ) );
	}

	/**
	 * Initialize Fusion Builder integration.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_fusion_builder_integration() {
		if ( ! class_exists( 'FusionBuilder' ) ) {
			return;
		}
		
		// Add Fusion Builder data to SEO analysis
		add_action( 'fusion_builder_after_save', array( $this, 'fusion_builder_after_save' ), 10, 2 );
		
		// Add Fusion Builder elements for SEO
		add_action( 'fusion_builder_before_init', array( $this, 'register_fusion_builder_elements' ) );
		
		// Add Fusion Builder frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_fusion_builder_scripts' ) );
	}

	/**
	 * Extract content from page builders.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The original content.
	 * @param    int       $post_id    The post ID.
	 * @return   string                The extracted content.
	 */
	public function extract_page_builder_content( $content, $post_id ) {
		// Check if Elementor is active and has data
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor_content = $this->get_elementor_content( $post_id );
			if ( $elementor_content ) {
				return $elementor_content;
			}
		}
		
		// Check if Divi is active and has data
		if ( class_exists( 'ET_Builder_Plugin' ) ) {
			$divi_content = $this->get_divi_content( $post_id );
			if ( $divi_content ) {
				return $divi_content;
			}
		}
		
		// Check if Beaver Builder is active and has data
		if ( class_exists( 'FLBuilder' ) ) {
			$beaver_content = $this->get_beaver_builder_content( $post_id );
			if ( $beaver_content ) {
				return $beaver_content;
			}
		}
		
		// Check if Visual Composer is active and has data
		if ( class_exists( 'Vc_Manager' ) ) {
			$vc_content = $this->get_visual_composer_content( $post_id );
			if ( $vc_content ) {
				return $vc_content;
			}
		}
		
		// Check if Fusion Builder is active and has data
		if ( class_exists( 'FusionBuilder' ) ) {
			$fusion_content = $this->get_fusion_builder_content( $post_id );
			if ( $fusion_content ) {
				return $fusion_content;
			}
		}
		
		return $content;
	}

	/**
	 * Extract title from page builders.
	 *
	 * @since    1.0.0
	 * @param    string    $title      The original title.
	 * @param    int       $post_id    The post ID.
	 * @return   string                The extracted title.
	 */
	public function extract_page_builder_title( $title, $post_id ) {
		// Get custom title from page builder meta
		$custom_title = get_post_meta( $post_id, '_seo_simple_page_builder_title', true );
		if ( $custom_title ) {
			return $custom_title;
		}
		
		return $title;
	}

	/**
	 * Extract description from page builders.
	 *
	 * @since    1.0.0
	 * @param    string    $description  The original description.
	 * @param    int       $post_id      The post ID.
	 * @return   string                    The extracted description.
	 */
	public function extract_page_builder_description( $description, $post_id ) {
		// Get custom description from page builder meta
		$custom_description = get_post_meta( $post_id, '_seo_simple_page_builder_description', true );
		if ( $custom_description ) {
			return $custom_description;
		}
		
		return $description;
	}

	/**
	 * Get Elementor content.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The Elementor content.
	 */
	private function get_elementor_content( $post_id ) {
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
		if ( ! $elementor_data ) {
			return '';
		}
		
		$elementor_data = json_decode( $elementor_data, true );
		if ( ! $elementor_data ) {
			return '';
		}
		
		return $this->parse_elementor_data( $elementor_data );
	}

	/**
	 * Parse Elementor data to extract content.
	 *
	 * @since    1.0.0
	 * @param    array     $data       The Elementor data.
	 * @return   string                The extracted content.
	 */
	private function parse_elementor_data( $data ) {
		$content = '';
		
		foreach ( $data as $element ) {
			if ( isset( $element['elType'] ) ) {
				switch ( $element['elType'] ) {
					case 'section':
						if ( isset( $element['elements'] ) ) {
							$content .= $this->parse_elementor_data( $element['elements'] );
						}
						break;
						
					case 'column':
						if ( isset( $element['elements'] ) ) {
							$content .= $this->parse_elementor_data( $element['elements'] );
						}
						break;
						
					case 'widget':
						$content .= $this->extract_elementor_widget_content( $element );
						break;
				}
			}
		}
		
		return $content;
	}

	/**
	 * Extract content from Elementor widget.
	 *
	 * @since    1.0.0
	 * @param    array     $widget     The Elementor widget data.
	 * @return   string                The widget content.
	 */
	private function extract_elementor_widget_content( $widget ) {
		$content = '';
		
		if ( ! isset( $widget['widgetType'] ) || ! isset( $widget['settings'] ) ) {
			return $content;
		}
		
		$widget_type = $widget['widgetType'];
		$settings = $widget['settings'];
		
		switch ( $widget_type ) {
			case 'heading':
				if ( isset( $settings['title'] ) ) {
					$content .= $settings['title'] . ' ';
				}
				break;
				
			case 'text-editor':
				if ( isset( $settings['editor'] ) ) {
					$content .= $settings['editor'] . ' ';
				}
				break;
				
			case 'image':
				if ( isset( $settings['alt'] ) ) {
					$content .= $settings['alt'] . ' ';
				}
				break;
				
			case 'button':
				if ( isset( $settings['text'] ) ) {
					$content .= $settings['text'] . ' ';
				}
				break;
				
			case 'accordion':
				if ( isset( $settings['tabs'] ) ) {
					foreach ( $settings['tabs'] as $tab ) {
						if ( isset( $tab['tab_title'] ) ) {
							$content .= $tab['tab_title'] . ' ';
						}
						if ( isset( $tab['tab_content'] ) ) {
							$content .= $tab['tab_content'] . ' ';
						}
					}
				}
				break;
				
			case 'tabs':
				if ( isset( $settings['tabs'] ) ) {
					foreach ( $settings['tabs'] as $tab ) {
						if ( isset( $tab['tab_title'] ) ) {
							$content .= $tab['tab_title'] . ' ';
						}
						if ( isset( $tab['tab_content'] ) ) {
							$content .= $tab['tab_content'] . ' ';
						}
					}
				}
				break;
				
			case 'toggle':
				if ( isset( $settings['tabs'] ) ) {
					foreach ( $settings['tabs'] as $tab ) {
						if ( isset( $tab['tab_title'] ) ) {
							$content .= $tab['tab_title'] . ' ';
						}
						if ( isset( $tab['tab_content'] ) ) {
							$content .= $tab['tab_content'] . ' ';
						}
					}
				}
				break;
		}
		
		return $content;
	}

	/**
	 * Get Divi content.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The Divi content.
	 */
	private function get_divi_content( $post_id ) {
		// Check if post has Divi builder data
		$has_builder = get_post_meta( $post_id, '_et_pb_use_builder', true );
		if ( ! $has_builder ) {
			return '';
		}
		
		// Get Divi builder data
		$builder_data = get_post_meta( $post_id, '_et_pb_old_content', true );
		if ( ! $builder_data ) {
			return '';
		}
		
		return $builder_data;
	}

	/**
	 * Get Beaver Builder content.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The Beaver Builder content.
	 */
	private function get_beaver_builder_content( $post_id ) {
		// Check if post has Beaver Builder data
		if ( ! FLBuilderModel::is_builder_enabled( $post_id ) ) {
			return '';
		}
		
		// Get Beaver Builder data
		$nodes = FLBuilderModel::get_nodes( $post_id );
		if ( ! $nodes ) {
			return '';
		}
		
		return $this->parse_beaver_builder_data( $nodes );
	}

	/**
	 * Parse Beaver Builder data to extract content.
	 *
	 * @since    1.0.0
	 * @param    array     $nodes      The Beaver Builder nodes.
	 * @return   string                The extracted content.
	 */
	private function parse_beaver_builder_data( $nodes ) {
		$content = '';
		
		foreach ( $nodes as $node ) {
			if ( isset( $node->type ) && $node->type === 'module' ) {
				$content .= $this->extract_beaver_builder_module_content( $node );
			}
			
			if ( isset( $node->nodes ) && ! empty( $node->nodes ) ) {
				$content .= $this->parse_beaver_builder_data( $node->nodes );
			}
		}
		
		return $content;
	}

	/**
	 * Extract content from Beaver Builder module.
	 *
	 * @since    1.0.0
	 * @param    object    $module     The Beaver Builder module.
	 * @return   string                The module content.
	 */
	private function extract_beaver_builder_module_content( $module ) {
		$content = '';
		
		if ( ! isset( $module->settings ) ) {
			return $content;
		}
		
		$settings = $module->settings;
		
		// Extract content based on module type
		switch ( $module->settings->type ) {
			case 'heading':
				if ( isset( $settings->heading ) ) {
					$content .= $settings->heading . ' ';
				}
				break;
				
			case 'rich-text':
				if ( isset( $settings->text ) ) {
					$content .= $settings->text . ' ';
				}
				break;
				
			case 'photo':
				if ( isset( $settings->caption ) ) {
					$content .= $settings->caption . ' ';
				}
				break;
				
			case 'button':
				if ( isset( $settings->text ) ) {
					$content .= $settings->text . ' ';
				}
				break;
				
			case 'accordion':
				if ( isset( $settings->items ) ) {
					foreach ( $settings->items as $item ) {
						if ( isset( $item->label ) ) {
							$content .= $item->label . ' ';
						}
						if ( isset( $item->content ) ) {
							$content .= $item->content . ' ';
						}
					}
				}
				break;
		}
		
		return $content;
	}

	/**
	 * Get Visual Composer content.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The Visual Composer content.
	 */
	private function get_visual_composer_content( $post_id ) {
		// Check if post has Visual Composer data
		$vc_content = get_post_meta( $post_id, '_wpb_vc_js_status', true );
		if ( ! $vc_content ) {
			return '';
		}
		
		// Get the post content which contains VC shortcodes
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		
		return do_shortcode( $post->post_content );
	}

	/**
	 * Get Fusion Builder content.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @return   string                The Fusion Builder content.
	 */
	private function get_fusion_builder_content( $post_id ) {
		// Check if post has Fusion Builder data
		$has_builder = get_post_meta( $post_id, 'fusion_builder_status', true );
		if ( ! $has_builder ) {
			return '';
		}
		
		// Get the post content which contains Fusion Builder shortcodes
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}
		
		return do_shortcode( $post->post_content );
	}

	/**
	 * Check if page builder is supported.
	 *
	 * @since    1.0.0
	 * @param    bool     $supported   Whether the meta box is supported.
	 * @param    int      $post_id     The post ID.
	 * @return   bool                  Whether the meta box is supported.
	 */
	public function is_page_builder_supported( $supported, $post_id ) {
		// Check if any page builder is active
		if ( class_exists( '\Elementor\Plugin' ) && get_post_meta( $post_id, '_elementor_edit_mode', true ) === 'builder' ) {
			return true;
		}
		
		if ( class_exists( 'ET_Builder_Plugin' ) && get_post_meta( $post_id, '_et_pb_use_builder', true ) ) {
			return true;
		}
		
		if ( class_exists( 'FLBuilder' ) && FLBuilderModel::is_builder_enabled( $post_id ) ) {
			return true;
		}
		
		if ( class_exists( 'Vc_Manager' ) && get_post_meta( $post_id, '_wpb_vc_js_status', true ) ) {
			return true;
		}
		
		if ( class_exists( 'FusionBuilder' ) && get_post_meta( $post_id, 'fusion_builder_status', true ) ) {
			return true;
		}
		
		return $supported;
	}

	/**
	 * Save page builder SEO data.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    WP_Post   $post       The post object.
	 */
	public function save_page_builder_seo_data( $post_id, $post ) {
		// Check if this is an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Save Elementor SEO data
		if ( isset( $_POST['elementor_seo_title'] ) ) {
			update_post_meta( $post_id, '_seo_simple_page_builder_title', sanitize_text_field( $_POST['elementor_seo_title'] ) );
		}
		
		if ( isset( $_POST['elementor_seo_description'] ) ) {
			update_post_meta( $post_id, '_seo_simple_page_builder_description', sanitize_textarea_field( $_POST['elementor_seo_description'] ) );
		}
		
		// Save Divi SEO data
		if ( isset( $_POST['et_pb_seo_title'] ) ) {
			update_post_meta( $post_id, '_seo_simple_page_builder_title', sanitize_text_field( $_POST['et_pb_seo_title'] ) );
		}
		
		if ( isset( $_POST['et_pb_seo_description'] ) ) {
			update_post_meta( $post_id, '_seo_simple_page_builder_description', sanitize_textarea_field( $_POST['et_pb_seo_description'] ) );
		}
	}

	/**
	 * Enqueue page builder scripts.
	 *
	 * @since    1.0.0
	 * @param    string    $hook       The current admin page.
	 */
	public function enqueue_page_builder_scripts( $hook ) {
		// Only enqueue on post edit pages
		if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
			return;
		}
		
		// Enqueue Elementor compatibility script only if Elementor is active
		if ( defined('ELEMENTOR_VERSION') && is_plugin_active('elementor/elementor.php') ) {
			$elementor_deps = array('jquery');
			
			// Add elementor-common if available
			if ( wp_script_is('elementor-common', 'registered') ) {
				$elementor_deps[] = 'elementor-common';
			}
			
			// Add backbone-marionette if available
			if ( wp_script_is('backbone-marionette', 'registered') ) {
				$elementor_deps[] = 'backbone-marionette';
			}
			
			wp_enqueue_script(
				'seo-simple-elementor-compat',
				plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/seo-simple-elementor-compat.js',
				$elementor_deps,
				SEO_SIMPLE_VERSION,
				true
			);
			
			wp_localize_script(
				'seo-simple-elementor-compat',
				'seoSimpleElementor',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'seo_simple_elementor_nonce' ),
				)
			);
		}
		
		// Enqueue Divi compatibility script
		if ( class_exists( 'ET_Builder_Plugin' ) ) {
			wp_enqueue_script(
				'seo-simple-divi-compat',
				plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/seo-simple-divi-compat.js',
				array( 'jquery' ),
				SEO_SIMPLE_VERSION,
				true
			);
			
			wp_localize_script(
				'seo-simple-divi-compat',
				'seoSimpleDivi',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'seo_simple_divi_nonce' ),
				)
			);
		}
	}

	/**
	 * Elementor after save handler.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $data       The Elementor data.
	 */
	public function elementor_after_save( $post_id, $data ) {
		// Trigger SEO analysis after Elementor save
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
	}

	/**
	 * Divi after save handler.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $data       The Divi data.
	 */
	public function divi_after_save( $post_id, $data ) {
		// Trigger SEO analysis after Divi save
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
	}

	/**
	 * Beaver Builder after save handler.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $data       The Beaver Builder data.
	 */
	public function beaver_builder_after_save( $post_id, $data ) {
		// Trigger SEO analysis after Beaver Builder save
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
	}

	/**
	 * Visual Composer after save handler.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $data       The Visual Composer data.
	 */
	public function visual_composer_after_save( $post_id, $data ) {
		// Trigger SEO analysis after Visual Composer save
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
	}

	/**
	 * Fusion Builder after save handler.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    The post ID.
	 * @param    array     $data       The Fusion Builder data.
	 */
	public function fusion_builder_after_save( $post_id, $data ) {
		// Trigger SEO analysis after Fusion Builder save
		if ( class_exists( 'Seo_Simple_Analyzer' ) ) {
			$analyzer = new Seo_Simple_Analyzer();
			$analyzer->analyze_post( $post_id );
		}
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @since    1.0.0
	 */
	public function register_elementor_widgets() {
		// Include Elementor widgets
		if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-elementor-widgets.php' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-seo-simple-elementor-widgets.php';
		}
	}

	/**
	 * Add Elementor SEO section.
	 *
	 * @since    1.0.0
	 * @param    object    $element    The Elementor element.
	 * @param    string    $section_id The section ID.
	 */
	public function add_elementor_seo_section( $element, $section_id ) {
		// This would add SEO fields to Elementor elements
		// Implementation would be in a separate file
	}

	/**
	 * Register Elementor styles.
	 *
	 * @since    1.0.0
	 */
	public function register_elementor_styles() {
		wp_register_style(
			'seo-simple-elementor',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/seo-simple-elementor.css',
			array(),
			SEO_SIMPLE_VERSION
		);
	}

	/**
	 * Register Elementor scripts.
	 *
	 * @since    1.0.0
	 */
	public function register_elementor_scripts() {
		wp_register_script(
			'seo-simple-elementor',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/seo-simple-elementor.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}

	/**
	 * Register Divi modules.
	 *
	 * @since    1.0.0
	 */
	public function register_divi_modules() {
		// This would register custom Divi modules for SEO
		// Implementation would be in a separate file
	}

	/**
	 * Add Divi SEO fields.
	 *
	 * @since    1.0.0
	 * @param    array     $fields     The Divi fields.
	 * @param    string    $module     The module name.
	 * @return   array                 The modified fields.
	 */
	public function add_divi_seo_fields( $fields, $module ) {
		// This would add SEO fields to Divi modules
		// Implementation would be in a separate file
		return $fields;
	}

	/**
	 * Register Divi scripts.
	 *
	 * @since    1.0.0
	 */
	public function register_divi_scripts() {
		wp_register_script(
			'seo-simple-divi',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/seo-simple-divi.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}

	/**
	 * Register Beaver Builder modules.
	 *
	 * @since    1.0.0
	 */
	public function register_beaver_builder_modules() {
		// This would register custom Beaver Builder modules for SEO
		// Implementation would be in a separate file
	}

	/**
	 * Register Beaver Builder scripts.
	 *
	 * @since    1.0.0
	 */
	public function register_beaver_builder_scripts() {
		wp_register_script(
			'seo-simple-beaver',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/seo-simple-beaver.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}

	/**
	 * Register Visual Composer elements.
	 *
	 * @since    1.0.0
	 */
	public function register_visual_composer_elements() {
		// This would register custom Visual Composer elements for SEO
		// Implementation would be in a separate file
	}

	/**
	 * Register Visual Composer scripts.
	 *
	 * @since    1.0.0
	 */
	public function register_visual_composer_scripts() {
		wp_register_script(
			'seo-simple-vc',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/seo-simple-vc.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}

	/**
	 * Register Fusion Builder elements.
	 *
	 * @since    1.0.0
	 */
	public function register_fusion_builder_elements() {
		// This would register custom Fusion Builder elements for SEO
		// Implementation would be in a separate file
	}

	/**
	 * Register Fusion Builder scripts.
	 *
	 * @since    1.0.0
	 */
	public function register_fusion_builder_scripts() {
		wp_register_script(
			'seo-simple-fusion',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/seo-simple-fusion.js',
			array( 'jquery' ),
			SEO_SIMPLE_VERSION,
			true
		);
	}
}