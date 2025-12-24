/**
 * Auto Configuration Admin JavaScript
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     */

    $(document).ready(function() {
        // Handle AJAX apply configuration
        $('#ajax-apply-config').on('click', function(e) {
            e.preventDefault();
            
            if (confirm(seo_simple_auto_config_admin.strings.confirm_run)) {
                $('#config-progress').removeClass('hidden');
                $('.progress-bar-fill').css('width', '0%');
                
                // Animate progress bar
                $('.progress-bar-fill').animate({width: '90%'}, 2000);
                
                $.ajax({
                    url: seo_simple_auto_config_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'seo_simple_run_auto_config',
                        nonce: seo_simple_auto_config_admin.nonce_run_config
                    },
                    success: function(response) {
                        $('.progress-bar-fill').css('width', '100%');
                        
                        if (response.success) {
                            alert(response.data.message);
                            window.location.href = response.data.redirect;
                        } else {
                            alert(response.data.message);
                            $('#config-progress').addClass('hidden');
                        }
                    },
                    error: function() {
                        alert(seo_simple_auto_config_admin.strings.error);
                        $('#config-progress').addClass('hidden');
                    }
                });
            }
        });

        // Handle refresh site analysis
        $('#refresh-analysis').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var originalText = $button.text();
            
            $button.prop('disabled', true).text('Analyzing...');
            
            $.ajax({
                url: seo_simple_auto_config_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'seo_simple_get_site_analysis',
                    nonce: seo_simple_auto_config_admin.nonce_get_analysis
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show updated analysis
                        window.location.reload();
                    } else {
                        alert(response.data.message);
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert(seo_simple_auto_config_admin.strings.error);
                    $button.prop('disabled', false).text(originalText);
                }
            });
        });

        // Toggle settings details
        $('.toggle-settings-details').on('click', function(e) {
            e.preventDefault();
            
            var $details = $('.recommended-settings ul');
            var $button = $(this);
            
            if ($details.is(':visible')) {
                $details.hide();
                $button.text('Show Details');
            } else {
                $details.show();
                $button.text('Hide Details');
            }
        });

        // Initialize tooltips
        $('.seo-simple-tooltip').tooltip({
            position: {
                my: "left top",
                at: "left+15 top+5"
            }
        });

        // Handle form validation
        $('#auto-config-form').on('submit', function(e) {
            var $submit = $(this).find('input[type="submit"]');
            var originalValue = $submit.val();
            
            $submit.prop('disabled', true).val('Applying...');
            
            // Re-enable after 5 seconds in case of error
            setTimeout(function() {
                $submit.prop('disabled', false).val(originalValue);
            }, 5000);
        });
    });

})(jQuery);