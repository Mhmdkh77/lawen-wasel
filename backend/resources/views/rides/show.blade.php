<x-layout>
   <h2>Optimized Route for Ride #{{ $ride->id }}</h2>
   <div id="map" style="height: 600px;"></div>

   <script>
      const waypoints = @json($orderedWaypoints);
      const driverLocation = waypoints[0];

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

</x-layout>