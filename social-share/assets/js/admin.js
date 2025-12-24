jQuery(document).ready(function($) {
    // Initialize color pickers
    $('.ess-color-picker').wpColorPicker();
    
    // Preview SVG icon
    $('#network_icon').on('change', function() {
        var svg = $(this).val();
        if (svg) {
            var preview = $('<div class="ess-network-icon">' + svg + '</div>');
            $(this).after(preview);
        }
    });
    
    // Confirm network deletion
    $('.delete-network').on('click', function(e) {
        if (!confirm(ess_admin.i18n.confirm_delete)) {
            e.preventDefault();
        }
    });
    
    // Toggle network options based on button style
    $('#ess_settings\\[button_style\\]').on('change', function() {
        var style = $(this).val();
        var $sizeField = $('#ess_settings\\[button_size\\]').closest('p');
        var $shapeField = $('#ess_settings\\[button_shape\\]').closest('p');
        
        if (style === 'text-only') {
            $sizeField.hide();
            $shapeField.hide();
        } else {
            $sizeField.show();
            $shapeField.show();
        }
    }).trigger('change');
}); 