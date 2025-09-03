// Fixed Google Maps JavaScript - Updated for school location in Dar es Salaam
var google;

function init() {
    // Check if map element exists before initializing
    var mapElement = document.getElementById('map');
    if (!mapElement) {
        console.log('Map element not found on this page');
        return;
    }

    // Mary Mother of Mercy School coordinates in Dar es Salaam, Tanzania
    var schoolLocation = new google.maps.LatLng(-6.901727, 39.158310);
    
    var mapOptions = {
        // How zoomed in you want the map to start at
        zoom: 15,

        // The latitude and longitude to center the map
        center: schoolLocation,

        // Disable scroll wheel zoom for better UX
        scrollwheel: false,
        
        // Custom map styling
        styles: [
			  {
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#f5f5f5"
			      }
			    ]
			  },
			  {
			    "elementType": "labels.icon",
			    "stylers": [
			      {
			        "visibility": "off"
			      }
			    ]
			  },
			  {
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#616161"
			      }
			    ]
			  },
			  {
			    "elementType": "labels.text.stroke",
			    "stylers": [
			      {
			        "color": "#f5f5f5"
			      }
			    ]
			  },
			  {
			    "featureType": "administrative.land_parcel",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#bdbdbd"
			      }
			    ]
			  },
			  {
			    "featureType": "poi",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#eeeeee"
			      }
			    ]
			  },
			  {
			    "featureType": "poi",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#757575"
			      }
			    ]
			  },
			  {
			    "featureType": "poi.park",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#e5e5e5"
			      }
			    ]
			  },
			  {
			    "featureType": "poi.park",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#9e9e9e"
			      }
			    ]
			  },
			  {
			    "featureType": "road",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#ffffff"
			      }
			    ]
			  },
			  {
			    "featureType": "road.arterial",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#757575"
			      }
			    ]
			  },
			  {
			    "featureType": "road.highway",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#dadada"
			      }
			    ]
			  },
			  {
			    "featureType": "road.highway",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#616161"
			      }
			    ]
			  },
			  {
			    "featureType": "road.local",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#9e9e9e"
			      }
			    ]
			  },
			  {
			    "featureType": "transit.line",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#e5e5e5"
			      }
			    ]
			  },
			  {
			    "featureType": "transit.station",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#eeeeee"
			      }
			    ]
			  },
			  {
			    "featureType": "water",
			    "elementType": "geometry",
			    "stylers": [
			      {
			        "color": "#c9c9c9"
			      }
			    ]
			  },
			  {
			    "featureType": "water",
			    "elementType": "labels.text.fill",
			    "stylers": [
			      {
			        "color": "#9e9e9e"
			      }
			    ]
			  }
			]
    };

    // Create the Google Map using our element and options defined above
    var map = new google.maps.Map(mapElement, mapOptions);
    
    // Add a marker for the school
    var schoolMarker = new google.maps.Marker({
        position: schoolLocation,
        map: map,
        title: 'Mary Mother of Mercy Pre & Primary School',
        icon: {
            url: 'images/loc.png',
            scaledSize: new google.maps.Size(40, 40) // Resize marker if needed
        }
    });

    // Add info window for the school marker
    var infoWindow = new google.maps.InfoWindow({
        content: '<div style="padding: 10px;"><h4>Mary Mother of Mercy Pre & Primary School</h4><p>Mjimpya Relini, Dar es Salaam<br>Phone: 0784168758<br>Email: motherofmercyprimaryschool@gmail.com</p></div>'
    });

    // Show info window when marker is clicked
    schoolMarker.addListener('click', function() {
        infoWindow.open(map, schoolMarker);
    });
}

// Initialize map when page loads, but only if Google Maps API is available
function initializeMap() {
    if (typeof google !== 'undefined' && google.maps) {
        init();
    } else {
        console.log('Google Maps API not loaded');
    }
}

// Use different initialization methods for better compatibility
if (typeof google !== 'undefined' && google.maps && google.maps.event) {
    google.maps.event.addDomListener(window, 'load', initializeMap);
} else {
    // Fallback for when Google Maps API loads after this script
    window.addEventListener('load', function() {
        setTimeout(initializeMap, 1000);
    });
}