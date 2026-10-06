<x-layout>
    <x-page-header title="Dashboard">
        <span class="text-sm text-ink-700">{{ now()->format('l, F j, Y') }}</span>
    </x-page-header>

    <div class="space-y-6 bg-[#f8fafb] p-4 sm:p-6">
        @php
            $cards = [
                ['label' => 'Rides today', 'value' => $overview['rides_today'], 'detail' => 'Scheduled for today', 'icon' => 'fa-solid fa-calendar-day', 'href' => route('admin.rides.index')],
                ['label' => 'In progress', 'value' => $overview['rides_in_progress'], 'detail' => 'Rides currently active', 'icon' => 'fa-solid fa-route', 'href' => route('admin.rides.index')],
                ['label' => 'Active bookings', 'value' => $overview['active_bookings'], 'detail' => 'Confirmed seats', 'icon' => 'fa-solid fa-ticket', 'href' => route('admin.bookings.index')],
                ['label' => 'Open requests', 'value' => $overview['open_requests'], 'detail' => 'Pending or driver offered', 'icon' => 'fa-solid fa-inbox', 'href' => route('admin.ride-requests.index')],
            ];
        @endphp

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Operations overview">
            @foreach ($cards as $card)
                <a href="{{ $card['href'] }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-brand-400 hover:shadow-md focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium text-ink-700">{{ $card['label'] }}</p>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true">
                            <i class="{{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-ink-900">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 text-xs text-ink-700">{{ $card['detail'] }}</p>
                </a>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" aria-labelledby="upcoming-rides-heading">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="upcoming-rides-heading" class="font-semibold text-ink-900">Upcoming rides</h2>
                        <p class="mt-0.5 text-xs text-ink-700">Next scheduled departures</p>
                    </div>
                    <a href="{{ route('admin.rides.index') }}" class="shrink-0 text-sm font-semibold text-ink-800 hover:text-brand-700">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[660px] w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs font-medium uppercase tracking-wide text-ink-700">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Ride</th>
                                <th scope="col" class="px-5 py-3">Driver</th>
                                <th scope="col" class="px-5 py-3">Departure</th>
                                <th scope="col" class="px-5 py-3">Seats</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($upcomingRides as $ride)
                                <tr class="hover:bg-gray-50/70">
                                    <td class="px-5 py-3.5 sm:px-6">
                                        <a href="{{ route('admin.rides.show', $ride) }}" class="font-semibold text-ink-900 hover:text-brand-700">Ride #{{ $ride->id }}</a>
                                        <p class="mt-0.5 text-xs text-ink-700">{{ $ride->type === 'to_institution' ? 'To institution' : 'From institution' }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-ink-800">{{ $ride->vehicle?->driver?->user?->name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-ink-800">{{ $ride->scheduled_time->format('M j, g:i A') }}</td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-ink-800">{{ $ride->booked_seats }} / {{ $ride->booked_seats + $ride->available_seats }}</td>
                                    <td class="px-5 py-3.5"><x-status-badge :status="$ride->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-ink-700">No upcoming rides scheduled.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="attention-heading">
                <h2 id="attention-heading" class="font-semibold text-ink-900">Needs attention</h2>
                <p class="mt-0.5 text-xs text-ink-700">Items to review across the platform</p>
                @php
                    $attentionItems = [
                        ['label' => 'Pending requests', 'count' => $attention['pending_requests'], 'href' => route('admin.ride-requests.index')],
                        ['label' => 'Driver offered requests', 'count' => $attention['driver_offered_requests'], 'href' => route('admin.ride-requests.index')],
                        ['label' => 'Pending offers', 'count' => $attention['pending_offers'], 'href' => route('admin.ride-offers.index')],
                        ['label' => 'Unverified drivers', 'count' => $attention['unverified_drivers'], 'href' => route('admin.drivers.index')],
                    ];
                @endphp
                <div class="mt-5 divide-y divide-gray-100">
                    @foreach ($attentionItems as $item)
                        <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0 hover:text-brand-700">
                            <span class="text-sm font-medium text-ink-800">{{ $item['label'] }}</span>
                            <span class="inline-flex min-w-8 items-center justify-center rounded-lg px-2 py-1 text-sm font-semibold {{ $item['count'] ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500' }}">{{ number_format($item['count']) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="schedule-heading">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="schedule-heading" class="font-semibold text-ink-900">Ride schedule</h2>
                        <p class="mt-0.5 text-xs text-ink-700">Scheduled rides over the next 7 days</p>
                    </div>
                    <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">{{ $schedule->sum('count') }} total</span>
                </div>
                <div class="mt-7 grid grid-cols-7 gap-2 sm:gap-4">
                    @foreach ($schedule as $day)
                        <div class="flex min-w-0 flex-col items-center">
                            <span class="mb-2 text-xs font-semibold text-ink-800">{{ $day['count'] }}</span>
                            <div class="flex h-32 w-full items-end justify-center rounded-lg bg-gray-50 px-1.5">
                                <div class="w-full max-w-10 rounded-t-md {{ $day['count'] ? 'bg-brand-500' : 'bg-gray-200' }}" style="height: {{ $day['height'] }}%" title="{{ $day['date'] }}: {{ $day['count'] }} rides"></div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-ink-800">{{ $day['label'] }}</span>
                            <span class="text-[11px] text-ink-700">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" aria-labelledby="recent-requests-heading">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="recent-requests-heading" class="font-semibold text-ink-900">Recent open requests</h2>
                        <p class="mt-0.5 text-xs text-ink-700">Latest passenger requests awaiting a decision</p>
                    </div>
                    <a href="{{ route('admin.ride-requests.index') }}" class="shrink-0 text-sm font-semibold text-ink-800 hover:text-brand-700">View all</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($recentRequests as $request)
                        <a href="{{ route('admin.ride-requests.show', $request) }}" class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-gray-50/70 sm:px-6">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink-900">{{ $request->passenger?->user?->name ?? 'Unknown passenger' }}</p>
                                <p class="mt-0.5 truncate text-xs text-ink-700">{{ $request->institutionLocation?->name ?? 'Unknown institution' }} · {{ $request->nb_seats_requested }} {{ $request->nb_seats_requested === 1 ? 'seat' : 'seats' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <x-status-badge :status="$request->status" />
                                <p class="mt-1 text-xs text-ink-700">{{ $request->created_at->format('M j') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="px-6 py-10 text-center text-sm text-ink-700">No open requests right now.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-sm sm:px-6" aria-label="Platform totals">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <a href="{{ route('admin.rides.index') }}" class="text-sm text-ink-700 hover:text-brand-700">All rides <span class="ml-1 font-semibold text-ink-900">{{ number_format($network['rides']) }}</span></a>
                <a href="{{ route('admin.drivers.index') }}" class="text-sm text-ink-700 hover:text-brand-700">Drivers <span class="ml-1 font-semibold text-ink-900">{{ number_format($network['drivers']) }}</span></a>
                <a href="{{ route('admin.passengers.index') }}" class="text-sm text-ink-700 hover:text-brand-700">Passengers <span class="ml-1 font-semibold text-ink-900">{{ number_format($network['passengers']) }}</span></a>
                <a href="{{ route('admin.vehicles.index') }}" class="text-sm text-ink-700 hover:text-brand-700">Vehicles <span class="ml-1 font-semibold text-ink-900">{{ number_format($network['vehicles']) }}</span></a>
            </div>
        </section>
    </div>
</x-layout>
