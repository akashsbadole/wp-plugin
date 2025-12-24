<?php
/**
 * Security dashboard widget template.
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="seo-simple-security-dashboard">
	<div class="seo-simple-security-overview">
		<div class="seo-simple-security-card">
			<h4><?php _e( 'Security Score', 'seo-simple' ); ?></h4>
			<div class="count <?php echo empty( $scan_results['issues'] ) ? 'status-good' : 'status-warning'; ?>">
				<?php echo empty( $scan_results['issues'] ) ? '100%' : ( 100 - min( 100, count( $scan_results['issues'] ) * 10 ) ) . '%'; ?>
			</div>
		</div>
		
		<div class="seo-simple-security-card">
			<h4><?php _e( 'Blocked IPs', 'seo-simple' ); ?></h4>
			<div class="count"><?php echo count( $blocked_ips ); ?></div>
		</div>
		
		<div class="seo-simple-security-card <?php echo empty( $scan_results['issues'] ) ? 'status-good' : 'status-error'; ?>">
			<h4><?php _e( 'Issues Found', 'seo-simple' ); ?></h4>
			<div class="count"><?php echo ! empty( $scan_results['issues'] ) ? count( $scan_results['issues'] ) : 0; ?></div>
		</div>
	</div>
	
	<?php if ( ! empty( $security_logs ) ) : ?>
		<h4><?php _e( 'Recent Security Events', 'seo-simple' ); ?></h4>
		<ul>
			<?php foreach ( array_slice( $security_logs, 0, 5 ) as $log ) : ?>
				<li>
					<strong><?php echo esc_html( $log['event'] ); ?></strong>
					<br />
					<span class="description"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['timestamp'] ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	
	<p>
		<a href="<?php echo admin_url( 'admin.php?page=seo-simple-security' ); ?>" class="button button-primary">
			<?php _e( 'View Security Details', 'seo-simple' ); ?>
		</a>
	</p>
</div>