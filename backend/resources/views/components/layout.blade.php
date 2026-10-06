<x-html>

   <body class="flex bg-gray-50" x-data="{ sidebarOpen: false }">

      {{-- Mobile sidebar backdrop --}}
      <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden" x-cloak></div>

      <nav class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col shadow-md transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
         <div class="flex items-center gap-2 px-5 py-5 border-b border-gray-100">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" class="w-10" alt="Logo">
            <span class="font-semibold text-ink-900">Lawen Wasel</span>
         </div>
         <ul class="space-y-1 w-full py-4 overflow-y-auto">
            <x-sidebar-link route="admin.dashboard" icon="fa-solid fa-gauge">Dashboard</x-sidebar-link>
            <x-sidebar-link route="admin.drivers.index" icon="fa-solid fa-id-card">Drivers</x-sidebar-link>
            <x-sidebar-link route="admin.passengers.index" icon="fa-solid fa-users">Passengers</x-sidebar-link>
            <x-sidebar-link route="admin.vehicles.index" icon="fa-solid fa-car">Vehicles</x-sidebar-link>
            <x-sidebar-link route="admin.rides.index" icon="fa-solid fa-route">Rides</x-sidebar-link>
            <x-sidebar-link route="admin.bookings.index" icon="fa-solid fa-ticket">Bookings</x-sidebar-link>
            <x-sidebar-link route="admin.ride-requests.index" icon="fa-solid fa-hand">Ride Requests</x-sidebar-link>
            <x-sidebar-link route="admin.ride-offers.index" icon="fa-solid fa-tag">Ride Offers</x-sidebar-link>
            <x-sidebar-link route="admin.ride-template-groups.index" icon="fa-solid fa-calendar-days">Ride Templates</x-sidebar-link>
            <x-sidebar-link route="admin.ratings.index" icon="fa-solid fa-star">Ratings</x-sidebar-link>
            <x-sidebar-link route="admin.locations.index" icon="fa-solid fa-location-dot">Locations</x-sidebar-link>
            <li class="my-2 mx-5 border-t border-gray-100"></li>
            <x-sidebar-link route="admin.admins.index" icon="fa-solid fa-user-shield">Admin Users</x-sidebar-link>
         </ul>
      </nav>

      <div class="flex-1 min-w-0 flex flex-col h-screen">
         <div class="flex items-center justify-between gap-4 px-6 py-3 border-b border-gray-200 bg-white h-14 shrink-0">
            <div class="flex items-center gap-4">
               <button @click="sidebarOpen = true" class="lg:hidden text-ink-800 text-lg">
                  <i class="fa-solid fa-bars"></i>
               </button>
               <p class="capitalize text-sm text-ink-800 font-medium">{{ auth('admin')->user()->name }}</p>
            </div>

            <form action="{{ route('admin.logout') }}" method="POST">
               @csrf
               @method('DELETE')
               <button type="submit"
                  class="inline-flex min-h-9 items-center justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-ink-800 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                  Sign out
               </button>
            </form>
         </div>

         <x-flash-messages />

         <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-lg border border-gray-200 min-h-full flex flex-col overflow-hidden">
               {{ $slot }}
            </div>
         </main>
      </div>

      @livewireScripts
   </body>
</x-html>
