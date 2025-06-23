<x-layout>
   <div class="py-10 px-6 space-y-8 overflow-auto">

      <div class="flex items-center justify-between">
         <h2 class="text-3xl font-bold text-gray-800">Ride #{{ $ride->id }}</h2>
         <span class="text-sm text-gray-500">Created: {{ $ride->created_at->format('F j, Y, g:i a') }}</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-white p-6 rounded-xl shadow">
         <div>
            <h3 class="font-semibold text-gray-600">Driver</h3>
            <p class="text-gray-800">{{ $ride->vehicle->driver->user->name ?? 'N/A' }}</p>
         </div>
         <div>
            <h3 class="font-semibold text-gray-600">Vehicle</h3>
            <p class="text-gray-800">{{ $ride->vehicle->brand ?? 'N/A' }}</p>
         </div>
         <div>
            <h3 class="font-semibold text-gray-600">Status</h3>
            <p class="text-gray-800 capitalize">{{ $ride->status }}</p>
         </div>
         <div>
            <h3 class="font-semibold text-gray-600">Schedule Date</h3>
            <p class="text-gray-800">{{ $ride->scheduled_time->format('F j, Y, g:i a') }}</p>
         </div>
         <div>
            <h3 class="font-semibold text-gray-600">Booked Seats</h3>
            <p class="text-gray-800">{{ $ride->booked_seats }}</p>
         </div>
         <div>
            <h3 class="font-semibold text-gray-600">Available Seats</h3>
            <p class="text-gray-800">{{ $ride->available_seats }}</p>
         </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow">
         <h3 class="text-xl font-semibold text-gray-800 mb-4">Passenger Bookings</h3>
         @if($ride->bookings->count())
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
               <thead>
                 <tr class="border-b text-gray-600">
                   <th class="py-2 px-4">Passenger</th>
                   <th class="py-2 px-4">Pickup</th>
                   <th class="py-2 px-4">Dropoff</th>
                   <th class="py-2 px-4">Seats</th>
                   <th class="py-2 px-4">Price</th>
                   <th class="py-2 px-4">Status</th>
                 </tr>
               </thead>
               <tbody>
                 @foreach ($ride->bookings as $booking)
                <tr class="border-b hover:bg-gray-50">
                  <td class="py-2 px-4">{{ $booking->passenger->user->name ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $booking->node->pickupLocation->name ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $booking->node->dropoffLocation->name ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $booking->nb_seats }}</td>
                  <td class="py-2 px-4">${{ number_format($booking->price, 2) }}</td>
                  <td class="py-2 px-4 capitalize">{{ $booking->status }}</td>
                </tr>
              @endforeach
               </tbody>
            </table>
          </div>
       @else
          <p class="text-gray-600">No bookings for this ride yet.</p>
       @endif
      </div>

      <div class="bg-white p-6 rounded-xl shadow">
         <h3 class="text-xl font-semibold text-gray-800 mb-4">Optimized Route</h3>
         <div id="map" class="w-full h-[500px] rounded-lg border"></div>
      </div>



   </div>

   <script>
      const waypoints = @json($orderedWaypoints);
      const labeledWaypoints = @json($labeledWaypoints);
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