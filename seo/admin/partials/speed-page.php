<?php
/**
 * Speed Page
 *
 * @package    SEO_Simple
 * @subpackage SEO_Simple/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get speed optimization settings
$settings = get_option('seo_simple_settings', array());
$enable_speed_optimization = isset($settings['speed_page_caching']) ? $settings['speed_page_caching'] : 0;
$minify_css = isset($settings['speed_minify_css']) ? $settings['speed_minify_css'] : 0;
$minify_js = isset($settings['speed_minify_js']) ? $settings['speed_minify_js'] : 0;
$enable_lazy_loading = isset($settings['speed_lazy_loading']) ? $settings['speed_lazy_loading'] : 0;
$cache_expiry = isset($settings['cache_expiry']) ? $settings['cache_expiry'] : 3600;
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php do_settings_sections('seo-simple-speed'); ?>
        <?php settings_fields('seo_simple_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_speed_optimization"><?php _e('Enable Speed Optimization', 'seo-simple'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_speed_optimization" name="seo_simple_settings[speed_page_caching]" value="1" <?php checked(1, $enable_speed_optimization); ?> />
                    <p class="description"><?php _e('Enable basic speed optimization features.', 'seo-simple'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="minify_css"><?php _e('Minify CSS', 'seo-simple'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="minify_css" name="seo_simple_settings[speed_minify_css]" value="1" <?php checked(1, $minify_css); ?> />
                    <p class="description"><?php _e('Minify CSS files to reduce page load time.', 'seo-simple'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="minify_js"><?php _e('Minify JavaScript', 'seo-simple'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="minify_js" name="seo_simple_settings[speed_minify_js]" value="1" <?php checked(1, $minify_js); ?> />
                    <p class="description"><?php _e('Minify JavaScript files to reduce page load time.', 'seo-simple'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="enable_lazy_loading"><?php _e('Enable Lazy Loading', 'seo-simple'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="enable_lazy_loading" name="seo_simple_settings[speed_lazy_loading]" value="1" <?php checked(1, $enable_lazy_loading); ?> />
                    <p class="description"><?php _e('Enable lazy loading for images to improve page load time.', 'seo-simple'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cache_expiry"><?php _e('Cache Expiry (seconds)', 'seo-simple'); ?></label>
                </th>
                <td>
                    <input type="number" id="cache_expiry" name="seo_simple_settings[cache_expiry]" value="<?php echo esc_attr($cache_expiry); ?>" min="60" max="86400" step="60" />
                    <p class="description"><?php _e('Set the cache expiry time in seconds (default: 3600).', 'seo-simple'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>