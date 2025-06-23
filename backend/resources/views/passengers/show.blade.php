<x-layout>

   <div class=" rounded-2xl p-10 overflow-auto">
      <div class="flex flex-col lg:flex-row space-y-10 lg:space-y-0 lg:space-x-12">

         <div class="flex-shrink-0 flex flex-col items-center lg:items-start text-center lg:text-left">
            <img
               src="{{ $user->image ? Storage::url($user->image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
               alt="{{ $user->name }}" class="w-36 h-36 rounded-full object-cover border-4 border-gray-300">
            <h2 class="mt-4 text-3xl font-bold text-gray-800">{{ $user->name }}</h2>
            <p class="text-lg text-gray-600 mt-1">{{ ucfirst($user->role) }} — {{ ucfirst($user->gender ?? 'N/A') }}
            </p>
         </div>


         <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 text-lg">
            <div>
               <h3 class="text-gray-700 font-semibold">Email:</h3>
               <p class="text-gray-900">{{ $user->email }}</p>
            </div>

            <div>
               <h3 class="text-gray-700 font-semibold">Phone:</h3>
               <p class="text-gray-900">{{ $user->phone }}</p>
            </div>

            <div>
               <h3 class="text-gray-700 font-semibold">City:</h3>
               <p class="text-gray-900">{{ $user->city->name ?? 'N/A' }}</p>
            </div>

            <div>
               <h3 class="text-gray-700 font-semibold">Coordinates:</h3>
               <p class="text-gray-900">
                  {{ $user->latitude ?? 'N/A' }}, {{ $user->longitude ?? 'N/A' }}
               </p>
            </div>

         </div>
      </div>

      {{-- Map Section --}}
      <div class="mt-12">
         <h3 class="text-gray-700 font-semibold mb-2">Location on Map:</h3>
         <div id="map" class="w-full h-[400px] rounded-lg border"></div>
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

   <script src="https://maps.googleapis.com/maps/api/js?key={{  env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async
      defer></script>
</x-layout>