<div class="flex flex-col h-full">
    <div class="p-4">
        <div class="w-full max-w-sm min-w-[200px]">
            <div class="relative flex items-center">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input wire:model.live="search"
                    class="w-full bg-white placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-9 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-brand-500 hover:border-slate-300 shadow-sm focus:shadow"
                    placeholder="Search passengers..." />
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <x-table.th field='name' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 30%;">Name</x-table.th>
                    <x-table.th field='email' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 35%;">Email</x-table.th>
                    <x-table.th field='phone' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">Phone</x-table.th>
                    <x-table.th field='city' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 15%;">City</x-table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">
                            <a href="{{ route('admin.passengers.show', $user->id) }}" class="hover:text-brand-600">
                                {{ $user->name }}</a>
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->phone }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->city?->name ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            No passengers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $users->links('pagination.admin', ['livewire' => true]) }}
    </div>
</div>
