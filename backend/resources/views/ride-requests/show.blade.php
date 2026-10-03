<x-layout>
    <x-page-header title="Ride Request #{{ $rideRequest->id }}" :back="route('admin.ride-requests.index')" />

    <div class="p-6 space-y-6 overflow-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white p-6 rounded-lg border border-gray-200">
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Passenger</h3>
                <a href="{{ route('admin.passengers.show', $rideRequest->passenger->user_id) }}"
                    class="text-brand-600 hover:underline">{{ $rideRequest->passenger->user->name ?? 'N/A' }}</a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Status</h3>
                <x-status-badge :status="$rideRequest->status" />
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Type</h3>
                <p class="text-ink-900">{{ ucfirst(str_replace('_', ' ', $rideRequest->type)) }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Institution</h3>
                <p class="text-ink-900">{{ $rideRequest->institutionLocation->name ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Pickup Location</h3>
                <p class="text-ink-900">
                    {{ $rideRequest->passengerLocation->name ?? 'Custom location' }}
                    <span class="block text-xs text-gray-400">{{ $rideRequest->passenger_latitude }}, {{ $rideRequest->passenger_longitude }}</span>
                </p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Seats Requested</h3>
                <p class="text-ink-900">{{ $rideRequest->nb_seats_requested }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">To Institution Ride</h3>
                @if($rideRequest->toInstRide)
                    <a href="{{ route('admin.rides.show', $rideRequest->to_inst_ride_id) }}" class="text-brand-600 hover:underline">
                        #{{ $rideRequest->to_inst_ride_id }} — {{ $rideRequest->toInstRide->scheduled_time->format('M j, g:i a') }}
                    </a>
                @else
                    <p class="text-gray-400">N/A</p>
                @endif
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">From Institution Ride</h3>
                @if($rideRequest->fromInstRide)
                    <a href="{{ route('admin.rides.show', $rideRequest->from_inst_ride_id) }}" class="text-brand-600 hover:underline">
                        #{{ $rideRequest->from_inst_ride_id }} — {{ $rideRequest->fromInstRide->scheduled_time->format('M j, g:i a') }}
                    </a>
                @else
                    <p class="text-gray-400">N/A</p>
                @endif
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Requested At</h3>
                <p class="text-ink-900">{{ $rideRequest->created_at->format('F j, Y, g:i a') }}</p>
            </div>
            @if($rideRequest->notes)
                <div class="md:col-span-2 lg:col-span-3">
                    <h3 class="text-gray-500 font-semibold text-sm mb-1">Notes</h3>
                    <p class="text-ink-900">{{ $rideRequest->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <h3 class="font-semibold text-ink-900 px-4 py-3 border-b border-gray-100">Driver Offers</h3>
            @forelse($rideRequest->rideOffers as $offer)
                <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm border-b border-gray-100 last:border-b-0">
                    <div>
                        <h4 class="text-gray-500 font-semibold mb-1">Driver</h4>
                        <a href="{{ route('admin.ride-offers.show', $offer->id) }}"
                            class="text-brand-600 hover:underline">{{ $offer->driver->user->name ?? 'N/A' }}</a>
                    </div>
                    <div>
                        <h4 class="text-gray-500 font-semibold mb-1">Offered Price</h4>
                        <p class="text-ink-900">${{ number_format($offer->offered_price, 2) }}</p>
                    </div>
                    <div>
                        <h4 class="text-gray-500 font-semibold mb-1">Status</h4>
                        <x-status-badge :status="$offer->status" />
                    </div>
                    <div>
                        <h4 class="text-gray-500 font-semibold mb-1">Pickup Time</h4>
                        <p class="text-ink-900">{{ $offer->pickup_time ?? 'N/A' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 p-4">No offer has been made for this request yet.</p>
            @endforelse
        </div>
    </div>
</x-layout>
