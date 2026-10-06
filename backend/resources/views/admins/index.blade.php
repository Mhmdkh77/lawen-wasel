<x-layout>
    <x-page-header title="Admin Users" />
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm text-left text-gray-600" style="table-layout: fixed;">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3" style="width: 35%;">Name</th>
                    <th class="px-6 py-3" style="width: 35%;">Email</th>
                    <th class="px-6 py-3" style="width: 30%;">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-ink-900 whitespace-nowrap">
                            <a href="{{ route('admin.admins.show', $admin->id) }}" class="hover:text-brand-600 capitalize">
                                {{ $admin->name }}</a>
                        </td>
                        <td class="px-6 py-4">{{ $admin->email }}</td>
                        <td class="px-6 py-4">{{ $admin->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                            No admin users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $admins->links('pagination.admin') }}
    </div>
</x-layout>
