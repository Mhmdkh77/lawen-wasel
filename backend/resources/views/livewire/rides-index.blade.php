<div class="flex flex-col h-full">
    <div class="p-4 flex flex-wrap items-center gap-3">
        <div class="w-full max-w-sm min-w-[200px]">
            <div class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input wire:model.live="search"
                    class="w-full bg-white placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-9 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-brand-500 hover:border-slate-300 shadow-sm focus:shadow"
                    placeholder="Search rides..." />
            </div>
        </div>
        <select wire:model.live="statusFilter"
            class="text-sm border border-slate-200 rounded-md px-3 py-2 text-slate-700 focus:outline-none focus:border-brand-500">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="canceled">Canceled</option>
        </select>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <x-table.th field='driver_name' :sortField="$sortField" :sortDirection="$sortDirection">Driver
                        Name</x-table.th>
                    <x-table.th field='scheduled_time' :sortField="$sortField" :sortDirection="$sortDirection">Scheduled
                        Time</x-table.th>
                    <x-table.th field='type' :sortField="$sortField" :sortDirection="$sortDirection">Type</x-table.th>
                    <x-table.th field='booked_seats' :sortField="$sortField" :sortDirection="$sortDirection">Booked
                        Seats</x-table.th>
                    <x-table.th field='available_seats' :sortField="$sortField"
                        :sortDirection="$sortDirection">Available Seats</x-table.th>
                    <x-table.th field='status' :sortField="$sortField"
                        :sortDirection="$sortDirection">Status</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rides as $ride)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">
                            <a href="{{ route('admin.rides.show', $ride->id) }}" class="hover:text-brand-600">
                                {{ $ride->driver->user->name ?? 'N/A' }}</a>
                        </td>
                        <td class="px-6 py-4">
                            {{ $ride->scheduled_time }}
                        </td>
                        <td class="px-6 py-4">
                            {{ ucfirst(str_replace('_', ' ', $ride->type)) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $ride->booked_seats }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $ride->available_seats }}
                        </td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$ride->status" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No rides found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $rides->links() }}
    </div>
</div>
