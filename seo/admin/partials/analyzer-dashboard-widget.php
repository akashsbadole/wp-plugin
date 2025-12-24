<?php
/**
 * Dashboard widget template for SEO Analyzer.
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get analyzer instance
$analyzer = new Seo_Simple_Analyzer();
$posts_needing_improvement = $analyzer->get_posts_needing_improvement(5);
$total_posts = wp_count_posts('publish');
$indexed_pages = isset($total_posts->publish) ? $total_posts->publish : 0;

// Get posts with missing SEO data
$posts_missing_meta = $analyzer->get_posts_missing_seo_data();
$missing_meta_count = count($posts_missing_meta);
?>

<div class="seo-simple-dashboard-widget">
    <div class="seo-simple-dashboard-summary">
        <div class="seo-simple-summary-item">
            <div class="seo-simple-summary-value"><?php echo esc_html($indexed_pages); ?></div>
            <div class="seo-simple-summary-label"><?php _e('Indexed Pages', 'seo-simple'); ?></div>
        </div>
        <div class="seo-simple-summary-item">
            <div class="seo-simple-summary-value"><?php echo esc_html($missing_meta_count); ?></div>
            <div class="seo-simple-summary-label"><?php _e('Missing Meta', 'seo-simple'); ?></div>
        </div>
    </div>

    <?php if (!empty($posts_needing_improvement)) : ?>
        <div class="seo-simple-posts-needing-improvement">
            <h4><?php _e('Top 5 Posts Needing SEO Improvement', 'seo-simple'); ?></h4>
            <div class="posts-list">
                <?php foreach ($posts_needing_improvement as $post) : ?>
                    <div class="post-item">
                        <div class="post-title">
                            <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                <?php echo esc_html($post->post_title); ?>
                            </a>
                        </div>
                        <div class="post-scores">
                            <div class="post-score">
                                <?php _e('SEO', 'seo-simple'); ?>: 
                                <span class="post-score-value <?php echo $analyzer->get_score_class($post->seo_score); ?>">
                                    <?php echo esc_html($post->seo_score); ?>
                                </span>
                            </div>
                            <div class="post-score">
                                <?php _e('Readability', 'seo-simple'); ?>: 
                                <span class="post-score-value <?php echo $analyzer->get_score_class($post->readability_score); ?>">
                                    <?php echo esc_html($post->readability_score); ?>
                                </span>
                            </div>
                            <div class="post-recommendations">
                                <?php 
                                $recommendations_count = isset($post->recommendations_count) ? $post->recommendations_count : 0;
                                if ($recommendations_count > 0) {
                                    printf(
                                        _n('%d recommendation', '%d recommendations', $recommendations_count, 'seo-simple'),
                                        $recommendations_count
                                    );
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="seo-simple-all-good">
            <p><?php _e('Great job! All your posts are well optimized.', 'seo-simple'); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($missing_meta_count > 0) : ?>
        <div class="seo-simple-missing-meta">
            <h4><?php _e('Posts Missing SEO Data', 'seo-simple'); ?></h4>
            <p>
                <?php 
                printf(
                    __('You have %d posts missing SEO meta data. <a href="%s">Bulk edit now</a>.', 'seo-simple'),
                    $missing_meta_count,
                    esc_url(admin_url('admin.php?page=seo-simple-bulk-editor'))
                );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="seo-simple-dashboard-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=seo-simple-settings')); ?>" class="button button-primary">
            <?php _e('SEO Settings', 'seo-simple'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=seo-simple-bulk-editor')); ?>" class="button">
            <?php _e('Bulk Editor', 'seo-simple'); ?>
        </a>
    </div>
</div>

<style>
.seo-simple-dashboard-widget {
    padding: 10px;
}

.seo-simple-dashboard-summary {
    display: flex;
    margin-bottom: 15px;
}

.seo-simple-summary-item {
    flex: 1;
    text-align: center;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
    margin-right: 10px;
}

.seo-simple-summary-item:last-child {
    margin-right: 0;
}

.seo-simple-summary-value {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.seo-simple-summary-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.seo-simple-posts-needing-improvement h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
}

.seo-simple-posts-needing-improvement .posts-list {
    max-height: 200px;
    overflow-y: auto;
}

.seo-simple-posts-needing-improvement .post-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.seo-simple-posts-needing-improvement .post-item:last-child {
    border-bottom: none;
}

.seo-simple-posts-needing-improvement .post-title {
    font-weight: 600;
    margin-bottom: 3px;
}

.seo-simple-posts-needing-improvement .post-title a {
    color: #333;
    text-decoration: none;
}

.seo-simple-posts-needing-improvement .post-title a:hover {
    color: #0073aa;
}

.seo-simple-posts-needing-improvement .post-scores {
    display: flex;
    align-items: center;
    font-size: 11px;
    color: #666;
}

.seo-simple-posts-needing-improvement .post-score {
    margin-right: 10px;
}

.seo-simple-posts-needing-improvement .post-score-value {
    font-weight: bold;
}

.seo-simple-posts-needing-improvement .post-recommendations {
    color: #dc3232;
}

.seo-simple-all-good {
    text-align: center;
    padding: 15px 0;
    color: #46b450;
}

.seo-simple-missing-meta {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.seo-simple-missing-meta h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
}

.seo-simple-missing-meta p {
    margin: 0;
    font-size: 12px;
}

.seo-simple-dashboard-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
}

.seo-simple-dashboard-actions .button {
    flex: 1;
    margin-right: 10px;
    text-align: center;
}

.seo-simple-dashboard-actions .button:last-child {
    margin-right: 0;
}

/* Score colors */
.seo-simple-dashboard-widget .score-good {
    color: #46b450;
}

.seo-simple-dashboard-widget .score-okay {
    color: #ffb900;
}

.seo-simple-dashboard-widget .score-warning {
    color: #ff9800;
}

.seo-simple-dashboard-widget .score-bad {
    color: #dc3232;
}

/* Responsive */
@media screen and (max-width: 782px) {
    .seo-simple-dashboard-summary {
        flex-direction: column;
    }
    
    .seo-simple-summary-item {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .seo-simple-dashboard-actions {
        flex-direction: column;
    }
    
    .seo-simple-dashboard-actions .button {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>