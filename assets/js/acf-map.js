(function($) {
    function initMap($el) {
        var $markers = $el.find('.marker');

        var args = {
            zoom: $el.data('zoom') || 14,
            center: new google.maps.LatLng(0, 0),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map($el[0], args);
        map.markers = [];

        $markers.each(function(){
            initMarker($(this), map);
        });

        centerMap(map);
        return map;
    }

    function initMarker($marker, map) {
        var lat = $marker.data('lat');
        var lng = $marker.data('lng');
        var latLng = new google.maps.LatLng(lat, lng);

        var marker = new google.maps.Marker({
            position: latLng,
            map: map
        });

        map.markers.push(marker);
    }

    function centerMap(map) {
        var bounds = new google.maps.LatLngBounds();
        $.each(map.markers, function(i, marker){
            bounds.extend(marker.getPosition());
        });

        if(map.markers.length == 1){
            map.setCenter(bounds.getCenter());
            map.setZoom(map.zoom);
        } else {
            map.fitBounds(bounds);
        }
    }

    $(document).ready(function(){
        $('.acf-map').each(function(){
            initMap($(this));
        });
    });
})(jQuery);
