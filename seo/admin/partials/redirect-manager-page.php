<?php
/**
 * Redirect Manager admin page template.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize redirect manager
$redirect_manager = new Seo_Simple_Redirect_Manager();
// Prevent duplicate menu registration by removing the admin_menu hook
remove_action( 'admin_menu', array( $redirect_manager, 'add_admin_menu' ) );
$redirect_stats = $redirect_manager->get_redirect_stats();

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'redirects';
?>

<div class="wrap seo-simple-redirect-manager">
	<h1><?php esc_html_e( 'Smart Redirect Manager', 'seo-simple' ); ?></h1>
	
	<div class="seo-simple-redirect-stats">
		<div class="seo-simple-stat-card">
			<h3><?php esc_html_e( 'Total Redirects', 'seo-simple' ); ?></h3>
			<span class="seo-simple-stat-number"><?php echo esc_html( $redirect_stats['total_redirects'] ); ?></span>
		</div>
		<div class="seo-simple-stat-card">
			<h3><?php esc_html_e( '404 Errors Logged', 'seo-simple' ); ?></h3>
			<span class="seo-simple-stat-number"><?php echo esc_html( $redirect_stats['total_404s'] ); ?></span>
		</div>
		<div class="seo-simple-stat-card">
			<h3><?php esc_html_e( 'Pending Suggestions', 'seo-simple' ); ?></h3>
			<span class="seo-simple-stat-number"><?php echo esc_html( $redirect_stats['pending_suggestions'] ); ?></span>
		</div>
	</div>
	
	<h2 class="nav-tab-wrapper">
		<a href="?page=seo-simple-redirects&tab=redirects" class="nav-tab <?php echo $current_tab === 'redirects' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Redirects', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-redirects&tab=404s" class="nav-tab <?php echo $current_tab === '404s' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( '404 Monitor', 'seo-simple' ); ?>
		</a>
		<a href="?page=seo-simple-redirects&tab=suggestions" class="nav-tab <?php echo $current_tab === 'suggestions' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Suggestions', 'seo-simple' ); ?>
			<?php if ( $redirect_stats['pending_suggestions'] > 0 ) : ?>
				<span class="seo-simple-tab-badge"><?php echo esc_html( $redirect_stats['pending_suggestions'] ); ?></span>
			<?php endif; ?>
		</a>
		<a href="?page=seo-simple-redirects&tab=import" class="nav-tab <?php echo $current_tab === 'import' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Import/Export', 'seo-simple' ); ?>
		</a>
	</h2>
	
	<div class="seo-simple-tab-content">
			<?php
			switch ( $current_tab ) {
				case 'redirects':
					$redirect_manager->render_redirects_tab();
					break;
				case '404s':
					$redirect_manager->render_404s_tab( $redirect_manager );
					break;
				case 'suggestions':
					$redirect_manager->render_suggestions_tab( $redirect_manager );
					break;
				case 'import':
					$redirect_manager->render_import_tab( $redirect_manager );
					break;
			}
			?>
		</div>
</div>


<style>
.seo-simple-redirect-manager .seo-simple-redirect-stats {
	display: flex;
	gap: 20px;
	margin: 20px 0;
}

.seo-simple-redirect-manager .seo-simple-stat-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 15px;
	flex: 1;
	text-align: center;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.seo-simple-redirect-manager .seo-simple-stat-number {
	display: block;
	font-size: 2em;
	font-weight: 600;
	color: #0073aa;
}

.seo-simple-redirect-manager .seo-simple-tab-badge {
	background: #d63638;
	color: #fff;
	border-radius: 10px;
	padding: 2px 8px;
	font-size: 12px;
	margin-left: 5px;
}

.seo-simple-redirect-manager .tab-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
}

.seo-simple-redirect-manager .tab-actions {
	display: flex;
	gap: 10px;
}

.seo-simple-redirect-manager .confidence-indicator {
	height: 4px;
	background: #0073aa;
	margin-top: 5px;
}

.seo-simple-redirect-manager .confidence-score {
	font-weight: 600;
}

.seo-simple-redirect-manager .status-active {
	color: #00a32a;
}

.seo-simple-redirect-manager .status-inactive {
	color: #d63638;
}

.seo-simple-redirect-manager .seo-simple-modal {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0,0,0,0.5);
	z-index: 100000;
	display: flex;
	align-items: center;
	justify-content: center;
}

.seo-simple-redirect-manager .seo-simple-modal-content {
	background: #fff;
	border-radius: 4px;
	width: 500px;
	max-width: 90%;
}

.seo-simple-redirect-manager .seo-simple-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15px 20px;
	border-bottom: 1px solid #ddd;
}

.seo-simple-redirect-manager .seo-simple-modal-body {
	padding: 20px;
}

.seo-simple-redirect-manager .seo-simple-modal-close {
	background: none;
	border: none;
	font-size: 20px;
	cursor: pointer;
}

.seo-simple-redirect-manager .form-row {
	margin-bottom: 15px;
}

.seo-simple-redirect-manager .form-row label {
	display: block;
	margin-bottom: 5px;
	font-weight: 600;
}

.seo-simple-redirect-manager .form-row .description {
	font-size: 12px;
	color: #666;
	margin-top: 5px;
}

.seo-simple-redirect-manager .import-section,
.seo-simple-redirect-manager .export-section {
	margin-bottom: 30px;
}

.seo-simple-redirect-manager .import-method {
	margin-bottom: 30px;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.seo-simple-redirect-manager .seo-simple-top-404s {
	margin-bottom: 30px;
}

.seo-simple-redirect-manager .tab-footer {
	margin-top: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Modal handling
	$('#seo-simple-add-redirect').on('click', function() {
		$('#seo-simple-redirect-modal').show();
		$('#seo-simple-redirect-form')[0].reset();
		$('#seo-simple-redirect-form').removeData('edit-id');
		$('#seo-simple-redirect-modal h3').text('<?php esc_html_e( "Add Redirect", "seo-simple" ); ?>');
	});
	
	$('.seo-simple-modal-close, .seo-simple-modal-cancel').on('click', function() {
		$('#seo-simple-redirect-modal').hide();
		$('#seo-simple-redirect-form')[0].reset();
		$('#seo-simple-redirect-form').removeData('edit-id');
	});
	
	// Handle redirect form submission
	$('#seo-simple-redirect-form').on('submit', function(e) {
		e.preventDefault();
		
		var formData = $(this).serialize();
		var redirectId = $('#seo-simple-redirect-form').data('edit-id');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData + '&action=seo_simple_save_redirect&nonce=<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>' + (redirectId ? '&id=' + redirectId : ''),
			success: function(response) {
				if (response.success) {
					$('#seo-simple-redirect-modal').hide();
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle delete redirect
	$(document).on('click', '.seo-simple-delete-redirect', function() {
		if (confirm('<?php esc_html_e( "Are you sure you want to delete this redirect?", "seo-simple" ); ?>')) {
			var redirectId = $(this).data('id');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'seo_simple_delete_redirect',
					id: redirectId,
					nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
					}
				},
				error: function() {
					alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			});
		}
	});
	
	// Handle edit redirect
	$(document).on('click', '.seo-simple-edit-redirect', function() {
		var redirectId = $(this).data('id');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_edit_redirect',
				id: redirectId,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					var redirect = response.data.redirect;
					$('#seo-simple-redirect-form').data('edit-id', redirect.id);
					$('#seo-simple-from-url').val(redirect.from_url);
					$('#seo-simple-to-url').val(redirect.to_url);
					$('#seo-simple-redirect-type').val(redirect.redirect_type);
					$('#seo-simple-redirect-status').prop('checked', redirect.status === 1);
					$('#seo-simple-redirect-modal h3').text('<?php esc_html_e( "Edit Redirect", "seo-simple" ); ?>');
					$('#seo-simple-redirect-modal').show();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle suggest redirect
	$(document).on('click', '.seo-simple-suggest-redirect', function() {
		var url = $(this).data('url');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_suggest_redirect',
				from_url: url,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					$('#seo-simple-redirect-modal').show();
					$('#seo-simple-from-url').val(url);
					$('#seo-simple-to-url').val(response.data.suggested_url);
					$('#seo-simple-redirect-modal h3').text('<?php esc_html_e( "Add Redirect (Suggested)", "seo-simple" ); ?>');
				} else {
					alert(response.data.message || '<?php esc_html_e( "No suitable redirect found.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle create single redirect from suggestion
	$(document).on('click', '.seo-simple-create-redirect', function() {
		var suggestionId = $(this).data('id');
		var fromUrl = $(this).data('from');
		var toUrl = $(this).data('to');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_create_redirect_from_suggestion',
				suggestion_id: suggestionId,
				from_url: fromUrl,
				to_url: toUrl,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle create all redirects
	$('#seo-simple-create-all-redirects, #seo-simple-create-selected-redirects').on('click', function() {
		var createAll = $(this).attr('id') === 'seo-simple-create-all-redirects';
		var suggestions = [];
		
		if (createAll) {
			$('.seo-simple-suggestion-checkbox').each(function() {
				suggestions.push($(this).val());
			});
		} else {
			$('.seo-simple-suggestion-checkbox:checked').each(function() {
				suggestions.push($(this).val());
			});
		}
		
		if (suggestions.length === 0) {
			alert('<?php esc_html_e( "Please select at least one suggestion.", "seo-simple" ); ?>');
			return;
		}
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_bulk_create_redirects',
				suggestions: suggestions,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle select all checkbox
	$('#seo-simple-select-all-suggestions').on('change', function() {
		$('.seo-simple-suggestion-checkbox').prop('checked', $(this).prop('checked'));
	});
	
	// Handle dismiss suggestion
	$(document).on('click', '.seo-simple-dismiss-suggestion', function() {
		var suggestionId = $(this).data('id');
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_dismiss_suggestion',
				suggestion_id: suggestionId,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle import CSV
	$('#seo-simple-import-csv-form').on('submit', function(e) {
		e.preventDefault();
		
		var formData = new FormData();
		formData.append('action', 'seo_simple_import_redirects');
		formData.append('source', 'csv');
		formData.append('nonce', '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>');
		formData.append('csv_file', $('#seo-simple-import-csv-form input[type="file"]')[0].files[0]);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle import .htaccess
	$('#seo-simple-import-htaccess-form').on('submit', function(e) {
		e.preventDefault();
		
		var htaccessContent = $(this).find('textarea').val();
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_import_redirects',
				source: 'htaccess',
				data: htaccessContent,
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			}
		});
	});
	
	// Handle export
	$('#seo-simple-export-form').on('submit', function(e) {
		e.preventDefault();
		
		window.location.href = ajaxurl + '?action=seo_simple_export_redirects&nonce=<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>';
	});
	
	// Handle clear 404 logs
	$('#seo-simple-clear-404-logs').on('click', function() {
		if (confirm('<?php esc_html_e( "Are you sure you want to clear all 404 error logs? This action cannot be undone.", "seo-simple" ); ?>')) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'seo_simple_clear_404_logs',
					nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message || '<?php esc_html_e( "404 logs cleared successfully.", "seo-simple" ); ?>');
						location.reload();
					} else {
						alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
					}
				},
				error: function() {
					alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			});
		}
	});
	
	// Handle refresh suggestions
	$('#seo-simple-refresh-suggestions').on('click', function() {
		var $button = $(this);
		$button.prop('disabled', true);
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'seo_simple_refresh_suggestions',
				nonce: '<?php echo wp_create_nonce( "seo_simple_redirect_nonce" ); ?>'
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message || '<?php esc_html_e( "Suggestions refreshed successfully.", "seo-simple" ); ?>');
					location.reload();
				} else {
					alert(response.data.message || '<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( "An error occurred.", "seo-simple" ); ?>');
			},
			complete: function() {
				$button.prop('disabled', false);
			}
		});
	});
});
</script>