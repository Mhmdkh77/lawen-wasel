<x-layout>
    <x-page-header title="Dashboard" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
        <x-stat-card label="Users" :value="$users_count" icon="fa-solid fa-users" />
        <x-stat-card label="Passengers" :value="$passengers_count" icon="fa-solid fa-user"
            :href="route('admin.passengers.index')" />
        <x-stat-card label="Drivers" :value="$drivers_count" icon="fa-solid fa-id-card"
            :href="route('admin.drivers.index')" />
        <x-stat-card label="Vehicles" :value="$vehicles_count" icon="fa-solid fa-car"
            :href="route('admin.vehicles.index')" />
        <x-stat-card label="Rides" :value="$rides_count" icon="fa-solid fa-route"
            :href="route('admin.rides.index')" />
        <x-stat-card label="Active Bookings" :value="$bookings_count" icon="fa-solid fa-ticket"
            :href="route('admin.bookings.index')" />
        <x-stat-card label="Cities" :value="$cities_count" icon="fa-solid fa-city"
            :href="route('admin.locations.index')" />
        <x-stat-card label="Stations" :value="$stations_count" icon="fa-solid fa-train-subway"
            :href="route('admin.locations.index')" />
        <x-stat-card label="Institutions" :value="$institutions_count" icon="fa-solid fa-building-columns"
            :href="route('admin.locations.index')" />
    </div>
</x-layout>
