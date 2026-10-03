<x-layout>
   <x-page-header title="Driver Profile" :back="route('admin.drivers.index')" />
   <div class="p-6 overflow-auto">
      {{-- Header Section --}}
      <div class="flex flex-col lg:flex-row items-center lg:items-start gap-6 lg:gap-10 bg-gray-50 border border-gray-200 rounded-lg p-6">
         <img
            src="{{ $user->image ? Storage::url($user->image) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
            alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md" />

         <div class="text-center lg:text-left">
            <h2 class="text-3xl font-bold text-ink-900">{{ $user->name }}</h2>
            <p class="text-gray-600 mt-1">
               <span class="capitalize">{{ $user->role }}</span> — {{ ucfirst($user->gender ?? 'N/A') }}
            </p>
            <p class="mt-2 text-sm text-gray-500">Joined: {{ $user->created_at->format('F Y') }}</p>
            <div class="mt-3 flex items-center justify-center lg:justify-start gap-2">
               <span id="verify-status">
                  <x-status-badge :status="$user->driver->is_verified ? 'verified' : 'unverified'" />
               </span>
               <button onclick="toggleVerification({{ $user->driver->id }})"
                  class="px-3 py-1 bg-ink-800 text-white text-xs rounded-md hover:bg-ink-900 transition">
                  Toggle Verification
               </button>
            </div>
         </div>
      </div>

      {{-- Info Grid --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 text-sm">
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">Email</h3>
            <p class="text-ink-900">{{ $user->email }}</p>
         </div>
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">Phone</h3>
            <p class="text-ink-900">{{ $user->phone }}</p>
         </div>
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">City</h3>
            <p class="text-ink-900">{{ $user->city->name ?? 'N/A' }}</p>
         </div>
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">Location (Lat, Lng)</h3>
            <p class="text-ink-900">{{ $user->latitude }}, {{ $user->longitude }}</p>
         </div>
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">Vehicles</h3>
            @forelse ($user->driver->vehicles as $vehicle)
               <a href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                  class="block text-brand-600 hover:underline">{{ $vehicle->brand }} — {{ $vehicle->plate_number }}</a>
            @empty
               <p class="text-gray-500">No vehicles registered.</p>
            @endforelse
         </div>
         <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-500 mb-1">Driver License</h3>
            @if ($user->driver->driver_license)
            <button onclick="openLicenseModal('{{ Storage::url($user->driver->driver_license) }}')"
               class="text-brand-600 underline">View License</button>
         @else
            <p class="text-gray-500">No license uploaded.</p>
         @endif
         </div>
      </div>

      {{-- Map --}}
      <div class="mt-6 bg-white border border-gray-200 rounded-lg p-4">
         <h3 class="font-semibold text-gray-500 mb-2">Driver Location</h3>
         <div id="map" class="w-full h-[400px] rounded-lg border border-gray-200"></div>
      </div>


      {{-- License Modal --}}
      <div id="license-modal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center">
         <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full p-6 relative">
            <button onclick="closeLicenseModal()"
               class="absolute top-2 right-4 text-2xl text-gray-500 hover:text-ink-900">&times;</button>
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
         const url = '{{ route("admin.drivers.toggle-verification", ["driver" => "__ID__"]) }}'.replace('__ID__', driverId);

         fetch(url, {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
         })
            .then(res => res.json())
            .then(data => {
               const badgeWrapper = document.getElementById('verify-status');
               badgeWrapper.innerHTML = data.status
                  ? `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-status-verified-bg text-status-verified-text">Verified</span>`
                  : `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-status-unverified-bg text-status-unverified-text">Unverified</span>`;
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
   <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap" async
      defer></script>
</x-layout>
