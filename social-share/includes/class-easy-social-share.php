<?php
/**
 * Main plugin class
 */
class Easy_Social_Share_Light {
    
    /**
     * Plugin settings
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Load settings only when needed
        add_action('init', array($this, 'init'));
        
        // Register social media icons
        add_action('init', array($this, 'register_social_icons'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load settings
        $this->settings = get_option('ess_settings', $this->get_default_settings());
        
        // Add minimal actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add meta box
        add_action('add_meta_boxes', array($this, 'add_social_share_meta_box'));
        add_action('save_post', array($this, 'save_social_share_meta_box'));
        
        // Add content filter
        if (!empty($this->settings['auto_insert'])) {
            add_filter('the_content', array($this, 'add_social_buttons'));
        }
        
        // Add shortcode
        add_shortcode('easy_social_share', array($this, 'social_share_shortcode'));
    }
    
    /**
     * Register social media icons
     */
    public function register_social_icons() {
        $icon_paths = array(
            'facebook' => 'assets/images/facebook.svg',
            'twitter' => 'assets/images/twitter.svg',
            'linkedin' => 'assets/images/linkedin.svg',
            'pinterest' => 'assets/images/pinterest.svg',
            'whatsapp' => 'assets/images/whatsapp.svg'
        );

        foreach ($icon_paths as $network => $path) {
            $attachment_id = $this->get_or_create_attachment($network, $path);
            if ($attachment_id) {
                $this->settings['icon_attachments'][$network] = $attachment_id;
            }
        }
    }

    /**
     * Get or create attachment for social media icon
     */
    private function get_or_create_attachment($network, $path) {
        $full_path = ESS_PLUGIN_DIR . $path;
        if (!file_exists($full_path)) {
            return false;
        }

        // Check if attachment already exists using a more efficient query
        $existing_attachment = get_posts(array(
            'post_type' => 'attachment',
            'name' => sanitize_title($network . '-social-icon'),
            'posts_per_page' => 1,
            'post_status' => 'inherit',
            'fields' => 'ids'
        ));

        if (!empty($existing_attachment)) {
            return $existing_attachment[0];
        }

        // Create new attachment
        $file_type = wp_check_filetype(basename($full_path), null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => $network . ' Social Icon',
            'post_name' => sanitize_title($network . '-social-icon'),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $full_path);
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $full_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            return $attachment_id;
        }

