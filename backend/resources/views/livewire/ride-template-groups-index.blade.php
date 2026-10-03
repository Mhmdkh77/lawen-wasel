<div class="flex flex-col h-full">
    <div class="p-4 flex flex-wrap items-center gap-3">
        <div class="w-full max-w-sm min-w-[200px]">
            <div class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input wire:model.live="search"
                    class="w-full bg-white placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-9 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-brand-500 hover:border-slate-300 shadow-sm focus:shadow"
                    placeholder="Search by name or driver..." />
            </div>
        </div>
        <select wire:model.live="activeFilter"
            class="text-sm border border-slate-200 rounded-md px-3 py-2 text-slate-700 focus:outline-none focus:border-brand-500">
            <option value="">All</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <x-table.th field='name' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 30%;">Name</x-table.th>
                    <th class="px-6 py-3" style="width: 25%;">Driver</th>
                    <th class="px-6 py-3" style="width: 20%;">Templates</th>
                    <x-table.th field='is_active' :sortField="$sortField" :sortDirection="$sortDirection" style="width: 25%;">Status</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($groups as $group)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">
                            <a href="{{ route('admin.ride-template-groups.show', $group->id) }}" class="hover:text-brand-600">
                                {{ $group->name }}</a>
                        </td>
                        <td class="px-6 py-4">{{ $group->driver->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $group->ride_templates_count }} template(s)</td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$group->is_active ? 'verified' : 'unverified'" :label="$group->is_active ? 'Active' : 'Inactive'" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            No ride templates found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $groups->links() }}
    </div>
</div>
