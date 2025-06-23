<div class="flex flex-col h-full">
    <div class="p-2 pb-3">
        <div class="w-full max-w-sm min-w-[200px]">
            <div class="relative flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                    class="absolute w-5 h-5 top-2.5 left-2.5 text-slate-600">
                    <path fill-rule="evenodd"
                        d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z"
                        clip-rule="evenodd" />
                </svg>

                <input wire:model.live="search"
                    class="w-full bg-white placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-md pl-10 pr-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow"
                    placeholder="Search..." />
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500" style="table-layout: fixed;">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
                <tr>
                    <x-table.th field='name' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 25%;">Name</x-table.th>
                    <x-table.th field='email' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 35%;">Email</x-table.th>
                    <x-table.th field='phone_number' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">Phone
                        Number</x-table.th>
                    <x-table.th field='city' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">City</x-table.th>
                </tr>
            </thead>
            <tbody>


                </tr>
                @forelse ($users as $user)
                    <tr class="bg-white border-b   border-gray-200">

                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            <a
                                href="{{ $user->role == 'passenger' ? route('admin.passengers.show', $user->id) : route('admin.drivers.show', $user->id)  }}">
                                {{ $user->name }}</a>
                        </th>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->phone_number }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->city?->name }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            No passengers found.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>
</div>