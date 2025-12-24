<?php
/**
 * Security page template.
 *
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php _e( 'Security', 'seo-simple' ); ?></h1>
	
	<div class="seo-simple-security-overview">
		<div class="seo-simple-security-card">
			<h3><?php _e( 'Security Score', 'seo-simple' ); ?></h3>
			<div class="count <?php echo empty( $scan_results['issues'] ) ? 'status-good' : 'status-warning'; ?>">
				<?php echo empty( $scan_results['issues'] ) ? '100%' : ( 100 - min( 100, count( $scan_results['issues'] ) * 10 ) ) . '%'; ?>
			</div>
		</div>
		
		<div class="seo-simple-security-card">
			<h3><?php _e( 'Blocked IPs', 'seo-simple' ); ?></h3>
			<div class="count seo-simple-blocked-ips-count"><?php echo count( $blocked_ips ); ?></div>
		</div>
		
		<div class="seo-simple-security-card">
			<h3><?php _e( 'Last Scan', 'seo-simple' ); ?></h3>
			<div class="count">
				<?php echo ! empty( $scan_results['scan_time'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $scan_results['scan_time'] ) ) : __( 'Never', 'seo-simple' ); ?>
			</div>
		</div>
		
		<div class="seo-simple-security-card <?php echo empty( $scan_results['issues'] ) ? 'status-good' : 'status-error'; ?>">
			<h3><?php _e( 'Issues Found', 'seo-simple' ); ?></h3>
			<div class="count"><?php echo ! empty( $scan_results['issues'] ) ? count( $scan_results['issues'] ) : 0; ?></div>
		</div>
	</div>
	
	<div class="seo-simple-tabs">
		<div class="seo-simple-tab active" data-tab="scan"><?php _e( 'Scan', 'seo-simple' ); ?></div>
		<div class="seo-simple-tab" data-tab="blocked-ips"><?php _e( 'Blocked IPs', 'seo-simple' ); ?></div>
		<div class="seo-simple-tab" data-tab="logs"><?php _e( 'Security Logs', 'seo-simple' ); ?></div>
		<div class="seo-simple-tab" data-tab="wp-security"><?php _e( 'WordPress Security', 'seo-simple' ); ?></div>
		<div class="seo-simple-tab" data-tab="php-security"><?php _e( 'PHP Security', 'seo-simple' ); ?></div>
	</div>
	
	<div class="seo-simple-tab-content">
		<!-- Scan Tab -->
		<div id="scan" class="seo-simple-tab-pane active">
			<h2><?php _e( 'Security Scan', 'seo-simple' ); ?></h2>
			<p><?php _e( 'Run a security scan to check for malware, suspicious code, and other security issues.', 'seo-simple' ); ?></p>
			
			<p>
				<button type="button" id="seo-simple-run-scan" class="button button-primary">
					<?php _e( 'Run Security Scan', 'seo-simple' ); ?>
				</button>
			</p>
			
			<div id="seo-simple-scan-results">
				<?php if ( ! empty( $scan_results ) ) : ?>
					<div class="seo-simple-scan-summary">
						<h3><?php _e( 'Last Scan Results', 'seo-simple' ); ?></h3>
						<p><?php _e( 'Scan Time:', 'seo-simple' ); ?> <?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $scan_results['scan_time'] ) ); ?></p>
						<p><?php _e( 'Scanned Files:', 'seo-simple' ); ?> <?php echo $scan_results['scanned_files']; ?></p>
						<p><?php _e( 'Issues Found:', 'seo-simple' ); ?> <?php echo count( $scan_results['issues'] ); ?></p>
					</div>
					
					<?php if ( ! empty( $scan_results['issues'] ) ) : ?>
						<div class="seo-simple-scan-issues">
							<h4><?php _e( 'Issues Found', 'seo-simple' ); ?></h4>
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th><?php _e( 'Type', 'seo-simple' ); ?></th>
										<th><?php _e( 'File', 'seo-simple' ); ?></th>
										<th><?php _e( 'Message', 'seo-simple' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $scan_results['issues'] as $issue ) : ?>
										<tr>
											<td><?php echo esc_html( $issue['type'] ); ?></td>
											<td><?php echo esc_html( $issue['file'] ); ?></td>
											<td><?php echo esc_html( $issue['message'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else : ?>
						<div class="seo-simple-scan-no-issues">
							<p><?php _e( 'No security issues found. Your website appears to be secure.', 'seo-simple' ); ?></p>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Blocked IPs Tab -->
		<div id="blocked-ips" class="seo-simple-tab-pane">
			<h2><?php _e( 'Blocked IPs', 'seo-simple' ); ?></h2>
			<p><?php _e( 'Manage blocked IP addresses.', 'seo-simple' ); ?></p>
			
			<div class="seo-simple-blocked-ips">
				<?php if ( ! empty( $blocked_ips ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th class="column-ip"><?php _e( 'IP Address', 'seo-simple' ); ?></th>
								<th class="column-reason"><?php _e( 'Reason', 'seo-simple' ); ?></th>
								<th class="column-date"><?php _e( 'Date Blocked', 'seo-simple' ); ?></th>
								<th class="column-actions"><?php _e( 'Actions', 'seo-simple' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $blocked_ips as $ip ) : ?>
								<tr>
									<td><?php echo esc_html( $ip ); ?></td>
									<td><?php echo isset( $ip['reason'] ) ? esc_html( $ip['reason'] ) : __( 'Manual block', 'seo-simple' ); ?></td>
									<td><?php echo isset( $ip['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $ip['date'] ) ) : __( 'Unknown', 'seo-simple' ); ?></td>
									<td>
										<button type="button" class="button seo-simple-unblock-ip" data-ip="<?php echo esc_attr( $ip ); ?>">
											<?php _e( 'Unblock', 'seo-simple' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php _e( 'No IP addresses are currently blocked.', 'seo-simple' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Logs Tab -->
		<div id="logs" class="seo-simple-tab-pane">
			<h2><?php _e( 'Security Logs', 'seo-simple' ); ?></h2>
			<p><?php _e( 'View recent security events.', 'seo-simple' ); ?></p>
			
			<p>
				<button type="button" id="seo-simple-load-logs" class="button" data-limit="50">
					<?php _e( 'Load Security Logs', 'seo-simple' ); ?>
				</button>
			</p>
			
			<div id="seo-simple-security-logs">
				<?php if ( ! empty( $security_logs ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th class="column-timestamp"><?php _e( 'Timestamp', 'seo-simple' ); ?></th>
								<th class="column-event"><?php _e( 'Event', 'seo-simple' ); ?></th>
								<th class="column-details"><?php _e( 'Details', 'seo-simple' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $security_logs as $log ) : ?>
								<tr>
									<td><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['timestamp'] ) ); ?></td>
									<td><?php echo esc_html( $log['event'] ); ?></td>
									<td><?php echo esc_html( json_encode( $log['data'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php _e( 'No security logs found.', 'seo-simple' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- WordPress Security Tab -->
		<div id="wp-security" class="seo-simple-tab-pane">
			<h2><?php _e( 'WordPress Security Status', 'seo-simple' ); ?></h2>
			<div class="seo-simple-security-overview">
				<div class="seo-simple-security-card">
					<h3><?php _e( 'WordPress Version', 'seo-simple' ); ?></h3>
					<?php
					global $wp_version;
					echo '<div class="count">' . esc_html( $wp_version ) . '</div>';
					
					if ( ! function_exists( 'wp_version_check' ) ) {
						include_once ABSPATH . WPINC . '/update.php';
					}
					wp_version_check();
					$updates = get_core_updates();
					
					if ( ! empty( $updates ) && isset( $updates[0] ) && $updates[0]->response != 'latest' ) {
						echo '<p class="status-error">' . __( 'Update available:', 'seo-simple' ) . ' ' . esc_html( $updates[0]->version ) . '</p>';
					} else {
						echo '<p class="status-good">' . __( 'Up to date', 'seo-simple' ) . '</p>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'Database Prefix', 'seo-simple' ); ?></h3>
					<?php
					global $wpdb;
					echo '<div class="count">' . esc_html( $wpdb->prefix ) . '</div>';
					if ( $wpdb->prefix === 'wp_' ) {
						echo '<p class="status-error">' . __( 'Default prefix', 'seo-simple' ) . '</p>';
					} else {
						echo '<p class="status-good">' . __( 'Custom prefix', 'seo-simple' ) . '</p>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'File Editing', 'seo-simple' ); ?></h3>
					<?php
					if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
						echo '<div class="count status-good">' . __( 'Disabled', 'seo-simple' ) . '</div>';
					} else {
						echo '<div class="count status-error">' . __( 'Enabled', 'seo-simple' ) . '</div>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'Plugin Updates', 'seo-simple' ); ?></h3>
					<?php
					if ( ! function_exists( 'wp_update_plugins' ) ) {
						require_once ABSPATH . 'wp-admin/includes/update.php';
					}
					wp_update_plugins();
					$plugin_updates = get_plugin_updates();
					
					if ( empty( $plugin_updates ) ) {
						echo '<div class="count status-good">0</div>';
						echo '<p class="status-good">' . __( 'All up to date', 'seo-simple' ) . '</p>';
					} else {
						echo '<div class="count status-warning">' . count( $plugin_updates ) . '</div>';
						echo '<p class="status-warning">' . __( 'Updates available', 'seo-simple' ) . '</p>';
					}
					?>
				</div>
			</div>
			
			<h3><?php _e( 'Security Issues Found', 'seo-simple' ); ?></h3>
			<?php
			// Get WordPress security issues from scan results
			$wp_issues = array();
			if ( ! empty( $scan_results['issues'] ) ) {
				foreach ( $scan_results['issues'] as $issue ) {
					if ( in_array( $issue['type'], array(
						'wordpress_version', 'debug_mode', 'script_debug', 'save_queries',
						'db_prefix', 'default_username', 'old_password',
						'security_key', 'file_edit', 'file_mods', 'auto_update', 'ssl_admin',
						'too_many_admins', 'inactive_user', 'too_many_contributors',
						'inactive_plugins', 'outdated_plugin', 'outdated_theme', 'readme_file'
					) ) ) {
						$wp_issues[] = $issue;
					}
				}
			}
			
			if ( ! empty( $wp_issues ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'Type', 'seo-simple' ); ?></th>
							<th><?php _e( 'File', 'seo-simple' ); ?></th>
							<th><?php _e( 'Message', 'seo-simple' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $wp_issues as $issue ) : ?>
							<tr>
								<td><?php echo esc_html( $issue['type'] ); ?></td>
								<td><?php echo esc_html( $issue['file'] ); ?></td>
								<td><?php echo esc_html( $issue['message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php _e( 'No WordPress security issues found.', 'seo-simple' ); ?></p>
			<?php endif; ?>
		</div>
		
		<!-- PHP Security Tab -->
		<div id="php-security" class="seo-simple-tab-pane">
			<h2><?php _e( 'PHP Security Status', 'seo-simple' ); ?></h2>
			<div class="seo-simple-security-overview">
				<div class="seo-simple-security-card">
					<h3><?php _e( 'PHP Version', 'seo-simple' ); ?></h3>
					<?php
					echo '<div class="count">' . esc_html( PHP_VERSION ) . '</div>';
					if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
						echo '<p class="status-error">' . __( 'Outdated - upgrade recommended', 'seo-simple' ) . '</p>';
					} else {
						echo '<p class="status-good">' . __( 'Supported', 'seo-simple' ) . '</p>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'Display Errors', 'seo-simple' ); ?></h3>
					<?php
					if ( ini_get( 'display_errors' ) ) {
						echo '<div class="count status-error">' . __( 'Enabled', 'seo-simple' ) . '</div>';
					} else {
						echo '<div class="count status-good">' . __( 'Disabled', 'seo-simple' ) . '</div>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'Allow URL Include', 'seo-simple' ); ?></h3>
					<?php
					if ( ini_get( 'allow_url_include' ) ) {
						echo '<div class="count status-error">' . __( 'Enabled', 'seo-simple' ) . '</div>';
					} else {
						echo '<div class="count status-good">' . __( 'Disabled', 'seo-simple' ) . '</div>';
					}
					?>
				</div>
				
				<div class="seo-simple-security-card">
					<h3><?php _e( 'Disabled Functions', 'seo-simple' ); ?></h3>
					<?php
					$dangerous_functions = array(
						'exec', 'system', 'shell_exec', 'passthru', 'proc_open', 'popen',
						'eval', 'create_function', 'assert', 'preg_replace' . '/e',
					);
					
					$disabled_functions = ini_get( 'disable_functions' );
					$disabled_array = empty( $disabled_functions ) ? array() : explode( ',', $disabled_functions );
					$disabled_array = array_map( 'trim', $disabled_array );
					
					$enabled_dangerous = array();
					foreach ( $dangerous_functions as $function ) {
						if ( ! in_array( $function, $disabled_array ) && function_exists( $function ) ) {
							$enabled_dangerous[] = $function;
						}
					}
					
					if ( empty( $enabled_dangerous ) ) {
						echo '<div class="count status-good">' . __( '0', 'seo-simple' ) . '</div>';
						echo '<p class="status-good">' . __( 'All disabled', 'seo-simple' ) . '</p>';
					} else {
						echo '<div class="count status-warning">' . count( $enabled_dangerous ) . '</div>';
						echo '<p class="status-warning">' . __( 'Some enabled', 'seo-simple' ) . '</p>';
					}
					?>
				</div>
			</div>
			
			<h3><?php _e( 'PHP Security Issues', 'seo-simple' ); ?></h3>
			<?php
			// Get PHP security issues from scan results
			$php_issues = array();
			if ( ! empty( $scan_results['issues'] ) ) {
				foreach ( $scan_results['issues'] as $issue ) {
					if ( in_array( $issue['type'], array(
						'php_version', 'dangerous_function', 'php_errors',
						'allow_url_include', 'register_globals'
					) ) ) {
						$php_issues[] = $issue;
					}
				}
			}
			
			if ( ! empty( $php_issues ) ) : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'Type', 'seo-simple' ); ?></th>
							<th><?php _e( 'File', 'seo-simple' ); ?></th>
							<th><?php _e( 'Message', 'seo-simple' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $php_issues as $issue ) : ?>
							<tr>
								<td><?php echo esc_html( $issue['type'] ); ?></td>
								<td><?php echo esc_html( $issue['file'] ); ?></td>
								<td><?php echo esc_html( $issue['message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php _e( 'No PHP security issues found.', 'seo-simple' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>