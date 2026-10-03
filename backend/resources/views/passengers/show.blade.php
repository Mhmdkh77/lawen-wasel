<x-layout>
   <x-page-header title="Passenger Profile" :back="route('admin.passengers.index')" />
   <div class="p-6 overflow-auto">
      <div class="flex flex-col lg:flex-row gap-6 lg:gap-10 bg-gray-50 border border-gray-200 rounded-lg p-6">

         <div class="flex-shrink-0 flex flex-col items-center lg:items-start text-center lg:text-left">
            <img
               src="{{ $user->image ? Storage::url($user->image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
               alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
            <h2 class="mt-4 text-2xl font-bold text-ink-900">{{ $user->name }}</h2>
            <p class="text-gray-600 mt-1">{{ ucfirst($user->role) }} — {{ ucfirst($user->gender ?? 'N/A') }}</p>
         </div>

         <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
               <h3 class="text-gray-500 font-semibold mb-1">Email</h3>
               <p class="text-ink-900">{{ $user->email }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
               <h3 class="text-gray-500 font-semibold mb-1">Phone</h3>
               <p class="text-ink-900">{{ $user->phone }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
               <h3 class="text-gray-500 font-semibold mb-1">City</h3>
               <p class="text-ink-900">{{ $user->city->name ?? 'N/A' }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
               <h3 class="text-gray-500 font-semibold mb-1">Coordinates</h3>
               <p class="text-ink-900">{{ $user->latitude ?? 'N/A' }}, {{ $user->longitude ?? 'N/A' }}</p>
            </div>
         </div>
      </div>

      {{-- Recent bookings --}}
      <div class="mt-6 bg-white border border-gray-200 rounded-lg overflow-hidden">
         <h3 class="text-gray-500 font-semibold px-4 py-3 border-b border-gray-100 text-sm">Recent Bookings</h3>
         <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
               <tr>
                  <th class="px-4 py-2">Ride</th>
                  <th class="px-4 py-2">Seats</th>
                  <th class="px-4 py-2">Price</th>
                  <th class="px-4 py-2">Status</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
               @forelse ($user->passenger?->bookings->take(10) ?? [] as $booking)
                  <tr class="hover:bg-gray-50">
                     <td class="px-4 py-3">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                           class="text-brand-600 hover:underline">#{{ $booking->ride_id }}</a>
                     </td>
                     <td class="px-4 py-3">{{ $booking->nb_seats }}</td>
                     <td class="px-4 py-3">${{ number_format($booking->price, 2) }}</td>
                     <td class="px-4 py-3"><x-status-badge :status="$booking->status" /></td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="4" class="px-4 py-6 text-center text-gray-400">No bookings yet.</td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      {{-- Map Section --}}
      <div class="mt-6 bg-white border border-gray-200 rounded-lg p-4">
         <h3 class="text-gray-500 font-semibold mb-2">Location</h3>
         <div id="map" class="w-full h-[400px] rounded-lg border border-gray-200"></div>
      </div>

   </div>

   <script>
      function initMap() {
         const userLocation = { lat: {{ $user->latitude ?? 0 }}, lng: {{ $user->longitude ?? 0 }} };

         const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 14,
            center: userLocation,
         });

         new google.maps.Marker({
            position: userLocation,
            map: map,
            title: "{{ $user->name }}",
         });
      }
   </script>

   <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap" async
      defer></script>
</x-layout>
