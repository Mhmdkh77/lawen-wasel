<div class="flex flex-col h-full">
    <div class="p-2 pb-3">
        <div class="w-full ">
            <div class="relative flex items-center justify-between">
                <div class="w-full max-w-sm relative">
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

                <div class="ml-auto">
                    <a href="{{ route('admin.locations.create') }}"
                        class="inline-block bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700 transition">
                        + New
                    </a>
                </div>
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
                        style="width: 35%;">Type</x-table.th>
                    <x-table.th field='phone_number' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">Latitude</x-table.th>
                    <x-table.th field='phone_number' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">Longitude</x-table.th>
                    <x-table.th field='city' :sortField="$sortField" :sortDirection="$sortDirection"
                        style="width: 20%;">Parent City</x-table.th>
                </tr>
            </thead>
            <tbody>


                </tr>
                @forelse ($locations as $location)
                    <tr class="bg-white border-b   border-gray-200">

                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            <a href="{{  route('admin.locations.edit', $location->id)  }}">
                                {{ $location->name }}</a>
                        </th>
                        <td class="px-6 py-4">
                            {{ $location->type }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $location->latitude }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $location->longitude }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $location->city?->name }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap ">
                            No Locations found.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <div>
        {{ $locations->links() }}
    </div>
</div>