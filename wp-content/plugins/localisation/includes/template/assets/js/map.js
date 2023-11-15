jQuery(document).ready(function($) {

	// Init
	var is_xs, is_sm, is_md, is_lg, is_xl, is_landscape, is_hd,
        body = $( 'body' ),
        opt = $( '#options' ),
        offset = 0;

	// Map
	if( $( '#map__canvas' ).length > 0 ) {
        google.maps.event.addDomListener( window, 'load', bravadGoogleMap );
    }

    function bravadGoogleMap() {

	    var clusterStyles = [{
		    textColor: 'white',
		    url: '/wp-content/plugins/localisation/includes/template/assets/img/m1.png',
		    height: 53,
		    width: 53
		}];

        var map,
            sizeX = 29,
            halfSizeX = sizeX / 2,
            sizeY = 40,
            dragOption = true,
            marker,
            infoWindows = [],
            markers = [],
            markers_clusters = {},
            view_map = $( '#view-map' ),
            mapBounds = new google.maps.LatLngBounds();

        if( is_xs ) {
            dragOption = false;
            sizeX = 29;
            halfSizeX = sizeX / 2;
            sizeY = 40;
        }

        if( is_sm ) {
            sizeX = 29;
            halfSizeX = sizeX / 2;
            sizeY = 40;
        }

        var destination = new google.maps.LatLng( 45.474430, -73.699438 ),
            infowindow = new google.maps.InfoWindow(),
            maptypeId = 'custom',
            featureOpts = [{ stylers: [ { hue: "#bbbbbb" }, { saturation: -100 }, { lightness: 0 }, { gamma: 1 } ] }],
            mapOptions = {
                zoom: 5,
                center: destination,
                scrollwheel: false,
                panControl: false,
                panControlOptions: {
                    position: google.maps.ControlPosition.TOP_RIGHT
                },
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.TOP_LEFT
                },
                mapTypeControl: false,
                mapTypeControlOptions: {
                    mapTypeIds: [google.maps.MapTypeId.ROADMAP]
                },
                draggable: dragOption,
                disableDefaultUI: true,
                mapTypeId: maptypeId
            };

        map = new google.maps.Map( document.getElementById( 'map__canvas' ), mapOptions );

		var mcOptions = {
		    styles: clusterStyles,
		};
        markers_clusters = new MarkerClusterer(map, [], mcOptions);

        var image = {
                url: '/wp-content/plugins/localisation/includes/template/assets/img/pin.png',
                size: new google.maps.Size( sizeX, sizeY ),
                scaledSize: new google.maps.Size( sizeX, sizeY ),
                anchor: new google.maps.Point( halfSizeX, sizeY )
            },
            locations = [],
            styledMapOptions = {
                name: 'N&B'
            },
            curr_marker,
            customMapType = new google.maps.StyledMapType( featureOpts, styledMapOptions );

		var imageActive = {
                url: '/wp-content/plugins/localisation/includes/template/assets/img/pin-active.png',
                size: new google.maps.Size( sizeX, sizeY ),
                scaledSize: new google.maps.Size( sizeX, sizeY ),
                anchor: new google.maps.Point( halfSizeX, sizeY )
        };

        map.mapTypes.set( maptypeId, customMapType );

        // Get the map points
        $.ajax({
            url: bravad.ajax_url,
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'map_points',
                page_id: bravad.post_id
            },
            success : function( data, textStatus, jqXHR ) {
                for (var i = 0; i < data.length; i++) {
                    locations[i] = {
                        '_latitude': data[i]._lat,
                        '_longitude': data[i]._lng,
                        '_address': data[i]._address,
                        '_phone': data[i]._phone,
                        '_email': data[i]._email,
                        '_title': data[i]._title,
                        '_slug': data[i]._slug
                    };
                }

                for (var i = 0; i < data.length; i++) {
                    bravadCreatePoints( locations[i] );
                }

                setTimeout( function() {
                    map.fitBounds( mapBounds );
                }, 0 );
            },
            error : function( jqXHR, textStatus, errorThrown ) {
                console.log( errorThrown );
            }
        });

        function bravadClearMarker() {
            if( typeof marker !== 'undefined' ) {
                marker.setMap(null);
            }

            $( '#retailers' ).empty();

            for ( var i = 0; i < markers.length; i++ ) {
                markers[i].setMap(null);
            }
        }

        function closeAllInfoWindows() {
            for ( var i = 0; i < infoWindows.length; i++ ) {
                infoWindows[i].close();
            }

           for ( var i = 0; i < markers.length; i++ ) {
                markers[i].setIcon(image);
            }

        }


        function bravadResetPosition( position ) {
            var image = {
                    url: '/wp-content/plugins/localisation/includes/template/assets/img/localisation.png',
                    size: new google.maps.Size( sizeX, sizeY ),
                    scaledSize: new google.maps.Size( sizeX, sizeY ),
                    anchor: new google.maps.Point( halfSizeX, sizeY )
                };

            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: image,
                zIndex: 1
            });

            markers.push( marker );

            var infowindow = new google.maps.InfoWindow({
                content: bravad.you
            });

            infoWindows.push( infowindow );

            marker.addListener( 'click', function() {
                closeAllInfoWindows();

                // Pan to current marker
                map.panTo( marker.position );

                infowindow.open( map, marker );
            });

            mapBounds.extend( marker.position );

            // Create elements
            var imax = locations.length;

            for ( var i = 0; i < imax; i++ ) {
                var latLng = new google.maps.LatLng( locations[i]._latitude, locations[i]._longitude ),
                    distance,
                    format_distance = '';

                distance = google.maps.geometry.spherical.computeDistanceBetween( position, latLng ) / 1000;
                format_distance = Math.round(distance);

                locations[i].distance = format_distance;
            }

            function compareDistance(oObjA, oObjB) {
                var iRes = 0;

                if (oObjA.distance < oObjB.distance) {
                    iRes = -1;
                } else if (oObjA.distance > oObjB.distance) {
                    iRes = 1;
                }

                return iRes;
            }

            locations.sort( compareDistance );
            var retailers_count = 0;

			// Search Radius for localisation (in Km)
            var searchradius = 100;

            for ( var i = 0; i < imax; i++ ) {
                if( locations[i].distance <= searchradius ) {
                    retailers_count++;
                    bravadCreatePoints( locations[i] );

                    $( '.loc_distance' ).toggle();

                    if( retailers_count % 2 == 0 ) {
                        $( '#retailers' ).append( '<div class="u-clearfix is-visible-xs"></div>' );
                    }

                    if( retailers_count % 4 == 0 ) {
                        $( '#retailers' ).append( '<div class="u-clearfix is-visible-sm"></div>' );
                    }

                    if( retailers_count % 5 == 0 ) {
                        $( '#retailers' ).append( '<div class="u-clearfix is-hidden-xl is-hidden-sm is-hidden-xs"></div>' );
                    }

                    if( retailers_count % 6 == 0 ) {
                        $( '#retailers' ).append( '<div class="u-clearfix is-visible-xl"></div>' );
                    }
                }
            }

            if( retailers_count == 0 ) {
                $( '#null' ).fadeIn( 300 );
                $( '#retailers' ).empty();
                $( '.map__loading' ).hide();

            } else {
                $( '#null' ).hide();
                $( '.map__loading' ).fadeOut( 1000 );
            }

            setTimeout( function() {
                map.fitBounds( mapBounds );
            }, 0 );

            var listener = google.maps.event.addListener( map, 'idle', function() {
                if ( map.getZoom() > 16 ) map.setZoom( 10 );
                google.maps.event.removeListener( listener );
            });
        }


        function bravadCreatePoints( element ) {
            var latLng = new google.maps.LatLng( element._latitude, element._longitude ),
                output_html;

            element.marker = new google.maps.Marker({
                position: latLng,
                map: map,
                animation: google.maps.Animation.DROP,
                title: element._title,
                icon: image,
                zIndex: 2
            });

            element.marker.set( 'marker_id', element._id );

			markers_clusters.addMarker(element.marker);

            markers.push( element.marker );

            var format_phone = element._phone.replace(/\(|\)|\-| /g, '');

			element.infowindow = new google.maps.InfoWindow({
				pixelOffset: new google.maps.Size(210,130),
                content:    '<div class="infowindow__heading">' + element._title + '</div>' +
                            '<div class="infowindow__content">' + element._address +
                            '<a href="' + element._slug + '">Plus d’info »</a></div>'
            });

            infoWindows.push( element.infowindow );

            element.marker.addListener( 'click', function() {
                closeAllInfoWindows();

				element.marker.setIcon(imageActive);

                element.infowindow.open( map, element.marker );
            });

            google.maps.event.addListener( element.marker, 'click', function() {
                map.panTo( latLng );
            });

            output_html =   '<div class="retailer"><a href="' + element._slug + '">' + element._title + '</a></p></div>';

            $( '#retailers' ).append( output_html );
            $('.map__loading').fadeOut('slow');

            mapBounds.extend( latLng );

            setTimeout( function() {
                map.fitBounds( mapBounds );
            }, 0 );

        }

    }

});