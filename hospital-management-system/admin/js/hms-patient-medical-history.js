jQuery(document).ready(function($) {
    "use strict";

    // Patient search functionality
    const patientSearchInput = $('#hms-patient-search');
    const patientSelect = $('#patient_select');
    const searchResultsContainer = $('#hms-patient-search-results');
    const searchSpinner = $('#hms-patient-search-spinner');
    const noResultsMessage = $('#hms-patient-search-no-results');
    const patientForm = $('form[method="get"]');
    const searchSubmitButton = $('#hms-patient-search-submit');
    let selectedPatientId = null;
    
    // Function to perform patient search via AJAX
    function searchPatients(searchTerm) {
        // Show loading spinner
        searchSpinner.show();
        noResultsMessage.hide();
        searchSubmitButton.hide();
        
        // Clear previous results
        searchResultsContainer.empty();
        selectedPatientId = null;
        
        // Make AJAX request to search for patients
        $.ajax({
            type: 'GET',
            url: ajaxurl,
            data: {
                action: 'hms_search_patients',
                nonce: hms_admin_vars.nonce,
                search: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    // Display search results
                    displaySearchResults(response.data);
                } else {
                    // Show no results message
                    noResultsMessage.show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error searching patients:', error);
                console.error('Response:', xhr.responseText);
                noResultsMessage.show();
            },
            complete: function() {
                // Hide loading spinner
                searchSpinner.hide();
            }
        });
    }
    
    // Function to display search results
    function displaySearchResults(patients) {
        searchResultsContainer.empty();
        
        patients.forEach(function(patient) {
            const resultItem = $('<div class="hms-patient-search-result">');
            resultItem.attr('data-patient-id', patient.id);
            resultItem.attr('data-patient-name', patient.first_name + ' ' + patient.last_name);
            resultItem.attr('data-patient-id-text', patient.patient_id);
            
            const patientInfo = $('<div class="hms-patient-info">');
            patientInfo.html(`
                <strong>${patient.first_name} ${patient.last_name}</strong>
                <span class="hms-patient-id">(${patient.patient_id})</span>
            `);
            
            resultItem.append(patientInfo);
            
            // Add click event to select patient
            resultItem.on('click', function() {
                const patientId = $(this).attr('data-patient-id');
                const patientName = $(this).attr('data-patient-name');
                const patientIdText = $(this).attr('data-patient-id-text');
                
                // Store the selected patient ID
                selectedPatientId = patientId;
                
                // Update the select dropdown
                patientSelect.val(patientId);
                
                // Highlight the selected patient
                $('.hms-patient-search-result').removeClass('selected');
                $(this).addClass('selected');
                
                // Show the submit button
                searchSubmitButton.show();
            });
            
            searchResultsContainer.append(resultItem);
        });
    }
    
    // Event listener for search input
    patientSearchInput.on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Only search if term has at least 2 characters
        if (searchTerm.length >= 2) {
            // Clear any existing timeout
            clearTimeout(window.searchTimeout);
            
            // Set a new timeout to delay the search
            window.searchTimeout = setTimeout(function() {
                searchPatients(searchTerm);
            }, 300); // 300ms delay
        } else {
            // Clear results if search term is too short
            searchResultsContainer.empty();
            noResultsMessage.hide();
            searchSubmitButton.hide();
            selectedPatientId = null;
        }
    });
    
    // Event listener for search input focus
    patientSearchInput.on('focus', function() {
        const searchTerm = $(this).val().trim();
        
        // If there's a search term and results are empty, perform search
        if (searchTerm.length >= 2 && searchResultsContainer.is(':empty')) {
            searchPatients(searchTerm);
        }
    });
    
    // Event listener for submit button
    searchSubmitButton.on('click', function() {
        if (selectedPatientId) {
            // Update the form with the selected patient ID
            patientForm.append('<input type="hidden" name="patient_id" value="' + selectedPatientId + '">');
            patientForm.submit();
        }
    });
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#hms-patient-search-container').length) {
            searchResultsContainer.empty();
            noResultsMessage.hide();
            searchSubmitButton.hide();
        }
    });
    
    // Clear search results when form is submitted
    patientForm.on('submit', function() {
        searchResultsContainer.empty();
        noResultsMessage.hide();
    });
});