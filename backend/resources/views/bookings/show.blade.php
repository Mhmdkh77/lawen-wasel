<x-layout>
    <x-page-header title="Booking #{{ $booking->id }}" :back="route('admin.bookings.index')" />

    <div class="p-6 space-y-6 overflow-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-6 rounded-lg border border-gray-200">
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Passenger</h3>
                <a href="{{ route('admin.passengers.show', $booking->passenger->user_id) }}"
                    class="text-brand-600 hover:underline">{{ $booking->passenger->user->name ?? 'N/A' }}</a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Ride</h3>
                <a href="{{ route('admin.rides.show', $booking->ride_id) }}" class="text-brand-600 hover:underline">
                    #{{ $booking->ride_id }} — {{ $booking->ride->vehicle->driver->user->name ?? 'N/A' }}
                </a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Seats / Price</h3>
                <p class="text-ink-900">{{ $booking->nb_seats }} seat(s) — ${{ number_format($booking->price, 2) }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Status</h3>
                <x-status-badge :status="$booking->status" />
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Pickup</h3>
                <p class="text-ink-900">
                    {{ $booking->node?->pickupLocation?->name ?? 'Custom location' }}
                    @if($booking->node)
                        <span class="block text-xs text-gray-400">{{ $booking->node->pickup_latitude }}, {{ $booking->node->pickup_longitude }}</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Dropoff</h3>
                <p class="text-ink-900">
                    {{ $booking->node?->dropoffLocation?->name ?? 'N/A' }}
                    @if($booking->node)
                        <span class="block text-xs text-gray-400">{{ $booking->node->dropoff_latitude }}, {{ $booking->node->dropoff_longitude }}</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Ride Request</h3>
                @if($booking->rideRequest)
                    <a href="{{ route('admin.ride-requests.show', $booking->ride_request_id) }}"
                        class="text-brand-600 hover:underline">#{{ $booking->ride_request_id }}</a>
                @else
                    <p class="text-gray-400">N/A</p>
                @endif
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Booked At</h3>
                <p class="text-ink-900">{{ $booking->created_at->format('F j, Y, g:i a') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <h3 class="font-semibold text-ink-900 px-4 py-3 border-b border-gray-100">Other Bookings in This Group</h3>
            @php
                $otherBookings = $booking->bookingGroup?->bookings->where('id', '!=', $booking->id) ?? collect();
            @endphp
            @if($otherBookings->isNotEmpty())
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="py-2 px-4">Booking</th>
                            <th class="py-2 px-4">Ride</th>
                            <th class="py-2 px-4">Seats</th>
                            <th class="py-2 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($otherBookings as $other)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4">
                                    <a href="{{ route('admin.bookings.show', $other->id) }}" class="text-brand-600 hover:underline">#{{ $other->id }}</a>
                                </td>
                                <td class="py-2 px-4">#{{ $other->ride_id }} ({{ $other->ride->type ?? 'N/A' }})</td>
                                <td class="py-2 px-4">{{ $other->nb_seats }}</td>
                                <td class="py-2 px-4"><x-status-badge :status="$other->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-400 p-4">This booking is not part of a multi-booking group.</p>
            @endif
        </div>
    </div>
</x-layout>
