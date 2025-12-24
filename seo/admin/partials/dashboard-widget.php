<?php
/**
 * Dashboard widget partial for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Get SEO statistics
$stats = $this->get_seo_statistics();
$posts_needing_improvement = $this->get_posts_needing_improvement( 5 );
$missing_meta = $this->get_missing_meta_count();
?>

<div class="seo-simple-dashboard-widget">
	<div class="seo-simple-stats-grid">
		<div class="seo-simple-stat-item">
			<div class="seo-simple-stat-number"><?php echo esc_html( $stats['total_posts'] ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Total Posts', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat-item">
			<div class="seo-simple-stat-number"><?php echo esc_html( $stats['indexed_pages'] ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Indexed Pages', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat-item">
			<div class="seo-simple-stat-number"><?php echo esc_html( $stats['good_seo_score'] ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Good SEO Score', 'seo-simple' ); ?></div>
		</div>
		
		<div class="seo-simple-stat-item">
			<div class="seo-simple-stat-number"><?php echo esc_html( $stats['posts_needing_improvement'] ); ?></div>
			<div class="seo-simple-stat-label"><?php _e( 'Need Improvement', 'seo-simple' ); ?></div>
		</div>
	</div>

	<div class="seo-simple-seo-health">
		<h4><?php _e( 'SEO Health', 'seo-simple' ); ?></h4>
		<div class="seo-simple-health-bar">
			<?php 
			$health_percentage = $stats['total_posts'] > 0 ? 
				round( ( $stats['good_seo_score'] / $stats['total_posts'] ) * 100 ) : 0;
			$health_class = 'bad';
			if ( $health_percentage >= 70 ) $health_class = 'good';
			elseif ( $health_percentage >= 50 ) $health_class = 'ok';
			?>
			<div class="seo-simple-health-fill seo-simple-<?php echo esc_attr( $health_class ); ?>" 
				 style="width: <?php echo esc_attr( $health_percentage ); ?>%;"></div>
		</div>
		<div class="seo-simple-health-text">
			<?php 
			printf( 
				__( '%d%% of your content has good SEO scores', 'seo-simple' ), 
				$health_percentage 
			); 
			?>
		</div>
	</div>

	<div class="seo-simple-missing-meta">
		<h4><?php _e( 'Missing SEO Data', 'seo-simple' ); ?></h4>
		<div class="seo-simple-meta-issues">
			<div class="seo-simple-meta-issue">
				<span class="seo-simple-issue-label"><?php _e( 'Missing Titles:', 'seo-simple' ); ?></span>
				<span class="seo-simple-issue-count seo-simple-<?php echo $missing_meta['title'] > 0 ? 'bad' : 'good'; ?>">
					<?php echo esc_html( $missing_meta['title'] ); ?>
				</span>
			</div>
			<div class="seo-simple-meta-issue">
				<span class="seo-simple-issue-label"><?php _e( 'Missing Descriptions:', 'seo-simple' ); ?></span>
				<span class="seo-simple-issue-count seo-simple-<?php echo $missing_meta['description'] > 0 ? 'bad' : 'good'; ?>">
					<?php echo esc_html( $missing_meta['description'] ); ?>
				</span>
			</div>
			<div class="seo-simple-meta-issue">
				<span class="seo-simple-issue-label"><?php _e( 'Missing Focus Keywords:', 'seo-simple' ); ?></span>
				<span class="seo-simple-issue-count seo-simple-<?php echo $missing_meta['focus_keyword'] > 0 ? 'bad' : 'good'; ?>">
					<?php echo esc_html( $missing_meta['focus_keyword'] ); ?>
				</span>
			</div>
		</div>
	</div>

	<?php if ( ! empty( $posts_needing_improvement ) ) : ?>
	<div class="seo-simple-needs-improvement">
		<h4><?php _e( 'Top 5 Posts Needing SEO Improvement', 'seo-simple' ); ?></h4>
		<ul class="seo-simple-posts-list">
			<?php foreach ( $posts_needing_improvement as $post ) : ?>
				<li class="seo-simple-post-item">
					<div class="seo-simple-post-title">
						<a href="<?php echo esc_url( $post['edit_link'] ); ?>" target="_blank">
							<?php echo esc_html( $post['title'] ); ?>
						</a>
					</div>
					<div class="seo-simple-post-issues">
						<?php 
						$issues_text = sprintf( 
							_n( '%d issue', '%d issues', $post['issues'], 'seo-simple' ), 
							$post['issues'] 
						);
						$score_class = 'bad';
						if ( $post['seo_score'] >= 70 ) $score_class = 'good';
						elseif ( $post['seo_score'] >= 50 ) $score_class = 'ok';
						?>
						<span class="seo-simple-issues-text"><?php echo esc_html( $issues_text ); ?></span>
						<span class="seo-simple-score seo-simple-<?php echo esc_attr( $score_class ); ?>">
							<?php echo esc_html( $post['seo_score'] ); ?>/100
						</span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<div class="seo-simple-dashboard-actions">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple-bulk-editor' ) ); ?>" 
		   class="button button-primary">
			<?php _e( 'Bulk Edit SEO Data', 'seo-simple' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=seo-simple' ) ); ?>" 
		   class="button">
			<?php _e( 'View Full Dashboard', 'seo-simple' ); ?>
		</a>
		<button type="button" class="button seo-simple-refresh-widget" id="seo-simple-refresh-widget">
			<?php _e( 'Refresh', 'seo-simple' ); ?>
		</button>
	</div>
</div>

<style>
.seo-simple-dashboard-widget {
	padding: 12px;
}

.seo-simple-stats-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 12px;
	margin-bottom: 20px;
}

.seo-simple-stat-item {
	text-align: center;
	padding: 10px;
	background: #f9f9f9;
	border-radius: 4px;
}

.seo-simple-stat-number {
	font-size: 24px;
	font-weight: bold;
	color: #333;
	display: block;
}

.seo-simple-stat-label {
	font-size: 12px;
	color: #666;
	text-transform: uppercase;
	margin-top: 4px;
}

.seo-simple-seo-health {
	margin-bottom: 20px;
}

.seo-simple-seo-health h4,
.seo-simple-missing-meta h4,
.seo-simple-needs-improvement h4 {
	margin: 0 0 8px 0;
	font-size: 14px;
	color: #333;
}

.seo-simple-health-bar {
	height: 8px;
	background: #e1e1e1;
	border-radius: 4px;
	margin-bottom: 6px;
	overflow: hidden;
}

.seo-simple-health-fill {
	height: 100%;
	transition: width 0.3s ease;
}

.seo-simple-health-fill.seo-simple-good {
	background-color: #46b450;
}

.seo-simple-health-fill.seo-simple-ok {
	background-color: #ffb900;
}

.seo-simple-health-fill.seo-simple-bad {
	background-color: #dc3232;
}

.seo-simple-health-text {
	font-size: 12px;
	color: #666;
}

.seo-simple-meta-issues {
	margin-bottom: 20px;
}

.seo-simple-meta-issue {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 4px 0;
}

.seo-simple-issue-label {
	font-size: 12px;
	color: #333;
}

.seo-simple-issue-count {
	font-weight: bold;
	font-size: 12px;
}

.seo-simple-issue-count.seo-simple-good {
	color: #46b450;
}

.seo-simple-issue-count.seo-simple-ok {
	color: #ffb900;
}

.seo-simple-issue-count.seo-simple-bad {
	color: #dc3232;
}

.seo-simple-posts-list {
	margin: 0;
	padding: 0;
	list-style: none;
}

.seo-simple-post-item {
	padding: 8px 0;
	border-bottom: 1px solid #eee;
}

.seo-simple-post-item:last-child {
	border-bottom: none;
}

.seo-simple-post-title a {
	display: block;
	font-size: 13px;
	color: #0073aa;
	text-decoration: none;
}

.seo-simple-post-title a:hover {
	text-decoration: underline;
}

.seo-simple-post-issues {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 4px;
}

.seo-simple-issues-text {
	font-size: 11px;
	color: #666;
}

.seo-simple-score {
	font-size: 11px;
	font-weight: bold;
	padding: 2px 4px;
	border-radius: 2px;
	color: #fff;
}

.seo-simple-score.seo-simple-good {
	background-color: #46b450;
}

.seo-simple-score.seo-simple-ok {
	background-color: #ffb900;
}

.seo-simple-score.seo-simple-bad {
	background-color: #dc3232;
}

.seo-simple-dashboard-actions {
	display: flex;
	gap: 8px;
	flex-wrap: wrap;
	margin-top: 15px;
}

.seo-simple-dashboard-actions .button {
	font-size: 12px;
	padding: 0 8px;
	height: 28px;
	line-height: 26px;
}

.seo-simple-refresh-widget {
	position: relative;
}

.seo-simple-refresh-widget.seo-simple-loading::after {
	content: "";
	position: absolute;
	right: 8px;
	top: 50%;
	transform: translateY(-50%);
	width: 16px;
	height: 16px;
	border: 2px solid #f3f3f3;
	border-top: 2px solid #0073aa;
	border-radius: 50%;
	animation: seo-simple-spin 1s linear infinite;
}

@keyframes seo-simple-spin {
	0% { transform: translateY(-50%) rotate(0deg); }
	100% { transform: translateY(-50%) rotate(360deg); }
}

@media screen and (max-width: 782px) {
	.seo-simple-stats-grid {
		grid-template-columns: 1fr;
	}
	
	.seo-simple-dashboard-actions {
		flex-direction: column;
	}
	
	.seo-simple-dashboard-actions .button {
		width: 100%;
		text-align: center;
	}
}
</style>