<?php
/**
 * Import/Export page template
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap seo-simple-import-export">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<?php
	// Display import results if available
	if ( isset( $import_results ) && ! empty( $import_results ) ) :
		$message_class = $import_results['success'] > 0 ? 'notice-success' : 'notice-error';
		?>
		<div class="notice <?php echo esc_attr( $message_class ); ?> is-dismissible">
			<h3><?php esc_html_e( 'Import Results', 'seo-simple' ); ?></h3>
			<ul>
				<li><?php printf( esc_html__( 'Successfully imported: %d', 'seo-simple' ), $import_results['success'] ); ?></li>
				<li><?php printf( esc_html__( 'Failed: %d', 'seo-simple' ), $import_results['failed'] ); ?></li>
				<li><?php printf( esc_html__( 'Skipped: %d', 'seo-simple' ), $import_results['skipped'] ); ?></li>
			</ul>
			<?php if ( ! empty( $import_results['errors'] ) ) : ?>
				<details>
					<summary><?php esc_html_e( 'View Errors', 'seo-simple' ); ?></summary>
					<ul>
						<?php foreach ( $import_results['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="seo-import-export-container">
		
		<!-- Export Section -->
		<div class="postbox">
			<h2 class="hndle"><span><?php esc_html_e( 'Export SEO Data', 'seo-simple' ); ?></span></h2>
			<div class="inside">
				<p><?php esc_html_e( 'Export your SEO data to CSV or JSON format. You can export all posts or specific posts.', 'seo-simple' ); ?></p>
				
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="seo-export-form">
					<?php wp_nonce_field( 'seo_simple_export', 'seo_simple_export_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="export_format"><?php esc_html_e( 'Export Format', 'seo-simple' ); ?></label>
							</th>
							<td>
								<select name="export_format" id="export_format" class="regular-text">
									<option value="csv"><?php esc_html_e( 'CSV (Comma Separated Values)', 'seo-simple' ); ?></option>
									<option value="json"><?php esc_html_e( 'JSON (JavaScript Object Notation)', 'seo-simple' ); ?></option>
								</select>
								<p class="description">
									<?php esc_html_e( 'CSV is best for Excel/Google Sheets. JSON includes settings and is better for backup/restore.', 'seo-simple' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="export_scope"><?php esc_html_e( 'Export Scope', 'seo-simple' ); ?></label>
							</th>
							<td>
								<select name="export_scope" id="export_scope" class="regular-text">
									<option value="all"><?php esc_html_e( 'All Posts & Pages', 'seo-simple' ); ?></option>
									<option value="posts"><?php esc_html_e( 'Posts Only', 'seo-simple' ); ?></option>
									<option value="pages"><?php esc_html_e( 'Pages Only', 'seo-simple' ); ?></option>
									<option value="products"><?php esc_html_e( 'Products Only (WooCommerce)', 'seo-simple' ); ?></option>
									<option value="custom"><?php esc_html_e( 'Custom Post IDs', 'seo-simple' ); ?></option>
								</select>
							</td>
						</tr>
						<tr id="custom_post_ids_row" style="display: none;">
							<th scope="row">
								<label for="post_ids"><?php esc_html_e( 'Post IDs', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="text" name="post_ids" id="post_ids" class="regular-text" placeholder="1,2,3,4,5">
								<p class="description">
									<?php esc_html_e( 'Enter comma-separated post IDs to export specific posts.', 'seo-simple' ); ?>
								</p>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<button type="submit" name="action" value="seo_simple_export_csv" class="button button-primary export-btn">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export to CSV', 'seo-simple' ); ?>
						</button>
						<button type="submit" name="action" value="seo_simple_export_json" class="button button-primary export-btn">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export to JSON', 'seo-simple' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>

		<!-- Import Section -->
		<div class="postbox">
			<h2 class="hndle"><span><?php esc_html_e( 'Import SEO Data', 'seo-simple' ); ?></span></h2>
			<div class="inside">
				<p><?php esc_html_e( 'Import SEO data from CSV or JSON files. Make sure the file format matches the export format.', 'seo-simple' ); ?></p>
				
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="seo-import-form">
					<input type="hidden" name="action" value="seo_simple_import">
					<?php wp_nonce_field( 'seo_simple_import', 'seo_simple_import_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="import_file"><?php esc_html_e( 'Select File', 'seo-simple' ); ?></label>
							</th>
							<td>
								<input type="file" name="import_file" id="import_file" accept=".csv,.json" required>
								<p class="description">
									<?php esc_html_e( 'Upload a CSV or JSON file exported from Simple SEO or another SEO plugin.', 'seo-simple' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="import_mode"><?php esc_html_e( 'Import Mode', 'seo-simple' ); ?></label>
							</th>
							<td>
								<select name="import_mode" id="import_mode" class="regular-text">
									<option value="update"><?php esc_html_e( 'Update Existing (Recommended)', 'seo-simple' ); ?></option>
									<option value="skip"><?php esc_html_e( 'Skip Existing', 'seo-simple' ); ?></option>
									<option value="overwrite"><?php esc_html_e( 'Overwrite All', 'seo-simple' ); ?></option>
								</select>
								<p class="description">
									<?php esc_html_e( 'Choose how to handle posts that already have SEO data.', 'seo-simple' ); ?>
								</p>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<button type="submit" class="button button-primary">
							<span class="dashicons dashicons-upload"></span>
							<?php esc_html_e( 'Import File', 'seo-simple' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>

		<!-- Migration Section -->
		<div class="postbox">
			<h2 class="hndle"><span><?php esc_html_e( 'Migrate from Other SEO Plugins', 'seo-simple' ); ?></span></h2>
			<div class="inside">
				<p><?php esc_html_e( 'Import SEO data from Yoast SEO or Rank Math. This will copy all SEO meta data to Simple SEO.', 'seo-simple' ); ?></p>
				
				<div class="migration-options">
					<!-- Yoast SEO Migration -->
					<div class="migration-option">
						<h3>
							<span class="dashicons dashicons-migrate"></span>
							<?php esc_html_e( 'Yoast SEO', 'seo-simple' ); ?>
						</h3>
						<p><?php esc_html_e( 'Import meta titles, descriptions, focus keywords, Open Graph data, Twitter Cards, and more from Yoast SEO.', 'seo-simple' ); ?></p>
						
						<?php
						// Check if Yoast data exists
						global $wpdb;
						$yoast_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_yoast_wpseo_%'" );
						?>
						
						<?php if ( $yoast_count > 0 ) : ?>
							<p class="migration-status">
								<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
								<?php printf( esc_html__( 'Found %d Yoast SEO meta entries', 'seo-simple' ), $yoast_count ); ?>
							</p>
							
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="migration-form">
								<input type="hidden" name="action" value="seo_simple_import_yoast">
								<?php wp_nonce_field( 'seo_simple_import_yoast', 'seo_simple_import_yoast_nonce' ); ?>
								
								<p>
									<label>
										<input type="checkbox" name="keep_yoast_data" value="1" checked>
										<?php esc_html_e( 'Keep original Yoast data (recommended)', 'seo-simple' ); ?>
									</label>
								</p>
								
								<button type="submit" class="button button-secondary">
									<span class="dashicons dashicons-migrate"></span>
									<?php esc_html_e( 'Import from Yoast SEO', 'seo-simple' ); ?>
								</button>
							</form>
						<?php else : ?>
							<p class="migration-status">
								<span class="dashicons dashicons-warning" style="color: #dba617;"></span>
								<?php esc_html_e( 'No Yoast SEO data found', 'seo-simple' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<!-- Rank Math Migration -->
					<div class="migration-option">
						<h3>
							<span class="dashicons dashicons-migrate"></span>
							<?php esc_html_e( 'Rank Math', 'seo-simple' ); ?>
						</h3>
						<p><?php esc_html_e( 'Import meta titles, descriptions, focus keywords, Open Graph data, Twitter Cards, schema types, and more from Rank Math.', 'seo-simple' ); ?></p>
						
						<?php
						// Check if Rank Math data exists
						$rankmath_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'rank_math_%'" );
						?>
						
						<?php if ( $rankmath_count > 0 ) : ?>
							<p class="migration-status">
								<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
								<?php printf( esc_html__( 'Found %d Rank Math meta entries', 'seo-simple' ), $rankmath_count ); ?>
							</p>
							
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="migration-form">
								<input type="hidden" name="action" value="seo_simple_import_rankmath">
								<?php wp_nonce_field( 'seo_simple_import_rankmath', 'seo_simple_import_rankmath_nonce' ); ?>
								
								<p>
									<label>
										<input type="checkbox" name="keep_rankmath_data" value="1" checked>
										<?php esc_html_e( 'Keep original Rank Math data (recommended)', 'seo-simple' ); ?>
									</label>
								</p>
								
								<button type="submit" class="button button-secondary">
									<span class="dashicons dashicons-migrate"></span>
									<?php esc_html_e( 'Import from Rank Math', 'seo-simple' ); ?>
								</button>
							</form>
						<?php else : ?>
							<p class="migration-status">
								<span class="dashicons dashicons-warning" style="color: #dba617;"></span>
								<?php esc_html_e( 'No Rank Math data found', 'seo-simple' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<div class="migration-notice">
					<p><strong><?php esc_html_e( 'Important Notes:', 'seo-simple' ); ?></strong></p>
					<ul>
						<li><?php esc_html_e( 'Migration will copy data from the other plugin to Simple SEO', 'seo-simple' ); ?></li>
						<li><?php esc_html_e( 'Existing Simple SEO data will be overwritten', 'seo-simple' ); ?></li>
						<li><?php esc_html_e( 'Original plugin data will be kept by default', 'seo-simple' ); ?></li>
						<li><?php esc_html_e( 'You can safely deactivate the other plugin after migration', 'seo-simple' ); ?></li>
						<li><?php esc_html_e( 'Create a backup before migrating (recommended)', 'seo-simple' ); ?></li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Help Section -->
		<div class="postbox">
			<h2 class="hndle"><span><?php esc_html_e( 'Import/Export Help', 'seo-simple' ); ?></span></h2>
			<div class="inside">
				<h3><?php esc_html_e( 'CSV Format', 'seo-simple' ); ?></h3>
				<p><?php esc_html_e( 'CSV files contain the following columns:', 'seo-simple' ); ?></p>
				<ul>
					<li><strong>Post ID:</strong> <?php esc_html_e( 'WordPress post ID (required)', 'seo-simple' ); ?></li>
					<li><strong>Post Title:</strong> <?php esc_html_e( 'Post title (for reference)', 'seo-simple' ); ?></li>
					<li><strong>Meta Title:</strong> <?php esc_html_e( 'SEO title', 'seo-simple' ); ?></li>
					<li><strong>Meta Description:</strong> <?php esc_html_e( 'SEO description', 'seo-simple' ); ?></li>
					<li><strong>Focus Keyword:</strong> <?php esc_html_e( 'Primary keyword', 'seo-simple' ); ?></li>
					<li><strong>And more...</strong></li>
				</ul>

				<h3><?php esc_html_e( 'JSON Format', 'seo-simple' ); ?></h3>
				<p><?php esc_html_e( 'JSON files include:', 'seo-simple' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'All post SEO data', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Plugin settings', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Export metadata (version, date, site URL)', 'seo-simple' ); ?></li>
				</ul>

				<h3><?php esc_html_e( 'Best Practices', 'seo-simple' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Always create a backup before importing', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Test imports on a staging site first', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Use JSON for complete backups', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Use CSV for bulk editing in Excel/Sheets', 'seo-simple' ); ?></li>
					<li><?php esc_html_e( 'Keep original plugin data when migrating', 'seo-simple' ); ?></li>
				</ul>
			</div>
		</div>

	</div>
</div>

<style>
.seo-import-export-container {
	max-width: 1200px;
}

.seo-import-export-container .postbox {
	margin-bottom: 20px;
}

.seo-import-export-container .postbox h2 {
	padding: 15px;
	font-size: 16px;
}

.seo-import-export-container .inside {
	padding: 20px;
}

.migration-options {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.migration-option {
	padding: 20px;
	background: #f6f7f7;
	border-radius: 5px;
	border-left: 4px solid #2271b1;
}

.migration-option h3 {
	margin-top: 0;
	display: flex;
	align-items: center;
	gap: 10px;
}

.migration-option .dashicons {
	font-size: 24px;
	width: 24px;
	height: 24px;
}

.migration-status {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 10px;
	background: white;
	border-radius: 3px;
	margin: 15px 0;
}

.migration-notice {
	margin-top: 30px;
	padding: 15px;
	background: #fff3cd;
	border-left: 4px solid #ffc107;
	border-radius: 3px;
}

.migration-notice ul {
	margin: 10px 0 0 20px;
}

.export-btn .dashicons,
.button .dashicons {
	line-height: 28px;
	margin-right: 5px;
}

#custom_post_ids_row {
	transition: all 0.3s ease;
}

.form-table th {
	width: 200px;
}

details summary {
	cursor: pointer;
	color: #2271b1;
	text-decoration: underline;
}

details summary:hover {
	color: #135e96;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Show/hide custom post IDs field
	$('#export_scope').on('change', function() {
		if ($(this).val() === 'custom') {
			$('#custom_post_ids_row').show();
		} else {
			$('#custom_post_ids_row').hide();
		}
	});

	// Handle export button clicks
	$('.export-btn').on('click', function(e) {
		var format = $(this).val().includes('csv') ? 'csv' : 'json';
		$('#export_format').val(format);
	});

	// Confirm migration
	$('.migration-form').on('submit', function(e) {
		var pluginName = $(this).find('button').text().trim();
		if (!confirm('<?php esc_html_e( 'Are you sure you want to migrate data? This will overwrite existing Simple SEO data.', 'seo-simple' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});

	// Validate import file
	$('.seo-import-form').on('submit', function(e) {
		var file = $('#import_file')[0].files[0];
		if (!file) {
			alert('<?php esc_html_e( 'Please select a file to import.', 'seo-simple' ); ?>');
			e.preventDefault();
			return false;
		}

		var ext = file.name.split('.').pop().toLowerCase();
		if (ext !== 'csv' && ext !== 'json') {
			alert('<?php esc_html_e( 'Please select a CSV or JSON file.', 'seo-simple' ); ?>');
			e.preventDefault();
			return false;
		}

		if (!confirm('<?php esc_html_e( 'Are you sure you want to import this file? Existing data may be overwritten.', 'seo-simple' ); ?>')) {
			e.preventDefault();
			return false;
		}
	});
});
</script