<?php
/**
 * Internal Links Meta Box Template
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();
?>

<div class="seo-simple-internal-links-container">
    <div class="seo-simple-internal-links-header">
        <h3 class="seo-simple-internal-links-title"><?php _e( 'Internal Link Suggestions', 'seo-simple' ); ?></h3>
        <span class="seo-simple-suggestions-count"><?php echo count( $suggestions ); ?></span>
    </div>
    
    <div class="seo-simple-internal-links-actions">
        <button type="button" class="button seo-simple-get-internal-link-suggestions" data-post-id="<?php echo $post_id; ?>">
            <?php _e( 'Get Suggestions', 'seo-simple' ); ?>
        </button>
    </div>
    
    <div class="seo-simple-loading"></div>
    
    <div class="seo-simple-internal-links-suggestions-container">
        <?php if ( ! empty( $suggestions ) ) : ?>
            <div class="seo-simple-internal-links-suggestions">
                <?php foreach ( $suggestions as $suggestion ) : ?>
                    <div class="seo-simple-internal-link-suggestion">
                        <h4>
                            <a href="<?php echo esc_url( $suggestion['url'] ); ?>" target="_blank">
                                <?php echo esc_html( $suggestion['title'] ); ?>
                            </a>
                        </h4>
                        <p><?php _e( 'Similarity:', 'seo-simple' ); ?> <?php echo $suggestion['similarity']; ?>%</p>
                        
                        <?php if ( ! empty( $suggestion['common_keywords'] ) && is_array( $suggestion['common_keywords'] ) ) : ?>
                            <?php
                            $keywords = array_filter( $suggestion['common_keywords'], 'is_string' );
                            if ( ! empty( $keywords ) ) :
                            ?>
                            <p>
                                <?php _e( 'Common keywords:', 'seo-simple' ); ?>
                                <?php echo implode( ', ', array_map( 'esc_html', $keywords ) ); ?>
                            </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $suggestion['anchor_texts'] ) ) : ?>
                            <p><?php _e( 'Suggested anchor text:', 'seo-simple' ); ?></p>
                            <ul class="seo-simple-anchor-text-suggestions">
                                <?php foreach ( $suggestion['anchor_texts'] as $anchor_text ) : ?>
                                    <li>
                                        <code><?php echo esc_html( $anchor_text ); ?></code>
                                        <button type="button" class="button button-small seo-simple-insert-link" 
                                                data-url="<?php echo esc_url( $suggestion['url'] ); ?>" 
                                                data-title="<?php echo esc_attr( $suggestion['title'] ); ?>" 
                                                data-anchor="<?php echo esc_attr( $anchor_text ); ?>">
                                            <?php _e( 'Insert Link', 'seo-simple' ); ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <button type="button" class="button seo-simple-insert-link" 
                                data-url="<?php echo esc_url( $suggestion['url'] ); ?>" 
                                data-title="<?php echo esc_attr( $suggestion['title'] ); ?>">
                            <?php _e( 'Insert Link', 'seo-simple' ); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p><?php _e( 'Click "Get Suggestions" to find internal linking opportunities.', 'seo-simple' ); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="seo-simple-search-posts-container">
        <h4><?php _e( 'Search for Posts to Link', 'seo-simple' ); ?></h4>
        <div class="seo-simple-search-posts-form">
            <input type="text" class="seo-simple-keyword-search" placeholder="<?php _e( 'Enter keyword...', 'seo-simple' ); ?>">
            <button type="button" class="button seo-simple-search-posts-button">
                <?php _e( 'Search', 'seo-simple' ); ?>
            </button>
        </div>
        <div class="seo-simple-loading"></div>
        <div class="seo-simple-search-results"></div>
    </div>
</div>