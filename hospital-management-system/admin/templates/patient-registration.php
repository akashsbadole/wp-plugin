<div class="wrap hms-patient-registration-wrap">
    <?php
    // Check if we're editing an existing patient
    $is_edit = isset($_GET['id']) && !empty($_GET['id']);
    $patient_id = $is_edit ? intval($_GET['id']) : 0;
    $patient_data = null;
    
    if ($is_edit) {
        // Get patient data
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';
        $patient_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $patient_id));
        
        if (!$patient_data) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Patient not found.', 'hospital-management-system') . '</p></div>';
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=hms_patients')) . '" class="button">' . esc_html__('Back to Patients List', 'hospital-management-system') . '</a></p>';
            return;
        }
    }
    ?>
    
    <h1 class="hms-page-title"><?php echo $is_edit ? __('Edit Patient', 'hospital-management-system') : __('Patient Registration', 'hospital-management-system'); ?></h1>
    
    <?php
    // Display error or success messages if any
    if (isset($_GET['error'])) {
        $error = sanitize_text_field($_GET['error']);
        $error_message = '';
        
        switch ($error) {
            case 'missing_fields':
                $error_message = __('Please fill in all required fields.', 'hospital-management-system');
                break;
            case 'validation_failed':
                $fields = isset($_GET['fields']) ? explode(',', sanitize_text_field($_GET['fields'])) : array();
                $field_names = array();
                foreach ($fields as $field) {
                    switch ($field) {
                        case 'first_name':
                            $field_names[] = __('First Name', 'hospital-management-system');
                            break;
                        case 'last_name':
                            $field_names[] = __('Last Name', 'hospital-management-system');
                            break;
                        case 'date_of_birth':
                            $field_names[] = __('Date of Birth', 'hospital-management-system');
                            break;
                        case 'invalid_date':
                            $field_names[] = __('Valid Date of Birth', 'hospital-management-system');
                            break;
                        case 'gender':
                            $field_names[] = __('Gender', 'hospital-management-system');
                            break;
                        case 'contact_number':
                            $field_names[] = __('Phone Number', 'hospital-management-system');
                            break;
                        case 'invalid_phone':
                            $field_names[] = __('Valid Phone Number', 'hospital-management-system');
                            break;
                        case 'emergency_contact_name':
                            $field_names[] = __('Emergency Contact Name', 'hospital-management-system');
                            break;
                        case 'emergency_contact_number':
                            $field_names[] = __('Emergency Contact Number', 'hospital-management-system');
                            break;
                        case 'invalid_emergency_phone':
                            $field_names[] = __('Valid Emergency Contact Number', 'hospital-management-system');
                            break;
                    }
                }
                $error_message = sprintf(__('Please check the following fields: %s', 'hospital-management-system'), implode(', ', $field_names));
                break;
            case 'invalid_email':
                $error_message = __('Please enter a valid email address.', 'hospital-management-system');
                break;
            case 'email_exists':
                $error_message = __('A user with this email address already exists.', 'hospital-management-system');
                break;
            case 'user_creation_failed':
                $error_code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
                $error_message = sprintf(__('Failed to create user account. Error: %s', 'hospital-management-system'), $error_code);
                break;
            case 'db_insert':
                $error_message = __('Failed to save patient data to the database.', 'hospital-management-system');
                break;
            case 'db_table_missing':
                $error_message = __('The patients database table is missing. Please reactivate the plugin.', 'hospital-management-system');
                break;
            case 'db_columns_missing':
                $error_message = __('The patients database table is missing required columns. Please reactivate the plugin.', 'hospital-management-system');
                break;
            case 'update_failed':
                $error_message = __('Failed to update patient information. Please try again.', 'hospital-management-system');
                break;
            default:
                $error_message = __('An unknown error occurred.', 'hospital-management-system');
        }
        
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
    }
    
    if (isset($_GET['success'])) {
        $success_type = sanitize_text_field($_GET['success']);
        switch ($success_type) {
            case 'patient_updated':
                $message = __('Patient information updated successfully!', 'hospital-management-system');
                break;
            default:
                $message = __('Patient registered successfully!', 'hospital-management-system');
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    
    <!-- Test to verify JavaScript is loaded -->
    <!-- <div id="js-test" style="display:none;">JavaScript not loaded</div> -->
    
    <!-- Debug button to test JavaScript functionality -->
    <!-- <button type="button" id="debug-test-btn" class="button" style="margin-bottom: 10px;">Test JavaScript</button> -->
    
    <!-- Enhanced styling for patient registration form -->
    <style>
        .hms-input.error, .hms-select.error, .hms-textarea.error {
            border-color: #dc3232 !important;
            box-shadow: 0 0 2px rgba(220, 50, 50, 0.8) !important;
        }
        
        .hms-form-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .hms-input, .hms-select, .hms-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .hms-input:focus, .hms-select:focus, .hms-textarea:focus {
            border-color: #0073aa;
            outline: none;
            box-shadow: 0 0 2px rgba(0, 115, 170, 0.8);
        }
        
        .hms-required {
            color: #dc3232;
        }
        
        .hms-form-navigation {
            margin-top: 30px;
            text-align: right;
        }
        
        .hms-form-navigation .button {
            margin-left: 10px;
        }
        
        .hms-registration-progress {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .hms-progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 20px;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        
        .hms-progress-step.active {
            opacity: 1;
        }
        
        .hms-step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .hms-progress-step.active .hms-step-number {
            background: #0073aa;
            color: white;
        }
        
        .hms-form-section {
            display: none;
        }
        
        .hms-form-section.active {
            display: block;
        }
        
        .hms-section-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .hms-section-header h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .hms-section-header p {
            color: #666;
            font-style: italic;
        }
        
        .hms-form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .hms-form-row .hms-form-group {
            flex: 1;
        }
        
        .hms-subsection {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .hms-subsection h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .hms-input-with-icon {
            position: relative;
        }
        
        .hms-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .hms-input-with-icon .hms-input {
            padding-left: 35px;
        }
    </style>

    <!-- Inline script to handle Next button clicks -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Inline script loaded');
            
            // Handle Next button clicks
            $('.hms-next-step').on('click', function(e) {
                e.preventDefault();
                console.log('Next button clicked via inline script');
                
                // Get current step
                var currentStep = $('.hms-form-section.active').data('section');
                console.log('Current step:', currentStep);
                
                // Validate current step
                var $currentSection = $('.hms-form-section[data-section="' + currentStep + '"]');
                var $requiredFields = $currentSection.find('[required]');
                var isValid = true;
                
                console.log('Found', $requiredFields.length, 'required fields');
                
                $requiredFields.each(function() {
                    var $field = $(this);
                    var fieldValue = $field.val().trim();
                    var fieldType = $field.attr('type');
                    var fieldName = $field.attr('name');
                    
                    // Basic required field validation
                    if (!fieldValue) {
                        isValid = false;
                        $field.addClass('error');
                        return;
                    }
                    
                    // Additional validation based on field type
                    if (fieldType === 'email' && fieldValue) {
                        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(fieldValue)) {
                            isValid = false;
                            $field.addClass('error');
                            return;
                        }
                    }
                    
                    if (fieldType === 'tel' || fieldName === 'contact_number' || fieldName === 'emergency_contact_number') {
                        var phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
                        if (!phoneRegex.test(fieldValue)) {
                            isValid = false;
                            $field.addClass('error');
                            return;
                        }
                    }
                    
                    if (fieldType === 'date') {
                        var selectedDate = new Date(fieldValue);
                        var today = new Date();
                        if (selectedDate > today) {
                            isValid = false;
                            $field.addClass('error');
                            return;
                        }
                    }
                    
                    // If we get here, field is valid
                    $field.removeClass('error');
                });
                
                console.log('Validation result:', isValid);
                
                if (isValid && currentStep < 3) {
                    // Move to next step
                    $('.hms-form-section').removeClass('active');
                    $('.hms-form-section[data-section="' + (currentStep + 1) + '"]').addClass('active');
                    
                    // Update progress
                    $('.hms-progress-step').removeClass('active');
                    for (var i = 1; i <= currentStep + 1; i++) {
                        $('.hms-progress-step[data-step="' + i + '"]').addClass('active');
                    }
                    
                    // Scroll to top
                    $('html, body').animate({
                        scrollTop: $('#patient-registration-form').offset().top - 50
                    }, 300);
                } else if (!isValid) {
                    // Show validation error message
                    var errorMessage = 'Please fill in all required fields correctly before proceeding.';
                    if ($('.hms-validation-error').length === 0) {
                        $currentSection.prepend('<div class="notice notice-error hms-validation-error"><p>' + errorMessage + '</p></div>');
                    }
                    
                    // Scroll to first error field
                    var $firstError = $currentSection.find('.error').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 300);
                    }
                }
            });
            
            // Handle Previous button clicks
            $('.hms-prev-step').on('click', function(e) {
                e.preventDefault();
                console.log('Previous button clicked via inline script');
                
                // Get current step
                var currentStep = $('.hms-form-section.active').data('section');
                console.log('Current step:', currentStep);
                
                if (currentStep > 1) {
                    // Move to previous step
                    $('.hms-form-section').removeClass('active');
                    $('.hms-form-section[data-section="' + (currentStep - 1) + '"]').addClass('active');
                    
                    // Update progress
                    $('.hms-progress-step').removeClass('active');
                    for (var i = 1; i <= currentStep - 1; i++) {
                        $('.hms-progress-step[data-step="' + i + '"]').addClass('active');
                    }
                    
                    // Scroll to top
                    $('html, body').animate({
                        scrollTop: $('#patient-registration-form').offset().top - 50
                    }, 300);
                }
            });
            
            // Handle form submission
            $('#patient-registration-form').on('submit', function(e) {
                // Remove any existing validation errors
                $('.hms-validation-error').remove();
                $('.error').removeClass('error');
                
                // Validate all required fields
                var $requiredFields = $(this).find('[required]');
                var isValid = true;
                var firstErrorField = null;
                
                $requiredFields.each(function() {
                    var $field = $(this);
                    var fieldValue = $field.val().trim();
                    var fieldType = $field.attr('type');
                    var fieldName = $field.attr('name');
                    
                    // Basic required field validation
                    if (!fieldValue) {
                        isValid = false;
                        $field.addClass('error');
                        if (!firstErrorField) firstErrorField = $field;
                        return;
                    }
                    
                    // Additional validation based on field type
                    if (fieldType === 'email' && fieldValue) {
                        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(fieldValue)) {
                            isValid = false;
                            $field.addClass('error');
                            if (!firstErrorField) firstErrorField = $field;
                            return;
                        }
                    }
                    
                    if (fieldType === 'tel' || fieldName === 'contact_number' || fieldName === 'emergency_contact_number') {
                        var phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
                        if (!phoneRegex.test(fieldValue)) {
                            isValid = false;
                            $field.addClass('error');
                            if (!firstErrorField) firstErrorField = $field;
                            return;
                        }
                    }
                    
                    if (fieldType === 'date') {
                        var selectedDate = new Date(fieldValue);
                        var today = new Date();
                        if (selectedDate > today) {
                            isValid = false;
                            $field.addClass('error');
                            if (!firstErrorField) firstErrorField = $field;
                            return;
                        }
                    }
                    
                    // If we get here, field is valid
                    $field.removeClass('error');
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Show validation error message
                    var errorMessage = 'Please fill in all required fields correctly before submitting.';
                    $('#patient-registration-form').prepend('<div class="notice notice-error hms-validation-error"><p>' + errorMessage + '</p></div>');
                    
                    // Scroll to first error field
                    if (firstErrorField) {
                        $('html, body').animate({
                            scrollTop: firstErrorField.offset().top - 100
                        }, 300);
                    }
                    
                    return false;
                }
                
                // If validation passes, show loading state
                $(this).find('input[type="submit"]').prop('disabled', true).val('<?php echo $is_edit ? __('Updating...', 'hospital-management-system') : __('Registering...', 'hospital-management-system'); ?>');
            });
            
            // Debug test button
            $('#debug-test-btn').on('click', function() {
                // alert('Debug test button clicked! Inline script is working.');
                console.log('Debug test button clicked');
                console.log('Next button found:', $('.hms-next-step').length);
                console.log('Previous button found:', $('.hms-prev-step').length);
                console.log('Form sections found:', $('.hms-form-section').length);
                console.log('Progress steps found:', $('.hms-progress-step').length);
            });
        });
    </script>
    
    <div class="hms-registration-progress">
        <div class="hms-progress-step active" data-step="1">
            <span class="hms-step-number">1</span>
            <span class="hms-step-label"><?php _e('Personal Info', 'hospital-management-system'); ?></span>
        </div>
        <div class="hms-progress-step" data-step="2">
            <span class="hms-step-number">2</span>
            <span class="hms-step-label"><?php _e('Contact Details', 'hospital-management-system'); ?></span>
        </div>
        <div class="hms-progress-step" data-step="3">
            <span class="hms-step-number">3</span>
            <span class="hms-step-label"><?php _e('Medical Info', 'hospital-management-system'); ?></span>
        </div>
    </div>
    
    <form id="patient-registration-form" method="post" action="<?php echo esc_url(admin_url('admin.php?page=hms_patient_registration')); ?>" class="hms-registration-form">
        <input type="hidden" name="test_submission" value="1">
        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="page" value="hms_patient_registration">
        <input type="hidden" name="form_submitted" value="1">
        <?php if ($is_edit): ?>
        <input type="hidden" name="patient_id" value="<?php echo esc_attr($patient_id); ?>">
        <input type="hidden" name="action" value="edit">
        <?php endif; ?>
        
        <!-- Step 1: Personal Information -->
        <div class="hms-form-section active" data-section="1">
            <div class="hms-section-header">
                <h2><?php _e('Personal Information', 'hospital-management-system'); ?></h2>
                <p><?php _e('Enter the patient\'s basic personal details', 'hospital-management-system'); ?></p>
            </div>
            
            <div class="hms-form-row">
                <div class="hms-form-group">
                    <label for="first_name"><?php _e('First Name', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->first_name) : ''; ?>">
                </div>
                
                <div class="hms-form-group">
                    <label for="last_name"><?php _e('Last Name', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->last_name) : ''; ?>">
                </div>
            </div>
            
            <div class="hms-form-row">
                <div class="hms-form-group">
                    <label for="dob"><?php _e('Date of Birth', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                    <input type="date" id="dob" name="dob" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->date_of_birth) : ''; ?>">
                </div>
                
                <div class="hms-form-group">
                    <label for="gender"><?php _e('Gender', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                    <select id="gender" name="gender" class="hms-select" required>
                        <option value=""><?php _e('Select Gender', 'hospital-management-system'); ?></option>
                        <option value="Male" <?php echo ($is_edit && $patient_data->gender === 'Male') ? 'selected' : ''; ?>><?php _e('Male', 'hospital-management-system'); ?></option>
                        <option value="Female" <?php echo ($is_edit && $patient_data->gender === 'Female') ? 'selected' : ''; ?>><?php _e('Female', 'hospital-management-system'); ?></option>
                        <option value="Other" <?php echo ($is_edit && $patient_data->gender === 'Other') ? 'selected' : ''; ?>><?php _e('Other', 'hospital-management-system'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="hms-form-row">
                <div class="hms-form-group">
                    <label for="national_id"><?php _e('National ID', 'hospital-management-system'); ?></label>
                    <input type="text" id="national_id" name="national_id" class="hms-input" value="<?php echo $is_edit ? esc_attr($patient_data->national_id) : ''; ?>">
                </div>
                
                <div class="hms-form-group">
                    <label for="blood_group"><?php _e('Blood Group', 'hospital-management-system'); ?></label>
                    <select id="blood_group" name="blood_group" class="hms-select">
                        <option value=""><?php _e('Select Blood Group', 'hospital-management-system'); ?></option>
                        <option value="A+" <?php echo ($is_edit && $patient_data->blood_group === 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo ($is_edit && $patient_data->blood_group === 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo ($is_edit && $patient_data->blood_group === 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo ($is_edit && $patient_data->blood_group === 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo ($is_edit && $patient_data->blood_group === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo ($is_edit && $patient_data->blood_group === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo ($is_edit && $patient_data->blood_group === 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo ($is_edit && $patient_data->blood_group === 'O-') ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>
            </div>
            
            <div class="hms-form-navigation">
                <button type="button" class="button button-primary hms-next-step"><?php _e('Next', 'hospital-management-system'); ?></button>
            </div>
        </div>
        
        <!-- Step 2: Contact Information -->
        <div class="hms-form-section" data-section="2">
            <div class="hms-section-header">
                <h2><?php _e('Contact Information', 'hospital-management-system'); ?></h2>
                <p><?php _e('Enter the patient\'s contact details and emergency contact', 'hospital-management-system'); ?></p>
            </div>
            
            <div class="hms-form-row">
                <div class="hms-form-group">
                    <label for="contact_number"><?php _e('Phone Number', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                    <input type="tel" id="contact_number" name="contact_number" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->contact_number) : ''; ?>">
                </div>
                
                <div class="hms-form-group">
                    <label for="email"><?php _e('Email Address', 'hospital-management-system'); ?></label>
                    <input type="email" id="email" name="email" class="hms-input" value="<?php echo $is_edit ? esc_attr($patient_data->email) : ''; ?>">
                </div>
            </div>
            
            <div class="hms-form-group">
                <label for="address"><?php _e('Address', 'hospital-management-system'); ?></label>
                <textarea id="address" name="address" class="hms-textarea" rows="3"><?php echo $is_edit ? esc_textarea($patient_data->address) : ''; ?></textarea>
            </div>
            
            <div class="hms-subsection">
                <h3><?php _e('Emergency Contact', 'hospital-management-system'); ?></h3>
                
                <div class="hms-form-row">
                    <div class="hms-form-group">
                        <label for="emergency_contact_name"><?php _e('Emergency Contact Name', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->emergency_contact_name) : ''; ?>">
                    </div>
                    
                    <div class="hms-form-group">
                        <label for="emergency_contact_number"><?php _e('Emergency Contact Number', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                        <input type="tel" id="emergency_contact_number" name="emergency_contact_number" class="hms-input" required value="<?php echo $is_edit ? esc_attr($patient_data->emergency_contact_number) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="hms-form-navigation">
                <button type="button" class="button hms-prev-step"><?php _e('Previous', 'hospital-management-system'); ?></button>
                <button type="button" class="button button-primary hms-next-step"><?php _e('Next', 'hospital-management-system'); ?></button>
            </div>
        </div>
        
        <!-- Step 3: Medical Information -->
        <div class="hms-form-section" data-section="3">
            <div class="hms-section-header">
                <h2><?php _e('Medical Information', 'hospital-management-system'); ?></h2>
                <p><?php _e('Enter the patient\'s medical details and history', 'hospital-management-system'); ?></p>
            </div>
            
            <div class="hms-form-row">
                <div class="hms-form-group">
                    <label for="deposit_money"><?php _e('Initial Deposit (Optional)', 'hospital-management-system'); ?></label>
                    <div class="hms-input-with-icon">
                        <span class="hms-input-icon"><?php echo esc_html(get_option('hms_currency_symbol', '$')); ?></span>
                        <input type="number" id="deposit_money" name="deposit_money" class="hms-input" step="0.01" min="0" value="<?php echo $is_edit ? esc_attr($patient_data->deposit_money) : '0.00'; ?>">
                    </div>
                </div>
            </div>
            
            <div class="hms-form-group">
                <label for="allergies"><?php _e('Allergies', 'hospital-management-system'); ?></label>
                <textarea id="allergies" name="allergies" class="hms-textarea" rows="3" placeholder="<?php _e('List any known allergies', 'hospital-management-system'); ?>"><?php echo $is_edit ? esc_textarea($patient_data->allergies) : ''; ?></textarea>
            </div>
            
            <div class="hms-form-group">
                <label for="medical_history"><?php _e('Medical History', 'hospital-management-system'); ?></label>
                <textarea id="medical_history" name="medical_history" class="hms-textarea" rows="5" placeholder="<?php _e('Provide relevant medical history information', 'hospital-management-system'); ?>"><?php echo $is_edit ? esc_textarea($patient_data->medical_history) : ''; ?></textarea>
            </div>

            <?php do_action( 'hms_patient_registration_form_after' ); ?>
            
            <div class="hms-form-navigation">
                <button type="button" class="button hms-prev-step"><?php _e('Previous', 'hospital-management-system'); ?></button>
                <input type="submit" name="submit_patient_registration" class="button button-primary" value="<?php echo $is_edit ? __('Update Patient', 'hospital-management-system') : __('Register Patient', 'hospital-management-system'); ?>">
            </div>
        </div>
    </form>
</div>
