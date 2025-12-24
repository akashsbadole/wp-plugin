jQuery(document).ready(function($) {
    // Preset templates
    var presets = {
        'google-analytics': {
            name: 'Google Analytics',
            code: '<!-- Global site tag (gtag.js) - Google Analytics -->\n<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n  gtag(\'config\', \'GA_TRACKING_ID\');\n</script>',
            location: 'header'
        },
        'facebook-pixel': {
            name: 'Facebook Pixel',
            code: '<!-- Facebook Pixel Code -->\n<script>\n!function(f,b,e,v,n,t,s)\n{if(f.fbq)return;n=f.fbq=function(){n.callMethod?\nn.callMethod.apply(n,arguments):n.queue.push(arguments);}\nif(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';\nn.queue=[];t=b.createElement(e);t.async=!0;\nt.src=v;s=b.getElementsByTagName(e)[0];\ns.parentNode.insertBefore(t,s)}(window, document,\'script\',\n\'https://connect.facebook.net/en_US/fbevents.js\');\nfbq(\'init\', \'YOUR_PIXEL_ID\');\nfbq(\'track\', \'PageView\');\n</script>\n<noscript><img height="1" width="1" style="display:none"\nsrc="https://www.facebook.com/tr?id=YOUR_PIXEL_ID&ev=PageView&noscript=1"\n/></noscript>',
            location: 'header'
        }
    };
    
    // Handle preset selection
    $('#snippet_preset').on('change', function() {
        var selectedPreset = $(this).val();
        
        if (selectedPreset && presets[selectedPreset]) {
            var preset = presets[selectedPreset];
            
            // Only fill in if name is empty or user confirms
            var currentName = $('#snippet_name').val();
            if (currentName === '' || confirm(seo_simple_admin.preset_confirm)) {
                $('#snippet_name').val(preset.name);
            }
            
            // Only fill in if code is empty or user confirms
            var currentCode = $('#snippet_code').val();
            if (currentCode === '' || confirm(seo_simple_admin.preset_confirm)) {
                $('#snippet_code').val(preset.code);
            }
            
            // Set location
            $('#snippet_location').val(preset.location);
            
            // Reset preset selection
            $(this).val('');
        }
    });
    
    // Form validation
    $('#seo-simple-code-snippets-form-container form').on('submit', function(e) {
        var name = $('#snippet_name').val().trim();
        var code = $('#snippet_code').val().trim();
        
        if (name === '') {
            alert(seo_simple_admin.name_required);
            $('#snippet_name').focus();
            e.preventDefault();
            return false;
        }
        
        if (code === '') {
            alert(seo_simple_admin.code_required);
            $('#snippet_code').focus();
            e.preventDefault();
            return false;
        }
        
        // Verify nonce is present
        var nonceField = $('#seo_simple_code_snippet_nonce');
        if (nonceField.length === 0 || nonceField.val() === '') {
            alert('Security verification failed. Please refresh the page and try again.');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Delete confirmation
    $('.delete-snippet').on('click', function(e) {
        if (!confirm(seo_simple_admin.delete_confirm)) {
            e.preventDefault();
            return false;
        }
        
        // Verify nonce is present in the URL
        var href = $(this).attr('href');
        if (href && href.indexOf('_wpnonce=') === -1) {
            alert('Security verification failed. Please refresh the page and try again.');
            e.preventDefault();
            return false;
        }
    });
});