<?php
/**
 * Dashboard page template for the SEO Simple plugin.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = $this->settings->get_settings();
?>

<div class="wrap seo-simple-dashboard">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div class="postbox-container" style="width: 50%;">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'SEO Overview', 'seo-simple' ); ?></h2>
					<div class="inside">
						<p><?php esc_html_e( 'Welcome to SEO Simple! Here\'s an overview of your SEO settings.', 'seo-simple' ); ?></p>
						
						<ul>
							<li><?php
								$posts_with_seo = count( get_posts( array( 
									'post_type' => 'post',
									'post_status' => 'publish',
									'numberposts' => -1,
									'meta_key' => '_seo_simple_title',
									'meta_compare' => 'EXISTS'
								) ) );
								printf( esc_html__( 'Posts with SEO data: %d', 'seo-simple' ), $posts_with_seo );
							?></li>
							<li><?php
								$pages_with_seo = count( get_posts( array( 
									'post_type' => 'page',
									'post_status' => 'publish',
									'numberposts' => -1,
									'meta_key' => '_seo_simple_title',
									'meta_compare' => 'EXISTS'
								) ) );
								printf( esc_html__( 'Pages with SEO data: %d', 'seo-simple' ), $pages_with_seo );
							?></li>
							<li><?php
								$last_generated = get_option( 'seo_simple_sitemap_last_generated', 0 );
								if ( $last_generated ) {
									printf( esc_html__( 'Last sitemap generation: %s', 'seo-simple' ), date_i18n( get_option( 'date_format' ), $last_generated ) );
								} else {
									esc_html_e( 'Sitemap not generated yet', 'seo-simple' );
								}
							?></li>
						</ul>
						
						<p>
							<a href="?page=seo-simple-settings" class="button button-primary"><?php esc_html_e( 'Configure SEO Settings', 'seo-simple' ); ?></a>
						</p>
					</div>
				</div>
				
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Quick Actions', 'seo-simple' ); ?></h2>
					<div class="inside">
						<p>
							<a href="?page=seo-simple-settings" class="button"><?php esc_html_e( 'SEO Settings', 'seo-simple' ); ?></a>
						</p>
						<p>
							<button type="button" class="button regenerate-sitemap"><?php esc_html_e( 'Regenerate Sitemap', 'seo-simple' ); ?></button>
						</p>
						<p>
							<a href="?page=seo-simple-settings&tab=advanced" class="button"><?php esc_html_e( 'Import/Export Settings', 'seo-simple' ); ?></a>
						</p>
					</div>
				</div>
			</div>
			
			<div class="postbox-container" style="width: 50%;">
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'SEO Tips', 'seo-simple' ); ?></h2>
					<div class="inside">
						<ol>
							<li><?php esc_html_e( 'Write unique, descriptive titles for each page.', 'seo-simple' ); ?></li>
							<li><?php esc_html_e( 'Include your target keywords naturally in your content.', 'seo-simple' ); ?></li>
							<li><?php esc_html_e( 'Write compelling meta descriptions that encourage clicks.', 'seo-simple' ); ?></li>
							<li><?php esc_html_e( 'Use appropriate heading tags (H1, H2, H3).', 'seo-simple' ); ?></li>
							<li><?php esc_html_e( 'Optimize your images with alt text.', 'seo-simple' ); ?></li>
							<li><?php esc_html_e( 'Submit your sitemap to search engines.', 'seo-simple' ); ?></li>
						</ol>
					</div>
				</div>
				
				<div class="postbox">
					<h2 class="hndle"><?php esc_html_e( 'Resources', 'seo-simple' ); ?></h2>
					<div class="inside">
						<ul>
							<li><a href="https://developers.google.com/search/docs/fundamentals/seo-starter-guide" target="_blank"><?php esc_html_e( 'Google SEO Starter Guide', 'seo-simple' ); ?></a></li>
							<li><a href="https://search.google.com/search-console" target="_blank"><?php esc_html_e( 'Google Search Console', 'seo-simple' ); ?></a></li>
							<li><a href="https://wordpress.org/support/article/wordpress-seo/" target="_blank"><?php esc_html_e( 'WordPress SEO Documentation', 'seo-simple' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Regenerate sitemap
	$('.regenerate-sitemap').on('click', function() {
		var button = $(this);
		button.prop('disabled', true).text('<?php esc_html_e( 'Regenerating...', 'seo-simple' ); ?>');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_regenerate_sitemap',
				nonce: '<?php echo wp_create_nonce( 'seo_simple_regenerate_sitemap' ); ?>'
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					button.prop('disabled', false).text('<?php esc_html_e( 'Regenerate Sitemap', 'seo-simple' ); ?>');
					alert('<?php esc_html_e( 'Error regenerating sitemap. Please try again.', 'seo-simple' ); ?>');
				}
			},
			error: function() {
				button.prop('disabled', false).text('<?php esc_html_e( 'Regenerate Sitemap', 'seo-simple' ); ?>');
				alert('<?php esc_html_e( 'Error regenerating sitemap. Please try again.', 'seo-simple' ); ?>');
			}
		});
	});
});
</script>