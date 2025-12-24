/**
 * Easy Social Share Plugin Scripts
 */
(function($) {
    'use strict';
    
    // Open share links in popup window
    $('.ess-social-share a').on('click', function(e) {
        // Skip for email links
        if ($(this).closest('.ess-email').length) {
            return true;
        }
        
        e.preventDefault();
        
        var url = $(this).attr('href');
        var windowName = 'socialShareWindow';
        var windowFeatures = 'width=550,height=450,resizable=yes,scrollbars=yes,status=yes';
        
        window.open(url, windowName, windowFeatures);
    });
    
    // Track social share clicks (if analytics is available)
    $('.ess-social-share a').on('click', function() {
        var network = $(this).closest('li').attr('class').replace('ess-', '');
        
        // Track with Google Analytics if available
        if (typeof ga !== 'undefined') {
            ga('send', 'event', 'Social Share', 'Click', network);
        }
        
        // Track with Google Tag Manager if available
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                'event': 'socialShare',
                'socialNetwork': network,
                'socialAction': 'share',
                'socialTarget': window.location.href
            });
        }
    });
    
})(jQuery);