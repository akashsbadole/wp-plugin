<?php
/**
 * Auto configuration page template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap seo-simple-auto-config">
	<h1><?php esc_html_e( 'SEO Auto Configuration', 'seo-simple' ); ?></h1>
	
	<?php if ( $config_status === 'success' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Auto configuration has been successfully applied to your SEO settings.', 'seo-simple' ); ?></p>
		</div>
	<?php elseif ( $config_status === 'error' ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'An error occurred while applying auto configuration. Please try again.', 'seo-simple' ); ?></p>
		</div>
	<?php endif; ?>
	
	<div class="seo-simple-card">
		<h2><?php esc_html_e( 'Site Analysis', 'seo-simple' ); ?></h2>
		<p><?php esc_html_e( 'We have analyzed your site to determine the optimal SEO configuration. Here\'s what we found:', 'seo-simple' ); ?></p>
		
		<div class="site-analysis-grid">
			<div class="analysis-item">
				<h3><?php esc_html_e( 'Site Type', 'seo-simple' ); ?></h3>
				<p class="analysis-value"><?php echo esc_html( ucfirst( $site_analysis['site_type'] ) ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h3><?php esc_html_e( 'Site Size', 'seo-simple' ); ?></h3>
				<p class="analysis-value"><?php echo esc_html( ucfirst( $site_analysis['site_size'] ) ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h3><?php esc_html_e( 'Theme', 'seo-simple' ); ?></h3>
				<p class="analysis-value"><?php echo esc_html( $site_analysis['theme'] ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h3><?php esc_html_e( 'Multisite', 'seo-simple' ); ?></h3>
				<p class="analysis-value"><?php echo $site_analysis['is_multisite'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h3><?php esc_html_e( 'WooCommerce', 'seo-simple' ); ?></h3>
				<p class="analysis-value"><?php echo $site_analysis['has_woocommerce'] ? esc_html__( 'Active', 'seo-simple' ) : esc_html__( 'Not Active', 'seo-simple' ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h3><?php esc_html_e( 'Page Builders', 'seo-simple' ); ?></h3>
				<p class="analysis-value">
					<?php 
					if ( empty( $site_analysis['page_builders'] ) ) {
						esc_html_e( 'None detected', 'seo-simple' );
					} else {
						echo esc_html( implode( ', ', $site_analysis['page_builders'] ) );
					}
					?>
				</p>
			</div>
		</div>
		
		<h3><?php esc_html_e( 'Content Analysis', 'seo-simple' ); ?></h3>
		<div class="content-analysis-grid">
			<div class="analysis-item">
				<h4><?php esc_html_e( 'Average Content Length', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo esc_html( number_format( $site_analysis['content_analysis']['avg_content_length'] ) ); ?> <?php esc_html_e( 'characters', 'seo-simple' ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h4><?php esc_html_e( 'Image Usage', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo esc_html( round( $site_analysis['content_analysis']['image_usage_ratio'] * 100, 1 ) ); ?>%</p>
			</div>
			
			<div class="analysis-item">
				<h4><?php esc_html_e( 'Heading Usage', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo esc_html( round( $site_analysis['content_analysis']['heading_usage_ratio'] * 100, 1 ) ); ?>%</p>
			</div>
		</div>
		
		<h3><?php esc_html_e( 'Performance Analysis', 'seo-simple' ); ?></h3>
		<div class="performance-analysis-grid">
			<div class="analysis-item">
				<h4><?php esc_html_e( 'Caching Plugin', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo $site_analysis['performance']['has_caching'] ? esc_html__( 'Detected', 'seo-simple' ) : esc_html__( 'Not Detected', 'seo-simple' ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h4><?php esc_html_e( 'Image Optimization', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo $site_analysis['performance']['has_image_optimization'] ? esc_html__( 'Detected', 'seo-simple' ) : esc_html__( 'Not Detected', 'seo-simple' ); ?></p>
			</div>
			
			<div class="analysis-item">
				<h4><?php esc_html_e( 'CDN', 'seo-simple' ); ?></h4>
				<p class="analysis-value"><?php echo $site_analysis['performance']['has_cdn'] ? esc_html__( 'Detected', 'seo-simple' ) : esc_html__( 'Not Detected', 'seo-simple' ); ?></p>
			</div>
		</div>
	</div>
	
	<div class="seo-simple-card">
		<h2><?php esc_html_e( 'Recommended Settings', 'seo-simple' ); ?></h2>
		<p><?php esc_html_e( 'Based on your site analysis, we recommend the following SEO settings:', 'seo-simple' ); ?></p>
		
		<div class="recommended-settings">
			<h3><?php esc_html_e( 'General Settings', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Enable tracking:', 'seo-simple' ); ?> <?php echo $recommended_settings['general']['enable_tracking'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable advanced analysis:', 'seo-simple' ); ?> <?php echo $recommended_settings['general']['enable_advanced_analysis'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Cache interval:', 'seo-simple' ); ?> <?php echo esc_html( $recommended_settings['general']['cache_interval'] ); ?></li>
				<li><?php esc_html_e( 'Post types to include:', 'seo-simple' ); ?> <?php echo esc_html( implode( ', ', $recommended_settings['general']['post_types'] ) ); ?></li>
			</ul>
			
			<h3><?php esc_html_e( 'Title & Meta Settings', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Title template:', 'seo-simple' ); ?> <code><?php echo esc_html( $recommended_settings['titles_meta']['title_template'] ); ?></code></li>
				<li><?php esc_html_e( 'Description template:', 'seo-simple' ); ?> <code><?php echo esc_html( $recommended_settings['titles_meta']['description_template'] ); ?></code></li>
				<li><?php esc_html_e( 'Meta description length:', 'seo-simple' ); ?> <?php echo esc_html( $recommended_settings['titles_meta']['meta_description_length'] ); ?> <?php esc_html_e( 'characters', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable social meta:', 'seo-simple' ); ?> <?php echo $recommended_settings['titles_meta']['enable_social_meta'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
			</ul>
			
			<h3><?php esc_html_e( 'Social Settings', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Enable Open Graph:', 'seo-simple' ); ?> <?php echo $recommended_settings['social']['enable_og'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable Twitter Cards:', 'seo-simple' ); ?> <?php echo $recommended_settings['social']['enable_twitter'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
			</ul>
			
			<h3><?php esc_html_e( 'Sitemap Settings', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Enable sitemap:', 'seo-simple' ); ?> <?php echo $recommended_settings['sitemap']['enable_sitemap'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Update frequency:', 'seo-simple' ); ?> <?php echo esc_html( $recommended_settings['sitemap']['update_frequency'] ); ?></li>
				<li><?php esc_html_e( 'Include post types:', 'seo-simple' ); ?> <?php echo esc_html( implode( ', ', $recommended_settings['sitemap']['include_post_types'] ) ); ?></li>
				<li><?php esc_html_e( 'Include taxonomies:', 'seo-simple' ); ?> <?php echo esc_html( implode( ', ', $recommended_settings['sitemap']['include_taxonomies'] ) ); ?></li>
			</ul>
			
			<h3><?php esc_html_e( 'Advanced Settings', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Enable caching:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_caching'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable image optimization:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_image_optimization'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable minification:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_minification'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable lazy loading:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_lazy_loading'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable GZIP:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_gzip'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable page builder integration:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_page_builder_integration'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
				<li><?php esc_html_e( 'Enable advanced security:', 'seo-simple' ); ?> <?php echo $recommended_settings['advanced']['enable_advanced_security'] ? esc_html__( 'Yes', 'seo-simple' ) : esc_html__( 'No', 'seo-simple' ); ?></li>
			</ul>
		</div>
	</div>
	
	<div class="seo-simple-card">
		<h2><?php esc_html_e( 'Apply Auto Configuration', 'seo-simple' ); ?></h2>
		<p><?php esc_html_e( 'Click the button below to apply the recommended settings to your site. This will update your current SEO settings with the optimal configuration based on your site analysis.', 'seo-simple' ); ?></p>
		<p><strong><?php esc_html_e( 'Note:', 'seo-simple' ); ?></strong> <?php esc_html_e( 'This will merge with your existing settings, not replace them entirely.', 'seo-simple' ); ?></p>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'seo_simple_auto_config', 'seo_simple_auto_config_nonce' ); ?>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Apply Auto Configuration', 'seo-simple' ); ?>">
			<button type="button" id="ajax-apply-config" class="button button-secondary"><?php esc_html_e( 'Apply via AJAX', 'seo-simple' ); ?></button>
		</form>
		
		<div id="config-progress" class="hidden">
			<div class="progress-bar">
				<div class="progress-bar-fill"></div>
			</div>
			<p><?php esc_html_e( 'Analyzing site and configuring settings...', 'seo-simple' ); ?></p>
		</div>
	</div>
</div>

<style>
.seo-simple-auto-config .seo-simple-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
	margin-top: 20px;
	padding: 15px;
}

.seo-simple-auto-config .site-analysis-grid,
.seo-simple-auto-config .content-analysis-grid,
.seo-simple-auto-config .performance-analysis-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 15px;
	margin: 15px 0;
}

.seo-simple-auto-config .analysis-item {
	background: #f9f9f9;
	border: 1px solid #e5e5e5;
	padding: 15px;
	border-radius: 4px;
}

.seo-simple-auto-config .analysis-item h3,
.seo-simple-auto-config .analysis-item h4 {
	margin: 0 0 5px 0;
	font-size: 14px;
}

.seo-simple-auto-config .analysis-item h3 {
	font-size: 16px;
}

.seo-simple-auto-config .analysis-value {
	font-weight: bold;
	color: #0073aa;
	margin: 0;
}

.seo-simple-auto-config .recommended-settings ul {
	list-style: disc;
	margin-left: 20px;
}

.seo-simple-auto-config .recommended-settings li {
	margin-bottom: 5px;
}

.seo-simple-auto-config .progress-bar {
	width: 100%;
	height: 20px;
	background-color: #f1f1f1;
	border-radius: 10px;
	margin: 15px 0;
}

.seo-simple-auto-config .progress-bar-fill {
	height: 100%;
	background-color: #0073aa;
	border-radius: 10px;
	width: 0%;
	transition: width 0.3s ease;
}

.seo-simple-auto-config .hidden {
	display: none;
}

.seo-simple-auto-config code {
	background: #f1f1f1;
	padding: 2px 4px;
	border-radius: 3px;
	font-family: monospace;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('#ajax-apply-config').on('click', function(e) {
		e.preventDefault();
		
		if (confirm(seo_simple_auto_config_admin.strings.confirm_run)) {
			$('#config-progress').removeClass('hidden');
			$('.progress-bar-fill').css('width', '0%');
			
			// Animate progress bar
			$('.progress-bar-fill').animate({width: '90%'}, 2000);
			
			$.ajax({
				url: seo_simple_auto_config_admin.ajax_url,
				type: 'POST',
				data: {
					action: 'seo_simple_run_auto_config',
					nonce: seo_simple_auto_config_admin.nonce_run_config
				},
				success: function(response) {
					$('.progress-bar-fill').css('width', '100%');
					
					if (response.success) {
						alert(response.data.message);
						window.location.href = response.data.redirect;
					} else {
						alert(response.data.message);
						$('#config-progress').addClass('hidden');
					}
				},
				error: function() {
					alert(seo_simple_auto_config_admin.strings.error);
					$('#config-progress').addClass('hidden');
				}
			});
		}
	});
});
</script>