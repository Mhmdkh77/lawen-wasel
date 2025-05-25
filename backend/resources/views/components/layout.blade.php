<x-html>

   <body class="bg-black flex w-screen h-screen">

      <nav class="w-1/6 bg-gray-50 flex  flex-col items-center ">
         <div class="">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" class="w-32" alt="Logo">
         </div>
         <ul class="space-y-2 w-full">
            <x-sidebar-link route="admin.dashboard">Dashboard</x-sidebar-link>
            <x-sidebar-link route="admin.drivers.index">Drivers</x-sidebar-link>
            <x-sidebar-link route="admin.passengers.index">Passengers</x-sidebar-link>
            <x-sidebar-link route="admin.rides.index">Rides</x-sidebar-link>
            <x-sidebar-link route="admin.stations.index">Stations</x-sidebar-link>
         </ul>
      </nav>
      <main class="w-5/6">

      </main>

   </body>


</x-html>