<x-layout>
   <div class=" bg-white p-6 rounded shadow overflow-auto">
      <h2 class="text-2xl font-bold mb-4">Add New Location</h2>
      <form method="POST" action="{{ route('admin.locations.update', $location) }}">
         @include('locations._form')
      </form>
      @if(isset($location))
        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST"
          onsubmit="return confirm('Are you sure you want to delete this location?');" id='delete-form' class="hidden">
          @csrf
          @method('DELETE')
        </form>
     @endif

   </div>
</x-layout>