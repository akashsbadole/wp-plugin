<?php
/**
 * Meta Box Instant Indexing Template
 *
 * @since      1.0.0
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="seo-simple-instant-indexing-meta-box">
    <?php if ( $is_published ) : ?>
        <div class="seo-simple-indexing-status">
            <h4><?php _e( 'Indexing Status', 'seo-simple' ); ?></h4>
            
            <?php if ( ! empty( $indexing_status['status'] ) ) : ?>
                <?php foreach ( $indexing_status['status'] as $engine => $status ) : ?>
                    <div class="status-item <?php echo $status['success'] ? 'success' : 'error'; ?>">
                        <strong><?php echo esc_html( ucfirst( $engine ) ); ?>:</strong>
                        <?php echo esc_html( $status['message'] ); ?>
                        <br>
                        <small><?php _e( 'Submitted:', 'seo-simple' ); ?> <?php echo esc_html( $status['submitted'] ); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p><?php _e( 'Not submitted to any search engine yet.', 'seo-simple' ); ?></p>
            <?php endif; ?>
            
            <?php if ( ! empty( $last_submitted ) ) : ?>
                <p>
                    <small><?php _e( 'Last submitted:', 'seo-simple' ); ?> <?php echo esc_html( $last_submitted ); ?></small>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="seo-simple-indexing-buttons">
            <button type="button" class="button" id="seo-simple-submit-single">
                <?php _e( 'Submit to Search Engines', 'seo-simple' ); ?>
            </button>
        </div>
        
        <div class="seo-simple-indexing-engines">
            <h4><?php _e( 'Search Engines', 'seo-simple' ); ?></h4>
            <div class="engine-option">
                <label>
                    <input type="checkbox" name="engines[]" value="google" checked>
                    <?php _e( 'Google', 'seo-simple' ); ?>
                </label>
            </div>
            <div class="engine-option">
                <label>
                    <input type="checkbox" name="engines[]" value="bing" checked>
                    <?php _e( 'Bing', 'seo-simple' ); ?>
                </label>
            </div>
            <div class="engine-option">
                <label>
                    <input type="checkbox" name="engines[]" value="yandex">
                    <?php _e( 'Yandex', 'seo-simple' ); ?>
                </label>
            </div>
        </div>
        
        <div id="seo-simple-single-results" style="display: none; margin-top: 10px;"></div>
    <?php else : ?>
        <p><?php _e( 'Publish this post to enable instant indexing.', 'seo-simple' ); ?></p>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle single URL submission
    $('#seo-simple-submit-single').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var originalText = button.text();
        var url = '<?php echo esc_js( $indexing_status['url'] ); ?>';
        var postId = '<?php echo esc_js( get_the_ID() ); ?>';
        var engines = [];
        
        // Collect selected engines
        $('.seo-simple-indexing-engines input:checked').each(function() {
            engines.push($(this).val());
        });
        
        if (engines.length === 0) {
            alert('<?php _e( 'Please select at least one search engine.', 'seo-simple' ); ?>');
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('<?php _e( 'Submitting...', 'seo-simple' ); ?>');
        
        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'seo_simple_submit_indexing',
                nonce: '<?php echo wp_create_nonce( 'seo_simple_submit_indexing' ); ?>',
                url: url,
                post_id: postId,
                engines: engines
            },
            success: function(response) {
                if (response.success) {
                    // Display results
                    var resultsContainer = $('#seo-simple-single-results');
                    resultsContainer.empty();
                    
                    var resultsHtml = '<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>';
                    
                    // Add detailed results
                    if (response.data.results) {
                        resultsHtml += '<table class="wp-list-table widefat fixed striped" style="margin-top: 10px;"><thead><tr><th><?php _e( 'Search Engine', 'seo-simple' ); ?></th><th><?php _e( 'Status', 'seo-simple' ); ?></th><th><?php _e( 'Message', 'seo-simple' ); ?></th></tr></thead><tbody>';
                        
                        for (var engine in response.data.results) {
                            var result = response.data.results[engine];
                            var statusClass = result.success ? 'success' : 'error';
                            var statusText = result.success ? '<?php _e( 'Success', 'seo-simple' ); ?>' : '<?php _e( 'Failed', 'seo-simple' ); ?>';
                            
                            resultsHtml += '<tr><td>' + engine.charAt(0).toUpperCase() + engine.slice(1) + '</td><td class="' + statusClass + '">' + statusText + '</td><td>' + result.message + '</td></tr>';
                        }
                        
                        resultsHtml += '</tbody></table>';
                    }
                    
                    resultsContainer.html(resultsHtml);
                    resultsContainer.show();
                    
                    // Reload page after a short delay to update status
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php _e( 'An error occurred while submitting URL.', 'seo-simple' ); ?>');
            },
            complete: function() {
                // Restore button state
                button.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>