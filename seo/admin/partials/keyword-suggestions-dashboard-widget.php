<?php
/**
 * Template for the keyword suggestions dashboard widget.
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
?>

<div class="seo-simple-keyword-suggestions-dashboard">
	<?php if ( $enabled ) : ?>
		<?php if ( ! empty( $trending_keywords ) ) : ?>
			<div class="trending-keywords-list">
				<?php foreach ( $trending_keywords as $keyword ) : ?>
					<div class="trending-keyword-item">
						<div class="trending-keyword-title"><?php echo esc_html( $keyword['keyword'] ); ?></div>
						<div class="trending-keyword-metrics">
							<span class="difficulty-indicator difficulty-<?php echo esc_attr( $keyword['difficulty'] < 30 ? 'easy' : ( $keyword['difficulty'] < 60 ? 'medium' : 'hard' ) ); ?>"></span>
							<span><?php echo esc_html( Seo_Simple_Keyword_Suggestions::get_instance()->get_difficulty_label( $keyword['difficulty'] ) ); ?></span>
							<span> • </span>
							<span><?php echo esc_html( $keyword['volume'] ); ?>/mo</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<div class="trending-keywords-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Keyword Settings', 'seo-simple' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="no-trending-keywords">
				<p><?php esc_html_e( 'No trending keywords available.', 'seo-simple' ); ?></p>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="keyword-suggestions-disabled">
			<p><?php esc_html_e( 'Keyword suggestions are disabled.', 'seo-simple' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-settings' ) ); ?>">
					<?php esc_html_e( 'Enable in settings', 'seo-simple' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
</div>

<style>
.seo-simple-keyword-suggestions-dashboard .trending-keywords-list {
	max-height: 200px;
	overflow-y: auto;
	margin-bottom: 10px;
}

.seo-simple-keyword-suggestions-dashboard .trending-keyword-item {
	padding: 8px 0;
	border-bottom: 1px solid #f0f0f0;
}

.seo-simple-keyword-suggestions-dashboard .trending-keyword-item:last-child {
	border-bottom: none;
}

.seo-simple-keyword-suggestions-dashboard .trending-keyword-title {
	font-weight: 600;
	color: #333;
	margin-bottom: 3px;
}

.seo-simple-keyword-suggestions-dashboard .trending-keyword-metrics {
	font-size: 11px;
	color: #666;
}

.seo-simple-keyword-suggestions-dashboard .difficulty-indicator {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	display: inline-block;
	margin-right: 3px;
}

.seo-simple-keyword-suggestions-dashboard .difficulty-easy {
	background-color: #46b450;
}

.seo-simple-keyword-suggestions-dashboard .difficulty-medium {
	background-color: #ffb900;
}

.seo-simple-keyword-suggestions-dashboard .difficulty-hard {
	background-color: #dc3232;
}

.seo-simple-keyword-suggestions-dashboard .no-trending-keywords,
.seo-simple-keyword-suggestions-dashboard .keyword-suggestions-disabled {
	padding: 10px 0;
	text-align: center;
	color: #666;
}

.seo-simple-keyword-suggestions-dashboard .trending-keywords-actions {
	text-align: center;
}
</style>