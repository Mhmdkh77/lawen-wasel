<x-layout>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-10 text-white">
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Users</span>
            <p>{{ $users_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Passengers</span>
            <p>{{ $passengers_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Drivers</span>
            <p>{{ $drivers_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Vehicls</span>
            <p>{{ $vehicles_count }}</p>
        </div>

        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Rides</span>
            <p>{{ $rides_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Bookings</span>
            <p>{{ $bookings_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Cities</span>
            <p>{{ $cities_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Stations</span>
            <p>{{ $stations_count }}</p>
        </div>
        <div class="bg-black px-6 py-10 rounded flex justify-between items-center">
            <span>Institutions</span>
            <p>{{ $institutions_count }}</p>
        </div>
    </div>

</x-layout>