<?php

/**
 * HMS White-Label Branding
 *
 * This class handles the customization of the plugin's appearance and branding
 * to match the hospital's identity.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_White_Label {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->hooks();
    }

    /**
     * Register the hooks for the white-label functionality.
     */
    private function hooks() {
        add_action( 'admin_menu', array( $this, 'add_white_label_admin_page' ) );
        add_action( 'admin_init', array( $this, 'register_white_label_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_head', array( $this, 'apply_frontend_branding' ) );
        add_action( 'admin_head', array( $this, 'apply_admin_branding' ) );
        add_action( 'login_headerurl', array( $this, 'custom_login_logo_url' ) );
        add_action( 'login_headertitle', array( $this, 'custom_login_logo_title' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'custom_login_stylesheet' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'custom_email_from_name' ) );
        add_filter( 'wp_mail_from', array( $this, 'custom_email_from_address' ) );
        // Hook into email templates if a system is in place
        // add_filter( 'hms_email_template_header', array( $this, 'custom_email_header' ), 10, 2 );
        // add_filter( 'hms_email_template_footer', array( $this, 'custom_email_footer' ), 10, 2 );
    }

    /**
     * Add the admin page for white-label settings.
     */
    public function add_white_label_admin_page() {
        add_submenu_page(
            'hms-dashboard', // Parent slug
            'White-Label Branding', // Page title
            'White-Label', // Menu title
            'manage_options', // Capability
            'hms-white-label', // Menu slug
            array( $this, 'render_white_label_admin_page' ) // Callback function
        );
    }

    /**
     * Register settings, sections, and fields for the white-label admin page.
     */
    public function register_white_label_settings() {
        register_setting( 'hms_white_label_settings_group', 'hms_white_label_options', array( $this, 'sanitize_white_label_options' ) );

        // General Branding Section
        add_settings_section(
            'hms_wl_general_section',
            esc_html__( 'General Branding', 'hospital-management-system' ),
            array( $this, 'general_section_callback' ),
            'hms-white-label'
        );

        add_settings_field(
            'hms_wl_hospital_name',
            esc_html__( 'Hospital Name', 'hospital-management-system' ),
            array( $this, 'text_field_callback' ),
            'hms-white-label',
            'hms_wl_general_section',
            array(
                'id'          => 'hms_wl_hospital_name',
                'name'        => 'hms_white_label_options[hospital_name]',
                'description' => esc_html__( 'Enter the full name of the hospital.', 'hospital-management-system' ),
                'default'     => get_bloginfo( 'name' ),
            )
        );

        add_settings_field(
            'hms_wl_logo',
            esc_html__( 'Hospital Logo', 'hospital-management-system' ),
            array( $this, 'media_upload_field_callback' ),
            'hms-white-label',
            'hms_wl_general_section',
            array(
                'id'          => 'hms_wl_logo',
                'name'        => 'hms_white_label_options[logo_id]',
                'description' => esc_html__( 'Upload the hospital logo. Recommended size: 200x60 pixels.', 'hospital-management-system' ),
            )
        );
        
        add_settings_field(
            'hms_wl_favicon',
            esc_html__( 'Favicon', 'hospital-management-system' ),
            array( $this, 'media_upload_field_callback' ),
            'hms-white-label',
            'hms_wl_general_section',
            array(
                'id'          => 'hms_wl_favicon',
                'name'        => 'hms_white_label_options[favicon_id]',
                'description' => esc_html__( 'Upload a favicon. Recommended size: 32x32 pixels.', 'hospital-management-system' ),
            )
        );

        // Color Scheme Section
        add_settings_section(
            'hms_wl_color_section',
            esc_html__( 'Color Scheme', 'hospital-management-system' ),
            array( $this, 'color_section_callback' ),
            'hms-white-label'
        );

        add_settings_field(
            'hms_wl_primary_color',
            esc_html__( 'Primary Color', 'hospital-management-system' ),
            array( $this, 'color_field_callback' ),
            'hms-white-label',
            'hms_wl_color_section',
            array(
                'id'          => 'hms_wl_primary_color',
                'name'        => 'hms_white_label_options[primary_color]',
                'description' => esc_html__( 'Select the primary brand color.', 'hospital-management-system' ),
                'default'     => '#0073aa',
            )
        );

        add_settings_field(
            'hms_wl_secondary_color',
            esc_html__( 'Secondary Color', 'hospital-management-system' ),
            array( $this, 'color_field_callback' ),
            'hms-white-label',
            'hms_wl_color_section',
            array(
                'id'          => 'hms_wl_secondary_color',
                'name'        => 'hms_white_label_options[secondary_color]',
                'description' => esc_html__( 'Select the secondary brand color.', 'hospital-management-system' ),
                'default'     => '#00a0d2',
            )
        );
        
        add_settings_field(
            'hms_wl_background_color',
            esc_html__( 'Page Background Color', 'hospital-management-system' ),
            array( $this, 'color_field_callback' ),
            'hms-white-label',
            'hms_wl_color_section',
            array(
                'id'          => 'hms_wl_background_color',
                'name'        => 'hms_white_label_options[background_color]',
                'description' => esc_html__( 'Select the background color for pages.', 'hospital-management-system' ),
                'default'     => '#f1f1f1',
            )
        );

        // Typography Section
        add_settings_section(
            'hms_wl_typography_section',
            esc_html__( 'Typography', 'hospital-management-system' ),
            array( $this, 'typography_section_callback' ),
            'hms-white-label'
        );

        add_settings_field(
            'hms_wl_heading_font',
            esc_html__( 'Heading Font', 'hospital-management-system' ),
            array( $this, 'select_field_callback' ),
            'hms-white-label',
            'hms_wl_typography_section',
            array(
                'id'          => 'hms_wl_heading_font',
                'name'        => 'hms_white_label_options[heading_font]',
                'description' => esc_html__( 'Select the font for headings (H1-H6).', 'hospital-management-system' ),
                'options'     => $this->get_google_fonts(),
                'default'     => 'Arial, sans-serif',
            )
        );

        add_settings_field(
            'hms_wl_body_font',
            esc_html__( 'Body Font', 'hospital-management-system' ),
            array( $this, 'select_field_callback' ),
            'hms-white-label',
            'hms_wl_typography_section',
            array(
                'id'          => 'hms_wl_body_font',
                'name'        => 'hms_white_label_options[body_font]',
                'description' => esc_html__( 'Select the font for body text.', 'hospital-management-system' ),
                'options'     => $this->get_google_fonts(),
                'default'     => 'Helvetica, sans-serif',
            )
        );

        // Email Branding Section
        add_settings_section(
            'hms_wl_email_section',
            esc_html__( 'Email Branding', 'hospital-management-system' ),
            array( $this, 'email_section_callback' ),
            'hms-white-label'
        );

        add_settings_field(
            'hms_wl_email_from_name',
            esc_html__( 'Email "From" Name', 'hospital-management-system' ),
            array( $this, 'text_field_callback' ),
            'hms-white-label',
            'hms_wl_email_section',
            array(
                'id'          => 'hms_wl_email_from_name',
                'name'        => 'hms_white_label_options[email_from_name]',
                'description' => esc_html__( 'The name that appears in the "From" field of outgoing emails.', 'hospital-management-system' ),
                'default'     => get_bloginfo( 'name' ),
            )
        );

        add_settings_field(
            'hms_wl_email_from_address',
            esc_html__( 'Email "From" Address', 'hospital-management-system' ),
            array( $this, 'text_field_callback' ),
            'hms-white-label',
            'hms_wl_email_section',
            array(
                'id'          => 'hms_wl_email_from_address',
                'name'        => 'hms_white_label_options[email_from_address]',
                'description' => esc_html__( 'The email address that appears in the "From" field of outgoing emails.', 'hospital-management-system' ),
                'default'     => get_bloginfo( 'admin_email' ),
                'type'        => 'email',
            )
        );
        
        add_settings_field(
            'hms_wl_email_header_template',
            esc_html__( 'Email Header Template', 'hospital-management-system' ),
            array( $this, 'textarea_field_callback' ),
            'hms-white-label',
            'hms_wl_email_section',
            array(
                'id'          => 'hms_wl_email_header_template',
                'name'        => 'hms_white_label_options[email_header_template]',
                'description' => esc_html__( 'Custom HTML for the email header. Available tags: {hospital_name}, {logo_url}', 'hospital-management-system' ),
                'default'     => $this->get_default_email_header(),
            )
        );

        add_settings_field(
            'hms_wl_email_footer_template',
            esc_html__( 'Email Footer Template', 'hospital-management-system' ),
            array( $this, 'textarea_field_callback' ),
            'hms-white-label',
            'hms_wl_email_section',
            array(
                'id'          => 'hms_wl_email_footer_template',
                'name'        => 'hms_white_label_options[email_footer_template]',
                'description' => esc_html__( 'Custom HTML for the email footer. Available tags: {hospital_name}, {site_url}', 'hospital-management-system' ),
                'default'     => $this->get_default_email_footer(),
            )
        );

        // Patient Portal Section
        add_settings_section(
            'hms_wl_portal_section',
            esc_html__( 'Patient Portal & Mobile', 'hospital-management-system' ),
            array( $this, 'portal_section_callback' ),
            'hms-white-label'
        );

        add_settings_field(
            'hms_wl_portal_custom_domain',
            esc_html__( 'Patient Portal Custom Domain', 'hospital-management-system' ),
            array( $this, 'text_field_callback' ),
            'hms-white-label',
            'hms_wl_portal_section',
            array(
                'id'          => 'hms_wl_portal_custom_domain',
                'name'        => 'hms_white_label_options[portal_custom_domain]',
                'description' => esc_html__( 'Enter a custom domain for the patient portal (e.g., portal.yourhospital.com). Requires DNS configuration.', 'hospital-management-system' ),
                'default'     => '',
                'type'        => 'url',
            )
        );
    }

    /**
     * Render the white-label admin page.
     */
    public function render_white_label_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Customize the look and feel of the Hospital Management System to match your hospital\'s brand.', 'hospital-management-system' ); ?></p>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'hms_white_label_settings_group' );
                do_settings_sections( 'hms-white-label' );
                submit_button( esc_html__( 'Save Branding Settings', 'hospital-management-system' ), 'primary', 'submit_white_label' );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Callback for general section.
     */
    public function general_section_callback() {
        echo esc_html__( 'Set the basic branding information for your hospital.', 'hospital-management-system' );
    }

    /**
     * Callback for color section.
     */
    public function color_section_callback() {
        echo esc_html__( 'Customize the color scheme of the plugin.', 'hospital-management-system' );
    }

    /**
     * Callback for typography section.
     */
    public function typography_section_callback() {
        echo esc_html__( 'Choose fonts for headings and body text.', 'hospital-management-system' );
    }

    /**
     * Callback for email section.
     */
    public function email_section_callback() {
        echo esc_html__( 'Personalize email templates and sender information.', 'hospital-management-system' );
    }

    /**
     * Callback for portal section.
     */
    public function portal_section_callback() {
        echo esc_html__( 'Configure settings for the patient portal and mobile experience.', 'hospital-management-system' );
    }

    /**
     * Callback for text input fields.
     */
    public function text_field_callback( $args ) {
        $options = get_option( 'hms_white_label_options' );
        $value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $args['default'];
        $type    = isset( $args['type'] ) ? $args['type'] : 'text';
        ?>
        <input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Callback for color input fields.
     */
    public function color_field_callback( $args ) {
        $options = get_option( 'hms_white_label_options' );
        $value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $args['default'];
        ?>
        <input type="color" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
        <span class="color-preview-text" style="margin-left: 10px;"><?php echo esc_html( $value ); ?></span>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Callback for textarea fields.
     */
    public function textarea_field_callback( $args ) {
        $options = get_option( 'hms_white_label_options' );
        $value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $args['default'];
        ?>
        <textarea id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" rows="5" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Callback for select fields.
     */
    public function select_field_callback( $args ) {
        $options = get_option( 'hms_white_label_options' );
        $value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : $args['default'];
        ?>
        <select id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>">
            <?php foreach ( $args['options'] as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Callback for media upload fields.
     */
    public function media_upload_field_callback( $args ) {
        $options = get_option( 'hms_white_label_options' );
        $value   = isset( $options[ $args['id'] ] ) ? $options[ $args['id'] ] : '';
        $image_url = $value ? wp_get_attachment_url( $value ) : '';
        ?>
        <div class="media-upload-wrapper">
            <img class="media-upload-preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width: 200px; max-height: 60px; display: <?php echo $image_url ? 'block' : 'none'; ?>;" />
            <input type="hidden" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
            <button type="button" class="button media-upload-button"><?php esc_html_e( 'Upload Image', 'hospital-management-system' ); ?></button>
            <button 
                type="button" 
                class="button media-remove-button" 
                style="<?php echo $image_url ? "display:inline-block;" : "display:none;"; ?>">
                <?php esc_html_e( 'Remove Image', 'hospital-management-system' ); ?>
            </button>

        </div>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Enqueue admin scripts and styles for the white-label page.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'hospital-management-system_page_hms-white-label' !== $hook ) {
            return;
        }

        wp_enqueue_media(); // Required for the media uploader.
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( $this->plugin_name . '-white-label-admin', plugin_dir_url( __FILE__ ) . '../admin/js/hms-white-label.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );
    }

    /**
     * Apply branding styles to the frontend.
     */
    public function apply_frontend_branding() {
        $options = get_option( 'hms_white_label_options' );
        if ( empty( $options ) ) {
            return;
        }

        $css = '';

        // Favicon
        if ( ! empty( $options['favicon_id'] ) ) {
            $favicon_url = wp_get_attachment_url( $options['favicon_id'] );
            if ( $favicon_url ) {
                echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '" type="image/x-icon">';
            }
        }

        // Custom CSS
        if ( ! empty( $options['primary_color'] ) ) {
            $css .= ':root { --hms-primary-color: ' . esc_attr( $options['primary_color'] ) . '; }';
            $css .= 'a, .btn-primary, #submit, input[type="submit"] { background-color: var(--hms-primary-color) !important; border-color: var(--hms-primary-color) !important; color: #fff !important; }';
            $css .= 'a:hover, a:focus { color: ' . $this->adjust_brightness( $options['primary_color'], -30 ) . ' !important; }';
        }
        if ( ! empty( $options['secondary_color'] ) ) {
            $css .= ':root { --hms-secondary-color: ' . esc_attr( $options['secondary_color'] ) . '; }';
            $css .= '.btn-secondary { background-color: var(--hms-secondary-color) !important; border-color: var(--hms-secondary-color) !important; color: #fff !important; }';
        }
        if ( ! empty( $options['background_color'] ) ) {
            $css .= 'body { background-color: ' . esc_attr( $options['background_color'] ) . ' !important; }';
        }
        if ( ! empty( $options['heading_font'] ) ) {
            $css .= 'h1, h2, h3, h4, h5, h6 { font-family: ' . esc_attr( $options['heading_font'] ) . ' !important; }';
        }
        if ( ! empty( $options['body_font'] ) ) {
            $css .= 'body, p, span, div { font-family: ' . esc_attr( $options['body_font'] ) . ' !important; }';
        }
        
        // Apply custom logo to header if possible (this is theme-dependent)
        // This is a simplified example. A real implementation would need to target specific theme hooks.
        if ( ! empty( $options['logo_id'] ) ) {
            $logo_url = wp_get_attachment_url( $options['logo_id'] );
            if ( $logo_url ) {
                 // This is a very basic example. You'd need to know the theme's structure.
                 // For instance, if the theme uses a .site-logo class:
                 // $css .= '.site-logo { background-image: url(' . esc_url($logo_url) . '); background-size: contain; background-repeat: no-repeat; width: 200px; height: 60px; display: block; text-indent: -9999px; }';
            }
        }


        if ( ! empty( $css ) ) {
            echo '<style type="text/css">' . $css . '</style>';
        }
    }
    
    /**
     * Apply branding styles to the admin area.
     */
    public function apply_admin_branding() {
        $options = get_option( 'hms_white_label_options' );
        if ( empty( $options ) ) {
            return;
        }
        
        $css = '';
        // Admin Bar Logo
        if ( ! empty( $options['logo_id'] ) ) {
            $logo_url = wp_get_attachment_url( $options['logo_id'] );
            if ( $logo_url ) {
                // Replace the default WordPress 'W' logo in the admin bar.
                // This targets the default WP admin bar logo. It might not work with all admin bar customizations.
                $css .= '#wpadminbar #wp-admin-bar-site-name>.ab-item:before { content: "" !important; background-image: url(' . esc_url($logo_url) . ') !important; background-size: contain; background-repeat: no-repeat; background-position: center; width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 5px; }';
            }
        }
        // Admin Menu Page Icon
        if ( ! empty( $options['logo_id'] ) ) {
            $logo_url = wp_get_attachment_url( $options['logo_id'] );
            if ( $logo_url ) {
                 // This targets the main HMS menu icon.
                 $css .= '#adminmenu #toplevel_page_hms_dashboard .wp-menu-image img { content: "" !important; background-image: url(' . esc_url($logo_url) . ') !important; background-size: contain; background-repeat: no-repeat; background-position: center; width: 20px; height: 20px; }';
            }
        }

        if ( ! empty( $css ) ) {
            echo '<style type="text/css">' . $css . '</style>';
        }
    }

    /**
     * Custom login logo URL.
     */
    public function custom_login_logo_url() {
        $options = get_option( 'hms_white_label_options' );
        return ! empty( $options['portal_custom_domain'] ) ? esc_url( $options['portal_custom_domain'] ) : home_url();
    }

    /**
     * Custom login logo title.
     */
    public function custom_login_logo_title() {
        $options = get_option( 'hms_white_label_options' );
        return ! empty( $options['hospital_name'] ) ? esc_attr( $options['hospital_name'] ) : get_bloginfo( 'name' );
    }

    /**
     * Custom login stylesheet and logo.
     */
    public function custom_login_stylesheet() {
        $options = get_option( 'hms_white_label_options' );
        if ( empty( $options ) ) {
            return;
        }

        $css = '';
        if ( ! empty( $options['logo_id'] ) ) {
            $logo_url = wp_get_attachment_url( $options['logo_id'] );
            if ( $logo_url ) {
                $css .= '.login h1 a { background-image: url(' . esc_url( $logo_url ) . ') !important; background-size: contain !important; width: 320px !important; height: 80px !important; }';
            }
        }
        if ( ! empty( $options['primary_color'] ) ) {
            $css .= '.login #backtoblog a, .login #nav a { color: ' . esc_attr( $options['primary_color'] ) . ' !important; }';
        }
        if ( ! empty( $options['body_font'] ) ) {
            $css .= 'body.login { font-family: ' . esc_attr( $options['body_font'] ) . ' !important; }';
        }
        if ( ! empty( $options['background_color'] ) ) {
            $css .= 'body.login { background-color: ' . esc_attr( $options['background_color'] ) . ' !important; }';
        }

        if ( ! empty( $css ) ) {
            wp_add_inline_style( 'login', $css );
        }
    }

    /**
     * Custom email "from" name.
     */
    public function custom_email_from_name( $original_from_name ) {
        $options = get_option( 'hms_white_label_options' );
        return ! empty( $options['email_from_name'] ) ? sanitize_text_field( $options['email_from_name'] ) : $original_from_name;
    }

    /**
     * Custom email "from" address.
     */
    public function custom_email_from_address( $original_from_address ) {
        $options = get_option( 'hms_white_label_options' );
        return ! empty( $options['email_from_address'] ) && is_email( $options['email_from_address'] ) ? sanitize_email( $options['email_from_address'] ) : $original_from_address;
    }

    /**
     * Helper function to adjust color brightness.
     */
    private function adjust_brightness( $hex, $steps ) {
        $steps = max( -255, min( 255, $steps ) );
        $hex   = str_replace( '#', '', $hex );
        if ( strlen( $hex ) == 3 ) {
            $hex = str_repeat( substr( $hex, 0, 1 ), 2 )
                . str_repeat( substr( $hex, 1, 1 ), 2 )
                . str_repeat( substr( $hex, 2, 1 ), 2 );
        }
        $color_parts = str_split( $hex, 2 );
        $r          = hexdec( $color_parts[0] );
        $g          = hexdec( $color_parts[1] );
        $b          = hexdec( $color_parts[2] );
        $r = max( 0, min( 255, $r + $steps ) );
        $g = max( 0, min( 255, $g + $steps ) );
        $b = max( 0, min( 255, $b + $steps ) );
        return '#' . sprintf( '%02x%02x%02x', $r, $g, $b );
    }

    /**
     * Get a list of Google Fonts (simplified).
     */
    private function get_google_fonts() {
        // This is a simplified list. A real implementation might fetch from an API or a larger predefined list.
        return array(
            'Arial, sans-serif'                    => 'Arial',
            'Helvetica, sans-serif'               => 'Helvetica',
            'Times New Roman, serif'              => 'Times New Roman',
            'Georgia, serif'                      => 'Georgia',
            'Verdana, sans-serif'                 => 'Verdana',
            'Courier New, monospace'              => 'Courier New',
            '"Open Sans", sans-serif'             => 'Open Sans',
            '"Roboto", sans-serif'                => 'Roboto',
            '"Lato", sans-serif'                  => 'Lato',
            '"Montserrat", sans-serif'           => 'Montserrat',
            '"Poppins", sans-serif'               => 'Poppins',
            '"Source Sans Pro", sans-serif'      => 'Source Sans Pro',
            '"Oswald", sans-serif'               => 'Oswald',
            '"Raleway", sans-serif'              => 'Raleway',
            '"Merriweather", serif'              => 'Merriweather',
            '"Playfair Display", serif'          => 'Playfair Display',
        );
    }
    
    /**
     * Get default email header template.
     */
    private function get_default_email_header() {
        return '<table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" style="padding: 20px 0;">
                    <a href="{site_url}" target="_blank">
                        {logo_html}
                    </a>
                </td>
            </tr>
            <tr>
                <td bgcolor="var(--hms-primary-color, #0073aa)" align="center" style="padding: 10px; color: #ffffff; font-family: \'Helvetica\', sans-serif; font-size: 16px;">
                    <h1 style="margin: 0; color: inherit;">{hospital_name}</h1>
                </td>
            </tr>
        </table>';
    }

    /**
     * Get default email footer template.
     */
    private function get_default_email_footer() {
        return '<table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td bgcolor="#f4f4f4" align="center" style="padding: 20px; color: #666666; font-family: \'Helvetica\', sans-serif; font-size: 12px;">
                    &copy; ' . gmdate('Y') . ' {hospital_name}. All rights reserved.<br>
                    <a href="{site_url}" target="_blank" style="color: var(--hms-primary-color, #0073aa); text-decoration: none;">Visit our website</a>
                </td>
            </tr>
        </table>';
    }

    /**
     * Sanitize white label options.
     *
     * @param array $input The input options to sanitize.
     * @return array The sanitized options.
     */
    public function sanitize_white_label_options( $input ) {
        $sanitized = array();
        
        // Sanitize text fields
        if ( isset( $input['hospital_name'] ) ) {
            $sanitized['hospital_name'] = sanitize_text_field( $input['hospital_name'] );
        }
        
        // Sanitize media IDs (integers)
        if ( isset( $input['logo_id'] ) ) {
            $sanitized['logo_id'] = absint( $input['logo_id'] );
        }
        
        if ( isset( $input['favicon_id'] ) ) {
            $sanitized['favicon_id'] = absint( $input['favicon_id'] );
        }
        
        // Sanitize color fields
        if ( isset( $input['primary_color'] ) ) {
            $sanitized['primary_color'] = sanitize_hex_color( $input['primary_color'] );
        }
        
        if ( isset( $input['secondary_color'] ) ) {
            $sanitized['secondary_color'] = sanitize_hex_color( $input['secondary_color'] );
        }
        
        if ( isset( $input['background_color'] ) ) {
            $sanitized['background_color'] = sanitize_hex_color( $input['background_color'] );
        }
        
        // Sanitize font fields
        if ( isset( $input['heading_font'] ) ) {
            $sanitized['heading_font'] = sanitize_text_field( $input['heading_font'] );
        }
        
        if ( isset( $input['body_font'] ) ) {
            $sanitized['body_font'] = sanitize_text_field( $input['body_font'] );
        }
        
        // Sanitize email fields
        if ( isset( $input['email_from_name'] ) ) {
            $sanitized['email_from_name'] = sanitize_text_field( $input['email_from_name'] );
        }
        
        if ( isset( $input['email_from_address'] ) ) {
            $sanitized['email_from_address'] = sanitize_email( $input['email_from_address'] );
        }
        
        // Sanitize textarea fields
        if ( isset( $input['email_header_template'] ) ) {
            $sanitized['email_header_template'] = wp_kses_post( $input['email_header_template'] );
        }
        
        if ( isset( $input['email_footer_template'] ) ) {
            $sanitized['email_footer_template'] = wp_kses_post( $input['email_footer_template'] );
        }
        
        // Sanitize URL field
        if ( isset( $input['portal_custom_domain'] ) ) {
            $sanitized['portal_custom_domain'] = esc_url_raw( $input['portal_custom_domain'] );
        }
        
        return $sanitized;
    }
}
