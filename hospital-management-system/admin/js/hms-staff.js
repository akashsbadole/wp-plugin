jQuery(document).ready(function($) {
    // Staff form submission via AJAX
    $('#staff-member-form').on('submit', function(e) {
        e.preventDefault();
        
        // Basic client-side validation
        if ($('#first_name').val() === '') {
            show_notice(hms_staff_vars.strings.please_enter_first_name, 'error');
            $('#first_name').focus();
            return false;
        }
        if ($('#last_name').val() === '') {
            show_notice(hms_staff_vars.strings.please_enter_last_name, 'error');
            $('#last_name').focus();
            return false;
        }
        if ($('#staff_id_code').val() === '') {
            show_notice(hms_staff_vars.strings.please_enter_staff_id, 'error');
            $('#staff_id_code').focus();
            return false;
        }
        if ($('#role').val() === '') {
            show_notice(hms_staff_vars.strings.please_select_role, 'error');
            $('#role').focus();
            return false;
        }
        if ($('#department').val() === '') {
            show_notice(hms_staff_vars.strings.please_select_department, 'error');
            $('#department').focus();
            return false;
        }
        
        // Show loading spinner
        $('#publishing-action .spinner').addClass('is-active');
        $('#publish').prop('disabled', true);
        
        // Collect form data
        var formData = $(this).serialize();
        var isEdit = $('#staff-member-form input[name="staff_id"]').length > 0;
        var action = isEdit ? 'hms_update_staff' : 'hms_add_staff';
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: hms_staff_vars.ajax_url,
            data: formData + '&action=' + action,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    show_notice(response.data.message, 'success');
                    
                    // Redirect after successful submission
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    }
                } else {
                    show_notice(response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : hms_staff_vars.strings.unexpected_error;
                show_notice(errorMessage, 'error');
            },
            complete: function() {
                // Hide loading spinner
                $('#publishing-action .spinner').removeClass('is-active');
                $('#publish').prop('disabled', false);
            }
        });
    });
    
    // Function to show notices
    function show_notice(message, type) {
        // Remove any existing notices
        $('#message').remove();
        
        // Create new notice
        var noticeHtml = '<div id="message" class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>';
        $('h1').after(noticeHtml);
        
        // Make notice dismissible
        $('.notice.is-dismissible').on('click', '.notice-dismiss', function() {
            $(this).parent().remove();
        });
    }
});