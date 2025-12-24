(function($) {
    'use strict';
    
    // Track page views
    function trackPageView() {
        if (typeof gtag === 'function') {
            gtag('event', 'page_view', {
                'send_to': clrGA.trackingId
            });
        }
    }
    
    // Track login redirect events
    function trackLoginRedirect() {
        // Check if we have a stored event
        var userId = $('body').data('user-id');
        
        if (userId) {
            var eventDataKey = 'clr_ga_event_' + userId;
            var eventData = sessionStorage.getItem(eventDataKey);
            
            if (eventData) {
                try {
                    eventData = JSON.parse(eventData);
                    
                    if (typeof gtag === 'function') {
                        gtag('event', eventData.event_action, {
                            'event_category': eventData.event_category,
                            'event_label': eventData.event_label,
                            'send_to': clrGA.trackingId
                        });
                        
                        // Remove the stored event
                        sessionStorage.removeItem(eventDataKey);
                    }
                } catch (e) {
                    console.error('Error parsing GA event data:', e);
                }
            }
        }
    }
    
    // Track user interactions
    function trackUserInteractions() {
        // Track form submissions
        $('form').on('submit', function() {
            if (typeof gtag === 'function') {
                gtag('event', 'form_submit', {
                    'event_category': 'engagement',
                    'send_to': clrGA.trackingId
                });
            }
        });
        
        // Track outbound links
        $('a[href^="http"]').on('click', function(e) {
            var url = $(this).attr('href');
            
            // Check if it's an external link
            if (url.indexOf(window.location.hostname) === -1) {
                if (typeof gtag === 'function') {
                    gtag('event', 'click', {
                        'event_category': 'outbound',
                        'event_label': url,
                        'transport_type': 'beacon',
                        'send_to': clrGA.trackingId
                    });
                }
            }
        });
        
        // Track file downloads
        $('a[href$=".pdf"], a[href$=".doc"], a[href$=".docx"], a[href$=".xls"], a[href$=".xlsx"], a[href$=".ppt"], a[href$=".pptx"], a[href$=".zip"], a[href$=".rar"]').on('click', function() {
            var url = $(this).attr('href');
            var filename = url.split('/').pop();
            
            if (typeof gtag === 'function') {
                gtag('event', 'download', {
                    'event_category': 'file',
                    'event_label': filename,
                    'send_to': clrGA.trackingId
                });
            }
        });
    }
    
    // Track scroll depth
    function trackScrollDepth() {
        var scrollTracked = {
            '25%': false,
            '50%': false,
            '75%': false,
            '90%': false
        };
        
        $(window).on('scroll', function() {
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height();
            var winHeight = $(window).height();
            var scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);
            
            // Track at specific scroll depths
            if (scrollPercent >= 25 && !scrollTracked['25%']) {
                scrollTracked['25%'] = true;
                trackScrollEvent('25%');
            }
            
            if (scrollPercent >= 50 && !scrollTracked['50%']) {
                scrollTracked['50%'] = true;
                trackScrollEvent('50%');
            }
            
            if (scrollPercent >= 75 && !scrollTracked['75%']) {
                scrollTracked['75%'] = true;
                trackScrollEvent('75%');
            }
            
            if (scrollPercent >= 90 && !scrollTracked['90%']) {
                scrollTracked['90%'] = true;
                trackScrollEvent('90%');
            }
        });
    }
    
    function trackScrollEvent(depth) {
        if (typeof gtag === 'function') {
            gtag('event', 'scroll', {
                'event_category': 'engagement',
                'event_label': depth,
                'send_to': clrGA.trackingId
            });
        }
    }
    
    // Track time on page
    function trackTimeOnPage() {
        var timeIntervals = [30, 60, 120, 300, 600]; // 30s, 1m, 2m, 5m, 10m
        var timeOnPage = 0;
        
        setInterval(function() {
            timeOnPage += 10;
            
            if (timeIntervals.includes(timeOnPage)) {
                if (typeof gtag === 'function') {
                    gtag('event', 'time_on_page', {
                        'event_category': 'engagement',
                        'event_label': timeOnPage + 's',
                        'send_to': clrGA.trackingId
                    });
                }
            }
        }, 10000); // Check every 10 seconds
    }
    
    // Initialize tracking when DOM is ready
    $(document).ready(function() {
        trackPageView();
        trackLoginRedirect();
        trackUserInteractions();
        trackScrollDepth();
        trackTimeOnPage();
    });
    
})(jQuery);