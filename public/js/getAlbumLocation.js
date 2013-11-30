(function ($) {
    function initialize() {
        if (($("#locationLat").val() != '') && ($("#locationLng").val() != '')) {
            $("#map-canvas").show();

            var locationLatlng = new google.maps.LatLng($("#locationLat").val(), $("#locationLng").val());

            var mapOptions = {
                center: locationLatlng,
                zoom: 7,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
        } else {
            var mapOptions = {
                center: new google.maps.LatLng(55.364283, 23.917816),
                zoom: 7,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
        }

        map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);


        if (($("#locationLat").val() != '') && ($("#locationLng").val() != '')) {
            marker = new google.maps.Marker({
                position: locationLatlng,
                map: map
            });

            map.panTo(locationLatlng);
        }
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    $("#get-location").click(function () {
        if ($("#location").val() != '') {
            var address = $("#location").val().replace(/ /g, '+');
            $.getJSON("http://maps.googleapis.com/maps/api/geocode/json?address=" + address + "&sensor=false", function (data) {
                //console.log(data);
                if (data.status != "ZERO_RESULTS") {
                    $("#map-canvas").show();
                    google.maps.event.trigger(map, 'resize');
                    var locationLatlng = new google.maps.LatLng(data.results[0].geometry.location.lat, data.results[0].geometry.location.lng);
                    if (typeof marker != 'undefined') {
                        marker.setMap(null);
                    }
                    marker = new google.maps.Marker({
                        position: locationLatlng,
                        map: map
                    });
                    map.panTo(locationLatlng);
                    $("#locationLat").val(data.results[0].geometry.location.lat);
                    $("#locationLng").val(data.results[0].geometry.location.lng);
                } else {
                    alert('no_location_found');
                }
            });
        } else {
            alert('specify_location_first');
        }
    });

    $("#location").change(function () {
        resetMap();
    });

    $("#location").bind('input', function () {
        resetMap();
    });

    function resetMap() {
        if (typeof marker != 'undefined') {
            marker.setMap(null);
        }
        $("#locationLat").val('');
        $("#locationLng").val('');
        $("#map-canvas").hide();
    }
})(jQuery);