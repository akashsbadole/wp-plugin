<?php

/**
 * Dashboard widget for speed optimization.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="seo-simple-speed-dashboard-widget">
	<div class="seo-simple-speed-summary">
		<h3><?php esc_html_e( 'Speed Overview', 'seo-simple' ); ?></h3>
		
		<div class="seo-simple-speed-metrics">
			<div class="seo-simple-speed-metric">
				<span class="seo-simple-speed-label"><?php esc_html_e( 'Page Load Time', 'seo-simple' ); ?></span>
				<span class="seo-simple-speed-value">
					<?php if ( ! empty( $speed_test_results['page_load_time'] ) ) : ?>
						<?php echo esc_html( $speed_test_results['page_load_time'] ); ?>s
					<?php else : ?>
						<?php esc_html_e( 'Not tested', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
			
			<div class="seo-simple-speed-metric">
				<span class="seo-simple-speed-label"><?php esc_html_e( 'Page Size', 'seo-simple' ); ?></span>
				<span class="seo-simple-speed-value">
					<?php if ( ! empty( $speed_test_results['page_size'] ) ) : ?>
						<?php echo esc_html( size_format( $speed_test_results['page_size'] ) ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Not tested', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
			
			<div class="seo-simple-speed-metric">
				<span class="seo-simple-speed-label"><?php esc_html_e( 'Requests', 'seo-simple' ); ?></span>
				<span class="seo-simple-speed-value">
					<?php if ( ! empty( $speed_test_results['requests'] ) ) : ?>
						<?php echo esc_html( $speed_test_results['requests'] ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Not tested', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
		</div>
	</div>
	
	<div class="seo-simple-cache-summary">
		<h4><?php esc_html_e( 'Cache Statistics', 'seo-simple' ); ?></h4>
		
		<div class="seo-simple-cache-metrics">
			<div class="seo-simple-cache-metric">
				<span class="seo-simple-cache-label"><?php esc_html_e( 'Page Cache', 'seo-simple' ); ?></span>
				<span class="seo-simple-cache-value">
					<?php if ( isset( $cache_stats['page_cache'] ) ) : ?>
						<?php echo esc_html( $cache_stats['page_cache']['files'] ); ?> files
						(<?php echo esc_html( size_format( $cache_stats['page_cache']['size'] ) ); ?>)
					<?php else : ?>
						<?php esc_html_e( 'No data', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
			
			<div class="seo-simple-cache-metric">
				<span class="seo-simple-cache-label"><?php esc_html_e( 'CSS Cache', 'seo-simple' ); ?></span>
				<span class="seo-simple-cache-value">
					<?php if ( isset( $cache_stats['css_cache'] ) ) : ?>
						<?php echo esc_html( $cache_stats['css_cache']['files'] ); ?> files
						(<?php echo esc_html( size_format( $cache_stats['css_cache']['size'] ) ); ?>)
					<?php else : ?>
						<?php esc_html_e( 'No data', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
			
			<div class="seo-simple-cache-metric">
				<span class="seo-simple-cache-label"><?php esc_html_e( 'JS Cache', 'seo-simple' ); ?></span>
				<span class="seo-simple-cache-value">
					<?php if ( isset( $cache_stats['js_cache'] ) ) : ?>
						<?php echo esc_html( $cache_stats['js_cache']['files'] ); ?> files
						(<?php echo esc_html( size_format( $cache_stats['js_cache']['size'] ) ); ?>)
					<?php else : ?>
						<?php esc_html_e( 'No data', 'seo-simple' ); ?>
					<?php endif; ?>
				</span>
			</div>
		</div>
	</div>
	
	<div class="seo-simple-speed-actions">
		<button type="button" class="button button-primary" id="seo-simple-run-speed-test">
			<?php esc_html_e( 'Run Speed Test', 'seo-simple' ); ?>
		</button>
		
		<button type="button" class="button" id="seo-simple-clear-cache">
			<?php esc_html_e( 'Clear Cache', 'seo-simple' ); ?>
		</button>
		
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-speed' ) ); ?>" class="button">
			<?php esc_html_e( 'View Details', 'seo-simple' ); ?>
		</a>
	</div>
	
	<div class="seo-simple-speed-status">
		<?php
		// Count enabled features
		$enabled_features = 0;
		$speed_features = array(
			'speed_page_caching',
			'speed_browser_caching',
			'speed_gzip_compression',
			'speed_minify_css',
			'speed_minify_js',
			'speed_minify_html',
			'speed_lazy_loading',
			'speed_database_cleanup',
			'speed_combine_css',
			'speed_combine_js',
			'speed_defer_js',
			'speed_async_css',
			'speed_remove_query_strings',
			'speed_optimize_images',
			'speed_webp_support',
			'speed_local_google_fonts',
		);
		
		foreach ( $speed_features as $feature ) {
			if ( isset( $settings[$feature] ) && $settings[$feature] ) {
				$enabled_features++;
			}
		}
		
		$percentage = count( $speed_features ) > 0 ? round( ( $enabled_features / count( $speed_features ) ) * 100 ) : 0;
		?>
		
		<div class="seo-simple-speed-progress">
			<div class="seo-simple-speed-progress-bar">
				<div class="seo-simple-speed-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
			</div>
			<span class="seo-simple-speed-progress-text">
				<?php printf( esc_html__( '%d/%d features enabled (%d%%)', 'seo-simple' ), 
					$enabled_features, 
					count( $speed_features ), 
					$percentage 
				); ?>
			</span>
		</div>
	</div>
</div>

<style>
.seo-simple-speed-dashboard-widget {
	margin-bottom: 10px;
}

.seo-simple-speed-metrics,
.seo-simple-cache-metrics {
	display: flex;
	flex-wrap: wrap;
	margin: 10px 0;
}

.seo-simple-speed-metric,
.seo-simple-cache-metric {
	flex: 1;
	min-width: 120px;
	margin: 5px;
	padding: 10px;
	background-color: #f9f9f9;
	border-left: 4px solid #0073aa;
}

.seo-simple-speed-label,
.seo-simple-cache-label {
	display: block;
	font-size: 12px;
	color: #666;
	margin-bottom: 5px;
}

.seo-simple-speed-value,
.seo-simple-cache-value {
	display: block;
	font-size: 14px;
	font-weight: bold;
}

.seo-simple-speed-actions {
	margin: 15px 0;
}

.seo-simple-speed-actions .button {
	margin-right: 5px;
	margin-bottom: 5px;
}

.seo-simple-speed-progress {
	margin-top: 15px;
}

.seo-simple-speed-progress-bar {
	height: 10px;
	background-color: #f1f1f1;
	border-radius: 5px;
	overflow: hidden;
	margin-bottom: 5px;
}

.seo-simple-speed-progress-fill {
	height: 100%;
	background-color: #0073aa;
	transition: width 0.3s ease;
}

.seo-simple-speed-progress-text {
	font-size: 12px;
	color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('#seo-simple-run-speed-test').on('click', function(e) {
		e.preventDefault();
		var $button = $(this);
		var originalText = $button.text();
		
		$button.prop('disabled', true).text('<?php esc_html_e( 'Testing...', 'seo-simple' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_test_page_speed',
				nonce: seo_simple_speed_optimization.nonce,
				post_id: $('input#post_ID').val() || 0
			},
			success: function(response) {
				if (response.success) {
					$button.text('<?php esc_html_e( 'Test Complete', 'seo-simple' ); ?>');
					setTimeout(function() {
						$button.text(originalText).prop('disabled', false);
						location.reload();
					}, 2000);
				} else {
					$button.text(originalText).prop('disabled', false);
					alert(response.data.message || '<?php esc_html_e( 'An error occurred. Please try again.', 'seo-simple' ); ?>');
				}
			},
			error: function() {
				$button.text(originalText).prop('disabled', false);
				alert('<?php esc_html_e( 'An error occurred. Please try again.', 'seo-simple' ); ?>');
			}
		});
	});
	
	$('#seo-simple-clear-cache').on('click', function(e) {
		e.preventDefault();
		if (!confirm('<?php esc_html_e( 'Are you sure you want to clear all caches?', 'seo-simple' ); ?>')) {
			return;
		}
		
		var $button = $(this);
		var originalText = $button.text();
		
		$button.prop('disabled', true).text('<?php esc_html_e( 'Clearing...', 'seo-simple' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_clear_cache',
				nonce: seo_simple_speed_optimization.nonce
			},
			success: function(response) {
				if (response.success) {
					$button.text('<?php esc_html_e( 'Cleared!', 'seo-simple' ); ?>');
					setTimeout(function() {
						$button.text(originalText).prop('disabled', false);
						location.reload();
					}, 2000);
				} else {
					$button.text(originalText).prop('disabled', false);
					alert(response.data.message || '<?php esc_html_e( 'An error occurred. Please try again.', 'seo-simple' ); ?>');
				}
			},
			error: function() {
				$button.text(originalText).prop('disabled', false);
				alert('<?php esc_html_e( 'An error occurred. Please try again.', 'seo-simple' ); ?>');
			}
		});
	});
});
</script>