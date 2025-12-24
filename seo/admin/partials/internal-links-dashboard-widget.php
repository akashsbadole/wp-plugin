<?php
/**
 * Internal Links Dashboard Widget Template
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get some statistics
$total_posts = wp_count_posts();
$published_posts = $total_posts->publish;

// Get orphaned posts count
$orphaned_posts = $this->internal_links->get_orphaned_posts( 100 );
$orphaned_count = count( $orphaned_posts );
?>

<div class="seo-simple-dashboard-stats">
    <div class="seo-simple-dashboard-stat">
        <div class="seo-simple-dashboard-stat-number"><?php echo $published_posts; ?></div>
        <div class="seo-simple-dashboard-stat-label"><?php _e( 'Published Posts', 'seo-simple' ); ?></div>
    </div>
    <div class="seo-simple-dashboard-stat">
        <div class="seo-simple-dashboard-stat-number"><?php echo $orphaned_count; ?></div>
        <div class="seo-simple-dashboard-stat-label"><?php _e( 'Orphaned Posts', 'seo-simple' ); ?></div>
    </div>
    <div class="seo-simple-dashboard-stat">
        <div class="seo-simple-dashboard-stat-number"><?php echo $published_posts > 0 ? round( ( ( $published_posts - $orphaned_count ) / $published_posts ) * 100 ) : 0; ?>%</div>
        <div class="seo-simple-dashboard-stat-label"><?php _e( 'Linked Posts', 'seo-simple' ); ?></div>
    </div>
</div>

<?php if ( ! empty( $orphaned_posts ) ) : ?>
    <div class="seo-simple-orphaned-posts-container">
        <h4><?php _e( 'Top Orphaned Posts', 'seo-simple' ); ?></h4>
        <p><?php _e( 'These posts have few or no internal links pointing to them:', 'seo-simple' ); ?></p>
        
        <div class="seo-simple-orphaned-posts-results">
            <div class="seo-simple-orphaned-posts-list">
                <?php foreach ( array_slice( $orphaned_posts, 0, 3 ) as $post ) : ?>
                    <div class="seo-simple-orphaned-post-item">
                        <h4>
                            <a href="<?php echo esc_url( $post['url'] ); ?>" target="_blank">
                                <?php echo esc_html( $post['title'] ); ?>
                            </a>
                        </h4>
                        <p><?php _e( 'Internal links:', 'seo-simple' ); ?> <?php echo $post['link_count']; ?></p>
                        <a href="<?php echo get_edit_post_link( $post['post_id'] ); ?>" class="button">
                            <?php _e( 'Edit Post', 'seo-simple' ); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <p>
            <button type="button" class="button seo-simple-get-orphaned-posts">
                <?php _e( 'View All Orphaned Posts', 'seo-simple' ); ?>
            </button>
        </p>
        
        <div class="seo-simple-loading"></div>
        <div class="seo-simple-orphaned-posts-results" style="display: none;"></div>
    </div>
<?php else : ?>
    <p><?php _e( 'Great! No orphaned posts found on your site.', 'seo-simple' ); ?></p>
<?php endif; ?>

<p>
    <a href="<?php echo admin_url( 'admin.php?page=seo-simple-internal-links' ); ?>" class="button button-primary">
        <?php _e( 'Manage Internal Links', 'seo-simple' ); ?>
    </a>
</p>