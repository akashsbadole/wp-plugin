<?php
/**
 * Template for the keyword suggestions section in the meta box.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$settings = get_option( 'seo_simple_settings', array() );
$enabled = isset( $settings['enable_keyword_suggestions'] ) ? (bool) $settings['enable_keyword_suggestions'] : true;
$language = isset( $settings['keyword_suggestions_language'] ) ? $settings['keyword_suggestions_language'] : 'en';
$country = isset( $settings['keyword_suggestions_country'] ) ? $settings['keyword_suggestions_country'] : 'us';
$limit = isset( $settings['keyword_suggestions_limit'] ) ? (int) $settings['keyword_suggestions_limit'] : 10;
?>

<div class="seo-simple-keyword-suggestions">
	<?php if ( $enabled ) : ?>
		<div class="keyword-input-container">
			<input type="text" class="seo-simple-keyword-input" placeholder="<?php esc_attr_e( 'Enter a keyword to get suggestions...', 'seo-simple' ); ?>" value="<?php echo esc_attr( $focus_keyword ); ?>">
			<button type="button" class="seo-simple-get-suggestions button button-secondary" data-post-id="<?php echo $post_id; ?>" data-limit="<?php echo $limit; ?>" data-language="<?php echo esc_attr( $language ); ?>" data-country="<?php echo esc_attr( $country ); ?>">
				<?php esc_html_e( 'Get Suggestions', 'seo-simple' ); ?>
			</button>
		</div>
		
		<div class="suggestions-container">
			<div class="empty-state">
				<?php esc_html_e( 'Enter a keyword above to get suggestions.', 'seo-simple' ); ?>
			</div>
		</div>
		
		<div class="trending-keywords">
			<h4><?php esc_html_e( 'Trending Keywords', 'seo-simple' ); ?> 
				<button type="button" class="seo-simple-refresh-trending button button-small" title="<?php esc_attr_e( 'Refresh trending keywords', 'seo-simple' ); ?>">
					<span class="dashicons dashicons-update"></span>
				</button>
			</h4>
			<div class="trending-keywords-list">
				<div class="loading"><?php esc_html_e( 'Loading trending keywords...', 'seo-simple' ); ?></div>
			</div>
		</div>
		
		<div class="keyword-actions">
			<button type="button" class="seo-simple-clear-cache button button-small">
				<?php esc_html_e( 'Clear Cache', 'seo-simple' ); ?>
			</button>
		</div>
		
		<div class="keyword-help">
			<p>
				<strong><?php esc_html_e( 'Tips:', 'seo-simple' ); ?></strong><br>
				<?php esc_html_e( '• Use specific keywords for better suggestions', 'seo-simple' ); ?><br>
				<?php esc_html_e( '• Green difficulty means easier to rank for', 'seo-simple' ); ?><br>
				<?php esc_html_e( '• Higher volume means more monthly searches', 'seo-simple' ); ?><br>
				<?php esc_html_e( '• Click "Use Keyword" to set it as focus keyword', 'seo-simple' ); ?>
			</p>
		</div>
	<?php else : ?>
		<div class="keyword-suggestions-disabled">
			<p><?php esc_html_e( 'Keyword suggestions are disabled.', 'seo-simple' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings' ) ); ?>">
					<?php esc_html_e( 'Enable keyword suggestions in settings', 'seo-simple' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>