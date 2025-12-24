(function($) {
    "use strict";
    
    $(document).ready(function() {
        // Open share links in popup window
        $(".ess-social-share a").on("click", function(e) {
            // Skip for email links
            if ($(this).closest(".ess-email").length) {
                return true;
            }
            
            e.preventDefault();
            
            var url = $(this).attr("href");
            var windowName = "socialShareWindow";
            var windowFeatures = "width=550,height=450,resizable=yes,scrollbars=yes,status=yes";
            
            window.open(url, windowName, windowFeatures);
        });
    });
    
})(jQuery); 