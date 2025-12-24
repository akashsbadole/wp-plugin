/**
 * Export/Import functionality JavaScript.
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize export/import functionality
		initExportImport();
	});

	/**
	 * Initialize export/import functionality.
	 */
	function initExportImport() {
		// Handle export form submission
		$('#seo-simple-export-form').on('submit', handleExport);
		
		// Handle import form submission
		$('#seo-simple-import-form').on('submit', handleImport);
		
		// Handle preview import button
		$('#seo-simple-preview-import-btn').on('click', handlePreviewImport);
		
		// Handle auto map fields button
		$('#seo-simple-auto-map-btn').on('click', handleAutoMapFields);
		
		// Handle continue import button
		$('#seo-simple-continue-import-btn').on('click', handleContinueImport);
		
		// Handle file input change for import
		$('#import-file').on('change', handleFileChange);
	}

	/**
	 * Handle export form submission.
	 *
	 * @param {Event} e The form submission event.
	 */
	function handleExport(e) {
		e.preventDefault();
		
		var form = $(this);
		var formData = new FormData(form[0]);
		
		// Add action and nonce
		formData.append('action', 'seo_simple_export_seo');
		formData.append('nonce', seo_simple_bulk_editor.nonce_export);
		
		// Show loading state
		$('#seo-simple-export-btn').prop('disabled', true).text('Exporting...');
		$('#seo-simple-export-import-progress').show();
		updateProgress(0, 0, 'Starting export...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						updateProgress(percentComplete * 100, 0, 'Exporting...');
					}
				}, false);
				return xhr;
			},
			success: function(response) {
				if (response.success) {
					updateProgress(100, 0, 'Export completed successfully!');
					
					// Show success message
					showResults('success', response.data.message);
					
					// Download the file
					window.location.href = response.data.file_url;
					
					// Update recent exports
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					showResults('error', response.data.message || 'Export failed.');
				}
			},
			error: function(xhr, status, error) {
				showResults('error', 'Request failed: ' + error);
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-export-btn').prop('disabled', false).text('Export SEO Data');
				setTimeout(function() {
					$('#seo-simple-export-import-progress').hide();
				}, 2000);
			}
		});
	}

	/**
	 * Handle import form submission.
	 *
	 * @param {Event} e The form submission event.
	 */
	function handleImport(e) {
		e.preventDefault();
		
		var form = $(this);
		var formData = new FormData(form[0]);
		var fileInput = $('#import-file')[0];
		
		// Check if file is selected
		if (!fileInput.files || !fileInput.files[0]) {
			showResults('error', 'Please select a file to import.');
			return;
		}
		
		// Check file type
		var file = fileInput.files[0];
		var allowedTypes = ['csv', 'json', 'xlsx'];
		var fileExtension = file.name.split('.').pop().toLowerCase();
		
		if (allowedTypes.indexOf(fileExtension) === -1) {
			showResults('error', 'Invalid file type. Please select a CSV, JSON, or XLSX file.');
			return;
		}
		
		// Check file size (10MB max)
		if (file.size > 10 * 1024 * 1024) {
			showResults('error', 'File size exceeds 10MB limit.');
			return;
		}
		
		// Add action and nonce
		formData.append('action', 'seo_simple_import_seo');
		formData.append('nonce', seo_simple_bulk_editor.nonce_import);
		
		// Show loading state
		$('#seo-simple-import-btn').prop('disabled', true).text('Importing...');
		$('#seo-simple-export-import-progress').show();
		updateProgress(0, 0, 'Starting import...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						updateProgress(percentComplete * 100, 0, 'Uploading file...');
					}
				}, false);
				return xhr;
			},
			success: function(response) {
				if (response.success) {
					updateProgress(100, response.data.result.processed, 'Import completed successfully!');
					
					// Show success message
					showResults('success', response.data.message);
					
					// Update recent imports
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					showResults('error', response.data.message || 'Import failed.');
				}
			},
			error: function(xhr, status, error) {
				showResults('error', 'Request failed: ' + error);
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-import-btn').prop('disabled', false).text('Import SEO Data');
				setTimeout(function() {
					$('#seo-simple-export-import-progress').hide();
				}, 2000);
			}
		});
	}

	/**
	 * Handle preview import button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handlePreviewImport(e) {
		e.preventDefault();
		
		var fileInput = $('#import-file')[0];
		
		// Check if file is selected
		if (!fileInput.files || !fileInput.files[0]) {
			showResults('error', 'Please select a file to preview.');
			return;
		}
		
		var formData = new FormData();
		formData.append('action', 'seo_simple_preview_import');
		formData.append('nonce', seo_simple_bulk_editor.nonce_import);
		formData.append('import_file', fileInput.files[0]);
		
		// Show loading state
		$(this).prop('disabled', true).text('Previewing...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					// Show field mapping interface
					showFieldMapping(response.data.columns, response.data.preview);
				} else {
					showResults('error', response.data.message || 'Preview failed.');
				}
			},
			error: function() {
				showResults('error', 'Request failed.');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-preview-import-btn').prop('disabled', false).text('Preview Import');
			}
		});
	}

	/**
	 * Handle auto map fields button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleAutoMapFields(e) {
		e.preventDefault();
		
		// Get all available fields
		var availableFields = [
			'post_id', 'post_title', 'post_url', 'seo_title', 
			'seo_description', 'focus_keyword', 'seo_score', 
			'meta_robots', 'canonical_url', 'schema_type'
		];
		
		// Get all column selects
		$('#mapping-container select').each(function() {
			var $select = $(this);
			var columnName = $select.data('column');
			
			// Try to find matching field
			var matchedField = null;
			
			// Direct match
			if (availableFields.indexOf(columnName.toLowerCase()) !== -1) {
				matchedField = columnName.toLowerCase();
			} else {
				// Fuzzy matching
				for (var i = 0; i < availableFields.length; i++) {
					var field = availableFields[i];
					if (columnName.toLowerCase().indexOf(field) !== -1 || 
						field.indexOf(columnName.toLowerCase()) !== -1) {
						matchedField = field;
						break;
					}
				}
			}
			
			if (matchedField) {
				$select.val(matchedField);
			}
		});
	}

	/**
	 * Handle continue import button click.
	 *
	 * @param {Event} e The click event.
	 */
	function handleContinueImport(e) {
		e.preventDefault();
		
		// Get field mappings
		var mappings = {};
		$('#mapping-container select').each(function() {
			var column = $(this).data('column');
			var field = $(this).val();
			if (field) {
				mappings[column] = field;
			}
		});
		
		// Get import settings
		var importMode = $('#import-mode').val();
		var createBackup = $('#import-backup').is(':checked');
		var dryRun = $('#import-dry-run').is(':checked');
		
		var formData = new FormData();
		formData.append('action', 'seo_simple_process_import');
		formData.append('nonce', seo_simple_bulk_editor.nonce_import);
		formData.append('import_file', $('#import-file')[0].files[0]);
		formData.append('mappings', JSON.stringify(mappings));
		formData.append('import_mode', importMode);
		formData.append('create_backup', createBackup ? '1' : '0');
		formData.append('dry_run', dryRun ? '1' : '0');
		
		// Show loading state
		$(this).prop('disabled', true).text('Processing...');
		$('#seo-simple-export-import-progress').show();
		updateProgress(0, 0, 'Processing import...');
		
		// Send request
		$.ajax({
			url: seo_simple_bulk_editor.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					updateProgress(100, response.data.result.processed, 'Import completed successfully!');
					
					// Show success message
					showResults('success', response.data.message);
					
					// Hide mapping interface
					$('#seo-simple-import-mapping').hide();
					
					// Update recent imports
					setTimeout(function() {
						location.reload();
					}, 2000);
				} else {
					showResults('error', response.data.message || 'Import failed.');
				}
			},
			error: function() {
				showResults('error', 'Request failed.');
			},
			complete: function() {
				// Reset button state
				$('#seo-simple-continue-import-btn').prop('disabled', false).text('Continue Import');
				setTimeout(function() {
					$('#seo-simple-export-import-progress').hide();
				}, 2000);
			}
		});
	}

	/**
	 * Handle file input change.
	 *
	 * @param {Event} e The change event.
	 */
	function handleFileChange(e) {
		var file = e.target.files[0];
		if (file) {
			// Show file info
			var fileSize = (file.size / 1024 / 1024).toFixed(2);
			var fileInfo = '<p>Selected file: ' + file.name + ' (' + fileSize + ' MB)</p>';
			$('#seo-simple-import-form .description').after(fileInfo);
		}
	}

	/**
	 * Show field mapping interface.
	 *
	 * @param {Array} columns The columns from the file.
	 * @param {Array} preview The preview data.
	 */
	function showFieldMapping(columns, preview) {
		var $container = $('#mapping-container');
		$container.empty();
		
		// Create mapping for each column
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			var $mapping = $('<div class="mapping-row"></div>');
			
			$mapping.append('<label>' + column + ':</label>');
			
			var $select = $('<select data-column="' + column + '"></select>');
			$select.append('<option value="">-- Do not import --</option>');
			$select.append('<option value="post_id">Post ID</option>');
			$select.append('<option value="post_title">Post Title</option>');
			$select.append('<option value="post_url">Post URL</option>');
			$select.append('<option value="seo_title">SEO Title</option>');
			$select.append('<option value="seo_description">SEO Description</option>');
			$select.append('<option value="focus_keyword">Focus Keyword</option>');
			$select.append('<option value="seo_score">SEO Score</option>');
			$select.append('<option value="meta_robots">Meta Robots</option>');
			$select.append('<option value="canonical_url">Canonical URL</option>');
			$select.append('<option value="schema_type">Schema Type</option>');
			
			$mapping.append($select);
			$container.append($mapping);
		}
		
		// Show preview data
		if (preview && preview.length > 0) {
			var $preview = $('<div class="import-preview"><h5>Preview:</h5><table class="wp-list-table widefat striped"><thead></thead><tbody></tbody></table></div>');
			
			// Add header
			var $header = $preview.find('thead');
			var $headerRow = $('<tr></tr>');
			for (var i = 0; i < columns.length; i++) {
				$headerRow.append('<th>' + columns[i] + '</th>');
			}
			$header.append($headerRow);
			
			// Add preview rows
			var $body = $preview.find('tbody');
			for (var i = 0; i < Math.min(5, preview.length); i++) {
				var $row = $('<tr></tr>');
				for (var j = 0; j < preview[i].length; j++) {
					$row.append('<td>' + preview[i][j] + '</td>');
				}
				$body.append($row);
			}
			
			$container.append($preview);
		}
		
		// Show mapping interface
		$('#seo-simple-import-mapping').show();
	}

	/**
	 * Update progress bar.
	 *
	 * @param {number} percent The completion percentage.
	 * @param {number} records The number of records processed.
	 * @param {string} status The status message.
	 */
	function updateProgress(percent, records, status) {
		$('.progress-bar-fill').css('width', percent + '%');
		$('.current-record').text(records);
		$('.total-records').text(records || '...');
		$('.status-text').text(status);
	}

	/**
	 * Show results message.
	 *
	 * @param {string} type The message type (success, error, warning).
	 * @param {string} message The message to display.
	 */
	function showResults(type, message) {
		var $results = $('#seo-simple-export-import-results');
		$results.removeClass('success error warning').addClass(type);
		$results.html('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
		$results.show();
		
		// Auto-hide after 5 seconds
		setTimeout(function() {
			$results.fadeOut();
		}, 5000);
	}

})(jQuery);