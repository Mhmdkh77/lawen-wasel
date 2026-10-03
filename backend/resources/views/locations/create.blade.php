<x-layout>
   <x-page-header title="Add New Location" :back="route('admin.locations.index')" />
   <div class="p-6 overflow-auto">
      <form method="POST" action="{{ route('admin.locations.store') }}">
         @include('locations._form')
      </form>
   </div>
</x-layout>
