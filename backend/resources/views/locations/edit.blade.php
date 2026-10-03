<x-layout>
   <x-page-header title="Edit Location" :back="route('admin.locations.index')" />
   <div class="p-6 overflow-auto">
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
