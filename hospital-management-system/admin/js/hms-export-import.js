/**
 * HMS Export & Import JavaScript
 * 
 * Handles client-side functionality for data export and import operations.
 */

jQuery(document).ready(function($) {
    'use strict';

    // Global variables
    let currentExportId = null;
    let currentImportId = null;
    let exportProgressInterval = null;
    let importProgressInterval = null;

    // Initialize the export-import functionality
    init();

    function init() {
        bindEvents();
        loadExportModules();
        setupFileUpload();
    }

    function bindEvents() {
        // Tab switching
        $('.nav-tab').on('click', handleTabSwitch);
        
        // Export form events
        $('#hms-export-form').on('submit', handleExportSubmit);
        $('#select-all-modules').on('click', selectAllModules);
        $('#deselect-all-modules').on('click', deselectAllModules);
        $('#date-range').on('change', handleDateRangeChange);
        $('#download-export').on('click', downloadExportFile);
        
        // Import form events
        $('#hms-import-upload-form').on('submit', handleFileUpload);
        $('#hms-import-validate-form').on('submit', handleFileValidation);
        $('#hms-import-process-form').on('submit', handleImportProcess);
        
        // Step navigation
        $('#back-to-upload').on('click', () => showImportStep(1));
        $('#back-to-validation').on('click', () => showImportStep(2));
        $('#proceed-to-import').on('click', () => showImportStep(3));
        
        // File selection
        $('#select-file-btn').on('click', () => $('#import-file').click());
        $('#import-file').on('change', handleFileSelection);
        $('#remove-file').on('click', clearFileSelection);
        
        // Target module change
        $('#target-module').on('change', function() {
            $('#validate-file-btn').prop('disabled', !$(this).val());
        });
    }

    function handleTabSwitch(e) {
        e.preventDefault();
        
        const targetTab = $(this).attr('href');
        
        // Update tab navigation
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Update tab content
        $('.tab-content').removeClass('active');
        $(targetTab).addClass('active');
    }

    function handleDateRangeChange() {
        if ($(this).val() === 'custom') {
            $('#custom-date-range').show();
        } else {
            $('#custom-date-range').hide();
        }
    }

    function loadExportModules() {
        showLoading('#export-modules-grid', 'Loading available modules...');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'hms_get_export_modules',
                nonce: hms_admin_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayExportModules(response.data.modules);
                    populateTargetModules(response.data.modules);
                } else {
                    showError('Failed to load export modules: ' + response.data);
                }
            },
            error: function() {
                showError('Failed to load export modules. Please try again.');
            }
        });
    }

    function displayExportModules(modules) {
        const $grid = $('#export-modules-grid');
        $grid.empty();
        
        $.each(modules, function(key, module) {
            const $card = createModuleCard(key, module);
            $grid.append($card);
        });
    }

    function createModuleCard(key, module) {
        const $card = $('<div class="module-card">');
        const $label = $('<label>');
        const $checkbox = $('<input type="checkbox" name="modules[]" value="' + key + '">');
        const $info = $('<div class="module-info">');
        const $title = $('<h4>').text(module.name);
        const $desc = $('<p>').text(module.description);
        
        $info.append($title, $desc);
        $label.append($checkbox, $info);
        $card.append($label);
        
        $checkbox.on('change', function() {
            if ($(this).is(':checked')) {
                $card.addClass('selected');
            } else {
                $card.removeClass('selected');
            }
        });
        
        return $card;
    }

    function populateTargetModules(modules) {
        const $select = $('#target-module');
        $select.find('option:not(:first)').remove();
        
        $.each(modules, function(key, module) {
            $select.append('<option value="' + key + '">' + module.name + '</option>');
        });
    }

    function selectAllModules() {
        $('#export-modules-grid input[type="checkbox"]').prop('checked', true).trigger('change');
    }

    function deselectAllModules() {
        $('#export-modules-grid input[type="checkbox"]').prop('checked', false).trigger('change');
    }

    function handleExportSubmit(e) {
        e.preventDefault();
        
        const selectedModules = getSelectedModules();
        
        if (selectedModules.length === 0) {
            showError('Please select at least one module to export.');
            return;
        }
        
        // First get export statistics
        const statisticsData = {
            action: 'hms_get_export_statistics',
            nonce: hms_admin_vars.nonce,
            modules: selectedModules,
            date_range: $('#date-range').val(),
            start_date: $('#start-date').val(),
            end_date: $('#end-date').val()
        };
        
        const $button = $('#start-export');
        $button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: statisticsData,
            success: function(response) {
                if (response.success) {
                    showExportStatistics(response.data);
                    
                    // Proceed with export
                    const formData = {
                        action: 'hms_start_data_export',
                        nonce: hms_admin_vars.nonce,
                        modules: selectedModules,
                        format: $('#export-format').val(),
                        date_range: $('#date-range').val(),
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val()
                    };
                    
                    startExport(formData);
                } else {
                    showError('Failed to get export statistics: ' + response.data);
                    $button.prop('disabled', false).removeClass('loading');
                }
            },
            error: function() {
                showError('Failed to get export statistics. Please try again.');
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }

    function showExportStatistics(statistics) {
        let statisticsHtml = '<div class="export-statistics">';
        statisticsHtml += '<h4>Export Preview</h4>';
        statisticsHtml += '<p><strong>Total Records:</strong> ' + statistics.total_records + '</p>';
        statisticsHtml += '<p><strong>Estimated Size:</strong> ' + statistics.estimated_size + '</p>';
        statisticsHtml += '<h5>Module Breakdown:</h5>';
        statisticsHtml += '<ul>';
        
        $.each(statistics.modules, function(key, module) {
            let statusIcon = '';
            let statusClass = '';
            
            if (module.status === 'ready') {
                statusIcon = '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>';
                statusClass = 'status-ready';
            } else if (module.status === 'table_missing') {
                statusIcon = '<span class="dashicons dashicons-warning" style="color: #dba617;"></span>';
                statusClass = 'status-warning';
            } else {
                statusIcon = '<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>';
                statusClass = 'status-error';
            }
            
            statisticsHtml += '<li class="' + statusClass + '">';
            statisticsHtml += statusIcon + ' ' + module.name + ': ' + module.count + ' records';
            if (module.error) {
                statisticsHtml += ' <small>(' + module.error + ')</small>';
            }
            statisticsHtml += '</li>';
        });
        
        statisticsHtml += '</ul></div>';
        
        $('#export-progress-details').html(statisticsHtml);
        $('#export-progress').show();
    }

    function getSelectedModules() {
        const modules = [];
        $('#export-modules-grid input[type="checkbox"]:checked').each(function() {
            modules.push($(this).val());
        });
        return modules;
    }

    function startExport(formData) {
        const $button = $('#start-export');
        $button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    currentExportId = response.data.export_id;
                    showExportProgress();
                    startExportProgressTracking();
                    showSuccess(response.data.message);
                } else {
                    showError('Export failed: ' + response.data);
                }
            },
            error: function() {
                showError('Export request failed. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }

    function showExportProgress() {
        $('#export-progress').show();
        updateExportProgress(0, 'Initializing export...');
    }

    function startExportProgressTracking() {
        exportProgressInterval = setInterval(checkExportProgress, 2000);
    }

    function checkExportProgress() {
        if (!currentExportId) return;
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'hms_get_export_progress',
                nonce: hms_admin_vars.nonce,
                export_id: currentExportId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    updateExportProgress(data.progress, getProgressMessage(data));
                    
                    if (data.status === 'completed') {
                        clearInterval(exportProgressInterval);
                        showExportComplete(data);
                    } else if (data.status === 'failed') {
                        clearInterval(exportProgressInterval);
                        showError('Export failed: ' + (data.error || 'Unknown error'));
                    }
                }
            }
        });
    }

    function updateExportProgress(progress, message) {
        $('#export-progress-fill').css('width', progress + '%');
        $('#export-progress-text').text(Math.round(progress) + '%');
        $('#export-progress-details').html('<p>' + message + '</p>');
        
        // Add progress animation class
        if (progress > 0) {
            $('#export-progress-fill').addClass('animated');
        }
    }

    function getProgressMessage(data) {
        if (data.status === 'processing') {
            let message = '';
            
            if (data.step_details) {
                message = data.step_details;
            } else if (data.current_step === 'calculating') {
                message = 'Calculating total records to export...';
            } else if (data.current_step === 'exporting') {
                if (data.current_module) {
                    message = `Exporting ${data.current_module} data... (${data.processed_records || 0} of ${data.total_records || 0} records)`;
                } else {
                    message = `Processing ${data.processed_records || 0} of ${data.total_records || 0} records...`;
                }
            } else {
                message = `Processing ${data.processed_records || 0} of ${data.total_records || 0} records...`;
            }
            
            // Add module progress if available
            if (data.modules_completed !== undefined && data.total_modules) {
                message += `<br><small>Modules completed: ${data.modules_completed} of ${data.total_modules}</small>`;
            }
            
            return message;
        } else if (data.status === 'completed') {
            return data.step_details || 'Export completed successfully!';
        } else if (data.status === 'failed') {
            return data.step_details || data.error || 'Export failed';
        } else {
            return data.step_details || 'Preparing export...';
        }
    }

    function showExportComplete(data) {
        updateExportProgress(100, 'Export completed successfully!');
        $('#export-progress-actions').show();
        
        const fileSize = formatFileSize(data.file_size);
        const completedTime = data.completed_at ? new Date(data.completed_at).toLocaleString() : 'Just now';
        
        $('#export-progress-details').append(
            '<div class="export-summary">' +
            '<h4>Export Summary</h4>' +
            '<p><strong>File Size:</strong> ' + fileSize + '</p>' +
            '<p><strong>Total Records:</strong> ' + (data.total_records || 0) + '</p>' +
            '<p><strong>Modules Exported:</strong> ' + (data.total_modules || 0) + '</p>' +
            '<p><strong>Completed:</strong> ' + completedTime + '</p>' +
            '</div>'
        );
        
        // Add success styling
        $('#export-progress').addClass('export-success');
    }

    function downloadExportFile() {
        if (!currentExportId) return;
        
        const downloadUrl = hms_admin_vars.ajax_url + 
            '?action=hms_download_export_file' +
            '&export_id=' + encodeURIComponent(currentExportId) +
            '&nonce=' + encodeURIComponent(hms_admin_vars.nonce);
        
        window.location.href = downloadUrl;
    }

    function setupFileUpload() {
        const $uploadArea = $('#file-upload-area');
        
        // Drag and drop events
        $uploadArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        $uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        $uploadArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelection({ target: { files: files } });
            }
        });
        
        // Click to select file
        $uploadArea.on('click', function() {
            $('#import-file').click();
        });
    }

    function handleFileSelection(e) {
        const file = e.target.files[0];
        
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['text/csv', 'application/csv', 'text/plain', 
                             'application/vnd.ms-excel', 
                             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        if (!allowedTypes.includes(file.type)) {
            showError('Invalid file type. Please select CSV or Excel files only.');
            return;
        }
        
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showError('File size too large. Maximum allowed size is 10MB.');
            return;
        }
        
        // Display file info
        $('#selected-file-name').text(file.name);
        $('#selected-file-size').text(formatFileSize(file.size));
        $('#file-info').show();
        $('#upload-file-btn').prop('disabled', false);
    }

    function clearFileSelection() {
        $('#import-file').val('');
        $('#file-info').hide();
        $('#upload-file-btn').prop('disabled', true);
    }

    function handleFileUpload(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const file = $('#import-file')[0].files[0];
        
        if (!file) {
            showError('Please select a file to upload.');
            return;
        }
        
        formData.append('action', 'hms_upload_import_file');
        formData.append('nonce', hms_admin_vars.nonce);
        formData.append('import_file', file);
        
        const $button = $('#upload-file-btn');
        $button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    currentImportId = response.data.import_id;
                    showSuccess(response.data.message);
                    showImportStep(2);
                } else {
                    showError('Upload failed: ' + response.data);
                }
            },
            error: function() {
                showError('Upload failed. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }

    function handleFileValidation(e) {
        e.preventDefault();
        
        const targetModule = $('#target-module').val();
        
        if (!targetModule) {
            showError('Please select a target module.');
            return;
        }
        
        const $button = $('#validate-file-btn');
        $button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'hms_validate_import_file',
                nonce: hms_admin_vars.nonce,
                import_id: currentImportId,
                target_module: targetModule
            },
            success: function(response) {
                if (response.success) {
                    displayValidationResults(response.data);
                    $('#proceed-to-import').show();
                } else {
                    showError('Validation failed: ' + response.data);
                }
            },
            error: function() {
                showError('Validation failed. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }

    function displayValidationResults(data) {
        const $results = $('#validation-results');
        $results.empty().show();
        
        if (data.success) {
            $results.addClass('validation-success').removeClass('validation-error');
            $results.html(
                '<h4><span class="dashicons dashicons-yes-alt"></span>Validation Successful</h4>' +
                '<p>File structure is valid and ready for import.</p>' +
                '<p><strong>Total Rows:</strong> ' + (data.total_rows || 0) + '</p>' +
                '<p><strong>Valid Records:</strong> ' + (data.valid_records || 0) + '</p>'
            );
            
            if (data.preview_data && data.preview_data.length > 0) {
                $results.append('<h5>Preview (First 5 rows):</h5>');
                const $table = $('<table class="wp-list-table widefat fixed striped">');
                
                // Add headers
                if (data.headers) {
                    const $headerRow = $('<tr>');
                    data.headers.forEach(header => {
                        $headerRow.append('<th>' + header + '</th>');
                    });
                    $table.append($('<thead>').append($headerRow));
                }
                
                // Add preview rows
                const $tbody = $('<tbody>');
                data.preview_data.forEach(row => {
                    const $row = $('<tr>');
                    row.forEach(cell => {
                        $row.append('<td>' + (cell || '') + '</td>');
                    });
                    $tbody.append($row);
                });
                $table.append($tbody);
                $results.append($table);
            }
        } else {
            $results.addClass('validation-error').removeClass('validation-success');
            $results.html(
                '<h4><span class="dashicons dashicons-dismiss"></span>Validation Failed</h4>' +
                '<p>' + (data.message || 'File validation failed.') + '</p>'
            );
            
            if (data.errors && data.errors.length > 0) {
                $results.append('<h6>Errors found:</h6>');
                const $errorList = $('<ul>');
                data.errors.forEach(error => {
                    $errorList.append('<li>' + error + '</li>');
                });
                $results.append($errorList);
            }
        }
    }

    function handleImportProcess(e) {
        e.preventDefault();
        
        const duplicateHandling = $('input[name="duplicate_handling"]:checked').val();
        
        const $button = $('#start-import-btn');
        $button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'hms_start_data_import',
                nonce: hms_admin_vars.nonce,
                import_id: currentImportId,
                duplicate_handling: duplicateHandling
            },
            success: function(response) {
                if (response.success) {
                    showImportProgress();
                    startImportProgressTracking();
                    showSuccess('Import process started successfully.');
                } else {
                    showError('Import failed: ' + response.data);
                }
            },
            error: function() {
                showError('Import failed. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }

    function showImportProgress() {
        $('#import-progress').show();
        updateImportProgress(0, 'Initializing import...');
    }

    function startImportProgressTracking() {
        importProgressInterval = setInterval(checkImportProgress, 2000);
    }

    function checkImportProgress() {
        if (!currentImportId) return;
        
        $.ajax({
            url: hms_admin_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'hms_get_import_progress',
                nonce: hms_admin_vars.nonce,
                import_id: currentImportId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    updateImportProgress(data.progress, getImportProgressMessage(data));
                    
                    if (data.status === 'completed') {
                        clearInterval(importProgressInterval);
                        showImportComplete(data);
                    } else if (data.status === 'failed') {
                        clearInterval(importProgressInterval);
                        showError('Import failed: ' + (data.error || 'Unknown error'));
                    }
                }
            }
        });
    }

    function updateImportProgress(progress, message) {
        $('#import-progress-fill').css('width', progress + '%');
        $('#import-progress-text').text(Math.round(progress) + '%');
        $('#import-progress-details').html('<p>' + message + '</p>');
    }

    function getImportProgressMessage(data) {
        if (data.status === 'processing') {
            return `Processing ${data.processed_records || 0} of ${data.total_records || 0} records...`;
        } else if (data.status === 'completed') {
            return 'Import completed successfully!';
        } else {
            return 'Preparing import...';
        }
    }

    function showImportComplete(data) {
        updateImportProgress(100, 'Import completed successfully!');
        
        const $summary = $('#import-summary');
        $summary.show();
        
        $summary.html(
            '<h5>Import Summary</h5>' +
            '<p><strong>Total Records Processed:</strong> ' + (data.total_records || 0) + '</p>' +
            '<p><strong>Successfully Imported:</strong> ' + (data.imported_records || 0) + '</p>' +
            '<p><strong>Skipped (Duplicates):</strong> ' + (data.skipped_records || 0) + '</p>' +
            '<p><strong>Errors:</strong> ' + (data.error_records || 0) + '</p>'
        );
        
        if (data.errors && data.errors.length > 0) {
            $summary.append('<h6>Import Errors:</h6>');
            const $errorList = $('<ul>');
            data.errors.forEach(error => {
                $errorList.append('<li>' + error + '</li>');
            });
            $summary.append($errorList);
        }
    }

    function showImportStep(stepNumber) {
        // Update step indicators
        $('.step').removeClass('active completed');
        for (let i = 1; i < stepNumber; i++) {
            $('#step-' + i).addClass('completed');
        }
        $('#step-' + stepNumber).addClass('active');
        
        // Show/hide step content
        $('.import-step').removeClass('active');
        $('#import-step-' + stepNumber).addClass('active');
    }

    function showLoading(selector, message) {
        $(selector).html(
            '<div class="loading-spinner">' +
            '<span class="spinner is-active"></span>' +
            '<span>' + message + '</span>' +
            '</div>'
        );
    }

    function showSuccess(message) {
        const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(() => {
            $notice.fadeOut(() => $notice.remove());
        }, 5000);
    }

    function showError(message) {
        const $notice = $('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(() => {
            $notice.fadeOut(() => $notice.remove());
        }, 8000);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});