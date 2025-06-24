<x-layout>
   <div class="container mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Vehicle Details</h1>

      <div class="bg-white p-4 rounded shadow space-y-2">
         <p><strong>Plate Number:</strong> {{ $vehicle->plate_number }}</p>
         <p><strong>Brand:</strong> {{ $vehicle->brand }}</p>
         <p><strong>Color:</strong> {{ $vehicle->color }}</p>
         <p><strong>Capacity:</strong> {{ $vehicle->capacity }}</p>
         <p><strong>Driver:</strong> {{ $vehicle->driver->user->name ?? 'N/A' }}</p>
      </div>

      <h2 class="text-xl font-semibold mt-6 mb-2">Vehicle Images</h2>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
         @forelse ($vehicle->images as $image)
          <div>
            <img src="{{ asset('storage/' . $image->path) }}" class="rounded-lg w-full h-48 object-cover" />
          </div>
       @empty
          <p class="text-gray-500">No images available.</p>
       @endforelse
      </div>
   </div>
</x-layout>