<?php
/**
 * Analyzer section template for meta box.
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current post ID
$post_id = get_the_ID();
$focus_keyword = get_post_meta($post_id, '_seo_simple_focus_keyword', true);

// Get analyzer instance
$analyzer = new Seo_Simple_Analyzer();
$analysis = $analyzer->get_cached_analysis($post_id);

// If no cached analysis, try to get a fresh analysis
if (false === $analysis) {
    $fresh_analysis = $analyzer->analyze_post($post_id);
    if (!empty($fresh_analysis)) {
        // Restructure the data to match expected format
        $analysis = array(
            'scores' => array(
                'overall' => $fresh_analysis['overall_score'],
                'seo' => $fresh_analysis['seo_score'],
                'readability' => $fresh_analysis['readability_score'],
            ),
            'seo_checks' => array(),
            'readability_checks' => array(),
            'recommendations' => $fresh_analysis['recommendations'],
            'analyzed_at' => time(),
        );
        
        // Separate checks into SEO and readability
        foreach ($fresh_analysis['checks'] as $key => $check) {
            if (in_array($key, array(
                'sentence_length', 'paragraph_length', 'subheading_distribution',
                'passive_voice', 'transition_words', 'flesch_reading_ease'
            ))) {
                $analysis['readability_checks'][] = $check;
            } else {
                $analysis['seo_checks'][] = $check;
            }
        }
    }
}

// Enqueue analyzer scripts and styles
wp_enqueue_style('seo-simple-analyzer');
wp_enqueue_script('seo-simple-analyzer');

// Localize script
wp_localize_script('seo-simple-analyzer', 'seoSimpleAnalyzer', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('seo_simple_analyzer_nonce')
));
?>

<div class="seo-simple-analyzer">
    <div class="seo-simple-focus-keyword-input">
        <label for="seo-simple-focus-keyword"><?php _e('Focus Keyword', 'seo-simple'); ?></label>
        <input type="text" id="seo-simple-focus-keyword" class="seo-simple-focus-keyword" 
               name="seo_simple_focus_keyword" value="<?php echo esc_attr($focus_keyword); ?>" 
               placeholder="<?php _e('Enter your focus keyword', 'seo-simple'); ?>">
    </div>

    <div class="seo-simple-analyzer-content">
        <?php if ($analysis && !empty($analysis['scores'])) : ?>
            <div class="seo-simple-score-display">
                <div class="seo-simple-score-overall">
                    <div class="seo-simple-score-number <?php echo $analyzer->get_score_class($analysis['scores']['overall']); ?>">
                        <?php echo esc_html($analysis['scores']['overall']); ?>
                    </div>
                    <div class="seo-simple-score-label"><?php _e('Overall', 'seo-simple'); ?></div>
                </div>
                <div class="seo-simple-score-breakdown">
                    <div class="seo-simple-score-item seo-simple-score-seo">
                        <div class="seo-simple-score-title"><?php _e('SEO', 'seo-simple'); ?></div>
                        <div class="seo-simple-score-value <?php echo $analyzer->get_score_class($analysis['scores']['seo']); ?>">
                            <?php echo esc_html($analysis['scores']['seo']); ?>
                        </div>
                    </div>
                    <div class="seo-simple-score-item seo-simple-score-readability">
                        <div class="seo-simple-score-title"><?php _e('Readability', 'seo-simple'); ?></div>
                        <div class="seo-simple-score-value <?php echo $analyzer->get_score_class($analysis['scores']['readability']); ?>">
                            <?php echo esc_html($analysis['scores']['readability']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="seo-simple-analysis-tabs">
                <div class="seo-simple-tab-nav">
                    <button class="active" data-tab="seo"><?php _e('SEO', 'seo-simple'); ?></button>
                    <button data-tab="readability"><?php _e('Readability', 'seo-simple'); ?></button>
                </div>
                
                <div id="seo-content" class="seo-simple-tab-content active">
                    <div class="seo-simple-seo-checks seo-simple-checks-list">
                        <?php if (!empty($analysis['seo_checks'])) : ?>
                            <?php foreach ($analysis['seo_checks'] as $check) : ?>
                                <div class="seo-simple-check-item">
                                    <div class="seo-simple-check-icon <?php echo $analyzer->get_status_class($check['status']); ?>">
                                        <?php echo $analyzer->get_status_icon($check['status']); ?>
                                    </div>
                                    <div class="seo-simple-check-details">
                                        <div class="seo-simple-check-title"><?php echo esc_html($check['title']); ?></div>
                                        <div class="seo-simple-check-message"><?php echo esc_html($check['message']); ?></div>
                                    </div>
                                    <?php if (isset($check['value']) && is_scalar($check['value'])) : ?>
                                        <div class="seo-simple-check-value"><?php echo esc_html($check['value']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="seo-simple-empty-state"><?php _e('No SEO checks to display.', 'seo-simple'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="readability-content" class="seo-simple-tab-content">
                    <div class="seo-simple-readability-checks seo-simple-checks-list">
                        <?php if (!empty($analysis['readability_checks'])) : ?>
                            <?php foreach ($analysis['readability_checks'] as $check) : ?>
                                <div class="seo-simple-check-item">
                                    <div class="seo-simple-check-icon <?php echo $analyzer->get_status_class($check['status']); ?>">
                                        <?php echo $analyzer->get_status_icon($check['status']); ?>
                                    </div>
                                    <div class="seo-simple-check-details">
                                        <div class="seo-simple-check-title"><?php echo esc_html($check['title']); ?></div>
                                        <div class="seo-simple-check-message"><?php echo esc_html($check['message']); ?></div>
                                    </div>
                                    <?php if (isset($check['value']) && is_scalar($check['value'])) : ?>
                                        <div class="seo-simple-check-value"><?php echo esc_html($check['value']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="seo-simple-empty-state"><?php _e('No readability checks to display.', 'seo-simple'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($analysis['recommendations'])) : ?>
                <div class="seo-simple-recommendations">
                    <div class="seo-simple-recommendations-title"><?php _e('Recommendations', 'seo-simple'); ?></div>
                    <?php foreach ($analysis['recommendations'] as $recommendation) : ?>
                        <div class="seo-simple-recommendation-item <?php echo $analyzer->get_priority_class($recommendation['priority']); ?>">
                            <div class="seo-simple-recommendation-title"><?php echo esc_html($recommendation['title']); ?></div>
                            <div class="seo-simple-recommendation-message"><?php echo esc_html($recommendation['message']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($analysis['analyzed_at'])) : ?>
                <div class="seo-simple-last-analyzed">
                    <?php 
                    $date = new DateTime('@' . $analysis['analyzed_at']);
                    echo esc_html__('Last analyzed: ', 'seo-simple') . $date->format('H:i:s'); 
                    ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="seo-simple-empty-state">
                <p><?php _e('Click "Analyze Content" to get SEO and readability recommendations for this content.', 'seo-simple'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="seo-simple-analyzer-actions">
        <button type="button" class="seo-simple-analyze-btn button button-primary">
            <?php _e('Analyze Content', 'seo-simple'); ?>
        </button>
        <button type="button" class="seo-simple-clear-cache-btn button">
            <?php _e('Clear Cache', 'seo-simple'); ?>
        </button>
    </div>
</div>