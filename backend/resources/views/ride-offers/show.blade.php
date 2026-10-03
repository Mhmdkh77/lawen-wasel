<x-layout>
    <x-page-header title="Ride Offer #{{ $rideOffer->id }}" :back="route('admin.ride-offers.index')" />

    <div class="p-6 space-y-6 overflow-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white p-6 rounded-lg border border-gray-200">
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Driver</h3>
                <a href="{{ route('admin.drivers.show', $rideOffer->driver->user_id) }}"
                    class="text-brand-600 hover:underline">{{ $rideOffer->driver->user->name ?? 'N/A' }}</a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Passenger</h3>
                <a href="{{ route('admin.ride-requests.show', $rideOffer->ride_request_id) }}"
                    class="text-brand-600 hover:underline">{{ $rideOffer->rideRequest->passenger->user->name ?? 'N/A' }}</a>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Status</h3>
                <x-status-badge :status="$rideOffer->status" />
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Offered Price</h3>
                <p class="text-ink-900">${{ number_format($rideOffer->offered_price, 2) }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Suggested Pickup</h3>
                <p class="text-ink-900">
                    {{ $rideOffer->suggestedPickupLocation->name ?? 'Custom location' }}
                    <span class="block text-xs text-gray-400">{{ $rideOffer->suggested_pickup_latitude }}, {{ $rideOffer->suggested_pickup_longitude }}</span>
                </p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Pickup Time</h3>
                <p class="text-ink-900">{{ $rideOffer->pickup_time ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Driver Message</h3>
                <p class="text-ink-900">{{ $rideOffer->driver_message ?? 'No message.' }}</p>
            </div>
            <div>
                <h3 class="text-gray-500 font-semibold text-sm mb-1">Sent At</h3>
                <p class="text-ink-900">{{ $rideOffer->created_at->format('F j, Y, g:i a') }}</p>
            </div>
        </div>
    </div>
</x-layout>
