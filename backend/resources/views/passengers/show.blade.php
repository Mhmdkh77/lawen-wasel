<x-layout>
    <x-page-header title="Passenger profile" :back="route('admin.passengers.index')" />

    <div class="space-y-6 p-6">
        <x-profile-hero
            :name="$user->name"
            role="Passenger"
            :image="$user->image ? Storage::url($user->image) : null"
            :subtitle="$user->city?->name"
            :joined="$user->created_at?->format('F Y')"
        />

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <h3 class="text-base font-semibold text-ink-900">Account details</h3>
                <dl class="mt-4 divide-y divide-gray-100 text-sm">
                    <div class="py-3">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="mt-1 break-words font-medium text-ink-900">{{ $user->email }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">Gender</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->gender ? ucfirst($user->gender) : 'Not provided' }}</dd>
                    </div>
                    <div class="py-3">
                        <dt class="text-gray-500">City</dt>
                        <dd class="mt-1 font-medium text-ink-900">{{ $user->city?->name ?? 'Not provided' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-base font-semibold text-ink-900">Saved location</h3>
                    @if ($user->latitude !== null && $user->longitude !== null)
                        <span class="text-xs text-gray-500">{{ number_format((float) $user->latitude, 5) }}, {{ number_format((float) $user->longitude, 5) }}</span>
                    @endif
                </div>
                <x-profile-map :latitude="$user->latitude" :longitude="$user->longitude" :label="$user->name" />
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-ink-900">Recent bookings</h3>
                <span class="text-xs text-gray-500">Latest 10</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[620px] w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">Ride</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Scheduled</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Seats</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Price</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($user->passenger?->bookings ?? [] as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="font-semibold text-brand-700 hover:underline">Ride #{{ $booking->ride_id }}</a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $booking->ride?->scheduled_time?->format('M j, Y · g:i A') ?? '—' }}</td>
                                <td class="px-5 py-3 text-ink-900">{{ $booking->nb_seats }}</td>
                                <td class="px-5 py-3 text-ink-900">${{ number_format($booking->price, 2) }}</td>
                                <td class="px-5 py-3"><x-status-badge :status="$booking->status" :label="$booking->status === 'active' ? 'Confirmed' : null" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layout>
