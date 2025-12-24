<?php
/**
 * Export/Import page template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get available post types
$post_types = get_post_types( array( 'public' => true ), 'objects' );

// Get recent exports/imports
$recent_exports = get_option( 'seo_simple_recent_exports', array() );
$recent_imports = get_option( 'seo_simple_recent_imports', array() );
?>

<div class="seo-simple-export-import-container">
	<div class="seo-simple-export-section">
		<h3><?php _e( 'Export SEO Data', 'seo-simple' ); ?></h3>
		<p><?php _e( 'Export SEO data from your posts and pages. Choose the format and post types you want to export.', 'seo-simple' ); ?></p>
		
		<form id="seo-simple-export-form" class="seo-simple-form">
			<div class="form-row">
				<label for="export-post-types"><?php _e( 'Post Types:', 'seo-simple' ); ?></label>
				<div class="checkbox-group">
					<?php foreach ( $post_types as $pt ) : ?>
						<label>
							<input type="checkbox" name="export_post_types[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( $pt->name, 'post' ); ?> />
							<?php echo esc_html( $pt->labels->name ); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			
			<div class="form-row">
				<label for="export-format"><?php _e( 'Export Format:', 'seo-simple' ); ?></label>
				<select id="export-format" name="export_format">
					<option value="csv"><?php _e( 'CSV', 'seo-simple' ); ?></option>
					<option value="json"><?php _e( 'JSON', 'seo-simple' ); ?></option>
					<option value="xlsx"><?php _e( 'Excel (XLSX)', 'seo-simple' ); ?></option>
				</select>
			</div>
			
			<div class="form-row">
				<label for="export-status"><?php _e( 'Post Status:', 'seo-simple' ); ?></label>
				<select id="export-status" name="export_status">
					<option value="all"><?php _e( 'All Status', 'seo-simple' ); ?></option>
					<option value="publish"><?php _e( 'Published Only', 'seo-simple' ); ?></option>
					<option value="draft"><?php _e( 'Drafts Only', 'seo-simple' ); ?></option>
					<option value="private"><?php _e( 'Private Only', 'seo-simple' ); ?></option>
				</select>
			</div>
			
			<div class="form-row">
				<label><?php _e( 'Export Fields:', 'seo-simple' ); ?></label>
				<div class="checkbox-group">
					<label>
						<input type="checkbox" name="export_fields[]" value="post_id" checked />
						<?php _e( 'Post ID', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="post_title" checked />
						<?php _e( 'Post Title', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="post_url" checked />
						<?php _e( 'Post URL', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="seo_title" checked />
						<?php _e( 'SEO Title', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="seo_description" checked />
						<?php _e( 'SEO Description', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="focus_keyword" checked />
						<?php _e( 'Focus Keyword', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="seo_score" />
						<?php _e( 'SEO Score', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="meta_robots" />
						<?php _e( 'Meta Robots', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="canonical_url" />
						<?php _e( 'Canonical URL', 'seo-simple' ); ?>
					</label>
					<label>
						<input type="checkbox" name="export_fields[]" value="schema_type" />
						<?php _e( 'Schema Type', 'seo-simple' ); ?>
					</label>
				</div>
			</div>
			
			<div class="form-row">
				<label>
					<input type="checkbox" id="export-only-missing" name="export_only_missing" />
					<?php _e( 'Export only posts with missing SEO data', 'seo-simple' ); ?>
				</label>
			</div>
			
			<div class="form-row">
				<label>
					<input type="checkbox" id="export-include-backup" name="export_include_backup" />
					<?php _e( 'Include backup data (for rollback)', 'seo-simple' ); ?>
				</label>
			</div>
			
			<div class="form-actions">
				<button type="submit" class="button button-primary" id="seo-simple-export-btn">
					<?php _e( 'Export SEO Data', 'seo-simple' ); ?>
				</button>
			</div>
		</form>
	</div>
	
	<div class="seo-simple-import-section">
		<h3><?php _e( 'Import SEO Data', 'seo-simple' ); ?></h3>
		<p><?php _e( 'Import SEO data from a previously exported file. Supported formats: CSV, JSON, XLSX.', 'seo-simple' ); ?></p>
		
		<form id="seo-simple-import-form" class="seo-simple-form" enctype="multipart/form-data">
			<div class="form-row">
				<label for="import-file"><?php _e( 'Select File:', 'seo-simple' ); ?></label>
				<input type="file" id="import-file" name="import_file" accept=".csv,.json,.xlsx" required />
				<p class="description">
					<?php _e( 'Maximum file size: 10MB. Supported formats: CSV, JSON, XLSX', 'seo-simple' ); ?>
				</p>
			</div>
			
			<div class="form-row">
				<label for="import-mode"><?php _e( 'Import Mode:', 'seo-simple' ); ?></label>
				<select id="import-mode" name="import_mode">
					<option value="update"><?php _e( 'Update existing data', 'seo-simple' ); ?></option>
					<option value="overwrite"><?php _e( 'Overwrite all data', 'seo-simple' ); ?></option>
					<option value="merge"><?php _e( 'Merge with existing data', 'seo-simple' ); ?></option>
					<option value="skip_existing"><?php _e( 'Skip existing posts', 'seo-simple' ); ?></option>
				</select>
			</div>
			
			<div class="form-row">
				<label>
					<input type="checkbox" id="import-backup" name="import_backup" checked />
					<?php _e( 'Create backup before importing', 'seo-simple' ); ?>
				</label>
			</div>
			
			<div class="form-row">
				<label>
					<input type="checkbox" id="import-dry-run" name="import_dry_run" />
					<?php _e( 'Dry run (preview changes without applying)', 'seo-simple' ); ?>
				</label>
			</div>
			
			<div class="form-actions">
				<button type="submit" class="button button-primary" id="seo-simple-import-btn">
					<?php _e( 'Import SEO Data', 'seo-simple' ); ?>
				</button>
				<button type="button" class="button" id="seo-simple-preview-import-btn">
					<?php _e( 'Preview Import', 'seo-simple' ); ?>
				</button>
			</div>
		</form>
	</div>
	
	<div class="seo-simple-import-mapping" id="seo-simple-import-mapping" style="display: none;">
		<h4><?php _e( 'Field Mapping', 'seo-simple' ); ?></h4>
		<p><?php _e( 'Map the columns from your file to the corresponding SEO fields:', 'seo-simple' ); ?></p>
		<div id="mapping-container"></div>
		<div class="mapping-actions">
			<button type="button" class="button" id="seo-simple-auto-map-btn">
				<?php _e( 'Auto Map Fields', 'seo-simple' ); ?>
			</button>
			<button type="button" class="button button-primary" id="seo-simple-continue-import-btn">
				<?php _e( 'Continue Import', 'seo-simple' ); ?>
			</button>
		</div>
	</div>
	
	<div class="seo-simple-recent-activity">
		<h3><?php _e( 'Recent Activity', 'seo-simple' ); ?></h3>
		
		<div class="activity-tabs">
			<ul class="tab-nav">
				<li class="active"><a href="#tab-recent-exports"><?php _e( 'Recent Exports', 'seo-simple' ); ?></a></li>
				<li><a href="#tab-recent-imports"><?php _e( 'Recent Imports', 'seo-simple' ); ?></a></li>
			</ul>
			
			<div class="tab-content">
				<div id="tab-recent-exports" class="tab-pane active">
					<?php if ( ! empty( $recent_exports ) ) : ?>
						<table class="wp-list-table widefat striped">
							<thead>
								<tr>
									<th><?php _e( 'Date', 'seo-simple' ); ?></th>
									<th><?php _e( 'File', 'seo-simple' ); ?></th>
									<th><?php _e( 'Post Types', 'seo-simple' ); ?></th>
									<th><?php _e( 'Records', 'seo-simple' ); ?></th>
									<th><?php _e( 'Actions', 'seo-simple' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( array_slice( $recent_exports, 0, 10 ) as $export ) : ?>
									<tr>
										<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $export['date'] ) ) ); ?></td>
										<td><?php echo esc_html( basename( $export['file'] ) ); ?></td>
										<td><?php echo esc_html( implode( ', ', $export['post_types'] ) ); ?></td>
										<td><?php echo esc_html( $export['records'] ); ?></td>
										<td>
											<?php if ( file_exists( $export['file'] ) ) : ?>
												<a href="<?php echo esc_url( $export['download_url'] ); ?>" class="button button-small">
													<?php _e( 'Download', 'seo-simple' ); ?>
												</a>
											<?php else : ?>
												<span class="seo-simple-missing"><?php _e( 'File not found', 'seo-simple' ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php _e( 'No recent exports found.', 'seo-simple' ); ?></p>
					<?php endif; ?>
				</div>
				
				<div id="tab-recent-imports" class="tab-pane">
					<?php if ( ! empty( $recent_imports ) ) : ?>
						<table class="wp-list-table widefat striped">
							<thead>
								<tr>
									<th><?php _e( 'Date', 'seo-simple' ); ?></th>
									<th><?php _e( 'File', 'seo-simple' ); ?></th>
									<th><?php _e( 'Records', 'seo-simple' ); ?></th>
									<th><?php _e( 'Status', 'seo-simple' ); ?></th>
									<th><?php _e( 'Actions', 'seo-simple' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( array_slice( $recent_imports, 0, 10 ) as $import ) : ?>
									<tr>
										<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $import['date'] ) ) ); ?></td>
										<td><?php echo esc_html( basename( $import['file'] ) ); ?></td>
										<td><?php echo esc_html( $import['records'] ); ?></td>
										<td>
											<span class="import-status status-<?php echo esc_attr( $import['status'] ); ?>">
												<?php 
												$status_labels = array(
													'success' => __( 'Success', 'seo-simple' ),
													'partial' => __( 'Partial', 'seo-simple' ),
													'failed' => __( 'Failed', 'seo-simple' ),
												);
												echo esc_html( isset( $status_labels[$import['status']] ) ? $status_labels[$import['status']] : $import['status'] );
												?>
											</span>
										</td>
										<td>
											<?php if ( isset( $import['backup_file'] ) && file_exists( $import['backup_file'] ) ) : ?>
												<a href="<?php echo esc_url( $import['backup_download_url'] ); ?>" class="button button-small">
													<?php _e( 'Download Backup', 'seo-simple' ); ?>
												</a>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php _e( 'No recent imports found.', 'seo-simple' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="seo-simple-export-import-progress" id="seo-simple-export-import-progress" style="display: none;">
		<h4><?php _e( 'Progress', 'seo-simple' ); ?></h4>
		<div class="progress-bar">
			<div class="progress-bar-fill" style="width: 0%;"></div>
		</div>
		<div class="progress-text">
			<span class="current-record">0</span> / <span class="total-records">0</span> <?php _e( 'records processed', 'seo-simple' ); ?>
		</div>
		<div class="progress-status">
			<span class="status-text"></span>
		</div>
	</div>
	
	<div id="seo-simple-export-import-results" class="seo-simple-results" style="display: none;"></div>
</div>

<style>
.seo-simple-export-import-container {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 30px;
	margin-bottom: 30px;
}

@media (max-width: 768px) {
	.seo-simple-export-import-container {
		grid-template-columns: 1fr;
	}
}

.seo-simple-export-section,
.seo-simple-import-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	border-radius: 4px;
}

.seo-simple-form .form-row {
	margin-bottom: 15px;
}

.seo-simple-form label {
	display: block;
	margin-bottom: 5px;
	font-weight: 600;
}

.seo-simple-form .checkbox-group {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 10px;
}

.seo-simple-form .checkbox-group label {
	display: flex;
	align-items: center;
	font-weight: normal;
}

.seo-simple-form .checkbox-group input[type="checkbox"] {
	margin-right: 8px;
}

.seo-simple-form select,
.seo-simple-form input[type="file"] {
	width: 100%;
	max-width: 400px;
}

.seo-simple-form .form-actions {
	margin-top: 20px;
	padding-top: 15px;
	border-top: 1px solid #ddd;
}

.seo-simple-import-mapping {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin-bottom: 20px;
	border-radius: 4px;
}

.seo-simple-recent-activity {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	border-radius: 4px;
}

.activity-tabs .tab-nav {
	margin: 0;
	padding: 0;
	list-style: none;
	border-bottom: 1px solid #ddd;
	margin-bottom: 20px;
}

.activity-tabs .tab-nav li {
	display: inline-block;
	margin-right: 10px;
}

.activity-tabs .tab-nav li a {
	display: block;
	padding: 10px 15px;
	text-decoration: none;
	color: #555;
	border: 1px solid #ddd;
	border-bottom: none;
	background: #f8f9f9;
}

.activity-tabs .tab-nav li.active a {
	background: #fff;
	color: #23282d;
	border-bottom: 1px solid #fff;
	position: relative;
	top: 1px;
}

.tab-pane {
	display: none;
}

.tab-pane.active {
	display: block;
}

.import-status.status-success {
	color: #46b450;
	font-weight: bold;
}

.import-status.status-partial {
	color: #ffb900;
	font-weight: bold;
}

.import-status.status-failed {
	color: #dc3232;
	font-weight: bold;
}

.progress-bar {
	width: 100%;
	height: 20px;
	background: #f0f0f0;
	border-radius: 10px;
	overflow: hidden;
	margin-bottom: 10px;
}

.progress-bar-fill {
	height: 100%;
	background: #0073aa;
	transition: width 0.3s ease;
}

.seo-simple-results {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin-top: 20px;
	border-radius: 4px;
}

.seo-simple-results.success {
	border-left: 4px solid #46b450;
}

.seo-simple-results.error {
	border-left: 4px solid #dc3232;
}

.seo-simple-results.warning {
	border-left: 4px solid #ffb900;
}

.mapping-row {
	display: flex;
	align-items: center;
	margin-bottom: 10px;
	gap: 15px;
}

.mapping-row label {
	min-width: 150px;
	margin-bottom: 0;
	font-weight: normal;
}

.mapping-row select {
	flex: 1;
	max-width: 300px;
}

.mapping-actions {
	margin-top: 20px;
	padding-top: 15px;
	border-top: 1px solid #ddd;
}

.import-preview {
	margin-top: 20px;
	max-height: 300px;
	overflow: auto;
}

.import-preview table {
	margin-top: 10px;
	font-size: 12px;
}

.import-preview th,
.import-preview td {
	padding: 5px;
	border: 1px solid #ddd;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab switching for recent activity
	$('.activity-tabs .tab-nav a').on('click', function(e) {
		e.preventDefault();
		
		var $tabLink = $(this);
		var tabId = $tabLink.attr('href').substring(1);
		
		// Update nav
		$('.activity-tabs .tab-nav li').removeClass('active');
		$tabLink.parent().addClass('active');
		
		// Update content
		$('.tab-pane').removeClass('active');
		$('#' + tabId).addClass('active');
	});
});
</script>

<!-- Include export/import JavaScript -->
<script type="text/javascript" src="<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'admin/js/seo-simple-export-import.js'; ?>"></script>
