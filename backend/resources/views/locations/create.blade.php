<x-layout>
   <div class="overflow-auto bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-4">Add New Location</h2>
      <form method="POST" action="{{ route('admin.locations.store') }}">
         @include('locations._form')
      </form>

      
   </div>
</x-layout>