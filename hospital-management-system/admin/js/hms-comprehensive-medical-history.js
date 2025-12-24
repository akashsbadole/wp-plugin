/**
 * Comprehensive Medical History JavaScript
 * 
 * Handles advanced search, filtering, inline editing, and audit trail functionality
 * for the comprehensive medical history interface.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/js
 * @author     HMS Development Team
 * @since      1.0.0
 */

jQuery(document).ready(function($) {
    "use strict";

    // Global variables
    let currentFilters = {};
    let allRecords = [];
    let filteredRecords = [];
    let searchTimeout = null;

    // Initialize the interface
    init();

    function init() {
        // Store all records for client-side filtering
        storeAllRecords();
        
        // Bind event handlers
        bindEventHandlers();
        
        // Initialize advanced filters toggle
        initAdvancedFilters();
        
        // Initialize timeline controls
        initTimelineControls();
        
        // Initialize inline editing
        initInlineEditing();
        
        // Initialize patient search (if on selection page)
        initPatientSearch();
    }

    function storeAllRecords() {
        allRecords = [];
        $('.hms-timeline-item').each(function() {
            const $item = $(this);
            allRecords.push({
                element: $item,
                id: $item.data('record-id'),
                type: $item.data('record-type'),
                doctorId: $item.data('doctor-id'),
                createdDate: $item.data('created-date'),
                diagnosis: $item.data('diagnosis'),
                symptoms: $item.data('symptoms'),
                treatment: $item.data('treatment'),
                notes: $item.data('notes'),
                doctorName: $item.data('doctor-name')
            });
        });
        filteredRecords = [...allRecords];
    }

    function bindEventHandlers() {
        // Basic search
        $('#comprehensive-search').on('input', debounce(handleBasicSearch, 300));
        $('#clear-search').on('click', clearBasicSearch);
        
        // Advanced filters
        $('#toggle-advanced-filters').on('click', toggleAdvancedFilters);
        $('#apply-filters').on('click', applyAdvancedFilters);
        $('#clear-all-filters').on('click', clearAllFilters);
        
        // Timeline controls
        $('#expand-all-records').on('click', expandAllRecords);
        $('#collapse-all-records').on('click', collapseAllRecords);
        $('.toggle-record-details').on('click', toggleRecordDetails);
        
        // Inline editing
        $('.edit-record-inline').on('click', showInlineEdit);
        $(document).on('click', '.cancel-inline-edit', hideInlineEdit);
        $(document).on('submit', '.hms-quick-edit-form', handleQuickEdit);
        
        // Export functionality
        $('#export-filtered-records').on('click', exportFilteredRecords);
        
        // Modal handling
        $('.hms-modal-close').on('click', closeModal);
        $(document).on('click', '.hms-modal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Load more records
        $('#load-more-records').on('click', loadMoreRecords);
    }

    function initAdvancedFilters() {
        // Set up filter change handlers
        $('#filter-record-type, #filter-doctor, #filter-diagnosis, #filter-date-from, #filter-date-to, #filter-sort')
            .on('change input', debounce(applyAdvancedFilters, 300));
    }

    function initTimelineControls() {
        // Initialize all records as collapsed
        $('.hms-timeline-details').hide();
        $('.toggle-record-details .collapse-text, .toggle-record-details .collapse-icon').hide();
        $('.toggle-record-details .expand-text, .toggle-record-details .expand-icon').show();
    }

    function initInlineEditing() {
        // Initialize inline edit forms as hidden
        $('.hms-inline-edit-form').hide();
    }

    function initPatientSearch() {
        const $patientSearch = $('#hms-patient-search');
        const $searchResults = $('#hms-patient-search-results');
        const $noResults = $('#hms-patient-search-no-results');
        const $spinner = $('#hms-patient-search-spinner');

        if ($patientSearch.length === 0) return;

        $patientSearch.on('input', debounce(function() {
            const searchTerm = $(this).val().trim();
            
            if (searchTerm.length >= 2) {
                searchPatients(searchTerm, $searchResults, $noResults, $spinner);
            } else {
                $searchResults.empty();
                $noResults.hide();
            }
        }, 300));

        // Handle result selection
        $(document).on('click', '.hms-patient-search-result', function() {
            const patientId = $(this).data('patient-id');
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('patient_id', patientId);
            window.location.href = currentUrl.toString();
        });
    }

    function searchPatients(searchTerm, $resultsContainer, $noResults, $spinner) {
        $spinner.show();
        $noResults.hide();
        $resultsContainer.empty();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_search_patients',
                nonce: hms_admin_vars.nonce,
                search: searchTerm
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    displayPatientSearchResults(response.data, $resultsContainer);
                } else {
                    $noResults.show();
                }
            },
            error: function() {
                $noResults.show();
            },
            complete: function() {
                $spinner.hide();
            }
        });
    }

    function displayPatientSearchResults(patients, $container) {
        $container.empty();
        
        patients.forEach(function(patient) {
            const $result = $('<div class="hms-patient-search-result">');
            $result.attr('data-patient-id', patient.id);
            $result.html(`
                <div class="hms-patient-info">
                    <strong>${patient.first_name} ${patient.last_name}</strong>
                    <span class="hms-patient-id">(${patient.patient_id})</span>
                    <div class="hms-patient-meta">
                        <span>${patient.contact_number || ''}</span>
                        <span>${patient.email || ''}</span>
                    </div>
                </div>
            `);
            $container.append($result);
        });
    }

    function handleBasicSearch() {
        const searchTerm = $('#comprehensive-search').val().toLowerCase().trim();
        
        if (searchTerm) {
            $('#clear-search').show();
            filterRecords({ search: searchTerm });
        } else {
            $('#clear-search').hide();
            filterRecords({});
        }
    }

    function clearBasicSearch() {
        $('#comprehensive-search').val('');
        $('#clear-search').hide();
        filterRecords({});
    }

    function toggleAdvancedFilters() {
        const $panel = $('#advanced-filters-panel');
        const $button = $('#toggle-advanced-filters');
        
        if ($panel.is(':visible')) {
            $panel.slideUp();
            $button.html('<span class="dashicons dashicons-filter"></span> ' + hms_admin_vars.advanced_filters);
        } else {
            $panel.slideDown();
            $button.html('<span class="dashicons dashicons-arrow-up-alt2"></span> ' + hms_admin_vars.hide_filters);
        }
    }

    function applyAdvancedFilters() {
        const filters = {
            search: $('#comprehensive-search').val().toLowerCase().trim(),
            recordType: $('#filter-record-type').val(),
            doctor: $('#filter-doctor').val(),
            diagnosis: $('#filter-diagnosis').val().toLowerCase().trim(),
            dateFrom: $('#filter-date-from').val(),
            dateTo: $('#filter-date-to').val(),
            sort: $('#filter-sort').val()
        };

        currentFilters = filters;
        filterRecords(filters);
    }

    function clearAllFilters() {
        // Clear all filter inputs
        $('#comprehensive-search').val('');
        $('#filter-record-type').val('');
        $('#filter-doctor').val('');
        $('#filter-diagnosis').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#filter-sort').val('date_desc');
        
        $('#clear-search').hide();
        
        // Reset filters and show all records
        currentFilters = {};
        filterRecords({});
    }

    function filterRecords(filters) {
        showFilterLoading();
        
        // Start with all records
        filteredRecords = [...allRecords];
        
        // Apply search filter
        if (filters.search) {
            filteredRecords = filteredRecords.filter(record => {
                return record.diagnosis.includes(filters.search) ||
                       record.symptoms.includes(filters.search) ||
                       record.treatment.includes(filters.search) ||
                       record.notes.includes(filters.search) ||
                       record.doctorName.includes(filters.search);
            });
        }
        
        // Apply record type filter
        if (filters.recordType) {
            filteredRecords = filteredRecords.filter(record => record.type === filters.recordType);
        }
        
        // Apply doctor filter
        if (filters.doctor) {
            filteredRecords = filteredRecords.filter(record => record.doctorId == filters.doctor);
        }
        
        // Apply diagnosis filter
        if (filters.diagnosis) {
            filteredRecords = filteredRecords.filter(record => record.diagnosis.includes(filters.diagnosis));
        }
        
        // Apply date filters
        if (filters.dateFrom) {
            const fromDate = new Date(filters.dateFrom);
            filteredRecords = filteredRecords.filter(record => {
                const recordDate = new Date(record.createdDate);
                return recordDate >= fromDate;
            });
        }
        
        if (filters.dateTo) {
            const toDate = new Date(filters.dateTo);
            toDate.setHours(23, 59, 59, 999); // End of day
            filteredRecords = filteredRecords.filter(record => {
                const recordDate = new Date(record.createdDate);
                return recordDate <= toDate;
            });
        }
        
        // Apply sorting
        if (filters.sort) {
            sortRecords(filters.sort);
        }
        
        // Update display
        updateRecordsDisplay();
        updateFilterStatus();
        hideFilterLoading();
    }

    function sortRecords(sortBy) {
        switch (sortBy) {
            case 'date_asc':
                filteredRecords.sort((a, b) => new Date(a.createdDate) - new Date(b.createdDate));
                break;
            case 'date_desc':
                filteredRecords.sort((a, b) => new Date(b.createdDate) - new Date(a.createdDate));
                break;
            case 'type':
                filteredRecords.sort((a, b) => a.type.localeCompare(b.type));
                break;
            case 'doctor':
                filteredRecords.sort((a, b) => a.doctorName.localeCompare(b.doctorName));
                break;
        }
    }

    function updateRecordsDisplay() {
        // Hide all records first
        $('.hms-timeline-item').hide();
        
        // Show filtered records in order
        const $timeline = $('#medical-records-timeline');
        
        if (filteredRecords.length === 0) {
            showNoResultsMessage();
        } else {
            hideNoResultsMessage();
            
            // Reorder and show filtered records
            filteredRecords.forEach((record, index) => {
                const $element = record.element;
                $element.show();
                
                // Update timeline connectors
                updateTimelineConnectors($element, index, filteredRecords.length);
                
                // Append to timeline to maintain order
                $timeline.append($element);
            });
        }
    }

    function updateTimelineConnectors($element, index, total) {
        const $connector = $element.find('.hms-timeline-connector');
        const $start = $connector.find('.hms-timeline-start');
        const $line = $connector.find('.hms-timeline-line');
        
        // Show/hide start marker
        if (index === 0) {
            if ($start.length === 0) {
                $connector.prepend('<div class="hms-timeline-start"></div>');
            }
        } else {
            $start.remove();
        }
        
        // Show/hide connecting line
        if (index < total - 1) {
            if ($line.length === 0) {
                $connector.append('<div class="hms-timeline-line"></div>');
            }
        } else {
            $line.remove();
        }
    }

    function updateFilterStatus() {
        const total = allRecords.length;
        const filtered = filteredRecords.length;
        const $status = $('#filter-status');
        const $count = $('#records-count-display');
        
        if (filtered < total) {
            $count.text(`Showing ${filtered} of ${total} records`);
            $status.show();
        } else {
            $status.hide();
        }
    }

    function showFilterLoading() {
        $('#filter-loading').show();
    }

    function hideFilterLoading() {
        $('#filter-loading').hide();
    }

    function showNoResultsMessage() {
        if ($('#no-results-message').length === 0) {
            const $message = $(`
                <div id="no-results-message" class="hms-no-results">
                    <div class="hms-no-results-icon">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                    <h4>${hms_admin_vars.no_records_found}</h4>
                    <p>${hms_admin_vars.try_different_filters}</p>
                    <button type="button" id="clear-filters-from-no-results" class="hms-btn hms-btn-secondary">
                        ${hms_admin_vars.clear_filters}
                    </button>
                </div>
            `);
            $('#medical-records-timeline').append($message);
            
            $('#clear-filters-from-no-results').on('click', clearAllFilters);
        }
    }

    function hideNoResultsMessage() {
        $('#no-results-message').remove();
    }

    function expandAllRecords() {
        $('.hms-timeline-details').slideDown();
        $('.toggle-record-details .expand-text, .toggle-record-details .expand-icon').hide();
        $('.toggle-record-details .collapse-text, .toggle-record-details .collapse-icon').show();
    }

    function collapseAllRecords() {
        $('.hms-timeline-details').slideUp();
        $('.toggle-record-details .collapse-text, .toggle-record-details .collapse-icon').hide();
        $('.toggle-record-details .expand-text, .toggle-record-details .expand-icon').show();
    }

    function toggleRecordDetails() {
        const $button = $(this);
        const recordId = $button.data('record-id');
        const $details = $('#record-details-' + recordId);
        
        if ($details.is(':visible')) {
            $details.slideUp();
            $button.find('.collapse-text, .collapse-icon').hide();
            $button.find('.expand-text, .expand-icon').show();
        } else {
            $details.slideDown();
            $button.find('.expand-text, .expand-icon').hide();
            $button.find('.collapse-text, .collapse-icon').show();
        }
    }

    function showInlineEdit() {
        const recordId = $(this).data('record-id');
        const $details = $('#record-details-' + recordId);
        const $editForm = $('#inline-edit-' + recordId);
        
        // Hide details and show edit form
        $details.hide();
        $editForm.slideDown();
        
        // Focus on first textarea
        $editForm.find('textarea').first().focus();
    }

    function hideInlineEdit() {
        const $form = $(this).closest('.hms-inline-edit-form');
        const recordId = $form.attr('id').replace('inline-edit-', '');
        const $details = $('#record-details-' + recordId);
        
        // Hide edit form and show details
        $form.slideUp();
        $details.show();
    }

    function handleQuickEdit(e) {
        e.preventDefault();
        
        const $form = $(this);
        const recordId = $form.data('record-id');
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Show loading state
        $submitBtn.prop('disabled', true).text(hms_admin_vars.saving);
        
        // Prepare form data
        const formData = {
            action: 'hms_quick_edit_medical_record',
            nonce: $form.find('input[name^="quick_edit_nonce"]').val(),
            record_id: recordId,
            diagnosis: $form.find('textarea[name="diagnosis"]').val(),
            treatment_plan: $form.find('textarea[name="treatment_plan"]').val(),
            notes: $form.find('textarea[name="notes"]').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Update the record display with new data
                    updateRecordDisplay(recordId, response.data.record);
                    
                    // Hide edit form and show updated details
                    hideInlineEdit.call($form.find('.cancel-inline-edit'));
                    
                    // Show success message
                    showNotification(response.data.message, 'success');
                    
                    // Update audit trail
                    updateAuditTrail(recordId, response.data.record);
                } else {
                    showNotification(response.data || hms_admin_vars.update_failed, 'error');
                }
            },
            error: function() {
                showNotification(hms_admin_vars.update_failed, 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(hms_admin_vars.save_changes);
            }
        });
    }

    function updateRecordDisplay(recordId, recordData) {
        const $item = $(`.hms-timeline-item[data-record-id="${recordId}"]`);
        const $details = $item.find('.hms-timeline-details');
        
        // Update summary diagnosis
        const $summaryDiagnosis = $item.find('.hms-summary-diagnosis');
        if (recordData.diagnosis) {
            $summaryDiagnosis.html(`<strong>${hms_admin_vars.diagnosis}:</strong> ${recordData.diagnosis.substring(0, 100)}${recordData.diagnosis.length > 100 ? '...' : ''}`);
        }
        
        // Update detailed sections
        updateDetailSection($details, 'symptoms', recordData.symptoms);
        updateDetailSection($details, 'diagnosis', recordData.diagnosis);
        updateDetailSection($details, 'treatment_plan', recordData.treatment_plan);
        updateDetailSection($details, 'notes', recordData.notes);
        
        // Update data attributes for filtering
        $item.attr('data-diagnosis', (recordData.diagnosis || '').toLowerCase());
        $item.attr('data-treatment', (recordData.treatment_plan || '').toLowerCase());
        $item.attr('data-notes', (recordData.notes || '').toLowerCase());
        
        // Update stored record data
        const recordIndex = allRecords.findIndex(r => r.id == recordId);
        if (recordIndex !== -1) {
            allRecords[recordIndex].diagnosis = (recordData.diagnosis || '').toLowerCase();
            allRecords[recordIndex].treatment = (recordData.treatment_plan || '').toLowerCase();
            allRecords[recordIndex].notes = (recordData.notes || '').toLowerCase();
        }
    }

    function updateDetailSection($container, sectionName, content) {
        const $section = $container.find(`.hms-detail-section:has(h4:contains("${sectionName.replace('_', ' ')}"))`);
        
        if (content && content.trim()) {
            if ($section.length) {
                $section.find('.hms-detail-content').text(content);
            } else {
                // Create new section if it doesn't exist
                const title = sectionName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                const $newSection = $(`
                    <div class="hms-detail-section">
                        <h4>${title}</h4>
                        <div class="hms-detail-content">${content}</div>
                    </div>
                `);
                $container.find('.hms-audit-trail').before($newSection);
            }
        } else {
            $section.remove();
        }
    }

    function updateAuditTrail(recordId, recordData) {
        const $auditTrail = $(`.hms-timeline-item[data-record-id="${recordId}"] .hms-audit-trail .hms-audit-info`);
        
        // Update or add last modified entry
        let $lastModified = $auditTrail.find('.hms-audit-item:contains("Last Modified")');
        
        if ($lastModified.length) {
            $lastModified.find('.hms-audit-value').text(recordData.updated_at_formatted);
        } else {
            const $newAuditItem = $(`
                <div class="hms-audit-item">
                    <span class="hms-audit-label">${hms_admin_vars.last_modified}:</span>
                    <span class="hms-audit-value">${recordData.updated_at_formatted}</span>
                </div>
            `);
            $auditTrail.find('.hms-audit-item:first').after($newAuditItem);
        }
    }

    function exportFilteredRecords() {
        if (filteredRecords.length === 0) {
            showNotification(hms_admin_vars.no_records_to_export, 'warning');
            return;
        }
        
        const recordIds = filteredRecords.map(record => record.id);
        const patientId = new URLSearchParams(window.location.search).get('patient_id');
        
        // Create export form and submit
        const $form = $('<form>', {
            method: 'POST',
            action: ajaxurl,
            style: 'display: none;'
        });
        
        $form.append($('<input>', { name: 'action', value: 'hms_export_medical_records' }));
        $form.append($('<input>', { name: 'nonce', value: hms_admin_vars.nonce }));
        $form.append($('<input>', { name: 'patient_id', value: patientId }));
        $form.append($('<input>', { name: 'record_ids', value: recordIds.join(',') }));
        $form.append($('<input>', { name: 'filters', value: JSON.stringify(currentFilters) }));
        
        $('body').append($form);
        $form.submit();
        $form.remove();
        
        showNotification(hms_admin_vars.export_started, 'info');
    }

    function loadMoreRecords() {
        const $button = $('#load-more-records');
        const patientId = $button.data('patient-id');
        const offset = parseInt($button.data('offset')) || 0;
        const limit = 10;
        
        $button.prop('disabled', true).html('<span class="hms-loading-spinner"></span> ' + hms_admin_vars.loading);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_load_more_medical_records',
                nonce: hms_admin_vars.nonce,
                patient_id: patientId,
                offset: offset,
                limit: limit
            },
            success: function(response) {
                if (response.success && response.data.records.length > 0) {
                    // Append new records to timeline
                    appendNewRecords(response.data.records);
                    
                    // Update offset
                    $button.data('offset', offset + limit);
                    
                    // Hide button if no more records
                    if (response.data.records.length < limit) {
                        $button.hide();
                    }
                } else {
                    $button.hide();
                }
            },
            error: function() {
                showNotification(hms_admin_vars.load_more_failed, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> ' + hms_admin_vars.load_more);
            }
        });
    }

    function appendNewRecords(records) {
        // This would need to be implemented based on the server response format
        // For now, we'll just refresh the page to load more records
        window.location.reload();
    }

    function showNotification(message, type = 'info') {
        const $notification = $(`
            <div class="hms-notification hms-notification-${type}">
                <span class="hms-notification-message">${message}</span>
                <button type="button" class="hms-notification-close">&times;</button>
            </div>
        `);
        
        $('body').append($notification);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, 5000);
        
        // Manual close
        $notification.find('.hms-notification-close').on('click', () => {
            $notification.fadeOut(() => $notification.remove());
        });
    }

    function closeModal() {
        $('.hms-modal').hide();
    }

    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(searchTimeout);
                func(...args);
            };
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(later, wait);
        };
    }

    // Utility function to format dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Export functions for external use
    window.HMS_ComprehensiveMedicalHistory = {
        filterRecords: filterRecords,
        exportRecords: exportFilteredRecords,
        showNotification: showNotification
    };
});