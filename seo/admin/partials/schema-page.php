<?php
/**
 * Schema Markup admin page template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get schema settings
$schema_settings = get_option('seo_simple_schema_settings', array());
$default_schema_type = isset($schema_settings['default_schema_type']) ? $schema_settings['default_schema_type'] : 'article';
$enable_auto_schema = isset($schema_settings['enable_auto_schema']) ? $schema_settings['enable_auto_schema'] : 1;
$enable_faq_schema = isset($schema_settings['enable_faq_schema']) ? $schema_settings['enable_faq_schema'] : 1;
$enable_breadcrumb_schema = isset($schema_settings['enable_breadcrumb_schema']) ? $schema_settings['enable_breadcrumb_schema'] : 1;
$enable_organization_schema = isset($schema_settings['enable_organization_schema']) ? $schema_settings['enable_organization_schema'] : 1;
$organization_name = isset($schema_settings['organization_name']) ? $schema_settings['organization_name'] : get_bloginfo('name');
$organization_logo = isset($schema_settings['organization_logo']) ? $schema_settings['organization_logo'] : '';
$organization_phone = isset($schema_settings['organization_phone']) ? $schema_settings['organization_phone'] : '';
$organization_address = isset($schema_settings['organization_address']) ? $schema_settings['organization_address'] : '';
?>

<div class="wrap">
    <h1><?php _e('Schema Markup Settings', 'seo-simple'); ?></h1>
    
    <div class="seo-simple-admin-card">
        <h2><?php _e('Global Schema Settings', 'seo-simple'); ?></h2>
        <p><?php _e('Configure structured data (schema) settings for your website. Schema markup helps search engines understand your content better.', 'seo-simple'); ?></p>
        
        <form method="post" action="options.php">
            <?php settings_fields('seo_simple_schema_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="seo_simple_enable_auto_schema"><?php _e('Enable Auto Schema', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="seo_simple_enable_auto_schema" name="seo_simple_schema_settings[enable_auto_schema]" value="1" <?php checked($enable_auto_schema, 1); ?> />
                        <p class="description"><?php _e('Automatically generate schema markup for posts and pages.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_default_schema_type"><?php _e('Default Schema Type', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <select id="seo_simple_default_schema_type" name="seo_simple_schema_settings[default_schema_type]">
                            <option value="article" <?php selected($default_schema_type, 'article'); ?>><?php _e('Article', 'seo-simple'); ?></option>
                            <option value="webpage" <?php selected($default_schema_type, 'webpage'); ?>><?php _e('WebPage', 'seo-simple'); ?></option>
                            <option value="blogposting" <?php selected($default_schema_type, 'blogposting'); ?>><?php _e('BlogPosting', 'seo-simple'); ?></option>
                            <option value="product" <?php selected($default_schema_type, 'product'); ?>><?php _e('Product', 'seo-simple'); ?></option>
                        </select>
                        <p class="description"><?php _e('Default schema type for posts and pages.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_enable_faq_schema"><?php _e('Enable FAQ Schema', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="seo_simple_enable_faq_schema" name="seo_simple_schema_settings[enable_faq_schema]" value="1" <?php checked($enable_faq_schema, 1); ?> />
                        <p class="description"><?php _e('Automatically generate FAQ schema when FAQ content is detected.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_enable_breadcrumb_schema"><?php _e('Enable Breadcrumb Schema', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="seo_simple_enable_breadcrumb_schema" name="seo_simple_schema_settings[enable_breadcrumb_schema]" value="1" <?php checked($enable_breadcrumb_schema, 1); ?> />
                        <p class="description"><?php _e('Generate breadcrumb list schema for navigation.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_enable_organization_schema"><?php _e('Enable Organization Schema', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" id="seo_simple_enable_organization_schema" name="seo_simple_schema_settings[enable_organization_schema]" value="1" <?php checked($enable_organization_schema, 1); ?> />
                        <p class="description"><?php _e('Generate organization schema for your website.', 'seo-simple'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Organization Information', 'seo-simple'); ?></h3>
            <p><?php _e('Configure organization details for schema markup.', 'seo-simple'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="seo_simple_organization_name"><?php _e('Organization Name', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="seo_simple_organization_name" name="seo_simple_schema_settings[organization_name]" value="<?php echo esc_attr($organization_name); ?>" class="regular-text" />
                        <p class="description"><?php _e('The name of your organization.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_organization_logo"><?php _e('Organization Logo', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="seo_simple_organization_logo" name="seo_simple_schema_settings[organization_logo]" value="<?php echo esc_attr($organization_logo); ?>" class="regular-text" />
                        <button type="button" class="button seo-simple-upload-image" data-target="seo_simple_organization_logo"><?php _e('Upload Image', 'seo-simple'); ?></button>
                        <p class="description"><?php _e('URL to your organization logo.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_organization_phone"><?php _e('Organization Phone', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="seo_simple_organization_phone" name="seo_simple_schema_settings[organization_phone]" value="<?php echo esc_attr($organization_phone); ?>" class="regular-text" />
                        <p class="description"><?php _e('Contact phone number for your organization.', 'seo-simple'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="seo_simple_organization_address"><?php _e('Organization Address', 'seo-simple'); ?></label>
                    </th>
                    <td>
                        <textarea id="seo_simple_organization_address" name="seo_simple_schema_settings[organization_address]" rows="3" class="large-text"><?php echo esc_textarea($organization_address); ?></textarea>
                        <p class="description"><?php _e('Full address of your organization.', 'seo-simple'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Schema Settings', 'seo-simple')); ?>
        </form>
    </div>
    
    <div class="seo-simple-admin-card">
        <h2><?php _e('Schema Testing', 'seo-simple'); ?></h2>
        <p><?php _e('Test your schema markup with Google\'s Rich Results Test.', 'seo-simple'); ?></p>
        
        <div class="schema-test-tool">
            <label for="seo_schema_test_url"><?php _e('Enter URL to test:', 'seo-simple'); ?></label>
            <input type="url" id="seo_schema_test_url" class="regular-text" placeholder="<?php echo esc_url(home_url('/')); ?>" />
            <button type="button" id="seo_schema_test_button" class="button"><?php _e('Test Schema', 'seo-simple'); ?></button>
            
            <div id="seo_schema_test_result" style="margin-top: 15px;"></div>
        </div>
    </div>
    
    <div class="seo-simple-admin-card">
        <h2><?php _e('Schema Statistics', 'seo-simple'); ?></h2>
        
        <div class="schema-stats">
            <div class="stat-item">
                <h3><?php _e('Posts with Schema', 'seo-simple'); ?></h3>
                <p class="stat-number"><?php
                    global $wpdb;
                    $count = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_seo_simple_%schema%'");
                    echo number_format_i18n($count);
                ?></p>
            </div>
            
            <div class="stat-item">
                <h3><?php _e('Most Used Schema Type', 'seo-simple'); ?></h3>
                <p class="stat-number"><?php
                    $type = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_seo_simple_schema_type' GROUP BY meta_value ORDER BY COUNT(*) DESC LIMIT 1");
                    echo $type ? esc_html(ucfirst($type)) : __('None', 'seo-simple');
                ?></p>
            </div>
            
            <div class="stat-item">
                <h3><?php _e('Custom Schema Entries', 'seo-simple'); ?></h3>
                <p class="stat-number"><?php
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_seo_simple_custom_schema' AND meta_value != ''");
                    echo number_format_i18n($count);
                ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.seo-simple-admin-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-top: 20px;
    padding: 15px;
}

.schema-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-item {
    flex: 1;
    min-width: 200px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0 0;
}

.schema-test-tool {
    margin-top: 15px;
}

.schema-test-tool label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.schema-test-tool input[type="url"] {
    width: 70%;
    margin-right: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Image upload
    $('.seo-simple-upload-image').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        
        var frame = wp.media({
            title: '<?php _e('Select Image', 'seo-simple'); ?>',
            button: {
                text: '<?php _e('Use This Image', 'seo-simple'); ?>'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + target).val(attachment.url);
        });
        
        frame.open();
    });
    
    // Schema test
    $('#seo_schema_test_button').on('click', function() {
        var url = $('#seo_schema_test_url').val();
        if (!url) {
            url = $('#seo_schema_test_url').attr('placeholder');
        }
        
        $('#seo_schema_test_result').html('<div class="spinner is-active"></div>');
        
        // Open Google Rich Results Test in a new tab
        window.open('https://search.google.com/test/rich-results?url=' + encodeURIComponent(url), '_blank');
        
        $('#seo_schema_test_result').html('<p><?php _e('Google Rich Results Test opened in a new tab.', 'seo-simple'); ?></p>');
    });
});
</script>