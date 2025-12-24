<?php
/**
 * Internal Links Settings Page Template
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$settings = get_option( 'seo_simple_internal_links_settings', array() );
$similarity_threshold = isset( $settings['similarity_threshold'] ) ? intval( $settings['similarity_threshold'] ) : 60;
$max_suggestions = isset( $settings['max_suggestions'] ) ? intval( $settings['max_suggestions'] ) : 5;
$auto_suggest = isset( $settings['auto_suggest'] ) ? boolval( $settings['auto_suggest'] ) : true;
$enable_dashboard_widget = isset( $settings['enable_dashboard_widget'] ) ? boolval( $settings['enable_dashboard_widget'] ) : true;
?>

<div class="wrap seo-simple-internal-links-settings">
    <h1><?php _e( 'Internal Links Settings', 'seo-simple' ); ?></h1>
    
    <p><?php _e( 'Configure the internal linking functionality to improve your site\'s SEO and user experience.', 'seo-simple' ); ?></p>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'seo_simple_internal_links_settings' ); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="seo_simple_similarity_threshold"><?php _e( 'Similarity Threshold', 'seo-simple' ); ?></label>
                </th>
                <td>
                    <input type="number" id="seo_simple_similarity_threshold" name="seo_simple_internal_links_settings[similarity_threshold]" 
                           value="<?php echo $similarity_threshold; ?>" min="1" max="100" step="1" class="small-text">
                    <p class="description"><?php _e( 'Minimum similarity score (1-100) for suggesting internal links. Higher values mean more relevant suggestions.', 'seo-simple' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="seo_simple_max_suggestions"><?php _e( 'Maximum Suggestions', 'seo-simple' ); ?></label>
                </th>
                <td>
                    <input type="number" id="seo_simple_max_suggestions" name="seo_simple_internal_links_settings[max_suggestions]" 
                           value="<?php echo $max_suggestions; ?>" min="1" max="20" step="1" class="small-text">
                    <p class="description"><?php _e( 'Maximum number of internal link suggestions to display.', 'seo-simple' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="seo_simple_auto_suggest"><?php _e( 'Auto-Suggest Links', 'seo-simple' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="seo_simple_auto_suggest" name="seo_simple_internal_links_settings[auto_suggest]" 
                           value="1" <?php checked( $auto_suggest ); ?>>
                    <label for="seo_simple_auto_suggest"><?php _e( 'Automatically suggest internal links while writing content.', 'seo-simple' ); ?></label>
                    <p class="description"><?php _e( 'When enabled, internal link suggestions will automatically update as you write.', 'seo-simple' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="seo_simple_enable_dashboard_widget"><?php _e( 'Dashboard Widget', 'seo-simple' ); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="seo_simple_enable_dashboard_widget" name="seo_simple_internal_links_settings[enable_dashboard_widget]" 
                           value="1" <?php checked( $enable_dashboard_widget ); ?>>
                    <label for="seo_simple_enable_dashboard_widget"><?php _e( 'Show internal links statistics on the dashboard.', 'seo-simple' ); ?></label>
                    <p class="description"><?php _e( 'Display a widget on the WordPress dashboard with internal linking statistics.', 'seo-simple' ); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr>
    
    <h2><?php _e( 'Internal Links Statistics', 'seo-simple' ); ?></h2>
    
    <?php
    // Get statistics
    $total_posts = wp_count_posts();
    $published_posts = $total_posts->publish;
    
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
    
    <h3><?php _e( 'Orphaned Posts', 'seo-simple' ); ?></h3>
    <p><?php _e( 'These posts have few or no internal links pointing to them. Consider adding internal links to these posts to improve their SEO.', 'seo-simple' ); ?></p>
    
    <div class="seo-simple-orphaned-posts-container">
        <button type="button" class="button seo-simple-get-orphaned-posts">
            <?php _e( 'View All Orphaned Posts', 'seo-simple' ); ?>
        </button>
        
        <div class="seo-simple-loading"></div>
        <div class="seo-simple-orphaned-posts-results"></div>
    </div>
    
    <hr>
    
    <h2><?php _e( 'Search for Posts', 'seo-simple' ); ?></h2>
    <p><?php _e( 'Search for posts by keyword to find relevant content to link to.', 'seo-simple' ); ?></p>
    
    <div class="seo-simple-search-posts-container">
        <div class="seo-simple-search-posts-form">
            <input type="text" class="seo-simple-keyword-search" placeholder="<?php _e( 'Enter keyword...', 'seo-simple' ); ?>">
            <button type="button" class="button seo-simple-search-posts-button">
                <?php _e( 'Search', 'seo-simple' ); ?>
            </button>
        </div>
        <div class="seo-simple-loading"></div>
        <div class="seo-simple-search-results"></div>
    </div>
    
    <hr>
    
    <h2><?php _e( 'Bulk Actions', 'seo-simple' ); ?></h2>
    <p><?php _e( 'Perform bulk actions on internal links across your site.', 'seo-simple' ); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field( 'seo_simple_bulk_internal_links', 'bulk_nonce' ); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'Rebuild Internal Links Data', 'seo-simple' ); ?></th>
                <td>
                    <button type="submit" name="seo_simple_rebuild_internal_links" class="button">
                        <?php _e( 'Rebuild Data', 'seo-simple' ); ?>
                    </button>
                    <p class="description"><?php _e( 'Rebuild the internal links data for all posts. This may take some time on sites with many posts.', 'seo-simple' ); ?></p>
                </td>
            </tr>
        </table>
    </form>
</div>