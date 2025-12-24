<?php
/**
 * Instant Indexing Page Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get daily stats
$daily_stats = $this->get_daily_stats();

// Get recent submissions
$recent_submissions = $this->get_recent_submissions( 10 );
?>

<div class="wrap seo-simple-instant-indexing-page">
    <h1><?php _e( 'Instant Indexing', 'seo-simple' ); ?></h1>
    
    <?php if ( empty( $api_keys_configured ) ) : ?>
        <div class="notice notice-warning">
            <p><?php _e( 'No search engine API keys are configured. Please configure them in the SEO settings to enable instant indexing.', 'seo-simple' ); ?></p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings&tab=indexing' ) ); ?>" class="button button-primary">
                    <?php _e( 'Configure API Keys', 'seo-simple' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="seo-simple-indexing-tabs">
        <button class="tab-button active" data-tab="submit-tab"><?php _e( 'Submit URLs', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="bulk-tab"><?php _e( 'Bulk Submit', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="stats-tab"><?php _e( 'Statistics', 'seo-simple' ); ?></button>
        <button class="tab-button" data-tab="history-tab"><?php _e( 'History', 'seo-simple' ); ?></button>
    </div>

    <!-- Submit URLs Tab -->
    <div id="submit-tab" class="seo-simple-indexing-tab active">
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Submit URL', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <div class="seo-simple-url-submission">
                    <div>
                        <input type="text" id="seo-simple-url" class="regular-text" placeholder="<?php esc_attr_e( 'Enter URL to submit...', 'seo-simple' ); ?>" value="<?php echo esc_url( home_url() ); ?>">
                        <button type="button" class="button button-primary" id="seo-simple-submit-url"><?php _e( 'Submit URL', 'seo-simple' ); ?></button>
                    </div>
                    
                    <div class="seo-simple-engines">
                        <h4><?php _e( 'Search Engines', 'seo-simple' ); ?></h4>
                        <?php if ( in_array( 'google', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="google" checked>
                                    <?php _e( 'Google', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( in_array( 'bing', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="bing" checked>
                                    <?php _e( 'Bing', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( in_array( 'yandex', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="yandex">
                                    <?php _e( 'Yandex', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div id="seo-simple-submission-results" class="seo-simple-submission-results"></div>
            </div>
        </div>
    </div>

    <!-- Bulk Submit Tab -->
    <div id="bulk-tab" class="seo-simple-indexing-tab">
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Bulk Submit', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <div class="seo-simple-bulk-submission">
                    <p><?php _e( 'Select posts to submit to search engines for instant indexing.', 'seo-simple' ); ?></p>
                    
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <label for="seo-simple-select-all" class="screen-reader-text"><?php _e( 'Select All', 'seo-simple' ); ?></label>
                            <input type="checkbox" id="seo-simple-select-all">
                            <label for="seo-simple-select-all"><?php _e( 'Select All', 'seo-simple' ); ?></label>
                        </div>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped" id="seo-simple-posts-table">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column"><input type="checkbox"></td>
                                <th><?php _e( 'Title', 'seo-simple' ); ?></th>
                                <th><?php _e( 'URL', 'seo-simple' ); ?></th>
                                <th><?php _e( 'Last Indexed', 'seo-simple' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $args = array(
                                'post_type' => 'post',
                                'post_status' => 'publish',
                                'posts_per_page' => 20,
                                'orderby' => 'date',
                                'order' => 'DESC'
                            );
                            
                            $posts_query = new WP_Query( $args );
                            
                            if ( $posts_query->have_posts() ) :
                                while ( $posts_query->have_posts() ) : $posts_query->the_post();
                                    $last_indexed = get_post_meta( get_the_ID(), '_seo_simple_last_indexed', true );
                                    ?>
                                    <tr>
                                        <th class="check-column">
                                            <input type="checkbox" name="post_ids[]" value="<?php echo get_the_ID(); ?>">
                                        </th>
                                        <td>
                                            <strong>
                                                <a href="<?php echo get_edit_post_link(); ?>"><?php the_title(); ?></a>
                                            </strong>
                                        </td>
                                        <td>
                                            <a href="<?php the_permalink(); ?>" target="_blank"><?php the_permalink(); ?></a>
                                        </td>
                                        <td>
                                            <?php if ( $last_indexed ) : ?>
                                                <?php echo esc_html( $last_indexed ); ?>
                                            <?php else : ?>
                                                <em><?php _e( 'Never', 'seo-simple' ); ?></em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                                ?>
                                <tr>
                                    <td colspan="4"><?php _e( 'No posts found.', 'seo-simple' ); ?></td>
                                </tr>
                                <?php
                            endif;
                            ?>
                        </tbody>
                    </table>
                    
                    <div class="seo-simple-bulk-engines">
                        <h4><?php _e( 'Search Engines', 'seo-simple' ); ?></h4>
                        <?php if ( in_array( 'google', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="google" checked>
                                    <?php _e( 'Google', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( in_array( 'bing', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="bing" checked>
                                    <?php _e( 'Bing', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( in_array( 'yandex', $api_keys_configured ) ) : ?>
                            <div class="engine-option">
                                <label>
                                    <input type="checkbox" name="engines[]" value="yandex">
                                    <?php _e( 'Yandex', 'seo-simple' ); ?>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tablenav bottom">
                        <div class="alignleft">
                            <button type="button" class="button button-primary" id="seo-simple-bulk-submit"><?php _e( 'Submit Selected', 'seo-simple' ); ?></button>
                        </div>
                    </div>
                </div>
                
                <div id="seo-simple-bulk-results" class="seo-simple-bulk-results"></div>
            </div>
        </div>
    </div>

    <!-- Statistics Tab -->
    <div id="stats-tab" class="seo-simple-indexing-tab">
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Daily Statistics', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <div class="seo-simple-stats-grid">
                    <div class="seo-simple-stat">
                        <div class="seo-simple-stat-number"><?php echo number_format_i18n( $daily_stats['submitted'] ); ?></div>
                        <div class="seo-simple-stat-label"><?php _e( 'Submitted Today', 'seo-simple' ); ?></div>
                    </div>
                    <div class="seo-simple-stat">
                        <div class="seo-simple-stat-number"><?php echo number_format_i18n( $daily_stats['remaining'] ); ?></div>
                        <div class="seo-simple-stat-label"><?php _e( 'Remaining Today', 'seo-simple' ); ?></div>
                    </div>
                    <div class="seo-simple-stat">
                        <div class="seo-simple-stat-number"><?php echo number_format_i18n( $daily_stats['limit'] ); ?></div>
                        <div class="seo-simple-stat-label"><?php _e( 'Daily Limit', 'seo-simple' ); ?></div>
                    </div>
                </div>
                
                <div class="seo-simple-progress-bar">
                    <div class="seo-simple-progress-bar-fill" style="width: <?php echo esc_attr( $daily_stats['percentage'] ); ?>%;"></div>
                </div>
                <p><?php printf( __( '%d%% of daily limit used', 'seo-simple' ), $daily_stats['percentage'] ); ?></p>
            </div>
        </div>
        
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Recent Submissions', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <?php if ( ! empty( $recent_submissions ) ) : ?>
                    <div class="seo-simple-recent-submissions">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e( 'Title', 'seo-simple' ); ?></th>
                                    <th><?php _e( 'Last Indexed', 'seo-simple' ); ?></th>
                                    <th><?php _e( 'Status', 'seo-simple' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $recent_submissions as $submission ) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url( $submission['url'] ); ?>" target="_blank"><?php echo esc_html( $submission['title'] ); ?></a>
                                        </td>
                                        <td>
                                            <?php if ( $submission['last_indexed'] ) : ?>
                                                <?php echo esc_html( $submission['last_indexed'] ); ?>
                                            <?php else : ?>
                                                <em><?php _e( 'Never', 'seo-simple' ); ?></em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ( ! empty( $submission['indexing_status'] ) ) : ?>
                                                <?php foreach ( $submission['indexing_status'] as $engine => $status ) : ?>
                                                    <span class="engine-status <?php echo $status['success'] ? 'success' : 'error'; ?>">
                                                        <?php echo esc_html( ucfirst( $engine ) ); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <em><?php _e( 'Not submitted', 'seo-simple' ); ?></em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p><?php _e( 'No recent submissions found.', 'seo-simple' ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- History Tab -->
    <div id="history-tab" class="seo-simple-indexing-tab">
        <div class="seo-simple-indexing-section">
            <h3 class="seo-simple-indexing-section-header"><?php _e( 'Submission History', 'seo-simple' ); ?></h3>
            <div class="seo-simple-indexing-section-content">
                <div class="tablenav top">
                    <div class="alignleft">
                        <button type="button" class="button" id="seo-simple-clear-history"><?php _e( 'Clear History', 'seo-simple' ); ?></button>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped" id="seo-simple-history-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Date', 'seo-simple' ); ?></th>
                            <th><?php _e( 'User', 'seo-simple' ); ?></th>
                            <th><?php _e( 'Posts', 'seo-simple' ); ?></th>
                            <th><?php _e( 'Search Engines', 'seo-simple' ); ?></th>
                            <th><?php _e( 'Success Rate', 'seo-simple' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5"><?php _e( 'Loading history...', 'seo-simple' ); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div id="seo-simple-history-pagination" class="seo-simple-history-pagination"></div>
            </div>
        </div>
    </div>
</div>