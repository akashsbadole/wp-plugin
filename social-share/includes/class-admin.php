<?php
/**
 * Admin Interface Class
 */
class ESS_Admin {
    
    /**
     * Social Media instance
     */
    private $networks;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->networks = new ESS_Social_Networks();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__('Easy Social Share', 'social-share'),
            esc_html__('Social Share', 'social-share'),
            'manage_options',
            'easy-social-share',
            array($this, 'settings_page'),
            'dashicons-share'
        );
        
        add_submenu_page(
            'easy-social-share',
            esc_html__('Settings', 'social-share'),
            esc_html__('Settings', 'social-share'),
            'manage_options',
            'easy-social-share',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'easy-social-share',
            esc_html__('Custom Networks', 'social-share'),
            esc_html__('Custom Networks', 'social-share'),
            'manage_options',
            'ess-custom-networks',
            array($this, 'custom_networks_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'easy-social-share') === false) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style('ess-admin', plugins_url('assets/css/admin.css', dirname(__FILE__)), array(), ESS_VERSION);
        wp_enqueue_script('ess-admin', plugins_url('assets/js/admin.js', dirname(__FILE__)), array('jquery', 'wp-color-picker'), ESS_VERSION, true);
    }
    
    /**
     * Register settings
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
     * Settings page
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
     * Custom networks page
     */
    public function custom_networks_page() {
        // Handle form submission
        if (isset($_POST['ess_add_network']) && check_admin_referer('ess_add_network')) {
            if (isset($_POST['network_id'])) {
                $network_id = sanitize_text_field(wp_unslash($_POST['network_id']));
            }
            if (isset($_POST['network_name'])) {
                $network_name = sanitize_text_field(wp_unslash($_POST['network_name']));
            }
            if (isset($_POST['network_icon'])) {
                $network_icon = sanitize_text_field(wp_unslash($_POST['network_icon']));
            }
            if (isset($_POST['network_color'])) {
                $network_color = sanitize_hex_color(wp_unslash($_POST['network_color']));
            }
            if (isset($_POST['network_share_url'])) {
                $network_share_url = esc_url_raw(wp_unslash($_POST['network_share_url']));
            }
            
            $network_data = array(
                'name' => $network_name,
                'icon' => $network_icon,
                'color' => $network_color,
                'share_url' => $network_share_url
            );
            
            $this->networks->add_custom_network($network_id, $network_data);
            echo '<div class="notice notice-success"><p>' . esc_html__('Network added successfully!', 'social-share') . '</p></div>';
        }
        
        // Handle network deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['network']) && check_admin_referer('delete_network')) {
            $network_id = sanitize_key($_GET['network']);
            if ($this->networks->remove_custom_network($network_id)) {
                echo '<div class="notice notice-success"><p>' . esc_html__('Network removed successfully!', 'social-share') . '</p></div>';
            }
        }
        
        // Get all networks
        $networks = $this->networks->get_networks();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Custom Social Media', 'social-share'); ?></h1>
            
            <h2><?php esc_html_e('Add New Network', 'social-share'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('ess_add_network'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="network_id"><?php esc_html_e('Network ID', 'social-share'); ?></label></th>
                        <td>
                            <input type="text" name="network_id" id="network_id" class="regular-text" required>
                            <p class="description"><?php esc_html_e('A unique identifier for the network (e.g., instagram)', 'social-share'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="network_name"><?php esc_html_e('Network Name', 'social-share'); ?></label></th>
                        <td>
                            <input type="text" name="network_name" id="network_name" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="network_icon"><?php esc_html_e('Network Icon (SVG)', 'social-share'); ?></label></th>
                        <td>
                            <textarea name="network_icon" id="network_icon" class="large-text code" rows="5" required></textarea>
                            <p class="description"><?php esc_html_e('SVG icon code for the network', 'social-share'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="network_color"><?php esc_html_e('Network Color', 'social-share'); ?></label></th>
                        <td>
                            <input type="text" name="network_color" id="network_color" class="ess-color-picker" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="network_share_url"><?php esc_html_e('Share URL', 'social-share'); ?></label></th>
                        <td>
                            <input type="url" name="network_share_url" id="network_share_url" class="regular-text" required>
                            <p class="description"><?php esc_html_e('Use {url}, {title}, and {image} as placeholders', 'social-share'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="ess_add_network" class="button button-primary" value="<?php esc_html_e('Add Network', 'social-share'); ?>">
                </p>
            </form>
            
            <h2><?php esc_html_e('Custom Networks', 'social-share'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Network', 'social-share'); ?></th>
                        <th><?php esc_html_e('Name', 'social-share'); ?></th>
                        <th><?php esc_html_e('Color', 'social-share'); ?></th>
                        <th><?php esc_html_e('Actions', 'social-share'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($networks as $network_id => $network): ?>
                        <?php if (isset($network['custom']) && $network['custom']): ?>
                            <tr>
                                <td>
                                    <div class="ess-network-icon" style="color: <?php echo esc_attr($network['color']); ?>">
                                        <?php echo esc_html($network['icon']); ?>
                                    </div>
                                </td>
                                <td><?php echo esc_html($network['name']); ?></td>
                                <td>
                                    <span class="ess-color-preview" style="background-color: <?php echo esc_attr($network['color']); ?>"></span>
                                    <?php echo esc_html($network['color']); ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ess-custom-networks&action=delete&network=' . $network_id), 'delete_network')); ?>" class="button button-small" onclick="return confirm('<?php esc_html_e('Are you sure you want to delete this network?', 'social-share'); ?>');"><?php esc_html_e('Delete', 'social-share'); ?></a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Display options callback
     */
    public function display_options_callback() {
        $settings = get_option('ess_settings', array());
        $auto_insert = isset($settings['auto_insert']) ? $settings['auto_insert'] : true;
        $position = isset($settings['position']) ? $settings['position'] : 'after';
        ?>
        <p>
            <label>
                <input type="checkbox" name="ess_settings[auto_insert]" value="1" <?php checked($auto_insert, true); ?> />
                <?php esc_html_e('Automatically insert sharing buttons on posts and pages', 'social-share'); ?>
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
        <?php
    }
    
    /**
     * Networks callback
     */
    public function networks_callback() {
        $settings = get_option('ess_settings', array());
        $networks = isset($settings['networks']) ? $settings['networks'] : array();
        $available_networks = $this->networks->get_networks();
        
        foreach ($available_networks as $network_id => $network) {
            $checked = isset($networks[$network_id]) ? $networks[$network_id] : false;
            ?>
            <label style="display: block; margin-bottom: 5px;">
                <input type="checkbox" name="ess_settings[networks][<?php echo esc_attr($network_id); ?>]" value="1" <?php checked($checked, true); ?> />
                <?php echo esc_html($network['name']); ?>
            </label>
            <?php
        }
    }
    
    /**
     * Appearance callback
     */
    public function appearance_callback() {
        $settings = get_option('ess_settings', array());
        $button_style = isset($settings['button_style']) ? $settings['button_style'] : 'icon-text';
        $button_size = isset($settings['button_size']) ? $settings['button_size'] : 'medium';
        $button_shape = isset($settings['button_shape']) ? $settings['button_shape'] : 'rounded';
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
        $networks = $this->networks->get_networks();
        foreach ($networks as $network_id => $network) {
            $sanitized['networks'][$network_id] = isset($input['networks'][$network_id]) ? true : false;
        }
        
        // Button style
        $sanitized['button_style'] = isset($input['button_style']) && in_array($input['button_style'], array('icon-only', 'icon-text', 'text-only')) ? $input['button_style'] : 'icon-text';
        
        // Button size
        $sanitized['button_size'] = isset($input['button_size']) && in_array($input['button_size'], array('small', 'medium', 'large')) ? $input['button_size'] : 'medium';
        
        // Button shape
        $sanitized['button_shape'] = isset($input['button_shape']) && in_array($input['button_shape'], array('square', 'rounded', 'circle')) ? $input['button_shape'] : 'rounded';
        
        return $sanitized;
    }
} 