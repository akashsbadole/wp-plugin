/**
 * Local Business JavaScript
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/public/js
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initLocalBusiness();
    });

    /**
     * Initialize local business functionality
     */
    function initLocalBusiness() {
        // Initialize business hours toggle
        initBusinessHoursToggle();
        
        // Initialize map functionality
        initMapFunctionality();
        
        // Initialize location finder
        initLocationFinder();
        
        // Initialize directions functionality
        initDirections();
    }

    /**
     * Initialize business hours toggle
     */
    function initBusinessHoursToggle() {
        $('.business-hours-toggle').on('click', function(e) {
            e.preventDefault();
            var $hours = $('.business-hours-details');
            $hours.slideToggle(300);
            $(this).toggleClass('active');
            
            if ($(this).hasClass('active')) {
                $(this).text($(this).data('hide-text'));
            } else {
                $(this).text($(this).data('show-text'));
            }
        });
    }

    /**
     * Initialize map functionality
     */
    function initMapFunctionality() {
        // Initialize map containers
        $('.business-map-container').each(function() {
            var $container = $(this);
            var latitude = $container.data('latitude');
            var longitude = $container.data('longitude');
            var title = $container.data('title');
            
            if (latitude && longitude) {
                initMap($container[0], latitude, longitude, title);
            }
        });
    }

    /**
     * Initialize map
     */
    function initMap(container, latitude, longitude, title) {
        // Create map container
        var mapContainer = document.createElement('div');
        mapContainer.style.width = '100%';
        mapContainer.style.height = '300px';
        container.appendChild(mapContainer);

        // OpenStreetMap iframe
        var iframe = document.createElement('iframe');
        iframe.width = '100%';
        iframe.height = '100%';
        iframe.frameBorder = '0';
        iframe.style.border = '0';
        iframe.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + 
                     (parseFloat(longitude) - 0.005) + ',' + 
                     (parseFloat(latitude) - 0.005) + ',' + 
                     (parseFloat(longitude) + 0.005) + ',' + 
                     (parseFloat(latitude) + 0.005) + 
                     '&layer=mapnik&marker=' + latitude + ',' + longitude;
        iframe.allowFullscreen = true;
        
        mapContainer.appendChild(iframe);
    }

    /**
     * Initialize location finder
     */
    function initLocationFinder() {
        $('#find-nearest-location').on('click', function(e) {
            e.preventDefault();
            
            if (navigator.geolocation) {
                $(this).prop('disabled', true).text('Finding...');
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var latitude = position.coords.latitude;
                        var longitude = position.coords.longitude;
                        findNearestLocation(latitude, longitude);
                    },
                    function(error) {
                        $('#find-nearest-location').prop('disabled', false).text('Find Nearest Location');
                        alert('Unable to get your location. Please enable location services and try again.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });
    }

    /**
     * Find nearest location
     */
    function findNearestLocation(userLat, userLng) {
        var locations = [];
        
        $('.location-item').each(function() {
            var $this = $(this);
            var lat = parseFloat($this.data('latitude'));
            var lng = parseFloat($this.data('longitude'));
            
            if (!isNaN(lat) && !isNaN(lng)) {
                var distance = calculateDistance(userLat, userLng, lat, lng);
                locations.push({
                    element: $this,
                    distance: distance
                });
            }
        });
        
        // Sort by distance
        locations.sort(function(a, b) {
            return a.distance - b.distance;
        });
        
        // Highlight nearest location
        if (locations.length > 0) {
            $('.location-item').removeClass('nearest');
            locations[0].element.addClass('nearest');
            
            // Scroll to nearest location
            $('html, body').animate({
                scrollTop: locations[0].element.offset().top - 100
            }, 500);
            
            // Show distance
            locations[0].element.find('.location-distance').text(
                locations[0].distance.toFixed(2) + ' km away'
            ).show();
        }
        
        $('#find-nearest-location').prop('disabled', false).text('Find Nearest Location');
    }

    /**
     * Calculate distance between two points
     */
    function calculateDistance(lat1, lon1, lat2, lon2) {
        var R = 6371; // Radius of the earth in km
        var dLat = deg2rad(lat2 - lat1);
        var dLon = deg2rad(lon2 - lon1);
        var a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c; // Distance in km
        return d;
    }

    /**
     * Convert degrees to radians
     */
    function deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    /**
     * Initialize directions functionality
     */
    function initDirections() {
        $('.get-directions').on('click', function(e) {
            e.preventDefault();
            
            var $this = $(this);
            var address = $this.data('address');
            var latitude = $this.data('latitude');
            var longitude = $this.data('longitude');
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        var userLat = position.coords.latitude;
                        var userLng = position.coords.longitude;
                        
                        // Open directions in Google Maps
                        var url = 'https://www.google.com/maps/dir/' + 
                                 userLat + ',' + userLng + '/' + 
                                 latitude + ',' + longitude;
                        
                        window.open(url, '_blank');
                    },
                    function(error) {
                        // Fallback to address-based directions
                        if (address) {
                            var url = 'https://www.google.com/maps/dir/?api=1&destination=' + 
                                     encodeURIComponent(address);
                            window.open(url, '_blank');
                        } else {
                            alert('Unable to get your location. Please try again.');
                        }
                    }
                );
            } else {
                // Fallback to address-based directions
                if (address) {
                    var url = 'https://www.google.com/maps/dir/?api=1&destination=' + 
                             encodeURIComponent(address);
                    window.open(url, '_blank');
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            }
        });
    }

    /**
     * Initialize location filter
     */
    function initLocationFilter() {
        $('#location-filter').on('change', function() {
            var category = $(this).val();
            
            if (category === '') {
                $('.location-item').show();
            } else {
                $('.location-item').hide();
                $('.location-item[data-category="' + category + '"]').show();
            }
        });
    }

    /**
     * Initialize location search
     */
    function initLocationSearch() {
        $('#location-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm === '') {
                $('.location-item').show();
            } else {
                $('.location-item').each(function() {
                    var $this = $(this);
                    var title = $this.find('.location-title').text().toLowerCase();
                    var address = $this.find('.location-address').text().toLowerCase();
                    
                    if (title.includes(searchTerm) || address.includes(searchTerm)) {
                        $this.show();
                    } else {
                        $this.hide();
                    }
                });
            }
        });
    }

})(jQuery);