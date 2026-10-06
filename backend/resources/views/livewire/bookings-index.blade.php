<div class="flex flex-col h-full">
    <div class="p-4 flex flex-wrap items-center gap-3">
        <div class="w-full max-w-sm min-w-[200px]">
            <div class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input wire:model.live="search"
                    class="w-full bg-white placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-9 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-brand-500 hover:border-slate-300 shadow-sm focus:shadow"
                    placeholder="Search by passenger or ride ID..." />
            </div>
        </div>
        <select wire:model.live="statusFilter"
            class="text-sm border border-slate-200 rounded-md px-3 py-2 text-slate-700 focus:outline-none focus:border-brand-500">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="passenger_canceled">Passenger Canceled</option>
            <option value="ride_canceled">Ride Canceled</option>
        </select>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3" style="width: 25%;">Passenger</th>
                    <th class="px-6 py-3" style="width: 15%;">Ride</th>
                    <x-table.th field='nb_seats' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 15%;">Seats</x-table.th>
                    <x-table.th field='price' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 15%;">Price</x-table.th>
                    <x-table.th field='status' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 15%;">Status</x-table.th>
                    <x-table.th field='created_at' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 15%;">Booked At</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="hover:text-brand-600">
                                {{ $booking->passenger->user->name ?? 'N/A' }}</a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.rides.show', $booking->ride_id) }}" class="text-brand-600 hover:underline">
                                #{{ $booking->ride_id }}
                            </a>
                        </td>
                        <td class="px-6 py-4">{{ $booking->nb_seats }}</td>
                        <td class="px-6 py-4">${{ number_format($booking->price, 2) }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$booking->status" /></td>
                        <td class="px-6 py-4">{{ $booking->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No bookings found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $bookings->links('pagination.admin', ['livewire' => true]) }}
    </div>
</div>
