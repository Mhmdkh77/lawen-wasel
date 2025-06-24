<x-html>

   <body class="flex">

      <nav class="w-[13%] bg-gray-50 flex flex-col items-center shadow-md z-10 h-screen">
         <div class="">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" class="w-28" alt="Logo">
         </div>
         <ul class="space-y-2 w-full">
            <x-sidebar-link route="admin.dashboard">Dashboard</x-sidebar-link>
            <x-sidebar-link route="admin.drivers.index">Drivers</x-sidebar-link>
            <x-sidebar-link route="admin.passengers.index">Passengers</x-sidebar-link>
            <x-sidebar-link route="admin.rides.index">Rides</x-sidebar-link>
            <x-sidebar-link route="admin.vehicles.index">Vehicles</x-sidebar-link>
            <x-sidebar-link route="admin.locations.index">Locations</x-sidebar-link>
         </ul>
      </nav>
      <main class="flex-1 bg-black z-0 max-h-screen flex flex-col">
         <div class="py-3 px-6 text-white flex justify-between border-b-3 h-13 items-center">
            <p class="capitalize">{{ auth('admin')->user()->name }} </p>

            <form action="{{ route('admin.logout') }}" method="POST">
               @csrf
               @method('DELETE')
               <button class="cursor-pointer bg-white rounded text-xs text-black px-2 py-1 hover:bg-gray-200">Log
                  Out</button>
            </form>
         </div>
         <div class="p-6 h-[calc(100vh-52px)]">
            <div class="bg-gray-100 h-full rounded flex-1 flex flex-col overflow-hidden">{{ $slot }}</div>
         </div>

      </main>
      @livewireScripts
   </body>
</x-html>