        return false;
    }
    
    /**
     * Get default settings - keeping this simple
     */
    public function get_default_settings() {
        return array(
            'auto_insert' => true,
            'position' => 'after',
            'post_types' => array('post', 'page'),
            'networks' => array(
                'facebook' => true,
                'twitter' => true,
                'linkedin' => true,
                'pinterest' => false,
                'email' => true
            ),
            'custom_networks' => array(),
            'button_style' => 'icon-text',
            'button_size' => 'medium',
            'button_shape' => 'rounded'
        );
    }
    
    /**
     * Enqueue frontend assets - simplified
     */
    public function enqueue_frontend_assets() {
        // Only enqueue assets when needed
        if (is_singular() && (!empty($this->settings['auto_insert']) || has_shortcode(get_post()->post_content, 'easy_social_share'))) {
            wp_enqueue_style('easy-social-share', plugins_url('assets/css/social-share.css', dirname(__FILE__)), array(), ESS_VERSION);
            wp_enqueue_script('easy-social-share', plugins_url('assets/js/social-share.js', dirname(__FILE__)), array('jquery'), ESS_VERSION, true);
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            esc_html__('Easy Social Share Settings', 'social-share'),
            esc_html__('Easy Social Share', 'social-share'),
            'manage_options',
            'easy-social-share',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings - simplified
     */
    public function register_settings() {
        register_setting('ess_settings_group', 'ess_settings', array($this, 'sanitize_settings'));
        
        // General section
        add_settings_section(
            'ess_general_section',
            esc_html__('Settings', 'social-share'),
            '__return_false',
            'easy-social-share'
        );
        
        // Display options
        add_settings_field(
            'ess_display_options',
            esc_html__('Display Options', 'social-share'),
            array($this, 'display_options_callback'),
            'easy-social-share',
            'ess_general_section'
        );
        
        // Networks
        add_settings_field(
            'ess_networks',
            esc_html__('Social Media', 'social-share'),
            array($this, 'networks_callback'),
            'easy-social-share',
            'ess_general_section'
        );

        // Custom Networks
        add_settings_field(
            'ess_custom_networks',
            esc_html__('Custom Social Media', 'social-share'),
            array($this, 'custom_networks_callback'),
            'easy-social-share',
            'ess_general_section'
        );
        
        // Appearance
        add_settings_field(
            'ess_appearance',
            esc_html__('Appearance', 'social-share'),
            array($this, 'appearance_callback'),
            'easy-social-share',
            'ess_general_section'
        );
    }
    
    /**
     * Display options callback
     */
    public function display_options_callback() {
        $auto_insert = isset($this->settings['auto_insert']) ? $this->settings['auto_insert'] : true;
        $position = isset($this->settings['position']) ? $this->settings['position'] : 'after';
        $post_types = isset($this->settings['post_types']) ? $this->settings['post_types'] : array('post', 'page');
        ?>
        <p>
            <label>
                <input type="checkbox" name="ess_settings[auto_insert]" value="1" <?php checked($auto_insert, true); ?> />
                <?php esc_html_e('Automatically insert sharing buttons', 'social-share'); ?>
            </label>
        </p>
        <p>
            <label><?php esc_html_e('Position:', 'social-share'); ?></label>
            <select name="ess_settings[position]">
                <option value="before" <?php selected($position, 'before'); ?>><?php esc_html_e('Before content', 'social-share'); ?></option>
                <option value="after" <?php selected($position, 'after'); ?>><?php esc_html_e('After content', 'social-share'); ?></option>
                <option value="both" <?php selected($position, 'both'); ?>><?php esc_html_e('Before and after content', 'social-share'); ?></option>
            </select>
        </p>
        <p>
            <label><?php esc_html_e('Post Types:', 'social-share'); ?></label><br>
            <?php
            $available_post_types = get_post_types(array('public' => true), 'objects');
            foreach ($available_post_types as $post_type) {
                ?>
                <label style="display: block; margin: 5px 0;">
                    <input type="checkbox" name="ess_settings[post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $post_types, true), true); ?> />
                    <?php echo esc_html($post_type->label); ?>
                </label>
                <?php
            }
            ?>
        </p>
        <?php
    }
    
    /**
     * Networks callback
     */
    public function networks_callback() {
        $networks = isset($this->settings['networks']) ? $this->settings['networks'] : array();
        $available_networks = array(
            'facebook' => esc_html__('Facebook', 'social-share'),
            'twitter' => esc_html__('Twitter', 'social-share'),
            'linkedin' => esc_html__('LinkedIn', 'social-share'),
            'pinterest' => esc_html__('Pinterest', 'social-share'),
            'email' => esc_html__('Email', 'social-share')
        );
        
        foreach ($available_networks as $network => $label) {
            ?>
            <label style="display: block; margin-bottom: 5px;">
                <input type="checkbox" name="ess_settings[networks][<?php echo esc_attr($network); ?>]" value="1" <?php checked(isset($networks[$network]) && $networks[$network], true); ?> />
                <?php echo esc_html($label); ?>
            </label>
            <?php
        }
    }
    
    /**
     * Custom Networks callback
     */
    public function custom_networks_callback() {
        $custom_networks = isset($this->settings['custom_networks']) ? $this->settings['custom_networks'] : array();
        ?>
        <div id="custom-networks-container">
            <?php
            if (!empty($custom_networks)) {
                foreach ($custom_networks as $index => $network) {
                    $this->render_custom_network_fields($index, $network);
                }
            }
            ?>
        </div>
        <button type="button" class="button" id="add-custom-network"><?php esc_html_e('Add Custom Social Media Url', 'social-share'); ?></button>
        <script>
            jQuery(document).ready(function($) {
                var networkIndex = <?php echo absint(count($custom_networks)); ?>;
                
                $('#add-custom-network').on('click', function() {
                    var template = wp.template('custom-network-item')({
                        index: networkIndex,
                        nameLabel: <?php echo wp_json_encode(esc_html__('Network Name:', 'social-share')); ?>,
                        iconLabel: <?php echo wp_json_encode(esc_html__('Icon URL:', 'social-share')); ?>,
                        shareUrlLabel: <?php echo wp_json_encode(esc_html__('Share URL (use {url} for post URL):', 'social-share')); ?>,
                        removeLabel: <?php echo wp_json_encode(esc_html__('Remove', 'social-share')); ?>
                    });
                    $('#custom-networks-container').append(template);
                    networkIndex++;
                });

                $(document).on('click', '.remove-network', function() {
                    $(this).closest('.custom-network-item').remove();
                });
            });
        </script>
        <script type="text/template" id="tmpl-custom-network-item">
            <div class="custom-network-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
                <p>
                    <label>{{data.nameLabel}}</label>
                    <input type="text" name="ess_settings[custom_networks][{{data.index}}][name]" required />
                </p>
                <p>
                    <label>{{data.iconLabel}}</label>
                    <input type="url" name="ess_settings[custom_networks][{{data.index}}][icon]" required />
                </p>
                <p>
                    <label>{{data.shareUrlLabel}}</label>
                    <input type="url" name="ess_settings[custom_networks][{{data.index}}][share_url]" required />
                </p>
                <button type="button" class="button remove-network">{{data.removeLabel}}</button>
            </div>
        </script>
        <?php
    }

    /**
     * Render custom network fields
     */
    private function render_custom_network_fields($index, $network) {
        ?>
        <div class="custom-network-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
            <p>
                <label><?php esc_html_e('Network Name:', 'social-share'); ?></label>
                <input type="text" name="ess_settings[custom_networks][<?php echo esc_attr($index); ?>][name]" value="<?php echo esc_attr($network['name']); ?>" required />
            </p>
            <p>
                <label><?php esc_html_e('Icon URL:', 'social-share'); ?></label>
                <input type="url" name="ess_settings[custom_networks][<?php echo esc_attr($index); ?>][icon]" value="<?php echo esc_url($network['icon']); ?>" required />
            </p>
            <p>
                <label><?php esc_html_e('Share URL (use {url} for post URL):', 'social-share'); ?></label>
                <input type="url" name="ess_settings[custom_networks][<?php echo esc_attr($index); ?>][share_url]" value="<?php echo esc_url($network['share_url']); ?>" required />
            </p>
            <button type="button" class="button remove-network"><?php esc_html_e('Remove', 'social-share'); ?></button>
        </div>
        <?php
    }
    
    /**
     * Appearance callback
     */
    public function appearance_callback() {
        $button_style = isset($this->settings['button_style']) ? $this->settings['button_style'] : 'icon-text';
        $button_size = isset($this->settings['button_size']) ? $this->settings['button_size'] : 'medium';
        $button_shape = isset($this->settings['button_shape']) ? $this->settings['button_shape'] : 'rounded';
        ?>
        <p>
            <label><?php esc_html_e('Button Style:', 'social-share'); ?></label>
            <select name="ess_settings[button_style]">
                <option value="icon-only" <?php selected($button_style, 'icon-only'); ?>><?php esc_html_e('Icon Only', 'social-share'); ?></option>
                <option value="icon-text" <?php selected($button_style, 'icon-text'); ?>><?php esc_html_e('Icon and Text', 'social-share'); ?></option>
                <option value="text-only" <?php selected($button_style, 'text-only'); ?>><?php esc_html_e('Text Only', 'social-share'); ?></option>
            </select>
        </p>
        <p>
            <label><?php esc_html_e('Button Size:', 'social-share'); ?></label>
            <select name="ess_settings[button_size]">
                <option value="small" <?php selected($button_size, 'small'); ?>><?php esc_html_e('Small', 'social-share'); ?></option>
                <option value="medium" <?php selected($button_size, 'medium'); ?>><?php esc_html_e('Medium', 'social-share'); ?></option>
                <option value="large" <?php selected($button_size, 'large'); ?>><?php esc_html_e('Large', 'social-share'); ?></option>
            </select>
        </p>
        <p>
            <label><?php esc_html_e('Button Shape:', 'social-share'); ?></label>
            <select name="ess_settings[button_shape]">
                <option value="square" <?php selected($button_shape, 'square'); ?>><?php esc_html_e('Square', 'social-share'); ?></option>
                <option value="rounded" <?php selected($button_shape, 'rounded'); ?>><?php esc_html_e('Rounded', 'social-share'); ?></option>
                <option value="circle" <?php selected($button_shape, 'circle'); ?>><?php esc_html_e('Circle', 'social-share'); ?></option>
            </select>
        </p>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Auto insert
        $sanitized['auto_insert'] = isset($input['auto_insert']) ? true : false;
        
        // Position
        $sanitized['position'] = isset($input['position']) && in_array($input['position'], array('before', 'after', 'both')) ? $input['position'] : 'after';
        
        // Networks
        $networks = array(
            'facebook', 'twitter', 'linkedin', 'pinterest', 'email'
        );
        
        foreach ($networks as $network) {
            $sanitized['networks'][$network] = isset($input['networks'][$network]) ? true : false;
        }

        // Custom Networks
        $sanitized['custom_networks'] = array();
        if (!empty($input['custom_networks'])) {
            foreach ($input['custom_networks'] as $network) {
                if (!empty($network['name']) && !empty($network['icon']) && !empty($network['share_url'])) {
                    $sanitized['custom_networks'][] = array(
                        'name' => sanitize_text_field($network['name']),
                        'icon' => esc_url_raw($network['icon']),
                        'share_url' => esc_url_raw($network['share_url'])
                    );
                }
            }
        }
        
        // Button style
        $sanitized['button_style'] = isset($input['button_style']) && in_array($input['button_style'], array('icon-only', 'icon-text', 'text-only')) ? $input['button_style'] : 'icon-text';
        
        // Button size
        $sanitized['button_size'] = isset($input['button_size']) && in_array($input['button_size'], array('small', 'medium', 'large')) ? $input['button_size'] : 'medium';
        
        // Button shape
        $sanitized['button_shape'] = isset($input['button_shape']) && in_array($input['button_shape'], array('square', 'rounded', 'circle')) ? $input['button_shape'] : 'rounded';
        
        return $sanitized;
    }
    
    /**
     * Settings page - simplified
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('ess_settings_group');
                do_settings_sections('easy-social-share');
                submit_button();
                ?>
            </form>
            <div class="ess-info">
                <h2><?php esc_html_e('Shortcode Usage', 'social-share'); ?></h2>
                <p><?php esc_html_e('Use the following shortcode to display social sharing buttons anywhere in your content:', 'social-share'); ?></p>
                <code>[easy_social_share]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Generate social share buttons HTML - simplified
     */
    public function get_social_buttons($custom_args = array()) {
        $args = wp_parse_args($custom_args, array(
            'post_id' => get_the_ID(),
            'post_title' => get_the_title(),
            'post_url' => get_permalink(),
            'button_style' => $this->settings['button_style'],
            'button_size' => $this->settings['button_size'],
            'button_shape' => $this->settings['button_shape']
        ));

        $networks = isset($this->settings['networks']) ? $this->settings['networks'] : array();
        $custom_networks = isset($this->settings['custom_networks']) ? $this->settings['custom_networks'] : array();
        
        $buttons = array();
        
        // Standard networks
        if (!empty($networks['facebook'])) {
            $buttons[] = sprintf(
                '<a href="https://www.facebook.com/sharer/sharer.php?u=%s" class="ess-button ess-facebook ess-%s ess-%s ess-%s" target="_blank" rel="noopener noreferrer">%s</a>',
                urlencode($args['post_url']),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_button_content('facebook', $args['button_style'])
            );
        }
        
        if (!empty($networks['twitter'])) {
            $buttons[] = sprintf(
                '<a href="https://twitter.com/intent/tweet?url=%s&text=%s" class="ess-button ess-twitter ess-%s ess-%s ess-%s" target="_blank" rel="noopener noreferrer">%s</a>',
                urlencode($args['post_url']),
                urlencode($args['post_title']),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_button_content('twitter', $args['button_style'])
            );
        }
        
        if (!empty($networks['linkedin'])) {
            $buttons[] = sprintf(
                '<a href="https://www.linkedin.com/shareArticle?mini=true&url=%s&title=%s" class="ess-button ess-linkedin ess-%s ess-%s ess-%s" target="_blank" rel="noopener noreferrer">%s</a>',
                urlencode($args['post_url']),
                urlencode($args['post_title']),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_button_content('linkedin', $args['button_style'])
            );
        }
        
        if (!empty($networks['pinterest'])) {
            $buttons[] = sprintf(
                '<a href="https://pinterest.com/pin/create/button/?url=%s&description=%s" class="ess-button ess-pinterest ess-%s ess-%s ess-%s" target="_blank" rel="noopener noreferrer">%s</a>',
                urlencode($args['post_url']),
                urlencode($args['post_title']),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_button_content('pinterest', $args['button_style'])
            );
        }
        
        if (!empty($networks['email'])) {
            $buttons[] = sprintf(
                '<a href="mailto:?subject=%s&body=%s" class="ess-button ess-email ess-%s ess-%s ess-%s">%s</a>',
                urlencode($args['post_title']),
                urlencode($args['post_url']),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_button_content('email', $args['button_style'])
            );
        }

        // Custom networks
        foreach ($custom_networks as $network) {
            $share_url = str_replace('{url}', urlencode($args['post_url']), $network['share_url']);
            $buttons[] = sprintf(
                '<a href="%s" class="ess-button ess-custom ess-%s ess-%s ess-%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($share_url),
                $args['button_style'],
                $args['button_size'],
                $args['button_shape'],
                $this->get_custom_button_content($network, $args['button_style'])
            );
        }
        
        return '<div class="ess-buttons">' . implode('', $buttons) . '</div>';
    }

    /**
     * Get custom button content
     */
    private function get_custom_button_content($network, $style) {
        $icon = sprintf('<img src="%s" alt="%s" class="ess-icon" />', esc_url($network['icon']), esc_attr($network['name']));
        
        switch ($style) {
            case 'icon-only':
                return $icon;
            case 'text-only':
                return esc_html($network['name']);
            default:
                return $icon . '<span class="ess-text">' . esc_html($network['name']) . '</span>';
        }
    }
    
    /**
     * Add social buttons to content
     */
    public function add_social_buttons($content) {
        if (!is_singular()) {
            return $content;
        }

        $post_id = get_the_ID();
        $show_social_share = get_post_meta($post_id, '_show_social_share', true);
        
        // Check if social share is explicitly disabled for this post
        if ($show_social_share === 'hide') {
            return $content;
        }
        
        // Check if social share is explicitly enabled or using default settings
        if ($show_social_share === 'show' || ($show_social_share === 'default' && !empty($this->settings['auto_insert']))) {
            $buttons = $this->get_social_buttons();
            
            if ($this->settings['position'] === 'before' || $this->settings['position'] === 'both') {
                $content = $buttons . $content;
            }
            
            if ($this->settings['position'] === 'after' || $this->settings['position'] === 'both') {
                $content = $content . $buttons;
            }
        }
        
        return $content;
    }
    
    /**
     * Social share shortcode
     */
    public function social_share_shortcode($atts) {
        $atts = shortcode_atts(array(
            'networks' => '',
            'style' => '',
            'size' => '',
            'shape' => ''
        ), $atts, 'easy_social_share');
        
        $custom_args = array();
        
        // Parse networks
        if (!empty($atts['networks'])) {
            $networks_list = explode(',', $atts['networks']);
            $custom_args['networks'] = array();
            
            foreach ($networks_list as $network) {
                $network = trim($network);
                $custom_args['networks'][$network] = true;
            }
        }
        
        // Other attributes
        if (!empty($atts['style'])) {
            $custom_args['style'] = $atts['style'];
        }
        
        if (!empty($atts['size'])) {
            $custom_args['size'] = $atts['size'];
        }
        
        if (!empty($atts['shape'])) {
            $custom_args['shape'] = $atts['shape'];
        }
        
        return $this->get_social_buttons($custom_args);
    }

    /**
     * Get button content based on style
     */
    private function get_button_content($network, $style) {
        $labels = array(
            'facebook' => esc_html__('Facebook', 'social-share'),
            'twitter' => esc_html__('Twitter', 'social-share'),
            'linkedin' => esc_html__('LinkedIn', 'social-share'),
            'pinterest' => esc_html__('Pinterest', 'social-share'),
            'whatsapp' => esc_html__('WhatsApp', 'social-share')
        );

        $icon = '';
        if (isset($this->settings['icon_attachments'][$network])) {
            $attachment_id = $this->settings['icon_attachments'][$network];
            $icon = wp_get_attachment_image($attachment_id, array(24, 24), false, array(
                'class' => 'ess-icon',
                'alt' => $labels[$network]
            ));
        }

        $label = isset($labels[$network]) ? $labels[$network] : '';

        switch ($style) {
            case 'icon-only':
                return $icon;
            case 'text-only':
                return esc_html($label);
            default:
                return $icon . '<span class="ess-text">' . esc_html($label) . '</span>';
        }
    }

    /**
     * Add meta box to posts and pages
     */
    public function add_social_share_meta_box() {
        $post_types = isset($this->settings['post_types']) ? $this->settings['post_types'] : array('post', 'page');
        foreach ($post_types as $post_type) {
            add_meta_box(
                'social_share_meta_box',
                esc_html__('Social Share Settings', 'social-share'),
                array($this, 'render_social_share_meta_box'),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render meta box content
     */
    public function render_social_share_meta_box($post) {
        wp_nonce_field('social_share_meta_box', 'social_share_meta_box_nonce');
        $show_social_share = get_post_meta($post->ID, '_show_social_share', true);
        if ($show_social_share === '') {
            $show_social_share = 'default';
        }
        ?>
        <p>
            <label><?php esc_html_e('Show Social Share Buttons:', 'social-share'); ?></label><br>
            <select name="show_social_share" style="width: 100%;">
                <option value="default" <?php selected($show_social_share, 'default'); ?>><?php esc_html_e('Use Default Settings', 'social-share'); ?></option>
                <option value="show" <?php selected($show_social_share, 'show'); ?>><?php esc_html_e('Show', 'social-share'); ?></option>
                <option value="hide" <?php selected($show_social_share, 'hide'); ?>><?php esc_html_e('Hide', 'social-share'); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Save meta box data
     */
    public function save_social_share_meta_box($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['social_share_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['social_share_meta_box_nonce'])), 'social_share_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and save the data
        if (isset($_POST['show_social_share'])) {
            $show_social_share = sanitize_text_field(wp_unslash($_POST['show_social_share']));
            if (in_array($show_social_share, array('default', 'show', 'hide'), true)) {
                update_post_meta($post_id, '_show_social_share', $show_social_share);
            }
        }
    }
} 