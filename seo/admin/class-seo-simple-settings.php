<?php

/**
 * Handles the plugin settings page.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 */

/**
 * Handles the plugin settings page.
 *
 * This class handles all operations related to the plugin settings page including
 * registering settings, rendering the settings page, and processing form submissions.
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin
 * @author     akash <akash@example.com>
 */
class Seo_Simple_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The settings page slug.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $page_slug    The settings page slug.
	 */
	private $page_slug = 'seo-simple-settings';

	/**
	 * The settings option group.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_group    The settings option group.
	 */
	private $option_group = 'seo_simple_settings_group';

	/**
	 * The settings option name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_name    The settings option name.
	 */
	private $option_name = 'seo_simple_settings';

	/**
	 * The code snippets handler instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Seo_Simple_Code_Snippets    $code_snippets    The code snippets handler instance.
	 */
	private $code_snippets;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->code_snippets = new Seo_Simple_Code_Snippets();
	}

	/**
	 * Register the settings page.
	 *
	 * @since    1.0.0
	 */
	public function register_settings_page() {
		add_options_page(
			__( 'SEO Simple Settings', 'seo-simple' ),
			__( 'SEO Simple', 'seo-simple' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register the settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		// Register the main settings option
		register_setting(
			$this->option_group,
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		// Enqueue settings page scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );

		// General Settings Section
		add_settings_section(
			'seo_simple_general_section',
			__( 'General Settings', 'seo-simple' ),
			array( $this, 'render_general_section_description' ),
			$this->page_slug . '_general'
		);

		add_settings_field(
			'seo_enable_posts',
			__( 'Enable SEO for Posts', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_general',
			'seo_simple_general_section',
			array(
				'name' => 'seo_enable_posts',
				'description' => __( 'Enable SEO meta box for posts.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_pages',
			__( 'Enable SEO for Pages', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_general',
			'seo_simple_general_section',
			array(
				'name' => 'seo_enable_pages',
				'description' => __( 'Enable SEO meta box for pages.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_categories',
			__( 'Enable SEO for Categories', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_general',
			'seo_simple_general_section',
			array(
				'name' => 'seo_enable_categories',
				'description' => __( 'Enable SEO meta box for categories.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_tags',
			__( 'Enable SEO for Tags', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_general',
			'seo_simple_general_section',
			array(
				'name' => 'seo_enable_tags',
				'description' => __( 'Enable SEO meta box for tags.', 'seo-simple' ),
				'default' => 1
			)
		);

		// Titles & Meta Section
		add_settings_section(
			'seo_simple_titles_section',
			__( 'Titles & Meta', 'seo-simple' ),
			array( $this, 'render_titles_section_description' ),
			$this->page_slug . '_titles'
		);

		add_settings_field(
			'seo_title_separator',
			__( 'Title Separator', 'seo-simple' ),
			array( $this, 'render_select_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_title_separator',
				'options' => array(
					'-' => __( 'Hyphen (-)', 'seo-simple' ),
					'|' => __( 'Pipe (|)', 'seo-simple' ),
					'_' => __( 'Underscore (_)', 'seo-simple' ),
					'•' => __( 'Bullet (•)', 'seo-simple' ),
					'»' => __( 'Right Angle (»)', 'seo-simple' ),
				),
				'description' => __( 'Character used to separate title parts.', 'seo-simple' ),
				'default' => '-'
			)
		);

		add_settings_field(
			'seo_title_format',
			__( 'Title Template', 'seo-simple' ),
			array( $this, 'render_text_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_title_format',
				'description' => __( 'Available tags: %post_title%, %site_name%, %separator%', 'seo-simple' ),
				'default' => '%post_title% | %site_name%'
			)
		);

		add_settings_field(
			'seo_description_template',
			__( 'Description Template', 'seo-simple' ),
			array( $this, 'render_textarea_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_description_template',
				'description' => __( 'Default meta description template. Can include post excerpt or content.', 'seo-simple' ),
				'default' => ''
			)
		);

		add_settings_field(
			'seo_home_title',
			__( 'Homepage Title', 'seo-simple' ),
			array( $this, 'render_text_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_home_title',
				'description' => __( 'Leave empty to use default site title.', 'seo-simple' ),
				'default' => ''
			)
		);

		add_settings_field(
			'seo_home_description',
			__( 'Homepage Description', 'seo-simple' ),
			array( $this, 'render_textarea_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_home_description',
				'description' => __( 'Meta description for the homepage.', 'seo-simple' ),
				'default' => ''
			)
		);

		add_settings_field(
			'seo_title_length',
			__( 'Max Title Length', 'seo-simple' ),
			array( $this, 'render_number_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_title_length',
				'description' => __( 'Maximum character length for titles.', 'seo-simple' ),
				'default' => 60,
				'min' => 40,
				'max' => 120
			)
		);

		add_settings_field(
			'seo_description_length',
			__( 'Max Description Length', 'seo-simple' ),
			array( $this, 'render_number_field' ),
			$this->page_slug . '_titles',
			'seo_simple_titles_section',
			array(
				'name' => 'seo_description_length',
				'description' => __( 'Maximum character length for descriptions.', 'seo-simple' ),
				'default' => 160,
				'min' => 120,
				'max' => 320
			)
		);

		// Social Settings Section
		add_settings_section(
			'seo_simple_social_section',
			__( 'Social Settings', 'seo-simple' ),
			array( $this, 'render_social_section_description' ),
			$this->page_slug . '_social'
		);

		add_settings_field(
			'seo_enable_og',
			__( 'Enable Open Graph', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_social',
			'seo_simple_social_section',
			array(
				'name' => 'seo_enable_og',
				'description' => __( 'Add Open Graph meta tags for social media.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_twitter',
			__( 'Enable Twitter Cards', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_social',
			'seo_simple_social_section',
			array(
				'name' => 'seo_enable_twitter',
				'description' => __( 'Add Twitter Card meta tags.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_json_ld',
			__( 'Enable JSON-LD', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_social',
			'seo_simple_social_section',
			array(
				'name' => 'seo_enable_json_ld',
				'description' => __( 'Add JSON-LD structured data.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_og_image',
			__( 'Default OG Image', 'seo-simple' ),
			array( $this, 'render_image_field' ),
			$this->page_slug . '_social',
			'seo_simple_social_section',
			array(
				'name' => 'seo_og_image',
				'description' => __( 'Default image for Open Graph when no post-specific image is set.', 'seo-simple' ),
				'default' => ''
			)
		);

		// Sitemap Settings Section
		add_settings_section(
			'seo_simple_sitemap_section',
			__( 'Sitemap Settings', 'seo-simple' ),
			array( $this, 'render_sitemap_section_description' ),
			$this->page_slug . '_sitemap'
		);

		add_settings_field(
			'seo_enable_sitemap',
			__( 'Enable XML Sitemap', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_sitemap',
			'seo_simple_sitemap_section',
			array(
				'name' => 'seo_enable_sitemap',
				'description' => __( 'Generate XML sitemap for search engines.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_sitemap_include_posts',
			__( 'Include Posts', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_sitemap',
			'seo_simple_sitemap_section',
			array(
				'name' => 'seo_sitemap_include_posts',
				'description' => __( 'Include posts in XML sitemap.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_sitemap_include_pages',
			__( 'Include Pages', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_sitemap',
			'seo_simple_sitemap_section',
			array(
				'name' => 'seo_sitemap_include_pages',
				'description' => __( 'Include pages in XML sitemap.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_sitemap_include_categories',
			__( 'Include Categories', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_sitemap',
			'seo_simple_sitemap_section',
			array(
				'name' => 'seo_sitemap_include_categories',
				'description' => __( 'Include categories in XML sitemap.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_sitemap_include_tags',
			__( 'Include Tags', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_sitemap',
			'seo_simple_sitemap_section',
			array(
				'name' => 'seo_sitemap_include_tags',
				'description' => __( 'Include tags in XML sitemap.', 'seo-simple' ),
				'default' => 1
			)
		);

		// Advanced Settings Section
		add_settings_section(
			'seo_simple_advanced_section',
			__( 'Advanced Settings', 'seo-simple' ),
			array( $this, 'render_advanced_section_description' ),
			$this->page_slug . '_advanced'
		);

		add_settings_field(
			'seo_show_metabox',
			__( 'Show SEO Meta Box', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_advanced',
			'seo_simple_advanced_section',
			array(
				'name' => 'seo_show_metabox',
				'description' => __( 'Show SEO meta box in post editor.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_analysis',
			__( 'Enable SEO Analysis', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_advanced',
			'seo_simple_advanced_section',
			array(
				'name' => 'seo_enable_analysis',
				'description' => __( 'Enable real-time SEO analysis in the meta box.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_readability',
			__( 'Enable Readability Analysis', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_advanced',
			'seo_simple_advanced_section',
			array(
				'name' => 'seo_enable_readability',
				'description' => __( 'Enable readability analysis in the meta box.', 'seo-simple' ),
				'default' => 1
			)
		);

		add_settings_field(
			'seo_enable_robots',
			__( 'Enable Robots.txt', 'seo-simple' ),
			array( $this, 'render_checkbox_field' ),
			$this->page_slug . '_advanced',
			'seo_simple_advanced_section',
			array(
				'name' => 'seo_enable_robots',
				'description' => __( 'Enable virtual robots.txt editing.', 'seo-simple' ),
				'default' => 1
			)
		);
	}

	/**
	 * Enqueue scripts for the settings page.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_settings_scripts() {
		// Only load on our settings page
		$screen = get_current_screen();
		if ( $screen && $screen->id === 'settings_page_seo-simple-settings' ) {
			// Define preset data for code snippets
			$presets = array(
				'google-analytics' => array(
					'name' => 'Google Analytics',
					'code' => '<!-- Global site tag (gtag.js) - Google Analytics -->' . "\n" .
								'<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>' . "\n" .
								'<script>' . "\n" .
								'  window.dataLayer = window.dataLayer || [];' . "\n" .
								'  function gtag(){dataLayer.push(arguments);}' . "\n" .
								'  gtag(\'js\', new Date());' . "\n" .
								'  gtag(\'config\', \'GA_TRACKING_ID\');' . "\n" .
								'</script>',
					'location' => 'header'
				),
				'facebook-pixel' => array(
					'name' => 'Facebook Pixel',
					'code' => '<!-- Facebook Pixel Code -->' . "\n" .
								'<script>' . "\n" .
								'!function(f,b,e,v,n,t,s)' . "\n" .
								'{if(f.fbq)return;n=f.fbq=function(){n.callMethod?' . "\n" .
								'n.callMethod.apply(n,arguments):n.queue.push(arguments);}' . "\n" .
								'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';' . "\n" .
								'n.queue=[];t=b.createElement(e);t.async=!0;' . "\n" .
								't.src=v;s=b.getElementsByTagName(e)[0];' . "\n" .
								's.parentNode.insertBefore(t,s)}(window, document,\'script\',' . "\n" .
								'\'https://connect.facebook.net/en_US/fbevents.js\');' . "\n" .
								'fbq(\'init\', \'YOUR_PIXEL_ID\');' . "\n" .
								'fbq(\'track\', \'PageView\');' . "\n" .
								'</script>' . "\n" .
								'<noscript><img height="1" width="1" style="display:none"' . "\n" .
								'src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"' . "\n" .
								'/></noscript>',
					'location' => 'header'
				)
			);

			// Localize script
			wp_localize_script(
				$this->plugin_name . '-admin',
				'seoSimpleSettings',
				array(
					'pageSlug' => $this->page_slug,
					'nonce' => wp_create_nonce( 'seo_simple_regenerate_sitemap' ),
					'i18n' => array(
						'selectImage' => __( 'Select Image', 'seo-simple' ),
						'useThisImage' => __( 'Use this image', 'seo-simple' ),
						'preview' => __( 'Preview', 'seo-simple' ),
						'copied' => __( 'Copied!', 'seo-simple' ),
						'copyUrl' => __( 'Copy URL', 'seo-simple' ),
						'regenerating' => __( 'Regenerating...', 'seo-simple' ),
						'regenerateSitemap' => __( 'Regenerate Sitemap', 'seo-simple' ),
						'errorRegenerate' => __( 'Error regenerating sitemap. Please try again.', 'seo-simple' ),
						'errorImport' => __( 'Error importing settings. Please check the file and try again.', 'seo-simple' ),
						'errorReset' => __( 'Error resetting settings. Please try again.', 'seo-simple' ),
						'selectFile' => __( 'Please select a file to import.', 'seo-simple' ),
						'resetConfirm' => __( 'Are you sure you want to reset all settings to their default values? This action cannot be undone.', 'seo-simple' ),
						'presetConfirm' => __( 'This will replace the current content. Do you want to continue?', 'seo-simple' ),
						'nameRequired' => __( 'Please enter a name for the snippet.', 'seo-simple' ),
						'codeRequired' => __( 'Please enter the code for the snippet.', 'seo-simple' ),
					),
					'presets' => $presets
				)
			);
		}
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @since    1.0.0
	 * @param    array    $input    The input data to sanitize.
	 * @return   array              The sanitized data.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// General Settings
		$sanitized['seo_enable_posts'] = isset( $input['seo_enable_posts'] ) ? 1 : 0;
		$sanitized['seo_enable_pages'] = isset( $input['seo_enable_pages'] ) ? 1 : 0;
		$sanitized['seo_enable_categories'] = isset( $input['seo_enable_categories'] ) ? 1 : 0;
		$sanitized['seo_enable_tags'] = isset( $input['seo_enable_tags'] ) ? 1 : 0;

		// Custom Post Types
		$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names' );
		foreach ( $post_types as $post_type ) {
			$option_name = 'seo_enable_' . $post_type;
			$sanitized[$option_name] = isset( $input[$option_name] ) ? 1 : 0;
		}

		// Titles & Meta
		$sanitized['seo_title_separator'] = sanitize_text_field( $input['seo_title_separator'] );
		$sanitized['seo_title_format'] = sanitize_text_field( $input['seo_title_format'] );
		$sanitized['seo_description_template'] = sanitize_textarea_field( $input['seo_description_template'] );
		$sanitized['seo_home_title'] = sanitize_text_field( $input['seo_home_title'] );
		$sanitized['seo_home_description'] = sanitize_textarea_field( $input['seo_home_description'] );
		$sanitized['seo_title_length'] = absint( $input['seo_title_length'] );
		$sanitized['seo_description_length'] = absint( $input['seo_description_length'] );

		// Content Type Templates
		$content_types = array( 'post', 'page' ) + $post_types;
		foreach ( $content_types as $content_type ) {
			$title_option = 'seo_' . $content_type . '_title_format';
			$desc_option = 'seo_' . $content_type . '_description_format';
			
			if ( isset( $input[$title_option] ) ) {
				$sanitized[$title_option] = sanitize_text_field( $input[$title_option] );
			}
			if ( isset( $input[$desc_option] ) ) {
				$sanitized[$desc_option] = sanitize_textarea_field( $input[$desc_option] );
			}
		}

		// Taxonomy Templates
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			$title_option = 'seo_' . $taxonomy . '_title_format';
			$desc_option = 'seo_' . $taxonomy . '_description_format';
			
			if ( isset( $input[$title_option] ) ) {
				$sanitized[$title_option] = sanitize_text_field( $input[$title_option] );
			}
			if ( isset( $input[$desc_option] ) ) {
				$sanitized[$desc_option] = sanitize_textarea_field( $input[$desc_option] );
			}
		}

		// Social Settings
		$sanitized['seo_enable_og'] = isset( $input['seo_enable_og'] ) ? 1 : 0;
		$sanitized['seo_enable_twitter'] = isset( $input['seo_enable_twitter'] ) ? 1 : 0;
		$sanitized['seo_enable_json_ld'] = isset( $input['seo_enable_json_ld'] ) ? 1 : 0;
		$sanitized['seo_og_image'] = esc_url_raw( $input['seo_og_image'] );
		
		// Social Media Profiles
		if ( isset( $input['seo_facebook_url'] ) ) {
			$sanitized['seo_facebook_url'] = esc_url_raw( $input['seo_facebook_url'] );
		}
		if ( isset( $input['seo_twitter_username'] ) ) {
			$sanitized['seo_twitter_username'] = sanitize_text_field( $input['seo_twitter_username'] );
		}
		if ( isset( $input['seo_instagram_url'] ) ) {
			$sanitized['seo_instagram_url'] = esc_url_raw( $input['seo_instagram_url'] );
		}
		if ( isset( $input['seo_linkedin_url'] ) ) {
			$sanitized['seo_linkedin_url'] = esc_url_raw( $input['seo_linkedin_url'] );
		}

		// Sitemap Settings
		$sanitized['seo_enable_sitemap'] = isset( $input['seo_enable_sitemap'] ) ? 1 : 0;
		$sanitized['seo_sitemap_include_posts'] = isset( $input['seo_sitemap_include_posts'] ) ? 1 : 0;
		$sanitized['seo_sitemap_include_pages'] = isset( $input['seo_sitemap_include_pages'] ) ? 1 : 0;
		$sanitized['seo_sitemap_include_categories'] = isset( $input['seo_sitemap_include_categories'] ) ? 1 : 0;
		$sanitized['seo_sitemap_include_tags'] = isset( $input['seo_sitemap_include_tags'] ) ? 1 : 0;

		// Advanced Settings
		$sanitized['seo_show_metabox'] = isset( $input['seo_show_metabox'] ) ? 1 : 0;
		$sanitized['seo_enable_analysis'] = isset( $input['seo_enable_analysis'] ) ? 1 : 0;
		$sanitized['seo_enable_readability'] = isset( $input['seo_enable_readability'] ) ? 1 : 0;
		$sanitized['seo_enable_robots'] = isset( $input['seo_enable_robots'] ) ? 1 : 0;

		return $sanitized;
	}

	/**
	 * Get the settings.
	 *
	 * @since    1.0.0
	 * @return   array    The settings.
	 */
	public function get_settings() {
		$defaults = array(
			// General Settings
			'seo_enable_posts' => 1,
			'seo_enable_pages' => 1,
			'seo_enable_categories' => 1,
			'seo_enable_tags' => 1,

			// Titles & Meta
			'seo_title_separator' => '-',
			'seo_title_format' => '%post_title% | %site_name%',
			'seo_description_template' => '',
			'seo_home_title' => '',
			'seo_home_description' => '',
			'seo_title_length' => 60,
			'seo_description_length' => 160,

			// Social Settings
			'seo_enable_og' => 1,
			'seo_enable_twitter' => 1,
			'seo_enable_json_ld' => 1,
			'seo_og_image' => '',

			// Sitemap Settings
			'seo_enable_sitemap' => 1,
			'seo_sitemap_include_posts' => 1,
			'seo_sitemap_include_pages' => 1,
			'seo_sitemap_include_categories' => 1,
			'seo_sitemap_include_tags' => 1,

			// Advanced Settings
			'seo_show_metabox' => 1,
			'seo_enable_analysis' => 1,
			'seo_enable_readability' => 1,
			'seo_enable_robots' => 1,
		);

		$settings = get_option( $this->option_name, array() );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get a specific setting value.
	 *
	 * @since    1.0.0
	 * @param    string    $key        The setting key.
	 * @param    mixed     $default    The default value.
	 * @return   mixed                 The setting value.
	 */
	public function get_setting( $key, $default = null ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		$tabs = array(
			'general' => __( 'General', 'seo-simple' ),
			'titles' => __( 'Titles & Meta', 'seo-simple' ),
			'social' => __( 'Social', 'seo-simple' ),
			'sitemap' => __( 'Sitemap', 'seo-simple' ),
			'code-snippets' => __( 'Code Snippets', 'seo-simple' ),
			'advanced' => __( 'Advanced', 'seo-simple' ),
		);

		// Handle code snippets actions
		if ( $active_tab === 'code-snippets' ) {
			$this->handle_code_snippets_actions();
		}

		// Include the settings page template
		include_once plugin_dir_path( __FILE__ ) . 'partials/settings-page.php';
	}

	/**
	 * Render the general section description.
	 *
	 * @since    1.0.0
	 */
	public function render_general_section_description() {
		echo '<p>' . esc_html__( 'Configure general SEO settings for your site.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render the titles section description.
	 *
	 * @since    1.0.0
	 */
	public function render_titles_section_description() {
		echo '<p>' . esc_html__( 'Configure title and meta description templates.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render the social section description.
	 *
	 * @since    1.0.0
	 */
	public function render_social_section_description() {
		echo '<p>' . esc_html__( 'Configure social media integration settings.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render the sitemap section description.
	 *
	 * @since    1.0.0
	 */
	public function render_sitemap_section_description() {
		echo '<p>' . esc_html__( 'Configure XML sitemap settings.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render the advanced section description.
	 *
	 * @since    1.0.0
	 */
	public function render_advanced_section_description() {
		echo '<p>' . esc_html__( 'Configure advanced SEO settings.', 'seo-simple' ) . '</p>';
	}

	/**
	 * Render a text field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_text_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$value = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$id = str_replace( '_', '-', $name );

		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
		
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render a number field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_number_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$value = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$id = str_replace( '_', '-', $name );
		$min = isset( $args['min'] ) ? $args['min'] : '';
		$max = isset( $args['max'] ) ? $args['max'] : '';

		$attributes = '';
		if ( ! empty( $min ) ) {
			$attributes .= ' min="' . esc_attr( $min ) . '"';
		}
		if ( ! empty( $max ) ) {
			$attributes .= ' max="' . esc_attr( $max ) . '"';
		}

		echo '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '" value="' . esc_attr( $value ) . '" class="small-text"' . $attributes . ' />';
		
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render a textarea field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_textarea_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$value = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$id = str_replace( '_', '-', $name );

		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '" rows="5" class="large-text">' . esc_textarea( $value ) . '</textarea>';
		
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render a checkbox field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$checked = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : 0 );
		$id = str_replace( '_', '-', $name );

		echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '" value="1" ' . checked( $checked, 1, false ) . ' />';
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $args['description'] ) . '</label>';
	}

	/**
	 * Render a select field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_select_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$value = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$id = str_replace( '_', '-', $name );

		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '">';
		
		foreach ( $args['options'] as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( $option_value ) . '" ' . selected( $value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		
		echo '</select>';
		
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Render an image field.
	 *
	 * @since    1.0.0
	 * @param    array    $args    The field arguments.
	 */
	public function render_image_field( $args ) {
		$settings = $this->get_settings();
		$name = $args['name'];
		$value = isset( $settings[ $name ] ) ? $settings[ $name ] : ( isset( $args['default'] ) ? $args['default'] : '' );
		$id = str_replace( '_', '-', $name );

		echo '<div class="image-field-container">';
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $this->option_name . '[' . $name . ']' ) . '" value="' . esc_attr( $value ) . '" class="regular-text image-url" />';
		echo '<button type="button" class="button image-upload-button" data-target="' . esc_attr( $id ) . '">' . esc_html__( 'Select Image', 'seo-simple' ) . '</button>';
		
		if ( ! empty( $value ) ) {
			echo '<div class="image-preview">';
			echo '<img src="' . esc_url( $value ) . '" alt="' . esc_attr__( 'Preview', 'seo-simple' ) . '" style="max-width: 100px; max-height: 100px; margin-top: 10px;" />';
			echo '</div>';
		}
		
		echo '</div>';
		
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Export settings.
	 *
	 * @since    1.0.0
	 */
	public function export_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seo-simple' ) );
		}

		$settings = $this->get_settings();
		$filename = 'seo-simple-settings-' . date( 'Y-m-d' ) . '.json';

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Expires: 0' );
		header( 'Pragma: public' );

		echo wp_json_encode( $settings );
		exit;
	}

	/**
	 * Import settings.
	 *
	 * @since    1.0.0
	 */
	public function import_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seo-simple' ) );
		}

		if ( isset( $_FILES['settings_file'] ) && $_FILES['settings_file']['error'] == UPLOAD_ERR_OK ) {
			$json_content = file_get_contents( $_FILES['settings_file']['tmp_name'] );
			$settings = json_decode( $json_content, true );

			if ( is_array( $settings ) ) {
				update_option( $this->option_name, $settings );
				wp_redirect( add_query_arg( array( 'page' => $this->page_slug, 'imported' => 'true' ), admin_url( 'options-general.php' ) ) );
				exit;
			} else {
				wp_redirect( add_query_arg( array( 'page' => $this->page_slug, 'import_error' => 'true' ), admin_url( 'options-general.php' ) ) );
				exit;
			}
		} else {
			wp_redirect( add_query_arg( array( 'page' => $this->page_slug, 'import_error' => 'true' ), admin_url( 'options-general.php' ) ) );
			exit;
		}
	}

	/**
		* Handle code snippets actions.
		*
		* @since    1.0.0
		*/
	public function handle_code_snippets_actions() {
		// Handle form submission for adding/editing snippets
		if ( isset( $_POST['seo_simple_code_snippet_nonce'] ) &&
			 wp_verify_nonce( $_POST['seo_simple_code_snippet_nonce'], 'seo_simple_code_snippet_action' ) ) {
			$this->handle_snippet_form_submission();
		}
		
		// Handle deletion of snippets
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_snippet' &&
			 isset( $_GET['id'] ) && isset( $_GET['_wpnonce'] ) ) {
			$this->handle_snippet_deletion();
		}
	}

	/**
		* Handle form submission for adding/editing snippets.
		*
		* @since    1.0.0
		*/
	private function handle_snippet_form_submission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$id = isset( $_POST['snippet_id'] ) ? intval( $_POST['snippet_id'] ) : 0;
		$name = sanitize_text_field( $_POST['snippet_name'] );
		$code = wp_kses_post( $_POST['snippet_code'] );
		$location = sanitize_text_field( $_POST['snippet_location'] );
		$status = sanitize_text_field( $_POST['snippet_status'] );
		$priority = intval( $_POST['snippet_priority'] );
		
		$data = array(
			'name' => $name,
			'code' => $code,
			'location' => $location,
			'status' => $status,
			'priority' => $priority
		);
		
		if ( $id > 0 ) {
			$result = $this->code_snippets->update_snippet( $id, $data );
			if ( $result ) {
				wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=updated' ) );
				exit;
			}
		} else {
			$result = $this->code_snippets->add_snippet( $data );
			if ( $result ) {
				wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=added' ) );
				exit;
			}
		}
	}

	/**
		* Handle deletion of snippets.
		*
		* @since    1.0.0
		*/
	private function handle_snippet_deletion() {
		if ( ! isset( $_GET['_wpnonce'] ) ||
			 ! wp_verify_nonce( $_GET['_wpnonce'], 'seo_simple_delete_snippet_' . $_GET['id'] ) ) {
			wp_die( __( 'Security check failed!', 'seo-simple' ) );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'seo-simple' ) );
		}
		
		$id = intval( $_GET['id'] );
		$result = $this->code_snippets->delete_snippet( $id );
		
		if ( $result ) {
			wp_redirect( admin_url( 'options-general.php?page=seo-simple-settings&tab=code-snippets&message=deleted' ) );
			exit;
		}
	}

	/**
		* Get all code snippets.
		*
		* @since    1.0.0
		* @return   array    Array of code snippets.
		*/
	public function get_all_snippets() {
		return $this->code_snippets->get_all_snippets();
	}

	/**
		* Get a single code snippet by ID.
		*
		* @since    1.0.0
		* @param    int      $id    The snippet ID.
		* @return   object|null    The snippet object or null if not found.
		*/
	public function get_snippet( $id ) {
		return $this->code_snippets->get_snippet( $id );
	}

	/**
	 * Display the list of code snippets.
	 *
	 * @since    1.0.0
	 * @param    string    $message    Optional message to display.
	 */
	public function display_snippets_list( $message = '' ) {
		$snippets = $this->get_all_snippets();
		include_once plugin_dir_path( __FILE__ ) . 'partials/settings-code-snippets-list.php';
	}

	/**
	 * Display the add/edit form for code snippets.
	 *
	 * @since    1.0.0
	 * @param    object    $snippet    The snippet object (for edit mode).
	 * @param    string    $preset     The preset to use (for add mode).
	 */
	public function display_snippet_form( $snippet = null, $preset = '' ) {
		$is_edit = $snippet !== null;
		
		// Get preset data if specified
		$preset_data = array();
		if ( ! empty( $preset ) && ! $is_edit ) {
			switch ( $preset ) {
				case 'google-analytics':
					$preset_data = array(
						'name' => 'Google Analytics',
						'code' => '<!-- Global site tag (gtag.js) - Google Analytics -->' . "\n" .
								  '<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>' . "\n" .
								  '<script>' . "\n" .
								  '  window.dataLayer = window.dataLayer || [];' . "\n" .
								  '  function gtag(){dataLayer.push(arguments);}' . "\n" .
								  '  gtag(\'js\', new Date());' . "\n" .
								  '  gtag(\'config\', \'GA_TRACKING_ID\');' . "\n" .
								  '</script>',
						'location' => 'header'
					);
					break;
				case 'facebook-pixel':
					$preset_data = array(
						'name' => 'Facebook Pixel',
						'code' => '<!-- Facebook Pixel Code -->' . "\n" .
								  '<script>' . "\n" .
								  '!function(f,b,e,v,n,t,s)' . "\n" .
								  '{if(f.fbq)return;n=f.fbq=function(){n.callMethod?' . "\n" .
								  'n.callMethod.apply(n,arguments):n.queue.push(arguments);}' . "\n" .
								  'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';' . "\n" .
								  'n.queue=[];t=b.createElement(e);t.async=!0;' . "\n" .
								  't.src=v;s=b.getElementsByTagName(e)[0];' . "\n" .
								  's.parentNode.insertBefore(t,s)}(window, document,\'script\',' . "\n" .
								  '\'https://connect.facebook.net/en_US/fbevents.js\');' . "\n" .
								  'fbq(\'init\', \'YOUR_PIXEL_ID\');' . "\n" .
								  'fbq(\'track\', \'PageView\');' . "\n" .
								  '</script>' . "\n" .
								  '<noscript><img height="1" width="1" style="display:none"' . "\n" .
								  'src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"' . "\n" .
								  '/></noscript>',
						'location' => 'header'
					);
					break;
			}
		}
		
		include_once plugin_dir_path( __FILE__ ) . 'partials/settings-code-snippets-form.php';
	}
}
