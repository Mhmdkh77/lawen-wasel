<x-layout>
   <x-page-header title="Vehicle Details" :back="route('admin.vehicles.index')" />
   <div class="p-6">
      <div class="bg-white border border-gray-200 rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Plate Number</h3>
            <p class="text-ink-900">{{ $vehicle->plate_number }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Brand</h3>
            <p class="text-ink-900">{{ $vehicle->brand }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Color</h3>
            <p class="text-ink-900">{{ $vehicle->color }}</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Capacity</h3>
            <p class="text-ink-900">{{ $vehicle->capacity }} seats</p>
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Driver</h3>
            @if ($vehicle->driver)
               <a href="{{ route('admin.drivers.show', $vehicle->driver->user_id) }}"
                  class="text-brand-600 hover:underline">{{ $vehicle->driver->user->name ?? 'N/A' }}</a>
            @else
               <p class="text-ink-900">N/A</p>
            @endif
         </div>
         <div>
            <h3 class="text-gray-500 font-semibold mb-1">Default Vehicle</h3>
            @if ($vehicle->driver && $vehicle->driver->default_vehicle_id === $vehicle->id)
               <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-status-verified-bg text-status-verified-text">
                  <i class="fa-solid fa-star mr-1"></i> Default
               </span>
            @else
               <p class="text-gray-400">No</p>
            @endif
         </div>
      </div>

      <div class="mt-6 bg-white border border-gray-200 rounded-lg p-4">
         <h2 class="text-gray-500 font-semibold mb-3 text-sm">Vehicle Images</h2>
         <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse ($vehicle->images as $image)
             <div>
               <img src="{{ asset('storage/' . $image->path) }}" class="rounded-lg w-full h-48 object-cover border border-gray-200" />
             </div>
          @empty
             <p class="text-gray-400">No images available.</p>
          @endforelse
         </div>
      </div>
   </div>
</x-layout>
