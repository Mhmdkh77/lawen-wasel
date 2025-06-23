<x-layout>
   <div class="p-10 overflow-auto">
      {{-- Header Section --}}
      <div class="flex flex-col lg:flex-row items-center lg:items-start space-y-6 lg:space-y-0 lg:space-x-12">
         <img
            src="{{ $user->image ? Storage::url($user->image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
            alt="{{ $user->name }}" class="w-36 h-36 rounded-full object-cover border-4 border-gray-300 shadow-md" />

         <div class="text-center lg:text-left">
            <h2 class="text-4xl font-extrabold text-gray-900">{{ $user->name }}</h2>
            <p class="text-lg text-gray-600 mt-1">
               <span class="capitalize">{{ $user->role }}</span> — {{ ucfirst($user->gender ?? 'N/A') }}
            </p>
            <p class="mt-2 text-sm text-gray-500">Joined: {{ $user->created_at->format('F Y') }}</p>
         </div>
      </div>

      {{-- Info Grid --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 text-base text-gray-700">
         <div>
            <h3 class="font-semibold mb-1">Email:</h3>
            <p class="text-gray-900">{{ $user->email }}</p>
         </div>
         <div>
            <h3 class="font-semibold mb-1">Phone:</h3>
            <p class="text-gray-900">{{ $user->phone }}</p>
         </div>
         <div>
            <h3 class="font-semibold mb-1">City:</h3>
            <p class="text-gray-900">{{ $user->city->name ?? 'N/A' }}</p>
         </div>
         <div>
            <h3 class="font-semibold mb-1">Location (Lat, Lng):</h3>
            <p class="text-gray-900">{{ $user->latitude }}, {{ $user->longitude }}</p>
         </div>
         <div>
            <h3 class="font-semibold mb-1">Verified Status:</h3>
            <p id="verify-status" class="inline-block px-2 py-1 rounded text-white font-medium
               {{ $user->driver->is_verified ? 'bg-green-600' : 'bg-red-600' }}">
               {{ $user->driver->is_verified ? 'Verified' : 'Unverified' }}
            </p>
            <button onclick="toggleVerification({{ $user->driver->id }})"
               class="ml-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
               Toggle
            </button>
         </div>
         <div>
            <h3 class="font-semibold mb-1">Driver License:</h3>
            @if ($user->driver->driver_license)
            <button onclick="openLicenseModal('{{ Storage::url($user->driver->driver_license) }}')"
               class="text-blue-600 underline">View License</button>
         @else
            <p class="text-gray-500">No license uploaded.</p>
         @endif
         </div>
      </div>

      {{-- Map --}}
      <div class="mt-10">
         <h3 class="text-lg font-semibold text-gray-700 mb-2">Driver Location on Map:</h3>
         <div id="map" class="w-full h-[400px] rounded-xl border shadow-inner"></div>
      </div>


      {{-- License Modal --}}
      <div id="license-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden items-center justify-center">
         <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full p-6 relative">
            <button onclick="closeLicenseModal()"
               class="absolute top-2 right-4 text-2xl text-gray-500 hover:text-black">&times;</button>
            <div id="license-content" class="max-h-[600px] overflow-auto rounded-lg"></div>
         </div>
      </div>
   </div>
   {{-- Scripts --}}
   <script>
      function openLicenseModal(url) {
         const modal = document.getElementById('license-modal');
         const content = document.getElementById('license-content');
         if (url.endsWith('.pdf')) {
            content.innerHTML = `<iframe src="${url}" class="w-full h-[500px]" frameborder="0"></iframe>`;
         } else {
            content.innerHTML = `<img src="${url}" class="mx-auto max-h-[500px]">`;
         }
         modal.classList.remove('hidden');
         modal.classList.add('flex');
      }

      function closeLicenseModal() {
         const modal = document.getElementById('license-modal');
         modal.classList.remove('flex');
         modal.classList.add('hidden');
      }

      function toggleVerification(driverId) {
         fetch(`/admin/drivers/${driverId}/toggle-verification`, {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
         })
            .then(res => res.json())
            .then(data => {
               const status = document.getElementById('verify-status');
               status.textContent = data.status ? 'Verified' : 'Unverified';
               status.className = `inline-block px-2 py-1 rounded text-white font-medium ${data.status ? 'bg-green-600' : 'bg-red-600'}`;
            })
            .catch(() => alert('Failed to toggle verification'));
      }

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
   <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async
      defer></script>
</x-layout>