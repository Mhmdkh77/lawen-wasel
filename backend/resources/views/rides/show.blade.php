<x-layout>
   <x-page-header title="Ride #{{ $ride->id }}" :back="route('admin.rides.index')">
      <span class="text-sm text-gray-500">Created: {{ $ride->created_at->format('F j, Y, g:i a') }}</span>
   </x-page-header>

   <div class="p-6 space-y-6 overflow-auto">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white p-6 rounded-lg border border-gray-200">
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Driver</h3>
            <p class="text-ink-900">{{ $ride->vehicle->driver->user->name ?? 'N/A' }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Vehicle</h3>
            <p class="text-ink-900">{{ $ride->vehicle->brand ?? 'N/A' }} ({{ $ride->vehicle->plate_number ?? 'N/A' }})</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Status</h3>
            <x-status-badge :status="$ride->status" />
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Type</h3>
            <p class="text-ink-900">{{ ucfirst(str_replace('_', ' ', $ride->type)) }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Schedule Date</h3>
            <p class="text-ink-900">{{ $ride->scheduled_time->format('F j, Y, g:i a') }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold text-sm mb-1">Seats</h3>
            <p class="text-ink-900">{{ $ride->booked_seats }} booked / {{ $ride->available_seats }} available</p>
         </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
         <h3 class="font-semibold text-ink-900 px-4 py-3 border-b border-gray-100">Passenger Bookings</h3>
         @if($ride->bookings->count())
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
               <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                 <tr>
                   <th class="py-2 px-4">Passenger</th>
                   <th class="py-2 px-4">Pickup</th>
                   <th class="py-2 px-4">Dropoff</th>
                   <th class="py-2 px-4">Seats</th>
                   <th class="py-2 px-4">Price</th>
                   <th class="py-2 px-4">Status</th>
                 </tr>
               </thead>
               <tbody class="divide-y divide-gray-100">
                 @foreach ($ride->bookings as $booking)
                <tr class="hover:bg-gray-50">
                  <td class="py-2 px-4">
                     <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-brand-600 hover:underline">
                        {{ $booking->passenger->user->name ?? 'N/A' }}
                     </a>
                  </td>
                  <td class="py-2 px-4">{{ $booking->node->pickupLocation->name ?? ($booking->node ? 'Custom pickup' : 'N/A') }}</td>
                  <td class="py-2 px-4">{{ $booking->node->dropoffLocation->name ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $booking->nb_seats }}</td>
                  <td class="py-2 px-4">${{ number_format($booking->price, 2) }}</td>
                  <td class="py-2 px-4"><x-status-badge :status="$booking->status" /></td>
                </tr>
              @endforeach
               </tbody>
            </table>
          </div>
       @else
          <p class="text-gray-400 p-4">No bookings for this ride yet.</p>
       @endif
      </div>

      <div class="bg-white p-4 rounded-lg border border-gray-200">
         <h3 class="font-semibold text-ink-900 mb-3">Route Map</h3>
         <div id="map" class="w-full h-[500px] rounded-lg border border-gray-200"></div>
      </div>
   </div>

   <script>
      const waypoints = @json($orderedWaypoints);
      const labeledWaypoints = @json($labeledWaypoints);
      const driverLocation = waypoints[0];

      function initMap() {
         if (!driverLocation) {
            document.getElementById('map').textContent = 'No route coordinates available.';
            return;
         }

         const map = new google.maps.Map(document.getElementById("map"), {
            center: driverLocation,
            zoom: 10,
         });

         if (waypoints.length === 1) {
            new google.maps.Marker({ map, position: driverLocation });
            return;
         }

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

   <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap" async
      defer></script>
</x-layout>
