/**
 * Hospital Management System - Patient Registration Form JavaScript
 *
 * @package Hospital_Management_System
 */

jQuery(document).ready(function($) {
    console.log('Patient Registration script loaded');
    
    // Show that JavaScript is loaded
    $('#js-test').text('JavaScript loaded').show();
    
    // Debug test button
    $('#debug-test-btn').on('click', function() {
        alert('Debug test button clicked! JavaScript is working.');
        console.log('Debug test button clicked');
        console.log('Next button found:', $('.hms-next-step').length);
        console.log('Previous button found:', $('.hms-prev-step').length);
        console.log('Form sections found:', $('.hms-form-section').length);
        console.log('Progress steps found:', $('.hms-progress-step').length);
    });
    
    // Patient Registration Form Multi-step Functionality
    var currentStep = 1;
    
    // Test function to verify script is loaded - make it global
    window.testPatientRegistration = function() {
        console.log('Testing Patient Registration Form');
        console.log('Current step:', currentStep);
        $('.hms-next-step').first().trigger('click');
    };
    
    // Global function to handle Next button click
    window.handleNextClick = function() {
        console.log('Next button clicked via global function');
        console.log('Current step before:', currentStep);
        
        if (validateCurrentStep()) {
            console.log('Validation passed');
            if (currentStep < 3) {
                currentStep++;
                console.log('Current step after:', currentStep);
                showSection(currentStep);
                updateProgress();
            } else {
                console.log('Already at the last step');
            }
        } else {
            console.log('Validation failed');
        }
    };
    
    // Next button click - try direct binding
    $('.hms-next-step').on('click', function(e) {
        e.preventDefault();
        console.log('Next button clicked - direct event handler triggered');
        console.log('Current step before:', currentStep);
        
        if (validateCurrentStep()) {
            console.log('Validation passed');
            if (currentStep < 3) {
                currentStep++;
                console.log('Current step after:', currentStep);
                showSection(currentStep);
                updateProgress();
            } else {
                console.log('Already at the last step');
            }
        } else {
            console.log('Validation failed');
        }
    });
    
    // Fallback event delegation
    $(document).on('click', '.hms-next-step', function(e) {
        e.preventDefault();
        console.log('Next button clicked - fallback event handler triggered');
        console.log('Current step before:', currentStep);
        
        if (validateCurrentStep()) {
            console.log('Validation passed');
            if (currentStep < 3) {
                currentStep++;
                console.log('Current step after:', currentStep);
                showSection(currentStep);
                updateProgress();
            } else {
                console.log('Already at the last step');
            }
        } else {
            console.log('Validation failed');
        }
    });
    
    // Previous button click - try direct binding
    $('.hms-prev-step').on('click', function(e) {
        e.preventDefault();
        console.log('Previous button clicked - direct event handler');
        
        if (currentStep > 1) {
            currentStep--;
            showSection(currentStep);
            updateProgress();
        }
    });
    
    // Fallback event delegation
    $(document).on('click', '.hms-prev-step', function(e) {
        e.preventDefault();
        console.log('Previous button clicked - fallback event handler');
        
        if (currentStep > 1) {
            currentStep--;
            showSection(currentStep);
            updateProgress();
        }
    });
    
    // Show specific section
    function showSection(step) {
        console.log('Showing section: ' + step);
        var $targetSection = $('.hms-form-section[data-section="' + step + '"]');
        console.log('Target section found:', $targetSection.length > 0);
        
        $('.hms-form-section').removeClass('active');
        $targetSection.addClass('active');
        console.log('Section activated');
        
        // Scroll to top of form
        var $form = $('#patient-registration-form');
        console.log('Form found:', $form.length > 0);
        if ($form.length > 0) {
            var offset = $form.offset().top - 50;
            console.log('Scrolling to offset:', offset);
            $('html, body').animate({
                scrollTop: offset
            }, 300);
        }
    }
    
    // Update progress indicators
    function updateProgress() {
        console.log('Updating progress to step:', currentStep);
        $('.hms-progress-step').removeClass('active');
        
        for (var i = 1; i <= currentStep; i++) {
            var $step = $('.hms-progress-step[data-step="' + i + '"]');
            console.log('Activating progress step', i, 'found:', $step.length > 0);
            $step.addClass('active');
        }
    }
    
    // Validate current step
    function validateCurrentStep() {
        console.log('Patient Registration Form: Validating step', currentStep);
        var $currentSection = $('.hms-form-section[data-section="' + currentStep + '"]');
        console.log('Patient Registration Form: Current section found:', $currentSection.length > 0);
        var $requiredFields = $currentSection.find('[required]');
        var isValid = true;
        
        console.log('Patient Registration Form: Found', $requiredFields.length, 'required fields in step', currentStep);
        
        $requiredFields.each(function() {
            var $field = $(this);
            var $formGroup = $field.closest('.hms-form-group');
            var fieldName = $field.attr('name');
            var fieldValue = $field.val().trim();
            
            console.log('Patient Registration Form: Checking field', fieldName, 'with value:', fieldValue);
            
            if (!fieldValue) {
                console.log('Patient Registration Form: Field', fieldName, 'is empty');
                $field.addClass('error');
                $formGroup.addClass('has-error');
                isValid = false;
            } else {
                console.log('Patient Registration Form: Field', fieldName, 'has value', fieldValue);
                $field.removeClass('error');
                $formGroup.removeClass('has-error');
            }
        });
        
        console.log('Patient Registration Form: Step', currentStep, 'validation result:', isValid);
        return isValid;
    }
    
    // Remove error class on input
    $(document).on('input change', '#patient-registration-form input, #patient-registration-form select, #patient-registration-form textarea', function() {
        $(this).removeClass('error');
        $(this).closest('.hms-form-group').removeClass('has-error');
    });
    
    // Form submission
    $('#patient-registration-form').on('submit', function(e) {
        console.log('Patient Registration Form: Form submission event triggered');
        
        // Validate all steps before submission
        var originalStep = currentStep;
        var allValid = true;
        
        // Check all sections
        for (var i = 1; i <= 3; i++) {
            currentStep = i;
            if (!validateCurrentStep()) {
                allValid = false;
                showSection(i);
                updateProgress();
                break;
            }
        }
        
        console.log('Patient Registration Form: Validation result:', allValid);
        
        if (!allValid) {
            e.preventDefault();
            return false;
        }
        
        // Restore original step if validation passed
        currentStep = originalStep;
        
        // Add loading state
        $('#patient-registration-form').addClass('hms-form-loading');
        
        // Disable submit button to prevent double submission
        $('#patient-registration-form').find('input[type="submit"]').prop('disabled', true);
        
        console.log('Patient Registration Form: Form submission allowed - will redirect to Patients Management page after successful registration');
        
        // Let the form submit normally - the server will handle the redirect after successful registration
        return true;
    });
    
    // Handle form submit button click directly to ensure it's triggered
    $('#patient-registration-form').find('input[type="submit"]').on('click', function(e) {
        console.log('Patient Registration Form: Submit button clicked');
        // Trigger form submission
        $('#patient-registration-form').trigger('submit');
    });
    
    // Test function
    window.testPatientRegistration = function() {
        console.log('Testing Patient Registration Form');
        console.log('Current step:', currentStep);
        $('.hms-next-step').first().trigger('click');
    };
    
    console.log('Patient Registration script initialized. Type testPatientRegistration() in console to test.');
});