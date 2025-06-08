<div class="flex flex-col h-full">
    <div class="pb-4">
        <div class="relative mt-1">
            <input type="text" id="table-search" wire:model.live="search"
                class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 "
                placeholder="Search for items">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
        </div>
    </div>
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 ">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 ">
                <tr>
                    <x-table.th field='name' :sortField="$sortField" :sortDirection="$sortDirection">Name</x-table.th>
                    <x-table.th field='email' :sortField="$sortField" :sortDirection="$sortDirection">Email</x-table.th>
                    <x-table.th field='phone_number' :sortField="$sortField" :sortDirection="$sortDirection">Phone
                        Number</x-table.th>
                    <x-table.th field='city' :sortField="$sortField" :sortDirection="$sortDirection">City</x-table.th>
                    <th scope="col" class="px-6 py-3">
                        <span class="sr-only">Edit</span>
                    </th>
                </tr>
            </thead>
            <tbody>


                </tr>
                @forelse ($users as $user)
                    <tr class="bg-white border-b   border-gray-200">

                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            <a href="{{ route('admin.passengers.show', $user->id) }}"> {{ $user->name }}</a>
                        </th>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->phone_number }}
                        </td>
                        <td class="px-6 py-4">
                            To DO
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="#" class="font-medium text-blue-600  hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="p-2 text-center">No passengers found.</td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>
</div>