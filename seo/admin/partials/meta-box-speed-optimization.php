<?php
/**
 * Speed Optimization Meta Box
 *
 * @package    SEO_Simple
 * @subpackage SEO_Simple/admin/partials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get post meta values
$seo_score = get_post_meta($post->ID, '_seo_score', true);
$focus_keyword = get_post_meta($post->ID, '_focus_keyword', true);
?>
<div class="seo-simple-speed-optimization">
    <p>
        <label for="seo_speed_score"><?php _e('Speed Score:', 'seo-simple'); ?></label>
        <input type="text" id="seo_speed_score" name="seo_speed_score" value="<?php echo esc_attr($seo_score); ?>" class="widefat" readonly />
    </p>
    <p>
        <label for="seo_focus_keyword"><?php _e('Focus Keyword:', 'seo-simple'); ?></label>
        <input type="text" id="seo_focus_keyword" name="seo_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" class="widefat" />
    </p>
    <p>
        <?php _e('Speed optimization helps improve page loading times, which is a factor in search rankings.', 'seo-simple'); ?>
    </p>
</div>