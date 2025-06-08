<x-layout>
   <h2>Optimized Route for Ride #{{ $ride->id }}</h2>
   <div id="map" style="height: 600px;"></div>

   <script>
      const waypoints = @json($orderedWaypoints);
      const driverLocation = waypoints[0]; // First one is driver

      function initMap() {
         const map = new google.maps.Map(document.getElementById("map"), {
            center: driverLocation,
            zoom: 10,
         });

         const directionsService = new google.maps.DirectionsService();
         const directionsRenderer = new google.maps.DirectionsRenderer({ map });

         const gWaypoints = waypoints.slice(1, -1).map(point => ({
            location: new google.maps.LatLng(point.lat, point.lng),
            stopover: true,
         }));

         directionsService.route({
            origin: new google.maps.LatLng(waypoints[0].lat, waypoints[0].lng),
            destination: new google.maps.LatLng(waypoints[waypoints.length - 1].lat, waypoints[waypoints.length - 1].lng),
            waypoints: gWaypoints,
            travelMode: google.maps.TravelMode.DRIVING,
         }, (result, status) => {
            if (status === "OK") {
               directionsRenderer.setDirections(result);
            } else {
               console.error("Failed to fetch directions: " + status);
            }
         });
      }

      window.initMap = initMap;
   </script>

   <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async
      defer></script>
   {{-- <div id="map" style="height: 600px; width: 100%;"></div>

   <script>
      const encodedPolyline = {!! json_encode($geometry) !!}; // encoded polyline string from ORS
      const waypoints = @json($orderedWaypoints); // array of {lat, lng}

      function initMap() {
         const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: waypoints[0],
         });

         // Decode polyline
         const routePath = google.maps.geometry.encoding.decodePath(encodedPolyline);

         // Draw polyline
         const routePolyline = new google.maps.Polyline({
            path: routePath,
            geodesic: true,
            strokeColor: '#007bff',
            strokeOpacity: 0.8,
            strokeWeight: 5
         });
         routePolyline.setMap(map);

         // Fit bounds
         const bounds = new google.maps.LatLngBounds();
         routePath.forEach(point => bounds.extend(point));
         map.fitBounds(bounds);

         // Add markers
         waypoints.forEach((point, idx) => {
            new google.maps.Marker({
               position: point,
               map,
               label: `${idx + 1}`
            });
         });
      }

      window.initMap = initMap;
   </script>

   <script
      src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=geometry&callback=initMap"
      async defer></script> --}}


</x-layout>