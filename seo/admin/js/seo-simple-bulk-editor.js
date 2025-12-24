/**
 * Bulk SEO editor JavaScript.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize bulk editor
		initBulkEditor();
	});

	/**
	 * Initialize bulk editor functionality.
	 */
	function initBulkEditor() {
		// Handle bulk edit form submission
		$('#seo-simple-bulk-edit-form').on('submit', handleBulkEdit);
		
		// Handle bulk analyze button
		$('#seo-simple-bulk-analyze').on('click', handleBulkAnalyze);
		
		// Handle bulk optimize button
		$('#seo-simple-bulk-optimize').on('click', handleBulkOptimize);
		
		// Handle post type selection
		$('#seo-simple-post-type').on('change', handlePostTypeChange);
		
		// Handle filter changes
		$('#seo-simple-filter').on('change', handleFilterChange);
		
		// Handle select all checkbox
		$('#seo-simple-select-all').on('change', handleSelectAll);
		
		// Handle individual post checkboxes
		$('.seo-simple-post-checkbox').on('change', handlePostCheckboxChange);
		
		// Handle export buttons
		$('#seo-simple-export-csv, #seo-simple-export-json').on('click', handleExport);
		
		// Handle import button
		$('#seo-simple-import').on('click', handleImport);
		
		// Handle file selection for import
		$('#seo-simple-import-file').on('change', handleImportFileChange);
		
		// Initialize tooltips
		initTooltips();
	}

	/**
	 * Handle bulk edit form submission.
	 *
	 * @param {Event} e The form submission event.
	 */
	function handleBulkEdit(e) {
		e.preventDefault();
		
		var form = $(this);
		var postIds = getSelectedPostIds();
		
		if (postIds.length === 0) {
			alert(seo_simple_bulk_editor.text_no_posts);
			return;
		}
		
		if (!confirm(seo_simple_bulk_editor.text_confirm_bulk)) {
			return;
		}
		
		// Show loading state
		form.find('button[type="submit"]').prop('disabled', true).text(seo_simple_bulk_editor.text_updating);
		$('#seo-simple-bulk-results').html('<div class="spinner is-active"></div> ' + seo_simple_bulk_editor.text_updating).show();
		
		// Get form data
		var formData = new FormData(form[0]);
		formData.append('action', 'seo_simple_bulk_edit');
		formData.append('nonce', seo_simple_bulk_editor.nonce_bulk_edit);
		formData.append('post_ids', JSON.stringify(postIds));
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					$('#seo-simple-bulk-results').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
					
					// Refresh post list after a delay
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || seo_simple_bulk_editor.text_error) + '</p></div>');
				}
			},
			error: function() {
				$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + seo_simple_bulk_editor.text_error + '</p></div>');
			},
			complete: function() {
				// Reset button state
				form.find('button[type="submit"]').prop('disabled', false).text(form.data('original-text') || 'Update SEO');
			}
		});
	}

	/**
	 * Handle bulk analyze button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleBulkAnalyze(e) {
		e.preventDefault();
		
		var postIds = getSelectedPostIds();
		
		if (postIds.length === 0) {
			alert(seo_simple_bulk_editor.text_no_posts);
			return;
		}
		
		if (!confirm(seo_simple_bulk_editor.text_confirm_bulk)) {
			return;
		}
		
		// Show loading state
		$(this).prop('disabled', true).text(seo_simple_bulk_editor.text_analyzing);
		$('#seo-simple-bulk-results').html('<div class="spinner is-active"></div> ' + seo_simple_bulk_editor.text_analyzing).show();
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: {
				action: 'seo_simple_bulk_analyze',
				nonce: seo_simple_bulk_editor.nonce_bulk_analyze,
				post_ids: postIds
			},
			success: function(response) {
				if (response.success) {
					$('#seo-simple-bulk-results').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
					
					// Show analysis results if available
					if (response.data.results) {
						displayAnalysisResults(response.data.results);
					}
					
					// Refresh post list after a delay
					setTimeout(function() {
						location.reload();
					}, 3000);
				} else {
					$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || seo_simple_bulk_editor.text_error) + '</p></div>');
				}
			},
			error: function() {
				$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + seo_simple_bulk_editor.text_error + '</p></div>');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-bulk-analyze').prop('disabled', false).text('Analyze SEO');
			}
		});
	}

	/**
	 * Handle bulk optimize button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleBulkOptimize(e) {
		e.preventDefault();
		
		var postIds = getSelectedPostIds();
		
		if (postIds.length === 0) {
			alert(seo_simple_bulk_editor.text_no_posts);
			return;
		}
		
		if (!confirm(seo_simple_bulk_editor.text_confirm_bulk)) {
			return;
		}
		
		// Show loading state
		$(this).prop('disabled', true).text(seo_simple_bulk_editor.text_optimizing);
		$('#seo-simple-bulk-results').html('<div class="spinner is-active"></div> ' + seo_simple_bulk_editor.text_optimizing).show();
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: {
				action: 'seo_simple_bulk_optimize',
				nonce: seo_simple_bulk_editor.nonce_bulk_optimize,
				post_ids: postIds
			},
			success: function(response) {
				if (response.success) {
					$('#seo-simple-bulk-results').html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
					
					// Refresh post list after a delay
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || seo_simple_bulk_editor.text_error) + '</p></div>');
				}
			},
			error: function() {
				$('#seo-simple-bulk-results').html('<div class="notice notice-error is-dismissible"><p>' + seo_simple_bulk_editor.text_error + '</p></div>');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-bulk-optimize').prop('disabled', false).text('Auto Optimize SEO');
			}
		});
	}

	/**
	 * Handle post type selection change.
	 *
	 * @param {Event} e The change event.
	 */
	function handlePostTypeChange(e) {
		var postType = $(this).val();
		
		// Reload the page with the new post type
		var url = new URL(window.location);
		url.searchParams.set('post_type', postType);
		url.searchParams.delete('paged'); // Reset pagination
		window.location.href = url.toString();
	}

	/**
	 * Handle filter selection change.
	 *
	 * @param {Event} e The change event.
	 */
	function handleFilterChange(e) {
		var filter = $(this).val();
		
		// Show/hide posts based on filter
		if (filter === 'all') {
			$('.seo-simple-post-row').show();
		} else {
			$('.seo-simple-post-row').hide();
			
			switch (filter) {
				case 'missing_title':
					$('.seo-simple-post-row[data-has-title="0"]').show();
					break;
				case 'missing_description':
					$('.seo-simple-post-row[data-has-description="0"]').show();
					break;
				case 'missing_keyword':
					$('.seo-simple-post-row[data-has-keyword="0"]').show();
					break;
				case 'low_score':
					$('.seo-simple-post-row[data-score]').filter(function() {
						return parseInt($(this).data('score')) < 50;
					}).show();
					break;
			}
		}
	}

	/**
	 * Handle select all checkbox change.
	 *
	 * @param {Event} e The change event.
	 */
	function handleSelectAll(e) {
		var isChecked = $(this).prop('checked');
		$('.seo-simple-post-checkbox').prop('checked', isChecked);
		updateSelectedCount();
	}

	/**
	 * Handle individual post checkbox change.
	 *
	 * @param {Event} e The change event.
	 */
	function handlePostCheckboxChange(e) {
		updateSelectAllState();
		updateSelectedCount();
	}

	/**
	 * Handle export button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleExport(e) {
		e.preventDefault();
		
		var format = $(this).data('format');
		var postType = $('#seo-simple-post-type').val();
		
		// Show loading state
		$(this).prop('disabled', true).text('Exporting...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: {
				action: 'seo_simple_export_seo',
				nonce: seo_simple_bulk_editor.nonce_export,
				post_type: postType,
				format: format
			},
			success: function(response) {
				if (response.success) {
					// Download the file
					window.location.href = response.data.file_url;
				} else {
					alert(response.data.message || 'Export failed.');
				}
			},
			error: function() {
				alert('Request failed.');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-export-csv').prop('disabled', false).text('Export CSV');
				$('#seo-simple-export-json').prop('disabled', false).text('Export JSON');
			}
		});
	}

	/**
	 * Handle import button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleImport(e) {
		e.preventDefault();
		$('#seo-simple-import-file').click();
	}

	/**
	 * Handle import file selection change.
	 *
	 * @param {Event} e The change event.
	 */
	function handleImportFileChange(e) {
		var file = this.files[0];
		
		if (!file) {
			return;
		}
		
		// Check file type
		if (!file.name.match(/\.(csv|json)$/i)) {
			alert('Please select a CSV or JSON file.');
			return;
		}
		
		// Confirm import
		if (!confirm('This will overwrite existing SEO data. Are you sure?')) {
			return;
		}
		
		// Create form data
		var formData = new FormData();
		formData.append('action', 'seo_simple_import_seo');
		formData.append('nonce', seo_simple_bulk_editor.nonce_import);
		formData.append('import_file', file);
		
		// Show loading state
		$('#seo-simple-import').prop('disabled', true).text('Importing...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					// Reload page to show changes
					location.reload();
				} else {
					alert(response.data.message || 'Import failed.');
				}
			},
			error: function() {
				alert('Request failed.');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-import').prop('disabled', false).text('Import');
				// Clear file input
				$('#seo-simple-import-file').val('');
			}
		});
	}

	/**
	 * Get selected post IDs.
	 *
	 * @return {Array} The selected post IDs.
	 */
	function getSelectedPostIds() {
		var postIds = [];
		$('.seo-simple-post-checkbox:checked').each(function() {
			postIds.push(parseInt($(this).val()));
		});
		return postIds;
	}

	/**
	 * Update select all checkbox state.
	 */
	function updateSelectAllState() {
		var totalCheckboxes = $('.seo-simple-post-checkbox').length;
		var checkedCheckboxes = $('.seo-simple-post-checkbox:checked').length;
		
		$('#seo-simple-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
	}

	/**
	 * Update selected count display.
	 */
	function updateSelectedCount() {
		var count = $('.seo-simple-post-checkbox:checked').length;
		$('#seo-simple-selected-count').text(count);
	}

	/**
	 * Display analysis results.
	 *
	 * @param {Object} results The analysis results.
	 */
	function displayAnalysisResults(results) {
		var html = '<div class="seo-simple-analysis-results"><h4>' + seo_simple_bulk_editor.text_analysis_results + '</h4>';
		
		for (var postId in results) {
			var result = results[postId];
			var postTitle = $('.seo-simple-post-row[data-post-id="' + postId + '"] .seo-simple-post-title').text();
			
			html += '<div class="seo-simple-analysis-result">';
			html += '<h5>' + postTitle + '</h5>';
			html += '<div class="seo-simple-score-display">';
			html += '<div class="seo-simple-score-circle seo-simple-score-' + getScoreClass(result.score) + '">' + result.score + '</div>';
			html += '</div>';
			
			for (var key in result) {
				if (key !== 'score') {
					var item = result[key];
					var statusClass = 'seo-simple-status-' + item.status;
					
					html += '<div class="seo-simple-analysis-item ' + statusClass + '">';
					html += '<div class="seo-simple-analysis-status">' + getStatusIcon(item.status) + '</div>';
					html += '<div class="seo-simple-analysis-message">' + item.message + '</div>';
					html += '</div>';
				}
			}
			
			html += '</div>';
		}
		
		html += '</div>';
		
		$('#seo-simple-bulk-results').append(html);
	}

	/**
	 * Get score class based on score value.
	 *
	 * @param {number} score The score value.
	 * @return {string} The score class.
	 */
	function getScoreClass(score) {
		if (score >= 80) {
			return 'good';
		} else if (score >= 50) {
			return 'warning';
		} else {
			return 'bad';
		}
	}

	/**
	 * Get status icon HTML.
	 *
	 * @param {string} status The status.
	 * @return {string} The icon HTML.
	 */
	function getStatusIcon(status) {
		switch (status) {
			case 'good':
				return '<span class="dashicons dashicons-yes-alt"></span>';
			case 'warning':
				return '<span class="dashicons dashicons-warning"></span>';
			case 'error':
				return '<span class="dashicons dashicons-no-alt"></span>';
			default:
				return '<span class="dashicons dashicons-info"></span>';
		}
	}

	/**
	 * Initialize tooltips.
	 */
	function initTooltips() {
		// Initialize tooltips if the tooltip library is available
		if ($.tooltip) {
			$('.seo-simple-tooltip').tooltip();
		}
	}

})(jQuery);